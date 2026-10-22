//! ビルド済みHTMLからリンクを集める。check_links.py の「収集」と同じ判断をする。

use std::collections::{BTreeMap, BTreeSet};
use std::fs;
use std::io;
use std::path::{Path, PathBuf};
use std::sync::LazyLock;

use regex::Regex;

use crate::urlsplit::{join_path, urlsplit};

/// URL(またはパス) → それが載っているページの集合
pub type LinkMap = BTreeMap<String, BTreeSet<String>>;

const SKIP_SCHEMES: [&str; 6] = ["mailto:", "tel:", "javascript:", "data:", "sms:", "#"];

// 接続先を温めるだけの指定。リンクではないので追いかけない
const SKIP_REL: [&str; 5] = [
    "preconnect",
    "dns-prefetch",
    "prefetch",
    "prerender",
    "modulepreload",
];

// script/style の中身は「JSが実行時に組み立てる文字列」であって、そのままではURLにならない。
// regex クレートは後方参照(\1)を持たないので、閉じタグごとに書き分ける
static SCRIPT_RE: LazyLock<Regex> = LazyLock::new(|| {
    Regex::new(r"(?is)<script\b[^>]*>.*?</script\s*>|<style\b[^>]*>.*?</style\s*>").unwrap()
});
// ${d.article} のようなテンプレート変数が残っているものは検査できない
static PLACEHOLDER_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"\$\{|\{\{|<%|%s\b|\$\(").unwrap());
static TAG_RE: LazyLock<Regex> = LazyLock::new(|| Regex::new(r"<(\w+)\b([^>]*)>").unwrap());
static HREF_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r#"(?i)(?:href|src)\s*=\s*["']([^"']+)["']"#).unwrap());
static REL_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r#"(?i)\brel\s*=\s*["']([^"']*)["']"#).unwrap());

// ---------------------------------------------------------------- 除外リスト
pub fn load_ignore_rules(file: &Path) -> io::Result<Vec<String>> {
    let text = match fs::read_to_string(file) {
        Ok(t) => t,
        Err(e) if e.kind() == io::ErrorKind::NotFound => return Ok(Vec::new()),
        Err(e) => return Err(e),
    };
    Ok(text
        .lines()
        .map(|line| line.split('#').next().unwrap_or("").trim())
        .filter(|line| !line.is_empty())
        .map(String::from)
        .collect())
}

pub fn is_ignored(url: &str, rules: &[String]) -> bool {
    let host = urlsplit(url).hostname();
    rules.iter().any(|rule| {
        if rule.contains('/') {
            // パスを含む指定は前方一致
            url.starts_with(rule.as_str())
        } else {
            // ドメイン指定はサブドメインも含めて一致
            host == *rule || host.ends_with(&format!(".{rule}"))
        }
    })
}

// ---------------------------------------------------------------- 収集
/// public/YouTube/karin-rec.html → /YouTube/karin-rec
fn page_path(root: &Path, file: &Path) -> String {
    let rel = file.strip_prefix(root).unwrap_or(file);
    let rel = rel
        .components()
        .map(|c| c.as_os_str().to_string_lossy())
        .collect::<Vec<_>>()
        .join("/");
    if rel == "index.html" {
        return "/".to_string();
    }
    format!("/{}", rel.strip_suffix(".html").unwrap_or(&rel))
}

/// HTMLから追いかけるべきURLを取り出す。preconnect等の非リンクは飛ばす
fn link_urls(text: &str) -> Vec<&str> {
    let mut urls = Vec::new();
    for tag in TAG_RE.captures_iter(text) {
        let attrs = tag.get(2).map_or("", |m| m.as_str());
        if tag[1].eq_ignore_ascii_case("link")
            && let Some(rel) = REL_RE.captures(attrs)
            && rel[1]
                .to_lowercase()
                .split_whitespace()
                .any(|r| SKIP_REL.contains(&r))
        {
            continue;
        }
        urls.extend(
            HREF_RE
                .captures_iter(attrs)
                .map(|c| c.get(1).unwrap().as_str()),
        );
    }
    urls
}

fn html_files(dir: &Path, out: &mut Vec<PathBuf>) -> io::Result<()> {
    for entry in fs::read_dir(dir)? {
        let path = entry?.path();
        if path.is_dir() {
            html_files(&path, out)?;
        } else if path.extension().is_some_and(|e| e == "html") {
            out.push(path);
        }
    }
    Ok(())
}

/// (外部URL → 出現ページ, 内部パス → 出現ページ) を返す
pub fn collect_links(root: &Path, base_url: &str) -> io::Result<(LinkMap, LinkMap)> {
    let mut external = LinkMap::new();
    let mut internal = LinkMap::new();
    let base_host = urlsplit(base_url).hostname();

    let mut files = Vec::new();
    html_files(root, &mut files)?;
    files.sort();

    for file in &files {
        let page = page_path(root, file);
        let raw = fs::read(file)?;
        let text = String::from_utf8_lossy(&raw);
        let text = SCRIPT_RE.replace_all(&text, " ");

        for raw_url in link_urls(&text) {
            let mut url = htmlize::unescape(raw_url.trim()).into_owned();
            let lower = url.to_lowercase();
            if url.is_empty() || SKIP_SCHEMES.iter().any(|s| lower.starts_with(s)) {
                continue;
            }
            if PLACEHOLDER_RE.is_match(&url) {
                continue;
            }
            if url.starts_with("//") {
                url.insert_str(0, "https:");
            }
            let mut split = urlsplit(&url);
            if split.scheme == "http" || split.scheme == "https" {
                if !base_host.is_empty() && split.hostname() == base_host {
                    let path = if split.path.is_empty() {
                        "/".to_string()
                    } else {
                        split.path
                    };
                    internal.entry(path).or_default().insert(page.clone());
                } else {
                    // フラグメントだけ違うものは同じURLとして1回で済ませる
                    split.fragment.clear();
                    external
                        .entry(split.unsplit())
                        .or_default()
                        .insert(page.clone());
                }
            } else if split.scheme.is_empty() {
                let path = if url.starts_with('/') {
                    split.path
                } else {
                    join_path(&page, &split.path)
                };
                if !path.is_empty() {
                    internal.entry(path).or_default().insert(page.clone());
                }
            }
        }
    }
    Ok((external, internal))
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn ignore_rules() {
        let rules = vec!["x.com".to_string(), "https://example.jp/old/".to_string()];
        assert!(is_ignored("https://x.com/karin", &rules));
        assert!(is_ignored("https://mobile.x.com/karin", &rules));
        assert!(!is_ignored("https://box.com/", &rules));
        assert!(is_ignored("https://example.jp/old/page", &rules));
        assert!(!is_ignored("https://example.jp/new/page", &rules));
    }

    #[test]
    fn skips_preconnect() {
        let html = r#"<link rel="preconnect" href="https://fonts.gstatic.com">
            <link rel="stylesheet" href="/css/a.css"><a class="x" href='/b'>b</a>"#;
        assert_eq!(link_urls(html), vec!["/css/a.css", "/b"]);
    }

    #[test]
    fn page_paths() {
        let root = Path::new("public");
        assert_eq!(page_path(root, Path::new("public/index.html")), "/");
        assert_eq!(
            page_path(root, Path::new("public/YouTube/karin-rec.html")),
            "/YouTube/karin-rec"
        );
    }
}
