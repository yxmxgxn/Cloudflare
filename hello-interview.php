<?php
  $title = "宮本佳林さん インタビュー記事まとめ";
  $description = "宮本佳林さんが登場するWebインタビュー記事を一覧でまとめました。掲載日・媒体・連載名（特集）ごとに整理し、各記事へのリンクとWeb Archive（魚拓）も掲載しています。現在は宮本佳林さん関連が中心です。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>宮本佳林さん インタビュー記事まとめ</h1>
  宮本佳林さんが中心でWebインタビュー記事を一覧でまとめました。<br>
  各カードは、掲載日とタイトル（押すと記事本文へ）、媒体・連載名、登場メンバーで構成されています。右下の「Web Archive」からアーカイブを開けます。<br>
  グループ・メンバー名での絞り込み、掲載日・タイトルでの並び替え、フリーワード検索ができます。現在は宮本佳林さん関連が中心です。<br>

<div id="interview-controls">
  <input type="text" id="iv-search" placeholder="タイトル・媒体・メンバーで絞り込み…" oninput="ivFilter()">
  <select id="iv-group" onchange="ivFilter()">
    <option value="">すべてのグループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/interview_groups.php'; ?>
  </select>
  <select id="iv-name" onchange="ivFilter()">
    <option value="">すべてのメンバー</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/interview_names.php'; ?>
  </select>
  <select id="iv-sort" onchange="ivFilter()">
    <option value="date-desc">掲載日が新しい順</option>
    <option value="date-asc">掲載日が古い順</option>
    <option value="title">タイトル順</option>
  </select>
  <span id="interview-count"></span>
</div>
<div id="interview-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/interview_cards.php'; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/interview_generated.php'; ?>

</main>
<script>
(function() {
  var container = document.getElementById('interview-cards');
  var allCards = Array.from(container.querySelectorAll('.iv-card')).map(function(el) {
    return {
      el: el,
      date: el.getAttribute('data-date') || '',
      title: el.getAttribute('data-title') || '',
      groups: (el.getAttribute('data-groups') || '').split('|'),
      names: (el.getAttribute('data-names') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });

  function ivFilter() {
    var q = document.getElementById('iv-search').value.toLowerCase();
    var grFilter = document.getElementById('iv-group').value;
    var nmFilter = document.getElementById('iv-name').value;
    var sort = document.getElementById('iv-sort').value;

    var filtered = allCards.filter(function(c) {
      var matchQ = !q || c.search.indexOf(q) !== -1;
      var matchGr = !grFilter || c.groups.indexOf(grFilter) !== -1;
      var matchNm = !nmFilter || c.names.indexOf(nmFilter) !== -1;
      return matchQ && matchGr && matchNm;
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

    document.getElementById('interview-count').textContent =
      filtered.length + ' 件 / 全' + allCards.length + ' 件';
  }

  ivFilter();
  window.ivFilter = ivFilter;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
