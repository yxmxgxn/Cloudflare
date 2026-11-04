<?php
/**
 * YouTube動画のカード一覧(YouTube/ 以下の手書きリンク集をカード型にするための共通部品)。
 * サムネイルを押すまでiframeは読み込まない(lite embed)。
 *
 * ■ video_cards … 1動画 = 1カード
 *
 *   <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/common/video_cards.php'; ?>
 *   <?= video_cards([
 *     ['id' => 'JVUuaG5Ptj8', 'start' => 1256, 'title' => 'ハロ！ステ#48', 'sub' => 'ハロコン'],
 *   ]) ?>
 *
 *   id    : YouTubeの動画ID(必須)
 *   start : 再生開始位置(秒)。URLの &t=◯s にあたる(省略可)
 *   title : カードの見出し(必須)
 *   date  : 公開日・配信日。見出しの上に小さく出す(省略可。'2026/05/12' のように書く)
 *   sub   : 見出しの下の補足。複数行にしたいときは配列で渡す(省略可)
 *
 * ■ song_cards … 1曲 = 1カード。同じ曲の映像を何本でも並べられる
 *
 *   <?= song_cards([
 *     ['title' => '泣き虫少年', 'sub' => '原曲：Buono!', 'videos' => [
 *       ['id' => 'hDkrX2T-dYI', 'start' => 177, 'label' => '夏焼雅・宮本佳林・浅倉樹々'],
 *       ['id' => 'dCBeCwWNFeo', 'start' => 40,  'label' => '夏焼雅・鈴木愛理・宮本佳林'],
 *     ]],
 *   ]) ?>
 *
 *   title  : 曲名(必須)
 *   sub    : 曲名の下の補足。配列なら複数行(省略可)
 *   data   : カードに付ける data-* 属性(絞り込み用。['artist' => 'Buono!'] → data-artist="Buono!")(省略可)
 *   videos : 映像の一覧(必須)。id・start は video_cards と同じ
 *            label : 一覧に出す名前(ライブ名・歌っているメンバーなど)
 *            note  : label の下に小さく出す補足(日付・会場など)。配列なら複数行、空文字の行は出さない(省略可)
 *   カード上部は1本目のサムネイル。一覧の映像を押すと、その映像がカード上部で再生される。
 *
 * ■ その場で再生させたくないとき(動画が多いページなど)
 *   第2引数に ['embed' => false] を渡すと、サムネイルも一覧もYouTubeを新しいタブで開くだけになる。
 *   ページの中では統一すること(同じページで混ぜない)。
 */

require_once __DIR__ . '/icons.php';

function vc_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** 1256 → 20:56 / 3725 → 1:02:05 */
function vc_hms(int $sec): string
{
    $h = intdiv($sec, 3600);
    $m = intdiv($sec % 3600, 60);
    $s = $sec % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
}

function vc_url(array $v): string
{
    $start = (int)($v['start'] ?? 0);
    return 'https://www.youtube.com/watch?v=' . $v['id'] . ($start ? '&t=' . $start . 's' : '');
}

/** 再生ボタン・一覧の項目に付ける data-yt / data-start / data-title */
function vc_play_attrs(array $v, string $title): string
{
    $start = (int)($v['start'] ?? 0);
    return ' data-yt="' . vc_h($v['id']) . '"'
         . ($start ? ' data-start="' . $start . '"' : '')
         . ' data-title="' . vc_h($title) . '"';
}

/**
 * サムネイル。$embed が true なら押した時にその場で再生(それまでiframeは読み込まない)、
 * false なら YouTube を新しいタブで開くだけ(動画が多いページはこちら)。
 */
function vc_thumb(array $v, string $title, bool $embed = true): string
{
    $start = (int)($v['start'] ?? 0);
    $time = $start ? vc_hms($start) . 'から' : '';
    // サムネイルは出さない(長い動画の途中から始まるので、画像と場面が合わないため)
    $inner = '<span class="vc-play">▶ ' . ($time === '' ? ($embed ? '再生' : 'YouTubeで見る') : $time . ($embed ? '再生' : '見る')) . '</span>';
    if (!$embed) {
        return '<a class="vc-thumb" href="' . vc_h(vc_url($v)) . '" target="_blank" rel="noopener noreferrer"'
             . ' aria-label="' . vc_h($title) . ' をYouTubeで見る">' . $inner . '</a>';
    }
    return '<button type="button" class="vc-thumb"' . vc_play_attrs($v, $title)
         . ' aria-label="' . vc_h($title) . ' を再生">' . $inner . '</button>';
}

function vc_subs($sub): string
{
    $out = '';
    foreach ((array)($sub ?? []) as $line) {
        $out .= '<p class="vc-sub">' . vc_h($line) . '</p>';
    }
    return $out;
}

function video_cards(array $videos, array $opts = []): string
{
    $embed = $opts['embed'] ?? true;   // false ならその場で再生せずYouTubeを開く
    $out = '<div class="card-grid vc-grid">';
    foreach ($videos as $v) {
        // embed => false のときは下の「YouTubeで見る」だけにする(時間付きの再生ボタンと中身が同じため)
        $out .= '<div class="card vc-card">'
              . ($embed ? vc_thumb($v, $v['title']) : '')
              . (empty($v['date']) ? '' : '<p class="vc-date">' . vc_h($v['date']) . '</p>')
              . '<p class="vc-title">' . vc_h($v['title']) . '</p>'
              . vc_subs($v['sub'] ?? null)
              . '<p class="vc-link"><a href="' . vc_h(vc_url($v)) . '" target="_blank" rel="noopener noreferrer">'
              . icon('youtube-brand', 'ic ic-20') . 'YouTubeで見る</a></p>'
              . '</div>';
    }
    return $out . '</div>' . ($embed ? vc_script() : '');
}

function song_cards(array $songs, array $opts = []): string
{
    $embed = $opts['embed'] ?? true;   // false ならその場で再生せずYouTubeを開く
    $out = '<div class="card-grid vc-grid">';
    foreach ($songs as $s) {
        $attrs = '';
        foreach ($s['data'] ?? [] as $k => $val) {
            $attrs .= ' data-' . vc_h($k) . '="' . vc_h($val) . '"';
        }
        $out .= '<div class="card vc-card sc-card"' . $attrs . '>'
              . '<div class="vc-stage">' . vc_thumb($s['videos'][0], $s['title'], $embed) . '</div>'
              . '<p class="vc-title">' . vc_h($s['title']) . '</p>'
              . vc_subs($s['sub'] ?? null)
              . '<ul class="sc-list">';
        foreach ($s['videos'] as $i => $v) {
            $start = (int)($v['start'] ?? 0);
            $out .= '<li class="sc-item' . ($i === 0 ? ' is-current' : '') . '">'
                  . ($embed
                        ? '<button type="button" class="sc-pick"' . vc_play_attrs($v, $s['title']) . '>'
                        : '<a class="sc-pick" href="' . vc_h(vc_url($v)) . '" target="_blank" rel="noopener noreferrer">')
                  . '<span class="sc-label">' . vc_h($v['label']) . '</span>'
                  . implode('', array_map(
                        fn($n) => '<span class="sc-note">' . vc_h($n) . '</span>',
                        array_filter((array)($v['note'] ?? []), 'strlen')))
                  . ($embed ? '</button>' : '</a>')
                  . '<a class="sc-yt" href="' . vc_h(vc_url($v)) . '" target="_blank" rel="noopener noreferrer"'
                  . ' title="YouTubeで開く' . ($start ? '（' . vc_hms($start) . 'から）' : '') . '">'
                  . icon('youtube-brand', 'ic ic-20', 'YouTubeで開く') . '</a>'
                  . '</li>';
        }
        $out .= '</ul></div>';
    }
    return $out . '</div>' . ($embed ? vc_script() : '');
}

/** 再生用のスクリプト。1ページに1回だけ出す(何度呼んでも重複しない) */
function vc_script(): string
{
    static $done = false;
    if ($done) return '';
    $done = true;
    return <<<'JS'
<script>
(function () {
  function player(d) {
    var wrap = document.createElement('div');
    wrap.className = 'vc-player';
    var f = document.createElement('iframe');
    // URLの &t=◯s は埋め込みでは効かないので start= で渡す
    f.src = 'https://www.youtube-nocookie.com/embed/' + d.yt + '?autoplay=1'
      + (d.start ? '&start=' + d.start : '');
    f.title = d.title;
    f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    f.allowFullscreen = true;
    wrap.appendChild(f);
    return wrap;
  }

  document.addEventListener('click', function (e) {
    // サムネイル: その場でプレイヤーに差し替える
    var thumb = e.target.closest('.vc-thumb');
    if (thumb) {
      thumb.replaceWith(player(thumb.dataset));
      return;
    }
    // 曲カードの一覧: 選んだ映像をカード上部で再生する
    var pick = e.target.closest('.sc-pick');
    if (pick) {
      var card = pick.closest('.sc-card');
      var stage = card.querySelector('.vc-stage');
      stage.innerHTML = '';
      stage.appendChild(player(pick.dataset));
      card.querySelectorAll('.sc-item').forEach(function (li) {
        li.classList.toggle('is-current', li.contains(pick));
      });
    }
  });
})();
</script>
JS;
}
