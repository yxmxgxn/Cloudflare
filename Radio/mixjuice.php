<?php
  $title = "Juice=Juice 松永里愛のMIX=Juice 選曲まとめ"; 
  $description = "TBSラジオ 宮本佳林 Music Palette♪のJuice=Juice 松永里愛のMIX=Juiceのコーナーで選曲された曲をまとめています。";
  $jsonld_type = "Dataset";
  $about = [
    "工藤由愛", "松永里愛", "有澤一華", "江端妃咲", "石山咲良", "遠藤彩加里", "川嶋美楓",
    ["name" => "Juice=Juice", "type" => "MusicGroup"],  // グループだけ明示
  ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>Juice=Juice 松永里愛のMIX=Juice 選曲まとめ</h1>
  TBSラジオ 宮本佳林 Music Palette♪のJuice=Juice 松永里愛のMIX=Juiceのコーナーで選曲された曲をまとめています<br>
  放送日をクリックすると<a href="https://www.tbsradio.jp/mp/" target="_blank" rel="noopener noreferrer">TBSラジオ Music Palette♪</a>の各回のページに<br>
  Xのアイコンを飛ぶとポストに飛びます<br>
  <div id="mixjuice-filter">
    <select id="mixjuice-select-selector">
      <option value="">すべての選曲者</option>
      <?php
      $members = ['松永里愛','工藤由愛','有澤一華','江端妃咲','石山咲良','遠藤彩加里','川嶋美楓'];
      foreach ($members as $m) {
        echo '<option value="' . htmlspecialchars($m) . '">' . htmlspecialchars($m) . '</option>';
      }
      ?>
    </select>
  </div>
  <div id="mixjuice-wrap">
    <table id="mixjuice-table">
        <thead>
        <tr>
            <th>放送日</th>
            <th>曲名 / アーティスト</th>
            <th>選曲者</th>
            <th>テーマ</th>
          </tr>
        </thead>
      <tbody id="mixjuice-tbody">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/Spreadsheet/mixjuice_table.php'; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/Generated/mixjuice_generated.php'; ?>

<script>
(function () {
  document.getElementById('mixjuice-select-selector').addEventListener('change', function () {
    const selector = this.value;
    const rows = document.querySelectorAll('#mixjuice-tbody tr');
    rows.forEach(tr => {
      const s = tr.dataset.selector || '';
      const show = !selector || s === selector;
      tr.style.display = show ? '' : 'none';
    });
  });
})();
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
