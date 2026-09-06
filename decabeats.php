<?php
  $title = "DECA!BEAT（デカビート）まとめ";
  $description = "ハロー！プロジェクトとEarth hacksのコラボレーションで生まれた特別ユニット「DECA!BEAT（デカビート）」のまとめ。メンバーごとの紹介ポスト・新ビジュアル・TikTokの紹介動画・お披露目前のデカボなボイスを埋め込みでまとめ、YouTubeのMVやDANCE PRACTICE、グッズ紹介ポストへのリンクも掲載しています。";
  $jsonld_type = "CollectionPage";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>
<?php
// ------------------------------------------------------------------
// DECA!BEAT のデータ。期間限定ユニットで更新の予定がないため、
// スプレッドシートから一度だけ書き出したものをこのファイルが直接持っている。
// (Spreadsheet/ や Generated/ の生成物ではないので、直したいときはここを手で直す)
// 埋め込みは元コードのHTMLを持たず、ツイートID・TikTokの動画IDだけを持つ。
// ------------------------------------------------------------------

$dc_members = [
  [
    'slug' => 'kirara_yonemura',
    'name' => '米村姫良々',
    'yomi' => 'よねむらきらら',
    'group' => 'OCHA NORMA',
    'friend' => 'キラニャ',
    'official' => 'https://helloproject.com/ochanorma/kirara_yonemura/',
    'instagram' => 'https://www.instagram.com/p/DVNJYG7Eydl/',
    'intro' => ['2028410164597129579', '2026-03-02'],
    'visual' => ['2095088783930876210', '2026-09-02'],
    'tiktok' => '7611005836357356821',
    'dareda' => ['2025774505914995070', '2026-02-23'],
    'dareda_no' => '⑦',
  ],
  [
    'slug' => 'minami_okamura',
    'name' => '岡村美波',
    'yomi' => 'おかむらみなみ',
    'group' => 'BEYOOOOONDS',
    'friend' => 'ミイメェ',
    'official' => 'https://helloproject.com/beyooooonds/minami_okamura/',
    'instagram' => 'https://www.instagram.com/p/DVQXHd7E2fy/',
    'intro' => ['2028664238454243540', '2026-03-03'],
    'visual' => ['2095118103336153138', '2026-09-02'],
    'tiktok' => '7611469573522492692',
    'dareda' => ['2025143477059944504', '2026-02-21'],
    'dareda_no' => '⑥',
  ],
  [
    'slug' => 'kisaki_ebata',
    'name' => '江端妃咲',
    'yomi' => 'えばたきさき',
    'group' => 'Juice=Juice',
    'friend' => 'エバネコ',
    'official' => 'https://helloproject.com/juicejuice/kisaki_ebata/',
    'instagram' => 'https://www.instagram.com/p/DVSVLJxkwXR/',
    'intro' => ['2029027249018855787', '2026-03-04'],
    'visual' => ['2095451311823278122', '2026-09-03'],
    'tiktok' => '7611752871318605077',
    'dareda' => ['2024321836063088710', '2026-02-19'],
    'dareda_no' => '⑤',
  ],
  [
    'slug' => 'runo_yofu',
    'name' => '豫風瑠乃',
    'yomi' => 'よふうるの',
    'group' => 'つばきファクトリー',
    'friend' => 'ルノコーン',
    'official' => 'https://helloproject.com/tsubakifactory/runo_yofu/',
    'instagram' => 'https://www.instagram.com/p/DVV3lpTkxYz/',
    'intro' => ['2029400475767062546', '2026-03-05'],
    'visual' => ['2095478263816867999', '2026-09-03'],
    'tiktok' => '7612262625329597716',
    'dareda' => ['2023637008669917602', '2026-02-17'],
    'dareda_no' => '④',
  ],
  [
    'slug' => 'hana_goto',
    'name' => '後藤花',
    'yomi' => 'ごとうはな',
    'group' => 'アンジュルム',
    'friend' => 'ハナペン',
    'official' => 'https://helloproject.com/angerme/hana_goto/',
    'instagram' => 'https://www.instagram.com/p/DVXbEpOkx1f/',
    'intro' => ['2029765570149974086', '2026-03-06'],
    'visual' => ['2095791994874343640', '2026-09-04'],
    'tiktok' => '7612486397647244564',
    'dareda' => ['2022874484286644401', '2026-02-15'],
    'dareda_no' => '③',
  ],
  [
    'slug' => 'ako_yumigeta',
    'name' => '弓桁朱琴',
    'yomi' => 'ゆみげたあこ',
    'group' => 'モーニング娘。\'26',
    'friend' => 'シシゲタ',
    'official' => 'https://helloproject.com/morningmusume/ako_yumigeta/',
    'instagram' => 'https://www.instagram.com/p/DVatzf-E9AG/',
    'intro' => ['2030124792330998166', '2026-03-07'],
    'visual' => ['2095840357263282534', '2026-09-04'],
    'tiktok' => '7612960023349628181',
    'dareda' => ['2022139545064702373', '2026-02-13'],
    'dareda_no' => '②',
  ],
  [
    'slug' => 'rena_kamimura',
    'name' => '上村麗菜',
    'yomi' => 'かみむられな',
    'group' => 'ロージークロニクル',
    'friend' => 'ウサレナ',
    'official' => 'https://helloproject.com/rosychronicle/rena_kamimura/',
    'instagram' => 'https://www.instagram.com/p/DVdPXTLE3MR/',
    'intro' => ['2030509421403734220', '2026-03-08'],
    'visual' => ['2096155764264161518', '2026-09-05'],
    'tiktok' => '7613323791078329620',
    'dareda' => ['2021424691878220171', '2026-02-11'],
    'dareda_no' => '①',
  ],
];

$dc_videos = [
  [
    'title' => '『ワッチャ☆フューチャー』',
    'date' => '2026年3月10日',
    'url' => 'https://www.youtube.com/watch?v=gqgBqDDyPZ0',
    'id' => 'gqgBqDDyPZ0',
  ],
  [
    'title' => '『ワッチャ☆フューチャー』Music Video Making',
    'date' => '2026年3月10日',
    'url' => 'https://www.youtube.com/watch?v=VAzqgCGH8e4',
    'id' => 'VAzqgCGH8e4',
  ],
  [
    'title' => '『ワッチャ☆フューチャー』DANCE PRACTICE ver.',
    'date' => '2026年8月26日',
    'url' => 'https://www.youtube.com/watch?v=daXwOUr6bBs',
    'id' => 'daXwOUr6bBs',
  ],
  [
    'title' => '『ワッチャ☆フューチャー』MV密着',
    'date' => '2026年3月25日',
    'url' => 'https://www.youtube.com/watch?v=expaEkZZUgs&t=1054s',
    'id' => 'expaEkZZUgs',
    // 再生開始位置(秒)。URLの &t=1054s にあたるもの(埋め込みでは start= で渡す)
    'start' => 1054,
  ],
  [
    'title' => '『ワッチャ☆フューチャー』ダンスレッスン',
    'date' => '2026年4月3日',
    'url' => 'https://www.youtube.com/watch?v=wvDA5TWn-4M&t=338s',
    'id' => 'wvDA5TWn-4M',
    'start' => 338,
  ],
];

$dc_posts = [
  [
    'cat' => '全メンバー解禁',
    'title' => '全メンバー解禁',
    'date' => '2026年2月25日',
    'url' => 'https://x.com/Earthhacks2023/status/2026462173564916219',
  ],
  [
    'cat' => 'デカボMISSION',
    'title' => 'デカボMISSION vol.1',
    'date' => '2026年7月9日',
    'url' => 'https://x.com/Earthhacks2023/status/2075070265688748109',
  ],
  [
    'cat' => 'グッズ紹介',
    'title' => 'オフィシャルグッズ発売決定！第１弾',
    'date' => '2026年3月16日',
    'url' => 'https://x.com/Earthhacks2023/status/2033539259106468203',
  ],
  [
    'cat' => 'グッズ紹介',
    'title' => 'オフィシャルグッズ発売決定！第２弾',
    'date' => '2026年3月18日',
    'url' => 'https://x.com/Earthhacks2023/status/2034100856816271392',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月12日',
    'url' => 'https://x.com/Earthhacks2023/status/2032041822227480741',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月13日',
    'url' => 'https://x.com/Earthhacks2023/status/2032371302590480542',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月14日',
    'url' => 'https://x.com/Earthhacks2023/status/2032796825288393203',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'MVオフショット',
    'date' => '2026年3月15日',
    'url' => 'https://x.com/Earthhacks2023/status/2033172744297451818',
  ],
  [
    'cat' => 'オフショット',
    'title' => 'ひなフェスオフショット',
    'date' => '2026年3月22日',
    'url' => 'https://x.com/Earthhacks2023/status/2035707039142326460',
  ],
  [
    'cat' => 'デカビートフレンズ',
    'title' => 'デカビートフレンズ集合',
    'date' => '2026年9月5日',
    'url' => 'https://x.com/Earthhacks2023/status/2096190978843607276',
  ],
];

$dc_sns = [
  [
    'title' => '公式プロフィール',
    'url' => 'https://co.earth-hacks.jp/decabeat/',
  ],
  [
    'title' => 'TikTok',
    'url' => 'https://www.tiktok.com/@decarbo46',
  ],
  [
    'title' => 'Earth hacks X',
    'url' => 'https://x.com/Earthhacks2023/',
  ],
  [
    'title' => 'Earth hacks Instagram',
    'url' => 'https://www.instagram.com/earthhacks.jp/',
  ],
];

/** 埋め込みポストの投稿元(全ポスト Earth hacks 公式) */
const DC_X_ACCOUNT = 'Earthhacks2023';
const DC_TIKTOK_ACCOUNT = 'decarbo46';

function dc_h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** 2026-03-02 → 2026年3月2日 */
function dc_jdate(string $ymd): string {
    [$y, $m, $d] = array_map('intval', explode('-', $ymd));
    return "{$y}年{$m}月{$d}日";
}

/** 下部の公式リンク: URLからアイコンを決める */
function dc_sns_icon(string $url): string {
    if (str_contains($url, 'tiktok.com'))    return icon('tiktok-brand', 'ic ic-20');
    if (str_contains($url, 'instagram.com')) return icon('instagram-brand', 'ic ic-20');
    if (str_contains($url, 'x.com'))         return icon('x', 'ic ic-20');
    return icon('link-brand', 'ic ic-20');
}

/**
 * 埋め込み1枠。中身はJSが後から入れる(選択されたメンバーの分だけ読み込む)。
 * 埋め込みが表示できない環境でも辿れるように、元ポストへのリンクは常に出す。
 */
function dc_embed(string $type, string $label, string $id, string $url, string $linktext): string {
    return '<div class="dc-embed" data-type="' . $type . '" data-id="' . dc_h($id) . '">'
         . '<h4>' . dc_h($label) . '</h4>'
         . '<div class="dc-slot"></div>'
         . '<p class="dc-embed-link"><a href="' . dc_h($url) . '" target="_blank" rel="noopener noreferrer">'
         . dc_h($linktext) . '</a></p>'
         . '</div>';
}

function dc_x_embed(string $label, array $tw): string {
    $url = 'https://x.com/' . DC_X_ACCOUNT . '/status/' . $tw[0];
    return dc_embed('x', $label, $tw[0], $url, 'Xで見る（' . dc_jdate($tw[1]) . '）');
}
?>

<main>
  <h1>DECA!BEAT（デカビート）まとめ</h1>

  <p class="dc-lead">ハロー！プロジェクトとEarth hacksがコラボレーションして生まれたDECA!BEATの、各メンバーの紹介ポスト・新ビジュアル・TikTokでの紹介動画・お披露目前のデカボなボイスを見やすくまとめています。<br>
  またYouTubeのMVやDANCE PRACTICE・オフショットの動画もその場で再生でき、そのほかグッズ紹介などのポストもカードで並べています。</p>

  <details class="dc-about">
    <summary>DECA!BEATとは（公式プロフィールより）</summary>
    <blockquote cite="https://co.earth-hacks.jp/decabeat/">
      <p>地球の未来を歌い続けてきたハロー！プロジェクトと「無理なく楽しく」脱炭素の選択肢を広げるEarth hacksがコラボレーション！ハロー！プロジェクトの7つのグループから未来の地球を担うメンバーが集まって新ユニット「デカビート」としてみんなの「デカボ」を応援します。</p>
      <p class="dc-cite"><a href="https://co.earth-hacks.jp/decabeat/" target="_blank" rel="noopener noreferrer"><?= icon('link-brand', 'ic ic-20') ?>DECA!BEAT 公式プロフィール</a></p>
    </blockquote>
  </details>

  <!-- メンバー -->
  <section class="dc-section" id="members">
    <h2>メンバー</h2>
    <p>メンバーを選ぶと、そのメンバーの紹介ポスト・新ビジュアル・紹介動画・お披露目前の「だーれだ」ポストが表示されます。</p>

    <div class="tab-buttons dc-tabs" id="dc-member-tabs">
      <?php foreach ($dc_members as $m): ?>
      <button type="button" data-slug="<?= dc_h($m['slug']) ?>">
        <span class="dc-btn-name"><?= dc_h($m['name']) ?></span>
        <span class="dc-btn-friend"><?= dc_h($m['friend']) ?></span>
      </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($dc_members as $m): ?>
    <div class="dc-panel" id="dc-panel-<?= dc_h($m['slug']) ?>" hidden>
      <h3 class="dc-name"><?= dc_h($m['name']) ?><span class="dc-yomi"><?= dc_h($m['yomi']) ?></span></h3>
      <p class="dc-meta"><?= dc_h($m['group']) ?><span class="dc-friend">デカビートフレンズ：<?= dc_h($m['friend']) ?></span></p>
      <p class="dc-profile-links">
        <a href="<?= dc_h($m['official']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('link-brand', 'ic ic-20') ?>ハロー！プロジェクト公式プロフィール</a>
        <a href="<?= dc_h($m['instagram']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('instagram-brand', 'ic ic-20') ?>Instagram（紹介動画）</a>
      </p>
      <div class="dc-embeds">
        <?= dc_x_embed('メンバー紹介ポスト', $m['intro']) ?>
        <?= dc_x_embed('新ビジュアル', $m['visual']) ?>
        <?= dc_embed('tiktok', '紹介動画（TikTok）', $m['tiktok'],
              'https://www.tiktok.com/@' . DC_TIKTOK_ACCOUNT . '/video/' . $m['tiktok'], 'TikTokで見る') ?>
        <?= dc_x_embed('お披露目前のポスト「だーれだ！' . $m['dareda_no'] . '」', $m['dareda']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </section>

  <!-- YouTube -->
  <section class="dc-section" id="youtube">
    <h2>YouTube</h2>
    <p>タイトルを選んで再生ボタンを押すと、その場で再生されます。</p>
    <div class="tab-buttons dc-tabs" id="dc-video-tabs">
      <?php foreach ($dc_videos as $i => $v): ?>
      <button type="button" data-i="<?= $i ?>"><?= dc_h($v['title']) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="dc-player" id="dc-player"></div>
    <p class="dc-video-info" id="dc-video-info"></p>
    <noscript>
      <ul class="dc-video-list">
        <?php foreach ($dc_videos as $v): ?>
        <li><a href="<?= dc_h($v['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('youtube-brand', 'ic ic-20') ?><?= dc_h($v['title']) ?></a>（<?= dc_h($v['date']) ?>）</li>
        <?php endforeach; ?>
      </ul>
    </noscript>
  </section>

  <!-- ポスト -->
  <section class="dc-section" id="posts">
    <h2>ポスト</h2>
    <p>メンバー紹介以外のポストです。押すとXの元ポストが開きます。</p>
    <div class="card-grid">
      <?php foreach ($dc_posts as $p): ?>
      <div class="card dc-post">
        <?php if (!str_starts_with($p['title'], $p['cat'])): // タイトルと同じ分類は出さない ?>
        <span class="dc-post-cat"><?= dc_h($p['cat']) ?></span>
        <?php endif; ?>
        <a class="dc-post-link" href="<?= dc_h($p['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('x', 'ic ic-20') ?><?= dc_h($p['title']) ?></a>
        <p class="dc-post-date"><?= dc_h($p['date']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 公式リンク -->
  <section class="dc-section" id="official">
    <h2>公式リンク</h2>
    <ul class="dc-sns">
      <?php foreach ($dc_sns as $s): ?>
      <li><a href="<?= dc_h($s['url']) ?>" target="_blank" rel="noopener noreferrer"><?= dc_sns_icon($s['url']) ?><?= dc_h($s['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
</main>

<!-- X埋め込みウィジェット(公式の読み込みスニペット) -->
<script>
window.twttr = (function (d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0], t = window.twttr || {};
  if (d.getElementById(id)) return t;
  js = d.createElement(s); js.id = id; js.async = true;
  js.src = "https://platform.x.com/widgets.js";
  fjs.parentNode.insertBefore(js, fjs);
  t._e = [];
  t.ready = function (f) { t._e.push(f); };
  return t;
}(document, "script", "twitter-wjs"));
</script>

<script>
(function () {
  var VIDEOS = <?= json_encode($dc_videos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  /* ---- メンバー ---- */

  // 埋め込みは選択されたメンバーの分だけ読み込む(7人分を一度に読むと重いため)
  function loadEmbeds(panel) {
    panel.querySelectorAll('.dc-embed').forEach(function (box) {
      if (box.dataset.loaded) return;
      box.dataset.loaded = '1';
      var slot = box.querySelector('.dc-slot');
      var id = box.dataset.id;
      // 埋め込みが入ると .dc-loading はCSS側で隠れる(:only-child のときだけ出る)。
      // X側のPromiseは解決しないことがあるので、消すのをJSのタイミングに頼らない。
      var note = document.createElement('p');
      note.className = 'dc-loading';
      note.textContent = '読み込み中…';
      slot.appendChild(note);
      setTimeout(function () {
        if (slot.children.length === 1) {
          note.textContent = '埋め込みを表示できませんでした。下のリンクから見られます。';
        }
      }, 12000);

      if (box.dataset.type === 'x') {
        window.twttr.ready(function (tw) {
          tw.widgets.createTweet(id, slot, {
            lang: 'ja', conversation: 'none', dnt: true, align: 'center'
          });
        });
      } else {
        var f = document.createElement('iframe');
        f.src = 'https://www.tiktok.com/embed/v2/' + id;
        f.title = 'TikTokの紹介動画';
        f.loading = 'lazy';
        f.allow = 'encrypted-media; fullscreen; picture-in-picture';
        slot.appendChild(f);
      }
    });
  }

  var memberTabs = document.getElementById('dc-member-tabs');

  function selectMember(slug, updateHash) {
    var panel = document.getElementById('dc-panel-' + slug);
    if (!panel) return false;
    memberTabs.querySelectorAll('button').forEach(function (b) {
      b.classList.toggle('active', b.dataset.slug === slug);
    });
    document.querySelectorAll('.dc-panel').forEach(function (p) {
      p.hidden = (p !== panel);
    });
    loadEmbeds(panel);
    // 共有できるようにURLへ反映する(履歴は増やさない)
    if (updateHash && window.history.replaceState) {
      window.history.replaceState(null, '', '#' + slug);
    }
    return true;
  }

  memberTabs.addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (btn) selectMember(btn.dataset.slug, true);
  });

  window.addEventListener('hashchange', function () {
    selectMember(decodeURIComponent(location.hash.slice(1)), false);
  });

  /* ---- YouTube ---- */

  var player = document.getElementById('dc-player');
  var info = document.getElementById('dc-video-info');
  var current = 0;

  function mmss(sec) {
    return Math.floor(sec / 60) + ':' + ('0' + (sec % 60)).slice(-2);
  }

  function showVideo(i, play) {
    var v = VIDEOS[i];
    if (!v) return;
    current = i;
    document.querySelectorAll('#dc-video-tabs button').forEach(function (b) {
      b.classList.toggle('active', Number(b.dataset.i) === i);
    });
    player.innerHTML = '';
    if (play) {
      var f = document.createElement('iframe');
      // 開始位置の指定があるものは start= で渡す(URLの &t=◯s は埋め込みでは効かない)
      f.src = 'https://www.youtube-nocookie.com/embed/' + v.id + '?autoplay=1'
        + (v.start ? '&start=' + v.start : '');
      f.title = v.title;
      f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      f.allowFullscreen = true;
      player.appendChild(f);
    } else {
      // 押されるまでYouTubeのiframeは読み込まない
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'dc-thumb';
      btn.setAttribute('aria-label', v.title + ' を再生');
      btn.innerHTML = '<img src="https://i.ytimg.com/vi/' + v.id + '/hqdefault.jpg" alt="" loading="lazy">'
        + '<span class="dc-play">▶ 再生' + (v.start ? '（' + mmss(v.start) + 'から）' : '') + '</span>';
      btn.addEventListener('click', function () { showVideo(current, true); });
      player.appendChild(btn);
    }
    info.innerHTML = '';
    var a = document.createElement('a');
    a.href = v.url;
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
    a.textContent = v.title;
    info.appendChild(a);
    info.appendChild(document.createTextNode('（' + v.date + '）'));
  }

  // タイトルを選んだ時点では再生しない(サムネイルの▶を押してから再生する)
  document.getElementById('dc-video-tabs').addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (btn) showVideo(Number(btn.dataset.i), false);
  });

  /* ---- 初期表示 ---- */

  var hash = decodeURIComponent(location.hash.slice(1));
  if (!hash || !selectMember(hash, false)) {
    selectMember(memberTabs.querySelector('button').dataset.slug, false);
  }
  showVideo(0, false);
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
