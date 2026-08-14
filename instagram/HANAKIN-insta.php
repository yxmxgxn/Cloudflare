<?php
  $title = "宮本佳林 Instagramチャレンジまとめ（HANAKIN・シャニカマー45°）";
  $description = "宮本佳林さんがInstagram・TikTokで投稿している「HANAKINコラボ」と「シャニカマー45°チャレンジ」をまとめました。日付・投稿・コラボ相手（公式Instagramへのリンク付き）を一覧にしています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

  // ヘッダー用の単色SVGアイコン(本文の行はPythonが同じアイコンを出力)
  $icon_ig = '<svg class="sns-icon" role="img" aria-label="Instagram" viewBox="-90 -90 180 180" xmlns="http://www.w3.org/2000/svg"><rect x="-80" y="-80" width="160" height="160" rx="36" fill="none" stroke="currentColor" stroke-width="7"/><circle cx="0" cy="0" r="42" fill="none" stroke="currentColor" stroke-width="7"/><circle cx="0" cy="0" r="22" fill="none" stroke="currentColor" stroke-width="5"/><circle cx="50" cy="-50" r="10" fill="currentColor"/></svg>';
  $icon_tt = '<svg class="sns-icon" role="img" aria-label="TikTok" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M16.5 3c.3 2.1 1.7 3.8 3.8 4.1v2.6c-1.2 0-2.4-.4-3.4-1v5.9c0 3.3-2.7 6-6 6s-6-2.7-6-6 2.7-6 6-6c.3 0 .6 0 .9.1v2.7c-.3-.1-.6-.2-.9-.2-1.8 0-3.3 1.5-3.3 3.3s1.5 3.3 3.3 3.3 3.3-1.4 3.3-3.2V3h2.3z"/></svg>';
?>

<main>
  <h1>宮本佳林 Instagramチャレンジまとめ</h1>
  宮本佳林さんが<a href="https://www.instagram.com/karin_miyamoto.official/" target="_blank" rel="noopener noreferrer">Instagram</a>・<a href="https://www.tiktok.com/@karin_miyamoto?lang=ja-JP" target="_blank" rel="noopener noreferrer">TikTok</a>で投稿している企画をまとめました。<br>
  HANAKINコラボとシャニカマー45°チャレンジです。<br>
  日付・投稿へのリンク・コラボ相手の名前はその方の公式Instagramにリンクしています。<br>
  一人でも特別な会場の投稿は含めています。

<h2 id="hanakin">HANAKINコラボ</h2>
<div class="hanakin-wrap">
<table class="hanakin-table">
<thead>
<tr>
<th>日付</th>
<th><?php echo $icon_ig; ?></th>
<th><?php echo $icon_tt; ?></th>
<th>場所</th>
<th>コラボ相手</th>
<th>グループ</th>
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
<th><?php echo $icon_ig; ?></th>
<th><?php echo $icon_tt; ?></th>
<th>コラボ相手</th>
<th>グループ</th>
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
