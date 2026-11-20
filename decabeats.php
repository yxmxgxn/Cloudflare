<?php
  $title = "DECA!BEAT（デカビート）非公式まとめ";
  $description = "ハロー！プロジェクトとEarth hacksのコラボレーションで生まれた特別ユニット「DECA!BEAT（デカビート）」の非公式まとめ。メンバーごとの紹介ポスト・新ビジュアル・TikTokの紹介動画・お披露目前のデカボなボイスを埋め込みでまとめ、YouTubeのMVやDANCE PRACTICE、グッズ紹介ポストへのリンクも掲載しています。";
  $jsonld_type = "CollectionPage";
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
    'no' => '01',
    'leader' => true,
    'catch' => ['デカビートの頼りになるリーダー！', '回収ガンでデカボエネルギーを集める。'],
    'name' => '米村姫良々',
    'yomi' => 'よねむらきらら',
    'group' => 'OCHA NORMA',
    'friend' => 'キラニャ',
    'friend_en' => 'KIRA-NYA',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デカビートフレンズのリーダー！', '軽やかな動きで、階段を素早く登るよ！'],
    'official' => 'https://helloproject.com/ochanorma/kirara_yonemura/',
    'instagram' => 'https://www.instagram.com/p/DVNJYG7Eydl/',
    'action' => 'なるべく階段を使う！',
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
    'no' => '02',
    'catch' => ['みんなのデカボを算出・解析する', 'デカビートのキュートな頭脳担当。'],
    'name' => '岡村美波',
    'yomi' => 'おかむらみなみ',
    'group' => 'BEYOOOOONDS',
    'friend' => 'ミイメェ',
    'friend_en' => 'MII-MEE',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デカビートフレンズのおねえさんひつじ。', 'しっかりものでゴミの分別が得意！'],
    'official' => 'https://helloproject.com/beyooooonds/minami_okamura/',
    'instagram' => 'https://www.instagram.com/p/DVQXHd7E2fy/',
    'action' => 'アンティーク・蚤の市に行く！',
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
    'no' => '03',
    'catch' => ['日常に隠れたデカボを', 'スカウターであざとく見つける！'],
    'name' => '江端妃咲',
    'yomi' => 'えばたきさき',
    'group' => 'Juice=Juice',
    'friend' => 'エバネコ',
    'friend_en' => 'EBA-NEKO',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デカビートフレンズのあざとキャット。', 'お買い物は絶対マイバッグだよ'],
    'official' => 'https://helloproject.com/juicejuice/kisaki_ebata/',
    'instagram' => 'https://www.instagram.com/p/DVSVLJxkwXR/',
    'action' => 'マイバッグを持ち歩く！',
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
    'no' => '04',
    'catch' => ['メガホンを使い、デッカいボイスで', '見つけたデカボをメンバーに伝えるの！'],
    'name' => '豫風瑠乃',
    'yomi' => 'よふうるの',
    'group' => 'つばきファクトリー',
    'friend' => 'ルノコーン',
    'friend_en' => 'RUNO-CORN',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デっカいボイスが自慢のユニコーン。', 'お魚が大好きでいっぱい食べルノー！'],
    'official' => 'https://helloproject.com/tsubakifactory/runo_yofu/',
    'instagram' => 'https://www.instagram.com/p/DVV3lpTkxYz/',
    'action' => '一駅分歩く！',
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
    'no' => '05',
    'catch' => ['デカボを発見したらキックボードで', '人一倍元気に、現場に急行！'],
    'name' => '後藤花',
    'yomi' => 'ごとうはな',
    'group' => 'アンジュルム',
    'friend' => 'ハナペン',
    'friend_en' => 'HANA-PEN',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デカビートフレンズのニコニコ元気担当！', '水色のマイボトルがお気に入り♡'],
    'official' => 'https://helloproject.com/angerme/hana_goto/',
    'instagram' => 'https://www.instagram.com/p/DVXbEpOkx1f/',
    'action' => 'マイボトルを使う！',
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
    'no' => '06',
    'catch' => ['デッカいスタンプでデカボを認定！', 'みんなのデカボを応援する！'],
    'name' => '弓桁朱琴',
    'yomi' => 'ゆみげたあこ',
    'group' => 'モーニング娘。\'26',
    'friend' => 'シシゲタ',
    'friend_en' => 'SHISHI-GETA',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デカビートフレンズのメラメラ応援団長。', '2Lのペットボトルを軽々持ち上げる！'],
    'official' => 'https://helloproject.com/morningmusume/ako_yumigeta/',
    'instagram' => 'https://www.instagram.com/p/DVatzf-E9AG/',
    'action' => 'ショートヘアーで、ドライヤー節電！',
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
    'no' => '07',
    'catch' => ['スツールを使って高いところから', '隠れたデカボもくまなくチェック'],
    'name' => '上村麗菜',
    'yomi' => 'かみむられな',
    'group' => 'ロージークロニクル',
    'friend' => 'ウサレナ',
    'friend_en' => 'USA-RENA',
    // 公式のフレンズ紹介(1行目の「〇〇のデカビートフレンズ。」は名前と並べるので省く)
    'friend_desc' => ['デカビートフレンズのちっちゃい末っ子！', '卵かけごはんが大好き！'],
    'official' => 'https://helloproject.com/rosychronicle/rena_kamimura/',
    'instagram' => 'https://www.instagram.com/p/DVdPXTLE3MR/',
    'action' => '電気をこまめに消す！',
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
  [
    'title' => '環境省『デコ活2ndステージ』発表会',
    'date' => '2026年9月28日',
    'url' => 'https://www.youtube.com/watch?v=l-IUnSeHQZ4',
    'id' => 'l-IUnSeHQZ4',
  ],
];

// どっちっちchannel(毎週更新)。新しい回はここに足す(表示は新しい回が左)
// offshots は「offメシ、どっちっち？」のオフショットポスト [日付, ツイートID]
$dc_dotchicchi = [
  ['title' => '#1 豫風瑠乃×米村姫良々', 'url' => 'https://www.youtube.com/watch?v=acQZk0gb8SA',
   'credit' => 'ロケ協力', 'place' => '原宿さくら亭', 'place_url' => 'https://www.sakuratei.co.jp/',
   'offshots' => [['2026-09-24', '2103046787472994670'], ['2026-10-01', '2105582019711586579']]],
  ['title' => '#2 上村麗菜×米村姫良々', 'url' => 'https://www.youtube.com/watch?v=P6bgjmfNP1Y',
   'credit' => '撮影地協力', 'place' => 'EMPORIO cafe&dining', 'place_url' => 'https://www.emporio.tv/',
   'offshots' => [['2026-10-08', '2108168113690943784'], ['2026-10-15', '2110668292419662186']]],
  ['title' => '#3 岡村美波×米村姫良々', 'url' => 'https://www.youtube.com/watch?v=mZF7dc_mEf8',
   'credit' => '撮影協力', 'place' => 'EMPORIO cafe&dining', 'place_url' => 'https://www.emporio.tv/',
   'offshots' => [['2026-10-21', '2112836392695030091'], ['2026-10-22', '2113245030173540416'], ['2026-10-29', '2115734909012848853']]],
  ['title' => '#4 後藤花×米村姫良々', 'url' => 'https://www.youtube.com/watch?v=lkZEs-Wom5s',
   'credit' => '撮影協力', 'place' => 'ころ九', 'place_url' => 'http://kaisendon-koroku.com/',
   'offshots' => [['2026-11-04', '2117907873749713122'], ['2026-11-05', '2118313038931698143'], ['2026-11-12', '2120806484397502731']]],
];

// J-WAVEのPodcast「offの日、どっちっち？」(2回で終了)
$dc_podcasts = [
  ['title' => '#67 米村姫良々・岡村美波', 'date' => '2026年4月3日',
   'url' => 'https://open.spotify.com/episode/68CKVEiM7p5HN6vOaNhGRb',
   'article' => 'https://www.j-wave.co.jp/podcasts/docchicchi/entry/260403.html'],
  ['title' => '#68 米村姫良々・岡村美波', 'date' => '2026年4月17日',
   'url' => 'https://open.spotify.com/episode/5kkwGyKYTXeRxqUjWcsAvq',
   'article' => 'https://www.j-wave.co.jp/podcasts/docchicchi/entry/260417.html'],
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
    'title' => 'ワッチャ☆GROUP SHOTS 米村姫良々・弓桁朱琴',
    'date' => '2026年9月16日',
    'url' => 'https://x.com/Earthhacks2023/status/2100183391857688863',
  ],
  [
    'cat' => 'デカボMISSION',
    'title' => 'ワッチャ☆GROUP SHOTS 岡村美波・後藤花・上村麗菜',
    'date' => '2026年9月17日',
    'url' => 'https://x.com/Earthhacks2023/status/2100539941247988191',
  ],
  [
    'cat' => 'デカボMISSION',
    'title' => 'ワッチャ☆GROUP SHOTS 江端妃咲・豫風瑠乃',
    'date' => '2026年9月17日',
    'url' => 'https://x.com/Earthhacks2023/status/2100902046258086051',
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
const DC_YT_CHANNEL_URL = 'https://www.youtube.com/@decarbo46';
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
    // 見出しはカードの切り替えボタンが兼ねるので、ここでは読み上げ用のラベルだけ持つ
    return '<div class="dc-embed" data-type="' . $type . '" data-id="' . dc_h($id) . '"'
         . ($embed === '' ? '' : ' data-embed="' . dc_h($embed) . '"')
         . ' role="tabpanel" aria-label="' . dc_h($label) . '" hidden>'
         . '<div class="dc-slot">' . $slot . '</div>'
         . '<p class="dc-embed-link"><a href="' . dc_h($url) . '" target="_blank">'
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

  <details class="dc-about">
    <summary>DECA!BEATとは（公式プロフィールより）</summary>
    <blockquote cite="https://co.earth-hacks.jp/decabeat/">
      <p>地球の未来を歌い続けてきたハロー！プロジェクトと「無理なく楽しく」脱炭素の選択肢を広げるEarth hacksがコラボレーション！ハロー！プロジェクトの7つのグループから未来の地球を担うメンバーが集まって新ユニット「デカビート」としてみんなの「デカボ」を応援します。</p>
      <p class="dc-cite"><a href="https://co.earth-hacks.jp/decabeat/" target="_blank"><?= icon('link-brand', 'ic ic-20') ?>DECA!BEAT 公式プロフィール</a></p>
    </blockquote>
  </details>

  <!-- メンバー -->
  <section class="dc-section" id="members">
    <h2>メンバー</h2>
    <p>メンバーを選ぶと、デカビートフレンズ・紹介ポスト・新ビジュアル・紹介TikTok・お披露目前の「だーれだ」が表示されます。</p>

    <div class="tab-buttons dc-tabs" id="dc-member-tabs">
      <?php foreach ($dc_members as $m): ?>
      <button type="button" data-slug="<?= dc_h($m['slug']) ?>">
        <span class="dc-btn-no"><?= dc_h($m['no']) ?></span>
        <span class="dc-btn-name"><?= dc_h($m['name']) ?></span>
      </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($dc_members as $m): ?>
    <div class="dc-panel" id="dc-panel-<?= dc_h($m['slug']) ?>" hidden>
      <?php // 名前はハロー！プロジェクト公式プロフィールへのリンクを兼ねる ?>
      <h3 class="dc-name"><span class="dc-no"><?= dc_h($m['no']) ?></span><a href="<?= dc_h($m['official']) ?>" target="_blank" title="ハロー！プロジェクト公式プロフィール"><?= dc_h($m['name']) ?></a><span class="dc-yomi"><?= dc_h($m['yomi']) ?></span><?php if (!empty($m['leader'])): ?><span class="dc-leader">リーダー</span><?php endif; ?>
        <?php // シェア先URLは表示中のメンバーに合わせてJSが入れる ?>
        <a class="dc-share" target="_blank"
           data-text="<?= dc_h($m['name'] . '（' . $m['friend'] . '）｜DECA!BEAT 非公式まとめ #Earthhacks #デカビート') ?>"><?= icon('x', 'ic ic-20') ?>シェア</a></h3>
      <p class="dc-catch"><?= dc_h($m['catch'][0]) ?><br><?= dc_h($m['catch'][1]) ?></p>
      <p class="dc-meta"><span><?= dc_h($m['group']) ?></span><span>デカビートフレンズ：<?= dc_h($m['friend']) ?>（<?= dc_h($m['friend_en']) ?>）</span></p>
      <p class="dc-action">MY「デカボ」アクション：<?= dc_h($m['action']) ?></p>
      <p class="dc-mission"><a href="<?= dc_h(DC_MISSION_URL . '?culture=ja&spot=' . $m['spot']) ?>" target="_blank"><?= dc_h($m['mission']) ?>（デカボスコア<?= dc_h($m['score']) ?>％）</a></p>
      <?php // 埋め込み4枚は一度に並べず、1枚ずつめくって見せる(表示した1枚だけ読み込む) ?>
      <div class="dc-deck">
        <div class="dc-deck-tabs" role="tablist">
          <?php foreach (['紹介ポスト', '新ビジュアル', '紹介TikTok', 'だーれだ' . $m['dareda_no']] as $i => $label): ?>
          <button type="button" role="tab" data-card="<?= $i ?>"><?= dc_h($label) ?></button>
          <?php endforeach; ?>
        </div>
        <div class="dc-embeds">
        <?= dc_x_embed('メンバー紹介ポスト', $m['intro']) ?>
        <?= dc_x_embed('新ビジュアル', $m['visual']) ?>
        <?php // Instagramは同じ動画なので、紹介動画の枠の中に並べる ?>
        <?php // TikTokの埋め込みは表示するといきなり再生が始まるので、押されるまで読み込まない ?>
        <?= dc_embed('tiktok', '紹介動画（TikTok）', $m['tiktok'],
              'https://www.tiktok.com/@' . DC_TIKTOK_ACCOUNT . '/video/' . $m['tiktok'], 'TikTokで見る',
              '<p class="dc-embed-link"><a href="' . dc_h($m['instagram']) . '" target="_blank">Instagram（同じ動画）</a></p>',
              '',
              '<button type="button" class="dc-tiktok-play">' . icon('tiktok-brand', 'ic dc-tiktok-icon')
              . '<span>▶ 紹介動画を再生</span></button>') ?>
        <?= dc_x_embed('お披露目前の#デカビート だーれだ！' . $m['dareda_no'], $m['dareda']) ?>
        </div>
        <div class="dc-deck-nav">
          <button type="button" class="dc-deck-prev" aria-label="前のカード">‹</button>
          <span class="dc-deck-count">1 / 4</span>
          <button type="button" class="dc-deck-next" aria-label="次のカード">›</button>
        </div>
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
        <li><a href="<?= dc_h($v['url']) ?>" target="_blank"><?= icon('youtube-brand', 'ic ic-20') ?><?= dc_h($v['title']) ?></a>（<?= dc_h($v['date']) ?>）</li>
        <?php endforeach; ?>
      </ul>
    </noscript>
  </section>

  <!-- どっちっちchannel -->
  <section class="dc-section" id="dotchicchi">
    <h2>どっちっちchannel</h2>
    <p>DECA!BEATのYouTubeチャンネルで毎週更新中です。</p>
    <ul class="dc-sns">
      <li><a href="<?= dc_h(DC_YT_CHANNEL_URL) ?>" target="_blank"><?= icon('youtube-brand', 'ic ic-20') ?>どっちっちchannel（YouTube）</a></li>
    </ul>
    <?php // 再生はYouTube側。1回分を1枚のカードにして横に回す(全部HTMLに書き出す) ?>
    <div class="dc-carousel">
      <button type="button" class="dc-carousel-prev" aria-label="新しい回へ">‹</button>
      <ul class="dc-carousel-track">
        <?php foreach (array_reverse($dc_dotchicchi) as $v): ?>
        <li class="dc-card">
          <h3><?= dc_h($v['title']) ?></h3>
          <p><a href="<?= dc_h($v['url']) ?>" target="_blank"><?= icon('youtube-brand', 'ic ic-20') ?>YouTubeで見る</a></p>
          <?php if (!empty($v['offshots'])): ?>
          <ul class="dc-card-shots">
            <?php foreach ($v['offshots'] as $o): ?>
            <li><a href="<?= dc_h('https://x.com/' . DC_X_ACCOUNT . '/status/' . $o[1]) ?>" target="_blank"><?= icon('x', 'ic ic-20') ?>オフショット（<?= dc_h(dc_jdate($o[0])) ?>）</a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <?php if (!empty($v['place'])): ?>
          <p class="dc-card-credit"><?= dc_h($v['credit']) ?>：<a href="<?= dc_h($v['place_url']) ?>" target="_blank"><?= dc_h($v['place']) ?></a></p>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="dc-carousel-next" aria-label="前の回へ">›</button>
    </div>

    <h3>Podcast「offの日、どっちっち？」（J-WAVE）</h3>
    <ul class="dc-video-list">
      <?php foreach ($dc_podcasts as $v): ?>
      <li><a href="<?= dc_h($v['url']) ?>" target="_blank"><?= icon('spotify-brand', 'ic ic-20') ?><?= dc_h($v['title']) ?></a>（<?= dc_h($v['date']) ?>・<a href="<?= dc_h($v['article']) ?>" target="_blank">J-WAVEの記事</a>）</li>
      <?php endforeach; ?>
    </ul>
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
        <a class="dc-post-link" href="<?= dc_h($p['url']) ?>" target="_blank"><?= icon('x', 'ic ic-20') ?><?= dc_h($p['title']) ?></a>
        <p class="dc-post-date"><?= dc_h($p['date']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- デカビートフレンズ -->
  <?php // 7人分を全部HTMLに書き出し、横スクロールで回す(隠さないので検索エンジンにもそのまま読まれる) ?>
  <section class="dc-section" id="friends">
    <h2>デカビートフレンズ</h2>
    <p>みんなが集めたデカボのパワーで生まれた、DECA!BEATの小さなお友達です。</p>
    <div class="dc-carousel">
      <button type="button" class="dc-carousel-prev" aria-label="前のフレンズ">‹</button>
      <ul class="dc-carousel-track">
        <?php foreach ($dc_members as $m): ?>
        <li class="dc-card dc-friend-card">
          <h3><?= dc_h($m['friend']) ?><span><?= dc_h($m['friend_en']) ?></span></h3>
          <p class="dc-friend-of"><a href="#<?= dc_h($m['slug']) ?>"><?= dc_h($m['name']) ?></a>のデカビートフレンズ</p>
          <p><?= dc_h($m['friend_desc'][0]) ?><br><?= dc_h($m['friend_desc'][1]) ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="dc-carousel-next" aria-label="次のフレンズ">›</button>
    </div>
  </section>

  <!-- 公式リンク -->
  <section class="dc-section" id="official">
    <h2>公式リンク</h2>
    <ul class="dc-sns">
      <?php foreach ($dc_sns as $s): ?>
      <li><a href="<?= dc_h($s['url']) ?>" target="_blank"><?= dc_sns_icon($s['url']) ?><?= dc_h($s['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
</main>

<script>
(function () {
  var VIDEOS = <?= json_encode($dc_videos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  /* ---- メンバー ---- */

  // 埋め込みはめくって表示された1枚だけ読み込む
  function loadEmbed(box) {
    if (box.dataset.loaded) return;
    box.dataset.loaded = '1';

    // TikTokは押されたときに読み込む(表示した時点で再生が始まってしまうため)
    if (box.dataset.type !== 'x') return;

    // Xは <blockquote> を差し込んでからウィジェットに変換させる。
    // 変換に失敗しても引用テキストが残るので、読み込み中の表示は要らない。
    var slot = box.querySelector('.dc-slot');
    slot.innerHTML = box.dataset.embed;
    loadTwitterWidget(function () { window.twttr.widgets.load(slot); });
    hideQuotesWhenEmbedded(box);
  }

  // カードを i 枚目にめくる(端まで行ったら反対側に回る)
  function showCard(deck, i) {
    var cards = deck.querySelectorAll('.dc-embed');
    i = (i + cards.length) % cards.length;
    deck.dataset.current = i;
    cards.forEach(function (c, k) {
      // めくって隠したTikTokは読み込み直して止める(見えないまま音が鳴り続けるので)
      var f = c.querySelector('iframe');
      if (k !== i && !c.hidden && f && c.dataset.type === 'tiktok') f.src = f.src;
      c.hidden = (k !== i);
    });
    deck.querySelectorAll('.dc-deck-tabs button').forEach(function (b, k) {
      b.classList.toggle('active', k === i);
      b.setAttribute('aria-selected', k === i ? 'true' : 'false');
    });
    deck.querySelector('.dc-deck-count').textContent = (i + 1) + ' / ' + cards.length;
    loadEmbed(cards[i]);
  }

  document.addEventListener('click', function (e) {
    var deck = e.target.closest('.dc-deck');
    if (!deck) return;
    var cur = Number(deck.dataset.current || 0);
    var tab = e.target.closest('.dc-deck-tabs button');
    if (tab) showCard(deck, Number(tab.dataset.card));
    else if (e.target.closest('.dc-deck-prev')) showCard(deck, cur - 1);
    else if (e.target.closest('.dc-deck-next')) showCard(deck, cur + 1);
  });

  // スマホでは左右のスワイプでもめくれる
  document.querySelectorAll('.dc-deck .dc-embeds').forEach(function (area) {
    var x0 = null, y0 = 0;
    area.addEventListener('touchstart', function (e) {
      x0 = e.touches[0].clientX;
      y0 = e.touches[0].clientY;
    }, { passive: true });
    area.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      var dy = e.changedTouches[0].clientY - y0;
      x0 = null;
      if (Math.abs(dx) < 50 || Math.abs(dx) < Math.abs(dy) * 1.5) return;
      var deck = area.closest('.dc-deck');
      showCard(deck, Number(deck.dataset.current || 0) + (dx < 0 ? 1 : -1));
    });
  });

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
  // (別のカードにめくられている間は高さが0なので、表示中に判定できるまで待つ)
  function hideQuotesWhenEmbedded(box) {
    var tries = 0;
    var timer = setInterval(function () {
      var quote = box.querySelector('blockquote.twitter-tweet');
      var embed = box.querySelector('div.twitter-tweet');
      if (quote && embed && embed.getBoundingClientRect().height > 0) quote.hidden = true;
      if (!quote || quote.hidden || ++tries > 20) clearInterval(timer);
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
    var deck = panel.querySelector('.dc-deck');
    showCard(deck, Number(deck.dataset.current || 0));
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

  // 選んだら選択ボタンが画面の上に来るところまで送る(その下にメンバーの中身が続く)
  memberTabs.addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (btn && selectMember(btn.dataset.slug, true)) scrollToTabs(true);
  });

  // TikTok: 押されたらその場で読み込む
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.dc-tiktok-play');
    if (!btn) return;
    var f = document.createElement('iframe');
    f.src = 'https://www.tiktok.com/embed/v2/' + btn.closest('.dc-embed').dataset.id;
    f.title = 'TikTokの紹介動画';
    f.allow = 'encrypted-media; fullscreen; picture-in-picture';
    f.referrerPolicy = 'strict-origin-when-cross-origin';
    btn.replaceWith(f);
  });

  window.addEventListener('hashchange', function () {
    if (selectMember(decodeURIComponent(location.hash.slice(1)), false)) scrollToTabs();
  });

  // メンバー指定のURLで来たとき、他のメンバーに移れる選択ボタンごと見えるようにする
  function scrollToTabs(smooth) {
    window.scrollTo({
      top: memberTabs.getBoundingClientRect().top + window.scrollY - 12,
      behavior: smooth ? 'smooth' : 'auto'
    });
  }

  /* ---- デカビートフレンズ ---- */

  // ‹ › で1枚ずつ送る。端まで行ったら反対の端に戻る(フレンズ・どっちっちの両方)
  document.querySelectorAll('.dc-carousel').forEach(function (box) {
    var track = box.querySelector('.dc-carousel-track');
    function step(dir) {
      var card = track.querySelector('.dc-card');
      var w = card.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0);
      var max = track.scrollWidth - track.clientWidth;
      var to = track.scrollLeft + dir * w;
      if (to > max + 2) to = 0;
      else if (to < -2) to = max;
      track.scrollTo({ left: to, behavior: 'smooth' });
    }
    box.querySelector('.dc-carousel-prev').addEventListener('click', function () { step(-1); });
    box.querySelector('.dc-carousel-next').addEventListener('click', function () { step(1); });
  });

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
      f.referrerPolicy = 'strict-origin-when-cross-origin';
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
