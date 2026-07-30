<?php
  $title = "宮本佳林 HANAKINチャレンジ まとめ";
  $description = "宮本佳林さんがInstagram・TikTokで「HANAKINチャレンジ」としてハロー！プロジェクトのメンバーや各地で撮影した投稿をまとめました。日付・投稿・コラボ相手（公式Instagramへのリンク付き）・場所を一覧にしています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>宮本佳林 HANAKINチャレンジ まとめ</h1>
  宮本佳林さんが<a href="https://www.instagram.com/karin_miyamoto.official/" target="_blank" rel="noopener noreferrer">Instagram</a>・TikTokで投稿している「HANAKINチャレンジ」をまとめました。<br>
  日付・投稿へのリンク・コラボ相手・特別な会場（@）を一覧にしています。コラボ相手の名前はその方の公式Instagramにリンクしています。<br>

<div id="hanakin-wrap">
<table id="hanakin-table">
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
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/hanakin_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
