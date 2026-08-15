# 匿名フィードバックフォーム — 仕組みと保守メモ

2026-08-15 追加。サイトの全ページのフッターに置いてある「誤りの報告・追加のご要望」フォームについて、
**Cloudflare側の仕組み**を中心に書いたもの。PHPやPythonの部分は普通のコードなので説明しない。

このファイルはビルド対象外なので公開されない（`build/build.sh` は `.php` しかレンダリングせず、
`css` `img` `Timeline` しかコピーしないため）。

---

## 1. なぜPHPなのに動的処理ができるのか

まずここが分からないと以降が読めないので先に書く。

**このサイトのPHPは、サーバー上では一切動いていない。**

GitHub Actions が `build/build.sh` を実行し、その中でPHPのビルトインサーバーを一時的に立てて
全ページをcurlで取得し、**素のHTMLに変換して `public/` に出力**している。Cloudflareに置かれるのは
そのHTMLだけで、PHPファイルは1つもアップロードされていない。

つまりフッターの `<?php ... ?>` は**ビルド時に1回だけ実行されて消える**。
実行時に何かを処理したければ、PHPではなく別の場所が必要になる。それが Worker。

```
[あなた] git push
    ↓
[GitHub Actions] build/build.sh で PHP → 静的HTML (public/)
    ↓                              wrangler deploy
[Cloudflare] 静的HTML + worker/index.js が配信される
```

## 2. 登場するもの

| 名前 | 役割 | 実体 |
|---|---|---|
| **Static Assets** | ビルド済みHTML・CSS・画像を配る | `public/` の中身 |
| **Worker** | 実行時に動くプログラム。フォーム送信を受ける | `worker/index.js` |
| **D1** | Cloudflareのデータベース(中身はSQLite) | `karin-interview` という名前のDB |
| **Turnstile** | GoogleのreCAPTCHAに相当するbot対策 | Cloudflareのダッシュボードで作ったウィジェット |
| **Discord Webhook** | URLを叩くとチャンネルに投稿できる仕組み | Discordのチャンネル設定で発行したURL |

D1が `karin-interview`（インタビュー検索用に作ったDB）を使い回している点に注意。
フィードバックは同じDBの中の別テーブル `feedback` に入っている。

## 3. 送信されてから通知が届くまで

```
訪問者がフッターのフォームに書いて「送信」
    ↓  ブラウザのJavaScriptが fetch() で POST
POST /api/feedback   ←─ worker/index.js の handleFeedback()
    ↓
 1. 本文が空でないか、2000文字以内か
 2. Turnstileのトークンを Cloudflare に問い合わせて検証
 3. 同じIPから10分に5件を超えていないか（D1に問い合わせ）
 4. D1の feedback テーブルに INSERT      ←─ ここまで来れば記録は確定
 5. Discord の Webhook URL を叩いて通知   ←─ 失敗しても握りつぶす
    ↓
 {"ok":true} を返す → フォームが「送信しました」に切り替わる
```

**4と5の順番が重要。** 先にD1へ保存してから通知する。しかも通知は `ctx.waitUntil()` で
レスポンスと切り離してあり、失敗しても `.catch(() => {})` で捨てている。
Discordが落ちていても、通知に気づかなくても、**データは必ずD1に残る**という設計。

## 4. ファイルと役割

| ファイル | 中身 |
|---|---|
| `common/footer.php` | フォームのHTMLと、送信するJavaScript。全ページのフッターに読み込まれる |
| `css/style.css` | フォームの見た目（`.fb-` で始まるクラスが該当箇所） |
| `worker/index.js` | 「匿名フィードバック」の区切りコメント以下が全部この機能 |
| `worker/feedback-schema.sql` | D1のテーブル定義。**適用済み。再実行の必要はない** |
| `wrangler.jsonc` | Cloudflareの設定。`run_worker_first` が超重要（後述） |

## 5. 設定値はCloudflareのダッシュボードにある

コードには秘密の値が一切入っていない。全部Cloudflare側に置いてある。

**場所**: Cloudflare → Workers & Pages → `karin-archive` → 設定 → 変数とシークレット

| 名前 | 中身 | 無いとどうなるか |
|---|---|---|
| `DISCORD_WEBHOOK` | Discordの通知先URL | 通知が飛ばないだけ。保存は動く |
| `TURNSTILE_SECRET` | Turnstileの秘密鍵 | **検証を丸ごとスキップする**（そういうコードにしてある） |
| `FEEDBACK_TOKEN` | 管理ページを開くための合言葉 | 管理ページが常に404になる |
| `FEEDBACK_SALT` | IPをハッシュ化するときに混ぜる文字列 | デフォルト値が使われる。実害は小さい |

いずれも **Type は Secret**。一度Secretにすると**二度と値を読み出せない**。
変えたいときは新しい値で上書きするしかない。

Turnstileの**サイトキー**だけは公開鍵なので例外で、`common/footer.php` の
`$turnstile_sitekey` に直接書いてある。これはHTMLに出る前提の値なので秘密ではない。

## 6. いちばんの落とし穴 — `run_worker_first`

**新しくWorker専用のURLを追加するときは、必ず `wrangler.jsonc` にも書き足すこと。**
これを忘れると、原因がまず分からない症状が出る。

### 何が起きるか

Cloudflareは、アドレスバーから開くような**画面遷移のリクエスト**（`Sec-Fetch-Mode: navigate`）に対して、
一致する静的ファイルが無いと `not_found_handling` の設定に従って**404ページを返し、そこで終了する**。
Workerは呼ばれない。

一方、JavaScriptの `fetch()` による通信は画面遷移ではないので、普通にWorkerへ届く。

結果、こうなる:

- フォームの送信（`fetch`）は**成功する**
- 同じWorkerにあるはずの管理ページをブラウザで開くと**404**
- curlやPowerShellで叩くと**成功する**（画面遷移ではないため）

「送信は通るのに管理ページだけ404」「コマンドラインからは動くのにブラウザだけ駄目」という、
非常に紛らわしい症状になる。実際にこれで半日溶かした。

### 対処

`wrangler.jsonc` の `assets.run_worker_first` に、そのパスを追加する。

```jsonc
"run_worker_first": ["/api/*", "/_feedback", "/_feedback/*"]
```

`true` にすると全リクエストがWorker経由になり、静的ページの表示までWorkerの
リクエスト数を消費してしまう。**配列でパスを限定すること。**

## 7. 管理ページ

```
https://karin-archive.com/_feedback?t=（FEEDBACK_TOKENの値）
```

- ログイン機構は無い。**URLを知っていれば誰でも開ける**
- トークンが違えば404を返す。ページの存在自体が分からないようにしてある
- トークンは64文字のランダム値なので、総当たりで当てるのは現実的に不可能
- 未読が上、新しい順に最大200件。「既読にする」「削除」ボタン付き

**トークンを紛失したら復旧できない**（Secretなので読み出せない）。
新しい値を作って `FEEDBACK_TOKEN` を上書きするしかない。
ブックマークかパスワードマネージャーに控えておくこと。

## 8. よくある変更のやり方

| やりたいこと | 触る場所 |
|---|---|
| 文言・プレースホルダを変える | `common/footer.php` の `.fb-` まわりのHTML |
| 見た目を変える | `css/style.css` の末尾、`.fb-` で始まるクラス |
| 種別（誤り/追加希望/その他）を増やす | `footer.php` のボタン、`worker/index.js` の `FB_KINDS` と `FB_LABEL` の**両方** |
| 文字数上限を変える | `worker/index.js` の `FB_MAX_BODY` と、`footer.php` の `maxlength` の**両方** |
| レート制限を変える | `worker/index.js` の `FB_RATE_MIN` / `FB_RATE_MAX` |
| 通知先をDiscord以外にする | `worker/index.js` の `notifyDiscord()` を差し替え。D1保存部分は触らなくていい |
| Turnstileをやめる | `TURNSTILE_SECRET` をダッシュボードから削除するだけ。コード変更不要 |

変更後は `git push` するだけでGitHub Actionsがデプロイする。
**ただしシークレットの変更はダッシュボードで行う。pushでは反映されない。**

## 9. 動かないときの切り分け

この機能は「ブラウザ / Worker / D1 / 外部サービス」と層が多いので、上から順に切っていく。

**手順1: Workerが応答しているか**

```
curl -i https://karin-archive.com/api/feedback
```

`405 {"error":"method"}` が返れば正常（POST専用なので405が正しい）。
サイトの404ページのHTMLが返ってきたら、**第6章の `run_worker_first` を疑う**。

**手順2: ブラウザからも届いているか**

同じURLをブラウザのアドレスバーに入れて開く。
コマンドラインでは405なのにブラウザでは404ページ、という食い違いが出たら、
これも `run_worker_first` の問題。

**手順3: D1にデータが入っているか**

Cloudflare → ストレージとデータベース → D1 → `karin-interview` → コンソール

```sql
SELECT id, kind, created_at, page_url FROM feedback ORDER BY id DESC LIMIT 10;
```

行があるのに通知が来ていなければ、原因は `DISCORD_WEBHOOK` だけ。逆に行が無ければ手前で失敗している。

**手順4: 送信が403で弾かれる**

Turnstileの検証失敗。ウィジェットの**ホスト名**が実際のドメイン（`karin-archive.com`）と
一致しているか確認する。ここがずれていると全滅する。
急ぎで止めたければ `TURNSTILE_SECRET` を削除すれば検証がスキップされる。

## 10. プライバシー上の約束

このフォームは匿名を謳っている。実装もそれに合わせてある。

- 名前・メールアドレスの入力欄は**そもそも無い**
- IPアドレスは**保存していない**。`ソルト + その日の日付 + IP` のSHA-256ハッシュだけを持つ
- 日付が混ざるので**翌日には別の値になり**、日をまたいだ追跡はできない。短時間のレート制限にしか使えない
- 保存しているのは、本文・種別・送信元のパス・User-Agent・日時

文言で「匿名」「返信しません」と明示している以上、**ここを勝手に変えない**こと。
送信者を特定できる情報を足すなら、フォームの文言も同時に直す必要がある。
