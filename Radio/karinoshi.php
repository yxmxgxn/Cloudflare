<?php
  $title = "宮本佳林 Music Palette♪カリンオシ まとめ"; 
  $description = "TBSラジオ Music Palette♪のカリンオシのコーナーで宮本佳林さんが紹介したお菓子等をXにてポストされたものをまとめています。";
  $jsonld_type = "Dataset";
  $about = "宮本佳林";
  // グループ単体
  // $about = ["name" => "モーニング娘。", "type" => "MusicGroup"];
  // 混在(人もグループも)
  // $about = [
  //   "宮本佳林",                                      // 文字列はPerson扱い
  //   ["name" => "Juice=Juice", "type" => "MusicGroup"],
  //   ["name" => "譜久村聖"],                          // typeなし → Person
  // ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>宮本佳林 Music Palette♪カリンオシ まとめ</h1>
  <p>TBSラジオ Music Palette♪のカリンオシのコーナーで宮本佳林さんが紹介したお菓子等をXにてポストされたものをまとめています<br>
  放送日（日時）ページをクリックすると<a href="https://www.tbsradio.jp/mp/" target="_blank" rel="noopener noreferrer">TBSラジオ Music Palette♪</a>のページに飛びます<br>
  また都道府県タグでフィルタリングできます</p>

  <p>追記<br>
  宮本佳林さんのMusic Palette♪は2026/09/27が最終回なので、このページの更新は終わります。</p>

  <p>日本地図を塗っていっているページは<a href="/Radio/karinoshi-map" target="_blank" rel="noopener noreferrer">こちら</a></p>
 
  <div id="karin-oshi-wrap">
    <div id="karin-filter">
      <select id="karin-pref-select">
        <option value="">すべての都道府県</option>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karinoshi_prefs.php'; ?>
      </select>
    </div>
    <div id="karin-gallery">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karinoshi_cards.php'; ?>
    </div>
  </div>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/karinoshi_generated.php'; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function loadTwitterWidget(callback) {
    if (window.twttr && window.twttr.widgets) { callback(); return; }
    const script = document.createElement('script');
    script.src = 'https://platform.twitter.com/widgets.js';
    script.charset = 'utf-8';
    script.onload = callback;
    document.head.appendChild(script);
  }

  function activateEmbed(el) {
    if (el.dataset.activated) return;
    el.dataset.activated = '1';

    if (el.dataset.embed) {
      el.innerHTML = el.dataset.embed;
      loadTwitterWidget(() => { window.twttr.widgets.load(el); });
    } else if (el.dataset.xurl) {
      const a = document.createElement('a');
      a.href = el.dataset.xurl;
      a.target = '_blank';
      a.rel = 'noopener';
      a.textContent = 'Xポスト';
      el.appendChild(a);
    }
  }

  // 都道府県フィルタ
  document.getElementById('karin-pref-select').addEventListener('change', function () {
    const val = this.value;
    document.querySelectorAll('#karin-gallery .karin-card').forEach(card => {
      const pref = card.dataset.pref || '';
      card.style.display = (!val || pref === val) ? '' : 'none';
    });
  });

  // 遅延読み込み
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        activateEmbed(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { rootMargin: '200px' });

  document.querySelectorAll('.karin-embed-placeholder').forEach(el => {
    observer.observe(el);
  });
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
