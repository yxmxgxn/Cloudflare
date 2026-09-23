<?php
  $title = "宮本佳林 M-Line Music・公式YouTube ユニット ライブYouTubeまとめ";
  $description = "宮本佳林さんがM-Line Music・公式YouTubeでユニットで歌ってる公式ライブ映像をまとめました。公式YouTube・M-Line Musicからリンクしています。";
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
// 1曲 = 1カード。同じ曲を別のメンバーで歌った映像は videos に並べる。
//   artist : 原曲のアーティスト(絞り込みの選択肢にもなる)
//   title  : 曲名
//   id     : YouTubeの動画ID(URLの watch?v= の後ろ11文字)
//   start  : 再生開始位置(秒)。URLの &t=◯s の数字。頭からなら省略
//   members: 歌っているメンバーを「・」区切りで(メンバーの絞り込みにも使う)
//   live   : 公演名。表記はそろえる(例 'M-line Special 2023～Magical Wish～')
//   when   : 日付と会場(例 '2023/4/14 なかのZERO 大ホール')。分からなければ省略
// ------------------------------------------------------------------
$unit_songs = [
  ['artist' => 'Berryz工房', 'title' => 'CLAP!', 'videos' => [
    ['id' => 'zjsfDQb2By4', 'start' => 1302, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/10/3 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Berryz工房', 'title' => 'VERY BEAUTY', 'videos' => [
    ['id' => 'A2VQ-Saeo2Q', 'start' => 797, 'members' => '宮本佳林・竹内朱莉・小片リサ', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/3/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Berryz工房', 'title' => 'すっちゃかめっちゃか～', 'videos' => [
    ['id' => 'vmdeIgPZHiU', 'start' => 30, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/6/4 戸田市文化会館'],
  ]],
  ['artist' => 'Berryz工房', 'title' => 'スッペシャル ジェネレ～ション', 'videos' => [
    ['id' => 'ItLBufXK_ls', 'start' => 1238, 'members' => '夏焼雅・宮本佳林・長谷川萌美・佐藤優樹・宮崎由加・島倉りか', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/6/17 戸田市文化会館'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '愛する人の名前を日記に', 'videos' => [
    ['id' => 'RglVy-I4_6I', 'start' => 300, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/13 Zepp Haneda'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '愛の弾丸', 'videos' => [
    ['id' => 'DjuR2MGaL_k', 'start' => 216, 'members' => '夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/3/21 横浜ランドマークホール'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '一丁目ロック！', 'videos' => [
    ['id' => 'U9VEl1vAlgo', 'start' => 194, 'members' => '夏焼雅・宮本佳林・長谷川萌美・佐藤優樹・宮崎由加・島倉りか', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/6/17 戸田市文化会館'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '胸さわぎスカーレット', 'videos' => [
    ['id' => 'JQdgkF_tFuY', 'start' => 1253, 'members' => '夏焼雅・鈴木愛理・宮本佳林・小片リサ', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/12/11 Zepp Tokyo'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '本気ボンバー！！', 'videos' => [
    ['id' => 'dO1E74lkemM', 'start' => 257, 'members' => '宮本佳林・小片リサ・小関舞・Bitter & Sweet・稲場愛香・平井美葉', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/8/31 タワーホール船堀'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '友情 純情 oh 青春', 'videos' => [
    ['id' => '8vb0mzVTWEs', 'start' => 645, 'members' => '夏焼雅・宮本佳林・竹内朱莉・稲場愛香・小関舞・段原瑠々', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/10/27 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '友達は友達なんだ！', 'videos' => [
    ['id' => 'MqVggYyEDpc', 'start' => 193, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet・佐藤優樹', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => 'Berryz工房', 'title' => '流星ボーイ', 'videos' => [
    ['id' => 'vvnTZAHaPB0', 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/13 Zepp Haneda'],
  ]],
  ['artist' => 'BEYOOOOONDS', 'title' => 'ニッポンノD・N・A！', 'videos' => [
    ['id' => 'pERZZ1DeAl8', 'start' => 872, 'members' => '宮本佳林・小片リサ・小関舞・Bitter & Sweet・稲場愛香・平井美葉', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/8/31 タワーホール船堀'],
  ]],
  ['artist' => 'BEYOOOOONDS', 'title' => '灰toダイヤモンド', 'videos' => [
    ['id' => '-uVXvjRWUqE', 'start' => 991, 'members' => '宮本佳林・平井美葉', 'live' => 'エムハロイベント vol.4～宮本佳林×BEYOOOOONDS/SeasoningS 平井美葉～', 'when' => '2025/10/28 せたがやイーグレットホール'],
  ]],
  ['artist' => 'Bitter & Sweet', 'title' => 'Rolling Days (B&S Edition)', 'videos' => [
    ['id' => '2JQfW2Yt4eo', 'start' => 741, 'members' => '宮本佳林・小片リサ・小関舞・Bitter & Sweet', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/8/31 タワーホール船堀'],
  ]],
  ['artist' => 'Bitter & Sweet', 'title' => 'インストール', 'videos' => [
    ['id' => 'zjsfDQb2By4', 'start' => 541, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/10/10 三郷市文化会館'],
  ]],
  ['artist' => 'Bitter & Sweet', 'title' => 'ハレルヤ', 'videos' => [
    ['id' => 'qOf6y8CWlT8', 'start' => 1081, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/13 Zepp Haneda'],
  ]],
  ['artist' => 'Bitter & Sweet', 'title' => '泣いて 泣いて 恋してる', 'videos' => [
    ['id' => 'sVeS_aWt8_k', 'start' => 115, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/6/4 戸田市文化会館'],
  ]],
  ['artist' => 'Buono!', 'title' => 'MY BOY', 'videos' => [
    ['id' => 'VdqWrgrrdYc', 'start' => 216, 'members' => '夏焼雅・宮本佳林・小関舞', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/12/5 なかのZERO 大ホール'],
  ]],
  ['artist' => 'Buono!', 'title' => 'マイラブ', 'videos' => [
    ['id' => 'odJfWR8XVao', 'start' => 210, 'members' => '夏焼雅・宮本佳林・小片リサ・田﨑あさひ・長谷川萌美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/3/21 横浜ランドマークホール'],
  ]],
  ['artist' => 'Buono!', 'title' => 'れでぃぱんさぁ', 'videos' => [
    ['id' => 'Grnf0Kbh0qE', 'members' => '佐藤優樹・宮本佳林・森戸知沙希', 'live' => 'M-line Special 2026 Spring～LOVELY～', 'when' => '2026/5/6 横浜Bay Hall'],
  ]],
  ['artist' => 'Buono!', 'title' => '泣き虫少年', 'videos' => [
    ['id' => 'hDkrX2T-dYI', 'start' => 177, 'members' => '夏焼雅・宮本佳林・浅倉樹々', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/2/4 横浜Bay Hall'],
    ['id' => 'dCBeCwWNFeo', 'start' => 40, 'members' => '夏焼雅・鈴木愛理・宮本佳林', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/12/11 Zepp Tokyo'],
  ]],
  ['artist' => 'Buono!', 'title' => '初恋サイダー', 'videos' => [
    ['id' => 'BW_zp62yBA0', 'start' => 158, 'members' => '宮本佳林・小片リサ・小関舞・佐藤優樹', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/10/16 横浜ランドマークホール'],
  ]],
  ['artist' => '℃-ute', 'title' => 'Danceでバコーン！', 'videos' => [
    ['id' => '5Nue7Fv_AgE', 'start' => 194, 'members' => '夏焼雅・宮本佳林・小片リサ・小関舞・長谷川萌美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/7/6 めぐろパーシモンホール'],
  ]],
  ['artist' => '℃-ute', 'title' => 'キャンパスライフ～生まれて来てよかった～', 'videos' => [
    ['id' => 'eKVQvIM6W4E', 'start' => 985, 'members' => '鈴木愛理・宮本佳林・小片リサ・川村文乃', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/11/20 中野サンプラザ'],
  ]],
  ['artist' => '℃-ute', 'title' => 'めぐる恋の季節', 'videos' => [
    ['id' => 'bZdO8ppE-IA', 'start' => 1612, 'members' => '鈴木愛理・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/9/11 江戸川区総合文化センター 大ホール'],
  ]],
  ['artist' => '℃-ute', 'title' => '愛ってもっと斬新', 'videos' => [
    ['id' => 'oQMhhQB2SRM', 'start' => 1423, 'members' => '夏焼雅・鈴木愛理・宮本佳林・小片リサ', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/12/11 Zepp Tokyo'],
  ]],
  ['artist' => '℃-ute', 'title' => '超WONDERFUL!', 'videos' => [
    ['id' => 'HrSvNkdYYnQ', 'start' => 661, 'members' => '宮本佳林・小片リサ・小関舞・Bitter & Sweet', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/8/31 タワーホール船堀'],
    ['id' => 'TGM_3FKpkjk', 'start' => 1745, 'members' => '宮本佳林・夏焼雅・鈴木愛理・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/12/11 Zepp Tokyo'],
  ]],
  ['artist' => '℃-ute', 'title' => '情熱エクスタシー', 'videos' => [
    ['id' => 'k16JVr2q8RM', 'start' => 640, 'members' => '鈴木愛理・宮本佳林・小片リサ・加賀楓', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/11/20 中野サンプラザ'],
  ]],
  ['artist' => '℃-ute', 'title' => '悲しきヘブン', 'videos' => [
    ['id' => 'pERZZ1DeAl8', 'start' => 241, 'members' => '宮本佳林・小片リサ・Bitter & Sweet・平井美葉', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/8/31 タワーホール船堀'],
    ['id' => '893Kn4afoj8', 'members' => '宮本佳林・佐藤優樹', 'live' => 'M-line Special 2025 Spring～move it on～', 'when' => '2025/3/2 新宿ReNY'],
  ]],
  ['artist' => 'GAM', 'title' => '蜃気楼ロマンス', 'videos' => [
    ['id' => 'odJfWR8XVao', 'start' => 1068, 'members' => '宮本佳林・田﨑あさひ', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/3/21 横浜ランドマークホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'Future Smile', 'videos' => [
    ['id' => 'ylw8j8bDudQ', 'start' => 111, 'members' => '宮本佳林・段原瑠々・井上玲音', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/6/4 戸田市文化会館'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'G.O.A.T.', 'videos' => [
    ['id' => 'aPCguQfPxY0', 'start' => 1463, 'members' => '宮本佳林・稲場愛香', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/3/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'KEEP ON 上昇志向！！', 'videos' => [
    ['id' => 'hDkrX2T-dYI', 'start' => 984, 'members' => '宮崎由加・宮本佳林', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/12/5 なかのZERO 大ホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'Mon Amour', 'videos' => [
    ['id' => 'EPI9YjiqgNA', 'start' => 226, 'members' => '宮本佳林・稲場愛香', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/7/6 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'Va-Va-Voom', 'videos' => [
    ['id' => 'KcTqi-UCkXs', 'start' => 1514, 'members' => '鈴木愛理・宮本佳林・小片リサ・加賀楓・川村文乃', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/11/20 中野サンプラザ'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'イジワルしないで 抱きしめてよ', 'videos' => [
    ['id' => 'W9kGuqorezA', 'start' => 170, 'members' => '夏焼雅・宮本佳林・宮崎由加・小関舞・小片リサ', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/12/5 なかのZERO 大ホール'],
    ['id' => 'I4paTYWhcPQ', 'start' => 1364, 'members' => '鈴木愛理・宮本佳林・小田さくら・植村あかり・井上玲音', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/3/20 NHK大阪ホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'がんばれないよ', 'videos' => [
    ['id' => 'RSDQC3umiW8', 'start' => 1843, 'members' => '宮本佳林・段原瑠々', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/6/4 戸田市文化会館'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'この世界は捨てたもんじゃない', 'videos' => [
    ['id' => 'ItLBufXK_ls', 'start' => 176, 'members' => '宮崎由加・宮本佳林', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/6/17 戸田市文化会館'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '「ひとりで生きられそう」って それってねえ、褒めているの？', 'videos' => [
    ['id' => 'JQdgkF_tFuY', 'start' => 391, 'members' => '田中れいな・夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/2/23 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => 'ロマンスの途中', 'videos' => [
    ['id' => 'sRSK2LNcByo', 'start' => 163, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/3/21 横浜ランドマークホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '雨の中の口笛', 'videos' => [
    ['id' => 'V9Slp5TRebk', 'start' => 155, 'members' => '宮本佳林・Bitter & Sweet・工藤由愛・松永里愛', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/4/28 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '禁断少女', 'videos' => [
    ['id' => '40YU6dzsTF8', 'start' => 222, 'members' => '宮本佳林・稲場愛香・浅倉樹々', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/2/4 横浜Bay Hall'],
    ['id' => 'tI78CMio-vs', 'start' => 1583, 'members' => '宮本佳林・小片リサ・小関舞・稲場愛香', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/12/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '銀色のテレパシー', 'videos' => [
    ['id' => '0KhNlhHzEuM', 'start' => 948, 'members' => '宮本佳林・小片リサ・Bitter & Sweet・工藤由愛・松永里愛', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/4/28 めぐろパーシモンホール'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '好きって言ってよ', 'videos' => [
    ['id' => 'JC0XfyZWnsk', 'start' => 165, 'members' => '鈴木愛理・宮本佳林', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/11/20 中野サンプラザ'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '私が言う前に抱きしめなきゃね（MEMORIAL EDIT）', 'videos' => [
    ['id' => 'E4jYcxSlJgM', 'start' => 838, 'members' => '宮本佳林・佐藤優樹', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/13 Zepp Haneda'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '生まれたてのBaby Love', 'videos' => [
    ['id' => 'nRdTqAFHhCk', 'start' => 269, 'members' => '夏焼雅・宮本佳林・竹内朱莉・稲場愛香・小関舞・段原瑠々', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/10/27 めぐろパーシモンホール'],
    ['id' => 'CBB6zE5dgdc', 'start' => 1179, 'members' => '鈴木愛理・宮本佳林・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/1/31 セルリアンタワー ボールルーム'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '背伸び', 'videos' => [
    ['id' => 'tzuEG_CMCc4', 'members' => '宮本佳林・小関舞', 'live' => 'M-line Special 2025 Spring～move it on～', 'when' => '2025/3/2 新宿ReNY'],
  ]],
  ['artist' => 'Juice=Juice', 'title' => '微炭酸', 'videos' => [
    ['id' => 'JC0XfyZWnsk', 'start' => 1512, 'members' => '宮本佳林・小片リサ・稲場愛香', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/10/10 三郷市文化会館'],
    ['id' => 'dZpljq8vxSQ', 'start' => 268, 'members' => '宮本佳林・稲場愛香', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/12/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'MilkyWay', 'title' => 'タンタンターン！', 'videos' => [
    ['id' => '7yJvoU-6mgY', 'start' => 934, 'members' => '宮本佳林・小片リサ・山﨑愛生', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/4/14 なかのZERO 大ホール'],
  ]],
  ['artist' => 'M-line', 'title' => 'We are the M-line!!', 'videos' => [
    ['id' => 'pIOG2xW_Nys', 'members' => '宮本佳林・佐藤優樹・小関舞', 'live' => 'M-line Special 2025 Spring～move it on～', 'when' => '2025/3/2 新宿ReNY'],
  ]],
  ['artist' => 'NEXT YOU', 'title' => '大人の事情', 'videos' => [
    ['id' => '05TC_caYjvU', 'start' => 982, 'members' => '宮本佳林・平井美葉', 'live' => 'エムハロイベント vol.4～宮本佳林×BEYOOOOONDS/SeasoningS 平井美葉～', 'when' => '2025/10/28 せたがやイーグレットホール'],
  ]],
  ['artist' => 'PINK CRES', 'title' => 'Tell me why', 'videos' => [
    ['id' => '_vsdTWua8h0', 'start' => 1160, 'members' => '夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => 'SALT5', 'title' => 'GET UP!ラッパー', 'videos' => [
    ['id' => 'l_SkogtdkcM', 'start' => 211, 'members' => '夏焼雅・宮本佳林・小片リサ・佐々木莉佳子・稲場愛香', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/10/10 三郷市文化会館'],
  ]],
  ['artist' => 'ZYX', 'title' => '白いTOKYO', 'videos' => [
    ['id' => 'Zc7-o-E6_7E', 'members' => '宮本佳林・小関舞', 'live' => 'COVERS -One on One-'],
    ['id' => 'QPu8gEwd9Dw', 'start' => 144, 'members' => '宮本佳林・小片リサ・小関舞', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/12/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'あか組4', 'title' => '赤い日記帳', 'videos' => [
    ['id' => '7J9j3KbD1h4', 'start' => 168, 'members' => '夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/2/23 めぐろパーシモンホール'],
  ]],
  ['artist' => 'アンジュルム', 'title' => 'Uraha=Lover', 'videos' => [
    ['id' => '6njGwroq86Q', 'start' => 653, 'members' => '竹内朱莉・宮本佳林', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/9/23 仙台GIGS'],
  ]],
  ['artist' => 'アンジュルム', 'title' => '泣けないぜ・・・共感詐欺', 'videos' => [
    ['id' => 'HiuMx6GS4VU', 'start' => 187, 'members' => '竹内朱莉・宮本佳林・稲場愛香', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/3/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'アンジュルム', 'title' => '大器晩成', 'videos' => [
    ['id' => 'Io_eSr7j61A', 'start' => 1110, 'members' => '夏焼雅・宮本佳林・小片リサ・小関舞・長谷川萌美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/7/6 めぐろパーシモンホール'],
  ]],
  ['artist' => 'カントリー・ガールズ', 'title' => 'ピーナッツバタージェリーラブ', 'videos' => [
    ['id' => 'Hyzjywprdwo', 'start' => 188, 'members' => '宮本佳林・小関舞', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/10/16 横浜ランドマークホール'],
  ]],
  ['artist' => 'カントリー・ガールズ', 'title' => 'リズムが呼んでいるぞ！', 'videos' => [
    ['id' => 'aqgdX44Q6ao', 'start' => 197, 'members' => '宮本佳林・小片リサ・小関舞', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/5/5 横浜ランドマークホール'],
  ]],
  ['artist' => 'カントリー・ガールズ', 'title' => '書いては消しての “I Love You”', 'videos' => [
    ['id' => 'YxOX5Q1NkWE', 'start' => 329, 'members' => '宮本佳林・小片リサ・小関舞', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/10/16 横浜ランドマークホール'],
  ]],
  ['artist' => 'カントリー・ガールズ', 'title' => '恋はマグネット', 'videos' => [
    ['id' => 'fOuJvADoPIM', 'start' => 554, 'members' => '宮本佳林・小片リサ・小関舞・稲場愛香', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/12/24 めぐろパーシモンホール'],
  ]],
  ['artist' => 'カントリー娘。に紺野と藤本（モーニング娘。）', 'title' => '浮気なハニーパイ', 'videos' => [
    ['id' => 'iHeOrsoLkzM', 'start' => 211, 'members' => '竹内朱莉・宮本佳林・稲場愛香・小関舞・小片リサ・森戸知沙希', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/3/24 めぐろパーシモンホール'],
    ['id' => 'MqVggYyEDpc', 'start' => 1602, 'members' => '宮本佳林・小片リサ・小関舞', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/10/16 横浜ランドマークホール'],
  ]],
  ['artist' => 'カントリー娘｡に石川梨華(モーニング娘｡)', 'title' => '色っぽい女～SEXY BABY～', 'videos' => [
    ['id' => 'uZQCh-uONuc', 'members' => '宮本佳林・佐藤優樹・小関舞', 'live' => 'M-line Special 2025 Spring～move it on～', 'when' => '2025/3/2 新宿ReNY'],
  ]],
  ['artist' => 'ジュリン', 'title' => 'ほたる祭りの日', 'videos' => [
    ['id' => 'I4paTYWhcPQ', 'start' => 137, 'members' => '宮本佳林・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/1/31 セルリアンタワー ボールルーム'],
    ['id' => 'QNsbQulWZxU', 'start' => 208, 'members' => '宮本佳林・佐藤優樹', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => 'スマイレージ', 'title' => 'スキちゃん', 'videos' => [
    ['id' => 'aqgdX44Q6ao', 'start' => 613, 'members' => '宮本佳林・小片リサ・小関舞・勝田里奈', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/5/5 横浜ランドマークホール'],
  ]],
  ['artist' => 'スマイレージ', 'title' => 'パン屋さんのアルバイト', 'videos' => [
    ['id' => '7yJvoU-6mgY', 'start' => 207, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/3/21 横浜ランドマークホール'],
  ]],
  ['artist' => 'スマイレージ', 'title' => '寒いね。', 'videos' => [
    ['id' => 'Zc7-o-E6_7E', 'start' => 839, 'members' => '夏焼雅・宮本佳林・稲場愛香・浅倉樹々', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/2/4 横浜Bay Hall'],
  ]],
  ['artist' => 'スマイレージ', 'title' => '私、ちょいとカワイイ裏番長', 'videos' => [
    ['id' => 'PeJ8dQyLcDE', 'start' => 598, 'members' => '宮本佳林・小片リサ・小関舞・勝田里奈', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/5/5 横浜ランドマークホール'],
  ]],
  ['artist' => 'スマイレージ', 'title' => '有頂天LOVE', 'videos' => [
    ['id' => 'Iv7J1M4UWGc', 'start' => 1453, 'members' => '鈴木愛理・宮本佳林・小片リサ・竹内朱莉', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/9/11 江戸川区総合文化センター 大ホール'],
  ]],
  ['artist' => 'ダイヤレディー', 'title' => 'レディーマーメイド', 'videos' => [
    ['id' => 'yyiJ4RgQSvI', 'members' => '鈴木愛理・宮本佳林', 'live' => 'COVERS -One on One-'],
  ]],
  ['artist' => 'タンポポ', 'title' => 'ラストキッス', 'videos' => [
    ['id' => '5dJvQZM0h5Q', 'start' => 1459, 'members' => '鈴木愛理・宮本佳林・小田さくら・植村あかり', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/3/20 NHK大阪ホール'],
  ]],
  ['artist' => 'タンポポ', 'title' => '恋をしちゃいました！', 'videos' => [
    ['id' => 'faeEy5maNVw', 'start' => 200, 'members' => '宮本佳林・小関舞', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/10/27 めぐろパーシモンホール'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => 'My Darling ～Do you love me?～', 'videos' => [
    ['id' => '32LEGgSAhHM', 'start' => 221, 'members' => '宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/13 Zepp Haneda'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => 'アドレナリン・ダメ', 'videos' => [
    ['id' => '33a1T3Mf2tA', 'start' => 195, 'members' => '宮本佳林・稲場愛香・浅倉樹々', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/2/4 横浜Bay Hall'],
    ['id' => 'GuwSjZQLdMI', 'start' => 1125, 'members' => '宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => 'ハッピークラッカー', 'videos' => [
    ['id' => 'tvglrBFOXT4', 'start' => 1605, 'members' => '夏焼雅・鈴木愛理・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/12/11 Zepp Tokyo'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => '今夜だけ浮かれたかった', 'videos' => [
    ['id' => 'MSBFAt5cnks', 'start' => 306, 'members' => '宮本佳林・小片リサ・小関舞・Bitter & Sweet', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/8/31 タワーホール船堀'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => '春恋歌', 'videos' => [
    ['id' => 'WlIBruzMF3c', 'start' => 1289, 'members' => '田中れいな・夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/2/23 めぐろパーシモンホール'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => '初恋サンライズ', 'videos' => [
    ['id' => '60ZPECtPC8Y', 'start' => 139, 'members' => '竹内朱莉・宮本佳林・稲場愛香・小関舞・小片リサ', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/3/24 めぐろパーシモンホール'],
    ['id' => 'qvT8HCgbNiE', 'start' => 175, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/10/10 三郷市文化会館'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => '表面張力～Surface Tension～', 'videos' => [
    ['id' => 'AsnJSwZj6cA', 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/23 仙台GIGS'],
    ['id' => '7_fns65ABzs', 'members' => '譜久村聖・佐藤優樹・宮本佳林・森戸知沙希・小関舞', 'live' => 'M-line Special 2026 Spring～LOVELY～', 'when' => '2026/3/7 横浜Bay Hall'],
  ]],
  ['artist' => 'つばきファクトリー', 'title' => '恋のUFOキャッチャー', 'videos' => [
    ['id' => 'iPOAItAdSNo', 'start' => 149, 'members' => '宮本佳林・浅倉樹々', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/9/23 仙台GIGS'],
  ]],
  ['artist' => 'ハロー！プロジェクト モベキマス', 'title' => 'もしも…', 'videos' => [
    ['id' => 'aGPXcbgzBFQ', 'start' => 609, 'members' => '宮本佳林・小片リサ・小関舞', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/10/16 横浜ランドマークホール'],
    ['id' => 'VdVyiDR3_QE', 'start' => 194, 'members' => '宮本佳林・小片リサ・小関舞・勝田里奈', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/5/5 横浜ランドマークホール'],
  ]],
  ['artist' => 'プッチモニ', 'title' => 'BABY！ 恋に KNOCK OUT！', 'videos' => [
    ['id' => 'U9VEl1vAlgo', 'start' => 1050, 'members' => '夏焼雅・宮本佳林・島倉りか', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/6/17 戸田市文化会館'],
  ]],
  ['artist' => 'ベキマス', 'title' => '負けるな わっしょい！', 'videos' => [
    ['id' => 'n0u7i0CjLXU', 'start' => 211, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/3/21 横浜ランドマークホール'],
  ]],
  ['artist' => 'メロン記念日', 'title' => 'This is 運命', 'videos' => [
    ['id' => 'KcTqi-UCkXs', 'start' => 131, 'members' => '宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/12/11 Zepp Tokyo'],
  ]],
  ['artist' => 'メロン記念日', 'title' => 'さあ、早速盛り上げて 行こか～！！', 'videos' => [
    ['id' => 'CrBjYWgCRtI', 'members' => '宮本佳林・佐藤優樹・小関舞', 'live' => 'M-line Special 2025 Spring～move it on～', 'when' => '2025/3/2 新宿ReNY'],
  ]],
  ['artist' => 'メロン記念日', 'title' => 'MI DA RA 摩天楼', 'videos' => [
    ['id' => 'YXxhq1Cxt3A', 'members' => '宮本佳林・米村姫良々', 'live' => 'ONE PLUS ONE（ハロドリ。）'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'Mr.Moonlight～愛のビッグバンド～', 'videos' => [
    ['id' => 'arHGlsSkjOw', 'start' => 765, 'members' => '宮本佳林・平井美葉', 'live' => 'エムハロイベント vol.4～宮本佳林×BEYOOOOONDS/SeasoningS 平井美葉～', 'when' => '2025/10/28 せたがやイーグレットホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'Only you', 'videos' => [
    ['id' => 'NdxbeIOsHWI', 'start' => 132, 'members' => '高橋愛・田中れいな・夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/2/23 めぐろパーシモンホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'SEXY BOY～そよ風に寄り添って～', 'videos' => [
    ['id' => 'F5fHQ7_-yyM', 'start' => 596, 'members' => '夏焼雅・宮本佳林・小片リサ・小関舞・長谷川萌美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/7/6 めぐろパーシモンホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'インスピレーション！', 'videos' => [
    ['id' => 'v8ax7vZ3Qc8', 'start' => 136, 'members' => '夏焼雅・佐藤優樹・宮崎由加・宮本佳林・小関舞・小片リサ・石田亜佑美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/12/5 なかのZERO 大ホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'さよなら SEE YOU AGAIN アディオス BYE BYE チャッチャ！', 'videos' => [
    ['id' => 'YFrh5mHSSCI', 'start' => 191, 'members' => '高橋愛・田中れいな・夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/2/23 めぐろパーシモンホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'ジェラシー ジェラシー', 'videos' => [
    ['id' => 'uUHfOe1RHuU', 'start' => 1276, 'members' => '鈴木愛理・宮本佳林・小片リサ・加賀楓・川村文乃', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/11/20 中野サンプラザ'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'シャボン玉', 'videos' => [
    ['id' => 'pfqSQC37zIc', 'start' => 1347, 'members' => '田中れいな・PINK CRES.・鈴木愛理・宮本佳林', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/3/6 仙台GIGS'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'シルバーの腕時計', 'videos' => [
    ['id' => 'WOAHynr0fOU', 'start' => 160, 'members' => '宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'ドッカ～ン カプリッチオ', 'videos' => [
    ['id' => 'nBSygUgPCto', 'start' => 158, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet・佐藤優樹', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'ブラボー！', 'videos' => [
    ['id' => 'E4jYcxSlJgM', 'start' => 144, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet・佐藤優樹', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/11/13 Zepp Haneda'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => 'ラヴ＆ピィ～ス! HEROがやって来たっ。', 'videos' => [
    ['id' => 'V9Slp5TRebk', 'start' => 707, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet・岡村ほまれ・山﨑愛生', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/4/14 なかのZERO 大ホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => '今すぐ飛び込む勇気', 'videos' => [
    ['id' => 'IoF3qYSecSM', 'start' => 873, 'members' => '宮本佳林・佐藤優樹・石田亜佑美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/12/5 なかのZERO 大ホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => '笑顔YESヌード', 'videos' => [
    ['id' => 'XCXQ8NAvASY', 'start' => 241, 'members' => '夏焼雅・宮本佳林・佐藤優樹・稲場愛香・浅倉樹々', 'live' => 'M-line Special 2024～Many well wishes～', 'when' => '2024/2/4 横浜Bay Hall'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => '彼と一緒にお店がしたい！', 'videos' => [
    ['id' => 'UKQ5ZGP9Fik', 'start' => 575, 'members' => '道重さゆみ・田中れいな・PINK CRES.・宮本佳林', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/6/20 中野サンプラザ'],
    ['id' => 'DjuR2MGaL_k', 'start' => 683, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet・岡村ほまれ・山﨑愛生', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/4/14 なかのZERO 大ホール'],
  ]],
  ['artist' => 'モーニング娘。', 'title' => '踊れ！モーニングカレー', 'videos' => [
    ['id' => 'HQaLQ2w356o', 'start' => 1418, 'members' => '田中れいな・夏焼雅・宮本佳林・小片リサ', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/2/23 めぐろパーシモンホール'],
  ]],
  ['artist' => '音楽ガッタス', 'title' => '鳴り始めた恋のBELL', 'videos' => [
    ['id' => 'xg8locr8d-k', 'start' => 1940, 'members' => '夏焼雅・宮本佳林・小片リサ・Bitter & Sweet', 'live' => 'M-line Special 2022～My Wish～', 'when' => '2022/9/25 戸田市文化会館'],
  ]],
  ['artist' => '後藤真希', 'title' => 'うわさのSEXY GUY', 'videos' => [
    ['id' => 'dKEuC92OgtI', 'members' => '譜久村聖・宮本佳林', 'live' => 'M-line Special 2026 Spring～LOVELY～', 'when' => '2026/5/31 新宿ReNY'],
  ]],
  ['artist' => '黄色5', 'title' => '黄色いお空で BOOM BOOM BOOM', 'videos' => [
    ['id' => 'k0UKxgbaDo8', 'start' => 983, 'members' => '夏焼雅・宮本佳林', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/6/20 中野サンプラザ'],
  ]],
  ['artist' => '太陽とシスコムーン', 'title' => 'Magic of Love', 'videos' => [
    ['id' => 'avkKCtJYh3s', 'start' => 144, 'members' => '鈴木愛理・宮本佳林・小片リサ・Bitter & Sweet・竹内朱莉', 'live' => 'M-line Special 2021～Make a Wish!～', 'when' => '2021/9/11 江戸川区総合文化センター 大ホール'],
  ]],
  ['artist' => '太陽とシスコムーン', 'title' => 'ガタメキラ', 'videos' => [
    ['id' => '-IGB_XYdbWQ', 'start' => 156, 'members' => '夏焼雅・宮本佳林・長谷川萌美・佐藤優樹', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/6/17 戸田市文化会館'],
  ]],
  ['artist' => '後浦なつみ', 'title' => 'LOVE LIKE CRAZY', 'videos' => [
    ['id' => 'hssWGXiFBDY', 'start' => 622, 'members' => '宮本佳林・稲場愛香・段原瑠々', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/10/27 めぐろパーシモンホール'],
  ]],
  ['artist' => '後浦なつみ', 'title' => '恋愛戦隊シツレンジャー', 'videos' => [
    ['id' => 'h-QDXjyqVh4', 'start' => 1386, 'members' => '宮本佳林・竹内朱莉・浅倉樹々', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/9/23 仙台GIGS'],
  ]],
  ['artist' => '松浦亜弥', 'title' => 'Yeah！めっちゃホリディ', 'videos' => [
    ['id' => 'qr7Q2Zq7ixI', 'start' => 216, 'members' => '夏焼雅・宮本佳林・長谷川萌美', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/6/17 戸田市文化会館'],
  ]],
  ['artist' => '真野恵里菜', 'title' => '青春のセレナーデ', 'videos' => [
    ['id' => 'hssWGXiFBDY', 'start' => 184, 'members' => '宮本佳林・小片リサ', 'live' => 'M-line Special 2023～Magical Wish～', 'when' => '2023/12/5 なかのZERO 大ホール'],
  ]],
  ['artist' => '宮本佳林・小片リサ・佐藤優樹', 'title' => 'すっぴん', 'videos' => [
    ['id' => 'hssWGXiFBDY', 'start' => 1430, 'members' => '宮本佳林・小片リサ・佐藤優樹', 'live' => 'ナルチカ 2023 OCHA NORMA in 備前', 'when' => '2023/11/19 備前市市民センター'],
  ]],
];

// カード用に整える(原曲の表示・絞り込み用の属性)。メンバーは出演回数の多い順に選択肢にする
$cards = [];
$artists = [];
$member_count = [];
foreach ($unit_songs as $s) {
    $members = [];
    foreach ($s['videos'] as $v) {
        foreach (explode('・', $v['members']) as $m) {
            $members[$m] = true;
        }
    }
    foreach (array_keys($members) as $m) {
        if ($m !== '宮本佳林') $member_count[$m] = ($member_count[$m] ?? 0) + 1;
    }
    $artists[$s['artist']] = true;
    $cards[] = [
        'title' => $s['title'],
        'sub' => '原曲：' . $s['artist'],
        'data' => ['artist' => $s['artist'], 'members' => implode('|', array_keys($members))],
        // 一覧にはメンバーを出し、その下に公演名と日付・会場を添える
        'videos' => array_map(fn($v) => $v + [
            'label' => $v['members'],
            'note' => [$v['live'] ?? '', $v['when'] ?? ''],
        ], $s['videos']),
    ];
}
arsort($member_count);
?>

<main>
  <h1>宮本佳林 M-Line Music・公式YouTube ユニット ライブYouTubeまとめ</h1>
  <p>宮本佳林さんが<a href="https://www.youtube.com/c/mlinemusic" target="_blank" rel="noopener noreferrer">M-Line Music</a>・<a href="https://www.youtube.com/c/miyamotokarin" target="_blank" rel="noopener noreferrer">公式YouTube</a>でユニットで歌っている公式ライブ映像をまとめました。<br>
  1曲ごとのカードに、歌っているメンバーと公演を並べています。一覧を押すと、カードの上でその映像が再生されます。</p>
  <p class="sc-section-desc">公演名・日付・会場は、各動画の概要欄や動画タイトルを参考に表記をそろえています。概要欄に書かれていないものは同じ公演の別の動画から補っているため、誤りが含まれている可能性があります。</p>

  <div class="sc-filter" id="unit-filter">
    <select id="unit-artist" aria-label="原曲のアーティストで絞り込む">
      <option value="">原曲のアーティスト</option>
      <?php foreach (array_keys($artists) as $a): ?>
      <option value="<?= vc_h($a) ?>"><?= vc_h($a) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="unit-member" aria-label="一緒に歌ったメンバーで絞り込む">
      <option value="">一緒に歌ったメンバー</option>
      <?php foreach ($member_count as $m => $n): ?>
      <option value="<?= vc_h($m) ?>"><?= vc_h($m) ?>（<?= $n ?>曲）</option>
      <?php endforeach; ?>
    </select>
    <button type="button" id="unit-reset">絞り込み解除</button>
    <span class="sc-count" id="unit-count"></span>
  </div>

  <?= song_cards($cards) ?>
</main>
<script>
(function () {
  var artist = document.getElementById('unit-artist');
  var member = document.getElementById('unit-member');
  var cards = document.querySelectorAll('.sc-card');

  function filter() {
    var shown = 0;
    cards.forEach(function (c) {
      var ok = (!artist.value || c.dataset.artist === artist.value)
            && (!member.value || c.dataset.members.split('|').indexOf(member.value) !== -1);
      c.hidden = !ok;
      if (ok) shown++;
    });
    document.getElementById('unit-count').textContent = shown + ' 曲 / 全' + cards.length + ' 曲';
    [artist, member].forEach(function (s) { s.classList.toggle('is-active', s.selectedIndex > 0); });
  }

  artist.addEventListener('change', filter);
  member.addEventListener('change', filter);
  document.getElementById('unit-reset').addEventListener('click', function () {
    artist.value = '';
    member.value = '';
    filter();
  });
  filter();
})();
</script>
<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
