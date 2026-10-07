#!/usr/bin/env python3
"""
ハロー！プロジェクトのファンクラブツアー(バスツアー)の一覧。1ツアー=1カード。

スプレッドシートは4タブ:
  ツアー   (gid=0)
    ID,Group,Departure,Arrival,LiveDate,Title,Prefecture,LivePlace,LivePrefecture,
    YouTube,YouTube2,Live Title,Tomoko,Hello! Project Wiki
    ※ ヘッダーに末尾空白が付いているものがあるので strip して読む
    LiveDate/LivePlace … ツアー内のライブ。ツアーにライブが無く、同じ日に別チケットの
                         ライブがある回は Live Title にそのライブ名が入る(日付・会場は同じ列)
    Tomoko    … セトリ検索サイトのページ(あればこれを優先してセトリとしてリンク)
  お知らせ (gid=1634165374)
    ID,Group,Company,News,NewsWebArchive,TourCompany,NewsWebArchive
    ※ NewsWebArchive が2列あるので位置で読む。現存URLが無いときだけ魚拓をリンク
  メンバー (gid=1949586703)  ID,OG,Group,Member(縦持ち。OGは使わない)
  セトリ   (gid=309895065)   ID,Number,Song,Singer,Artist(縦持ち)
    Number … 1,2,3… の後に En1,En2…(アンコール。E1 表記も可)
    Singer … 空なら参加者全員

海外ツアー(Prefecture が47都道府県以外)は日程を yyyy/mm までにし、ライブ日付は出さない。

hello-funclubtour.php から include される。
"""

import csv
import html
import io
import re
import urllib.request
from collections import defaultdict
from datetime import date, datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn
from icons import icon

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQdHmGYiQ9iJWFRrc96q8f2O7vS9XVDN10JDVp5fAb_QKdyr9IyWZ2xSADfhsLgLGsA0vaa-nAnIvlt/pub"
)
GID_TOURS = "0"
GID_NEWS = "1634165374"
GID_MEMBERS = "1949586703"
GID_SETLIST = "309895065"

PREFECTURES = {
    "北海道", "青森県", "岩手県", "宮城県", "秋田県", "山形県", "福島県",
    "茨城県", "栃木県", "群馬県", "埼玉県", "千葉県", "東京都", "神奈川県",
    "新潟県", "富山県", "石川県", "福井県", "山梨県", "長野県", "岐阜県",
    "静岡県", "愛知県", "三重県", "滋賀県", "京都府", "大阪府", "兵庫県",
    "奈良県", "和歌山県", "鳥取県", "島根県", "岡山県", "広島県", "山口県",
    "徳島県", "香川県", "愛媛県", "高知県", "福岡県", "佐賀県", "長崎県",
    "熊本県", "大分県", "宮崎県", "鹿児島県", "沖縄県",
}

WEEKDAYS = "月火水木金土日"

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親
OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "bustour_cards.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "bustour_generated.php"


# ---------------------------------------------------------------- 取得・小物
def fetch_rows(gid: str) -> list[list[str]]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    return list(csv.reader(io.StringIO(content)))


def fetch_dicts(gid: str) -> list[dict]:
    rows = fetch_rows(gid)
    head = [h.strip() for h in rows[0]]
    return [{k: (v or "").strip() for k, v in zip(head, r)} for r in rows[1:]]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def parse_date(s: str) -> date | None:
    try:
        return datetime.strptime((s or "").strip(), "%Y/%m/%d").date()
    except ValueError:
        return None


def ymd(d: date) -> str:
    return f'{d.year}/{d.month:02d}/{d.day:02d}<span class="bt-dow">（{WEEKDAYS[d.weekday()]}）</span>'


def link(url: str, label: str, cls: str = "bt-link") -> str:
    return (f'<a class="{cls}" href="{esc(url)}" target="_blank"'
            f' rel="noopener noreferrer">{label}</a>')


# ---------------------------------------------------------------- 各タブ
def load_news() -> dict[str, dict[str, str]]:
    """ID → {fc, fc_arc, tour, tour_arc}。列名が重複しているので位置で読む。"""
    out = {}
    for r in fetch_rows(GID_NEWS)[1:]:
        r = [(c or "").strip() for c in (r + [""] * 7)[:7]]
        if r[0]:
            out[r[0]] = {"fc": r[3], "fc_arc": r[4], "tour": r[5], "tour_arc": r[6]}
    return out


def load_members() -> dict[str, str]:
    """ID → 「A・B（グループ）／C（グループ）」。グループはシートに出てきた順。"""
    by_id: dict[str, dict[str, list[str]]] = defaultdict(dict)
    for r in fetch_dicts(GID_MEMBERS):
        if r.get("ID") and r.get("Member"):
            by_id[r["ID"]].setdefault(r.get("Group", ""), []).append(r["Member"])
    return {
        i: "／".join(f"{'・'.join(ms)}（{g}）" if g else "・".join(ms) for g, ms in gs.items())
        for i, gs in by_id.items()
    }


def setlist_key(no: str) -> tuple:
    m = re.fullmatch(r"(En?)?(\d+)", no, re.I)
    if not m:
        return (2, 0)
    return (1 if m.group(1) else 0, int(m.group(2)))


def load_setlists() -> dict[str, list[dict]]:
    by_id: dict[str, list[dict]] = defaultdict(list)
    for r in fetch_dicts(GID_SETLIST):
        if r.get("ID") and r.get("Song"):
            if not re.fullmatch(r"(En?)?\d+", r.get("Number", ""), re.I):
                warn(f"{r['ID']}: セトリの Number '{r.get('Number')}' が読めません", title="セトリ番号異常")
            by_id[r["ID"]].append(r)
    for rows in by_id.values():
        rows.sort(key=lambda r: setlist_key(r.get("Number", "")))
    return by_id


# ---------------------------------------------------------------- 組み立て
def date_html(t: dict, overseas: bool) -> str:
    dep, arr = parse_date(t["Departure"]), parse_date(t["Arrival"])
    if not dep:
        if t["Departure"]:
            warn(f"{t['ID']}: Departure '{t['Departure']}' を日付として読めません", title="日付異常")
        return esc(t["Departure"])
    if overseas:
        return f"{dep.year}/{dep.month:02d}"
    if not arr or arr == dep:
        return ymd(dep)
    # 同じ年なら帰着日の年は省く
    arr_s = ymd(arr)[5:] if arr.year == dep.year else ymd(arr)
    return f"{ymd(dep)}〜{arr_s}"


def setlist_html(rows: list[dict]) -> str:
    body = []
    for r in rows:
        no = r.get("Number", "")
        no_disp = "アンコール" if no[:1].lower() == "e" else no
        song = esc(r["Song"]) + (f"／{esc(r['Singer'])}" if r.get("Singer") else "")
        body.append(f'<tr><td class="bt-sl-no">{esc(no_disp)}</td>'
                    f'<td>{song}</td><td>{esc(r.get("Artist", ""))}</td></tr>')
    return (
        '<details class="bt-setlist"><summary>セットリスト</summary>'
        '<table class="bt-sl-table"><thead><tr><th>No.</th><th>曲名／（歌唱者）</th>'
        '<th>原曲アーティスト</th></tr></thead><tbody>'
        + "".join(body) + "</tbody></table></details>"
    )


def card_html(t: dict, news: dict, members: dict, setlists: dict) -> str:
    tid = t["ID"]
    pref = t["Prefecture"]
    overseas = bool(pref) and pref not in PREFECTURES

    lines = [f'<article class="card bt-card" id="{esc(tid)}">']
    meta = [f'<span class="bt-date">{date_html(t, overseas)}</span>']
    if pref:
        meta.append(f'<span class="bt-pref{" bt-overseas" if overseas else ""}">{esc(pref)}</span>')
    lines.append(f'<p class="bt-meta">{"".join(meta)}</p>')
    lines.append(f'<h2 class="bt-title">{esc(t["Title"])}</h2>')

    mem = members.get(tid) or t["Group"]
    if not members.get(tid):
        warn(f"{tid}: メンバーシートに行がないので Group 列を表示しました", title="メンバー未入力")
    lines.append(f'<p class="bt-members">{esc(mem)}</p>')

    # ライブ: ツアー内(Live Title 空)か、別チケット(Live Title あり)か
    place = t["LivePlace"] + (f"（{t['LivePrefecture']}）" if t["LivePrefecture"] else "")
    live_date = "" if overseas else (ymd(d) if (d := parse_date(t["LiveDate"])) else "")
    if t["Live Title"]:
        detail = " ".join(x for x in (live_date, esc(place)) if x)
        lines.append('<p class="bt-live"><span class="bt-label bt-label-sep">別チケットのライブ</span>'
                     f'{esc(t["Live Title"])}'
                     + (f'<span class="bt-live-sub">{detail}</span>' if detail else "") + "</p>")
    elif place or live_date:
        detail = " ".join(x for x in (live_date, esc(place)) if x)
        lines.append(f'<p class="bt-live"><span class="bt-label">ツアー内ライブ</span>{detail}</p>')

    links = []
    n = news.get(tid, {})
    if n.get("fc"):
        links.append(link(n["fc"], "ファンクラブお知らせ"))
    elif n.get("fc_arc"):
        links.append(link(n["fc_arc"], "ファンクラブお知らせ（WebArchive）"))
    if n.get("tour"):
        links.append(link(n["tour"], "旅行会社お知らせ"))
    elif n.get("tour_arc"):
        links.append(link(n["tour_arc"], "旅行会社お知らせ（WebArchive）"))
    for col, label in (("YouTube", "YouTube"), ("YouTube2", "YouTube 2")):
        if t[col]:
            links.append(link(t[col], icon("youtube-brand") + label))
    if t["Hello! Project Wiki"]:
        links.append(link(t["Hello! Project Wiki"], "Hello! Project Wiki"))
    if t["Tomoko"]:
        links.append(link(t["Tomoko"], "セットリスト"))
    if links:
        lines.append(f'<p class="bt-links">{"".join(links)}</p>')

    # ToMoKo が無いときだけ手入力のセトリを折りたたみで出す
    if not t["Tomoko"] and setlists.get(tid):
        lines.append(setlist_html(setlists[tid]))
    elif t["Tomoko"] and setlists.get(tid):
        warn(f"{tid}: ToMoKo とセトリシートの両方があります(ToMoKoを優先)", title="セトリ重複")

    lines.append("</article>")
    return "\n".join(lines)


# ---------------------------------------------------------------- 出力
def write(path: Path, body: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"
    path.write_text(stamp + body + "\n", encoding="utf-8")


def main() -> None:
    tours = [t for t in fetch_dicts(GID_TOURS) if t.get("ID")]
    news, members, setlists = load_news(), load_members(), load_setlists()

    tours.sort(key=lambda t: (parse_date(t["Departure"]) or date.min, t["ID"]), reverse=True)
    write(OUTPUT_CARDS, "\n".join(card_html(t, news, members, setlists) for t in tours))

    jst = timezone(timedelta(hours=9))
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.write_text(
        f'<p class="last-update">データ取得日：{datetime.now(jst).strftime("%Y/%m/%d")}</p>\n',
        encoding="utf-8",
    )
    print(f"生成: {len(tours)}ツアー")


if __name__ == "__main__":
    main()
