//! 転送(リダイレクト)された外部リンクの判定。check_links.py の classify_redirect と同じ。
//!
//! 消えたページを 404 にせずトップや上の階層へ転送するサイトが多いので、
//! 200 で返ってきても「どこに着いたか」を見てリンク切れかどうかを決める。

use std::sync::LazyLock;

use percent_encoding::percent_decode_str;
use regex::Regex;

use crate::urlsplit::urlsplit;

/// 1件のリンクの判定
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum Verdict {
    Ok,
    /// リンク切れ
    Broken,
    /// 要確認(botを弾かれた・一時障害など、切れているとは限らない)
    Warn,
    /// 別のページへ転送された(引っ越しかもしれない)
    Moved,
    /// http のリンクが https で開ける(貼り替え推奨)
    Https,
}

// 転送先の比較で同じページとみなす末尾 (/index.html など)
static INDEX_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"(?i)/index\.(?:html?|php)$").unwrap());
// /ja や /en-us は言語別のトップページ
static LANG_TOP_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"^/[a-z]{2}(?:-[a-z]{2})?$").unwrap());

/// 転送前後を比べるための (ホスト, パス)。http/https・www・末尾スラッシュ・index.html は無視
fn comparable(url: &str) -> (String, String) {
    let split = urlsplit(url);
    let hostname = split.hostname();
    let host = hostname
        .strip_prefix("www.")
        .unwrap_or(&hostname)
        .to_string();
    let path = percent_decode_str(&split.path).decode_utf8_lossy();
    let path = INDEX_RE.replace(&path, "");
    (host, path.trim_end_matches('/').to_string())
}

fn is_lang_top(path: &str) -> bool {
    LANG_TOP_RE.is_match(path)
}

/// 転送されていれば (判定, 詳細) を返す。実質同じページなら None
pub fn classify_redirect(url: &str, last: &str) -> Option<(Verdict, &'static str)> {
    let (src_host, src_path) = comparable(url);
    let (dst_host, dst_path) = comparable(last);

    if src_host == dst_host && src_path == dst_path {
        if urlsplit(url).scheme == "http" && urlsplit(last).scheme == "https" {
            return Some((Verdict::Https, "https へ転送"));
        }
        return None;
    }

    // 元がトップページ自体なら、どこへ転送されても「消えた」とは言えない
    if !src_path.is_empty() && !is_lang_top(&src_path) {
        if dst_path.is_empty() || is_lang_top(&dst_path) {
            let detail = if dst_host == src_host {
                "トップへ転送 (ページが消えた可能性大)"
            } else {
                "別サイトのトップへ転送 (ページが消えた可能性大)"
            };
            return Some((Verdict::Broken, detail));
        }
        if dst_host == src_host && src_path.starts_with(&format!("{dst_path}/")) {
            return Some((Verdict::Broken, "上の階層へ転送 (ページが消えた可能性大)"));
        }
    }
    Some((Verdict::Moved, "別のページへ転送"))
}

#[cfg(test)]
mod tests {
    use super::*;

    fn verdict(url: &str, last: &str) -> Option<Verdict> {
        classify_redirect(url, last).map(|(v, _)| v)
    }

    #[test]
    fn same_page_is_not_a_redirect() {
        assert_eq!(
            verdict("https://example.jp/a", "https://www.example.jp/a/"),
            None
        );
        assert_eq!(
            verdict("https://example.jp/a/", "https://example.jp/a/index.html"),
            None
        );
        assert_eq!(
            verdict(
                "https://example.jp/%E4%BD%B3%E6%9E%97",
                "https://example.jp/佳林"
            ),
            None
        );
    }

    #[test]
    fn http_to_https() {
        assert_eq!(
            verdict("http://example.jp/a", "https://example.jp/a"),
            Some(Verdict::Https)
        );
    }

    #[test]
    fn to_top_is_broken() {
        assert_eq!(
            classify_redirect("https://example.jp/news/123", "https://example.jp/"),
            Some((Verdict::Broken, "トップへ転送 (ページが消えた可能性大)"))
        );
        assert_eq!(
            classify_redirect("https://example.jp/news/123", "https://other.jp/ja"),
            Some((
                Verdict::Broken,
                "別サイトのトップへ転送 (ページが消えた可能性大)"
            ))
        );
    }

    #[test]
    fn to_parent_is_broken() {
        assert_eq!(
            verdict("https://example.jp/news/123", "https://example.jp/news/"),
            Some(Verdict::Broken)
        );
        // /new は /news の「上の階層」ではない
        assert_eq!(
            verdict("https://example.jp/news/123", "https://example.jp/new"),
            Some(Verdict::Moved)
        );
    }

    #[test]
    fn top_to_language_top_is_moved() {
        assert_eq!(
            verdict("https://example.jp/", "https://example.jp/ja"),
            Some(Verdict::Moved)
        );
        assert_eq!(
            verdict("https://example.jp/ja", "https://example.jp/"),
            Some(Verdict::Moved)
        );
    }
}
