/**
 * Cloudflare Worker (Static Assets 併用)
 *
 * 役割:
 *   - /api/interview-search?q=… : D1 の全文検索(bigram×FTS5)を引き、一致した記事IDだけ返す。
 *     対象は本文と、スプシのタイトル・Feature・媒体名・メンバー名。
 *     本文・スニペットは一切返さない。
 *   - /api/karinhello-search?q=… : #佳林の１日１ハロ のポスト本文を同じ方式で引き、一致したポストIDだけ返す(D1 karin-hello)。
 *   - POST /api/feedback : フッターの匿名フィードバックを受け、D1 に保存して Discord へ通知。
 *   - /_feedback?t=… : 受け取ったフィードバックの管理ページ(トークン一致時のみ。不一致は404)。
 *   - それ以外の全リクエスト : 静的アセット(env.ASSETS)へ委譲。既存ページの挙動は不変。
 *   - Cron : 宮本佳林のブログの最近の記事から自分のコメントを拾って D1 (COMMENT_DB) に保存し、新しく入ったら Discord へ通知。
 *
 * 索引側(Python)と同じ normalize / bigram を使うこと(下の実装と一致させる)。
 */

const MAX_Q = 100;      // クエリ長の上限
const LIMIT = 5000;     // 返す最大ID数(記事総数より十分大きく。500だとよくある語で結果が欠けた)

// 索引時と完全に一致させる正規化: NFKC→小文字→日本語と英数字以外を除去
function normalize(s) {
  return (s || "")
    .normalize("NFKC")
    .toLowerCase()
    .replace(/[^0-9a-z々぀-ヿ㐀-䶿一-鿿]/g, "");
}

function bigrams(s) {
  const out = [];
  for (let i = 0; i + 1 < s.length; i++) out.push(s.slice(i, i + 2));
  return out;
}

// FTS5 のフレーズクエリを組む(連続一致=元テキストの部分一致になる)
function buildMatch(q) {
  const toks = bigrams(normalize(q.slice(0, MAX_Q)));
  if (toks.length === 0) return null;
  const phrase = toks.join(" ").replace(/"/g, '""'); // 念のためエスケープ
  return '"' + phrase + '"';
}

function json(obj, status = 200, cache = "public, max-age=60") {
  return new Response(JSON.stringify(obj), {
    status,
    headers: {
      "content-type": "application/json; charset=utf-8",
      "cache-control": cache,
    },
  });
}

async function handleSearch(url, env) {
  const q = url.searchParams.get("q") || "";
  const match = buildMatch(q);
  if (!match) return json({ ids: [] }); // 2文字未満などは空(サーバ絞り込み無効)

  // 本文(article_fts) と タイトル・Feature・媒体名・メンバー名(article_meta_fts) の両方を引く。
  // 1記事が複数行に分かれているので UNION で重複IDをまとめる。
  let results;
  try {
    ({ results } = await env.DB
      .prepare(
        "SELECT id FROM article_fts WHERE article_fts MATCH ?1 " +
        "UNION SELECT id FROM article_meta_fts WHERE article_meta_fts MATCH ?1 LIMIT ?2"
      )
      .bind(match, LIMIT)
      .all());
  } catch (e) {
    // article_meta_fts は索引ワークフロー初回実行時に作られる。それまでは本文だけで検索する
    ({ results } = await env.DB
      .prepare("SELECT DISTINCT id FROM article_fts WHERE article_fts MATCH ? LIMIT ?")
      .bind(match, LIMIT)
      .all());
  }

  return json({ ids: results.map((r) => r.id) });
}

// #佳林の１日１ハロ のポスト本文検索。一致したポストIDだけ返す(本文は D1 にも持っていない)
async function handleKarinHelloSearch(url, env) {
  const match = buildMatch(url.searchParams.get("q") || "");
  if (!match) return json({ ids: [] });
  const { results } = await env.HELLO_DB
    .prepare("SELECT id FROM post_fts WHERE post_fts MATCH ? LIMIT ?")
    .bind(match, LIMIT)
    .all();
  return json({ ids: results.map((r) => r.id) });
}

/* ============================ 匿名フィードバック ============================ */

const FB_MAX_BODY = 2000;   // 本文の上限文字数
const FB_MAX_PATH = 300;    // 送信元パスの上限
const FB_KINDS = ["error", "add", "other"];
const FB_RATE_MIN = 10;     // レート制限の窓(分)
const FB_RATE_MAX = 5;      // 窓あたりの上限件数
const FB_LIST_LIMIT = 200;  // 管理ページに出す件数

const FB_LABEL = { error: "誤りの報告", add: "追加希望", other: "その他" };

async function sha256Hex(s) {
  const buf = await crypto.subtle.digest("SHA-256", new TextEncoder().encode(s));
  return [...new Uint8Array(buf)].map((b) => b.toString(16).padStart(2, "0")).join("");
}

function esc(s) {
  return String(s ?? "").replace(/[&<>"']/g, (c) =>
    ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
}

async function verifyTurnstile(token, ip, secret) {
  const form = new FormData();
  form.append("secret", secret);
  form.append("response", token);
  if (ip) form.append("remoteip", ip);
  const r = await fetch("https://challenges.cloudflare.com/turnstile/v0/siteverify", {
    method: "POST",
    body: form,
  });
  const d = await r.json();
  return d.success === true;
}

// 通知はあくまで「気づくため」。失敗しても D1 には残るので握りつぶす。
async function notifyDiscord(webhook, row) {
  const body = row.body.length > 1500 ? row.body.slice(0, 1500) + "…" : row.body;
  await fetch(webhook, {
    method: "POST",
    headers: { "content-type": "application/json" },
    body: JSON.stringify({
      embeds: [{
        title: FB_LABEL[row.kind] || "種別なし",
        description: body,
        color: 0x8c6bb8,
        fields: [{ name: "ページ", value: row.page_url || "(不明)" }],
        timestamp: row.created_at,
      }],
    }),
  });
}

async function handleFeedback(request, env, ctx) {
  let payload;
  try {
    payload = await request.json();
  } catch (e) {
    return json({ error: "bad_json" }, 400, "no-store");
  }

  const body = String(payload.body || "").trim();
  if (!body) return json({ error: "empty" }, 400, "no-store");
  if (body.length > FB_MAX_BODY) return json({ error: "too_long" }, 400, "no-store");

  const kind = FB_KINDS.includes(payload.kind) ? payload.kind : null;

  // 自サイトの相対パスだけ受ける(javascript: 等が管理ページの href に入らないように)
  let page = String(payload.page || "");
  if (!page.startsWith("/") || page.length > FB_MAX_PATH) page = "";

  const ip = request.headers.get("cf-connecting-ip") || "";
  const today = new Date().toISOString().slice(0, 10);
  const ipHash = await sha256Hex(`${env.FEEDBACK_SALT || "karin"}:${today}:${ip}`);

  // TURNSTILE_SECRET 未設定なら検証を飛ばす(キー投入前でもフォームが動く)
  if (env.TURNSTILE_SECRET) {
    const ok = await verifyTurnstile(String(payload.token || ""), ip, env.TURNSTILE_SECRET);
    if (!ok) return json({ error: "turnstile" }, 403, "no-store");
  }

  const since = new Date(Date.now() - FB_RATE_MIN * 60000).toISOString();
  const { results: cnt } = await env.DB
    .prepare("SELECT COUNT(*) AS n FROM feedback WHERE ip_hash = ? AND created_at > ?")
    .bind(ipHash, since)
    .all();
  if ((cnt[0]?.n || 0) >= FB_RATE_MAX) return json({ error: "rate" }, 429, "no-store");

  const createdAt = new Date().toISOString();
  const ua = (request.headers.get("user-agent") || "").slice(0, 300);

  await env.DB
    .prepare(
      "INSERT INTO feedback (kind, body, page_url, user_agent, ip_hash, created_at) VALUES (?, ?, ?, ?, ?, ?)"
    )
    .bind(kind, body, page, ua, ipHash, createdAt)
    .run();

  if (env.DISCORD_WEBHOOK) {
    ctx.waitUntil(
      notifyDiscord(env.DISCORD_WEBHOOK, { kind, body, page_url: page, created_at: createdAt })
        .catch(() => {})
    );
  }

  return json({ ok: true }, 200, "no-store");
}

function fbAdminHtml(rows, token) {
  const q = encodeURIComponent(token);
  const items = rows.map((r) => `
    <li class="${r.is_read ? "read" : "unread"}">
      <div class="meta">
        <b>${esc(FB_LABEL[r.kind] || "種別なし")}</b>
        <time>${esc(r.created_at)}</time>
        ${r.page_url ? `<a href="${esc(r.page_url)}" target="_blank">${esc(r.page_url)}</a>` : ""}
      </div>
      <p>${esc(r.body)}</p>
      <form method="post" action="?t=${q}">
        <input type="hidden" name="id" value="${r.id}">
        <button name="action" value="${r.is_read ? "unread" : "read"}">${r.is_read ? "未読に戻す" : "既読にする"}</button>
        <button name="action" value="delete" onclick="return confirm('削除しますか？')">削除</button>
      </form>
    </li>`).join("");

  const unread = rows.filter((r) => !r.is_read).length;
  return `<!doctype html><html lang="ja"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>フィードバック (${unread})</title>
<style>
:root{--accent:#8C6BB8}
body{font-family:system-ui,sans-serif;max-width:760px;margin:0 auto;padding:16px;color:#333;line-height:1.7}
h1{font-size:18px;border-bottom:2px solid var(--accent);padding-bottom:8px}
ul{list-style:none;padding:0}
li{border:1px solid #ddd;border-radius:6px;padding:12px;margin-bottom:12px}
li.unread{border-left:4px solid var(--accent);background:#faf8fc}
li.read{opacity:.55}
.meta{font-size:12px;color:#666;display:flex;gap:12px;flex-wrap:wrap;align-items:center}
.meta b{color:var(--accent)}
p{white-space:pre-wrap;word-break:break-word;margin:8px 0}
button{font-size:12px;padding:4px 10px;margin-right:6px;cursor:pointer;border:1px solid #bbb;border-radius:4px;background:#fff}
</style></head><body>
<h1>フィードバック — 未読 ${unread} / 全 ${rows.length}</h1>
${rows.length ? `<ul>${items}</ul>` : "<p>まだ何も届いていません。</p>"}
</body></html>`;
}

async function handleFeedbackAdmin(url, request, env) {
  // ダッシュボードへ貼る際に前後へ空白や改行が混ざることがあるので落としてから比較する
  const expected = (env.FEEDBACK_TOKEN || "").trim();
  const token = (url.searchParams.get("t") || "").trim();

  // トークン不一致は 404(存在自体を隠す)
  if (!expected || token !== expected) {
    return new Response("Not Found", { status: 404 });
  }

  if (request.method === "POST") {
    const form = await request.formData();
    const id = Number(form.get("id"));
    const action = String(form.get("action") || "");
    if (Number.isInteger(id) && id > 0) {
      if (action === "delete") {
        await env.DB.prepare("DELETE FROM feedback WHERE id = ?").bind(id).run();
      } else if (action === "read" || action === "unread") {
        await env.DB.prepare("UPDATE feedback SET is_read = ? WHERE id = ?")
          .bind(action === "read" ? 1 : 0, id).run();
      }
    }
    // 再送信防止のため GET へ戻す
    return new Response(null, {
      status: 303,
      headers: { location: `${url.pathname}?t=${encodeURIComponent(token)}` },
    });
  }

  const { results } = await env.DB
    .prepare(
      "SELECT id, kind, body, page_url, created_at, is_read FROM feedback ORDER BY is_read ASC, created_at DESC LIMIT ?"
    )
    .bind(FB_LIST_LIMIT)
    .all();

  return new Response(fbAdminHtml(results, token), {
    headers: {
      "content-type": "text/html; charset=utf-8",
      "cache-control": "no-store",
      "x-robots-tag": "noindex, nofollow",
    },
  });
}

/* ===================== ブログコメントの保存(Cron) ===================== */

// コメントは承認されてから API に出るので、最近の記事を毎回見直す。
// 保存は comment_id で重複を弾くだけなので、何度拾っても同じ。
// 最初は1回ごとに記事を遡り、BACKFILL_FROM より前の記事に届いたら以降は最新分だけ見る。
const AMEBLO_BLOG_ID = 10063665039;      // ameblo.jp/miyamotokarin-official
const MY_AMEBA_ID = "yxmxgxn";
const MY_NAME = "よみがな";              // ログインせず名前だけで書いた回もある(他に同名はいない)
const BACKFILL_FROM = "2026-04-01";      // 最初のコメントは 4/25(4/23 の記事)。少し古い記事まで見る
const ENTRIES_PER_RUN = 15;              // 無料プランのサブリクエスト上限(50)に収める
const AMEBLO_API = "https://ameblo.jp/_api";

const BLOG_COMMENT_SCHEMA = [
  `CREATE TABLE IF NOT EXISTS blog_comment (
     comment_id       INTEGER PRIMARY KEY,   -- アメブロのコメントID
     entry_id         INTEGER NOT NULL,
     entry_title      TEXT,
     entry_created_at TEXT,                  -- 記事の投稿日時
     comment_name     TEXT,
     comment_url      TEXT,                  -- 名前に付けたURL(付けずに書いた回は空)
     body             TEXT NOT NULL,         -- <br /> を改行に戻したプレーンテキスト
     posted_at        TEXT NOT NULL,         -- 書いた日時(アメブロの値そのまま, +09:00)
     updated_at       TEXT,                  -- アメブロ側の更新日時(だいたい承認された日時)
     saved_at         TEXT NOT NULL          -- この表に入った日時(UTC)
   )`,
  // 遡りの進み具合。backfill_offset が無ければ完了
  `CREATE TABLE IF NOT EXISTS blog_comment_state (key TEXT PRIMARY KEY, value TEXT)`,
  `INSERT OR IGNORE INTO blog_comment_state (key, value) VALUES ('backfill_offset', '0')`,
];

async function amebloJson(path) {
  const r = await fetch(`${AMEBLO_API}/${path}`, { headers: { "user-agent": "Mozilla/5.0 (karin-archive)" } });
  if (!r.ok) throw new Error(`ameblo ${r.status}: ${path}`);
  return r.json();
}

function isMine(c) {
  return c.comment_author?.ameba_id === MY_AMEBA_ID
    || (c.comment_url || "").includes(`ameblo.jp/${MY_AMEBA_ID}/`)
    || c.comment_name === MY_NAME;
}

// コメント本文は <br /> と実体参照だけのHTML
function commentText(html) {
  return (html || "")
    .replace(/<br\s*\/?>/gi, "\n")
    .replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(Number(n)))
    .replace(/&(lt|gt|quot|#39|amp);/g, (_, e) => ({ lt: "<", gt: ">", quot: '"', "#39": "'", amp: "&" }[e]));
}

async function collectBlogComments(env) {
  // 最初の1回で表を作る。以降は IF NOT EXISTS / OR IGNORE で何もしない
  await env.COMMENT_DB.batch(BLOG_COMMENT_SCHEMA.map((s) => env.COMMENT_DB.prepare(s)));
  const state = await env.COMMENT_DB.prepare("SELECT value FROM blog_comment_state WHERE key = 'backfill_offset'").first();
  const backfilling = state !== null;
  const offset = backfilling ? Number(state.value) : 0;

  const d = await amebloJson(`blogEntries;blogId=${AMEBLO_BLOG_ID};limit=${ENTRIES_PER_RUN};offset=${offset}`);
  const entries = Object.values(d.entities?.entryMap || {});
  const rows = [];
  for (const e of entries) {
    for (let off = 0; ; off += 100) {
      const c = await amebloJson(`blogComments;blogId=${AMEBLO_BLOG_ID};entryId=${e.entry_id};limit=100;offset=${off}`);
      for (const x of Object.values(c.commentMap || {})) {
        if (isMine(x)) rows.push({ e, x });
      }
      if ((c.commentIds || []).length < 100) break;
    }
  }

  const savedAt = new Date().toISOString();
  const stmt = env.COMMENT_DB.prepare(
    `INSERT OR IGNORE INTO blog_comment
       (comment_id, entry_id, entry_title, entry_created_at, comment_name, comment_url, body, posted_at, updated_at, saved_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
  );
  const res = rows.length ? await env.COMMENT_DB.batch(rows.map(({ e, x }) => stmt.bind(
    x.comment_id, e.entry_id, e.entry_title, e.entry_created_datetime, x.comment_name, x.comment_url || "",
    commentText(x.comment_text), x.ins_datetime, x.upd_datetime || null, savedAt,
  ))) : [];
  const added = rows.filter((_, i) => res[i].meta.changes > 0);

  if (backfilling) {
    const oldest = entries.map((e) => e.entry_created_datetime).sort()[0] || "";
    if (!entries.length || oldest < BACKFILL_FROM) {
      await env.COMMENT_DB.prepare("DELETE FROM blog_comment_state WHERE key = 'backfill_offset'").run();
      const { n } = await env.COMMENT_DB.prepare("SELECT COUNT(*) AS n FROM blog_comment").first();
      await discordSafe(env, { content: `ブログコメントの過去分を取り込みました(${n}件)` });
    } else {
      // 遡る間に新しい記事が出ると後ろへずれるので、少し重ねて進める
      await env.COMMENT_DB.prepare("UPDATE blog_comment_state SET value = ? WHERE key = 'backfill_offset'")
        .bind(String(offset + ENTRIES_PER_RUN - 3)).run();
    }
    return;
  }

  added.sort((a, b) => a.x.ins_datetime.localeCompare(b.x.ins_datetime));
  // Discord は1メッセージにつき embed 10個まで
  for (let i = 0; i < added.length; i += 10) {
    await discordSafe(env, {
      content: i === 0 ? `ブログコメントが反映されました(${added.length}件)` : undefined,
      embeds: added.slice(i, i + 10).map(({ e, x }) => {
        const body = commentText(x.comment_text);
        return {
          title: e.entry_title,
          url: `https://ameblo.jp/miyamotokarin-official/entry-${e.entry_id}.html`,
          description: body.length > 1500 ? body.slice(0, 1500) + "…" : body,
          color: 0xe5739a,
          timestamp: new Date(x.ins_datetime).toISOString(),
        };
      }),
    });
  }
}

// 通知に失敗しても D1 には入っている
async function discordSafe(env, payload) {
  if (!env.DISCORD_WEBHOOK) return;
  try {
    await fetch(env.DISCORD_WEBHOOK, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify(payload),
    });
  } catch (e) {}
}

/* =========================================================================== */

export default {
  async scheduled(controller, env, ctx) {
    ctx.waitUntil(collectBlogComments(env));
  },

  async fetch(request, env, ctx) {
    const url = new URL(request.url);

    if (url.pathname === "/api/interview-search") {
      if (request.method !== "GET") return json({ error: "method" }, 405);
      try {
        return await handleSearch(url, env);
      } catch (e) {
        return json({ error: "search_failed" }, 500);
      }
    }

    if (url.pathname === "/api/karinhello-search") {
      if (request.method !== "GET") return json({ error: "method" }, 405);
      try {
        return await handleKarinHelloSearch(url, env);
      } catch (e) {
        return json({ error: "search_failed" }, 500, "no-store");
      }
    }

    if (url.pathname === "/api/feedback") {
      if (request.method !== "POST") return json({ error: "method" }, 405, "no-store");
      try {
        return await handleFeedback(request, env, ctx);
      } catch (e) {
        return json({ error: "feedback_failed" }, 500, "no-store");
      }
    }

    if (url.pathname === "/_feedback" || url.pathname === "/_feedback/") {
      try {
        return await handleFeedbackAdmin(url, request, env);
      } catch (e) {
        return new Response("error", { status: 500 });
      }
    }

    // その他は静的アセットへ(既存ページはここを通る=挙動不変)
    return env.ASSETS.fetch(request);
  },
};
