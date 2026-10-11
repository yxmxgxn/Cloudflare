-- #佳林の１日１ハロ ポスト本文検索用 D1 スキーマ
-- 適用: wrangler d1 execute karin-hello --file worker/karinhello-schema.sql --remote
--
-- 本文そのものは持たない。検索に使うのは bigram（2文字断片）のみ。
-- 日次更新(update-daily.yml)が Python/generate_karinhello.py の出す SQL で毎回全件入れ直す。

CREATE VIRTUAL TABLE IF NOT EXISTS post_fts USING fts5(
  id UNINDEXED,
  bigram
);
