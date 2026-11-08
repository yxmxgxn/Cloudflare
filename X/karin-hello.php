<?php
  $title = "#佳林の１日１ハロまとめ"; 
  $description = "宮本佳林さんがハロープロジェクトサブスク解禁にともない毎日ハロプロの曲を1曲紹介する企画をまとめました。日付に元ポスト・アルバム名に公式のリンク・作詞作曲編曲や、YouTube Music／Spotify／Amazon Music／Apple Music／AWA／LINE MUSIC／NexTone.LinkのURLなどをいれています。";
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
  <h1>#佳林の１日１ハロまとめ</h1>
  宮本佳林さんがXでハロープロジェクトサブスク解禁にともない毎日ハロプロの曲を<a href="https://x.com/search?q=%23%E4%BD%B3%E6%9E%97%E3%81%AE1%E6%97%A51%E3%83%8F%E3%83%AD%20from%3A%40karin__miyamoto&src=typed_query&f=live" target="_blank">#佳林の１日１ハロ</a>で1曲紹介する企画をまとめました。<br>
  日付に元ポスト・アルバム名に公式のリンク・作詞作曲編曲・YouTube Music／Spotify／Amazon Music／Apple Music／AWA／LINE MUSIC／NexTone.LinkのURLをいれています。<br>
  アーティスト名・作詞作曲編曲でフィルタをかけられます。<br>
  プレイリストを作りました：<a href="https://music.youtube.com/playlist?list=PLAbxdMpUP3BU" target="_blank" class="kh-playlist" data-embed="https://www.youtube.com/embed/videoseries?list=PLAbxdMpUP3BU">YouTube Music</a>・<a href="https://open.spotify.com/playlist/5YVrEQ0hb7LTOviTXum8zi" target="_blank" class="kh-playlist" data-embed="https://open.spotify.com/embed/playlist/5YVrEQ0hb7LTOviTXum8zi">Spotify</a>・<a href="https://music.amazon.co.jp/user-playlists/46349edb307f4bda8ab48d0f060a0c69jajp" target="_blank" class="kh-playlist" data-embed="https://music.amazon.co.jp/embed/46349edb307f4bda8ab48d0f060a0c69jajp/?id=EpScUMtmV8&amp;marketplaceId=A1VC38T7YXB528&amp;musicTerritory=JP">Amazon Music</a><br>
  <div id="kh-playlist-embed" hidden></div>

<div id="kh-post-search">
  <input type="search" id="kh-post-q" placeholder="ポストの本文を検索…" aria-label="ポストの本文を検索">
  <span id="kh-post-count"></span>
</div>
<div id="kh-post-results"></div>

<div id="karinhello-controls">
  <input type="text" id="kh-search" placeholder="曲名・アーティスト・作詞作曲編曲で絞り込み…" oninput="khFilter()">
  <select id="kh-artist" onchange="khFilter()">
    <option value="">すべてのアーティスト</option>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karin-hello_artists.php'; ?>
  </select>
  <span id="karinhello-count"></span>
</div>
<div id="kh-queue" hidden></div>
<div id="karinhello-table">
  <table id="karinhello">
    <thead>
      <tr>
        <th onclick="khSort('date')" id="kh-th-date">ポスト日</th>
        <th onclick="khSort('song')" id="kh-th-song">曲名</th>
        <th onclick="khSort('artist')" id="kh-th-artist">アーティスト</th>
        <th>アルバム</th>
        <th>作詞</th>
        <th>作曲</th>
        <th>編曲</th>
        <th>配信</th>
      </tr>
    </thead>
    <tbody id="kh-tbody">
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/karin-hello_table.php'; ?>
    </tbody>
  </table>
</div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/karin-hello_generated.php'; ?>
<script>
(function() {
  var sortKey = 'date';
  var sortAsc = false;

  // DOMからtrを読み取ってallRowsを構築
  var tbody = document.getElementById('kh-tbody');
  var allRows = Array.from(tbody.querySelectorAll('tr')).map(function(tr) {
    return {
      el: tr,
      date:     tr.cells[0] ? tr.cells[0].textContent.trim() : '',
      song:     tr.cells[1] ? tr.cells[1].textContent.trim() : '',
      artist:   tr.getAttribute('data-artist') || '',
      lyricist: tr.cells[4] ? tr.cells[4].textContent.trim() : '',
      composer: tr.cells[5] ? tr.cells[5].textContent.trim() : '',
      arranger: tr.cells[6] ? tr.cells[6].textContent.trim() : '',
    };
  });

  function khFilter() {
    var q = document.getElementById('kh-search').value.toLowerCase();
    var aFilter = document.getElementById('kh-artist').value;

    var filtered = allRows.filter(function(r) {
      var matchQ = !q
        || r.song.toLowerCase().indexOf(q) !== -1
        || r.artist.toLowerCase().indexOf(q) !== -1
        || r.lyricist.toLowerCase().indexOf(q) !== -1
        || r.composer.toLowerCase().indexOf(q) !== -1
        || r.arranger.toLowerCase().indexOf(q) !== -1;
      var matchA = !aFilter || r.artist === aFilter;
      return matchQ && matchA;
    });

    // ソート
    filtered.sort(function(a, b) {
      var av = sortKey === 'date' ? a.date : sortKey === 'song' ? a.song : a.artist;
      var bv = sortKey === 'date' ? b.date : sortKey === 'song' ? b.song : b.artist;
      return sortAsc ? av.localeCompare(bv, 'ja') : bv.localeCompare(av, 'ja');
    });

    // 全trを非表示にしてからフィルタ結果を順に追加
    allRows.forEach(function(r) { r.el.style.display = 'none'; });
    filtered.forEach(function(r) {
      r.el.style.display = '';
      tbody.appendChild(r.el);
    });

    document.getElementById('karinhello-count').textContent = filtered.length + ' 件 / 全' + allRows.length + ' 件';
    renderQueue(filtered);
  }

  // 絞り込み結果のYouTube動画IDを、表示順にそのまま並べた連続再生リンクを作る。
  // watch_videos?video_ids= はID列から一時プレイリストを作って再生する。IDは50件までなので50曲ずつに分ける。
  var QUEUE_MAX = 50;
  function renderQueue(rows) {
    var box = document.getElementById('kh-queue');
    var ids = [];
    rows.forEach(function(r) {
      var a = r.el.querySelector('td.kh-links a.kh-yt');
      var m = a && /[?&]v=([\w-]{11})/.exec(a.href);
      if (m && ids.indexOf(m[1]) === -1) ids.push(m[1]);
    });

    box.textContent = '';
    if (!ids.length) { box.hidden = true; return; }

    box.appendChild(document.createTextNode('この絞り込みを連続再生（YouTube）：'));
    for (var i = 0; i < ids.length; i += QUEUE_MAX) {
      var chunk = ids.slice(i, i + QUEUE_MAX);
      var a = document.createElement('a');
      a.href = 'https://www.youtube.com/watch_videos?video_ids=' + chunk.join(',');
      a.target = '_blank';
      a.textContent = ids.length > QUEUE_MAX
        ? (i + 1) + '〜' + (i + chunk.length) + '曲目'
        : ids.length + '曲を再生';
      box.appendChild(a);
    }
    box.hidden = false;
  }

  function khSort(key) {
    if (sortKey === key) sortAsc = !sortAsc;
    else { sortKey = key; sortAsc = true; }
    document.querySelectorAll('#karinhello th').forEach(function(th) { th.classList.remove('sorted'); });
    var map = { date: 'kh-th-date', song: 'kh-th-song', artist: 'kh-th-artist' };
    if (map[key]) document.getElementById(map[key]).classList.add('sorted');
    khFilter();
  }

  // 初期表示（全件・日付降順）
  khFilter();

  window.khFilter = khFilter;
  window.khSort = khSort;
})();

// プレイリストのリンクを押したら、その下に埋め込みプレイヤーを出す（同じリンクをもう一度押すと閉じる）
(function() {
  var box = document.getElementById('kh-playlist-embed');
  var current = null;
  document.querySelectorAll('a.kh-playlist').forEach(function(a) {
    a.addEventListener('click', function(e) {
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
      e.preventDefault();
      box.textContent = '';
      if (current === a) { current = null; box.hidden = true; return; }
      current = a;
      var f = document.createElement('iframe');
      f.src = a.getAttribute('data-embed');
      f.title = a.textContent + ' プレイリスト';
      f.allow = 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture';
      f.allowFullscreen = true;
      f.referrerPolicy = 'strict-origin-when-cross-origin';
      box.appendChild(f);
      box.hidden = false;
    });
  });
})();

// ポスト日のリンクを押したら、その場にXの埋め込みをポップアップで出す。
// 168件ぶんを最初から埋め込むと重く、Xにも弾かれる(デカビートのページで経験済み)ので、押された1件だけiframeで読む。
// 埋め込みの下には元ポストへのリンクを置く。Ctrl/Shift/中クリックは今までどおりそのままXへ飛ぶ。
(function() {
  var LINK_SEL = '#kh-tbody td:first-child a, #kh-post-results .kh-post a';
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

  function open(a, id) {
    close();
    openLink = a;

    pop = document.createElement('div');
    pop.className = 'kh-embed';
    pop.setAttribute('role', 'dialog');
    pop.setAttribute('aria-label', 'ポストの埋め込み');

    var x = document.createElement('button');
    x.type = 'button';
    x.className = 'kh-embed-close';
    x.setAttribute('aria-label', '閉じる');
    x.textContent = '×';
    x.addEventListener('click', close);

    // 埋め込みはポストIDのURLだけで出せる。押された1件ぶんだけiframeを作る
    var f = document.createElement('iframe');
    f.className = 'kh-embed-frame';
    f.src = 'https://platform.twitter.com/embed/Tweet.html?id=' + id + '&lang=ja&dnt=true&theme=light';
    f.title = 'ポストの埋め込み';
    f.loading = 'lazy';
    f.referrerPolicy = 'strict-origin-when-cross-origin';

    var link = document.createElement('p');
    link.className = 'kh-embed-link';
    var go = document.createElement('a');
    go.href = a.href;
    go.target = '_blank';
    go.textContent = 'Xで見る →';
    link.appendChild(go);

    pop.appendChild(x);
    pop.appendChild(f);
    pop.appendChild(link);
    document.body.appendChild(pop);
    place(a);
    keepInView();
  }

  document.addEventListener('click', function(e) {
    var a = e.target.closest ? e.target.closest(LINK_SEL) : null;
    if (a) {
      var m = /\/status\/(\d+)/.exec(a.href);
      if (!m || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
      e.preventDefault();
      if (openLink === a) close(); else open(a, m[1]);
      return;
    }
    if (pop && !pop.contains(e.target)) close();
  });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') close(); });
})();

// ポスト本文の検索。本文はページに持たず、Worker が一致したポストIDだけ返す
(function() {
  var input = document.getElementById('kh-post-q');
  var results = document.getElementById('kh-post-results');
  var count = document.getElementById('kh-post-count');

  var posts = {};
  var order = [];
  document.querySelectorAll('#kh-tbody tr[data-post]').forEach(function(tr) {
    var id = tr.getAttribute('data-post');
    if (!id) return;
    var link = tr.cells[0].querySelector('a');
    posts[id] = {
      date: tr.cells[0].textContent.trim(),
      url: link ? link.href : '',
      song: tr.cells[1].textContent.trim(),
      artist: tr.getAttribute('data-artist') || ''
    };
    order.push(id);
  });

  var timer = null;
  var seq = 0;

  function render(ids) {
    results.textContent = '';
    var hit = {};
    ids.forEach(function(id) { hit[id] = true; });
    var shown = order.filter(function(id) { return hit[id]; });
    count.textContent = shown.length ? shown.length + ' 件' : '見つかりませんでした';

    shown.forEach(function(id) {
      var p = posts[id];
      var card = document.createElement('div');
      card.className = 'kh-post';
      var date = document.createElement(p.url ? 'a' : 'span');
      date.textContent = p.date;
      if (p.url) { date.href = p.url; date.target = '_blank'; date.rel = 'noopener noreferrer'; }
      card.appendChild(date);
      card.appendChild(document.createTextNode('　' + p.song + ' / ' + p.artist));
      results.appendChild(card);
    });
  }

  function search() {
    var q = input.value.trim();
    var mySeq = ++seq;
    if (!q) { results.textContent = ''; count.textContent = ''; return; }
    if (q.length < 2) { results.textContent = ''; count.textContent = '2文字以上で検索'; return; }

    fetch('/api/karinhello-search?q=' + encodeURIComponent(q))
      .then(function(r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function(d) { if (mySeq === seq) render(d.ids || []); })
      .catch(function() {
        if (mySeq !== seq) return;
        results.textContent = '';
        count.textContent = '検索でエラーが起きました';
      });
  }

  input.addEventListener('input', function() {
    clearTimeout(timer);
    timer = setTimeout(search, 250);
  });
})();
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
