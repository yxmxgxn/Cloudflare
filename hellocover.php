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
<p>サブスクでは、次の3作品に入っています。リンクは「笑顔に涙」の曲のページです。</p>
<ul>
  <li>『恋愛ハンター / 新垣里沙卒業記念盤』EP（2012/4/11）：
    <a href="https://music.apple.com/jp/album/514296802?i=514296806" target="_blank">Apple Music</a>・<a href="https://open.spotify.com/track/07tiaHFHnoECePaquTPMqS" target="_blank">Spotify</a>・<a href="https://music.amazon.co.jp/tracks/B00EPC1LAC" target="_blank">Amazon Music</a>・<a href="https://s.awa.fm/track/21c4c99c661dd9e69595" target="_blank">AWA</a>・<a href="https://music.line.me/webapp/track/mt0000000024955022" target="_blank">LINE MUSIC</a></li>
  <li>『プッチベスト 13』（2012/12/5）：
    <a href="https://music.apple.com/jp/album/576698522?i=576698624" target="_blank">Apple Music</a>・<a href="https://open.spotify.com/track/4v3cSWzcX0B0vZOoy7Pkma" target="_blank">Spotify</a>・<a href="https://music.amazon.co.jp/tracks/B0GVJLLGPN" target="_blank">Amazon Music</a>・<a href="https://s.awa.fm/track/2f5f6b3616525041be25" target="_blank">AWA</a>・<a href="https://music.line.me/webapp/track/mt0000000025557705" target="_blank">LINE MUSIC</a></li>
  <li>『モーニング娘。'14 カップリングコレクション2』（2014/3/12）：
    <a href="https://music.apple.com/jp/album/828941879?i=828944925" target="_blank">Apple Music</a>・<a href="https://open.spotify.com/track/5g2oxBNY3QtzBz8EbpDRr9" target="_blank">Spotify</a>・<a href="https://music.amazon.co.jp/tracks/B00IUEBKTI" target="_blank">Amazon Music</a>・<a href="https://s.awa.fm/track/bdba25bb43ffe5db2700" target="_blank">AWA</a>・<a href="https://music.line.me/webapp/track/mt00000000249557c4" target="_blank">LINE MUSIC</a></li>
</ul>
<p>新垣里沙のカバー8曲に3曲を加えて、CD『ハロカバ』（UFCW-1029、2012/6/27）になっています。「笑顔に涙」は入っていません。</p>

<h3>岡井千聖「ロマンティック 浮かれモード」（第3弾）</h3>
<p>第3弾（2011/1/24）で配信された「ロマンティック 浮かれモード」は、第2弾の「LOVE涙色」とあわせて、CDシングル『ロマンティック 浮かれモード/LOVE涙色』（UWKS-001）になっています。
（<a href="https://helloproject.com/release/2218/" target="_blank">ハロー!プロジェクト</a>・<a href="https://up-front-works.jp/release/2218/" target="_blank">UP-FRONT WORKS</a>）</p>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/hellocover_generated.php'; ?>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
