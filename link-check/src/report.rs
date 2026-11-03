//! 検査結果を report.md(Issue と Actions のサマリーに貼る)と stats.json(件数)にする。

use std::collections::{BTreeMap, BTreeSet};
use std::time::Duration;

use chrono::{FixedOffset, Utc};
use serde::Serialize;

use crate::check::Outcome;
use crate::collect::LinkMap;
use crate::pattern::group_moved;
use crate::redirect::Verdict;

/// 要確認は件数が多くなりがちなので、max_items とは別にこの件数で打ち切る
const MAX_WARN_ITEMS: usize = 200;

/// レポートの見出しに載せる件数
pub struct Counts {
    pub checked: usize,
    pub ignored: usize,
    pub internal: usize,
}

/// stats.json の中身。ワークフローが broken_total と https を見てIssueを立てるか決める
#[derive(Serialize)]
pub struct Stats {
    pub broken_external: usize,
    pub broken_internal: usize,
    pub broken_total: usize,
    pub warn: usize,
    pub moved: usize,
    pub https: usize,
    pub checked: usize,
    pub ignored: usize,
    pub elapsed_sec: u64,
}

/// 1234567 → "1,234,567"
fn comma(n: usize) -> String {
    let digits = n.to_string();
    let mut out = String::new();
    for (i, c) in digits.chars().enumerate() {
        if i > 0 && (digits.len() - i).is_multiple_of(3) {
            out.push(',');
        }
        out.push(c);
    }
    out
}

/// 10件を超えたら末尾に … を付ける
fn ellipsis(len: usize) -> &'static str {
    if len > 10 { "…" } else { "" }
}

fn pick(results: &[(String, Outcome)], verdict: Verdict) -> Vec<&(String, Outcome)> {
    results
        .iter()
        .filter(|(_, o)| o.verdict == verdict)
        .collect()
}

pub fn stats(
    results: &[(String, Outcome)],
    broken_internal: usize,
    counts: &Counts,
    elapsed: Duration,
) -> Stats {
    let broken_external = pick(results, Verdict::Broken).len();
    Stats {
        broken_external,
        broken_internal,
        broken_total: broken_external + broken_internal,
        warn: pick(results, Verdict::Warn).len(),
        moved: pick(results, Verdict::Moved).len(),
        https: pick(results, Verdict::Https).len(),
        checked: counts.checked,
        ignored: counts.ignored,
        elapsed_sec: elapsed.as_secs_f64().round() as u64,
    }
}

pub fn build_report(
    base_url: &str,
    external: &LinkMap,
    internal_broken: &[(&String, &BTreeSet<String>)],
    results: &[(String, Outcome)],
    counts: &Counts,
    elapsed: Duration,
    max_items: usize,
) -> String {
    let jst = FixedOffset::east_opt(9 * 3600).unwrap();
    let now = Utc::now().with_timezone(&jst).format("%Y-%m-%d %H:%M JST");
    let secs = elapsed.as_secs_f64();
    // results は URL 順に並んでいるので、振り分けても URL 順のまま
    let broken = pick(results, Verdict::Broken);
    let warn = pick(results, Verdict::Warn);
    let moved = pick(results, Verdict::Moved);
    let upgrade = pick(results, Verdict::Https);
    let (patterns, moved_rest) = group_moved(&moved);

    // そのURLが載っているページへのリンク(最大10件)
    let sources = |url: &str| -> String {
        let pages: Vec<&String> = external
            .get(url)
            .map(|p| p.iter().collect())
            .unwrap_or_default();
        let links: Vec<String> = pages
            .iter()
            .take(10)
            .map(|p| format!("[{p}]({base_url}{p})"))
            .collect();
        format!("  - 掲載元: {}{}", links.join(", "), ellipsis(pages.len()))
    };

    let mut l: Vec<String> = Vec::new();
    l.push("## リンクチェック結果".into());
    l.push("".into());
    l.push(format!(
        "- チェック日時: **{now}**（所要 {:.0}分{:.0}秒）",
        (secs / 60.0).floor(),
        secs % 60.0
    ));
    l.push(format!(
        "- 検査した外部リンク: **{}** 件（除外 {} 件）",
        comma(counts.checked),
        comma(counts.ignored)
    ));
    l.push(format!(
        "- 検査した内部リンク: **{}** 件",
        comma(counts.internal)
    ));
    l.push(format!(
        "- 🔴 **リンク切れ {} 件**（外部 {} / 内部 {}）",
        comma(broken.len() + internal_broken.len()),
        comma(broken.len()),
        comma(internal_broken.len())
    ));
    if !upgrade.is_empty() {
        l.push(format!(
            "- 🔒 **https化 {} 件**（https に対応したので貼り替え推奨）",
            comma(upgrade.len())
        ));
    }
    if !moved.is_empty() {
        l.push(format!(
            "- 🟡 別のページへ転送 {} 件（引っ越しなら新しいURLに貼り替えると安心）",
            comma(moved.len())
        ));
        if !patterns.is_empty() {
            let grouped: usize = patterns.iter().map(|p| p.items.len()).sum();
            l.push(format!(
                "  - うち {} 件は {} 通りの書き換えでまとめて直せます",
                comma(grouped),
                patterns.len()
            ));
        }
    }
    if !warn.is_empty() {
        l.push(format!(
            "- ⚪ 要確認 {} 件（アクセス拒否・一時障害の可能性。切れているとは限らない）",
            comma(warn.len())
        ));
    }
    l.push("".into());

    if !internal_broken.is_empty() {
        l.push(format!(
            "### 🔴 サイト内リンク切れ（{}件）",
            internal_broken.len()
        ));
        l.push("".into());
        l.push("存在しないページを指しているリンク。ほぼ確実に貼り間違いです。".into());
        l.push("".into());
        for (path, pages) in internal_broken.iter().take(max_items) {
            let shown: Vec<String> = pages.iter().take(10).map(|p| format!("`{p}`")).collect();
            l.push(format!("- `{path}`"));
            l.push(format!(
                "  - 掲載元: {}{}",
                shown.join(", "),
                ellipsis(pages.len())
            ));
        }
        l.push("".into());
    }

    if !broken.is_empty() {
        l.push(format!("### 🔴 外部リンク切れ（{}件）", broken.len()));
        l.push("".into());
        l.push(
            "404 / 410 / ドメイン消滅と、トップや上の階層へ飛ばされるものを載せています。".into(),
        );
        l.push("".into());
        for (url, o) in broken.iter().take(max_items) {
            l.push(format!("- [{}] {url}", o.detail));
            if !o.last.is_empty() {
                l.push(format!("  - 転送先: {}", o.last));
            }
            l.push(sources(url));
        }
        if broken.len() > max_items {
            l.push("".into());
            l.push(format!(
                "…ほか {} 件（全件は Actions の成果物 report.md を参照）",
                broken.len() - max_items
            ));
        }
        l.push("".into());
    }

    if !upgrade.is_empty() {
        l.push(format!("### 🔒 https化（{}件）", upgrade.len()));
        l.push("".into());
        l.push(
            "http で貼ってあるリンクのうち、相手が https に対応したもの。URLを https に貼り替えてください。"
                .into(),
        );
        l.push("".into());
        for (url, o) in upgrade.iter().take(max_items) {
            l.push(format!("- [{}] {url}", o.detail));
            l.push(format!("  - 貼り替え先: {}", o.last));
            l.push(sources(url));
        }
        l.push("".into());
    }

    if !patterns.is_empty() {
        l.push(format!(
            "### 🟡 まとめて直せる転送（{}通り・{}件）",
            patterns.len(),
            patterns.iter().map(|p| p.items.len()).sum::<usize>()
        ));
        l.push("".into());
        l.push(
            "同じ書き換えで転送されているリンク。左を右に置き換えれば、まとめて転送されなくなります。"
                .into(),
        );
        l.push("".into());
        for p in &patterns {
            l.push(format!(
                "- **{}** {}件: `{}` → `{}`",
                p.host,
                comma(p.items.len()),
                p.from,
                p.to
            ));
            let (url, o) = p.items[0];
            l.push(format!("  - 例: {url} → {}", o.last));
            // どのページに多いか(直す場所の目安)
            let mut per_page: BTreeMap<&str, usize> = BTreeMap::new();
            for (url, _) in &p.items {
                for page in external.get(url).into_iter().flatten() {
                    *per_page.entry(page.as_str()).or_default() += 1;
                }
            }
            let mut per_page: Vec<_> = per_page.into_iter().collect();
            per_page.sort_by_key(|(_, n)| std::cmp::Reverse(*n));
            let shown: Vec<String> = per_page
                .iter()
                .take(10)
                .map(|(page, n)| format!("[{page}]({base_url}{page})（{n}件）"))
                .collect();
            l.push(format!(
                "  - 掲載元: {}{}",
                shown.join(", "),
                ellipsis(per_page.len())
            ));
        }
        l.push("".into());
    }

    if !moved_rest.is_empty() {
        l.push(format!(
            "<details><summary>🟡 別のページへ転送 {}件\
             （ページは開けるが、元と違う場所に着いた。引っ越しか、別の記事に差し替わったか）</summary>",
            moved_rest.len()
        ));
        l.push("".into());
        for (url, o) in moved_rest.iter().take(max_items) {
            l.push(format!("- {url}"));
            l.push(format!("  - 転送先: {}", o.last));
            l.push(sources(url));
        }
        if moved_rest.len() > max_items {
            l.push(format!(
                "- …ほか {} 件（全件は Actions の成果物 report.md を参照）",
                moved_rest.len() - max_items
            ));
        }
        l.push("".into());
        l.push("</details>".into());
        l.push("".into());
    }

    if !warn.is_empty() {
        l.push(format!(
            "<details><summary>⚪ 要確認 {}件\
             （botを弾かれた・一時的に繋がらなかった。ブラウザでは普通に開けることが多い）</summary>",
            warn.len()
        ));
        l.push("".into());
        for (url, o) in warn.iter().take(MAX_WARN_ITEMS) {
            l.push(format!("- [{}] {url}", o.detail));
        }
        if warn.len() > MAX_WARN_ITEMS {
            l.push(format!("- …ほか {} 件", warn.len() - MAX_WARN_ITEMS));
        }
        l.push("".into());
        l.push("</details>".into());
        l.push("".into());
    }

    if broken.is_empty() && internal_broken.is_empty() {
        l.push("### ✅ リンク切れはありませんでした".into());
        l.push("".into());
    }

    l.push("---".into());
    l.push("".into());
    l.push(
        "除外設定は `link-check/ignore.txt`、実行するワークフローは \
         `.github/workflows/link-check.yml`（毎月1日 JST 12:00 / 手動実行可）。"
            .into(),
    );
    l.join("\n")
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn thousands_separator() {
        assert_eq!(comma(0), "0");
        assert_eq!(comma(999), "999");
        assert_eq!(comma(1000), "1,000");
        assert_eq!(comma(5347), "5,347");
        assert_eq!(comma(1234567), "1,234,567");
    }
}
