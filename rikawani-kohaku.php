<?php
  $title = "島倉りか りかワニ紅白歌合戦 選曲まとめ";
  $description = "ワニブックス NewsCrunch（ニュースクランチ）の連載「りかワニ紅白歌合戦」で、BEYOOOOONDS島倉りかさんがテーマごとに紅組・白組に分けて選んだ昭和歌謡と勝敗のまとめです。サイト終了で読めなくなった記事はウェブアーカイブにリンクしています。";
  $jsonld_type = "Dataset";
  $about = [
    "島倉りか",
    ["name" => "BEYOOOOONDS", "type" => "MusicGroup"],
  ];
  $brand = 'hello';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';

  $WB = 'https://web.archive.org/web/';
  $NC = '/https://wanibooks-newscrunch.com/articles/-/';

  // win: 'red' / 'white' / null(対決なし)
  // links: [ラベル, 魚拓のタイムスタンプ, 記事ID]
  $rounds = [
    ['no' => '第1回', 'theme' => '海辺で一目惚れ 来生たかお対決',
     'red' => ['中森明菜', 'スローモーション'], 'white' => ['来生たかお', '浅い夢'], 'win' => 'red',
     'links' => [['記事', '20211201121944', 1338]]],
    ['no' => '第2回', 'theme' => '別れ',
     'red' => ['テレサ・テン', 'つぐない'], 'white' => ['野口五郎', '私鉄沿線'], 'win' => 'red',
     'links' => [['記事', '20211201120204', 1406]]],
    ['no' => '第3回', 'theme' => 'イントロ、歌詞が始まるまで長い曲',
     'red' => ['由紀さおり', '夜明けのスキャット'], 'white' => ['寺尾聰', 'SHADOW CITY'], 'win' => 'white',
     'links' => [['記事', '20211023134713', 1459]]],
    ['no' => '第4回', 'theme' => '彼女にもなれなかった、切なすぎる失恋',
     'red' => ['斉藤由貴', '白い炎'], 'white' => ['チェッカーズ', '神様ヘルプ！'], 'win' => 'red',
     'links' => [['記事', '20211026130515', 1499]]],
    ['no' => '第5回', 'theme' => '電車、旅で聴きたい！',
     'red' => ['松田聖子', 'ボン・ボヤージュ'], 'white' => ['井上陽水', '東へ西へ'], 'win' => 'white',
     'links' => [['記事', '20211026115730', 1553]]],
    ['no' => '第6回', 'theme' => '危険な関係……',
     'red' => ['おニャン子クラブ', 'およしになってねTEACHER'], 'white' => ['来生たかお', '官能少女'], 'win' => 'white',
     'links' => [['記事', '20211022112457', 1591]]],
    ['no' => '第7回', 'theme' => 'デート前に聴きたい曲',
     'red' => ['岡田有希子', 'リトルプリンセス'], 'white' => ['沢田研二', 'おまえにチェックイン'], 'win' => 'red',
     'links' => [['記事', '20211201115138', 1640]]],
    ['no' => '第8回', 'theme' => '聴いているだけで胸が苦しくなる曲',
     'red' => ['テレサ・テン', '別れの予感'], 'white' => ['大滝詠一', '恋するカレン'], 'win' => 'red',
     'links' => [['記事', '20211021041915', 1677]]],
    ['no' => '第9回', 'theme' => '昭和の３人組グループ',
     'red' => ['キャンディーズ', '哀愁のシンフォニー'], 'white' => ['少年隊', null], 'win' => 'red',
     'links' => [['記事', '20211019185657', 1728]],
     'note' => '白組の曲名が載っている記事の2ページ目はウェブアーカイブに残っておらず、曲名は不明です。少年隊であることは1ページ目の次ページ見出しと商品リンクから、紅組の勝ちは第29回の年間成績（紅10勝・白4勝）から判断しています。ご存じの方は教えてください。'],
    ['no' => '第10回', 'theme' => 'ディナーのときに聴きたい曲',
     'red' => ['荒井由実', 'チャイニーズスープ'], 'white' => ['佐野元春', 'こんな素敵な日には'], 'win' => 'red',
     'links' => [['記事', '20211019183556', 1768]]],
    ['no' => '第11・12回', 'theme' => '卒業ソング対決',
     'red' => ['斉藤由貴', '卒業'], 'white' => ['チェッカーズ', 'MY GRADUATION'], 'win' => 'red',
     'links' => [['前編', '20211019182214', 1811], ['後編', '20211019185242', 1845]]],
    ['no' => '第13・14回', 'theme' => '春、そして別れ対決',
     'red' => ['斉藤由貴', '悲しみよこんにちは'], 'white' => ['風', '22才の別れ'], 'win' => 'red',
     'links' => [['前編', '20211021015741', 1892], ['後編', '20210928173812', 1930]]],
    ['no' => '第15・16回', 'theme' => 'プロポーズソング対決',
     'red' => ['松田聖子', 'そよ風のフェイント'], 'white' => ['新沼謙治', '嫁に来ないか'], 'win' => 'red',
     'links' => [['前編', '20210928052319', 1988], ['後編', '20210603033207', 2046]]],
    ['no' => '第17・18回', 'theme' => '雨の日が楽しくなる曲対決',
     'red' => ['松田聖子', '雨のリゾート'], 'white' => ['オフコース', '眠れぬ夜'], 'win' => 'red',
     'links' => [['前編', '20211021012126', 2092], ['後編', '20220520105636', 2134]]],
    ['no' => '第19・20回', 'theme' => '６０年代対決',
     'red' => ['越路吹雪', 'ろくでなし'], 'white' => ['デューク・エイセス', 'いい湯だな'], 'win' => 'white',
     'links' => [['前編', '20220520115509', 2189], ['後編', '20220129141235', 2220]]],
    ['no' => '第21・22回', 'theme' => 'デュエット対決',
     'red' => ['石川優子とチャゲ', 'ふたりの愛ランド'], 'white' => ['和田弘とマヒナスターズ＆田村美代子', '愛して愛して愛しちゃったのよ'], 'win' => 'white',
     'links' => [['前編', '20220129123640', 2266], ['後編', '20220129134008', 2312]]],
    ['no' => '第23・24回', 'theme' => '“東京”対決',
     'red' => ['美空ひばり', '東京キッド'], 'white' => ['フランク永井', '有楽町で逢いましょう'], 'win' => 'white',
     'links' => [['前編', '20220517103248', 2444], ['後編', '20220522234248', 2495]]],
    ['no' => '第25・26回', 'theme' => '“電話”対決',
     'red' => ['中島みゆき', '悪女'], 'white' => ['稲垣潤一', '1ダースの言い訳'], 'win' => 'red',
     'links' => [['前編', '20220522113538', 2531], ['後編', '20220522110142', 2583]]],
    ['no' => '第27・28回', 'theme' => '“スイーツ”対決',
     'red' => ['竹内まりや', '不思議なピーチパイ'], 'white' => ['シャネルズ', '涙のスウィート・チェリー'], 'win' => 'red',
     'links' => [['前編', '20220125104930', 2647], ['後編', '20220125111905', 2680]]],
    ['no' => '第29回', 'theme' => '2021年・りかワニ紅白まとめ（特別編・対決なし）',
     'red' => null, 'white' => null, 'win' => null,
     'links' => [['記事', '20220126203621', 2732]]],
    ['no' => '第30・31回', 'theme' => '“女性目線・男性目線で見る同じ楽曲”対決',
     'red' => ['薬師丸ひろ子', 'メイン・テーマ'], 'white' => ['南佳孝', 'スタンダード・ナンバー'], 'win' => 'red',
     'links' => [['前編', '20220126211334', 2754], ['後編', '20220203032339', 2807]]],
    ['no' => '第32・33回', 'theme' => '“日本全国お疲れ様”対決',
     'red' => ['山本リンダ', 'どうにもとまらない'], 'white' => ['ハナ肇とクレージーキャッツ', '無責任一代男'], 'win' => 'white',
     'links' => [['前編', '20220213115856', 2848], ['後編', '20220228152139', 2846]]],
    ['no' => '第34・35回', 'theme' => '“三角関係”対決',
     'red' => ['研ナオコ', '愚図'], 'white' => ['浜田省吾', 'もうひとつの土曜日'], 'win' => 'red',
     'links' => [['前編', '20230330174312', 2922], ['後編', '20230531044850', 3040]]],
  ];

  $red_wins = count(array_filter($rounds, fn($r) => $r['win'] === 'red'));
  $white_wins = count(array_filter($rounds, fn($r) => $r['win'] === 'white'));

  $h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

  // 紅組・白組のセル。勝った側に印を付ける
  function rw_side($side, $won, $h) {
    if ($side === null) return '<td class="rw-none">—</td>';
    [$artist, $song] = $side;
    $song_html = $song === null ? '<span class="rw-unknown">曲名不明</span>' : '『' . $h($song) . '』';
    return '<td class="' . ($won ? 'rw-win' : '') . '">'
      . ($won ? '<span class="rw-mark">勝</span>' : '')
      . '<span class="rw-song">' . $song_html . '</span>'
      . '<span class="rw-artist">' . $h($artist) . '</span></td>';
  }
?>

<main>
  <h1>島倉りか りかワニ紅白歌合戦 選曲まとめ</h1>
  <p>ワニブックスのWebマガジン NewsCrunch（ニュースクランチ）で連載されていた「りかワニ紅白歌合戦」の選曲まとめです。<br>
  昭和歌謡好きのBEYOOOOONDS 島倉りかさんが毎回テーマを決めて紅組・白組から1曲ずつ選び、どちらが勝ちかを決めていました。<br>
  記事は現在読めなくなっているため、リンクは<a href="https://web.archive.org/" target="_blank" rel="noopener noreferrer">Wayback Machine</a>の魚拓に飛びます。<br>
  <a href="https://web.archive.org/web/20220307091302/https://wanibooks-newscrunch.com/category/series-031" target="_blank" rel="noopener noreferrer">連載の記事一覧ページ（魚拓）</a></p>

  <p class="rw-score">
    <span class="rw-score-red">紅組 <?= $red_wins ?>勝</span>
    <span class="rw-score-white">白組 <?= $white_wins ?>勝</span>
  </p>

  <table id="rw-table">
    <thead>
      <tr>
        <th>回</th>
        <th>テーマ</th>
        <th class="rw-th-red">紅組</th>
        <th class="rw-th-white">白組</th>
        <th>記事（魚拓）</th>
      </tr>
    </thead>
    <tbody>
<?php foreach ($rounds as $r): ?>
      <tr<?= isset($r['note']) ? ' class="rw-has-note"' : '' ?>>
        <td class="rw-no"><?= $h($r['no']) ?></td>
        <td class="rw-theme"><?= $h($r['theme']) ?></td>
        <?= rw_side($r['red'], $r['win'] === 'red', $h) ?>
        <?= rw_side($r['white'], $r['win'] === 'white', $h) ?>
        <td class="rw-links"><?php foreach ($r['links'] as [$label, $ts, $id]): ?><a href="<?= $WB . $ts . $NC . $id ?>" target="_blank" rel="noopener noreferrer"><?= $h($label) ?></a><?php endforeach; ?></td>
      </tr>
<?php if (isset($r['note'])): ?>
      <tr class="rw-note-row">
        <td colspan="5">※<?= $r['no'] ?>：<?= $h($r['note']) ?></td>
      </tr>
<?php endif; ?>
<?php endforeach; ?>
    </tbody>
  </table>
</main>
<div class="last-update">最終更新日：2026/09/15</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
