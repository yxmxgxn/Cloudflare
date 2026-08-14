#!/usr/bin/env python3
"""インタビュー全文検索インデックスを作る（D1投入用SQLを生成）。

流れ:
  スプシ(メインシート)から ID・URL・ExtraURL を読む
   → 各記事のページ群を集める(ExtraURL優先 / 無ければ自動追尾)
   → 各ページを取得し trafilatura で純本文抽出 → 連結
   → normalize + bigram 化(Worker側 worker/index.js と完全一致)
   → build/interview_index.sql を出力(article_fts / article_src を upsert)

増分:
  --current current_hashes.json (D1から出した {id: src_hash}) を渡すと、
  src_hash が変わった記事だけ再取得。シートから消えたIDはDELETE。

本文は build/interview_index.sql の中(＝D1へ)にしか入らない。リポジトリには残さない
（このSQLはActionsが生成→D1適用したら破棄する運用）。
"""

import argparse
import csv
import hashlib
import io
import json
import re
import sys
import time
import unicodedata
import urllib.parse
import urllib.request
import urllib.error
from datetime import datetime, timezone
from pathlib import Path

try:
    import trafilatura  # 本文抽出(広告・ナビ除去)
except ImportError:
    trafilatura = None

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQUuY0kp6M2i3IBrzIJ0rxkRI19oqwdGFUszeQvsTJyydQJZae5OXrVEgshlXsDR906hUOm6497dUsr/pub"
)
GID_MAIN = "0"

REPO_ROOT = Path(__file__).resolve().parent.parent
OUT_SQL = REPO_ROOT / "build" / "interview_index.sql"

MAX_PAGES = 10       # 自動追尾の上限
PAGE_NUM_MAX = 100   # 末尾数字をページ番号とみなす上限(これ以上は記事IDとみなし追尾しない)
INDEX_VERSION = "5"  # 索引ロジックを変えたらここを上げる → src_hashが変わり全記事を再索引
MAX_INDEX_CHARS = 300000  # 索引対象本文の上限(正規化後の文字数)
BIGRAM_CHUNK = 6000       # 1 INSERT あたりのbigram数(≈31KB。D1のstatement上限に余裕を持たせる)
BIGRAM_OVERLAP = 64       # チャンク境界を跨ぐ検索を落とさないための重なり
BODY_PREVIEW = 2000       # article_src.body に残すプレビュー長(全文はbigram側が保持)
UA = ("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
      "(KHTML, like Gecko) Chrome/125.0 Safari/537.36")

# ---- Worker(worker/index.js)と一致させる正規化・bigram ----
_KEEP = re.compile(r"[^0-9a-z々぀-ヿ㐀-䶿一-鿿]")


def normalize(s: str) -> str:
    return _KEEP.sub("", unicodedata.normalize("NFKC", s or "").lower())


def bigrams(s: str) -> str:
    return " ".join(s[i:i + 2] for i in range(len(s) - 1))


def bigram_list(s: str) -> list[str]:
    return [s[i:i + 2] for i in range(len(s) - 1)]


def chunk_bigrams(tokens: list[str]) -> list[str]:
    """bigram列を、1 INSERTが大きくなりすぎない塊に分割(境界は少し重ねる)。"""
    if len(tokens) <= BIGRAM_CHUNK:
        return [" ".join(tokens)] if tokens else [""]
    chunks, i, step = [], 0, BIGRAM_CHUNK - BIGRAM_OVERLAP
    while i < len(tokens):
        chunks.append(" ".join(tokens[i:i + BIGRAM_CHUNK]))
        if i + BIGRAM_CHUNK >= len(tokens):
            break
        i += step
    return chunks


# ---- 取得 ----
def fetch_html(url: str) -> str | None:
    try:
        req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Language": "ja"})
        with urllib.request.urlopen(req, timeout=20) as r:
            raw = r.read()
    except Exception:
        return None
    enc = "utf-8"
    m = re.search(rb'charset=["\']?([\w\-]+)', raw[:4000], re.I)
    if m:
        enc = m.group(1).decode("ascii", "ignore")
    try:
        return raw.decode(enc, "replace")
    except LookupError:
        return raw.decode("utf-8", "replace")


def fetch_archive(arc_url: str, tries: int = 4) -> str:
    """ライブ取得が失敗する媒体向け: WebArchive(魚拓)から本文を取る。
    archive.orgはActionのIPをブロックしないため、ボット弾きサイトを救える。503はリトライ。"""
    for _ in range(tries):
        html = fetch_html(arc_url)  # 503等は fetch_html が None を返す
        if html:
            body = extract_body(html)
            if body:
                return body
        time.sleep(6)
    return ""


def extract_body(html: str) -> str:
    if not html:
        return ""
    if trafilatura:
        txt = trafilatura.extract(html, include_comments=False, include_tables=False,
                                  no_fallback=False) or ""
        return txt
    # trafilatura が無い環境向けの粗いフォールバック(タグ除去)
    txt = re.sub(r"(?is)<(script|style|nav|header|footer|aside)[^>]*>.*?</\1>", " ", html)
    txt = re.sub(r"(?s)<[^>]+>", " ", txt)
    return re.sub(r"\s+", " ", txt)


# ---- ページ自動追尾 ----
def next_page_url(html: str, cur: str) -> str | None:
    """rel=next もしくは同一パスの連番から次ページURLを推測。"""
    # 1) <link rel="next"> / <a rel="next">
    m = re.search(r'<(?:link|a)[^>]+rel=["\']next["\'][^>]+href=["\']([^"\']+)["\']', html or "", re.I)
    if not m:
        m = re.search(r'<(?:link|a)[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']next["\']', html or "", re.I)
    if m:
        return urllib.parse.urljoin(cur, m.group(1))
    # 2) URL規則の連番: ?page=N を +1。
    #    末尾 /N or /N/ は「小さい数字＝ページ番号」の時だけ +1 する。
    #    /pickup/704725 のような“末尾が記事ID”を次記事と誤連結しないための安全弁。
    pr = urllib.parse.urlparse(cur)
    qs = urllib.parse.parse_qs(pr.query)
    if "page" in qs:
        try:
            qs["page"] = [str(int(qs["page"][0]) + 1)]
            return pr._replace(query=urllib.parse.urlencode(qs, doseq=True)).geturl()
        except ValueError:
            return None
    mnum = re.search(r"/(\d+)/?$", pr.path)
    if mnum and int(mnum.group(1)) < PAGE_NUM_MAX:
        n = int(mnum.group(1)) + 1
        newpath = pr.path[:mnum.start()] + f"/{n}" + ("/" if pr.path.endswith("/") else "")
        return pr._replace(path=newpath).geturl()
    return None


# 本文が文末で終わっているか(=途中で切れていないか)の判定用
_END_OK = re.compile(r"[。．！？!?」』）)\]】〉》…]\s*$")
_PAGER_HINT = re.compile(r"次のページ|続きを読む|次へ|pageNav|pager|pagination|paginat", re.I)


def looks_truncated(body: str, last_html: str) -> bool:
    """単ページ結果が「続きがありそう」に見えるか(⚠ヒント用)。"""
    if not body:
        return False
    if not _END_OK.search(body.rstrip()):
        return True   # 文末で終わっていない=途中で切れている可能性
    if last_html and _PAGER_HINT.search(last_html):
        return True   # ページャらしき要素があるのに辿れていない
    return False


def _stem(u: str):
    """URLから「ページ番号を除いた記事の幹」を取り出す(同一記事判定用)。"""
    pr = urllib.parse.urlparse(u)
    path = re.sub(r"/\d+/?$", "", pr.path).rstrip("/")  # 末尾 /N /N/ を除去
    qs = urllib.parse.parse_qs(pr.query)
    qs.pop("page", None)
    return (pr.netloc, path, urllib.parse.urlencode(qs, doseq=True))


def same_article(cur: str, nxt: str) -> bool:
    """next先が「同じ記事の別ページ」か。別スラッグ(=別記事)なら False。
    suumo等の rel=next が“次の記事”を指すケースの暴走連結を防ぐ。"""
    return _stem(cur) == _stem(nxt)


def collect_pages(main_url: str, extra: list[str], no_follow: bool = False):
    """(本文リスト, 辿ったURLリスト, 切れてるかも) を返す。"""
    bodies, urls = [], []
    seen = set()

    def take(u: str) -> str | None:
        html = fetch_html(u)
        body = extract_body(html or "")
        if body and body[:200] not in seen:
            seen.add(body[:200])
            bodies.append(body)
            urls.append(u)
        return html

    # ExtraURL="-" : このページは追尾しない(メインのみ確定)
    if no_follow:
        take(main_url)
        return bodies, urls, False

    # ExtraURL にURL列挙 : 自動追尾せず明示URL群を使う
    if extra:
        take(main_url)
        for u in extra:
            take(u)
        return bodies, urls, False

    # 自動追尾
    cur, last_html = main_url, ""
    for _ in range(MAX_PAGES):
        html = fetch_html(cur)
        body = extract_body(html or "")
        if not body or body[:200] in seen:
            break
        seen.add(body[:200])
        bodies.append(body)
        urls.append(cur)
        last_html = html or ""
        nxt = next_page_url(html or "", cur)
        if not nxt or nxt == cur or not same_article(cur, nxt):
            break  # 別記事(別スラッグ)への rel=next 等は追わない
        cur = nxt

    truncated = len(urls) == 1 and looks_truncated("\n".join(bodies), last_html)
    return bodies, urls, truncated


# ---- スプシ ----
def fetch_articles() -> list[dict]:
    url = f"{PUB_BASE}?gid={GID_MAIN}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    return list(csv.DictReader(io.StringIO(content)))


def sql_str(s: str) -> str:
    return "'" + (s or "").replace("'", "''") + "'"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--current", help="D1から出した {id: src_hash} のJSON。増分判定に使う")
    ap.add_argument("--out", default=str(OUT_SQL))
    args = ap.parse_args()

    if trafilatura is None:
        print("警告: trafilatura未導入。粗い抽出にフォールバックします (pip install trafilatura)", file=sys.stderr)

    current: dict[str, str] = {}
    if args.current and Path(args.current).exists():
        current = json.loads(Path(args.current).read_text(encoding="utf-8"))

    articles = fetch_articles()
    now = datetime.now(timezone.utc).isoformat()
    stmts: list[str] = []
    sheet_ids = set()
    n_new = n_skip = 0

    for a in articles:
        aid = (a.get("ID") or "").strip()
        url = (a.get("URL") or "").strip()
        if not aid or not url:
            continue
        sheet_ids.add(aid)
        extra_raw = (a.get("ExtraURL") or "").strip()
        arc = (a.get("WebArcive") or "").strip()

        # ExtraURL の解釈を含めてハッシュ化(空/"-"/URL群を切り替えたら再索引される)
        src_hash = hashlib.sha1(("\n".join([INDEX_VERSION, url, extra_raw])).encode("utf-8")).hexdigest()
        # current は "hash|pages"。ハッシュ一致でも pages=0(取得失敗)なら再挑戦する
        cur_hash, _, cur_pages = current.get(aid, "").partition("|")
        if cur_hash == src_hash and cur_pages not in ("", "0"):
            n_skip += 1
            continue  # 変更なし かつ 前回取得済み → 再取得しない

        no_follow = extra_raw == "-"
        extra = [] if no_follow else [u for u in re.split(r"[,\s]+", extra_raw) if u]
        bodies, used, truncated = collect_pages(url, extra, no_follow=no_follow)
        body = "\n".join(bodies)
        # ライブが空/極端に短い(ボット弾き・同意壁等)なら魚拓へフォールバックし、長い方を採用
        if len(normalize(body)) < 100 and "web.archive.org/web/" in arc:
            ab = fetch_archive(arc)
            if len(normalize(ab)) > len(normalize(body)):
                bodies, used, truncated, body = [ab], [arc], False, ab
                print(f"  ↳ {aid}: liveが空/短い → 魚拓から取得", flush=True)
        tokens = bigram_list(normalize(body)[:MAX_INDEX_CHARS])
        chunks = chunk_bigrams(tokens)
        print(f"{aid}: {len(used)}ページ連結 ({', '.join(used)})", flush=True)
        if truncated:
            print(f"  ⚠ {aid}: 本文が途中で切れているかも → 続きのURLを ExtraURL に、"
                  f"連番の別記事なら '-' を入れて再実行を検討", flush=True)

        # bigramは複数行に分割して投入(1 statementが上限超えしないように)
        preview = re.sub(r"\s+", " ", body[:BODY_PREVIEW]).replace(";", "；")
        stmts.append(f"DELETE FROM article_fts WHERE id={sql_str(aid)};")
        for ch in chunks:
            stmts.append(f"INSERT INTO article_fts(id,bigram) VALUES({sql_str(aid)},{sql_str(ch)});")
        stmts.append(
            "INSERT OR REPLACE INTO article_src(id,src_hash,pages,body,indexed_at) "
            f"VALUES({sql_str(aid)},{sql_str(src_hash)},{len(used)},{sql_str(preview)},{sql_str(now)});"
        )
        n_new += 1
        time.sleep(0.3)

    # シートから消えたIDは削除
    removed = [i for i in current if i not in sheet_ids]
    for aid in removed:
        stmts.append(f"DELETE FROM article_fts WHERE id={sql_str(aid)};")
        stmts.append(f"DELETE FROM article_src WHERE id={sql_str(aid)};")

    out = Path(args.out)
    out.parent.mkdir(parents=True, exist_ok=True)
    if stmts:
        out.write_text("\n".join(stmts) + "\n", encoding="utf-8")
    else:
        out.write_text("-- 変更なし\n", encoding="utf-8")

    print(f"\n生成: {out}  更新{n_new} / スキップ{n_skip} / 削除{len(removed)}")


if __name__ == "__main__":
    main()
