<?php
  $title = "宮本佳林 ファミ通ゲーム実況chまとめ";
  $description = "宮本佳林さんがファミ通ゲーム実況chでゲーム配信したアーカイブをまとめました。動画のリンクはファミ通ゲーム実況chの動画に飛びます。";
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
  <h1>宮本佳林 ファミ通ゲーム実況chまとめ</h1>
  <p>宮本佳林さんが<a href="https://www.youtube.com/@famitsugamelive" target="_blank">ファミ通ゲーム実況ch</a>でゲーム配信したアーカイブをまとめました。<br>
  サムネイルを押すとその場で再生されます。<br>
  <a href="/HelloYouTube/hello-gamelive">ハロプロ ファミ通ゲーム実況ch 配信アーカイブまとめ</a>では他のメンバーの配信もまとめています。</p>

  <?= video_cards([
    [
      'id' => 'Nd5Sfcs4Zpk', 'start' => 109, 'date' => '2019/11/05',
      'title' => '【ハロ通LIVE】Juice=Juice 金澤朋子＆宮本佳林がゲーム実況やってみた【マリオメーカー2 ほか】',
      'sub' => ['一緒に配信：金澤朋子', 'ゲーム：スーパーマリオメーカー2・完全爆弾解除マニュアル：Keep Talking and Nobody Explodes・オーバークック スペシャルエディション'],
    ],
    [
      'id' => '6tfLl838P8E', 'date' => '2022/10/07',
      'title' => '宮本佳林の職権濫用！公私混同!! 推しといっしょにゲーム配信',
      'sub' => ['一緒に配信：竹内朱莉', 'ゲーム：Fall Guys・スーパーバニーマン・プロジェクトセカイ カラフルステージ！ feat. 初音ミク'],
    ],
    [
      'id' => 'vQeoEYzryqo', 'start' => 147, 'date' => '2025/02/27',
      'title' => '【Apex Legends】「宮本佳林の職権濫用！公私混同!!」いつか推しをキャリーしたい！',
      'sub' => ['ゲーム：Apex Legends'],
    ],
    [
      'id' => '-1q5ijP6t6k', 'start' => 303, 'date' => '2026/05/12',
      'title' => '譜久村聖・宮本佳林・小関舞の「ハロ通GAMES」協力山登りゲーをプレイ【みんなのリズム天国/PEAK】',
      'sub' => ['一緒に配信：譜久村聖・小関舞', 'ゲーム：みんなのリズム天国・PEAK'],
    ],
  ]) ?>
</main>
<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
