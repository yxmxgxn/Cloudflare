#!/usr/bin/env python3
"""インタビュー全文検索の索引を、この PC から作って D1 に入れる。

GitHub Actions のサーバーからは natalie.mu・scawaiiweb.com などに弾かれて本文が取れない(0ページになる)ため、
日本の回線から実行する。中身は .github/workflows/index-interview.yml と同じ流れで、最後にページ数の一覧を出す。

  python Python/run_interview_index.py            索引を更新して、ページ数の一覧を出す
  python Python/run_interview_index.py --dry-run  SQLを作るだけで D1 には入れない
  python Python/run_interview_index.py --report   いま D1 に入っているページ数の一覧だけ出す
  python Python/run_interview_index.py --refetch ID0093,ID0149
                                                  URLが同じでも指定した記事だけ取り直す(0ページの記事は毎回自動で取り直す)
"""

import argparse
import collections
import csv
import html
import json
import os
import re
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

REPO = Path(__file__).resolve().parent.parent
DB = "karin-interview"

sys.stdout.reconfigure(errors="replace")


def wrangler_dir() -> Path:
    """wrangler は設定ファイルの隣に .wrangler/ を作るので、設定ごと一時フォルダに置いて動かす。"""
    work = Path(tempfile.mkdtemp(prefix="interview-index-"))
    shutil.copy(REPO / "wrangler.jsonc", work)
    return work


def d1(work: Path, *args: str, capture: bool = False) -> str:
    npx = shutil.which("npx")
    if not npx:
        sys.exit("npx が見つかりません(Node.js を入れてください)")
    r = subprocess.run([npx, "--yes", "wrangler@4", "d1", "execute", DB, "--remote", "--yes", *args],
                       cwd=work, check=True, text=True, encoding="utf-8",
                       stdout=subprocess.PIPE if capture else None)
    return r.stdout or ""


def query(work: Path, sql: str) -> list[dict]:
    return json.loads(d1(work, "--json", "--command", sql, capture=True))[0]["results"]


def card_info() -> dict[str, tuple[str, str, str]]:
    """記事ID → (掲載日, タイトル, URL)。一覧ページ用の全件データから引く。"""
    path = REPO / "data" / "interview-cards.json"
    info = {}
    for c in json.loads(path.read_text(encoding="utf-8"))["cards"]:
        attr = lambda n: html.unescape((re.search(f' data-{n}="([^"]*)"', c) or [None, ""])[1])  # noqa: E731
        link = re.search(r'class="iv-(?:title|arc)"><a href="([^"]+)"', c)
        info[attr("id")] = (attr("date"), attr("title"), html.unescape(link[1]) if link else "")
    return info


def report(work: Path) -> None:
    rows = query(work, "SELECT id, pages, indexed_at FROM article_src")
    info = card_info()
    dist = collections.Counter(r["pages"] for r in rows)
    print(f"\n索引済み {len(rows)} 件 / ページ数ごとの件数: "
          + "、".join(f"{p}ページ {n}件" for p, n in sorted(dist.items())))

    zero = sorted(r["id"] for r in rows if r["pages"] == 0)
    if zero:
        print(f"\n本文が取れていない(0ページ・タイトル等でしか検索に出ない) {len(zero)} 件:")
        for i in zero:
            print(f"  {i}  {info.get(i, ('', '', ''))[2]}")

    out = work / "索引ページ数一覧.csv"
    with open(out, "w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ID", "索引ページ数", "掲載日", "タイトル", "URL", "索引日時(UTC)"])
        for r in sorted(rows, key=lambda r: (r["pages"], r["id"])):
            w.writerow([r["id"], r["pages"], *info.get(r["id"], ("", "", "")), r["indexed_at"]])
    print(f"\n記事ごとのページ数: {out}")


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true", help="SQLを作るだけで D1 には入れない")
    ap.add_argument("--report", action="store_true", help="ページ数の一覧だけ出す")
    ap.add_argument("--refetch", default="", help="取り直す記事ID(カンマ区切り)")
    args = ap.parse_args()
    refetch = {x.strip() for x in args.refetch.split(",") if x.strip()}

    work = wrangler_dir()
    if args.report:
        report(work)
        return

    d1(work, "--file", str(REPO / "worker" / "schema.sql"))
    # 取り直す記事は「前回の取得に失敗した(0ページ)」扱いで渡す。シートから消えた判定には残す
    current = {r["id"]: f'{r["src_hash"]}|{0 if r["id"] in refetch else r["pages"]}'
               for r in query(work, "SELECT id, src_hash, pages FROM article_src")}
    (work / "current.json").write_text(json.dumps(current), encoding="utf-8")
    print(f"既存: {len(current)} 件")

    sql = work / "interview_index.sql"
    # 子プロセスの「⚠」などが Windows のコンソール(cp932)で落ちないように
    env = dict(os.environ, PYTHONIOENCODING=f"{sys.stdout.encoding or 'utf-8'}:replace")
    subprocess.run([sys.executable, str(REPO / "Python" / "generate_interview_index.py"),
                    "--current", str(work / "current.json"), "--out", str(sql)], check=True, env=env)

    if args.dry_run:
        print(f"\n--dry-run のため D1 には入れていません: {sql}")
        return
    d1(work, "--file", str(sql))
    report(work)


if __name__ == "__main__":
    main()
