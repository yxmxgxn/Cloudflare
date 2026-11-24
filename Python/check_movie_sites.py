#!/usr/bin/env python3
"""
映画サイト(Filmarks / 映画.com / TMDb)の出演作一覧と、スプレッドシートの作品一覧を突き合わせ、
シートに無い作品をDiscordに通知する。

- メンバーごとのIDはキャストマスターの filmarks_id / eiga_com_id / tmdb_id 列、
  シート側の作品IDは作品一覧の filmarks / eiga_com / tmdb / all_cinemas 列を使う
- 作品IDがどれか1つでもシートにあれば登録済み。IDが無い作品用に、作品名の一致も見る
- 同じ作品が複数サイトで見つかったら1件にまとめ、載っていたサイトを併記する
- 通知済みの作品は Python/state/movie-check-notified.json に残し、次回以降は通知しない
  (シートに足したくない作品も、ここに入っていれば通知されない)
- 通知には、スプレッドシートに貼れる行(作品一覧用 movies.tsv・キャスト一覧用 cast.tsv)を添付する。
  サイトごとのID・公開日・監督・役名(映画.com)・TMDbのIDを、わかる範囲で埋めてある
- DISCORD_WEBHOOK が未設定、または --dry-run のときは、通知せず標準出力に出す(状態も書かない)
- TMDB_API_KEY(APIリードアクセストークンでもAPIキーでも可)が未設定なら TMDb は飛ばす

使い方: python Python/check_movie_sites.py [--dry-run] [--sites filmarks,eiga,tmdb] [--limit N]
"""

import argparse
import csv
import html
import io
import json
import os
import re
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timedelta, timezone
from pathlib import Path

from sheets import fetch_text

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vTMBPXFDKzaVe7pj4NAWBQ4Rs5ABjyosKv5ix21nEwh4AP4EPGOo0c2tYstS803bs4r-8WgOYtv1d2_/pub"
)
GID_MOVIES = "777837729"
GID_CAST = "389226267"  # キャスト一覧(作品ごとの出演者)
GID_CAST_MASTER = "0"

REPO_ROOT = Path(__file__).resolve().parent.parent
STATE_PATH = REPO_ROOT / "Python" / "state" / "movie-check-notified.json"

UA = "Mozilla/5.0 (compatible; karin-archive-movie-check/1.0; +https://karin-archive.com/)"
WAIT_HTML = 2.0   # Filmarks・映画.com は1リクエストごとに空ける秒数
WAIT_API = 0.3    # TMDb API
MAX_PAGES = 8     # 1人あたりの一覧ページ数の上限(念のため)

SITE_LABEL = {"filmarks": "Filmarks", "eiga": "映画.com", "tmdb": "TMDb"}
# 作品ページのURL(通知にそのまま載せる)
SITE_URL = {
    "filmarks": "https://filmarks.com/movies/{id}",
    "eiga": "https://eiga.com/movie/{id}/",
    "tmdb": "https://www.themoviedb.org/movie/{id}?language=ja-JP",
}
# シートの作品一覧の列名
SHEET_COL = {"filmarks": "filmarks", "eiga": "eiga_com", "tmdb": "tmdb"}
# キャストマスターの列名
CAST_COL = {"filmarks": "filmarks_id", "eiga": "eiga_com_id", "tmdb": "tmdb_id"}


class Blocked(Exception):
    """サイトに拒否された(403/429が続いた)。そのサイトの取得はそこで止める"""


# ---------- スプレッドシート ----------

def fetch_rows(gid: str) -> list[dict]:
    text = fetch_text(f"{PUB_BASE}?gid={gid}&single=true&output=csv")
    return list(csv.DictReader(io.StringIO(text)))


def norm(s: str) -> str:
    s = unicodedata.normalize("NFKC", s).lower()
    return re.sub(r"[\s・:：!！?？~〜～\-ー―—'’\"“”「」『』()（）\[\]【】,，.、。/／&＆]", "", s)


def load_sheet(movies: list[dict]):
    """シートの作品IDの集合(サイト別)と、正規化した作品名の集合を返す"""
    ids = {site: set() for site in SITE_LABEL}
    titles = set()
    for m in movies:
        for site, col in SHEET_COL.items():
            v = (m.get(col) or "").strip()
            if v:
                ids[site].add(v)
        t = norm(m.get("Title") or "")
        if t:
            titles.add(t)
    return ids, titles


def title_known(title: str, sheet_titles: set[str]) -> bool:
    """作品名がシートのどれかと(ほぼ)同じか。短い名前の部分一致は誤爆するので3文字以上だけ"""
    n = norm(title)
    if not n:
        return True
    return any(n == t or (len(n) >= 3 and n in t) or (len(t) >= 3 and t in n) for t in sheet_titles)


# ---------- 取得 ----------

def http_get(url: str, headers: dict | None = None) -> str:
    hdr = {"User-Agent": UA, "Accept-Language": "ja"}
    hdr.update(headers or {})
    waits = [10, 30, 60]
    for attempt in range(len(waits) + 1):
        try:
            req = urllib.request.Request(url, headers=hdr)
            with urllib.request.urlopen(req, timeout=60) as res:
                return res.read().decode("utf-8", "replace")
        except urllib.error.HTTPError as e:
            if e.code in (403, 429):
                if attempt == len(waits):
                    raise Blocked(f"{e.code} {url}")
            elif e.code in (500, 502, 503, 504):
                if attempt == len(waits):
                    raise
            else:
                raise
        except (urllib.error.URLError, TimeoutError):
            if attempt == len(waits):
                raise
        time.sleep(waits[attempt])
    raise RuntimeError("unreachable")


def year_of(s: str) -> str:
    m = re.search(r"(\d{4})", s or "")
    return m.group(1) if m else ""


def filmarks_works(pid: str) -> list[dict]:
    """Filmarksの人物ページ(映画)。ドラマは /dramas なので入らない"""
    works: dict[str, dict] = {}
    for page in range(1, MAX_PAGES + 1):
        h = http_get(f"https://filmarks.com/people/{pid}/movies?page={page}")
        time.sleep(WAIT_HTML)
        new = 0
        for chunk in h.split('class="js-cassette"')[1:]:
            m = re.search(r"movie_id&quot;:(\d+)", chunk)
            if not m or m.group(1) in works:
                continue
            flat = re.sub(r"\|+", "|", re.sub(r"<[^>]+>", "|", chunk[:6000]))
            flat = html.unescape(flat)
            t = re.search(r"\{\{ viewingClipCount \}\}\|([^|]+)\|", flat)
            y = re.search(r"上映日：\|(\d{4})年(\d{2})月(\d{2})日", flat)
            y1 = y or re.search(r"上映日：\|(\d{4})年", flat)
            d = re.search(r"監督\|([^|]+)\|", flat)
            works[m.group(1)] = {
                "id": m.group(1),
                "title": (t.group(1) if t else "").strip(),
                "year": y1.group(1) if y1 else "",
                "date": f"{y.group(1)}/{y.group(2)}/{y.group(3)}" if y else "",
                "director": d.group(1).strip() if d else "",
            }
            new += 1
        if new == 0:
            break
    return list(works.values())


def eiga_works(pid: str) -> list[dict]:
    """映画.comの人物の関連作品(映画)"""
    works: dict[str, dict] = {}
    for page in range(1, MAX_PAGES + 1):
        url = f"https://eiga.com/person/{pid}/movie/" + (f"?page={page}" if page > 1 else "")
        try:
            h = http_get(url)
        except urllib.error.HTTPError as e:
            # 映画の出演作が1件も無い人は、人物ページがあっても /movie/ が404になる
            if e.code == 404 and page == 1:
                time.sleep(WAIT_HTML)
                return []
            raise
        time.sleep(WAIT_HTML)
        new = 0
        for li in re.findall(r"<li class=\"col-s-4\">(.*?)</li>", h, re.S):
            m = re.search(r'href="/movie/(\d+)/?"', li)
            t = re.search(r'<p class="title">(.*?)</p>', li, re.S)
            if not m or not t or m.group(1) in works:
                continue
            y = re.search(r"(\d{4})年公開", li)
            r = re.search(r'<p class="role-name">(.*?)</p>', li, re.S)
            works[m.group(1)] = {
                "id": m.group(1),
                "title": html.unescape(re.sub(r"<[^>]+>", "", t.group(1))).strip(),
                "year": y.group(1) if y else "",
                "role": html.unescape(re.sub(r"<[^>]+>", "", r.group(1))).strip() if r else "",
            }
            new += 1
        if new == 0:
            break
    return list(works.values())


def tmdb_date(s: str | None) -> str:
    """2021-03-12 → 2021/03/12(シートの日付の形)。日付が揃っていなければ空"""
    return s.replace("-", "/") if s and re.fullmatch(r"\d{4}-\d{2}-\d{2}", s) else ""


def tmdb_headers(token: str) -> tuple[dict, str]:
    """(ヘッダー, URLに足す認証パラメータ)。JWTのトークンならヘッダー、APIキーならURLに付ける"""
    if token.startswith("eyJ"):
        return {"Accept": "application/json", "Authorization": f"Bearer {token}"}, ""
    return {"Accept": "application/json"}, f"&api_key={urllib.parse.quote(token)}"


def tmdb_search(title: str, year: str, token: str) -> dict | None:
    """作品名と年から、TMDbの作品を1件探す。名前が(ほぼ)同じで、公開年が1年以内のものだけ"""
    headers, auth = tmdb_headers(token)
    url = ("https://api.themoviedb.org/3/search/movie?language=ja-JP&include_adult=false"
           f"&query={urllib.parse.quote(title)}{auth}")
    data = json.loads(http_get(url, headers))
    time.sleep(WAIT_API)
    n = norm(title)

    def year_ok(r: dict) -> bool:
        ry = year_of(r.get("release_date") or "")
        return not year or not ry or abs(int(ry) - int(year)) <= 1

    results = data.get("results", [])[:5]
    # まず名前が完全に同じもの。次に、付け足しだけの違い(「劇場版」など3文字以上)を許す。
    # 「…」と「…2」のように1〜2文字の差は続編の可能性が高いので、同じ作品とみなさない
    for r in results:
        if year_ok(r) and n in {norm(r.get("title") or ""), norm(r.get("original_title") or "")}:
            return r
    for r in results:
        for x in {norm(r.get("title") or ""), norm(r.get("original_title") or "")}:
            if year_ok(r) and x and min(len(x), len(n)) >= 5 and (x in n or n in x) and abs(len(x) - len(n)) >= 3:
                return r
    return None


SELF_RE = re.compile(r"\b(self|herself|himself|themselves)\b|本人", re.I)


def tmdb_works(pid: str, token: str) -> list[dict]:
    """TMDbの出演映画(ドラマは tv_credits なので入らない)。本人役での出演は除く"""
    url = f"https://api.themoviedb.org/3/person/{pid}/movie_credits?language=ja-JP"
    headers, auth = tmdb_headers(token)
    data = json.loads(http_get(url + auth, headers))
    time.sleep(WAIT_API)
    works = {}
    for c in data.get("cast", []):
        if SELF_RE.search(c.get("character") or ""):
            continue
        works[str(c["id"])] = {
            "id": str(c["id"]),
            "title": (c.get("title") or c.get("original_title") or "").strip(),
            "year": year_of(c.get("release_date") or ""),
            "date": tmdb_date(c.get("release_date")),
            # 絞り込みの調整用(映画以外の映像作品を見分ける材料)
            "tmdb": {
                "video": bool(c.get("video")),
                "votes": c.get("vote_count") or 0,
                "pop": round(c.get("popularity") or 0, 1),
                "genres": c.get("genre_ids") or [],
                "char": (c.get("character") or "").strip(),
            },
        }
    return list(works.values())


# ---------- 突き合わせ ----------

def tmdb_plausible(info: dict | None) -> bool:
    """TMDbだけにある作品が、映画らしいか。ビデオ作品・音楽(コンサート)・ドキュメンタリー・
    テレビ映画のジャンル、ジャンル無し、評価数0は外す(実データで見ると、これらが大半だった)"""
    if not info:
        return False
    return (
        not info["video"]
        and bool(info["genres"])
        and not ({10402, 99, 10770} & set(info["genres"]))
        and info["votes"] >= 1
    )


class Candidate:
    def __init__(self, title: str, year: str):
        self.title = title
        self.year = year
        self.keys: dict[str, str] = {}      # サイト → 作品ID
        self.members: list[str] = []
        self.tmdb_info: dict | None = None
        self.date = ""                       # 2021/03/12 の形。年しか分からなければ空
        self.director = ""
        self.roles: dict[str, str] = {}      # メンバー → 役名(映画.comから)

    def add(self, site: str, work: dict, member: str) -> None:
        self.keys.setdefault(site, work["id"])
        if site == "tmdb":
            self.tmdb_info = work.get("tmdb")
        if member not in self.members:
            self.members.append(member)
        if work.get("date") and len(work["date"]) > len(self.date):
            self.date = work["date"]
        if work.get("director") and not self.director:
            self.director = work["director"]
        if work.get("role"):
            self.roles[member] = work["role"]
        if not self.year and work["year"]:
            self.year = work["year"]
        # 日本語の作品名はFilmarks・映画.comのほうが確実なので、そちらを優先して残す
        if site in ("filmarks", "eiga") and work["title"]:
            self.title = work["title"]


def find_candidate(cands: list[Candidate], site: str, work: dict) -> Candidate | None:
    for c in cands:
        if c.keys.get(site) == work["id"]:
            return c
    n = norm(work["title"])
    if not n:
        return None
    for c in cands:
        if site in c.keys:  # 同じサイトの別IDは別作品
            continue
        t = norm(c.title)
        # 「打姫オバカミーコ」と「劇場版・打姫オバカミーコ」のような付け足しは同じ作品とみなす
        same = t == n or (min(len(t), len(n)) >= 5 and (t in n or n in t))
        near = not c.year or not work["year"] or abs(int(c.year) - int(work["year"])) <= 1
        if same and near:
            return c
    return None


def state_key(c: Candidate) -> list[str]:
    return [f"{s}:{i}" for s, i in c.keys.items()]


def load_state() -> dict:
    try:
        return json.loads(STATE_PATH.read_text(encoding="utf-8"))
    except FileNotFoundError:
        return {}


def save_state(state: dict) -> None:
    STATE_PATH.parent.mkdir(parents=True, exist_ok=True)
    STATE_PATH.write_text(json.dumps(state, ensure_ascii=False, indent=1, sort_keys=True) + "\n", encoding="utf-8")


# ---------- Discord ----------

def post_discord(webhook: str, content: str) -> None:
    body = json.dumps({"content": content}).encode()
    req = urllib.request.Request(
        webhook, data=body, headers={"Content-Type": "application/json", "User-Agent": UA}
    )
    for _ in range(4):
        try:
            urllib.request.urlopen(req, timeout=30).read()
            return
        except urllib.error.HTTPError as e:
            if e.code != 429:
                raise
            time.sleep(int(e.headers.get("Retry-After") or 2) + 1)
    raise RuntimeError("Discord通知に失敗しました")


def post_discord_files(webhook: str, content: str, files: dict[str, str]) -> None:
    """ファイルを添付して送る(multipart)。files: ファイル名 → 中身(UTF-8のテキスト)"""
    boundary = "----karinarchive" + str(int(time.time() * 1000))
    payload = {"content": content, "attachments": [{"id": i, "filename": n} for i, n in enumerate(files)]}
    body = (
        f'--{boundary}\r\nContent-Disposition: form-data; name="payload_json"\r\n'
        f"Content-Type: application/json\r\n\r\n{json.dumps(payload, ensure_ascii=False)}\r\n"
    ).encode()
    for i, (name, text) in enumerate(files.items()):
        body += (
            f'--{boundary}\r\nContent-Disposition: form-data; name="files[{i}]"; filename="{name}"\r\n'
            "Content-Type: text/tab-separated-values; charset=utf-8\r\n\r\n"
        ).encode() + text.encode("utf-8") + b"\r\n"
    body += f"--{boundary}--\r\n".encode()
    req = urllib.request.Request(webhook, data=body, headers={
        "Content-Type": f"multipart/form-data; boundary={boundary}", "User-Agent": UA})
    for _ in range(4):
        try:
            urllib.request.urlopen(req, timeout=60).read()
            return
        except urllib.error.HTTPError as e:
            if e.code != 429:
                raise
            time.sleep(int(e.headers.get("Retry-After") or 2) + 1)
    raise RuntimeError("Discordへのファイル送信に失敗しました")


def next_movie_number(movie_rows: list[dict]) -> int:
    nums = [int(m.group(1)) for r in movie_rows if (m := re.fullmatch(r"M(\d+)", (r.get("Movie_id") or "").strip()))]
    return max(nums, default=0) + 1


def tsv_line(header: list[str], values: dict) -> str:
    """ヘッダーの列順に並べた1行。タブ・改行はセルを壊すので空白にする"""
    return "\t".join(re.sub(r"[\t\r\n]+", " ", str(values.get(h, ""))) for h in header)


def build_paste(cands: list[Candidate], movie_header: list[str], cast_header: list[str],
                first_no: int, cast_ids: dict[str, str]) -> tuple[str, str]:
    """シートの作品一覧・キャスト一覧に、ヘッダー無しで貼れる行を作る"""
    movie_lines, cast_lines = [], []
    for n, c in enumerate(cands, start=first_no):
        mid = f"M{n:03d}"
        date = c.date or c.year
        movie = {"Movie_id": mid, "Title": c.title, "Date": date, "Director": c.director}
        for site, col in SHEET_COL.items():
            movie[col] = c.keys.get(site, "")
        movie_lines.append(tsv_line(movie_header, movie))
        for i, member in enumerate(c.members, start=1):
            cast_lines.append(tsv_line(cast_header, {
                "Movie_id": mid, "CastSort_id": f"S{i:02d}", "Date": date,
                "Cast_id": cast_ids.get(member, ""), "Member": member, "Title": c.title,
                "Role_name": c.roles.get(member, ""),
            }))
    return "\n".join(movie_lines) + "\n", "\n".join(cast_lines) + "\n"


def chunk_messages(blocks: list[str], head: str, limit: int = 1900) -> list[str]:
    msgs, cur = [], head
    for b in blocks:
        if len(cur) + len(b) + 1 > limit:
            msgs.append(cur)
            cur = ""
        cur += ("\n" if cur else "") + b
    if cur:
        msgs.append(cur)
    return msgs


def format_candidate(c: Candidate) -> str:
    sites = "・".join(SITE_LABEL[s] for s in c.keys)
    year = f" ({c.year}年)" if c.year else ""
    links = " ".join(f"<{SITE_URL[s].format(id=i)}>" for s, i in c.keys.items())
    return f"**{c.title}**{year} — {'、'.join(c.members)}\n　{sites} {links}"


# ---------- main ----------

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true", help="Discordに送らず、状態も書かない")
    ap.add_argument("--sites", default="filmarks,eiga,tmdb", help="調べるサイト(カンマ区切り)")
    ap.add_argument("--limit", type=int, default=0, help="先頭からN人だけ調べる(動作確認用)")
    ap.add_argument("--debug-tmdb", action="store_true",
                    help="TMDbだけの作品を、絞り込み用の項目つきで標準出力に出す(通知はしない)")
    args = ap.parse_args()
    sites = [s for s in args.sites.split(",") if s in SITE_LABEL]
    webhook = os.environ.get("DISCORD_WEBHOOK", "").strip()
    tmdb_token = os.environ.get("TMDB_API_KEY", "").strip()
    if "tmdb" in sites and not tmdb_token:
        print("TMDB_API_KEY が未設定なので TMDb は飛ばします")
        sites.remove("tmdb")

    cast = fetch_rows(GID_CAST_MASTER)
    if args.limit:
        cast = cast[:args.limit]
    movie_rows = fetch_rows(GID_MOVIES)
    sheet_ids, sheet_titles = load_sheet(movie_rows)
    print(f"メンバー {len(cast)}人 / シート上の作品 {len(sheet_titles)}件 / 調べるサイト {sites}")

    cands: list[Candidate] = []
    blocked: dict[str, str] = {}
    failed: dict[str, list[str]] = {s: [] for s in sites}
    fetched = {s: 0 for s in sites}

    for site in sites:
        col = CAST_COL[site]
        for row in cast:
            pid = (row.get(col) or "").strip()
            name = (row.get("Name") or "").strip()
            if not pid or not name:
                continue
            try:
                if site == "filmarks":
                    works = filmarks_works(pid)
                elif site == "eiga":
                    works = eiga_works(pid)
                else:
                    works = tmdb_works(pid, tmdb_token)
            except Blocked as e:
                blocked[site] = str(e)
                print(f"{SITE_LABEL[site]}に拒否されたので、ここで止めます: {e}", file=sys.stderr)
                break
            except Exception as e:  # 1人の失敗で全体を止めない
                failed[site].append(name)
                print(f"{SITE_LABEL[site]} {name}: 取得失敗 {e}", file=sys.stderr)
                continue
            fetched[site] += 1
            for w in works:
                if not w["title"] or w["id"] in sheet_ids[site] or title_known(w["title"], sheet_titles):
                    continue
                c = find_candidate(cands, site, w)
                if c is None:
                    c = Candidate(w["title"], w["year"])
                    cands.append(c)
                c.add(site, w, name)
        print(f"{SITE_LABEL[site]}: {fetched[site]}人分を取得")

    state = load_state()
    new = [c for c in cands if not any(k in state for k in state_key(c))]
    # 複数サイトに載っている作品を先に。TMDbだけの作品は別枠にする(ドラマ・本人出演などの混入が多いため)
    solid = sorted((c for c in new if set(c.keys) != {"tmdb"}), key=lambda c: (-len(c.keys), c.title))
    tmdb_only_all = sorted((c for c in new if set(c.keys) == {"tmdb"}), key=lambda c: c.title)
    # TMDbの出演一覧にはFCイベント・DVD・コンサート映像などが大量に入っているので、映画らしいものだけ残す
    tmdb_only = [c for c in tmdb_only_all if tmdb_plausible(c.tmdb_info)]
    print(f"シートに無い作品: {len(cands)}件 / 通知済みを除いて {len(new)}件"
          f"(TMDbのみ {len(tmdb_only_all)}件のうち、映画らしい {len(tmdb_only)}件)")

    if args.debug_tmdb:
        # 評価数の多い順。BOTH=他のサイトにも載っている(=確かに映画)、ONLY=TMDbだけ
        for kind, group in (("BOTH", [c for c in solid if "tmdb" in c.keys]), ("ONLY", tmdb_only_all)):
            for c in sorted(group, key=lambda c: -(c.tmdb_info or {}).get("votes", 0)):
                i = c.tmdb_info or {}
                print(f"DEBUG-{kind}\tvotes={i.get('votes')}\tpop={i.get('pop')}\tvideo={i.get('video')}"
                      f"\tgenres={i.get('genres')}\t{c.year}\t{c.title}\t役={i.get('char')}\t{'、'.join(c.members[:3])}")
        return 0

    notes = []
    for site, why in blocked.items():
        notes.append(f"⚠️ {SITE_LABEL[site]}に拒否されたため、一部を取得できていません")
    for site, names in failed.items():
        if names:
            notes.append(f"⚠️ {SITE_LABEL[site]}で取得に失敗: {'、'.join(names[:10])}" + (" ほか" if len(names) > 10 else ""))

    if not new and not notes:
        return 0

    reported = solid + tmdb_only
    if tmdb_token and not args.limit:
        # TMDbのIDが分からない作品は、作品名と年で探して埋める(シートの tmdb 列用)
        for c in reported:
            if "tmdb" in c.keys:
                continue
            try:
                r = tmdb_search(c.title, c.year, tmdb_token)
            except Exception as e:
                print(f"TMDb検索に失敗: {c.title} {e}", file=sys.stderr)
                continue
            if r:
                c.keys["tmdb"] = str(r["id"])
                c.date = c.date or tmdb_date(r.get("release_date"))

    blocks = []
    if solid:
        blocks.append("**▼ シートに無い作品**")
        blocks += [format_candidate(c) for c in solid]
    if tmdb_only:
        blocks.append("**▼ TMDbだけにある作品**(ドラマの劇場版・本人出演などが混ざることがあります)")
        blocks += [format_candidate(c) for c in tmdb_only]
    blocks += notes
    messages = chunk_messages(blocks, "🎬 映画サイトに載っていて、スプレッドシートに無い作品があります")

    paste = None
    paste_note = ""
    if reported:
        movie_header = [h for h in movie_rows[0].keys() if h is not None]
        cast_header = [h for h in fetch_rows(GID_CAST)[0].keys() if h is not None]
        cast_ids = {(r.get("Name") or "").strip(): (r.get("Cast_id") or "").strip() for r in cast}
        first_no = next_movie_number(movie_rows)
        paste = build_paste(reported, movie_header, cast_header, first_no, cast_ids)
        paste_note = (
            f"📋 スプレッドシートに貼る行です(ヘッダー無し)。作品IDは M{first_no:03d} から振っています。\n"
            "movies.tsv → 作品一覧の末尾、cast.tsv → キャスト一覧の末尾に貼ってください。"
            "ジャンル・監督・役名などは、貼ったあとに埋めてください。"
            "先にシートへ作品を足した場合は、作品IDが重なるので振り直してください。"
        )

    if args.dry_run or not webhook:
        for m in messages:
            print("----\n" + m)
        if paste:
            print("---- movies.tsv\n" + paste[0] + "---- cast.tsv\n" + paste[1])
        return 0

    for m in messages:
        post_discord(webhook, m)
        time.sleep(1)
    if paste:
        post_discord_files(webhook, paste_note, {"movies.tsv": paste[0], "cast.tsv": paste[1]})
    today = datetime.now(timezone(timedelta(hours=9))).strftime("%Y-%m-%d")
    for c in solid + tmdb_only:  # 通知したものだけ記録する(絞り込みで外したものは、条件を変えたとき拾えるように残す)
        for k in state_key(c):
            state[k] = f"{today} {c.title}"
    save_state(state)
    return 0


if __name__ == "__main__":
    sys.exit(main())
