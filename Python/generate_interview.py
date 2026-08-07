#!/usr/bin/env python3
"""
インタビュー記事スプレッドシート(記事一覧+メンバー一覧)を読み取り、
PHPインクルード用のカード・フィルタ選択肢フラグメントを生成する。

現時点の中身は宮本佳林関連が中心だが、スプレッドシートは
ハロプロメンバー全員に対応できる構造(ID紐付け・Group/Name)になっている。
"""

import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQUuY0kp6M2i3IBrzIJ0rxkRI19oqwdGFUszeQvsTJyydQJZae5OXrVEgshlXsDR906hUOm6497dUsr/pub"
)
GID_MAIN = "0"            # 記事一覧: ID,URL,Date,Media,Feature,Title,TimeStamp,WebArcive
GID_MEMBERS = "600835955"  # メンバー一覧: ID,Group,Name (IDは記事一覧と対応)

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "interview_cards.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "interview_groups.php"
OUTPUT_NAMES = REPO_ROOT / "Spreadsheet" / "interview_names.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "interview_generated.php"


def fetch_csv(gid: str) -> list[dict]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.DictReader(io.StringIO(content))
    return [row for row in reader]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def sort_date(s: str) -> str:
    """2026/08/06 → 2026-08-06 (ソート用)。パースできなければそのまま。"""
    parts = (s or "").strip().split("/")
    if len(parts) == 3:
        try:
            return f"{int(parts[0]):04d}-{int(parts[1]):02d}-{int(parts[2]):02d}"
        except ValueError:
            pass
    return (s or "").strip()


def build_members_block(members: list[dict]) -> tuple[str, list[str], list[str]]:
    """記事に紐づくメンバー行から、表示用HTML・グループ一覧・名前一覧を作る。

    同じ Group のメンバーは1行にまとめる:
        宮崎由加・金澤朋子・… (Juice=Juice)
    Group が空欄のメンバーは名前だけを1行ずつ表示する。
    """
    ordered: list[tuple[str | None, list[str]]] = []  # (group, names) 出現順
    group_index: dict[str, list[str]] = {}
    all_groups: list[str] = []
    all_names: list[str] = []

    for m in members:
        name = (m.get("Name") or "").strip()
        if not name:
            continue
        all_names.append(name)
        group = (m.get("Group") or "").strip()
        if group:
            if group not in group_index:
                group_index[group] = []
                ordered.append((group, group_index[group]))
                all_groups.append(group)
            group_index[group].append(name)
        else:
            ordered.append((None, [name]))

    lines = []
    for group, names in ordered:
        joined = "・".join(esc(n) for n in names)
        if group:
            lines.append(
                f'<p class="iv-member">{joined}'
                f' <span class="iv-group">({esc(group)})</span></p>'
            )
        else:
            lines.append(f'<p class="iv-member">{joined}</p>')

    html_block = f'<div class="iv-members">{"".join(lines)}</div>' if lines else ""
    return html_block, all_groups, all_names


def build_cards(articles: list[dict], members_by_id: dict) -> str:
    cards = []
    for a in articles:
        aid = (a.get("ID") or "").strip()
        if not aid:
            continue

        date_disp = esc(a.get("Date"))
        date_sort = esc(sort_date(a.get("Date")))
        title = esc(a.get("Title"))
        url = (a.get("URL") or "").strip()
        media = esc(a.get("Media"))
        feature = esc(a.get("Feature"))
        archive = (a.get("WebArcive") or "").strip()

        # 1段目: Date + Title(URLがあれば埋め込む)
        if url:
            title_html = (
                f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">{title}</a>'
            )
        else:
            title_html = title
        head = (
            f'<p class="iv-head"><span class="iv-date">{date_disp}</span>'
            f'<span class="iv-title">{title_html}</span></p>'
        )

        # 2段目: Feature / Media (Featureはある時のみ)
        sub_parts = [x for x in [feature, media] if x]
        sub = f'<p class="iv-sub">{" / ".join(sub_parts)}</p>' if sub_parts else ""

        # 3段目以降: メンバー
        members = members_by_id.get(aid, [])
        members_html, groups, names = build_members_block(members)

        # 最後: WebArchive(右寄せ)
        arc = ""
        if archive:
            arc = (
                '<p class="iv-arc"><a href="' + esc(archive) + '"'
                ' target="_blank" rel="noopener noreferrer">Web Archive</a></p>'
            )

        search = " ".join([a.get("Title") or "", a.get("Media") or "",
                           a.get("Feature") or "", " ".join(names)])

        cards.append(
            f'<div class="iv-card" data-date="{date_sort}" data-title="{title}"'
            f' data-groups="{esc("|".join(dict.fromkeys(groups)))}"'
            f' data-names="{esc("|".join(dict.fromkeys(names)))}"'
            f' data-search="{esc(search)}">'
            f"{head}{sub}{members_html}{arc}"
            "</div>"
        )
    return "\n".join(cards)


def build_options(values: list[str]) -> str:
    return "\n".join(f'<option value="{esc(v)}">{esc(v)}</option>' for v in values)


def main():
    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    articles = fetch_csv(GID_MAIN)
    member_rows = fetch_csv(GID_MEMBERS)

    members_by_id: dict[str, list[dict]] = {}
    for m in member_rows:
        mid = (m.get("ID") or "").strip()
        if mid:
            members_by_id.setdefault(mid, []).append(m)

    groups = sorted({(m.get("Group") or "").strip() for m in member_rows if (m.get("Group") or "").strip()})
    names = sorted({(m.get("Name") or "").strip() for m in member_rows if (m.get("Name") or "").strip()})

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(articles, members_by_id) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(groups) + "\n")
    with open(OUTPUT_NAMES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(names) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_CARDS} ({len(articles)} 記事 / メンバー行 {len(member_rows)} 行)")


if __name__ == "__main__":
    main()
