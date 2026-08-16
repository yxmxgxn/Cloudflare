#!/usr/bin/env python3
"""
「宮本佳林のボカロ部」(ニコニコのチャンネル番組)で紹介された曲のデータベース。

スプレッドシートは正規化された5タブ構成:
  tracks       … 曲マスタ (TrackID,Name,VideoID,Platform,Date,Composer,UserID)
  track_vocals … 曲×ボーカル (TrackID,VocalName,Engine,Sort) ※縦持ち
  mentions     … 紹介 (SongID,TrackID,Episode,Speaker,Format,Notes)
  performances … 宮本/ゲストが歌った・踊った (PerfID,TrackID,Episode,Kind,Guest,
                 PerfVideoID,PerfPlatform)
  song_videos  … その回で流した追加動画 (SongID,VideoID,Platform,Kind,Label)

同じ曲が別の回・別の人に何度も紹介されるため、曲(tracks)と言及(mentions)を
分けてある。ボーカルは曲に1回だけ登録すればよい。

出力は2つのビュー:
  回ビュー … 1行1言及。Episode降順 → 言及者(ゲスト→宮本→パフォーマンス) → SongID
  曲ビュー … 1行1曲。紹介回数の多い順

vocalo-club.php から include される。
※ X/karin-vocalo.php(#今日のボカロまとめ)は別企画・別データで、generate_vocalo.py が担当。
"""

import csv
import html
import io
import re
import urllib.request
from collections import Counter, defaultdict
from datetime import datetime, timezone, timedelta
from pathlib import Path

from gh_warn import warn

PUB_BASE = (
    "https://docs.google.com/spreadsheets/d/e/"
    "2PACX-1vRTbgNOmqBxHNbM5L4yDxtfvrntKc5NNFc6rGUGVxl29fNSpcOe4ucMobzkEYamPv5pkBOGrXHOkDS8/pub"
)
GID_TRACKS = "0"
GID_TRACK_VOCALS = "907867250"
GID_PERFORMANCES = "1051047128"
GID_MENTIONS = "849194044"
GID_SONG_VIDEOS = "1587459576"
GID_GUESTS = "252681052"  # ゲストのSNS: Name(A列),Instagram,X,TikTok,Note

# TikTokの動画URLはアカウント名を含む形 (https://www.tiktok.com/@{user}/video/{id})。
# パフォーマンス動画は「宮本佳林のボカロ部」の1アカウントからしか出ないので決め打ちでよい。
# 空にすると TikTok はリンクにならず(「動画あり」とだけ表示)、生成時に警告が出る。
TIKTOK_USER = "miyavocal"

# パフォーマンスの並び順(この順で回の末尾に置く)
KIND_ORDER = ["歌唱", "二人歌唱", "TikTok"]

# 「第N回」ではない特別な Episode の表示名。
# Episode 0 は放送回ではなく、宮本佳林が毎回のように出してくる定番フリップをまとめたもの
# (実際に登場し始めるのは第2回)。そのまま「第0回」と出すと初回と誤解されるため差し替える。
EPISODE_LABELS = {"0": "宮本佳林フリップ"}

REPO_ROOT = Path(__file__).resolve().parent.parent

OUT_EPISODE = REPO_ROOT / "Spreadsheet" / "vocaloclub_episode_rows.php"
OUT_TRACK = REPO_ROOT / "Spreadsheet" / "vocaloclub_track_rows.php"
OUT_SPEAKERS = REPO_ROOT / "Spreadsheet" / "vocaloclub_speakers.php"
OUT_EPISODES = REPO_ROOT / "Spreadsheet" / "vocaloclub_episodes.php"
OUT_ENGINES = REPO_ROOT / "Spreadsheet" / "vocaloclub_engines.php"
OUT_GUESTS = REPO_ROOT / "Spreadsheet" / "vocaloclub_guests.php"
OUT_GENERATED = REPO_ROOT / "Generated" / "vocaloclub_generated.php"

# 単色(currentColor)のSVGアイコン。既存ページ(generate_hanakin.py / index.php)と同じ流儀。
ICON_X = (
    '<svg class="sns-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<rect width="24" height="24" rx="4" fill="currentColor"/>'
    '<path d="M17.5 3.5H20.5L14.5 10.5L21.5 20.5H16L11.5 14.5L6.5 20.5H3.5L9.8 13L3 3.5H8.7L12.8 9L17.5 3.5Z" fill="#fff"/>'
    '<path d="M16.5 19L5.5 5H7.5L18.5 19H16.5Z" fill="#fff"/>'
    "</svg>"
)
ICON_INSTAGRAM = (
    '<svg class="sns-icon" viewBox="-90 -90 180 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<rect x="-80" y="-80" width="160" height="160" rx="36" fill="none" stroke="currentColor" stroke-width="7"/>'
    '<circle cx="0" cy="0" r="42" fill="none" stroke="currentColor" stroke-width="7"/>'
    '<circle cx="0" cy="0" r="22" fill="none" stroke="currentColor" stroke-width="5"/>'
    '<circle cx="50" cy="-50" r="10" fill="currentColor"/>'
    "</svg>"
)
ICON_TIKTOK = (
    '<svg class="sns-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
    '<path fill="currentColor" d="M16.5 3c.3 2.1 1.7 3.8 3.8 4.1v2.6c-1.2 0-2.4-.4-3.4-1v5.9'
    "c0 3.3-2.7 6-6 6s-6-2.7-6-6 2.7-6 6-6c.3 0 .6 0 .9.1v2.7c-.3-.1-.6-.2-.9-.2-1.8 0-3.3 1.5-3.3 3.3"
    's1.5 3.3 3.3 3.3 3.3-1.4 3.3-3.2V3h2.3z"/>'
    "</svg>"
)

# SNS列 → (URLテンプレート, アイコン, ラベル)。ハンドルだけを持ち、URLはここで組む。
SNS = [
    ("X", "https://x.com/{h}", ICON_X, "X"),
    ("Instagram", "https://www.instagram.com/{h}/", ICON_INSTAGRAM, "Instagram"),
    ("TikTok", "https://www.tiktok.com/@{h}", ICON_TIKTOK, "TikTok"),
]


def fetch_csv(gid: str) -> list[dict]:
    url = f"{PUB_BASE}?gid={gid}&single=true&output=csv"
    with urllib.request.urlopen(url) as res:
        content = res.read().decode("utf-8-sig")
    rows = list(csv.DictReader(io.StringIO(content)))
    # 完全に空の行を落とす(スプレッドシート末尾の空行対策)
    return [r for r in rows if any((v or "").strip() for v in r.values())]


def esc(s) -> str:
    return html.escape((s or "").strip())


def get(row: dict, key: str) -> str:
    return (row.get(key) or "").strip()


def pad_date(s: str) -> str:
    """2014/10/8 → 2014/10/08。スプレッドシートにゼロ埋め有無が混在しているため、
    表示を揃える(既存の #今日のボカロまとめ と同じ流儀)。パースできなければそのまま。"""
    parts = (s or "").strip().split("/")
    if len(parts) == 3:
        try:
            return f"{int(parts[0]):04d}/{int(parts[1]):02d}/{int(parts[2]):02d}"
        except ValueError:
            pass
    return (s or "").strip()


def episode_label(ep: str) -> str:
    """Episode の表示名。特別扱いのもの以外は「第N回」。"""
    return EPISODE_LABELS.get(ep, f"第{ep}回")


def video_url(vid: str, platform: str) -> str:
    """裸のID + Platform から視聴URLを組み立てる(空欄=ニコニコ)。"""
    if not vid:
        return ""
    p = (platform or "").strip().lower()
    if p == "youtube":
        return f"https://www.youtube.com/watch?v={vid}"
    if p == "tiktok":
        # アカウント名が分からないと正しいURLが作れないので、未設定ならリンクにしない
        return f"https://www.tiktok.com/@{TIKTOK_USER}/video/{vid}" if TIKTOK_USER else ""
    return f"https://www.nicovideo.jp/watch/{vid}"


def link(url: str, text: str, cls: str = "") -> str:
    if not url:
        return text
    c = f' class="{cls}"' if cls else ""
    return f'<a href="{esc(url)}"{c} target="_blank" rel="noopener noreferrer">{text}</a>'


# ---------------------------------------------------------------- 検証
def validate(tracks, tvocals, mentions, perfs, videos) -> None:
    """データの壊れを警告する(生成は止めない)。"""
    tid = {get(t, "TrackID") for t in tracks}

    # 参照整合性
    for label, rows, col in (
        ("track_vocals", tvocals, "TrackID"),
        ("mentions", mentions, "TrackID"),
        ("performances", perfs, "TrackID"),
    ):
        bad = sorted({get(r, col) for r in rows} - tid)
        if bad:
            warn(f"{label}: tracks に無い TrackID を参照: {'、'.join(bad)}", title="参照エラー")

    mids = {get(m, "SongID") for m in mentions}
    bad = sorted({get(v, "SongID") for v in videos} - mids)
    if bad:
        warn(f"song_videos: mentions に無い SongID を参照: {'、'.join(bad)}", title="参照エラー")

    novoc = sorted(tid - {get(v, "TrackID") for v in tvocals})
    if novoc:
        warn(f"ボーカル未登録の曲: {'、'.join(novoc)}", title="ボーカル未登録")

    # VideoID の形式(ニコニコは sm/nm/so + 8桁以内、YouTubeは11文字)
    def vid_ng(v: str) -> str:
        if not v:
            return ""
        if v[:2] in ("sm", "nm", "so"):
            return "" if re.fullmatch(r"(sm|nm|so)\d{1,8}", v) else "ニコニコIDの桁数が異常"
        return "" if re.fullmatch(r"[A-Za-z0-9_-]{11}", v) else "想定外の形式"

    for label, rows, idcol in (("tracks", tracks, "TrackID"), ("song_videos", videos, "SongID")):
        for r in rows:
            ng = vid_ng(get(r, "VideoID"))
            if ng:
                warn(f"{label} {get(r, idcol)}: VideoID '{get(r, 'VideoID')}' は{ng}", title="VideoID異常")

    # 日付の形式
    for t in tracks:
        d = get(t, "Date")
        if d and not re.fullmatch(r"\d{4}/\d{1,2}/\d{1,2}", d):
            warn(f"tracks {get(t, 'TrackID')}: Date '{d}' が YYYY/M/D 形式でない", title="日付異常")

    # 同じ VideoID が2つの TrackID に登録されている(二重登録)
    dup = [v for v, c in Counter(get(t, "VideoID") for t in tracks).items() if c > 1 and v]
    if dup:
        warn(f"同じ VideoID が複数の曲に登録されています: {'、'.join(dup)}", title="曲の二重登録")

    # Composer と UserID の対応崩れ(表記ゆれ・IDの打ち間違いを検出)
    u2c, c2u = defaultdict(set), defaultdict(set)
    for t in tracks:
        if get(t, "UserID"):
            u2c[get(t, "UserID")].add(get(t, "Composer"))
            c2u[get(t, "Composer")].add(get(t, "UserID"))
    for u, cs in sorted(u2c.items()):
        if len(cs) > 1:
            warn(f"UserID {u} に複数のP名: {' / '.join(sorted(cs))}", title="P名の表記ゆれ")
    for c, us in sorted(c2u.items()):
        if len(us) > 1:
            warn(f"P名 {c} に複数のUserID: {' / '.join(sorted(us))}", title="UserIDの不一致")

    # セル内改行(スプレッドシートで Alt+Enter が混入するとTSV出力が壊れる)
    for label, rows in (("tracks", tracks), ("mentions", mentions), ("performances", perfs)):
        for r in rows:
            for k, v in r.items():
                if v and "\n" in v:
                    warn(f"{label} の {k} 列にセル内改行があります: {v!r}", title="セル内改行")

    # パフォーマンス動画のプラットフォーム記入漏れ
    miss = [get(p, "PerfID") for p in perfs if get(p, "PerfVideoID") and not get(p, "PerfPlatform")]
    if miss:
        warn(f"PerfVideoID があるのに PerfPlatform が空: {'、'.join(miss)}", title="Platform記入漏れ")

    # 1つの回に同じ Kind が2つある = どれかの Episode の入力ミスの可能性が高い
    # (通常は 歌唱1 / 二人歌唱1 / TikTok1)
    kinds_by_ep: dict[str, list[str]] = defaultdict(list)
    for p in perfs:
        kinds_by_ep[get(p, "Episode")].append(get(p, "Kind"))
    for ep, ks in sorted(kinds_by_ep.items(), key=lambda x: x[0]):
        dupes = [k for k, c in Counter(ks).items() if c > 1]
        if dupes:
            ids = [get(p, "PerfID") for p in perfs
                   if get(p, "Episode") == ep and get(p, "Kind") in dupes]
            warn(
                f"第{ep}回に「{'、'.join(dupes)}」が複数あります({'、'.join(ids)})。"
                "どれかの Episode が間違っていないか確認してください",
                title="パフォーマンスの回が重複",
            )

    # TikTokはアカウント名が無いとURLを作れない
    tk = [get(p, "PerfID") for p in perfs
          if get(p, "PerfVideoID") and get(p, "PerfPlatform").lower() == "tiktok"]
    if tk and not TIKTOK_USER:
        warn(
            f"TIKTOK_USER が未設定のため TikTok がリンクになりません({len(tk)}件: "
            f"{'、'.join(tk)})。generate_vocaloclub.py の TIKTOK_USER にハンドルを設定してください",
            title="TikTokアカウント未設定",
        )


# ---------------------------------------------------------------- 組み立て
def vocal_html(vocals: list[dict]) -> tuple[str, list[str], list[str]]:
    """[{VocalName,Engine,Sort}] → 表示用「【初音ミク・GUMI】」・名前一覧・エンジン一覧。"""
    ordered = sorted(vocals, key=lambda v: int(get(v, "Sort") or 0))
    names = [get(v, "VocalName") for v in ordered if get(v, "VocalName")]
    engines = [get(v, "Engine") for v in ordered if get(v, "Engine")]
    if not names:
        return "", [], []
    spans = "・".join(f'<span class="vc-vocal">{esc(n)}</span>' for n in names)
    return f'<span class="vc-vocals">【{spans}】</span>', names, engines


def vocal_keys(names: list[str], engines: list[str]) -> list[str]:
    """ボーカル絞り込み用のキー。1つのプルダウンで
    「合成音声のみ/人間含む」「ソフト名」「ボーカル名」のどれでも引けるよう全部持たせる。"""
    keys = list(dict.fromkeys(names)) + list(dict.fromkeys(engines))
    if engines and all(e != "人間" for e in engines):
        keys.append("__synth")
    if any(e == "人間" for e in engines):
        keys.append("__human")
    return keys


def extra_videos_html(rows: list[dict]) -> str:
    """song_videos(その回で流した歌ってみた等)を小さなリンク列にする。"""
    out = []
    for v in rows:
        label = get(v, "Label")
        kind = get(v, "Kind") or "動画"
        text = f"{esc(kind)}：{esc(label)}" if label else esc(kind)
        url = video_url(get(v, "VideoID"), get(v, "Platform"))
        out.append(f'<span class="vc-extra">{link(url, text)}</span>')
    return "".join(out)


def build_episode_rows(mentions, perfs, tinfo, videos_by_song) -> str:
    """回ビュー: Episode降順 → ゲスト→宮本→パフォーマンス → SongID。"""
    # 各回の各言及者について、最初に登場した SongID(＝番組での登場順)
    first_seen: dict[tuple[str, str], str] = {}
    for m in sorted(mentions, key=lambda r: get(r, "SongID")):
        first_seen.setdefault((get(m, "Episode"), get(m, "Speaker")), get(m, "SongID"))

    def speaker_rank(sp: str) -> int:
        if not sp:
            return 3
        return 2 if sp == "宮本佳林" else 1

    items = []
    for m in mentions:
        ep, sp = get(m, "Episode"), get(m, "Speaker")
        items.append({
            "kind": "mention", "row": m, "ep": ep, "speaker": sp,
            "sort": (speaker_rank(sp), first_seen.get((ep, sp), ""), get(m, "SongID")),
        })
    for p in perfs:
        kind = get(p, "Kind")
        order = KIND_ORDER.index(kind) if kind in KIND_ORDER else len(KIND_ORDER)
        items.append({
            "kind": "perf", "row": p, "ep": get(p, "Episode"), "speaker": "",
            "sort": (3, f"{order:02d}", get(p, "PerfID")),
        })

    def ep_num(e: str) -> int:
        return int(e) if e.lstrip("-").isdigit() else -9999

    items.sort(key=lambda x: (-ep_num(x["ep"]), x["sort"]))

    out = []
    for it in items:
        r = it["row"]
        t = tinfo.get(get(r, "TrackID"))
        if not t:
            continue

        name_cell = link(t["url"], esc(t["name"]), "vc-song") + t["vocal_html"]
        composer_cell = link(t["composer_url"], esc(t["composer"]))

        if it["kind"] == "mention":
            sid = get(r, "SongID")
            who = esc(it["speaker"])
            # 絞り込み用: 紹介者そのもの
            filter_speakers = [it["speaker"]] if it["speaker"] else []
            note_parts = []
            if get(r, "Format"):
                note_parts.append(f'<span class="vc-format">{esc(get(r, "Format"))}</span>')
            if get(r, "Notes"):
                note_parts.append(f'<span class="vc-note">{esc(get(r, "Notes"))}</span>')
            extra = extra_videos_html(videos_by_song.get(sid, []))
            if extra:
                note_parts.append(extra)
            note_cell = "".join(note_parts)
            search_extra = " ".join(
                [get(r, "Format"), get(r, "Notes")]
                + [get(v, "Label") for v in videos_by_song.get(sid, [])]
            )
            row_kind = "mention"
        else:
            guest = get(r, "Guest")
            # パフォーマンスは「紹介」ではないので紹介者セルは空欄。
            # 誰が歌った/踊ったかは備考側の Kind バッジとゲスト名で表す。
            who = ""
            # 絞り込み用: 宮本とゲストの両方。ゲストで絞ったときもその回の
            # 二人歌唱・TikTokが出るようにする
            filter_speakers = ["宮本佳林"] + ([guest] if guest else [])
            perf_url = video_url(get(r, "PerfVideoID"), get(r, "PerfPlatform"))
            kind_badge = f'<span class="vc-kind">{esc(get(r, "Kind"))}</span>'
            if perf_url:
                body = link(perf_url, "動画を見る", "vc-perf-link")
            elif get(r, "PerfVideoID"):
                # IDはあるがURLを組み立てられない(TikTokのアカウント名未設定など)
                body = '<span class="vc-unreleased">動画あり</span>'
            else:
                body = '<span class="vc-unreleased">動画未公開</span>'
            performer = f"宮本佳林・{guest}" if guest else "宮本佳林"
            note_cell = (kind_badge
                         + f'<span class="vc-performer">{esc(performer)}</span>'
                         + body)
            search_extra = " ".join([get(r, "Kind"), guest, "パフォーマンス"])
            row_kind = "perf"

        search = " ".join([t["name"], " ".join(t["vocal_names"]), t["composer"],
                           it["speaker"], search_extra])

        out.append(
            f'<tr class="vc-row vc-row-{row_kind}"'
            f' data-episode="{esc(it["ep"])}"'
            f' data-speakers="{esc("|".join(filter_speakers))}"'
            f' data-kind="{row_kind}"'
            f' data-vkeys="{esc("|".join(t["vocal_keys"]))}"'
            f' data-search="{esc(search.lower())}">'
            f'<td class="vc-ep">{esc(episode_label(it["ep"]))}</td>'
            f'<td class="vc-who">{who}</td>'
            f'<td class="vc-name">{name_cell}</td>'
            f'<td class="vc-composer">{composer_cell}</td>'
            f'<td class="vc-date">{esc(t["date"])}</td>'
            f'<td class="vc-notes">{note_cell}</td>'
            "</tr>"
        )
    return "\n".join(out)


def build_track_rows(tracks, tinfo, mentions, perfs) -> str:
    """曲ビュー: 1行1曲、紹介回数の多い順 → TrackID順。"""
    eps_by_track: dict[str, list[str]] = defaultdict(list)
    perf_by_track: dict[str, list[str]] = defaultdict(list)
    speakers_by_track: dict[str, list[str]] = defaultdict(list)
    for m in sorted(mentions, key=lambda r: get(r, "SongID")):
        eps_by_track[get(m, "TrackID")].append(get(m, "Episode"))
        if get(m, "Speaker"):
            speakers_by_track[get(m, "TrackID")].append(get(m, "Speaker"))
    for p in sorted(perfs, key=lambda r: get(r, "PerfID")):
        tk = get(p, "TrackID")
        perf_by_track[tk].append(get(p, "Kind"))
        # 歌った/踊った人も紹介者と同じ軸で絞り込めるようにする
        speakers_by_track[tk].append("宮本佳林")
        if get(p, "Guest"):
            speakers_by_track[tk].append(get(p, "Guest"))

    def ep_num(e: str) -> int:
        return int(e) if e.lstrip("-").isdigit() else -9999

    rows = []
    for t in tracks:
        tkey = get(t, "TrackID")
        info = tinfo.get(tkey)
        if not info:
            continue
        eps = eps_by_track.get(tkey, [])
        kinds = perf_by_track.get(tkey, [])
        total = len(eps) + len(kinds)
        rows.append((total, tkey, t, info, eps, kinds))

    rows.sort(key=lambda x: (-x[0], x[1]))

    out = []
    for total, tkey, t, info, eps, kinds in rows:
        uniq_eps = sorted(set(eps) | {get(p, "Episode") for p in perfs
                                      if get(p, "TrackID") == tkey}, key=ep_num)
        ep_cell = "・".join(esc(episode_label(e)) for e in uniq_eps)
        if kinds:
            ep_cell += f' <span class="vc-kind">{esc("・".join(dict.fromkeys(kinds)))}</span>'

        speakers = list(dict.fromkeys(speakers_by_track.get(tkey, [])))
        search = " ".join([info["name"], " ".join(info["vocal_names"]),
                           info["composer"], " ".join(speakers)])

        out.append(
            f'<tr class="vc-trow"'
            f' data-episodes="{esc("|".join(uniq_eps))}"'
            f' data-speakers="{esc("|".join(speakers))}"'
            f' data-vkeys="{esc("|".join(info["vocal_keys"]))}"'
            f' data-search="{esc(search.lower())}">'
            f'<td class="vc-name">{link(info["url"], esc(info["name"]), "vc-song")}'
            f'{info["vocal_html"]}</td>'
            f'<td class="vc-composer">{link(info["composer_url"], esc(info["composer"]))}</td>'
            f'<td class="vc-date">{esc(info["date"])}</td>'
            f'<td class="vc-count">{total}</td>'
            f'<td class="vc-eps">{ep_cell}</td>'
            "</tr>"
        )
    return "\n".join(out)


def build_guests(guests, mentions, perfs) -> str:
    """出演ゲスト一覧。出演回は mentions/performances から自動で拾う(入力不要)。"""
    eps_by_name: dict[str, set[str]] = defaultdict(set)
    for m in mentions:
        if get(m, "Speaker") and get(m, "Speaker") != "宮本佳林":
            eps_by_name[get(m, "Speaker")].add(get(m, "Episode"))
    for p in perfs:
        if get(p, "Guest"):
            eps_by_name[get(p, "Guest")].add(get(p, "Episode"))

    def ep_num(e: str) -> int:
        return int(e) if e.lstrip("-").isdigit() else -9999

    # 初出演の回が早い順(第1回の松丸亮吾から)。紹介者プルダウンと同じ並び。
    ordered = sorted(
        guests,
        key=lambda g: min((ep_num(e) for e in eps_by_name.get(guest_name(g), set())),
                          default=9999),
    )

    cards = []
    for g in ordered:
        name = guest_name(g)
        eps = sorted(eps_by_name.get(name, set()), key=ep_num)
        ep_txt = "・".join(episode_label(e) for e in eps)

        icons = []
        for col, tmpl, icon, label in SNS:
            handle = get(g, col).lstrip("@")
            if handle:
                icons.append(
                    f'<a href="{esc(tmpl.format(h=handle))}" target="_blank"'
                    f' rel="noopener noreferrer" aria-label="{name} の{label}">{icon}</a>'
                )
        note = get(g, "Note")
        cards.append(
            f'<div class="vc-guest">'
            f'<p class="vc-guest-ep">{esc(ep_txt)}</p>'
            f'<p class="vc-guest-name"><span class="vc-guest-link" data-speaker="{esc(name)}">'
            f'{esc(name)}</span>'
            + (f'<span class="vc-guest-note">{esc(note)}</span>' if note else "")
            + "</p>"
            f'<p class="vc-guest-sns">{"".join(icons) or "&nbsp;"}</p>'
            "</div>"
        )
    return "\n".join(cards)


def guest_name(g: dict) -> str:
    """ゲスト名。A列のヘッダーが空欄でも読めるようにしてある。"""
    for key in ("Name", "", None):
        if key in g and (g.get(key) or "").strip():
            return (g[key] or "").strip()
    return next((str(v).strip() for v in g.values() if v and str(v).strip()), "")


def build_options(values, labels=None) -> str:
    labels = labels or {}
    return "\n".join(
        f'<option value="{esc(v)}">{esc(labels.get(v, v))}</option>' for v in values
    )


def build_optgroups(groups) -> str:
    """[(見出し, [(value, label), ...]), ...] → optgroup 付きの選択肢。"""
    out = []
    for heading, items in groups:
        if not items:
            continue
        out.append(f'<optgroup label="{esc(heading)}">')
        out += [f'<option value="{esc(v)}">{esc(lab)}</option>' for v, lab in items]
        out.append("</optgroup>")
    return "\n".join(out)


def main():
    tracks = fetch_csv(GID_TRACKS)
    tvocals = fetch_csv(GID_TRACK_VOCALS)
    mentions = fetch_csv(GID_MENTIONS)
    perfs = fetch_csv(GID_PERFORMANCES)
    videos = fetch_csv(GID_SONG_VIDEOS)
    guests = fetch_csv(GID_GUESTS)

    validate(tracks, tvocals, mentions, perfs, videos)

    # ゲスト名は mentions.Speaker と完全一致していないと紐づかない
    speakers_in_data = {get(m, "Speaker") for m in mentions if get(m, "Speaker")} - {"宮本佳林"}
    guest_names = {guest_name(g) for g in guests if guest_name(g)}
    only_sheet = sorted(guest_names - speakers_in_data)
    only_data = sorted(speakers_in_data - guest_names)
    if only_sheet:
        warn(f"guests にいるが紹介者として登場しない: {'、'.join(only_sheet)}", title="ゲスト名の不一致")
    if only_data:
        warn(f"紹介者として登場するが guests に未登録: {'、'.join(only_data)}", title="ゲスト未登録")

    vocals_by_track: dict[str, list[dict]] = defaultdict(list)
    for v in tvocals:
        vocals_by_track[get(v, "TrackID")].append(v)

    videos_by_song: dict[str, list[dict]] = defaultdict(list)
    for v in videos:
        videos_by_song[get(v, "SongID")].append(v)

    # 曲ごとの表示用情報を1回だけ組み立てて使い回す
    tinfo: dict[str, dict] = {}
    for t in tracks:
        tkey = get(t, "TrackID")
        vhtml, vnames, vengines = vocal_html(vocals_by_track.get(tkey, []))
        uid = get(t, "UserID")
        tinfo[tkey] = {
            "name": get(t, "Name"),
            "url": video_url(get(t, "VideoID"), get(t, "Platform")),
            "date": pad_date(get(t, "Date")),
            "composer": get(t, "Composer"),
            "composer_url": f"https://www.nicovideo.jp/user/{uid}" if uid else "",
            "vocal_html": vhtml,
            "vocal_names": vnames,
            "vocal_keys": vocal_keys(vnames, vengines),
        }

    episode_rows = build_episode_rows(mentions, perfs, tinfo, videos_by_song)
    track_rows = build_track_rows(tracks, tinfo, mentions, perfs)

    # 絞り込みの選択肢
    def ep_num(e: str) -> int:
        return int(e) if e.lstrip("-").isdigit() else -9999

    episodes = sorted({get(m, "Episode") for m in mentions}
                      | {get(p, "Episode") for p in perfs}, key=ep_num, reverse=True)

    # 言及者は初出演の回が早い順(第1回の松丸亮吾から)。宮本佳林は全回に出るので最後に回す。
    first_ep: dict[str, int] = {}
    for m in mentions:
        sp = get(m, "Speaker")
        if sp:
            n = ep_num(get(m, "Episode"))
            if sp not in first_ep or n < first_ep[sp]:
                first_ep[sp] = n
    speakers = sorted((s for s in first_ep if s != "宮本佳林"), key=lambda s: first_ep[s])
    if "宮本佳林" in first_ep:
        speakers.append("宮本佳林")

    # ボーカル絞り込みは1つのプルダウンに optgroup で3段
    #   ①まとめて(合成音声のみ/人間含む) ②ソフト名 ③ボーカル名(曲数の多い順)
    engines_used = list(dict.fromkeys(get(v, "Engine") for v in tvocals if get(v, "Engine")))
    vocal_counts = Counter()
    for t in tracks:
        for n in dict.fromkeys(get(v, "VocalName")
                               for v in vocals_by_track.get(get(t, "TrackID"), [])):
            if n:
                vocal_counts[n] += 1

    vocal_groups = [
        ("まとめて", [("__synth", "合成音声のみ"), ("__human", "人間ボーカルを含む")]),
        ("ソフト", [(e, e) for e in sorted(engines_used)]),
        ("ボーカル", [(n, f"{n}（{c}曲）") for n, c in vocal_counts.most_common()]),
    ]

    jst = timezone(timedelta(hours=9))
    generated = datetime.now(jst).strftime("%Y/%m/%d")
    stamp = f"<!-- generated: {datetime.now(timezone.utc).isoformat()} -->\n"

    OUT_EPISODE.parent.mkdir(parents=True, exist_ok=True)
    OUT_GENERATED.parent.mkdir(parents=True, exist_ok=True)
    for path, body in (
        (OUT_EPISODE, episode_rows),
        (OUT_TRACK, track_rows),
        (OUT_SPEAKERS, build_options(speakers)),
        (OUT_EPISODES, build_options(episodes, {e: episode_label(e) for e in episodes})),
        (OUT_ENGINES, build_optgroups(vocal_groups)),
        (OUT_GUESTS, build_guests(guests, mentions, perfs)),
    ):
        with open(path, "w", encoding="utf-8") as f:
            f.write(stamp + body + "\n")

    with open(OUT_GENERATED, "w", encoding="utf-8") as f:
        f.write(f'<p class="last-update">データ取得日：{generated}</p>\n')

    print(
        f"生成完了: 曲 {len(tracks)} / ボーカル {len(tvocals)} / 言及 {len(mentions)} / "
        f"パフォーマンス {len(perfs)} / 追加動画 {len(videos)} / ゲスト {len(guests)}"
    )


if __name__ == "__main__":
    main()
