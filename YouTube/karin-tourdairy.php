<?php
  $title = "宮本佳林 ツアー日記 まとめ";
  $description = "宮本佳林さんが出ているM-line Musicで放送されているツアー日記をまとめました。ツアータイトルと日記の日付・会場・一緒に動画に出演しているメンバーの名前を書いています。";
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
// ツアー => その日記の一覧。1公演 = 1カード。
//   id     : YouTubeの動画ID(URLの watch?v= の後ろ11文字)
//   start  : 再生開始位置(秒)。URLの &t=◯s の数字
//   date   : 公演の日付
//   title  : 会場
//   sub    : 一緒に出演しているメンバー
// 本数が多いページなので、その場では再生せずYouTubeを開く(embed => false)。
// ------------------------------------------------------------------
$tour_diaries = [
  '宮本佳林 LIVE 2022春～アメジスト～' => [
    ['id' => '8lnL80gPoCI', 'start' => 1310, 'date' => '2022/5/4', 'title' => '日本特殊陶業市民会館 ビレッジホール', 'sub' => '出演：宮本佳林・松原ユリヤ・小野田華凜'],
    ['id' => 'z0zG2Py05lg', 'start' => 1069, 'date' => '2022/5/22', 'title' => 'めぐろパーシモンホール', 'sub' => '出演：宮本佳林・橋田歩果・村越彩菜'],
  ],
  '宮本佳林 LIVE 2023～ヒトリトイロ～' => [
    ['id' => 'Hyzjywprdwo', 'start' => 1473, 'date' => '2023/2/5', 'title' => 'めぐろパーシモンホール', 'sub' => '出演：宮本佳林・橋田歩果・村越彩菜'],
    ['id' => 'fOuJvADoPIM', 'start' => 1008, 'date' => '2023/2/11', 'title' => 'メルパルクホール大阪', 'sub' => '出演：宮本佳林・植村葉純・川嶋美楓'],
  ],
  '宮本佳林 LIVE 2023秋～Hello! Brand new me～' => [
    ['id' => 'doV8B5rZrLA', 'start' => 1108, 'date' => '2023/10/7', 'title' => 'Zepp DiverCity', 'sub' => '出演：宮本佳林'],
  ],
  '宮本佳林 LIVE 2024春～Hello! Brand new me～ RETURNS' => [
    ['id' => 'VHLUIsEOy_M', 'start' => 649, 'date' => '2024/4/14', 'title' => '横浜Bay Hall', 'sub' => '出演：宮本佳林'],
  ],
  '宮本佳林 LIVE 2024～TWiNKLE～' => [
    ['id' => 'KvNZcPRPdjs', 'start' => 777, 'date' => '2024/12/29', 'title' => 'Zepp DiverCity', 'sub' => '出演：宮本佳林'],
  ],
  '宮本佳林 LIVE 2025～魔法のたからばこ～' => [
    ['id' => 'JpO8c8YyMQk', 'start' => 950, 'date' => '2025/11/24', 'title' => 'SOUND SHOWER ark 清水', 'sub' => '出演：宮本佳林'],
  ],
  '宮本佳林 Sweet Box 2025～Happy Valentine\'s Day!～' => [
    ['id' => 'dDXIX2en-ts', 'start' => 734, 'date' => '2025/2/16', 'title' => '第一ホテル両国「清澄」', 'sub' => '出演：宮本佳林'],
  ],
  '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～' => [
    ['id' => 'wnRSEIqhfQw', 'start' => 749, 'date' => '2026/2/15', 'title' => '第一ホテル両国「清澄」', 'sub' => '出演：宮本佳林'],
  ],
  'M-line Special 2022～My Wish～' => [
    ['id' => 'eKVQvIM6W4E', 'start' => 1207, 'date' => '2022/2/13', 'title' => '松下IMPホール（初日コメント）', 'sub' => '出演：夏焼雅・宮本佳林'],
    ['id' => 'WlIBruzMF3c', 'start' => 1038, 'date' => '2022/3/21', 'title' => '広島JMSアステールプラザ 大ホール', 'sub' => '出演：宮本佳林・植村あかり'],
    ['id' => '14RRXEjiBxg', 'start' => 1459, 'date' => '2022/6/4', 'title' => '戸田市文化会館', 'sub' => '出演：宮本佳林・段原瑠々・井上玲音'],
    ['id' => '40HPuMPjB1I', 'start' => 1536, 'date' => '2022/9/4', 'title' => '大阪メルパルクホール', 'sub' => '出演：宮本佳林・佐々木莉佳子・上國料萌衣'],
    ['id' => 'o5Vk47KUhlk', 'start' => 1611, 'date' => '2022/9/25', 'title' => '戸田市文化会館', 'sub' => '出演：佐藤優樹・宮本佳林'],
    ['id' => 'QNsbQulWZxU', 'start' => 1667, 'date' => '2022/10/2', 'title' => 'NHK大阪ホール', 'sub' => '出演：宮本佳林・工藤由愛・松永里愛'],
    ['id' => 'nBSygUgPCto', 'start' => 1652, 'date' => '2022/10/16', 'title' => '横浜ランドマークホール', 'sub' => '出演：宮本佳林・小片リサ・小関舞・佐藤優樹'],
    ['id' => 'E4jYcxSlJgM', 'start' => 1672, 'date' => '2022/11/13', 'title' => 'Zepp Haneda', 'sub' => '出演：宮本佳林・小片リサ・小関舞・佐藤優樹・稲場愛香'],
  ],
  'M-line Special 2023～Magical Wish～' => [
    ['id' => 'dZpljq8vxSQ', 'start' => 1468, 'date' => '2023/3/4', 'title' => '松下IMPホール', 'sub' => '出演：宮本佳林・松永里愛・有澤一華'],
    ['id' => 'aOu0vFEEJS4', 'start' => 1727, 'date' => '2023/3/25', 'title' => '札幌共済ホール', 'sub' => '出演：宮本佳林・稲場愛香'],
    ['id' => '7yJvoU-6mgY', 'start' => 1554, 'date' => '2023/4/28', 'title' => 'めぐろパーシモンホール 大ホール', 'sub' => '出演：宮本佳林・工藤由愛・松永里愛'],
    ['id' => 'V9Slp5TRebk', 'start' => 1367, 'date' => '2023/5/4', 'title' => '横浜ランドマークホール', 'sub' => '出演：宮本佳林・小片リサ・小関舞・勝田里奈'],
    ['id' => 'oWbQUQgGAZ0', 'start' => 1368, 'date' => '2023/2/26', 'title' => '仙台GIGS', 'sub' => '出演：夏焼雅・宮本佳林・佐々木莉佳子'],
    ['id' => '5Nue7Fv_AgE', 'start' => 1581, 'date' => '2023/7/26', 'title' => '大田区民ホール・アプリコ 大ホール', 'sub' => '出演：宮本佳林・稲場愛香・伊勢鈴蘭'],
    ['id' => 'HrSvNkdYYnQ', 'start' => 1476, 'date' => '2023/9/23', 'title' => '仙台GIGS', 'sub' => '出演：竹内朱莉・佐藤優樹・宮本佳林・浅倉樹々'],
    ['id' => 'doV8B5rZrLA', 'start' => 1363, 'date' => '2023/10/27', 'title' => 'めぐろパーシモンホール', 'sub' => '出演：夏焼雅・竹内朱莉・宮本佳林・段原瑠々'],
    ['id' => 'hssWGXiFBDY', 'start' => 1738, 'date' => '2023/12/5', 'title' => 'なかのZERO 大ホール', 'sub' => '出演：佐藤優樹・宮崎由加・宮本佳林・石田亜佑美'],
  ],
  'M-line Special 2025 Spring～move it on～' => [
    ['id' => '0QUyUk8CGcA', 'start' => 694, 'date' => '2025/3/2', 'title' => '新宿ReNY（初日コメント）', 'sub' => '出演：佐藤優樹・宮本佳林・小関舞・森戸知沙希'],
    ['id' => 'WF8yuGi99dk', 'start' => 1118, 'date' => '2025/5/5', 'title' => '仙台darwin', 'sub' => '出演：佐藤優樹・宮本佳林・小関舞・森戸知沙希'],
  ],
  'STAGE VANGUARD「悪嬢転生」' => [
    ['id' => 'q5tRVzxanOI', 'start' => 1544, 'date' => '2022/7/5', 'title' => 'COTTON CLUB', 'sub' => '出演：宮本佳林・小関舞'],
    ['id' => 'V7xfzsXp9Ls', 'start' => 410, 'date' => '2022/8/9', 'title' => 'COTTON CLUB', 'sub' => '出演：宮本佳林・小関舞'],
  ],
  'STAGE VANGUARD「悪嬢転生」リニューアル公演' => [
    ['id' => 'aqgdX44Q6ao', 'start' => 1516, 'date' => '2023/5/24', 'title' => 'COTTON CLUB', 'sub' => '出演：宮本佳林・小関舞'],
  ],
  'エムハロイベント vol.4～宮本佳林×BEYOOOOONDS/SeasoningS 平井美葉～' => [
    ['id' => '2TcTv9ujf6o', 'start' => 946, 'date' => '2025/10/28', 'title' => 'せたがやイーグレットホール', 'sub' => '出演：宮本佳林・平井美葉'],
  ],
];
?>

<main>
  <h1>宮本佳林 ツアー日記 まとめ</h1>
  <p>宮本佳林さんが出ている<a href="https://www.youtube.com/@mlinemusic" target="_blank" rel="noopener noreferrer">M-line Music</a>のツアー日記をまとめました。<br>
  ツアーごとに、日記の日付・会場・一緒に出演しているメンバーを並べています。<br>
  押すとYouTubeが新しいタブで開き、ツアー日記の場面から始まります。</p>

  <nav class="sc-toc">
    <ul>
      <?php $i = 0; foreach ($tour_diaries as $tour => $rows): $i++; ?>
      <li><a href="#tour<?= $i ?>"><?= vc_h($tour) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <?php $i = 0; foreach ($tour_diaries as $tour => $rows): $i++; ?>
  <section class="sc-section" id="tour<?= $i ?>">
    <h2><?= vc_h($tour) ?></h2>
    <?= video_cards($rows, ['embed' => false]) ?>
  </section>
  <?php endforeach; ?>
</main>
<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
