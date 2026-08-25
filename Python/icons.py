#!/usr/bin/env python3
"""サイト共通のSVGアイコン。

定義元は common/icons.json 一箇所だけで、PHP側(common/icons.php)も同じJSONを読む。
generate_*.py はここの icon() / icon_for_url() を使い、SVGを自前で持たない。

  icon("youtube-brand")                → class="ic"
  icon("instagram", "ic sns-icon")     → クラスを指定
  icon("x", label="X")                 → aria-hidden ではなく aria-label を付ける

サイズはCSS側(.ic とページ別の上書き)で決める。SVGにwidth/heightは付けない。
"""

import html
import json
import urllib.parse
from functools import lru_cache
from pathlib import Path

ICONS_JSON = Path(__file__).resolve().parent.parent / "common" / "icons.json"

# URLのホスト名 → ブランド色アイコン。上から順に部分一致で判定する。
_HOST_ICONS = (
    ("youtube.com", "youtube-brand"),
    ("youtu.be", "youtube-brand"),
    ("instagram.com", "instagram-brand"),
    ("tiktok.com", "tiktok-brand"),
)


@lru_cache(maxsize=1)
def icon_defs() -> dict[str, dict[str, str]]:
    defs = json.loads(ICONS_JSON.read_text(encoding="utf-8"))
    defs.pop("_comment", None)
    return defs


def icon(name: str, css_class: str = "ic", label: str | None = None) -> str:
    defs = icon_defs()
    if name not in defs:
        # 名前の打ち間違いは静かに消えると気づけないので、生成時に落とす
        raise KeyError(f"アイコン '{name}' は {ICONS_JSON.name} にありません")
    d = defs[name]
    a11y = (
        'aria-hidden="true"'
        if label is None
        else f'role="img" aria-label="{html.escape(label)}"'
    )
    return (
        f'<svg class="{html.escape(css_class)}" viewBox="{d["vb"]}"'
        f' xmlns="http://www.w3.org/2000/svg" {a11y}>{d["svg"]}</svg>'
    )


def icon_name_for_url(url: str) -> str:
    """URLのホスト名から使うアイコン名を選ぶ。未知のドメインは汎用リンクアイコン"""
    host = urllib.parse.urlparse((url or "").strip()).netloc.lower()
    for needle, name in _HOST_ICONS:
        if needle in host:
            return name
    return "link-brand"


def icon_for_url(url: str, css_class: str = "ic") -> str:
    return icon(icon_name_for_url(url), css_class)
