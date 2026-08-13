<?php
  $title = "ハロープロジェクトメンバー出演映画";
  $description = "ハロープロジェクトのメンバーが出演している映画作品を一覧でまとめました。公開日（上映日）や監督、ジャンルといった基本情報はもちろん、各映画情報サイトへのリンクやYouTubeで見られる予告編動画も網羅しています。";
  $jsonld_type = "Dataset";
  $about = [
    ["name" => "モーニング娘。", "type" => "MusicGroup"],
    ["name" => "Berryz工房", "type" => "MusicGroup"],
    ["name" => "℃-ute", "type" => "MusicGroup"],
    ["name" => "アンジュルム", "type" => "MusicGroup"],
    ["name" => "Juice=Juice", "type" => "MusicGroup"],
    ["name" => "カントリー・ガールズ", "type" => "MusicGroup"],
    ["name" => "こぶしファクトリー", "type" => "MusicGroup"],
    ["name" => "つばきファクトリー", "type" => "MusicGroup"],
    ["name" => "BEYOOOOONDS", "type" => "MusicGroup"],
    ["name" => "OCHA NORMA", "type" => "MusicGroup"],
    ["name" => "ロージークロニクル", "type" => "MusicGroup"],
  ];
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロープロジェクトメンバー出演映画</h1>
  ハロープロジェクトのメンバーが出演している映画作品を一覧でまとめました。<br>
  メンバーのWikipeidaを参考にしています。公開日・監督・ジャンルのほか、各映画情報サイトへのリンクや予告編へのリンクをまとめています。<br>
  「詳細」ボタンを押すと役名・クレジット・出演時の所属が展開されます。予告編はサムネイルを押すとその場で再生されます。<br>
  タイトル・メンバー名・監督名での検索、ジャンル・グループでの絞り込み、公開日・タイトルでの並び替え、主演映画フィルターがあります。<br>

<div id="movies-controls">
  <input type="text" id="mv-search" placeholder="タイトル・メンバー・監督で絞り込み…" oninput="mvFilter()">
  <select id="mv-genre" onchange="mvFilter()">
    <option value="">すべてのジャンル</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_genres.php'; ?>
  </select>
  <select id="mv-group" onchange="mvFilter()">
    <option value="">すべてのグループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_groups.php'; ?>
  </select>
  <button type="button" id="mv-lead" onclick="mvLead()">主演のみ</button>
  <select id="mv-sort" onchange="mvFilter()">
    <option value="date-desc">公開日が新しい順</option>
    <option value="date-asc">公開日が古い順</option>
    <option value="title">タイトル順</option>
  </select>
  <span id="movies-count"></span>
</div>
<div id="movies-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_cards.php'; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/movies_generated.php'; ?>

</main>
<script>
(function() {
  var container = document.getElementById('movies-cards');
  var allCards = Array.from(container.querySelectorAll('.movie-card')).map(function(el) {
    return {
      el: el,
      date: el.getAttribute('data-date') || '',
      title: el.getAttribute('data-title') || '',
      genre: el.getAttribute('data-genre') || '',
      groups: (el.getAttribute('data-groups') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
      leadSearch: (el.getAttribute('data-lead-search') || '').toLowerCase(),
      hasLead: el.getAttribute('data-has-lead') === '1',
    };
  });
  var leadOnly = false;

  function mvFilter() {
    var q = document.getElementById('mv-search').value.toLowerCase();
    var gFilter = document.getElementById('mv-genre').value;
    var grFilter = document.getElementById('mv-group').value;
    var sort = document.getElementById('mv-sort').value;

    var filtered = allCards.filter(function(c) {
      // 主演のみON時は主演者名(+タイトル・監督)だけを検索対象にする
      var matchQ = !q || (leadOnly ? c.leadSearch : c.search).indexOf(q) !== -1;
      var matchG = !gFilter || c.genre === gFilter;
      var matchGr = !grFilter || c.groups.indexOf(grFilter) !== -1;
      var matchLead = !leadOnly || c.hasLead;
      return matchQ && matchG && matchGr && matchLead;
    });

    filtered.sort(function(a, b) {
      if (sort === 'title') return a.title.localeCompare(b.title, 'ja');
      if (sort === 'date-asc') return a.date.localeCompare(b.date);
      return b.date.localeCompare(a.date);
    });

    allCards.forEach(function(c) { c.el.style.display = 'none'; });
    filtered.forEach(function(c) {
      c.el.style.display = '';
      container.appendChild(c.el);
    });

    document.getElementById('movies-count').textContent = filtered.length + ' 作品 / 全' + allCards.length + ' 作品';
  }

  // 詳細(役名など)の開閉
  function mvToggle(id) {
    var el = document.getElementById('mv-cast-' + id);
    if (el) el.style.display = (el.style.display === 'none') ? '' : 'none';
  }

  // 主演のみトグル
  function mvLead() {
    leadOnly = !leadOnly;
    document.getElementById('mv-lead').classList.toggle('active', leadOnly);
    mvFilter();
  }

  // メンバー名クリックでそのメンバーで絞り込み
  function mvByName(name) {
    document.getElementById('mv-search').value = name;
    mvFilter();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // 「…他n人」で出演者全員を表示
  function mvMore(id) {
    var span = document.getElementById('mv-more-' + id);
    var btn = document.getElementById('mv-more-btn-' + id);
    if (span) span.style.display = '';
    if (btn) btn.style.display = 'none';
  }

  // 予告編: 押した時だけYouTube iframeを読み込む
  function mvPlay(id) {
    var btn = document.getElementById('mv-trailer-' + id);
    if (!btn) return;
    var yt = btn.getAttribute('data-yt');
    var wrap = document.createElement('div');
    wrap.className = 'mv-player';
    wrap.innerHTML = '<iframe src="https://www.youtube-nocookie.com/embed/' + yt +
      '?autoplay=1" title="予告編" frameborder="0" ' +
      'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ' +
      'allowfullscreen></iframe>';
    btn.replaceWith(wrap);
  }

  mvFilter();

  window.mvFilter = mvFilter;
  window.mvLead = mvLead;
  window.mvToggle = mvToggle;
  window.mvByName = mvByName;
  window.mvMore = mvMore;
  window.mvPlay = mvPlay;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
