<?php
  $title = "福田花音バースデーイベントまとめ";
  $description = "福田花音さんのこれまでのバースデーイベントの開催日時・会場をまとめました。セットリストが非公式サイト「Tomoko」に公開されていたものはリンクしています。";
  $jsonld_type = "Dataset";
  $about = "福田花音";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
<p class="site-title"><a href="/hello-birthday">メンバー一覧に戻る</a></p>
<h1>福田花音バースデーイベント会場まとめ</h1>
<p>福田花音さん（生まれ）のこれまでのバースデーイベントをまとめました。非公式サイトのハロプロセトリ検索システム「ToMoKo」にてセットリストが公開されていたものはリンクを貼っています。</p>


</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/birthday_generated.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/birthday_footer.php'; ?>
