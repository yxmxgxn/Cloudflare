<?php
  // ハロプロ研修生ブログの目次。karin-archive の中の1ページだが、ヘッダー・フッター・見た目は専用にして
  // 別のサイトとして見せる(共通の header.php / footer.php は使わない)。誤りの報告フォームだけ共通。
  // 中身は data/kenshusei-blog.json(Python/generate_kenshusei_blog.py が毎日更新)。本文は持たない。
  $title = "ハロプロ研修生ブログ 目次";
  $description = "ハロプロ研修生ブログ(upfc.jp)を、メンバーと月で探せる目次です。記事は upfc.jp で読めます。非公式。";
  $canonical_url = "https://karin-archive.com/hello-kenshusei-blog";
?>
<!Doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="same-origin">
<title><?php echo $title; ?></title>
<meta name="description" content="<?php echo $description; ?>">
<link rel="canonical" href="<?php echo $canonical_url; ?>">
<link rel="icon" href="/img/favicon.ico" type="image/x-icon" sizes="any">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap">
<link rel="stylesheet" media="all" href="/css/kenshusei-blog.css">
<link rel="stylesheet" media="all" href="/css/feedback.css">

<!-- Google Analytics(karin-archive と同じ) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-MCL3G6E983"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-MCL3G6E983');
</script>
</head>

<body class="kb">
<header class="kb-head">
  <h1><a href="/hello-kenshusei-blog">ハロプロ研修生ブログ 目次</a></h1>
  <p class="kb-lead">ハロプロ研修生のブログを、メンバーと月で探せます。記事は upfc.jp で読めます。</p>
</header>

<main class="kb-main">
  <section class="kb-members" aria-label="メンバーで絞る">
    <div id="kb-members" class="kb-chips"></div>
  </section>

  <nav class="kb-month" aria-label="月でさかのぼる">
    <button type="button" id="kb-prev" aria-label="前の月">‹</button>
    <select id="kb-ym" aria-label="月"></select>
    <button type="button" id="kb-next" aria-label="次の月">›</button>
  </nav>

  <p id="kb-status" class="kb-status" aria-live="polite">読み込み中…</p>
  <ol id="kb-list" class="kb-list"></ol>
</main>

<footer class="kb-foot">
  <p>
    upfc.jp の「ハロプロ研修生 無料コンテンツ」の一覧から、記事の日付と書いた人だけを集めた非公式の目次です。
    本文や写真はこのページには載せていません。記事は upfc.jp でお読みください。
  </p>
  <p id="kb-updated"></p>
  <?php include __DIR__ . '/common/feedback.php'; ?>
  <p class="kb-home"><a href="/">宮本佳林・M-line・ハロプロ Archive</a> の1ページです</p>
  <ul class="kb-links">
    <li><a href="/common/privacy" target="_blank">プライバシーポリシー</a></li>
    <li><a href="/common/disclaimer" target="_blank">免責事項</a></li>
    <li><a href="https://x.com/yxmxgxn" target="_blank">X @yxmxgxn</a></li>
  </ul>
</footer>

<script>
(function () {
  var $ = function (id) { return document.getElementById(id); };
  var DETAIL = 'https://www.upfc.jp/helloproject/artist/trcontents_detail.php?@uid=';
  var entries = [], name = '', ym = '';

  function el(tag, attrs, text) {
    var e = document.createElement(tag);
    for (var k in attrs || {}) e.setAttribute(k, attrs[k]);
    if (text != null) e.textContent = text;
    return e;
  }
  var label = function (m) { var p = m.split('-'); return p[0] + '年' + Number(p[1]) + '月'; };
  var picked = function () { return name ? entries.filter(function (e) { return e.name === name; }) : entries; };

  // 選んでいる人が書いた月だけを、新しい順に
  function months() {
    var seen = {}, out = [];
    picked().forEach(function (e) { var m = e.date.slice(0, 7); if (!seen[m]) { seen[m] = 1; out.push(m); } });
    return out;
  }

  function drawMembers() {
    var box = $('kb-members');
    box.textContent = '';
    // 最近書いた人から並べる(いまの研修生が先に来る)
    var order = [], count = {};
    entries.forEach(function (e) {
      if (!(e.name in count)) { count[e.name] = 0; order.push(e.name); }
      count[e.name]++;
    });
    var all = el('button', { type: 'button', class: 'kb-chip' + (name ? '' : ' is-on') }, 'すべて');
    all.addEventListener('click', function () { setName(''); });
    box.appendChild(all);
    order.forEach(function (n) {
      var b = el('button', { type: 'button', class: 'kb-chip' + (name === n ? ' is-on' : '') }, n);
      b.appendChild(el('span', { class: 'kb-n' }, String(count[n])));
      b.addEventListener('click', function () { setName(name === n ? '' : n); });
      box.appendChild(b);
    });
  }

  function draw() {
    var ms = months();
    if (ms.indexOf(ym) < 0) ym = ms[0] || '';
    var sel = $('kb-ym');
    sel.textContent = '';
    ms.forEach(function (m) { sel.appendChild(el('option', { value: m }, label(m))); });
    sel.value = ym;
    var i = ms.indexOf(ym);
    $('kb-prev').disabled = i < 0 || i >= ms.length - 1;   // 前の月 = 古い方
    $('kb-next').disabled = i <= 0;

    var list = $('kb-list');
    list.textContent = '';
    var rows = picked().filter(function (e) { return e.date.slice(0, 7) === ym; });
    rows.forEach(function (e) {
      var li = el('li');
      var a = el('a', { href: DETAIL + encodeURIComponent(e.id), target: '_blank' });
      a.appendChild(el('time', { datetime: e.date }, e.date.replace(/-/g, '.')));
      a.appendChild(el('span', { class: 'kb-name' }, e.name));
      li.appendChild(a);
      list.appendChild(li);
    });
    $('kb-status').textContent = (name ? name + ' ／ ' : '') + (ym ? label(ym) + ' ／ ' : '') + rows.length + '件';
    drawMembers();
    sync();
  }

  function setName(n) { name = n; draw(); }

  // URL に絞り込みを残す(共有・ブックマーク用)。何も選んでいなければ書かない
  function sync() {
    var p = new URLSearchParams();
    if (name) p.set('m', name);
    var ms = months();
    if (ym && ym !== ms[0]) p.set('ym', ym);
    var q = p.toString();
    history.replaceState(null, '', location.pathname + (q ? '?' + q : ''));
  }

  $('kb-ym').addEventListener('change', function (e) { ym = e.target.value; draw(); });
  $('kb-prev').addEventListener('click', function () { var ms = months(); ym = ms[ms.indexOf(ym) + 1] || ym; draw(); });
  $('kb-next').addEventListener('click', function () { var ms = months(); ym = ms[ms.indexOf(ym) - 1] || ym; draw(); });

  fetch('/data/kenshusei-blog.json').then(function (r) { return r.json(); }).then(function (d) {
    entries = d.entries || [];
    var p = new URLSearchParams(location.search);
    name = p.get('m') || '';
    if (name && !entries.some(function (e) { return e.name === name; })) name = '';
    ym = p.get('ym') || '';
    if (d.updated) $('kb-updated').textContent = '更新: ' + d.updated + '（毎日）';
    draw();
  }).catch(function () {
    $('kb-status').textContent = '読み込めませんでした。時間をおいて開き直してください';
  });
})();
</script>
</body>
</html>
