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

## ブランチとコミット

作業フォルダは `D:\Cloudflare` の1つだけ。ブランチごとにフォルダを分けず（worktree は使わない）、このフォルダでブランチを切り替えて作業する。Claude のセッションや GitHub Desktop が同じフォルダを使うので、ブランチが知らない間に切り替わることがある。次を守る。

- コミットやプッシュの前に `git branch --show-current` と `git status` で、今のブランチと変更を確認する。思っていたブランチでなければ、コミットせずに止めて報告する
- 小さくこまめにコミットする。未コミットの変更はブランチを切り替えても付いてくるので、作業の区切りごとにコミットして、変更を残したままにしない
- 作業ごとに `main` から新しいブランチを切り、`main` には直接コミットしない。すでにその作業用のブランチがあるなら、新しく作らずそこにコミットする
- 作業が終わったら `main` に入れて、ブランチをローカルと GitHub の両方から消す。まだ `main` に入っていない変更があるブランチは、消す前に確認する
- マージなどでブランチを切り替えるときは、先に変更をコミットか `git stash` で退避する。他のセッションの変更を `git checkout .` や `git reset --hard` で消さない

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

## 外部リンクと埋め込み

Referer は `common/header.php` の `<meta name="referrer" content="same-origin">` でサイト全体に指定している。

- 外部リンクは `target="_blank"` だけ書く。`rel="noopener noreferrer"` は付けない（noopener はブラウザが自動で付け、Referer は meta で止まる）
- `<iframe>` には必ず `referrerpolicy="strict-origin-when-cross-origin"` を付ける（JSで作るなら `f.referrerPolicy = '...'`）。YouTube などは Referer がないと再生できない
- 例外: `worker/index.js` の管理画面は `header.php` を通らないので `rel` を残している
