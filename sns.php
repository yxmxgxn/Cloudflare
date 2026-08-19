<?php
  $title = "ハロプロ SNS一覧";
  $description = "ハロー！プロジェクトの現役メンバー・OGの公式SNS（X・Instagram・YouTube・TikTok・Threads・アメブロほか）を一覧でまとめました。名前・読み仮名での検索、所属・SNSごとの絞り込みができます。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ SNS一覧</h1>
  ハロー！プロジェクトの現役メンバー・OGの公式SNSアカウントを一覧でまとめました。<br>
  各メンバーのカードに、そのメンバーが持つSNSへのリンクを横並びで載せています。<br>
  人数が多いので、スクロールで探すより<strong>上の検索欄に名前（読み仮名でも可）を入れる</strong>のが早いです。SNSのIDでも検索できます。<br>
  所属（現役・アップフロント系）・SNSの種類での絞り込みもできます。<br>
  「アーカイブ ▾」があるカードはクリックすると過去のアメブロが開きます。<br>

<div id="sns-controls">
  <input type="text" id="sns-search" placeholder="名前・読み仮名・SNSのIDで検索…" oninput="snsFilter()">
  <fieldset id="sns-affil">
    <label><input type="checkbox" class="sns-aff-box" value="genin" checked onchange="snsFilter()"> 現役</label>
    <label><input type="checkbox" class="sns-aff-box" value="jproom" checked onchange="snsFilter()"> J.P ROOM</label>
    <label><input type="checkbox" class="sns-aff-box" value="upfrontcreate" checked onchange="snsFilter()"> UP-FRONT CREATE</label>
    <label><input type="checkbox" class="sns-aff-box" value="justpro" checked onchange="snsFilter()"> ジャストプロ</label>
    <label><input type="checkbox" class="sns-aff-box" value="upfrontpromotion" checked onchange="snsFilter()"> UP-FRONT PROMOTION</label>
    <label id="sns-showall-label"><input type="checkbox" id="sns-retired" onchange="snsFilter()"> 引退したメンバーも表示</label>
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
  #sns-controls input[type=text],
  #sns-controls select {
    padding: .4em .6em;
    font-size: 1em;
    border: 1px solid #ccc;
    border-radius: 6px;
  }
  #sns-controls input[type=text] { flex: 1 1 16em; }
  #sns-controls select.is-active { font-weight: bold; border-color: #AE8DBC; }
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
  #sns-affil label { font-size: .9em; white-space: nowrap; cursor: pointer; }
  #sns-showall-label { border-left: 1px solid #e2dbe7; padding-left: .9em; font-weight: bold; }

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
  .sns-card.has-archive { cursor: pointer; }
  .sns-name {
    margin: 0 0 .5em;
    font-weight: bold;
    font-size: 1.05em;
  }
  .sns-badge {
    display: inline-block;
    margin-left: .5em;
    padding: .1em .7em;
    font-size: .8em;
    font-weight: bold;
    color: #fff;
    background: #AE8DBC;
    border-radius: 999px;
    vertical-align: middle;
  }

  .sns-links {
    display: flex;
    flex-wrap: wrap;
    gap: .35em;
    align-items: center;
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
  .sns-link:hover { background: #AE8DBC; border-color: #AE8DBC; color: #fff; }
  .sns-icon-link { padding: .28em; line-height: 0; }
  .sns-ic { width: 1.15em; height: 1.15em; display: block; }

  .sns-arc-toggle {
    font: inherit;
    font-size: .78em;
    color: #8a7d92;
    background: transparent;
    border: 1px dashed #cbbfd3;
    border-radius: 999px;
    padding: .15em .6em;
    cursor: pointer;
  }
  .sns-archive-layer {
    display: none;
    flex-wrap: wrap;
    gap: .35em;
    margin-top: .5em;
    padding-top: .5em;
    border-top: 1px dashed #e2dbe7;
  }
  .sns-card.expanded .sns-archive-layer { display: flex; }
  .sns-card.expanded .sns-arc-toggle { color: #AE8DBC; border-style: solid; }
  .sns-archive { opacity: .5; }
</style>

<script>
(function() {
  var container = document.getElementById('sns-cards');
  var allCards = Array.from(container.querySelectorAll('.sns-card')).map(function(el) {
    return {
      el: el,
      bucket: el.getAttribute('data-bucket') || '',
      retired: el.getAttribute('data-retired') === '1',
      group: el.getAttribute('data-group') || '',
      platforms: (el.getAttribute('data-platforms') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });
  var affBoxes = Array.from(document.querySelectorAll('.sns-aff-box'));
  var retiredBox = document.getElementById('sns-retired');

  function snsFilter() {
    var q = document.getElementById('sns-search').value.toLowerCase();
    var grFilter = document.getElementById('sns-group').value;
    var plFilter = document.getElementById('sns-platform').value;
    var checked = affBoxes.filter(function(b) { return b.checked; })
                          .map(function(b) { return b.value; });
    var showRetired = retiredBox.checked;

    var shown = 0;
    allCards.forEach(function(c) {
      var affOk;
      if (c.retired) affOk = showRetired;
      else if (c.bucket === 'other') affOk = true;       // free/other は非引退なら常時表示
      else affOk = checked.indexOf(c.bucket) !== -1;      // 現役・事務所系はチェックで出し入れ
      var match = affOk &&
        (!grFilter || c.group === grFilter) &&
        (!plFilter || c.platforms.indexOf(plFilter) !== -1) &&
        (!q || c.search.indexOf(q) !== -1);
      c.el.style.display = match ? '' : 'none';
      if (match) shown++;
    });

    document.getElementById('sns-count').textContent = shown + ' 人 / 全' + allCards.length + ' 人';
    Array.prototype.forEach.call(
      document.querySelectorAll('#sns-controls select'),
      function(s) { s.classList.toggle('is-active', s.selectedIndex > 0); }
    );
  }

  // アーカイブのあるカードはクリックで展開/折りたたみ(リンククリックは除外)
  container.addEventListener('click', function(e) {
    if (e.target.closest('a')) return;
    var card = e.target.closest('.sns-card.has-archive');
    if (card) card.classList.toggle('expanded');
  });

  snsFilter();
  window.snsFilter = snsFilter;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
