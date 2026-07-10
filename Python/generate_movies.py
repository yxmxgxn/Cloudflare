#!/usr/bin/env python3
"""
映画スプレッドシート(映画一覧+キャスト一覧)を読み取り、
PHPインクルード用のテーブル・フィルタ選択肢フラグメントを生成する
"""

import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vTMBPXFDKzaVe7pj4NAWBQ4Rs5ABjyosKv5ix21nEwh4AP4EPGOo0c2tYstS803bs4r-8WgOYtv1d2_/pub"
)
GID_MOVIES = "777837729"
GID_CAST = "389226267"

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_TABLE = REPO_ROOT / "Spreadsheet" / "movies_table.php"
OUTPUT_GENRES = REPO_ROOT / "Spreadsheet" / "movies_genres.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "movies_groups.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "movies_generated.php"


def fetch_csv(gid: str) -> list[dict]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.DictReader(io.StringIO(content))
    return [row for row in reader]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def sort_date(s: str) -> str:
    """2002/12/14 → 2002-12-14 (ソート用)。パースできなければそのまま。"""
    parts = (s or "").strip().split("/")
    if len(parts) == 3:
        return f"{parts[0]}-{int(parts[1]):02d}-{int(parts[2]):02d}"
    return (s or "").strip()


def build_links(m: dict) -> str:
    """外部サイトリンクのセル内容を組み立てる(IDがある列だけ)"""
    links = []

    def add(label, url):
        links.append(
            f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">{label}</a>'
        )

    y = (m.get("Y_trailer") or "").strip()
    if y:
        add("予告編(YT)", f"https://www.youtube.com/watch?v={y}")
    e_tr = (m.get("E_Trailer") or "").strip()
    if e_tr:
        add("予告編(映画.com)", f"https://eiga.com/movie/{e_tr}/video/")
    wiki = (m.get("Wikipedia") or "").strip()
    if wiki:
        add("Wikipedia", wiki)
    fm = (m.get("filmarks") or "").strip()
    if fm:
        add("Filmarks", f"https://filmarks.com/movies/{fm}")
    eg = (m.get("eiga_com") or "").strip()
    if eg:
        add("映画.com", f"https://eiga.com/movie/{eg}/")
    ac = (m.get("all_cinemas") or "").strip()
    if ac:
        add("allcinema", f"https://www.allcinema.net/cinema/{ac}")
    pv = (m.get("Prime_Video") or "").strip()
    if pv:
        add("Prime Video", f"https://www.amazon.co.jp/gp/video/detail/{pv}")
    un = (m.get("U-next") or "").strip()
    if un:
        add("U-NEXT", f"https://www.video.unext.jp/title/{un}/")

    return " ".join(links)


def build_cast_rows(cast: list[dict]) -> str:
    """1映画分のキャスト一覧(展開行の中身)"""
    lines = []
    for c in cast:
        lines.append(
            "<tr>"
            f"<td>{esc(c.get('Member'))}</td>"
            f"<td>{esc(c.get('Role_name'))}</td>"
            f"<td>{esc(c.get('Credit'))}</td>"
            f"<td>{esc(c.get('Group'))}</td>"
            f"<td>{esc(c.get('Status'))}</td>"
            "</tr>"
        )
    return "\n".join(lines)


def build_table(movies: list[dict], cast_by_movie: dict) -> str:
    rows = []
    for m in movies:
        mid = (m.get("Movie_id") or "").strip()
        if not mid:
            continue
        cast = cast_by_movie.get(mid, [])
        cast.sort(key=lambda c: (c.get("CastSort_id") or ""))

        members = " ".join((c.get("Member") or "").strip() for c in cast)
        roles = " ".join((c.get("Role_name") or "").strip() for c in cast)
        groups = sorted({(c.get("Group") or "").strip() for c in cast if (c.get("Group") or "").strip()})

        title = esc(m.get("Title"))
        date = esc(m.get("Date"))
        genre = esc(m.get("Genre"))
        director = esc(m.get("Director"))

        # 映画行(フィルタ用データ属性付き)
        rows.append(
            f'<tr class="mv-row" data-id="{esc(mid)}" data-date="{esc(sort_date(m.get("Date")))}"'
            f' data-genre="{genre}" data-groups="{esc("|".join(groups))}"'
            f' data-search="{esc(" ".join([m.get("Title") or "", m.get("Director") or "", members, roles]))}">'
            f"<td>{date}</td>"
            f'<td class="mv-title">{title}</td>'
            f"<td>{genre}</td>"
            f"<td>{director}</td>"
            f'<td class="mv-cast-toggle"><button type="button" onclick="mvToggle(\'{esc(mid)}\')">'
            f"出演 {len(cast)}人</button></td>"
            f'<td class="mv-links">{build_links(m)}</td>'
            "</tr>"
        )
        # キャスト展開行(初期非表示)
        if cast:
            rows.append(
                f'<tr class="mv-cast" id="mv-cast-{esc(mid)}" style="display:none">'
                '<td colspan="6"><table class="mv-cast-table">'
                "<thead><tr><th>メンバー</th><th>役名</th><th>クレジット</th><th>グループ</th><th>出演時</th></tr></thead>"
                f"<tbody>{build_cast_rows(cast)}</tbody>"
                "</table></td></tr>"
            )
    return "\n".join(rows)


def build_options(values: list[str]) -> str:
    return "\n".join(f'<option value="{esc(v)}">{esc(v)}</option>' for v in values)


def main():
    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    movies = fetch_csv(GID_MOVIES)
    cast_rows = fetch_csv(GID_CAST)

    cast_by_movie: dict[str, list[dict]] = {}
    for c in cast_rows:
        mid = (c.get("Movie_id") or "").strip()
        if mid:
            cast_by_movie.setdefault(mid, []).append(c)

    genres = sorted({(m.get("Genre") or "").strip() for m in movies if (m.get("Genre") or "").strip()})
    groups = sorted({(c.get("Group") or "").strip() for c in cast_rows if (c.get("Group") or "").strip()})

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_TABLE, "w", encoding="utf-8") as f:
        f.write(stamp + build_table(movies, cast_by_movie) + "\n")
    with open(OUTPUT_GENRES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(genres) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(groups) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_TABLE} ({len(movies)} 作品 / キャスト {len(cast_rows)} 行)")


if __name__ == "__main__":
    main()
