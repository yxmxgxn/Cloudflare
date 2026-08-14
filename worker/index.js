/**
 * Cloudflare Worker (Static Assets 併用)
 *
 * 役割:
 *   - /api/interview-search?q=… : D1 の全文検索(bigram×FTS5)を引き、一致した記事IDだけ返す。
 *     本文・スニペットは一切返さない。
 *   - それ以外の全リクエスト : 静的アセット(env.ASSETS)へ委譲。既存ページの挙動は不変。
 *
 * 索引側(Python)と同じ normalize / bigram を使うこと(下の実装と一致させる)。
 */

const MAX_Q = 100;      // クエリ長の上限
const LIMIT = 500;      // 返す最大ID数

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

function json(obj, status = 200) {
  return new Response(JSON.stringify(obj), {
    status,
    headers: {
      "content-type": "application/json; charset=utf-8",
      "cache-control": "public, max-age=60",
    },
  });
}

async function handleSearch(url, env) {
  const q = url.searchParams.get("q") || "";
  const match = buildMatch(q);
  if (!match) return json({ ids: [] }); // 2文字未満などは空(サーバ絞り込み無効)

  // 1記事のbigramは複数行に分割されているため DISTINCT で重複IDをまとめる
  const { results } = await env.DB
    .prepare("SELECT DISTINCT id FROM article_fts WHERE article_fts MATCH ? LIMIT ?")
    .bind(match, LIMIT)
    .all();

  return json({ ids: results.map((r) => r.id) });
}

export default {
  async fetch(request, env) {
    const url = new URL(request.url);

    if (url.pathname === "/api/interview-search") {
      if (request.method !== "GET") return json({ error: "method" }, 405);
      try {
        return await handleSearch(url, env);
      } catch (e) {
        return json({ error: "search_failed" }, 500);
      }
    }

    // その他は静的アセットへ(既存ページはここを通る=挙動不変)
    return env.ASSETS.fetch(request);
  },
};
