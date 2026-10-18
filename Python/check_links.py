#!/usr/bin/env python3
"""ビルド済み public/ の全リンクを検査し、壊れているものをレポートする。

使い方:
    python Python/check_links.py --root public --out report.md --stats stats.json

方針(コードから読み取れない判断):
- 相手サーバーに優しくするため、同一ドメインへのリクエストは必ず1本ずつ・間隔をあけて送る。
  ドメインが違えば並列で走るので、全体の所要時間は「最多件数のドメイン × 間隔」でほぼ決まる。
- **「リンク切れ」と呼ぶのは 404 / 410 / DNS解決不能だけ**にする。403・405・429・5xx・
  タイムアウトは「相手がbotを弾いた」のか「本当に消えた」のか区別がつかず、報告すると
  誤検知だらけになって誰も読まなくなるため、別枠の「要確認」に落とす。
- ただし消えたページを 404 にせずトップや上の階層へ転送するサイトも多い。200 で返ってきても
  転送先がトップ・上の階層なら「実質リンク切れ」としてリンク切れに数える。
  それ以外の場所へ転送されたものは、引っ越しかもしれないので別枠の「転送」に元と先を並べる。
  http→https、www の有無、末尾スラッシュ、index.html だけの違いは転送とみなさない。
- 接続エラーは並列実行中の一時的な詰まりでよく起きるので、最後に直列でもう一度確かめる。
- YouTube は削除済み動画でも watch ページが 200 を返すので oEmbed で生死を判定する。
- 除外したいドメイン・URLは link-check/ignore.txt に書く(理由もそこにコメントで残す)。
"""

import argparse
import html
import json
import re
import sys
import threading
import time
from collections import defaultdict
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone, timedelta
from pathlib import Path
from urllib.parse import urlsplit, urlunsplit, urljoin, quote, unquote

import requests

REPO_ROOT = Path(__file__).resolve().parent.parent
IGNORE_FILE = REPO_ROOT / "link-check" / "ignore.txt"
JST = timezone(timedelta(hours=9))

USER_AGENT = (
    "Mozilla/5.0 (compatible; karin-archive-linkcheck/1.0; "
    "+https://karin-archive.com/)"
)

# 同一ドメインへの最小リクエスト間隔(秒)。件数が多い相手ほど長めにする
DEFAULT_DELAY = 0.5
DOMAIN_DELAY = {
    "www.nicovideo.jp": 0.8,
    "nicovideo.jp": 0.8,
    "www.youtube.com": 0.3,
    "music.youtube.com": 0.3,
    "i.ytimg.com": 0.2,
}

TIMEOUT = (10, 20)          # connect, read
RECHECK_TIMEOUT = (20, 40)  # 再検査は気長に待つ
RETRY_WAIT = 3.0

SKIP_SCHEMES = ("mailto:", "tel:", "javascript:", "data:", "sms:", "#")

# 接続先を温めるだけの指定。リンクではないので追いかけない
SKIP_REL = {"preconnect", "dns-prefetch", "prefetch", "prerender", "modulepreload"}

# ドメインそのものが消えたと判断してよいエラー
DNS_HINTS = (
    "NameResolutionError", "Name or service not known", "nodename nor servname",
    "getaddrinfo failed", "No address associated", "Temporary failure in name resolution",
)

# script/style の中身は「JSが実行時に組み立てる文字列」であって、そのままではURLにならない
SCRIPT_RE = re.compile(r"<(script|style)\b[^>]*>.*?</\1\s*>", re.I | re.S)
# ${d.article} のようなテンプレート変数が残っているものは検査できない
PLACEHOLDER_RE = re.compile(r"\$\{|\{\{|<%|%s\b|\$\(")

TAG_RE = re.compile(r"<(\w+)\b([^>]*)>")
HREF_RE = re.compile(r"""(?:href|src)\s*=\s*["']([^"']+)["']""", re.I)
REL_RE = re.compile(r"""\brel\s*=\s*["']([^"']*)["']""", re.I)
# 転送先の比較で同じページとみなす末尾 (/index.html など)
INDEX_RE = re.compile(r"/index\.(?:html?|php)$", re.I)
# /ja や /en-us は言語別のトップページ
LANG_TOP_RE = re.compile(r"/[a-z]{2}(?:-[a-z]{2})?")
YT_ID_RE = re.compile(
    r"(?:youtube\.com/watch\?(?:[^\"']*&)?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)"
    r"([A-Za-z0-9_-]{11})"
)


# ---------------------------------------------------------------- 収集
def load_ignore_rules() -> list[str]:
    if not IGNORE_FILE.exists():
        return []
    rules = []
    for line in IGNORE_FILE.read_text(encoding="utf-8").splitlines():
        line = line.split("#", 1)[0].strip()
        if line:
            rules.append(line)
    return rules


def is_ignored(url: str, rules: list[str]) -> bool:
    host = (urlsplit(url).hostname or "").lower()
    for rule in rules:
        if "/" in rule:  # パスを含む指定は前方一致
            if url.startswith(rule):
                return True
        else:            # ドメイン指定はサブドメインも含めて一致
            if host == rule or host.endswith("." + rule):
                return True
    return False


def page_path(root: Path, file: Path) -> str:
    """public/YouTube/karin-rec.html → /YouTube/karin-rec"""
    rel = file.relative_to(root).as_posix()
    if rel == "index.html":
        return "/"
    return "/" + re.sub(r"\.html$", "", rel)


def iter_link_urls(text: str):
    """HTMLから追いかけるべきURLを取り出す。preconnect等の非リンクは飛ばす"""
    for tag, attrs in TAG_RE.findall(text):
        if tag.lower() == "link":
            rel = REL_RE.search(attrs)
            if rel and set(rel.group(1).lower().split()) & SKIP_REL:
                continue
        for url in HREF_RE.findall(attrs):
            yield url


def collect_links(root: Path, base_url: str) -> tuple[dict, dict]:
    """(外部URL → 出現ページ集合, 内部パス → 出現ページ集合) を返す"""
    external: dict[str, set[str]] = defaultdict(set)
    internal: dict[str, set[str]] = defaultdict(set)
    base_host = (urlsplit(base_url).hostname or "").lower() if base_url else ""

    for file in sorted(root.rglob("*.html")):
        page = page_path(root, file)
        text = SCRIPT_RE.sub(" ", file.read_text(encoding="utf-8", errors="replace"))
        for raw in iter_link_urls(text):
            url = html.unescape(raw.strip())
            if not url or url.lower().startswith(SKIP_SCHEMES):
                continue
            if PLACEHOLDER_RE.search(url):
                continue
            if url.startswith("//"):
                url = "https:" + url
            split = urlsplit(url)
            if split.scheme in ("http", "https"):
                host = (split.hostname or "").lower()
                if base_host and host == base_host:
                    internal[split.path or "/"].add(page)
                else:
                    # フラグメントだけ違うものは同じURLとして1回で済ませる
                    external[urlunsplit(split._replace(fragment=""))].add(page)
            elif not split.scheme:
                path = urljoin(page, split.path) if not url.startswith("/") else split.path
                if path:
                    internal[path].add(page)
    return external, internal


# ---------------------------------------------------------------- 内部リンク
def load_redirects(root: Path) -> dict[str, str]:
    f = root / "_redirects"
    if not f.exists():
        return {}
    rules = {}
    for line in f.read_text(encoding="utf-8").splitlines():
        parts = line.split()
        if len(parts) >= 2:
            rules[parts[0]] = parts[1]
    return rules


def resolve_internal(path: str, root: Path, redirects: dict[str, str]) -> bool:
    """public/ 配下に実体があるか(またはリダイレクト先に実体があるか)"""
    seen = set()
    while True:
        clean = path.split("#", 1)[0].split("?", 1)[0]
        if not clean.startswith("/"):
            clean = "/" + clean
        if clean in ("/", "/index", "/index.html"):
            return (root / "index.html").exists()
        rel = clean.lstrip("/")
        for candidate in (root / rel, root / (rel + ".html"), root / rel / "index.html"):
            if candidate.is_file():
                return True
        target = redirects.get(clean)
        if not target or clean in seen:
            return False
        seen.add(clean)
        path = target


# ---------------------------------------------------------------- 外部リンク
def make_session() -> requests.Session:
    s = requests.Session()
    s.headers.update({
        "User-Agent": USER_AGENT,
        "Accept": "text/html,application/xhtml+xml,image/*,*/*;q=0.8",
        "Accept-Language": "ja,en;q=0.8",
    })
    return s


def classify_status(code: int) -> tuple[str, str, str]:
    """HTTPステータスを (判定, 詳細, 種別) に振り分ける"""
    if code < 400:
        return "ok", str(code), "http"
    if code in (404, 410):
        return "broken", f"{code} (ページが存在しない)", "http"
    if code in (401, 403, 405, 406, 429, 999):
        return "warn", f"{code} (アクセス拒否の可能性)", "http"
    if code >= 500:
        return "warn", f"{code} (サーバー側の一時障害の可能性)", "http"
    return "warn", str(code), "http"


def classify_error(e: Exception) -> tuple[str, str, str]:
    msg = str(e)
    if any(h in msg for h in DNS_HINTS):
        return "broken", "DNS解決できず (ドメイン消滅の可能性)", "network"
    return "warn", type(e).__name__, "network"


def _comparable(url: str) -> tuple[str, str]:
    """転送前後を比べるための (ホスト, パス)。http/https・www・末尾スラッシュ・index.html は無視"""
    s = urlsplit(url)
    host = (s.hostname or "").lower().removeprefix("www.")
    path = INDEX_RE.sub("", unquote(s.path)).rstrip("/")
    return host, path


def classify_redirect(url: str, final: str):
    """転送されていれば (判定, 詳細) を返す。実質同じページなら None"""
    src_host, src_path = _comparable(url)
    dst_host, dst_path = _comparable(final)
    if (src_host, src_path) == (dst_host, dst_path):
        return None
    if src_path and not LANG_TOP_RE.fullmatch(src_path):
        if dst_path == "" or LANG_TOP_RE.fullmatch(dst_path):
            where = "トップ" if dst_host == src_host else "別サイトのトップ"
            return "broken", f"{where}へ転送 (ページが消えた可能性大)"
        if dst_host == src_host and src_path.startswith(dst_path + "/"):
            return "broken", "上の階層へ転送 (ページが消えた可能性大)"
    return "moved", "別のページへ転送"


def check_youtube(session, video_id: str, timeout) -> tuple[str, str, str]:
    """oEmbed で動画の生死を判定する。watch ページは削除済みでも 200 を返すため"""
    target = quote(f"https://www.youtube.com/watch?v={video_id}", safe="")
    url = f"https://www.youtube.com/oembed?url={target}&format=json"
    try:
        res = session.get(url, timeout=timeout)
    except requests.RequestException as e:
        return classify_error(e)
    if res.status_code == 401:
        # 埋め込み禁止設定なだけで動画自体は存在する
        return "ok", "401 (埋め込み不可・動画は存在)", "http"
    if res.status_code == 404:
        return "broken", "404 (削除または非公開)", "http"
    return classify_status(res.status_code)


def check_url(session, url: str, timeout=TIMEOUT) -> tuple[str, str, str, str]:
    """(判定, 詳細, 種別, 転送先) を返す。判定は ok / broken / warn / moved。転送先は転送されたときだけ"""
    yt = YT_ID_RE.search(url)
    if yt and "youtube.com/oembed" not in url:
        return check_youtube(session, yt.group(1), timeout) + ("",)

    for attempt in (1, 2):
        try:
            res = session.head(url, timeout=timeout, allow_redirects=True)
            # HEAD を受け付けないサーバーが多いので、4xx/5xx なら GET で確かめ直す
            if res.status_code >= 400:
                res = session.get(url, timeout=timeout, allow_redirects=True, stream=True)
                res.close()
        except requests.RequestException as e:
            if attempt == 1:
                time.sleep(RETRY_WAIT)
                continue
            return classify_error(e) + ("",)

        if res.status_code >= 500 and attempt == 1:
            time.sleep(RETRY_WAIT)
            continue
        verdict, detail, kind = classify_status(res.status_code)
        # 転送の先で 200 が返っても、行き着いた先が元のページとは限らない
        if verdict == "ok" and res.history:
            redirect = classify_redirect(url, res.url)
            if redirect:
                return redirect + ("redirect", res.url)
        return verdict, detail, kind, ""
    return "warn", "unknown", "http", ""


_progress_lock = threading.Lock()
_done = 0


def check_domain(host: str, urls: list[str], total: int, delay_scale: float) -> list[tuple]:
    """1ドメイン分を順番に(間隔をあけて)処理する"""
    global _done
    session = make_session()
    delay = DOMAIN_DELAY.get(host, DEFAULT_DELAY) * delay_scale
    results = []
    for i, url in enumerate(urls):
        if i:
            time.sleep(delay)
        results.append((url,) + check_url(session, url))
        with _progress_lock:
            _done += 1
            if _done % 100 == 0:
                print(f"  ... {_done}/{total}", file=sys.stderr, flush=True)
    session.close()
    return results


def recheck_network_failures(results: list[tuple], limit: int) -> list[tuple]:
    """接続に失敗したものを直列で確かめ直す。

    並列で回している最中の一時的な詰まりや相手側のレート制限で ConnectTimeout に
    なることがあり、そのまま報告するとブラウザでは普通に開ける誤検知になるため。
    """
    targets = [r for r in results if r[3] == "network" and r[1] != "ok"]
    if not targets:
        return results
    print(f"接続失敗 {len(targets)} 件を直列で再検査します", file=sys.stderr, flush=True)
    session = make_session()
    fixed = {}
    for i, row in enumerate(targets[:limit]):
        if i:
            time.sleep(1.5)
        fixed[row[0]] = check_url(session, row[0], timeout=RECHECK_TIMEOUT)
    session.close()
    return [((r[0],) + fixed[r[0]]) if r[0] in fixed else r for r in results]


# ---------------------------------------------------------------- レポート
def build_report(base_url, external, internal_broken, results, counts, elapsed, max_items):
    now = datetime.now(JST).strftime("%Y-%m-%d %H:%M JST")
    broken = sorted([r for r in results if r[1] == "broken"])
    warn = sorted([r for r in results if r[1] == "warn"])
    moved = sorted([r for r in results if r[1] == "moved"])

    def sources(url):
        pages = sorted(external.get(url, []))
        return (f"  - 掲載元: {', '.join(f'[{p}]({base_url}{p})' for p in pages[:10])}"
                + ("…" if len(pages) > 10 else ""))

    L = []
    L.append("## リンクチェック結果")
    L.append("")
    L.append(f"- チェック日時: **{now}**（所要 {elapsed//60:.0f}分{elapsed%60:.0f}秒）")
    L.append(f"- 検査した外部リンク: **{counts['checked']:,}** 件（除外 {counts['ignored']:,} 件）")
    L.append(f"- 検査した内部リンク: **{counts['internal']:,}** 件")
    L.append(f"- 🔴 **リンク切れ {len(broken) + len(internal_broken):,} 件**"
             f"（外部 {len(broken):,} / 内部 {len(internal_broken):,}）")
    if moved:
        L.append(f"- 🟡 別のページへ転送 {len(moved):,} 件（引っ越しなら新しいURLに貼り替えると安心）")
    if warn:
        L.append(f"- ⚪ 要確認 {len(warn):,} 件（アクセス拒否・一時障害の可能性。切れているとは限らない）")
    L.append("")

    if internal_broken:
        L.append(f"### 🔴 サイト内リンク切れ（{len(internal_broken)}件）")
        L.append("")
        L.append("存在しないページを指しているリンク。ほぼ確実に貼り間違いです。")
        L.append("")
        for path, pages in sorted(internal_broken.items())[:max_items]:
            L.append(f"- `{path}`")
            L.append(f"  - 掲載元: {', '.join(f'`{p}`' for p in sorted(pages)[:10])}"
                     + ("…" if len(pages) > 10 else ""))
        L.append("")

    if broken:
        L.append(f"### 🔴 外部リンク切れ（{len(broken)}件）")
        L.append("")
        L.append("404 / 410 / ドメイン消滅と、トップや上の階層へ飛ばされるものを載せています。")
        L.append("")
        for url, _v, detail, _k, final in broken[:max_items]:
            L.append(f"- [{detail}] {url}")
            if final:
                L.append(f"  - 転送先: {final}")
            L.append(sources(url))
        if len(broken) > max_items:
            L.append("")
            L.append(f"…ほか {len(broken) - max_items} 件（全件は Actions の成果物 report.md を参照）")
        L.append("")

    if moved:
        L.append(f"<details><summary>🟡 別のページへ転送 {len(moved)}件"
                 "（ページは開けるが、元と違う場所に着いた。引っ越しか、別の記事に差し替わったか）</summary>")
        L.append("")
        for url, _v, _d, _k, final in moved[:max_items]:
            L.append(f"- {url}")
            L.append(f"  - 転送先: {final}")
            L.append(sources(url))
        if len(moved) > max_items:
            L.append(f"- …ほか {len(moved) - max_items} 件")
        L.append("")
        L.append("</details>")
        L.append("")

    if warn:
        L.append(f"<details><summary>⚪ 要確認 {len(warn)}件"
                 "（botを弾かれた・一時的に繋がらなかった。ブラウザでは普通に開けることが多い）</summary>")
        L.append("")
        for url, _v, detail, _k, _f in warn[:200]:
            L.append(f"- [{detail}] {url}")
        if len(warn) > 200:
            L.append(f"- …ほか {len(warn) - 200} 件")
        L.append("")
        L.append("</details>")
        L.append("")

    if not broken and not internal_broken:
        L.append("### ✅ リンク切れはありませんでした")
        L.append("")

    L.append("---")
    L.append("")
    L.append("除外設定は `link-check/ignore.txt`、実行するワークフローは "
             "`.github/workflows/link-check.yml`（毎月1日 JST 12:00 / 手動実行可）。")
    return "\n".join(L)


# ---------------------------------------------------------------- main
def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default="public", help="ビルド済みサイトのディレクトリ")
    ap.add_argument("--base-url", default="https://karin-archive.com")
    ap.add_argument("--out", default="link-check-report.md")
    ap.add_argument("--stats", default="link-check-stats.json")
    ap.add_argument("--workers", type=int, default=12, help="並列に処理するドメイン数")
    ap.add_argument("--delay-scale", type=float, default=1.0, help="リクエスト間隔の倍率")
    ap.add_argument("--max-items", type=int, default=150, help="レポートに載せる最大件数")
    ap.add_argument("--recheck-limit", type=int, default=300, help="直列で再検査する最大件数")
    ap.add_argument("--limit", type=int, default=0, help="動作確認用: 検査するURL数の上限")
    args = ap.parse_args()

    root = Path(args.root)
    if not root.is_dir():
        print(f"エラー: {root} がありません。先に build/build.sh を実行してください。", file=sys.stderr)
        return 2

    rules = load_ignore_rules()
    external, internal = collect_links(root, args.base_url)

    # 内部リンクはファイルの有無で判定する(HTTPリクエスト不要)
    redirects = load_redirects(root)
    internal_broken = {
        path: pages for path, pages in internal.items()
        if not resolve_internal(path, root, redirects)
    }

    targets = [u for u in external if not is_ignored(u, rules)]
    ignored = len(external) - len(targets)
    targets.sort()
    if args.limit:
        targets = targets[: args.limit]

    print(f"内部リンク {len(internal):,} 件（切れ {len(internal_broken)} 件）", file=sys.stderr)
    print(f"外部リンク {len(external):,} 件中 {len(targets):,} 件を検査（除外 {ignored:,} 件）",
          file=sys.stderr)

    by_host: dict[str, list[str]] = defaultdict(list)
    for url in targets:
        by_host[(urlsplit(url).hostname or "").lower()].append(url)
    print(f"ドメイン数 {len(by_host)}（最多: "
          + ", ".join(f"{h}={len(u)}" for h, u in
                      sorted(by_host.items(), key=lambda kv: -len(kv[1]))[:5]) + "）",
          file=sys.stderr)

    started = time.time()
    results: list[tuple] = []
    with ThreadPoolExecutor(max_workers=args.workers) as ex:
        futures = [
            ex.submit(check_domain, host, urls, len(targets), args.delay_scale)
            for host, urls in sorted(by_host.items(), key=lambda kv: -len(kv[1]))
        ]
        for f in futures:
            results.extend(f.result())
    results = recheck_network_failures(results, args.recheck_limit)
    elapsed = time.time() - started

    counts = {"checked": len(targets), "ignored": ignored, "internal": len(internal)}
    report = build_report(args.base_url, external, internal_broken, results,
                          counts, elapsed, args.max_items)
    Path(args.out).write_text(report, encoding="utf-8")

    broken = [r for r in results if r[1] == "broken"]
    stats = {
        "broken_external": len(broken),
        "broken_internal": len(internal_broken),
        "broken_total": len(broken) + len(internal_broken),
        "warn": len([r for r in results if r[1] == "warn"]),
        "moved": len([r for r in results if r[1] == "moved"]),
        "checked": len(targets),
        "ignored": ignored,
        "elapsed_sec": round(elapsed),
    }
    Path(args.stats).write_text(json.dumps(stats, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(stats, ensure_ascii=False), file=sys.stderr)
    return 0


if __name__ == "__main__":
    sys.exit(main())
