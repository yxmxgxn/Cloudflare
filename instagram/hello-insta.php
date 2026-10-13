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
  <p class="ig-hint">メンバー未選択なら全員。Ctrl（Macは⌘）を押しながらクリックで複数選べます。一覧のメンバー名を押すと、その人だけに絞り込めます。</p>
</form>

<section id="ig-cal" aria-label="投稿カレンダー">
  <div class="ig-cal-head">
    <button type="button" onclick="igMonth(-12)" aria-label="前の年">«</button>
    <button type="button" onclick="igMonth(-1)" aria-label="前の月">‹</button>
    <strong id="ig-cal-title"></strong>
    <button type="button" onclick="igMonth(1)" aria-label="次の月">›</button>
    <button type="button" onclick="igMonth(12)" aria-label="次の年">»</button>
  </div>
  <div class="ig-cal-grid" id="ig-cal-grid"></div>
  <p class="ig-hint">数字はその日の投稿数（メンバー・本文の条件込み）。日付を押すとその日の投稿だけを表示し、もう一度押すと解除します。</p>
</section>

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
  .ig-hint { font-size: .85em; opacity: .75; margin: .3em 0 0; }
  #ig-cal { max-width: 28em; margin: 1em 0; }
  .ig-cal-head { display: flex; align-items: center; gap: .4em; margin-bottom: .4em; }
  .ig-cal-head strong { flex: 1; text-align: center; }
  .ig-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
  .ig-cal-grid .dow { text-align: center; font-size: .75em; opacity: .7; padding: .2em 0; }
  .ig-cal-grid .dow:first-child { color: #d33; }
  .ig-cal-grid .dow:nth-child(7) { color: #36c; }
  .ig-day { aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 1px solid rgba(128,128,128,.25); border-radius: .3em; background: none; color: inherit;
            font: inherit; font-size: .85em; padding: 0; cursor: pointer; }
  .ig-day:disabled { cursor: default; opacity: .35; }
  .ig-day .n { font-size: .75em; font-weight: bold; }
  .ig-day.has { background: rgba(225, 48, 108, calc(.12 + var(--lv) * .5)); }
  .ig-day.sel { outline: 2px solid #e1306c; outline-offset: -2px; }
  .ig-day.today { text-decoration: underline; }
  #ig-list { list-style: none; padding: 0; }
  #ig-list li { padding: .5em 0; border-bottom: 1px solid rgba(128,128,128,.25); }
  .ig-meta { font-size: .85em; opacity: .8; margin-right: .3em; }
  .ig-name { font: inherit; font-size: .85em; font-weight: bold; background: none; border: 0; padding: 0; margin-right: .5em;
             color: inherit; text-decoration: underline dotted; cursor: pointer; }
  .ig-type { font-size: .75em; padding: 0 .4em; border: 1px solid currentColor; border-radius: .3em; margin-right: .4em; }
  #ig-list a { display: block; margin-top: .2em; word-break: break-all; }
  #ig-list mark { background: #ffe066; color: #000; border-radius: .15em; }
</style>

<script>
(function () {
  var TYPE = { VIDEO: "動画", CAROUSEL_ALBUM: "複数枚", IMAGE: "画像" };
  var names = window.INSTA_NAMES || {};
  var lastTs = "", busy = false, reqNo = 0;
  var calYear, calMonth; // 表示中の月(calMonth は 1-12)
  var $ = function (id) { return document.getElementById(id); };
  var pad = function (n) { return (n < 10 ? "0" : "") + n; };

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

  // 検索語を <mark> で囲む(大文字小文字・全角半角の違いは無視)
  function highlight(el, text, q) {
    var words = q.split(/\s+/).filter(Boolean);
    if (!words.length) { el.textContent = text; return; }
    var norm = function (s) { return s.normalize("NFKC").toLowerCase(); };
    // NFKC で長さが変わる文字があるとずれるので、1文字ずつ正規化して元の位置と対応を取る
    var chars = Array.from(text), flat = "", map = [];
    chars.forEach(function (c, i) {
      var n = norm(c);
      for (var k = 0; k < n.length; k++) map.push(i);
      flat += n;
    });
    var hit = chars.map(function () { return false; });
    words.forEach(function (w) {
      w = norm(w);
      for (var i = flat.indexOf(w); i >= 0; i = flat.indexOf(w, i + 1)) {
        for (var k = i; k < i + w.length; k++) hit[map[k]] = true;
      }
    });
    var buf = "", on = false;
    function flush() {
      if (!buf) return;
      if (on) { var m = document.createElement("mark"); m.textContent = buf; el.appendChild(m); }
      else el.appendChild(document.createTextNode(buf));
      buf = "";
    }
    chars.forEach(function (c, i) { if (hit[i] !== on) { flush(); on = hit[i]; } buf += c; });
    flush();
  }

  function row(post, q) {
    var li = document.createElement("li");
    var meta = document.createElement("span");
    meta.className = "ig-meta";
    meta.textContent = post.ts.replace(/-/g, "/");
    li.appendChild(meta);
    var nm = document.createElement("button");
    nm.type = "button";
    nm.className = "ig-name";
    nm.textContent = names[post.account] || post.account;
    nm.title = "このメンバーだけに絞り込む";
    nm.onclick = function () { igOnly(post.account); };
    li.appendChild(nm);
    if (TYPE[post.media_type]) {
      var t = document.createElement("span");
      t.className = "ig-type";
      t.textContent = TYPE[post.media_type];
      li.appendChild(t);
    }
    var a = document.createElement("a");
    // /reel/ だと別の動画に飛ばされることがあるので、リールも開ける /p/ にそろえる
    a.href = post.permalink.replace("/reel/", "/p/");
    a.target = "_blank";
    a.rel = "noopener noreferrer";
    if (post.excerpt) highlight(a, post.excerpt, q); else a.textContent = "（本文なし）";
    li.appendChild(a);
    return li;
  }

  /* ---------- カレンダー ---------- */

  function drawCalendar(days) {
    var grid = $("ig-cal-grid");
    grid.innerHTML = "";
    $("ig-cal-title").textContent = calYear + "年" + calMonth + "月";
    "日月火水木金土".split("").forEach(function (d) {
      var e = document.createElement("div");
      e.className = "dow";
      e.textContent = d;
      grid.appendChild(e);
    });
    var first = new Date(calYear, calMonth - 1, 1).getDay();
    var last = new Date(calYear, calMonth, 0).getDate();
    var max = 1;
    Object.keys(days || {}).forEach(function (k) { if (days[k] > max) max = days[k]; });
    var now = new Date();
    var today = now.getFullYear() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate());
    var selDay = $("ig-from").value && $("ig-from").value === $("ig-to").value ? $("ig-from").value : "";
    for (var i = 0; i < first; i++) grid.appendChild(document.createElement("div"));
    for (var d = 1; d <= last; d++) {
      var key = calYear + "-" + pad(calMonth) + "-" + pad(d);
      var n = days ? (days[key] || 0) : 0;
      var b = document.createElement("button");
      b.type = "button";
      b.className = "ig-day" + (n ? " has" : "") + (key === selDay ? " sel" : "") + (key === today ? " today" : "");
      b.innerHTML = "<span>" + d + "</span>" + (n ? '<span class="n">' + n + "</span>" : "");
      if (n) b.style.setProperty("--lv", (n / max).toFixed(2));
      b.disabled = !n && key !== selDay;
      b.setAttribute("aria-label", calMonth + "月" + d + "日 " + n + "件");
      b.onclick = (function (k) { return function () { igDay(k); }; })(key);
      grid.appendChild(b);
    }
  }

  var calNo = 0;
  function loadCalendar() {
    var p = params();
    p.delete("from");
    p.delete("to");
    p.set("month", calYear + "-" + pad(calMonth));
    drawCalendar(null);
    var my = ++calNo;
    fetch("/api/insta-calendar?" + p.toString())
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (d) { if (my === calNo) drawCalendar(d.days); })
      .catch(function () {});
  }

  window.igMonth = function (delta) {
    var m = calYear * 12 + (calMonth - 1) + delta;
    calYear = Math.floor(m / 12);
    calMonth = m % 12 + 1;
    loadCalendar();
  };

  window.igDay = function (key) {
    var on = !($("ig-from").value === key && $("ig-to").value === key);
    $("ig-from").value = $("ig-to").value = on ? key : "";
    igLoad(false);
  };

  window.igOnly = function (account) {
    Array.prototype.forEach.call($("ig-account").options, function (o) { o.selected = o.value === account; });
    igLoad(false);
    $("ig-controls").scrollIntoView({ behavior: "smooth" });
  };

  /* ---------- 一覧 ---------- */

  window.igLoad = function (more) {
    if (busy && more) return;
    busy = true;
    var my = ++reqNo;
    var p = params();
    var q = p.get("q") || "";
    if (more && lastTs) p.set("before", lastTs);
    if (!more) {
      $("ig-list").innerHTML = "";
      lastTs = "";
      // 条件をURLに残して、共有・再読み込みで同じ結果に戻れるようにする
      history.replaceState(null, "", p.toString() ? "?" + p.toString() : location.pathname);
      // 日付の指定があればカレンダーもその月へ
      var f = $("ig-from").value;
      if (f) { calYear = +f.slice(0, 4); calMonth = +f.slice(5, 7); }
      loadCalendar();
    }
    $("ig-status").textContent = "読み込み中…";
    $("ig-more").hidden = true;
    fetch("/api/insta?" + p.toString())
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (d) {
        if (my !== reqNo) return; // 後から出した条件の結果を優先
        d.posts.forEach(function (post) { $("ig-list").appendChild(row(post, q)); });
        if (d.posts.length) lastTs = d.posts[d.posts.length - 1].ts;
        var n = $("ig-list").children.length;
        $("ig-status").textContent = n ? n + "件表示中" : "該当する投稿はありません";
        $("ig-more").hidden = !d.more;
      })
      .catch(function () {
        if (my === reqNo) $("ig-status").textContent = "読み込みに失敗しました。時間をおいて再度お試しください。";
      })
      .then(function () { if (my === reqNo) busy = false; });
  };

  window.igSearch = function () { igLoad(false); };

  window.igReset = function () {
    Array.prototype.forEach.call($("ig-account").options, function (o) { o.selected = false; });
    $("ig-from").value = $("ig-to").value = $("ig-q").value = "";
    var now = new Date();
    calYear = now.getFullYear();
    calMonth = now.getMonth() + 1;
    igLoad(false);
  };

  // メンバーの選択を変えたらすぐ反映
  $("ig-account").addEventListener("change", function () { igLoad(false); });

  // URLの条件を復元してから初回表示
  var q = new URLSearchParams(location.search);
  var acc = (q.get("account") || "").split(",");
  Array.prototype.forEach.call($("ig-account").options, function (o) { o.selected = acc.indexOf(o.value) >= 0; });
  $("ig-from").value = q.get("from") || "";
  $("ig-to").value = q.get("to") || "";
  $("ig-q").value = q.get("q") || "";
  var now = new Date();
  calYear = now.getFullYear();
  calMonth = now.getMonth() + 1;
  igLoad(false);
})();
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
