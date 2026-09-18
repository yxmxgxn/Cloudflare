<?php
  $title = "ハロー！プロジェクト メンバー Web記事まとめ";
  $description = "ハロー！プロジェクト メンバーWebインタビュー・Web上での記事をまとめています。掲載日・媒体・連載名（特集）ごとに整理し、各記事へのリンクとWeb Archiveも掲載しています。現在は宮本佳林さん関連が中心です。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロー！プロジェクト メンバーのWeb記事まとめ</h1>
  <p>ハロー！プロジェクト メンバーのWebインタビュー・Web上での記事をまとめています。<br>
  各カードは、掲載日とタイトル（記事リンク）、媒体・連載名、メンバーで構成されています。</p>
  <p>グループ・メンバー名での絞り込み、掲載日・タイトルでの並び替え、記事本文・タイトル・連載名・メンバー名を対象にしたキーワード検索ができます。カードの連載名を押すと、その連載の記事だけに絞り込めます。</p>
  <p>右下のWeb Archiveからアーカイブを開けます。</p>
  <p>2026/09/14 宮本佳林さん・工藤遥さん・OCHA NORMA・ロージークロニクルを中心に集めています。</p>
  <p>管理人の独断と偏見で集めているので、取捨選択になっていることをご容赦ください</p>
<div id="interview-controls">
  <input type="text" id="iv-search" placeholder="本文・タイトル・連載名・メンバー名を検索…" oninput="ivSearchInput()">
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
  <button type="button" id="iv-date-toggle" onclick="ivDateToggle()" aria-expanded="false">📅 期間</button>
  <label id="iv-active-label"><input type="checkbox" id="iv-active" onchange="ivFilter()">現役時のみ</label>
  <button type="button" id="iv-openall" onclick="ivOpenAll()">表示中を別タブで開く</button>
  <span id="interview-count"></span>
</div>
<div id="iv-date-panel" hidden>
  <label>開始 <input type="date" id="iv-from" onchange="ivFilter()"></label>
  <span>〜</span>
  <label>終了 <input type="date" id="iv-to" onchange="ivFilter()"></label>
  <button type="button" id="iv-date-clear" onclick="ivDateClear()">クリア</button>
</div>
<div id="iv-feature-filter" hidden>
  <span>連載・企画：</span><b id="iv-feature-name"></b>
  <button type="button" id="iv-feature-clear" onclick="ivFeatureClear()" aria-label="連載・企画の絞り込みを解除">解除</button>
</div>
<div id="interview-cards" class="card-grid">
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
      id: el.getAttribute('data-id') || '',
      date: el.getAttribute('data-date') || '',
      title: el.getAttribute('data-title') || '',
      feature: el.getAttribute('data-feature') || '',
      groups: (el.getAttribute('data-groups') || '').split('|'),
      activeGroups: (el.getAttribute('data-active-groups') || '').split('|'),
      names: (el.getAttribute('data-names') || '').split('|'),
      activeNames: (el.getAttribute('data-active-names') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });

  // 全文検索の結果(記事IDの集合)。null = 検索未適用(サーバ絞り込み無効)
  var serverIds = null;
  var searchSeq = 0;   // 古いレスポンスを無視するためのトークン
  var searchTimer = null;
  var featureFilter = '';  // カードの連載名を押したときの絞り込み(完全一致)。'' = 無効

  // 入力のたびにデバウンスしてから全文検索を叩く
  function ivSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(ivSearch, 250);
  }

  function ivSearch() {
    var q = document.getElementById('iv-search').value.trim();
    if (q.length < 2) { serverIds = null; ivFilter(); return; }
    var mySeq = ++searchSeq;
    fetch('/api/interview-search?q=' + encodeURIComponent(q))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (mySeq !== searchSeq) return;          // 新しい入力が来ていたら破棄
        serverIds = new Set(data.ids || []);
        ivFilter();
      })
      .catch(function() {                          // API不通時はクライアント絞り込みに退避
        if (mySeq !== searchSeq) return;
        serverIds = clientFallback(q);
        ivFilter();
      });
  }

  // フォールバック: タイトル・媒体・メンバー(data-search)への部分一致
  function clientFallback(q) {
    q = q.toLowerCase();
    var s = new Set();
    allCards.forEach(function(c) { if (c.search.indexOf(q) !== -1) s.add(c.id); });
    return s;
  }

  function ivFilter() {
    var grFilter = document.getElementById('iv-group').value;
    var nmFilter = document.getElementById('iv-name').value;
    var sort = document.getElementById('iv-sort').value;
    var from = document.getElementById('iv-from').value;  // 'YYYY-MM-DD' or ''
    var to = document.getElementById('iv-to').value;       // data-date と同形式なので文字列比較でOK
    var activeOnly = document.getElementById('iv-active').checked;

    var filtered = allCards.filter(function(c) {
      var matchQ = serverIds === null || serverIds.has(c.id);
      // 現役時のみ: 選択中のグループ/メンバーが、その記事の掲載時点で在籍していたか
      var grList = activeOnly ? c.activeGroups : c.groups;
      var nmList = activeOnly ? c.activeNames : c.names;
      var matchGr = !grFilter || grList.indexOf(grFilter) !== -1;
      var matchNm = !nmFilter || nmList.indexOf(nmFilter) !== -1;
      var matchDate = (!from || (c.date && c.date >= from)) &&
                      (!to || (c.date && c.date <= to));
      var matchFt = !featureFilter || c.feature === featureFilter;
      return matchQ && matchGr && matchNm && matchDate && matchFt;
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
    // 期間が設定されていれば「📅 期間」ボタンを強調
    document.getElementById('iv-date-toggle')
      .classList.toggle('is-active', !!(from || to));
  }

  // 期間パネルの開閉(Google式: 押した時だけ出す)
  function ivDateToggle() {
    var p = document.getElementById('iv-date-panel');
    p.hidden = !p.hidden;
    document.getElementById('iv-date-toggle')
      .setAttribute('aria-expanded', String(!p.hidden));
  }

  // 期間クリア(パネルは開いたまま)
  function ivDateClear() {
    document.getElementById('iv-from').value = '';
    document.getElementById('iv-to').value = '';
    ivFilter();
  }

  // カードの連載名を押す → 同じ連載名の記事だけに絞る(検索語ではなく値の完全一致)
  container.addEventListener('click', function(e) {
    var b = e.target.closest('.iv-feature');
    if (!b) return;
    featureFilter = b.getAttribute('data-feature') || '';
    document.getElementById('iv-feature-name').textContent = featureFilter;
    document.getElementById('iv-feature-filter').hidden = false;
    ivFilter();
    document.getElementById('interview-controls').scrollIntoView({ block: 'start', behavior: 'smooth' });
  });

  function ivFeatureClear() {
    featureFilter = '';
    document.getElementById('iv-feature-filter').hidden = true;
    ivFilter();
  }

  // 表示中(フィルタ後)の記事を、確認のうえ全部別タブで開く
  function ivOpenAll() {
    var urls = [];
    allCards.forEach(function(c) {
      if (c.el.style.display === 'none') return;      // 非表示は対象外
      var a = c.el.querySelector('.iv-title a');
      if (a && a.href) urls.push(a.href);             // リンクの無い記事はスキップ
    });
    if (!urls.length) { alert('開けるリンクがありません'); return; }

    var msg = urls.length + ' 件を別タブで開きますか？';
    if (urls.length > 30) {
      msg = urls.length + ' 件を別タブで開きます。数が多いと一部がブラウザにブロックされたり動作が重くなります。よろしいですか？';
    }
    if (!confirm(msg)) return;

    var blocked = 0;
    urls.forEach(function(u) {
      var w = window.open(u, '_blank');
      if (w) { w.opener = null; } else { blocked++; }  // opener遮断＋ブロック検知
    });
    if (blocked) {
      alert(blocked + ' 件がブラウザにブロックされました。ポップアップを許可すると全部開けます。');
    }
  }

  // グループを選ぶと、メンバー選択肢をそのグループの人だけに絞る
  function ivSyncNames() {
    var g = document.getElementById('iv-group').value;
    var nameSel = document.getElementById('iv-name');
    var stillValid = false;
    Array.prototype.forEach.call(nameSel.options, function(o) {
      if (!o.value) return; // 「メンバー」(プレースホルダ)
      var og = (o.getAttribute('data-group') || '').split('|');  // 在籍歴すべて
      var show = !g || og.indexOf(g) !== -1;
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

  // カレンダーの選択可能範囲をデータの最小/最大日に合わせる
  (function initDateRange() {
    var dates = allCards.map(function(c) { return c.date; })
                        .filter(function(d) { return d; }).sort();
    if (!dates.length) return;
    var lo = dates[0], hi = dates[dates.length - 1];
    ['iv-from', 'iv-to'].forEach(function(id) {
      var el = document.getElementById(id);
      el.min = lo; el.max = hi;
    });
  })();

  ivFilter();
  window.ivFilter = ivFilter;
  window.ivGroupChange = ivGroupChange;
  window.ivSearchInput = ivSearchInput;
  window.ivDateToggle = ivDateToggle;
  window.ivDateClear = ivDateClear;
  window.ivOpenAll = ivOpenAll;
  window.ivFeatureClear = ivFeatureClear;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
