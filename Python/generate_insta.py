#!/usr/bin/env python3
"""
ハロプロ現役メンバーと一部OGの Instagram 投稿を Graph API の Business Discovery で取得し、
D1(karin-hello の insta_post / insta_fts)へ流し込む SQL と、ページ用のアカウント選択肢を出力する。

対象アカウント: SNS一覧の accounts スプレッドシートの instagram 行のうち、
所属が現役グループの人と、OGのうち J.P ROOM / UP-FRONT PROMOTION 所属・M-line 会員の人
(og_section)+ EXTRA_ACCOUNTS。メンバーの加入・卒業はスプレッドシート側の更新に追従する
(卒業して対象外になっても、D1 に貯めた投稿は消さない)。

Business Discovery はビジネス/クリエイターアカウントしか引けず、1回で最大50件、
過去分は after カーソルで遡る。API上限(1時間200回)に収まるよう1回の実行の呼び出し数を
MAX_CALLS に抑え、遡りきれなかった分は状態ファイルにカーソルを残して翌日続ける。
  - 遡り完了済みのアカウント: 最新ページを1回だけ引く(INSERT OR REPLACE なので重複は無害)
  - 未完了のアカウント: 保存カーソルから続きを引く

環境変数:
  IG_USER_ID       自分のプロアカウントの Instagram ユーザーID(数字)
  IG_ACCESS_TOKEN  そのアカウントのアクセストークン(instagram_basic 権限)
  INSTA_SQL        SQL の出力先(未指定ならSQLは書かない)
トークン未設定なら警告だけ出して何もせず終了する(日次更新を止めない)。
"""

import json
import os
import re
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timedelta, timezone
from pathlib import Path

from generate_sns import GID_ACCOUNTS, GROUPS, PUB_BASE, fetch_rows, load_members
from gh_warn import warn

GRAPH = "https://graph.facebook.com/v23.0"
PAGE_SIZE = 50
MAX_CALLS = 150          # 1回の実行で使うAPI呼び出しの上限(上限200/時に余裕を残す)
EXCERPT_LEN = 80

# スプレッドシートの判定に乗らないが対象にするアカウント: ユーザー名 → (表示名, 見出し)
EXTRA_ACCOUNTS: dict[str, tuple[str, str]] = {}

REPO_ROOT = Path(__file__).resolve().parent.parent
STATE_FILE = REPO_ROOT / "data" / "insta_state.json"
OUTPUT_ACCOUNTS = REPO_ROOT / "Spreadsheet" / "insta_accounts.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "insta_generated.php"

JST = timezone(timedelta(hours=9))

# 索引側(worker/index.js)と完全に一致させる正規化
_KEEP = re.compile(r"[^0-9a-z々぀-ヿ㐀-䶿一-鿿]")


def normalize(s: str) -> str:
    return _KEEP.sub("", unicodedata.normalize("NFKC", s or "").lower())


def bigrams(s: str) -> str:
    return " ".join(s[i:i + 2] for i in range(len(s) - 1))


def sql_str(s: str | None) -> str:
    if s is None:
        return "NULL"
    return "'" + s.replace("'", "''") + "'"


def esc(s: str) -> str:
    return (s or "").replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace('"', "&quot;")


def og_section(m) -> str:
    """対象にするOGなら選択肢の見出し(所属)を返す。対象外は空文字。
    J.P ROOM / UP-FRONT PROMOTION 所属と、M-line 会員ページを持つ人(=M-line)。"""
    if m.affiliation == "jproom":
        return "J.P ROOM"
    if m.affiliation == "upfrontpromotion":
        return "UP-FRONT PROMOTION"
    if any(platform == "m-line" for platform, _, _ in m.accounts):
        return "M-line"
    return ""


def target_accounts() -> dict[str, tuple[str, str]]:
    """ユーザー名 → (表示名, 見出し)。現役(グループ順→加入順)のあとにOG(所属ごと→加入順)。"""
    rows = fetch_rows(f"{PUB_BASE}?gid={GID_ACCOUNTS}&single=true&output=csv")
    members, _ = load_members(rows)
    order = list(GROUPS)
    og_order = ["J.P ROOM", "M-line", "UP-FRONT PROMOTION"]
    picked = []
    for m in members:
        if m.affiliation in GROUPS:
            picked.append(((0, order.index(m.affiliation), m.num), m, GROUPS[m.affiliation]))
        elif m.affiliation != "retirement" and (sec := og_section(m)):
            picked.append(((1, og_order.index(sec), m.num), m, sec))
    out: dict[str, tuple[str, str]] = {}
    for _, m, section in sorted(picked, key=lambda t: t[0]):
        users = [id_.strip().strip("/").lower() for platform, id_, _ in m.accounts if platform == "instagram"]
        for user in users:
            # 複数アカウントを持つ人(例: 書道用の別アカウント)はユーザー名を添えて区別する
            name = m.name if len(users) == 1 else f"{m.name}（@{user}）"
            out.setdefault(user, (name, section))
    for user, v in EXTRA_ACCOUNTS.items():
        out.setdefault(user, v)
    return out


def fetch_page(user_id: str, token: str, username: str, after: str | None) -> dict:
    media = f"media.limit({PAGE_SIZE})" + (f".after({after})" if after else "")
    fields = (f"business_discovery.username({username})"
              f"{{{media}{{id,caption,timestamp,permalink,media_type}}}}")
    q = urllib.parse.urlencode({"fields": fields, "access_token": token})
    with urllib.request.urlopen(f"{GRAPH}/{user_id}?{q}", timeout=60) as res:
        return json.loads(res.read().decode("utf-8"))


def err_text(e: urllib.error.HTTPError) -> str:
    """APIエラーを短く。ビジネス/クリエイターでないアカウントは subcode 2207013 で返る。"""
    try:
        err = json.loads(e.read().decode("utf-8")).get("error", {})
    except ValueError:
        return f"HTTP {e.code}"
    if err.get("error_subcode") == 2207013:
        return "個人アカウント(取得不可)"
    return f"{err.get('code')}/{err.get('error_subcode')} {err.get('message', '')}"


def to_jst(ts: str) -> str:
    # 例: 2026-10-13T10:00:00+0000
    return datetime.strptime(ts, "%Y-%m-%dT%H:%M:%S%z").astimezone(JST).strftime("%Y-%m-%d %H:%M")


def post_sql(username: str, p: dict) -> list[str]:
    caption = p.get("caption") or ""
    excerpt = " ".join(caption.split())[:EXCERPT_LEN]
    pid = sql_str(p["id"])
    stmts = [
        "INSERT OR REPLACE INTO insta_post(id,account,ts,permalink,media_type,excerpt) VALUES("
        f"{pid},{sql_str(username)},{sql_str(to_jst(p['timestamp']))},"
        f"{sql_str(p.get('permalink', ''))},{sql_str(p.get('media_type'))},{sql_str(excerpt)});",
        f"DELETE FROM insta_fts WHERE id={pid};",
    ]
    grams = bigrams(normalize(caption))
    if grams:
        stmts.append(f"INSERT INTO insta_fts(id,bigram) VALUES({pid},{sql_str(grams)});")
    return stmts


def write_accounts(accounts: dict[str, tuple[str, str]]) -> None:
    """アカウント選択肢(グループごとの optgroup)。"""
    groups: dict[str, list[str]] = {}
    for user, (name, grp) in accounts.items():
        groups.setdefault(grp, []).append(
            f'<option value="{esc(user)}">{esc(name)}</option>')
    lines = [f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->"]
    for grp, opts in groups.items():
        lines.append(f'<optgroup label="{esc(grp)}">' + "".join(opts) + "</optgroup>")
    OUTPUT_ACCOUNTS.write_text("\n".join(lines) + "\n", encoding="utf-8")

    # 一覧表示でユーザー名→名前を引くための対応表
    names = {u: n for u, (n, _) in accounts.items()}
    OUTPUT_GENERATED.write_text(
        f'<p class="last-update">データ取得日：{datetime.now(JST):%Y/%m/%d}</p>\n'
        f"<script>window.INSTA_NAMES={json.dumps(names, ensure_ascii=False)};</script>\n",
        encoding="utf-8")


def main() -> None:
    accounts = target_accounts()
    write_accounts(accounts)

    user_id = os.environ.get("IG_USER_ID", "").strip()
    token = os.environ.get("IG_ACCESS_TOKEN", "").strip()
    if not user_id or not token:
        warn("IG_USER_ID / IG_ACCESS_TOKEN が未設定のため投稿取得をスキップしました", "Instagram")
        return

    state: dict = json.loads(STATE_FILE.read_text(encoding="utf-8")) if STATE_FILE.exists() else {}
    stmts: list[str] = []
    calls = 0
    failed: list[str] = []

    # 1周目: 全アカウントの最新ページ。2周目以降: 遡り未完了のアカウントを予算の範囲で続ける
    for user in accounts:
        if calls >= MAX_CALLS:
            break
        st = state.setdefault(user, {"after": None, "done": False})
        try:
            data = fetch_page(user_id, token, user, None)
            calls += 1
        except urllib.error.HTTPError as e:
            calls += 1
            failed.append(f"{user}: {err_text(e)}")
            continue
        media = data.get("business_discovery", {}).get("media", {})
        for p in media.get("data", []):
            stmts += post_sql(user, p)
        if st["after"] is None and not st["done"]:
            st["after"] = media.get("paging", {}).get("cursors", {}).get("after")
            st["done"] = not media.get("paging", {}).get("next")
        time.sleep(0.5)

    for user in accounts:
        st = state.get(user)
        while st and not st["done"] and st["after"] and calls < MAX_CALLS:
            try:
                data = fetch_page(user_id, token, user, st["after"])
                calls += 1
            except urllib.error.HTTPError as e:
                calls += 1
                failed.append(f"{user}(遡り): {err_text(e)}")
                break
            media = data.get("business_discovery", {}).get("media", {})
            for p in media.get("data", []):
                stmts += post_sql(user, p)
            paging = media.get("paging", {})
            st["after"] = paging.get("cursors", {}).get("after")
            st["done"] = not paging.get("next")
            time.sleep(0.5)

    STATE_FILE.parent.mkdir(parents=True, exist_ok=True)
    STATE_FILE.write_text(json.dumps(state, ensure_ascii=False, indent=1, sort_keys=True) + "\n",
                          encoding="utf-8")

    sql_path = os.environ.get("INSTA_SQL")
    if sql_path:
        Path(sql_path).parent.mkdir(parents=True, exist_ok=True)
        Path(sql_path).write_text("\n".join(stmts) + "\n", encoding="utf-8")

    pending = [u for u, s in state.items() if not s["done"]]
    print(f"API {calls}回 / SQL {len(stmts)}文 / 遡り未完了 {len(pending)}アカウント")
    if failed:
        warn("取得できなかったアカウント(ビジネス/クリエイターでない・ユーザー名違いなど):\n"
             + "\n".join(failed), "Instagram")


if __name__ == "__main__":
    main()
