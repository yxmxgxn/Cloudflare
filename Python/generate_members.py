#!/usr/bin/env python3
"""
スプレッドシート(ハロプロメンバー)を読み取り、
PHPインクルード用のtr/tdフラグメントを生成する
"""

import csv
import io
import urllib.request
from datetime import datetime, timezone, timedelta

SPREADSHEET_ID = "16gVwxCotUPFyBszkG3x3dgJt0eLx8FtCh64b8faaBA8"
SHEET_GID = "837040930"

from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_PATH = REPO_ROOT / "Spreadsheet" / "members_table.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "members_generated.php"

def fetch_csv(spreadsheet_id: str, gid: str) -> list[list[str]]:
    url = (
        f"https://docs.google.com/spreadsheets/d/{spreadsheet_id}"
        f"/export?format=csv&gid={gid}"
    )
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.reader(io.StringIO(content))
    return list(reader)

def parse_jp_date(s: str):
    """YYYY年M月D日 → datetime。失敗したらNone。"""
    if not s:
        return None
    import re
    m = re.match(r"(\d+)年(\d+)月(\d+)日", s.strip())
    if not m:
        return None
    return datetime(int(m.group(1)), int(m.group(2)), int(m.group(3)))

def format_jp_date(s: str) -> str:
    d = parse_jp_date(s)
    if not d:
        return s.strip() if s else ""
    return f"{d.year}年{d.month}月{d.day}日"

def calc_age(s: str, today: datetime) -> str:
    b = parse_jp_date(s)
    if not b:
        return ""
    age = today.year - b.year
    if (today.month, today.day) < (b.month, b.day):
        age -= 1
    return f"{age}歳"

def calc_days(debut_s: str, grad_s: str, today: datetime) -> str:
    start = parse_jp_date(debut_s)
    if not start:
        return ""
    end = parse_jp_date(grad_s) if grad_s else today
    if not end:
        return ""
    return f"{(end - start).days}日"

def escape_html(s: str) -> str:
    return (
        s.replace("&", "&amp;")
         .replace("<", "&lt;")
         .replace(">", "&gt;")
         .replace('"', "&quot;")
    )

def build_rows(data: list[list[str]], today: datetime) -> str:
    headers = [h.strip() for h in data[0]]
    rows = []  # (days_int, html) のタプルで持つ

    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: raw[i].strip() for i in range(len(headers))}

        name       = escape_html(row.get("名前", ""))
        group      = escape_html(row.get("グループ", ""))
        birthday   = row.get("生年月日", "")
        debut      = row.get("デビュー日", "")
        graduation = row.get("ハロプロ卒業日", "")
        venue      = escape_html(row.get("卒業公演", ""))

        if not name:
            continue

        is_active = not graduation.strip()
        age       = calc_age(birthday, today)
        days      = calc_days(debut, graduation, today)

        # ソート用に数値で持っておく
        start = parse_jp_date(debut)
        end   = parse_jp_date(graduation) if graduation else today
        days_int = (end - start).days if start and end else 0

        tr_class = []
        if is_active:
            tr_class.append("active-member")
        if name == "宮本佳林":
            tr_class.append("karin")
        class_attr = f' class="{" ".join(tr_class)}"' if tr_class else ""

        html = (
            f"<tr{class_attr}>"
            f"<td>{name}</td>"
            f"<td>{group}</td>"
            f"<td>{format_jp_date(birthday)}</td>"
            f"<td>{age}</td>"
            f"<td>{format_jp_date(debut)}</td>"
            f"<td>{format_jp_date(graduation)}</td>"
            f"<td>{venue}</td>"
            f"<td>{days}</td>"
            "</tr>"
        )
        rows.append((days_int, html))

    # 在籍日数の多い順にソート
    rows.sort(key=lambda x: x[0], reverse=True)
    return "\n".join(html for _, html in rows)

def main():
    jst = timezone(timedelta(hours=9))
    today_aware = datetime.now(jst)
    today = datetime(today_aware.year, today_aware.month, today_aware.day)

    data = fetch_csv(SPREADSHEET_ID, SHEET_GID)
    tbody = build_rows(data, today)

    generated = today_aware.strftime("%Y/%m/%d")

    content = (
        f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"
        f"{tbody}\n"
    )

    with open(OUTPUT_PATH, "w", encoding="utf-8") as f:
        f.write(content)

    meta = f'<p class="last-update">データ取得日：{generated}</p>\n'

    meta = f'<p class="last-update">データ取得日：{generated}</p>\n'
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(meta)

    print(f"生成完了: {OUTPUT_PATH} ({len(data)-1} 行処理)")

if __name__ == "__main__":
    main()
