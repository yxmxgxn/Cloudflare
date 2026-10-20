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
  <p>プレイリストは <a href="https://hello-music.yxmxgxn.workers.dev/" target="_blank" rel="noopener noreferrer">hello-music</a> で変換しました。「YouTubeで聴く」を押すとこのページで再生します。</p>
</div>

<div class="card-grid ks-grid">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karinsetlist_cards.php'; ?>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/karinsetlist_generated.php'; ?>

</main>
<script>
// 「YouTubeで聴く」を押したら、そのカードの中に埋め込みプレイヤーを出す（もう一度押すと閉じる）
document.addEventListener('click', function (e) {
  var a = e.target.closest('a.ks-play');
  if (!a || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
  e.preventDefault();
  var box = a.closest('.ks-card').querySelector('.ks-embed');
  if (!box.hidden) { box.textContent = ''; box.hidden = true; return; }
  var f = document.createElement('iframe');
  f.src = a.getAttribute('data-embed');
  f.title = a.closest('.ks-card').querySelector('.ks-title').textContent;
  f.allow = 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture';
  f.allowFullscreen = true;
  f.referrerPolicy = 'strict-origin-when-cross-origin';
  box.appendChild(f);
  box.hidden = false;
});
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
