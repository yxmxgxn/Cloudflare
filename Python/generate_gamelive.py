#!/usr/bin/env python3
"""
ハロプロメンバー・OGがYouTube「ファミ通ゲーム実況ch」で配信したアーカイブ
(番組名は ハロ通LIVE → ハロ通GAMES と変遷)のスプレッドシート
(動画一覧+メンバー一覧+ゲーム一覧)を読み取り、
PHPインクルード用のカード・フィルタ選択肢フラグメントを生成する。
※ダイジェスト版・ショート版は対象外(スプレッドシート側で管理)。

構造はインタビュー記事(generate_interview.py)とほぼ同じ:
  - 動画一覧 (ID,Date,URL,Title)
  - メンバー一覧 (ID,Group,Member) ※IDで動画に紐付け。Groupが空欄=OG
  - ゲーム一覧 (ID,Game,Genre)     ※IDで動画に紐付け
Dateが新しいものが上に来るようソート。メンバー名・グループ・ゲーム・ジャンルで
検索でき、メンバー名クリックでそのメンバーの絞り込みがかかる(PHP側JSで処理)。
"""

import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn_missing_members

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vS025h-f1IobGgDvz8xfARo-o_ph7Qgryizf-0w61ecgPuIteMdkajcbCxpaVZX39V2WfVB0kOO6LfH/pub"
)
GID_MAIN = "0"              # 動画一覧: ID,Date,URL,Title
GID_MEMBERS = "1193318164"  # メンバー一覧: ID,Group,Member
GID_GAMES = "807534636"     # ゲーム一覧: ID,Game,Genre

# メンバーマスター(他プログラム共用): Member,Yomigana,Group。
# 兼任は1グループに単純化し Group は一意、モーニング娘。の各期もモーニング娘。に統合。
# これを使って メンバー選択を五十音(読み仮名)順に、グループ絞り込みを正規化グループで行う。
MASTER_PUB = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vS7zVX-GcZhARaYFCFwkoKvtapNqcG3aZ8EwEE87VxPSpMGBRk-Wca3EVkcX9hEh4MX0CVY4G6m68SE/pub"
)
MASTER_GID = "0"           # メンバーマスター: Member,Yomigana,Group

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "gamelive_cards.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "gamelive_groups.php"
OUTPUT_NAMES = REPO_ROOT / "Spreadsheet" / "gamelive_names.php"
OUTPUT_GENRES = REPO_ROOT / "Spreadsheet" / "gamelive_genres.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "gamelive_generated.php"


def fetch_csv(gid: str, base: str = PUB_BASE) -> list[dict]:
    url = f"{base}?gid={gid}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.DictReader(io.StringIO(content))
    return [row for row in reader]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def sort_date(s: str) -> str:
    """2019/05/17 → 2019-05-17 (ソート用)。パースできなければそのまま。"""
    parts = (s or "").strip().split("/")
    if len(parts) == 3:
        try:
            return f"{int(parts[0]):04d}-{int(parts[1]):02d}-{int(parts[2]):02d}"
        except ValueError:
            pass
    return (s or "").strip()


def build_members_block(members: list[dict]) -> tuple[str, list[str], list[str]]:
    """動画に紐づくメンバー行から、表示用HTML・グループ一覧・名前一覧を作る。

    同じ Group のメンバーは1行にまとめる:
        竹内朱莉・室田瑞希 (アンジュルム)
    Group が空欄のメンバー(OG)は名前だけを1行ずつ表示する。
    メンバー名は <span class="gl-name" data-name="..."> で包み、クリックで絞り込める。
    竹内朱莉のように現役時代とOG時代がある場合も、名前(Member)が同じなら同一人物として
    扱われる(グループ表記が違うだけで絞り込みキーは名前)。
    """
    ordered: list[tuple[str | None, list[str]]] = []  # (group, names) 出現順
    group_index: dict[str, list[str]] = {}
    all_groups: list[str] = []
    all_names: list[str] = []

    for m in members:
        name = (m.get("Member") or "").strip()
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

    def name_span(n: str) -> str:
        return f'<span class="gl-name" data-name="{esc(n)}">{esc(n)}</span>'

    lines = []
    for group, names in ordered:
        joined = "・".join(name_span(n) for n in names)
        if group:
            lines.append(
                f'<p class="gl-member">{joined}'
                f' <span class="gl-group">({esc(group)})</span></p>'
            )
        else:
            lines.append(f'<p class="gl-member">{joined}</p>')

    html_block = f'<div class="gl-members">{"".join(lines)}</div>' if lines else ""
    return html_block, all_groups, all_names


def build_games_block(games: list[dict]) -> tuple[str, list[str], list[str]]:
    """動画に紐づくゲーム行から、表示用HTML・検索用文字列リスト・ジャンル一覧を作る。

    ゲームは1行ずつ「ゲーム名 (ジャンル)」で下に続けて表示する。
    """
    lines = []
    search_terms: list[str] = []
    genres: list[str] = []
    for g in games:
        game = (g.get("Game") or "").strip()
        if not game:
            continue
        genre = (g.get("Genre") or "").strip()
        search_terms.append(game)
        if genre:
            search_terms.append(genre)
            genres.append(genre)
            lines.append(
                f'<p class="gl-game">{esc(game)}'
                f' <span class="gl-genre">({esc(genre)})</span></p>'
            )
        else:
            lines.append(f'<p class="gl-game">{esc(game)}</p>')

    html_block = f'<div class="gl-games">{"".join(lines)}</div>' if lines else ""
    return html_block, search_terms, genres


def build_cards(
    articles: list[dict],
    members_by_id: dict,
    games_by_id: dict,
    master: dict[str, tuple[str, str]],
) -> str:
    cards = []
    for a in articles:
        aid = (a.get("ID") or "").strip()
        if not aid:
            continue

        date_disp = esc(a.get("Date"))
        date_sort = esc(sort_date(a.get("Date")))
        title = esc(a.get("Title"))
        url = (a.get("URL") or "").strip()

        # 1段目: Date + Title(URLがあれば埋め込む)
        if url:
            title_html = (
                f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">{title}</a>'
            )
        else:
            title_html = title
        head = (
            f'<p class="gl-head"><span class="gl-date">{date_disp}</span>'
            f'<span class="gl-title">{title_html}</span></p>'
        )

        # 2段目以降: メンバー(カード内の表示は動画当時のグループ表記のまま)
        members = members_by_id.get(aid, [])
        members_html, _disp_groups, names = build_members_block(members)

        # その下: ゲーム
        games = games_by_id.get(aid, [])
        games_html, game_terms, genres = build_games_block(games)

        # 絞り込み用グループは、メンバーマスターの正規化グループ(名寄せ)を使う。
        # これにより OG(動画側で無所属表記)や モーニング娘。各期も、そのメンバーの
        # 現在/代表グループで一括して絞り込める。
        canon_groups = [master[n][1] for n in names if n in master and master[n][1]]

        search = " ".join(
            [a.get("Title") or "", " ".join(names), " ".join(canon_groups), " ".join(game_terms)]
        )

        cards.append(
            f'<div class="gl-card" data-date="{date_sort}" data-title="{title}"'
            f' data-groups="{esc("|".join(dict.fromkeys(canon_groups)))}"'
            f' data-names="{esc("|".join(dict.fromkeys(names)))}"'
            f' data-genres="{esc("|".join(dict.fromkeys(genres)))}"'
            f' data-search="{esc(search)}">'
            f"{head}{members_html}{games_html}"
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
    game_rows = fetch_csv(GID_GAMES)
    master_rows = fetch_csv(MASTER_GID, base=MASTER_PUB)

    # メンバーマスター: 名前 → (読み仮名, 正規化グループ)。グループの登場順も記録。
    master: dict[str, tuple[str, str]] = {}
    group_order: list[str] = []
    for r in master_rows:
        name = (r.get("Member") or "").strip()
        if not name:
            continue
        yomi = (r.get("Yomigana") or "").strip()
        group = (r.get("Group") or "").strip()
        master[name] = (yomi, group)
        if group and group not in group_order:
            group_order.append(group)

    members_by_id: dict[str, list[dict]] = {}
    for m in member_rows:
        mid = (m.get("ID") or "").strip()
        if mid:
            members_by_id.setdefault(mid, []).append(m)

    games_by_id: dict[str, list[dict]] = {}
    for g in game_rows:
        gid = (g.get("ID") or "").strip()
        if gid:
            games_by_id.setdefault(gid, []).append(g)

    # gameliveに実際に登場するメンバー
    used_names = {(m.get("Member") or "").strip() for m in member_rows if (m.get("Member") or "").strip()}

    # マスターに無いメンバーがいれば警告(読み仮名順・グループ絞り込みから漏れる)
    warn_missing_members(
        sorted(n for n in used_names if n not in master),
        note="(読み仮名順・グループ絞り込みから漏れる)",
    )

    # メンバー選択肢: 読み仮名(五十音)順。読み仮名が無ければ名前でフォールバック。
    names = sorted(used_names, key=lambda n: (master.get(n, ("", ""))[0] or n, n))

    # グループ選択肢: gameliveメンバーの正規化グループを、マスター登場順で。
    used_groups = {master[n][1] for n in used_names if n in master and master[n][1]}
    groups = [g for g in group_order if g in used_groups]

    genres = sorted({(g.get("Genre") or "").strip() for g in game_rows if (g.get("Genre") or "").strip()})

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(articles, members_by_id, games_by_id, master) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(groups) + "\n")
    with open(OUTPUT_NAMES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(names) + "\n")
    with open(OUTPUT_GENRES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(genres) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(
        f"生成完了: {OUTPUT_CARDS} "
        f"({len(articles)} 動画 / メンバー行 {len(member_rows)} / ゲーム行 {len(game_rows)})"
    )


if __name__ == "__main__":
    main()
