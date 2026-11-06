//! 「別のページへ転送」を、同じ書き換えのパターンごとにまとめる。
//!
//! 例えば Spotify は open.spotify.com/track/ID を open.spotify.com/intl-ja/track/ID へ転送するので、
//! 何百曲あっても直し方は「open.spotify.com/ を open.spotify.com/intl-ja/ に置き換える」の1つで済む。
//! 1件ずつ並べると他の転送が埋もれて読めないため、元URLと転送先の違う部分を取り出し、
//! (ホスト, 消えた部分, 足された部分) が同じものを1つのパターンにする。

use std::collections::BTreeMap;

use crate::check::Outcome;
use crate::urlsplit::urlsplit;

/// これ以上の件数が同じ書き換えなら、パターンとしてまとめる
pub const MIN_GROUP: usize = 3;

// 書き換えの境目はURLの区切りにそろえる(IDの途中で切れると、IDごとに別パターンになってしまう)
const DELIMS: &[u8] = b"/?&=#";

/// 検査結果の1件(URL, 判定)
type Item<'a> = &'a (String, Outcome);

/// まとめる単位: (ホスト, 消えた部分, 足された部分)
type Rewrite = (String, String, String);

/// 同じ書き換えで転送されるリンクのまとまり
pub struct Pattern<'a> {
    pub host: String,
    /// 置き換える前と後(前後の変わらない部分を少し含めて、そのまま検索・置換に使える形にしてある)
    pub from: String,
    pub to: String,
    pub items: Vec<Item<'a>>,
}

/// i が区切りの位置か(先頭・末尾、または前後のどちらかが区切り文字)
fn is_boundary(s: &[u8], i: usize) -> bool {
    i == 0 || i == s.len() || DELIMS.contains(&s[i - 1]) || DELIMS.contains(&s[i])
}

/// 元URLと転送先の違う部分を区切りにそろえて取り出す。(前の共通部分の長さ, 後ろの共通部分の長さ)
fn diff_span(src: &str, dst: &str) -> (usize, usize) {
    let (s, d) = (src.as_bytes(), dst.as_bytes());
    let mut pre = s.iter().zip(d).take_while(|(a, b)| a == b).count();
    while !(is_boundary(s, pre) && is_boundary(d, pre)) {
        pre -= 1;
    }
    // 後ろの共通部分は、前の共通部分と重ならない範囲で取る
    let max_suf = (s.len() - pre).min(d.len() - pre);
    let mut suf = s
        .iter()
        .rev()
        .zip(d.iter().rev())
        .take(max_suf)
        .take_while(|(a, b)| a == b)
        .count();
    while !(is_boundary(s, s.len() - suf) && is_boundary(d, d.len() - suf)) {
        suf -= 1;
    }
    (pre, suf)
}

/// 全員に共通する末尾を、区切りにそろえて返す
fn common_suffix<'s>(texts: &[&'s str]) -> &'s str {
    let first = texts[0].as_bytes();
    let mut n = texts
        .iter()
        .map(|t| {
            first
                .iter()
                .rev()
                .zip(t.as_bytes().iter().rev())
                .take_while(|(a, b)| a == b)
                .count()
        })
        .min()
        .unwrap_or(0);
    while n > 0 && !is_boundary(first, first.len() - n) {
        n -= 1;
    }
    &texts[0][texts[0].len() - n..]
}

/// 全員に共通する先頭を、区切りにそろえて返す
fn common_prefix<'s>(texts: &[&'s str]) -> &'s str {
    let first = texts[0].as_bytes();
    let mut n = texts
        .iter()
        .map(|t| {
            first
                .iter()
                .zip(t.as_bytes())
                .take_while(|(a, b)| a == b)
                .count()
        })
        .min()
        .unwrap_or(0);
    while n > 0 && !is_boundary(first, n) {
        n -= 1;
    }
    &texts[0][..n]
}

/// 転送をパターンごとにまとめる。MIN_GROUP 件に満たないものは個別の転送として返す。
/// パターンは件数の多い順、個別の転送は元の並び(URL順)のまま
pub fn group_moved<'a>(moved: &[Item<'a>]) -> (Vec<Pattern<'a>>, Vec<Item<'a>>) {
    // 書き換え → [(1件, 前の共通部分の長さ, 後ろの共通部分の長さ)]
    let mut groups: BTreeMap<Rewrite, Vec<(Item<'a>, usize, usize)>> = BTreeMap::new();
    for &item in moved {
        let (src, dst) = (item.0.as_str(), item.1.last.as_str());
        let (pre, suf) = diff_span(src, dst);
        let key = (
            urlsplit(src).hostname(),
            src[pre..src.len() - suf].to_string(),
            dst[pre..dst.len() - suf].to_string(),
        );
        groups.entry(key).or_default().push((item, pre, suf));
    }

    let mut patterns = Vec::new();
    let mut rest = Vec::new();
    for ((host, removed, added), members) in groups {
        if members.len() < MIN_GROUP {
            rest.extend(members.into_iter().map(|(item, _, _)| item));
            continue;
        }
        // 置換の前後に、全員に共通する変わらない部分を添える(スキームは長くなるだけなので外す)
        let heads: Vec<&str> = members.iter().map(|(it, pre, _)| &it.0[..*pre]).collect();
        let tails: Vec<&str> = members
            .iter()
            .map(|(it, _, suf)| &it.0[it.0.len() - suf..])
            .collect();
        let head = common_suffix(&heads);
        let head = head.split_once("://").map_or(head, |(_, h)| h);
        let tail = common_prefix(&tails);
        patterns.push(Pattern {
            host,
            from: format!("{head}{removed}{tail}"),
            to: format!("{head}{added}{tail}"),
            items: members.into_iter().map(|(item, _, _)| item).collect(),
        });
    }
    patterns.sort_by(|a, b| {
        b.items
            .len()
            .cmp(&a.items.len())
            .then_with(|| a.host.cmp(&b.host))
    });
    rest.sort_by(|a, b| a.0.cmp(&b.0));
    (patterns, rest)
}

#[cfg(test)]
mod tests {
    use super::*;
    use crate::check::Kind;
    use crate::redirect::Verdict;

    fn moved(src: &str, dst: &str) -> (String, Outcome) {
        let outcome = Outcome {
            verdict: Verdict::Moved,
            detail: "別のページへ転送".into(),
            kind: Kind::Redirect,
            last: dst.into(),
        };
        (src.into(), outcome)
    }

    fn rewrite(src: &str, dst: &str) -> (String, String) {
        let (pre, suf) = diff_span(src, dst);
        (
            src[pre..src.len() - suf].into(),
            dst[pre..dst.len() - suf].into(),
        )
    }

    #[test]
    fn inserted_segment() {
        let r = rewrite(
            "https://open.spotify.com/track/5x3yXeqBRB7oczRiu6eKhY",
            "https://open.spotify.com/intl-ja/track/5x3yXeqBRB7oczRiu6eKhY",
        );
        assert_eq!(r, ("".into(), "intl-ja/".into()));
        // IDの先頭が足された部分と同じ文字でも、区切りにそろえるので同じ書き換えになる
        let r = rewrite(
            "https://open.spotify.com/track/intlABC",
            "https://open.spotify.com/intl-ja/track/intlABC",
        );
        assert_eq!(r, ("".into(), "intl-ja/".into()));
    }

    #[test]
    fn changed_host_and_segment() {
        assert_eq!(
            rewrite(
                "http://old.example.jp/news/123",
                "https://www.example.com/news/123"
            ),
            (
                "http://old.example.jp".into(),
                "https://www.example.com".into()
            )
        );
        assert_eq!(
            rewrite("https://example.jp/a/1?x=2", "https://example.jp/b/1?x=2"),
            ("a".into(), "b".into())
        );
    }

    #[test]
    fn groups_same_rewrite_and_keeps_others() {
        let items = [
            moved(
                "https://open.spotify.com/track/AAA",
                "https://open.spotify.com/intl-ja/track/AAA",
            ),
            moved(
                "https://open.spotify.com/track/BBB",
                "https://open.spotify.com/intl-ja/track/BBB",
            ),
            moved(
                "https://open.spotify.com/album/CCC",
                "https://open.spotify.com/intl-ja/album/CCC",
            ),
            moved(
                "https://maps.app.goo.gl/x1",
                "https://www.google.co.jp/maps/place/1",
            ),
            moved(
                "https://maps.app.goo.gl/x2",
                "https://www.google.co.jp/maps/place/2",
            ),
        ];
        let refs: Vec<&(String, Outcome)> = items.iter().collect();
        let (patterns, rest) = group_moved(&refs);
        assert_eq!(patterns.len(), 1);
        assert_eq!(patterns[0].host, "open.spotify.com");
        assert_eq!(patterns[0].from, "open.spotify.com/");
        assert_eq!(patterns[0].to, "open.spotify.com/intl-ja/");
        assert_eq!(patterns[0].items.len(), 3);
        assert_eq!(rest.len(), 2);
    }

    #[test]
    fn replacement_keeps_shared_context() {
        let items: Vec<_> = ["AAA", "BBB", "CCC"]
            .iter()
            .map(|id| {
                moved(
                    &format!("https://open.spotify.com/track/{id}"),
                    &format!("https://open.spotify.com/intl-ja/track/{id}"),
                )
            })
            .collect();
        let refs: Vec<&(String, Outcome)> = items.iter().collect();
        let (patterns, _) = group_moved(&refs);
        assert_eq!(patterns[0].from, "open.spotify.com/track/");
        assert_eq!(patterns[0].to, "open.spotify.com/intl-ja/track/");
    }
}
