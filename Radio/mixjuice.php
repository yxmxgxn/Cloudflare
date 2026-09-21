<?php
  $title = "Juice=Juice 松永里愛のMIX=Juice 選曲まとめ"; 
  $description = "TBSラジオ 宮本佳林 Music Palette♪のJuice=Juice 松永里愛のMIX=Juiceのコーナーで選曲された曲をまとめています。";
  $jsonld_type = "Dataset";
  $about = [
    "工藤由愛", "松永里愛", "有澤一華", "江端妃咲", "石山咲良", "遠藤彩加里", "川嶋美楓",
    ["name" => "Juice=Juice", "type" => "MusicGroup"],  // グループだけ明示
  ];
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

  $CSV_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQGBS0Tm7Z9I32xmJ0PzdFma5Q5tJpvg71v8JRw7-QSlQsr57nqkYeVy0kSEOYttMg6FZfucZiHFYYt/pub?gid=1463327244&single=true&output=csv';

  function parse_csv_line($line) {
    $result = [];
    $cur = '';
    $inQuote = false;
    $len = strlen($line);
    for ($i = 0; $i < $len; $i++) {
      $c = $line[$i];
      if ($inQuote) {
        if ($c === '"' && isset($line[$i+1]) && $line[$i+1] === '"') {
          $cur .= '"'; $i++;
        } elseif ($c === '"') {
          $inQuote = false;
        } else {
          $cur .= $c;
        }
      } else {
        if ($c === '"') {
          $inQuote = true;
        } elseif ($c === ',') {
          $result[] = trim($cur);
          $cur = '';
        } else {
          $cur .= $c;
        }
      }
    }
    $result[] = trim($cur);
    return $result;
  }

  $csv_text = @file_get_contents($CSV_URL);
  $rows = [];
  $headers = [];

  if ($csv_text) {
    $lines = explode("\n", $csv_text);
    $headers = parse_csv_line($lines[0]);
    for ($i = 1; $i < count($lines); $i++) {
      $line = trim($lines[$i]);
      if (!$line) continue;
      $values = parse_csv_line($line);
      $row = [];
      foreach ($headers as $idx => $h) {
        $row[$h] = isset($values[$idx]) ? $values[$idx] : '';
      }
      $rows[] = $row;
    }
  }
?>

<main>
  <h1>Juice=Juice 松永里愛のMIX=Juice 選曲まとめ</h1>
  TBSラジオ 宮本佳林 Music Palette♪のJuice=Juice 松永里愛のMIX=Juiceのコーナーで選曲された曲をまとめています<br>
  放送日をクリックすると<a href="https://www.tbsradio.jp/mp/" target="_blank" rel="noopener noreferrer">TBSラジオ Music Palette♪</a>の各回のページに飛びます<br>
  <a href="https://docs.google.com/spreadsheets/d/1efKvrTK4vcSUOVpVwWCjkOgyes-32RX0zEpz6iIMJDY/edit?gid=1463327244#gid=1463327244" target="_blank" rel="noopener noreferrer">ファイルを操作した場合はこちら（スプレッドシート）</a>
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
