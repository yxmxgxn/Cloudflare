#!/usr/bin/env python3
"""
キャストマスターのWikipediaページを見て、「映画」の節にあるのにスプレッドシートに無い作品を
Discordに通知する。

- 状態は持たない。直近 WINDOW_HOURS 時間以内に編集されたページだけを調べる
  (毎日実行すれば、同じ編集を2回通知することはほぼ無い)
- 初回の洗い出しは WINDOW_HOURS を大きくして実行する(例: 100000)
- DISCORD_WEBHOOK が未設定なら、通知せず標準出力に出す

使い方: python Python/check_wikipedia_movies.py [--hours 25] [--dry-run]
"""

import argparse
import csv
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

from sheets import fetch_text

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vTMBPXFDKzaVe7pj4NAWBQ4Rs5ABjyosKv5ix21nEwh4AP4EPGOo0c2tYstS803bs4r-8WgOYtv1d2_/pub"
)
GID_MOVIES = "777837729"
GID_CAST_MASTER = "0"

API = "https://ja.wikipedia.org/w/api.php"
UA = "karin-archive-wikipedia-check/1.0 (https://github.com/yxmxgxn/cloudflare)"
BATCH = 50
WAIT_BETWEEN = 3  # 秒。429を避けるため間隔を空ける
WIKI_URL_RE = re.compile(r"https?://ja\.wikipedia\.org/wiki/([^\s?#]+)")


# ---------- スプレッドシート ----------

def fetch_rows(gid: str) -> list[list[str]]:
    return list(csv.reader(io.StringIO(fetch_text(f"{PUB_BASE}?gid={gid}&single=true&output=csv"))))


def load_members(rows: list[list[str]]) -> list[tuple[str, str]]:
    """キャストマスターから (名前, Wikipediaページ名) を作る。URLは行内のセルから探す"""
    header = [h.strip() for h in rows[0]]
    name_idx = next((i for i, h in enumerate(header) if h in ("Member", "名前", "Name", "Cast")), None)
    if name_idx is None:
        name_idx = next((i for i, h in enumerate(header) if h and h != "Cast_id"), 0)
    members = []
    for row in rows[1:]:
        for cell in row:
            m = WIKI_URL_RE.search(cell)
            if m:
                title = urllib.parse.unquote(m.group(1)).replace("_", " ")
                name = row[name_idx].strip() if len(row) > name_idx else ""
                members.append((name or title, title))
                break
    return members


def load_sheet_titles(rows: list[list[str]]) -> set[str]:
    header = [h.strip() for h in rows[0]]
    idx = header.index("Title")
    return {norm(r[idx]) for r in rows[1:] if len(r) > idx and r[idx].strip()}


# ---------- Wikipedia ----------

def api_get(params: dict) -> dict:
    url = API + "?" + urllib.parse.urlencode(params)
    for attempt in range(5):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": UA})
            with urllib.request.urlopen(req, timeout=60) as res:
                return json.load(res)
        except urllib.error.HTTPError as e:
            if e.code not in (429, 500, 502, 503, 504):
                raise
            wait = int(e.headers.get("Retry-After") or 0) or 10 * (attempt + 1)
        except (urllib.error.URLError, TimeoutError):
            wait = 10 * (attempt + 1)
        print(f"Wikipedia取得失敗、{wait}秒後にリトライ ({attempt + 1}/5)", file=sys.stderr)
        time.sleep(wait)
    raise RuntimeError("Wikipedia APIが応答しません")


def fetch_pages(titles: list[str]) -> dict[str, dict]:
    """ページ名 → {timestamp, text, title}。リダイレクトは辿り、元のページ名で引けるようにする"""
    result: dict[str, dict] = {}
    for i in range(0, len(titles), BATCH):
        chunk = titles[i:i + BATCH]
        if i:
            time.sleep(WAIT_BETWEEN)
        data = api_get({
            "action": "query", "format": "json", "formatversion": 2,
            "prop": "revisions", "rvprop": "timestamp|content", "rvslots": "main",
            "redirects": 1, "titles": "|".join(chunk),
        })["query"]
        alias = {}
        for key in ("normalized", "redirects"):
            for r in data.get(key, []):
                alias[r["from"]] = r["to"]
        by_title = {}
        for p in data.get("pages", []):
            if p.get("missing") or not p.get("revisions"):
                continue
            rev = p["revisions"][0]
            by_title[p["title"]] = {
                "title": p["title"],
                "timestamp": rev["timestamp"],
                "text": rev["slots"]["main"]["content"],
            }
        for t in chunk:
            cur = t
            for _ in range(3):  # 正規化→リダイレクトの順に辿る
                cur = alias.get(cur, cur)
            if cur in by_title:
                result[t] = by_title[cur]
    return result


# ---------- 「映画」の節の解析 ----------

HEADING_RE = re.compile(r"^(=+)\s*(.*?)\s*\1\s*$")


def movie_section_lines(wikitext: str) -> list[str]:
    """見出しが「映画」の節(複数あれば全部)の箇条書き行を返す"""
    lines = wikitext.splitlines()
    out, level = [], None
    for ln in lines:
        m = HEADING_RE.match(ln.strip())
        if m:
            lv, text = len(m.group(1)), re.sub(r"<[^>]+>|\[\[|\]\]", "", m.group(2)).strip()
            if level is not None and lv <= level:
                level = None
            if level is None and text == "映画":
                level = lv
            continue
        if level is not None and ln.lstrip().startswith(("*", "#")):
            out.append(ln)
    return out


def clean(s: str) -> str:
    s = re.sub(r"<!--.*?-->", "", s, flags=re.S)
    s = re.sub(r"<ref[^>]*/>|<ref[^>]*>.*?</ref>", "", s, flags=re.S)
    for _ in range(3):
        s = re.sub(r"\{\{[^{}]*\}\}", "", s)
    return s


def unlink(s: str) -> str:
    s = re.sub(r"\[\[([^\]|]*)\|([^\]]*)\]\]", r"\2", s)
    s = re.sub(r"\[\[([^\]]*)\]\]", r"\1", s)
    return re.sub(r"'{2,}", "", s).strip()


def parse_item(line: str):
    """箇条書き1行 → (作品名, 年) 。作品名が取れなければ None"""
    s = clean(line.lstrip("*# ").strip())
    ym = re.search(r"(\d{4})年", s)
    year = ym.group(1) if ym else ""
    m = re.search(r"『(.+?)』", s) or re.search(r"「(.+?)」", s)
    if m:
        return unlink(m.group(1)), year
    for lm in re.finditer(r"\[\[([^\]|]*)(?:\|([^\]]*))?\]\]", s):
        label = lm.group(2) or lm.group(1)
        if not re.fullmatch(r"\d{4}年(\d+月(\d+日)?)?", label.strip()):
            return label.strip(), year
    m = re.search(r"'{2,}(.+?)'{2,}", s)
    if m:
        return m.group(1).strip(), year
    return None


def norm(s: str) -> str:
    s = unicodedata.normalize("NFKC", s).lower()
    s = re.sub(r"[\s・･:：!！?？~〜～\-ー―—'’\"“”「」『』()（）\[\]【】,，.、。/／&＆]", "", s)
    return s


def is_known(title: str, sheet_titles: set[str]) -> bool:
    n = norm(title)
    if not n:
        return True
    return any(n == t or (len(n) >= 3 and n in t) or (len(t) >= 3 and t in n) for t in sheet_titles)


# ---------- Discord ----------

def post_discord(webhook: str, content: str) -> None:
    body = json.dumps({"content": content}).encode()
    req = urllib.request.Request(
        webhook, data=body, headers={"Content-Type": "application/json", "User-Agent": UA}
    )
    for attempt in range(4):
        try:
            urllib.request.urlopen(req, timeout=30).read()
            return
        except urllib.error.HTTPError as e:
            if e.code != 429:
                raise
            time.sleep(int(e.headers.get("Retry-After") or 2) + 1)
    raise RuntimeError("Discord通知に失敗しました")


def chunk_messages(lines: list[str], limit: int = 1900) -> list[str]:
    msgs, cur = [], ""
    for ln in lines:
        if cur and len(cur) + len(ln) + 1 > limit:
            msgs.append(cur)
            cur = ""
        cur += ("\n" if cur else "") + ln
    if cur:
        msgs.append(cur)
    return msgs


# ---------- main ----------

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--hours", type=float, default=25, help="この時間以内に編集されたページだけ調べる")
    ap.add_argument("--dry-run", action="store_true", help="Discordに送らず標準出力に出す")
    args = ap.parse_args()
    webhook = os.environ.get("DISCORD_WEBHOOK", "").strip()

    members = load_members(fetch_rows(GID_CAST_MASTER))
    sheet_titles = load_sheet_titles(fetch_rows(GID_MOVIES))
    print(f"メンバー {len(members)}人 / シート上の作品 {len(sheet_titles)}件")

    pages = fetch_pages(sorted({t for _, t in members}))
    since = datetime.now(timezone.utc) - timedelta(hours=args.hours)
    findings, no_section, missing = [], [], []

    for name, title in members:
        page = pages.get(title)
        if not page:
            missing.append(f"{name}({title})")
            continue
        ts = datetime.fromisoformat(page["timestamp"].replace("Z", "+00:00"))
        if ts < since:
            continue
        items = [parse_item(ln) for ln in movie_section_lines(page["text"])]
        items = [it for it in items if it]
        if not items:
            no_section.append(name)
            continue
        url = "https://ja.wikipedia.org/wiki/" + urllib.parse.quote(page["title"].replace(" ", "_"), safe="_-.~()!*',:")
        seen = set()
        for work, year in items:
            if work in seen or is_known(work, sheet_titles):
                continue
            seen.add(work)
            findings.append(f"**{name}**: {work}" + (f" ({year}年)" if year else "") + f"\n<{url}#映画>")

    print(f"ページ取得できず: {len(missing)}件 {missing}")
    print(f"期間内に編集されたが映画の節が読めない: {len(no_section)}件 {no_section}")
    print(f"スプレッドシートに無い作品: {len(findings)}件")
    if not findings:
        return 0
    lines = ["🎬 Wikipediaの「映画」の節に、スプレッドシートに無い作品があります"] + findings
    for msg in chunk_messages(lines):
        if args.dry_run or not webhook:
            print("----\n" + msg)
        else:
            post_discord(webhook, msg)
            time.sleep(1)
    return 0


if __name__ == "__main__":
    sys.exit(main())
