<?php
  $title = "宮本佳林 ハロ！ステ アプカミ GREEN ROOM アイキャッチまとめ";
  $description = "宮本佳林さんがハロ！ステ・アプカミ・GREEN ROOMで一言セリフのアイキャッチで言ったセリフと、リンク先の動画の時間をまとめました。";
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
  <h1>宮本佳林 ハロ！ステ アプカミ アイキャッチまとめ</h1>
  <p>宮本佳林さんが<a href="https://www.youtube.com/helloprojectstation" target="_blank" rel="noopener noreferrer">ハロ！ステ</a>・<a href="https://www.youtube.com/upcomi" target="_blank" rel="noopener noreferrer">アプカミ</a>・<a href="https://www.youtube.com/girlsnightoutch" target="_blank" rel="noopener noreferrer">GREEN ROOM</a>で一言セリフを言うアイキャッチ映像をまとめました。<br>
  このページは本数が多いので、サムネイルを押すとYouTubeが新しいタブで開きます（アイキャッチの場面から始まります）。</p>

  <?php // 本数が多いページなので、その場で再生せずYouTubeを開く ?>
  <?= video_cards([
    ['id' => '-lcFepEEpxQ', 'start' => 1394, 'date' => '2015/6/3', 'title' => 'ハロ！ステ#120', 'sub' => '「おかえり」'],
    ['id' => 'BvauQt6xxMc', 'start' => 2377, 'date' => '2015/7/22', 'title' => 'ハロ！ステ#127', 'sub' => '「すごく好き」'],
    ['id' => 'jEirlTcb7Jk', 'date' => '2015/10/14', 'title' => 'ハロ！ステ#139', 'sub' => '「すごい幸せ」'],
    ['id' => 'ckYajd2hbGc', 'date' => '2016/1/7', 'title' => 'GREEN ROOM#36', 'sub' => '「待ってたよ」'],
    ['id' => 'DLd9NzNd9zw', 'start' => 869, 'date' => '2016/2/10', 'title' => 'ハロ！ステ#155', 'sub' => '「すごーい幸せ」'],
    ['id' => 'BRDr4mOnIYc', 'date' => '2016/3/23', 'title' => 'ハロ！ステ#161', 'sub' => '「おかえり」'],
    ['id' => '0d8nhu_mRFc', 'date' => '2016/6/22', 'title' => 'ハロ！ステ#174', 'sub' => '「ファイト！」'],
    ['id' => 'dncVrc_vSGM', 'start' => 3436, 'date' => '2016/7/27', 'title' => 'ハロ！ステ#179', 'sub' => '「きょうはあっついねえ」'],
    ['id' => 'vhZXsMRMrbs', 'start' => 581, 'date' => '2016/8/24', 'title' => 'ハロ！ステ#183', 'sub' => '「ありがとう」'],
    ['id' => 'hHpOHkgrx9g', 'start' => 3004, 'date' => '2016/9/21', 'title' => 'ハロ！ステ#187', 'sub' => '「すごーい幸せ」'],
    ['id' => 'bfoogf5i6N8', 'date' => '2016/10/19', 'title' => 'ハロ！ステ#191', 'sub' => '「ありがとう」'],
    ['id' => 'qWqzzGUMMCg', 'start' => 1739, 'date' => '2016/12/14', 'title' => 'ハロ！ステ#199', 'sub' => '「おかえり」'],
    ['id' => 'Vh63VNvbPn0', 'start' => 619, 'date' => '2017/1/11', 'title' => 'ハロ！ステ#202', 'sub' => '「すごーく好き」'],
    ['id' => '1GPVTGPdlds', 'start' => 334, 'date' => '2017/3/8', 'title' => 'ハロ！ステ#210', 'sub' => '「寒いね。」'],
    ['id' => 'rcobxXVGLfg', 'start' => 1998, 'date' => '2017/5/17', 'title' => 'ハロ！ステ#219', 'sub' => '♪Wonderful World(Juice=Juice)'],
    ['id' => 'wgpRd7n7EoQ', 'start' => 2271, 'date' => '2017/6/14', 'title' => 'ハロ！ステ#223', 'sub' => '「頼りになるのはー 君だけだよ」'],
    ['id' => 'Mplth0DFKq0', 'date' => '2017/7/19', 'title' => 'ハロ！ステ#228', 'sub' => '「いい天気だねー どこか 遊び行こっか」'],
    ['id' => 'YxOdGE3VSPE', 'date' => '2017/8/30', 'title' => 'ハロ！ステ#234', 'sub' => '「きょうも一日お疲れ様 あしたも頑張ろうね」'],
    ['id' => 'fStTHVc3U58', 'start' => 3097, 'date' => '2017/9/27', 'title' => 'ハロ！ステ#238', 'sub' => '「ありがとう 楽しみにしてるね」'],
    ['id' => '3WlXVnLllEU', 'start' => 3023, 'date' => '2017/10/11', 'title' => 'ハロ！ステ#240', 'sub' => '「頼りになるのは 君だけだよ」'],
    ['id' => 'NLjc0xYKgJc', 'start' => 1321, 'date' => '2019/4/4', 'title' => 'アプカミ#131', 'sub' => '「私はネコが好き あなたは何が好き？」'],
    ['id' => 'O6STcc5SHJQ', 'start' => 1458, 'date' => '2019/8/22', 'title' => 'アプカミ#151', 'sub' => '「笑顔で頑張っていこう」'],
    ['id' => 'QLOcnnYr4pY', 'start' => 4, 'date' => '2019/11/20', 'title' => 'ハロ！ステ#316', 'sub' => '「ありがとう」'],
    ['id' => 'AicAdH538RQ', 'start' => 1880, 'date' => '2020/2/13', 'title' => 'アプカミ#175', 'sub' => '「いつも見てくれてありがとう」'],
    ['id' => 'cmcxP08NOfk', 'start' => 1209, 'date' => '2020/4/2', 'title' => 'アプカミ#182', 'sub' => '「続きもいっしょに見ようね」'],
    ['id' => '--zPZQdGol0', 'start' => 598, 'date' => '2020/8/20', 'title' => 'アプカミ#186', 'sub' => '「今日もいいことあるといいね」'],
    ['id' => 'iKDrnDYa0CU', 'start' => 1511, 'date' => '2020/9/23', 'title' => 'ハロ！ステ#343', 'sub' => '「ずっと応援してるからね」'],
    ['id' => 'DUu4rPiT8Yo', 'start' => 900, 'date' => '2020/11/11', 'title' => 'ハロ！ステ#350', 'sub' => '「きっといいことあるよ」'],
    ['id' => '-qcRKWzQ1-U', 'start' => 1874, 'date' => '2020/11/16', 'title' => 'アプカミ#200', 'sub' => '「どんな時でも 応援してるよ がんばれ」'],
    ['id' => 'rrfuqAZPaVQ', 'start' => 2538, 'date' => '2020/12/9', 'title' => 'ハロ！ステ#354', 'sub' => '「ありがとう また明日」'],
  ], ['embed' => false]) ?>
</main>
<div class="last-update">最終更新日：2026/05/10</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
