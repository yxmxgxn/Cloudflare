/**
 * Cloudflare Worker (Static Assets 併用)
 *
 * 役割:
 *   - /api/interview-search?q=… : D1 の全文検索(bigram×FTS5)を引き、一致した記事IDだけ返す。
 *     対象は本文と、スプシのタイトル・Feature・媒体名・メンバー名。
 *     本文・スニペットは一切返さない。
 *   - /api/karinhello-search?q=… : #佳林の１日１ハロ のポスト本文を同じ方式で引き、一致したポストIDだけ返す(D1 karin-hello)。
 *   - /api/insta?account=…&from=…&to=…&q=…&before=… : ハロプロ Instagram 投稿を日付の新しい順に返す(D1 karin-hello)。
 *   - POST /api/feedback : フッターの匿名フィードバックを受け、D1 に保存して Discord へ通知。
 *   - /_feedback?t=… : 受け取ったフィードバックの管理ページ(トークン一致時のみ。不一致は404)。
 *   - それ以外の全リクエスト : 静的アセット(env.ASSETS)へ委譲。既存ページの挙動は不変。
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

// ハロプロ Instagram 投稿一覧。account はカンマ区切り、from/to は YYYY-MM-DD、
// before は前ページ最後の ts(さらに古いものを返す)。q があれば本文の bigram 一致で絞る。
const INSTA_PAGE = 100;
const DATE_RE = /^\d{4}-\d{2}-\d{2}$/;

async function handleInsta(url, env) {
  const p = url.searchParams;
  const where = [];
  const args = [];
  const accounts = (p.get("account") || "").split(",").map((a) => a.trim()).filter(Boolean).slice(0, 100);
  if (accounts.length) {
    where.push(`account IN (${accounts.map(() => "?").join(",")})`);
    args.push(...accounts);
  }
  const from = p.get("from") || "";
  const to = p.get("to") || "";
  if (DATE_RE.test(from)) { where.push("ts >= ?"); args.push(from); }
  if (DATE_RE.test(to)) { where.push("ts < ?"); args.push(to + " 99"); } // その日の終わりまで
  const before = p.get("before") || "";
  if (before) { where.push("ts < ?"); args.push(before.slice(0, 16)); }
  const match = buildMatch(p.get("q") || "");
  if (match) {
    where.push("id IN (SELECT id FROM insta_fts WHERE insta_fts MATCH ?)");
    args.push(match);
  }
  const sql =
    "SELECT id, account, ts, permalink, media_type, excerpt FROM insta_post" +
    (where.length ? " WHERE " + where.join(" AND ") : "") +
    " ORDER BY ts DESC LIMIT ?";
  args.push(INSTA_PAGE);
  const { results } = await env.HELLO_DB.prepare(sql).bind(...args).all();
  return json({ posts: results, more: results.length === INSTA_PAGE }, 200, "public, max-age=300");
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

/* =========================================================================== */

export default {
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

    if (url.pathname === "/api/insta") {
      if (request.method !== "GET") return json({ error: "method" }, 405);
      try {
        return await handleInsta(url, env);
      } catch (e) {
        return json({ error: "insta_failed" }, 500, "no-store");
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
