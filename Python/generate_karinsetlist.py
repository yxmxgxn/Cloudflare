#!/usr/bin/env python3
"""
宮本佳林さんのツアーのセトリサイトで公開された「最強のセトリ」の一覧。1セトリ=1カード。

元のセトリはログインしないと見られないので、ページは noindex にしている(karin-setlist.php)。
プレイリストは hello-music(https://hello-music.yxmxgxn.workers.dev/)で変換したもの。

スプレッドシートは1タブ。列:
  URL, おなまえ, 称号, タイトル, 誰に？, どうなってほしい？, どんなセトリ？,
  Date, Time, タイムスタンプ, 曲数, Playlist, YouTubeIDids, (動画IDを1曲1列、見出しなし)
    Date        … 「10月9日」。年が無いときは YEAR とみなす(「2026/10/09」も可)
    Playlist    … watch?v=…&list=TLGG… は YouTube が一時的に作る再生リストで、
                   時間がたつと消えるので使わない
    YouTubeIDids… watch_videos?video_ids=ID,ID,… 。動画IDはここから取る
曲名・歌手名は YouTube の oEmbed で動画IDから引く(シートには持たない)。

使い方:
  python Python/generate_karinsetlist.py              … 公開シート(PUB_URL)から
  python Python/generate_karinsetlist.py 保存した.tsv … ダウンロードしたTSVから
"""

import csv
import html
import io
import json
import re
import sys
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn
from icons import icon
from sheets import fetch_text

# スプシを「ウェブに公開」した CSV の URL(未設定のあいだはTSVを引数で渡す)
PUB_URL = ""
YEAR = 2026

REPO_ROOT = Path(__file__).resolve().parent.parent
OUTPUT_CARDS = REPO_ROOT / "Spreadsheet" / "karinsetlist_cards.php"
OUTPUT_GENERATED = REPO_ROOT / "Generated" / "karinsetlist_generated.php"

VIDEO_ID = re.compile(r"[A-Za-z0-9_-]{11}")


def esc(s: str) -> str:
    return html.escape((s or "").strip())


# ---------------------------------------------------------------- 取得
def load_rows() -> list[dict]:
    if len(sys.argv) > 1:
        path = Path(sys.argv[1])
        text = path.read_text(encoding="utf-8-sig")
        delim = "\t" if path.suffix.lower() == ".tsv" else ","
    elif PUB_URL:
        text, delim = fetch_text(PUB_URL), ","
    else:
        sys.exit("PUB_URL が未設定です。TSV/CSV のパスを引数で渡してください")
    rows = list(csv.reader(io.StringIO(text), delimiter=delim))
    # 動画IDの列は見出しが空なので、見出しのある列だけを名前で読む
    head = [h.strip() for h in rows[0]]
    cols = {h: i for i, h in reversed(list(enumerate(head))) if h}
    out = []
    for r in rows[1:]:
        r = [(c or "").strip() for c in r] + [""] * len(head)
        d = {h: r[i] for h, i in cols.items()}
        if d.get("URL"):
            out.append(d)
    return out


def video_ids(url: str) -> list[str]:
    q = urllib.parse.parse_qs(urllib.parse.urlsplit(url).query)
    ids = [v.strip() for v in ",".join(q.get("video_ids", [])).split(",") if v.strip()]
    for v in ids:
        if not VIDEO_ID.fullmatch(v):
            warn(f"動画ID '{v}' の形がおかしいです", title="動画ID異常")
    return [v for v in ids if VIDEO_ID.fullmatch(v)]


def oembed(vid: str) -> tuple[str, str]:
    """動画ID → (曲名, 歌手名)。YouTube Music の音源は投稿者が「歌手 - Topic」になっている"""
    url = ("https://www.youtube.com/oembed?format=json&url="
           + urllib.parse.quote(f"https://www.youtube.com/watch?v={vid}", safe=""))
    try:
        d = json.loads(fetch_text(url))
    except Exception as e:  # 非公開・削除済みなど。曲名なしで出す
        warn(f"{vid}: oEmbed で曲名を取れませんでした ({e})", title="曲名取得失敗")
        return "", ""
    return d.get("title", ""), re.sub(r"\s*-\s*Topic$", "", d.get("author_name", ""))


def parse_when(date_s: str, time_s: str) -> datetime | None:
    m = (re.fullmatch(r"(\d{4})/(\d{1,2})/(\d{1,2})", date_s)
         or re.fullmatch(r"()(\d{1,2})月(\d{1,2})日", date_s))
    t = re.fullmatch(r"(\d{1,2}):(\d{2})", time_s)
    if not m:
        return None
    y = int(m.group(1)) if m.group(1) else YEAR
    h, mi = (int(t.group(1)), int(t.group(2))) if t else (0, 0)
    return datetime(y, int(m.group(2)), int(m.group(3)), h, mi)


# ---------------------------------------------------------------- 組み立て
def card_html(s: dict, songs: dict[str, tuple[str, str]]) -> str:
    sid = s["URL"].rstrip("/").rsplit("/", 1)[-1]
    ids = s["ids"]
    when = s["when"]

    lines = [f'<article class="card ks-card" id="{esc(sid)}">']
    meta = [f'<span class="ks-date">{when:%Y/%m/%d %H:%M}</span>' if when else ""]
    meta.append(f'<span class="ks-count">{len(ids)}曲</span>')
    lines.append(f'<p class="ks-meta">{"".join(meta)}</p>')
    lines.append(f'<h2 class="ks-title">{esc(s["タイトル"])}</h2>')
    by = esc(s["おなまえ"]) + (f'<span class="ks-rank">{esc(s["称号"])}</span>' if s["称号"] else "")
    lines.append(f'<p class="ks-by">{by}</p>')

    tags = [(k, s[k + "？"]) for k in ("誰に", "どうなってほしい", "どんなセトリ")]
    lines.append('<p class="ks-tags">' + "".join(
        f'<span class="ks-tag"><span class="ks-tag-k">{k}</span>{esc(v)}</span>' for k, v in tags if v
    ) + "</p>")

    links = [f'<a class="ks-link" href="{esc(s["URL"])}" target="_blank" rel="noopener noreferrer">'
             '元のセトリ（要ログイン）</a>']
    if ids:
        embed = ("https://www.youtube-nocookie.com/embed/" + ids[0]
                 + (f"?playlist={','.join(ids[1:])}" if len(ids) > 1 else ""))
        links.append(
            f'<a class="ks-link ks-play" href="{esc(s["YouTubeIDids"])}" target="_blank"'
            f' rel="noopener noreferrer" data-embed="{esc(embed)}">'
            f'{icon("youtube-brand")}YouTubeで聴く</a>')
    lines.append(f'<p class="ks-links">{"".join(links)}</p>')
    lines.append('<div class="ks-embed" hidden></div>')

    n = s["曲数"]
    if n.isdigit() and int(n) > len(ids):
        lines.append(f'<p class="ks-note">セトリは{n}曲。YouTubeで見つからなかった'
                     f'{int(n) - len(ids)}曲はプレイリストに入っていません</p>')

    items = []
    for i, v in enumerate(ids, 1):
        title, artist = songs.get(v, ("", ""))
        items.append(
            f'<li><span class="ks-no">{i}</span>'
            f'<a class="ks-song" href="https://www.youtube.com/watch?v={v}" target="_blank"'
            f' rel="noopener noreferrer">{esc(title) or esc(v)}</a>'
            + (f'<span class="ks-artist">{esc(artist)}</span>' if artist else "")
            + f'<code class="ks-id">{v}</code></li>')
    lines.append(f'<ol class="ks-songs">{"".join(items)}</ol>')
    lines.append("</article>")
    return "\n".join(lines)


# ---------------------------------------------------------------- 出力
def write(path: Path, body: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"
    path.write_text(stamp + body + "\n", encoding="utf-8")


def main() -> None:
    setlists = load_rows()
    for s in setlists:
        s["ids"] = video_ids(s.get("YouTubeIDids", ""))
        s["when"] = parse_when(s.get("Date", ""), s.get("Time", ""))
        if not s["ids"]:
            warn(f"{s['タイトル']}: YouTubeIDids から動画IDを読めません", title="動画IDなし")
        if not s["when"]:
            warn(f"{s['タイトル']}: Date '{s.get('Date')}' を日付として読めません", title="日付異常")

    uniq = sorted({v for s in setlists for v in s["ids"]})
    with ThreadPoolExecutor(max_workers=8) as ex:
        songs = dict(zip(uniq, ex.map(oembed, uniq)))

    # 古いセトリが上
    setlists.sort(key=lambda s: s["when"] or datetime.max)
    write(OUTPUT_CARDS, "\n".join(card_html(s, songs) for s in setlists))

    jst = timezone(timedelta(hours=9))
    OUTPUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_GENERATED.write_text(
        f'<p class="last-update">データ取得日：{datetime.now(jst).strftime("%Y/%m/%d")}</p>\n',
        encoding="utf-8",
    )
    print(f"生成: {len(setlists)}セトリ / {len(uniq)}曲")


if __name__ == "__main__":
    main()
