<?php
  $title = "DECA!BEAT（デカビート）非公式まとめ";
  $description = "ハロー！プロジェクトとEarth hacksのコラボレーションで生まれた特別ユニット「DECA!BEAT（デカビート）」の非公式まとめ。メンバーごとの紹介ポスト・新ビジュアル・TikTokの紹介動画・お披露目前のデカボなボイスを埋め込みでまとめ、YouTubeのMVやDANCE PRACTICE、グッズ紹介ポストへのリンクも掲載しています。";
  $jsonld_type = "CollectionPage";
  $brand = 'hello';
  $og_image = '/img/decabeat/ogp-1200x630.png';   // このページ専用のOGP画像
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>
<?php
// ------------------------------------------------------------------
// DECA!BEAT のデータ。期間限定ユニットで更新の予定がないため、
// スプレッドシートから一度だけ書き出したものをこのファイルが直接持っている。
// (Spreadsheet/ や Generated/ の生成物ではないので、直したいときはここを手で直す)
// Xの埋め込みは公式の <blockquote> をそのまま持つ($dc_tweet_html)。data-embed に入れておき、
// 選ばれたメンバーの分だけDOMに差し込む。差し替えに失敗しても引用テキストが残る。
// ------------------------------------------------------------------

$dc_members = [
  [
    'slug' => 'kirara_yonemura',
    'name' => '米村姫良々',
    'yomi' => 'よねむらきらら',
    'group' => 'OCHA NORMA',
    'friend' => 'キラニャ',
    'official' => 'https://helloproject.com/ochanorma/kirara_yonemura/',
    'instagram' => 'https://www.instagram.com/p/DVNJYG7Eydl/',
    'mission' => 'MISSION#1 移動の際に階段を利用する',
    'score' => '100',
    'spot' => '652916',
    'intro' => ['2028410164597129579', '2026-03-02'],
    'visual' => ['2095088783930876210', '2026-09-02'],
    'tiktok' => '7611005836357356821',
    'dareda' => ['2025774505914995070', '2026-02-23'],
    'dareda_no' => '⑦',
  ],
  [
    'slug' => 'minami_okamura',
    'name' => '岡村美波',
    'yomi' => 'おかむらみなみ',
    'group' => 'BEYOOOOONDS',
    'friend' => 'ミイメェ',
    'official' => 'https://helloproject.com/beyooooonds/minami_okamura/',
    'instagram' => 'https://www.instagram.com/p/DVQXHd7E2fy/',
    'mission' => 'MISSION#2 リサイクルのためにゴミを分別する',
    'score' => '74',
    'spot' => '653194',
    'intro' => ['2028664238454243540', '2026-03-03'],
    'visual' => ['2095118103336153138', '2026-09-02'],
    'tiktok' => '7611469573522492692',
    'dareda' => ['2025143477059944504', '2026-02-21'],
    'dareda_no' => '⑥',
  ],
  [
    'slug' => 'kisaki_ebata',
    'name' => '江端妃咲',
    'yomi' => 'えばたきさき',
    'group' => 'Juice=Juice',
    'friend' => 'エバネコ',
    'official' => 'https://helloproject.com/juicejuice/kisaki_ebata/',
    'instagram' => 'https://www.instagram.com/p/DVSVLJxkwXR/',
    'mission' => 'MISSION#3 買い物の際にマイバッグを利用する',
    'score' => '59',
    'spot' => '653195',
    'intro' => ['2029027249018855787', '2026-03-04'],
    'visual' => ['2095451311823278122', '2026-09-03'],
    'tiktok' => '7611752871318605077',
    'dareda' => ['2024321836063088710', '2026-02-19'],
    'dareda_no' => '⑤',
  ],
  [
    'slug' => 'runo_yofu',
    'name' => '豫風瑠乃',
    'yomi' => 'よふうるの',
    'group' => 'つばきファクトリー',
    'friend' => 'ルノコーン',
    'official' => 'https://helloproject.com/tsubakifactory/runo_yofu/',
    'instagram' => 'https://www.instagram.com/p/DVV3lpTkxYz/',
    'mission' => 'MISSION#4 牛肉の代わりに魚を食べる',
    'score' => '84',
    'spot' => '653280',
    'intro' => ['2029400475767062546', '2026-03-05'],
    'visual' => ['2095478263816867999', '2026-09-03'],
    'tiktok' => '7612262625329597716',
    'dareda' => ['2023637008669917602', '2026-02-17'],
    'dareda_no' => '④',
  ],
  [
    'slug' => 'hana_goto',
    'name' => '後藤花',
    'yomi' => 'ごとうはな',
    'group' => 'アンジュルム',
    'friend' => 'ハナペン',
    'official' => 'https://helloproject.com/angerme/hana_goto/',
    'instagram' => 'https://www.instagram.com/p/DVXbEpOkx1f/',
    'mission' => 'MISSION#5 マイボトルを利用する',
    'score' => '88',
    'spot' => '653282',
    'intro' => ['2029765570149974086', '2026-03-06'],
    'visual' => ['2095791994874343640', '2026-09-04'],
    'tiktok' => '7612486397647244564',
    'dareda' => ['2022874484286644401', '2026-02-15'],
    'dareda_no' => '③',
  ],
  [
    'slug' => 'ako_yumigeta',
    'name' => '弓桁朱琴',
    'yomi' => 'ゆみげたあこ',
    'group' => 'モーニング娘。\'26',
    'friend' => 'シシゲタ',
    'official' => 'https://helloproject.com/morningmusume/ako_yumigeta/',
    'instagram' => 'https://www.instagram.com/p/DVatzf-E9AG/',
    'mission' => 'MISSION#6 2Lの水をペットボトル1本で買う',
    'score' => '55',
    'spot' => '653295',
    'intro' => ['2030124792330998166', '2026-03-07'],
    'visual' => ['2095840357263282534', '2026-09-04'],
    'tiktok' => '7612960023349628181',
    'dareda' => ['2022139545064702373', '2026-02-13'],
    'dareda_no' => '②',
  ],
  [
    'slug' => 'rena_kamimura',
    'name' => '上村麗菜',
    'yomi' => 'かみむられな',
    'group' => 'ロージークロニクル',
    'friend' => 'ウサレナ',
    'official' => 'https://helloproject.com/rosychronicle/rena_kamimura/',
    'instagram' => 'https://www.instagram.com/p/DVdPXTLE3MR/',
    'mission' => 'MISSION#7 卵かけご飯を食べる',
    'score' => '21',
    'spot' => '653300',
    'intro' => ['2030509421403734220', '2026-03-08'],
    'visual' => ['2096155764264161518', '2026-09-05'],
    'tiktok' => '7613323791078329620',
    'dareda' => ['2021424691878220171', '2026-02-11'],
    'dareda_no' => '①',
  ],
];

$dc_videos = [
  [
    'title' => '『ワッチャ☆フューチャー』',
    'date' => '2026年3月10日',
    'url' => 'https://www.youtube.com/watch?v=gqgBqDDyPZ0',
    'id' => 'gqgBqDDyPZ0',
  ],
  [
    'title' => '『ワッチャ☆フューチャー』Music Video Making',
    'date' => '2026年3月10日',
    'url' => 'https://www.youtube.com/watch?v=VAzqgCGH8e4',
    'id' => 'VAzqgCGH8e4',
  ],
  [
    'title' => '『ワッチャ☆フューチャー』DANCE PRACTICE ver.',
    'date' => '2026年8月26日',
    'url' => 'https://www.youtube.com/watch?v=daXwOUr6bBs',
    'id' => 'daXwOUr6bBs',
  ],
  [
    'title' => '『ワッチャ☆フューチャー』MV密着',
    'date' => '2026年3月25日',
    'url' => 'https://www.youtube.com/watch?v=expaEkZZUgs&t=1054s',
    'id' => 'expaEkZZUgs',
    // 再生開始位置(秒)。URLの &t=1054s にあたるもの(埋め込みでは start= で渡す)
    'start' => 1054,
  ],
  [
    'title' => '『ワッチャ☆フューチャー』ダンスレッスン',
    'date' => '2026年4月3日',
    'url' => 'https://www.youtube.com/watch?v=wvDA5TWn-4M&t=338s',
    'id' => 'wvDA5TWn-4M',
    'start' => 338,
  ],
  [
    'title' => 'TOKYO GX ACTION イベント・ダンプラ動画撮影の裏側',
    'date' => '2026年9月10日',
    'url' => 'https://www.youtube.com/watch?v=83LCma6C8xs',
    'id' => '83LCma6C8xs',
  ],
];

$dc_posts = [
  [
    'cat' => '全メンバー解禁',
    'title' => '全メンバー解禁',
    'date' => '2026年2月25日',
    'url' => 'https://x.com/Earthhacks2023/status/2026462173564916219',
  ],
  [
    'cat' => 'デカボMISSION',
    'title' => 'デカボMISSION vol.1',
    'date' => '2026年7月9日',
    'url' => 'https://x.com/Earthhacks2023/status/2075070265688748109',
  ],
  [
    'cat' => 'グッズ紹介',
    'title' => 'オフィシャルグッズ発売決定！第１弾',
    'date' => '2026年3月16日',
    'url' => 'https://x.com/Earthhacks2023/status/2033539259106468203',
  ],
  [
    'cat' => 'グッズ紹介',
    'title' => 'オフィシャルグッズ発売決定！第２弾',
    'date' => '2026年3月18日',
    'url' => 'https://x.com/Earthhacks2023/status/2034100856816271392',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月12日',
    'url' => 'https://x.com/Earthhacks2023/status/2032041822227480741',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月13日',
    'url' => 'https://x.com/Earthhacks2023/status/2032371302590480542',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月14日',
    'url' => 'https://x.com/Earthhacks2023/status/2032796825288393203',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月15日',
    'url' => 'https://x.com/Earthhacks2023/status/2033172744297451818',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'ひなフェスオフショット',
    'date' => '2026年3月22日',
    'url' => 'https://x.com/Earthhacks2023/status/2035707039142326460',
  ],
  [
    'cat' => 'デカビートフレンズ',
    'title' => 'デカビートフレンズ集合',
    'date' => '2026年9月5日',
    'url' => 'https://x.com/Earthhacks2023/status/2096190978843607276',
  ],
  [
    'cat' => 'デカボMISSION',
    'title' => 'GROUP SHOTS 米村姫良々・弓桁朱琴',
    'date' => '2026年9月16日',
    'url' => 'https://x.com/Earthhacks2023/status/2100183391857688863',
  ],
  [
    'cat' => 'デカボMISSION',
    'title' => 'GROUP SHOTS 岡村美波・後藤花・上村麗菜',
    'date' => '2026年9月17日',
    'url' => 'https://x.com/Earthhacks2023/status/2100539941247988191',
  ],
];

$dc_sns = [
  [
    'title' => '公式プロフィール',
    'url' => 'https://co.earth-hacks.jp/decabeat/',
  ],
  [
    'title' => 'TikTok',
    'url' => 'https://www.tiktok.com/@decarbo46',
  ],
  [
    'title' => 'Earth hacks X',
    'url' => 'https://x.com/Earthhacks2023/',
  ],
  [
    'title' => 'Earth hacks Instagram',
    'url' => 'https://www.instagram.com/earthhacks.jp/',
  ],
  [
    'title' => 'DECA!BEATといっしょに！デカボミッション vol.1 | プラチナマップ',
    'url' => 'https://platinumaps.jp/d/deca-beat',
  ],
];

// Xの埋め込み用マークアップ(ツイートID => 公式の <blockquote>)。
// widgets.js が読めたときはこれが埋め込みに差し替わり、読めなければ引用のまま残る。
$dc_tweet_html = [
  '2028410164597129579' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>❤️ 米村 姫良々（OCHA NORMA）<br><br>デカビートの頼りになるリーダー！👩‍🏫<br>回収ガンでデカボエネルギーを集めます🔫<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/ocha_norma?src=hash&amp;ref_src=twsrc%5Etfw">#ocha_norma</a> <a href="https://x.com/hashtag/%E7%B1%B3%E6%9D%91%E5%A7%AB%E8%89%AF%E3%80%85?src=hash&amp;ref_src=twsrc%5Etfw">#米村姫良々</a> <a href="https://t.co/K8pANt10p2">pic.twitter.com/K8pANt10p2</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2028410164597129579?ref_src=twsrc%5Etfw">March 2, 2026</a></blockquote>',
  '2095088783930876210' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">＼🌼おまたせしました🌼／<br>「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>新ビジュアルでは、DECA!BEATメンバーと… <a href="https://t.co/tUrMsPOlvP">pic.twitter.com/tUrMsPOlvP</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2095088783930876210?ref_src=twsrc%5Etfw">September 2, 2026</a></blockquote>',
  '2025774505914995070' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！⑦🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br>ユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>本日は第７回目の投稿です！<br>誰か分かったらリプライで教えて下さいね🙊… <a href="https://t.co/zyZjY8NqX9">pic.twitter.com/zyZjY8NqX9</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2025774505914995070?ref_src=twsrc%5Etfw">February 23, 2026</a></blockquote>',
  '2028664238454243540' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>🩷岡村 美波（BEYOOOOONDS）<br><br>みんなのデカボを算出・解析するデカビートのキュートな頭脳担当❣️🕵️<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/BEYOOOOONDS?src=hash&amp;ref_src=twsrc%5Etfw">#BEYOOOOONDS</a> <a href="https://x.com/hashtag/%E5%B2%A1%E6%9D%91%E7%BE%8E%E6%B3%A2?src=hash&amp;ref_src=twsrc%5Etfw">#岡村美波</a> <a href="https://t.co/nNWcJB18nI">pic.twitter.com/nNWcJB18nI</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2028664238454243540?ref_src=twsrc%5Etfw">March 3, 2026</a></blockquote>',
  '2095118103336153138' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>今回は <a href="https://x.com/hashtag/%E5%B2%A1%E6%9D%91%E7%BE%8E%E6%B3%A2?src=hash&amp;ref_src=twsrc%5Etfw">#岡村美波</a> さんと、ミイメェ🐏が登場！<br><br>🎶デカビートフレンズとは？📣<br>みんなが集めたデカボのパワーで生まれた、<br>DECA! BEATの小さなお友達。<br>DECA!… <a href="https://t.co/WWuGHou4tF">pic.twitter.com/WWuGHou4tF</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2095118103336153138?ref_src=twsrc%5Etfw">September 2, 2026</a></blockquote>',
  '2025143477059944504' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！⑥🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br>ユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>本日は第６回目の投稿です！<br>誰か分かったらリプライで教えて下さいね🙊<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a>… <a href="https://t.co/IhJz6TWF09">pic.twitter.com/IhJz6TWF09</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2025143477059944504?ref_src=twsrc%5Etfw">February 21, 2026</a></blockquote>',
  '2029027249018855787' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>💛江端 妃咲（Juice=Juice）<br><br>日常に隠れたデカボをスカウターであざとく見つける！👮😎<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/juicejuice?src=hash&amp;ref_src=twsrc%5Etfw">#juicejuice</a> <a href="https://x.com/hashtag/%E6%B1%9F%E7%AB%AF%E5%A6%83%E5%92%B2?src=hash&amp;ref_src=twsrc%5Etfw">#江端妃咲</a> <a href="https://t.co/tHZWzvkGRl">pic.twitter.com/tHZWzvkGRl</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2029027249018855787?ref_src=twsrc%5Etfw">March 4, 2026</a></blockquote>',
  '2095451311823278122' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>今回は <a href="https://x.com/hashtag/%E6%B1%9F%E7%AB%AF%E5%A6%83%E5%92%B2?src=hash&amp;ref_src=twsrc%5Etfw">#江端妃咲</a> さんと、エバネコ🐈‍⬛が登場！<br><br>🎶デカビートフレンズとは？📣<br>みんなが集めたデカボのパワーで生まれた、<br>DECA! BEATの小さなお友達。<br>DECA!… <a href="https://t.co/6Sohw1AkPD">pic.twitter.com/6Sohw1AkPD</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2095451311823278122?ref_src=twsrc%5Etfw">September 3, 2026</a></blockquote>',
  '2024321836063088710' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！⑤🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br> ユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>本日は第５回目の投稿です！<br>誰か分かったらリプライで教えて下さいね🙊<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a>… <a href="https://t.co/VWdaOOj6nL">pic.twitter.com/VWdaOOj6nL</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2024321836063088710?ref_src=twsrc%5Etfw">February 19, 2026</a></blockquote>',
  '2029765570149974086' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>🩵後藤 花（アンジュルム）<br><br>デカボを発見したらキックボードで人一倍元気に、現場に急行🛴💭<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/%E3%82%A2%E3%83%B3%E3%82%B8%E3%83%A5%E3%83%AB%E3%83%A0?src=hash&amp;ref_src=twsrc%5Etfw">#アンジュルム</a> <a href="https://x.com/hashtag/%E5%BE%8C%E8%97%A4%E8%8A%B1?src=hash&amp;ref_src=twsrc%5Etfw">#後藤花</a> <a href="https://t.co/BMbjGHmOVZ">pic.twitter.com/BMbjGHmOVZ</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2029765570149974086?ref_src=twsrc%5Etfw">March 6, 2026</a></blockquote>',
  '2095478263816867999' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>今回は <a href="https://x.com/hashtag/%E8%B1%AB%E9%A2%A8%E7%91%A0%E4%B9%83?src=hash&amp;ref_src=twsrc%5Etfw">#豫風瑠乃</a> さんと、ルノコーン🦄が登場！<br><br>🎶デカビートフレンズとは？📣<br>みんなが集めたデカボのパワーで生まれた、<br>DECA! BEATの小さなお友達。<br>DECA!… <a href="https://t.co/Uuj4n7wZ78">pic.twitter.com/Uuj4n7wZ78</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2095478263816867999?ref_src=twsrc%5Etfw">September 3, 2026</a></blockquote>',
  '2023637008669917602' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！④🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br> ユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>本日は第４回目の投稿です！<br>誰か分かったらリプライで教えて下さいね🙊<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a>… <a href="https://t.co/FLr2EtWox3">pic.twitter.com/FLr2EtWox3</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2023637008669917602?ref_src=twsrc%5Etfw">February 17, 2026</a></blockquote>',
  '2029400475767062546' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>💛豫風 瑠乃（つばきファクトリー）<br><br>メガホンを使い、デッカいボイスで見つけたデカボをメンバーに伝えるの！📢<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/%E3%81%A4%E3%81%B0%E3%81%8D%E3%83%95%E3%82%A1%E3%82%AF%E3%83%88%E3%83%AA%E3%83%BC?src=hash&amp;ref_src=twsrc%5Etfw">#つばきファクトリー</a> <a href="https://x.com/hashtag/%E8%B1%AB%E9%A2%A8%E7%91%A0%E4%B9%83?src=hash&amp;ref_src=twsrc%5Etfw">#豫風瑠乃</a> <a href="https://t.co/fmoaBqGUXg">pic.twitter.com/fmoaBqGUXg</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2029400475767062546?ref_src=twsrc%5Etfw">March 5, 2026</a></blockquote>',
  '2095791994874343640' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>今回は <a href="https://x.com/hashtag/%E5%BE%8C%E8%97%A4%E8%8A%B1?src=hash&amp;ref_src=twsrc%5Etfw">#後藤花</a> さんと、ハナペン🐧が登場！<br><br>🎶デカビートフレンズとは？📣<br>みんなが集めたデカボのパワーで生まれた、<br>DECA!BEATの小さなお友達。… <a href="https://t.co/yxHBtuQwsZ">pic.twitter.com/yxHBtuQwsZ</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2095791994874343640?ref_src=twsrc%5Etfw">September 4, 2026</a></blockquote>',
  '2022874484286644401' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！③🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br> ユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>本日は第３回目の投稿です！<br>誰か分かったらリプライで教えて下さいね🙊<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a>… <a href="https://t.co/wrpWRUYqGa">pic.twitter.com/wrpWRUYqGa</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2022874484286644401?ref_src=twsrc%5Etfw">February 15, 2026</a></blockquote>',
  '2030124792330998166' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>❤️弓桁 朱琴（モーニング娘。’26）<br><br>でっかいスタンプでデカボを認定📛<br>みんなのデカボを応援する！👊🏻🔥<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/%E3%83%A2%E3%83%BC%E3%83%8B%E3%83%B3%E3%82%B0%E5%A8%9826?src=hash&amp;ref_src=twsrc%5Etfw">#モーニング娘26</a> <a href="https://x.com/hashtag/%E5%BC%93%E6%A1%81%E6%9C%B1%E7%90%B4?src=hash&amp;ref_src=twsrc%5Etfw">#弓桁朱琴</a> <a href="https://t.co/blpDmyPnSG">pic.twitter.com/blpDmyPnSG</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2030124792330998166?ref_src=twsrc%5Etfw">March 7, 2026</a></blockquote>',
  '2095840357263282534' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>今回は <a href="https://x.com/hashtag/%E5%BC%93%E6%A1%81%E6%9C%B1%E7%90%B4?src=hash&amp;ref_src=twsrc%5Etfw">#弓桁朱琴</a> さんと、シシゲタ🦁が登場！<br><br>🎶デカビートフレンズとは？📣<br>みんなが集めたデカボのパワーで生まれた、<br>DECA!BEATの小さなお友達。… <a href="https://t.co/UBGbBGIoND">pic.twitter.com/UBGbBGIoND</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2095840357263282534?ref_src=twsrc%5Etfw">September 4, 2026</a></blockquote>',
  '2022139545064702373' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！②🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br> ユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>本日は第２回目の投稿です！<br>誰か分かったらリプライで教えて下さいね🙊<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a>… <a href="https://t.co/4EtQk69jTD">pic.twitter.com/4EtQk69jTD</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2022139545064702373?ref_src=twsrc%5Etfw">February 13, 2026</a></blockquote>',
  '2030509421403734220' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> メンバー紹介🌏<br>💛上村 麗菜（ロージークロニクル）<br><br>スツールを使って高いところから離れたデカボもくまなくチェック🪜👀<a href="https://x.com/hashtag/Earthhacks?src=hash&amp;ref_src=twsrc%5Etfw">#Earthhacks</a> <a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> <a href="https://x.com/hashtag/%E3%83%8F%E3%83%AD%E3%83%97%E3%83%AD?src=hash&amp;ref_src=twsrc%5Etfw">#ハロプロ</a> <a href="https://x.com/hashtag/Helloproject?src=hash&amp;ref_src=twsrc%5Etfw">#Helloproject</a> <a href="https://x.com/hashtag/%E3%83%AD%E3%83%BC%E3%82%B8%E3%83%BC%E3%82%AF%E3%83%AD%E3%83%8B%E3%82%AF%E3%83%AB?src=hash&amp;ref_src=twsrc%5Etfw">#ロージークロニクル</a> <a href="https://x.com/hashtag/%E4%B8%8A%E6%9D%91%E9%BA%97%E8%8F%9C?src=hash&amp;ref_src=twsrc%5Etfw">#上村麗菜</a> <a href="https://t.co/Ub4HwKNaAX">pic.twitter.com/Ub4HwKNaAX</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2030509421403734220?ref_src=twsrc%5Etfw">March 8, 2026</a></blockquote>',
  '2096155764264161518' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">「DECA!BEATといっしょに！デカボMISSION vol.1」<br>5,000ACTION達成のでっかいボーナスとして、DECA!BEAT新ビジュアルを公開🪞✨<br><br>今回は <a href="https://x.com/hashtag/%E4%B8%8A%E6%9D%91%E9%BA%97%E8%8F%9C?src=hash&amp;ref_src=twsrc%5Etfw">#上村麗菜</a> さんと、ウサレナ🐰が登場！<br><br>🎶デカビートフレンズとは？📣<br>みんなが集めたデカボのパワーで生まれた、<br>DECA!BEATの小さなお友達。… <a href="https://t.co/3R8I72qHRj">pic.twitter.com/3R8I72qHRj</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2096155764264161518?ref_src=twsrc%5Etfw">September 5, 2026</a></blockquote>',
  '2021424691878220171' =>
    '<blockquote class="twitter-tweet"><p lang="ja" dir="ltr">🌏<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a> だーれだ！①🌏<br>デカボ×ビートで未来を変える!? <br>Earth hacks × ハロー！プロジェクト 特別ユニット結成<br><br> 本日よりユニットメンバーのデカボなエピソードと、デカボなボイスを１人ずつお届け✉️<br>誰か分かったらリプライで教えて下さいね🙊<a href="https://x.com/hashtag/%E3%83%87%E3%82%AB%E3%83%93%E3%83%BC%E3%83%88?src=hash&amp;ref_src=twsrc%5Etfw">#デカビート</a>… <a href="https://t.co/mmB98r21Hz">pic.twitter.com/mmB98r21Hz</a></p>&mdash; Earth hacks【公式】 (@Earthhacks2023) <a href="https://x.com/Earthhacks2023/status/2021424691878220171?ref_src=twsrc%5Etfw">February 11, 2026</a></blockquote>',
];

/** 埋め込みポストの投稿元(全ポスト Earth hacks 公式) */
const DC_X_ACCOUNT = 'Earthhacks2023';
const DC_TIKTOK_ACCOUNT = 'decarbo46';
/** デカボミッション vol.1 のマップ(各メンバーのミッションは ?spot= で開く) */
const DC_MISSION_URL = 'https://platinumaps.jp/d/deca-beat';

/**
 * Xにセンシティブ判定をかけられたポスト(埋め込みAPIが TweetTombstone を返す)。
 * Xアプリ以外には出さない扱いなので、本文の引用も出さずリンクだけにする。
 * 判定が解除されたらこの配列から外せば元どおり埋め込みになる。
 */
const DC_NO_EMBED = ['2030124792330998166'];   // 弓桁朱琴 メンバー紹介ポスト(2026-03-07)

function dc_h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** 2026-03-02 → 2026年3月2日 */
function dc_jdate(string $ymd): string {
    [$y, $m, $d] = array_map('intval', explode('-', $ymd));
    return "{$y}年{$m}月{$d}日";
}

/** 下部の公式リンク: URLからアイコンを決める */
function dc_sns_icon(string $url): string {
    if (str_contains($url, 'tiktok.com'))    return icon('tiktok-brand', 'ic ic-20');
    if (str_contains($url, 'instagram.com')) return icon('instagram-brand', 'ic ic-20');
    if (str_contains($url, 'x.com'))         return icon('x', 'ic ic-20');
    return icon('link-brand', 'ic ic-20');
}

/**
 * 埋め込み1枠。中身はJSが後から入れる(選択されたメンバーの分だけ読み込む)。
 * 埋め込みが表示できない環境でも辿れるように、元ポストへのリンクは常に出す。
 */
function dc_embed(string $type, string $label, string $id, string $url, string $linktext, string $extra = '', string $embed = '', string $slot = ''): string {
    // 埋め込みのマークアップはDOMに置かず data-embed に持たせ、選ばれたときだけ差し込む。
    // 21件をDOMに置くと widgets.js が全件まとめてXに取りにいき、弾かれて何も出なくなる。
    return '<div class="dc-embed" data-type="' . $type . '" data-id="' . dc_h($id) . '"'
         . ($embed === '' ? '' : ' data-embed="' . dc_h($embed) . '"') . '>'
         . '<h4>' . dc_h($label) . '</h4>'
         . '<div class="dc-slot">' . $slot . '</div>'
         . '<p class="dc-embed-link"><a href="' . dc_h($url) . '" target="_blank" rel="noopener noreferrer">'
         . dc_h($linktext) . '</a></p>'
         . $extra
         . '</div>';
}

function dc_x_embed(string $label, array $tw): string {
    global $dc_tweet_html;
    $url = 'https://x.com/' . DC_X_ACCOUNT . '/status/' . $tw[0];
    $link = 'Xで見る（' . dc_jdate($tw[1]) . '）';
    if (in_array($tw[0], DC_NO_EMBED, true)) {
        return dc_embed('gone', $label, $tw[0], $url, $link, '', '',
            '<p class="dc-gone">このポストはXでセンシティブな内容として扱われているため、'
            . '埋め込みでは表示できません。Xアプリでは見られます。</p>');
    }
    return dc_embed('x', $label, $tw[0], $url, $link, '', $dc_tweet_html[$tw[0]] ?? '');
}
?>

<main>
  <h1>DECA!BEAT（デカビート）非公式まとめ</h1>

  <p class="dc-lead">ハロー！プロジェクトとEarth hacksがコラボレーションして生まれたDECA!BEATの、各メンバーの紹介ポスト・新ビジュアル・TikTokでの紹介動画・お披露目前のデカボなボイスを見やすくまとめています。<br>
  またYouTubeのMVやDANCE PRACTICE・オフショットの動画もその場で再生でき、そのほかグッズ紹介などのポストもカードで並べています。</p>

  <details class="dc-about">
    <summary>DECA!BEATとは（公式プロフィールより）</summary>
    <blockquote cite="https://co.earth-hacks.jp/decabeat/">
      <p>地球の未来を歌い続けてきたハロー！プロジェクトと「無理なく楽しく」脱炭素の選択肢を広げるEarth hacksがコラボレーション！ハロー！プロジェクトの7つのグループから未来の地球を担うメンバーが集まって新ユニット「デカビート」としてみんなの「デカボ」を応援します。</p>
      <p class="dc-cite"><a href="https://co.earth-hacks.jp/decabeat/" target="_blank" rel="noopener noreferrer"><?= icon('link-brand', 'ic ic-20') ?>DECA!BEAT 公式プロフィール</a></p>
    </blockquote>
  </details>

  <!-- メンバー -->
  <section class="dc-section" id="members">
    <h2>メンバー</h2>
    <p>メンバーを選ぶと、そのメンバーの紹介ポスト・新ビジュアル・紹介動画・お披露目前の「だーれだ」ポストが表示されます。</p>

    <div class="tab-buttons dc-tabs" id="dc-member-tabs">
      <?php foreach ($dc_members as $m): ?>
      <button type="button" data-slug="<?= dc_h($m['slug']) ?>">
        <span class="dc-btn-name"><?= dc_h($m['name']) ?></span>
        <span class="dc-btn-friend"><?= dc_h($m['friend']) ?></span>
      </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($dc_members as $m): ?>
    <div class="dc-panel" id="dc-panel-<?= dc_h($m['slug']) ?>" hidden>
      <h3 class="dc-name"><?= dc_h($m['name']) ?><span class="dc-yomi"><?= dc_h($m['yomi']) ?></span></h3>
      <p class="dc-meta"><?= dc_h($m['group']) ?><span class="dc-friend">デカビートフレンズ：<?= dc_h($m['friend']) ?></span></p>
      <p class="dc-mission"><a href="<?= dc_h(DC_MISSION_URL . '?culture=ja&spot=' . $m['spot']) ?>" target="_blank" rel="noopener noreferrer"><?= dc_h($m['mission']) ?>（デカボスコア<?= dc_h($m['score']) ?>％）</a></p>
      <p class="dc-profile-links">
        <a href="<?= dc_h($m['official']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('link-brand', 'ic ic-20') ?>ハロー！プロジェクト公式プロフィール</a>
        <?php // シェア先URLは表示中のメンバーに合わせてJSが入れる ?>
        <a class="dc-share" target="_blank" rel="noopener noreferrer"
           data-text="<?= dc_h($m['name'] . '（' . $m['friend'] . '）｜DECA!BEAT 非公式まとめ #Earthhacks #デカビート') ?>"><?= icon('x', 'ic ic-20') ?>Xでシェア</a>
      </p>
      <div class="dc-embeds">
        <?= dc_x_embed('メンバー紹介ポスト', $m['intro']) ?>
        <?= dc_x_embed('新ビジュアル', $m['visual']) ?>
        <?php // Instagramは同じ動画なので、紹介動画の枠の中に並べる ?>
        <?php // TikTokの埋め込みは表示するといきなり再生が始まるので、押されるまで読み込まない ?>
        <?= dc_embed('tiktok', '紹介動画（TikTok）', $m['tiktok'],
              'https://www.tiktok.com/@' . DC_TIKTOK_ACCOUNT . '/video/' . $m['tiktok'], 'TikTokで見る',
              '<p class="dc-embed-link"><a href="' . dc_h($m['instagram']) . '" target="_blank" rel="noopener noreferrer">Instagram（同じ動画）</a></p>',
              '',
              '<button type="button" class="dc-tiktok-play">' . icon('tiktok-brand', 'ic dc-tiktok-icon')
              . '<span>▶ 紹介動画を再生</span></button>') ?>
        <?= dc_x_embed('お披露目前の#デカビート だーれだ！' . $m['dareda_no'], $m['dareda']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </section>

  <!-- YouTube -->
  <section class="dc-section" id="youtube">
    <h2>YouTube</h2>
    <p>タイトルを選んで再生ボタンを押すと、その場で再生されます。</p>
    <div class="tab-buttons dc-tabs" id="dc-video-tabs">
      <?php foreach ($dc_videos as $i => $v): ?>
      <button type="button" data-i="<?= $i ?>"><?= dc_h($v['title']) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="dc-player" id="dc-player"></div>
    <p class="dc-video-info" id="dc-video-info"></p>
    <noscript>
      <ul class="dc-video-list">
        <?php foreach ($dc_videos as $v): ?>
        <li><a href="<?= dc_h($v['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('youtube-brand', 'ic ic-20') ?><?= dc_h($v['title']) ?></a>（<?= dc_h($v['date']) ?>）</li>
        <?php endforeach; ?>
      </ul>
    </noscript>
  </section>

  <!-- ポスト -->
  <section class="dc-section" id="posts">
    <h2>ポスト</h2>
    <p>メンバー紹介以外のポストです。押すとXの元ポストが開きます。</p>
    <div class="card-grid">
      <?php foreach ($dc_posts as $p): ?>
      <div class="card dc-post">
        <?php if (!str_starts_with($p['title'], $p['cat'])): // タイトルと同じ分類は出さない ?>
        <span class="dc-post-cat"><?= dc_h($p['cat']) ?></span>
        <?php endif; ?>
        <a class="dc-post-link" href="<?= dc_h($p['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('x', 'ic ic-20') ?><?= dc_h($p['title']) ?></a>
        <p class="dc-post-date"><?= dc_h($p['date']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 公式リンク -->
  <section class="dc-section" id="official">
    <h2>公式リンク</h2>
    <ul class="dc-sns">
      <?php foreach ($dc_sns as $s): ?>
      <li><a href="<?= dc_h($s['url']) ?>" target="_blank" rel="noopener noreferrer"><?= dc_sns_icon($s['url']) ?><?= dc_h($s['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
</main>

<script>
(function () {
  var VIDEOS = <?= json_encode($dc_videos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  /* ---- メンバー ---- */

  // 埋め込みは選択されたメンバーの分だけ読み込む(7人分を一度に読むと重いため)
  function loadEmbeds(panel) {
    var needTweets = false;
    panel.querySelectorAll('.dc-embed').forEach(function (box) {
      if (box.dataset.loaded) return;
      box.dataset.loaded = '1';

      // TikTokは押されたときに読み込む(表示した時点で再生が始まってしまうため)
      if (box.dataset.type !== 'x') return;

      // Xは <blockquote> を差し込んでからウィジェットに変換させる。
      // 変換に失敗しても引用テキストが残るので、読み込み中の表示は要らない。
      var slot = box.querySelector('.dc-slot');
      slot.innerHTML = box.dataset.embed;
      loadTwitterWidget(function () { window.twttr.widgets.load(slot); });
      needTweets = true;
    });

    if (needTweets) hideQuotesWhenEmbedded(panel);
  }

  // widgets.js は必要になってから1回だけ読む
  var twQueue = null;
  function loadTwitterWidget(callback) {
    if (window.twttr && window.twttr.widgets) { callback(); return; }
    if (twQueue) { twQueue.push(callback); return; }
    twQueue = [callback];
    var s = document.createElement('script');
    s.src = 'https://platform.twitter.com/widgets.js';
    s.charset = 'utf-8';
    s.onload = function () {
      var queued = twQueue;
      twQueue = null;
      queued.forEach(function (f) { f(); });
    };
    document.head.appendChild(s);
  }

  // 埋め込みに差し替わった枠の引用を消す(X側でも消えるが、残ると二重に見えるので)
  function hideQuotesWhenEmbedded(panel) {
    var tries = 0;
    var timer = setInterval(function () {
      var pending = false;
      panel.querySelectorAll('.dc-embed[data-type="x"] .dc-slot').forEach(function (slot) {
        var quote = slot.querySelector('blockquote.twitter-tweet');
        if (!quote || quote.hidden) return;
        var embed = slot.querySelector('div.twitter-tweet');
        if (embed && embed.getBoundingClientRect().height > 0) {
          quote.hidden = true;
        } else {
          pending = true;
        }
      });
      if (!pending || ++tries > 10) clearInterval(timer);
    }, 1500);
  }

  var memberTabs = document.getElementById('dc-member-tabs');

  function selectMember(slug, updateHash) {
    var panel = document.getElementById('dc-panel-' + slug);
    if (!panel) return false;
    memberTabs.querySelectorAll('button').forEach(function (b) {
      b.classList.toggle('active', b.dataset.slug === slug);
    });
    document.querySelectorAll('.dc-panel').forEach(function (p) {
      p.hidden = (p !== panel);
    });
    loadEmbeds(panel);
    // Xのシェアボタン。共有先はいま見ているメンバーのURLにする
    var share = panel.querySelector('.dc-share');
    if (share) {
      // 本文とURLをまとめて text に入れる(url/hashtags を分けるとXが並び順を決めてしまう)
      share.href = 'https://x.com/intent/post?text='
        + encodeURIComponent(share.dataset.text + '\n' + location.origin + location.pathname + '#' + slug);
    }
    // 共有できるようにURLへ反映する(履歴は増やさない)
    if (updateHash && window.history.replaceState) {
      window.history.replaceState(null, '', '#' + slug);
    }
    return true;
  }

  memberTabs.addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (btn) selectMember(btn.dataset.slug, true);
  });

  // TikTok: 押されたらその場で読み込む
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.dc-tiktok-play');
    if (!btn) return;
    var f = document.createElement('iframe');
    f.src = 'https://www.tiktok.com/embed/v2/' + btn.closest('.dc-embed').dataset.id;
    f.title = 'TikTokの紹介動画';
    f.allow = 'encrypted-media; fullscreen; picture-in-picture';
    btn.replaceWith(f);
  });

  window.addEventListener('hashchange', function () {
    if (selectMember(decodeURIComponent(location.hash.slice(1)), false)) scrollToTabs();
  });

  // メンバー指定のURLで来たとき、他のメンバーに移れる選択ボタンごと見えるようにする
  function scrollToTabs() {
    window.scrollTo(0, memberTabs.getBoundingClientRect().top + window.scrollY - 12);
  }

  /* ---- YouTube ---- */

  var player = document.getElementById('dc-player');
  var info = document.getElementById('dc-video-info');
  var current = 0;

  function mmss(sec) {
    return Math.floor(sec / 60) + ':' + ('0' + (sec % 60)).slice(-2);
  }

  function showVideo(i, play) {
    var v = VIDEOS[i];
    if (!v) return;
    current = i;
    document.querySelectorAll('#dc-video-tabs button').forEach(function (b) {
      b.classList.toggle('active', Number(b.dataset.i) === i);
    });
    player.innerHTML = '';
    if (play) {
      var f = document.createElement('iframe');
      // 開始位置の指定があるものは start= で渡す(URLの &t=◯s は埋め込みでは効かない)
      f.src = 'https://www.youtube-nocookie.com/embed/' + v.id + '?autoplay=1'
        + (v.start ? '&start=' + v.start : '');
      f.title = v.title;
      f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      f.allowFullscreen = true;
      player.appendChild(f);
    } else {
      // 押されるまでYouTubeのiframeは読み込まない
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'dc-thumb';
      btn.setAttribute('aria-label', v.title + ' を再生');
      btn.innerHTML = '<img src="https://i.ytimg.com/vi/' + v.id + '/hqdefault.jpg" alt="" loading="lazy">'
        + '<span class="dc-play">▶ 再生' + (v.start ? '（' + mmss(v.start) + 'から）' : '') + '</span>';
      btn.addEventListener('click', function () { showVideo(current, true); });
      player.appendChild(btn);
    }
    info.innerHTML = '';
    var a = document.createElement('a');
    a.href = v.url;
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
    a.textContent = v.title;
    info.appendChild(a);
    info.appendChild(document.createTextNode('（' + v.date + '）'));
  }

  // タイトルを選んだ時点では再生しない(サムネイルの▶を押してから再生する)
  document.getElementById('dc-video-tabs').addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (btn) showVideo(Number(btn.dataset.i), false);
  });

  /* ---- 初期表示 ---- */

  var hash = decodeURIComponent(location.hash.slice(1));
  if (!hash || !selectMember(hash, false)) {
    selectMember(memberTabs.querySelector('button').dataset.slug, false);
  } else {
    scrollToTabs();
    // Webフォントの読み込みで位置がずれることがあるので、まだ動かされていなければ合わせ直す
    var landed = window.scrollY;
    window.addEventListener('load', function () {
      if (Math.abs(window.scrollY - landed) < 40) scrollToTabs();
    });
  }
  showVideo(0, false);
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
