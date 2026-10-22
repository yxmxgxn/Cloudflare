//! Python の urllib.parse (urlsplit / urlunsplit / urljoin) と同じ分け方をする小さな実装。
//!
//! url クレートは WHATWG 準拠でURLを正規化する(末尾に / を足す、エスケープを直す等)ため、
//! 集めたURLの文字列がPython版と変わってしまう。レポートの突き合わせができるよう、
//! ここではPythonと同じく「切り分けるだけで書き換えない」。

/// urlsplit の結果。scheme は小文字化済み
#[derive(Debug, Clone, Default, PartialEq, Eq)]
pub struct SplitUrl {
    pub scheme: String,
    pub netloc: String,
    pub path: String,
    pub query: String,
    pub fragment: String,
}

fn is_scheme_char(c: char) -> bool {
    c.is_ascii_alphanumeric() || matches!(c, '+' | '-' | '.')
}

pub fn urlsplit(url: &str) -> SplitUrl {
    // Python と同じく先頭の制御文字・空白を落とし、タブと改行は途中のものも消す
    let cleaned: String = url
        .trim_start_matches(|c: char| c <= ' ')
        .chars()
        .filter(|c| !matches!(c, '\t' | '\r' | '\n'))
        .collect();
    let mut rest = cleaned.as_str();
    let mut out = SplitUrl::default();

    if let Some(i) = rest.find(':') {
        let head = &rest[..i];
        let first_ok = head.chars().next().is_some_and(|c| c.is_ascii_alphabetic());
        if first_ok && head.chars().all(is_scheme_char) {
            out.scheme = head.to_ascii_lowercase();
            rest = &rest[i + 1..];
        }
    }
    if let Some(after) = rest.strip_prefix("//") {
        let end = after.find(['/', '?', '#']).unwrap_or(after.len());
        out.netloc = after[..end].to_string();
        rest = &after[end..];
    }
    if let Some((r, frag)) = rest.split_once('#') {
        out.fragment = frag.to_string();
        rest = r;
    }
    if let Some((r, query)) = rest.split_once('?') {
        out.query = query.to_string();
        rest = r;
    }
    out.path = rest.to_string();
    out
}

impl SplitUrl {
    /// Python の SplitResult.hostname。ユーザー情報とポートを除いて小文字化する
    pub fn hostname(&self) -> String {
        let hostinfo = self
            .netloc
            .rsplit_once('@')
            .map_or(self.netloc.as_str(), |(_, h)| h);
        let host = if let Some(v6) = hostinfo.strip_prefix('[') {
            v6.split(']').next().unwrap_or("")
        } else {
            hostinfo.split(':').next().unwrap_or("")
        };
        host.to_lowercase()
    }

    /// urlunsplit。Python と同じく空の query / fragment は ? や # ごと落とす
    pub fn unsplit(&self) -> String {
        let mut url = self.path.clone();
        let uses_netloc = matches!(
            self.scheme.as_str(),
            "http" | "https" | "ftp" | "ws" | "wss"
        );
        if !self.netloc.is_empty() || (uses_netloc && !url.starts_with("//")) {
            if !url.is_empty() && !url.starts_with('/') {
                url.insert(0, '/');
            }
            url = format!("//{}{}", self.netloc, url);
        }
        if !self.scheme.is_empty() {
            url = format!("{}:{}", self.scheme, url);
        }
        if !self.query.is_empty() {
            url = format!("{url}?{}", self.query);
        }
        if !self.fragment.is_empty() {
            url = format!("{url}#{}", self.fragment);
        }
        url
    }
}

/// urljoin(base, path) のうち、どちらもスキームもホストも持たない「パス同士」の場合だけ。
/// ルートより上に .. で出ると先頭の / まで消えるPythonの癖もそのまま再現する
pub fn join_path(base: &str, path: &str) -> String {
    if path.is_empty() {
        return base.to_string();
    }
    let mut segments: Vec<&str> = if path.starts_with('/') {
        path.split('/').collect()
    } else {
        let mut base_parts: Vec<&str> = base.split('/').collect();
        if base_parts.last() != Some(&"") {
            base_parts.pop();
        }
        base_parts.extend(path.split('/'));
        base_parts
    };
    // 先頭と末尾以外の空セグメント(// の重なり)は詰める
    if segments.len() > 2 {
        let last = segments.pop().unwrap();
        let mut kept = vec![segments[0]];
        kept.extend(segments[1..].iter().filter(|s| !s.is_empty()));
        kept.push(last);
        segments = kept;
    }

    let mut resolved: Vec<&str> = Vec::new();
    for seg in &segments {
        match *seg {
            ".." => {
                resolved.pop();
            }
            "." => {}
            s => resolved.push(s),
        }
    }
    if matches!(segments.last(), Some(&".") | Some(&"..")) {
        resolved.push("");
    }
    let joined = resolved.join("/");
    if joined.is_empty() {
        "/".to_string()
    } else {
        joined
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn split_and_unsplit() {
        let s = urlsplit("HTTPS://User@Example.COM:8080/a/b?x=1#top");
        assert_eq!(s.scheme, "https");
        assert_eq!(s.netloc, "User@Example.COM:8080");
        assert_eq!(s.hostname(), "example.com");
        assert_eq!(s.path, "/a/b");
        assert_eq!(s.query, "x=1");
        assert_eq!(s.fragment, "top");
        let no_frag = SplitUrl {
            fragment: String::new(),
            ..s
        };
        assert_eq!(no_frag.unsplit(), "https://User@Example.COM:8080/a/b?x=1");
    }

    #[test]
    fn empty_query_is_dropped() {
        assert_eq!(urlsplit("https://a.jp/x?#y").unsplit(), "https://a.jp/x#y");
    }

    #[test]
    fn relative_has_no_scheme() {
        let s = urlsplit("img/a.png?v=2");
        assert_eq!(s.scheme, "");
        assert_eq!(s.path, "img/a.png");
    }

    #[test]
    fn join_like_python() {
        assert_eq!(join_path("/YouTube/karin-rec", "other"), "/YouTube/other");
        assert_eq!(
            join_path("/YouTube/karin-rec", "../img/a.png"),
            "/img/a.png"
        );
        assert_eq!(join_path("/a/b", "./"), "/a/");
        assert_eq!(join_path("/a/b", ".."), "/");
        assert_eq!(join_path("/", "../x"), "x");
        assert_eq!(join_path("/a/b", ""), "/a/b");
    }
}
