<?php require_once __DIR__ . '/icons.php';  // 共通SVGアイコン icon() ?>
<!Doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<?php if (isset($noindex) && $noindex): ?>
<meta name="robots" content="noindex,nofollow">
<?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
  $p_title = isset($title) ? $title : "宮本佳林・M-line・ハロプロ Archive";
  $p_desc  = isset($description) ? $description : "宮本佳林さん・M-line・ハロープロジェクトのデータを公開しています";
  // OGP画像・ホーム画面アイコンのブランド別セット(img/{karin,hello,juice}/)。各ページで 'hello'/'juice' を指定、無ければ karin
  $brand = isset($brand) ? $brand : 'karin';
?>
<meta name="description" content="<?php echo $p_desc; ?>">

<meta property="og:title" content="<?php echo $p_title; ?>">
<meta property="og:description" content="<?php echo $p_desc; ?>">
<meta property="og:type" content="website">
<?php
  // 実行中ファイルのパスから正規URLを決め打ち（クエリ・拡張子を排除）
  $canonical_path = strtok($_SERVER['REQUEST_URI'], '?');   // クエリ除去
  $canonical_path = preg_replace('/\.(php|html)$/', '', $canonical_path); // 拡張子除去
  if ($canonical_path === '/index' || $canonical_path === '') {
      $canonical_path = '/';
  }
  $canonical_url = 'https://karin.heavy.jp' . $canonical_path;
?>
<meta property="og:url" content="<?php echo $canonical_url; ?>">
<meta property="og:image" content="https://karin.heavy.jp/img/<?php echo $brand; ?>/ogp-1200x630.png">
<meta property="og:site_name" content="宮本佳林・M-line・ハロプロ Archive">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="宮本佳林・M-line・ハロプロ Archive">
<meta name="twitter:description" content="宮本佳林さん・M-line・ハロープロジェクトのデータを公開しています">
<meta name="twitter:image" content="https://karin.heavy.jp/img/<?php echo $brand; ?>/ogp-1200x630.png">

<title><?php if (isset($title)) {echo $title . " | 宮本佳林・M-line・ハロプロ Archive"; } else {echo "宮本佳林・M-line・ハロプロ Archive"; } ?></title>

<!-- Webフォント(style.css の body で 'Noto Sans JP' を指定しているため必要) -->

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap">

<!-- CSS -->

<link rel="stylesheet" media="all" href="/css/style.css">

<!-- Favicon -->

<link rel="icon" href="/img/favicon.ico" type="image/x-icon" sizes="any">
<link rel="icon" type="image/png" href="/img/<?php echo $brand; ?>/android-chrome-192x192.png" sizes="192x192">
<link rel="icon" type="image/png" href="/img/<?php echo $brand; ?>/android-chrome-512x512.png" sizes="512x512">
<link rel="apple-touch-icon" href="/img/<?php echo $brand; ?>/apple-touch-icon.png">
<link rel="canonical" href="<?php echo $canonical_url; ?>">

<?php
// 構造化データ(JSON-LD) — 各ページが $jsonld_type をセットしたときだけ出力
if (isset($jsonld_type)) {
  $ld = [
    "@context"    => "https://schema.org",
    "@type"       => $jsonld_type,
    "name"        => $p_title,
    "description" => trim($p_desc),
    "url"         => $canonical_url,
  ];

  if ($jsonld_type === "Dataset") {
    $a = isset($about) ? $about : "宮本佳林";

    // 単体指定(文字列 or 連想配列1個)を配列に揃える
    $list = (is_array($a) && !isset($a["name"])) ? $a : [$a];

    $ld["about"] = array_map(function ($item) {
      if (is_string($item)) {                        // "名前" → Person
        return ["@type" => "Person", "name" => $item];
      }
      return [                                        // ["name"=>..,"type"=>..]
        "@type" => $item["type"] ?? "Person",         // type省略ならPerson
        "name"  => $item["name"],
      ];
    }, $list);
  }

  echo '<script type="application/ld+json">'
     . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
     . "</script>\n";
}
?>

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-MCL3G6E983"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-MCL3G6E983');
</script>

</head>

<body>
<header><?php $is_home = ($canonical_path === '/'); ?>
<?php if ($is_home): ?><h1>宮本佳林・M-line・ハロプロ Archive</h1>
<?php else: ?><p class="site-title"><a href="/">宮本佳林・M-line・ハロプロ Archive</a></p>
<?php endif; ?></header>
