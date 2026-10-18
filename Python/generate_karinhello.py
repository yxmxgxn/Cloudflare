#!/usr/bin/env python3
"""
スプレッドシート(佳林の1日1ハロ)を読み取り、
PHPインクルード用のtr/tdフラグメント・アーティスト一覧・更新日を生成する
"""

import csv
import io
import os
import re
import unicodedata
from datetime import datetime, timezone, timedelta

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQonFnpzakCXLq34UXXrsrKlp-54wcyVFfHcxh1F9U7lZvCSzbu4556GHDrawh3Q1zK9tcOQ68ZS3vm/pub"
)
SHEET_GID = "0"

from pathlib import Path

from sheets import fetch_text

from icons import icon

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_TABLE = REPO_ROOT / "Spreadsheet" / "karin-hello_table.php"
OUTPUT_ARTISTS = REPO_ROOT / "Spreadsheet" / "karin-hello_artists.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "karin-hello_generated.php"

def fetch_csv(gid: str) -> list[list[str]]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    content = fetch_text(url)
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


def parse_rows(data: list[list[str]]) -> list[dict]:
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
    return rows

def post_url_of(row: dict) -> str:
    post_id = row.get("ポスト", "")
    return f"https://x.com/karin__miyamoto/status/{post_id}" if post_id else ""

# ---- Worker(worker/index.js)の normalize / bigrams と一致させること ----
_KEEP = re.compile(r"[^0-9a-z々぀-ヿ㐀-䶿一-鿿]")

def normalize(s: str) -> str:
    return _KEEP.sub("", unicodedata.normalize("NFKC", s or "").lower())

def bigrams(s: str) -> str:
    return " ".join(s[i:i + 2] for i in range(len(s) - 1))

def sql_str(s: str) -> str:
    return "'" + s.replace("'", "''") + "'"

def build_index_sql(rows: list[dict]) -> str:
    """ポスト本文の検索索引(D1 karin-hello の post_fts)を全件入れ直すSQL。本文そのものは入れない。"""
    stmts = ["DELETE FROM post_fts;"]
    for row in rows:
        post_id = row.get("ポスト", "")
        grams = bigrams(normalize(row.get("Comment", "")))
        if post_id and grams:
            stmts.append(f"INSERT INTO post_fts(id,bigram) VALUES({sql_str(post_id)},{sql_str(grams)});")
    return "\n".join(stmts) + "\n"

# 配信サービス: (ID列, URL列, IDからURLを作るときの前置き, title, アイコン, tdのclass)
# ID列が空ならURL列を見る。セルにURLがそのまま入っていればそれを使う。
SERVICES = (
    ("YouTube Music", "Youtube Music URL", "https://music.youtube.com/watch?v=", "YouTube Music", "youtube-brand", "kh-yt"),
    ("Spotify", "Spotify URL", "https://open.spotify.com/track/", "Spotify", "spotify-brand", "kh-spotify"),
    ("Amazon Music", "Amazon Music URL", "https://music.amazon.co.jp/tracks/", "Amazon Music", "amazon-music-brand", "kh-amazon"),
    ("Apple Music", "Apple Music URL", "https://music.apple.com/jp/song/", "Apple Music", "apple-music-brand", "kh-apple"),
    ("LINE MUSIC", "LINE URL", "https://music.line.me/webapp/track/", "LINE MUSIC", "line-music-brand", "kh-line"),
    ("AWA", "AWA URL", "https://s.awa.fm/track/", "AWA", "awa-brand", "kh-awa"),
)

def service_url(row: dict, id_col: str, url_col: str, prefix: str) -> str:
    value = row.get(id_col, "") or row.get(url_col, "")
    if not value:
        return ""
    if value.startswith(("http://", "https://")):
        return value
    return prefix + value

def service_cell(row: dict, id_col: str, url_col: str, prefix: str, title: str, icon_name: str) -> str:
    url = service_url(row, id_col, url_col, prefix)
    if not url:
        return ""
    return f'<a href="{escape_html(url)}" target="_blank" rel="noopener noreferrer" title="{title}">{icon(icon_name, "ic ic-20")}</a>'

def build_table(rows: list[dict]) -> str:
    lines = []
    for row in rows:
        post_url     = post_url_of(row)
        post_date    = pad_date(row.get("ポスト日", ""))
        song         = escape_html(row.get("曲名", ""))
        artist       = escape_html(row.get("アーティスト名", ""))
        official_url = row.get("公式URL", "")
        album        = escape_html(row.get("アルバム名", ""))
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
        service_cells = "".join(
            f'<td class="{css}">{service_cell(row, id_col, url_col, prefix, title, icon_name)}</td>'
            for id_col, url_col, prefix, title, icon_name, css in SERVICES
        )

        lines.append(
            f'<tr data-artist="{artist}" data-post="{escape_html(row.get("ポスト", ""))}">'
            f"<td>{date_cell}</td>"
            f'<td class="kh-song">{song}</td>'
            f'<td class="kh-artist">{artist}</td>'
            f'<td class="kh-album">{album_cell}</td>'
            f"{service_cells}"
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

    data = fetch_csv(SHEET_GID)
    rows = parse_rows(data)

    # table
    with open(OUTPUT_TABLE, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_table(rows) + "\n")

    # 検索索引。本文から作るのでリポジトリには置かず、指定されたときだけ書き出す(CIでD1へ適用→破棄)
    index_sql = os.environ.get("KARINHELLO_INDEX_SQL")
    if index_sql:
        Path(index_sql).parent.mkdir(parents=True, exist_ok=True)
        Path(index_sql).write_text(build_index_sql(rows), encoding="utf-8")

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
