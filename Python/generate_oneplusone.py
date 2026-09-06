#!/usr/bin/env python3
"""
ハロプロのデュオ企画(ONE PLUS ONE / COVERS -One on One- / ハロドリ。1アングルバージョン！)の
スプレッドシートを読み取り、PHPインクルード用のカードフラグメントを生成する

  シート : video_id,Feature,Title,Member1,Group1,Member2,Group2,Kenshusei,published_jst,url,Artist
    Feature   企画名。同じ曲でONE PLUS ONE版とハロドリ版の2行になる
    Title     披露した曲名
    MemberN   1対1の2人。GroupN はそのときの所属(OGは "OG")
    Kenshusei ハロプロ研修生がその後どこに入ったか。表示せず、グループ絞り込みにだけ効かせる
    Artist    原曲のアーティスト名(あとから埋める列。空なら出さない)

  同じ曲・同じ2人の行はまとめて1枚のカードにし、中に企画ごとの動画リンクを並べる。

新しい動画が出たときだけの更新なので手動実行(generate-manual.ymlのoneplusone)で回す。
ローカル確認用にCSVファイルのパスも渡せる:
  python Python/generate_oneplusone.py --csv oneonone.csv
"""

import argparse
import csv
import html
import io
import re
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn
from icons import icon_for_url

CSV_URL = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vR10r0yJgbZSz6_O6Mp2zL2lGQy9-_Bh-CEztUFIG_bDmUFTO-ce8LE5cNHy2ag0XiacEdUFkDjvBix/pub"
    "?gid=0&single=true&output=csv"
)

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "oneplusone_cards.php"
OUTPUT_FEATURES = REPO_ROOT / "Spreadsheet" / "oneplusone_features.php"
OUTPUT_GROUPS = REPO_ROOT / "Spreadsheet" / "oneplusone_groups.php"
OUTPUT_NAMES = REPO_ROOT / "Spreadsheet" / "oneplusone_names.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "oneplusone_generated.php"

# シートのFeature表記 → 表示名。シート側が略記なので、ここで正式名に直す。
FEATURE_LABELS = {
    "COVERS -One on One": "COVERS -One on One-",
    "ONE PLUS ONE": "ONE PLUS ONE",
}

# ハロドリ版の判定と、カード内リンクで省く番組名の接頭辞。
# シート側が「ハロドリ。1アングルバージョン！」でも「1アングルバージョン」でも
# 同じ企画として扱い、末尾の「！」の有無はシートの書き方をそのまま活かす。
HALODORI_MARK = "1アングルバージョン"
HALODORI_PREFIX = "ハロドリ。"

# カードの代表行に選ぶ優先順位。ハロドリ版は本編(ONE PLUS ONE)の派生なので後ろ。
FEATURE_ORDER = ["ONE PLUS ONE", "COVERS -One on One-"]

# グループ絞り込みの並び順。ここに無いものは末尾にまわす。
GROUP_ORDER = [
    "モーニング娘。",
    "アンジュルム",
    "Juice=Juice",
    "つばきファクトリー",
    "BEYOOOOONDS",
    "OCHA NORMA",
    "ロージークロニクル",
    "ハロプロ研修生",
    "OG",
]

# 原曲アーティスト列の想定名(あとから足す列なので表記の揺れを吸収する)
ARTIST_KEYS = ("Artist", "原曲アーティスト", "OriginalArtist")


def fetch_csv(source: str) -> list[dict]:
    """URLでもローカルのCSVパスでも読める"""
    path = Path(source)
    if path.exists():
        content = path.read_text(encoding="utf-8-sig")
    else:
        with urllib.request.urlopen(source) as res:
            content = res.read().decode("utf-8")
    return [row for row in csv.DictReader(io.StringIO(content))]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def val(row: dict, key: str) -> str:
    """末尾のカンマ混入と全角アポストロフィを吸収して読む"""
    s = (row.get(key) or "").strip().rstrip(",").strip()
    return s.replace("’", "'")


def artist_of(row: dict) -> str:
    for key in ARTIST_KEYS:
        if val(row, key):
            return val(row, key)
    return ""


def feature_label(row: dict) -> str:
    """シートの表記ゆれ(全角スペース等)を落として表示名に寄せる"""
    raw = re.sub(r"[\s　]+", "", val(row, "Feature"))
    for key, label in FEATURE_LABELS.items():
        if re.sub(r"[\s　]+", "", key) == raw:
            return label
    # ハロドリ版は「ハロドリ。」が省かれていても補う(末尾の「！」はシートのまま)
    if HALODORI_MARK in raw and not raw.startswith(HALODORI_PREFIX):
        return HALODORI_PREFIX + raw
    return raw


def feature_short(label: str) -> str:
    """カード内リンク用。ONE PLUS ONEと並ぶので番組名の接頭辞は省く"""
    if HALODORI_MARK in label and label.startswith(HALODORI_PREFIX):
        return label[len(HALODORI_PREFIX):]
    return label


def feature_rank(label: str) -> int:
    """カードの代表行を決める並び。ハロドリ版は本編の派生なので最後にまわす"""
    if label in FEATURE_ORDER:
        return FEATURE_ORDER.index(label)
    return len(FEATURE_ORDER) + (1 if HALODORI_MARK in label else 0)


def canon_group(group: str) -> str:
    """モーニング娘。'22 → モーニング娘。 絞り込みは年次をまたいで1つに集約する"""
    g = (group or "").strip().rstrip(",").strip().replace("’", "'")
    return re.sub(r"'\d{2}$", "", g).strip()


def format_date(date_str: str) -> str:
    """2021-05-01 → 2021年5月1日"""
    s = (date_str or "").strip()
    for fmt in ("%Y-%m-%d", "%Y/%m/%d", "%Y年%m月%d日"):
        try:
            dt = datetime.strptime(s, fmt)
            return f"{dt.year}年{dt.month}月{dt.day}日"
        except ValueError:
            continue
    return s


def sort_key_date(date_str: str) -> str:
    s = (date_str or "").strip()
    for fmt in ("%Y-%m-%d", "%Y/%m/%d", "%Y年%m月%d日"):
        try:
            return datetime.strptime(s, fmt).strftime("%Y-%m-%d")
        except ValueError:
            continue
    return s


def video_url(row: dict) -> str:
    """url列を優先し、無ければvideo_idから組み立てる"""
    url = val(row, "url")
    if url:
        return url
    vid = val(row, "video_id")
    return f"https://www.youtube.com/watch?v={vid}" if vid else ""


def card_key(row: dict) -> tuple:
    """同じ曲・同じ2人なら企画が違っても1枚のカードにまとめる"""
    return (val(row, "Title"), frozenset({val(row, "Member1"), val(row, "Member2")}))


def build_videos(rows: list[dict], card_date: str) -> str:
    lines = []
    for r in rows:
        url = video_url(r)
        if not url:
            warn(f"URLもvideo_idも無い行があります: {val(r, 'Title')}", title="ONE PLUS ONE")
            continue
        # 本編とハロドリ版で公開日がずれる回があるので、違うときだけ添える
        note = ""
        if val(r, "published_jst") and val(r, "published_jst") != card_date:
            note = f'<span class="opo-vdate">（{esc(format_date(val(r, "published_jst")))}公開）</span>'
        label = feature_label(r)
        lines.append(
            f'<li>{icon_for_url(url, "ic")}'
            f'<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">'
            f"{esc(feature_short(label))}</a>"
            f"{note}</li>"
        )
    return f'<ul class="opo-videos">{"".join(lines)}</ul>' if lines else ""


def build_member(name: str, group: str) -> str:
    """石山咲良（ハロプロ研修生） 名前はクリックで絞り込めるようにspanで包む"""
    out = f'<span class="opo-name">{esc(name)}</span>'
    if group:
        out += f'<span class="opo-group">（{esc(group)}）</span>'
    return out


def build_cards(rows: list[dict]) -> tuple[str, list[dict]]:
    by_card: dict[tuple, list[dict]] = {}
    for row in rows:
        if not val(row, "Title"):
            continue
        by_card.setdefault(card_key(row), []).append(row)

    cards = []
    for key, group_rows in by_card.items():
        # 企画の優先順で並べ、先頭を代表行(カードの日付・所属の出どころ)にする
        group_rows.sort(key=lambda r: feature_rank(feature_label(r)))
        head = group_rows[0]
        card_date = val(head, "published_jst")

        m1, g1 = val(head, "Member1"), val(head, "Group1")
        m2, g2 = val(head, "Member2"), val(head, "Group2")

        # 同じカードの中で所属がずれている行はシート側の入力ミスなので知らせる
        for r in group_rows[1:]:
            for field, expect in (("Group1", g1), ("Group2", g2)):
                if val(r, field) and val(r, field) != expect:
                    warn(
                        f"「{val(head, 'Title')}」の{field}が行で食い違っています: "
                        f"{expect} / {val(r, field)}",
                        title="ONE PLUS ONE",
                    )

        # 絞り込み用。研修生の行はKenshusei(その後の加入先)でも引っかかるようにする
        names = [n for n in (m1, m2) if n]
        own: dict[str, set[str]] = {}
        for name, group in ((m1, g1), (m2, g2)):
            if name and canon_group(group):
                own.setdefault(name, set()).add(canon_group(group))
        for r in group_rows:
            kenshusei = canon_group(val(r, "Kenshusei"))
            if not kenshusei:
                continue
            # Kenshuseiは「研修生だった側」の加入先。該当が無ければ入力ミスとして知らせる
            trainees = [n for n in names if "ハロプロ研修生" in own.get(n, set())]
            if trainees:
                for n in trainees:
                    own[n].add(kenshusei)
            else:
                warn(
                    f"「{val(head, 'Title')}」にKenshusei（{kenshusei}）があるのに"
                    "ハロプロ研修生の行がありません",
                    title="ONE PLUS ONE",
                )
        groups = {g for gs in own.values() for g in gs}
        features = []
        for r in group_rows:
            label = feature_label(r)
            if label and label not in features:
                features.append(label)

        artist = next((artist_of(r) for r in group_rows if artist_of(r)), "")
        artist_html = (
            f'<p class="opo-artist">原曲：{esc(artist)}</p>' if artist else ""
        )

        cards.append(
            {
                "date": sort_key_date(card_date),
                "title": val(head, "Title"),
                "names": names,
                "groups": sorted(groups),
                "member_groups": {n: sorted(gs) for n, gs in own.items()},
                "features": features,
                "html": (
                    f'<div class="card opo-card"'
                    f' data-features="{esc("|".join(features))}"'
                    f' data-names="{esc("|".join(names))}"'
                    f' data-groups="{esc("|".join(sorted(groups)))}">'
                    f'<p class="opo-song">{esc(val(head, "Title"))}</p>'
                    f"{artist_html}"
                    f'<p class="opo-pair">{build_member(m1, g1)}'
                    f'<span class="opo-x">×</span>{build_member(m2, g2)}</p>'
                    f'<p class="opo-date">{esc(format_date(card_date))}</p>'
                    f"{build_videos(group_rows, card_date)}"
                    "</div>"
                ),
            }
        )

    # 公開日の新しい順。同日は曲名で安定させる
    cards.sort(key=lambda c: (c["date"], c["title"]), reverse=True)
    return "\n".join(c["html"] for c in cards), cards


def build_feature_options(cards: list[dict]) -> str:
    counts: dict[str, int] = {}
    for c in cards:
        for f in c["features"]:
            counts[f] = counts.get(f, 0) + 1

    return "\n".join(
        f'<option value="{esc(f)}">{esc(f)}（{counts[f]}）</option>'
        for f in sorted(counts, key=feature_rank)
    )


def build_group_options(cards: list[dict]) -> str:
    counts: dict[str, int] = {}
    for c in cards:
        for g in c["groups"]:
            counts[g] = counts.get(g, 0) + 1

    def order(g):
        return (GROUP_ORDER.index(g), "") if g in GROUP_ORDER else (len(GROUP_ORDER), g)

    return "\n".join(
        f'<option value="{esc(g)}">{esc(g)}（{counts[g]}）</option>'
        for g in sorted(counts, key=order)
    )


def build_name_options(cards: list[dict]) -> str:
    """メンバーの選択肢。data-groupはその人自身の所属(在籍歴が複数ある人は全部)で、
    グループを選んだときに選択肢を絞るのに使う。相手側の所属は入れない"""
    counts: dict[str, int] = {}
    groups: dict[str, list[str]] = {}
    for c in cards:
        for n in c["names"]:
            counts[n] = counts.get(n, 0) + 1
            for g in c["member_groups"].get(n, []):
                if g not in groups.setdefault(n, []):
                    groups[n].append(g)
    return "\n".join(
        f'<option value="{esc(n)}" data-group="{esc("|".join(groups[n]))}">'
        f"{esc(n)}（{counts[n]}）</option>"
        for n in sorted(counts)
    )


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--csv", default=CSV_URL)
    args = parser.parse_args()

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")
    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    rows = fetch_csv(args.csv)

    # 11文字でないvideo_idは取りこぼし(先頭のハイフンが落ちる等)なので知らせる
    for r in rows:
        vid = val(r, "video_id")
        if vid and len(vid) != 11:
            warn(f"video_idが11文字ではありません: {vid}（{val(r, 'Title')}）", title="ONE PLUS ONE")

    cards_html, cards = build_cards(rows)

    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    for path, body in (
        (OUTPUT_CARDS, cards_html),
        (OUTPUT_FEATURES, build_feature_options(cards)),
        (OUTPUT_GROUPS, build_group_options(cards)),
        (OUTPUT_NAMES, build_name_options(cards)),
    ):
        path.write_text(stamp + body + "\n", encoding="utf-8")
    OUTPUT_GENERATED.write_text(
        f'<div class="last-update">最終更新日：{generated}</div>\n', encoding="utf-8"
    )

    print(f"生成完了: {OUTPUT_CARDS} ({len(cards)} 曲 / 動画 {len(rows)} 本)")


if __name__ == "__main__":
    main()
