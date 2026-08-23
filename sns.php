<?php
  $title = "ハロプロ SNS一覧";
  $description = "ハロー！プロジェクトの現役メンバー・OGの公式SNS（X・Instagram・YouTube・TikTok・Threads・アメブロほか）を一覧でまとめました。名前・読み仮名での検索、所属・SNSごとの絞り込みができます。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ SNS一覧</h1>
  <p>ハロー！プロジェクトの現役メンバー・OGの公式SNSアカウントを一覧でまとめました。</p>
  <p>人数が多いので、スクロールで探すより<strong>検索欄に名前（読み仮名でも可）を入れる</strong>のが早いです。</p>
  <p>所属・グループ（現役／OG問わず）・SNSの種類で絞り込めます。</p>
  <p>グループで「モーニング娘。」を選んで「ハロプロ」のチェックを外せば、OGのモーニング娘。だけを出せます。<br>
  <p>「アーカイブ ▾」があるカードはクリックすると過去のアメブロが開きます。</p>f
  <p>掲載順は現役、OGは加入順で、アップフロント系列に所属している人を先にしていますが私の恣意的な順番になってしまっているのでご容赦ください。」</p>
<div id="sns-controls">
  <input type="text" id="sns-search" placeholder="名前・読み仮名・SNSのIDで検索…" oninput="snsFilter()">
  <fieldset id="sns-affil">
    <label><input type="checkbox" class="sns-aff-box" value="genin" checked onchange="snsFilter()"> ハロプロ</label>
    <label><input type="checkbox" class="sns-aff-box" value="jproom" checked onchange="snsFilter()"> J.P ROOM</label>
    <label><input type="checkbox" class="sns-aff-box" value="mline" checked onchange="snsFilter()"> M-line</label>
    <label><input type="checkbox" class="sns-aff-box" value="upfrontcreate" checked onchange="snsFilter()"> UP-FRONT CREATE</label>
    <label><input type="checkbox" class="sns-aff-box" value="upfrontpromotion" checked onchange="snsFilter()"> UP-FRONT PROMOTION</label>
    <label><input type="checkbox" class="sns-aff-box" value="justpro" checked onchange="snsFilter()"> ジャストプロ</label>
    <label><input type="checkbox" class="sns-aff-box" value="other" checked onchange="snsFilter()"> フリー・その他</label>
    <label><input type="checkbox" class="sns-aff-box" value="retirement" onchange="snsFilter()"> 引退済み</label>
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
  #sns-affil label:last-child { border-left: 1px solid #e2dbe7; padding-left: .9em; }

  /* モバイル2列 → 3列 → 最大4列(これ以上は増やさない)。 */
  #sns-cards {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .55em;
  }
  @media (min-width: 600px) { #sns-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
  @media (min-width: 860px) { #sns-cards { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
  .sns-card {
    border: 1px solid #e2dbe7;
    border-radius: 8px;
    padding: .5em .55em;
    background: #fff;
  }
  .sns-card.has-archive { cursor: pointer; }
  .sns-name {
    margin: 0 0 .4em;
    font-weight: bold;
    font-size: .92em;
    line-height: 1.35;
  }
  .sns-badge {
    display: inline-block;
    margin-left: .35em;
    padding: .05em .5em;
    font-size: .72em;
    font-weight: bold;
    color: #fff;
    background: #AE8DBC;
    border-radius: 999px;
    vertical-align: middle;
  }

  .sns-links {
    display: flex;
    flex-wrap: wrap;
    gap: .28em;
    align-items: center;
  }
  .sns-link {
    display: inline-block;
    padding: .16em .5em;
    font-size: .74em;
    line-height: 1.45;
    text-decoration: none;
    color: #5a4a63;
    background: #f4f0f7;
    border: 1px solid #e2dbe7;
    border-radius: 999px;
  }
  .sns-link:hover { background: #AE8DBC; border-color: #AE8DBC; color: #fff; }
  .sns-icon-link { padding: .22em; line-height: 0; }
  .sns-ic { width: 1.05em; height: 1.05em; display: block; }

  /* 事務所・公式・FC系リンクの下段（SNS行の下に改行して並べる） */
  .sns-official {
    display: flex;
    flex-wrap: wrap;
    gap: .28em;
    align-items: center;
    margin-top: .4em;
    padding-top: .4em;
    border-top: 1px solid #f0ebf4;
  }
  .sns-official .sns-link {
    font-size: .7em;
    color: #6d5f76;
    background: #faf7fc;
  }

  .sns-arc-toggle {
    font: inherit;
    font-size: .72em;
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
      mline: el.getAttribute('data-mline') === '1',
      groups: (el.getAttribute('data-group') || '').split('|'),
      platforms: (el.getAttribute('data-platforms') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });
  var affBoxes = Array.from(document.querySelectorAll('.sns-aff-box'));

  function snsFilter() {
    var q = document.getElementById('sns-search').value.toLowerCase();
    var grFilter = document.getElementById('sns-group').value;
    var plFilter = document.getElementById('sns-platform').value;
    var checked = affBoxes.filter(function(b) { return b.checked; })
                          .map(function(b) { return b.value; });
    var mlineOn = checked.indexOf('mline') !== -1;

    var shown = 0;
    allCards.forEach(function(c) {
      // 所属チェックのユニオン。M-lineはリンク持ちを別枠で加算。全部外せば0人。
      var affOk = checked.indexOf(c.bucket) !== -1 || (mlineOn && c.mline);
      var match = affOk &&
        (!grFilter || c.groups.indexOf(grFilter) !== -1) &&
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
