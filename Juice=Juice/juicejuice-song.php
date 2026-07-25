<?php
  $title = "Juice=Juice 楽曲まとめ";
  $description = "発売されているJuice=Juiceの曲をまとめました。作詞作曲編曲、MVがあるものはYouTubeのリンクを貼っています";
  $jsonld_type = "Dataset";
  $about = ["name" => "Juice=Juice", "type" => "MusicGroup"];
  // グループ単体
  // $about = ["name" => "モーニング娘。", "type" => "MusicGroup"];
  // 混在(人もグループも)
  // $about = [
  //   "宮本佳林",                                      // 文字列はPerson扱い
  //   ["name" => "Juice=Juice", "type" => "MusicGroup"],
  //   ["name" => "譜久村聖"],                          // typeなし → Person
  // ];
  $brand = 'juice';
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>Juice=Juice 楽曲まとめ</h1>
  発売されているJuice=Juiceの曲をまとめました。公式のリンク、作詞作曲編曲、Music Videoは▶️のマークを付けました
  <h2>インディーズシングル</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="karin-disco-head">タイトル</th>
      <th>作詞</th>
      <th>作曲</th>
      <th>編曲</th>
      <th>備考</th>
    </tr>
    <tr>
      <td>1st</td>
      <td>2013/4/3</td>
      <td><a href="https://www.youtube.com/watch?v=cchjirF7zM4" target="_blank" rel="noopener noreferrer">私が言う前に抱きしめなきゃね▶️</a>・<a href="https://www.youtube.com/watch?v=Sb6keJJznGI" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>2nd</td>
      <td>2013/5/8</td>
      <td><a href="https://www.youtube.com/watch?v=YHrpszUthUM" target="_blank" rel="noopener noreferrer">五月雨美女がさ乱れる▶️・<a href="https://www.youtube.com/watch?v=TBxjslahT-0" target="_blank" rel="noopener noreferrer">▶️（Close up Ver.）</a>・<a href="https://www.youtube.com/watch?v=buyWu8aPgss" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>板垣祐介</td>
      <td>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>3rd</td>
      <td>2013/6/12</td>
      <td><a href="https://www.youtube.com/watch?v=behGDIRH_8A" target="_blank" rel="noopener noreferrer">天まで登れ！▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td>ブラスアレンジ：鈴木俊介 大塚愛奈ラストシングル</td>
    </tr>
  </table>

  <h2>メジャーシングル</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="karin-disco-head">タイトル</th>
      <th class="karin-disco-head">曲名</th>
      <th>作詞</th>
      <th>作曲</th>
      <th>編曲</th>
    </tr>
    <tr>
      <td rowspan="3" class="karin-disco-title">1st</th>
      <td rowspan="3" class="karin-disco-title">2013/9/11</td>
      <td rowspan="3" class="karin-disco-title">私が言う前に抱きしめなきゃね（MEMORIAL EDIT）/五月雨美女がさ乱れる（MEMORIAL EDIT）</td>
      <td><a href="https://www.youtube.com/watch?v=rh8eeteWbHc" target="_blank" rel="noopener noreferrer">ロマンスの途中▶️</a>・<a href="https://www.youtube.com/watch?v=1lgANEF7cbE" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=VEK4N5qatIk" target="_blank" rel="noopener noreferrer">私が言う前に抱きしめなきゃね （MEMORIAL EDIT）▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=SCPzO2c-lMI" target="_blank" rel="noopener noreferrer">五月雨美女がさ乱れる （MEMORIAL EDIT）▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>板垣祐介</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">2nd</th>
      <td rowspan="2" class="karin-disco-title">2013/12/4</td>
      <td rowspan="2" class="karin-disco-title">イジワルしないで 抱きしめてよ/初めてを経験中</td>
      <td><a href="https://www.youtube.com/watch?v=wCNV_wAx82Y" target="_blank" rel="noopener noreferrer">イジワルしないで 抱きしめてよ▶️</a>・<a href="https://www.youtube.com/watch?v=LNPHQ0yrkW8" target="_blank" rel="noopener noreferrer">▶️（KYAST Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=NzxMVYTRg2Q" target="_blank" rel="noopener noreferrer">初めてを経験中▶️</a>・<a href="https://www.youtube.com/watch?v=GDE1sB-Uz4s" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>AKIRA</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">3rd</th>
      <td rowspan="2" class="karin-disco-title">2014/3/19</td>
      <td rowspan="2" class="karin-disco-title">裸の裸の裸のKISS/アレコレしたい!</td>
      <td><a href="https://www.youtube.com/watch?v=XusWMlJH178" target="_blank" rel="noopener noreferrer">裸の裸の裸のKISS▶️</a>・<a href="https://www.youtube.com/watch?v=AopkbcS_hlc" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=Fqm6smULwxs" target="_blank" rel="noopener noreferrer">アレコレしたい！▶️</a>・<a href="https://www.youtube.com/watch?v=x1qMU843aZk" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>近藤圭一</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">4th</th>
      <td rowspan="2" class="karin-disco-title">2014/7/30</td>
      <td rowspan="2" class="karin-disco-title">ブラックバタフライ/風に吹かれて</td>
      <td><a href="https://www.youtube.com/watch?v=ROeoeYQ33hM" target="_blank" rel="noopener noreferrer">ブラックバタフライ▶️</a>・<a href="https://www.youtube.com/watch?v=8iLP7hz-UxY" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=pem60iEtcGQ" target="_blank" rel="noopener noreferrer">風に吹かれて▶️</a>・<a href="https://www.youtube.com/watch?v=iDNexQ6aWCk" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">5th</th>
      <td rowspan="2" class="karin-disco-title">2014/10/1</td>
      <td rowspan="2" class="karin-disco-title">背伸び/伊達じゃないよ うちの人生は</td>
      <td><a href="https://www.youtube.com/watch?v=_so9w95wzVI" target="_blank" rel="noopener noreferrer">背伸び▶️・<a href="https://www.youtube.com/watch?v=6Pwx3tdZFN8" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=38c5UbdpfLw" target="_blank" rel="noopener noreferrer">伊達じゃないよ うちの人生は▶️</a>・<a href="https://www.youtube.com/watch?v=fS11KZTCmgg" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">6th</th>
      <td rowspan="2" class="karin-disco-title">2015/4/8</td>
      <td rowspan="2" class="karin-disco-title">Wonderful World/Ça va ? Ça va ?</td>
      <td><a href="https://www.youtube.com/watch?v=YlkOq6dXBEc" target="_blank" rel="noopener noreferrer">Wonderful World▶️</a>・<a href="https://www.youtube.com/watch?v=wTZ5cbaDGgI" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>gaokalab</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=XsnxU8QwJwI" target="_blank" rel="noopener noreferrer">Ça va？ Ça va？▶️</a>・<a href="https://www.youtube.com/watch?v=Aqdmz2qDSk8" target="_blank" rel="noopener noreferrer">▶️（Juice=Juice in PARIS Ver.）</a>・<a href="https://www.youtube.com/watch?v=xwSTKrvxfLU" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>三浦徳子</td>
      <td>川辺ヒロシ・上田禎</td>
      <td>CMJK</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">7th</th>
      <td rowspan="2" class="karin-disco-title">2016/2/3</td>
      <td rowspan="2" class="karin-disco-title">Next is you!/カラダだけが大人になったんじゃない</td>
      <td><a href="https://www.youtube.com/watch?v=7sJTyrHGK1k" target="_blank" rel="noopener noreferrer">Next is you!▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=kEkIMr3nmXk" target="_blank" rel="noopener noreferrer">カラダだけが大人になったんじゃない▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="3" class="karin-disco-title">8th</th>
      <td rowspan="3" class="karin-disco-title">2016/10/26</td>
      <td rowspan="3" class="karin-disco-title">Dream Road〜心が躍り出してる〜/KEEP ON 上昇志向!!/明日やろうはバカやろう</td>
      <td><a href="https://www.youtube.com/watch?v=EC19AV19rEg" target="_blank" rel="noopener noreferrer">Dream Road～心が躍り出してる～▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>江上浩太郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=JzDQj4X17gI" target="_blank" rel="noopener noreferrer">KEEP ON 上昇志向!!▶️</a></td>
      <td>前山田健一</td>
      <td>前山田健一</td>
      <td>ダンス☆マン</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=SgEl8y1hxHU" target="_blank" rel="noopener noreferrer">明日やろうはバカやろう▶️</a></td>
      <td>福田花音</td>
      <td>板垣祐介</td>
      <td>板垣祐介</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">9th</th>
      <td rowspan="2" class="karin-disco-title">2017/4/26</td>
      <td rowspan="2" class="karin-disco-title">地団駄ダンス/Feel!感じるよ</td>
      <td><a href="https://www.youtube.com/watch?v=HBFvJZR7yKQ" target="_blank" rel="noopener noreferrer">地団駄ダンス▶️</a></td>
      <td>児玉雨子</td>
      <td>BLACC HOLE &amp; NOBB-D</td>
      <td>BLACC HOLE &amp; NOBB-D with T</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=GScVBPWGYBc" target="_blank" rel="noopener noreferrer">Feel!感じるよ▶️</a></td>
      <td>三浦徳子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
    </tr>
    <tr>
      <td rowspan="3" class="karin-disco-title">10th</th>
      <td rowspan="3" class="karin-disco-title">2018/4/18</td>
      <td rowspan="3" class="karin-disco-title">SEXY SEXY/泣いていいよ/Vivid Midnight</td>
      <td><a href="https://www.youtube.com/watch?v=AgxdaRvQL5Q" target="_blank" rel="noopener noreferrer">SEXY SEXY▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=2I1hkkD9s6s" target="_blank" rel="noopener noreferrer">泣いていいよ▶️</a></td>
      <td>大森祥子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=tj8EFzQZuIk" target="_blank" rel="noopener noreferrer">Vivid Midnight▶️</a></td>
      <td>児玉雨子</td>
      <td>SSEION, Tasco, Tenzo</td>
      <td>Tasco, Tenzo</td>
    </tr>
    <tr>
      <td rowspan="3" class="karin-disco-title">11th</th>
      <td rowspan="3" class="karin-disco-title">2019/2/13</td>
      <td rowspan="3" class="karin-disco-title">微炭酸/ポツリと/Good bye & Good luck!</td>
      <td><a href="https://www.youtube.com/watch?v=NtBZEHyB_jo" target="_blank" rel="noopener noreferrer">微炭酸▶️</a></td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=QOMUp7ODrO8" target="_blank" rel="noopener noreferrer">ポツリと▶️</a></td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>浜田ピエール裕介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=pn3Djtkk66k" target="_blank" rel="noopener noreferrer">Good bye & Good luck!▶️</a></td>
      <td>三浦徳子</td>
      <td>KOUGA</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td rowspan="3" class="karin-disco-title">12th</th>
      <td rowspan="3" class="karin-disco-title">2019/6/5</td>
      <td rowspan="3" class="karin-disco-title">「ひとりで生きられそう」って それってねえ、褒めているの?/25歳永遠説</td>
      <td><a href="https://www.youtube.com/watch?v=yiYr2-6LtcU" target="_blank" rel="noopener noreferrer">「ひとりで生きられそう」って それってねえ、褒めているの？▶️</a>・<a href="https://www.youtube.com/watch?v=GOBwXucqSEo" target="_blank" rel="noopener noreferrer">▶️（リリック・ビデオ）</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=88fRO8entRc" target="_blank" rel="noopener noreferrer">25歳永遠説▶️</a></td>
      <td>児玉雨子</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td>ひとりで生きられそう」って それってねえ、褒めているの?」（New Vocal Ver.）</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td rowspan="4" class="karin-disco-title">13th</th>
      <td rowspan="4" class="karin-disco-title">2020/4/1</td>
      <td rowspan="4" class="karin-disco-title">ポップミュージック/好きって言ってよ</td>
      <td><a href="https://www.youtube.com/watch?v=IrggLg_hGE0" target="_blank" rel="noopener noreferrer">ポップミュージック▶️</a></td>
      <td>KAN</td>
      <td>KAN</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=gYAFijpPSws" target="_blank" rel="noopener noreferrer">好きって言ってよ▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>続いていくSTORY（Symphonic Version feat. Karin）</td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>上杉洋史</td>
    </tr>
    <tr>
      <td>Va-Va-Voom</td>
      <td>児玉雨子</td>
      <td>Shusui/Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">14th</th>
      <td rowspan="2" class="karin-disco-title">2021/4/28</td>
      <td rowspan="2" class="karin-disco-title">DOWN TOWN/がんばれないよ</td>
      <td><a href="https://www.youtube.com/watch?v=7xD8ORNRtDI" target="_blank" rel="noopener noreferrer">DOWN TOWN▶️</a></td>
      <td>伊藤銀次</td>
      <td>山下達郎</td>
      <td>Anders Dannvik</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=lfg5k3LyEKo" target="_blank" rel="noopener noreferrer">がんばれないよ▶️</a>・<a href="https://www.youtube.com/watch?v=bFHWI6-4jqw" target="_blank" rel="noopener noreferrer">▶️（Juice=Juice Solo mix Ver.）</a></td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td rowspan="3" class="karin-disco-title">15th</th>
      <td rowspan="3" class="karin-disco-title">2021/12/22</td>
      <td rowspan="3" class="karin-disco-title">プラスティック・ラブ/Familia/Future Smile</td>
      <td><a href="https://www.youtube.com/watch?v=f6Nuvg3RuEw" target="_blank" rel="noopener noreferrer">プラスティック・ラブ▶️</a></td>
      <td>竹内まりや</td>
      <td>竹内まりや</td>
      <td>Anders Dannvik</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=6iu4E-ql5Dg" target="_blank" rel="noopener noreferrer">Familia▶️</a></td>
      <td>イイジマケン</td>
      <td>Shusui / Shim Zeyun / tsubomi</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=IvyAieDrM1c" target="_blank" rel="noopener noreferrer">Future Smile▶️</a></td>
      <td>大森祥子</td>
      <td>Shusui / Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">16th</th>
      <td rowspan="2" class="karin-disco-title">2022/11/23</td>
      <td rowspan="2" class="karin-disco-title">全部賭けてGO!!/イニミニマニモ〜恋のライバル宣言〜</td>
      <td><a href="https://www.youtube.com/watch?v=4sO4_dLLeGM" target="_blank" rel="noopener noreferrer">全部賭けてGO!!▶️</a></td>
      <td>大森祥子</td>
      <td>徳田光希</td>
      <td>徳田光希</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=xgoVNqwh1wY" target="_blank" rel="noopener noreferrer">イニミニマニモ～恋のライバル宣言～▶️</a></td>
      <td>山崎あおい</td>
      <td>Johan Alkenas/Joacim Persson/Lisa Desmond</td>
      <td>Joacim Persson/Johan Alkenas</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">17th</th>
      <td rowspan="2" class="karin-disco-title">2023/7/12</td>
      <td rowspan="2" class="karin-disco-title">プライド・ブライト/FUNKY FLUSHIN'</td>
      <td><a href="https://www.youtube.com/watch?v=wrucm-6MqgE" target="_blank" rel="noopener noreferrer">プライド・ブライト▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=Uks7Dyn8DAM" target="_blank" rel="noopener noreferrer">FUNKY FLUSHIN'▶️</a></td>
      <td>吉田美奈子</td>
      <td>山下達郎</td>
      <td>Aksel Odenbalk</td>
    </tr>
    <tr>
      <td rowspan="4" class="karin-disco-title">18th</th>
      <td rowspan="4" class="karin-disco-title">2024/5/15</td>
      <td rowspan="4" class="karin-disco-title">トウキョウ・ブラー/ナイモノラブ/おあいこ</td>
      <td><a href="https://www.youtube.com/watch?v=DPImRhSUtR0" target="_blank" rel="noopener noreferrer">トウキョウ・ブラー▶️</a></td>
      <td>山崎あおい</td>
      <td>Erik Lidbom</td>
      <td>Erik Lidbom</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=wMy_mIgy6hM" target="_blank" rel="noopener noreferrer">ナイモノラブ▶️</a></td>
      <td>SUIMI</td>
      <td>渡辺泰司</td>
      <td>渡辺泰司</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=B8Zsip26P3g" target="_blank" rel="noopener noreferrer">おあいこ▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=CTqHcX-dg-I" target="_blank" rel="noopener noreferrer">Brilliance of memories▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>浜田ピエール裕介</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">19th</th>
      <td rowspan="2" class="karin-disco-title">2025/2/26</td>
      <td rowspan="2" class="karin-disco-title">初恋の亡霊/今夜はHearty Party</td>
      <td><a href="https://www.youtube.com/watch?v=DquBd86wOYw" target="_blank" rel="noopener noreferrer">初恋の亡霊▶️</a>・<a href="https://www.youtube.com/watch?v=g9EXEuk5sxo" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>西野蒟蒻</td>
      <td>KOUGA</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=DquBd86wOYw" target="_blank" rel="noopener noreferrer">今夜はHearty Party▶️</a></td>
      <td>竹内まりや</td>
      <td>竹内まりや</td>
      <td>Aksel Odenbalk/Albin Hillborg</td>
    </tr>
    <tr>
      <td rowspan="2" class="karin-disco-title">20th</th>
      <td rowspan="2" class="karin-disco-title">2025/10/8</td>
      <td rowspan="2" class="karin-disco-title">四の五の言わず颯（さっ）と別れてあげた/盛れ！ミ・アモーレ</td>
      <td><a href="https://www.youtube.com/watch?v=cnlS-vuMg40" target="_blank" rel="noopener noreferrer">四の五の言わず颯（さっ）と別れてあげた▶️</a></td>
      <td>大森祥子</td>
      <td>KOUGA</td>
      <td>荒幡亮平</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=3RtbnP1onaM" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ▶️</a>・<a href="https://www.youtube.com/watch?v=MlG9hptTvgQ" target="_blank" rel="noopener noreferrer">▶️（Dance Shot Ver.）</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
    </tr>
  </table>

  <h2>配信シングル</h2>
  <table id="karin-disco">
    <tr>
      <th>発売日</th>
      <th class="karin-disco-head">タイトル</th>
      <th>作詞</th>
      <th>作曲</th>
      <th>編曲</th>
      <th>備考</th>
    </tr>
    <tr>
      <td rowspan="2">2017/8/23</td>
      <td><a href="https://www.youtube.com/watch?v=0SMjNmSwFic" target="_blank" rel="noopener noreferrer">Fiesta! Fiesta!▶️</a></td>
      <td>井筒日美</td>
      <td>エリック・フクサキ</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>Wonderful World（English Ver.）</td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>gaokalab</td>
      <td>英語詞：鈴木桃子</td>
    </tr>
  </table>

  <h2>配信限定</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="karin-disco-head">タイトル</th>
      <th>作詞</th>
      <th>作曲</th>
      <th>編曲</th>
      <th>備考</th>
    </tr>
    <tr>
      <td>1st</td>
      <td>2016/3/2</td>
      <td>大人の事情</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td>NEXT YOU名義</td>
    </tr>
    <tr>
      <td>2nd</td>
      <td>2017/5/19</td>
      <td>Goal〜明日はあっちだよ〜</td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>近藤薫&沢頭たかし</td>
      <td></td>
    </tr>
    <tr>
      <td>3rd</td>
      <td>2017/6/16</td>
      <td>如雨露</td>
      <td>児玉雨子</td>
      <td>YUKO</td>
      <td>大久保薫</td>
      <td>梁川奈々美・段原瑠々加入</td>
    </tr>
    <tr>
      <td>-</td>
      <td>2025/12/23</td>
      <td><a href="https://www.youtube.com/watch?v=KGePThef8rc" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ（BAND Live Ver.）▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>-</td>
      <td>2025/12/23</td>
      <td><a href="https://www.youtube.com/watch?v=G36amiUCkXU" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ（Concert 2025 Queen of Hearts Special Flush）▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>-</td>
      <td>2025/12/23</td>
      <td><a href="https://www.youtube.com/watch?v=swn7_j3qusY" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ - From THE FIRST TAKE▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>-</td>
      <td>2026/2/13</td>
      <td>「ひとりで生きられそう」って それってねえ、褒めているの?- From THE FIRST TAKE</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
  </table>

  <h2>オリジナルアルバム</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="karin-disco-head">タイトル</th>
      <th class=" karin-disco-head">Disk</th>
      <th>Track</th>
      <th class="karin-disco-head">曲名</th>
      <th>作詞</th>
      <th>作曲</th>
      <th>編曲</th>
      <th>備考</th>
    </tr>
    <tr>
      <td rowspan="29" class="karin-disco-title">1st</th>
      <td rowspan="29" class="karin-disco-title">2015/7/15</td>
      <td rowspan="29" class="karin-disco-title">First Squeeze!</td>
      <td rowspan="12" class="karin-disco-title">Disc.1 The Best Juice</td>
      <td>1</td>
      <td>天まで登れ!</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>2</td>
      <td>ロマンスの途中</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>私が言う前に抱きしめなきゃね（MEMORIAL EDIT）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>五月雨美女がさ乱れる（MEMORIAL EDIT）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>板垣祐介</td>
      <td>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>5</td>
      <td>イジワルしないで 抱きしめてよ</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td>初めてを経験中</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>編曲: AKIRA</td>
      <td>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>7</td>
      <td>裸の裸の裸のKISS</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>アレコレしたい!?</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>編曲: 近藤圭一</td>
      <td></td>
    </tr>
    <tr>
      <td>9</td>
      <td>ブラックバタフライ</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>10</td>
      <td>風に吹かれて</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>11</td>
      <td>背伸び</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>12</td>
      <td>伊達じゃないよ うちの人生は</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td rowspan="11" class="karin-disco-title">Disc.2 The Brand-New Juice</td>
      <td>1</td>
      <td>Wonderful World</td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>gaokalab</td>
      <td></td>
    </tr>
    <tr>
      <td>2</td>
      <td><a href="https://www.youtube.com/watch?v=XBQ0v95I9RE" target="_blank" rel="noopener noreferrer">CHOICE & CHANCE▶️</a></td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>愛・愛・傘</td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>生まれたてのBaby Lov</td>
      <td>星部ショウ</td>
      <td>masaaki asada</td>
      <td>松井寛</td>
      <td></td>
    </tr>
    <tr>
      <td>5</td>
      <td>選ばれし私達</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>山崎淳</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td>Ca va ? Ca va ?（サヴァ サヴァ</td>
      <td>三浦徳子</td>
      <td>川辺ヒロシ・上田禎</td>
      <td>CMJK</td>
      <td></td>
    </tr>
    <tr>
      <td>7</td>
      <td>GIRLS BE AMBITIOUS</td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>中島卓偉・宮永治郎</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>愛のダイビング</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>土肥真生</td>
      <td></td>
    </tr>
    <tr>
      <td>9</td>
      <td>チクタク 私の旬</td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>CMJK</td>
      <td></td>
    </tr>
    <tr>
      <td>10</td>
      <td>未来へ、さあ走り出せ!</td>
      <td>角田崇徳</td>
      <td>KOJI oba</td>
      <td>KOJI oba</td>
      <td></td>
    </tr>
    <tr>
      <td>11</td>
      <td><a href="https://www.youtube.com/watch?v=E2AKITM9uA0" target="_blank" rel="noopener noreferrer">続いていくSTORY▶️</a></td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>近藤薫・HASSE</td>
      <td></td>
    </tr>
    <tr>
      <td rowspan="6" class="karin-disco-title">The Cover Juice</td>
      <td>1</td>
      <td>Magic of Love（J=J 2015Ver.）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>村山晋一郎</td>
      <td>太陽とシスコムーンのカバー</td>
    </tr>
    <tr>
      <td>2</td>
      <td>香水（J=J 2015Ver.）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td>メロン記念日のカバー</td>
    </tr>
    <tr>
      <td>3</td>
      <td>鳴り始めた恋のBell</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>松井寛</td>
      <td>音楽ガッタスのカバー</td>
    </tr>
    <tr>
      <td>4</td>
      <td>スクランブル</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木Daichi秀行</td>
      <td>後藤真希のカバー</td>
    </tr>
    <tr>
      <td>5</td>
      <td>BABY! 恋にKNOCK OUT!</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>小西貴雄</td>
      <td>プッチモニのカバー 歌：宮崎由加＆金澤朋子＆植村あかり</td>
    </tr>
    <tr>
      <td>6</td>
      <td>ラストキッス</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>小西貴雄</td>
      <td>タンポポのカバー 歌：高木紗友希＆宮本佳林</td>
    </tr>
    <tr>
      <td rowspan="22" class="karin-disco-title">2nd</th>
      <td rowspan="22" class="karin-disco-title">2018/8/1</td>
      <td rowspan="22" class="karin-disco-title">Juice=Juice#2 -!Una mas!-</td>
      <td rowspan="11" class="karin-disco-title">Disc.1</td>
      <td>1</td>
      <td>Fiesta! Fiesta!</td>
      <td>井筒日美</td>
      <td>エリック・フクサキ</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>2</td>
      <td>SEXY SEXY</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>泣いていいよ</td>
      <td>大森祥子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>Vivid Midnight</td>
      <td>児玉雨子</td>
      <td>SEION, Tasco, Tenzo</td>
      <td>Tasco, Tenzo</td>
      <td></td>
    </tr>
    <tr>
      <td>5</td>
      <td>地団駄ダンス</td>
      <td>児玉雨子</td>
      <td>BLACC HOLE&NOBB-D</td>
      <td>BLACC HOLE&NOBB-D with T</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td>Feel!感じるよ</td>
      <td>三浦徳子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
      <td></td>
    </tr>
    <tr>
      <td>7</td>
      <td>Dream Road～心が躍り出してる～</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>江上浩太郎</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>KEEP ON 上昇志向!! </td>
      <td>前山田健一</td>
      <td>ダンス☆マン</td>
      <td>ダンス☆マン</td>
      <td>ブラスアレンジ: 川松久芳</td>
    </tr>
    <tr>
      <td>9</td>
      <td>明日やろうはバカやろう</td>
      <td>福田花音</td>
      <td>板垣祐介</td>
      <td>板垣祐介</td>
      <td></td>
    </tr>
    <tr>
      <td>10</td>
      <td>Next is you!</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
      <td>NEXT YOU名義</td>
    </tr>
    <tr>
      <td>11</td>
      <td>カラダだけが大人になったんじゃない</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td rowspan="11" class="karin-disco-title">Disc.2</td>
      <td>1</td>
      <td>Never Never Surrender</td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>2</td>
      <td>如雨露（Album Version）</td>
      <td>児玉雨子</td>
      <td>YUKO</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>TOKYOグライダー</td>
      <td>唐沢美帆</td>
      <td>星部ショウ</td>
      <td>松井寛</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>シンクロ。</td>
      <td>井筒日美</td>
      <td>星部ショウ</td>
      <td>浜田ピエール裕介</td>
      <td></td>
    </tr>
    <tr>
      <td>5</td>
      <td>あばれてっか?! ハヴアグッタイ</td>
      <td>児玉雨子</td>
      <td>ダンス☆マン</td>
      <td>ダンス☆マン</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td>素直に甘えて</td>
      <td>NOBE</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td></td>
    </tr>
    <tr>
      <td>7</td>
      <td>Goal～明日はあっちだよ～（Album Version）</td>
      <td>近藤薫</td>
      <td>近藤薫&amp;沢頭たかし</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>銀色のテレパシー</td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>高橋諭一</td>
      <td></td>
    </tr>
    <tr>
      <td>9</td>
      <td>この世界は捨てたもんじゃない</td>
      <td>井筒日美</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
      <td></td>
    </tr>
    <tr>
      <td>10</td>
      <td>禁断少女</td>
      <td>大橋莉子</td>
      <td>大橋莉子</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>11</td>
      <td>大人の事情</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td rowspan="24" class="karin-disco-title">3rd</th>
      <td rowspan="24" class="karin-disco-title">2022/4/20</td>
      <td rowspan="24" class="karin-disco-title">terzo</td>
      <td rowspan="16" class="karin-disco-title">Disc.1 The Best Juice 2019-2022</td>
      <td>1</td>
      <td>微炭酸</td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
      <td></td>
    </tr>
    <tr>
      <td>2</td>
      <td>ポツリと</td>
      <td>中島卓偉</td>
      <td>浜田ピエール裕介</td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>Good bye & Good luck!</td>
      <td>三浦徳子</td>
      <td>KOUGA</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>「ひとりで生きられそう」って それってねえ、褒めているの?</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>5</td>
      <td>25歳永遠説</td>
      <td>児玉雨子</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td><a href="https://www.youtube.com/watch?v=ykotlrfr6oI" target="_blank" rel="noopener noreferrer">「ひとりで生きられそう」って それってねえ、褒めているの？ （New Vocal Ver.）▶️</a></td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>7</td>
      <td>ポップミュージック</td>
      <td>KAN</td>
      <td>KAN</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>好きって言ってよ</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>9</td>
      <td>Borderline</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>10</td>
      <td>Va-Va-Voom</td>
      <td>児玉雨子</td>
      <td>Shusui/Josef Melin</td>
      <td>Josef Melin</td>
      <td></td>
    </tr>
    <tr>
      <td>11</td>
      <td>続いていくSTORY （Symphonic Version feat. Karin）</td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>上杉洋史</td>
      <td></td>
    </tr>
    <tr>
      <td>12</td>
      <td>DOWN TOWN</td>
      <td>伊藤銀次</td>
      <td>山下達郎</td>
      <td>Anders Dannvik</td>
      <td></td>
    </tr>
    <tr>
      <td>13</td>
      <td>がんばれないよ</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td></td>
    </tr>
    <tr>
      <td>14</td>
      <td>プラスティック・ラブ</td>
      <td>竹内まりや</td>
      <td>竹内まりや</td>
      <td>Anders Dannvik</td>
      <td></td>
    </tr>
    <tr>
      <td>15</td>
      <td>Familia</td>
      <td>イイジマケン</td>
      <td>Shusui / Shim Zeyun / tsubomi</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>16</td>
      <td>Future Smile</td>
      <td>大森祥子</td>
      <td>Shusui / Josef Melin</td>
      <td>Josef Melin</td>
      <td></td>
    </tr>
    <tr>
      <td rowspan="8" class="karin-disco-title">Disc.2 The Brand-New Juice 2022</td>
      <td>1</td>
      <td>GIRLS BE AMBITIOUS! 2022</td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>中島卓偉 / 宮永治郎</td>
      <td></td>
    </tr>
    <tr>
      <td>2</td>
      <td>POPPIN' LOVE</td>
      <td>山崎あおい</td>
      <td>Erik Lidbom / Julie Yu / Andreas Carlsson</td>
      <td>Erik Lidbom</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>STAGE～アガってみな～</td>
      <td>徳田光希</td>
      <td>徳田光希</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>Mon Amour</td>
      <td>オオヤギヒロオ / 井筒日美</td>
      <td>オオヤギヒロオ</td>
      <td>浜田ピエール裕介</td>
      <td></td>
    </tr>
    <tr>
      <td>5</td>
      <td>ノクチルカ</td>
      <td>唐沢美帆</td>
      <td>松井寛</td>
      <td>松井寛</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td>G.O.A.T</td>
      <td>井筒日美</td>
      <td>Shusui / Andreas Ohrm / Henrik Smith</td>
      <td>Henrik Smith</td>
      <td></td>
    </tr>
    <tr>
      <td>7</td>
      <td>雨の中の口笛</td>
      <td>Shusui / Zeyun / tsubomi</td>
      <td>Shusui / Zeyun / tsubomi</td>
      <td>Zeyun</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>プラトニック・プラネット（Ultimate Juice Ver.） </td>
      <td>児玉雨子</td>
      <td>炭竃智弘</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
  </table>
    <h2>ベストアルバム</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="karin-disco-head">タイトル</th>
      <th>Track</th>
      <th class="karin-disco-head">曲名</th>
      <th>作詞</th>
      <th>作曲</th>
      <th>編曲</th>
      <th>備考</th>
    </tr>
    <tr>
      <td rowspan="14" class="karin-disco-title">1st</td>
      <td rowspan="14" class="karin-disco-title">2023/10/11</td>
      <td rowspan="14" class="karin-disco-title">Juicetory</td>
      <td>1</td>
      <td>ロマンスの途中（2023 10th Juice Ver.）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>2</td>
      <td>私が言う前に抱きしめなきゃね（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>イジワルしないで 抱きしめてよ（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>4</td>
      <td>初めてを経験中（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>AKIRA</td>
      <td>ブラスアレンジ: 鈴木俊介</td>
    </tr>
    <tr>
      <td>5</td>
      <td>ブラックバタフライ（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>6</td>
      <td>Wonderful World（2023 10th Juice Ver.）</td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>7</td>
      <td>CHOICE&CHANCE（2023）</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>8</td>
      <td>Magic of Love（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>村山晋一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>9</td>
      <td>カラダだけが大人になったんじゃない（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
      <td></td>
    </tr>
    <tr>
      <td>10</td>
      <td>Never Never Surrender</td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>大久保薫</td>
      <td></td>
    </tr>
    <tr>
      <td>11</td>
      <td>微炭酸</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td></td>
    </tr>
    <tr>
      <td>12</td>
      <td>「ひとりで生きられそう」って それってねえ、褒めているの?（2023）</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td></td>
    </tr>
    <tr>
      <td>13</td>
      <td>ポップミュージック（2023）</td>
      <td>KAN</td>
      <td>KAN</td>
      <td>炭竃智弘</td>
      <td></td>
    </tr>
    <tr>
      <td>14</td>
      <td>ボン・ヴォヤージュ～想いの軌跡～
      <td>井筒日美</td>
      <td>Shusui/Josef Melin</td>
      <td>Josef Melin</td>
      <td></td>
    </tr>
  </table>
  
  <h2>CD未収録楽曲</h2>
  <table id="karin-disco">
	<tr>
		<th>曲名</th>
		<th>作詞</th>
		<th>作曲</th>
		<th>編曲</th>
	</tr>
	<tr>
		<td>BLOODY BULLET</td>
		<td>西野蒟蒻</td>
		<td>星部ショウ</td>
		<td>平田祥一郎</td>
	</tr>
	<tr>
		<td>甘えんな</td>
		<td>大森祥子</td>
		<td>炭竃智弘</td>
		<td>炭竃智弘</td>
	</tr>
</table>
<div class="last-update">最終更新日：2026/05/17</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>