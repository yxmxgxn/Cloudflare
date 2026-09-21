<?php
  $title = "ハロプロメンバーが語るインタビュー・Web記事まとめ";
  $description = "ハロー！プロジェクトのメンバー本人の言葉が読めるWeb記事（インタビュー・対談・連載・コラムなど）をまとめています。掲載日・媒体・連載名ごとに整理し、各記事へのリンクとWeb Archiveも掲載しています。";
  $jsonld_type = "Dataset";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロメンバーが語るインタビュー・Web記事まとめ</h1>
  <p>ハロー！プロジェクトのメンバー本人の言葉が読めるWeb記事（インタビュー・対談・連載・コラムなど）をまとめています。<br>
  各カードは、掲載日とタイトル（記事リンク）、媒体・連載名、メンバーで構成されています。</p>
  <p>グループ・メンバー名での絞り込み、掲載日・タイトルでの並び替え、記事本文・タイトル・連載名・メンバー名を対象にしたキーワード検索ができます。カードの連載名・媒体名・メンバー名を押すと、それで絞り込めます。</p>
  <p>元記事が読めなくなったものは、右下のWeb Archiveからアーカイブを開けます。</p>
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
<div id="iv-chips" hidden></div>
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
      media: el.getAttribute('data-media') || '',
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
  // カードの連載名・媒体名を押したときの絞り込み(値の完全一致)。'' = 無効
  var chipFilters = { feature: '', media: '' };
  var CHIP_LABEL = { feature: '連載・企画', media: '媒体' };

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
      var matchChip = (!chipFilters.feature || c.feature === chipFilters.feature) &&
                      (!chipFilters.media || c.media === chipFilters.media);
      return matchQ && matchGr && matchNm && matchDate && matchChip;
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

  // カード内の連載名・媒体名・メンバー名を押したときの絞り込み
  container.addEventListener('click', function(e) {
    var b = e.target.closest('.iv-feature, .iv-media, .iv-member');
    if (!b) return;
    if (b.classList.contains('iv-member')) {
      ivSelectMember(b.getAttribute('data-name') || '');
    } else {
      var kind = b.classList.contains('iv-feature') ? 'feature' : 'media';
      chipFilters[kind] = b.getAttribute('data-' + kind) || '';
      renderChips();
      ivFilter();
    }
    document.getElementById('interview-controls').scrollIntoView({ block: 'start', behavior: 'smooth' });
  });

  // 連載・媒体の絞り込み中の表示(「連載・企画：○○ 解除」)。値は textContent で入れる
  function renderChips() {
    var box = document.getElementById('iv-chips');
    box.textContent = '';
    Object.keys(chipFilters).forEach(function(kind) {
      var v = chipFilters[kind];
      if (!v) return;
      var chip = document.createElement('span');
      chip.className = 'iv-chip';
      var label = document.createElement('span');
      label.textContent = CHIP_LABEL[kind] + '：';
      var val = document.createElement('b');
      val.textContent = v;
      var clear = document.createElement('button');
      clear.type = 'button';
      clear.textContent = '解除';
      clear.setAttribute('aria-label', CHIP_LABEL[kind] + 'の絞り込みを解除');
      clear.addEventListener('click', function() {
        chipFilters[kind] = '';
        renderChips();
        ivFilter();
      });
      chip.appendChild(label); chip.appendChild(val); chip.appendChild(clear);
      box.appendChild(chip);
    });
    box.hidden = !box.childNodes.length;
  }

  // メンバー名を押す → 既存のメンバー絞り込み(プルダウン)にその人をセット。
  // 選択中のグループにその人がいなければ、選べるようにグループの絞り込みを外す
  function ivSelectMember(name) {
    var grp = document.getElementById('iv-group');
    var sel = document.getElementById('iv-name');
    var opt = Array.prototype.find.call(sel.options, function(o) { return o.value === name; });
    if (!opt) return;
    var og = (opt.getAttribute('data-group') || '').split('|');
    if (grp.value && og.indexOf(grp.value) === -1) grp.value = '';
    ivSyncNames();
    sel.value = name;
    ivFilter();
  }

  // 表示中(フィルタ後)の記事を、確認のうえ全部別タブで開く
  function ivOpenAll() {
    var urls = [];
    allCards.forEach(function(c) {
      if (c.el.style.display === 'none') return;      // 非表示は対象外
      // 元記事へのリンク。元記事が読めないカードは魚拓のリンク
      var a = c.el.querySelector('.iv-title a') || c.el.querySelector('.iv-arc a');
      if (a && a.href) urls.push(a.href);             // どちらも無い記事はスキップ
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
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
