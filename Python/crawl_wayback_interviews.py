#!/usr/bin/env python3
"""Wayback Machine から取りこぼしインタビュー候補を機械的に全取得する。

対象ドメイン:
  - logirl.favclip.com/article/detail/<数字>   (ログイール)
  - hustlepress.co.jp  (id. サブドメイン含む)    (ハッスルプレス)

流れ(どちらも再開可能):
  list  : CDX API で「Wayback に残っている 200 のHTML」を列挙 → urls.jsonl
  fetch : 各URLのスナップショット(id_ 修飾子=ツールバー無しの元HTML)を取得し、
          タイトル・日付・タグ・本文を抜いて pages.jsonl に1行ずつ追記
  all   : list → fetch

出力先(--out)はリポジトリの外を指定すること(CLAUDE.md: 作業用ファイルを置かない)。
リポジトリ内を指定するとエラーにする。

使い方:
  python Python/crawl_wayback_interviews.py all --out C:/tmp/wb_interviews
  python Python/crawl_wayback_interviews.py fetch --out C:/tmp/wb_interviews --limit 20   # お試し
  python Python/crawl_wayback_interviews.py fetch --out ... --retry-errors               # 失敗分だけ再取得

pages.jsonl の1行:
  {url, ts, snapshot, title, og_title, description, published, tags[], text,
   hp_hits[], error}
  hp_hits は本文・タイトルにハロプロ系キーワードが出た語(絞り込み用の目安。
  ここでは落とさず全ページ保存する)。
"""

import argparse
import gzip
import html as htmllib
import json
import re
import sys
import threading
import time
import urllib.error
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

try:
    import trafilatura
except ImportError:
    trafilatura = None

REPO_ROOT = Path(__file__).resolve().parent.parent
CDX = "https://web.archive.org/cdx/search/cdx"
UA = ("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
      "(KHTML, like Gecko) Chrome/125.0 Safari/537.36")

# CDX の問い合わせ。url / matchType / 絞り込み正規表現(URLのパス部に対して)
TARGETS = [
    {
        "name": "logirl",
        "url": "logirl.favclip.com/article/detail/",
        "matchType": "prefix",
        "keep": re.compile(r"^/article/detail/\d+/?$"),
    },
    {
        "name": "hustlepress",
        "url": "hustlepress.co.jp",
        "matchType": "domain",
        # 記事以外(一覧・フィード・検索・静的ファイル)を除く。本文URLは /xxxx/ 形式が大半
        "keep": None,
    },
]
HUSTLE_SKIP = re.compile(
    r"^/(?:wp-[^/]*|feed|comments|page|category|tag|author|search|xmlrpc\.php)(?:/|$)"
    r"|^/\d{4}(?:/\d{1,2})?(?:/\d{1,2})?/?$"
    r"|/feed/?$|/page/\d+/?$|\.(?:css|js|png|jpe?g|gif|ico|svg|xml|txt|pdf|woff2?)$",
    re.I,
)

# ハロプロ系の目印(絞り込みの目安)。増やすならここ
HP_WORDS = [
    "ハロプロ", "ハロー!プロジェクト", "ハロー！プロジェクト", "Hello! Project", "Hello!Project",
    "モーニング娘", "アンジュルム", "スマイレージ", "Juice=Juice", "ジュースジュース",
    "つばきファクトリー", "こぶしファクトリー", "カントリー・ガールズ", "BEYOOOOONDS",
    "OCHA NORMA", "ロージークロニクル", "Berryz工房", "℃-ute", "Cute", "℃-ute",
    "道重さゆみ", "鞘師里保", "譜久村聖", "宮崎由加", "宮本佳林", "高木紗友希", "金澤朋子",
    "船木結", "竹内朱莉", "和田彩花", "勝田里奈", "室田瑞希", "上國料萌衣", "川村文乃",
    "浅倉樹々", "山岸理子", "小野瑞歩", "岸本ゆめの", "山﨑夢羽", "小片リサ", "稲場愛香",
    "森戸知沙希", "新沼希空", "浜浦彩乃", "谷本安美", "小野田紗栞", "山木梨沙", "西田汐里",
    "秋山眞緒", "清野桃々姫", "櫻井梨央", "斉藤円香", "北原もも", "橋迫鈴", "石山咲良",
    "野中美希", "牧野真莉愛", "羽賀朱音", "加賀楓", "横山玲奈", "尾形春水", "段原瑠々",
    "岡村ほまれ", "北川莉央", "小田さくら", "佐藤優樹", "工藤遥", "石田亜佑美", "飯窪春菜",
    "生田衣梨奈", "譜久村", "山崎愛生", "植村あかり", "ハロステ", "ハロコン",
]
HP_RE = re.compile("|".join(re.escape(w) for w in dict.fromkeys(HP_WORDS)), re.I)


# ---------------------------------------------------------------- HTTP
class RateLimiter:
    """全スレッド共通の最小間隔(秒)。Wayback に優しく。"""

    def __init__(self, interval: float):
        self.interval = interval
        self._lock = threading.Lock()
        self._next = 0.0

    def penalize(self, seconds: float):
        """429を受けたら全スレッドをまとめて待たせる(スレッドごとに別々に待つと再び叩いて長引く)。"""
        with self._lock:
            self._next = max(self._next, time.monotonic() + seconds)

    def wait(self):
        with self._lock:
            now = time.monotonic()
            t = max(now, self._next)
            self._next = t + self.interval
        if t > now:
            time.sleep(t - now)


LIMITER: "RateLimiter | None" = None


def http_get(url: str, timeout: float, retries: int = 5, backoff: float = 15.0) -> bytes:
    """429/5xx/タイムアウトは指数バックオフで再試行。404などは即座に例外。"""
    last: Exception | None = None
    for i in range(retries):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Encoding": "gzip"})
            with urllib.request.urlopen(req, timeout=timeout) as r:
                raw = r.read()
                if (r.headers.get("Content-Encoding") or "").lower() == "gzip" or raw[:2] == b"\x1f\x8b":
                    raw = gzip.decompress(raw)
                return raw
        except urllib.error.HTTPError as e:
            if e.code not in (429, 500, 502, 503, 504):
                raise
            if e.code == 429 and LIMITER is not None:
                LIMITER.penalize(60)
            last = e
        except (urllib.error.URLError, TimeoutError, ConnectionError, OSError) as e:
            last = e
        time.sleep(min(backoff * (2 ** i), 120))
    raise last if last else RuntimeError("fetch failed")


# ---------------------------------------------------------------- list
def norm_key(url: str) -> str:
    """http/https・:80・www の違いを潰した重複判定用キー(クエリは落とす)。"""
    p = urllib.parse.urlsplit(url)
    host = (p.hostname or "").lower()
    if host.startswith("www."):
        host = host[4:]
    path = urllib.parse.unquote(p.path)
    if not path.endswith("/") and "." not in path.rsplit("/", 1)[-1]:
        path += "/"
    return f"{host}{path}"


def clean_url(url: str) -> str:
    """表示・取得用: ':80' を外す。"""
    return re.sub(r"^(https?://[^/:]+):(?:80|443)(?=/|$)", r"\1", url)


def cmd_list(out: Path, _args) -> int:
    seen: dict[str, dict] = {}
    for t in TARGETS:
        print(f"[list] {t['name']}: CDX 問い合わせ中(1〜2分かかることがあります)…", flush=True)
        q = urllib.parse.urlencode(
            [
                ("url", t["url"]), ("matchType", t["matchType"]), ("output", "json"),
                ("fl", "timestamp,original,statuscode,mimetype,length"),
                ("filter", "statuscode:200"), ("filter", "mimetype:text/html"),
            ]
        )
        rows = json.loads(http_get(f"{CDX}?{q}", timeout=300, retries=4, backoff=10))[1:]
        kept = 0
        for ts, original, _sc, _mt, length in rows:
            url = clean_url(original)
            path = urllib.parse.urlsplit(url).path
            if t["keep"] is not None:
                if not t["keep"].search(path):
                    continue
            else:
                if (path in ("", "/") or HUSTLE_SKIP.search(path)
                        or urllib.parse.urlsplit(url).query):
                    continue
            key = norm_key(url)
            # 同じ記事の複数スナップショットのうち、一番大きいもの(=描画済み・省略なし)を使う。
            # LoGiRL は初期のスナップショットが未描画の殻(タイトルだけ)なので特に必要
            ln = int(length or 0)
            if key in seen and seen[key]["length"] >= ln:
                continue
            if key not in seen:
                kept += 1
            seen[key] = {"site": t["name"], "ts": ts, "url": url, "length": ln}
        print(f"[list] {t['name']}: CDX {len(rows)}件 → 対象 {kept}件", flush=True)

    items = sorted(seen.values(), key=lambda r: (r["site"], r["url"]))
    with (out / "urls.jsonl").open("w", encoding="utf-8") as f:
        for it in items:
            f.write(json.dumps(it, ensure_ascii=False) + "\n")
    print(f"[list] urls.jsonl に {len(items)}件", flush=True)
    return 0


# ---------------------------------------------------------------- extract
def decode_html(raw: bytes) -> str:
    enc = "utf-8"
    m = re.search(rb'charset=["\']?([\w\-]+)', raw[:4000], re.I)
    if m:
        enc = m.group(1).decode("ascii", "ignore")
    try:
        return raw.decode(enc, "replace")
    except LookupError:
        return raw.decode("utf-8", "replace")


def meta(html: str, key: str) -> str:
    for pat in (
        rf'<meta[^>]+(?:property|name)=["\']{re.escape(key)}["\'][^>]*content=["\']([^"\']*)["\']',
        rf'<meta[^>]+content=["\']([^"\']*)["\'][^>]*(?:property|name)=["\']{re.escape(key)}["\']',
    ):
        m = re.search(pat, html, re.I)
        if m:
            return htmllib.unescape(m.group(1)).strip()
    return ""


def strip_tags(html: str) -> str:
    s = re.sub(r"(?is)<(script|style|noscript)\b.*?</\1>", " ", html)
    s = re.sub(r"(?s)<[^>]+>", " ", s)
    return re.sub(r"\s+", " ", htmllib.unescape(s)).strip()


def extract_logirl(html: str) -> dict | None:
    """LoGiRL は AngularJS 描画後のDOMが保存されている。trafilatura だとサイドバーしか
    取れないので専用に抜く。構造: article-detail-date(日付) → -title → タグ → -body → -sns/-related"""
    h = re.sub(r"(?is)<(script|style).*?</>", " ", html)  # CSSの 'article-detail-body' に釣られない
    i = h.find('class="article-detail-body')
    if i < 0:
        return None
    sns = [k for k in (h.find('class="article-detail-sns', i), h.find('class="article-detail-related', i))
           if k > 0]
    end = min(sns) if sns else len(h)
    tl = h.find("<tag-list", i, end)           # 本文の下にタグ一覧がある
    body_end = tl if tl > 0 else end
    start = h.find(">", i) + 1                 # class="..." の属性の途中から始めない
    text = strip_tags(h[start:body_end])
    tags = []
    for m in re.finditer(r'tag-item-text[^>]*>(.*?)</span>', h[body_end:end], re.S):
        t = strip_tags(m.group(1))
        if t and t not in tags:
            tags.append(t)

    d = h.find('class="article-detail-date')
    m = re.search(r"(20\d{2})/(\d{1,2})/(\d{1,2})(?:\s+(\d{1,2}:\d{2}))?", strip_tags(h[d:d + 300])) if d >= 0 else None
    published = (f"{m.group(1)}-{int(m.group(2)):02d}-{int(m.group(3)):02d}"
                 + (f" {m.group(4)}" if m.group(4) else "")) if m else ""
    return {"published": published, "tags": tags, "text": text}


def extract(html: str, site: str = "") -> dict:
    m = re.search(r"(?is)<title[^>]*>(.*?)</title>", html)
    title = re.sub(r"\s+", " ", htmllib.unescape(m.group(1))).strip() if m else ""

    published = (meta(html, "article:published_time") or meta(html, "datePublished")
                 or meta(html, "date"))
    if not published:
        m = re.search(r'<time[^>]+datetime=["\']([^"\']+)["\']', html, re.I)
        published = m.group(1) if m else ""
    if not published:
        m = re.search(r"(20\d{2})[./年-]\s*(\d{1,2})[./月-]\s*(\d{1,2})", strip_tags(html)[:3000])
        published = f"{m.group(1)}-{int(m.group(2)):02d}-{int(m.group(3)):02d}" if m else ""

    tags = []
    for m in re.finditer(r'(?is)<a[^>]+rel=["\'][^"\']*\btag\b[^"\']*["\'][^>]*>(.*?)</a>', html):
        t = strip_tags(m.group(1))
        if t and t not in tags:
            tags.append(t)

    text = ""
    if trafilatura is not None:
        try:
            text = trafilatura.extract(html, include_comments=False, include_tables=False) or ""
        except Exception:
            text = ""
    if not text:
        text = strip_tags(html)
    rec = {
        "title": title,
        "og_title": meta(html, "og:title"),
        "description": meta(html, "og:description") or meta(html, "description"),
        "published": published,
        "tags": tags,
        "text": text,
    }
    if site == "logirl":
        rec.update(extract_logirl(html) or {"text": ""})
        # 描画前のスナップショットは本文が空 → og:description(冒頭のみ)で代用し thin を立てる
        if len(rec["text"]) < 300:
            rec["thin"] = True
            rec["text"] = rec["text"] or rec["description"]
    return rec


# ---------------------------------------------------------------- fetch
def fetch_one(item: dict, limiter: RateLimiter, timeout: float) -> dict:
    snap = f"https://web.archive.org/web/{item['ts']}id_/{item['url']}"
    rec = {"site": item["site"], "url": item["url"], "ts": item["ts"], "snapshot": snap,
           "title": "", "og_title": "", "description": "", "published": "", "tags": [],
           "text": "", "hp_hits": []}
    try:
        limiter.wait()
        html = decode_html(http_get(snap, timeout=timeout))
        rec.update(extract(html, item["site"]))
        hay = " ".join([rec["title"], rec["og_title"], rec["description"], " ".join(rec["tags"]),
                        rec["text"]])
        rec["hp_hits"] = sorted(set(m.group(0) for m in HP_RE.finditer(hay)))
        rec["error"] = ""
    except Exception as e:  # noqa: BLE001  失敗は記録して続行
        rec["error"] = f"{type(e).__name__}: {e}"[:200]
    return rec


def cmd_fetch(out: Path, args) -> int:
    urls_path = out / "urls.jsonl"
    if not urls_path.exists():
        print("urls.jsonl がありません。先に list を実行してください。", file=sys.stderr)
        return 1
    items = [json.loads(l) for l in urls_path.read_text(encoding="utf-8").splitlines() if l.strip()]
    if args.site:
        items = [i for i in items if i["site"] == args.site]

    pages_path = out / "pages.jsonl"
    done_ok: set[str] = set()
    done_err: set[str] = set()
    if pages_path.exists():
        for l in pages_path.read_text(encoding="utf-8").splitlines():
            if not l.strip():
                continue
            r = json.loads(l)
            (done_err if r.get("error") else done_ok).add(r["url"])
    skip = done_ok if args.retry_errors else (done_ok | done_err)
    todo = [i for i in items if i["url"] not in skip]
    if args.limit:
        todo = todo[: args.limit]
    print(f"[fetch] 全{len(items)}件 / 取得済み{len(done_ok)} / 今回{len(todo)}件 "
          f"(間隔{args.interval}s, 並列{args.workers})", flush=True)

    global LIMITER
    limiter = LIMITER = RateLimiter(args.interval)
    lock = threading.Lock()
    n = errs = 0
    t0 = time.monotonic()
    with pages_path.open("a", encoding="utf-8") as f, ThreadPoolExecutor(args.workers) as ex:
        for rec in ex.map(lambda it: fetch_one(it, limiter, args.timeout), todo):
            with lock:
                f.write(json.dumps(rec, ensure_ascii=False) + "\n")
                f.flush()
            n += 1
            errs += bool(rec["error"])
            if n % 25 == 0 or n == len(todo):
                el = time.monotonic() - t0
                eta = el / n * (len(todo) - n)
                print(f"[fetch] {n}/{len(todo)} 失敗{errs} 経過{el/60:.1f}分 残り約{eta/60:.0f}分",
                      flush=True)
    return 0


# ---------------------------------------------------------------- main
def main() -> int:
    for st in (sys.stdout, sys.stderr):  # Windowsコンソール(cp932)でも日本語ログを文字化けさせない
        st.reconfigure(encoding="utf-8", errors="replace")
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawTextHelpFormatter)
    ap.add_argument("cmd", choices=["list", "fetch", "all"])
    ap.add_argument("--out", required=True, help="出力ディレクトリ(リポジトリの外)")
    ap.add_argument("--site", choices=[t["name"] for t in TARGETS], help="fetch対象を片方に絞る")
    ap.add_argument("--limit", type=int, default=0, help="fetchの最大件数(お試し用)")
    ap.add_argument("--workers", type=int, default=1)
    ap.add_argument("--interval", type=float, default=5.0, help="全体のリクエスト最小間隔(秒)")
    ap.add_argument("--timeout", type=float, default=45.0)
    ap.add_argument("--retry-errors", action="store_true", help="前回失敗したURLも再取得")
    args = ap.parse_args()

    out = Path(args.out).resolve()
    if out == REPO_ROOT or REPO_ROOT in out.parents:
        print(f"--out はリポジトリの外にしてください: {out}", file=sys.stderr)
        return 2
    out.mkdir(parents=True, exist_ok=True)

    if args.cmd in ("list", "all"):
        rc = cmd_list(out, args)
        if rc:
            return rc
    if args.cmd in ("fetch", "all"):
        return cmd_fetch(out, args)
    return 0


if __name__ == "__main__":
    sys.exit(main())
