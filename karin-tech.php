<?php
  $title = "宮本佳林 テック仕事まとめ";
  $description = "宮本佳林さんのテック仕事（公開されたサイトや企画）を年代順にまとめています。";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/common/video_cards.php';

  // 公開されたものだけを、古い順に並べる。URLや日付が未確認のものは null にする
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
      'date' => '2026/06/28',
      'title' => 'アンケート&ボイス配布サイト',
      'desc' => 'リリースイベント会場でQRコードを読み込んでアンケートに答えると、その日だけ聴けるボイスメッセージが流れる',
      'url' => 'https://hanakin-survey.pages.dev/',
    ],
    [
      'date' => '2026/07/31',
      'title' => 'みんなと協力して家にあるものでMV再現10時間配信',
      'desc' => null,
      'url' => 'https://www.youtube.com/watch?v=ZnhFXyFOjto',
      'label' => 'YouTubeで見る',
    ],
    [
      'date' => null,
      'title' => 'WithLIVE 全通特典サイト',
      'desc' => 'WithLIVEで参加後にもらえるキーワードを全部集めると、特典動画が閲覧できる',
      'url' => 'https://hanakin-withlive.karindayodayooooon.workers.dev/',
    ],
    [
      'date' => '2026/09/18',
      'title' => 'あなたが選ぶ宮本佳林が最強になれるハロプロカバー曲',
      'desc' => '2026/12/01 KT Zepp Yokohama「宮本佳林 5周年記念LIVE TOUR 2026 〜ぼくのかんがえたさいきょうの「宮本佳林MAX」〜」のハロプロサブスク解禁楽曲からリクエストを選ぶアンケートサイト',
      'url' => 'https://zepp-vote.karindayodayooooon.workers.dev/',
    ],
    [
      'date' => '2026/10/02',
      'title' => 'ツアーサイトファンのみんなとデバッグ配信💻',
      'desc' => null,
      'url' => 'https://www.youtube.com/watch?v=wDl3w--ev3s',
      'label' => 'YouTubeで見る',
    ],
    [
      'date' => '2026/10/09',
      'title' => '宮本佳林5周年記念LIVE TOUR 2026 ～ぼくのかんがえたさいきょうのセトリ',
      'desc' => null,
      'url' => 'https://karin-tour-production.karindayodayooooon.workers.dev/',
    ],
    [
      'date' => '2026/10/09',
      'title' => 'ツアーサイトリリース配信💻',
      'desc' => null,
      'url' => 'https://www.youtube.com/watch?v=YBG2dSOsFmA',
      'label' => 'YouTubeで見る',
    ],
  ];

  // 登壇。古い順。概要は各イベントページの紹介文
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
        ['Web Archive', 'https://web.archive.org/web/20261027124609/https://workers-tech.connpass.com/event/400818/'],
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
        ['Web Archive', 'https://web.archive.org/web/20260908041615/https://qiita.com/conference/2026-autumn'],
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
        ['Web Archive', 'https://web.archive.org/web/20261027125445/https://niigata-ai.jp/expo/stageevent'],
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
        ['Web Archive（connpass）', 'https://web.archive.org/web/20261027125835/https://findy.connpass.com/event/406424/'],
        ['イベントページ（Findy）', 'https://findy-code.io/events/3J6COy3ogFL4x'],
        ['Web Archive（Findy）', 'https://web.archive.org/web/20261027130807/https://findy-code.io/events/3J6COy3ogFL4x'],
      ],
    ],
  ];

  // インタビュー。古い順。hello-articles にも同じ記事がある。Web Archive が取れなかった記事は archive を null にする
  $interviews = [
    [
      'date' => '2026/08/06',
      'title' => '宮本佳林、“バイブコーディング”への尽きない探究心　AIには「私の“分身”になってほしい」',
      'url' => 'https://realsound.jp/tech/2026/08/post-2481666.html',
      'media' => 'リアルサウンド テック',
      'media_url' => 'https://realsound.jp/tech',
      'archive' => 'https://web.archive.org/web/20260806061739/https://realsound.jp/tech/2026/08/post-2481666.html',
    ],
    [
      'date' => '2026/08/27',
      'title' => 'AIを使いこなすアイドル・宮本佳林はどのようにして生まれたのか',
      'url' => 'https://natalie.mu/music/column/686866',
      'media' => '音楽ナタリー',
      'media_url' => 'https://natalie.mu/music',
      'archive' => null,
    ],
    [
      'date' => '2026/09/06',
      'title' => '元Juice=Juiceエースの27歳・宮本佳林、AI開発で後輩たちと同時期ブレイクの現在地「感情に波はない」',
      'url' => 'https://encount.press/archives/1060593/',
      'media' => 'ENCOUNT',
      'media_url' => 'https://encount.press/',
      'archive' => 'https://web.archive.org/web/20260919055143/https://encount.press/archives/1060593/',
    ],
    [
      'date' => '2026/09/10',
      'title' => '｢すごいことをしている感覚はない｣プログラミング未経験のアイドル･宮本佳林がAIで診断サイトも生配信の仕組みも"自作"できた舞台裏',
      'url' => 'https://toyokeizai.net/articles/-/956929?display=b',
      'media' => '東洋経済オンライン',
      'media_url' => 'https://toyokeizai.net/',
      'archive' => 'https://web.archive.org/web/20260919061409/https://toyokeizai.net/articles/-/956929?display=b',
    ],
    [
      'date' => '2026/09/10',
      'title' => 'AIで企画書を作って修正案まで用意…元Juice=Juice･宮本佳林が示す"歌って踊るだけではない"新時代のアイドル像',
      'url' => 'https://toyokeizai.net/articles/-/956932?display=b',
      'media' => '東洋経済オンライン',
      'media_url' => 'https://toyokeizai.net/',
      'archive' => 'https://web.archive.org/web/20260919061455/https://toyokeizai.net/articles/-/956932?display=b',
    ],
  ];
?>

<main>
  <h1>宮本佳林 テック仕事まとめ</h1>
<div class="ks-lead">
  <p>公開されたものだけを、年代順に並べています。</p>
</div>

<h2>ヒストリー</h2>
<div class="card-grid ks-grid">
<?php foreach ($history as $item): ?>
  <article class="card ks-card">
    <p class="ks-meta"><span class="ks-date"><?= htmlspecialchars($item['date'] ?? '日付未定') ?></span></p>
    <h3 class="ks-title"><?= htmlspecialchars($item['title']) ?></h3>
<?php if (!empty($item['desc'])): ?>
    <p><?= htmlspecialchars($item['desc']) ?></p>
<?php endif; ?>
<?php if (!empty($item['url'])): ?>
    <p class="ks-links"><a class="ks-link" href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($item['label'] ?? 'サイトを開く') ?></a></p>
<?php else: ?>
    <p class="ks-note"><?= htmlspecialchars($item['note'] ?? '公開URLなし') ?></p>
<?php endif; ?>
  </article>
<?php endforeach; ?>
</div>

<h2>登壇</h2>
<div class="card-grid ks-grid">
<?php foreach ($talks as $talk): ?>
  <article class="card ks-card">
    <p class="ks-meta"><span class="ks-date"><?= htmlspecialchars($talk['date'] . ' ' . $talk['time']) ?></span></p>
    <h3 class="ks-title"><?= htmlspecialchars($talk['title']) ?></h3>
    <p class="ks-by"><?= htmlspecialchars($talk['conference']) ?></p>
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
    </p>
  </article>
<?php endforeach; ?>
</div>

<h2>YouTube Live</h2>
<p>サムネイルを押すと、その場で再生されます。</p>
<?= video_cards([
  ['id' => 'ZnhFXyFOjto', 'date' => '2026/07/31', 'title' => 'みんなと協力して家でMV再現配信✨'],
  ['id' => 'wDl3w--ev3s', 'date' => '2026/10/02', 'title' => 'ツアーサイトファンのみんなとデバッグ配信💻'],
  ['id' => 'YBG2dSOsFmA', 'date' => '2026/10/09', 'title' => 'ツアーサイトリリース配信💻'],
]) ?>

<h2>インタビュー</h2>
<div class="card-grid ks-grid">
<?php foreach ($interviews as $iv): ?>
  <article class="card ks-card">
    <p class="ks-meta"><span class="ks-date"><?= htmlspecialchars($iv['date']) ?></span></p>
    <h3 class="ks-title"><?= htmlspecialchars($iv['title']) ?></h3>
    <p class="ks-by"><a href="<?= htmlspecialchars($iv['media_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($iv['media']) ?></a></p>
    <p class="ks-links">
      <a class="ks-link" href="<?= htmlspecialchars($iv['url']) ?>" target="_blank" rel="noopener noreferrer">記事を読む</a>
<?php if (!empty($iv['archive'])): ?>
      <a class="ks-link" href="<?= htmlspecialchars($iv['archive']) ?>" target="_blank" rel="noopener noreferrer">Web Archive</a>
<?php endif; ?>
    </p>
  </article>
<?php endforeach; ?>
</div>
<p>テック以外も含めたインタビューは「<a href="/hello-articles">ハロプロメンバーが語るインタビュー・Web記事まとめ</a>」にまとめています。</p>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
