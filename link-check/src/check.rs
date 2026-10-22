//! 外部リンクのHTTP検査。check_links.py の check_url 以下と同じ判断をする。
//!
//! 同一ドメインへは1本ずつ・間隔をあけて送り、ドメインが違えば並列に走らせる。
//! 何を「リンク切れ」と呼ぶかの方針は check_links.py 冒頭のコメントを参照。

use std::collections::BTreeMap;
use std::error::Error as _;
use std::sync::atomic::{AtomicUsize, Ordering};
use std::sync::{Arc, LazyLock};
use std::time::Duration;

use percent_encoding::{AsciiSet, NON_ALPHANUMERIC, utf8_percent_encode};
use regex::Regex;
use reqwest::header::{ACCEPT, ACCEPT_LANGUAGE, HeaderMap, HeaderValue};
use reqwest::{Client, Response, Url, redirect};
use tokio::sync::Semaphore;
use tokio::task::JoinSet;
use tokio::time::sleep;

use crate::redirect::{Verdict, classify_redirect, comparable};
use crate::urlsplit::urlsplit;

const USER_AGENT: &str =
    "Mozilla/5.0 (compatible; karin-archive-linkcheck/1.0; +https://karin-archive.com/)";

// 同一ドメインへの最小リクエスト間隔(秒)。件数が多い相手ほど長めにする
const DEFAULT_DELAY: f64 = 0.5;
const DOMAIN_DELAY: [(&str, f64); 5] = [
    ("www.nicovideo.jp", 0.8),
    ("nicovideo.jp", 0.8),
    ("www.youtube.com", 0.3),
    ("music.youtube.com", 0.3),
    ("i.ytimg.com", 0.2),
];

// (接続, 読み込み)
const TIMEOUT: (u64, u64) = (10, 20);
const RECHECK_TIMEOUT: (u64, u64) = (20, 40); // 再検査は気長に待つ
const RETRY_WAIT: Duration = Duration::from_secs(3);
const RECHECK_INTERVAL: Duration = Duration::from_millis(1500);

// ドメインそのものが消えたと判断してよいエラー
const DNS_HINTS: [&str; 6] = [
    "NameResolutionError",
    "Name or service not known",
    "nodename nor servname",
    "getaddrinfo failed",
    "No address associated",
    "Temporary failure in name resolution",
];

static YT_ID_RE: LazyLock<Regex> = LazyLock::new(|| {
    Regex::new(
        r#"(?:youtube\.com/watch\?(?:[^"']*&)?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([A-Za-z0-9_-]{11})"#,
    )
    .unwrap()
});

// Python の quote(s, safe="") と同じく、英数字と -._~ 以外は全部エスケープする
const QUOTE_ALL: &AsciiSet = &NON_ALPHANUMERIC
    .remove(b'-')
    .remove(b'.')
    .remove(b'_')
    .remove(b'~');

/// 判定の出どころ。network は接続そのものに失敗したもので、最後に再検査の対象になる
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum Kind {
    Http,
    Network,
    Redirect,
    Probe,
}

/// 1件の検査結果。last は転送先・貼り替え先があるときだけ入る
#[derive(Debug, Clone)]
pub struct Outcome {
    pub verdict: Verdict,
    pub detail: String,
    pub kind: Kind,
    pub last: String,
}

impl Outcome {
    fn new(verdict: Verdict, detail: impl Into<String>, kind: Kind) -> Self {
        Outcome {
            verdict,
            detail: detail.into(),
            kind,
            last: String::new(),
        }
    }
}

// ---------------------------------------------------------------- 判定
/// HTTPステータスを振り分ける
fn classify_status(code: u16) -> Outcome {
    let (verdict, detail) = match code {
        0..=399 => (Verdict::Ok, code.to_string()),
        404 | 410 => (Verdict::Broken, format!("{code} (ページが存在しない)")),
        401 | 403 | 405 | 406 | 429 | 999 => {
            (Verdict::Warn, format!("{code} (アクセス拒否の可能性)"))
        }
        500.. => (
            Verdict::Warn,
            format!("{code} (サーバー側の一時障害の可能性)"),
        ),
        _ => (Verdict::Warn, code.to_string()),
    };
    Outcome::new(verdict, detail, Kind::Http)
}

/// 接続エラーを振り分ける。詳細はレポートの見た目を揃えるため Python 版の例外名に寄せる
fn classify_error(e: &reqwest::Error) -> Outcome {
    // reqwest のエラーは「送信失敗 ← 接続失敗 ← DNS失敗」のように原因が連なっているので、全部つなげて見る
    let mut chain = e.to_string();
    let mut source = e.source();
    while let Some(s) = source {
        chain.push_str(" | ");
        chain.push_str(&s.to_string());
        source = s.source();
    }
    if DNS_HINTS.iter().any(|h| chain.contains(h)) {
        return Outcome::new(
            Verdict::Broken,
            "DNS解決できず (ドメイン消滅の可能性)",
            Kind::Network,
        );
    }
    let lower = chain.to_lowercase();
    let name = if e.is_timeout() && e.is_connect() {
        "ConnectTimeout"
    } else if e.is_timeout() {
        "ReadTimeout"
    } else if e.is_redirect() {
        "TooManyRedirects"
    } else if lower.contains("certificate") || lower.contains("tls") {
        "SSLError"
    } else if e.is_connect() {
        "ConnectionError"
    } else if e.is_builder() {
        "InvalidURL"
    } else {
        "RequestException"
    };
    Outcome::new(Verdict::Warn, name, Kind::Network)
}

// ---------------------------------------------------------------- 1件の検査
#[derive(Clone)]
pub struct Checker {
    client: Client,
}

impl Checker {
    pub fn new(timeout: (u64, u64)) -> reqwest::Result<Self> {
        let mut headers = HeaderMap::new();
        headers.insert(
            ACCEPT,
            HeaderValue::from_static("text/html,application/xhtml+xml,image/*,*/*;q=0.8"),
        );
        headers.insert(ACCEPT_LANGUAGE, HeaderValue::from_static("ja,en;q=0.8"));
        let client = Client::builder()
            .user_agent(USER_AGENT)
            .default_headers(headers)
            .connect_timeout(Duration::from_secs(timeout.0))
            .read_timeout(Duration::from_secs(timeout.1))
            .redirect(redirect::Policy::limited(30)) // requests の既定に合わせる
            .build()?;
        Ok(Checker { client })
    }

    /// HEAD を送り、4xx/5xx なら GET で確かめ直す(HEAD を受け付けないサーバーが多いため)。
    /// GET の本文は読まずに捨てる
    async fn fetch(&self, url: &str) -> reqwest::Result<Response> {
        let res = self.client.head(url).send().await?;
        if res.status().as_u16() >= 400 {
            return self.client.get(url).send().await;
        }
        Ok(res)
    }

    /// oEmbed で動画の生死を判定する。watch ページは削除済みでも 200 を返すため
    async fn check_youtube(&self, video_id: &str) -> Outcome {
        let target = format!("https://www.youtube.com/watch?v={video_id}");
        let url = format!(
            "https://www.youtube.com/oembed?url={}&format=json",
            utf8_percent_encode(&target, QUOTE_ALL)
        );
        let res = match self.client.get(&url).send().await {
            Ok(r) => r,
            Err(e) => return classify_error(&e),
        };
        match res.status().as_u16() {
            // 埋め込み禁止設定なだけで動画自体は存在する
            401 => Outcome::new(Verdict::Ok, "401 (埋め込み不可・動画は存在)", Kind::Http),
            404 => Outcome::new(Verdict::Broken, "404 (削除または非公開)", Kind::Http),
            code => classify_status(code),
        }
    }

    pub async fn check_url(&self, url: &str) -> Outcome {
        if let Some(caps) = YT_ID_RE.captures(url)
            && !url.contains("youtube.com/oembed")
        {
            return self.check_youtube(&caps[1]).await;
        }

        for attempt in 1..=2 {
            let res = match self.fetch(url).await {
                Ok(r) => r,
                Err(_) if attempt == 1 => {
                    sleep(RETRY_WAIT).await;
                    continue;
                }
                Err(e) => return classify_error(&e),
            };
            let code = res.status().as_u16();
            if code >= 500 && attempt == 1 {
                sleep(RETRY_WAIT).await;
                continue;
            }
            let outcome = classify_status(code);
            // 転送の先で 200 が返っても、行き着いた先が元のページとは限らない
            let redirected = Url::parse(url).map_or(true, |u| u != *res.url());
            if outcome.verdict == Verdict::Ok
                && redirected
                && let Some((verdict, detail)) = classify_redirect(url, res.url().as_str())
            {
                return Outcome {
                    last: res.url().to_string(),
                    ..Outcome::new(verdict, detail, Kind::Redirect)
                };
            }
            return outcome;
        }
        Outcome::new(Verdict::Warn, "unknown", Kind::Http)
    }

    /// http の URL が https でも同じページとして開けるなら、その https の URL を返す
    async fn https_version(&self, url: &str) -> Option<String> {
        let https_url = format!("https://{}", url.strip_prefix("http://")?);
        // 証明書エラーなどで繋がらない = まだ対応していない
        let res = self.fetch(&https_url).await.ok()?;
        // https 側が http へ送り返してくるサイトもあるので、着いた先まで確かめる
        let last = res.url().as_str();
        let same_page = urlsplit(last).scheme == "https" && comparable(last) == comparable(url);
        (res.status().as_u16() < 400 && same_page).then(|| last.to_string())
    }
}

// ---------------------------------------------------------------- 全体
/// 1ドメイン分を順番に(間隔をあけて)処理する
async fn check_domain(
    checker: Checker,
    host: String,
    urls: Vec<String>,
    delay_scale: f64,
    done: Arc<AtomicUsize>,
    total: usize,
) -> Vec<(String, Outcome)> {
    let base = DOMAIN_DELAY
        .iter()
        .find(|(h, _)| *h == host)
        .map_or(DEFAULT_DELAY, |(_, d)| *d);
    let delay = Duration::from_secs_f64(base * delay_scale);
    let mut results = Vec::with_capacity(urls.len());
    for (i, url) in urls.into_iter().enumerate() {
        if i > 0 {
            sleep(delay).await;
        }
        let mut outcome = checker.check_url(&url).await;
        if outcome.verdict == Verdict::Ok && url.starts_with("http://") {
            sleep(delay).await;
            if let Some(upgraded) = checker.https_version(&url).await {
                outcome = Outcome {
                    last: upgraded,
                    ..Outcome::new(Verdict::Https, "https でも開ける", Kind::Probe)
                };
            }
        }
        results.push((url, outcome));
        let n = done.fetch_add(1, Ordering::Relaxed) + 1;
        if n.is_multiple_of(100) {
            eprintln!("  ... {n}/{total}");
        }
    }
    results
}

/// 接続に失敗したものを直列で確かめ直す。
///
/// 並列で回している最中の一時的な詰まりや相手側のレート制限で ConnectTimeout に
/// なることがあり、そのまま報告するとブラウザでは普通に開ける誤検知になるため。
async fn recheck_network_failures(
    results: &mut [(String, Outcome)],
    limit: usize,
) -> reqwest::Result<()> {
    let targets: Vec<usize> = results
        .iter()
        .enumerate()
        .filter(|(_, (_, o))| o.kind == Kind::Network && o.verdict != Verdict::Ok)
        .map(|(i, _)| i)
        .collect();
    if targets.is_empty() {
        return Ok(());
    }
    eprintln!("接続失敗 {} 件を直列で再検査します", targets.len());
    let checker = Checker::new(RECHECK_TIMEOUT)?;
    for (n, &i) in targets.iter().take(limit).enumerate() {
        if n > 0 {
            sleep(RECHECK_INTERVAL).await;
        }
        results[i].1 = checker.check_url(&results[i].0).await;
    }
    Ok(())
}

pub struct Options {
    pub workers: usize,
    pub delay_scale: f64,
    pub recheck_limit: usize,
}

/// ドメインごとに分けて並列に検査し、URL順に並べた結果を返す
pub async fn check_all(
    by_host: BTreeMap<String, Vec<String>>,
    opts: &Options,
) -> reqwest::Result<Vec<(String, Outcome)>> {
    let checker = Checker::new(TIMEOUT)?;
    let total = by_host.values().map(Vec::len).sum();
    let done = Arc::new(AtomicUsize::new(0));
    // 同時に走らせるドメイン数を workers 本に抑える
    let slots = Arc::new(Semaphore::new(opts.workers.max(1)));

    // 件数の多いドメインから始めると、全体の所要時間が最短になる
    let mut hosts: Vec<_> = by_host.into_iter().collect();
    hosts.sort_by_key(|(_, urls)| std::cmp::Reverse(urls.len()));

    let mut tasks = JoinSet::new();
    for (host, urls) in hosts {
        let (checker, done, slots) = (checker.clone(), done.clone(), slots.clone());
        let delay_scale = opts.delay_scale;
        tasks.spawn(async move {
            let _slot = slots
                .acquire_owned()
                .await
                .expect("semaphore is never closed");
            check_domain(checker, host, urls, delay_scale, done, total).await
        });
    }
    let mut results = Vec::with_capacity(total);
    while let Some(joined) = tasks.join_next().await {
        results.extend(joined.expect("検査タスクが異常終了した"));
    }
    results.sort_by(|a, b| a.0.cmp(&b.0));

    recheck_network_failures(&mut results, opts.recheck_limit).await?;
    Ok(results)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn status_classes() {
        assert_eq!(classify_status(200).verdict, Verdict::Ok);
        assert_eq!(classify_status(404).verdict, Verdict::Broken);
        assert_eq!(classify_status(410).verdict, Verdict::Broken);
        assert_eq!(classify_status(403).detail, "403 (アクセス拒否の可能性)");
        assert_eq!(
            classify_status(503).detail,
            "503 (サーバー側の一時障害の可能性)"
        );
        assert_eq!(classify_status(418).detail, "418");
    }

    #[test]
    fn youtube_ids() {
        let id = |u: &str| YT_ID_RE.captures(u).map(|c| c[1].to_string());
        assert_eq!(
            id("https://www.youtube.com/watch?v=dQw4w9WgXcQ").as_deref(),
            Some("dQw4w9WgXcQ")
        );
        assert_eq!(
            id("https://www.youtube.com/watch?list=x&v=dQw4w9WgXcQ").as_deref(),
            Some("dQw4w9WgXcQ")
        );
        assert_eq!(
            id("https://youtu.be/dQw4w9WgXcQ?t=10").as_deref(),
            Some("dQw4w9WgXcQ")
        );
        assert_eq!(id("https://www.youtube.com/@karin"), None);
    }

    #[test]
    fn oembed_quote_matches_python() {
        let q =
            utf8_percent_encode("https://www.youtube.com/watch?v=a-b_c.d~e", QUOTE_ALL).to_string();
        assert_eq!(q, "https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3Da-b_c.d~e");
    }
}
