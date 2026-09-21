<?php
  $title = "小片リサバースデーイベントまとめ";
  $description = "小片リサさんのこれまでのバースデーイベントの開催日時・会場をまとめました。セットリストが非公式サイト「Tomoko」に公開されていたものはリンクしています。";
  $jsonld_type = "Dataset";
  $about = "小片リサ";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
<p class="site-title"><a href="/hello-birthday">メンバー一覧に戻る</a></p>
<h1>小片リサバースデーイベント会場まとめ</h1>
<p>小片リサさん（生まれ）のこれまでのバースデーイベントをまとめました。非公式サイトのハロプロセトリ検索システム「ToMoKo」にてセットリストが公開されていたものはリンクを貼っています。</p>


</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/birthday_generated.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/birthday_footer.php'; ?>
