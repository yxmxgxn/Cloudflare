<?php
  $title = "ONE PLUS ONE・One on One まとめ";
  $description = "ハロー！プロジェクトのメンバーが二人でパフォーマンスするデュオ企画「ONE PLUS ONE」（テレビ番組「ハロドリ。」連動）と、アップフロントチャンネルの「COVERS -One on One-」を曲ごとにまとめました。披露した曲・二人の組み合わせ・公開日と動画へのリンクを、企画・グループで絞り込めます。";
  $jsonld_type = "Dataset";
  $about = [
    ["name" => "ハロー！プロジェクト", "type" => "Organization"],
  ];
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ONE PLUS ONE・One on One まとめ</h1>
<div class="opo-lead">
  <p>ハロー！プロジェクトのメンバーが二人でパフォーマンスするデュオ企画をまとめました。<br>
  
  <p><strong>ONE PLUS ONE</strong>はテレビ番組「ハロドリ。」のデュオ企画で、各グループの公式YouTubeチャンネルで特別編集版・<a href="https://www.youtube.com/@TVTOKYO" target="_blank" rel="noopener noreferrer">テレ東公式チャンネル</a>で1アングルバージョンが公開されています。<br>
  <strong>COVERS -One on One-</strong>はメンバー二人のデュオ企画で<a href="https://www.youtube.com/@upfrontchannel" target="_blank" rel="noopener noreferrer">アップフロントチャンネル</a>に公開されています。<br>
  COVERS -One on One-は<a href="https://helloproject.com/release/6817/" target="_blank" rel="noopener noreferrer">Blu-ray Disc + CD</a>が発売されています。</p>
  
  <p>動画リンクは公式の再生リストに繋いであるので、そのまま次の動画へ続きます。並びも再生リストに準拠しています。<br>
  ONE PLUS ONE・1アングルバージョンは<a href="https://www.youtube.com/@helloprokenshusei" target="_blank" rel="noopener noreferrer">ハロプロ研修生</a>の<a href="https://www.youtube.com/playlist?list=PLUjhiQzhlwqLcsAgVAJRHYAbP0dEQ1xGJ" target="_blank" rel="noopener noreferrer">ONE PLUS ONE</a>、
  COVERS -One on One-は<a href="https://www.youtube.com/@upfrontchannel" target="_blank" rel="noopener noreferrer">アップフロントチャンネル</a>の<a href="https://www.youtube.com/playlist?list=PLFMni9aeqKTw_nNHBiGWfPLT-VMdMez97" target="_blank" rel="noopener noreferrer">COVERS - One on One -</a>です。<br>
  再生リストに入っていない動画だけ、その動画単体へのリンクになっています。</p>

  <p>グループ名はその動画が公開された当時の所属で、ハロー！プロジェクトを卒業しているメンバーは「OG」としています。<br>
  当時ハロプロ研修生だったメンバーは、その後加入したグループでも絞り込めます。<br>
  メンバー名をクリックすると、そのメンバーで絞り込めます。</p>
</div>

<div id="opo-controls">
  <select id="opo-feature" onchange="opoFilter()">
    <option value="">企画</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/oneplusone_features.php'; ?>
  </select>
  <select id="opo-group" onchange="opoGroupChange()">
    <option value="">グループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/oneplusone_groups.php'; ?>
  </select>
  <select id="opo-name" onchange="opoFilter()">
    <option value="">メンバー</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/oneplusone_names.php'; ?>
  </select>
  <button type="button" id="opo-reset" onclick="opoReset()">絞り込み解除</button>
  <span id="opo-count"></span>
</div>

<div class="card-grid" id="opo-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/oneplusone_cards.php'; ?>
</div>

<p id="opo-empty" class="opo-empty" hidden>条件に合う曲がありませんでした。</p>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/oneplusone_generated.php'; ?>

</main>
<script>
(function () {
  // 1枚のカード = 1曲。生成時に公開日の新しい順で並べてあるので、JSは絞り込みだけを担当する。
  var grid = document.getElementById('opo-cards');
  var cards = Array.prototype.map.call(
    grid.querySelectorAll('.opo-card'),
    function (el) {
      return {
        el: el,
        features: (el.getAttribute('data-features') || '').split('|'),
        names: (el.getAttribute('data-names') || '').split('|'),
        groups: (el.getAttribute('data-groups') || '').split('|')
      };
    }
  );

  function opoFilter() {
    var feature = document.getElementById('opo-feature').value;
    var group = document.getElementById('opo-group').value;
    var name = document.getElementById('opo-name').value;

    var shown = 0;
    cards.forEach(function (c) {
      var ok = (!feature || c.features.indexOf(feature) !== -1)
            && (!group || c.groups.indexOf(group) !== -1)
            && (!name || c.names.indexOf(name) !== -1);
      c.el.hidden = !ok;
      if (ok) shown++;
    });

    document.getElementById('opo-count').textContent =
      shown + ' 曲 / 全' + cards.length + ' 曲';
    document.getElementById('opo-empty').hidden = (shown > 0);

    Array.prototype.forEach.call(
      document.querySelectorAll('#opo-controls select'),
      function (s) { s.classList.toggle('is-active', s.selectedIndex > 0); }
    );
  }

  // グループを選ぶと、メンバーの選択肢をそのグループの人だけに絞る
  function opoSyncNames() {
    var g = document.getElementById('opo-group').value;
    var sel = document.getElementById('opo-name');
    var stillValid = false;
    Array.prototype.forEach.call(sel.options, function (o) {
      if (!o.value) return;                                     // プレースホルダ
      var og = (o.getAttribute('data-group') || '').split('|'); // 在籍歴すべて
      var show = !g || og.indexOf(g) !== -1;
      o.hidden = !show;
      o.disabled = !show;
      if (o.value === sel.value && show) stillValid = true;
    });
    if (!stillValid) sel.value = '';                            // 隠れた選択は解除
  }

  function opoGroupChange() {
    opoSyncNames();
    opoFilter();
  }

  function opoReset() {
    ['opo-feature', 'opo-group', 'opo-name'].forEach(function (id) {
      document.getElementById(id).value = '';
    });
    opoSyncNames();
    opoFilter();
  }

  // メンバー名をクリックしたらその人で絞り込む(もう一度押すと解除)
  grid.addEventListener('click', function (e) {
    var t = e.target.closest('.opo-name');
    if (!t) return;
    var sel = document.getElementById('opo-name');
    var n = t.textContent.trim();
    if (!Array.prototype.some.call(sel.options, function (o) { return o.value === n; })) return;
    document.getElementById('opo-group').value = '';   // グループ絞り込みと衝突させない
    opoSyncNames();
    sel.value = (sel.value === n) ? '' : n;
    opoFilter();
    document.getElementById('opo-controls').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  opoFilter();
  window.opoFilter = opoFilter;
  window.opoGroupChange = opoGroupChange;
  window.opoReset = opoReset;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
