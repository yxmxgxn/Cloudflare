<?php
  $noindex = true;
  http_response_code(404);
  $title = "404 Not Found";
  $description = "ページが見つかりませんでした";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>404 Not Found</h1>
  <p>お探しのページは見つかりませんでした。</p>
  <p>移動・削除されたか、URLが間違っている可能性があります。</p>
  <p><a href="/">トップページへ戻る</a></p>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
