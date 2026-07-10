#!/usr/bin/env python3
"""
スプレッドシート(佳林の1日1ハロ)を読み取り、
PHPインクルード用のtr/tdフラグメント・アーティスト一覧・更新日を生成する
"""

import csv
import io
import urllib.request
from datetime import datetime, timezone, timedelta

SPREADSHEET_ID = "1kmPtWU7KqQIaNUfX_-kdW0mElJ5zVgFGZqY9h8IXQJU"
SHEET_GID = "0"

from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_TABLE = REPO_ROOT / "Spreadsheet" / "karin-hello_table.php"
OUTPUT_ARTISTS = REPO_ROOT / "Spreadsheet" / "karin-hello_artists.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "karin-hello_generated.php"

def fetch_csv(spreadsheet_id: str, gid: str) -> list[list[str]]:
    url = (
        f"https://docs.google.com/spreadsheets/d/{spreadsheet_id}"
        f"/export?format=csv&gid={gid}"
    )
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.reader(io.StringIO(content))
    return list(reader)

def pad_date(s: str) -> str:
    if not s:
        return ""
    s = s.strip().replace("/", "-")
    parts = s.split("-")
    if len(parts) != 3:
        return s
    return f"{parts[0]}/{parts[1].zfill(2)}/{parts[2].zfill(2)}"

def clean(s: str) -> str:
    return s.replace("\r", "").replace("\n", " ").strip()

def escape_html(s: str) -> str:
    return (
        s.replace("&", "&amp;")
         .replace("<", "&lt;")
         .replace(">", "&gt;")
         .replace('"', "&quot;")
    )

YT_ICON = (
    '<svg style="vertical-align: -4px;  margin-right: 4px"width="20" height="20" viewBox="0 0 64 44" xmlns="http://www.w3.org/2000/svg">'
    '<rect width="64" height="44" rx="10" fill="#FF0000"/><polygon points="22,8 54,22 22,36" fill="white"/>'
    '</svg>'
)

def build_table(data: list[list[str]]) -> str:
    headers = [h.strip() for h in data[0]]

    rows = []
    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: clean(raw[i]) for i in range(len(headers))}
        if not row.get("曲名", ""):
            continue
        rows.append(row)

    rows.sort(key=lambda row: pad_date(row.get("ポスト日", "")), reverse=True)

    lines = []
    for row in rows:
        post_id      = row.get("ポスト", "")
        post_url     = f"https://x.com/karin__miyamoto/status/{post_id}" if post_id else ""
        post_date    = pad_date(row.get("ポスト日", ""))
        song         = escape_html(row.get("曲名", ""))
        artist       = escape_html(row.get("アーティスト名", ""))
        official_url = row.get("公式URL", "")
        album        = escape_html(row.get("アルバム名", ""))
        yt_id        = row.get("YouTube Music", "")
        yt_url       = f"https://music.youtube.com/watch?v={yt_id}" if yt_id else ""
        lyricist     = escape_html(row.get("作詞", ""))
        composer     = escape_html(row.get("作曲", ""))
        arranger     = escape_html(row.get("編曲", ""))

        date_cell = (
            f'<a href="{escape_html(post_url)}" target="_blank" rel="noopener noreferrer">{post_date}</a>'
            if post_url else post_date
        )
        album_cell = (
            f'<a href="{escape_html(official_url)}" target="_blank" rel="noopener noreferrer">{album}</a>'
            if official_url else album
        )
        yt_cell = (
            f'<a href="{escape_html(yt_url)}" target="_blank" rel="noopener noreferrer" title="YouTube Music">{YT_ICON}</a>'
            if yt_url else ""
        )

        lines.append(
            f'<tr data-artist="{artist}">'
            f"<td>{date_cell}</td>"
            f'<td class="kh-song">{song}</td>'
            f'<td class="kh-artist">{artist}</td>'
            f'<td class="kh-album">{album_cell}</td>'
            f'<td class="kh-yt">{yt_cell}</td>'
            f'<td class="kh-lyricist">{lyricist}</td>'
            f'<td class="kh-composer">{composer}</td>'
            f'<td class="kh-arranger">{arranger}</td>'
            "</tr>"
        )
    return "\n".join(lines)

def build_artists(data: list[list[str]]) -> str:
    headers = [h.strip() for h in data[0]]
    seen = set()
    artists = []
    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: clean(raw[i]) for i in range(len(headers))}
        a = row.get("アーティスト名", "")
        if a and a not in seen:
            seen.add(a)
            artists.append(a)
    artists.sort(key=lambda s: s.lower())
    return "\n".join(
        f'<option value="{escape_html(a)}">{escape_html(a)}</option>'
        for a in artists
    )

def main():
    jst = timezone(timedelta(hours=9))
    today_aware = datetime.now(jst)

    data = fetch_csv(SPREADSHEET_ID, SHEET_GID)

    # table
    with open(OUTPUT_TABLE, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_table(data) + "\n")

    # artists
    with open(OUTPUT_ARTISTS, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_artists(data) + "\n")

    # generated
    generated = today_aware.strftime("%Y/%m/%d")
    meta = f'<p class="last-update">データ取得日：{generated}</p>\n'
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_TABLE} / {OUTPUT_ARTISTS} / {OUTPUT_GENERATED} ({len(data)-1} 行処理)")

if __name__ == "__main__":
    main()
