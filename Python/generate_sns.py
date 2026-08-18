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

# アイコン化する platform (単色SVG・fill/stroke は currentColor でチップ色に追従)。
# ここに無い platform は文字チップのまま。viewBox は全て 0 0 24 24 に統一。
ICONS: dict[str, str] = {
    "x": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
        '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817'
        'L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833'
        'L7.084 4.126H5.117z"/></svg>'
    ),
    "instagram": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        ' stroke-width="2" aria-hidden="true">'
        '<rect x="2" y="2" width="20" height="20" rx="5.5"/>'
        '<circle cx="12" cy="12" r="4.5"/>'
        '<circle cx="17.5" cy="6.5" r="1.3" fill="currentColor" stroke="none"/></svg>'
    ),
    "threads": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
        '<path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586'
        ' 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014'
        'c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96'
        '-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12'
        'c.027 3.086.718 5.496 2.065 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631'
        ' 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622'
        ' 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063'
        '-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291'
        'a13.85 13.85 0 0 1 3.02.142c-.126-.742-.375-1.332-.75-1.757-.513-.586-1.308-.883-2.359-.89'
        'h-.029c-.844 0-1.992.232-2.721 1.32L7.734 7.847c.98-1.454 2.568-2.256 4.478-2.256h.044'
        'c3.194.02 5.097 1.975 5.287 5.388.108.046.216.094.324.145 1.524.717 2.64 1.803 3.23 3.142'
        '.821 1.862.899 4.9-1.575 7.322-1.89 1.85-4.181 2.68-7.336 2.702zm1.868-11.6a10.65 10.65 0 0 0'
        '-.79.017c-1.84.105-2.982.951-2.916 2.156.062 1.263 1.451 1.85 2.787 1.779 1.226-.067 2.833'
        '-.545 3.098-3.72a8.98 8.98 0 0 0-2.179-.232z"/></svg>'
    ),
    "youtube": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
        '<path d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51'
        'A3.02 3.02 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.12 2.14'
        'C4.5 20.45 12 20.45 12 20.45s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14A31.5 31.5 0 0 0 24 12'
        'a31.5 31.5 0 0 0-.5-5.8zM9.6 15.6V8.4l6.2 3.6z"/></svg>'
    ),
    "tiktok": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
        '<path d="M16.5 3c.3 2.1 1.7 3.8 3.8 4.1v2.6c-1.2 0-2.4-.4-3.4-1v5.9c0 3.3-2.7 6-6 6s-6-2.7-6-6'
        ' 2.7-6 6-6c.3 0 .6 0 .9.1v2.7c-.3-.1-.6-.2-.9-.2-1.8 0-3.3 1.5-3.3 3.3s1.5 3.3 3.3 3.3'
        ' 3.3-1.4 3.3-3.2V3h2.3z"/></svg>'
    ),
    "wikpedia": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
        '<text x="12" y="17.5" text-anchor="middle" font-family="Georgia,\'Times New Roman\',serif"'
        ' font-size="17" font-weight="700">W</text></svg>'
    ),
    "note": (
        '<svg class="sns-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        ' stroke-width="2" aria-hidden="true">'
        '<rect x="3" y="3" width="18" height="18" rx="4"/>'
        '<path d="M8 16.5v-7l7 7v-9" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    ),
}

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
        icon = ICONS.get(platform)
        if icon:
            chips.append(
                f'<a class="sns-link sns-icon-link sns-{esc(platform)}" href="{esc(url)}"'
                f' target="_blank" rel="noopener noreferrer"'
                f' aria-label="{esc(label)}" title="{esc(label)}">{icon}</a>'
            )
        else:
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
