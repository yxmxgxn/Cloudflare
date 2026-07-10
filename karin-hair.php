<?php
  $title = "宮本佳林ヘアアレンジまとめ"; 
  $description = "宮本佳林さんがハロ！ステでヘアアレンジをしたものをまとめました。";
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
  <h1>宮本佳林ヘアアレンジまとめ</h1>
  宮本佳林さんがハロ！ステでヘアアレンジをしたものをまとめました
  <ul>
    <li><a href="https://www.youtube.com/watch?v=tosTxi3boPo&t=1638s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#56 2014/2/26</a></li>
    <li><a href="https://www.youtube.com/watch?v=Sj11NLfRaJ4&t=2699s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#113 2015/4/15</a></li>
    <li><a href="https://www.youtube.com/watch?v=adP4eyuql4w&t=2910s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#142 2015/11/4</a></li>
    <li><a href="https://www.youtube.com/watch?v=sObdiFqErc4&t=2887s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#169 2016/5/18</a></li>
    <li><a href="https://www.youtube.com/watch?v=wEMMGUH7g34&t=3253s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#200 2016/12/21</a></li>
    <li><a href="https://www.youtube.com/watch?v=-2QIniKaoi0&t=2943s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#247 2017/11/29</a></li>
    <li><a href="https://www.youtube.com/watch?v=rkJXf6E7uso&t=2641s"  target="_blank" rel="noopener noreferrer">ハロ！ステ#205 植村あかり 2017/2/1</a></li>
  </ul>
  全部をまとめたものをYouTubeにアップしました<br>
  <iframe 
    width="560" 
    height="315" 
    src="https://www.youtube.com/embed/lQ1iS4XyUF4" 
    frameborder="0" 
    allowfullscreen>
  </iframe>
<div class="last-update">最終更新日：2026/05/15</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>