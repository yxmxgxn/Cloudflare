<?php
  $title = "#今日のボカロまとめ"; 
  $description = "宮本佳林さんが宮本佳林のボカロ部にともないXでボカロ曲を紹介しているものをまとめました。日付に元ポスト、ニコニコ動画のURLやボカロPへのリンクをいれています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  // グループ単体
  // $about = ["name" => "モーニング娘。", "type" => "MusicGroup"];
  // 混在(人もグループも)
  // $about = [
  //   "宮本佳林",                                      // 文字列はPerson扱い
  //   ["name" => "Juice=Juice", "type" => "MusicGroup"],
  //   ["name" => "譜久村聖"],                          // typeなし → Person
  // ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>#今日のボカロまとめ</h1>
  宮本佳林さんが<a href="https://ch.nicovideo.jp/miyamoto-vocalo" target="_blank" rel="noopener noreferrer">宮本佳林のボカロ部</a>にともないXでボカロ曲を<a href="https://x.com/search?q=%23%E4%BB%8A%E6%97%A5%E3%81%AE%E3%83%9C%E3%82%AB%E3%83%AD%20from%3A%40karin__miyamoto&src=typed_query&f=live" target="_blank" rel="noopener noreferrer">#今日のボカロ</a>で紹介している曲をまとめました。<br>
  日付に元ポスト、ニコニコ動画のURLやボカロPへのリンクをいれています。<br>
  ニコニコのチャンネル番組で紹介された曲は<a href="/vocalo-club">宮本佳林のボカロ部 紹介曲データベース</a>にまとめています。<br>

<!-- generated: 2026-05-19T01:26:39.771Z -->
<table id="vocalo-table">
<thead>
<tr><th>ポスト日</th><th>曲名 【ボーカロイド名】</th><th>P名</th><th>投稿日</th></tr>
</thead>
<tbody>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/vocalo_table.php'; ?>
</tbody>
</table>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/vocalo_generated.php'; ?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
