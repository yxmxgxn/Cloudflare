<?php
  $title = "ハロプロ Instagram 投稿アーカイブ";
  $description = "ハロー！プロジェクト現役メンバーと宮本佳林さんの公式Instagramの投稿を、日付・メンバー・本文で検索できるアーカイブです。毎日自動で更新しています。";
  $jsonld_type = "Dataset";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ Instagram 投稿アーカイブ</h1>
  <p>ハロー！プロジェクト現役メンバーと宮本佳林さんの公式Instagramの投稿を、日付・メンバー・本文で探せます。</p>
  <p>Instagramでは難しい「日付で遡る」「本文で検索」ができるように、投稿を毎日自動で集めています。リンクはInstagramの投稿に飛びます。</p>

<form id="ig-controls" onsubmit="igSearch(); return false;">
  <select id="ig-account" multiple size="6" aria-label="メンバー（Ctrl/⌘で複数選択）">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/insta_accounts.php'; ?>
  </select>
  <div class="ig-row">
    <label>いつから <input type="date" id="ig-from"></label>
    <label>いつまで <input type="date" id="ig-to"></label>
  </div>
  <div class="ig-row">
    <input type="text" id="ig-q" placeholder="本文で検索（2文字以上）">
    <button type="submit">表示</button>
    <button type="button" onclick="igReset()">リセット</button>
  </div>
  <p class="ig-hint">メンバー未選択なら全員。Ctrl（Macは⌘）を押しながらクリックで複数選べます。</p>
</form>

<ul id="ig-list"></ul>
<p id="ig-status"></p>
<button type="button" id="ig-more" hidden onclick="igLoad(true)">さらに古い投稿</button>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/insta_generated.php'; ?>
</main>

<style>
  #ig-controls { display: flex; flex-direction: column; gap: .5em; margin: 1em 0; }
  #ig-account { width: 100%; max-width: 28em; }
  .ig-row { display: flex; flex-wrap: wrap; gap: .5em; align-items: center; }
  #ig-q { flex: 1; min-width: 12em; }
  .ig-hint { font-size: .85em; opacity: .75; margin: 0; }
  #ig-list { list-style: none; padding: 0; }
  #ig-list li { padding: .5em 0; border-bottom: 1px solid rgba(128,128,128,.25); }
  .ig-meta { font-size: .85em; opacity: .8; margin-right: .5em; }
  .ig-type { font-size: .75em; padding: 0 .4em; border: 1px solid currentColor; border-radius: .3em; margin-right: .4em; }
  #ig-list a { display: block; margin-top: .2em; word-break: break-all; }
</style>

<script>
(function () {
  var TYPE = { VIDEO: "動画", CAROUSEL_ALBUM: "複数枚", IMAGE: "画像" };
  var names = window.INSTA_NAMES || {};
  var lastTs = "", busy = false;
  var $ = function (id) { return document.getElementById(id); };

  function params() {
    var sel = Array.prototype.filter.call($("ig-account").options, function (o) { return o.selected; })
      .map(function (o) { return o.value; });
    var p = new URLSearchParams();
    if (sel.length) p.set("account", sel.join(","));
    if ($("ig-from").value) p.set("from", $("ig-from").value);
    if ($("ig-to").value) p.set("to", $("ig-to").value);
    if ($("ig-q").value.trim()) p.set("q", $("ig-q").value.trim());
    return p;
  }

  function row(post) {
    var li = document.createElement("li");
    var meta = document.createElement("span");
    meta.className = "ig-meta";
    meta.textContent = post.ts.replace(/-/g, "/") + "　" + (names[post.account] || post.account);
    li.appendChild(meta);
    if (TYPE[post.media_type]) {
      var t = document.createElement("span");
      t.className = "ig-type";
      t.textContent = TYPE[post.media_type];
      li.appendChild(t);
    }
    var a = document.createElement("a");
    a.href = post.permalink;
    a.target = "_blank";
    a.rel = "noopener noreferrer";
    a.textContent = post.excerpt || "（本文なし）";
    li.appendChild(a);
    return li;
  }

  window.igLoad = function (more) {
    if (busy) return;
    busy = true;
    var p = params();
    if (more && lastTs) p.set("before", lastTs);
    if (!more) { $("ig-list").innerHTML = ""; lastTs = ""; }
    $("ig-status").textContent = "読み込み中…";
    $("ig-more").hidden = true;
    // 条件をURLに残して、共有・再読み込みで同じ結果に戻れるようにする
    if (!more) history.replaceState(null, "", p.toString() ? "?" + p.toString() : location.pathname);
    fetch("/api/insta?" + p.toString())
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (d) {
        d.posts.forEach(function (post) { $("ig-list").appendChild(row(post)); });
        if (d.posts.length) lastTs = d.posts[d.posts.length - 1].ts;
        var n = $("ig-list").children.length;
        $("ig-status").textContent = n ? n + "件表示中" : "該当する投稿はありません";
        $("ig-more").hidden = !d.more;
      })
      .catch(function () { $("ig-status").textContent = "読み込みに失敗しました。時間をおいて再度お試しください。"; })
      .then(function () { busy = false; });
  };

  window.igSearch = function () { igLoad(false); };

  window.igReset = function () {
    Array.prototype.forEach.call($("ig-account").options, function (o) { o.selected = false; });
    $("ig-from").value = $("ig-to").value = $("ig-q").value = "";
    igLoad(false);
  };

  // URLの条件を復元してから初回表示
  var q = new URLSearchParams(location.search);
  var acc = (q.get("account") || "").split(",");
  Array.prototype.forEach.call($("ig-account").options, function (o) { o.selected = acc.indexOf(o.value) >= 0; });
  $("ig-from").value = q.get("from") || "";
  $("ig-to").value = q.get("to") || "";
  $("ig-q").value = q.get("q") || "";
  igLoad(false);
})();
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
