#!/usr/bin/env python3
"""ビルド済み public/ の全リンクを検査し、壊れているものをレポートする。

使い方:
    python Python/check_links.py --root public --out report.md --stats stats.json

方針(コードから読み取れない判断):
- 相手サーバーに優しくするため、同一ドメインへのリクエストは必ず1本ずつ・間隔をあけて送る。
  ドメインが違えば並列で走るので、全体の所要時間は「最多件数のドメイン × 間隔」でほぼ決まる。
- 403/429 は bot 拒否とリンク切れを区別できないため、壊れ扱いにせず件数だけ報告する。
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
from urllib.parse import urlsplit, urlunsplit, urljoin, quote

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

TIMEOUT = (10, 20)  # connect, read
RETRY_WAIT = 3.0

# リンクとして追いかけないスキーム
SKIP_SCHEMES = ("mailto:", "tel:", "javascript:", "data:", "sms:", "#")

ATTR_RE = re.compile(r"""(?:href|src)\s*=\s*["']([^"']+)["']""", re.I)
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
        else:  # ドメイン指定はサブドメインも含めて一致
            if host == rule or host.endswith("." + rule):
                return True
    return False


def page_path(root: Path, file: Path) -> str:
    """public/YouTube/karin-rec.html → /YouTube/karin-rec"""
    rel = file.relative_to(root).as_posix()
    if rel == "index.html":
        return "/"
    return "/" + re.sub(r"\.html$", "", rel)


def collect_links(root: Path, base_url: str) -> tuple[dict, dict]:
    """(外部URL → 出現ページ集合, 内部パス → 出現ページ集合) を返す"""
    external: dict[str, set[str]] = defaultdict(set)
    internal: dict[str, set[str]] = defaultdict(set)
    base_host = (urlsplit(base_url).hostname or "").lower() if base_url else ""

    for file in sorted(root.rglob("*.html")):
        page = page_path(root, file)
        text = file.read_text(encoding="utf-8", errors="replace")
        for raw in ATTR_RE.findall(text):
            url = html.unescape(raw.strip())
            if not url or url.lower().startswith(SKIP_SCHEMES):
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


def check_youtube(session: requests.Session, video_id: str) -> tuple[str, str]:
    """oEmbed で動画の生死を判定する。watch ページは削除済みでも 200 を返すため"""
    target = quote(f"https://www.youtube.com/watch?v={video_id}", safe="")
    url = f"https://www.youtube.com/oembed?url={target}&format=json"
    try:
        res = session.get(url, timeout=TIMEOUT)
    except requests.RequestException as e:
        return "error", type(e).__name__
    if res.status_code == 200:
        return "ok", "200"
    if res.status_code == 401:
        # 埋め込み禁止設定なだけで動画自体は存在する
        return "ok", "401 (埋め込み不可・動画は存在)"
    if res.status_code == 404:
        return "broken", "404 (削除または非公開)"
    if res.status_code in (403, 429):
        return "blocked", str(res.status_code)
    return "broken", str(res.status_code)


def check_url(session: requests.Session, url: str) -> tuple[str, str]:
    """(判定, 詳細) を返す。判定は ok / broken / blocked / error"""
    yt = YT_ID_RE.search(url)
    if yt and "youtube.com/oembed" not in url:
        return check_youtube(session, yt.group(1))

    for attempt in (1, 2):
        try:
            res = session.head(url, timeout=TIMEOUT, allow_redirects=True)
            # HEAD を受け付けないサーバーは GET で確かめ直す
            if res.status_code in (403, 404, 405, 406, 409, 429, 501) or res.status_code >= 500:
                res = session.get(url, timeout=TIMEOUT, allow_redirects=True, stream=True)
                res.close()
        except requests.RequestException as e:
            if attempt == 1:
                time.sleep(RETRY_WAIT)
                continue
            return "error", type(e).__name__

        code = res.status_code
        if code < 400:
            return "ok", str(code)
        if code in (401, 403, 429, 999):
            return "blocked", str(code)
        if code >= 500 and attempt == 1:
            time.sleep(RETRY_WAIT)
            continue
        return "broken", str(code)
    return "broken", "unknown"


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
        verdict, detail = check_url(session, url)
        results.append((url, verdict, detail))
        with _progress_lock:
            _done += 1
            if _done % 100 == 0:
                print(f"  ... {_done}/{total}", file=sys.stderr, flush=True)
    session.close()
    return results


# ---------------------------------------------------------------- レポート
def build_report(base_url, external, internal_broken, results, counts, elapsed, max_items):
    now = datetime.now(JST).strftime("%Y-%m-%d %H:%M JST")
    broken = [r for r in results if r[1] in ("broken", "error")]
    blocked = [r for r in results if r[1] == "blocked"]
    broken.sort(key=lambda r: r[0])

    L = []
    L.append("## リンクチェック結果")
    L.append("")
    L.append(f"- チェック日時: **{now}**（所要 {elapsed//60:.0f}分{elapsed%60:.0f}秒）")
    L.append(f"- 検査した外部リンク: **{counts['checked']:,}** 件"
             f"（除外 {counts['ignored']:,} 件）")
    L.append(f"- 検査した内部リンク: **{counts['internal']:,}** 件")
    L.append(f"- 🔴 **リンク切れ {len(broken) + len(internal_broken):,} 件**"
             f"（外部 {len(broken):,} / 内部 {len(internal_broken):,}）")
    if blocked:
        L.append(f"- ⚪ bot拒否と思われる応答: {len(blocked):,} 件（リンク切れとは限らない）")
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
        for url, _verdict, detail in broken[:max_items]:
            pages = sorted(external.get(url, []))
            L.append(f"- [{detail}] {url}")
            L.append(f"  - 掲載元: {', '.join(f'[{p}]({base_url}{p})' for p in pages[:10])}"
                     + ("…" if len(pages) > 10 else ""))
        if len(broken) > max_items:
            L.append("")
            L.append(f"…ほか {len(broken) - max_items} 件（全件は Actions の成果物 report.md を参照）")
        L.append("")

    if blocked:
        L.append("<details><summary>参考: bot拒否と思われる応答（リンク切れとは限らない）</summary>")
        L.append("")
        for url, _v, detail in sorted(blocked)[:100]:
            L.append(f"- [{detail}] {url}")
        if len(blocked) > 100:
            L.append(f"- …ほか {len(blocked) - 100} 件")
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
    elapsed = time.time() - started

    counts = {"checked": len(targets), "ignored": ignored, "internal": len(internal)}
    report = build_report(args.base_url, external, internal_broken, results,
                          counts, elapsed, args.max_items)
    Path(args.out).write_text(report, encoding="utf-8")

    broken = [r for r in results if r[1] in ("broken", "error")]
    stats = {
        "broken_external": len(broken),
        "broken_internal": len(internal_broken),
        "broken_total": len(broken) + len(internal_broken),
        "blocked": len([r for r in results if r[1] == "blocked"]),
        "checked": len(targets),
        "ignored": ignored,
        "elapsed_sec": round(elapsed),
    }
    Path(args.stats).write_text(json.dumps(stats, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(stats, ensure_ascii=False), file=sys.stderr)
    return 0


if __name__ == "__main__":
    sys.exit(main())
