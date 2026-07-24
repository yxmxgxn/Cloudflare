<?php
  $title = "エムハロイベントまとめ";
  $description = "M-lineメンバーとハロー！プロジェクト現役メンバーが共演するM-line club×Hello! Project合同企画「エムハロイベント」を回ごとにまとめました。開催日・会場・開演時間・セットリスト（ToMoKo）と、公開されているYouTube動画へのリンクをまとめています。";
  $jsonld_type = "Dataset";
  $about = [
    ["name" => "M-line Club", "type" => "Organization"],
    ["name" => "ハロー！プロジェクト", "type" => "Organization"],
  ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>エムハロイベントまとめ</h1>
  M-lineメンバーとハロー！プロジェクト現役メンバーが共演するM-line club×Hello! Project合同企画「エムハロイベント」を回ごとにまとめました。<br>
  開催日・会場・公演ごとの開演時間をまとめ、非公式サイトのハロプロセトリ検索システム「ToMoKo」にセットリストが公開されているものはリンクを貼っています。<br>
  各回でYouTubeに公開されている動画があるものは、その下にリンクをまとめています。<br>

<div id="mhello-cards">
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/mhello_cards.php'; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/mhello_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
