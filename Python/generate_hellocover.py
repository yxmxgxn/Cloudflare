#!/usr/bin/env python3
"""
ハロカバ(ハロプロ配信限定カバー、第1〜6弾・全50録音)の一覧ページ用テーブルを生成する。

スプレッドシート(1行=1録音)を読み取り、PHPインクルード用のテーブル(tbody)フラグメントを出力する。
列: 曲名,原曲アーティスト,カバーアーティスト,レコチョク,mora,iTunes,弾（配信日）
  レコチョク/mora/iTunes は各サービスの URL(空欄=配信なし・リンク切れ)。
  並びはシートの並びのまま(曲ごと→弾の順→同じ弾ならWikipediaの掲載順)。

出力: Spreadsheet/hellocover_table.php
      Generated/hellocover_generated.php(最終更新日)

更新頻度が高くないので手動実行(generate-manual.yml の hellocover)で回す。
ローカル確認用に CSV パスも渡せる:
  python Python/generate_hellocover.py --csv hellocover.csv
"""

import argparse
import csv
import html
import io
from datetime import datetime, timezone, timedelta
from pathlib import Path

from sheets import fetch_text

PUB_URL = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vRrRkdEwAP27BhkWySDI6xhIyrMYIca040e0NBzMM3rT04P5DTB4MN_n0Lc82YNxyNOnJguI8MOeXrj/pub"
    "?gid=662533794&single=true&output=csv"
)

REPO_ROOT = Path(__file__).resolve().parent.parent  # Python/ の親

OUTPUT_TABLE = REPO_ROOT / "Spreadsheet" / "hellocover_table.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "hellocover_generated.php"

REQUIRED = ["曲名", "原曲アーティスト", "カバーアーティスト", "レコチョク", "mora", "iTunes", "弾（配信日）"]
# サービス列 → リンクに出すラベル(アイコンは無いので文字)
SERVICES = [("レコチョク", "レコチョク"), ("mora", "mora"), ("iTunes", "iTunes")]


def esc(s: str) -> str:
    return html.escape((s or "").strip())


def val(row: dict, key: str) -> str:
    return (row.get(key) or "").strip()


def read_rows(source: str) -> list[dict]:
    path = Path(source)
    if path.exists():
        content = path.read_text(encoding="utf-8-sig")
    else:
        content = fetch_text(source)
    reader = csv.DictReader(io.StringIO(content))
    missing = [c for c in REQUIRED if c not in (reader.fieldnames or [])]
    if missing:
        raise SystemExit(f"シートに必要な列がありません: {', '.join(missing)}")
    return [r for r in reader if val(r, "曲名")]


def service_cell(url: str, label: str) -> str:
    # https:// 以外(入力ミスや javascript: など)はリンクにしない
    if not url.startswith("https://"):
        return "<td></td>"
    return f'<td><a href="{esc(url)}" target="_blank">{esc(label)}</a></td>'


def build_rows(rows: list[dict]) -> str:
    lines = []
    prev_song = None
    for r in rows:
        song = val(r, "曲名")
        classes = []
        if song != prev_song:
            classes.append("hc-song-start")  # 曲が変わる行は上に区切り線
        prev_song = song
        cls = f' class="{" ".join(classes)}"' if classes else ""
        cells = [
            f"<td>{esc(song)}</td>",
            f"<td>{esc(val(r, '原曲アーティスト'))}</td>",
            f"<td>{esc(val(r, 'カバーアーティスト'))}</td>",
            *[service_cell(val(r, col), label) for col, label in SERVICES],
            f"<td>{esc(val(r, '弾（配信日）'))}</td>",
        ]
        lines.append(f"<tr{cls}>" + "".join(cells) + "</tr>")
    return "\n".join(lines)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--csv", default=PUB_URL, help="CSVのURLまたはローカルパス")
    args = parser.parse_args()

    rows = read_rows(args.csv)
    if not rows:
        raise SystemExit("シートに行がありません")

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")
    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUTPUT_TABLE.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    with open(OUTPUT_TABLE, "w", encoding="utf-8") as f:
        f.write(stamp + build_rows(rows) + "\n")
    with open(OUTPUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<div class="last-update">最終更新日：{generated}</div>\n')

    counts = {col: sum(1 for r in rows if val(r, col)) for col, _ in SERVICES}
    print(f"生成完了: {len(rows)} 録音 / " + " ".join(f"{k} {v}" for k, v in counts.items()))


if __name__ == "__main__":
    main()
