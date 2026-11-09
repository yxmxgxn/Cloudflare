#!/usr/bin/env python3
"""
宮本佳林の「歌ってみた・踊ってみた」投稿のスプレッドシートを読み取り、
PHPインクルード用のtr/tdフラグメント・更新日を生成する

  列: Date, Song, Artist, Instagram, TikTok, ボカロ部TikTok, ボカロ部YouTube, Performance
      Instagram / TikTok / ボカロ部TikTok / ボカロ部YouTube は投稿のIDだけを入れる
      (Instagramは /p/ の後ろ、TikTokは /video/ の後ろ、YouTubeは v= の後ろ)。
      Performance は 歌ってみた / 踊ってみた / 歌って踊ってみた。見出しが空でも8列目なら読む。
      右側の https://… が入った列は、スプシ側でリンクを作る補助なので読まない。

出力: Spreadsheet/karin_mesinging_table.php(新しい順)
      Generated/karin_mesinging_generated.php(最終更新日)

リンクは href に元の投稿、data-embed に埋め込みURLを持つ。
押したときだけ iframe を作るのは instagram/karin-mesinging.php 側のJS。
"""

import csv
import html
import io
import re
import sys
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn
from icons import icon
from sheets import fetch_text

PUB_URL = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vRgO0NmLTX8QyrbkCz4VqJGytztYwTcM89sEIDxA0IglnE_a3gbiZTNhcC7TabCEdvMmJkvsEXEBeA3/pub"
    "?gid=0&single=true&output=csv"
)

REPO_ROOT = Path(__file__).resolve().parent.parent
OUTPUT_TABLE = REPO_ROOT / "Spreadsheet" / "karin_mesinging_table.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "karin_mesinging_generated.php"

PERFORMANCE_COL = 7  # 見出しが空のときに使う列(0始まり)

# 列名 → (リンク先, 埋め込みURL, IDの形式, アイコン名, ラベル, 埋め込みの高さpx)
# 高さは400px幅のポップアップ内でそのまま収まる値(JSが画面に収まるよう縮める)
SOURCES = [
    ("Instagram", "https://www.instagram.com/p/{id}/",
     "https://www.instagram.com/p/{id}/embed/captioned/", r"[A-Za-z0-9_-]{11}", "instagram", "Instagram", 780),
    ("TikTok", "https://www.tiktok.com/@karin_miyamoto/video/{id}",
     "https://www.tiktok.com/embed/v2/{id}", r"\d{19}", "tiktok", "TikTok", 740),
]
VOCALO_SOURCES = [
    ("ボカロ部TikTok", "https://www.tiktok.com/@miyavocal/video/{id}",
     "https://www.tiktok.com/embed/v2/{id}", r"\d{19}", "tiktok", "ボカロ部 TikTok", 740),
    ("ボカロ部YouTube", "https://www.youtube.com/watch?v={id}",
     "https://www.youtube.com/embed/{id}", r"[A-Za-z0-9_-]{11}", "youtube", "ボカロ部 YouTube", 260),
]


def esc(s: str) -> str:
    return html.escape((s or "").strip(), quote=True)


def parse_date(s: str):
    for fmt in ("%Y/%m/%d", "%Y-%m-%d"):
        try:
            return datetime.strptime(s.strip(), fmt)
        except ValueError:
            continue
    return None


def read_rows(text: str) -> list[dict]:
    rows = list(csv.reader(io.StringIO(text)))
    header = [h.strip() for h in rows[0]]
    if "Performance" not in header and len(header) > PERFORMANCE_COL and not header[PERFORMANCE_COL]:
        header[PERFORMANCE_COL] = "Performance"
    out = []
    for raw in rows[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw = raw + [""] * (len(header) - len(raw))
        row = {}
        for h, v in zip(header, raw):
            if h and h not in row:  # 空見出し・重複見出しは最初のものだけ
                row[h] = v.strip()
        out.append(row)
    return out


def build_link(row: dict, src, date_label: str) -> str:
    col, href_t, embed_t, pattern, icon_name, label, height = src
    vid = row.get(col, "")
    if not vid:
        return ""
    if not re.fullmatch(pattern, vid):
        warn(f"{date_label} の {col} 列がIDの形式ではありません: {vid}", title="投稿IDの形式")
        return ""
    return (
        f'<a href="{esc(href_t.format(id=vid))}" target="_blank" class="pf-link" '
        f'data-embed="{esc(embed_t.format(id=vid))}" data-h="{height}" '
        f'data-label="{esc(label)}で見る" aria-label="{esc(label)}">'
        f'{icon(icon_name, "ic sns-icon")}</a>'
    )


def build_table(rows: list[dict]) -> str:
    dated = []
    for i, row in enumerate(rows):
        d = parse_date(row.get("Date", ""))
        if d is None:
            warn(f"日付を読めない行があります: {row.get('Date', '')!r} {row.get('Song', '')}", title="日付")
            continue
        dated.append((d, i, row))
    dated.sort(key=lambda t: (t[0], t[1]), reverse=True)  # 新着順。同じ日は下の行が上

    lines = []
    for n, (d, _, row) in enumerate(dated):
        date_label = d.strftime("%Y/%m/%d")
        song = esc(row.get("Song", ""))
        artist = esc(row.get("Artist", ""))
        if song and artist:
            title_cell = f"{song} / {artist}"
        else:
            title_cell = song or artist or "—"

        links = [build_link(row, s, date_label) for s in SOURCES]
        vocalo = [build_link(row, s, date_label) for s in VOCALO_SOURCES]
        link_cell = "".join(x for x in links if x)
        if any(vocalo):
            link_cell += '<span class="pf-vocalo">ボカロ部</span>' + "".join(x for x in vocalo if x)
        if not link_cell:
            warn(f"{date_label} {row.get('Song', '')} にリンクがありません", title="リンクなし")

        cls = ' class="row-even"' if n % 2 else ""
        lines.append(
            f"<tr{cls}>"
            f"<td>{date_label}</td>"
            f"<td>{title_cell}</td>"
            f'<td class="pf-links">{link_cell}</td>'
            # 「歌って踊ってみた」はスマホで「歌って / 踊ってみた」の2行に折り返す
            f"<td>{esc(row.get('Performance', '')).replace('歌って踊ってみた', '歌って<wbr>踊ってみた')}</td>"
            "</tr>"
        )
    return "\n".join(lines)


def main():
    rows = read_rows(fetch_text(PUB_URL))
    jst = timezone(timedelta(hours=9))

    OUTPUT_TABLE.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_TABLE, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_table(rows) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{datetime.now(jst).strftime("%Y/%m/%d")}</div>\n')

    print(f"生成完了: {OUTPUT_TABLE} ({len(rows)} 行)")


if __name__ == "__main__":
    for st in (sys.stdout, sys.stderr):
        st.reconfigure(encoding="utf-8", errors="replace")
    main()
