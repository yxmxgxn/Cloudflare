<?php
  $title = "#佳林の１日１ハロまとめ"; 
  $description = "宮本佳林さんがハロープロジェクトサブスク解禁にともない毎日ハロプロの曲を1曲紹介する企画をまとめました。日付に元ポスト・アルバム名に公式のリンク・YouTube Music／Spotify／Amazon MusicのURLや作詞作曲編曲などをいれています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  // グループ単体
  // $about = ["name" => "モーニング娘。", "type" => "MusicGroup"];
  // 混在(人もグループも)
  // $about = [
  //   "宮本佳林",                                      // 文字列はPerson扱い
  //   ["name" => "Juice=Juice", "type" => "MusicGroup"],
  //   ["name" => "譜久村聖"],                          // typeなし → Person
  // ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>#佳林の１日１ハロまとめ</h1>
  宮本佳林さんがXでハロープロジェクトサブスク解禁にともない毎日ハロプロの曲を<a href="https://x.com/search?q=%23%E4%BD%B3%E6%9E%97%E3%81%AE1%E6%97%A51%E3%83%8F%E3%83%AD%20from%3A%40karin__miyamoto&src=typed_query&f=live" target="_blank" rel="noopener noreferrer">#佳林の１日１ハロ</a>で1曲紹介する企画をまとめました。<br>
  日付に元ポスト・アルバム名に公式のリンク・YouTube Music／Spotify／Amazon MusicのURLや作詞作曲編曲などをいれています。<br>
  アーティスト名・作詞作曲編曲でフィルタをかけられます。<br>
  プレイリストを作りました：<a href="https://music.youtube.com/playlist?list=PLAbxdMpUP3BU" target="_blank" rel="noopener noreferrer">YouTube Music</a>・<a href="https://open.spotify.com/playlist/5YVrEQ0hb7LTOviTXum8zi" target="_blank" rel="noopener noreferrer">Spotify</a>・<a href="https://music.amazon.co.jp/user-playlists/46349edb307f4bda8ab48d0f060a0c69jajp" target="_blank" rel="noopener noreferrer">Amazon Music</a><br>

<div id="kh-post-search">
  <input type="search" id="kh-post-q" placeholder="ポストの本文を検索…" aria-label="ポストの本文を検索">
  <span id="kh-post-count"></span>
</div>
<div id="kh-post-results"></div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karin-hello_posts.php'; ?>

<div id="karinhello-controls">
  <input type="text" id="kh-search" placeholder="曲名・アーティスト・作詞作曲編曲で絞り込み…" oninput="khFilter()">
  <select id="kh-artist" onchange="khFilter()">
    <option value="">すべてのアーティスト</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karin-hello_artists.php'; ?>
  </select>
  <span id="karinhello-count"></span>
</div>
<div id="karinhello-table">
  <table id="karinhello">
    <thead>
      <tr>
        <th onclick="khSort('date')" id="kh-th-date">ポスト日</th>
        <th onclick="khSort('song')" id="kh-th-song">曲名</th>
        <th onclick="khSort('artist')" id="kh-th-artist">アーティスト</th>
        <th>アルバム</th>
        <th>YT</th>
        <th>Spotify</th>
        <th>Amazon Music</th>
        <th>作詞</th>
        <th>作曲</th>
        <th>編曲</th>
      </tr>
    </thead>
    <tbody id="kh-tbody">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karin-hello_table.php'; ?>
    </tbody>
  </table>
</div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/karin-hello_generated.php'; ?>
<script>
(function() {
  var sortKey = 'date';
  var sortAsc = false;

  // DOMからtrを読み取ってallRowsを構築
  var tbody = document.getElementById('kh-tbody');
  var allRows = Array.from(tbody.querySelectorAll('tr')).map(function(tr) {
    return {
      el: tr,
      date:     tr.cells[0] ? tr.cells[0].textContent.trim() : '',
      song:     tr.cells[1] ? tr.cells[1].textContent.trim() : '',
      artist:   tr.getAttribute('data-artist') || '',
      lyricist: tr.cells[7] ? tr.cells[7].textContent.trim() : '',
      composer: tr.cells[8] ? tr.cells[8].textContent.trim() : '',
      arranger: tr.cells[9] ? tr.cells[9].textContent.trim() : '',
    };
  });

  function khFilter() {
    var q = document.getElementById('kh-search').value.toLowerCase();
    var aFilter = document.getElementById('kh-artist').value;

    var filtered = allRows.filter(function(r) {
      var matchQ = !q
        || r.song.toLowerCase().indexOf(q) !== -1
        || r.artist.toLowerCase().indexOf(q) !== -1
        || r.lyricist.toLowerCase().indexOf(q) !== -1
        || r.composer.toLowerCase().indexOf(q) !== -1
        || r.arranger.toLowerCase().indexOf(q) !== -1;
      var matchA = !aFilter || r.artist === aFilter;
      return matchQ && matchA;
    });

    // ソート
    filtered.sort(function(a, b) {
      var av = sortKey === 'date' ? a.date : sortKey === 'song' ? a.song : a.artist;
      var bv = sortKey === 'date' ? b.date : sortKey === 'song' ? b.song : b.artist;
      return sortAsc ? av.localeCompare(bv, 'ja') : bv.localeCompare(av, 'ja');
    });

    // 全trを非表示にしてからフィルタ結果を順に追加
    allRows.forEach(function(r) { r.el.style.display = 'none'; });
    filtered.forEach(function(r) {
      r.el.style.display = '';
      tbody.appendChild(r.el);
    });

    document.getElementById('karinhello-count').textContent = filtered.length + ' 件 / 全' + allRows.length + ' 件';
  }

  function khSort(key) {
    if (sortKey === key) sortAsc = !sortAsc;
    else { sortKey = key; sortAsc = true; }
    document.querySelectorAll('#karinhello th').forEach(function(th) { th.classList.remove('sorted'); });
    var map = { date: 'kh-th-date', song: 'kh-th-song', artist: 'kh-th-artist' };
    if (map[key]) document.getElementById(map[key]).classList.add('sorted');
    khFilter();
  }

  // 初期表示（全件・日付降順）
  khFilter();

  window.khFilter = khFilter;
  window.khSort = khSort;
})();

(function() {
  var posts = JSON.parse(document.getElementById('kh-posts').textContent);
  var input = document.getElementById('kh-post-q');
  var results = document.getElementById('kh-post-results');
  var count = document.getElementById('kh-post-count');

  function highlight(parent, text, q) {
    var lower = text.toLowerCase();
    var pos = 0, i;
    while ((i = lower.indexOf(q, pos)) !== -1) {
      parent.appendChild(document.createTextNode(text.slice(pos, i)));
      var mark = document.createElement('mark');
      mark.textContent = text.slice(i, i + q.length);
      parent.appendChild(mark);
      pos = i + q.length;
    }
    parent.appendChild(document.createTextNode(text.slice(pos)));
  }

  function search() {
    var q = input.value.trim().toLowerCase();
    results.textContent = '';
    if (!q) { count.textContent = ''; return; }

    var hits = posts.filter(function(p) { return p.text.toLowerCase().indexOf(q) !== -1; });
    count.textContent = hits.length + ' 件';

    hits.forEach(function(p) {
      var card = document.createElement('div');
      card.className = 'kh-post';

      var meta = document.createElement('div');
      meta.className = 'kh-post-meta';
      var date = document.createElement(p.url ? 'a' : 'span');
      date.textContent = p.date;
      if (p.url) { date.href = p.url; date.target = '_blank'; date.rel = 'noopener noreferrer'; }
      meta.appendChild(date);
      meta.appendChild(document.createTextNode('　' + p.song + ' / ' + p.artist));

      var body = document.createElement('p');
      body.className = 'kh-post-text';
      highlight(body, p.text, q);

      card.appendChild(meta);
      card.appendChild(body);
      results.appendChild(card);
    });
  }

  input.addEventListener('input', search);
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
