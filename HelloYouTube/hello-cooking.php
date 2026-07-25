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
  <select id="ck-group" onchange="ckFilter()">
    <option value="">すべてのグループ</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_groups.php'; ?>
  </select>
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
  <select id="ck-subcat" onchange="ckFilter()" style="display:none">
    <option value="">すべての種別</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_subcats.php'; ?>
  </select>
  <select id="ck-cook" onchange="ckFilter()">
    <option value="">すべての講師</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_cooks.php'; ?>
  </select>
  <span id="cooking-count"></span>
</div>
<div id="cooking-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/cooking_cards.php'; ?>
</div>

<section class="ck-cooks">
  <h2>講師紹介</h2>
  <ul>
    <li>宇部先生（元パシフィックヘブンシェフ）</li>
    <li>黒木二郎 料理長（麻布十番 京寿々）</li>
    <li>長澤宜久<br>
      2007年 千代田区・丸の内「resonance」のシェフに就任。<br>
      フランスのエスプリに溢れた、オリジナル料理の数々に多くのファンが訪れている。<br>
      2008年ソムリエ資格取得。（ハロ！ステ#246より）</li>
  </ul>
</section>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/cooking_generated.php'; ?>

</main>
<script>
(function() {
  var container = document.getElementById('cooking-cards');
  var allCards = Array.from(container.querySelectorAll('.ck-card')).map(function(el) {
    return {
      el: el,
      groups: (el.getAttribute('data-groups') || '').split('|').filter(Boolean),
      cook: el.getAttribute('data-cook') || '',
      genres: (el.getAttribute('data-genres') || '').split('|').filter(Boolean),
      categories: (el.getAttribute('data-categories') || '').split('|').filter(Boolean),
      types: (el.getAttribute('data-types') || '').split('|').filter(Boolean),
      subcats: (el.getAttribute('data-subcats') || '').split('|').filter(Boolean),
      search: (el.getAttribute('data-search') || '').toLowerCase(),
    };
  });

  function ckFilter() {
    var q = document.getElementById('ck-search').value.toLowerCase();
    var gr = document.getElementById('ck-group').value;
    var g = document.getElementById('ck-genre').value;
    var c = document.getElementById('ck-category').value;
    var ck = document.getElementById('ck-cook').value;
    var typeSel = document.getElementById('ck-type');
    var subSel = document.getElementById('ck-subcat');

    // デザートを選ぶと右のフィルタを食材→種別(SubCat)に切り替える
    var dessert = (c === 'デザート');
    typeSel.style.display = dessert ? 'none' : '';
    subSel.style.display = dessert ? '' : 'none';
    if (dessert) { typeSel.value = ''; } else { subSel.value = ''; }
    var t = typeSel.value;
    var sc = subSel.value;

    var shown = 0;
    allCards.forEach(function(card) {
      var ok =
        (!q || card.search.indexOf(q) !== -1) &&
        (!gr || card.groups.indexOf(gr) !== -1) &&
        (!g || card.genres.indexOf(g) !== -1) &&
        (!c || card.categories.indexOf(c) !== -1) &&
        (!t || card.types.indexOf(t) !== -1) &&
        (!sc || card.subcats.indexOf(sc) !== -1) &&
        (!ck || card.cook === ck);
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
