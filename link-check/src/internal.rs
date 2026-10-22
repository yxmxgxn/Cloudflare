//! サイト内リンクの検査。HTTPは使わず、public/ にファイルがあるかで判定する。

use std::collections::{HashMap, HashSet};
use std::fs;
use std::path::Path;

/// public/_redirects を (元パス → 転送先) にする
pub fn load_redirects(root: &Path) -> HashMap<String, String> {
    let Ok(text) = fs::read_to_string(root.join("_redirects")) else {
        return HashMap::new();
    };
    text.lines()
        .filter_map(|line| {
            let mut parts = line.split_whitespace();
            Some((parts.next()?.to_string(), parts.next()?.to_string()))
        })
        .collect()
}

/// public/ 配下に実体があるか(またはリダイレクト先に実体があるか)
pub fn resolve_internal(path: &str, root: &Path, redirects: &HashMap<String, String>) -> bool {
    let mut path = path.to_string();
    let mut seen = HashSet::new();
    loop {
        let clean = path.split('#').next().unwrap_or("");
        let clean = clean.split('?').next().unwrap_or("");
        let clean = if clean.starts_with('/') {
            clean.to_string()
        } else {
            format!("/{clean}")
        };
        if matches!(clean.as_str(), "/" | "/index" | "/index.html") {
            return root.join("index.html").exists();
        }
        let rel = clean.trim_start_matches('/');
        let candidates = [
            root.join(rel),
            root.join(format!("{rel}.html")),
            root.join(rel).join("index.html"),
        ];
        if candidates.iter().any(|c| c.is_file()) {
            return true;
        }
        let Some(target) = redirects.get(&clean) else {
            return false;
        };
        if !seen.insert(clean) {
            return false;
        }
        path = target.clone();
    }
}
