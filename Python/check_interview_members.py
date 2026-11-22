#!/usr/bin/env python3
"""メンバー一覧(スプシ)が記事の中身と合っているかを、本文を実際に読んで点検する。

やること(1記事ごと):
  1. 記事のURL(と ExtraURL・魚拓)から本文を取る。取り方は索引(generate_interview_index.py)と同じ
  2. 本文とタイトルを Claude Haiku に読ませ、名簿の中から「主役」と「話題に出るだけの人」を分けて答えさせる
  3. スプシのメンバー一覧と突き合わせて、足りない人・余分な人を挙げる

API のクレジットを使う(環境変数 ANTHROPIC_API_KEY)。本文もAPIの答えも、リポジトリの外(一時フォルダ)にだけ置く。
本文は1度取ったら、答えは1度出したら使い回すので、2回目以降は変わった記事しか取りに行かず・聞かない。

  python Python/check_interview_members.py --dry-run            本文を取るだけ。APIは呼ばず、文字数と費用の目安を出す
  python Python/check_interview_members.py --ids ID0558,ID0559  指定した記事だけ点検する
  python Python/check_interview_members.py --limit 20           まず20件だけ(使い回し済みは数えない)
  python Python/check_interview_members.py                      全件を点検する(--max-usd で費用の上限を決められる)
  python Python/check_interview_members.py --report             取り直し・API呼び出しなしで、いまのスプシと保存済みの答えを突き合わせ直す
  python Python/check_interview_members.py --export            確信度 high の判定をスプシに反映した「メンバー一覧」(ID,Name の縦持ち)を作る
  python Python/check_interview_members.py --fresh              本文も答えも捨てて取り直す(普段は要らない)

スプシを直したあとは --report だけで、直った行が一覧から消えたかを無料で確かめられる。
"""

import argparse
import collections
import csv
import hashlib
import json
import os
import re
import sys
import tempfile
import threading
import time
import unicodedata
import urllib.parse
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
sys.stdout.reconfigure(errors="replace")

import generate_interview_index as gi  # noqa: E402
from generate_interview import GID_MEMBERS, fetch_csv, fetch_master  # noqa: E402

MODEL = "claude-haiku-5-5"
PROMPT_VERSION = "2"        # プロンプトや出力の形を変えたら上げる → 保存済みの答えを使い回さない
MAX_CHARS = 150_000         # これを超える本文は切る(切ったら画面に出す)
MIN_BODY_CHARS = 200        # これより短い本文は読めていないとみなし、APIに聞かない
MAX_TOKENS = 4000
WORKERS = 8                 # ドメインごとに1人ずつ。同じサイトには間を空ける(gi._wait_host)
# 概算の単価($/100万トークン)。Haiku 5.5 の100Kトークン以下の入力。変わっていたらここを直す
PRICE_IN, PRICE_OUT = 0.10, 0.50
WORK_DEFAULT = Path(tempfile.gettempdir()) / "interview-member-check"

SYSTEM = """あなたはアイドル・芸能人のインタビュー記事を点検する係です。
記事のタイトルと本文を読み、下の名簿にいる人物を次の2つに分けてください。

main: その記事の主役として登場する人。インタビューを受けている人、対談・座談会・企画に出演して話している人、
      記事の中心になっている人。グループ全体の記事なら、本文に登場するメンバー全員。
mentioned: 本文で名前が挙がるだけで、その記事に出演して話しているわけではない人(先輩・後輩・家族・話題の人など)。

規則:
- 名前は名簿の表記のまま書く。名簿に無い人が main に当たるときは others_main に、本文の表記のまま書く
- タイトルだけで決めず、本文を読んで決める。本文が読み取れないときは confidence を low にする
- 同じ人を main と mentioned の両方に入れない
- 取材する側(聞き手・記者・編集者・ライター・カメラマン・撮影やヘアメイクの担当)は、記事を作る人なので
  main にも mentioned にも others_main にも入れない。ただし出演者が司会や聞き役を務める企画なら、その出演者は main に入れる
- reason は日本語で60字以内。誰が主役かの根拠を簡潔に書く
- 名簿にいない人を main に入れない

名簿(1行1人):
"""

SCHEMA = {
    "type": "object",
    "properties": {
        "main": {"type": "array", "items": {"type": "string"}},
        "mentioned": {"type": "array", "items": {"type": "string"}},
        "others_main": {"type": "array", "items": {"type": "string"}},
        "confidence": {"type": "string", "enum": ["high", "medium", "low"]},
        "reason": {"type": "string"},
    },
    "required": ["main", "mentioned", "others_main", "confidence", "reason"],
    "additionalProperties": False,
}

_VARIANTS = str.maketrans({"﨑": "崎", "髙": "高", "邉": "辺", "邊": "辺", "德": "徳", "濱": "浜"})


def norm(s: str) -> str:
    """表記ゆれ(﨑/崎・髙/高 など)と空白を揃えて比べる。"""
    return unicodedata.normalize("NFKC", s).translate(_VARIANTS).replace(" ", "").replace("　", "")


def build_roster(member_rows: list[dict]) -> list[str]:
    """スプシに出てくる名前を、多い順に。長すぎる(タイトルが入ってしまった行)・1文字のものは入れない。"""
    c = collections.Counter((r.get("Name") or "").strip() for r in member_rows)
    return [n for n, _ in c.most_common() if 2 <= len(n) <= 10]


def fetch_body(a: dict) -> tuple[str, str]:
    """索引と同じ取り方で本文を取る。(本文, 'live' | 'archive')"""
    url = (a.get("URL") or "").strip()
    arc = (a.get("WebArcive") or "").strip()
    extra_raw = (a.get("ExtraURL") or "").strip()
    if url == "-":   # ライブリンク切れ。魚拓だけから取る
        parts: list[str] = []
        for u in [arc] + [x for x in re.split(r"[,\s]+", extra_raw) if x.startswith("http")]:
            b = gi.fetch_archive(u) if u else ""
            if b and not gi.near_duplicate(b, parts):
                parts.append(b)
        return "\n".join(parts), "archive"
    no_follow = extra_raw == "-"
    extra = [] if no_follow else [u for u in re.split(r"[,\s]+", extra_raw) if u]
    bodies, _, _ = gi.collect_pages(url, extra, no_follow=no_follow)
    body, src = "\n".join(bodies), "live"
    if len(gi.normalize(body)) < 100 and "web.archive.org/web/" in arc:
        ab = gi.fetch_archive(arc)
        if len(gi.normalize(ab)) > len(gi.normalize(body)):
            body, src = ab, "archive"
    return body, src


def call_model(client, title: str, feature: str, body: str, roster: list[str]) -> dict:
    """Haiku に聞いて、JSON(SCHEMA の形)と使ったトークン数を返す。"""
    user = f"タイトル: {title}\n連載・特集名: {feature or '(なし)'}\n\n本文:\n{body}"
    resp = client.messages.create(
        model=MODEL,
        max_tokens=MAX_TOKENS,
        # 名簿は全記事で同じなのでキャッシュ対象にする(短すぎて効かない場合は何も起きない)
        system=[{"type": "text", "text": SYSTEM + "\n".join(roster), "cache_control": {"type": "ephemeral"}}],
        # 点検は判断が軽いので effort を下げる。サンプリング値(temperature など)はこのモデルでは指定しない
        output_config={"effort": "low", "format": {"type": "json_schema", "schema": SCHEMA}},
        messages=[{"role": "user", "content": user}],
    )
    if resp.stop_reason == "refusal":
        raise RuntimeError("モデルが回答を拒否した")
    if resp.stop_reason == "max_tokens":
        raise RuntimeError("出力が途中で切れた")
    text = next((b.text for b in resp.content if b.type == "text"), "")
    out = json.loads(text)
    u = resp.usage
    out["_usage"] = {
        "in": u.input_tokens, "out": u.output_tokens,
        "cache_write": getattr(u, "cache_creation_input_tokens", 0) or 0,
        "cache_read": getattr(u, "cache_read_input_tokens", 0) or 0,
    }
    return out


def usd(u: dict) -> float:
    """使ったトークンからの概算(キャッシュの書き込みは1.25倍・読み出しは0.1倍で見積もる)。"""
    return (u["in"] + u["cache_write"] * 1.25 + u["cache_read"] * 0.1) * PRICE_IN / 1e6 + u["out"] * PRICE_OUT / 1e6


class Store:
    """本文と答えの保存先(リポジトリの外)。"""

    def __init__(self, work: Path):
        self.work = work
        (work / "bodies").mkdir(parents=True, exist_ok=True)
        self.answers_path = work / "answers.jsonl"
        self.lock = threading.Lock()
        self.answers: dict[str, dict] = {}
        if self.answers_path.exists():
            for line in self.answers_path.read_text(encoding="utf-8").splitlines():
                if line.strip():
                    r = json.loads(line)
                    self.answers[r["id"]] = r

    def body(self, aid: str) -> tuple[str, str] | None:
        p, m = self.work / "bodies" / f"{aid}.txt", self.work / "bodies" / f"{aid}.src"
        if p.exists():
            return p.read_text(encoding="utf-8"), (m.read_text(encoding="utf-8") if m.exists() else "live")
        return None

    def save_body(self, aid: str, body: str, src: str) -> None:
        (self.work / "bodies" / f"{aid}.txt").write_text(body, encoding="utf-8")
        (self.work / "bodies" / f"{aid}.src").write_text(src, encoding="utf-8")

    def save_answer(self, rec: dict) -> None:
        with self.lock:
            self.answers[rec["id"]] = rec
            with self.answers_path.open("a", encoding="utf-8") as f:
                f.write(json.dumps(rec, ensure_ascii=False) + "\n")


def input_key(title: str, feature: str, body: str, roster: list[str]) -> str:
    h = hashlib.sha1("\n".join([MODEL, PROMPT_VERSION, title, feature, body, "|".join(roster)]).encode("utf-8"))
    return h.hexdigest()


def diff_row(sheet: list[str], ans: dict) -> dict:
    """スプシのメンバーと Haiku の答えの差。"""
    s = {norm(n): n for n in sheet}
    main = {norm(n): n for n in ans.get("main", [])}
    mentioned = {norm(n): n for n in ans.get("mentioned", [])}
    return {
        "missing": [main[k] for k in main if k not in s],                       # 主役なのに一覧に無い
        "absent": [s[k] for k in s if k not in main and k not in mentioned],    # 一覧にいるが本文に出てこない
        "only_mentioned": [s[k] for k in s if k not in main and k in mentioned],  # 一覧にいるが話題に出るだけ
        "others_main": [n for n in ans.get("others_main", []) if norm(n) not in s],
    }


def report(work: Path, articles: dict[str, dict], sheet: dict[str, list[str]], store: Store) -> None:
    rows = []
    for aid, a in articles.items():
        ans = store.answers.get(aid)
        if not ans:
            continue
        if ans.get("error"):
            rows.append({"id": aid, "a": a, "kind": "error", "err": ans["error"], "d": {}, "ans": {}})
            continue
        d = diff_row(sheet.get(aid, []), ans)
        if any(d.values()):
            rows.append({"id": aid, "a": a, "kind": "diff", "d": d, "ans": ans})
    out = work / "メンバー照合.csv"
    with out.open("w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ID", "タイトル", "URL", "スプシのメンバー", "AIが主役とみた人", "足りない(主役なのに一覧に無い)",
                    "余分(本文に出てこない)", "余分(話題に出るだけ)", "名簿に無い主役", "確信度", "理由/エラー"])
        for r in rows:
            a, d, ans = r["a"], r["d"], r["ans"]
            w.writerow([r["id"], a.get("Title", ""), a.get("URL", ""), "・".join(sheet.get(r["id"], [])),
                        "・".join(ans.get("main", [])), "・".join(d.get("missing", [])), "・".join(d.get("absent", [])),
                        "・".join(d.get("only_mentioned", [])), "・".join(d.get("others_main", [])),
                        ans.get("confidence", ""), r.get("err") or ans.get("reason", "")])
    n_done = sum(1 for aid in articles if aid in store.answers and not store.answers[aid].get("error"))
    n_err = sum(1 for aid in articles if store.answers.get(aid, {}).get("error"))
    print(f"\n点検済み {n_done} 件 / 取得・判定できず {n_err} 件 / スプシと違う {sum(1 for r in rows if r['kind'] == 'diff')} 件")
    order = {"high": 0, "medium": 1, "low": 2}
    diffs = sorted((r for r in rows if r["kind"] == "diff"),
                   key=lambda r: (order.get(r["ans"].get("confidence"), 3),
                                  -(len(r["d"]["missing"]) + len(r["d"]["absent"]))))
    for r in diffs[:40]:
        d, a = r["d"], r["a"]
        parts = [f"{k} {'・'.join(d[key])}" for k, key in
                 (("足りない", "missing"), ("本文に出てこない", "absent"), ("話題だけ", "only_mentioned"),
                  ("名簿に無い主役", "others_main")) if d[key]]
        print(f"  {r['id']} [{r['ans'].get('confidence')}] {a.get('Title', '')[:34]} → " + " / ".join(parts))
    if len(diffs) > 40:
        print(f"  …ほか {len(diffs) - 40} 件(全部は CSV)")
    print(f"一覧: {out}")


def proposed_members(cur: list[str], ans: dict | None, canon: dict[str, str]) -> tuple[list[str], str]:
    """(反映後のメンバー, 区分)。区分は applied(変えた) / unchanged / 要確認:理由(今のまま残した)。
    ・確信度 high の判定だけを反映する。それ以外・本文が取れなかった記事は今の一覧のまま残して要確認にする
    ・ハロプロのメンバー(メンバーマスターにいる人)だけを入れる。記者・俳優・ミュージシャンなどは入れない
    ・今の一覧にいる人の並びは崩さず、足りない人を後ろに足す"""
    if not ans or ans.get("error"):
        return cur, "要確認:本文が取れず判定できない"
    if ans.get("confidence") != "high":
        return cur, f"要確認:確信度 {ans.get('confidence')}"
    hp: list[str] = []
    for n in ans.get("main", []) + ans.get("others_main", []):   # 名簿に無い人の表記ゆれ(西崎→西﨑)もマスターで拾う
        c = canon.get(norm(n))
        if c and c not in hp:
            hp.append(c)
    if not hp:
        return cur, "要確認:ハロプロのメンバーが主役に見つからない"
    new = [n for n in cur if n in hp] + [n for n in hp if n not in cur]
    return new, ("unchanged" if set(new) == set(cur) else "applied")


def _ymd(s: str) -> tuple[int, int, int] | None:
    m = re.match(r"(\d{4})\D+(\d{1,2})\D+(\d{1,2})", s or "")
    return (int(m[1]), int(m[2]), int(m[3])) if m else None


def joined_after(name: str, article_date: str, master: dict) -> bool:
    """その人が、記事の掲載日より後に加入している(=その時点で居ないはずの人)か。"""
    ad = _ymd(article_date)
    joins = [j for j in (_ymd(st.get("join", "")) for st in master.get(name, {}).get("stints", [])) if j]
    return bool(ad and joins and min(joins) > ad)


def export(work: Path, articles: dict[str, dict], sheet: dict[str, list[str]], store: Store) -> None:
    """判定をスプシに反映した ID,Name の縦持ち CSV(丸ごと入れ替え用)と、変更点の一覧を書き出す。"""
    master = fetch_master()
    canon = {norm(k): k for k in master}
    out_rows, changes = [], []
    n = collections.Counter()
    for aid, a in articles.items():
        cur = sheet.get(aid, [])
        new, kind = proposed_members(cur, store.answers.get(aid), canon)
        # 掲載日より後に加入した人を足そうとしていたら、足さない(記事に載っている別の話題を拾った誤りのはず)
        skipped = [x for x in new if x not in cur and joined_after(x, a.get("Date", ""), master)]
        if skipped:
            new = [x for x in new if x not in skipped]
            if kind == "applied" and set(new) == set(cur):
                kind = "unchanged"
        n["applied" if kind == "applied" else "unchanged" if kind == "unchanged" else "review"] += 1
        uniq = list(dict.fromkeys(new))   # 今のシートに同じ人が2行あっても1行にする
        n["duplicate_rows"] += len(new) - len(uniq)
        new = uniq
        out_rows += [(aid, name) for name in new]
        if kind != "unchanged":
            ans = store.answers.get(aid) or {}
            changes.append([aid, a.get("Title", ""), "反映" if kind == "applied" else kind,
                            "・".join(cur), "・".join(new), "・".join(x for x in new if x not in cur),
                            "・".join(x for x in cur if x not in new), "・".join(ans.get("main", [])),
                            ans.get("confidence", ""), ans.get("error") or ans.get("reason", ""),
                            ("掲載日より後に加入のため足さなかった: " + "・".join(skipped)) if skipped else ""])
    changes.sort(key=lambda r: (r[2] != "反映", r[0]))
    f1 = work / "メンバー一覧_修正版.csv"
    with f1.open("w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ID", "Name"])
        w.writerows(out_rows)
    f2 = work / "メンバー変更点.csv"
    with f2.open("w", encoding="utf-8-sig", newline="") as f:
        w = csv.writer(f)
        w.writerow(["ID", "タイトル", "区分", "変更前", "変更後", "追加", "削除", "AIが主役とみた人", "確信度", "理由/エラー", "メモ"])
        w.writerows(changes)
    print(f"反映して変えた記事 {n['applied']} 件 / 変わらず {n['unchanged']} 件 / 今のまま残して要確認 {n['review']} 件")
    print(f"行数: 今 {sum(len(v) for v in sheet.values())} → {len(out_rows)}(同じ記事に同じ人が2行あったものを {n['duplicate_rows']} 行まとめた)")
    print(f"取り込み用: {f1}")
    print(f"変更点   : {f2}")


def main() -> None:
    ap = argparse.ArgumentParser(description="メンバー一覧を本文と突き合わせて点検する")
    ap.add_argument("--ids", default="", help="点検する記事ID(カンマ区切り)。省略すると全件")
    ap.add_argument("--limit", type=int, default=0, help="新しく点検する件数の上限(使い回し済みは数えない)")
    ap.add_argument("--dry-run", action="store_true", help="本文を取るだけでAPIは呼ばない。文字数と費用の目安を出す")
    ap.add_argument("--report", action="store_true", help="取り直し・API呼び出しなしで、保存済みの答えとスプシを突き合わせ直す")
    ap.add_argument("--export", action="store_true", help="確信度 high の判定を反映したメンバー一覧(縦持ちCSV)を作る")
    ap.add_argument("--fresh", action="store_true", help="保存済みの本文と答えを捨てて取り直す")
    ap.add_argument("--max-usd", type=float, default=5.0, help="この概算費用($)に達したら止める(既定 5)")
    ap.add_argument("--work", default=str(WORK_DEFAULT), help="本文と答えの保存先(リポジトリの外にすること)")
    args = ap.parse_args()

    work = Path(args.work)
    if args.fresh and work.exists():
        for p in (work / "bodies").glob("*"):
            p.unlink()
        (work / "answers.jsonl").unlink(missing_ok=True)
    store = Store(work)

    articles = {(a.get("ID") or "").strip(): a for a in gi.fetch_articles() if (a.get("ID") or "").strip()}
    member_rows = fetch_csv(GID_MEMBERS)
    sheet: dict[str, list[str]] = collections.defaultdict(list)
    for r in member_rows:
        i, n = (r.get("ID") or "").strip(), (r.get("Name") or "").strip()
        if i and n:
            sheet[i].append(n)
    roster = build_roster(member_rows)
    want = {x.strip() for x in args.ids.split(",") if x.strip()}
    if want:
        articles = {k: v for k, v in articles.items() if k in want}

    if args.report:
        report(work, articles, sheet, store)
        return
    if args.export:
        export(work, articles, sheet, store)
        return

    client = None
    if not args.dry_run:
        if not (os.environ.get("ANTHROPIC_API_KEY") or os.environ.get("ANTHROPIC_AUTH_TOKEN")):
            sys.exit("ANTHROPIC_API_KEY が設定されていません。PowerShell で次のようにしてから、もう一度実行してください:\n"
                     '  $env:ANTHROPIC_API_KEY = "(コンソールで作ったAPIキー)"\n'
                     "キーはチャットに貼らず、このウィンドウの中だけで設定します。")
        import anthropic
        client = anthropic.Anthropic(max_retries=5)

    todo = [a for a in articles.values() if (a.get("URL") or "").strip() and
            ((a.get("URL") or "").strip() != "-" or (a.get("WebArcive") or "").strip())]
    by_domain: dict[str, list[dict]] = collections.defaultdict(list)
    for a in todo:
        u = (a.get("URL") or "").strip()
        by_domain[gi._host(urllib.parse.urlparse(u if u != "-" else (a.get("WebArcive") or "")).netloc) or "?"].append(a)

    lock = threading.Lock()
    state = {"new": 0, "reused": 0, "unread": 0, "errors": 0, "usd": 0.0, "chars": 0, "stop": False}

    def work_one(a: dict) -> None:
        aid = (a.get("ID") or "").strip()
        title, feature = (a.get("Title") or "").strip(), (a.get("Feature") or "").strip()
        with lock:
            if state["stop"] or (args.limit and state["new"] >= args.limit):
                return
        got = store.body(aid)
        if got is None:
            body, src = fetch_body(a)
            store.save_body(aid, body, src)
        else:
            body, src = got
        n = len(body)
        if n < MIN_BODY_CHARS:
            with lock:
                state["unread"] += 1
            store.save_answer({"id": aid, "error": f"本文が取れていない({n}字)", "key": ""})
            print(f"{aid}: 本文が取れず判定できない({n}字)", flush=True)
            return
        if n > MAX_CHARS:
            print(f"{aid}: 本文が長いので先頭 {MAX_CHARS:,} 字だけ渡します(全体 {n:,} 字)", flush=True)
            body = body[:MAX_CHARS]
        with lock:
            state["chars"] += len(body)
        key = input_key(title, feature, body, roster)
        old = store.answers.get(aid)
        if old and old.get("key") == key and not old.get("error"):
            with lock:
                state["reused"] += 1
            return
        if client is None:
            return   # --dry-run
        with lock:
            if state["stop"] or (args.limit and state["new"] >= args.limit):
                return
            state["new"] += 1
        try:
            ans = call_model(client, title, feature, body, roster)
        except Exception as e:  # APIエラー・JSON不正など。1件の失敗で全体を止めない
            with lock:
                state["errors"] += 1
            store.save_answer({"id": aid, "error": f"{type(e).__name__}: {e}"[:200], "key": key})
            print(f"{aid}: 失敗 {type(e).__name__}: {str(e)[:100]}", flush=True)
            return
        cost = usd(ans["_usage"])
        with lock:
            state["usd"] += cost
            if state["usd"] >= args.max_usd:
                state["stop"] = True
        store.save_answer({"id": aid, "key": key, "src": src, "chars": n, **ans})
        print(f"{aid}: {src} {n:,}字 → 主役 {'・'.join(ans['main']) or '(なし)'} [{ans['confidence']}] ≈${cost:.4f}", flush=True)

    def work_domain(items: list[dict]) -> None:
        for a in items:
            work_one(a)

    started = time.time()
    with ThreadPoolExecutor(WORKERS) as ex:
        list(ex.map(work_domain, sorted(by_domain.values(), key=len, reverse=True)))

    print(f"\n{len(todo)} 件中: 新しく点検 {state['new']} / 保存済みを使い回し {state['reused']} / "
          f"本文が取れず {state['unread']} / API失敗 {state['errors']}  ({time.time() - started:.0f}秒)")
    if args.dry_run:
        # 日本語は1字≒1トークンまで増えうるので、上限の見積もりとして文字数をそのままトークン数とみなす
        est_in = state["chars"] * PRICE_IN / 1e6
        print(f"--dry-run: 渡す本文は合計 {state['chars']:,} 字。入力だけで最大 ${est_in:.2f} 前後"
              f"(出力は1件あたり数百トークンなので、合計でその1割ほど)")
        return
    print(f"概算費用 ${state['usd']:.3f}" + ("  ← --max-usd に達したので途中で止めました。続きは同じコマンドでできます" if state["stop"] else ""))
    report(work, articles, sheet, store)


if __name__ == "__main__":
    main()
