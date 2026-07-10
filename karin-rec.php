<?php
  $title = "宮本佳林レコーディングまとめ"; 
  $description = "宮本佳林さんがレコーディングしている映像をまとめました。";
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
  <h1>宮本佳林レコーディングまとめ</h1>
  宮本佳林さんがレコーディングしている映像をまとめました
  <ul>
    <li><a href="https://www.youtube.com/watch?v=nrhoRmo0ex0&t=1578s"  target="_blank" rel="noopener noreferrer">ロマンスの途中</a></li>
    <li><a href="https://www.youtube.com/watch?v=0kwKWqjVjl4&t=1694s"  target="_blank" rel="noopener noreferrer">GIRLS BE AMBITIOUS</a></li>
    <li><a href="https://www.youtube.com/watch?v=o-3dDfO22xg&t=2108s"  target="_blank" rel="noopener noreferrer">Vivid Midnight  ボーカル・レコーディング</a></li>
    <li><a href="https://www.youtube.com/watch?v=VDJeYD7ws3Q&t=123s"  target="_blank" rel="noopener noreferrer">Good bye & Good luck！</a></li>
    <li><a href="https://www.youtube.com/watch?v=eumWx7L4hsI&t=790s"  target="_blank" rel="noopener noreferrer">ひとりで生きられそう」って それってねえ、褒めているの？</a></li>
    <li><a href="https://www.youtube.com/watch?v=1z67fz0hBcg&t=248s"  target="_blank" rel="noopener noreferrer">25歳永遠説</a></li>
    <li><a href="https://www.youtube.com/watch?v=WDmscFbmk48&t=701s"  target="_blank" rel="noopener noreferrer">ポップミュージック</a></li>
    <li><a href="https://www.youtube.com/watch?v=orG30w79LbY&t=1437s"  target="_blank" rel="noopener noreferrer">続いていくSTORY(Symphonic Version feat. Karin)</a></li>
    <li><a href="https://www.youtube.com/watch?v=-KeieTh1sRQ&t=1279s"  target="_blank" rel="noopener noreferrer">未来のフィラメント</a></li>
  </ul>
<div class="last-update">最終更新日：2026/05/17</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>