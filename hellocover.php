<?php
  $title = "ハロカバまとめ（ハロプロ配信限定カバー 第1〜6弾）";
  $description = "ハロー!プロジェクトの配信限定カバー「ハロカバ」（2010年12月〜2011年3月、第1〜6弾・全50録音）を、曲・カバーしたメンバー・配信日ごとにまとめ、レコチョク・mora・iTunesへのリンクを一覧にしています。";
  $jsonld_type = "Dataset";
  $about = ["name" => "ハロー!プロジェクト", "type" => "Organization"];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロカバまとめ</h1>
  「ハロカバ」は、ハロー!プロジェクトのメンバーがハロプロの人気曲をカバーした、配信限定のシリーズです。<br>
  2010年12月から2011年3月まで、第1弾〜第6弾で全50録音が配信されました。<br>
  配信サービスごとのリンクは、今開けるものだけ載せています。空欄は、そのサービスで見つからなかったものです。

<div class="hc-wrap">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/hellocover_filter.php'; ?>
<div class="hanakin-wrap">
<table class="hanakin-table hc-table">
<thead>
<tr>
<th>曲名</th>
<th>原曲</th>
<th>カバー</th>
<th>レコチョク</th>
<th>mora</th>
<th>iTunes</th>
<th>弾（配信日）</th>
</tr>
</thead>
<tbody>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/hellocover_table.php'; ?>
</tbody>
</table>
</div>
</div>

<div class="hc-notes">
<h2>表のリンクについての補足</h2>

<h3>亀井絵里（第1弾・9曲）</h3>
<p>レコチョクは曲ごとにリンクがあります。moraは9曲が1つのパッケージ（UFDL-1078）で、曲ごとには買えません。表の亀井絵里の行は、moraリンクがすべて同じページです。iTunesでは見つかっていません。</p>

<h3>新垣里沙「笑顔に涙〜THANK YOU! DEAR MY FRIENDS〜」（第3弾）</h3>
<p>レコチョク・mora・iTunesのどれにも、この曲単体のリンクがないので、表は空欄です。レコチョクではアルバム単位でしか買えません。</p>
<p>iTunes（Apple Music）の日本のカタログには、次の3作品に入っています。曲名の表記は、3つとも「笑顔に涙 - Thank You! Dear My Friends」です。ハロカバと同じ録音かどうかは、聞き比べないと確定できていません。</p>
<ul>
  <li>『恋愛ハンター / 新垣里沙卒業記念盤』EP（2012/4/11）</li>
  <li>『プッチベスト 13』（2012/4/11）</li>
  <li>『モーニング娘。'14 カップリングコレクション2』（2014/3/12）</li>
</ul>
<p>新垣里沙のCD『ハロカバ』（UFCW-1029、2012/6/27発売、税抜2,857円、全11曲）には、真夏の光線・ふるさと・聖なる鐘がひびく夜・恋をしちゃいました!・LOVE涙色・香水・恋の花・お願い魅惑のターゲットのほか、3曲が入っています。「笑顔に涙」は入っていません。このCDは配信では見つかりませんでした。</p>

<h3>岡井千聖「ロマンティック 浮かれモード」（第3弾）</h3>
<p>第3弾（2011/1/24）で配信された「ロマンティック 浮かれモード」は、第2弾の「LOVE涙色」とあわせて、CDシングル『ロマンティック 浮かれモード/LOVE涙色』（UWKS-001）になっています。</p>
<ul>
  <li>公式のリリース情報では、発売日は2011/1/25、定価は734円（税抜667円）です。
    （<a href="https://helloproject.com/release/2218/" target="_blank">ハロー!プロジェクト</a>・<a href="https://up-front-works.jp/release/2218/" target="_blank">UP-FRONT WORKS</a>）</li>
  <li>2011/2/18のお知らせでは、ライブ会場限定で販売して好評だったので、オンラインショップ「e-LineUP!」でも販売すると案内しています。価格は700円（税込）です。
    （<a href="https://helloproject.com/news/1488/" target="_blank">お知らせ</a>）</li>
  <li>2011/2/24のお知らせでは、追加予約の受付を、2011/3/15までの期間限定で案内しています。
    （<a href="https://helloproject.com/news/1482/" target="_blank">お知らせ</a>）</li>
</ul>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/hellocover_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
