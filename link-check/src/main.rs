//! ビルド済み public/ のリンクを検査する。Python/check_links.py の Rust 版(移植中)。
//!
//! 使い方:
//!     cargo run --release --manifest-path link-check/Cargo.toml -- --root public
//!
//! いまはネットワークを使わない部分(リンクの収集・除外・サイト内リンクの検査)まで。
//! 外部リンクへのHTTP検査とレポート出力はまだ Python 版を使う。
//! 判断の方針は check_links.py 冒頭のコメントを参照。

mod collect;
mod internal;
// 外部リンクのHTTP検査を移すまでは main から呼ばれない
#[allow(dead_code)]
mod redirect;
mod urlsplit;

use std::collections::BTreeMap;
use std::path::PathBuf;
use std::process::ExitCode;

use clap::Parser;

use crate::collect::{collect_links, is_ignored, load_ignore_rules};
use crate::internal::{load_redirects, resolve_internal};
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
}

fn main() -> ExitCode {
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

    let mut by_host: BTreeMap<String, Vec<&String>> = BTreeMap::new();
    for url in &targets {
        by_host
            .entry(urlsplit(url).hostname())
            .or_default()
            .push(url);
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
        println!("BROKEN-INTERNAL\t{path}\t{}", pages.join(" "));
    }
    for url in &targets {
        println!("CHECK\t{url}");
    }
    ExitCode::SUCCESS
}
