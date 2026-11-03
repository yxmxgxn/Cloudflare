#!/usr/bin/env python3
"""
宮本佳林のInstagramチャレンジ(HANAKIN / シャニカマー45°)のスプレッドシート
(イベント+コラボ相手IG)を読み取り、PHPインクルード用のテーブル(tbody)
フラグメントを企画ごとに生成する

  イベントシート : ID,Date,Instagram,TikTok,Member1,Member2
                    IDの接頭辞で企画を振り分ける(HK=HANAKIN / SK=シャニカマー45°)。
                   Instagram/TikTokは投稿URL。Member1/2はコラボ相手(0人もあり)。場所だけの回や「織田信長@岐阜駅」のような
                   特殊な回は、相手IGシートに無い名前としてMember1にそのまま書く(リンク・グループなしで出る)
  相手IGシート   : ID,Member,Instagram,Group
                   ID+Memberでコラボ相手を公式Instagram・所属グループに紐づける(1IDに複数行)
                   コラボ相手列は「名前（グループ）」を1つにまとめる(名前部分がIGにリンク)

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

from icons import icon

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
    {"prefix": "HK", "table": REPO_ROOT / "Spreadsheet" / "hanakin_table.php"},
    {"prefix": "SK", "table": REPO_ROOT / "Spreadsheet" / "shanikama_table.php"},
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


# アイコンは common/icons.json が定義元(Python/icons.py 経由)。
# テキストラベルの代わりに使う(サイズはCSSの .ic / .sns-icon)
ICON_INSTAGRAM = icon("instagram", "ic sns-icon")
ICON_TIKTOK = icon("tiktok", "ic sns-icon")


def icon_link(url: str, icon: str, label: str) -> str:
    if not url:
        return ""
    return (
        f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer" '
        f'aria-label="{label}">{icon}</a>'
    )


def build_member_cell(ev: dict, profiles: dict[tuple, dict]) -> str:
    """コラボ相手セル(名前+グループを1つにまとめる)を返す。
    名前は相手IGへのリンク。グループは名前の後ろに（）で付ける。
      1人                    : 名前（グループ）
      複数人で全員同じグループ : 名前・名前（グループ）
      複数人でグループが違う  : 名前（グループ）・名前（グループ）
    グループが空、または名前と同じ(例: ロージークロニクル)なら（）は付けない。"""
    eid = val(ev, "ID")
    infos = []  # (name, ig, group)
    for key in ("Member1", "Member2"):
        name = val(ev, key)
        if not name:
            continue
        p = profiles.get((eid, name)) or {}
        group = p.get("group", "")
        infos.append((name, p.get("ig", ""), "" if group == name else group))

    def name_html(name: str, ig: str) -> str:
        return link(ig, esc(name)) if ig else esc(name)

    groups = [g for _, _, g in infos]
    distinct = {g for g in groups if g}
    if len(infos) > 1 and len(distinct) == 1 and all(groups):
        names = "・".join(name_html(n, ig) for n, ig, _ in infos)
        return f"{names}（{esc(next(iter(distinct)))}）"
    return "・".join(
        name_html(n, ig) + (f"（{esc(g)}）" if g else "") for n, ig, g in infos
    )


def build_rows(events: list[dict], profiles: dict[tuple, dict]) -> str:
    valid = [e for e in events if val(e, "ID")]
    valid.sort(key=lambda e: sort_key(val(e, "Date")))
    lines = []
    for i, ev in enumerate(valid):
        row_class = ' class="row-even"' if i % 2 else ""
        lines.append(
            f"<tr{row_class}>"
            f"<td>{format_date(val(ev, 'Date'))}</td>"
            f'<td>{icon_link(val(ev, "Instagram"), ICON_INSTAGRAM, "Instagram")}</td>'
            f'<td>{icon_link(val(ev, "TikTok"), ICON_TIKTOK, "TikTok")}</td>'
            f"<td>{build_member_cell(ev, profiles)}</td>"
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

    profiles = {}
    for m in members:
        mid, name = val(m, "ID"), val(m, "Member")
        if mid and name:
            profiles[(mid, name)] = {"ig": val(m, "Instagram"), "group": val(m, "Group")}

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    summary = []
    for ch in CHALLENGES:
        subset = [e for e in events if val(e, "ID").startswith(ch["prefix"])]
        ch["table"].parent.mkdir(parents=True, exist_ok=True)
        with open(ch["table"], "w", encoding="utf-8") as f:
            f.write(stamp + build_rows(subset, profiles) + "\n")
        summary.append(f"{ch['prefix']} {len(subset)}回")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{generated}</div>\n')

    print(f"生成完了: {' / '.join(summary)} / 相手IG {len(profiles)} 件")


if __name__ == "__main__":
    main()
