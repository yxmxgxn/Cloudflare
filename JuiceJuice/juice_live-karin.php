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
//   note   : 公演名(と、Ver.などの補足)。文字列でも配列でも可
// 並びはスプレッドシートで並べた順・日時順。
// ------------------------------------------------------------------
$jj_songs = [
  ['title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'AzUqqlRmc_0', 'start' => 410, 'label' => '2019/9/23 横浜Bay Hall', 'note' => ['目線で疑似体験', 'Juice=Juice LIVE TOUR 2019〜Con Amor〜']],
    ['id' => 'THzsLHhdKmc', 'start' => 34, 'label' => '2019/6/17 日本武道館', 'note' => 'ハロプロ プレミアム Juice=Juice CONCERT TOUR 2019 〜JuiceFull!!!!!!!〜 FINAL 宮崎由加卒業スペシャル'],
    ['id' => 'fnTdb-CW6C8', 'start' => 256, 'label' => '2019/7/13 上野学園ホール', 'note' => 'Hello! Project 2019 SUMMER 「beautiful」'],
    ['id' => 'yicB9ptACCo', 'start' => 354, 'label' => '2020/1/4 中野サンプラザ', 'note' => ['New Vocal Ver.', 'Hello! Project 2020 Winter HELLO! PROJECT IS [　　　　　] 〜side A〜']],
    ['id' => '5VofGX2mB2w', 'start' => 45, 'label' => '2020/12/10 日本武道館', 'note' => ['New Vocal Ver. -Digest-', 'Juice=Juice コンサート2020 〜続いていくSTORY〜 宮本佳林卒業スペシャル']],
    ['id' => 't6fWdElyxVs', 'start' => 325, 'label' => '2020/12/31 中野サンプラザ', 'note' => ['New Vocal Ver.', 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜']],
  ]],
  ['title' => '25歳永遠説', 'videos' => [
    ['id' => 'J7i9s17-WHw', 'start' => 698, 'label' => '2019/4/27 ハーモニーホール座間 大ホール', 'note' => 'Juice=Juice CONCERT TOUR 2019 〜JuiceFull!!!!!!!〜'],
  ]],
  ['title' => 'Borderline', 'videos' => [
    ['id' => 'dRLyIXD8zGA', 'start' => 1346, 'label' => '2019/3/30 幕張メッセ国際展示場 1・2ホール', 'note' => 'Hello! Project 20th Anniversary!! Hello! Project ひなフェス 2019 Hello! Project 20th Anniversary!! プレミアム'],
    ['id' => 'FSff9JgH2pA', 'start' => 367, 'label' => '2020/4/1 無観客ミニライブ', 'note' => 'Juice=Juice 13thシングル「ポップミュージック/好きって言ってよ」発売記念イベント'],
  ]],
  ['title' => 'Ça va ? Ça va ?', 'videos' => [
    ['id' => 'gb-TnWVxn88', 'start' => 1384, 'label' => '2017/2/2 新宿ReNY', 'note' => 'Juice=Juice LIVE AROUND 2017 〜NEXT ONE〜'],
    ['id' => '9APF0fLxCSQ', 'start' => 247, 'label' => '2017/10/10 Zepp DiverCity', 'note' => 'Juice=Juice LIVE AROUND 2017 〜in the World→J=J Day Special〜'],
  ]],
  ['title' => 'CHOICE & CHANCE', 'videos' => [
    ['id' => '68VtJ5d9PkM', 'start' => 2455, 'label' => '2015/6/21 横浜Bay Hall', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code1→Begin to Run〜'],
    ['id' => 'UMfWBZ7-Rw8', 'start' => 1718, 'label' => '2019/10/10 マイナビBLITZ赤坂', 'note' => 'Juice=Juice LIVE TOUR 2019〜Con Amor J=J DAY SPECIAL〜'],
  ]],
  ['title' => 'Dream Road～心が躍り出してる～', 'videos' => [
    ['id' => 'Rp6jKq6OhTo', 'start' => 594, 'label' => '2016/7/30 さっぽろ芸術文化の館 ニトリ文化ホール', 'note' => 'Hello! Project 2016 SUMMER 〜 Sunshine Parade 〜'],
    ['id' => 'FgecS6FityI', 'start' => 42, 'label' => '2016/10/10 川口総合文化センター・リリア メインホール', 'note' => 'Juice=Juice LIVE MISSION 220 〜Special Code→J=J Day 2016〜'],
  ]],
  ['title' => 'Fiesta! Fiesta!', 'videos' => [
    ['id' => 'eJbhWgyOsRg', 'start' => 302, 'label' => '2017/7/15 オリックス劇場', 'note' => 'Hello! Project 2017 SUMMER 〜HELLO! MEETING〜'],
    ['id' => 'g5ptCAjST7w', 'start' => 285, 'label' => '2017/8/31 横浜Bay Hall', 'note' => 'Juice=Juice LIVE AROUND 2017 〜Seven Horizon〜'],
  ]],
  ['title' => 'GIRLS BE AMBITIOUS', 'videos' => [
    ['id' => 'z77NTW2ibyM', 'start' => 1333, 'label' => '2015/7/12 日本特殊陶業市民会館 フォレストホール', 'note' => 'Hello! Project 2015 SUMMER 〜CHALLENGER〜'],
  ]],
  ['title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'zBrio8u91Ek', 'start' => 410, 'label' => '2017/11/20 日本武道館', 'note' => ['GIRLS BE AMBITIOUS 2017', 'Juice=Juice LIVE AROUND 2017 FINAL at 日本武道館 〜Seven Squeeze!〜']],
  ]],
  ['title' => 'Goal～明日はあっちだよ～', 'videos' => [
    ['id' => 'CAeajVBgSws', 'start' => 852, 'label' => '2016/10/22 松山サロンキティ', 'note' => 'Juice=Juice LIVE MISSION 220 〜Last Code→Full Squeeze!〜'],
    ['id' => '-zL_BCApmOI', 'start' => 1504, 'label' => '2017/5/3 中野サンプラザ', 'note' => 'Juice=Juice LIVE AROUND 2017 〜NEXT ONE SPECIAL〜'],
    ['id' => 'vXjymScSxM0', 'start' => 313, 'label' => '2019/10/10 マイナビBLITZ赤坂', 'note' => 'Juice=Juice LIVE TOUR 2019〜Con Amor J=J DAY SPECIAL〜'],
  ]],
  ['title' => 'KEEP ON 上昇志向!!', 'videos' => [
    ['id' => 'vhZXsMRMrbs', 'start' => 694, 'label' => '2016/5/4 中野サンプラザ', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code3 Special→Growing Up!〜'],
    ['id' => 'kYnQxjC3dtk', 'start' => 1594, 'label' => '2018/2/11 郡山Hip Shot Japan', 'note' => 'Juice=Juice LIVE GEAR 2018 〜Go ahead〜'],
  ]],
  ['title' => 'Magic of Love', 'videos' => [
    ['id' => 'YRLiEIfdm7w', 'start' => 369, 'label' => '2014/5/10 ディファ有明', 'note' => 'ぽにきゃん!アイドル倶楽部感謝祭〜アイドルサバイバル〜'],
  ]],
  ['title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'NbBkOY4CqZU', 'start' => 1879, 'label' => '2020/12/10 日本武道館', 'note' => ['J=J 2015Ver.', 'Juice=Juice コンサート2020 〜続いていくSTORY〜 宮本佳林卒業スペシャル']],
  ]],
  ['title' => 'Never Never Surrender', 'videos' => [
    ['id' => 'THa8XikNcy0', 'start' => 331, 'label' => '2017/11/20 日本武道館', 'note' => 'Juice=Juice LIVE AROUND 2017 FINAL at 日本武道館 〜Seven Squeeze!〜'],
    ['id' => '-eu-DP9O_uo', 'start' => 1899, 'label' => '2017/11/11 大分 DRUM Be-0', 'note' => 'Juice=Juice LIVE AROUND 2017 〜Beyond the Beyond〜'],
  ]],
  ['title' => 'TOKYOグライダー', 'videos' => [
    ['id' => '-eu-DP9O_uo', 'start' => 1702, 'label' => '2017/11/11 大分 DRUM Be-0', 'note' => 'Juice=Juice LIVE AROUND 2017 〜Beyond the Beyond〜'],
    ['id' => 'uW8NYvFIrSo', 'start' => 1613, 'label' => '2017/11/20 日本武道館', 'note' => 'Juice=Juice LIVE AROUND 2017 FINAL at 日本武道館 〜Seven Squeeze!〜'],
  ]],
  ['title' => 'Va-Va-Voom', 'videos' => [
    ['id' => 'FSff9JgH2pA', 'start' => 1222, 'label' => '2020/4/1 無観客ミニライブ', 'note' => 'Juice=Juice 13thシングル「ポップミュージック/好きって言ってよ」発売記念イベント'],
  ]],
  ['title' => 'Vivid Midnight', 'videos' => [
    ['id' => 'QZeB1vyGomE', 'start' => 1214, 'label' => '2018/4/21 TFTホール1000', 'note' => 'Juice=Juice 10thシングル「SEXY SEXY/泣いていいよ/Vivid Midnight」発売記念ミニライブ＆握手会'],
  ]],
  ['title' => 'Wonderful World', 'videos' => [
    ['id' => 'Sj11NLfRaJ4', 'start' => 767, 'label' => '2015/4/8 池袋サンシャインシティ アルパB1F 噴水広場', 'note' => 'Juice=Juice 6thシングル「Wonderful World/Ça va ? Ça va ?（サヴァ サヴァ）」発売記念ミニライブ＆握手会'],
    ['id' => 'Whlxz_hH5Uc', 'start' => 722, 'label' => '2015/5/2 中野サンプラザ', 'note' => 'Juice=Juiceファーストライブツアー2015 特別編!! 〜Special Juice〜'],
    ['id' => '5VofGX2mB2w', 'start' => 267, 'label' => '2020/12/31 中野サンプラザ', 'note' => ['-Digest-', 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜']],
  ]],
  ['title' => 'あばれてっか？！ハブアグッタイ', 'videos' => [
    ['id' => 'JgifpHf29yQ', 'start' => 894, 'label' => '2017/2/2 新宿ReNY', 'note' => 'Juice=Juice LIVE AROUND 2017 〜NEXT ONE〜'],
  ]],
  ['title' => 'イジワルしないで 抱きしめてよ', 'videos' => [
    ['id' => 'JF0hz8lsHTs', 'start' => 389, 'label' => '2013/11/17 小樽GOLDSTONE', 'note' => 'ナルチカ2013秋 Berryz工房×Juice=Juice'],
    ['id' => 'xkZLpUwzAng', 'start' => 366, 'label' => '2013/11/28 日本武道館', 'note' => ['モーニング娘。ツアーファイナル', 'モーニング娘。コンサートツアー2013秋 〜 CHANCE!〜']],
    ['id' => 'ONTflT4Rqtk', 'start' => 170, 'label' => '2014/6/13 大阪FANJ-twice', 'note' => 'Juice=Juice ファーストライブツアー2014〜2015 News=News 〜各地よりお届けします!〜'],
    ['id' => 'NDsLJ-ywuqA', 'start' => 1796, 'label' => '2019/9/11 新宿ReNY', 'note' => 'Juice=Juice LIVE TOUR 2019〜Con Amor〜'],
  ]],
  ['title' => 'カラダだけが大人になったんじゃない', 'videos' => [
    ['id' => 'DLd9NzNd9zw', 'start' => 1468, 'label' => '2015/11/30 CLUB CITTA\'', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code2→NEXT to YOU〜'],
  ]],
  ['title' => 'がんばれないよ', 'videos' => [
    ['id' => 'pLeJTEvAhiQ', 'start' => 111, 'label' => '2020/12/2 日本武道館', 'note' => ['-Digest-', 'Hello! Project 2020 〜The Ballad〜 Special Number']],
    ['id' => 'PtwsnUzc5Fw', 'start' => 296, 'label' => '2020/12/31 中野サンプラザ', 'note' => ['-Digest-', 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜']],
  ]],
  ['title' => 'この世界は捨てたもんじゃない', 'videos' => [
    ['id' => 'TiURorQYT3M', 'start' => 869, 'label' => '2017/2/2 新宿ReNY', 'note' => 'Juice=Juice LIVE AROUND 2017 〜NEXT ONE〜'],
  ]],
  ['title' => 'スクランブル', 'videos' => [
    ['id' => 'INFWNZCYoVU', 'start' => 2006, 'label' => '2015/10/10 江戸川区総合文化センター 大ホール', 'note' => 'Juice=Juice LIVE MISSION 220 〜Special Code→J=J Day 2015〜'],
  ]],
  ['title' => 'チクタク 私の旬', 'videos' => [
    ['id' => 'JL0hvWgEr3M', 'start' => 462, 'label' => '2015/10/21 横浜Bay Hall', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code2→NEXT to YOU〜'],
    ['id' => 'BUU99oZPrZ8', 'start' => 1287, 'label' => '2019/10/10 マイナビBLITZ赤坂', 'note' => 'Juice=Juice LIVE TOUR 2019〜Con Amor J=J DAY SPECIAL〜'],
  ]],
  ['title' => 'プラトニック・プラネット', 'videos' => [
    ['id' => 'g2c1OaSc2vw', 'start' => 2016, 'label' => '2019/12/4 国立代々木競技場 第一体育館', 'note' => 'Juice=Juice Concert 2019 〜octopic!〜'],
  ]],
  ['title' => 'ポップミュージック', 'videos' => [
    ['id' => '7Z3LUlOQ9uc', 'start' => 129, 'label' => '2020/3/21 明治神宮会館', 'note' => 'Hello! Project ひなフェス 2020〜被災地復興支援・東北を元気に！〜 Juice=Juice プレミアム'],
    ['id' => 'FSff9JgH2pA', 'start' => 903, 'label' => '2020/4/1 無観客ミニライブ', 'note' => 'Juice=Juice 13thシングル「ポップミュージック/好きって言ってよ」発売記念イベント'],
    ['id' => '5VofGX2mB2w', 'start' => 163, 'label' => '2020/12/31 中野サンプラザ', 'note' => ['-Digest-', 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜']],
  ]],
  ['title' => 'ロマンスの途中', 'videos' => [
    ['id' => 'VCsWlnmyMdw', 'start' => 786, 'label' => '2013/7/27 オリックス劇場', 'note' => ['夏ハロコン', 'Hello! Project 2013 SUMMER COOL HELLO! 〜ソレゾーレ!〜']],
    ['id' => 'jEirlTcb7Jk', 'start' => 1293, 'label' => '2015/10/3 河岸留言 西門紅樓展演館', 'note' => 'Juice=Juice LIVE MISSION 220 in Taipei & Hong Kong'],
    ['id' => 'j4Gxc2FqcY0', 'start' => 871, 'label' => '2017/10/10 Zepp DiverCity', 'note' => 'Juice=Juice LIVE AROUND 2017 〜in the World→J=J Day Special〜'],
    ['id' => 'FWuFOm-WOvQ', 'start' => 1318, 'label' => '2020/10/10 CLUB CITTA\'', 'note' => 'Juice=Juice スペシャルイベント2020 ～10月10日はJuice=Juiceの日！～'],
  ]],
  ['title' => '愛・愛・傘', 'videos' => [
    ['id' => '1SiDmKHHLro', 'start' => 2592, 'label' => '2015/6/21 横浜Bay Hall', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code1→Begin to Run〜'],
  ]],
  ['title' => '愛のダイビング', 'videos' => [
    ['id' => 'hfrALgcKlgc', 'start' => 845, 'label' => '2016/4/16 千葉県文化会館 大ホール', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code3 Special→Growing Up!〜'],
  ]],
  ['title' => '伊達じゃないよ うちの人生は', 'videos' => [
    ['id' => 'QX1FgV2cJdU', 'start' => 825, 'label' => '2014/8/31 金沢AZ', 'note' => 'Juice=Juice ファーストライブツアー2014〜2015 News=News 〜各地よりお届けします!〜'],
    ['id' => 'IWph3hNX_m0', 'start' => 842, 'label' => '2017/8/31 横浜Bay Hall', 'note' => 'Juice=Juice LIVE AROUND 2017 〜Seven Horizon〜'],
  ]],
  ['title' => '泣いていいよ', 'videos' => [
    ['id' => '5VofGX2mB2w', 'start' => 220, 'label' => '2020/12/31 中野サンプラザ', 'note' => 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜'],
  ]],
  ['title' => '禁断少女', 'videos' => [
    ['id' => '-6X5KfIXDFE', 'start' => 206, 'label' => '2019/10/10 マイナビBLITZ赤坂', 'note' => 'Juice=Juice LIVE TOUR 2019〜Con Amor J=J DAY SPECIAL〜'],
    ['id' => 'vSzkhVZRBxk', 'start' => 1396, 'label' => '2018/7/14 オリックス劇場', 'note' => 'Hello! Project 20th Anniversary!! Hello! Project 2018 SUMMER 〜ALL FOR ONE〜'],
  ]],
  ['title' => '銀色のテレパシー', 'videos' => [
    ['id' => '5BH8DpdQm4c', 'start' => 911, 'label' => '2017/2/2 新宿ReNY', 'note' => 'Juice=Juice LIVE AROUND 2017 〜NEXT ONE〜'],
    ['id' => 'NbBkOY4CqZU', 'start' => 170, 'label' => '2020/12/10 日本武道館', 'note' => 'Juice=Juice コンサート2020 〜続いていくSTORY〜 宮本佳林卒業スペシャル'],
  ]],
  ['title' => '五月雨美女がさ乱れる', 'videos' => [
    ['id' => 'MQc5Qq4ytLY', 'start' => 1359, 'label' => '2013/5/5 中野サンプラザ', 'note' => ['ハロプロ研修生春の診断テスト', 'ハロプロ研修生 発表会 2013 〜春の公開実力診断テスト〜']],
    ['id' => 'Z6sb1yLSB-c', 'start' => 1756, 'label' => '2014/4/13 東別院ホール', 'note' => 'Juice=Juice 3rd「裸の裸の裸のKISS/アレコレしたい!」発売記念イベント'],
    ['id' => 'vjCL6CVyLoQ', 'start' => 1387, 'label' => '2014/2/16 オリックス劇場', 'note' => ['MEMORIAL EDIT', 'Hello! Project 2014 WINTER 〜GOiSU MODE〜']],
  ]],
  ['title' => '好きって言ってよ', 'videos' => [
    ['id' => 'nF_rDO-Yldw', 'start' => 395, 'label' => '2020/3/21 明治神宮会館', 'note' => 'Hello! Project ひなフェス 2020〜被災地復興支援・東北を元気に！〜 Juice=Juice プレミアム'],
    ['id' => 'FSff9JgH2pA', 'start' => 1628, 'label' => '2020/4/1 無観客ミニライブ', 'note' => 'Juice=Juice 13thシングル「ポップミュージック/好きって言ってよ」発売記念イベント'],
    ['id' => 'ke4k00H1-kk', 'start' => 146, 'label' => '2020/12/31 中野サンプラザ', 'note' => 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜'],
  ]],
  ['title' => '私が言う前に抱きしめなきゃね', 'videos' => [
    ['id' => 'rVfAYc3yxLc', 'start' => 417, 'label' => '2013/6/13 池袋サンシャインシティ アルパB1F 噴水広場', 'note' => 'インディーズ3rdシングル「天まで登れ!」発売記念イベント'],
    ['id' => 'iY_PTZlflGw', 'start' => 1500, 'label' => '2013/3/16 オリンパスホール八王子（J:COMホール八王子）', 'note' => ['モーニング娘。ツアー初日映像', 'モーニング娘。コンサートツアー2013春 ミチシゲ☆イレブンSOUL 〜田中れいな卒業記念スペシャル〜']],
    ['id' => 'DxrNrvBXTnE', 'start' => 1342, 'label' => '2013/3/3 パシフィコ横浜展示ホール A/B', 'note' => 'Hello! Project 春の大感謝 ひな祭りフェスティバル 2013'],
    ['id' => 'WELuju3bypk', 'start' => 358, 'label' => '2019/4/27 ハーモニーホール座間 大ホール', 'note' => 'Juice=Juice CONCERT TOUR 2019 〜JuiceFull!!!!!!!〜'],
    ['id' => 'ymqH7UMMgRM', 'start' => 1726, 'label' => '2019/6/17 日本武道館', 'note' => 'ハロプロ プレミアム Juice=Juice CONCERT TOUR 2019 〜JuiceFull!!!!!!!〜 FINAL 宮崎由加卒業スペシャル'],
  ]],
  ['title' => '初めてを経験中', 'videos' => [
    ['id' => 'yKRSD7fGI6s', 'start' => 1654, 'label' => '2013/12/3 池袋サンシャインシティ アルパB1F 噴水広場', 'note' => 'Juice=Juice 2ndシングル「イジワルしないで 抱きしめてよ/初めてを経験中」発売記念ミニライブ＆握手会'],
    ['id' => 'k_gAQR6uLrc', 'start' => 1691, 'label' => '2014/1/3 中野サンプラザ', 'note' => 'Hello! Project 2014 WINTER 〜DE-HA MiX〜'],
  ]],
  ['title' => '生まれたてのBaby Love', 'videos' => [
    ['id' => 'BvauQt6xxMc', 'start' => 1192, 'label' => '2015/7/15 東京ドームシティ ラクーアガーデンステージ', 'note' => 'Juice=Juice 1stアルバム「First Squeeze!」発売記念ミニライブ＆握手会'],
    ['id' => 'Vh63VNvbPn0', 'start' => 2329, 'label' => '2017/1/2 中野サンプラザ', 'note' => ['冬ハロコン', 'Hello! Project 2017 WINTER 〜 Crystal Clear 〜']],
    ['id' => 'nfdCl2yy1DA', 'start' => 550, 'label' => '2019/8/25 中野サンプラザ', 'note' => 'Hello! Project 2019 SUMMER 「harmony」'],
    ['id' => '34RMkF6QcnM', 'start' => 1403, 'label' => '2018/2/11 郡山Hip Shot Japan', 'note' => 'Juice=Juice LIVE GEAR 2018 〜Go ahead〜'],
  ]],
  ['title' => '選ばれし私達', 'videos' => [
    ['id' => 'S6MuWrxnx4A', 'start' => 775, 'label' => '2016/4/21 新宿ReNY', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code3→Growing!〜'],
    ['id' => 'v4rfNYe55WA', 'start' => 1190, 'label' => '2016/11/7 日本武道館', 'note' => 'Juice=Juice LIVE MISSION FINAL at 日本武道館'],
  ]],
  ['title' => '素直に甘えて', 'videos' => [
    ['id' => 'pwPWvwn5wH8', 'label' => '2018/8/1 パレットプラザ ステージ', 'note' => 'Juice=Juice 2ndアルバム「Juice=Juice#2 -! Una mas! -」発売記念ミニライブ＆握手会'],
  ]],
  ['title' => '続いていくSTORY', 'videos' => [
    ['id' => 'Ff1SN8u1mgI', 'start' => 1174, 'label' => '2015/5/2 中野サンプラザ', 'note' => 'Juice=Juiceファーストライブツアー2015 特別編!! 〜Special Juice〜'],
    ['id' => '0d8nhu_mRFc', 'start' => 737, 'label' => '2016/5/4 中野サンプラザ', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code3 Special→Growing Up!〜'],
    ['id' => 'THzsLHhdKmc', 'start' => 1446, 'label' => '2019/6/17 日本武道館', 'note' => 'ハロプロ プレミアム Juice=Juice CONCERT TOUR 2019 〜JuiceFull!!!!!!!〜 FINAL 宮崎由加卒業スペシャル'],
    ['id' => 'NbBkOY4CqZU', 'start' => 1559, 'label' => '2020/12/10 日本武道館', 'note' => ['Symphonic Version feat. Karin', 'Juice=Juice コンサート2020 〜続いていくSTORY〜 宮本佳林卒業スペシャル']],
  ]],
  ['title' => '大人の事情', 'videos' => [
    ['id' => '_x6IzhQyzE0', 'start' => 546, 'label' => '2016/3/19 パシフィコ横浜展示ホール A/B', 'note' => 'Hello! Project ひなフェス 2016 Juice=Juice＋カントリー・ガールズプレミアム'],
  ]],
  ['title' => '地団駄ダンス', 'videos' => [
    ['id' => '-Mg97DP6rCk', 'start' => 277, 'label' => '2017/4/26 池袋サンシャインシティ アルパB1F 噴水広場', 'note' => 'Juice=Juice 9thシングル「地団駄ダンス/Feel!感じるよ」発売記念ミニライブ＆握手会'],
    ['id' => '58blJtfLq2U', 'start' => 1687, 'label' => '2019/7/30 OTODAMA SEA STUDIO', 'note' => 'OTODAMA SEA STUDIO 2019 supported by POCARI SWEAT J=J Summer Special'],
    ['id' => 'lg3jhCBWjCY', 'start' => 43, 'label' => '2017/3/25 幕張メッセ国際展示場 展示ホール1', 'note' => 'Hello! Project ひなフェス 2017 モーニング娘。\'17 プレミアム'],
  ]],
  ['title' => '如雨露', 'videos' => [
    ['id' => '0nmKZdPB0qA', 'start' => 2008, 'label' => '2017/5/3 中野サンプラザ', 'note' => 'Juice=Juice LIVE AROUND 2017 〜NEXT ONE SPECIAL〜'],
    ['id' => 'ojbV3VhoEoo', 'start' => 1002, 'label' => '2016/6/25 横浜Bay Hall', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code3→Growing!〜'],
  ]],
  ['title' => '背伸び', 'videos' => [
    ['id' => 'QX1FgV2cJdU', 'start' => 1168, 'label' => '2014/8/31 金沢AZ', 'note' => 'Juice=Juice ファーストライブツアー2014〜2015 News=News 〜各地よりお届けします!〜'],
  ]],
  ['title' => '微炭酸', 'videos' => [
    ['id' => '5VofGX2mB2w', 'start' => 195, 'label' => '2020/12/31 中野サンプラザ', 'note' => ['-Digest-', 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜']],
    ['id' => 'ORWrIh77MpE', 'start' => 1702, 'label' => '2019/2/5 横浜Bay Hall', 'note' => ['最前列特等席映像', 'Juice=Juice LIVE TOUR 2019 〜GO FOR IT!〜']],
  ]],
  ['title' => '風に吹かれて', 'videos' => [
    ['id' => '5VofGX2mB2w', 'start' => 145, 'label' => '2020/12/31 中野サンプラザ', 'note' => ['-Digest-', 'Hello! Project Year-End Party 2020 〜 GOOD BYE & HELLO ! 〜']],
    ['id' => 'Vln2vJ57LVk', 'start' => 278, 'label' => '2020/2/3 新宿ReNY', 'note' => 'Juice=Juice LIVE TOUR 2020 〜NEW SENSATION〜'],
  ]],
  ['title' => '未来へ、さあ走り出せ！', 'videos' => [
    ['id' => 'ZXN_qKfqMFQ', 'start' => 2017, 'label' => '2015/9/26 新宿ReNY', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code1→Begin to Run〜'],
  ]],
  ['title' => '明日やろうはバカやろう', 'videos' => [
    ['id' => 'WnKIivHT0Ag', 'start' => 1562, 'label' => '2016/5/4 中野サンプラザ', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code3 Special→Growing Up!〜'],
  ]],
  ['title' => '裸の裸の裸のKISS', 'videos' => [
    ['id' => '164hpigiHK8', 'start' => 2208, 'label' => '2014/12/6 Zepp Tokyo', 'note' => 'ハロプロ研修生 発表会2014〜11・12月の生タマゴShow!〜'],
    ['id' => 'd6vD1TbuI2A', 'start' => 743, 'label' => '2014/3/21 池袋サンシャインシティ アルパB1F 噴水広場', 'note' => 'Juice=Juice 3rdシングル「裸の裸の裸のKISS/アレコレしたい!」発売記念ミニライブ＆握手会'],
    ['id' => 'GmNV5_AMWt8', 'start' => 1145, 'label' => '2016/3/27 横浜Bay Hall', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code2→NEXT to YOU〜'],
    ['id' => '6goOm_nnK1M', 'start' => 993, 'label' => '2018/5/4 中野サンプラザ', 'note' => 'Juice=Juice LIVE GEAR 2018 〜Go ahead SPECIAL〜'],
  ]],
  ['title' => '天まで登れ！', 'sub' => 'ハロプロ研修生 feat. Juice=Juice', 'videos' => [
    ['id' => 'pw55c84GAWk', 'start' => 304, 'label' => '2015/5/19 日比谷野外大音楽堂', 'note' => '日比谷野音90周年記念事業 Hello!Project 野音プレミアムLIVE 〜外フェス〜'],
    ['id' => 'tosTxi3boPo', 'start' => 1282, 'label' => '2014/2/23 Zepp Tokyo', 'note' => 'ハロプロ研修生 発表会2014〜2月・3月の生タマゴShow!〜'],
  ]],
  ['title' => '彼女になりたいっ!!!', 'sub' => 'ハロプロ研修生 feat. Juice=Juice', 'videos' => [
    ['id' => 'GGUSZVUUAHc', 'start' => 1069, 'label' => '2014/2/23 Zepp Tokyo', 'note' => 'ハロプロ研修生 発表会2014〜2月・3月の生タマゴShow!〜'],
  ]],
  ['title' => 'Danceでバコーン！', 'sub' => '℃-ute', 'videos' => [
    ['id' => 'Z6sb1yLSB-c', 'start' => 1857, 'label' => '2014/4/13 東別院ホール', 'note' => 'Juice=Juice 3rd「裸の裸の裸のKISS/アレコレしたい!」発売記念イベント'],
  ]],
  ['title' => '恋愛ハンター', 'sub' => 'モーニング娘。', 'videos' => [
    ['id' => 'j55OifHPTSc', 'start' => 94, 'label' => '2020/3/20 明治神宮会館', 'note' => 'Hello! Project ひなフェス 2020〜被災地復興支援・東北を元気に！〜 モーニング娘。\'20 プレミアム'],
  ]],
  ['title' => '香水', 'sub' => 'メロン記念日', 'videos' => [
    ['id' => '3lrMCnvjbK4', 'start' => 2330, 'label' => '2015/8/19 柏PALOOZA', 'note' => 'Juice=Juice LIVE MISSION 220 〜Code1→Begin to Run〜'],
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
