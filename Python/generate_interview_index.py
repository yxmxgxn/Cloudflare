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
import difflib
import gzip
import html as htmllib
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

sys.path.insert(0, str(Path(__file__).resolve().parent))
# 媒体名・メンバーは表示側(カード)と同じ解決方法を使う
from generate_interview import GID_MEMBERS, fetch_csv, fetch_domain, media_for  # noqa: E402

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQUuY0kp6M2i3IBrzIJ0rxkRI19oqwdGFUszeQvsTJyydQJZae5OXrVEgshlXsDR906hUOm6497dUsr/pub"
)
GID_MAIN = "0"

REPO_ROOT = Path(__file__).resolve().parent.parent
OUT_SQL = REPO_ROOT / "build" / "interview_index.sql"

MAX_PAGES = 10       # 自動追尾の上限
PAGE_NUM_MAX = 100   # 末尾数字をページ番号とみなす上限(これ以上は記事IDとみなし追尾しない)
INDEX_VERSION = "10"  # 索引ロジックを変えたらここを上げる → src_hashが変わり全記事を再索引
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
def ascii_url(url: str) -> str:
    """日本語ドメイン(移住.net)や日本語のパスを、urllib が送れる形(punycode・%エンコード)にする。"""
    pr = urllib.parse.urlsplit(url)
    host = pr.hostname or ""
    try:
        host = host.encode("idna").decode("ascii")
    except UnicodeError:
        pass
    netloc = host + (f":{pr.port}" if pr.port else "")
    path = urllib.parse.quote(pr.path, safe="/%:@!$&'()*+,;=-._~")
    query = urllib.parse.quote(pr.query, safe="/%:@!$&'()*+,;=-._~?")
    return urllib.parse.urlunsplit((pr.scheme, netloc, path, query, pr.fragment))


def fetch_html(url: str) -> str | None:
    try:
        req = urllib.request.Request(ascii_url(url), headers={"User-Agent": UA, "Accept-Language": "ja"})
        with urllib.request.urlopen(req, timeout=20) as r:
            raw = r.read()
            if (r.headers.get("Content-Encoding") or "").lower() == "gzip":
                # web.archive.orgの id_ 修飾子は元レスポンスのgzip圧縮をそのまま返してくるため
                raw = gzip.decompress(raw)
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


_WAYBACK_TS = re.compile(r"(/web/\d{4,14})(/)")


def archive_id_url(arc_url: str) -> str:
    """web.archive.orgのURLに id_ 修飾子を足す(無ければ)。
    通常表示(ツールバー付き)はページ内に'Wayback Machine'等の文言を含み、
    本文抽出に混入してしまう(実測: 通常1946字→id_版12943字、正しい本文はid_側)。
    表示用リンク(スプシのWebArcive列)はそのまま人間向けの通常表示でよく、
    索引取得のときだけこちらでid_版に変換して使う。"""
    return _WAYBACK_TS.sub(r"\1id_\2", arc_url, count=1)


def fetch_archive(arc_url: str, tries: int = 4) -> str:
    """ライブ取得が失敗する媒体向け: WebArchive(魚拓)から本文を取る。
    archive.orgはActionのIPをブロックしないため、ボット弾きサイトを救える。503はリトライ。"""
    arc_url = archive_id_url(arc_url)
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
_HREF = re.compile(r'href=["\']([^"\'#]+)', re.I)


def _path_segs(path: str) -> list[str]:
    return path.rstrip("/").split("/")


def _host(netloc: str) -> str:
    """www. の有無は同じサイト(oricon.co.jp で登録した記事の「次へ」が www.oricon.co.jp を指す)。"""
    return netloc.lower().removeprefix("www.")


def _page_segment_diff(a: urllib.parse.ParseResult, b: urllib.parse.ParseResult) -> int | None:
    """2つのURLが「PAGE_NUM_MAX未満の数字の区間1つだけ」違うなら、その区間の位置を返す。
    ホスト・クエリ・区間数・他の区間がすべて同じであることが条件。
    大きい数字(記事ID)の違いは対象外なので、/37296/1/1/1 のような隣の記事は該当しない。"""
    if _host(a.netloc) != _host(b.netloc) or a.query != b.query:
        return None
    sa, sb = _path_segs(a.path), _path_segs(b.path)
    if len(sa) != len(sb):
        return None
    diff = [i for i, (x, y) in enumerate(zip(sa, sb)) if x != y]
    if len(diff) != 1:
        return None
    i = diff[0]
    if sa[i].isdigit() and sb[i].isdigit() and int(sa[i]) < PAGE_NUM_MAX and int(sb[i]) < PAGE_NUM_MAX:
        return i
    return None


def _sibling_page_link(html: str, cur: str) -> str | None:
    """ページ内に実在するリンクのうち「現在URLと数字の区間が1つだけ違い、その値が+1」のものを次ページとする。
    ・実在するリンクしか選ばない: rollingstonejapan.com は存在しないページ番号でも200で空ページを返すため、
      番号を機械的に作ると空ページまで連結してしまう
    ・数字が変わる区間が複数ある場合は、いちばん左(上位階層)をページ番号とみなす。
      rollingstone は /記事ID/ページ/写真/ の階層で、写真の枚数がページ数より多い記事もあるため
      「リンク数の多い区間」で選ぶと写真送りを辿ってしまう(実測で10ページ暴走した)"""
    pr = urllib.parse.urlparse(cur)
    segs = _path_segs(pr.path)
    cands: dict[int, str] = {}
    varying: set[int] = set()
    for href in _HREF.findall(html or ""):
        u = urllib.parse.urlparse(urllib.parse.urljoin(cur, href))
        i = _page_segment_diff(pr, u)
        if i is None:
            continue
        varying.add(i)
        if int(_path_segs(u.path)[i]) == int(segs[i]) + 1:
            cands[i] = u.geturl()
    if not varying:
        return None
    return cands.get(min(varying))  # ページ番号の区間に次が無ければ最終ページ


_PAGE_KEYS = ("page", "p", "P")   # ページ番号のクエリ名(shueisha.online ?page= / news.ntv.co.jp ?p= / woman.nikkei.com ?P=)


def _small_num(s: str) -> bool:
    return s.isdigit() and int(s) < PAGE_NUM_MAX


def _is_page2_of(cur: str, nxt: str) -> bool:
    """nxt が「ページ番号の無いURL cur の2ページ目」か。
    パスの後ろに /2・/page/2・/p2 を足した形(i-voce.jp/feed/1299587/ → /2/、natalie.mu/music/pp/xxx/ → /page/2、
    thetv.jp/news/detail/1023365/ → /p2/)と、
    同じパスに ?page=2・?p=2・?P=2 を足した形(shueisha.online は ?disp=paging&page=2 と1つ余計に付く)。
    1ページ目の末尾は記事IDなので、末尾の数字の +1 では見つからない。
    -2・_2 は WordPress の同名スラッグ(別記事)と区別できないので含めない。"""
    a, b = urllib.parse.urlparse(cur), urllib.parse.urlparse(nxt)
    if _host(a.netloc) != _host(b.netloc):
        return False
    if a.query == b.query:
        base = a.path.rstrip("/")
        return b.path.rstrip("/") in (base + "/2", base + "/page/2", base + "/p2")
    qa, qb = urllib.parse.parse_qs(a.query), urllib.parse.parse_qs(b.query)
    added = {k: v for k, v in qb.items() if qa.get(k) != v}
    return (a.path.rstrip("/") == b.path.rstrip("/") and len(added) <= 2
            and any(added.get(k) == ["2"] for k in _PAGE_KEYS)
            and all(qb.get(k) == v for k, v in qa.items()))


# rel=next があるのに「同じ記事の続き」と判定できず使わなかったもの (今のページ, rel=next の先)。
# 知らない形のページ番号(thetv.jp の /p2/ など)を見つけるための記録。main() が記事IDを付けて一覧にする
SKIPPED_NEXT: list[tuple[str, str]] = []


def next_page_url(html: str, cur: str) -> str | None:
    """rel=next もしくは同一パスの連番から次ページURLを推測。"""
    # 1) <link rel="next"> / <a rel="next">
    m = re.search(r'<(?:link|a)[^>]+rel=["\']next["\'][^>]+href=["\']([^"\']+)["\']', html or "", re.I)
    if not m:
        m = re.search(r'<(?:link|a)[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']next["\']', html or "", re.I)
    # rel=next が「次の記事」を指すサイト(tvfan.kyodo.co.jp・tvguide.or.jp など)は、それを使わずに下の規則で探す
    if m:
        rel_next = urllib.parse.urljoin(cur, m.group(1))
        if same_article(cur, rel_next):
            return rel_next
        SKIPPED_NEXT.append((cur, rel_next))
    # 2) URL規則の連番: ?page=N を +1。
    #    末尾 /N or /N/ は「小さい数字＝ページ番号」の時だけ +1 する。
    #    /pickup/704725 のような“末尾が記事ID”を次記事と誤連結しないための安全弁。
    pr = urllib.parse.urlparse(cur)
    qs = urllib.parse.parse_qs(pr.query)
    #    クエリも小さい数字の時だけ(WordPress の ?p=1234 は記事IDなので +1 すると別記事になる)
    for key in _PAGE_KEYS:
        if key in qs and _small_num(qs[key][0]):
            qs[key] = [str(int(qs[key][0]) + 1)]
            return pr._replace(query=urllib.parse.urlencode(qs, doseq=True)).geturl()
    # 3) 1ページ目のURLに page パラメータが無い(=暗黙の1ページ目)サイト向け:
    #    rel=next を付けずに本文中へ直接 "?page=2" へのリンクだけ置くページャがある(例: hominis.media)。
    #    「2」固定で拾う(1ページ目からの遷移にしか使わないため、他の数字への誤爆リスクはない)。
    m = re.search(r'href=["\']([^"\']*[?&]page=2(?:&[^"\']*)?)["\']', html or "", re.I)
    if m:
        return urllib.parse.urljoin(cur, m.group(1))
    # 4) ページ番号がパスの途中にあるサイト向け(例: rollingstonejapan.com /detail/37295/1/1/1 → /37295/2/1/1)。
    #    下の 6) は末尾の数字しか見ないため /1/1/2 を作ってしまう。こちらを先に試す。
    sib = _sibling_page_link(html, cur)
    if sib:
        return sib
    # 5) 1ページ目だけページ番号を持たないサイト(natalie.mu /pp/xxx/・i-voce.jp /feed/123/ など)。
    #    ページ内に実在する2ページ目へのリンクだけを拾う(3ページ目以降は 2) か 6) の +1 で進む)
    for href in _HREF.findall(html or ""):
        u = urllib.parse.urljoin(cur, href)
        if _is_page2_of(cur, u):
            return u
    # 6) 末尾 /N を +1
    #    thetv.jp の /p2/ のように頭に p が付く形も同じ
    mnum = re.search(r"/(p?)(\d+)/?$", pr.path)
    if mnum and int(mnum.group(2)) < PAGE_NUM_MAX:
        n = int(mnum.group(2)) + 1
        newpath = pr.path[:mnum.start()] + f"/{mnum.group(1)}{n}" + ("/" if pr.path.endswith("/") else "")
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
    """URLから「ページ番号を除いた記事の幹」を取り出す(同一記事判定用)。
    末尾 /N・/pN は「小さい数字＝ページ番号」の時だけ除去する。
    /news/248033 のような“末尾が記事ID(大きい数字)”を除去すると、別記事どうしが
    同じ幹に潰れて誤連結するため、PAGE_NUM_MAX 未満のみ除去する。"""
    pr = urllib.parse.urlparse(u)
    path = pr.path
    m = re.search(r"/p?(\d+)/?$", path)
    if m and int(m.group(1)) < PAGE_NUM_MAX:
        path = path[:m.start()]
    path = path.rstrip("/")
    qs = urllib.parse.parse_qs(pr.query)
    for key in _PAGE_KEYS:
        if key in qs and _small_num(qs[key][0]):
            qs.pop(key)
    return (_host(pr.netloc), path, urllib.parse.urlencode(qs, doseq=True))


def same_article(cur: str, nxt: str) -> bool:
    """next先が「同じ記事の別ページ」か。別スラッグ(=別記事)なら False。
    suumo等の rel=next が“次の記事”を指すケースの暴走連結を防ぐ。
    途中の区間がページ番号のURL(/37295/1/1/1 と /37295/2/1/1)は末尾基準の _stem では別物になるので、
    「小さい数字の区間が1つだけ違う」も同一記事とみなす。"""
    if _stem(cur) == _stem(nxt) or _is_page2_of(cur, nxt):
        return True
    return _page_segment_diff(urllib.parse.urlparse(cur), urllib.parse.urlparse(nxt)) is not None


TITLE_SIM_MIN = 0.6  # ページ間タイトル類似度の下限(実測: 同一記事の別ページ≈0.9+ / 別記事≈0.3)


def _page_title(html: str) -> str:
    m = re.search(r'og:title["\'][^>]+content=["\']([^"\']+)', html or "", re.I)
    if not m:
        m = re.search(r"<title[^>]*>(.*?)</title>", html or "", re.I | re.S)
    return htmllib.unescape(m.group(1).strip()) if m else ""


def title_ok(a: str, b: str) -> bool:
    """次ページのタイトルが前ページと十分似ているか(=同じ記事の続きか)。
    ページ番号マーカー程度の差は類似度で吸収。別記事は落とす。判定不能(空)は許容。"""
    if not a or not b:
        return True
    return difflib.SequenceMatcher(None, a, b).ratio() >= TITLE_SIM_MIN


# 本文がこれ以上似ていたら「同じページの別URL」とみなす。
# 実測: 同一記事の別ページどうし 最大0.920(hominis、関連記事欄が残るため高め) /
#       rollingstone の写真違いURL(本文同一でキャプションだけ違う) 0.994〜1.000
BODY_DUP_MIN = 0.97


def near_duplicate(body: str, bodies: list[str]) -> bool:
    """既に取ったページのどれかと本文がほぼ同じか。先頭200字の一致だけでは、
    写真キャプション等だけ違う同内容ページを見逃すため、本文全体で比べる。"""
    nb = normalize(body)
    for b in bodies:
        sm = difflib.SequenceMatcher(None, nb, normalize(b), autojunk=False)
        if sm.quick_ratio() >= BODY_DUP_MIN and sm.ratio() >= BODY_DUP_MIN:
            return True
    return False


def collect_pages(main_url: str, extra: list[str], no_follow: bool = False):
    """(本文リスト, 辿ったURLリスト, 切れてるかも) を返す。
    重複ページの判定は本文全体の類似度だけで行う。natalie.mu・ginza.jp などは各ページの冒頭に
    同じリード文が付くので、先頭の一致で判定すると2ページ目以降を捨ててしまう。"""
    bodies, urls = [], []

    def take(u: str) -> str | None:
        html = fetch_html(u)
        body = extract_body(html or "")
        if body and not near_duplicate(body, bodies):
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
    cur, last_html, prev_title = main_url, "", None
    for _ in range(MAX_PAGES):
        html = fetch_html(cur)
        body = extract_body(html or "")
        if not body or near_duplicate(body, bodies):
            break
        cur_title = _page_title(html or "")
        # 2ページ目以降: タイトルが前ページと似ていなければ別記事とみなし追加せず終了
        if prev_title is not None and not title_ok(prev_title, cur_title):
            break
        bodies.append(body)
        urls.append(cur)
        prev_title = cur_title
        last_html = html or ""
        nxt = next_page_url(html or "", cur)
        # URLの幹(別スラッグ)とタイトル、両方で「同じ記事の続き」を担保
        if not nxt or nxt == cur or not same_article(cur, nxt):
            break
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


def build_meta_stmts(articles: list[dict]) -> list[str]:
    """タイトル・Feature・媒体名・メンバー名を article_meta_fts に全件入れ直すSQL。
    ・本文(article_fts)とは別テーブル: 本文の末尾とタイトルの頭がくっついて偽の一致が起きないように
    ・1項目1行: タイトルとFeatureの境目をまたいだ偽の一致も起きないように
    ・毎回全件作り直す: 1記事数十文字で軽く、ハッシュ判定が要らない(Featureを直しても本文は再取得しない)
    ・URLが空や"-"の記事(本文を索引できない)も、タイトル等では検索に載る"""
    names: dict[str, list[str]] = {}
    for m in fetch_csv(GID_MEMBERS):
        mid, n = (m.get("ID") or "").strip(), (m.get("Name") or "").strip()
        if mid and n:
            names.setdefault(mid, []).append(n)
    domain = fetch_domain()

    stmts = ["DELETE FROM article_meta_fts;"]
    for a in articles:
        aid = (a.get("ID") or "").strip()
        if not aid:
            continue
        url = (a.get("URL") or "").strip()
        fields = [a.get("Title"), a.get("Feature"), media_for("" if url == "-" else url, domain),
                  *names.get(aid, [])]
        for f in dict.fromkeys((x or "").strip() for x in fields):
            toks = bigram_list(normalize(f))
            if toks:
                stmts.append(f"INSERT INTO article_meta_fts(id,bigram) "
                             f"VALUES({sql_str(aid)},{sql_str(' '.join(toks))});")
    return stmts


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
    skipped: list[tuple[str, str, str]] = []   # (記事ID, 今のページ, 使わなかった rel=next)
    pages_of: dict[str, int] = {}

    for a in articles:
        aid = (a.get("ID") or "").strip()
        url = (a.get("URL") or "").strip()
        if not aid or not url:
            continue
        sheet_ids.add(aid)
        extra_raw = (a.get("ExtraURL") or "").strip()
        arc = (a.get("WebArcive") or "").strip()
        archive_only = url == "-"  # ライブリンク切れ。魚拓だけを本文取得元にする

        # ExtraURL の解釈を含めてハッシュ化(空/"-"/URL群を切り替えたら再索引される)。
        # archive_onlyの間はURLが常に"-"で無意味になるため、代わりにWebArciveをハッシュに使う
        # (魚拓のリンクを差し替えたときにちゃんと再索引されるように)
        hash_key = arc if archive_only else url
        src_hash = hashlib.sha1(("\n".join([INDEX_VERSION, hash_key, extra_raw])).encode("utf-8")).hexdigest()
        # current は "hash|pages"。ハッシュ一致でも pages=0(取得失敗)なら再挑戦する
        cur_hash, _, cur_pages = current.get(aid, "").partition("|")
        if cur_hash == src_hash and cur_pages not in ("", "0"):
            n_skip += 1
            continue  # 変更なし かつ 前回取得済み → 再取得しない

        if archive_only:
            # ライブ取得は試みず、魚拓(web.archive.org/archive.today どちらでも可)から直接本文取得
            # ExtraURL に2ページ目以降の魚拓が書いてあればそれも足す(ID1372 の oricon index2.html など)
            used, parts = [], []
            for u in [arc] + [x for x in re.split(r"[,\s]+", extra_raw) if x.startswith("http")]:
                b = fetch_archive(u) if u else ""
                if b and not near_duplicate(b, parts):
                    used.append(u)
                    parts.append(b)
            body = "\n".join(parts)
            truncated = looks_truncated(body, "")
            if body:
                print(f"  ↳ {aid}: URL=- のため魚拓のみから取得", flush=True)
        else:
            no_follow = extra_raw == "-"
            extra = [] if no_follow else [u for u in re.split(r"[,\s]+", extra_raw) if u]
            bodies, used, truncated = collect_pages(url, extra, no_follow=no_follow)
            body = "\n".join(bodies)
            # ライブが空/極端に短い(ボット弾き・同意壁等)なら魚拓へフォールバックし、長い方を採用
            if len(normalize(body)) < 100 and "web.archive.org/web/" in arc:
                ab = fetch_archive(arc)
                if len(normalize(ab)) > len(normalize(body)):
                    used, truncated, body = [arc], False, ab
                    print(f"  ↳ {aid}: liveが空/短い → 魚拓から取得", flush=True)
        skipped.extend((aid, c, n) for c, n in SKIPPED_NEXT)
        SKIPPED_NEXT.clear()
        pages_of[aid] = len(used)
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

    meta = build_meta_stmts(articles)  # タイトル・Feature等は毎回全件入れ直す
    stmts.extend(meta)

    out = Path(args.out)
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text("\n".join(stmts) + "\n", encoding="utf-8")

    print(f"\n生成: {out}  本文 更新{n_new} / スキップ{n_skip} / 削除{len(removed)}"
          f"  メタデータ {len(meta) - 1}行")
    report_skipped_next(skipped, pages_of, out.with_name("使わなかった次ページ.csv"))


def report_skipped_next(skipped: list[tuple[str, str, str]], pages_of: dict[str, int], out: Path) -> None:
    """rel=next を使わなかった記事をドメインごとにまとめる。
    1ページで終わった記事だけ画面に出す(2ページ以上取れた記事は別の規則で辿れているので、CSVだけに残す)。
    今回取りに行った記事の分だけなので、全記事を見るには --refetch でドメインごと取り直す。"""
    if not skipped:
        return
    with out.open("w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ID", "索引ページ数", "今のページ", "使わなかった rel=next"])
        for aid, cur, nxt in skipped:
            w.writerow([aid, pages_of.get(aid, ""), cur, nxt])
    by_dom: dict[str, list[tuple[str, str, str]]] = {}
    for aid, cur, nxt in skipped:
        if pages_of.get(aid) == 1:
            by_dom.setdefault(_host(urllib.parse.urlparse(cur).netloc), []).append((aid, cur, nxt))
    if by_dom:
        print("\nrel=next があるのに辿らず1ページで終わった記事(知らない形のページ番号かも。別の記事を指すサイトもある):")
        for dom, rows in sorted(by_dom.items(), key=lambda kv: -len(kv[1])):
            aid, cur, nxt = rows[0]
            print(f"  {dom}: {len(rows)}件  例 {aid} {cur} → {nxt}")
    print(f"一覧: {out}")


if __name__ == "__main__":
    main()
