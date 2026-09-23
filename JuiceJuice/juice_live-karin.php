<?php
  $title = "宮本佳林 Juice=Juiceライブ映像まとめ";
  $description = "宮本佳林さんのJuice=Juice時代のライブの映像をまとめました。ハロ！ステなどからリンクしています。";
  $jsonld_type = "Dataset";
  $about = [
    "宮本佳林",
    ["name" => "Juice=Juice", "type" => "MusicGroup"],
  ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/common/video_cards.php';

// ------------------------------------------------------------------
// 1曲 = 1カード。同じ曲の映像は videos に何本でも並べられる。
//   title  : 曲名
//   sub    : Juice=Juiceの曲でないときだけ、元のグループ名(省略可)
//   id     : YouTubeの動画ID(URLの watch?v= の後ろ11文字)
//   start  : 再生開始位置(秒)。URLの &t=◯s の数字。頭からなら省略
//   label  : 公演の日付と会場
// 並びはスプレッドシートで並べた順・日時順。
// ------------------------------------------------------------------
$jj_songs = [
  ['title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'AzUqqlRmc_0', 'start' => 410, 'label' => 'LIVE TOUR 2019～Con Amor～ 目線で疑似体験'],
    ['id' => 'THzsLHhdKmc', 'start' => 34, 'label' => '2020/6/17 日本武道館'],
    ['id' => 'fnTdb-CW6C8', 'start' => 256, 'label' => '2020/7/13 上野学園ホール'],
    ['id' => 'yicB9ptACCo', 'start' => 354, 'label' => 'New Vocal Ver. 2020/1/4 中野サンプラザ'],
    ['id' => '5VofGX2mB2w', 'start' => 45, 'label' => 'New Vocal Ver. 2020/12/10 日本武道館 -Digest-'],
    ['id' => 't6fWdElyxVs', 'start' => 325, 'label' => 'New Vocal Ver. 2020/12/31 中野サンプラザ'],
  ]],
  ['title' => '25歳永遠説', 'videos' => [
    ['id' => 'J7i9s17-WHw', 'start' => 698, 'label' => '2020/4/27 ハーモニーホール座間'],
  ]],
  ['title' => 'Borderline', 'videos' => [
    ['id' => 'dRLyIXD8zGA', 'start' => 1346, 'label' => '2020/3/30 幕張メッセ'],
    ['id' => 'FSff9JgH2pA', 'start' => 367, 'label' => '2020/4/1 無観客ミニライブ'],
  ]],
  ['title' => 'Ça va ? Ça va ?', 'videos' => [
    ['id' => 'gb-TnWVxn88', 'start' => 1384, 'label' => '2017/2 新宿ReNY'],
    ['id' => '9APF0fLxCSQ', 'start' => 247, 'label' => '2017/10/10 Zepp DiverCity'],
  ]],
  ['title' => 'CHOICE & CHANCE', 'videos' => [
    ['id' => '68VtJ5d9PkM', 'start' => 2455, 'label' => '2015/06/21 横浜Bay Hall'],
    ['id' => 'UMfWBZ7-Rw8', 'start' => 1718, 'label' => '2020/10/10 マイナビBLITZ赤坂'],
  ]],
  ['title' => 'Dream Road～心が躍り出してる～', 'videos' => [
    ['id' => 'Rp6jKq6OhTo', 'start' => 594, 'label' => '2016/07/30 ニトリ文化ホール'],
    ['id' => 'FgecS6FityI', 'start' => 42, 'label' => '2016/10/10 川口総合文化センター'],
  ]],
  ['title' => 'Fiesta! Fiesta!', 'videos' => [
    ['id' => 'eJbhWgyOsRg', 'start' => 302, 'label' => '2017/7/15 オリックス劇場'],
    ['id' => 'g5ptCAjST7w', 'start' => 285, 'label' => '2017/8/31 横浜ベイホール'],
  ]],
  ['title' => 'GIRLS BE AMBITIOUS', 'videos' => [
    ['id' => 'z77NTW2ibyM', 'start' => 1333, 'label' => 'GIRLS BE AMBITIOUS 2015夏ハロコン 日本特殊陶業市民会館'],
  ]],
  ['title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'zBrio8u91Ek', 'start' => 410, 'label' => 'GIRLS BE AMBITIOUS 2017 2017/11/20 日本武道館'],
  ]],
  ['title' => 'Goal～明日はあっちだよ～', 'videos' => [
    ['id' => 'CAeajVBgSws', 'start' => 852, 'label' => '2016/10/22 松山サロンキティ'],
    ['id' => '-zL_BCApmOI', 'start' => 1504, 'label' => '2017/5/3 中野サンプラザ'],
    ['id' => 'vXjymScSxM0', 'start' => 313, 'label' => '2020/10/10 マイナビBLITZ赤坂'],
  ]],
  ['title' => 'KEEP ON 上昇志向!!', 'videos' => [
    ['id' => 'vhZXsMRMrbs', 'start' => 694, 'label' => '2016/05/4 中野サンプラザ'],
    ['id' => 'kYnQxjC3dtk', 'start' => 1594, 'label' => '2018/2/1 日郡山Hip Shot Japan'],
  ]],
  ['title' => 'Magic of Love', 'videos' => [
    ['id' => 'YRLiEIfdm7w', 'start' => 369, 'label' => '2014/5/10 ディファ有明'],
  ]],
  ['title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'NbBkOY4CqZU', 'start' => 1879, 'label' => 'J=J 2015Ver. 2020/12/10 日本武道館'],
  ]],
  ['title' => 'Never Never Surrender', 'videos' => [
    ['id' => 'THa8XikNcy0', 'start' => 331, 'label' => '2017/11/20 日本武道館'],
    ['id' => '-eu-DP9O_uo', 'start' => 1899, 'label' => '2017/11/11大分 DRUM Be-0'],
  ]],
  ['title' => 'TOKYOグライダー', 'videos' => [
    ['id' => '-eu-DP9O_uo', 'start' => 1702, 'label' => '2017/11/11 大分 DRUM Be-0'],
    ['id' => 'uW8NYvFIrSo', 'start' => 1613, 'label' => '2017/11/20 日本武道館'],
  ]],
  ['title' => 'Va-Va-Voom', 'videos' => [
    ['id' => 'FSff9JgH2pA', 'start' => 1222, 'label' => '2020/4/1 無観客ミニライブ'],
  ]],
  ['title' => 'Vivid Midnight', 'videos' => [
    ['id' => 'QZeB1vyGomE', 'start' => 1214, 'label' => '2020/4/21TFTホール'],
  ]],
  ['title' => 'Wonderful World', 'videos' => [
    ['id' => 'Sj11NLfRaJ4', 'start' => 767, 'label' => '2015/4/8 池袋サンシャイン噴水広場'],
    ['id' => 'Whlxz_hH5Uc', 'start' => 722, 'label' => '2015/05 中野サンプラザ'],
    ['id' => '5VofGX2mB2w', 'start' => 267, 'label' => '2020/12/31 中野サンプラザ -Digest-'],
  ]],
  ['title' => 'あばれてっか？！ハブアグッタイ', 'videos' => [
    ['id' => 'JgifpHf29yQ', 'start' => 894, 'label' => '2017/2 新宿ReNY'],
  ]],
  ['title' => 'イジワルしないで 抱きしめてよ', 'videos' => [
    ['id' => 'JF0hz8lsHTs', 'start' => 389, 'label' => '2013/11/17 小樽GOLDSTONE'],
    ['id' => 'xkZLpUwzAng', 'start' => 366, 'label' => '2013/11/28 モーニング娘。ツアーファイナル 日本武道館'],
    ['id' => 'ONTflT4Rqtk', 'start' => 170, 'label' => '2014/06/13 大阪FANJ-twice'],
    ['id' => 'NDsLJ-ywuqA', 'start' => 1796, 'label' => '2020/9//1 新宿ReNY'],
  ]],
  ['title' => 'カラダだけが大人になったんじゃない', 'videos' => [
    ['id' => 'DLd9NzNd9zw', 'start' => 1468, 'label' => '2015/11/30 川崎CLUB CITTA\''],
  ]],
  ['title' => 'がんばれないよ', 'videos' => [
    ['id' => 'pLeJTEvAhiQ', 'start' => 111, 'label' => '2020/12/2 日本武道館 -Digest-'],
    ['id' => 'PtwsnUzc5Fw', 'start' => 296, 'label' => '2020/12/31 中野サンプラザ -Digest-'],
  ]],
  ['title' => 'この世界は捨てたもんじゃない', 'videos' => [
    ['id' => 'TiURorQYT3M', 'start' => 869, 'label' => '2017/2 新宿ReNY'],
  ]],
  ['title' => 'スクランブル', 'videos' => [
    ['id' => 'INFWNZCYoVU', 'start' => 2006, 'label' => '2015/10/10 江戸川区総合文化センター'],
  ]],
  ['title' => 'チクタク 私の旬', 'videos' => [
    ['id' => 'JL0hvWgEr3M', 'start' => 462, 'label' => '2015/10/21 横浜BayHall'],
    ['id' => 'BUU99oZPrZ8', 'start' => 1287, 'label' => '2020/10/10 マイナビBLITZ赤坂'],
  ]],
  ['title' => 'プラトニック・プラネット', 'videos' => [
    ['id' => 'g2c1OaSc2vw', 'start' => 2016, 'label' => '2020/12/4 国立代々木競技場 第一体育館'],
  ]],
  ['title' => 'ポップミュージック', 'videos' => [
    ['id' => '7Z3LUlOQ9uc', 'start' => 129, 'label' => '2020/3/21 Juice=Juice プレミアム 明治神宮会館'],
    ['id' => 'FSff9JgH2pA', 'start' => 903, 'label' => '2020/4/1 無観客ミニライブ'],
    ['id' => '5VofGX2mB2w', 'start' => 163, 'label' => '2020/12/31 中野サンプラザ -Digest-'],
  ]],
  ['title' => 'ロマンスの途中', 'videos' => [
    ['id' => 'VCsWlnmyMdw', 'start' => 786, 'label' => '2013/7/2 夏ハロコン オリックス劇場'],
    ['id' => 'jEirlTcb7Jk', 'start' => 1293, 'label' => '2015/10/3 河岸留言 西門紅樓展演館／大河岸 台北'],
    ['id' => 'j4Gxc2FqcY0', 'start' => 871, 'label' => '2017/10/10 Zepp DiverCity'],
    ['id' => 'FWuFOm-WOvQ', 'start' => 1318, 'label' => '2020/10/10 CLUB CITTA\''],
  ]],
  ['title' => '愛・愛・傘', 'videos' => [
    ['id' => '1SiDmKHHLro', 'start' => 2592, 'label' => '2015/06/21 横浜Bay Hall'],
  ]],
  ['title' => '愛のダイビング', 'videos' => [
    ['id' => 'hfrALgcKlgc', 'start' => 845, 'label' => '2016/04/16 千葉県文化会館'],
  ]],
  ['title' => '伊達じゃないよ うちの人生は', 'videos' => [
    ['id' => 'QX1FgV2cJdU', 'start' => 825, 'label' => '2014/08/31 金沢AZ'],
    ['id' => 'IWph3hNX_m0', 'start' => 842, 'label' => '2017/8/31 横浜ベイホール'],
  ]],
  ['title' => '泣いていいよ', 'videos' => [
    ['id' => '5VofGX2mB2w', 'start' => 220, 'label' => '2020/12/31 中野サンプラザ'],
  ]],
  ['title' => '禁断少女', 'videos' => [
    ['id' => '-6X5KfIXDFE', 'start' => 206, 'label' => '2019/10/10 マイナビBLITZ赤坂'],
    ['id' => 'vSzkhVZRBxk', 'start' => 1396, 'label' => '2020/7/14 オリックス劇場'],
  ]],
  ['title' => '銀色のテレパシー', 'videos' => [
    ['id' => '5BH8DpdQm4c', 'start' => 911, 'label' => '2017/2 新宿ReNY'],
    ['id' => 'NbBkOY4CqZU', 'start' => 170, 'label' => '2020/12/10 日本武道館'],
  ]],
  ['title' => '五月雨美女がさ乱れる', 'videos' => [
    ['id' => 'MQc5Qq4ytLY', 'start' => 1359, 'label' => '2013/5/5 ハロプロ研修生春の診断テスト 中野サンプラザ'],
    ['id' => 'Z6sb1yLSB-c', 'start' => 1756, 'label' => '2014-04-13 東別院ホール'],
    ['id' => 'vjCL6CVyLoQ', 'start' => 1387, 'label' => 'MEMORIAL EDIT 2014冬ハロコン オリックス劇場'],
  ]],
  ['title' => '好きって言ってよ', 'videos' => [
    ['id' => 'nF_rDO-Yldw', 'start' => 395, 'label' => '2020/3/21 Juice=Juice プレミアム 明治神宮会館'],
    ['id' => 'FSff9JgH2pA', 'start' => 1628, 'label' => '2020/4/1 無観客ミニライブ'],
    ['id' => 'ke4k00H1-kk', 'start' => 146, 'label' => '2020/12/31 中野サンプラザ'],
  ]],
  ['title' => '私が言う前に抱きしめなきゃね', 'videos' => [
    ['id' => 'rVfAYc3yxLc', 'start' => 417, 'label' => '2013/06/13 池袋サンシャインシティ'],
    ['id' => 'iY_PTZlflGw', 'start' => 1500, 'label' => '2013/3 モーニング娘。ツアー初日映像 シアターグリーン'],
    ['id' => 'DxrNrvBXTnE', 'start' => 1342, 'label' => '2013ひなフェス パシフィコ横浜'],
    ['id' => 'WELuju3bypk', 'start' => 358, 'label' => '2020/4/27 ハーモニーホール座間'],
    ['id' => 'ymqH7UMMgRM', 'start' => 1726, 'label' => '2020/6/17 日本武道館'],
  ]],
  ['title' => '初めてを経験中', 'videos' => [
    ['id' => 'yKRSD7fGI6s', 'start' => 1654, 'label' => '2013/12/03 池袋サンシャイン噴水広場'],
    ['id' => 'k_gAQR6uLrc', 'start' => 1691, 'label' => '2014冬ハロコン 中野サンプラザ'],
  ]],
  ['title' => '生まれたてのBaby Love', 'videos' => [
    ['id' => 'BvauQt6xxMc', 'start' => 1192, 'label' => '2015/7/15 東京ドームシティラクーア'],
    ['id' => 'Vh63VNvbPn0', 'start' => 2329, 'label' => '2017/1 冬ハロコン'],
    ['id' => 'nfdCl2yy1DA', 'start' => 550, 'label' => '2020/8/25 中野サンプラザ'],
    ['id' => '34RMkF6QcnM', 'start' => 1403, 'label' => '2018/2/1 郡山Hip Shot Japan'],
  ]],
  ['title' => '選ばれし私達', 'videos' => [
    ['id' => 'S6MuWrxnx4A', 'start' => 775, 'label' => '2016/04/21 新宿ReNY'],
    ['id' => 'v4rfNYe55WA', 'start' => 1190, 'label' => '2016/11/7 日本武道館'],
  ]],
  ['title' => '素直に甘えて', 'videos' => [
    ['id' => 'pwPWvwn5wH8', 'label' => '2018/08/01 パレットプラザ'],
  ]],
  ['title' => '続いていくSTORY', 'videos' => [
    ['id' => 'Ff1SN8u1mgI', 'start' => 1174, 'label' => '2015/05/06 中野サンプラザ'],
    ['id' => '0d8nhu_mRFc', 'start' => 737, 'label' => '2016/05/4 中野サンプラザ'],
    ['id' => 'THzsLHhdKmc', 'start' => 1446, 'label' => '2020/6/17 日本武道館'],
    ['id' => 'NbBkOY4CqZU', 'start' => 1559, 'label' => 'Symphonic Version feat. Karin 2020/12/10 日本武道館'],
  ]],
  ['title' => '大人の事情', 'videos' => [
    ['id' => '_x6IzhQyzE0', 'start' => 546, 'label' => 'ひなフェス2016 パシフィコ横浜'],
  ]],
  ['title' => '地団駄ダンス', 'videos' => [
    ['id' => '-Mg97DP6rCk', 'start' => 277, 'label' => '2017/4/26 池袋サンシャインシティ噴水広場'],
    ['id' => '58blJtfLq2U', 'start' => 1687, 'label' => '2020/7/30 OTODAMA SEA STUDIO'],
    ['id' => 'lg3jhCBWjCY', 'start' => 43, 'label' => 'ひなフェス2017 幕張メッセ 国際展示場 1ホール'],
  ]],
  ['title' => '如雨露', 'videos' => [
    ['id' => '0nmKZdPB0qA', 'start' => 2008, 'label' => '2017/5/3 中野サンプラザ'],
    ['id' => 'ojbV3VhoEoo', 'start' => 1002, 'label' => '2016/6 横浜Bayhall'],
  ]],
  ['title' => '背伸び', 'videos' => [
    ['id' => 'QX1FgV2cJdU', 'start' => 1168, 'label' => '2014/08/31 金沢AZ'],
  ]],
  ['title' => '微炭酸', 'videos' => [
    ['id' => '5VofGX2mB2w', 'start' => 195, 'label' => '2020/12/31 中野サンプラザ -Digest-'],
    ['id' => 'ORWrIh77MpE', 'start' => 1702, 'label' => '2020/2/5 横浜ベイホール 最前列特等席映像'],
  ]],
  ['title' => '風に吹かれて', 'videos' => [
    ['id' => '5VofGX2mB2w', 'start' => 145, 'label' => '2020/12/31 中野サンプラザ -Digest-'],
    ['id' => 'Vln2vJ57LVk', 'start' => 278, 'label' => '2020/2/3 新宿ReNY'],
  ]],
  ['title' => '未来へ、さあ走り出せ！', 'videos' => [
    ['id' => 'ZXN_qKfqMFQ', 'start' => 2017, 'label' => '2015/09/26 新宿ReNY'],
  ]],
  ['title' => '明日やろうはバカやろう', 'videos' => [
    ['id' => 'WnKIivHT0Ag', 'start' => 1562, 'label' => '2016/05/4 中野サンプラザ'],
  ]],
  ['title' => '裸の裸の裸のKISS', 'videos' => [
    ['id' => '164hpigiHK8', 'start' => 2208, 'label' => '2014/12/6 Zepp TOKYO'],
    ['id' => 'd6vD1TbuI2A', 'start' => 743, 'label' => '2014/3/21 池袋サンシャイン噴水広場'],
    ['id' => 'GmNV5_AMWt8', 'start' => 1145, 'label' => '2016/6 横浜Bay Hall'],
    ['id' => '6goOm_nnK1M', 'start' => 993, 'label' => '2020/5/4 中野サンプラザ'],
  ]],
  ['title' => '天まで登れ！', 'sub' => 'ハロプロ研修生 feat. Juice=Juice', 'videos' => [
    ['id' => 'pw55c84GAWk', 'start' => 304, 'label' => '2015/5/19 日比谷野外音楽堂'],
    ['id' => 'tosTxi3boPo', 'start' => 1282, 'label' => '2014/2/23 Zepp Tokyo'],
  ]],
  ['title' => '彼女になりたいっ!!!', 'sub' => 'ハロプロ研修生 feat. Juice=Juice', 'videos' => [
    ['id' => 'GGUSZVUUAHc', 'start' => 1069, 'label' => '2014/2/23 Zepp Tokyo'],
  ]],
  ['title' => 'Danceでバコーン！', 'sub' => '℃-ute', 'videos' => [
    ['id' => 'Z6sb1yLSB-c', 'start' => 1857, 'label' => '2014-04-13 東別院ホール'],
  ]],
  ['title' => '恋愛ハンター', 'sub' => 'モーニング娘。', 'videos' => [
    ['id' => 'j55OifHPTSc', 'start' => 94, 'label' => '2020/3/20 明治神宮会館'],
  ]],
  ['title' => '香水', 'sub' => 'メロン記念日', 'videos' => [
    ['id' => '3lrMCnvjbK4', 'start' => 2330, 'label' => '2015/08/19 柏PALOOZA'],
  ]],
];
?>

<main>
  <h1>宮本佳林 Juice=Juiceライブ映像まとめ</h1>
  <p>宮本佳林さんのJuice=Juice時代のライブ映像をまとめました。ハロ！ステなどからリンクしています。<br>
  1曲ごとのカードに公演を並べています。一覧を押すと、カードの上でその映像が再生されます。</p>
  <p class="sc-section-desc">日付・会場は各動画の概要欄や動画タイトルを参考にしているため、誤りが含まれている可能性があります。</p>

  <div class="sc-filter">
    <input type="search" id="jj-search" placeholder="曲名・会場でしぼりこむ" aria-label="曲名・会場でしぼりこむ">
    <button type="button" id="jj-reset">解除</button>
    <span class="sc-count" id="jj-count"></span>
  </div>

  <?= song_cards($jj_songs) ?>
</main>
<script>
(function () {
  var box = document.getElementById('jj-search');
  var cards = document.querySelectorAll('.sc-card');

  function filter() {
    var q = box.value.trim().toLowerCase();
    var shown = 0;
    cards.forEach(function (c) {
      var ok = !q || c.textContent.toLowerCase().indexOf(q) !== -1;
      c.hidden = !ok;
      if (ok) shown++;
    });
    document.getElementById('jj-count').textContent = shown + ' 曲 / 全' + cards.length + ' 曲';
  }

  box.addEventListener('input', filter);
  document.getElementById('jj-reset').addEventListener('click', function () {
    box.value = '';
    filter();
  });
  filter();
})();
</script>
<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
