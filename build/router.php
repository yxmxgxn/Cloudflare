<?php
// php -S 用ルーター。.htaccess のクリーンURLロジックを再現する。
// /foo → foo.php、/ や /dir/ → index.php、実在する静的ファイルはそのまま配信。

$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = $_SERVER['DOCUMENT_ROOT'];

// 実在するファイルは PHP ならこのプロセスで実行、静的ならビルトインサーバーに任せる
if ($uri !== '/' && is_file($root . $uri)) {
    if (substr($uri, -4) === '.php') {
        $_SERVER['SCRIPT_NAME'] = $uri;
        chdir(dirname($root . $uri));
        require $root . $uri;
        return true;
    }
    return false;
}

// / → index.php
if ($uri === '/' || $uri === '/index') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require $root . '/index.php';
    return true;
}

// クリーンURL → 実体 .php
$candidate = $root . rtrim($uri, '/') . '.php';
if (is_file($candidate)) {
    $_SERVER['SCRIPT_NAME'] = rtrim($uri, '/') . '.php';
    chdir(dirname($candidate));
    require $candidate;
    return true;
}

http_response_code(404);
require $root . '/404.php';
return true;
