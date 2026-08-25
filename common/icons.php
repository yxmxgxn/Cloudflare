<?php
/**
 * サイト共通のSVGアイコン。定義元は common/icons.json 一箇所だけで、
 * Python側(Python/icons.py)も同じJSONを読む。
 *
 *   <?= icon('youtube-brand') ?>            → class="ic"
 *   <?= icon('instagram', 'ic sns-icon') ?> → クラスを指定
 *   <?= icon('x', 'ic', 'X') ?>             → aria-hidden ではなく aria-label を付ける
 *
 * サイズはCSS側(.ic とページ別の上書き)で決める。SVGにwidth/heightは付けない。
 */

function icon_defs(): array
{
    static $defs = null;
    if ($defs === null) {
        $json = file_get_contents(__DIR__ . '/icons.json');
        $defs = json_decode($json, true);
        if (!is_array($defs)) {
            throw new RuntimeException('common/icons.json を読めませんでした');
        }
        unset($defs['_comment']);
    }
    return $defs;
}

function icon(string $name, string $class = 'ic', ?string $label = null): string
{
    $defs = icon_defs();
    if (!isset($defs[$name])) {
        // 名前の打ち間違いは静かに消えると気づけないので、ビルド時に落とす
        throw new RuntimeException("アイコン '{$name}' は common/icons.json にありません");
    }
    $d = $defs[$name];
    $a11y = $label === null
        ? 'aria-hidden="true"'
        : 'role="img" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '"';
    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"'
        . ' viewBox="' . $d['vb'] . '" xmlns="http://www.w3.org/2000/svg" ' . $a11y . '>'
        . $d['svg'] . '</svg>';
}
