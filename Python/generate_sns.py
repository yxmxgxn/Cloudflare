#!/usr/bin/env python3
"""
ハロプロ現役・OGのSNS一覧ページ用フラグメントを生成する。

accounts スプレッドシート(縦持ち・1行=1アカウント)を読み取り、メンバーごとに
全SNSリンクを横並びにしたカード群と、絞り込み用の選択肢フラグメントを出力する。

accounts の列(位置で読む。ヘッダーに ID が重複しているため DictReader は使わない):
  0: member番号  1: 名前  2: platform  3: ID(handle / group-slug / フルURL)
  4: MEMBER(hello=メンバーslug / ameblo_g=テーマ番号 / それ以外は空)

各メンバーは platform="affiliation" の行を1つ持ち、その col3 が所属(下記)。
  現役グループ7 / upfrontcreate / upfrontpromotion / justpro / jproom / free / other / retirement
所属はカードの「でかでかバッジ」と絞り込みバケツ、既定表示(retirementは隠す)を決める。

URL は platform ごとのテンプレに ID / MEMBER を差し込んで生成する
(official/fanclub/shop/justpro は col3 にフルURLが入っているので素通し)。

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

from icons import icon

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vSLHCVWWxyVI5GS9SSohSNYL4U-uY4jekuMXbaKYXYjWrSZlgSxGV0BFvnCWRrd-A4Z5sqkoRwRyDqD/pub"
)
GID_ACCOUNTS = "1522035537"

# メンバーマスター(gamelive等と共用): Member,Yomigana,Group。読み仮名検索に使う。
MASTER_PUB = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vS7zVX-GcZhARaYFCFwkoKvtapNqcG3aZ8EwEE87VxPSpMGBRk-Wca3EVkcX9hEh4MX0CVY4G6m68SE/pub"
)
MASTER_GID = "0"

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "sns_cards.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "sns_groups.php"
OUTPUT_PLATFORMS = REPO_ROOT / "Spreadsheet" / "sns_platforms.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "sns_generated.php"

# platform マスタ。表示順(=カード内のチップ並び順)も兼ねる。
# url に ID / MEMBER を差し込む。url=None は col3 のフルURLを素通し(official 等)。
PLATFORMS: dict[str, dict] = {
    "x":            {"label": "X",          "url": "https://x.com/ID"},
    "instagram":    {"label": "Instagram",  "url": "https://www.instagram.com/ID/"},
    "youtube":      {"label": "YouTube",     "url": "https://www.youtube.com/@ID"},
    "tiktok":       {"label": "TikTok",      "url": "https://www.tiktok.com/@ID"},
    "threads":      {"label": "Threads",     "url": "https://www.threads.com/@ID"},
    "note":         {"label": "note",        "url": "https://note.com/ID"},
    "ameblo":       {"label": "アメブロ",     "url": "https://ameblo.jp/ID/"},
    "ameblo_g":     {"label": "アメブロ",     "url": "https://ameblo.jp/ID/theme-MEMBER.html"},
    "wear":         {"label": "WEAR",        "url": "https://wear.jp/ID/"},
    "wikpedia":     {"label": "Wikipedia",   "url": "https://ja.wikipedia.org/wiki/ID"},
    "hello":        {"label": "H!P公式",      "url": "https://helloproject.com/ID/MEMBER/"},
    "m-line":       {"label": "M-line",      "url": "https://www.upfc.jp/m-line/artist/?@uid=ID"},
    "upfrontcreate": {"label": "UP-FRONT CREATE", "url": "https://up-front-create.com/ID/"},
    "jproom":       {"label": "J.P ROOM",    "url": "https://www.jp-r.co.jp/ID/"},
    "justpro":      {"label": "ジャストプロ",  "url": None},
    "fanicon":      {"label": "fanicon",     "url": "https://fanicon.net/fancommunities/ID"},
    "bitfan":       {"label": "Bitfan",      "url": "https://ID.bitfan.id/"},
    "official":     {"label": "公式サイト",    "url": None},
    "fanclub":      {"label": "FC",          "url": None},
    "shop":         {"label": "SHOP",        "url": None},
}
PLATFORM_ORDER = list(PLATFORMS.keys())

# メタ行(リンクにしない)。affiliation=所属判定 / GroupNo・SubGroup=グループ番号。
META_PLATFORMS = {"affiliation", "GroupNo", "SubGroup"}

# アイコン化する platform → common/icons.json のアイコン名。
# 単色SVGで fill/stroke は currentColor、チップの色に追従する。
ICON_NAMES: dict[str, str] = {
    "x": "x",
    "instagram": "instagram",
    "threads": "threads",
    "youtube": "youtube",
    "tiktok": "tiktok",
    "wikpedia": "wikipedia",
    "note": "note",
    "ameblo": "ameblo",
    "ameblo_g": "ameblo",
}
ICONS: dict[str, str] = {k: icon(v, "ic sns-ic") for k, v in ICON_NAMES.items()}

# 現役グループ(affiliation slug → でかでかバッジの表示名。この順が現役の並び順)。
# モーニング娘。の年表記は改名に追従して毎年更新する。
GROUPS: dict[str, str] = {
    "morningmusume":  "モーニング娘。'26",
    "angerme":        "アンジュルム",
    "juicejuice":     "Juice=Juice",
    "tsubakifactory": "つばきファクトリー",
    "beyooooonds":    "BEYOOOOONDS",
    "ochanorma":      "OCHA NORMA",
    "rosychronicle":  "ロージークロニクル",
}
GROUP_ORDER = list(GROUPS.keys())

# 現役以外でバッジを出す所属(=絞り込みチェックボックスにもなる)。free/other/retirementは出さない。
AFFIL_BADGE: dict[str, str] = {
    "jproom":           "J.P ROOM",
    "upfrontcreate":    "UP-FRONT CREATE",
    "justpro":          "ジャストプロ",
    "upfrontpromotion": "UP-FRONT PROMOTION",
}

# グループ番号 → 表示名(縦持ち platform=GroupNo/SubGroup の値。現役もOGも持つ)。
# グループ絞り込みプルダウンとカードの data-group に使う。増減時はここに1行足す。
GROUPNO: dict[int, str] = {
    1:  "平家みちよ",
    2:  "モーニング娘。",
    3:  "太陽とシスコムーン",
    4:  "ココナッツ娘。",
    5:  "カントリー娘。",
    6:  "メロン記念日",
    7:  "Berryz工房",
    8:  "松浦亜弥",
    9:  "℃-ute",
    10: "真野恵里菜",
    11: "アンジュルム",
    12: "Juice=Juice",
    13: "カントリー・ガールズ",
    14: "こぶしファクトリー",
    15: "つばきファクトリー",
    16: "BEYOOOOONDS",
    17: "OCHA NORMA",
    18: "ロージークロニクル",
}

# 名前の下段(事務所・公式・FC系)へ回すリンクと、その並び順。残りはSNS行に出す。
OFFICIAL_ORDER = [
    "wikpedia", "hello", "jproom", "m-line", "upfrontcreate",
    "upfrontpromotion", "justpro", "official", "fanclub", "fanicon", "bitfan",
]
OFFICIAL_SET = set(OFFICIAL_ORDER)

# 個人アメブロ(platform=ameblo)のうち、ハンドルがこれらのものは「アーカイブ」扱い。
# (現役でないのにグループアメブロ ameblo_g がある場合も自動でアーカイブ)
ARCHIVE_AMEBLO_HANDLES = {
    "ogawamakoto-blog",      # 小川麻琴
    "angerme-ayakawada",     # 和田彩花
    "wadaayaka",             # 和田彩花
    "kanon-fukuda0312",      # 福田花音
    "kanon-fukuda",          # 福田花音
}


def _read(source: str) -> str:
    path = Path(source)
    if path.exists():
        return path.read_text(encoding="utf-8-sig")
    with urllib.request.urlopen(source) as res:
        return res.read().decode("utf-8")


def fetch_rows(source: str) -> list[list[str]]:
    rows = list(csv.reader(io.StringIO(_read(source))))
    return rows[1:] if rows else []


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def col(row: list[str], i: int) -> str:
    return row[i].strip() if i < len(row) and row[i] else ""


def to_int(s: str) -> int | None:
    try:
        return int(s)
    except (TypeError, ValueError):
        return None


def build_url(platform: str, id_: str, member: str) -> str:
    tmpl = PLATFORMS.get(platform, {}).get("url")
    if tmpl is None:  # official/fanclub/shop/justpro: col3 がフルURL
        return id_
    return tmpl.replace("MEMBER", member).replace("ID", id_)


class Member:
    __slots__ = ("num", "name", "yomi", "affiliation", "groupno", "subgroup", "accounts")

    def __init__(self, num: int, name: str):
        self.num = num
        self.name = name
        self.yomi = ""
        self.affiliation = ""
        self.groupno: int | None = None
        self.subgroup: int | None = None
        self.accounts: list[tuple[str, str, str]] = []  # (platform, id, member) ※メタ行除く

    @property
    def is_current(self) -> bool:
        return self.affiliation in GROUPS

    @property
    def badge(self) -> str:
        if self.affiliation in GROUPS:
            return GROUPS[self.affiliation]
        return AFFIL_BADGE.get(self.affiliation, "")

    @property
    def bucket(self) -> str:
        if self.affiliation in GROUPS:
            return "genin"
        if self.affiliation in AFFIL_BADGE:
            return self.affiliation
        if self.affiliation == "retirement":
            return "retirement"
        return "other"  # free / other / 未設定

    @property
    def groups(self) -> list[str]:
        """所属グループ表示名(GroupNo→SubGroup の順、重複除去)。現役もOGも持つ。"""
        out: list[str] = []
        for n in (self.groupno, self.subgroup):
            name = GROUPNO.get(n) if n is not None else None
            if name and name not in out:
                out.append(name)
        return out

    @property
    def has_mline(self) -> bool:
        return any(p == "m-line" for p, _i, _m in self.accounts)

    def is_archive_ameblo(self, platform: str, handle: str) -> bool:
        if platform == "ameblo_g":
            return not self.is_current   # 現役でないグループブログはアーカイブ
        if platform == "ameblo":
            return handle in ARCHIVE_AMEBLO_HANDLES
        return False

    def official_label(self) -> str:
        if self.affiliation == "upfrontpromotion":
            return "UP-FRONT PROMOTION"
        if self.affiliation in ("free", "other"):
            return "公式"
        return "公式サイト"


def load_members(rows: list[list[str]]) -> tuple[list[Member], set[str]]:
    members: dict[int, Member] = {}
    unknown_platforms: set[str] = set()
    for row in rows:
        num_s, name, platform = col(row, 0), col(row, 1), col(row, 2)
        id_, member_slug = col(row, 3), col(row, 4)
        if not num_s or not platform:
            continue
        try:
            num = int(num_s)
        except ValueError:
            continue
        m = members.get(num)
        if m is None:
            m = members[num] = Member(num, name)
        elif not m.name and name:
            m.name = name
        if platform == "affiliation":
            m.affiliation = id_
            continue
        if platform in ("GroupNo", "SubGroup"):
            n = to_int(id_)
            if platform == "GroupNo":
                m.groupno = n
            else:
                m.subgroup = n
            continue
        if not id_:
            continue
        if platform not in PLATFORMS:
            unknown_platforms.add(platform)
        m.accounts.append((platform, id_, member_slug))
    return list(members.values()), unknown_platforms


def sort_members(members: list[Member]) -> list[Member]:
    """セクション順(現役→事務所系バッジ→free/other→引退)、各セクション内はID順(加入順)。"""
    def section(m: Member) -> int:
        if m.affiliation in GROUPS:
            return 0
        if m.affiliation in AFFIL_BADGE:
            return 1
        if m.bucket == "retirement":
            return 3
        return 2  # free / other
    return sorted(members, key=lambda m: (section(m), m.num))


def render_chip(platform: str, url: str, label: str, archive: bool = False) -> str:
    icon = ICONS.get(platform)
    cls = f"sns-link sns-{esc(platform)}" + (" sns-archive" if archive else "")
    title = label + ("（アーカイブ）" if archive else "")
    if icon:
        return (
            f'<a class="sns-icon-link {cls}" href="{esc(url)}" target="_blank"'
            f' rel="noopener noreferrer" aria-label="{esc(title)}" title="{esc(title)}">{icon}</a>'
        )
    return (
        f'<a class="{cls}" href="{esc(url)}" target="_blank" rel="noopener noreferrer"'
        f'{" title=" + chr(34) + esc(title) + chr(34) if archive else ""}>{esc(label)}</a>'
    )


def build_links(m: Member) -> tuple[str, str, str, bool, list[str]]:
    """(SNS行HTML, 事務所・公式行HTML, アーカイブ層HTML, アーカイブ有無, ラベル一覧)。"""
    def order_key(platform: str, seq: list[str]) -> int:
        return seq.index(platform) if platform in seq else len(seq)

    sns, official, archive = [], [], []
    labels: list[str] = []
    for platform, id_, member_slug in m.accounts:
        conf = PLATFORMS.get(platform)
        label = conf["label"] if conf else platform
        if platform == "official":
            label = m.official_label()
        labels.append(label)
        url = build_url(platform, id_, member_slug)
        is_arc = m.is_archive_ameblo(platform, id_)
        chip = render_chip(platform, url, label, archive=is_arc)
        if is_arc:
            archive.append((platform, chip))
        elif platform in OFFICIAL_SET:
            official.append((platform, chip))
        else:
            sns.append((platform, chip))
    sns.sort(key=lambda e: order_key(e[0], PLATFORM_ORDER))
    official.sort(key=lambda e: order_key(e[0], OFFICIAL_ORDER))
    return (
        "".join(c for _p, c in sns),
        "".join(c for _p, c in official),
        "".join(c for _p, c in archive),
        bool(archive),
        list(dict.fromkeys(labels)),
    )


def build_cards(members: list[Member]) -> str:
    cards = []
    for m in members:
        sns_html, official_html, archive_html, has_archive, labels = build_links(m)
        badge = f' <span class="sns-badge">{esc(m.badge)}</span>' if m.badge else ""
        groups = m.groups
        search = " ".join([m.name, m.yomi, m.badge] + groups + labels + [a[1] for a in m.accounts])
        archive_block = ""
        toggle = ""
        card_cls = "sns-card"
        if has_archive:
            card_cls += " has-archive"
            toggle = '<button type="button" class="sns-arc-toggle">アメブロアーカイブ ▾</button>'
            archive_block = f'<div class="sns-archive-layer">{archive_html}</div>'
        official_row = f'<div class="sns-official">{official_html}</div>' if official_html else ""
        cards.append(
            f'<div class="{card_cls}" data-bucket="{esc(m.bucket)}"'
            f' data-retired="{"1" if m.bucket == "retirement" else "0"}"'
            f' data-mline="{"1" if m.has_mline else "0"}"'
            f' data-group="{esc("|".join(groups))}"'
            f' data-platforms="{esc("|".join(labels))}"'
            f' data-search="{esc(search)}">'
            f'<p class="sns-name">{esc(m.name)}{badge}</p>'
            f'<div class="sns-links">{sns_html}{toggle}</div>'
            f'{official_row}'
            f'{archive_block}'
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

    # 読み仮名マスターを引いて検索用に付与(名前一致。無ければ空のまま)
    master_url = f"{MASTER_PUB}?gid={MASTER_GID}&single=true&output=csv"
    yomi_by_name: dict[str, str] = {}
    for r in csv.DictReader(io.StringIO(_read(master_url))):
        name = (r.get("Member") or "").strip()
        if name:
            yomi_by_name[name] = (r.get("Yomigana") or "").strip()
    for m in members:
        m.yomi = yomi_by_name.get(m.name, "")

    members = sort_members(members)

    if unknown:
        print(f"::warning::未知の platform: {', '.join(sorted(unknown))}")
    missing_yomi = sum(1 for m in members if not m.yomi)
    if missing_yomi:
        print(f"読み仮名なし: {missing_yomi} 人(読み仮名検索から漏れる)")

    # グループプルダウン: GroupNo順(1..18)で、実在するグループのみ(現役もOGも含む)
    present_groups = set()
    for m in members:
        present_groups.update(m.groups)
    group_labels = [GROUPNO[n] for n in sorted(GROUPNO) if GROUPNO[n] in present_groups]

    # SNSプルダウン: 実際に登場する platform ラベルを PLATFORMS 順で(重複排除)
    present = set()
    for m in members:
        for platform, _id, _member in m.accounts:
            conf = PLATFORMS.get(platform)
            present.add(conf["label"] if conf else platform)
    platform_labels = list(dict.fromkeys(
        PLATFORMS[k]["label"] for k in PLATFORM_ORDER if PLATFORMS[k]["label"] in present
    ))

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(members) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(group_labels) + "\n")
    with open(OUTPUT_PLATFORMS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(platform_labels) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    n_current = sum(1 for m in members if m.is_current)
    n_retired = sum(1 for m in members if m.bucket == "retirement")
    print(
        f"生成完了: {OUTPUT_CARDS} "
        f"(メンバー {len(members)} 人 / 現役 {n_current} / 引退 {n_retired} / "
        f"アカウント行 {len(rows)})"
    )


if __name__ == "__main__":
    main()
