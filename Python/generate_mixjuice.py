#!/usr/bin/env python3
"""
スプレッドシート(MIX=Juice)を読み取り、
PHPインクルード用のtr/tdフラグメント・更新日を生成する
"""

import csv
import io
from datetime import datetime, timezone, timedelta

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQGBS0Tm7Z9I32xmJ0PzdFma5Q5tJpvg71v8JRw7-QSlQsr57nqkYeVy0kSEOYttMg6FZfucZiHFYYt/pub"
)
SHEET_GID      = "1463327244"

from pathlib import Path

from sheets import fetch_text

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_PATH = REPO_ROOT / "Spreadsheet" / "mixjuice_table.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "mixjuice_generated.php"

def fetch_csv(gid: str) -> list[list[str]]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    content = fetch_text(url)
    reader = csv.reader(io.StringIO(content))
    return list(reader)

def pad_date(val: str) -> str:
    if not val:
        return ""
    for fmt in ("%Y-%m-%d", "%Y/%m/%d", "%m/%d/%Y", "%Y-%m-%dT%H:%M:%S"):
        try:
            dt = datetime.strptime(val.strip(), fmt)
            return dt.strftime("%Y/%m/%d")
        except ValueError:
            continue
    return val.strip()

def escape_html(s: str) -> str:
    return (
        s.replace("&", "&amp;")
         .replace("<", "&lt;")
         .replace(">", "&gt;")
         .replace('"', "&quot;")
    )

def build_table(data: list[list[str]]) -> str:
    headers = [h.strip() for h in data[0]]
    lines = []
    current_date = ""
    color_index = 0

    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: raw[i].strip() for i in range(len(headers))}

        broadcast_date = pad_date(row.get("放送日", ""))
        article_no = row.get("ArticleNo.", "")
        url = f"https://www.tbsradio.jp/articles/{article_no}/" if article_no else ""
        song           = escape_html(row.get("曲名", ""))
        artist         = escape_html(row.get("アーティスト", ""))
        selector       = escape_html(row.get("選曲者", ""))
        theme          = escape_html(row.get("テーマ", ""))

        if broadcast_date != current_date:
            current_date = broadcast_date
            color_index += 1

        row_class = "row-even" if color_index % 2 == 0 else ""
        class_attr = f' class="{row_class}"' if row_class else ""

        date_cell = (
            f'<a href="{escape_html(url)}" target="_blank" rel="noopener noreferrer">{escape_html(broadcast_date)}</a>'
            if url else escape_html(broadcast_date)
        )
        song_cell = f"{song} / {artist}" if artist else song

        lines.append(
            f'<tr data-selector="{selector}"{class_attr}>'
            f"<td>{date_cell}</td>"
            f"<td>{song_cell}</td>"
            f"<td>{selector}</td>"
            f"<td>{theme}</td>"
            "</tr>"
        )

    return "\n".join(lines)

def main():
    jst = timezone(timedelta(hours=9))
    today_aware = datetime.now(jst)

    data = fetch_csv(SHEET_GID)

    with open(OUTPUT_PATH, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_table(data) + "\n")

    generated = today_aware.strftime("%Y/%m/%d")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_PATH} / {OUTPUT_GENERATED} ({len(data)-1} 行処理)")

if __name__ == "__main__":
    main()
