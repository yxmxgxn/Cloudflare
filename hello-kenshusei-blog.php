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
  <form id="kb-google" class="kb-google" action="https://www.google.com/search" target="_blank" role="search">
    <input type="search" id="kb-q" list="kb-names" placeholder="ブログの本文を Google で探す" aria-label="Google で探す語" autocomplete="off">
    <input type="hidden" name="q" id="kb-gq">
    <button type="submit">Google で探す</button>
    <datalist id="kb-names"></datalist>
  </form>

  <section class="kb-members" aria-label="メンバーで絞る">
    <div id="kb-members"></div>
  </section>
  <div id="kb-profile" class="kb-profile" hidden></div>

  <!-- 月で絞る。年は打てる(空なら一番新しい年)。書いていない月は押せない。押した月をもう一度押すと外れる -->
  <nav class="kb-month" aria-label="月で絞る">
    <label class="kb-year">年 <input type="number" id="kb-year" inputmode="numeric" aria-label="年"></label>
    <div id="kb-months" class="kb-months"></div>
    <p id="kb-ynote" class="kb-ynote" aria-live="polite"></p>
  </nav>

  <p id="kb-status" class="kb-status" aria-live="polite">読み込み中…</p>
  <ol id="kb-list" class="kb-list"></ol>
  <button type="button" id="kb-more" class="kb-more" hidden>もっと見る</button>
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
  var entries = [], members = [], byName = {}, name = '', ym = '', year = '', shown = 0;
  var PAGE = 50;   // 月を選んでいないときに一度に出す件数

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

  // 名簿(data/kenshusei-members.json)の順。いまの研修生は公式サイトの並び、そのあと研修を終えた人(終えた順)。
  // 名簿にない名前は、いまの研修生の最後に仮で足す
  function roster() {
    var count = {}, seen = {};
    entries.forEach(function (e) { count[e.name] = (count[e.name] || 0) + 1; });
    var list = members.filter(function (m) { seen[m.name] = 1; return count[m.name]; });
    Object.keys(count).forEach(function (n) { if (!seen[n]) list.push({ slug: '', name: n, kana: '', active: true }); });
    return { list: list, count: count };
  }

  function drawMembers() {
    var box = $('kb-members');
    box.textContent = '';
    var r = roster();
    [['いまの研修生', true], ['研修終了', false]].forEach(function (g, gi) {
      var group = r.list.filter(function (m) { return !!m.active === g[1]; });
      if (!group.length) return;
      box.appendChild(el('h2', { class: 'kb-group' }, g[0]));
      var chips = el('div', { class: 'kb-chips' });
      if (gi === 0) {
        var all = el('button', { type: 'button', class: 'kb-chip' + (name ? '' : ' is-on') }, 'すべて');
        all.addEventListener('click', function () { setName(''); });
        chips.appendChild(all);
      }
      group.forEach(function (m) {
        // 検索エンジンがたどれるよう、slug のある人はリンクにする(押したときはページを読み直さない)
        var b = el(m.slug ? 'a' : 'button', m.slug ? { href: '?m=' + m.slug, class: 'kb-chip' + (name === m.name ? ' is-on' : '') }
          : { type: 'button', class: 'kb-chip' + (name === m.name ? ' is-on' : '') }, m.name);
        b.appendChild(el('span', { class: 'kb-n' }, String(r.count[m.name])));
        b.addEventListener('click', function (ev) { ev.preventDefault(); setName(name === m.name ? '' : m.name); });
        chips.appendChild(b);
      });
      box.appendChild(chips);
    });
  }

  // 見出しの説明文とタブのタイトルを、選んだ人に合わせる(?m= で開いたときに Worker が書くのと同じ形)
  var LEAD = 'ハロプロ研修生のブログを、メンバーと月で探せます。記事は upfc.jp で読めます。';
  var TITLE = 'ハロプロ研修生ブログ 目次';
  function drawLead() {
    var lead = document.querySelector('.kb-lead');
    if (!name) { lead.textContent = LEAD; document.title = TITLE; return; }
    var m = byName[name] || {}, mine = picked(), who = m.kana ? name + '（' + m.kana + '）' : name;
    lead.textContent = who + 'のブログ ' + mine.length + '件（' + label(mine[mine.length - 1].date) + '〜' + label(mine[0].date) + '）。記事は upfc.jp で読めます。';
    document.title = who + 'のブログ一覧｜' + TITLE;
  }

  // 選んだ人の名前・よみ・公式プロフィールへのリンク(趣味などは公式で)
  function drawProfile() {
    var box = $('kb-profile'), m = byName[name];
    box.textContent = '';
    box.hidden = !name;
    if (!name) return;
    box.appendChild(el('strong', { class: 'kb-pname' }, name));
    if (m && m.kana) box.appendChild(el('span', { class: 'kb-kana' }, m.kana));
    if (m && m.url) {
      box.appendChild(el('a', { href: m.url, target: '_blank' }, '公式プロフィール'));
    }
  }

  function row(e) {
    var li = el('li');
    var a = el('a', { href: DETAIL + encodeURIComponent(e.id), target: '_blank' });
    a.appendChild(el('time', { datetime: e.date }, e.date.replace(/-/g, '.')));
    a.appendChild(el('span', { class: 'kb-name' }, e.name));
    li.appendChild(a);
    return li;
  }

  // 年の欄と月のボタン。年が空なら、選んでいる人が書いた一番新しい年
  function drawMonths() {
    var ms = months(), box = $('kb-months'), note = $('kb-ynote'), yin = $('kb-year');
    var last = ms.length ? ms[0].slice(0, 4) : '', first = ms.length ? ms[ms.length - 1] : '';
    yin.placeholder = last;
    yin.min = first.slice(0, 4);
    yin.max = last;
    var y = year || last;
    note.textContent = '';
    if (year && first && (year < first.slice(0, 4) || year > last)) {
      note.textContent = year < first.slice(0, 4)
        ? (name ? name + 'の' : '') + '最初のブログは' + label(first) + 'です。' + first.slice(0, 4) + '年から' + last + '年の間で入れてください'
        : last + '年より後のブログはまだありません';
    }
    box.textContent = '';
    for (var i = 1; i <= 12; i++) {
      var m = y + '-' + (i < 10 ? '0' : '') + i;
      var b = el('button', { type: 'button', class: 'kb-mbtn' + (m === ym ? ' is-on' : '') }, i + '月');
      if (ms.indexOf(m) < 0) b.disabled = true;   // その月は書いていない
      (function (m) { b.addEventListener('click', function () { ym = ym === m ? '' : m; draw(); }); })(m);
      box.appendChild(b);
    }
  }

  function draw() {
    if (ym && months().indexOf(ym) < 0) ym = '';   // 人を変えて、その月に書いていなければ外す
    if (ym) year = ym.slice(0, 4);
    var list = $('kb-list');
    list.textContent = '';
    var all = picked();
    var rows = ym ? all.filter(function (e) { return e.date.slice(0, 7) === ym; }) : all;
    shown = ym ? rows.length : Math.min(PAGE, rows.length);
    rows.slice(0, shown).forEach(function (e) { list.appendChild(row(e)); });
    $('kb-more').hidden = shown >= rows.length;
    $('kb-more').onclick = function () {
      rows.slice(shown, shown + PAGE).forEach(function (e) { list.appendChild(row(e)); });
      shown += PAGE;
      $('kb-more').hidden = shown >= rows.length;
    };
    $('kb-status').textContent = (name ? name + ' ／ ' : '') + (ym ? label(ym) + ' ／ ' : '') + rows.length + '件';
    drawMonths();
    drawMembers();
    drawProfile();
    drawLead();
    sync();
  }

  function setName(n) { name = n; year = ''; $('kb-year').value = ''; draw(); }

  // URL に絞り込みを残す(共有・ブックマーク用)。何も選んでいなければ書かない
  function sync() {
    var p = new URLSearchParams();
    if (name) { var m = byName[name]; if (m && m.slug) p.set('m', m.slug); }
    if (ym) p.set('ym', ym);
    var q = p.toString();
    history.replaceState(null, '', location.pathname + (q ? '?' + q : ''));
  }

  // 4桁そろったら月のボタンを描き直す(年を変えても、選んだ月はそのまま)
  $('kb-year').addEventListener('input', function (e) {
    var v = e.target.value.trim();
    if (v === '' || /^\d{4}$/.test(v)) { year = v; drawMonths(); }
  });

  // 打った語に site: を足して Google を別タブで開く(結果は Google のもの)。inurl: だと当たらないので
  // 記事のあるディレクトリまでを site: に書く。メンバーを選んでいたら名前も足す
  $('kb-google').addEventListener('submit', function (ev) {
    var q = $('kb-q').value.trim();
    if (!q) { ev.preventDefault(); return; }
    $('kb-gq').value = 'site:www.upfc.jp/helloproject/artist/ ' + (name ? name + ' ' : '') + q;
  });

  var get = function (u) { return fetch(u).then(function (r) { return r.json(); }); };
  Promise.all([get('/data/kenshusei-blog.json'), get('/data/kenshusei-members.json').catch(function () { return {}; })]).then(function (res) {
    var d = res[0];
    entries = d.entries || [];
    members = res[1].members || [];
    members.forEach(function (m) { byName[m.name] = m; });
    // 予測変換の候補: 研修生(名簿) → ハロプロの現役メンバー・グループ(d.suggest)
    var seen = {};
    members.map(function (m) { return m.name; }).concat(d.suggest || []).forEach(function (n) {
      if (seen[n]) return;
      seen[n] = 1;
      $('kb-names').appendChild(el('option', { value: n }));
    });
    var p = new URLSearchParams(location.search), slug = p.get('m') || '';
    var hit = slug && members.filter(function (m) { return m.slug === slug; })[0];
    name = hit ? hit.name : '';
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
