<?php
  $title = "小川麻琴バースデーイベントまとめ";
  $description = "小川麻琴さんのこれまでのバースデーイベントの開催日時・会場をまとめました。セットリストが非公式サイト「Tomoko」に公開されていたものはリンクしています。";
  $jsonld_type = "Dataset";
  $about = "小川麻琴";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
<p class="site-title"><a href="/hello-birthday">メンバー一覧に戻る</a></p>
<h1>小川麻琴バースデーイベント会場まとめ</h1>
<p>小川麻琴さん（生まれ）のこれまでのバースデーイベントをまとめました。非公式サイトのハロプロセトリ検索システム「ToMoKo」にてセットリストが公開されていたものはリンクを貼っています。</p>


</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/birthday_generated.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/birthday_footer.php'; ?>
