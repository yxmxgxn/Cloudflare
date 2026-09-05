# 作業ルール

## リポジトリ内に作業用ファイルを作らない

このディレクトリはそのままGitHubのリポジトリになっている。作業の副産物をここに置かないこと。

- 一時ファイル・検証用スクリプト・プレビュー用HTML・調査メモの類は、リポジトリの外（スクラッチパッド）に作る
- 「.gitignore済みだから」は作っていい理由にならない。コミットされなくても作業ディレクトリは汚れる
- どうしてもリポジトリ内に置く必要があるときは、置く前に理由を伝えて、用が済んだら消す

ビルドして確認したいときは、出力先を外に逃がす:

```bash
OUT_DIR=/tmp/verify BASE_URL=http://localhost:8123 bash build/build.sh
```

`OUT_DIR` の既定は `./public`。デプロイ（`wrangler.jsonc` の `assets.directory`）がそこを見るので、既定値そのものは変えないこと。ローカルのPHPは `C:\php\php.exe` にあるのでPATHに足して使う。

## 手で編集しないファイル

- `Spreadsheet/*.php` と `Generated/*.php` は `Python/generate_*.py` がスプレッドシートから書き出す生成物
- `public/` はビルド成果物

## アイコン

サイト内のSVGアイコンは `common/icons.json` が唯一の定義元。PHPは `common/icons.php` の `icon()`、Pythonは `Python/icons.py` の `icon()` / `icon_for_url()` を使う。**ページやジェネレータに `<svg>` を直書きしない。**

```php
<?= icon('youtube-brand', 'ic ic-20') ?>
```

```python
from icons import icon, icon_for_url
```

新しいアイコンは `common/icons.json` に `"名前": {"vb": "...", "svg": "..."}` を足す（`<svg>` タグ自体は書かず中身だけ）。
