"""NexTone.Link のURLから、その曲の各配信サービスでの曲IDを出す。

    python Python/nextone_ids.py https://nex-tone.link/A00028110

NexTone のページに並ぶのはアルバム(シングル盤)へのリンクなので、
そのアルバムの収録曲を取りに行き、ページの曲名と一致する曲のIDを拾う。
Spotify / YouTube Music / Apple Music は直接取得し、
直接だとはじかれる Amazon Music / LINE MUSIC / AWA はヘッドレスブラウザ(Playwright)で開く。
"""
import json
import re
import sys
import unicodedata
import urllib.request
from html import unescape

UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"


def fetch(url: str, data: bytes | None = None, headers: dict | None = None) -> str:
    req = urllib.request.Request(url, data=data, headers={"User-Agent": UA, "Accept-Language": "ja", **(headers or {})})
    with urllib.request.urlopen(req, timeout=20) as res:
        return res.read().decode("utf-8")


def norm(s: str) -> str:
    return re.sub(r"\s+", "", unicodedata.normalize("NFKC", s)).lower()


def nextone_page(url: str) -> tuple[str, str, dict[str, str]]:
    html = fetch(url)
    params = json.loads(re.search(r"__linkchase_params__\s*=\s*(\{.*?\});", html).group(1))
    links = {label: unescape(href) for label, href in re.findall(r'data-label="([^"]+)" href="([^"]+)"', html)}
    return params["page_title"], params.get("page_subtitle") or "", links


def spotify_tracks(url: str) -> list[tuple[str, str]]:
    m = re.search(r"open\.spotify\.com/(album|track)/([A-Za-z0-9]+)", url)
    html = fetch(f"https://open.spotify.com/embed/{m.group(1)}/{m.group(2)}")
    data = json.loads(re.search(r'<script id="__NEXT_DATA__" type="application/json">(.*?)</script>', html).group(1))
    entity = data["props"]["pageProps"]["state"]["data"]["entity"]
    if m.group(1) == "track":
        return [(m.group(2), entity["name"])]
    return [(t["uri"].split(":")[-1], t["title"]) for t in entity["trackList"]]


def ytmusic_tracks(url: str) -> list[tuple[str, str]]:
    m = re.search(r"list=([\w-]+)", url)
    body = {
        "context": {"client": {"clientName": "WEB_REMIX", "clientVersion": "1.20241010.01.00", "hl": "ja", "gl": "JP"}},
        "browseId": "VL" + m.group(1),
    }
    res = json.loads(fetch(
        "https://music.youtube.com/youtubei/v1/browse?prettyPrint=false",
        data=json.dumps(body).encode(),
        headers={"Content-Type": "application/json", "Origin": "https://music.youtube.com"},
    ))
    out = []

    def walk(o):
        if isinstance(o, dict):
            r = o.get("musicResponsiveListItemRenderer")
            if r:
                vid = (r.get("playlistItemData") or {}).get("videoId")
                title = r["flexColumns"][0]["musicResponsiveListItemFlexColumnRenderer"]["text"]["runs"][0]["text"]
                if vid:
                    out.append((vid, title))
            for v in o.values():
                walk(v)
        elif isinstance(o, list):
            for v in o:
                walk(v)

    walk(res)
    return out


def apple_tracks(url: str) -> list[tuple[str, str]]:
    album_id = re.search(r"/id(\d+)|/(\d+)(?:\?|$)", url)
    album_id = album_id.group(1) or album_id.group(2)
    res = json.loads(fetch(f"https://itunes.apple.com/lookup?id={album_id}&entity=song&country=jp"))
    return [(str(r["trackId"]), r["trackName"]) for r in res["results"] if r.get("wrapperType") == "track"]


_browser = None


def browser_links(url: str, pattern: str) -> list[tuple[str, str]]:
    """ブラウザでページを開き、href が pattern に合うリンクの (ID, テキスト) を返す"""
    global _browser
    if _browser is None:
        from playwright.sync_api import sync_playwright
        _browser = sync_playwright().start().chromium.launch(headless=True)
    page = _browser.new_page(locale="ja-JP", user_agent=UA)
    try:
        page.goto(url, wait_until="domcontentloaded", timeout=30000)
        try:
            page.wait_for_function(
                "p => [...document.querySelectorAll('a[href]')].some(a => new RegExp(p).test(a.getAttribute('href')))",
                arg=pattern, timeout=20000,
            )
        except Exception:
            pass
        links = page.eval_on_selector_all("a[href]", "els => els.map(e => [e.getAttribute('href'), e.textContent.trim()])")
    finally:
        page.close()
    out = []
    for href, text in links:
        m = re.search(pattern, href or "")
        if m and text and (m.group(1), text) not in out:
            out.append((m.group(1), text))
    return out


SERVICES = [
    ("Spotify", "spotify", spotify_tracks, "https://open.spotify.com/track/{}"),
    ("YouTube Music", "youtube_music", ytmusic_tracks, "https://music.youtube.com/watch?v={}"),
    ("Amazon Music", "amazon_music_unlimited",
     lambda u: browser_links(u, r"/tracks/([A-Z0-9]{10})"), "https://music.amazon.co.jp/tracks/{}"),
    ("Apple Music", "apple_music", apple_tracks, "https://music.apple.com/jp/song/{}"),
    ("LINE MUSIC", "line_music",
     lambda u: browser_links(u, r"/track/(mt\w+)"), "https://music.line.me/webapp/track/{}"),
    ("AWA", "awa",
     lambda u: browser_links(u, r"^/track/(\w+)"), "https://s.awa.fm/track/{}"),
]


def pick(title: str, tracks: list[tuple[str, str]]) -> tuple[str, str] | None:
    key = norm(title)
    for tid, name in tracks:
        if norm(name) == key:
            return tid, name
    # YouTube Music は「アーティスト「曲名」(MV)」のような名前のことがあるので、含むものも拾う。
    # ただし Instrumental などの別バージョンは除く
    for tid, name in tracks:
        n = norm(name)
        if key in n and not re.search(r"instrumental|inst\.|カラオケ|off ?vocal|remix", n):
            return tid, name
    return None


def main() -> None:
    sys.stdout.reconfigure(encoding="utf-8")
    if len(sys.argv) < 2:
        sys.exit("使い方: python Python/nextone_ids.py <NexTone.LinkのURL>")
    title, artist, links = nextone_page(sys.argv[1])
    print(f"曲名: {title} / {artist}\n")
    for label, key, func, fmt in SERVICES:
        url = links.get(key)
        if not url:
            print(f"{label}: (リンクなし)")
            continue
        try:
            hit = pick(title, func(url))
        except Exception as e:
            print(f"{label}: 取得失敗 ({e})  {url}")
            continue
        if hit:
            note = "" if norm(hit[1]) == norm(title) else f"  ※「{hit[1]}」"
            print(f"{label}: {hit[0]}{note}\n    {fmt.format(hit[0])}")
        else:
            print(f"{label}: 曲名が一致する曲なし  {url}")


if __name__ == "__main__":
    main()
