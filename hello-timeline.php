<?php
  $title = "ハロプロ各グループタイムライン画像"; 
  $description = "モーニング娘。・アンジュルム・Juice=Juice・つばきファクトリー各グループの全メンバーのタイムライン画像を表示しています。";
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>ハロプロ各グループタイムライン画像</h1>
  モーニング娘。・アンジュルム・Juice=Juice・つばきファクトリー各グループの全メンバーのタイムライン画像を表示しています<br>
  
<div class="tab-buttons">
  <button onclick="showTab('mm')" class="active">モーニング娘。</button>
  <button onclick="showTab('anj')">アンジュルム</button>
  <button onclick="showTab('juice')">Juice=Juice</button>
  <button onclick="showTab('tsubaki')">つばきファクトリー</button>
</div>

<div id="tab-mm" class="tab-content"><img src="Timeline/timeline_mm.png"></div>
<div id="tab-anj" class="tab-content" style="display:none"><img src="Timeline/timeline_anj.png"></div>
<div id="tab-juice" class="tab-content" style="display:none"><img src="Timeline/timeline_juice.png"></div>
<div id="tab-tsubaki" class="tab-content" style="display:none"><img src="Timeline/timeline_tsubaki.png"></div>

<script>
function showTab(id) {
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab-buttons button').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-' + id).style.display = 'block';
  event.target.classList.add('active');
}
</script>

</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/timeline_generated.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
