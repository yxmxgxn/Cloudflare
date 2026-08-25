#!/usr/bin/env python3
"""
エムハロイベントのスプレッドシート(イベント一覧+YouTube一覧)を読み取り、
PHPインクルード用のカードフラグメントを生成する

  イベントシート : ID,Date,Day,Time,OG,Group,Member,Place,Prefecture,Tomoko
                   同じIDの行が複数 = 同じ日の複数公演(開演時間ごと)
  動画シート     : ID,Title,URL,Channel,Episode
                   イベントIDに紐づく動画。1イベントに何本でも可。
                   URLのホスト名でYouTube/Instagram/TikTokのアイコンを出し分ける(icons.py)。
                   Channel/Episodeは（M-line Music #258）のように括弧で併記

1〜2ヶ月に1回のイベントなので手動実行(generate-manual.ymlのmhello)で回す。
ローカル確認用にCSVファイルのパスも渡せる:
  python Python/generate_mhello.py --events events.csv --videos videos.csv
"""

import argparse
import csv
import html
import io
import urllib.request
from datetime import datetime, timezone, timedelta
from pathlib import Path

from icons import icon_for_url

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vRQeVLCcIzgV87uQEeJ5-kLWqSHV3vwSx6bYiiohNBX33WKjf0HSGozIR9EEtC83e7sXWdODpP9fdQw/pub"
)
GID_EVENTS = "0"          # イベント一覧シートのgid
GID_VIDEOS = "243131199"  # YouTube一覧シートのgid

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "mhello_cards.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "mhello_generated.php"

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
    return (row.get(key) or "").strip()


def format_date(date_str: str) -> str:
    """2026年08月20日 / 2026-08-20 / 2026/8/20 → 2026年8月20日"""
    s = (date_str or "").strip()
    for fmt in ("%Y年%m月%d日", "%Y/%m/%d", "%Y-%m-%d"):
        try:
            dt = datetime.strptime(s, fmt)
            return f"{dt.year}年{dt.month}月{dt.day}日"
        except ValueError:
            continue
    return s


def sort_key_date(date_str: str) -> str:
    """並び替え用の 2026-08-20 形式。パースできなければそのまま"""
    s = (date_str or "").strip()
    for fmt in ("%Y年%m月%d日", "%Y/%m/%d", "%Y-%m-%d"):
        try:
            return datetime.strptime(s, fmt).strftime("%Y-%m-%d")
        except ValueError:
            continue
    return s


def build_title(vol: str, row: dict) -> str:
    """エムハロイベント vol.11 ～石川梨華×モーニング娘。'26 弓桁朱琴～"""
    og = val(row, "OG")
    group = val(row, "Group")
    member = val(row, "Member")
    guest = " ".join(x for x in [group, member] if x)
    pair = "×".join(x for x in [og, guest] if x)
    head = f"エムハロイベント vol.{esc(vol)}"
    return f"{head} ～{esc(pair)}～" if pair else head


def build_meta(row: dict) -> str:
    """2026年8月20日(木)LANDMARK HALL(神奈川)"""
    date = format_date(val(row, "Date"))
    day = val(row, "Day")
    place = val(row, "Place")
    pref = val(row, "Prefecture")
    text = esc(date)
    if day:
        text += f"({esc(day)})"
    if place:
        text += esc(place)
    if pref:
        text += f"({esc(pref)})"
    return text


def build_stages(rows: list[dict], card_date: str) -> str:
    """公演ごとの 17:30 ToMoKo（セットリスト）"""
    lines = []
    for r in rows:
        time = val(r, "Time")
        tomoko = val(r, "Tomoko")
        if not time and not tomoko:
            continue
        # 同じIDでも日付が違う公演(2days等)はその行の日付も出す
        label = esc(time)
        if val(r, "Date") and val(r, "Date") != card_date:
            label = f"{esc(format_date(val(r, 'Date')))} {label}".strip()
        link = (
            f'<a href="{esc(tomoko)}" target="_blank" rel="noopener noreferrer">ToMoKo（セットリスト）</a>'
            if tomoko else '<span class="mh-nolink">セットリスト未掲載</span>'
        )
        lines.append(f"<li>{label} {link}</li>")
    return f'<ul class="mh-stages">{"".join(lines)}</ul>' if lines else ""


def build_source(row: dict) -> str:
    """（M-line Music #258）/（つばきファクトリーのhappyに過ごそうよ）。両方空なら括弧ごと出さない"""
    channel = val(row, "Channel")
    episode = val(row, "Episode").lstrip("#")
    if channel and episode:
        inner = f"{channel} #{episode}"
    elif channel:
        inner = channel
    elif episode:
        inner = f"#{episode}"
    else:
        return ""
    return f'<span class="mh-src">（{esc(inner)}）</span>'


def build_videos(videos: list[dict]) -> str:
    lines = []
    for v in videos:
        url = val(v, "URL")
        title = val(v, "Title")
        if not url:
            continue
        lines.append(
            f'<li>{icon_for_url(url, "ic")}<a href="{esc(url)}" target="_blank" rel="noopener noreferrer">'
            f'{esc(title) or esc(url)}</a>{build_source(v)}</li>'
        )
    if not lines:
        return '<p class="mh-novideo">動画なし</p>'
    return f'<ul class="mh-videos">{"".join(lines)}</ul>'


def build_cards(events: list[dict], videos_by_id: dict[str, list[dict]]) -> str:
    # ID単位にまとめる(同じIDの複数行 = 同じイベントの複数公演)
    by_id: dict[str, list[dict]] = {}
    for row in events:
        eid = val(row, "ID")
        if not eid:
            continue
        by_id.setdefault(eid, []).append(row)

    def order(item):
        eid, rows = item
        try:
            return (0, int(eid), "")
        except ValueError:
            return (1, 0, sort_key_date(val(rows[0], "Date")))

    cards = []
    for eid, rows in sorted(by_id.items(), key=order):
        head = rows[0]
        card_date = val(head, "Date")
        cards.append(
            f'<div class="mh-card" id="mh-{esc(eid)}">'
            f'<p class="mh-title">{build_title(eid, head)}</p>'
            f'<p class="mh-meta">{build_meta(head)}</p>'
            f"{build_stages(rows, card_date)}"
            f"{build_videos(videos_by_id.get(eid, []))}"
            "</div>"
        )
    return "\n".join(cards)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--events", default=f"{PUB_BASE}?gid={GID_EVENTS}&single=true&output=csv")
    parser.add_argument("--videos", default=f"{PUB_BASE}?gid={GID_VIDEOS}&single=true&output=csv")
    args = parser.parse_args()

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")

    events = fetch_csv(args.events)
    videos = fetch_csv(args.videos)

    videos_by_id: dict[str, list[dict]] = {}
    for v in videos:
        vid = val(v, "ID")
        if vid:
            videos_by_id.setdefault(vid, []).append(v)

    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_CARDS.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(stamp + build_cards(events, videos_by_id) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{generated}</div>\n')

    event_ids = {val(r, "ID") for r in events if val(r, "ID")}
    print(f"生成完了: {OUTPUT_CARDS} ({len(event_ids)} イベント / 公演 {len(events)} 行 / 動画 {len(videos)} 本)")


if __name__ == "__main__":
    main()
