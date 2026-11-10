<footer>
<div class="share-area">
<?php
  // 共有用の絶対URL。ビルド時に build/build.sh が OLD_URL をデプロイ先ドメインへ置換する
  $page_url  = "https://karin-archive.com" . $_SERVER['REQUEST_URI'];
  $share_ttl = isset($p_title_full) ? $p_title_full : "宮本佳林・M-line・ハロプロ Archive";
  // rawurlencode: 空白が + ではなく %20 になる(URLを値として渡すのでこちらが無難)
  // Xだけ url= を使わず text= に %0A で連結する(url= だと本文とURLが空白1つで繋がって改行されない)
  $s_url = rawurlencode($page_url);
  $s_txt = rawurlencode($share_ttl);
?>
<p>Share:
  <a href="https://x.com/intent/post?text=<?php echo $s_txt; ?>%0A<?php echo $s_url; ?>" target="_blank" class="btn-x">X</a>
  <a href="https://www.threads.com/intent/post?text=<?php echo $s_txt; ?>&amp;url=<?php echo $s_url; ?>" target="_blank" class="btn-threads">Threads</a>
  <a href="https://social-plugins.line.me/lineit/share?url=<?php echo $s_url; ?>" target="_blank" class="btn-line">LINE</a>
  <button type="button" onclick="sharePage()" class="btn-copy">その他で送る/保存</button></p>
</div>


<script>
async function sharePage() {
  var data = {
    title: <?php echo json_encode($share_ttl, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES); ?>,
    url:   <?php echo json_encode($page_url,  JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES); ?>
  };
  if (navigator.share) {
    try { await navigator.share(data); }
    catch (e) { if (e.name !== 'AbortError') console.error(e); }   // AbortErrorは利用者が閉じただけ
    return;
  }
  // 共有機能がないブラウザ(PCなど)はURLをクリップボードにコピーする
  try {
    await navigator.clipboard.writeText(data.url);   // 完了を待ってから成功を伝える
    alert('URLをコピーしました！');
  } catch (e) {
    prompt('このURLをコピーしてください', data.url); // http やコピー拒否のときの逃げ道
  }
}
</script>

<?php include __DIR__ . '/feedback.php'; ?>

<ul class="footer-links">
  <li><a href="/common/privacy" target="_blank">プライバシーポリシー</a></li>
  <li><a href="/common/disclaimer" target="_blank">免責事項</a></li>
  <li class="footer-break"></li>
  <li><a href="https://tools.yxmxgxn.workers.dev/sns-links/" target="_blank">SNS Link</a></li>
  <li><a href="https://x.com/yxmxgxn" target="_blank">X @yxmxgxn</a></li>
  <li>&copy; 2026 @yxmxgxn</li>
</ul>
</footer>

<!-- 表をスマホで横スクロールできるよう、全<table>を.table-scrollで自動ラップ -->
<script>
(function () {
  document.querySelectorAll('table').forEach(function (t) {
    if (t.closest('.table-scroll')) return;                 // すでにラップ済み
    var p = t.parentElement;
    if (p) {
      var ox = getComputedStyle(p).overflowX;
      if (ox === 'auto' || ox === 'scroll') return;         // 親が既にスクロール対応
    }
    var w = document.createElement('div');
    w.className = 'table-scroll';
    t.parentNode.insertBefore(w, t);
    w.appendChild(t);
  });
})();
</script>
</body>
</html>