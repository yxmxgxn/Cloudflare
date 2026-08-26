#!/usr/bin/env python3
"""
スプレッドシート(カリンオシ)を読み取り、
PHPインクルード用のカードHTML・都道府県option・更新日・地図データを生成する
"""

import csv
import io
import json
import re
import os
from datetime import datetime, date, timezone, timedelta
from pathlib import Path

from sheets import fetch_text

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vRxidkdN9N3sth5DCicGYBcvl0sXFQ0eEMuF5GCNOy-Z_kifD3x3xDXKMJ19H0N1VxYDJOSR1uD2uDN/pub"
)
SHEET_GID        = "2021582423"
REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "karinoshi_cards.php"
OUTPUT_PREFS = REPO_ROOT / "Spreadsheet" / "karinoshi_prefs.php"
OUTPUT_MAP   = REPO_ROOT / "Spreadsheet" / "karinoshi_map_data.php"   # ★追加
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

def fetch_csv(gid: str) -> list[list[str]]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    content = fetch_text(url)
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

def parse_rows(data: list[list[str]]) -> list[dict]:
    """ヘッダー行を使って各行をdict化する(共通処理)"""
    headers = [h.strip() for h in data[0]]
    rows = []
    for raw in data[1:]:
        if not any(c.strip() for c in raw):
            continue
        raw = raw + [""] * (len(headers) - len(raw))
        rows.append({headers[i]: raw[i].strip() for i in range(len(headers))})
    return rows

def build_cards(data: list[list[str]]) -> str:
    lines = []

    for row in parse_rows(data):
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

def build_map_data(data: list[list[str]]) -> str:
    """★追加: 地図ページ用のJSONデータ(scriptタグごと)を生成する"""
    items = []
    for row in parse_rows(data):
        pref = row.get("都道府県", "")
        if not pref:
            continue  # 都道府県が無い行は地図に置けない
        items.append({
            "name":    row.get("Name", ""),
            "article": row.get("ArticleNo.", ""),   # 文字列のまま
            "date":    pad_date(row.get("放送日時", "")),
            "pref":    pref,
            "post":    row.get("ポスト", ""),        # 文字列のまま(JS安全整数超え対策)
        })
    js = json.dumps(items, ensure_ascii=False, separators=(",", ":"))
    js = js.replace("</", "<\\/")  # </script>で構文が壊れないように
    return f"<script>const KARINOSHI_MAP_DATA = {js};</script>"

def main():
    jst = timezone(timedelta(hours=9))
    today_aware = datetime.now(jst)

    data = fetch_csv(SHEET_GID)

    with open(OUTPUT_CARDS, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_cards(data) + "\n")

    with open(OUTPUT_PREFS, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_prefs(data) + "\n")

    # ★追加: 地図データ
    with open(OUTPUT_MAP, "w", encoding="utf-8") as f:
        f.write(f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n")
        f.write(build_map_data(data) + "\n")

    generated = today_aware.strftime("%Y/%m/%d")

    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(f"生成完了: {OUTPUT_CARDS} / {OUTPUT_PREFS} / {OUTPUT_MAP} / {OUTPUT_GENERATED} ({len(data)-1} 行処理)")

if __name__ == "__main__":
    main()
