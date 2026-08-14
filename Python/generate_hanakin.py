#!/usr/bin/env python3
"""
宮本佳林のInstagramチャレンジ(HANAKIN / シャニカマー45°)のスプレッドシート
(イベント+コラボ相手IG)を読み取り、PHPインクルード用のテーブル(tbody)
フラグメントを企画ごとに生成する

  イベントシート : ID,Date,Instagram,TikTok,Member1,Member2,Place
                    IDの接頭辞で企画を振り分ける(HK=HANAKIN / SK=シャニカマー45°)。
                   Instagram/TikTokは投稿URL。Member1/2はコラボ相手(0人もあり)。Placeは特別な会場
  相手IGシート   : ID,Member,Instagram
                   ID+Memberでコラボ相手を公式Instagramプロフィールに紐づける(1IDに複数行)

両企画は同じイベント/相手IGシートを共有し、接頭辞で別テーブルに出力する。
出力: Spreadsheet/hanakin_table.php, Spreadsheet/shanikama_table.php
      Generated/hanakin_generated.php(最終更新日。両表で共有)

更新頻度が高くないので手動実行(generate-manual.ymlのhanakin)で回す。
ローカル確認用にCSVパスも渡せる:
  python Python/generate_hanakin.py --events e.csv --members m.csv
"""

import argparse
import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vRQYvAQc0UULvEAkF7k27YBQkPp5LCTGl-NglxoekowjtdWnAYeVvVGkC0XTjT-j4x2WNYEeLqUtnUq/pub"
)
GID_EVENTS = "0"
GID_MEMBERS = "126937742"

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_GENERATED = REPO_ROOT / "Generated" / "hanakin_generated.php"

# IDの接頭辞ごとに別テーブルへ振り分ける(同じイベント/相手IGシートを共有)
CHALLENGES = [
    {"prefix": "HK", "table": REPO_ROOT / "Spreadsheet" / "hanakin_table.php", "place": True},
    {"prefix": "SK", "table": REPO_ROOT / "Spreadsheet" / "shanikama_table.php", "place": False},
]


def fetch_csv(source: str) -> list[dict]:
    path = Path(source)
    if path.exists():
        content = path.read_text(encoding="utf-8-sig")
    else:
        with urllib.request.urlopen(source) as res:
            content = res.read().decode("utf-8")
    return [row for row in csv.DictReader(io.StringIO(content))]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def val(row: dict, key: str) -> str:
    return (row.get(key) or "").strip()


def format_date(s: str) -> str:
    # 年は自明(すべて同年)なので表示は月日のみ。スプレッドシートには年込みで保持する
    for fmt in ("%Y/%m/%d", "%Y-%m-%d", "%Y年%m月%d日"):
        try:
            dt = datetime.strptime((s or "").strip(), fmt)
            return f"{dt.month}月{dt.day}日"
        except ValueError:
            continue
    return esc(s)


def sort_key(s: str) -> str:
    for fmt in ("%Y/%m/%d", "%Y-%m-%d", "%Y年%m月%d日"):
        try:
            return datetime.strptime((s or "").strip(), fmt).strftime("%Y-%m-%d")
        except ValueError:
            continue
    return (s or "").strip()


def link(url: str, label: str) -> str:
    if not url:
        return ""
    return f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">{label}</a>'


# 単色(currentColor)のSVGアイコン。テキストラベルの代わりに使う(サイズはCSSの.sns-icon)
ICON_INSTAGRAM = (
    '<svg class="sns-icon" viewBox="-90 -90 180 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<rect x="-80" y="-80" width="160" height="160" rx="36" fill="none" stroke="currentColor" stroke-width="7"/>'
    '<circle cx="0" cy="0" r="42" fill="none" stroke="currentColor" stroke-width="7"/>'
    '<circle cx="0" cy="0" r="22" fill="none" stroke="currentColor" stroke-width="5"/>'
    '<circle cx="50" cy="-50" r="10" fill="currentColor"/>'
    "</svg>"
)
ICON_TIKTOK = (
    '<svg class="sns-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<path fill="currentColor" d="M16.5 3c.3 2.1 1.7 3.8 3.8 4.1v2.6c-1.2 0-2.4-.4-3.4-1v5.9'
    "c0 3.3-2.7 6-6 6s-6-2.7-6-6 2.7-6 6-6c.3 0 .6 0 .9.1v2.7c-.3-.1-.6-.2-.9-.2-1.8 0-3.3 1.5-3.3 3.3"
    's1.5 3.3 3.3 3.3 3.3-1.4 3.3-3.2V3h2.3z"/>'
    "</svg>"
)


def icon_link(url: str, icon: str, label: str) -> str:
    if not url:
        return ""
    return (
        f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer" '
        f'aria-label="{label}">{icon}</a>'
    )


def build_members(ev: dict, profiles: dict[tuple, str]) -> str:
    eid = val(ev, "ID")
    names = [val(ev, "Member1"), val(ev, "Member2")]
    parts = []
    for name in names:
        if not name:
            continue
        url = profiles.get((eid, name))
        parts.append(link(url, esc(name)) if url else esc(name))
    return "・".join(parts)


def build_rows(events: list[dict], profiles: dict[tuple, str], with_place: bool = True) -> str:
    valid = [e for e in events if val(e, "ID")]
    valid.sort(key=lambda e: sort_key(val(e, "Date")))
    lines = []
    for i, ev in enumerate(valid):
        row_class = ' class="row-even"' if i % 2 else ""
        place_cell = ""
        if with_place:
            place = val(ev, "Place")
            place_cell = f'<td>{"@" + esc(place) if place else ""}</td>'
        lines.append(
            f"<tr{row_class}>"
            f"<td>{format_date(val(ev, 'Date'))}</td>"
            f'<td>{icon_link(val(ev, "Instagram"), ICON_INSTAGRAM, "Instagram")}</td>'
            f'<td>{icon_link(val(ev, "TikTok"), ICON_TIKTOK, "TikTok")}</td>'
            f"<td>{build_members(ev, profiles)}</td>"
            f"{place_cell}"
            "</tr>"
        )
    return "\n".join(lines)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--events", default=f"{PUB_BASE}?gid={GID_EVENTS}&single=true&output=csv")
    parser.add_argument("--members", default=f"{PUB_BASE}?gid={GID_MEMBERS}&single=true&output=csv")
    args = parser.parse_args()

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    events = fetch_csv(args.events)
    members = fetch_csv(args.members)

    profiles = {
        (val(m, "ID"), val(m, "Member")): val(m, "Instagram")
        for m in members
        if val(m, "ID") and val(m, "Member") and val(m, "Instagram")
    }

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    summary = []
    for ch in CHALLENGES:
        subset = [e for e in events if val(e, "ID").startswith(ch["prefix"])]
        ch["table"].parent.mkdir(parents=True, exist_ok=True)
        with open(ch["table"], "w", encoding="utf-8") as f:
            f.write(stamp + build_rows(subset, profiles, ch["place"]) + "\n")
        summary.append(f"{ch['prefix']} {len(subset)}回")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{generated}</div>\n')

    print(f"生成完了: {' / '.join(summary)} / 相手IG {len(profiles)} 件")


if __name__ == "__main__":
    main()
