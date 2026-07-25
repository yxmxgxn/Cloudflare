<?php
  $title = "ハロプロ クッキングコーナーまとめ";
  $description = "ハロー！プロジェクトのメンバーがGirls Night Out・M-line Music・ハロ！ステなどのYouTube番組で料理に挑戦したクッキングコーナーを回ごとにまとめました。作った料理・出演メンバー・講師と、動画へのリンクをまとめ、料理のジャンル・カテゴリ・食材で絞り込めます。";
  $jsonld_type = "Dataset";
  $about = [
    ["name" => "ハロー！プロジェクト", "type" => "Organization"],
  ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ クッキングコーナーまとめ</h1>
  ハロー！プロジェクトのメンバーがYouTube番組で料理に挑戦したクッキングコーナーを、回ごとにまとめました。<br>
  出演メンバー・講師・作った料理と、その回の動画へのリンクをまとめています。料理のジャンル・カテゴリ・食材（種類）で絞り込みできます。<br>

<div id="cooking-controls">
  <input type="text" id="ck-search" placeholder="メンバー・料理名で絞り込み…" oninput="ckFilter()">
  <select id="ck-genre" onchange="ckFilter()">
    <option value="">すべてのジャンル</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_genres.php'; ?>
  </select>
  <select id="ck-category" onchange="ckFilter()">
    <option value="">すべてのカテゴリ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_categories.php'; ?>
  </select>
  <select id="ck-type" onchange="ckFilter()">
    <option value="">すべての食材</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_types.php'; ?>
  </select>
  <span id="cooking-count"></span>
</div>
<div id="cooking-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_cards.php'; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/cooking_generated.php'; ?>

</main>
<script>
(function() {
  var container = document.getElementById('cooking-cards');
  var allCards = Array.from(container.querySelectorAll('.ck-card')).map(function(el) {
    return {
      el: el,
      genres: (el.getAttribute('data-genres') || '').split('|').filter(Boolean),
      categories: (el.getAttribute('data-categories') || '').split('|').filter(Boolean),
      types: (el.getAttribute('data-types') || '').split('|').filter(Boolean),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });

  function ckFilter() {
    var q = document.getElementById('ck-search').value.toLowerCase();
    var g = document.getElementById('ck-genre').value;
    var c = document.getElementById('ck-category').value;
    var t = document.getElementById('ck-type').value;

    var shown = 0;
    allCards.forEach(function(card) {
      var ok =
        (!q || card.search.indexOf(q) !== -1) &&
        (!g || card.genres.indexOf(g) !== -1) &&
        (!c || card.categories.indexOf(c) !== -1) &&
        (!t || card.types.indexOf(t) !== -1);
      card.el.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });

    document.getElementById('cooking-count').textContent = shown + ' 回 / 全' + allCards.length + ' 回';
  }

  ckFilter();
  window.ckFilter = ckFilter;
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
