"""ジェネレータ共通の警告出力。

GitHub Actions 実行時は実行サマリーに黄色いアノテーション(::warning::)を出し、
ログを掘らなくても一目で気づけるようにする。ローカル実行時は通常の1行表示。
いずれの場合もデプロイ/生成は止めない(終了コードは変えない)。
"""

import os


def _esc(s: str) -> str:
    # アノテーションのメッセージ部分でエスケープが必要な文字
    return s.replace("%", "%25").replace("\r", "%0D").replace("\n", "%0A")


def warn(message: str, title: str = "警告") -> None:
    if os.environ.get("GITHUB_ACTIONS") == "true":
        print(f"::warning title={title}::{_esc(message)}")
    else:
        print(f"⚠ {message}")


def warn_missing_members(missing, note: str = "", title: str = "マスター未登録メンバー") -> None:
    """よみがなマスターに無いメンバーがいれば警告する。空なら何もしない。

    note にはプログラムごとの補足(例: "(記事側Groupで代替)")を渡す。
    """
    names = list(missing)
    if not names:
        return
    warn(f"マスター未登録{note}: {'、'.join(names)}", title=title)
