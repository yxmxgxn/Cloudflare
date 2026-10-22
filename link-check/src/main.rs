//! ビルド済み public/ のリンクを検査する。Python/check_links.py の Rust 版(移植中)。
//!
//! 使い方:
//!     cargo run --release --manifest-path link-check/Cargo.toml -- --root public
//!
//! リンクの収集・サイト内リンクの検査・外部リンクのHTTP検査まで移した。
//! 結果はまだ1行1件のタブ区切りで標準出力に出すだけで、レポート(report.md / stats.json)は
//! Python 版が作る。--list を付けるとHTTPは送らず、検査対象の一覧だけを出す。
//! 判断の方針は check_links.py 冒頭のコメントを参照。

mod check;
mod collect;
mod internal;
mod redirect;
mod urlsplit;

use std::collections::BTreeMap;
use std::path::PathBuf;
use std::process::ExitCode;
use std::time::Instant;

use clap::Parser;

use crate::check::{Options, check_all};
use crate::collect::{collect_links, is_ignored, load_ignore_rules};
use crate::internal::{load_redirects, resolve_internal};
use crate::redirect::Verdict;
use crate::urlsplit::urlsplit;

#[derive(Parser)]
struct Args {
    /// ビルド済みサイトのディレクトリ
    #[arg(long, default_value = "public")]
    root: PathBuf,
    #[arg(long, default_value = "https://karin-archive.com")]
    base_url: String,
    /// 除外リスト
    #[arg(long, default_value = concat!(env!("CARGO_MANIFEST_DIR"), "/ignore.txt"))]
    ignore: PathBuf,
    /// 動作確認用: 検査するURL数の上限
    #[arg(long, default_value_t = 0)]
    limit: usize,
    /// 並列に処理するドメイン数
    #[arg(long, default_value_t = 12)]
    workers: usize,
    /// リクエスト間隔の倍率
    #[arg(long, default_value_t = 1.0)]
    delay_scale: f64,
    /// 直列で再検査する最大件数
    #[arg(long, default_value_t = 300)]
    recheck_limit: usize,
    /// HTTPは送らず、検査対象の一覧だけを出す
    #[arg(long)]
    list: bool,
}

#[tokio::main]
async fn main() -> ExitCode {
    let args = Args::parse();
    if !args.root.is_dir() {
        eprintln!(
            "エラー: {} がありません。先に build/build.sh を実行してください。",
            args.root.display()
        );
        return ExitCode::from(2);
    }

    let rules = match load_ignore_rules(&args.ignore) {
        Ok(r) => r,
        Err(e) => {
            eprintln!("エラー: {} を読めません: {e}", args.ignore.display());
            return ExitCode::from(2);
        }
    };
    let (external, internal) = match collect_links(&args.root, &args.base_url) {
        Ok(links) => links,
        Err(e) => {
            eprintln!("エラー: HTMLを読めません: {e}");
            return ExitCode::from(2);
        }
    };

    // 内部リンクはファイルの有無で判定する(HTTPリクエスト不要)
    let redirects = load_redirects(&args.root);
    let internal_broken: Vec<(&String, _)> = internal
        .iter()
        .filter(|(path, _)| !resolve_internal(path, &args.root, &redirects))
        .collect();

    // BTreeMap なのでキーは既にソート済み
    let mut targets: Vec<&String> = external.keys().filter(|u| !is_ignored(u, &rules)).collect();
    let ignored = external.len() - targets.len();
    if args.limit > 0 {
        targets.truncate(args.limit);
    }

    eprintln!(
        "内部リンク {} 件（切れ {} 件）",
        internal.len(),
        internal_broken.len()
    );
    eprintln!(
        "外部リンク {} 件中 {} 件を検査（除外 {} 件）",
        external.len(),
        targets.len(),
        ignored
    );

    let mut by_host: BTreeMap<String, Vec<String>> = BTreeMap::new();
    for url in &targets {
        by_host
            .entry(urlsplit(url).hostname())
            .or_default()
            .push(url.to_string());
    }
    let mut busiest: Vec<_> = by_host.iter().map(|(h, u)| (h, u.len())).collect();
    busiest.sort_by_key(|(_, n)| std::cmp::Reverse(*n));
    let top: Vec<String> = busiest
        .iter()
        .take(5)
        .map(|(h, n)| format!("{h}={n}"))
        .collect();
    eprintln!("ドメイン数 {}（最多: {}）", by_host.len(), top.join(", "));

    for (path, pages) in &internal_broken {
        let pages: Vec<&str> = pages.iter().map(String::as_str).collect();
        println!("broken-internal\t{path}\t{}", pages.join(" "));
    }
    if args.list {
        for url in &targets {
            println!("check\t{url}");
        }
        return ExitCode::SUCCESS;
    }

    let opts = Options {
        workers: args.workers,
        delay_scale: args.delay_scale,
        recheck_limit: args.recheck_limit,
    };
    let started = Instant::now();
    let results = match check_all(by_host, &opts).await {
        Ok(r) => r,
        Err(e) => {
            eprintln!("エラー: HTTPクライアントを作れません: {e}");
            return ExitCode::from(2);
        }
    };
    for (url, o) in &results {
        println!("{}\t{url}\t{}\t{}", o.verdict.as_str(), o.detail, o.last);
    }
    let count = |v: Verdict| results.iter().filter(|(_, o)| o.verdict == v).count();
    eprintln!(
        "リンク切れ 外部 {} / 内部 {}・https化 {}・転送 {}・要確認 {}（所要 {} 秒）",
        count(Verdict::Broken),
        internal_broken.len(),
        count(Verdict::Https),
        count(Verdict::Moved),
        count(Verdict::Warn),
        started.elapsed().as_secs()
    );
    ExitCode::SUCCESS
}
