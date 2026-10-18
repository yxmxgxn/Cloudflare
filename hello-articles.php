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
  <p>グループ・メンバー名での絞り込み、掲載日・タイトルでの並び替え、記事本文・タイトル・連載名・メンバー名を対象にしたキーワード検索ができます。カードの連載名・媒体名・メンバー名を押すと、それで絞り込めます。検索すると、本文とタイトル等のどちらで一致したかがカードに出ます。年別・媒体別の件数は<a href="/hello-articles-stats">件数のページ</a>にまとめています。</p>
  <p>掲載から30日以内の記事には「NEW」が付きます。元記事が読めなくなったものには「リンク切れ」が付いていて、右下のWeb Archiveからアーカイブを開けます。</p>
  <p>このページを開いた時点では、掲載日が新しい20件だけを読み込んでいます。グループ・メンバーを選ぶか、検索ワードを入れてEnterを押すと、全<?= trim(@file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/Generated/interview_total.txt')) ?: '' ?>件から絞り込んで20件ずつ表示します。</p>
  <p>2026/09/14 宮本佳林さん・工藤遥さん・OCHA NORMA・ロージークロニクルを中心に集めています。</p>
  <p>管理人の独断と偏見で集めているので、取捨選択になっていることをご容赦ください</p>
<div id="interview-controls">
  <input type="text" id="iv-search" placeholder="本文・タイトル・連載名・メンバー名を検索（Enterで実行）" onkeydown="ivSearchKey(event)">
  <button type="button" id="iv-search-go" onclick="ivSearch()">検索</button>
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
<div id="interview-cards" class="card-grid" data-total="<?= trim(@file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/Generated/interview_total.txt')) ?: '0' ?>">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/interview_cards.php'; ?>
</div>
<div id="iv-more-wrap" hidden>
  <button type="button" id="iv-more" onclick="ivMore()"></button>
  <button type="button" id="iv-more-all" onclick="ivMoreAll()"></button>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/interview_generated.php'; ?>

</main>
<script>
(function() {
  var container = document.getElementById('interview-cards');
  var allCards = [];
  // HTMLに入っているのは新着20件だけ。残りは絞り込み・検索・「次の20件」で初めて読む
  var loaded = false;      // 全件JSONを読み込み済みか
  var loading = null;      // 読み込み中のPromise(二重fetch防止)

  // 掲載日が閲覧日から30日以内なら NEW(ページは定期生成なので、判定は見た日に合わせてここでする)
  var NEW_SINCE = (function() {
    var d = new Date(Date.now() - 30 * 86400000);
    return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
  })();

  function scanCards() {
    allCards = Array.prototype.map.call(container.querySelectorAll('.iv-card'), cardInfo);
    allCards.forEach(function(c) {
      c.el.classList.toggle('iv-new', c.date >= NEW_SINCE);
      addRelatedButton(c.el);
    });
    initDateRange();
  }

  // 「関連する記事」: 押したときだけ、そのカードの中に近い記事を数件出す
  var RELATED_MAX = 3;

  function addRelatedButton(el) {
    var p = document.createElement('p');
    p.className = 'iv-rel';
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'iv-rel-btn';
    b.setAttribute('aria-expanded', 'false');
    b.textContent = '関連する記事';
    p.appendChild(b);
    el.insertBefore(p, el.querySelector('.iv-arc'));
  }

  // 同じ連載 > メンバーの重なり(顔ぶれが近いほど上) > 同じ媒体、同点なら掲載日が近い順
  function relatedOf(c) {
    var t = Date.parse(c.date) || 0;
    var scored = [];
    allCards.forEach(function(d) {
      if (d.id === c.id) return;
      var sameFeature = c.feature && d.feature === c.feature;
      var shared = c.names.filter(function(n) { return n && d.names.indexOf(n) !== -1; }).length;
      if (!sameFeature && !shared) return;
      var union = c.names.length + d.names.length - shared;
      var score = (sameFeature ? 100 : 0) + (shared ? 30 * shared / union : 0) + (c.media && d.media === c.media ? 5 : 0);
      var gap = Math.abs((Date.parse(d.date) || 0) - t);
      scored.push({ d: d, score: score, gap: gap, why: sameFeature ? '同じ連載' : '同じメンバー' });
    });
    scored.sort(function(a, b) { return b.score - a.score || a.gap - b.gap; });
    return scored.slice(0, RELATED_MAX);
  }

  function toggleRelated(btn) {
    var card = btn.closest('.iv-card');
    var open = btn.getAttribute('aria-expanded') === 'true';
    var list = card.querySelector('.iv-rel-list');
    if (open) {
      if (list) list.hidden = true;
      btn.setAttribute('aria-expanded', 'false');
      return;
    }
    if (list) {
      list.hidden = false;
      btn.setAttribute('aria-expanded', 'true');
      return;
    }
    var id = card.getAttribute('data-id');
    ensureAll().then(function() {
      // 全件を読み込むとカードが作り直されるので、ボタンも取り直す
      var c = allCards.find(function(x) { return x.id === id; });
      if (!c) return;
      if (!lastFiltered.length) ivApply();
      var b = c.el.querySelector('.iv-rel-btn');
      var ul = document.createElement('ul');
      ul.className = 'iv-rel-list';
      var items = relatedOf(c);
      if (!items.length) {
        var none = document.createElement('li');
        none.textContent = '見つかりませんでした';
        ul.appendChild(none);
      }
      items.forEach(function(r) {
        var li = document.createElement('li');
        var date = document.createElement('span');
        date.className = 'iv-rel-date';
        date.textContent = r.d.el.querySelector('.iv-date').firstChild.textContent;  // 表示用の日付(粒度そのまま)
        var src = r.d.el.querySelector('.iv-title a') || r.d.el.querySelector('.iv-arc a');
        var title = document.createElement(src ? 'a' : 'span');
        title.textContent = r.d.title;
        if (src) {
          title.href = src.href;
          title.target = '_blank';
          title.rel = 'noopener noreferrer';
        }
        var why = document.createElement('span');
        why.className = 'iv-rel-why';
        why.textContent = r.why;
        li.appendChild(date); li.appendChild(title); li.appendChild(why);
        ul.appendChild(li);
      });
      b.parentNode.appendChild(ul);
      b.setAttribute('aria-expanded', 'true');
    });
  }

  function cardInfo(el) {
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
  }

  // 全件(data/interview-cards.json)を一度だけ読み込んでカードを差し替える
  function ensureAll() {
    if (loaded) return Promise.resolve();
    if (loading) return loading;
    var count = document.getElementById('interview-count');
    count.textContent = '読み込み中…';
    loading = fetch('/data/interview-cards.json')
      .then(function(r) { return r.json(); })
      .then(function(data) {
        container.innerHTML = (data.cards || []).join('');
        container.classList.add('iv-paged');   // ここから先は表示分だけ見せる
        loaded = true;
        scanCards();
      })
      .catch(function() {
        count.textContent = '記事一覧を読み込めませんでした';
      })
      .then(function() { loading = null; });
    return loading;
  }

  // 全文検索の結果(記事IDの集合)。null = 検索未適用(サーバ絞り込み無効)
  var serverIds = null;
  // そのうち本文で一致したID / タイトル・連載名・媒体名・メンバー名で一致したID(カードの印に使う)
  var hitBody = null, hitMeta = null;
  var searchSeq = 0;   // 古いレスポンスを無視するためのトークン
  // カードの連載名・媒体名を押したときの絞り込み(値の完全一致)。'' = 無効
  var chipFilters = { feature: '', media: '' };
  var CHIP_LABEL = { feature: '連載・企画', media: '媒体' };

  // 検索はEnter(または検索ボタン)で実行する。入力のたびには走らせない
  function ivSearchKey(e) {
    if (e.key === 'Enter') { e.preventDefault(); ivSearch(); }
  }

  function ivSearch() {
    var q = document.getElementById('iv-search').value.trim();
    if (q.length < 2) { serverIds = hitBody = hitMeta = null; ivFilter(); return; }
    var mySeq = ++searchSeq;
    ensureAll();
    fetch('/api/interview-search?q=' + encodeURIComponent(q))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (mySeq !== searchSeq) return;          // 新しい入力が来ていたら破棄
        serverIds = new Set(data.ids || []);
        hitBody = data.body ? new Set(data.body) : null;
        hitMeta = data.meta ? new Set(data.meta) : null;
        ivFilter();
      })
      .catch(function() {                          // API不通時はクライアント絞り込みに退避
        if (mySeq !== searchSeq) return;
        ensureAll().then(function() {             // 全件を読み終えてから部分一致を取る
          serverIds = hitMeta = clientFallback(q);
          hitBody = null;
          ivApply();
        });
      });
  }

  // フォールバック: タイトル・媒体・メンバー(data-search)への部分一致
  function clientFallback(q) {
    q = q.toLowerCase();
    var s = new Set();
    allCards.forEach(function(c) { if (c.search.indexOf(q) !== -1) s.add(c.id); });
    return s;
  }

  // 一度に出すカードの数。全部(1200件超)を並べるとスマホで重いので少しずつ出す
  var PAGE = 20;
  var shown = PAGE;            // 今表示している件数
  var lastFiltered = [];       // 絞り込み後のカード(並び替え済み)

  // lastFiltered の先頭 shown 件だけを表示する
  function ivRender() {
    allCards.forEach(function(c) { c.el.classList.remove('iv-shown'); });
    var view = lastFiltered.slice(0, shown);
    view.forEach(function(c) {
      c.el.classList.add('iv-shown');
      setHitLabel(c);
      container.appendChild(c.el);
    });

    var total = lastFiltered.length;
    document.getElementById('interview-count').textContent =
      total ? view.length + ' 件を表示 / ' + total + ' 件中'
            : '該当なし / 全' + allCards.length + ' 件';

    var rest = total - view.length;
    var wrap = document.getElementById('iv-more-wrap');
    wrap.hidden = rest <= 0;
    if (rest > 0) {
      document.getElementById('iv-more').textContent = '次の' + Math.min(PAGE, rest) + '件を表示';
      var all = document.getElementById('iv-more-all');
      all.textContent = '残り' + rest + '件をすべて表示';
      all.hidden = rest <= PAGE;
    }
  }

  // 検索中は、本文とタイトル等のどちらで一致したかをカードに書く(本文そのものは出さない)
  function setHitLabel(c) {
    var old = c.el.querySelector('.iv-hit');
    if (old) old.remove();
    if (serverIds === null) return;
    var inBody = hitBody && hitBody.has(c.id), inMeta = hitMeta && hitMeta.has(c.id);
    if (!inBody && !inMeta) return;
    var p = document.createElement('p');
    p.className = 'iv-hit';
    p.textContent = inBody && inMeta ? '本文・タイトル等に一致' : inBody ? '本文に一致' : 'タイトル等に一致';
    if (inMeta) p.title = 'タイトル等 = タイトル・連載名・媒体名・メンバー名';
    c.el.insertBefore(p, c.el.querySelector('.iv-rel'));
  }

  function ivMore() {
    if (!loaded) {   // 初期表示(新着20件)からの「次」は、まず全件を読み込む
      ensureAll().then(function() { ivApply(); shown = PAGE * 2; ivRender(); });
      return;
    }
    shown += PAGE;
    ivRender();
  }

  function ivMoreAll() {
    ensureAll().then(function() {
      if (!lastFiltered.length) ivApply();
      shown = lastFiltered.length;
      ivRender();
    });
  }

  // 操作の入口。全件をまだ読んでいなければ先に読み込む
  function ivFilter() {
    ensureAll().then(ivApply);
  }

  function ivApply() {
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

    lastFiltered = filtered;
    shown = PAGE;     // 絞り込みが変わったら1ページ目に戻す
    ivRender();

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
    var rel = e.target.closest('.iv-rel-btn');
    if (rel) { toggleRelated(rel); return; }
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
      if (loaded && !c.el.classList.contains('iv-shown')) return;   // 表示中のものだけ
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
  function initDateRange() {
    var dates = allCards.map(function(c) { return c.date; })
                        .filter(function(d) { return d; }).sort();
    if (!dates.length) return;
    var lo = dates[0], hi = dates[dates.length - 1];
    ['iv-from', 'iv-to'].forEach(function(id) {
      var el = document.getElementById(id);
      el.min = lo; el.max = hi;
    });
  }

  // 初期表示: HTMLに入っている新着20件をそのまま見せるだけ(全件は読まない)
  var TOTAL = parseInt(container.getAttribute('data-total') || '0', 10);
  scanCards();
  document.getElementById('interview-count').textContent =
    '新着 ' + allCards.length + ' 件を表示 / 全' + (TOTAL || allCards.length) + ' 件';
  document.getElementById('iv-more-wrap').hidden = false;
  document.getElementById('iv-more').textContent = '次の' + PAGE + '件を表示';
  document.getElementById('iv-more-all').textContent = '残りをすべて表示';

  // 件数のページ(hello-articles-stats)から ?year=2024 / ?media=媒体名 で来たら、その絞り込みで開く
  var params = new URLSearchParams(location.search);
  var qYear = params.get('year') || '', qMedia = params.get('media') || '';
  if (/^\d{4}$/.test(qYear)) {
    document.getElementById('iv-from').value = qYear + '-01-01';
    document.getElementById('iv-to').value = qYear + '-12-31';
    ivDateToggle();   // 期間パネルを開いて、何で絞っているか見せる
  }
  if (qMedia) {
    chipFilters.media = qMedia;
    renderChips();
  }
  if (/^\d{4}$/.test(qYear) || qMedia) ivFilter();

  window.ivFilter = ivFilter;
  window.ivGroupChange = ivGroupChange;
  window.ivSearch = ivSearch;
  window.ivSearchKey = ivSearchKey;
  window.ivDateToggle = ivDateToggle;
  window.ivDateClear = ivDateClear;
  window.ivOpenAll = ivOpenAll;
  window.ivMore = ivMore;
  window.ivMoreAll = ivMoreAll;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
