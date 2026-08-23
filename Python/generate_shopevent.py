#!/usr/bin/env python3
"""
ハロー！プロジェクトオフィシャルショップ(ハロショ)で行われたトークイベントの一覧。

スプレッドシートは1行1部(公演回の中の #1/#2/#3)の1タブ構成:
  ID,Date,Show,Time,EventNameID,EventName,Number,Title,,Member1〜5,Place,Type
  ID       … EventNameID + Number(数字は3桁ゼロ埋め) + Show
  Number   … 回数。数字のほか SA の H01(特別編)、LH の L01、AK の AK1 がある
  Member1〜5 … その部の出演者(部ごとに違うことがある。例: アネックス第七夜は
               つばきファクトリーを4人ずつ3部に分けている)
  MC       … 進行役(いれば)。ハロメンとは限らないので出演者とは別扱いにする。
             列ごと無くても動く(まだ埋めている途中)
  Type     … 会場 / 配信(コロナ禍に無観客配信された回。会場開催の回にも配信が
             付くことはあるが、そこはシートで追っていない)

出力は1行1公演回(Number)にまとめた表。部は1セルの中に縦に並べる。
同じ Number でも Date が違う行がある(振替公演・入力ミス)ため、
グループ化のキーは (EventNameID, Number, Date) にしてある。

回数は公式の表記にあわせて出す。千夜一夜系(SI/SO/SA)は「第八十八夜」、
大阪独演会・二人会(OD/OF)は「第四十五席」、感謝祭(AK)は「一日目」、
Lovelysの部屋(LH)だけ漢数字ではなく「vol.1」。
変えたいときは SERIES の label({n}=漢数字 / {d}=算用数字)を書き換える。

メンバーのよみがな・所属グループは別スプレッドシートのメンバーマスターから引く
(generate_interview.py と同じマスター)。よみがな検索とグループ絞り込みに使う。

hello-shop-event.php から include される。
"""

import csv
import html
import io
import re
import urllib.request
from collections import Counter, defaultdict
from datetime import date, datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn, warn_missing_members

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQfdS6nT53n0W6u9ZhYlKz7wUN8_sAl7NFPhTkzqhb2Of1Xowx-1hqiqkLFmbKCz8uVp6ZIO0P_DYE-/pub"
)
GID_EVENTS = "1885244952"
# 出演者ごとの「その日のブログ記事」タブ。手で埋めていくので生成では触らない。
GID_BLOGS = "349913342"

# メンバーマスター(別スプレッドシート): Member,Yomigana,Group,JoinDate,GradDate
MASTER_URL = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vS7zVX-GcZhARaYFCFwkoKvtapNqcG3aZ8EwEE87VxPSpMGBRk-Wca3EVkcX9hEh4MX0CVY4G6m68SE/pub"
    "?gid=0&single=true&output=csv"
)

MEMBER_COLS = [f"Member{i}" for i in range(1, 6)]

# シリーズ設定。short=絞り込みや表の見出しに使う短い名前、
# label=回数の見せ方({n} に漢数字、{d} に算用数字が入る)、
# special=Number の数字に付く接頭辞のうち、本編の通し番号ではないもの。
SERIES = {
    "SI": {"short": "千夜一夜",         "label": "第{n}夜", "special": {}},
    "SO": {"short": "千夜一夜 in 大阪", "label": "第{n}夜", "special": {}},
    # アネックスの H01〜 は「ひなフェス2015直前スペシャル」で本編の番号とは別系統
    "SA": {"short": "アネックス",       "label": "第{n}夜", "special": {"H": "特別編 {n}"}},
    "OD": {"short": "大阪独演会",       "label": "第{n}席", "special": {}},
    "OF": {"short": "大阪二人会",       "label": "第{n}席", "special": {}},
    # Lovelysの部屋だけは漢数字ではなく「vol.1」表記
    "LH": {"short": "Lovelysの部屋",    "label": "vol.{d}", "special": {}},
    "AK": {"short": "秋葉原 感謝祭",    "label": "{n}日目", "special": {}},
}

# 会場名の表示用の短縮。プルダウンの値もこの短い名前を使う。
PLACE_SHORT = {
    "ハロー！プロジェクトオフィシャルショップ 東京秋葉原店": "秋葉原店",
    "ハロー！プロジェクトオフィシャルショップ 大阪店": "大阪店",
    # 無観客配信の回。会場開催の回にも配信が付くことはあるので「のみ」を付けて区別する
    "HELLO! PROJECT STREAM": "配信のみ",
}
# 会場プルダウンの並び(ここに無いものは後ろに五十音順で付く)
PLACE_ORDER = ["秋葉原店", "大阪店", "配信のみ"]

WEEKDAYS = "月火水木金土日"

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_ROWS = REPO_ROOT / "Spreadsheet" / "shopevent_rows.php"
OUTPUT_SERIES = REPO_ROOT / "Spreadsheet" / "shopevent_series.php"
OUTPUT_NAMES = REPO_ROOT / "Spreadsheet" / "shopevent_names.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "shopevent_groups.php"
OUTPUT_PLACES = REPO_ROOT / "Spreadsheet" / "shopevent_places.php"
OUTPUT_YEARS = REPO_ROOT / "Spreadsheet" / "shopevent_years.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "shopevent_generated.php"


# ---------------------------------------------------------------- 取得・小物
def fetch_url(url: str) -> list[dict]:
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    return list(csv.DictReader(io.StringIO(content)))


def get(row: dict, key: str) -> str:
    return (row.get(key) or "").strip()


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def members_of(row: dict) -> list[str]:
    """その部の出演者。空欄は飛ばし、重複は落として並び順は保つ。"""
    return list(dict.fromkeys(m for m in (get(row, c) for c in MEMBER_COLS) if m))


def parse_date(s: str) -> date | None:
    for fmt in ("%Y/%m/%d", "%Y-%m-%d", "%Y年%m月%d日"):
        try:
            return datetime.strptime((s or "").strip(), fmt).date()
        except ValueError:
            continue
    return None


def kanji(n: int) -> str:
    """1〜9999 の漢数字。108→百八、88→八十八、10→十、20→二十。"""
    if n <= 0:
        return str(n)
    digits = "〇一二三四五六七八九"
    out = ""
    for unit_val, unit in ((1000, "千"), (100, "百"), (10, "十")):
        d, n = divmod(n, unit_val)
        if d:
            out += ("" if d == 1 else digits[d]) + unit
    if n:
        out += digits[n]
    return out


def number_label(series_id: str, number: str) -> str:
    """Number 列 → 表示用の回数(漢数字)。数字にできなければそのまま返す。"""
    conf = SERIES.get(series_id, {})
    m = re.fullmatch(r"([A-Za-z]*)(\d+)", number)
    if not m:
        return number
    prefix, n = m.group(1).upper(), int(m.group(2))
    tmpl = conf.get("special", {}).get(prefix) or conf.get("label", "第{n}回")
    return tmpl.format(n=kanji(n), d=n)


def number_sort(number: str) -> tuple:
    """回数の並び替え用。本編(接頭辞なし)を先に、その中で数値順。"""
    m = re.fullmatch(r"([A-Za-z]*)(\d+)", number)
    if not m:
        return (2, number, 0)
    return (1 if m.group(1) else 0, m.group(1).upper(), int(m.group(2)))


def place_short(place: str) -> str:
    return PLACE_SHORT.get(place, place)


# ---------------------------------------------------------------- マスター
def fetch_blogs() -> dict[tuple[str, str], dict[str, str]]:
    """(開催日, メンバー) -> そのイベント当日のブログ記事。

    ヘッダーに Member が2つあって DictReader だと潰れるので位置で読む。
    列: ID,Date,Member,Blog,ThemeID,Status,EntryDate,EntryURL,EntryTitle,WebArcive
    Blog が空の行は「ブログごと消えた」の意味で、元記事のリンクは出さず魚拓だけ出す。
    """
    url = f"{PUB_BASE}?gid={GID_BLOGS}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")

    out: dict[tuple[str, str], dict[str, str]] = {}
    for row in list(csv.reader(io.StringIO(content)))[1:]:
        row = (row + [""] * 10)[:10]
        d = parse_date(row[1])
        name = row[2].strip()
        entry, arc = row[7].strip(), row[9].strip()
        if not (d and name) or not (entry or arc):
            continue
        out[(d.isoformat(), name)] = {
            "blog": row[3].strip(),
            "entry": entry,
            "title": row[8].strip(),
            "arc": arc,
        }
    return out


def arc_label(arc: str) -> str:
    """魚拓URLのタイムスタンプから日付を作る(generate_interview.py と同じ流儀)。"""
    m = re.search(r"/web/(\d{4})(\d{2})(\d{2})", arc)
    return f"{m.group(1)}/{m.group(2)}/{m.group(3)}" if m else "Web Archive"


def fetch_master() -> dict[str, dict]:
    """メンバーマスター: 名前 → {"yomigana": .., "groups": [グループ(在籍歴すべて)]}。"""
    result: dict[str, dict] = {}
    for row in fetch_url(MASTER_URL):
        name = get(row, "Member")
        if not name:
            continue
        e = result.setdefault(name, {"yomigana": "", "groups": []})
        if not e["yomigana"]:
            e["yomigana"] = get(row, "Yomigana")
        g = get(row, "Group")
        if g and g not in e["groups"]:
            e["groups"].append(g)
    return result


def yomi_key(name: str, master: dict[str, dict]) -> str:
    """よみがな順。マスターに無い人は名前そのものでソート(末尾寄りになる)。"""
    return (master.get(name, {}).get("yomigana") or name)


# ---------------------------------------------------------------- 検証
def check_data(rows: list[dict]) -> None:
    """入力ミスを見つけたら警告する(生成は止めない)。"""
    for r in rows:
        if not parse_date(get(r, "Date")):
            warn(f"{get(r, 'ID')}: Date '{get(r, 'Date')}' を日付として読めません", title="日付異常")
        if get(r, "EventNameID") not in SERIES:
            warn(f"{get(r, 'ID')}: 未知の EventNameID '{get(r, 'EventNameID')}'"
                 "(回数は漢数字にせずそのまま出しました)", title="未知のイベント")
        if not members_of(r):
            warn(f"{get(r, 'ID')}: 出演者が1人も入っていません", title="出演者未入力")
        for k, v in r.items():
            if v and "\n" in v:
                warn(f"{get(r, 'ID')} の {k} 列にセル内改行があります", title="セル内改行")

    # ID重複 = Show の付け間違い、または本当に同じ枠が2つある
    dup = [i for i, c in Counter(get(r, "ID") for r in rows).items() if c > 1]
    if dup:
        warn(f"同じ ID の行が複数あります: {'、'.join(sorted(dup))}。"
             "Show(#1/#2)の付け間違いでないか確認してください", title="IDの重複")

    # 同じ回番号が別の日に入っている = 振替公演か入力ミス
    dates_by_no = defaultdict(set)
    for r in rows:
        dates_by_no[(get(r, "EventNameID"), get(r, "Number"))].add(get(r, "Date"))
    for (sid, no), ds in sorted(dates_by_no.items()):
        if len(ds) > 1:
            warn(f"{sid} の {no} が複数の日付に分かれています({'、'.join(sorted(ds))})。"
                 "振替公演ならそのままで、入力ミスなら直してください", title="回数と日付の不一致")

    # EventNameID と EventName の対応崩れ(表記ゆれ)
    names_by_id = defaultdict(set)
    for r in rows:
        names_by_id[get(r, "EventNameID")].add(get(r, "EventName"))
    for sid, names in sorted(names_by_id.items()):
        if len(names) > 1:
            warn(f"{sid} に複数のイベント名: {' / '.join(sorted(names))}", title="イベント名の表記ゆれ")

    # Type と Place の対応(配信なのに実店舗、など)
    for r in rows:
        stream = get(r, "Place") == "HELLO! PROJECT STREAM"
        if stream != (get(r, "Type") == "配信"):
            warn(f"{get(r, 'ID')}: Type '{get(r, 'Type')}' と Place '{get(r, 'Place')}' が"
                 "かみ合っていません", title="配信/会場の不一致")


# ---------------------------------------------------------------- 組み立て
def build_groups(rows: list[dict]) -> list[dict]:
    """1行1部のデータを公演回ごとにまとめる。新しい順に並べて返す。

    同じ回番号でも日付が違えば別の公演として扱う(振替公演があるため)。"""
    buckets: dict[tuple, list[tuple[int, dict]]] = defaultdict(list)
    order: dict[tuple, int] = {}
    for i, r in enumerate(rows):
        key = (get(r, "EventNameID"), get(r, "Number"), get(r, "Date"))
        buckets[key].append((i, r))
        order.setdefault(key, i)

    groups = []
    for (sid, number, datestr), indexed in buckets.items():
        # 開演時刻順。時刻が同じ(入力ミスを含む)ときはシートの並び順を保つ
        shows = [r for _, r in sorted(indexed, key=lambda x: (get(x[1], "Time"), x[0]))]
        first = shows[0]
        d = parse_date(datestr)
        names: list[str] = []
        for s in shows:
            for m in members_of(s):
                if m not in names:
                    names.append(m)
        mcs = [get(x, "MC") for x in shows]
        groups.append({
            "series": sid,
            "event_name": get(first, "EventName"),
            "number": number,
            "date": d,
            "date_str": datestr,
            "shows": shows,
            "names": names,
            # 部ごとに出演者が違う回(アネックス第七夜など)は出演セルを部ごとに分ける
            "per_show_cast": len({tuple(members_of(s)) for s in shows}) > 1,
            # MC(進行役)。全部同じなら回に1つだけ、部ごとに違うなら各部に出す
            "mc": mcs[0] if mcs and len(set(mcs)) == 1 else "",
            "per_show_mc": len(set(mcs)) > 1,
            "mc_names": [m for m in dict.fromkeys(mcs) if m],
            "place": get(first, "Place"),
            "type": get(first, "Type"),
            "order": order[(sid, number, datestr)],
        })

    groups.sort(key=lambda g: (
        g["date"] or date.min, number_sort(g["number"]), g["order"]
    ), reverse=True)
    return groups


def cast_list(names: list[str], date_key: str, blogs: dict) -> str:
    """出演者名と、その人がその日に書いたブログ記事へのリンク。

    名前の span はクリックで絞り込むトリガーなので、リンクは span の外に並べる
    (中に入れるとクリックが取られる)。魚拓は既定で hidden にしておき、
    ページ側のトグルでまとめて出す。Blog 列が空 = ブログごと消えているので、
    その場合は元記事を出さず魚拓だけを「アーカイブ」として出す。

    並びは CSS の grid に任せる。data-n(人数)を見て、2人までは縦一列、
    3人以上は2列(二人・一人…)に折り返す。"""
    out = []
    for n in names:
        cell = f'<span class="hs-name">{esc(n)}</span>'
        b = blogs.get((date_key, n))
        entry = b["entry"] if b and b["blog"] else ""
        arc = b["arc"] if b else ""
        title = esc(b["title"] or "ブログ") if b else ""
        if entry:
            cell += (f'<a class="hs-blog" href="{esc(entry)}" target="_blank"'
                     f' rel="noopener noreferrer" title="{title}">ブログ</a>')
            if arc:
                cell += (f'<a class="hs-arc" href="{esc(arc)}" target="_blank"'
                         f' rel="noopener noreferrer" title="{title}（ウェブアーカイブ）"'
                         f' hidden>{esc(arc_label(arc))}</a>')
        elif arc:
            cell += (f'<a class="hs-blog hs-blog-dead" href="{esc(arc)}" target="_blank"'
                     f' rel="noopener noreferrer" title="{title}（ウェブアーカイブ）">'
                     f'アーカイブ</a>')
        out.append(f'<span class="hs-cast-one">{cell}</span>')
    return f'<span class="hs-cast-list" data-n="{len(out)}">{"".join(out)}</span>'


def build_rows_html(groups: list[dict], master: dict[str, dict], blogs: dict) -> str:
    """1公演回 = 1つの <tbody>、その中の1行 = 1部。

    日付・イベント・会場（と、全部の部で出演者が同じ回は出演も）は rowspan で
    まとめる。部と時刻をタイトルとは別の列に出したいので、行内に div を積むのでは
    なく本物の行に分けている（div だとタイトルの折り返しで列がずれる）。"""
    lines = []
    for g in groups:
        d = g["date"]
        date_disp = (f'{d.year}/{d.month:02d}/{d.day:02d}'
                     f'<span class="hs-dow">（{WEEKDAYS[d.weekday()]}）</span>') if d else esc(g["date_str"])
        date_key = d.isoformat() if d else ""
        year = str(d.year) if d else ""

        conf = SERIES.get(g["series"], {})
        no_label = number_label(g["series"], g["number"])
        place = place_short(g["place"])
        shows = g["shows"]
        span = f' rowspan="{len(shows)}"' if len(shows) > 1 else ""

        groups_of_row: list[str] = []
        for n in g["names"]:
            for grp in master.get(n, {}).get("groups", []):
                if grp not in groups_of_row:
                    groups_of_row.append(grp)

        # 検索対象: タイトル・出演者(よみがな込み)・イベント名・回数・会場・日付
        search = " ".join([
            g["event_name"], conf.get("short", ""), no_label, g["number"],
            place, g["place"], g["type"], g["date_str"], date_key,
            " ".join(get(s, "Title") for s in shows),
            " ".join(g["names"]),
            " ".join(master.get(n, {}).get("yomigana", "") for n in g["names"]),
            " ".join(groups_of_row),
            " ".join(g["mc_names"]),   # MCはハロメンとは限らないので出演とは別に持つ
        ]).lower()

        lines.append(
            '<tbody class="hs-row"'
            f' data-date="{esc(date_key)}"'
            f' data-year="{esc(year)}"'
            f' data-series="{esc(g["series"])}"'
            f' data-place="{esc(place)}"'
            f' data-names="{esc("|".join(g["names"]))}"'
            f' data-groups="{esc("|".join(groups_of_row))}"'
            f' data-search="{esc(search)}">'
        )

        for i, s_row in enumerate(shows):
            cells = []
            if i == 0:
                cells.append(f'<td class="hs-date"{span}>{date_disp}</td>')
                cells.append(
                    f'<td class="hs-event"{span}>'
                    f'<span class="hs-series">{esc(conf.get("short") or g["event_name"])}</span>'
                    f'<span class="hs-no">{esc(no_label)}</span></td>'
                )
            slot = " ".join(x for x in (get(s_row, "Show"), get(s_row, "Time")) if x)
            cells.append(f'<td class="hs-slot">{esc(slot)}</td>')

            # MC: 部ごとに違えばその部に、全部同じなら最後の部の右下に1つだけ添える
            mc = get(s_row, "MC") if g["per_show_mc"] else (g["mc"] if i == len(shows) - 1 else "")
            mc_html = f'<p class="hs-mc">MC：{esc(mc)}</p>' if mc else ""
            cells.append(f'<td class="hs-title">{esc(get(s_row, "Title"))}{mc_html}</td>')

            if g["per_show_cast"]:
                cells.append(f'<td class="hs-cast">{cast_list(members_of(s_row), date_key, blogs)}</td>')
            elif i == 0:
                cells.append(f'<td class="hs-cast"{span}>{cast_list(g["names"], date_key, blogs)}</td>')

            if i == 0:
                cells.append(
                    f'<td class="hs-place"{span} title="{esc(g["place"])}">{esc(place)}</td>'
                )
            lines.append("<tr>" + "".join(cells) + "</tr>")

        lines.append("</tbody>")
    return "\n".join(lines)


def build_series_options(groups: list[dict]) -> str:
    """イベントのプルダウン。開催回数を添える。並びは SERIES の定義順。"""
    counts = Counter(g["series"] for g in groups)
    names = {g["series"]: g["event_name"] for g in groups}
    ids = [s for s in SERIES if s in counts] + [s for s in counts if s not in SERIES]
    out = []
    for sid in ids:
        label = names.get(sid, sid)
        out.append(f'<option value="{esc(sid)}">{esc(label)}（{counts[sid]}回）</option>')
    return "\n".join(out)


def build_name_options(groups: list[dict], master: dict[str, dict]) -> str:
    """出演者のプルダウン。よみがな順で、グループ絞り込み用に在籍歴を持たせる。"""
    counts = Counter()
    for g in groups:
        for n in g["names"]:
            counts[n] += 1
    out = []
    for name in sorted(counts, key=lambda n: yomi_key(n, master)):
        grp = "|".join(master.get(name, {}).get("groups", []))
        out.append(f'<option value="{esc(name)}" data-group="{esc(grp)}">'
                   f'{esc(name)}（{counts[name]}）</option>')
    return "\n".join(out)


def build_group_options(groups: list[dict], master: dict[str, dict]) -> str:
    """グループのプルダウン。出演者が在籍したことのあるグループだけを、人数の多い順に。"""
    counts = Counter()
    for name in {n for g in groups for n in g["names"]}:
        for grp in master.get(name, {}).get("groups", []):
            counts[grp] += 1
    out = []
    for grp, c in sorted(counts.items(), key=lambda x: (-x[1], x[0])):
        out.append(f'<option value="{esc(grp)}">{esc(grp)}</option>')
    return "\n".join(out)


def build_place_options(groups: list[dict]) -> str:
    counts = Counter(place_short(g["place"]) for g in groups)
    known = [p for p in PLACE_ORDER if p in counts]
    rest = sorted(p for p in counts if p not in PLACE_ORDER)
    return "\n".join(
        f'<option value="{esc(p)}">{esc(p)}（{counts[p]}）</option>' for p in known + rest
    )


def build_year_options(groups: list[dict]) -> str:
    years = sorted({g["date"].year for g in groups if g["date"]}, reverse=True)
    return "\n".join(f'<option value="{y}">{y}年</option>' for y in years)


# ---------------------------------------------------------------- 出力
def write(path: Path, body: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"
    path.write_text(stamp + body + "\n", encoding="utf-8")


def main() -> None:
    rows = [r for r in fetch_url(f"{PUB_BASE}?gid={GID_EVENTS}&single=true&output=csv")
            if get(r, "ID")]
    master = fetch_master()

    check_data(rows)
    warn_missing_members(
        sorted({n for r in rows for n in members_of(r)} - set(master)),
        note="(よみがな検索・グループ絞り込みの対象外になります)",
    )

    groups = build_groups(rows)

    write(OUTPUT_ROWS, build_rows_html(groups, master, fetch_blogs()))
    write(OUTPUT_SERIES, build_series_options(groups))
    write(OUTPUT_NAMES, build_name_options(groups, master))
    write(OUTPUT_GROUPS, build_group_options(groups, master))
    write(OUTPUT_PLACES, build_place_options(groups))
    write(OUTPUT_YEARS, build_year_options(groups))

    jst = timezone(timedelta(hours=9))
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.write_text(
        f'<p class="last-update">データ取得日：{datetime.now(jst).strftime("%Y/%m/%d")}</p>\n',
        encoding="utf-8",
    )

    print(f"生成: {len(groups)}回 / {len(rows)}部 "
          f"(出演者 {len({n for g in groups for n in g['names']})}人)")


if __name__ == "__main__":
    main()
