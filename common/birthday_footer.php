バースデーイベントのまとめはハロプロ公式・M-line公式ファンクラブのお知らせだけではなく、非公式サイト、各ファンのブログ・Xなど様々なソースからまとめたです<br>
他のページに比べ当時の記録が残っていない人が多いために、特にデータの抜けがあると思います。<br>
間違い・追加情報等あれば、<a href="https://x.com/yxmxgxn" target="_blank" rel="noopener noreferrer">Xでリプライ</a>を送っていただけるとありがたいです。
<footer>
<div class="share-area">
<?php
  // 共有用の絶対URL。ビルド時に build/build.sh が OLD_URL をデプロイ先ドメインへ置換する
  $page_url  = "https://karin-archive.com" . $_SERVER['REQUEST_URI'];
  $share_ttl = isset($p_title_full) ? $p_title_full : "宮本佳林・M-line・ハロプロ Archive";
  // rawurlencode: 空白が + ではなく %20 になる(URLを値として渡すのでこちらが無難)
  $s_url = rawurlencode($page_url);
  $s_txt = rawurlencode($share_ttl);
?>
<p>Share:
  <a href="https://x.com/share?url=<?php echo $s_url; ?>&amp;text=<?php echo $s_txt; ?>" target="_blank" rel="noopener" class="btn-x">X</a>
  <a href="https://www.threads.com/intent/post?text=<?php echo $s_txt; ?>&amp;url=<?php echo $s_url; ?>" target="_blank" rel="noopener" class="btn-threads">Threads</a>
  <a href="https://social-plugins.line.me/lineit/share?url=<?php echo $s_url; ?>" target="_blank" rel="noopener" class="btn-line">LINE</a>
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
<ul class="footer-links">
  <li><a href="/common/privacy" target="_blank" rel="noopener">プライバシーポリシー</a></li>
  <li><a href="/common/disclaimer" target="_blank" rel="noopener">免責事項</a></li>
  <li><a href="https://x.com/yxmxgxn" target="_blank" rel="noopener">&copy; 2026 @yxmxgxn</a></li>
</ul>
</footer>
</body>
</html>
