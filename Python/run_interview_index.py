#!/usr/bin/env python3
"""インタビュー全文検索の索引を、この PC から作って D1 に入れる。

GitHub Actions のサーバーからは natalie.mu・scawaiiweb.com などに弾かれて本文が取れない(0ページになる)ため、
日本の回線から実行する。中身は .github/workflows/index-interview.yml と同じ流れで、最後にページ数の一覧を出す。

  python Python/run_interview_index.py            索引を更新して、ページ数の一覧を出す
  python Python/run_interview_index.py --dry-run  SQLを作るだけで D1 には入れない
  python Python/run_interview_index.py --report   いま D1 に入っているページ数の一覧だけ出す
  python Python/run_interview_index.py --refetch ID0093,ID0149,natalie.mu
                                                  URLが同じでも指定した記事(ドメイン名ならその全記事)だけ取り直す
                                                  (0ページの記事は毎回自動で取り直す)
  python Python/run_interview_index.py --check    ドメインごとに1ページしか取れていない記事を数件開き、
                                                  2ページ目を取りこぼしていそうなドメインを挙げる(10〜20分)
"""

import argparse
import collections
import csv
import html
import json
import os
import random
import re
import shutil
import subprocess
import sys
import tempfile
import time
import urllib.parse
from concurrent.futures import ThreadPoolExecutor
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


def domain_of(url: str) -> str:
    return urllib.parse.urlparse(url).netloc.lower().removeprefix("www.") or "(URLなし)"


def report(work: Path) -> None:
    rows = query(work, "SELECT id, pages, indexed_at FROM article_src")
    info = card_info()
    dist = collections.Counter(r["pages"] for r in rows)
    print(f"\n索引済み {len(rows)} 件 / ページ数ごとの件数: "
          + "、".join(f"{p}ページ {n}件" for p, n in sorted(dist.items())))

    # ドメイン別: 取り方はサイトの作りで決まるので、ExtraURL が要るかはドメイン単位で見る
    by_domain: dict[str, collections.Counter] = collections.defaultdict(collections.Counter)
    for r in rows:
        c = by_domain[domain_of(info.get(r["id"], ("", "", ""))[2])]
        c["件数"] += 1
        c["0ページ" if r["pages"] == 0 else "1ページ" if r["pages"] == 1 else "2ページ以上"] += 1
    cols = ["件数", "0ページ", "1ページ", "2ページ以上"]
    print(f"\nドメイン別(件数5件以上。全{len(by_domain)}ドメインはCSVに):")
    print(f"  {'ドメイン':<32}" + "".join(f"{k:>10}" for k in cols))
    for d, c in sorted(by_domain.items(), key=lambda x: -x[1]["件数"]):
        if c["件数"] >= 5:
            print(f"  {d:<36}" + "".join(f"{c[k] or '':>10}" for k in cols))

    zero = sorted(r["id"] for r in rows if r["pages"] == 0)
    if zero:
        print(f"\n本文が取れていない(0ページ・タイトル等でしか検索に出ない) {len(zero)} 件:")
        for i in zero:
            print(f"  {i}  {info.get(i, ('', '', ''))[2]}")

    out = work / "索引ページ数一覧.csv"
    with open(out, "w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ID", "索引ページ数", "掲載日", "タイトル", "URL", "ドメイン", "索引日時(UTC)"])
        for r in sorted(rows, key=lambda r: (r["pages"], r["id"])):
            d, t, u = info.get(r["id"], ("", "", ""))
            w.writerow([r["id"], r["pages"], d, t, u, domain_of(u), r["indexed_at"]])
    out_d = work / "ドメイン別ページ数.csv"
    with open(out_d, "w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ドメイン", *cols])
        for d, c in sorted(by_domain.items(), key=lambda x: -x[1]["件数"]):
            w.writerow([d, *(c[k] for k in cols)])
    print(f"\n記事ごとのページ数: {out}\nドメイン別: {out_d}")


CHECK_PER_DOMAIN = 3   # 1ドメインあたり開く記事の数
_PAGE2 = re.compile(r"^(?:/page/|/p/|/|-|_)2/?$")   # 区切りが必須: /news/12345 と /news/123452(別記事)を混ぜない


def page2_links(html_text: str, url: str) -> list[str]:
    """記事URLの「2ページ目」らしいリンク(/xxx/2・/xxx/page/2・/xxx-2・?page=2 など)。
    1ページしか取れていないのにこれがあれば、追尾が2ページ目を見つけられていない。"""
    from generate_interview_index import _HREF, _host
    base = urllib.parse.urlparse(url)
    stem = base.path.rstrip("/")
    stem = re.sub(r"/1$", "", stem)   # /page/1 や /1 で登録した記事も同じ幹で比べる
    stem = re.sub(r"/page$", "", stem)
    found = []
    for href in _HREF.findall(html_text or ""):
        u = urllib.parse.urlparse(urllib.parse.urljoin(url, href))
        if _host(u.netloc) != _host(base.netloc):
            continue
        qs = urllib.parse.parse_qs(u.query)
        if any(qs.get(k) == ["2"] for k in ("page", "p", "P")) and u.path.rstrip("/") == base.path.rstrip("/"):
            found.append(u.geturl())
        elif u.path.startswith(stem) and _PAGE2.match(u.path[len(stem):]):
            found.append(u.geturl())
    return list(dict.fromkeys(found))


def check_domain(items: list[tuple[str, str]]) -> list[dict]:
    # 「次へ」などの語はサイトのメニューにもあって誤検知が多いので、弱い手がかりは本文が文の途中で終わっているかだけ
    from generate_interview_index import _END_OK, extract_body, fetch_html
    out = []
    for aid, url in items:
        page = fetch_html(url)
        if page is None:
            out.append({"id": aid, "url": url, "result": "取得失敗"})
        else:
            links = page2_links(page, url)
            body = extract_body(page)
            out.append({"id": aid, "url": url,
                        "result": "2ページ目へのリンクあり" if links else
                                  "本文が文の途中で終わる" if body and not _END_OK.search(body.rstrip()) else "問題なし",
                        "next": links[0] if links else ""})
        time.sleep(0.5)
    return out


def check(work: Path) -> None:
    """ドメインごとに1ページしか取れていない記事を数件開いて、取りこぼしていそうなドメインを挙げる。
    web.archive.org など魚拓は元サイトの作りが混ざるので対象外。"""
    rows = query(work, "SELECT id, pages FROM article_src")
    info = card_info()
    pool: dict[str, list[tuple[str, str]]] = collections.defaultdict(list)
    for r in rows:
        url = info.get(r["id"], ("", "", ""))[2]
        d = domain_of(url)
        if r["pages"] == 1 and url and not re.search(r"archive\.(org|today|ph|is|md|li|vn|fo)$", d):
            pool[d].append((r["id"], url))
    rng = random.Random(0)   # 毎回同じ記事を開く(結果を比べやすいように)
    targets = {d: rng.sample(v, min(CHECK_PER_DOMAIN, len(v))) for d, v in pool.items()}
    total = sum(len(v) for v in targets.values())
    print(f"{len(targets)} ドメイン・{total} 記事を開きます(ドメインごとに並行、同じサイトへは0.5秒おき)")

    # 同じサイトへ同時にアクセスしないよう、並行はドメイン単位
    with ThreadPoolExecutor(max_workers=8) as ex:
        results = dict(zip(targets, ex.map(check_domain, targets.values())))

    # 「本文が文の途中で終わる」は写真クレジット等で終わるサイトが多く誤検知だらけなので、画面には出さずCSVにだけ残す
    rank = {"2ページ目へのリンクあり": 0, "取得失敗": 1, "本文が文の途中で終わる": 2, "問題なし": 3}
    flagged, weak = [], 0
    for d, res in results.items():
        worst = min((rank[x["result"]] for x in res), default=3)
        if worst < 2:
            flagged.append((worst, -len(pool[d]), d, res))
        elif worst == 2:
            weak += 1
    flagged.sort()
    print(f"\n要確認 {len(flagged)} ドメイン(上ほど取りこぼしの疑いが強い。件数=1ページしか取れていない記事の数)。"
          f"ほかに本文が文の途中で終わるだけのドメインが {weak}(CSVのみ):")
    for _, n, d, res in flagged:
        print(f"\n  {d}  (1ページの記事 {-n} 件)")
        for x in res:
            extra = f" → {x['next']}" if x.get("next") else ""
            print(f"    {x['result']:<14} {x['id']} {x['url']}{extra}")

    out = work / "ドメイン見回り.csv"
    with open(out, "w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ドメイン", "1ページの記事数", "ID", "URL", "結果", "2ページ目らしいリンク"])
        for d, res in sorted(results.items()):
            for x in res:
                w.writerow([d, len(pool[d]), x["id"], x["url"], x["result"], x.get("next", "")])
    print(f"\n全結果: {out}")


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true", help="SQLを作るだけで D1 には入れない")
    ap.add_argument("--report", action="store_true", help="ページ数の一覧だけ出す")
    ap.add_argument("--refetch", default="", help="取り直す記事ID(カンマ区切り)")
    ap.add_argument("--check", action="store_true", help="取りこぼしていそうなドメインを挙げる")
    args = ap.parse_args()
    refetch = {x.strip() for x in args.refetch.split(",") if x.strip()}
    domains = {x.lower().removeprefix("www.") for x in refetch if not re.fullmatch(r"ID\d+", x)}
    if domains:   # ドメイン名を書いたら、そのドメインの記事を全部取り直す(追尾の規則をサイト単位で直したとき用)
        refetch |= {i for i, (_, _, u) in card_info().items() if domain_of(u) in domains}

    work = wrangler_dir()
    if args.report:
        report(work)
        return
    if args.check:
        check(work)
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
