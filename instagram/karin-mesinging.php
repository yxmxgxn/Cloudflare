<?php
  $title = "宮本佳林 歌ってみた・踊ってみたまとめ（Instagram・TikTok・ボカロ部）";
  $description = "宮本佳林さんが一人で披露した歌ってみた・踊ってみたを、Instagram・TikTok・宮本佳林のボカロ部の投稿からまとめています。日付・曲名・アーティストを新着順に並べ、アイコンを押すとその場で再生できます。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

  $icon_ig = icon('instagram', 'ic sns-icon', 'Instagram');
  $icon_tt = icon('tiktok', 'ic sns-icon', 'TikTok');
  $icon_yt = icon('youtube', 'ic sns-icon', 'YouTube');
?>

<main>
  <h1>宮本佳林 歌ってみた・踊ってみたまとめ</h1>
  宮本佳林さんが<a href="https://www.instagram.com/karin_miyamoto.official/" target="_blank">Instagram</a>・<a href="https://www.tiktok.com/@karin_miyamoto" target="_blank">TikTok</a>で投稿している、歌ってみた・踊ってみたのまとめです。<br>
  <strong>一人でのパフォーマンスだけ</strong>を載せています（コラボ相手がいるHANAKINなどは<a href="/instagram/HANAKIN-insta">HANAKINコラボまとめ</a>へ）。<br>
  <a href="/vocalo-club">宮本佳林のボカロ部</a>で披露した歌ってみたも入れています（「ボカロ部」の印の後ろのリンクです）。<br>
  <?php echo $icon_ig; ?>・<?php echo $icon_tt; ?>・<?php echo $icon_yt; ?>を押すと、その場で投稿を見られます。Ctrl（Macはcmd）かShiftを押しながらだと、そのまま元のサービスで開きます。
  <div class="hanakin-wrap">
    <table class="hanakin-table pf-table">
      <thead>
        <tr>
          <th>日付</th>
          <th>曲名 / アーティスト</th>
          <th>投稿</th>
          <th>種別</th>
        </tr>
      </thead>
      <tbody id="pf-tbody">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karin_mesinging_table.php'; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/karin_mesinging_generated.php'; ?>

<script>
// アイコンを押したら、その場に埋め込みをポップアップで出す。
// 全部を最初から埋め込むと重いので、押された1件だけiframeを作る。
// 下に元の投稿へのリンクを置く。Ctrl/Shift/中クリックは今までどおりそのまま飛ぶ。
(function () {
  var LINK_SEL = '#pf-tbody a.pf-link';
  var pop = null;
  var openLink = null;

  function close() {
    if (pop) { pop.remove(); pop = null; }
    openLink = null;
  }

  function place(a) {
    var r = a.getBoundingClientRect();
    var w = Math.min(400, window.innerWidth - 24);
    var left = Math.max(12, Math.min(r.left, window.innerWidth - w - 12));
    pop.style.width = w + 'px';
    pop.style.left = (left + window.scrollX) + 'px';
    pop.style.top = (r.bottom + window.scrollY + 6) + 'px';
  }

  function keepInView() {
    var r = pop.getBoundingClientRect();
    if (r.bottom > window.innerHeight - 8) {
      window.scrollBy({ top: Math.min(r.bottom - window.innerHeight + 16, r.top - 8), behavior: 'smooth' });
    }
  }

  function open(a) {
    close();
    openLink = a;

    pop = document.createElement('div');
    pop.className = 'pf-embed';
    pop.setAttribute('role', 'dialog');
    pop.setAttribute('aria-label', '投稿の埋め込み');

    var x = document.createElement('button');
    x.type = 'button';
    x.className = 'pf-embed-close';
    x.setAttribute('aria-label', '閉じる');
    x.textContent = '×';
    x.addEventListener('click', close);

    // 高さは画面に収まる範囲まで。はみ出す分はiframeの中でスクロールする
    var f = document.createElement('iframe');
    f.className = 'pf-embed-frame';
    f.src = a.dataset.embed;
    f.title = '投稿の埋め込み';
    f.style.height = Math.min(parseInt(a.dataset.h, 10) || 420, window.innerHeight - 130) + 'px';
    f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    f.loading = 'lazy';
    f.referrerPolicy = 'strict-origin-when-cross-origin';

    var link = document.createElement('p');
    link.className = 'pf-embed-link';
    var go = document.createElement('a');
    go.href = a.href;
    go.target = '_blank';
    go.textContent = (a.dataset.label || '元の投稿で見る') + ' →';
    link.appendChild(go);

    pop.appendChild(x);
    pop.appendChild(f);
    pop.appendChild(link);
    document.body.appendChild(pop);
    place(a);
    keepInView();
  }

  document.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest(LINK_SEL) : null;
    if (a) {
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
      e.preventDefault();
      if (openLink === a) close(); else open(a);
      return;
    }
    if (pop && !pop.contains(e.target)) close();
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
