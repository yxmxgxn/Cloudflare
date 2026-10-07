<?php
  $title = "宮本佳林 メイキング映像 まとめ";
  $description = "宮本佳林さんのMV撮影・YouTube動画などのメイキング映像をまとめました。時間指定をしているので該当の個所から再生できます。";
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

// ------------------------------------------------------------------
// 見出し => その中のメイキング映像。1本 = 1カード。
//   date : 動画の公開日  title : 曲名・内容  sub : メイキングの種類
// 本数が多いページなので、その場では再生せずYouTubeを開く(embed => false)。
// ------------------------------------------------------------------
$mk_sections = [
  '宮本佳林 MV撮影メイキング' => [
    ['id' => 'M2U9jhDaTqM', 'start' => 837, 'date' => '2020/12/24', 'title' => '未来のフィラメント', 'sub' => 'MV撮影メイキング'],
    ['id' => 'xiigep_9Wqc', 'start' => 839, 'date' => '2021/11/4', 'title' => 'どうして僕らにはやる気がないのか(2021)', 'sub' => 'MV撮影メイキング'],
    ['id' => '1KGj5qxlnlM', 'start' => 145, 'date' => '2021/11/18', 'title' => '氷点下', 'sub' => 'MV撮影メイキング'],
    ['id' => 'Ruu4rvbP5oA', 'start' => 177, 'date' => '2021/12/2', 'title' => '規格外のロマンス', 'sub' => 'MV撮影メイキング'],
    ['id' => 'eN-w-UQNVvk', 'start' => 185, 'date' => '2022/6/2', 'title' => 'なんてったって I Love You', 'sub' => 'MV撮影メイキング'],
    ['id' => 'aFFH9U05AwY', 'start' => 187, 'date' => '2022/6/9', 'title' => 'ハウリング', 'sub' => 'MV撮影メイキング'],
    ['id' => '9-BuBwIVmnQ', 'start' => 203, 'date' => '2023/11/9', 'title' => 'バンビーナ・バンビーノ', 'sub' => 'MV撮影メイキング'],
    ['id' => 'KBbSSuVnCRg', 'start' => 173, 'date' => '2023/11/23', 'title' => 'Lonely Bus', 'sub' => 'MV撮影メイキング'],
    ['id' => 'KbCkPfiHz6U', 'start' => 1627, 'date' => '2025/6/6', 'title' => 'キケンな魔法', 'sub' => 'MV撮影メイキング'],
    ['id' => 'MG8n0fmkOfA', 'start' => 1775, 'date' => '2025/6/13', 'title' => 'ビースト！', 'sub' => 'MV撮影メイキング'],
    ['id' => '8dEOrLVnQAU', 'start' => 1670, 'date' => '2025/6/20', 'title' => 'SUPER IDOL -Especial-(Single Ver.)', 'sub' => 'MV撮影メイキング'],
    ['id' => 'uEt_hyCxm0Q', 'start' => 772, 'date' => '2026/7/23', 'title' => 'HANAKIN', 'sub' => 'MV撮影メイキング'],
    ['id' => 'Grnf0Kbh0qE', 'start' => 995, 'date' => '2026/7/30', 'title' => 'シャニカマー', 'sub' => 'MV撮影メイキング'],
  ],
  '宮本佳林 舞台 メイキング' => [
    ['id' => 'lW-3ARXEm18', 'start' => 197, 'date' => '2022/9/8', 'title' => 'STAGE VANGUARD「悪嬢転生」', 'sub' => 'メイキング映像'],
  ],
  '宮本佳林 YouTube動画撮影 メイキング' => [
    ['id' => '5dJvQZM0h5Q', 'start' => 1028, 'date' => '2021/8/27', 'title' => '少女K 踊ってみた', 'sub' => '撮影メイキング'],
    ['id' => 'k0UKxgbaDo8', 'start' => 562, 'date' => '2021/9/3', 'title' => '優柔不断だね、Guilty 歌唱動画', 'sub' => '撮影メイキング'],
    ['id' => 'Iv7J1M4UWGc', 'start' => 1027, 'date' => '2021/10/1', 'title' => 'やっちまいな 歌唱動画', 'sub' => '撮影メイキング'],
    ['id' => 'ylw8j8bDudQ', 'start' => 874, 'date' => '2022/8/5', 'title' => 'COVERS One on One 佐藤優樹 x 宮本佳林 (ジュリン) 「ほたる祭りの日」', 'sub' => '撮影メイキング'],
    ['id' => 'GuwSjZQLdMI', 'start' => 677, 'date' => '2022/10/14', 'title' => '愛してるの言葉だけで 歌唱・イイオンナごっこ ダンス動画', 'sub' => 'メイキング'],
    ['id' => 'WOAHynr0fOU', 'start' => 1076, 'date' => '2022/10/21', 'title' => 'アコースティックスタジオライブ', 'sub' => '撮影メイキング'],
    ['id' => 'hDkrX2T-dYI', 'start' => 611, 'date' => '2024/3/1', 'title' => 'COVERS One on One 宮本佳林 × 小関舞 「白いTOKYO」', 'sub' => '撮影メイキング'],
    ['id' => 'T_GZW_myPSM', 'start' => 1418, 'date' => '2024/3/15', 'title' => 'COVERS One on One 宮本佳林 ×稲場愛香 「微炭酸」', 'sub' => '撮影メイキング'],
  ],
  'Juice=Juice MV撮影メイキング' => [
    ['id' => 'SyjZaWb4vBY', 'start' => 2057, 'date' => '2013/11/13', 'title' => 'イジワルしないで 抱きしめてよ', 'sub' => 'MV撮影メイキング'],
    ['id' => '4yTg2ptJyzU', 'start' => 2029, 'date' => '2015/7/23', 'title' => '続いていくSTORY', 'sub' => 'MV撮影メイキング'],
    ['id' => '_jWWll2vM7Y', 'start' => 1180, 'date' => '2015/7/30', 'title' => 'CHOICE & CHANCE', 'sub' => 'MV撮影メイキング'],
    ['id' => 'ju4r3PC-xEc', 'start' => 2070, 'date' => '2016/1/21', 'title' => 'カラダだけが大人になったんじゃない', 'sub' => 'MV撮影メイキング'],
    ['id' => 'c6IOPO2423Y', 'start' => 202, 'date' => '2016/10/28', 'title' => 'Dream Road～心が躍り出してる～', 'sub' => 'MV撮影メイキング'],
    ['id' => 'mpLIwucmIPw', 'start' => 190, 'date' => '2016/11/4', 'title' => 'KEEP ON 上昇志向!!', 'sub' => 'MV撮影メイキング'],
    ['id' => 'hBuSXDFZ938', 'start' => 1259, 'date' => '2016/11/11', 'title' => '明日やろうはバカやろう', 'sub' => 'MV撮影メイキング'],
    ['id' => 'zPoiE7-eCV0', 'start' => 1073, 'date' => '2017/4/19', 'title' => '地団駄ダンス', 'sub' => 'MV撮影メイキング'],
    ['id' => 'zy41AkM9kVM', 'start' => 148, 'date' => '2019/3/28', 'title' => 'Good bye & Good luck！', 'sub' => 'MV撮影メイキング'],
    ['id' => 'NLjc0xYKgJc', 'start' => 116, 'date' => '2019/4/4', 'title' => '微炭酸', 'sub' => 'MV撮影メイキング'],
    ['id' => 'JX6JYsdnHyI', 'start' => 306, 'date' => '2020/3/26', 'title' => 'ポップミュージック', 'sub' => 'MV撮影メイキング'],
    ['id' => 'EcOri7ZVbKM', 'start' => 191, 'date' => '2020/4/16', 'title' => '好きって言ってよ', 'sub' => 'MV撮影メイキング'],
  ],
];
?>

<main>
  <h1>宮本佳林 メイキング映像 まとめ</h1>
  <p>宮本佳林さんのMV撮影・YouTube動画などのメイキング映像をまとめました。<br>
  日付は動画の公開日です。押すとYouTubeが新しいタブで開き、メイキングの場面から始まります。</p>

  <?php foreach ($mk_sections as $name => $rows): ?>
  <section class="sc-section">
    <h2><?= vc_h($name) ?></h2>
    <?= video_cards($rows, ['embed' => false]) ?>
  </section>
  <?php endforeach; ?>
</main>
<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
