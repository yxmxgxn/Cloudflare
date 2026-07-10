#!/usr/bin/env python3
"""
バースデーイベントスプレッドシートを読み取り、
メンバーごとにPHPページを生成する
"""
import csv
import io
import os
import urllib.request
from itertools import groupby
from collections import defaultdict
from datetime import datetime, timezone, timedelta

CSV_URL = (
    "https://docs.google.com/spreadsheets/d/e/2PACX-1vQSRqxK38hOeau3jVUZ0faqKs7ce-iTi9EWo7Xx3U9gNe2J7rrWUJE2HDLwYouYF-6XrDSLU-cDpPMr/pub?gid=1771631512&single=true&output=csv"
)

from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親 = リポジトリルート

OUTPUT_DIR = REPO_ROOT / "Birthday"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "birthday_generated.php"

def fetch_csv(url: str) -> list[list[str]]:
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.reader(io.StringIO(content))
    return list(reader)

def escape_html(s: str) -> str:
    return (
        s.replace("&", "&amp;")
         .replace("<", "&lt;")
         .replace(">", "&gt;")
         .replace('"', "&quot;")
    )

def escape_php(s: str) -> str:
    """PHPの二重引用符文字列リテラル用エスケープ（HTML実体参照にはしない）"""
    return s.replace("\\", "\\\\").replace('"', '\\"')

def format_date(date_str: str) -> str:
    for fmt in ("%Y/%m/%d", "%Y-%m-%d"):
        try:
            dt = datetime.strptime(date_str.strip(), fmt)
            return f"{dt.year}年{dt.month}月{dt.day}日"
        except ValueError:
            continue
    return escape_html(date_str)

def build_rows(events: list[dict]) -> str:
    """イベント一覧のtr/tdを生成"""
    lines = []
    row_index = 0
    for ev in events:
        date       = ev.get("date", "").strip()
        start_time = ev.get("startTime", "").strip()
        event_name = escape_html(ev.get("eventName", "").strip())
        venue      = escape_html(ev.get("venueName", "").strip())
        prefecture = escape_html(ev.get("prefectureName", "").strip())
        tomoko_url = ev.get("tomoko_url", "").strip()

        # dateが空の行はスキップ
        if not date:
            continue

        row_class  = "row-even" if row_index % 2 == 0 else ""
        class_attr = f' class="{row_class}"' if row_class else ""
        row_index += 1

        date_cell    = format_date(date)
        time_cell    = escape_html(start_time)
        venue_cell   = f"{venue}" if venue else ""
        prefecture_cell   = f"{prefecture}" if prefecture else ""
        setlist_cell = (
            f'<a href="{escape_html(tomoko_url)}" target="_blank" rel="noopener noreferrer">Tomoko（セットリスト）</a>'
            if tomoko_url else ""
        )

        lines.append(
            f"<tr{class_attr}>"
            f"<td>{date_cell}</td>"
            f"<td>{time_cell}</td>"
            f"<td>{event_name}</td>"
            f"<td>{venue_cell}</td>"
            f"<td>{prefecture_cell}</td>"
            f"<td>{setlist_cell}</td>"
            "</tr>"
        )
    return "\n".join(lines)

def build_sections(events: list[dict]) -> str:
    valid = [ev for ev in events if ev.get("date", "").strip()]
    sections = []
    valid.sort(key=lambda x: x.get("nendo", ""))
    for nendo, group in groupby(valid, key=lambda x: x.get("nendo", "")):
        group_list = list(group)
        age = group_list[0].get("age", "")
        rows = build_rows(group_list)
        sections.append(
            f"{escape_html(nendo)}({escape_html(age)}歳)\n"
            f"<table class=\"birthday-table\">\n"
            f"<thead>\n<tr>\n"
            f"<th>日付</th>\n<th>開演</th>\n<th>イベント名</th>\n"
            f"<th>会場</th>\n<th>都道府県</th>\n<th>セットリスト</th>\n"
            f"</tr>\n</thead>\n<tbody>\n"
            f"{rows}\n"
            f"</tbody>\n</table>"
        )
    return "\n".join(sections)

def build_php(member_name: str, events: list[dict], generated_date: str) -> str:
    title       = f"{member_name}バースデーイベントまとめ"
    description = f"{member_name}さんのこれまでのバースデーイベントの開催日時・会場をまとめました。セットリストが非公式サイト「Tomoko」に公開されていたものはリンクしています。"
    birthday    = events[0].get("birthday", "").strip() if events else ""
    sections    = build_sections(events)
    valid_count = sum(1 for ev in events if ev.get("date", "").strip())
    return (
        f'<?php\n'
        f'  $title = "{escape_html(title)}";\n'
        f'  $description = "{escape_html(description)}";\n'
        f'  $jsonld_type = "Dataset";\n'
        f'  $about = "{escape_php(member_name)}";\n'
        f'  include $_SERVER[\'DOCUMENT_ROOT\'] . \'/common/header.php\';\n'
        f'?>\n\n'
        f'<main>\n'
        f'<p class="site-title"><a href="/Birthday/hello-birthday">メンバー一覧に戻る</a></p>\n'
        f'<h1>{escape_html(member_name)}バースデーイベント会場まとめ</h1>\n'
        f'<p>{escape_html(member_name)}さん（{escape_html(birthday)}生まれ）のこれまでのバースデーイベントをまとめました。'
        f'非公式サイトのハロプロセトリ検索システム「ToMoKo」にてセットリストが公開されていたものはリンクを貼っています。</p>\n'
        f'{sections}\n\n'
        f'</main>\n'
        f'<?php include $_SERVER[\'DOCUMENT_ROOT\'] . \'/Generated/birthday_generated.php\'; ?>\n'
        f'<?php include $_SERVER[\'DOCUMENT_ROOT\'] . \'/common/birthday_footer.php\'; ?>\n'
    )


def main():
    jst = timezone(timedelta(hours=9))
    today = datetime.now(jst).strftime("%Y/%m/%d")

    print("スプレッドシートを取得中...")
    data = fetch_csv(CSV_URL)

    headers = [h.strip() for h in data[0]]

    # (php_name, member_name) でグループ化
    member_events: dict[tuple, list[dict]] = defaultdict(list)
    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: raw[i].strip() for i in range(len(headers))}
        php_name    = row.get("php_name", "").strip()
        member_name = row.get("member_name", "").strip()
        if not php_name or not member_name:
            continue
        member_events[(php_name, member_name)].append(row)

    os.makedirs(OUTPUT_DIR, exist_ok=True)

    total_members = len(member_events)
    print(f"{total_members}名分のデータを検出、ページ生成中...")

    for (php_name, member_name), events in sorted(member_events.items()):
        # テスト用：宮本佳林のみ
        # if php_name != "karin_miyamoto-birth":
        #    continue
        filename = OUTPUT_DIR / f"{php_name}.php"
        php      = build_php(member_name, events, today)

        with open(filename, "w", encoding="utf-8") as f:
            f.write(php)

        print(f"  生成: {filename} ({len(events)}件)")

    # main()の中、ページ生成ループの後に追加
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{today}</div>\n')
        
    print(f"\n完了: {total_members}名 / {sum(len(v) for v in member_events.values())}件処理")


if __name__ == "__main__":
    main()
