<?php
  $title = "ハロー！プロジェクト メンバー インタビュー記事まとめ";
  $description = "ハロー！プロジェクト メンバーのWebインタビュー記事を一覧でまとめました。掲載日・媒体・連載名（特集）ごとに整理し、各記事へのリンクとWeb Archiveも掲載しています。現在は宮本佳林さん関連が中心です。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロー！プロジェクト メンバーのインタビュー記事まとめ</h1>
  <p>ハロー！プロジェクト メンバーのWebインタビュー記事を一覧でまとめました。<br>
  各カードは、掲載日とタイトル（記事リンク）、媒体・連載名、メンバーで構成されています。</p>
  <p>グループ・メンバー名での絞り込み、掲載日・タイトルでの並び替え、タイトル・媒体・メンバーでの検索ができます</p>
  <p>右下のWeb Archiveからアーカイブを開けます。</p>
  <p>2026/08/14 宮本佳林さん・OCHA NORMA・ロージークロニクルを中心に掲載しています。</p>
<div id="interview-controls">
  <input type="text" id="iv-search" placeholder="タイトル・媒体・メンバーで絞り込み…" oninput="ivFilter()">
  <select id="iv-group" onchange="ivGroupChange()">
    <option value="">グループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/interview_groups.php'; ?>
  </select>
  <select id="iv-name" onchange="ivFilter()">
    <option value="">メンバー</option>
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

    // 選択中(先頭の既定以外を選んだ)プルダウンだけ太字にする
    Array.prototype.forEach.call(
      document.querySelectorAll('#interview-controls select'),
      function(s) { s.classList.toggle('is-active', s.selectedIndex > 0); }
    );
  }

  // グループを選ぶと、メンバー選択肢をそのグループの人だけに絞る
  function ivSyncNames() {
    var g = document.getElementById('iv-group').value;
    var nameSel = document.getElementById('iv-name');
    var stillValid = false;
    Array.prototype.forEach.call(nameSel.options, function(o) {
      if (!o.value) return; // 「メンバー」(プレースホルダ)
      var show = !g || (o.getAttribute('data-group') || '') === g;
      o.hidden = !show;
      o.disabled = !show;
      if (o.value === nameSel.value && show) stillValid = true;
    });
    if (!stillValid) nameSel.value = ''; // 隠れた選択は解除
  }

  function ivGroupChange() {
    ivSyncNames();
    ivFilter();
  }

  ivFilter();
  window.ivFilter = ivFilter;
  window.ivGroupChange = ivGroupChange;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
