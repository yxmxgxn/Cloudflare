<?php
  $title = "宮本佳林 ハロ！ステダンス部まとめ";
  $description = "宮本佳林さんがハロ！ステダンス部に所属していた時の映像をまとめました。時間指定をしているので、そこから再生可能です。";
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
  require_once $_SERVER['DOCUMENT_ROOT'] . '/common/video_cards.php';
?>

<main>
  <h1>宮本佳林 ハロ！ステダンス部まとめ</h1>
  <p>宮本佳林さんがハロ！ステダンス部に所属していた時の映像をまとめました。<br>
  サムネイルを押すと、ダンス部のコーナーからその場で再生されます。</p>

  <?= video_cards([
    ['id' => 'JVUuaG5Ptj8', 'start' => 1256, 'title' => 'ハロ！ステ#48'],
    ['id' => 'YRLiEIfdm7w', 'start' => 857,  'title' => 'ハロ！ステ#67'],
    ['id' => 'nksQ7VhQx40', 'start' => 780,  'title' => 'ハロ！ステ#68'],
    ['id' => 'ONTflT4Rqtk', 'start' => 998,  'title' => 'ハロ！ステ#73'],
    ['id' => 'JAnwztGYEqY', 'start' => 1865, 'title' => 'ハロ！ステ#83',  'sub' => 'ハロコン'],
    ['id' => '4QKYVoiiHDM', 'start' => 1956, 'title' => 'ハロ！ステ#114', 'sub' => '前編'],
    ['id' => 'DghX_lIGTZg', 'start' => 1864, 'title' => 'ハロ！ステ#115', 'sub' => '後編'],
    ['id' => 'KhFlliQIYCY', 'start' => 2007, 'title' => 'ハロ！ステ#132', 'sub' => 'レッスン前編'],
    ['id' => 'ISoN-YmjTqg', 'start' => 903,  'title' => 'ハロ！ステ#133', 'sub' => 'レッスン後編'],
    ['id' => 'ISoN-YmjTqg', 'start' => 1342, 'title' => 'ハロ！ステ#133', 'sub' => '本番'],
  ]) ?>
</main>
<div class="last-update">最終更新日：2026/05/17</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
