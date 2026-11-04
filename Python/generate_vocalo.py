#!/usr/bin/env python3
"""
スプレッドシート(TodayVocalo)を読み取り、
PHPインクルード用のtr/tdフラグメントを生成する
"""

import csv
import io
from datetime import datetime, timezone, timedelta

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vSho7tsoiiQiCOmaYaSSSQiZZwpw0uLWAitI-uWhaZ4uuKaL9u9EVLD9AGcASdzlOX7sXlUOjD3aORX/pub"
)
SHEET_GID = "0"
from pathlib import Path

from sheets import fetch_text

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_PATH = REPO_ROOT / "Spreadsheet" / "vocalo_table.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "vocalo_generated.php"

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

def build_rows(data: list[list[str]]) -> str:
    headers = [h.strip() for h in data[0]]

    rows = []
    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: raw[i].strip() for i in range(len(headers))}
        if not row.get("Name", ""):
            continue
        rows.append(row)

    def sort_key(row):
        try:
            return datetime.strptime(pad_date(row.get("PostDate", "")), "%Y/%m/%d")
        except ValueError:
            return datetime.min
    rows.sort(key=sort_key, reverse=True)

    lines = []
    for row in rows:
        post_id       = row.get("PostID", "")
        post_url      = f"https://x.com/karin__miyamoto/status/{post_id}" if post_id else ""
        post_date     = pad_date(row.get("PostDate", ""))
        movie_id      = row.get("MovieID", "")
        nico_url      = f"https://www.nicovideo.jp/watch/{movie_id}" if movie_id else ""
        release_date  = pad_date(row.get("Date", ""))
        name          = escape_html(row.get("Name", ""))
        vocal         = escape_html(row.get("Vocal", ""))
        composer      = escape_html(row.get("Composer", ""))
        user_id       = row.get("UserID", "")
        composer_page = f"https://www.nicovideo.jp/user/{user_id}" if user_id else ""

        name_vocal = f"{name}【{vocal}】"

        post_cell = (
            f'<a href="{escape_html(post_url)}" target="_blank">{post_date}</a>'
            if post_url else post_date
        )
        name_cell = (
            f'<a href="{escape_html(nico_url)}" target="_blank">{name_vocal}</a>'
            if nico_url else name_vocal
        )
        composer_cell = (
            f'<a href="{escape_html(composer_page)}" target="_blank">{composer}</a>'
            if composer_page else composer
        )

        lines.append(
            "<tr>"
            f"<td>{post_cell}</td>"
            f"<td>{name_cell}</td>"
            f"<td>{composer_cell}</td>"
            f"<td>{release_date}</td>"
            "</tr>"
        )

    return "\n".join(lines)

def main():
    data = fetch_csv(SHEET_GID)
    tbody = build_rows(data)

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    content = (
        f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"
        f"{tbody}\n"
    )

    with open(OUTPUT_PATH, "w", encoding="utf-8") as f:
        f.write(content)

    meta = f'<p class="last-update">データ取得日：{generated}</p>\n'
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(meta)

    print(f"生成完了: {OUTPUT_PATH} ({len(data)-1} 行処理)")

if __name__ == "__main__":
    main()
