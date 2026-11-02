<?php
  $title = "宮本佳林 YouTube MCまとめ";
  $description = "宮本佳林さんがM-line Music・ハロ！ステ・Girls Night Out・アプカミ・tiny tinyでMCをやってる回をまとめました。一緒にしている人も書いています。";
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
// 1カード = 1回。M-line Music は同じMCの2回で1組なので、2本まとめて1カードにする。
//   channel : 番組名(上の絞り込みの選択肢になる)
//   title   : カードの見出し(例 'M-line Music#2・#3')
//   mc      : MCの二人(tiny tinyだけMCとゲスト)
//   no      : 回(例 '#2')
//   date    : 公開日(YouTubeの公開日に合わせている)
// 本数が多いページなので、その場では再生せずYouTubeを開く(embed => false)。
// ------------------------------------------------------------------
$mc_shows = [
  ['channel' => 'M-line Music', 'title' => 'M-line Music#2・#3', 'mc' => '宮本佳林・小片リサ', 'videos' => [
    ['id' => 'pfqSQC37zIc', 'no' => '#2', 'date' => '2021/8/13'],
    ['id' => 'yeYnojDsgA4', 'no' => '#3', 'date' => '2021/8/20'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#8・#9', 'mc' => '宮本佳林・田﨑あさひ', 'videos' => [
    ['id' => 'bZdO8ppE-IA', 'no' => '#8', 'date' => '2021/9/24'],
    ['id' => 'Iv7J1M4UWGc', 'no' => '#9', 'date' => '2021/10/1'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#18・#19', 'mc' => '鈴木愛理・宮本佳林', 'videos' => [
    ['id' => 'JC0XfyZWnsk', 'no' => '#18', 'date' => '2021/12/3'],
    ['id' => 'uUHfOe1RHuU', 'no' => '#19', 'date' => '2021/12/10'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#22・#23', 'mc' => '宮本佳林・長谷川萌美', 'videos' => [
    ['id' => 'KcTqi-UCkXs', 'no' => '#22', 'date' => '2022/1/7'],
    ['id' => 'dCBeCwWNFeo', 'no' => '#23', 'date' => '2022/1/14'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#26・#27', 'mc' => '夏焼雅・宮本佳林', 'videos' => [
    ['id' => 'UiyIyCzFlq8', 'no' => '#26', 'date' => '2022/2/4'],
    ['id' => 'tvglrBFOXT4', 'no' => '#27', 'date' => '2022/2/11'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#30・#31', 'mc' => '宮本佳林・田﨑あさひ', 'videos' => [
    ['id' => 'o50So6oeRlM', 'no' => '#30', 'date' => '2022/3/4'],
    ['id' => 'JQdgkF_tFuY', 'no' => '#31', 'date' => '2022/3/11'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#36・#37', 'mc' => '夏焼雅・宮本佳林', 'videos' => [
    ['id' => '7J9j3KbD1h4', 'no' => '#36', 'date' => '2022/4/15'],
    ['id' => 'NdxbeIOsHWI', 'no' => '#37', 'date' => '2022/4/22'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#42・#43', 'mc' => '宮本佳林・小片リサ', 'videos' => [
    ['id' => 'z0zG2Py05lg', 'no' => '#42', 'date' => '2022/5/27'],
    ['id' => 'N4g2iWwMBY0', 'no' => '#43', 'date' => '2022/6/3'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#52・#53', 'mc' => '宮本佳林・小片リサ', 'videos' => [
    ['id' => 'ylw8j8bDudQ', 'no' => '#52', 'date' => '2022/8/5'],
    ['id' => 'V7xfzsXp9Ls', 'no' => '#53', 'date' => '2022/8/12'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#56・#57', 'mc' => '宮本佳林・長谷川萌美', 'videos' => [
    ['id' => 'V9xqf1y4UgI', 'no' => '#56', 'date' => '2022/9/2'],
    ['id' => '40HPuMPjB1I', 'no' => '#57', 'date' => '2022/9/9'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#60・#61', 'mc' => '夏焼雅・宮本佳林', 'videos' => [
    ['id' => 'o5Vk47KUhlk', 'no' => '#60', 'date' => '2022/9/30'],
    ['id' => 'QNsbQulWZxU', 'no' => '#61', 'date' => '2022/10/7'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#64・#65', 'mc' => '佐藤優樹・宮本佳林', 'videos' => [
    ['id' => 'nBSygUgPCto', 'no' => '#64', 'date' => '2022/10/28'],
    ['id' => 'BW_zp62yBA0', 'no' => '#65', 'date' => '2022/11/4'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#68・#69', 'mc' => '宮本佳林・小片リサ', 'videos' => [
    ['id' => 'YxOX5Q1NkWE', 'no' => '#68', 'date' => '2022/11/25'],
    ['id' => '32LEGgSAhHM', 'no' => '#69', 'date' => '2022/12/2'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#72・#73', 'mc' => '宮本佳林・小片リサ', 'videos' => [
    ['id' => 'aGPXcbgzBFQ', 'no' => '#72', 'date' => '2022/12/23'],
    ['id' => 'E4jYcxSlJgM', 'no' => '#73', 'date' => '2022/12/30'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#78・#79', 'mc' => '宮本佳林・小関舞', 'videos' => [
    ['id' => 'Hyzjywprdwo', 'no' => '#78', 'date' => '2023/2/10'],
    ['id' => 'fOuJvADoPIM', 'no' => '#79', 'date' => '2023/2/17'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#82・#83', 'mc' => '宮本佳林・稲場愛香', 'videos' => [
    ['id' => 'dZpljq8vxSQ', 'no' => '#82', 'date' => '2023/3/10'],
    ['id' => 'HrxY93v3DPI', 'no' => '#83', 'date' => '2023/3/17'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#86・#87', 'mc' => '宮本佳林・長谷川萌美', 'videos' => [
    ['id' => 'sRSK2LNcByo', 'no' => '#86', 'date' => '2023/4/7'],
    ['id' => 'odJfWR8XVao', 'no' => '#87', 'date' => '2023/4/14'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#90・#91', 'mc' => '宮本佳林・田﨑あさひ', 'videos' => [
    ['id' => '7yJvoU-6mgY', 'no' => '#90', 'date' => '2023/5/5'],
    ['id' => 'V9Slp5TRebk', 'no' => '#91', 'date' => '2023/5/12'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#96・#97', 'mc' => '夏焼雅・宮本佳林', 'videos' => [
    ['id' => 'PeJ8dQyLcDE', 'no' => '#96', 'date' => '2023/6/16'],
    ['id' => 'VdVyiDR3_QE', 'no' => '#97', 'date' => '2023/6/23'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#100・#101', 'mc' => '宮本佳林・宮崎由加', 'videos' => [
    ['id' => 'ItLBufXK_ls', 'no' => '#100', 'date' => '2023/7/14'],
    ['id' => 'U9VEl1vAlgo', 'no' => '#101', 'date' => '2023/7/21'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#106・#107', 'mc' => '宮本佳林・田﨑あさひ', 'videos' => [
    ['id' => '-IGB_XYdbWQ', 'no' => '#106', 'date' => '2023/8/25'],
    ['id' => 'EPI9YjiqgNA', 'no' => '#107', 'date' => '2023/9/1'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#116・#117', 'mc' => '宮本佳林・稲場愛香', 'videos' => [
    ['id' => '6njGwroq86Q', 'no' => '#116', 'date' => '2023/11/3'],
    ['id' => 'doV8B5rZrLA', 'no' => '#117', 'date' => '2023/11/10'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#124・#125', 'mc' => '宮本佳林・小片リサ', 'videos' => [
    ['id' => 'hssWGXiFBDY', 'no' => '#124', 'date' => '2023/12/29'],
    ['id' => 'v8ax7vZ3Qc8', 'no' => '#125', 'date' => '2024/1/12'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#136・#137', 'mc' => '宮本佳林・竹内朱莉', 'videos' => [
    ['id' => '40YU6dzsTF8', 'no' => '#136', 'date' => '2024/3/29'],
    ['id' => '33a1T3Mf2tA', 'no' => '#137', 'date' => '2024/4/5'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#142・#143', 'mc' => '宮本佳林・長谷川萌美', 'videos' => [
    ['id' => 'HBZ4wGRidww', 'no' => '#142', 'date' => '2024/5/10'],
    ['id' => 'VHLUIsEOy_M', 'no' => '#143', 'date' => '2024/5/17'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#154・#155', 'mc' => '宮本佳林・田﨑あさひ', 'videos' => [
    ['id' => 'u31Qyy7ataE', 'no' => '#154', 'date' => '2024/8/2'],
    ['id' => 'Stp8Yi4_usA', 'no' => '#155', 'date' => '2024/8/9'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#170・#171', 'mc' => '宮本佳林・稲場愛香', 'videos' => [
    ['id' => '0_RDlgkux2s', 'no' => '#170', 'date' => '2024/11/22'],
    ['id' => 'NboNPHo1DT0', 'no' => '#171', 'date' => '2024/11/29'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#178・#179', 'mc' => '宮本佳林・長谷川萌美', 'videos' => [
    ['id' => '2KMqXPf6sUw', 'no' => '#178', 'date' => '2025/1/24'],
    ['id' => 'DQI1IJSfqMk', 'no' => '#179', 'date' => '2025/1/31'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#184・#185', 'mc' => '宮本佳林・田﨑あさひ', 'videos' => [
    ['id' => '0QUyUk8CGcA', 'no' => '#184', 'date' => '2025/3/7'],
    ['id' => '7iOKt_zxgnE', 'no' => '#185', 'date' => '2025/3/14'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#190・#191', 'mc' => '宮本佳林・川嶋美楓', 'videos' => [
    ['id' => 'DkRKw7D49CM', 'no' => '#190', 'date' => '2025/4/18'],
    ['id' => 'tzuEG_CMCc4', 'no' => '#191', 'date' => '2025/4/25'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#196・#197', 'mc' => '宮本佳林・斉藤円香', 'videos' => [
    ['id' => 'mckwPzbvlbA', 'no' => '#196', 'date' => '2025/5/30'],
    ['id' => 'KbCkPfiHz6U', 'no' => '#197', 'date' => '2025/6/6'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#222・#223', 'mc' => '宮本佳林・小野田華凜', 'videos' => [
    ['id' => '-uVXvjRWUqE', 'no' => '#222', 'date' => '2025/11/28'],
    ['id' => 'JpO8c8YyMQk', 'no' => '#223', 'date' => '2025/12/5'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#239', 'mc' => '宮本佳林・豫風瑠乃', 'videos' => [
    ['id' => 'N3lSmAuD8-k', 'no' => '#238', 'date' => '2026/3/27'],   
    ['id' => 'yePXvMbtjO0', 'no' => '#239', 'date' => '2026/4/2'],
  ]],
  ['channel' => 'M-line Music', 'title' => 'M-line Music#254・#255', 'mc' => '宮本佳林・島川波菜', 'videos' => [
    ['id' => 'RZkv4troQaI', 'no' => '#254', 'date' => '2026/7/16'],
    ['id' => 'uEt_hyCxm0Q', 'no' => '#255', 'date' => '2026/7/23'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#59', 'mc' => '宮本佳林', 'videos' => [
    ['id' => '5hNnroAPYjA', 'no' => '#59', 'date' => '2014/3/19'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#60', 'mc' => '宮本佳林', 'videos' => [
    ['id' => 'd6vD1TbuI2A', 'no' => '#60', 'date' => '2014/3/26'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#77', 'mc' => '徳永千奈美・宮本佳林', 'videos' => [
    ['id' => 'Kg7mgahEl58', 'no' => '#77', 'date' => '2014/7/30'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#121', 'mc' => '宮本佳林・森戸知沙希', 'videos' => [
    ['id' => 'Ff1SN8u1mgI', 'no' => '#121', 'date' => '2015/6/10'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#153', 'mc' => '生田衣梨奈・宮本佳林', 'videos' => [
    ['id' => '9qRvW2GCCQw', 'no' => '#153', 'date' => '2016/1/27'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#180', 'mc' => '宮本佳林・佐々木莉佳子', 'videos' => [
    ['id' => 'Rp6jKq6OhTo', 'no' => '#180', 'date' => '2016/8/3'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#198', 'mc' => '宮本佳林・羽賀朱音', 'videos' => [
    ['id' => 'v4rfNYe55WA', 'no' => '#198', 'date' => '2016/12/7'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#214', 'mc' => '宮本佳林・山木梨沙', 'videos' => [
    ['id' => 'NKU7CzzAxnc', 'no' => '#214', 'date' => '2017/4/12'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#245', 'mc' => '宮本佳林・野村みな美', 'videos' => [
    ['id' => 'Vvb899za9-A', 'no' => '#245', 'date' => '2017/11/15'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#280', 'mc' => '宮本佳林', 'videos' => [
    ['id' => 'vSzkhVZRBxk', 'no' => '#280', 'date' => '2018/7/25'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#334', 'mc' => '宮本佳林・川村文乃', 'videos' => [
    ['id' => 'nF_rDO-Yldw', 'no' => '#334', 'date' => '2020/4/1'],
  ]],
  ['channel' => 'ハロ！ステ', 'title' => 'ハロ！ステ#349', 'mc' => '宮本佳林・竹内朱莉', 'videos' => [
    ['id' => 'FWuFOm-WOvQ', 'no' => '#349', 'date' => '2020/11/4'],
  ]],
  ['channel' => 'Girls Night Out', 'title' => 'Girls Night Out#11', 'mc' => '夏焼雅・宮本佳林 ゲスト：金澤朋子', 'videos' => [
    ['id' => 'sRTNJQk1e0E', 'no' => '#11', 'date' => '2016/4/7'],
  ]],
  ['channel' => 'Girls Night Out', 'title' => 'Girls Night Out#37', 'mc' => '夏焼雅・宮本佳林 ゲスト：金澤朋子', 'videos' => [
    ['id' => '19B2O7Cx7A0', 'no' => '#37', 'date' => '2016/10/6'],
  ]],
  ['channel' => 'Girls Night Out', 'title' => 'Girls Night Out#45', 'mc' => '夏焼雅・宮本佳林 ゲスト：宮崎由加', 'videos' => [
    ['id' => 'RmugBmZS9m8', 'no' => '#45', 'date' => '2016/12/1'],
  ]],
  ['channel' => 'Girls Night Out', 'title' => 'Girls Night Out#52', 'mc' => '夏焼雅・宮本佳林 ゲスト：植村あかり', 'videos' => [
    ['id' => 'yx1F_1HPCIQ', 'no' => '#52', 'date' => '2017/1/26'],
  ]],
  ['channel' => 'アプカミ', 'title' => 'アプカミ#71', 'mc' => '宮澤茉凜・宮本佳林', 'videos' => [
    ['id' => '0nmKZdPB0qA', 'no' => '#71', 'date' => '2017/6/16'],
  ]],
  ['channel' => 'アプカミ', 'title' => 'アプカミ#145', 'mc' => '宮本佳林・一岡伶奈', 'videos' => [
    ['id' => 'SLo5XPHKG9M', 'no' => '#145', 'date' => '2019/7/11'],
  ]],
  ['channel' => 'アプカミ', 'title' => 'アプカミ#193', 'mc' => '宮本佳林・工藤由愛', 'videos' => [
    ['id' => 'IFmO5bfpyaI', 'no' => '#193', 'date' => '2020/10/8'],
  ]],
  ['channel' => 'tiny tiny', 'title' => 'tiny tiny#43', 'mc' => 'まこと・加藤紀子', 'videos' => [
    ['id' => 'uxc8t4Xn47s', 'no' => '#43', 'date' => '2018/8/24'],
  ]],
  ['channel' => 'tiny tiny', 'title' => 'tiny tiny#95', 'mc' => 'まこと・加藤紀子', 'videos' => [
    ['id' => 'JA1GC32yGI0', 'no' => '#95', 'date' => '2019/8/30'],
  ]],
  ['channel' => 'tiny tiny', 'title' => 'tiny tiny#144', 'mc' => 'みつばちまき・松原健之', 'videos' => [
    ['id' => 'Y2TG9xqHOMY', 'no' => '#144', 'date' => '2020/12/4'],
  ]],
];

$mc_channels = [];
foreach ($mc_shows as $s) {
    $mc_channels[$s['channel']] = ($mc_channels[$s['channel']] ?? 0) + 1;
}

$mc_cards = array_map(fn($s) => [
    'title' => $s['title'],
    'sub' => ($s['channel'] === 'tiny tiny' ? $s['mc'] . ' ゲスト：宮本佳林' : 'MC：' . $s['mc']),
    'data' => ['channel' => $s['channel']],
    'videos' => array_map(fn($v) => $v + ['label' => $v['no'], 'note' => $v['date']], $s['videos']),
], $mc_shows);
?>

<main>
  <h1>宮本佳林 YouTube MCまとめ</h1>
  <p>宮本佳林さんが<a href="https://www.youtube.com/mlinemusic" target="_blank" rel="noopener noreferrer">M-line Music</a>・<a href="https://www.youtube.com/helloprojectstation" target="_blank" rel="noopener noreferrer">ハロ！ステ</a>・<a href="https://www.youtube.com/girlsnightoutch" target="_blank" rel="noopener noreferrer">Girls Night Out</a>・<a href="https://www.youtube.com/upcomi" target="_blank" rel="noopener noreferrer">アプカミ</a>・<a href="https://www.youtube.com/tinytinytiny" target="_blank" rel="noopener noreferrer">tiny tiny</a>でMCをやっている回をまとめました。<br>
  M-line Musicは同じMCで2回ずつなので、2回を1枚にまとめています。日付は動画の公開日です。<br>
  本数が多いので、押すとYouTubeが新しいタブで開きます。</p>

  <div class="sc-filter" id="mc-filter">
    <select id="mc-channel" aria-label="番組で絞り込む">
      <option value="">すべての番組</option>
      <?php foreach ($mc_channels as $name => $n): ?>
      <option value="<?= vc_h($name) ?>"><?= vc_h($name) ?>（<?= $n ?>件）</option>
      <?php endforeach; ?>
    </select>
    <button type="button" id="mc-reset">絞り込み解除</button>
    <span class="sc-count" id="mc-count"></span>
  </div>

  <?= song_cards($mc_cards, ['embed' => false]) ?>
</main>
<script>
(function () {
  var sel = document.getElementById('mc-channel');
  var cards = document.querySelectorAll('.sc-card');

  function filter() {
    var v = sel.value;
    var shown = 0;
    cards.forEach(function (c) {
      var ok = !v || c.dataset.channel === v;
      c.hidden = !ok;
      if (ok) shown++;
    });
    document.getElementById('mc-count').textContent = shown + ' 件 / 全' + cards.length + ' 件';
    sel.classList.toggle('is-active', sel.selectedIndex > 0);
  }

  sel.addEventListener('change', filter);
  document.getElementById('mc-reset').addEventListener('click', function () {
    sel.value = '';
    filter();
  });
  filter();
})();
</script>
<div class="last-update">最終更新日：2026/11/01</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
