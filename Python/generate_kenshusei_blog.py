#!/usr/bin/env python3
"""
ハロプロ研修生ブログ(upfc.jp の無料コンテンツ)の一覧を読み、記事の目次を data/kenshusei-blog.json に書き出す。

保存するのは 記事ID・日付・書いた人 だけ。本文と写真は読まないし保存もしない。
upfc.jp は「当サイトに掲載されているすべてのコンテンツ」の無断複製を禁じているため
(https://www.upfc.jp/helloproject/information.php)、目次以上のものは持たない。
記事ページも開かない(一覧だけで ID・日付・書いた人が取れる)。

ふだんは新しい方から読み、全部が取り込み済みのページに当たったら止める。
--full のときは最後のページまで読み直し、一覧から消えた記事を落とす(週1回)。
"""

import argparse
import html
import json
import re
import sys
import time
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn

REPO_ROOT = Path(__file__).resolve().parent.parent
OUTPUT = REPO_ROOT / "data" / "kenshusei-blog.json"

LIST_URL = "https://www.upfc.jp/helloproject/artist/trcontents_list.php?@pg={}&@tbl=contents&@uid=KENSYUSEI"
UA = "karin-archive (+https://karin-archive.com/hello-kenshusei-blog)"
DELAY = 1.0          # リクエストの間隔(秒)。並列にはしない
MAX_PAGES = 200      # 念のための上限(2026-11 時点で78ページ)
JST = timezone(timedelta(hours=9))

ITEM = re.compile(
    r'<li class="contents_list_box">.*?trcontents_detail\.php\?[^"]*?@uid=([A-Za-z0-9]+)"'
    r'.*?<h4>(.*?)</h4>.*?<time>(\d{4})\.(\d{2})\.(\d{2})</time>\s*(.*?)\s*</h3>',
    re.S,
)

# Google で探す欄の予測変換に足す、ハロプロの現役メンバーとグループ(ハロプロ アメブロ検索の名簿から)
AMEBLO_MEMBERS = "https://ameblo.karin-archive.com/api/members"

_last = 0.0


def suggest(old: list[str]) -> list[str]:
    try:
        req = urllib.request.Request(AMEBLO_MEMBERS, headers={"User-Agent": UA})
        with urllib.request.urlopen(req, timeout=30) as r:
            d = json.load(r)
    except Exception as e:  # noqa: BLE001 取れなければ前回のまま
        print(f"  ! アメブロ検索の名簿が読めない {type(e).__name__}: {e}")
        return old
    act = [m for m in d.get("members", []) if m.get("active")]
    used = {g for m in act for g in m.get("groups", [])}   # 現役のいるグループだけ
    return [m["name"] for m in act] + [g["name"] for g in d.get("groups", []) if g["no"] in used]


def fetch(page: int) -> str:
    global _last
    wait = _last + DELAY - time.time()
    if wait > 0:
        time.sleep(wait)
    for n in range(3):
        _last = time.time()
        try:
            req = urllib.request.Request(LIST_URL.format(page), headers={"User-Agent": UA})
            with urllib.request.urlopen(req, timeout=30) as r:
                return r.read().decode("utf-8", "replace")
        except Exception as e:  # noqa: BLE001 一時的な失敗は数回やり直す
            print(f"  ! {page}ページ目 {type(e).__name__}: {e}")
            time.sleep(5 * (n + 1))
    raise RuntimeError(f"{page}ページ目が読めない")


def parse(page_html: str) -> list[dict]:
    out = []
    # シリーズ名(h4)は2026-11時点で全部「Hello! Project 研修生リハーサル日記」なので持たない
    for uid, _series, y, m, d, name in ITEM.findall(page_html):
        out.append({
            "id": uid,
            "date": f"{y}-{m}-{d}",
            "name": html.unescape(re.sub(r"\s+", " ", name)).strip(),
        })
    return out


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--full", action="store_true", help="最後のページまで読み直し、消えた記事を落とす")
    a = ap.parse_args()

    old, prev = [], {}
    if OUTPUT.exists():
        prev = json.loads(OUTPUT.read_text(encoding="utf-8"))
        old = prev.get("entries", [])
    known = {e["id"] for e in old}
    full = a.full or not old   # 初回は全部読む

    fresh = []   # 今回読んだ記事(新しい順)
    for page in range(1, MAX_PAGES + 1):
        items = parse(fetch(page))
        if not items:
            break   # 最後のページより先は空になる
        fresh += items
        if not full and all(e["id"] in known for e in items):
            break   # ここから先は取り込み済み
    else:
        warn(f"{MAX_PAGES}ページを超えた。上限を見直す", title="研修生ブログ")

    if full:
        entries = fresh
        gone = known - {e["id"] for e in fresh}
        if gone:
            print(f"一覧から消えた記事: {len(gone)}件")
    else:
        seen = {e["id"] for e in fresh}
        entries = fresh + [e for e in old if e["id"] not in seen]

    # 一覧の並び(新しい順)を保ったまま、日付でも新しい順にそろえる
    entries.sort(key=lambda e: e["date"], reverse=True)
    added = len({e["id"] for e in entries} - known)

    if not entries:
        warn("記事が1件も取れなかった。一覧ページの作りが変わったかもしれない", title="研修生ブログ")
        return 1

    data = {
        "updated": datetime.now(JST).strftime("%Y-%m-%d %H:%M"),
        "suggest": suggest(prev.get("suggest", [])),
        "entries": entries,
    }
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    text = json.dumps(data, ensure_ascii=False, separators=(",", ":"))
    if old and prev.get("entries") == entries and prev.get("suggest") == data["suggest"]:
        print(f"変更なし({len(entries)}件)")
        return 0
    OUTPUT.write_text(text + "\n", encoding="utf-8")
    print(f"{len(entries)}件(新しく {added}件){' 全件読み直し' if full else ''}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
