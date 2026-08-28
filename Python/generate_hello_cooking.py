#!/usr/bin/env python3
"""
クッキングコーナーのスプレッドシート(Member+Meal+URLの3枚)を読み取り、
PHPインクルード用のカード・フィルタ選択肢フラグメントを生成する

  Memberシート : ID,Member1,Member2,Cook   (1ID=1行、Member2/Cookは空欄可)
  Mealシート   : ID,Meal,Genre,Category,Type (1IDに料理が複数行=複数品、タグは空欄可)
  URLシート    : ID,Channel,Episode,Status,URL (1IDに動画が複数行=最大4本)

同じIDで3枚を突き合わせて1枚のカードにする。IDが大きいものが上。
更新頻度が低いので手動実行(generate-manual.ymlのcooking)で回す。
ローカル確認用にCSVパスも渡せる:
  python Python/generate_hello_cooking.py --member m.csv --meal me.csv --url u.csv
"""

import argparse
import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

from icons import icon

from gh_warn import warn_missing_members

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vTJIq52Z-varZUNgAlES7Y9LWatAXsX2Q31pgkm1Vfw-U1X87Tbxu40PWyA40EAz_BWA3dm5R6ZvLzU/pub"
)
GID_MEMBER = "1716951336"
GID_MEAL = "1055941910"
GID_URL = "1286538905"

# メンバーマスター(別スプレッドシート): Member,Yomigana,Group
# 全ハロメンのロスター。よみがな順ソートと、グループ→メンバー絞り込みに使う。
# 他プログラムでも使う共通マスターなので、余分なメンバーを含む。
MASTER_URL = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vS7zVX-GcZhARaYFCFwkoKvtapNqcG3aZ8EwEE87VxPSpMGBRk-Wca3EVkcX9hEh4MX0CVY4G6m68SE/pub"
    "?gid=0&single=true&output=csv"
)

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "cooking_cards.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "cooking_groups.php"
OUTPUT_NAMES = REPO_ROOT / "Spreadsheet" / "cooking_names.php"
OUTPUT_GENRES = REPO_ROOT / "Spreadsheet" / "cooking_genres.php"
OUTPUT_CATEGORIES = REPO_ROOT / "Spreadsheet" / "cooking_categories.php"
OUTPUT_TYPES = REPO_ROOT / "Spreadsheet" / "cooking_types.php"
OUTPUT_SUBCATS = REPO_ROOT / "Spreadsheet" / "cooking_subcats.php"
OUTPUT_COOKS = REPO_ROOT / "Spreadsheet" / "cooking_cooks.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "cooking_generated.php"


MAX_VIDEOS = 4  # 1IDあたり表示する動画の上限

# フィルタの表示順(この順に並べ、リストにない値は末尾に回す)
GENRE_ORDER = ["和食", "洋食", "インド", "タイ", "韓国", "中華", "その他"]
CATEGORY_ORDER = ["主食", "主菜", "副菜", "汁物", "デザート", "その他"]
TYPE_ORDER = ["肉", "魚", "野菜", "卵", "豆腐", "その他"]
# SubCatはデザートのときだけ食材フィルタの代わりに使う
SUBCAT_ORDER = ["焼き菓子", "高温調理", "蒸し菓子", "煮物", "冷菓", "和菓子", "下準備"]
COOK_ORDER = ["宇部先生", "黒木先生", "長澤先生"]
GROUP_ORDER = [
    "OG", "モーニング娘。", "アンジュルム", "Juice=Juice", "つばきファクトリー",
    "BEYOOOOONDS", "OCHA NORMA", "ロージークロニクル", "こぶしファクトリー",
    "カントリー・ガールズ", "Berryz工房", "℃-ute", "アップアップガールズ（仮）",
    "チャオ ベッラ チンクエッティ", "Bitter & Sweet", "PINK CRES.", "LoVendoЯ", "吉川友",
]

# Member列 → 対応するGroup列
MEMBER_COLS = [("Member1", "Group1"), ("Member2", "Group2")]


def order_by(values: set[str], order: list[str]) -> list[str]:
    def key(v: str):
        return (order.index(v), "") if v in order else (len(order), v)
    return sorted(values, key=key)


def fetch_csv(source: str) -> list[dict]:
    """URLでもローカルのCSVパスでも読める。ヘッダの前後空白は除去する"""
    path = Path(source)
    if path.exists():
        content = path.read_text(encoding="utf-8-sig")
    else:
        with urllib.request.urlopen(source) as res:
            content = res.read().decode("utf-8")
    reader = csv.reader(io.StringIO(content))
    rows = list(reader)
    if not rows:
        return []
    header = [h.strip() for h in rows[0]]  # "Category " のような末尾空白を吸収
    result = []
    for raw in rows[1:]:
        raw += [""] * (len(header) - len(raw))
        result.append({header[i]: raw[i] for i in range(len(header))})
    return result


def fetch_master(source: str) -> dict[str, dict]:
    """メンバーマスター: Member名 → {"yomigana": ..., "group": ...}"""
    result: dict[str, dict] = {}
    for row in fetch_csv(source):
        name = val(row, "Member")
        if not name:
            continue
        result[name] = {
            "yomigana": val(row, "Yomigana"),
            "group": val(row, "Group"),
        }
    return result


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def val(row: dict, key: str) -> str:
    return (row.get(key) or "").strip()


def resolve_group(name: str, master: dict, fallback: dict) -> str:
    """絞り込み用グループ: マスター(名簿)を優先、無ければ料理当時のGroupで代替。"""
    return master.get(name, {}).get("group") or fallback.get(name, "")


def name_span(name: str) -> str:
    """メンバー名をクリックで絞り込める <span> に包む。
    「（A＆B）」の合体表記(デュオ回)は個人の選択肢が無いのでクリック対象にしない。"""
    if name.startswith(("（", "(")):
        return esc(name)
    return f'<span class="ck-name" data-name="{esc(name)}">{esc(name)}</span>'


def build_title(row: dict) -> str:
    """1行目: 出演メンバー(・区切り、所属を（ ）付きで)"""
    parts = []
    for mkey, gkey in MEMBER_COLS:
        name = val(row, mkey)
        if not name:
            continue
        grp = val(row, gkey)
        parts.append(f"{name_span(name)}（{esc(grp)}）" if grp else name_span(name))
    return f'<p class="ck-title">{"・".join(parts)}</p>'


def build_cook(row: dict) -> str:
    """上段の右下に寄せる講師表記"""
    cook = val(row, "Cook")
    return f'<p class="ck-cook">講師：{esc(cook)}</p>' if cook else ""


def build_meals(meals: list[dict]) -> str:
    """料理名 + (ジャンル・カテゴリ・種類) を列挙"""
    if not meals:
        return ""
    lines = []
    for m in meals:
        meal = val(m, "Meal")
        if not meal:
            continue
        tags = [val(m, "Genre"), val(m, "Category"), val(m, "Type"), val(m, "SubCat")]
        tags = [t for t in tags if t]
        tag_html = f'<span class="ck-tags">{esc("・".join(tags))}</span>' if tags else ""
        lines.append(f"<li>{esc(meal)}{tag_html}</li>")
    return f'<ul class="ck-meals">{"".join(lines)}</ul>' if lines else ""


def build_videos(videos: list[dict]) -> str:
    """動画リンク(チャンネル + 話数 + ステータス)。最大MAX_VIDEOS本"""
    lines = []
    for v in videos[:MAX_VIDEOS]:
        url = val(v, "URL")
        if not url:
            continue
        label = " ".join(x for x in [val(v, "Channel"), val(v, "Episode"), val(v, "Status")] if x)
        lines.append(
            f'<li>{icon("youtube-brand", "ic")}<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">'
            f'{esc(label) or esc(url)}</a></li>'
        )
    if not lines:
        return '<p class="ck-novideo">動画なし</p>'
    return f'<ul class="ck-videos">{"".join(lines)}</ul>'


def build_cards(members: list[dict], meals_by_id: dict, videos_by_id: dict,
                master: dict, fallback: dict) -> str:
    def order(row):
        eid = val(row, "ID")
        try:
            return (0, -int(eid))          # 数値IDは降順
        except ValueError:
            return (1, 0)                  # 非数値は後ろ

    cards = []
    for row in sorted(members, key=order):
        eid = val(row, "ID")
        if not eid:
            continue
        meals = meals_by_id.get(eid, [])
        videos = videos_by_id.get(eid, [])

        genres = order_by({val(m, "Genre") for m in meals if val(m, "Genre")}, GENRE_ORDER)
        categories = order_by({val(m, "Category") for m in meals if val(m, "Category")}, CATEGORY_ORDER)
        types = order_by({val(m, "Type") for m in meals if val(m, "Type")}, TYPE_ORDER)
        subcats = order_by({val(m, "SubCat") for m in meals if val(m, "SubCat")}, SUBCAT_ORDER)
        # 出演メンバー(絞り込み用)。グループは名簿(マスター)基準で解決する
        member_names = [val(row, mkey) for mkey, _ in MEMBER_COLS if val(row, mkey)]
        groups = order_by(
            {resolve_group(n, master, fallback) for n in member_names
             if resolve_group(n, master, fallback)},
            GROUP_ORDER,
        )
        cook = val(row, "Cook")

        search_parts = [
            *member_names, cook,
            *[val(row, gkey) for _, gkey in MEMBER_COLS],
            *[val(m, "Meal") for m in meals],
        ]
        search = " ".join(p for p in search_parts if p)

        cards.append(
            f'<div class="card ck-card" id="ck-{esc(eid)}"'
            f' data-groups="{esc("|".join(groups))}"'
            f' data-names="{esc("|".join(dict.fromkeys(member_names)))}"'
            f' data-cook="{esc(cook)}"'
            f' data-genres="{esc("|".join(genres))}"'
            f' data-categories="{esc("|".join(categories))}"'
            f' data-types="{esc("|".join(types))}"'
            f' data-subcats="{esc("|".join(subcats))}"'
            f' data-search="{esc(search)}">'
            f'<div class="ck-top">'
            f"{build_title(row)}"
            f"{build_meals(meals)}"
            f"{build_cook(row)}"
            f"</div>"
            f"{build_videos(videos)}"
            "</div>"
        )
    return "\n".join(cards)


def build_options(values: list[str]) -> str:
    return "\n".join(f'<option value="{esc(v)}">{esc(v)}</option>' for v in values)


def build_name_options(names: list[str], master: dict, fallback: dict) -> str:
    """メンバー選択肢。よみがな順で並び、グループ絞り込み用に data-group を持たせる。"""
    return "\n".join(
        f'<option value="{esc(n)}" data-group="{esc(resolve_group(n, master, fallback))}">{esc(n)}</option>'
        for n in names
    )


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--member", default=f"{PUB_BASE}?gid={GID_MEMBER}&single=true&output=csv")
    parser.add_argument("--meal", default=f"{PUB_BASE}?gid={GID_MEAL}&single=true&output=csv")
    parser.add_argument("--url", default=f"{PUB_BASE}?gid={GID_URL}&single=true&output=csv")
    parser.add_argument("--master", default=MASTER_URL)
    args = parser.parse_args()

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    members = fetch_csv(args.member)
    meals = fetch_csv(args.meal)
    videos = fetch_csv(args.url)
    master = fetch_master(args.master)

    # マスター(名簿)に無いメンバーのフォールバック: 料理当時のGroup1/Group2(最初の非空)
    fallback_group: dict[str, str] = {}
    for r in members:
        for mkey, gkey in MEMBER_COLS:
            nm, g = val(r, mkey), val(r, gkey)
            if nm and g and nm not in fallback_group:
                fallback_group[nm] = g

    meals_by_id: dict[str, list[dict]] = {}
    for m in meals:
        mid = val(m, "ID")
        if mid:
            meals_by_id.setdefault(mid, []).append(m)

    videos_by_id: dict[str, list[dict]] = {}
    for v in videos:
        vid = val(v, "ID")
        if vid:
            videos_by_id.setdefault(vid, []).append(v)

    genres = order_by({val(m, "Genre") for m in meals if val(m, "Genre")}, GENRE_ORDER)
    categories = order_by({val(m, "Category") for m in meals if val(m, "Category")}, CATEGORY_ORDER)
    types = order_by({val(m, "Type") for m in meals if val(m, "Type")}, TYPE_ORDER)
    subcats = order_by({val(m, "SubCat") for m in meals if val(m, "SubCat")}, SUBCAT_ORDER)

    # 料理に登場するメンバー。マスターの余分な人は出さない
    cooking_names = {val(r, mkey) for r in members
                     for mkey, _ in MEMBER_COLS if val(r, mkey)}
    # メンバー選択肢に出す対象。「（A＆B）」のような合体表記(デュオ回)は個人名でないので除外
    dropdown_names = {n for n in cooking_names if not n.startswith(("（", "("))}

    def yomi_key(n: str):
        return (master.get(n, {}).get("yomigana") or n, n)

    names_sorted = sorted(dropdown_names, key=yomi_key)  # あいうえお順
    groups = order_by(
        {resolve_group(n, master, fallback_group) for n in cooking_names
         if resolve_group(n, master, fallback_group)},
        GROUP_ORDER,
    )
    cooks = order_by({val(r, "Cook") for r in members if val(r, "Cook")}, COOK_ORDER)

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(members, meals_by_id, videos_by_id,
                                    master, fallback_group) + "\n")
    with open(OUTPUT_GROUPS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(groups) + "\n")
    with open(OUTPUT_NAMES, "w", encoding="utf-8") as f:
        f.write(stamp + build_name_options(names_sorted, master, fallback_group) + "\n")
    with open(OUTPUT_COOKS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(cooks) + "\n")
    with open(OUTPUT_GENRES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(genres) + "\n")
    with open(OUTPUT_CATEGORIES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(categories) + "\n")
    with open(OUTPUT_TYPES, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(types) + "\n")
    with open(OUTPUT_SUBCATS, "w", encoding="utf-8") as f:
        f.write(stamp + build_options(subcats) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{generated}</div>\n')

    ids = {val(r, "ID") for r in members if val(r, "ID")}
    warn_missing_members(
        sorted(n for n in dropdown_names if n not in master),
        note="(料理側Groupで代替・よみがな順が名前基準になる)",
    )
    print(f"生成完了: {OUTPUT_CARDS} "
          f"({len(ids)} 回 / メンバー {len(cooking_names)} 名 / "
          f"料理 {len(meals)} 品 / 動画 {len(videos)} 本)")


if __name__ == "__main__":
    main()
