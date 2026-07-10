<?php
  $title = "宮本佳林 ファミ通ゲーム実況chまとめ"; 
  $description = "宮本佳林さんがファミ通ゲーム実況chでゲーム配信したアーカイブをまとめました。";
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
  <h1>宮本佳林 ファミ通ゲーム実況chまとめ</h1>
  宮本佳林さんが<a href="https://www.youtube.com/@famitsugamelive" target="_blank" rel="noopener noreferrer">ファミ通ゲーム実況ch</a>でゲーム配信したアーカイブをまとめました。<br>
  （）が一緒に配信したメンバーです
  <ul>
    <li><a href="https://www.youtube.com/watch?v=Nd5Sfcs4Zpk&t=109s" target="_blank" rel="noopener noreferrer">【ハロ通LIVE】Juice=Juice 金澤朋子＆宮本佳林がゲーム実況やってみた【マリオメーカー2 ほか】（金澤朋子）</a></li>
    <li><a href="https://www.youtube.com/watch?v=6tfLl838P8E" target="_blank" rel="noopener noreferrer">宮本佳林の職権濫用！公私混同!! 推しといっしょにゲーム配信（竹内朱莉）</a></li>
    <li><a href="https://www.youtube.com/watch?v=vQeoEYzryqo&t=147s" target="_blank" rel="noopener noreferrer">【Apex Legends】「宮本佳林の職権濫用！公私混同!!」いつか推しをキャリーしたい！</a></li>
    <li><a href="https://www.youtube.com/watch?v=-1q5ijP6t6k" target="_blank" rel="noopener noreferrer">譜久村聖・宮本佳林・小関舞の「ハロ通GAMES」協力山登りゲーをプレイ【みんなのリズム天国/PEAK】（譜久村聖・宮本佳林・小関舞）</a></li>
  </ul>
</main>
<div class="last-update">最終更新日：2026/05/04</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
