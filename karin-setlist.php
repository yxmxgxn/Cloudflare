<?php
  $title = "宮本佳林 最強のセトリ プレイリストまとめ";
  $description = "宮本佳林さんのツアーのセトリサイトで公開された「最強のセトリ」を、YouTubeのプレイリストにしてまとめました。";
  // 元のセトリはログインした人だけが見られるものなので、検索には出さない
  $noindex = true;
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>宮本佳林 最強のセトリ プレイリストまとめ</h1>
<div class="ks-lead">
  <p>宮本佳林さんのツアーのセトリサイトで公開された「最強のセトリ」を、古い順に並べています。元のセトリはログインしないと見られません。</p>
  <p>プレイリストは <a href="https://hello-music.yxmxgxn.workers.dev/" target="_blank">hello-music</a> で変換しました。</p>
</div>

<div class="card-grid ks-grid">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karinsetlist_cards.php'; ?>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/karinsetlist_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
