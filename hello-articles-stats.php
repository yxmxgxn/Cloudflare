<?php
  $title = "ハロプロメンバーのインタビュー・Web記事の件数（年別・媒体別）";
  $description = "「ハロプロメンバーが語るインタビュー・Web記事まとめ」に載せている記事の件数を、掲載年別・媒体別にまとめています。";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

  // 一覧ページと同じ全件データ(Python/generate_interview.py が書き出す)から数える
  $cards = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/data/interview-cards.json'), true)['cards'] ?? [];
  $attr = function (string $card, string $name): string {
    return preg_match('/ data-' . $name . '="([^"]*)"/', $card, $m)
      ? html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
  };
  $years = [];
  $media = [];
  foreach ($cards as $c) {
    $y = substr($attr($c, 'date'), 0, 4);
    $years[$y] = ($years[$y] ?? 0) + 1;
    $m = $attr($c, 'media');
    if ($m !== '') $media[$m] = ($media[$m] ?? 0) + 1;
  }
  krsort($years, SORT_STRING);
  uksort($media, function ($a, $b) use ($media) { return $media[$b] <=> $media[$a] ?: strcmp($a, $b); });

  $year_rows = [];
  foreach ($years as $y => $n) {
    $year_rows[] = ['label' => $y !== '' ? "{$y}年" : '日付なし', 'n' => $n,
                    'href' => $y !== '' ? '/hello-articles?year=' . rawurlencode($y) : ''];
  }
  $media_rows = [];
  foreach ($media as $m => $n) {
    $media_rows[] = ['label' => $m, 'n' => $n, 'href' => '/hello-articles?media=' . rawurlencode($m)];
  }

  // 「ラベル ▬▬ 件数」の行。バーの長さはその表の最大値に対する割合
  function stats_rows(array $rows, int $max): string {
    $out = '';
    foreach ($rows as $r) {
      $label = htmlspecialchars($r['label']);
      $w = $max ? round($r['n'] / $max * 100, 1) : 0;
      $inner = "<span class=\"iv-stats-label\">{$label}</span>"
             . "<span class=\"iv-stats-track\" aria-hidden=\"true\"><span class=\"iv-stats-bar\" style=\"width:{$w}%\"></span></span>"
             . "<span class=\"iv-stats-n\">{$r['n']}件</span>";
      $out .= $r['href']
        ? '<a class="iv-stats-row" href="' . htmlspecialchars($r['href']) . "\" title=\"{$label}の記事を一覧で見る\">{$inner}</a>\n"
        : "<div class=\"iv-stats-row\">{$inner}</div>\n";
    }
    return $out;
  }
  $MEDIA_TOP = 20;
  $media_max = $media_rows ? $media_rows[0]['n'] : 0;
?>

<main>
  <h1>インタビュー・Web記事の件数</h1>
  <p><a href="/hello-articles">ハロプロメンバーが語るインタビュー・Web記事まとめ</a>に載せている全<?= count($cards) ?>件を、掲載年別・媒体別に数えています。行を押すと、その年・媒体の記事だけを一覧で開きます。</p>

  <div class="iv-stats">
    <section class="iv-stats-list">
      <h2>年別</h2>
      <?= stats_rows($year_rows, $year_rows ? max(array_column($year_rows, 'n')) : 0) ?>
    </section>
    <section class="iv-stats-list">
      <h2>媒体別</h2>
      <?= stats_rows(array_slice($media_rows, 0, $MEDIA_TOP), $media_max) ?>
<?php if (count($media_rows) > $MEDIA_TOP): ?>
      <details>
        <summary>ほか <?= count($media_rows) - $MEDIA_TOP ?> 媒体を表示</summary>
        <?= stats_rows(array_slice($media_rows, $MEDIA_TOP), $media_max) ?>
      </details>
<?php endif; ?>
    </section>
  </div>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/interview_generated.php'; ?>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
