<?php
  $title = "ハロー！プロジェクトメンバー出演映画";
  $description = "ハロー！プロジェクトのメンバーが出演している映画作品を一覧でまとめました。公開日（上映日）や監督、ジャンルといった基本情報はもちろん、各映画情報サイトへのリンクやYouTubeで見られる予告編動画も網羅しています。";
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
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロー！プロジェクトメンバー出演映画</h1>
  ハロー！プロジェクトのメンバーが出演している映画作品を一覧でまとめました。<br>
  公開日・監督・ジャンルのほか、各映画情報サイトへのリンクや予告編へのリンクをまとめています。<br>
  「出演」ボタンを押すと出演メンバー・役名が展開されます。タイトル・メンバー名・監督名での検索、ジャンル・グループでの絞り込みができます。<br>

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
  <span id="movies-count"></span>
</div>
<div id="movies-table">
  <div style="overflow-x: auto;">
    <table id="hp-movies">
      <thead>
        <tr>
          <th onclick="mvSort('date')" id="mv-th-date">公開日</th>
          <th onclick="mvSort('title')" id="mv-th-title">タイトル</th>
          <th>ジャンル</th>
          <th>監督</th>
          <th>出演</th>
          <th>リンク</th>
        </tr>
      </thead>
      <tbody id="mv-tbody">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_table.php'; ?>
      </tbody>
    </table>
  </div>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/movies_generated.php'; ?>
</div>

</main>
<script>
(function() {
  var sortKey = 'date';
  var sortAsc = false;

  var tbody = document.getElementById('mv-tbody');
  // 映画行とキャスト展開行をペアで保持
  var allRows = Array.from(tbody.querySelectorAll('tr.mv-row')).map(function(tr) {
    return {
      el: tr,
      castEl: document.getElementById('mv-cast-' + tr.getAttribute('data-id')),
      date: tr.getAttribute('data-date') || '',
      title: tr.querySelector('.mv-title') ? tr.querySelector('.mv-title').textContent.trim() : '',
      genre: tr.getAttribute('data-genre') || '',
      groups: (tr.getAttribute('data-groups') || '').split('|'),
      search: (tr.getAttribute('data-search') || '').toLowerCase(),
    };
  });

  function mvFilter() {
    var q = document.getElementById('mv-search').value.toLowerCase();
    var gFilter = document.getElementById('mv-genre').value;
    var grFilter = document.getElementById('mv-group').value;

    var filtered = allRows.filter(function(r) {
      var matchQ = !q || r.search.indexOf(q) !== -1;
      var matchG = !gFilter || r.genre === gFilter;
      var matchGr = !grFilter || r.groups.indexOf(grFilter) !== -1;
      return matchQ && matchG && matchGr;
    });

    filtered.sort(function(a, b) {
      var av = sortKey === 'date' ? a.date : a.title;
      var bv = sortKey === 'date' ? b.date : b.title;
      return sortAsc ? av.localeCompare(bv, 'ja') : bv.localeCompare(av, 'ja');
    });

    allRows.forEach(function(r) {
      r.el.style.display = 'none';
      if (r.castEl) r.castEl.style.display = 'none';
    });
    filtered.forEach(function(r) {
      r.el.style.display = '';
      tbody.appendChild(r.el);
      if (r.castEl) tbody.appendChild(r.castEl); // 展開状態はリセット(非表示のまま)
    });

    document.getElementById('movies-count').textContent = filtered.length + ' 作品 / 全' + allRows.length + ' 作品';
  }

  function mvSort(key) {
    if (sortKey === key) sortAsc = !sortAsc;
    else { sortKey = key; sortAsc = (key === 'title'); }
    document.querySelectorAll('#hp-movies th').forEach(function(th) { th.classList.remove('sorted'); });
    var map = { date: 'mv-th-date', title: 'mv-th-title' };
    if (map[key]) document.getElementById(map[key]).classList.add('sorted');
    mvFilter();
  }

  function mvToggle(id) {
    var el = document.getElementById('mv-cast-' + id);
    if (el) el.style.display = (el.style.display === 'none') ? '' : 'none';
  }

  mvFilter();

  window.mvFilter = mvFilter;
  window.mvSort = mvSort;
  window.mvToggle = mvToggle;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
