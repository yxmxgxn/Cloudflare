<footer>
<div class="share-area">
<?php
  $s_url = urlencode("https://karin.heavy.jp" . $_SERVER['REQUEST_URI']);
  $s_txt = urlencode(isset($title) ? $title . " | 宮本佳林・M-line・ハロプロ Archive" : "宮本佳林・M-line・ハロプロ Archive");
?>
<p>Share:
  <a href="https://x.com/share?url=<?php echo $s_url; ?>&text=<?php echo $s_txt; ?>" target="_blank" class="btn-x">X</a>
  <a href="https://social-plugins.line.me/lineit/share?url=<?php echo $s_url; ?>" target="_blank" class="btn-line">LINE</a>
  <button onclick="sharePage()" class="btn-copy">その他で送る/保存</button></p>
</div>


<script>
function sharePage() {
  if (navigator.share) {
    navigator.share({
      title: '<?php echo $p_title; ?>',
      url: 'https://karin.heavy.jp<?php echo $_SERVER['REQUEST_URI']; ?>'
    }).catch(console.error);
  } else {
    // PCブラウザなどで共有機能がない場合は、URLをクリップボードにコピーする
    const url = 'https://karin.heavy.jp<?php echo $_SERVER['REQUEST_URI']; ?>';
    navigator.clipboard.writeText(url);
    alert('URLをコピーしました！');
  }
}
</script>
<ul class="footer-links">
  <li><a href="/common/privacy" target="_blank">プライバシーポリシー</a></li>
  <li><a href="/common/disclaimer" target="_blank">免責事項</a></li>
  <li><a href="https://x.com/yxmxgxn" target="_blank">&copy; 2026 @yxmxgxn</a></li>
</ul>
</footer>
</body>
</html>