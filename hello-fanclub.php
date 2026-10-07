<?php
  $title = "ハロプロ ファンクラブツアーまとめ";
  $description = "ハロー！プロジェクトのファンクラブツアー（バスツアー）を新しい順に一覧にまとめました。日程・参加メンバー・ライブ・ファンクラブや旅行会社のお知らせ・セットリストを1枚のカードで確認できます。";
  $jsonld_type = "Dataset";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ ファンクラブツアーまとめ</h1>
<div class="bt-lead">
  <p>ハロー！プロジェクトのファンクラブツアー（バスツアー）を、出発日の新しい順に並べています。</p>

  <p>ツアーには基本的にライブが含まれますが、最近はツアーにライブが含まれず、同じ日に別途チケットを購入するライブが開催される形が増えています。その場合は「別チケットのライブ」として表示しています。</p>

  <p>海外ツアーは自由行動の日などがあり、どこまでを開催日とするかがはっきりしないため、日程は年月までの表記とし、ライブの日付も時差があるため載せていません。</p>

  <p>お知らせのリンクは、以前はファンクラブのお知らせが中心でしたが、最近は旅行会社のページが申込ページになっています。ページが既に消えているものはWebArchive（魚拓）へリンクしています。</p>
</div>

<div class="card-grid bt-grid">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/bustour_cards.php'; ?>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/bustour_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
