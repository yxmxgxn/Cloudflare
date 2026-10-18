<?php
  $title = "#佳林の１日１ハロまとめ"; 
  $description = "宮本佳林さんがハロープロジェクトサブスク解禁にともない毎日ハロプロの曲を1曲紹介する企画をまとめました。日付に元ポスト・アルバム名に公式のリンク・YouTube Music／Spotify／Amazon Music／Apple Music／LINE MUSIC／AWAのURLや作詞作曲編曲などをいれています。";
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
  日付に元ポスト・アルバム名に公式のリンク・YouTube Music／Spotify／Amazon Music／Apple Music／LINE MUSIC／AWAのURLや作詞作曲編曲などをいれています。<br>
  アーティスト名・作詞作曲編曲でフィルタをかけられます。<br>
  プレイリストを作りました：<a href="https://music.youtube.com/playlist?list=PLAbxdMpUP3BU" target="_blank" rel="noopener noreferrer" class="kh-playlist" data-embed="https://www.youtube.com/embed/videoseries?list=PLAbxdMpUP3BU">YouTube Music</a>・<a href="https://open.spotify.com/playlist/5YVrEQ0hb7LTOviTXum8zi" target="_blank" rel="noopener noreferrer" class="kh-playlist" data-embed="https://open.spotify.com/embed/playlist/5YVrEQ0hb7LTOviTXum8zi">Spotify</a>・<a href="https://music.amazon.co.jp/user-playlists/46349edb307f4bda8ab48d0f060a0c69jajp" target="_blank" rel="noopener noreferrer" class="kh-playlist" data-embed="https://music.amazon.co.jp/embed/46349edb307f4bda8ab48d0f060a0c69jajp/?id=EpScUMtmV8&amp;marketplaceId=A1VC38T7YXB528&amp;musicTerritory=JP">Amazon Music</a><br>
  <div id="kh-playlist-embed" hidden></div>

<div id="kh-post-search">
  <input type="search" id="kh-post-q" placeholder="ポストの本文を検索…" aria-label="ポストの本文を検索">
  <span id="kh-post-count"></span>
</div>
<div id="kh-post-results"></div>

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
        <th>Apple Music</th>
        <th>LINE MUSIC</th>
        <th>AWA</th>
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
      lyricist: tr.cells[10] ? tr.cells[10].textContent.trim() : '',
      composer: tr.cells[11] ? tr.cells[11].textContent.trim() : '',
      arranger: tr.cells[12] ? tr.cells[12].textContent.trim() : '',
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

// プレイリストのリンクを押したら、その下に埋め込みプレイヤーを出す（同じリンクをもう一度押すと閉じる）
(function() {
  var box = document.getElementById('kh-playlist-embed');
  var current = null;
  document.querySelectorAll('a.kh-playlist').forEach(function(a) {
    a.addEventListener('click', function(e) {
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
      e.preventDefault();
      box.textContent = '';
      if (current === a) { current = null; box.hidden = true; return; }
      current = a;
      var f = document.createElement('iframe');
      f.src = a.getAttribute('data-embed');
      f.title = a.textContent + ' プレイリスト';
      f.allow = 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture';
      f.allowFullscreen = true;
      f.referrerPolicy = 'strict-origin-when-cross-origin';
      box.appendChild(f);
      box.hidden = false;
    });
  });
})();

// ポスト本文の検索。本文はページに持たず、Worker が一致したポストIDだけ返す
(function() {
  var input = document.getElementById('kh-post-q');
  var results = document.getElementById('kh-post-results');
  var count = document.getElementById('kh-post-count');

  var posts = {};
  var order = [];
  document.querySelectorAll('#kh-tbody tr[data-post]').forEach(function(tr) {
    var id = tr.getAttribute('data-post');
    if (!id) return;
    var link = tr.cells[0].querySelector('a');
    posts[id] = {
      date: tr.cells[0].textContent.trim(),
      url: link ? link.href : '',
      song: tr.cells[1].textContent.trim(),
      artist: tr.getAttribute('data-artist') || ''
    };
    order.push(id);
  });

  var timer = null;
  var seq = 0;

  function render(ids) {
    results.textContent = '';
    var hit = {};
    ids.forEach(function(id) { hit[id] = true; });
    var shown = order.filter(function(id) { return hit[id]; });
    count.textContent = shown.length ? shown.length + ' 件' : '見つかりませんでした';

    shown.forEach(function(id) {
      var p = posts[id];
      var card = document.createElement('div');
      card.className = 'kh-post';
      var date = document.createElement(p.url ? 'a' : 'span');
      date.textContent = p.date;
      if (p.url) { date.href = p.url; date.target = '_blank'; date.rel = 'noopener noreferrer'; }
      card.appendChild(date);
      card.appendChild(document.createTextNode('　' + p.song + ' / ' + p.artist));
      results.appendChild(card);
    });
  }

  function search() {
    var q = input.value.trim();
    var mySeq = ++seq;
    if (!q) { results.textContent = ''; count.textContent = ''; return; }
    if (q.length < 2) { results.textContent = ''; count.textContent = '2文字以上で検索'; return; }

    fetch('/api/karinhello-search?q=' + encodeURIComponent(q))
      .then(function(r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function(d) { if (mySeq === seq) render(d.ids || []); })
      .catch(function() {
        if (mySeq !== seq) return;
        results.textContent = '';
        count.textContent = '検索でエラーが起きました';
      });
  }

  input.addEventListener('input', function() {
    clearTimeout(timer);
    timer = setTimeout(search, 250);
  });
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
