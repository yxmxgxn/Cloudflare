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

# メタ行(リンクにしない)。affiliation は所属判定にのみ使う。
META_PLATFORMS = {"affiliation"}

# アイコン化する platform(単色SVG・fill/stroke は currentColor でチップ色に追従)。
_IC = 'class="sns-ic" viewBox="0 0 24 24" aria-hidden="true"'
_AMEBLO_SVG = (
    f'<svg {_IC} fill="currentColor">'
    '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.9959.9959 0 0 0 0-1.41'
    'l-2.34-2.34a.9959.9959 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>'
)
ICONS: dict[str, str] = {
    "x": (
        f'<svg {_IC} fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17'
        'l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833'
        'L7.084 4.126H5.117z"/></svg>'
    ),
    "instagram": (
        f'<svg {_IC} fill="none" stroke="currentColor" stroke-width="2">'
        '<rect x="2" y="2" width="20" height="20" rx="5.5"/>'
        '<circle cx="12" cy="12" r="4.5"/>'
        '<circle cx="17.5" cy="6.5" r="1.3" fill="currentColor" stroke="none"/></svg>'
    ),
    "threads": (
        f'<svg {_IC} fill="currentColor"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509'
        'C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0'
        'h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96'
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
        f'<svg {_IC} fill="currentColor"><path d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55'
        's-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.12 2.14'
        'C4.5 20.45 12 20.45 12 20.45s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14A31.5 31.5 0 0 0 24 12'
        'a31.5 31.5 0 0 0-.5-5.8zM9.6 15.6V8.4l6.2 3.6z"/></svg>'
    ),
    "tiktok": (
        f'<svg {_IC} fill="currentColor"><path d="M16.5 3c.3 2.1 1.7 3.8 3.8 4.1v2.6c-1.2 0-2.4-.4-3.4-1v5.9'
        'c0 3.3-2.7 6-6 6s-6-2.7-6-6 2.7-6 6-6c.3 0 .6 0 .9.1v2.7c-.3-.1-.6-.2-.9-.2-1.8 0-3.3 1.5-3.3 3.3'
        's1.5 3.3 3.3 3.3 3.3-1.4 3.3-3.2V3h2.3z"/></svg>'
    ),
    "wikpedia": (
        f'<svg {_IC} fill="currentColor"><text x="12" y="17.5" text-anchor="middle"'
        ' font-family="Georgia,\'Times New Roman\',serif" font-size="17" font-weight="700">W</text></svg>'
    ),
    "note": (
        f'<svg {_IC} fill="none" stroke="currentColor" stroke-width="2">'
        '<rect x="3" y="3" width="18" height="18" rx="4"/>'
        '<path d="M8 16.5v-7l7 7v-9" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    ),
    "ameblo": _AMEBLO_SVG,
    "ameblo_g": _AMEBLO_SVG,
}

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

# 絞り込みチェックボックス(バケツ)。(value, ラベル)。現役は7グループをまとめる。
AFFIL_BUCKETS: list[tuple[str, str]] = [
    ("genin", "現役"),
    ("jproom", "J.P ROOM"),
    ("upfrontcreate", "UP-FRONT CREATE"),
    ("justpro", "ジャストプロ"),
    ("upfrontpromotion", "UP-FRONT PROMOTION"),
]

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


def build_url(platform: str, id_: str, member: str) -> str:
    tmpl = PLATFORMS.get(platform, {}).get("url")
    if tmpl is None:  # official/fanclub/shop/justpro: col3 がフルURL
        return id_
    return tmpl.replace("MEMBER", member).replace("ID", id_)


class Member:
    __slots__ = ("num", "name", "yomi", "affiliation", "accounts")

    def __init__(self, num: int, name: str):
        self.num = num
        self.name = name
        self.yomi = ""
        self.affiliation = ""
        self.accounts: list[tuple[str, str, str]] = []  # (platform, id, member) ※affiliation除く

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
    def group_disp(self) -> str:
        return GROUPS.get(self.affiliation, "")

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


def build_links(m: Member) -> tuple[str, str, bool, list[str]]:
    """(メイン行HTML, アーカイブ層HTML, アーカイブ有無, platformラベル一覧) を返す。"""
    ordered = sorted(
        m.accounts,
        key=lambda a: PLATFORM_ORDER.index(a[0]) if a[0] in PLATFORM_ORDER else len(PLATFORM_ORDER),
    )
    main, archive = [], []
    labels: list[str] = []
    for platform, id_, member_slug in ordered:
        conf = PLATFORMS.get(platform)
        label = conf["label"] if conf else platform
        if platform == "official":
            label = m.official_label()
        labels.append(label)
        url = build_url(platform, id_, member_slug)
        if m.is_archive_ameblo(platform, id_):
            archive.append(render_chip(platform, url, label, archive=True))
        else:
            main.append(render_chip(platform, url, label))
    return "".join(main), "".join(archive), bool(archive), list(dict.fromkeys(labels))


def build_cards(members: list[Member]) -> str:
    cards = []
    for m in members:
        main_html, archive_html, has_archive, labels = build_links(m)
        badge = f' <span class="sns-badge">{esc(m.badge)}</span>' if m.badge else ""
        search = " ".join([m.name, m.yomi, m.badge] + labels + [a[1] for a in m.accounts])
        archive_block = ""
        toggle = ""
        card_cls = "sns-card"
        if has_archive:
            card_cls += " has-archive"
            toggle = '<button type="button" class="sns-arc-toggle">アーカイブ ▾</button>'
            archive_block = f'<div class="sns-archive-layer">{archive_html}</div>'
        cards.append(
            f'<div class="{card_cls}" data-bucket="{esc(m.bucket)}"'
            f' data-retired="{"1" if m.bucket == "retirement" else "0"}"'
            f' data-group="{esc(m.group_disp)}"'
            f' data-platforms="{esc("|".join(labels))}"'
            f' data-search="{esc(search)}">'
            f'<p class="sns-name">{esc(m.name)}{badge}</p>'
            f'<div class="sns-links">{main_html}{toggle}</div>'
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

    # グループプルダウン: 現役グループを定義順で(実在するもののみ)
    used_groups = {m.group_disp for m in members if m.is_current}
    group_labels = [GROUPS[k] for k in GROUP_ORDER if GROUPS[k] in used_groups]

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
