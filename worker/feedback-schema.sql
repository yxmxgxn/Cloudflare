-- 匿名フィードバック用 D1 スキーマ
-- 適用: wrangler d1 execute karin-interview --file worker/feedback-schema.sql --remote
--
-- 送信者を特定できる情報は持たない。
-- ip_hash は「ソルト＋その日の日付＋IP」のSHA-256で、日付が変われば別の値になる。
-- 復元も日をまたぐ追跡もできず、当日の短時間レート制限にだけ使う。

CREATE TABLE IF NOT EXISTS feedback (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  kind       TEXT,                            -- 'error' | 'add' | 'other' | NULL(未選択)
  body       TEXT NOT NULL,
  page_url   TEXT,                            -- 送信元のパス(/YouTube/karin-song など)
  user_agent TEXT,
  ip_hash    TEXT,
  created_at TEXT NOT NULL,                   -- ISO8601 (UTC)
  is_read    INTEGER NOT NULL DEFAULT 0
);

-- 管理ページの並び替え(未読が先、新しい順)用
CREATE INDEX IF NOT EXISTS idx_feedback_list ON feedback(is_read, created_at DESC);

-- レート制限の集計用
CREATE INDEX IF NOT EXISTS idx_feedback_rate ON feedback(ip_hash, created_at);
