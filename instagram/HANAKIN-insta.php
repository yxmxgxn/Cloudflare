<?php
  $title = "宮本佳林 Instagramチャレンジまとめ（HANAKIN・シャニカマー45°）";
  $description = "宮本佳林さんがInstagram・TikTokで投稿している「HANAKINコラボ」と「シャニカマー45°チャレンジ」をまとめました。日付・投稿・コラボ相手（公式Instagramへのリンク付き）を一覧にしています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>宮本佳林 Instagramチャレンジまとめ</h1>
  宮本佳林さんが<a href="https://www.instagram.com/karin_miyamoto.official/" target="_blank" rel="noopener noreferrer">Instagram</a>・<a href="https://www.tiktok.com/@karin_miyamoto?lang=ja-JP" target="_blank" rel="noopener noreferrer">TikTok</a>で投稿しているチャレンジ企画をまとめました。<br>
  日付・投稿へのリンク・コラボ相手の名前はその方の公式Instagramにリンクしています。<br>
  一人でも特別な会場の投稿は含めています。

<h2 id="hanakin">HANAKINコラボ</h2>
<div class="hanakin-wrap">
<table class="hanakin-table">
<thead>
<tr>
<th>日付</th>
<th>Instagram</th>
<th>TikTok</th>
<th>コラボ相手</th>
<th>場所</th>
</tr>
</thead>
<tbody>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/hanakin_table.php'; ?>
</tbody>
</table>
</div>

<h2 id="shanikama">シャニカマー45°チャレンジ</h2>
<div class="hanakin-wrap">
<table class="hanakin-table">
<thead>
<tr>
<th>日付</th>
<th>Instagram</th>
<th>TikTok</th>
<th>コラボ相手</th>
<th>場所</th>
</tr>
</thead>
<tbody>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/shanikama_table.php'; ?>
</tbody>
</table>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/hanakin_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
