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

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "movies_cards.php"
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
    """外部サイトリンク(IDがある列だけ)"""
    links = []

    def add(label, url):
        links.append(
            f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">{label}</a>'
        )

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


MEMBER_LIMIT = 5  # カードにデフォルト表示する出演者数


def build_cast_detail(cast: list[dict]) -> str:
    """詳細ボタンで展開されるキャスト表"""
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
    return (
        '<table class="mv-cast-table">'
        "<thead><tr><th>メンバー</th><th>役名</th><th>クレジット</th><th>グループ</th><th>出演時</th></tr></thead>"
        f"<tbody>{''.join(lines)}</tbody></table>"
    )


def build_members_line(cast: list[dict], mid: str) -> str:
    """カードに常時表示する出演者名(5人超は「…他n人」で展開)"""
    names = [(c.get("Member") or "").strip() for c in cast if (c.get("Member") or "").strip()]
    if not names:
        return ""
    if len(names) <= MEMBER_LIMIT:
        return f'<p class="mv-members">出演: {esc("、".join(names))}</p>'
    head = "、".join(names[:MEMBER_LIMIT])
    rest = "、".join(names[MEMBER_LIMIT:])
    return (
        f'<p class="mv-members">出演: {esc(head)}'
        f'<span id="mv-more-{esc(mid)}" style="display:none">、{esc(rest)}</span>'
        f'<button type="button" class="mv-more-btn" id="mv-more-btn-{esc(mid)}"'
        f' onclick="mvMore(\'{esc(mid)}\')">…他{len(names) - MEMBER_LIMIT}人</button></p>'
    )


def build_trailer(m: dict, mid: str) -> str:
    """YouTube予告編のクリック再生プレースホルダー(押すまでiframeを読み込まない)"""
    y = (m.get("Y_trailer") or "").strip()
    if not y:
        return ""
    return (
        f'<button type="button" class="mv-trailer" data-yt="{esc(y)}" id="mv-trailer-{esc(mid)}"'
        f' onclick="mvPlay(\'{esc(mid)}\')" aria-label="予告編を再生">'
        f'<img src="https://i.ytimg.com/vi/{esc(y)}/hqdefault.jpg" alt="予告編サムネイル" loading="lazy">'
        '<span class="mv-play">▶ 予告編</span></button>'
    )


def build_cards(movies: list[dict], cast_by_movie: dict) -> str:
    cards = []
    for m in movies:
        mid = (m.get("Movie_id") or "").strip()
        if not mid:
            continue
        cast = cast_by_movie.get(mid, [])
        cast.sort(key=lambda c: (c.get("CastSort_id") or ""))

        members = " ".join((c.get("Member") or "").strip() for c in cast)
        roles = " ".join((c.get("Role_name") or "").strip() for c in cast)
        groups = sorted({(c.get("Group") or "").strip() for c in cast if (c.get("Group") or "").strip()})

        meta = " / ".join(x for x in [
            esc(m.get("Date")),
            esc(m.get("Genre")),
            ("監督: " + esc(m.get("Director"))) if (m.get("Director") or "").strip() else "",
        ] if x)

        detail = ""
        if cast:
            detail = (
                f'<button type="button" class="mv-detail-btn" onclick="mvToggle(\'{esc(mid)}\')">詳細(役名など)</button>'
                f'<div class="mv-cast" id="mv-cast-{esc(mid)}" style="display:none">{build_cast_detail(cast)}</div>'
            )

        links = build_links(m)
        links_html = f'<p class="mv-links">{links}</p>' if links else ""

        cards.append(
            f'<div class="movie-card" data-date="{esc(sort_date(m.get("Date")))}"'
            f' data-genre="{esc(m.get("Genre"))}" data-groups="{esc("|".join(groups))}"'
            f' data-title="{esc(m.get("Title"))}"'
            f' data-search="{esc(" ".join([m.get("Title") or "", m.get("Director") or "", members, roles]))}">'
            f'<p class="mv-card-title">{esc(m.get("Title"))}</p>'
            f'<p class="mv-meta">{meta}</p>'
            f"{build_members_line(cast, mid)}"
            f"{build_trailer(m, mid)}"
            f"{links_html}"
            f"{detail}"
            "</div>"
        )
    return "\n".join(cards)


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
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(movies, cast_by_movie) + "\n")
    with open(OUTPUT_GENRES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(genres) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(groups) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_CARDS} ({len(movies)} 作品 / キャスト {len(cast_rows)} 行)")


if __name__ == "__main__":
    main()
