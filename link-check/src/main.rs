//! ビルド済み public/ の全リンクを検査し、壊れているものをレポートする。
//!
//! 使い方:
//!     cargo run --release --manifest-path link-check/Cargo.toml -- \
//!         --root public --out report.md --stats stats.json
//!
//! --list を付けるとHTTPは送らず、検査対象の一覧だけを出す。
//!
//! 方針(コードから読み取れない判断):
//! - 相手サーバーに優しくするため、同一ドメインへのリクエストは必ず1本ずつ・間隔をあけて送る。
//!   ドメインが違えば並列で走るので、全体の所要時間は「最多件数のドメイン × 間隔」でほぼ決まる。
//! - **「リンク切れ」と呼ぶのは 404 / 410 / DNS解決不能だけ**にする。403・405・429・5xx・
//!   タイムアウトは「相手がbotを弾いた」のか「本当に消えた」のか区別がつかず、報告すると
//!   誤検知だらけになって誰も読まなくなるため、別枠の「要確認」に落とす。
//! - ただし消えたページを 404 にせずトップや上の階層へ転送するサイトも多い。200 で返ってきても
//!   転送先がトップ・上の階層なら「実質リンク切れ」としてリンク切れに数える。
//!   それ以外の場所へ転送されたものは、引っ越しかもしれないので別枠の「転送」に元と先を並べる。
//!   www の有無、末尾スラッシュ、index.html だけの違いは転送とみなさない。
//! - 同じ書き換え(例: open.spotify.com/ → open.spotify.com/intl-ja/)で転送されるものが多いと
//!   1件ずつ並べても読めないので、レポートでは書き換えのパターンごとにまとめる。
//! - http のリンクは、相手が https に対応したら貼り替えたい。http→https の転送に加えて、
//!   転送しないサイトも https で開けるか1回だけ試し、開ければ「https化」として知らせる。
//! - 接続エラーは並列実行中の一時的な詰まりでよく起きるので、最後に直列でもう一度確かめる。
//! - YouTube は削除済み動画でも watch ページが 200 を返すので oEmbed で生死を判定する。
//! - 除外したいドメイン・URLは link-check/ignore.txt に書く(理由もそこにコメントで残す)。
//!
//! 2026年11月までは Python/check_links.py が同じことをしていた(判定・レポートの見た目はそれと同じ)。

mod check;
mod collect;
mod internal;
mod pattern;
mod redirect;
mod report;
mod urlsplit;

use std::collections::BTreeMap;
use std::fs;
use std::path::PathBuf;
use std::process::ExitCode;
use std::time::Instant;

use clap::Parser;

use crate::check::{Options, check_all};
use crate::collect::{collect_links, is_ignored, load_ignore_rules};
use crate::internal::{load_redirects, resolve_internal};
use crate::report::{Counts, build_report, stats};
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
    #[arg(long, default_value = "link-check-report.md")]
    out: PathBuf,
    #[arg(long, default_value = "link-check-stats.json")]
    stats: PathBuf,
    /// Issue に貼る用のレポート。Issue 本文の文字数制限に収まるよう、一覧を必要なだけ削って出す
    #[arg(long)]
    issue_out: Option<PathBuf>,
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

// GitHub の Issue 本文は 65,536 文字まで。ワークフローが前置きの1行を足すので余裕を見る
const ISSUE_BODY_LIMIT: usize = 60_000;

/// 全件で収まればそのまま、収まらなければ一覧の件数を減らしながら Issue の制限に収める
fn issue_report(report: impl Fn(usize) -> String) -> String {
    for max_items in [usize::MAX, 300, 150, 80, 40, 20] {
        let text = report(max_items);
        if text.chars().count() <= ISSUE_BODY_LIMIT {
            return text;
        }
    }
    report(10)
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

    if args.list {
        for (path, pages) in &internal_broken {
            let pages: Vec<&str> = pages.iter().map(String::as_str).collect();
            println!("broken-internal\t{path}\t{}", pages.join(" "));
        }
        for url in &targets {
            println!("check\t{url}");
        }
        return ExitCode::SUCCESS;
    }

    let counts = Counts {
        checked: targets.len(),
        ignored,
        internal: internal.len(),
    };
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
    let elapsed = started.elapsed();

    let report = |max_items| {
        build_report(
            &args.base_url,
            &external,
            &internal_broken,
            &results,
            &counts,
            elapsed,
            max_items,
        )
    };
    let stats = stats(&results, internal_broken.len(), &counts, elapsed);
    let written = fs::write(&args.out, report(usize::MAX))
        .and_then(|()| {
            let json = serde_json::to_string_pretty(&stats).expect("Stats は必ずJSONにできる");
            fs::write(&args.stats, json)
        })
        .and_then(|()| match &args.issue_out {
            Some(path) => fs::write(path, issue_report(report)),
            None => Ok(()),
        });
    if let Err(e) = written {
        eprintln!("エラー: レポートを書き出せません: {e}");
        return ExitCode::from(2);
    }
    eprintln!(
        "{}",
        serde_json::to_string(&stats).expect("Stats は必ずJSONにできる")
    );
    ExitCode::SUCCESS
}
