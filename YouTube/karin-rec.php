<?php
  $title = "宮本佳林レコーディングまとめ";
  $description = "ハロ！ステやM-line Musicで配信された、宮本佳林さんがレコーディングしている映像をまとめました。Juice=Juice時代だけでなく、ソロ曲も含まれています。";
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
  <h1>宮本佳林レコーディングまとめ</h1>
  <p>ハロ！ステやM-line Musicで配信された、宮本佳林さんがレコーディングしている映像をまとめました。Juice=Juice時代だけでなく、ソロ曲も含まれています。<br>
  日付は動画の公開日です。押すとYouTubeが新しいタブで開き、レコーディングの場面から始まります。</p>

  <?php // 曲名がカードの見出し。その場では再生せずYouTubeを開く ?>
  <?= video_cards([
    ['id' => 'nrhoRmo0ex0', 'start' => 1578, 'date' => '2013/8/21', 'title' => 'ロマンスの途中'],
    ['id' => '0kwKWqjVjl4', 'start' => 1694, 'date' => '2015/7/17', 'title' => 'GIRLS BE AMBITIOUS'],
    ['id' => 'o-3dDfO22xg', 'start' => 2108, 'date' => '2018/4/6', 'title' => 'Vivid Midnight', 'sub' => 'ボーカル・レコーディング'],
    ['id' => 'VDJeYD7ws3Q', 'start' => 123, 'date' => '2019/4/11', 'title' => 'Good bye & Good luck！'],
    ['id' => '1z67fz0hBcg', 'start' => 248, 'date' => '2019/6/20', 'title' => '25歳永遠説'],
    ['id' => 'eumWx7L4hsI', 'start' => 790, 'date' => '2019/10/24', 'title' => '「ひとりで生きられそう」って それってねえ、褒めているの？'],
    ['id' => 'WDmscFbmk48', 'start' => 701, 'date' => '2020/8/5', 'title' => 'ポップミュージック'],
    ['id' => 'orG30w79LbY', 'start' => 1437, 'date' => '2020/12/3', 'title' => '続いていくSTORY(Symphonic Version feat. Karin)'],
    ['id' => '-KeieTh1sRQ', 'start' => 1279, 'date' => '2021/1/7', 'title' => '未来のフィラメント'],
    ['id' => 'xgWhnyyrvPc', 'start' => 645, 'date' => '2026/8/6', 'title' => 'HANAKIN'],
  ], ['embed' => false]) ?>
</main>
<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
