<?php
  $title = "宮本佳林 テック仕事まとめ";
  $description = "宮本佳林さんのテック仕事（公開されたサイトや企画）を年代順にまとめています。";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

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
      'url' => null,
      'note' => '配信のURLは未確認',
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
      'date' => '2026/10/09',
      'title' => '宮本佳林5周年記念LIVE TOUR 2026 ～ぼくのかんがえたさいきょうのセトリ',
      'desc' => null,
      'url' => 'https://karin-tour-production.karindayodayooooon.workers.dev/',
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
    <p class="ks-links"><a class="ks-link" href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener noreferrer">サイトを開く</a></p>
<?php else: ?>
    <p class="ks-note"><?= htmlspecialchars($item['note'] ?? '公開URLなし') ?></p>
<?php endif; ?>
  </article>
<?php endforeach; ?>
</div>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
