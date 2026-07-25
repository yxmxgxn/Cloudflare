<?php
  $title = "ハロープロジェクト卒業公演・在籍日数"; 
  $description = "ハロープロジェクトのメンバーの・卒業公演の場所・デビュー日・卒業日・在籍日数を、現メンバーも含めてまとめました";
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
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロープロジェクト卒業公演・在籍日数</h1>
  ハロープロジェクトのメンバーの・卒業公演の場所・デビュー日・卒業日・在籍日数を、現メンバーも含めてまとめました。在籍日数と現年齢は自動で更新されます<br>
  現メンバーは薄い黄色になっています<br>
  <a href="https://docs.google.com/spreadsheets/d/16gVwxCotUPFyBszkG3x3dgJt0eLx8FtCh64b8faaBA8/edit?gid=837040930#gid=837040930" target="_blank" rel="noopener noreferrer">ファイルを操作した場合はこちら（スプレッドシート）</a><br>
  スプレッドシートは私が管理のために書いたID順になっています。在室日数や年齢などはPythonで計算しているのでソートできません。<br>
  ソートさせるときBのグループNo、CのソートID、DのサブIDでソートしてください。グループ結成順で並んで、その中に加入と、同じ中では生年月日での公式とおそらく同じ順に並びます。<br>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/members_filter.php'; // フィルタ実装 ?>
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
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/members_table.php'; ?>
    </tbody>
    </table>
  </div>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/members_generated.php'; ?>
</div>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
