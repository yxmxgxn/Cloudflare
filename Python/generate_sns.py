#!/usr/bin/env python3
"""
ハロプロ現役・OGのSNS一覧ページ用フラグメントを生成する。

accounts スプレッドシート(縦持ち・1行=1アカウント)を読み取り、メンバーごとに
全SNSリンクを横並びにしたカード群と、絞り込み用の選択肢フラグメントを出力する。

accounts の列(位置で読む。ヘッダーに ID が重複しているため DictReader は使わない):
  0: member番号  1: 名前  2: platform  3: ID(handle / group-slug / フルURL)
  4: MEMBER(hello=メンバーslug / ameblo_g=テーマ番号 / それ以外は空)

URL は platform ごとのテンプレに ID / MEMBER を差し込んで生成する
(official/fanclub/shop は col3 にフルURLが入っているので素通し)。
プレースホルダ ID と MEMBER は部分文字列が被らないので置換順は問わない。

現役/OG判定: hello 行があれば現役(所属グループも hello の col3 slug から)、無ければOG。

更新頻度が高くないので手動実行(generate-manual.yml の sns)で回す。
ローカル確認用に accounts CSV パスも渡せる:
  python Python/generate_sns.py --accounts accounts.csv
"""

import argparse
import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vSLHCVWWxyVI5GS9SSohSNYL4U-uY4jekuMXbaKYXYjWrSZlgSxGV0BFvnCWRrd-A4Z5sqkoRwRyDqD/pub"
)
GID_ACCOUNTS = "1522035537"

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "sns_cards.php"
OUTPUT_PLATFORMS = REPO_ROOT / "Spreadsheet" / "sns_platforms.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "sns_groups.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "sns_generated.php"

# platform マスタ。表示順(=カード内のチップ並び順・絞り込みプルダウンの順)も兼ねる。
# url に ID / MEMBER を差し込む。url=None は col3 のフルURLを素通し(official 等)。
# label は絞り込みの単位でもある(ameblo と ameblo_g は同じ「アメブロ」で束ねる)。
PLATFORMS: dict[str, dict] = {
    "x":            {"label": "X",          "url": "https://x.com/ID"},
    "instagram":    {"label": "Instagram",  "url": "https://www.instagram.com/ID/"},
    "youtube":      {"label": "YouTube",     "url": "https://www.youtube.com/@ID"},
    "tiktok":       {"label": "TikTok",      "url": "https://www.tiktok.com/@ID"},
    "threads":      {"label": "Threads",     "url": "https://www.threads.com/@ID"},
    "ameblo":       {"label": "アメブロ",     "url": "https://ameblo.jp/ID/"},
    "ameblo_g":     {"label": "アメブロ",     "url": "https://ameblo.jp/ID/theme-MEMBER.html"},
    "note":         {"label": "note",        "url": "https://note.com/ID"},
    "wear":         {"label": "WEAR",        "url": "https://wear.jp/ID/"},
    "m-line":       {"label": "M-line",      "url": "https://www.upfc.jp/m-line/artist/?@uid=ID"},
    "upfrontcreate": {"label": "UP-FRONT CREATE", "url": "https://up-front-create.com/ID/"},
    "jproom":       {"label": "J.P ROOM",    "url": "https://www.jp-r.co.jp/ID/"},
    "fanicon":      {"label": "fanicon",     "url": "https://fanicon.net/fancommunities/ID"},
    "bitfan":       {"label": "Bitfan",      "url": "https://ID.bitfan.id/"},
    "hello":        {"label": "H!P公式",      "url": "https://helloproject.com/ID/MEMBER/"},
    "official":     {"label": "公式サイト",    "url": None},
    "fanclub":      {"label": "FC",          "url": None},
    "shop":         {"label": "SHOP",        "url": None},
    "wikpedia":     {"label": "Wikipedia",   "url": "https://ja.wikipedia.org/wiki/ID"},
}
PLATFORM_ORDER = list(PLATFORMS.keys())

# 現役グループ: hello 行の slug → 表示名。この辞書の順が現役セクションの表示順。
GROUPS: dict[str, str] = {
    "morningmusume": "モーニング娘。",
    "angerme":       "アンジュルム",
    "juicejuice":    "Juice=Juice",
    "tsubakifactory": "つばきファクトリー",
    "beyooooonds":   "BEYOOOOONDS",
    "ochanorma":     "OCHA NORMA",
    "rosychronicle": "ロージークロニクル",
}
GROUP_ORDER = list(GROUPS.keys())
OG_LABEL = "OG"

# 所属バケツ: この platform のどれかを持つ人が「広い意味での現役」。デフォルト表示対象。
# これを1つも持たない人=完全OGで、既定では隠す(「全部表示」で出す)。
AFFIL_KEYS = ["hello", "m-line", "jproom", "upfrontcreate", "official"]


def fetch_rows(source: str) -> list[list[str]]:
    """CSVを位置ベースの行リストで読む(ヘッダー行は除く)。ローカルパスも可。"""
    path = Path(source)
    if path.exists():
        content = path.read_text(encoding="utf-8-sig")
    else:
        with urllib.request.urlopen(source) as res:
            content = res.read().decode("utf-8")
    rows = list(csv.reader(io.StringIO(content)))
    return rows[1:] if rows else []


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def col(row: list[str], i: int) -> str:
    return row[i].strip() if i < len(row) and row[i] else ""


def build_url(platform: str, id_: str, member: str) -> str:
    tmpl = PLATFORMS.get(platform, {}).get("url")
    if tmpl is None:  # official/fanclub/shop: col3 がフルURL
        return id_
    return tmpl.replace("MEMBER", member).replace("ID", id_)


class Member:
    __slots__ = ("num", "name", "accounts")

    def __init__(self, num: int, name: str):
        self.num = num
        self.name = name
        self.accounts: list[tuple[str, str, str]] = []  # (platform, id, member)

    @property
    def group_slug(self) -> str | None:
        for platform, id_, _member in self.accounts:
            if platform == "hello":
                return id_
        return None

    @property
    def is_active(self) -> bool:
        return self.group_slug is not None

    @property
    def group_disp(self) -> str:
        slug = self.group_slug
        if slug is None:
            return OG_LABEL
        return GROUPS.get(slug, slug)


def load_members(rows: list[list[str]]) -> tuple[list[Member], set[str]]:
    members: dict[int, Member] = {}
    unknown_platforms: set[str] = set()
    for row in rows:
        num_s, name, platform = col(row, 0), col(row, 1), col(row, 2)
        id_, member_slug = col(row, 3), col(row, 4)
        if not num_s or not platform or not id_:
            continue
        try:
            num = int(num_s)
        except ValueError:
            continue
        if platform not in PLATFORMS:
            unknown_platforms.add(platform)
        m = members.get(num)
        if m is None:
            m = members[num] = Member(num, name)
        elif not m.name and name:
            m.name = name
        m.accounts.append((platform, id_, member_slug))
    return list(members.values()), unknown_platforms


def sort_members(members: list[Member]) -> list[Member]:
    def key(m: Member):
        slug = m.group_slug
        if slug is not None and slug in GROUP_ORDER:
            return (0, GROUP_ORDER.index(slug), m.num)
        if slug is not None:  # hello行はあるが未知グループ → 現役の末尾
            return (0, len(GROUP_ORDER), m.num)
        return (1, 0, m.num)  # OG は現役の後
    return sorted(members, key=key)


def build_links(m: Member) -> tuple[str, list[str]]:
    """カード内のSNSチップ列と、そのメンバーが持つ platform ラベル一覧を返す。"""
    ordered = sorted(
        m.accounts,
        key=lambda a: PLATFORM_ORDER.index(a[0]) if a[0] in PLATFORM_ORDER else len(PLATFORM_ORDER),
    )
    chips = []
    labels: list[str] = []
    for platform, id_, member_slug in ordered:
        conf = PLATFORMS.get(platform)
        label = conf["label"] if conf else platform
        labels.append(label)
        url = build_url(platform, id_, member_slug)
        chips.append(
            f'<a class="sns-link sns-{esc(platform)}" href="{esc(url)}"'
            f' target="_blank" rel="noopener noreferrer">{esc(label)}</a>'
        )
    return "".join(chips), list(dict.fromkeys(labels))


def build_cards(members: list[Member]) -> str:
    cards = []
    for m in members:
        links_html, labels = build_links(m)
        status = "active" if m.is_active else "og"
        group = m.group_disp if m.is_active else ""
        member_platforms = {a[0] for a in m.accounts}
        affil = [k for k in AFFIL_KEYS if k in member_platforms]
        search = " ".join([m.name, group] + labels + [a[1] for a in m.accounts])
        badge = (
            f' <span class="sns-badge">{esc(m.group_disp)}</span>'
            if m.is_active else ' <span class="sns-badge sns-badge-og">OG</span>'
        )
        cards.append(
            f'<div class="sns-card" data-status="{status}"'
            f' data-group="{esc(group)}"'
            f' data-affil="{esc("|".join(affil))}"'
            f' data-platforms="{esc("|".join(labels))}"'
            f' data-search="{esc(search)}">'
            f'<p class="sns-name">{esc(m.name)}{badge}</p>'
            f'<div class="sns-links">{links_html}</div>'
            "</div>"
        )
    return "\n".join(cards)


def build_options(values: list[str]) -> str:
    return "\n".join(f'<option value="{esc(v)}">{esc(v)}</option>' for v in values)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--accounts",
        default=f"{PUB_BASE}?gid={GID_ACCOUNTS}&single=true&output=csv",
    )
    args = parser.parse_args()

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    rows = fetch_rows(args.accounts)
    members, unknown = load_members(rows)
    members = sort_members(members)

    if unknown:
        print(f"::warning::未知の platform: {', '.join(sorted(unknown))}")

    # 絞り込みプルダウン: 実際に登場する platform ラベルを PLATFORMS 順で(重複排除)
    present = set()
    for m in members:
        for platform, _id, _member in m.accounts:
            conf = PLATFORMS.get(platform)
            present.add(conf["label"] if conf else platform)
    platform_labels = list(dict.fromkeys(
        PLATFORMS[k]["label"] for k in PLATFORM_ORDER if PLATFORMS[k]["label"] in present
    ))

    # グループプルダウン: 現役グループを定義順で(実在するもののみ)
    used_groups = {m.group_disp for m in members if m.is_active}
    group_labels = [GROUPS[k] for k in GROUP_ORDER if GROUPS[k] in used_groups]

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(members) + "\n")
    with open(OUTPUT_PLATFORMS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(platform_labels) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(group_labels) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    active = sum(1 for m in members if m.is_active)
    print(
        f"生成完了: {OUTPUT_CARDS} "
        f"(メンバー {len(members)} 人 / 現役 {active} / OG {len(members) - active} / "
        f"アカウント行 {len(rows)})"
    )


if __name__ == "__main__":
    main()
