<?php
  $title = "ハロショ イベントまとめ";
  $description = "ハロー！プロジェクトオフィシャルショップ（ハロショ）東京秋葉原店・大阪店で行われたトークイベント「ハロショ千夜一夜」「千夜一夜アネックス」「大阪独演会」「大阪二人会」などを一覧にまとめました。開催日・出演メンバー・各部のタイトル・会場で絞り込めます。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロショ イベントまとめ</h1>
<div class="hs-lead">
  <p>ハロー！プロジェクトオフィシャルショップ（ハロショ）の東京秋葉原店・<a href="https://www.helloshop.info/" target="_blank" rel="noopener noreferrer">大阪店</a>で行われた、メンバーのトークイベントをまとめました。<br>
  1つのブロックが1回の公演で、その中の各部（#1・#2…）を開演時刻順に1行ずつ並べています。</p>

  <p>会場が「配信のみ」の回は、コロナ禍に<a href="https://helloproject-stream.com/" target="_blank" rel="noopener noreferrer">HELLO! PROJECT STREAM</a>で無観客配信されたものです。<br>
  加賀温泉郷・東京ベイ東急ホテルの回は、ハロショの外で行われた特別編です。</p>

  <p>イベント・グループ・出演メンバー・会場・年での絞り込みと、タイトルや出演者（読み仮名も可）でのキーワード検索ができます。</p>
</div>

<div id="hs-controls">
  <input type="text" id="hs-search" placeholder="タイトル・出演者（読み仮名も可）で検索…" oninput="hsFilter()">
  <select id="hs-series" onchange="hsFilter()">
    <option value="">イベント</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shopevent_series.php'; ?>
  </select>
  <select id="hs-group" onchange="hsGroupChange()">
    <option value="">グループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shopevent_groups.php'; ?>
  </select>
  <select id="hs-name" onchange="hsFilter()">
    <option value="">出演メンバー</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shopevent_names.php'; ?>
  </select>
  <select id="hs-place" onchange="hsFilter()">
    <option value="">会場</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shopevent_places.php'; ?>
  </select>
  <select id="hs-year" onchange="hsFilter()">
    <option value="">年</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shopevent_years.php'; ?>
  </select>
  <select id="hs-sort" onchange="hsFilter()">
    <option value="date-desc">開催日が新しい順</option>
    <option value="date-asc">開催日が古い順</option>
  </select>
  <button type="button" id="hs-reset" onclick="hsReset()">絞り込み解除</button>
  <span id="hs-count"></span>
</div>

  <table id="hs-table">
    <colgroup>
      <col class="hs-col-date"><col class="hs-col-event"><col class="hs-col-slot">
      <col class="hs-col-title"><col class="hs-col-cast"><col class="hs-col-place">
    </colgroup>
    <thead>
      <tr><th>開催日</th><th>イベント</th><th>部</th><th>タイトル</th><th>出演</th><th>会場</th></tr>
    </thead>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shopevent_rows.php'; ?>
  </table>

<p id="hs-empty" class="hs-empty" hidden>条件に合うイベントがありませんでした。</p>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/shopevent_generated.php'; ?>

<h2>会場</h2>
<p class="hs-venue-lead">イベントが行われたハロショの場所です。秋葉原店は2025年10月31日に閉店しました。</p>

<div class="hs-venues">
  <section class="hs-venue">
    <h3>東京秋葉原店<span class="hs-venue-tag">閉店</span></h3>
    <p class="hs-venue-addr">〒101-0021 東京都千代田区外神田6-14-2 サカイ末広ビル 6階</p>
    <p class="hs-venue-note">銀座線 末広町駅前。2013年3月オープン、2025年10月31日20時をもって閉店。<br>
      千夜一夜・アネックス・秋葉原 感謝祭の会場でした。</p>
    <iframe class="hs-map" loading="lazy" title="東京秋葉原店（サカイ末広ビル）の地図"
      src="https://www.openstreetmap.org/export/embed.html?bbox=139.767970,35.701417,139.774970,35.705017&amp;layer=mapnik&amp;marker=35.7032171,139.7714697"></iframe>
    <p class="hs-venue-links">
      <a href="https://www.openstreetmap.org/?mlat=35.7032171&amp;mlon=139.7714697#map=18/35.7032171/139.7714697" target="_blank" rel="noopener noreferrer">大きな地図</a>
      <a href="https://www.google.com/maps/search/?api=1&amp;query=%E6%9D%B1%E4%BA%AC%E9%83%BD%E5%8D%83%E4%BB%A3%E7%94%B0%E5%8C%BA%E5%A4%96%E7%A5%9E%E7%94%B06-14-2%20%E3%82%B5%E3%82%AB%E3%82%A4%E6%9C%AB%E5%BA%83%E3%83%93%E3%83%AB" target="_blank" rel="noopener noreferrer">Googleマップ</a>
    </p>
  </section>

  <section class="hs-venue">
    <h3>大阪店</h3>
    <p class="hs-venue-addr">〒556-0005 大阪府大阪市浪速区日本橋5-7-19 KMビル</p>
    <p class="hs-venue-note">堺筋線 恵美須町駅 1-A出入口より徒歩0分。<br>
      千夜一夜 in 大阪・大阪独演会・大阪二人会・Lovelysの部屋の会場です。</p>
    <iframe class="hs-map" loading="lazy" title="大阪店の地図"
      src="https://www.openstreetmap.org/export/embed.html?bbox=135.502314,34.655152,135.509314,34.658752&amp;layer=mapnik&amp;marker=34.656952,135.505814"></iframe>
    <p class="hs-venue-links">
      <a href="https://www.openstreetmap.org/?mlat=34.656952&amp;mlon=135.505814#map=18/34.656952/135.505814" target="_blank" rel="noopener noreferrer">大きな地図</a>
      <a href="https://www.google.com/maps/search/?api=1&amp;query=%E5%A4%A7%E9%98%AA%E5%BA%9C%E5%A4%A7%E9%98%AA%E5%B8%82%E6%B5%AA%E9%80%9F%E5%8C%BA%E6%97%A5%E6%9C%AC%E6%A9%8B5-7-19%20KM%E3%83%93%E3%83%AB" target="_blank" rel="noopener noreferrer">Googleマップ</a>
      <a href="https://www.helloshop.info/shop/20160108_59017.html" target="_blank" rel="noopener noreferrer">店舗ページ</a>
    </p>
  </section>
</div>

<p class="hs-venue-extra">このほか、加賀温泉郷（山代温泉）と東京ベイ東急ホテルで行われた特別編が1回ずつあります。</p>

</main>
<script>
(function () {
  // 1公演回 = 1つの <tbody>(中の行が各部)。生成時に開催日の新しい順で並べてあるので、
  // JSは絞り込みと並べ替えだけを担当する。
  var table = document.getElementById('hs-table');
  var rows = Array.prototype.map.call(
    table.querySelectorAll('tbody.hs-row'),
    function (el) {
      return {
        el: el,
        date: el.getAttribute('data-date') || '',
        year: el.getAttribute('data-year') || '',
        series: el.getAttribute('data-series') || '',
        place: el.getAttribute('data-place') || '',
        names: (el.getAttribute('data-names') || '').split('|'),
        groups: (el.getAttribute('data-groups') || '').split('|'),
        search: el.getAttribute('data-search') || ''
      };
    }
  );

  // まだ開催されていない回に「予定」を付ける(静的ページなので判定は表示時に行う)
  (function markUpcoming() {
    var d = new Date();
    var today = d.getFullYear() + '-' +
                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                ('0' + d.getDate()).slice(-2);
    rows.forEach(function (r) {
      if (r.date && r.date > today) r.el.classList.add('hs-upcoming');
    });
  })();

  function hsFilter() {
    var q = document.getElementById('hs-search').value.trim().toLowerCase();
    var series = document.getElementById('hs-series').value;
    var group = document.getElementById('hs-group').value;
    var name = document.getElementById('hs-name').value;
    var place = document.getElementById('hs-place').value;
    var year = document.getElementById('hs-year').value;
    var sort = document.getElementById('hs-sort').value;

    var shown = [];
    rows.forEach(function (r) {
      var ok = (!q || r.search.indexOf(q) !== -1)
            && (!series || r.series === series)
            && (!group || r.groups.indexOf(group) !== -1)
            && (!name || r.names.indexOf(name) !== -1)
            && (!place || r.place === place)
            && (!year || r.year === year);
      r.el.hidden = !ok;
      if (ok) shown.push(r);
    });

    if (sort === 'date-asc') {
      // 生成時が新しい順なので、逆順に積み直せば古い順になる
      for (var i = shown.length - 1; i >= 0; i--) table.appendChild(shown[i].el);
    } else {
      shown.forEach(function (r) { table.appendChild(r.el); });
    }

    // nth-child は非表示の回も数えてしまうので、縞模様はクラスで付け直す
    shown.forEach(function (r, i) { r.el.classList.toggle('hs-odd', i % 2 === 1); });

    document.getElementById('hs-count').textContent =
      shown.length + ' 回 / 全' + rows.length + ' 回';
    document.getElementById('hs-empty').hidden = (shown.length > 0);

    Array.prototype.forEach.call(
      document.querySelectorAll('#hs-controls select'),
      function (s) { s.classList.toggle('is-active', s.id !== 'hs-sort' && s.selectedIndex > 0); }
    );
    document.getElementById('hs-search').classList.toggle('is-active', q !== '');
  }

  // グループを選ぶと、出演メンバーの選択肢をそのグループの人だけに絞る
  function hsSyncNames() {
    var g = document.getElementById('hs-group').value;
    var sel = document.getElementById('hs-name');
    var stillValid = false;
    Array.prototype.forEach.call(sel.options, function (o) {
      if (!o.value) return;                                   // プレースホルダ
      var og = (o.getAttribute('data-group') || '').split('|'); // 在籍歴すべて
      var show = !g || og.indexOf(g) !== -1;
      o.hidden = !show;
      o.disabled = !show;
      if (o.value === sel.value && show) stillValid = true;
    });
    if (!stillValid) sel.value = '';                          // 隠れた選択は解除
  }

  function hsGroupChange() {
    hsSyncNames();
    hsFilter();
  }

  function hsReset() {
    document.getElementById('hs-search').value = '';
    ['hs-series', 'hs-group', 'hs-name', 'hs-place', 'hs-year'].forEach(function (id) {
      document.getElementById(id).value = '';
    });
    hsSyncNames();
    hsFilter();
  }

  // 出演者名をクリックしたらその人で絞り込む(もう一度押すと解除)
  table.addEventListener('click', function (e) {
    var t = e.target.closest('.hs-name');
    if (!t) return;
    var sel = document.getElementById('hs-name');
    var n = t.textContent.trim();
    if (!Array.prototype.some.call(sel.options, function (o) { return o.value === n; })) return;
    document.getElementById('hs-group').value = '';   // グループ絞り込みと衝突させない
    hsSyncNames();
    sel.value = (sel.value === n) ? '' : n;
    hsFilter();
    document.getElementById('hs-controls').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  hsFilter();
  window.hsFilter = hsFilter;
  window.hsGroupChange = hsGroupChange;
  window.hsReset = hsReset;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
