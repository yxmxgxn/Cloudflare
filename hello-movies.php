<?php
  $title = "ハロー！プロジェクトメンバー出演映画"; 
  $description = "ハロー！プロジェクトのメンバーが出演している映画作品を一覧でまとめました。公開日（上映日）や監督、ジャンルといった基本情報はもちろん、各映画情報サイトへのリンクやYouTubeで見られる予告編動画も網羅しています。";
  $jsonld_type = "Dataset";
  $about = [
    ["name" => "モーニング娘。", "type" => "MusicGroup"],
    ["name" => "Berryz工房", "type" => "MusicGroup"],
    ["name" => "℃-ute", "type" => "MusicGroup"],
    ["name" => "アンジュルム", "type" => "MusicGroup"],
    ["name" => "Juice=Juice", "type" => "MusicGroup"],
    ["name" => "カントリー・ガールズ", "type" => "MusicGroup"],
    ["name" => "こぶしファクトリー", "type" => "MusicGroup"],
    ["name" => "つばきファクトリー", "type" => "MusicGroup"],
    ["name" => "BEYOOOOONDS", "type" => "MusicGroup"],
    ["name" => "OCHA NORMA", "type" => "MusicGroup"],
    ["name" => "ロージークロニクル", "type" => "MusicGroup"],
  ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロー！プロジェクトメンバー出演映画</h1>
  ハロー！プロジェクトのメンバーが出演している映画作品を一覧でまとめました。公開日（上映日）や監督、ジャンルといった基本情報はもちろん、各映画情報サイトへのリンクやYouTubeで見られる予告編動画も網羅しています。"<br>
  現メンバーは薄い黄色になっています<br>
  <a href="https://docs.google.com/spreadsheets/d/16gVwxCotUPFyBszkG3x3dgJt0eLx8FtCh64b8faaBA8/edit?gid=837040930#gid=837040930" target="_blank" rel="noopener noreferrer">ファイルを操作した場合はこちら（スプレッドシート）</a><br>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_filter.php'; // フィルタ実装 ?>
<div id="helloproject-table">
  <div style="overflow-x: auto;">
    <table id="hp-members">
      <thead>
        <tr>
          <th>名前</th>
          <th>グループ</th>
          <th>生年月日</th>
          <th>現年齢</th>
          <th>デビュー日</th>
          <th>卒業日</th>
          <th>卒業公演</th>
          <th>在籍日数</th>
        </tr>
      </thead>
    <tbody>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_table.php'; ?>
    </tbody>
    </table>
  </div>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/movies_generated.php'; ?>
</div>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
