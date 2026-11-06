<?php
  $title = "宮本佳林 テック仕事まとめ";
  $description = "宮本佳林さんのテック仕事（公開されたサイトや企画）を年代順にまとめています。";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

  // リンクの後ろに付ける種類の表示(（YouTube）など)
  function kt_kind(string $url, string $default): string
  {
      return str_contains($url, 'youtube.com/') ? 'YouTube' : $default;
  }

  // 公開されたものだけを、古い順に並べる。日付が未確認・期間限定でリンクしないものは null にする。
  // 'blogs' => [[リンク名, アメブロの記事URL], ...] でブログへのリンクが出る
  $history = [
    [
      'date' => '2026/06/15',
      'title' => '週末生態系診断サイト',
      'desc' => '全10問から15タイプの画像付き診断結果（相性付き）',
      'url' => 'https://nameless-lake-b1b6.karindayodayooooon.workers.dev/',
    ],
    [
      'date' => '2026/06/21',
      'title' => 'HANAKINデート成立チャレンジ',
      'desc' => '上記の診断結果からのデートチャレンジ（抽選ではなく条件つき）',
      'url' => 'https://nameless-lake-b1b6.karindayodayooooon.workers.dev/hanakin-game',
    ],
    [
      'date' => '2026/06',  // 会場ごとに使ったので日付は決めず、月まで
      'title' => 'アンケート&ボイス配布サイト',
      'desc' => 'リリースイベント会場でQRコードを読み込んでアンケートに答えると、その日だけ聴けるボイスメッセージが流れる',
      'url' => null,  // 期間限定(会場ごと)のサイトなのでリンクしない
      'blogs' => [
        ['アンケートシステムの全体像', 'https://ameblo.jp/miyamotokarin-official/entry-12974691151.html'],
        ['アンケートシステムの技術構成', 'https://ameblo.jp/miyamotokarin-official/entry-12974750978.html'],
      ],
    ],
    [
      'date' => '2026/07',  // 最後の回が7/30ごろ。日付は決めず、月まで
      'title' => 'WithLIVE 全通特典サイト',
      'desc' => 'WithLIVEで参加後にもらえるキーワードを全部集めると、特典動画が閲覧できる',
      'url' => null,  // 期間限定のサイトなのでリンクしない
    ],
    [
      'date' => '2026/07/31',
      'title' => 'みんなと協力して家にあるものでMV再現10時間配信',
      'desc' => null,
      'url' => 'https://www.youtube.com/watch?v=ZnhFXyFOjto',
      'blogs' => [
        ['配信システムの全体像', 'https://ameblo.jp/miyamotokarin-official/entry-12974432505.html'],
        ['配信システムの技術構成', 'https://ameblo.jp/miyamotokarin-official/entry-12974440752.html'],
      ],
    ],
    [
      'date' => '2026/09/18',
      'title' => 'あなたが選ぶ宮本佳林が最強になれるハロプロカバー曲',
      'desc' => '宮本佳林 5周年記念LIVE TOUR 2026 最終日のKT Zepp Yokohama公演の曲を、サブスク解禁曲から選べるサイト',
      'url' => 'https://zepp-vote.karindayodayooooon.workers.dev/',
    ],
    [
      'date' => '2026/10/09',
      'title' => '宮本佳林5周年記念LIVE TOUR 2026 ～ぼくのかんがえたさいきょうのセトリ',
      'desc' => null,
      'url' => 'https://karin-tour-production.karindayodayooooon.workers.dev/',
      'blogs' => [
        ['デバッグ配信とサイトの裏側', 'https://ameblo.jp/miyamotokarin-official/entry-12980414977.html'],
      ],
    ],
  ];

  // YouTubeの配信。古い順。タイトルは動画タイトルのまま。ヒストリーの10時間配信とは重複してよい
  $streams = [
    [
      'date' => '2026/07/31',
      'title' => 'みんなと協力して家でMV再現配信✨',
      'desc' => '家にあるものを使って、みんなと協力しながらMVを再現する10時間の生配信。配信を支える仕組みも自分で作った。',
      'url' => 'https://www.youtube.com/watch?v=ZnhFXyFOjto',
    ],
    [
      'date' => '2026/10/02',
      'title' => 'ツアーサイトファンのみんなとデバッグ配信💻',
      'desc' => '公開前のツアーサイトを、ファンのみんなと触りながらバグを探した配信。',
      'url' => 'https://www.youtube.com/watch?v=wDl3w--ev3s',
      'blogs' => [
        ['デバッグ配信とサイトの裏側', 'https://ameblo.jp/miyamotokarin-official/entry-12980414977.html'],
      ],
    ],
    [
      'date' => '2026/10/09',
      'title' => 'ツアーサイトリリース配信💻',
      'desc' => 'ツアーサイトの公開に合わせた配信。',
      'url' => 'https://www.youtube.com/watch?v=YBG2dSOsFmA',
    ],
  ];

  // 登壇。古い順。概要は各イベントページの紹介文。'blogs' => [[リンク名, アメブロの記事URL], ...] でボタンが出る
  $talks = [
    [
      'conference' => 'Cloudflare Workers Tech Talks in Tokyo #8',
      'title' => 'アイドルから見たもの作りの本質',
      'date' => '2026/08/27',
      'time' => '16:30〜16:50',
      'place' => 'TODA HALL & CONFERENCE TOKYO Bホール',
      'host' => '和田裕介',
      'desc' => null,
      'links' => [
        ['イベントページ', 'https://workers-tech.connpass.com/event/400818/'],
      ],
      'blogs' => [
        ['感想ブログ', 'https://ameblo.jp/miyamotokarin-official/entry-12976962844.html'],
      ],
    ],
    [
      'conference' => 'Qiita Conference 2026 Autumn',
      'title' => 'AI時代だからこそ大事にするべき、開発という名の愛情',
      'date' => '2026/10/27',
      'time' => '19:35〜19:55',
      'place' => null,
      'host' => 'Qiita株式会社',
      'desc' => "ITスキルが全くないアイドルが、10時間生配信を支えるシステムをバイブコーディングで作りました。\n\n"
        . "コードは一行も書けず、読めもしません。\nそれでも当日、システムは一度も止まりませんでした。\n\n"
        . "なぜ自分で作ろうと思ったのか。\n移動中のスマホで何を考えて仕様書を詰めていたのか。\n"
        . "AIが出してきたものに対して、知識がない人間が何を基準に「これでいい」「これは違う」と判断していたのか。\n\n"
        . "自分の作ったものを紐解いていく中で見えてきたのは、\nAI時代とは真逆に捉えられがちな「愛」でした。\n\n"
        . "このシステムで誰がどれだけ幸せになってくれるのか。\n技術ではなく、そこが全部を決めていました。",
      'links' => [
        ['イベントページ', 'https://qiita.com/conference/2026-autumn'],
      ],
    ],
    [
      'conference' => 'NIIGATA AI EXPO 2026',
      'title' => 'プログラミング知識ゼロのアイドルがAIで「やりたい」を次々カタチにできた理由',
      'date' => '2026/10/28',
      'time' => '15:30〜16:00',
      'place' => '万代島多目的広場 大かま Stage A',
      'host' => '新潟県AI活用推進ラボ事務局',
      'desc' => 'コード知識ゼロから、生成AIの指示だけでファン参加型サイトやアンケート、生配信システムなどを自作。「こんなことができたらいいな」を最短で形にした実体験をもとに、AIを活用したものづくりの可能性と、専門人材不足に悩む地方中小企業へのヒントを語る。',
      'links' => [
        ['イベントページ', 'https://niigata-ai.jp/expo/stageevent'],
      ],
    ],
    [
      'conference' => 'Findy x playground',
      'title' => 'アイドルが1人開発を完走した裏側 ―playgroundと宮本佳林さんが語る“欲しい”を言語化する力',
      'date' => '2026/10/29',
      'time' => '19:05〜19:55',
      'place' => 'ファインディ オフィス',
      'host' => 'ファインディ株式会社',
      'desc' => "本イベントでは、歌手・タレントの宮本佳林さんが、10時間配信を支えるシステムを1人で開発・完走した事例を起点に、playground社が実践する「1プロダクト1人」の開発体制まで、これからのAI時代における開発のあり方を深掘りします。\n\n"
        . "宮本さんは、「SATOYAMA&SATOUMIへ行こう」でplayground社の顔認証チケットに触れたことをきっかけに、オフィスを訪問。代表の伊藤さんやエンジニアの方からWebアプリ開発を直接学び、その後のCursorを使った開発にもつながっていきました。ご本人もブログで「playgroundさんとの出会いがなければ今の宮本はいません」と語っています。\n"
        . "参考： 元アイドルがバイブコーディングできるようになるまで。\n\n"
        . "そんな宮本さんの事例では、楽曲プロモーションという目的から逆算して「何をつくるか」を考える発想力、自然言語でAIと対話しながら仕様を進化させていく開発プロセス、そして運用・ユーザー体験・セキュリティまで見据えた設計に焦点を当てます。\n\n"
        . "また、宮本さんの開発を支えたplayground社では、なぜ開発チームを解散し「1プロダクト1人」という体制へ移行したのか。その背景にある思想や、AIに任せること／人が担うことの境界線について掘り下げます。\n\n"
        . "「つくること」そのものがAIによって大きく変わりつつある今、求められるのは単にコードを書くスキルだけではありません。2つの事例を通じて、AIに代替されにくい本質的なスキルとは何か、そしてこれからのエンジニア像や開発プロセスはどう変わっていくのかを、一緒に考える場を目指します。",
      'links' => [
        ['イベントページ（connpass）', 'https://findy.connpass.com/event/406424/'],
        ['イベントページ（Findy）', 'https://findy-code.io/events/3J6COy3ogFL4x'],
      ],
    ],
  ];

  // 読み物。非エンジニアにも読める、作ったきっかけや開発の流れのブログ。上から順に通しで読む
  $reading = [
    ['title' => '10時間配信がバズったあとに書いた、作ることになった経緯', 'url' => 'https://ameblo.jp/miyamotokarin-official/entry-12974640519.html'],
    ['title' => 'ツアーサイトにたどり着くまでの流れ（デバッグ配信の記事の続き）', 'url' => 'https://ameblo.jp/miyamotokarin-official/entry-12980415652.html'],
  ];

  // アメブロのテック記事のテーマ一覧
  $tech_theme = 'https://ameblo.jp/miyamotokarin-official/theme-10113896044.html';

  // インタビュー。古い順。hello-articles にも同じ記事がある
  $interviews = [
    [
      'date' => '2026/08/06',
      'title' => '宮本佳林、“バイブコーディング”への尽きない探究心　AIには「私の“分身”になってほしい」',
      'url' => 'https://realsound.jp/tech/2026/08/post-2481666.html',
      'media' => 'リアルサウンド テック',
      'media_url' => 'https://realsound.jp/tech',
    ],
    [
      'date' => '2026/08/27',
      'title' => 'AIを使いこなすアイドル・宮本佳林はどのようにして生まれたのか',
      'url' => 'https://natalie.mu/music/column/686866',
      'media' => '音楽ナタリー',
      'media_url' => 'https://natalie.mu/music',
    ],
    [
      'date' => '2026/09/06',
      'title' => '元Juice=Juiceエースの27歳・宮本佳林、AI開発で後輩たちと同時期ブレイクの現在地「感情に波はない」',
      'url' => 'https://encount.press/archives/1060593/',
      'media' => 'ENCOUNT',
      'media_url' => 'https://encount.press/',
    ],
    [
      'date' => '2026/09/10',
      'title' => '｢すごいことをしている感覚はない｣プログラミング未経験のアイドル･宮本佳林がAIで診断サイトも生配信の仕組みも"自作"できた舞台裏',
      'url' => 'https://toyokeizai.net/articles/-/956929?display=b',
      'media' => '東洋経済オンライン',
      'media_url' => 'https://toyokeizai.net/',
    ],
    [
      'date' => '2026/09/10',
      'title' => 'AIで企画書を作って修正案まで用意…元Juice=Juice･宮本佳林が示す"歌って踊るだけではない"新時代のアイドル像',
      'url' => 'https://toyokeizai.net/articles/-/956932?display=b',
      'media' => '東洋経済オンライン',
      'media_url' => 'https://toyokeizai.net/',
    ],
    [
      'date' => '2026/09/25',
      'title' => '【業界騒然】アイドルがAIでシステム開発 / アイドルサイボーグのプロフェッショナル哲学 / 独学で学ぶ最短学習法 / ファンを巻き込む最強のPM思考 / 失敗を物語に変えるフェイルセーフ設計の全貌',
      'url' => 'https://www.youtube.com/watch?v=CqmalArD2Hg',
      'media' => 'テクノロジーメディア「Newbee」',
      'media_url' => 'https://newbee.co.jp/',
    ],
    [
      'date' => '2026/11/06',
      'title' => '【宮本佳林AIサイト制作プロジェクト】第1回：どんなサイトなら、ファンに楽しんでもらえる？',
      'url' => 'https://webdesigning.book.mynavi.jp/article/36644/',
      'media' => 'Web Designing',
      'media_url' => 'https://webdesigning.book.mynavi.jp/',
      'desc' => 'Web制作会社のちょっと株式会社と組んで、本人のように話すAIと会話できる特設サイトをつくる全3回の企画。第1回は、ブログをもとに口調を真似るAIのデモを囲んで、写真・ボイス・クイズ・診断などのアイデアを出し合う作戦会議。',
    ],
  ];
?>

<main>
  <h1>宮本佳林 テック仕事まとめ</h1>
<div class="kt-lead">
  <p>宮本佳林さんが作ったサイトや配信の仕組み、YouTube配信、登壇、インタビューを、公開されたものだけ年代順に並べています。技術のくわしい話は、本人のブログへのリンクからどうぞ。テックの記事は<a href="<?= htmlspecialchars($tech_theme) ?>" target="_blank" rel="noopener noreferrer">アメブロのテーマ一覧</a>にもまとまっています。</p>
  <p>技術の話はちょっと…という人は、作ったきっかけや開発の流れを書いたブログを<a href="#reading">「読み物」</a>にまとめています。</p>
</div>

<h2>ヒストリー</h2>
<ul class="kt-list">
<?php foreach ($history as $item): ?>
  <li>
    <span class="kt-date"><?= htmlspecialchars($item['date'] ?? '日付未定') ?></span>
<?php if (!empty($item['url'])): ?>
    <a class="kt-title" href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($item['title']) ?><span class="kt-kind">（<?= kt_kind($item['url'], 'サイト') ?>）</span></a>
<?php else: ?>
    <span class="kt-title"><?= htmlspecialchars($item['title']) ?></span>
<?php endif; ?>
<?php if (!empty($item['desc'])): ?>
    <span class="kt-desc"><?= htmlspecialchars($item['desc']) ?></span>
<?php endif; ?>
<?php if (!empty($item['blogs'])): ?>
    <span class="kt-sub kt-blogs"><?php foreach ($item['blogs'] as [$label, $url]): ?><a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($label) ?></a><?php endforeach; ?></span>
<?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>

<h2>YouTube配信</h2>
<ul class="kt-list">
<?php foreach ($streams as $st): ?>
  <li>
    <span class="kt-date"><?= htmlspecialchars($st['date']) ?></span>
    <a class="kt-title" href="<?= htmlspecialchars($st['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($st['title']) ?><span class="kt-kind">（YouTube）</span></a>
    <span class="kt-desc"><?= htmlspecialchars($st['desc']) ?></span>
<?php if (!empty($st['blogs'])): ?>
    <span class="kt-sub kt-blogs"><?php foreach ($st['blogs'] as [$label, $url]): ?><a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($label) ?></a><?php endforeach; ?></span>
<?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>

<h2>登壇</h2>
<div class="card-grid ks-grid">
<?php foreach ($talks as $talk): ?>
  <article class="card ks-card">
    <p class="ks-meta"><span class="ks-date"><?= htmlspecialchars($talk['date'] . ' ' . $talk['time']) ?></span></p>
    <h3 class="ks-title"><?= htmlspecialchars($talk['conference']) ?></h3>
    <p class="ks-by"><?= htmlspecialchars($talk['title']) ?></p>
<?php if (!empty($talk['place'])): ?>
    <p class="ks-note">会場：<?= htmlspecialchars($talk['place']) ?></p>
<?php endif; ?>
    <p class="ks-note">主催：<?= htmlspecialchars($talk['host']) ?></p>
<?php if (!empty($talk['desc'])): ?>
    <details>
      <summary>概要</summary>
      <p><?= nl2br(htmlspecialchars($talk['desc'])) ?></p>
    </details>
<?php endif; ?>
    <p class="ks-links">
<?php foreach ($talk['links'] as [$label, $url]): ?>
      <a class="ks-link" href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($label) ?></a>
<?php endforeach; ?>
<?php foreach ($talk['blogs'] ?? [] as [$label, $url]): ?>
      <a class="ks-link" href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($label) ?></a>
<?php endforeach; ?>
    </p>
  </article>
<?php endforeach; ?>
</div>

<h2>インタビュー</h2>
<ul class="kt-list">
<?php foreach ($interviews as $iv): ?>
  <li>
    <span class="kt-date"><?= htmlspecialchars($iv['date']) ?></span>
    <a class="kt-title" href="<?= htmlspecialchars($iv['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($iv['title']) ?><span class="kt-kind">（<?= kt_kind($iv['url'], '記事') ?>）</span></a>
<?php if (!empty($iv['desc'])): ?>
    <span class="kt-desc"><?= htmlspecialchars($iv['desc']) ?></span>
<?php endif; ?>
    <span class="kt-sub"><a href="<?= htmlspecialchars($iv['media_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($iv['media']) ?></a></span>
  </li>
<?php endforeach; ?>
</ul>
<p>テック以外も含めたインタビューは「<a href="/hello-articles">ハロプロメンバーが語るインタビュー・Web記事まとめ</a>」にまとめています。</p>

<h2 id="reading">読み物：作ったきっかけと開発の流れ</h2>
<div class="kt-lead">
  <p>どうして自分で作ろうと思ったのか、AIとどう作ってきたのかを、本人がブログに書いています。技術を知らなくても読めるので、上から順に通して読むのがおすすめです。</p>
</div>
<ol class="kt-list kt-reading">
<?php foreach ($reading as $r): ?>
  <li><a class="kt-title" href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($r['title']) ?></a></li>
<?php endforeach; ?>
</ol>
<p>ほかのテックの記事は<a href="<?= htmlspecialchars($tech_theme) ?>" target="_blank" rel="noopener noreferrer">アメブロのテーマ一覧</a>から読めます。</p>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
