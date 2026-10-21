-- インタビュー全文検索用 D1 スキーマ
-- 適用: wrangler d1 execute karin-interview --file worker/schema.sql --remote
--
-- 本文そのものは公開しない。検索に使うのは bigram（2文字断片）のみ。
-- FTS5 の既定トークナイザに食わせるため、索引ツール側で本文を
-- 「宮本 本佳 佳林 …」のように空白区切りの bigram 列へ変換して入れる。

-- 全文検索本体（id は検索対象にせず、返すためだけに保持）
CREATE VIRTUAL TABLE IF NOT EXISTS article_fts USING fts5(
  id UNINDEXED,
  bigram
);

-- タイトル・Feature・媒体名・メンバー名(スプシの値)。1項目1行、索引ワークフローのたびに全件入れ直す。
-- 本文とは別テーブルにして、本文とタイトルの境目をまたいだ偽の一致を防ぐ。
CREATE VIRTUAL TABLE IF NOT EXISTS article_meta_fts USING fts5(
  id UNINDEXED,
  bigram
);

-- 増分索引・再索引用のメタ。
-- src_hash: 取得元URL群のハッシュ。一致すれば再取得スキップ。
-- body: 本文の先頭2000字のプレビュー（BODY_PREVIEW）。全文は article_fts の bigram 索引側にある。読める本文を一切持ちたくなければ列ごと削除可。
CREATE TABLE IF NOT EXISTS article_src (
  id         TEXT PRIMARY KEY,
  src_hash   TEXT,
  pages      INTEGER,
  body       TEXT,
  indexed_at TEXT
);
