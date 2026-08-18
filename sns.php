<?php
  $title = "ハロプロ SNS一覧";
  $description = "ハロー！プロジェクトの現役メンバー・OGの公式SNS（X・Instagram・YouTube・TikTok・Threads・アメブロほか）を一覧でまとめました。グループ・在籍状況・SNSごとに絞り込めます。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ SNS一覧</h1>
  ハロー！プロジェクトの現役メンバー・OGの公式SNSアカウントを一覧でまとめました。<br>
  各メンバーのカードに、そのメンバーが持つSNSへのリンクを横並びで載せています。<br>
  グループ・在籍状況（現役／OG）・SNSの種類での絞り込み、名前やIDでのフリーワード検索ができます。<br>

<div id="sns-controls">
  <input type="text" id="sns-search" placeholder="名前・SNSのIDで絞り込み…" oninput="snsFilter()">
  <fieldset id="sns-affil">
    <label><input type="checkbox" class="sns-affil-box" value="hello" checked onchange="snsFilter()"> ハロプロ現役</label>
    <label><input type="checkbox" class="sns-affil-box" value="m-line" checked onchange="snsFilter()"> M-line</label>
    <label><input type="checkbox" class="sns-affil-box" value="jproom" checked onchange="snsFilter()"> J.P ROOM</label>
    <label><input type="checkbox" class="sns-affil-box" value="upfrontcreate" checked onchange="snsFilter()"> UP-FRONT CREATE</label>
    <label><input type="checkbox" class="sns-affil-box" value="official" checked onchange="snsFilter()"> 公式サイト</label>
    <label id="sns-showall-label"><input type="checkbox" id="sns-showall" onchange="snsFilter()"> 完全OGも含めて全部表示</label>
  </fieldset>
  <select id="sns-group" onchange="snsFilter()">
    <option value="">グループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/sns_groups.php'; ?>
  </select>
  <select id="sns-platform" onchange="snsFilter()">
    <option value="">SNS</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/sns_platforms.php'; ?>
  </select>
  <span id="sns-count"></span>
</div>
<div id="sns-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/sns_cards.php'; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/sns_generated.php'; ?>

</main>

<style>
  #sns-controls {
    display: flex;
    flex-wrap: wrap;
    gap: .5em;
    align-items: center;
    margin: 1em 0;
  }
  #sns-controls input,
  #sns-controls select {
    padding: .4em .6em;
    font-size: 1em;
    border: 1px solid #ccc;
    border-radius: 6px;
  }
  #sns-controls input { flex: 1 1 16em; }
  #sns-controls select.is-active {
    font-weight: bold;
    border-color: #AE8DBC;
  }
  #sns-count { color: #666; font-size: .9em; }

  #sns-affil {
    display: flex;
    flex-wrap: wrap;
    gap: .3em .9em;
    align-items: center;
    border: 1px solid #e2dbe7;
    border-radius: 6px;
    padding: .45em .7em;
    margin: 0;
  }
  #sns-affil label {
    font-size: .9em;
    white-space: nowrap;
    cursor: pointer;
  }
  #sns-affil input:disabled + * ,
  #sns-affil label:has(input:disabled) { color: #aaa; }
  #sns-showall-label {
    border-left: 1px solid #e2dbe7;
    padding-left: .9em;
    font-weight: bold;
  }

  #sns-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 20em), 1fr));
    gap: .8em;
  }
  .sns-card {
    border: 1px solid #e2dbe7;
    border-radius: 8px;
    padding: .7em .8em;
    background: #fff;
  }
  .sns-name {
    margin: 0 0 .5em;
    font-weight: bold;
  }
  .sns-badge {
    display: inline-block;
    margin-left: .4em;
    padding: .05em .5em;
    font-size: .72em;
    font-weight: normal;
    color: #fff;
    background: #AE8DBC;
    border-radius: 999px;
    vertical-align: middle;
  }
  .sns-badge-og { background: #b0a8a8; }

  .sns-links {
    display: flex;
    flex-wrap: wrap;
    gap: .35em;
  }
  .sns-link {
    display: inline-block;
    padding: .2em .6em;
    font-size: .82em;
    line-height: 1.5;
    text-decoration: none;
    color: #5a4a63;
    background: #f4f0f7;
    border: 1px solid #e2dbe7;
    border-radius: 999px;
  }
  .sns-link:hover {
    background: #AE8DBC;
    border-color: #AE8DBC;
    color: #fff;
  }
</style>

<script>
(function() {
  var container = document.getElementById('sns-cards');
  var allCards = Array.from(container.querySelectorAll('.sns-card')).map(function(el) {
    return {
      el: el,
      group: el.getAttribute('data-group') || '',
      affil: (el.getAttribute('data-affil') || '').split('|').filter(Boolean),
      platforms: (el.getAttribute('data-platforms') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });
  var affilBoxes = Array.from(document.querySelectorAll('.sns-affil-box'));
  var showAll = document.getElementById('sns-showall');

  function snsFilter() {
    var q = document.getElementById('sns-search').value.toLowerCase();
    var grFilter = document.getElementById('sns-group').value;
    var plFilter = document.getElementById('sns-platform').value;
    var checked = affilBoxes.filter(function(b) { return b.checked; })
                            .map(function(b) { return b.value; });
    // 「全部表示」中は所属チェックは無効(グレーアウト)
    affilBoxes.forEach(function(b) { b.disabled = showAll.checked; });

    var shown = 0;
    allCards.forEach(function(c) {
      var affilMatch = showAll.checked ||
        c.affil.some(function(a) { return checked.indexOf(a) !== -1; });
      var match = affilMatch &&
        (!q || c.search.indexOf(q) !== -1) &&
        (!grFilter || c.group === grFilter) &&
        (!plFilter || c.platforms.indexOf(plFilter) !== -1);
      c.el.style.display = match ? '' : 'none';
      if (match) shown++;
    });

    document.getElementById('sns-count').textContent =
      shown + ' 人 / 全' + allCards.length + ' 人';

    Array.prototype.forEach.call(
      document.querySelectorAll('#sns-controls select'),
      function(s) { s.classList.toggle('is-active', s.selectedIndex > 0); }
    );
  }

  snsFilter();
  window.snsFilter = snsFilter;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
