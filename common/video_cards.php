<?php
/**
 * YouTube動画のカード一覧(YouTube/ 以下の手書きリンク集をカード型にするための共通部品)。
 *
 *   <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/common/video_cards.php'; ?>
 *   <?= video_cards([
 *     ['id' => 'JVUuaG5Ptj8', 'start' => 1256, 'title' => 'ハロ！ステ#48', 'sub' => 'ハロコン'],
 *   ]) ?>
 *
 * id    : YouTubeの動画ID(必須)
 * start : 再生開始位置(秒)。URLの &t=◯s にあたる(省略可)
 * title : カードの見出し(必須)
 * sub   : 見出しの下の補足(省略可)
 *
 * サムネイルを押すまでiframeは読み込まない(lite embed)。
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

function video_cards(array $videos): string
{
    static $scriptDone = false;

    $out = '<div class="card-grid vc-grid">';
    foreach ($videos as $v) {
        $id = $v['id'];
        $start = (int)($v['start'] ?? 0);
        $url = 'https://www.youtube.com/watch?v=' . $id . ($start ? '&t=' . $start . 's' : '');

        $out .= '<div class="card vc-card">'
              . '<button type="button" class="vc-thumb" data-yt="' . vc_h($id) . '"'
              . ($start ? ' data-start="' . $start . '"' : '')
              . ' aria-label="' . vc_h($v['title']) . ' を再生">'
              . '<img src="https://i.ytimg.com/vi/' . vc_h($id) . '/hqdefault.jpg" alt="" loading="lazy">'
              . '<span class="vc-play">▶ ' . ($start ? vc_hms($start) . 'から再生' : '再生') . '</span>'
              . '</button>'
              . '<p class="vc-title">' . vc_h($v['title']) . '</p>'
              . (empty($v['sub']) ? '' : '<p class="vc-sub">' . vc_h($v['sub']) . '</p>')
              . '<p class="vc-link"><a href="' . vc_h($url) . '" target="_blank" rel="noopener noreferrer">'
              . icon('youtube-brand', 'ic ic-20') . 'YouTubeで見る</a></p>'
              . '</div>';
    }
    $out .= '</div>';

    // 再生用のスクリプトは1ページに1回だけ出す(video_cards を複数回呼んでも重複しない)
    if (!$scriptDone) {
        $scriptDone = true;
        $out .= <<<'JS'
<script>
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.vc-thumb');
  if (!btn) return;
  var wrap = document.createElement('div');
  wrap.className = 'vc-player';
  var f = document.createElement('iframe');
  // URLの &t=◯s は埋め込みでは効かないので start= で渡す
  f.src = 'https://www.youtube-nocookie.com/embed/' + btn.dataset.yt + '?autoplay=1'
    + (btn.dataset.start ? '&start=' + btn.dataset.start : '');
  f.title = btn.getAttribute('aria-label').replace(/ を再生$/, '');
  f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
  f.allowFullscreen = true;
  wrap.appendChild(f);
  btn.replaceWith(wrap);
});
</script>
JS;
    }
    return $out;
}
