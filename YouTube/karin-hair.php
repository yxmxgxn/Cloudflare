<?php
  $title = "宮本佳林ヘアアレンジまとめ";
  $description = "宮本佳林さんがハロ！ステでヘアアレンジをしたものをまとめました。植村あかりさんを宮本佳林さんがヘアアレンジするものもあります。";
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
  <h1>宮本佳林ヘアアレンジまとめ</h1>
  <p>宮本佳林さんがハロ！ステでヘアアレンジをしたものをまとめました。植村あかりさんを宮本佳林さんがヘアアレンジするものもあります。<br>
  サムネイルを押すと、ヘアアレンジのコーナーからその場で再生されます。</p>

  <?= video_cards([
    ['id' => 'tosTxi3boPo', 'start' => 1638, 'date' => '2014/2/26',  'title' => 'ハロ！ステ#56',  'sub' => '宮本佳林ヘアアレンジ'],
    ['id' => 'Sj11NLfRaJ4', 'start' => 2699, 'date' => '2015/4/15',  'title' => 'ハロ！ステ#113', 'sub' => '宮本佳林ヘアアレンジ'],
    ['id' => 'adP4eyuql4w', 'start' => 2910, 'date' => '2015/11/4',  'title' => 'ハロ！ステ#142', 'sub' => '宮本佳林ヘアアレンジ'],
    ['id' => 'sObdiFqErc4', 'start' => 2887, 'date' => '2016/5/18',  'title' => 'ハロ！ステ#169', 'sub' => '宮本佳林ヘアアレンジ'],
    ['id' => 'wEMMGUH7g34', 'start' => 3253, 'date' => '2016/12/21', 'title' => 'ハロ！ステ#200', 'sub' => '宮本佳林ヘアアレンジ'],
    ['id' => 'rkJXf6E7uso', 'start' => 2641, 'date' => '2017/2/1',   'title' => 'ハロ！ステ#205', 'sub' => '植村あかりヘアアレンジ（宮本佳林）'],
    ['id' => '-2QIniKaoi0', 'start' => 2943, 'date' => '2017/11/29', 'title' => 'ハロ！ステ#247', 'sub' => '宮本佳林ヘアアレンジ'],
  ]) ?>
</main>
<div class="last-update">最終更新日：2026/05/15</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
