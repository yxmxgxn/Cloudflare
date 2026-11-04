<?php
  $title = "ハロカバまとめ（ハロプロ配信限定カバー 第1〜6弾）";
  $description = "ハロー!プロジェクトの配信限定カバー「ハロカバ」（2010年12月〜2011年3月、第1〜6弾・全50録音）を、曲・カバーしたメンバー・配信日ごとにまとめ、レコチョク・mora・iTunesへのリンクを一覧にしています。";
  $jsonld_type = "Dataset";
  $about = ["name" => "ハロー!プロジェクト", "type" => "Organization"];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロカバまとめ</h1>
  「ハロカバ」は、ハロー!プロジェクトのメンバーがハロプロの人気曲をカバーした、配信限定のシリーズです。<br>
  2010年12月から2011年3月まで、第1弾〜第6弾で全50録音が配信されました。<br>
  配信サービスごとのリンクは、今開けるものだけ載せています。空欄は、そのサービスで見つからなかったものです。

<div class="hanakin-wrap">
<table class="hanakin-table hc-table">
<thead>
<tr>
<th>曲名</th>
<th>原曲</th>
<th>カバー</th>
<th>レコチョク</th>
<th>mora</th>
<th>iTunes</th>
<th>弾（配信日）</th>
</tr>
</thead>
<tbody>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/hellocover_table.php'; ?>
</tbody>
</table>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/hellocover_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
