<?php
  $title = "ハロプロ ファミ通ゲーム実況ch 配信アーカイブまとめ";
  $description = "ハロプロメンバー・OGがYouTube「ファミ通ゲーム実況ch」で配信したアーカイブ（番組名はハロ通LIVE→ハロ通GAMESと変遷）を一覧でまとめました。配信日・出演メンバー・プレイしたゲームごとに整理し、各動画へのリンクを掲載しています。ダイジェスト版・ショート版は含みません。";
  $jsonld_type = "Dataset";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ ファミ通ゲーム実況ch 配信アーカイブまとめ</h1>
  ハロプロメンバー・OGがYouTubeの<a href="https://www.youtube.com/@famitsugamelive" target="_blank" rel="noopener noreferrer">ファミ通ゲーム実況ch</a>で配信したアーカイブを一覧でまとめました。番組名は「ハロ通LIVE」から「ハロ通GAMES」に変わっていますが、同じチャンネルで続いている配信です。ダイジェスト版・ショート版は含みません。<br>
  各カードは、配信日とタイトル（押すと動画へ）、出演メンバー（グループ）、プレイしたゲーム（ジャンル）で構成されています。<br>
  グループ・メンバー名での絞り込み、配信日・タイトルでの並び替え、フリーワード検索（メンバー・グループ・ゲーム・ジャンル）ができます。<strong>メンバー名をクリックすると、そのメンバーで絞り込めます。</strong><br>

<div id="gamelive-controls">
  <input type="text" id="gl-search" placeholder="メンバー・グループ・ゲーム・ジャンルで絞り込み…" oninput="glFilter()">
  <select id="gl-group" onchange="glFilter()">
    <option value="">すべてのグループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/gamelive_groups.php'; ?>
  </select>
  <select id="gl-name" onchange="glFilter()">
    <option value="">すべてのメンバー</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/gamelive_names.php'; ?>
  </select>
  <select id="gl-sort" onchange="glFilter()">
    <option value="date-desc">配信日が新しい順</option>
    <option value="date-asc">配信日が古い順</option>
    <option value="title">タイトル順</option>
  </select>
  <span id="gamelive-count"></span>
</div>
<div id="gamelive-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/gamelive_cards.php'; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/gamelive_generated.php'; ?>

</main>
<script>
(function() {
  var container = document.getElementById('gamelive-cards');
  var allCards = Array.from(container.querySelectorAll('.gl-card')).map(function(el) {
    return {
      el: el,
      date: el.getAttribute('data-date') || '',
      title: el.getAttribute('data-title') || '',
      groups: (el.getAttribute('data-groups') || '').split('|'),
      names: (el.getAttribute('data-names') || '').split('|'),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });

  function glFilter() {
    var q = document.getElementById('gl-search').value.toLowerCase();
    var grFilter = document.getElementById('gl-group').value;
    var nmFilter = document.getElementById('gl-name').value;
    var sort = document.getElementById('gl-sort').value;

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

    document.getElementById('gamelive-count').textContent =
      filtered.length + ' 件 / 全' + allCards.length + ' 件';
  }

  // メンバー名クリックでそのメンバーに絞り込む
  container.addEventListener('click', function(e) {
    var target = e.target.closest('.gl-name');
    if (!target) return;
    var name = target.getAttribute('data-name') || '';
    var sel = document.getElementById('gl-name');
    // 選択肢に存在すれば設定して絞り込み
    for (var i = 0; i < sel.options.length; i++) {
      if (sel.options[i].value === name) {
        sel.value = name;
        glFilter();
        sel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        break;
      }
    }
  });

  glFilter();
  window.glFilter = glFilter;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
