-- ハロプロ Instagram 投稿アーカイブ用 D1 スキーマ(karin-hello DB に同居)
-- 適用: wrangler d1 execute karin-hello --file worker/insta-schema.sql --remote
--
-- 日次更新(update-daily.yml)が Python/generate_insta.py の出す SQL を流し込む。
-- 全件入れ直しではなく追記・上書き(Instagram側は過去分を遡りにくいので、ここが原本になる)。
-- 本文は先頭の抜粋だけ持つ。検索は本文全体の bigram(insta_fts)で行う。

CREATE TABLE IF NOT EXISTS insta_post (
  id         TEXT PRIMARY KEY,  -- Instagram のメディアID
  account    TEXT NOT NULL,     -- ユーザー名
  ts         TEXT NOT NULL,     -- 投稿日時(JST, 'YYYY-MM-DD HH:MM')
  permalink  TEXT NOT NULL,
  media_type TEXT,              -- IMAGE / VIDEO / CAROUSEL_ALBUM
  excerpt    TEXT               -- 本文の先頭(一覧表示用)
);
CREATE INDEX IF NOT EXISTS insta_post_ts ON insta_post(ts);
CREATE INDEX IF NOT EXISTS insta_post_account_ts ON insta_post(account, ts);

CREATE VIRTUAL TABLE IF NOT EXISTS insta_fts USING fts5(
  id UNINDEXED,
  bigram
);
