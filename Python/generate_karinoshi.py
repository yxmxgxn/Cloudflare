#!/usr/bin/env python3
"""
スプレッドシート(カリンオシ)を読み取り、
PHPインクルード用のカードHTML・都道府県option・更新日を生成する
"""

import csv
import io
import re
import urllib.request
import os
from datetime import datetime, date, timezone, timedelta
from pathlib import Path

SPREADSHEET_ID   = "1OFJRhLK1cn20XTMHnHAm9bqmf7Ubr7getICZJOXfnDY"
SHEET_GID        = "2021582423"
REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "karinoshi_cards.php"
OUTPUT_PREFS = REPO_ROOT / "Spreadsheet" / "karinoshi_prefs.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "karinoshi_generated.php"

PREF_ORDER = [
    '北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県',
    '茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県',
    '新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県',
    '静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県',
    '奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県',
    '徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県',
    '熊本県','大分県','宮崎県','鹿児島県','沖縄県'
]

def fetch_csv(spreadsheet_id: str, gid: str) -> list[list[str]]:
    url = (
        f"https://docs.google.com/spreadsheets/d/{spreadsheet_id}"
        f"/export?format=csv&gid={gid}"
    )
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8")
    reader = csv.reader(io.StringIO(content))
    return list(reader)

def pad_date(val: str) -> str:
    if not val:
        return ""
    for fmt in ("%Y-%m-%d", "%Y/%m/%d", "%m/%d/%Y", "%Y-%m-%dT%H:%M:%S"):
        try:
            dt = datetime.strptime(val.strip(), fmt)
            return dt.strftime("%Y/%m/%d")
        except ValueError:
            continue
    return val.strip()

def escape_html(s: str) -> str:
    return (
        s.replace("&", "&amp;")
         .replace("<", "&lt;")
         .replace(">", "&gt;")
         .replace('"', "&quot;")
    )

def strip_script(html: str) -> str:
    """scriptタグを除去する"""
    return re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL).strip()

def build_cards(data: list[list[str]]) -> str:
    headers = [h.strip() for h in data[0]]
    lines = []

    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw += [""] * (len(headers) - len(raw))
        row = {headers[i]: raw[i].strip() for i in range(len(headers))}

        name      = escape_html(row.get("Name", ""))
        article_no = row.get("ArticleNo.", "")
        tbs_url = f"https://www.tbsradio.jp/articles/{article_no}/" if article_no else ""
        broadcast = pad_date(row.get("放送日時", ""))
        pref      = escape_html(row.get("都道府県", ""))
        post_id = row.get("ポスト", "")
        xurl = f"https://x.com/karin__miyamoto/status/{post_id}" if post_id else ""
        embed     = strip_script(row.get("embed", "").strip())

        header_text = f"{pref} {name}".strip()

        # embedかXURLかで表示を分ける
        if embed:
            # data属性にエスケープせずそのまま入れる（JSでinnerHTMLに展開する）
            embed_attr = embed.replace('"', '&quot;')
            embed_html = f'<div class="karin-embed-placeholder" data-embed="{embed_attr}"></div>'
        elif xurl:
            embed_html = f'<div class="karin-embed-placeholder" data-xurl="{escape_html(xurl)}"></div>'
        else:
            embed_html = ""

        # TBSラジオリンク
        if tbs_url:
            tbs_html = f'<p class="karin-tbs"><a href="{escape_html(tbs_url)}" target="_blank" rel="noopener noreferrer">放送日{escape_html(broadcast)}ページ</a></p>'
        else:
            tbs_html = ""

        lines.append(
            f'<div class="karin-card" data-pref="{escape_html(pref)}">\n'
            f'<p class="karin-header">{escape_html(header_text)}</p>\n'
            f'{embed_html}\n'
            f'{tbs_html}\n'
            f'</div>'
        )

    return "\n".join(lines)

def build_prefs(data: list[list[str]]) -> str:
    headers = [h.strip() for h in data[0]]
    pref_idx = headers.index("都道府県") if "都道府県" in headers else -1
    if pref_idx < 0:
        return ""
    existing = set()
    for raw in data[1:]:
        if len(raw) > pref_idx:
            p = raw[pref_idx].strip()
            if p:
                existing.add(p)
    prefs = [p for p in PREF_ORDER if p in existing]
    return "\n".join(
        f'<option value="{escape_html(p)}">{escape_html(p)}</option>'
        for p in prefs
    )

def main():
    jst = timezone(timedelta(hours=9))
    today_aware = datetime.now(jst)

    data = fetch_csv(SPREADSHEET_ID, SHEET_GID)

    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_cards(data) + "\n")

    with open(OUTPUT_PREFS, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_prefs(data) + "\n")

    generated = today_aware.strftime("%Y/%m/%d")

    meta = f'<p class="last-update">データ取得日：{generated}</p>\n'
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_CARDS} / {OUTPUT_PREFS} / {OUTPUT_GENERATED} ({len(data)-1} 行処理)")

if __name__ == "__main__":
    main()
