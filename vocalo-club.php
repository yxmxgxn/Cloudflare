<?php
  $title = "宮本佳林のボカロ部 紹介曲データベース";
  $description = "ニコニコのチャンネル番組「宮本佳林のボカロ部」で紹介されたボカロ曲を、回・紹介者・ボーカル(初音ミク/GUMI等)ごとに整理したデータベースです。宮本佳林さんとゲストが歌唱・TikTokで踊った曲も収録しています。曲名から原曲、P名からボカロPのページにリンクしています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>宮本佳林のボカロ部 紹介曲データベース</h1>
<div class="vc-lead">
  <p>ニコニコのチャンネル番組<a href="https://ch.nicovideo.jp/miyamoto-vocalo" target="_blank" rel="noopener noreferrer">宮本佳林のボカロ部</a>で紹介された曲をまとめました。<br>
  番組で宮本佳林さんやゲストが歌った曲・TikTokで踊った曲も、各回の最後に入れています。</p>

  <p>曲名から原曲、P名からボカロPのユーザーページにリンクしています。<br>
  曲名のうしろの【】内は、その曲のボーカルです。</p>

  <p><strong>回ビュー</strong>は番組の進行順（ゲスト → 宮本佳林 → パフォーマンス）、<strong>曲ビュー</strong>は紹介された回数の多い順に並びます。<br>
  回・紹介者・ボーカルでの絞り込みと、フリーワード検索ができます。</p>

  <p>Xでの<a href="/X/karin-vocalo">#今日のボカロまとめ</a>は別ページです。</p>
</div>

<div class="vc-legend">
  <p><strong>回の「スケブ」</strong>：特定の回ではなく、宮本佳林さんが毎回のように出しているスケッチブックに書かれていた曲です。</p>
  <p><strong>形式の「スケブ」</strong>：スケッチブックに書かれていた曲です。</p>
  <p><strong>形式の「トーク」</strong>：スケッチブックには無く、トークで触れられた曲です。</p>
  <p><strong>形式の「トーク（スケブ）」</strong>：スケッチブックに書かれていて、かつトークの中で触れられた曲です。</p>
  <p><strong>形式の「アフタートーク」「Top1〜Top5」</strong>：番組内のそのコーナーで出てきた曲です。</p>
</div>

<h2>出演ゲスト</h2>
<p class="vc-guest-lead">名前をクリックすると、その回でその方が紹介した曲に絞り込めます。</p>
<div id="vc-guests">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocaloclub_guests.php'; ?>
</div>

<h2>紹介曲</h2>
<div id="vc-controls">
  <input type="text" id="vc-search" placeholder="曲名・ボーカル・P名・紹介者で絞り込み…" oninput="vcFilter()">
  <select id="vc-view" onchange="vcFilter()">
    <option value="episode">回ビュー（番組順）</option>
    <option value="track">曲ビュー（紹介回数順）</option>
  </select>
  <select id="vc-episode" onchange="vcFilter()">
    <option value="">回</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocaloclub_episodes.php'; ?>
  </select>
  <select id="vc-speaker" onchange="vcFilter()">
    <option value="">紹介者</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocaloclub_speakers.php'; ?>
  </select>
  <select id="vc-engine" onchange="vcFilter()">
    <option value="">ボーカル</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocaloclub_engines.php'; ?>
  </select>
  <button type="button" id="vc-reset" onclick="vcReset()">絞り込み解除</button>
  <span id="vc-count"></span>
</div>

<?php
  // 数百行の表を1枚で出すと、開いた瞬間に全行をレイアウトして遅いスマホで1秒以上固まる。
  // 見出しだけの表の下に VC_CHUNK 行ずつの表を並べ、画面外の表は CSS(.vc-chunk の content-visibility)で
  // 描画を後回しにする。HTMLには全行がそのまま入るので、検索・AIからの見え方は変わらない。
  // 列幅は table-layout: fixed と colgroup で決まるので、表を分けても列はそろう。
  // 全体を1つの .table-scroll で包み、横スクロールは表をまたいで一緒に動かす
  // (footer.php の自動ラップは .table-scroll の中の表を飛ばす)。
  const VC_CHUNK = 50;
  function vc_chunked_table(string $rowsFile, string $tableClass, string $colgroup, string $headRow): void {
    ob_start();
    include $rowsFile;
    $rows = preg_split('/(?=<tr\b)/', ob_get_clean());
    $lead = array_shift($rows);  // 先頭の生成日時コメント
    $open = '<table class="' . $tableClass . '">' . $colgroup;
    echo '<div class="table-scroll vc-table-wrap">';
    echo $open, '<thead>', $headRow, '</thead></table>';
    foreach (array_chunk($rows, VC_CHUNK) as $i => $chunk) {
      echo '<div class="vc-chunk">', $open, '<tbody>', $i === 0 ? $lead : '', implode('', $chunk), '</tbody></table></div>';
    }
    echo '</div>';
  }
?>
<div id="vc-view-episode" class="vc-view">
  <?php vc_chunked_table(
    $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocaloclub_episode_rows.php',
    'vc-table vc-episode-table',
    '<colgroup><col class="vc-col-ep"><col class="vc-col-who"><col class="vc-col-name">'
      . '<col class="vc-col-composer"><col class="vc-col-date"><col class="vc-col-notes"></colgroup>',
    '<tr><th>回</th><th>紹介者</th><th>曲名【ボーカル】</th><th>P名</th><th>投稿日</th><th>形式・備考</th></tr>'
  ); ?>
</div>

<div id="vc-view-track" class="vc-view" hidden>
  <?php vc_chunked_table(
    $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocaloclub_track_rows.php',
    'vc-table vc-track-table',
    '<colgroup><col class="vc-tcol-name"><col class="vc-tcol-composer"><col class="vc-tcol-date">'
      . '<col class="vc-tcol-count"><col class="vc-tcol-eps"></colgroup>',
    '<tr><th>曲名【ボーカル】</th><th>P名</th><th>投稿日</th><th>回数</th><th>登場した回</th></tr>'
  ); ?>
</div>

<p id="vc-empty" class="vc-empty" hidden>条件に合う曲がありませんでした。</p>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/vocaloclub_generated.php'; ?>

</main>
<script>
(function () {
  // 行は生成時に並べ済み。JSは絞り込み(表示/非表示)とビューの切り替えだけを担当する。
  function collect(sel, attrs) {
    return Array.prototype.map.call(document.querySelectorAll(sel), function (el) {
      var o = { el: el, search: (el.getAttribute('data-search') || '') };
      attrs.forEach(function (a) {
        var v = el.getAttribute('data-' + a.attr) || '';
        o[a.key] = a.list ? v.split('|') : v;
      });
      return o;
    });
  }

  var epRows = collect('#vc-view-episode .vc-row', [
    { key: 'episodes', attr: 'episode' },
    { key: 'speakers', attr: 'speakers', list: true },
    { key: 'vkeys', attr: 'vkeys', list: true },
    { key: 'kind', attr: 'kind' }
  ]);
  var trRows = collect('#vc-view-track .vc-trow', [
    { key: 'episodes', attr: 'episodes', list: true },
    { key: 'speakers', attr: 'speakers', list: true },
    { key: 'vkeys', attr: 'vkeys', list: true }
  ]);

  function has(v, want) {
    if (!want) return true;
    return Array.isArray(v) ? v.indexOf(want) !== -1 : v === want;
  }

  function vcFilter() {
    var q = document.getElementById('vc-search').value.trim().toLowerCase();
    var view = document.getElementById('vc-view').value;
    var ep = document.getElementById('vc-episode').value;
    var sp = document.getElementById('vc-speaker').value;
    var en = document.getElementById('vc-engine').value;

    var isEpisode = (view === 'episode');
    var viewEl = document.getElementById(isEpisode ? 'vc-view-episode' : 'vc-view-track');
    document.getElementById('vc-view-episode').hidden = !isEpisode;
    document.getElementById('vc-view-track').hidden = isEpisode;

    var rows = isEpisode ? epRows : trRows;
    var shown = 0;

    rows.forEach(function (r) {
      // パフォーマンス行は紹介者として「宮本佳林」とゲストの両方を持つので、
      // ゲストで絞り込むとその回の二人歌唱・TikTokも一緒に出る。
      var ok = (!q || r.search.indexOf(q) !== -1)
            && has(r.episodes, ep)
            && has(r.speakers, sp)
            && has(r.vkeys, en);
      r.el.hidden = !ok;
      if (ok) {
        // nth-child は非表示行も数えてしまうので、縞模様はクラスで付け直す
        r.el.classList.toggle('vc-odd', shown % 2 === 1);
        shown++;
      }
    });

    // 絞り込み中は表の描画の後回し(.vc-chunk の content-visibility)をやめ、該当行のない表は畳む。
    // 後回しにした表は描く前の仮の高さを確保したままなので、結果の下に数万pxの空白が残るため
    var filtered = shown < rows.length;
    viewEl.classList.toggle('is-filtered', filtered);
    Array.prototype.forEach.call(viewEl.querySelectorAll('.vc-chunk'), function (c) {
      c.hidden = filtered && !c.querySelector('tr:not([hidden])');
    });

    document.getElementById('vc-count').textContent =
      shown + ' 件 / 全' + rows.length + ' 件';
    document.getElementById('vc-empty').hidden = (shown > 0);

    Array.prototype.forEach.call(
      document.querySelectorAll('#vc-controls select'),
      function (s) { s.classList.toggle('is-active', s.id !== 'vc-view' && s.selectedIndex > 0); }
    );
    document.getElementById('vc-search').classList.toggle('is-active', q !== '');
  }

  function vcReset() {
    document.getElementById('vc-search').value = '';
    ['vc-episode', 'vc-speaker', 'vc-engine'].forEach(function (id) {
      document.getElementById(id).value = '';
    });
    vcFilter();
  }

  // 紹介者名をクリックしたらその人で絞り込む(パフォーマンス行は複数名なので対象外)
  document.getElementById('vc-view-episode').addEventListener('click', function (e) {
    var td = e.target.closest('.vc-who');
    if (!td) return;
    var row = td.closest('.vc-row');
    if (!row || row.getAttribute('data-kind') !== 'mention') return;
    var name = row.getAttribute('data-speakers') || '';
    if (!name) return;
    var sel = document.getElementById('vc-speaker');
    sel.value = (sel.value === name) ? '' : name;   // もう一度押すと解除
    vcFilter();
  });

  // 出演ゲスト一覧の名前クリックでも同じ絞り込みをかける
  var guestBox = document.getElementById('vc-guests');
  if (guestBox) {
    guestBox.addEventListener('click', function (e) {
      var t = e.target.closest('.vc-guest-link');
      if (!t) return;
      var name = t.getAttribute('data-speaker') || '';
      var sel = document.getElementById('vc-speaker');
      sel.value = (sel.value === name) ? '' : name;   // もう一度押すと解除
      document.getElementById('vc-view').value = 'episode';
      vcFilter();
      document.getElementById('vc-controls').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  vcFilter();
  window.vcFilter = vcFilter;
  window.vcReset = vcReset;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
