<?php
  $title = "宮本佳林ソロ公式ライブ映像まとめ";
  $description = "宮本佳林さんがソロで歌ってる公式ライブ映像をまとめました。公式YouTube・M-Line Musicからリンクしています。";
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
// 収録作品 => 曲の一覧。1曲 = 1カードで、videos に映像を何本でも並べられる。
//   title  : 曲名。カバー曲は 'sub' => '原曲：◯◯' を付ける
//   id     : YouTubeの動画ID(URLの watch?v= の後ろ11文字)
//   start  : 再生開始位置(秒)。URLの &t=◯s の数字。頭からなら省略
//   label  : ライブ名
//   note   : 日付と会場(省略可)
// ------------------------------------------------------------------
$solo_sections = [
  '1st Album ヒトリトイロ' => [
    ['title' => 'なんてったって I Love You', 'videos' => [
      ['id' => 'N4g2iWwMBY0', 'start' => 214, 'label' => '宮本佳林 LIVE 2022春～アメジスト～', 'note' => '2022/5/22 めぐろパーシモンホール'],
      ['id' => '1lzTFn7IZx4', 'label' => 'M-line Special 2026 Spring～LOVELY～', 'note' => '2026/3/7 横浜Bay Hall'],
    ]],
    ['title' => '自分ファースト', 'videos' => [
      ['id' => 'e7AvISfYuSE', 'label' => '宮本佳林 LIVE 2023～ヒトリトイロ～', 'note' => '2023/2/5 めぐろパーシモンホール'],
      ['id' => 'Stp8Yi4_usA', 'start' => 224, 'label' => '宮本佳林 LIVE 2024春～Hello! Brand new me～RETURNS', 'note' => '2024/4/14 横浜Bay Hall'],
    ]],
    ['title' => '若者ブランド', 'videos' => [
      ['id' => 'v0ei3Dge4-8', 'start' => 1264, 'label' => '宮本佳林 LIVE 2021春～アマリリス～'],
      ['id' => 'Drpdvt3u5RQ', 'label' => '宮本佳林 LIVE 2022春～アメジスト～', 'note' => '2022/5/22 めぐろパーシモンホール'],
      ['id' => 'SwHEPkBgrFY', 'start' => 848, 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
    ]],
    ['title' => 'どうして僕らにはやる気がないのか(2021)', 'videos' => [
      ['id' => 'dCBeCwWNFeo', 'start' => 844, 'label' => 'Hello! Project Year-End Party 2021～GOOD BYE & HELLO！～ アンジュルムプレミアム', 'note' => '2021/12/30 中野サンプラザ'],
      ['id' => 'jPrEIcnbSh0', 'start' => 1574, 'label' => '宮本佳林 LIVE 2022春～アメジスト～', 'note' => '2022/5/22 めぐろパーシモンホール'],
      ['id' => 'KIVaJ-2PLWg', 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
    ]],
    ['title' => '優柔不断だね、Guilty', 'videos' => [
      ['id' => 'IUj6EhE-fP0', 'label' => '宮本佳林「優柔不断だね、Guilty」歌唱動画 -YouTube Edit-'],
    ]],
    ['title' => 'ハウリング', 'videos' => [
      ['id' => 'pvazTpOBDhA', 'label' => '宮本佳林 LIVE 2025～魔法のたからばこ～', 'note' => '2025/11/24 SOUND SHOWER ark 清水'],
    ]],
    ['title' => 'Happy Days', 'videos' => [
      ['id' => 'NgAsR--Sq1A', 'start' => 625, 'label' => '宮本佳林 LIVE 2023～ヒトリトイロ～', 'note' => '2023/2/5 めぐろパーシモンホール'],
      ['id' => 'g16tzOwbLvk', 'label' => '宮本佳林 LIVE 2023～ヒトリトイロ～', 'note' => '2023/2/5 めぐろパーシモンホール'],
      ['id' => 'WF8yuGi99dk', 'start' => 525, 'label' => '宮本佳林 Sweet Box 2025～Happy Valentine\'s Day!～「Macalon」', 'note' => '2025/2/16 第一ホテル両国「清澄」'],
    ]],
    ['title' => '規格外のロマンス', 'videos' => [
      ['id' => 'uUHfOe1RHuU', 'start' => 550, 'label' => 'M-line Special 2021～Make a Wish!～', 'note' => '2021/11/20 中野サンプラザ'],
      ['id' => 'UbMcoJswX5c', 'label' => '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～「Pink Puff」', 'note' => '2026/2/15 第一ホテル両国「清澄」'],
    ]],
    ['title' => 'タメライ', 'videos' => [
      ['id' => 'Jf5oOreaxsM', 'label' => '宮本佳林 LIVE 2021～ダリア～', 'note' => '2021/9/20 中野サンプラザ'],
    ]],
    ['title' => '未来のフィラメント', 'videos' => [
      ['id' => 'SMRhls7JXMY', 'start' => 2196, 'label' => 'M-line Special 2021～Make a Wish!～', 'note' => '2021/1/31 セルリアンタワー ボールルーム'],
      ['id' => 'VHLUIsEOy_M', 'start' => 177, 'label' => '宮本佳林 LIVE 2024春～Hello! Brand new me～RETURNS', 'note' => '2024/4/14 横浜Bay Hall'],
    ]],
    ['title' => '氷点下', 'videos' => [
      ['id' => 'UiyIyCzFlq8', 'start' => 507, 'label' => 'Hello! Project Year-End Party 2021～GOOD BYE & HELLO！～ Juice=Juiceプレミアム', 'note' => '2021/12/31 中野サンプラザ'],
      ['id' => 'VzVjzBjrpFI', 'start' => 1498, 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
      ['id' => 'kclqnIS-8ig', 'start' => 868, 'label' => '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～「Sugar Drop」', 'note' => '2026/2/15 第一ホテル両国「清澄」'],
    ]],
    ['title' => '落ちこぼれのガラクタだって', 'videos' => [
      ['id' => '_hAx2S5e8jk', 'label' => '宮本佳林 LIVE 2021～ダリア～', 'note' => '2021/9/20 中野サンプラザ'],
    ]],
    ['title' => '夜明けまでのララバイ', 'videos' => [
      ['id' => 'kr_BKCHqwm4', 'label' => '宮本佳林 LIVE 2023～ヒトリトイロ～', 'note' => '2023/2/5 めぐろパーシモンホール'],
    ]],
  ],
  '2nd Album Spancall' => [
    ['title' => 'ポラリス・コンパス', 'videos' => [
      ['id' => 'Qso4fxLR1iM', 'start' => 840, 'label' => '2ndアルバム発売記念イベント', 'note' => '2024/6/7 池袋サンシャインシティ'],
      ['id' => 'FM3evV_SUdE', 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
      ['id' => 'hims-Z89oBs', 'label' => '宮本佳林 Sweet Box 2025～Happy Valentine\'s Day!～「Macalon」', 'note' => '2025/2/16 第一ホテル両国「清澄」'],
    ]],
    ['title' => 'ソリスト・ダンス', 'videos' => [
      ['id' => 'Hf1PuziTG3U', 'label' => 'M-line Special 2024～Many well wishes～', 'note' => '2024/2/4 横浜Bay Hall'],
      ['id' => 'RZkv4troQaI', 'label' => 'M-line Special 2026 Spring～LOVELY～', 'note' => '2026/3/15 新宿ReNY'],
    ]],
    ['title' => 'バンビーナ・バンビーノ', 'videos' => [
      ['id' => '6njGwroq86Q', 'start' => 218, 'label' => '宮本佳林 LIVE 2023秋～Hello! Brand new me～', 'note' => '2023/10/7 Zepp DiverCity'],
    ]],
    ['title' => 'アンフェアな事情', 'videos' => [
      ['id' => 'eDQz_f4FtX0', 'label' => '宮本佳林 LIVE 2024春～Hello! Brand new me～RETURNS', 'note' => '2024/4/14 横浜Bay Hall'],
    ]],
    ['title' => 'BAD', 'videos' => [
      ['id' => 'MSBFAt5cnks', 'start' => 721, 'label' => 'M-line Special 2023～Magical Wish～', 'note' => '2023/8/31 タワーホール船堀'],
    ]],
    ['title' => 'Love Gravity', 'videos' => [
      ['id' => 'Y0yqs98SXGs', 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
    ]],
    ['title' => 'パラレルハート', 'videos' => [
      ['id' => 'rvoCR8Hanxg', 'label' => 'M-line Special 2023～Magical Wish～', 'note' => '2023/12/5 なかのZERO 大ホール'],
      ['id' => 'S2ZVHt3PL1E', 'label' => 'M-line Special 2025 Spring～move it on～', 'note' => '2025/3/2 新宿ReNY'],
    ]],
    ['title' => 'Lonely Bus', 'videos' => [
      ['id' => 'mckwPzbvlbA', 'label' => 'M-line Special 2025 Spring～move it on～', 'note' => '2025/3/2 新宿ReNY'],
    ]],
    ['title' => 'C＼C（シンデレラ＼コンプレックス）\'24 宮本佳林 Solo Ver.', 'videos' => [
      ['id' => '-uVXvjRWUqE', 'start' => 1541, 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
    ]],
    ['title' => 'ヴァンパイア', 'videos' => [
      ['id' => 'vBj1LIPFnHw', 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
    ]],
    ['title' => '愛しあわなきゃもったいない！', 'videos' => [
      ['id' => 'hCA0kIZZNYA', 'start' => 150, 'label' => '宮本佳林 LIVE 2024春～Hello! Brand new me～RETURNS', 'note' => '2024/4/14 横浜Bay Hall'],
    ]],
    ['title' => 'Beautiful Song', 'videos' => [
      ['id' => 'bBRipr0T0Wk', 'label' => '宮本佳林 LIVE 2023秋～Hello! Brand new me～', 'note' => '2023/10/7 Zepp DiverCity'],
    ]],
  ],
  '2nd Single なんてったって I Love You／ハウリング' => [
    ['title' => '少女K', 'videos' => [
      ['id' => 'u31Qyy7ataE', 'start' => 190, 'label' => '宮本佳林 LIVE 2024春～Hello! Brand new me～RETURNS', 'note' => '2024/4/14 横浜Bay Hall'],
    ]],
    ['title' => 'どうして僕らにはやる気がないのか(2019)', 'videos' => [
      ['id' => '9jkh74RSXZw', 'start' => 344, 'label' => '宮本佳林 LIVE TOUR～Karing～', 'note' => '2019/10/17 Zepp Tokyo'],
      ['id' => '5dJvQZM0h5Q', 'start' => 142, 'label' => '宮本佳林 LIVE 2021春～アマリリス～'],
    ]],
    ['title' => '氷点下(2019)', 'videos' => [
      ['id' => 'PDj4BOE5PA8', 'start' => 635, 'label' => '宮本佳林 LIVE 2021～ダリア～'],
    ]],
  ],
  '4th Single キケンな魔法 / ビースト!' => [
    ['title' => 'キケンな魔法', 'videos' => [
      ['id' => 'MG8n0fmkOfA', 'start' => 923, 'label' => 'M-line Special 2025 Spring～move it on～', 'note' => '2025/5/25 横浜Bay Hall'],
      ['id' => '3B132NwtK0k', 'label' => '宮本佳林 LIVE 2025～魔法のたからばこ～', 'note' => '2025/11/24 SOUND SHOWER ark 清水'],
    ]],
    ['title' => 'ビースト!', 'videos' => [
      ['id' => 'CalM6dEhg5c', 'start' => 1124, 'label' => '4thシングル発売記念 ミニライブ&一言お話し会イベント', 'note' => '2025/6/11 池袋・サンシャインシティ 噴水広場'],
    ]],
    ['title' => 'SUPER IDOL -Especial-(Single Ver.)', 'videos' => [
      ['id' => 'jfApSDdHTqo', 'start' => 853, 'label' => '4thシングル発売記念 ミニライブ&一言お話し会イベント', 'note' => '2025/6/11 池袋・サンシャインシティ 噴水広場'],
    ]],
    ['title' => 'ハートビジョン', 'videos' => [
      ['id' => 'qE-Jaj4FjT4', 'start' => 866, 'label' => '4thシングル発売記念 ミニライブ&一言お話し会イベント', 'note' => '2025/6/11 池袋・サンシャインシティ 噴水広場'],
    ]],
    ['title' => 'トランジットタイム', 'videos' => [
      ['id' => 'JpqJvTROc6E', 'start' => 991, 'label' => '4thシングル発売記念 ミニライブ&一言お話し会イベント', 'note' => '2025/6/11 池袋・サンシャインシティ 噴水広場'],
    ]],
  ],
  '5th HANAKIN/シャニカマー' => [
    ['title' => 'HANAKIN', 'videos' => [
      ['id' => '62G0epfiL-U', 'label' => '5th シングル発売記念イベント', 'note' => '2026/7/16 サンシャインシティ 噴水広場'],
    ]],
    ['title' => 'シャニカマー', 'videos' => [
      ['id' => '-z8V9jBsHNc', 'start' => 796, 'label' => '5th シングル発売記念イベント', 'note' => '2026/7/16 サンシャインシティ 噴水広場'],
    ]],
    ['title' => 'Spinel Hearts', 'videos' => [
      ['id' => 'dXhiDRnP1aQ', 'label' => '宮本佳林 LIVE 2025～魔法のたからばこ～', 'note' => '2025/11/24 SOUND SHOWER ark 清水'],
      ['id' => 'dRQ0mElf-js', 'start' => 1177, 'label' => '5th シングル発売記念イベント', 'note' => '2026/7/16 サンシャインシティ 噴水広場'],
    ]],
    ['title' => 'アンタイトル', 'videos' => [
      ['id' => 'N3lSmAuD8-k', 'label' => '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～「Sugar Drop」', 'note' => '2026/2/15 第一ホテル両国「清澄」'],
      ['id' => '6kRfzpxPAIs', 'start' => 1394, 'label' => '5th シングル発売記念イベント', 'note' => '2026/7/16 サンシャインシティ 噴水広場'],
    ]],
  ],
  'STAGE VANGUARD「ザ☆アイドル！」オリジナルサウンドトラック' => [
    ['title' => 'SUPER IDOL -Especial-', 'videos' => [
      ['id' => '0QUyUk8CGcA', 'start' => 242, 'label' => '宮本佳林 バースデーイベント2024～CONTINUE～', 'note' => '2024/12/1 新都市ホール'],
    ]],
  ],
  'コピンクス名義' => [
    ['title' => 'カリーナノッテ', 'videos' => [
      ['id' => 'yePXvMbtjO0', 'label' => '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～「Pink Puff」', 'note' => '2026/2/15 第一ホテル両国「清澄」'],
    ]],
  ],
  'Juice=Juice名義' => [
    ['title' => '銀色のテレパシー', 'videos' => [
      ['id' => 'lbs3pml3u58', 'label' => '宮本佳林 LIVE 2024～TWiNKLE～', 'note' => '2024/12/29 Zepp DiverCity'],
    ]],
  ],
  '恋するハローキティ' => [
    ['title' => 'Only choice', 'videos' => [
      ['id' => 'I43k9amkTpQ', 'start' => 453, 'label' => '宮本佳林 Sweet Box 2025～Happy Valentine\'s Day!～「Chocola」', 'note' => '2025/2/16 第一ホテル両国「清澄」'],
    ]],
  ],
  'その他' => [
    ['title' => 'チョコレート魂', 'sub' => '原曲：松浦亜弥', 'videos' => [
      ['id' => 'EY9CEiUyI2g', 'start' => 1047, 'label' => '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～「Pink Puff」', 'note' => '2026/2/15 第一ホテル両国「清澄」'],
    ]],
    ['title' => 'First Love', 'sub' => '原曲：宇多田ヒカル', 'videos' => [
      ['id' => 'rrfuqAZPaVQ', 'start' => 318, 'label' => 'Hello! Project 2020 Summer COVERS～The Ballad～', 'note' => '2020/8/3 中野サンプラザ'],
    ]],
    ['title' => '', 'videos' => [
      ['id' => 'AnYLrehZnGg', 'label' => '宮本佳林「やっちまいな」歌唱動画 -YouTube Edit-'],
      ['id' => 'cwDuYOGUvVU', 'label' => '宮本佳林 Sweet Box 2026～Happy Valentine\'s Day!～「Sugar Drop」', 'note' => '2026/2/15 第一ホテル両国「清澄」'],
    ]],
  ],
  'Hello! Project 2020 ～The Ballad～ Digest' => [
    ['title' => 'M', 'sub' => '原曲：PRINCESS PRINCESS', 'videos' => [
      ['id' => 'weVef0_y-4g', 'start' => 18, 'label' => 'さいたま市民会館おおみや', 'note' => '2020/10/11'],
      ['id' => '6wFxeVZSNgg', 'start' => 174, 'label' => 'Zepp Tokyo', 'note' => '2020/12/6'],
    ]],
    ['title' => 'オリビアを聴きながら', 'sub' => '原曲：杏里', 'videos' => [
      ['id' => 't8dlLao0K9Y', 'start' => 680, 'label' => 'Zepp Namba', 'note' => '2020/9/22'],
      ['id' => 'WsD3uzW20qg', 'start' => 260, 'label' => '愛知県芸術劇場', 'note' => '2020/10/25'],
      ['id' => 'XV-o0jJGmvI', 'start' => 113, 'label' => 'NTNシティホール', 'note' => '2020/11/21'],
    ]],
    ['title' => 'スローモーション', 'sub' => '原曲：中森明菜', 'videos' => [
      ['id' => 'S3pjBix-1mI', 'start' => 18, 'label' => '福井県県民ホール', 'note' => '2020/11/8'],
    ]],
    ['title' => 'ひこうき雲', 'sub' => '原曲：松任谷由実', 'videos' => [
      ['id' => 'Nehnkomh0xM', 'start' => 18, 'label' => 'ハーモニーホール座間', 'note' => '2020/10/18'],
    ]],
    ['title' => 'やさしさで溢れるように', 'sub' => '原曲：JUJU', 'videos' => [
      ['id' => 'KhlcOR-oLgU', 'start' => 175, 'label' => 'オリックス劇場', 'note' => '2020/11/7'],
      ['id' => 'rwrXvR0CfBE', 'start' => 140, 'label' => '名古屋市公会堂', 'note' => '2020/11/22'],
      ['id' => 'lnd_o6ALeGg', 'start' => 793, 'label' => 'セルリアンタワー', 'note' => '2020/11/23'],
      ['id' => 'kRqYs1ZZ85A', 'start' => 566, 'label' => '中野サンプラザ', 'note' => '2020/11/29'],
    ]],
    ['title' => '明日への手紙', 'sub' => '原曲：手嶌葵', 'videos' => [
      ['id' => 'hspf7W89p5A', 'start' => 545, 'label' => 'KOTORIホール', 'note' => '2020/9/26'],
      ['id' => 'Q8CrgHvWVb8', 'start' => 21, 'label' => 'Extra Number 日本武道館', 'note' => '2020/10/12'],
      ['id' => 'eBwtcaieCMM', 'start' => 227, 'label' => '松戸森のホール21', 'note' => '2020/12/5'],
    ]],
  ],
];

// 見出しの下に説明を付ける作品
$solo_desc = [
  'Hello! Project 2020 ～The Ballad～ Digest' =>
    '往年の名曲をソロ歌唱で披露するカバーコンサート「～The Ballad～」のダイジェスト映像です。宮本佳林さんが歌っている部分から再生されます。',
];
?>

<main>
  <h1>宮本佳林ソロ公式ライブ映像まとめ</h1>
  <p>宮本佳林公式YouTube・M-Line Musicから、宮本佳林さんがソロで歌っている公式ライブ映像をまとめました。<br>
  1曲ごとのカードに、その曲のライブ映像を並べています。一覧の公演名を押すと、カードの上でその映像が再生されます。</p>
  <p class="sc-section-desc">公演名・日付・会場は、各動画の概要欄や動画タイトルを参考に表記をそろえています。概要欄に書かれていないものは同じ公演の別の動画から補っているため、誤りが含まれている可能性があります。</p>

  <nav class="sc-toc">
    <ul>
      <?php $i = 0; foreach ($solo_sections as $name => $songs): $i++; ?>
      <li><a href="#sec<?= $i ?>"><?= vc_h($name) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <?php $i = 0; foreach ($solo_sections as $name => $songs): $i++; ?>
  <section class="sc-section" id="sec<?= $i ?>">
    <h2><?= vc_h($name) ?></h2>
    <?php if (isset($solo_desc[$name])): ?>
    <p class="sc-section-desc"><?= vc_h($solo_desc[$name]) ?></p>
    <?php endif; ?>
    <?= song_cards($songs) ?>
  </section>
  <?php endforeach; ?>
</main>
<div class="last-update">最終更新日：2026/08/21</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
