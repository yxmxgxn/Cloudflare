<?php
  $title = "宮本佳林 One on One・ONE PLUS ONEまとめ";
  $description = "宮本佳林さんのM-Line Musicのメンバー1対1(One on One)のデュオ企画、テレビ番組「ハロドリ。」にて放送されたONE PLUS ONE（デュオ企画）の特別編集版をまとめました。";
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
// 1曲 = 1カード。見出しは「曲名 / 原曲のアーティスト」。
// 同じ曲で複数のバージョン(特別編集版と1アングルバージョンなど)があるものは videos に並べる。
//   id     : YouTubeの動画ID(URLの watch?v= の後ろ11文字)
//   pair   : 歌っている二人
//   from   : 企画名
// ------------------------------------------------------------------
$oo_songs = [
  [
    'title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'artist' => 'Juice=Juice',
    'videos' => [
      ['id' => 'xNYO7S_7blk', 'pair' => '鈴木愛理 x 宮本佳林', 'from' => 'COVERS -One on One-'],
    ],
  ],
  [
    'title' => '微炭酸', 'artist' => 'Juice=Juice',
    'videos' => [
      ['id' => '4pjbpt7kLeg', 'pair' => '宮本佳林 x 稲場愛香', 'from' => 'COVERS -One on One-'],
    ],
  ],
  [
    'title' => '愛のダイビング', 'artist' => 'Juice=Juice',
    'videos' => [
      ['id' => '2IGmo89Wqes', 'pair' => '江端妃咲 x 宮本佳林', 'from' => 'ONE PLUS ONE'],
      ['id' => 'Qf8MhgrTetE', 'pair' => '江端妃咲 x 宮本佳林', 'from' => 'ハロドリ。1アングルバージョン'],
    ],
  ],
  [
    'title' => '白いTOKYO', 'artist' => 'ZYX',
    'videos' => [
      ['id' => 'U7WP-Y_N9n8', 'pair' => '宮本佳林 x 小関舞', 'from' => 'COVERS -One on One-'],
    ],
  ],
  [
    'title' => 'ほたる祭りの日', 'artist' => 'ジュリン',
    'videos' => [
      ['id' => 'Bn14XigbuyI', 'pair' => '佐藤優樹 x 宮本佳林', 'from' => 'COVERS -One on One-'],
    ],
  ],
  [
    'title' => 'レディーマーメイド', 'artist' => 'ダイヤレディー',
    'videos' => [
      ['id' => 'yyiJ4RgQSvI', 'pair' => '鈴木愛理 x 宮本佳林', 'from' => 'COVERS -One on One-'],
    ],
  ],
  [
    'title' => 'MI DA RA 摩天楼', 'artist' => 'メロン記念日',
    'videos' => [
      ['id' => 'YXxhq1Cxt3A', 'pair' => '米村姫良々 x 宮本佳林', 'from' => 'ONE PLUS ONE'],
      ['id' => 'zsZfu7R0dTs', 'pair' => '米村姫良々 x 宮本佳林', 'from' => 'ハロドリ。1アングルバージョン'],
    ],
  ],
];

// カード用に整える(見出しは「曲名 / 原曲のアーティスト」、一覧は二人の名前と企画名)
$oo_cards = array_map(fn($s) => [
    'title' => $s['title'] . ' / ' . $s['artist'],
    'videos' => array_map(fn($v) => $v + ['label' => $v['pair'], 'note' => $v['from']], $s['videos']),
], $oo_songs);
?>

<main>
  <h1>宮本佳林 One on One・ONE PLUS ONEまとめ</h1>
  <p>宮本佳林さんのM-Line Musicのメンバー1対1（One on One）のデュオ企画と、テレビ番組「ハロドリ。」で放送されたONE PLUS ONE（デュオ企画）の特別編集版をまとめました。<br>
  1曲ごとのカードに、歌っている二人と企画を並べています。一覧を押すと、カードの上でその映像が再生されます。</p>
  <p class="sc-section-desc">ハロプロ全体のデュオ企画は<a href="/HelloYouTube/oneplusone">ONE PLUS ONE・One on One まとめ</a>にまとめています。</p>

  <?= song_cards($oo_cards) ?>
</main>
<div class="last-update">最終更新日：2026/05/04</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
