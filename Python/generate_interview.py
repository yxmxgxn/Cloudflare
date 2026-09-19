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
import os
import re
import unicodedata
import urllib.parse
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn_missing_members, warn
import generate_members  # メンバー一覧スプシ(平家みちよから並ぶ)の行順を、カードのメンバー表示順に使う

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vQUuY0kp6M2i3IBrzIJ0rxkRI19oqwdGFUszeQvsTJyydQJZae5OXrVEgshlXsDR906hUOm6497dUsr/pub"
)
GID_MAIN = "0"            # 記事一覧: ID,URL,Date,Feature,Title,WebArcive (Mediaは持たない)
GID_MEMBERS = "600835955"  # メンバー一覧: ID,Group,Name (IDは記事一覧と対応)
GID_DOMAIN = "1500125775"  # Domain辞書: Prefix,Media (URLのhost[+第1パス]→媒体名)

# メンバーマスター(別スプレッドシート): Member,Yomigana,Group
# 全ハロメンのロスター。よみがな順ソートと、グループ→メンバー絞り込みに使う。
MASTER_URL = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vS7zVX-GcZhARaYFCFwkoKvtapNqcG3aZ8EwEE87VxPSpMGBRk-Wca3EVkcX9hEh4MX0CVY4G6m68SE/pub"
    "?gid=0&single=true&output=csv"
)

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "interview_cards.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "interview_groups.php"
OUTPUT_NAMES = REPO_ROOT / "Spreadsheet" / "interview_names.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "interview_generated.php"


def fetch_csv(gid: str) -> list[dict]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    return fetch_url(url)


def fetch_url(url: str) -> list[dict]:
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.DictReader(io.StringIO(content))
    return [row for row in reader]


def fetch_master() -> dict[str, dict]:
    """メンバーマスター: Member名 → {"yomigana": .., "stints": [{group, join, grad}]}。
    同名の複数行 = 在籍履歴(移籍)。JoinDate/GradDate は空でも可(空=無期限/現役扱い)。"""
    result: dict[str, dict] = {}
    for row in fetch_url(MASTER_URL):
        name = (row.get("Member") or "").strip()
        if not name:
            continue
        e = result.setdefault(name, {"yomigana": "", "stints": []})
        if not e["yomigana"]:
            e["yomigana"] = (row.get("Yomigana") or "").strip()
        g = (row.get("Group") or "").strip()
        if g:
            e["stints"].append({
                "group": g,
                "join": (row.get("JoinDate") or "").strip(),
                "grad": (row.get("GradDate") or "").strip(),
            })
    return result


def _int(s: str):
    s = (s or "").strip()
    return int(s) if s.isdigit() else None


def fetch_member_order() -> dict:
    """メンバー一覧スプシ(在籍日数ページと同じ。平家みちよから並ぶ)から、カード内の並び順に使う情報を読む。
    このシートは1人1行で、移籍したメンバーは今の(メインの)グループの枠にいる。
      グループ: 'Juice=Juice（こぶしファクトリー）' → メイン=Juice=Juice / 前=こぶしファクトリー
      グループNo: グループの並び / ソートID: メイングループ内の順 / サブID: 前のグループ内の順
    戻り値: {"people": {名前: {...}}, "group_no": {グループ名: グループNo}}"""
    data = generate_members.fetch_csv(generate_members.SHEET_GID)
    h = [x.strip() for x in data[0]]
    col = {k: h.index(k) for k in ("名前", "グループ", "グループNo", "ソートID", "サブID")}
    people: dict[str, dict] = {}
    group_no: dict[str, int] = {}
    for i, r in enumerate(data[1:]):
        r = r + [""] * (len(h) - len(r))
        name = r[col["名前"]].strip()
        if not name or name in people:
            continue
        g = r[col["グループ"]].strip()
        m = re.match(r"(.*?)[（(](.*)[）)]\s*$", g)
        main = (m.group(1) if m else g).strip()
        # 「・」はグループ名自体に含まれる(カントリー・ガールズ)ので区切りに使わない
        prev = [x.strip() for x in re.split(r"[、,/]", m.group(2))] if m else []
        gno = _int(r[col["グループNo"]])
        people[name] = {"row": i, "main": main, "prev": prev, "gno": gno,
                        "sort": _int(r[col["ソートID"]]), "sub": _int(r[col["サブID"]])}
        if gno is not None:
            group_no.setdefault(main, gno)
    return {"people": people, "group_no": group_no}


def member_sort_key(name: str, era_group: str, order: dict):
    """カード内のメンバーの並び。記事の時点で在籍していたグループ(era_group)の枠で並べる:
    今のグループの記事ならソートID、前のグループの記事ならサブID。
    グループ同士はグループNo順。一覧に無い人は最後(sortedは安定なので入力順のまま)。"""
    p = order["people"].get(name)
    if not p:
        return (1, 0, 0, 0)
    big = 10 ** 6
    if era_group and era_group != p["main"] and era_group in p["prev"]:
        gno, pos = order["group_no"].get(era_group, p["gno"]), p["sub"]
    else:
        gno, pos = p["gno"], p["sort"]
    return (0, big if gno is None else gno, big if pos is None else pos, p["row"])


def fetch_domain() -> dict[str, str]:
    """Domain辞書: プレフィックス(host または host/第1パス) → 媒体名。小文字化して保持。"""
    result: dict[str, str] = {}
    for row in fetch_csv(GID_DOMAIN):
        pref = (row.get("Prefix") or "").strip().lower()
        media = (row.get("Media") or "").strip()
        if pref and media:
            result[pref] = media
    return result


def media_for(url: str, domain: dict[str, str]) -> str:
    """記事URLの媒体名をDomain辞書から最長前方一致で引く。
    host+第1パス(例 realsound.jp/tech) を優先し、無ければ host(例 realsound.jp)。
    www. の有無はどちらでも当たるようにする。未一致は空文字。"""
    if not url:
        return ""
    pr = urllib.parse.urlparse(url if "//" in url else "https://" + url)
    host = pr.netloc.lower()
    segs = [s for s in pr.path.split("/") if s]
    seg = segs[0].lower() if segs else ""
    hosts = [host, host[4:] if host.startswith("www.") else "www." + host]
    for h in hosts:                        # 具体的(host+seg)を先に
        if seg and f"{h}/{seg}" in domain:
            return domain[f"{h}/{seg}"]
    for h in hosts:                        # 次に host のみ
        if h in domain:
            return domain[h]
    return ""


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


def _pad_date(s: str, is_end: bool) -> str:
    """'2017' / '2017/6/11' / '2017年6月11日' を 'YYYY-MM-DD' に。欠けは開始なら01/01、終了なら12/31。"""
    parts = re.findall(r"\d+", s or "")  # 数字だけ拾う(区切り・「頃」などは無視)
    if not parts:
        return ""
    y = parts[0].zfill(4)
    m = parts[1].zfill(2) if len(parts) >= 2 else ("12" if is_end else "01")
    d = parts[2].zfill(2) if len(parts) >= 3 else ("31" if is_end else "01")
    return f"{y}-{m}-{d}"


def disp_date(s: str) -> str:
    """表示用: どの形式('2019年5月23日'・'2019/5/23'・'2019')でも yyyy/mm/dd に整形(粒度は保つ)。"""
    parts = re.findall(r"\d+", s or "")  # 数字だけ拾う(区切り・「頃」などは無視)
    if not parts:
        return ""
    out = parts[0].zfill(4)
    if len(parts) >= 2:
        out += "/" + parts[1].zfill(2)
    if len(parts) >= 3:
        out += "/" + parts[2].zfill(2)
    return out


def _stint_active(stint: dict, date_norm: str) -> bool:
    """記事日(YYYY-MM-DD)が在籍期間内か。Join/Grad空は無期限(=現役扱い)。"""
    lo = _pad_date(stint["join"], False) or "0000-00-00"
    hi = _pad_date(stint["grad"], True) or "9999-99-99"
    return lo <= date_norm <= hi


def member_info(name: str, date_norm: str, master: dict):
    """(表示グループ, 現役か, 現役グループ, 全在籍グループ) を返す。
    表示グループ = 記事日時点の在籍グループ / 無ければ直近(最後)のグループ。
    名簿に無い人は現役判定不能→現役扱い(除外しない)・グループ空。"""
    info = master.get(name)
    if not info or not info["stints"]:
        return ("", True, "", [])
    stints = info["stints"]
    all_groups = list(dict.fromkeys(s["group"] for s in stints if s["group"]))
    for s in stints:
        if _stint_active(s, date_norm):
            return (s["group"], True, s["group"], all_groups)
    return (stints[-1]["group"], False, "", all_groups)  # 回顧記事: 直近グループを表示


def build_members_block(names: list[str], groups_display: list[str]) -> str:
    """メンバー名を1行、その下にグループを列挙(重複まとめ・元グループも名前のまま)。
    名前は押すとメンバー絞り込み(プルダウン)にその人をセットするボタン。"""
    if not names:
        return ""
    buttons = "・".join(
        f'<button type="button" class="iv-member" data-name="{esc(n)}"'
        f' title="{esc(n)}さんの記事だけ表示">{esc(n)}</button>' for n in names)
    name_line = f'<p class="iv-member-names">{buttons}</p>'
    group_line = ""
    if groups_display:
        group_line = f'<p class="iv-member-groups">{"・".join(esc(g) for g in groups_display)}</p>'
    return f'<div class="iv-members">{name_line}{group_line}</div>'


def build_cards(articles: list[dict], members_by_id: dict, master: dict,
                domain: dict, unmatched: dict, member_order: dict) -> str:
    cards = []
    for a in articles:
        aid = (a.get("ID") or "").strip()
        if not aid:
            continue

        date_norm = _pad_date(a.get("Date"), False)   # YYYY-MM-DD (ソート/範囲/現役判定)
        date_disp = esc(disp_date(a.get("Date")))      # 表示は yyyy/mm/dd
        date_sort = esc(date_norm)
        title = esc(a.get("Title"))
        url = (a.get("URL") or "").strip()
        if url == "-":
            url = ""  # ライブリンク切れ(WebArciveのみ)のフラグ。リンク化・媒体解決の対象外にする
        media_raw = media_for(url, domain)   # 媒体名はDomain辞書から引く
        if url and not media_raw:
            unmatched[url] = aid
        media = esc(media_raw)
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

        # 2段目: Feature / Media (あるものだけ)。どちらも押すと同じ値の記事に絞り込むボタン
        feature_html = (f'<button type="button" class="iv-feature" data-feature="{feature}"'
                        f' title="この連載・企画の記事だけ表示">{feature}</button>') if feature else ""
        media_html = (f'<button type="button" class="iv-media" data-media="{media}"'
                      f' title="この媒体の記事だけ表示">{media}</button>') if media else ""
        sub_parts = [x for x in [feature_html, media_html] if x]
        sub = f'<p class="iv-sub">{" / ".join(sub_parts)}</p>' if sub_parts else ""

        # 3段目以降: メンバー(名前の行) + グループの行
        names = list(dict.fromkeys(
            (m.get("Name") or "").strip() for m in members_by_id.get(aid, []) if (m.get("Name") or "").strip()))
        # スプシへの入力順ではなく、メンバー一覧の並び(公式に近い順)で表示する
        infos = {n: member_info(n, date_norm, master) for n in names}
        names = sorted(names, key=lambda n: member_sort_key(n, infos[n][0], member_order))
        disp_groups, all_groups, active_groups, active_names = [], [], [], []
        for n in names:
            dgroup, active, agroup, mgroups = infos[n]
            if dgroup:
                disp_groups.append(dgroup)
            all_groups.extend(mgroups)
            if active:
                active_names.append(n)
                if agroup:
                    active_groups.append(agroup)
        disp_groups = list(dict.fromkeys(disp_groups))
        members_html = build_members_block(names, disp_groups)

        # 最後: 魚拓リンク(右寄せ)。元記事が読めるなら出さない。
        # URLが "-"(リンク切れ) か空欄で、元記事へのリンクが無いカードだけ魚拓を見せる。
        arc = ""
        if archive and not url:
            if re.search(r"archive\.(today|ph|is|md|li|vn|fo)", archive):
                label = "archive.today"
            else:
                # web.archive.org: タイムスタンプ(/web/YYYYMMDD...)から日付を添える
                label = "Web Archive"
                md = re.search(r"/web/(\d{4})(\d{2})(\d{2})", archive)
                if md:
                    label += f"（{md.group(1)}/{md.group(2)}/{md.group(3)}）"
            arc = (
                '<p class="iv-arc"><a href="' + esc(archive) + '"'
                ' target="_blank" rel="noopener noreferrer">' + label + '</a></p>'
            )

        search = " ".join([a.get("Title") or "", media_raw,
                           a.get("Feature") or "", " ".join(names)])

        cards.append(
            f'<div class="card iv-card" data-id="{esc(aid)}" data-date="{date_sort}" data-title="{title}"'
            f' data-feature="{feature}" data-media="{media}"'
            f' data-groups="{esc("|".join(dict.fromkeys(all_groups)))}"'
            f' data-active-groups="{esc("|".join(dict.fromkeys(active_groups)))}"'
            f' data-names="{esc("|".join(names))}"'
            f' data-active-names="{esc("|".join(dict.fromkeys(active_names)))}"'
            f' data-search="{esc(search)}">'
            f"{head}{sub}{members_html}{arc}"
            "</div>"
        )
    return "\n".join(cards)


def build_group_options(groups: list[str]) -> str:
    return "\n".join(f'<option value="{esc(v)}">{esc(v)}</option>' for v in groups)


def member_all_groups(name: str, master: dict) -> list[str]:
    info = master.get(name)
    if not info:
        return []
    return list(dict.fromkeys(s["group"] for s in info["stints"] if s["group"]))


def build_name_options(names: list[str], master: dict) -> str:
    """メンバー選択肢。よみがな順で並び、グループ絞り込み用に data-group(在籍歴すべて・|区切り)を持たせる。"""
    lines = []
    for n in names:
        g = "|".join(member_all_groups(n, master))
        lines.append(f'<option value="{esc(n)}" data-group="{esc(g)}">{esc(n)}</option>')
    return "\n".join(lines)


def main():
    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    articles = fetch_csv(GID_MAIN)
    member_rows = fetch_csv(GID_MEMBERS)
    master = fetch_master()
    domain = fetch_domain()
    unmatched: dict[str, str] = {}  # URL → 記事ID

    members_by_id: dict[str, list[dict]] = {}
    for m in member_rows:
        mid = (m.get("ID") or "").strip()
        if mid:
            members_by_id.setdefault(mid, []).append(m)

    # インタビューに登場するメンバー(=選択肢に出す対象)
    name_set = {(m.get("Name") or "").strip() for m in member_rows if (m.get("Name") or "").strip()}

    def yomi_key(n: str):
        return (master.get(n, {}).get("yomigana") or n, n)

    names_sorted = sorted(name_set, key=yomi_key)  # あいうえお順
    # グループ選択肢 = 登場メンバーの在籍歴グループ(卒業無関係)
    groups = sorted({g for n in name_set for g in member_all_groups(n, master)})

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(articles, members_by_id, master, domain, unmatched,
                                    fetch_member_order()) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_group_options(groups) + "\n")
    with open(OUTPUT_NAMES, "w", encoding="utf-8") as f:
        f.write(stamp + build_name_options(names_sorted, master) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    warn_missing_members(
        sorted(n for n in name_set if n not in master),
        note="(記事側Groupで代替)",
    )
    if unmatched:
        warn("Domainシート未登録(媒体名が空になる): " + "、".join(sorted(unmatched)),
             title="Domain未登録URL")
    write_domain_report(unmatched)
    write_member_report(member_rows, master)
    print(f"生成完了: {OUTPUT_CARDS} ({len(articles)} 記事 / メンバー {len(name_set)} 名 / "
          f"Domain未登録 {len(unmatched)} 件)")


def write_domain_report(unmatched: dict[str, str]) -> None:
    """Domain未登録URLのIssue本文(Markdown)を書き出す。0件なら空ファイル(=Issueを閉じる合図)。
    出力先は環境変数 INTERVIEW_DOMAIN_REPORT(Actionsが一時ディレクトリを指定)。未設定なら何もしない。
    本文は実行ごとに変わる情報(日時・実行URL)を含めない: 内容が同じなら通知しないための比較に使う。"""
    path = os.environ.get("INTERVIEW_DOMAIN_REPORT")
    if not path:
        return
    if not unmatched:
        Path(path).write_text("", encoding="utf-8")
        return
    by_host: dict[str, list[tuple[str, str]]] = {}
    for url, aid in unmatched.items():
        host = urllib.parse.urlparse(url).netloc.lower().removeprefix("www.")
        by_host.setdefault(host, []).append((aid, url))
    lines = [
        "インタビュー記事のURLのうち、Domainシートのどの Prefix にも一致しないものがあります。",
        "該当記事は媒体名が空欄で表示されています。Domainシートに `Prefix, Media` を1行足すと、次回の生成で解消します。",
        "",
        "| 候補Prefix | 件数 | 記事 |",
        "|---|---|---|",
    ]
    for host in sorted(by_host):
        items = sorted(by_host[host])
        refs = "<br>".join(f"{aid} {url}" for aid, url in items)
        label = f"`{host}`" if host else "⚠ URL欄がURLではない"  # タイトル等を誤って入れた行
        lines.append(f"| {label} | {len(items)} | {refs} |")
    Path(path).write_text("\n".join(lines) + "\n", encoding="utf-8")


# 旧字・異体字 → 通用字。表記ゆれの候補探しにだけ使う
_VARIANTS = str.maketrans({"﨑": "崎", "髙": "高", "濵": "浜", "邊": "辺", "邉": "辺", "齊": "斉", "齋": "斎"})


def _loose(name: str) -> str:
    return re.sub(r"\s", "", unicodedata.normalize("NFKC", name)).translate(_VARIANTS)


def write_member_report(member_rows: list[dict], master: dict) -> None:
    """メンバーシートの名前がよみがなマスターに無いときのIssue本文を書き出す。0件なら空ファイル。
    出力先は環境変数 INTERVIEW_MEMBER_REPORT。スペース・旧字違いならマスター側の表記を候補に出す。"""
    path = os.environ.get("INTERVIEW_MEMBER_REPORT")
    if not path:
        return
    missing: dict[str, list[str]] = {}
    for m in member_rows:
        n = (m.get("Name") or "").strip()
        if n and n not in master:
            missing.setdefault(n, []).append((m.get("ID") or "").strip())
    if not missing:
        Path(path).write_text("", encoding="utf-8")
        return
    loose = {_loose(k): k for k in master}
    lines = [
        "インタビューのメンバーシートに、よみがなマスターに無い名前があります。",
        "マスターと表記が違うと別人扱いになり、メンバー絞り込みで出てきません。",
        "",
        "| 名前 | 記事 | 候補 |",
        "|---|---|---|",
    ]
    for n in sorted(missing):
        cand = loose.get(_loose(n))
        lines.append(f"| `{n}` | {'、'.join(sorted(missing[n]))} | {f'`{cand}` に直す' if cand else '(マスターに追加?)'} |")
    Path(path).write_text("\n".join(lines) + "\n", encoding="utf-8")


if __name__ == "__main__":
    main()
