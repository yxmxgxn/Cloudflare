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
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>Juice=Juice 楽曲まとめ</h1>
  発売されているJuice=Juiceの曲をまとめました。公式のリンク、作詞作曲編曲、Music Videoに▶️、一番新しいライブ映像に🎥・Dance Shot/Practiceに💃を付けました
  <h2>インディーズシングル</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td>1st</td>
      <td>2013/4/3</td>
      <td><a href="https://helloproject.com/release/2210/" target="_blank" rel="noopener noreferrer">私が言う前に抱きしめなきゃね</a><a href="https://www.youtube.com/watch?v=cchjirF7zM4" target="_blank" rel="noopener noreferrer">▶️</a>・<a href="https://www.youtube.com/watch?v=RGDQLmLIsCY" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=Sb6keJJznGI" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>2nd</td>
      <td>2013/5/8</td>
      <td><a href="https://helloproject.com/release/2211/" target="_blank" rel="noopener noreferrer">五月雨美女がさ乱れる</a><a href="https://www.youtube.com/watch?v=YHrpszUthUM" target="_blank" rel="noopener noreferrer">▶️</a>・<a href="https://www.youtube.com/watch?v=F-i07le2wo0" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=TBxjslahT-0" target="_blank" rel="noopener noreferrer">▶️（Close up Ver.）</a>・<a href="https://www.youtube.com/watch?v=buyWu8aPgss" target="_blank" rel="noopener noreferrer">💃</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>板垣祐介<br>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>3rd</td>
      <td>2013/6/12</td>
      <td><a href="https://helloproject.com/release/2212/" target="_blank" rel="noopener noreferrer">天まで登れ！</a><a href="https://www.youtube.com/watch?v=behGDIRH_8A" target="_blank" rel="noopener noreferrer">▶️</a>・<a href="https://www.youtube.com/watch?v=F-i07le2wo0" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎<br>ブラスアレンジ：鈴木俊介</td>
    </tr>
  </table>

  <h2>メジャーシングル</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th class="dc-wide">曲名</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td rowspan="3">1st</td>
      <td rowspan="3">2013/9/11</td>
      <td rowspan="3"><a href="https://helloproject.com/release/1656/" target="_blank" rel="noopener noreferrer">私が言う前に抱きしめなきゃね（MEMORIAL EDIT）/五月雨美女がさ乱れる（MEMORIAL EDIT）</a></td>
      <td><a href="https://www.youtube.com/watch?v=rh8eeteWbHc" target="_blank" rel="noopener noreferrer">ロマンスの途中▶️</a>・<a href="https://www.youtube.com/watch?v=1lgANEF7cbE" target="_blank" rel="noopener noreferrer">💃</a></td>
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
      <td rowspan="2" class="dc-group">2nd</td>
      <td rowspan="2" class="dc-group">2013/12/4</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/1667/" target="_blank" rel="noopener noreferrer">イジワルしないで 抱きしめてよ/初めてを経験中</a></td>
      <td><a href="https://www.youtube.com/watch?v=wCNV_wAx82Y" target="_blank" rel="noopener noreferrer">イジワルしないで 抱きしめてよ▶️</a>・<a href="https://www.youtube.com/watch?v=0ySylUquWs4" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=LNPHQ0yrkW8" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=NzxMVYTRg2Q" target="_blank" rel="noopener noreferrer">初めてを経験中▶️</a>・<a href="https://www.youtube.com/watch?v=pXft-OIBw9I" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=GDE1sB-Uz4s" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>AKIRA</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">3rd</td>
      <td rowspan="2" class="dc-group">2014/3/19</td>
      <td rowspan="2" class="dc-group">裸の裸の裸のKISS/アレコレしたい!</td>
      <td><a href="https://www.youtube.com/watch?v=XusWMlJH178" target="_blank" rel="noopener noreferrer">裸の裸の裸のKISS▶️</a>・<a href="https://www.youtube.com/watch?v=Egmjjt3j3us&t=189s" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=AopkbcS_hlc" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=Fqm6smULwxs" target="_blank" rel="noopener noreferrer">アレコレしたい！▶️</a>・<a href="https://www.youtube.com/watch?v=x1qMU843aZk" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>近藤圭一</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">4th</td>
      <td rowspan="2" class="dc-group">2014/7/30</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/2277/" target="_blank" rel="noopener noreferrer">ブラックバタフライ/風に吹かれて</a></td>
      <td><a href="https://www.youtube.com/watch?v=ROeoeYQ33hM" target="_blank" rel="noopener noreferrer">ブラックバタフライ▶️</a>・<a href="https://www.youtube.com/watch?v=8iLP7hz-UxY" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=pem60iEtcGQ" target="_blank" rel="noopener noreferrer">風に吹かれて▶️</a>・<a href="https://www.youtube.com/watch?v=Vln2vJ57LVk&t=278s" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=iDNexQ6aWCk" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">5th</td>
      <td rowspan="2" class="dc-group">2014/10/1</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/2371/" target="_blank" rel="noopener noreferrer">背伸び/伊達じゃないよ うちの人生は</a></td>  
      <td><a href="https://www.youtube.com/watch?v=_so9w95wzVI" target="_blank" rel="noopener noreferrer">背伸び▶️</a>・<a href="https://www.youtube.com/watch?v=QX1FgV2cJdU&t=1168s" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=6Pwx3tdZFN8" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=38c5UbdpfLw" target="_blank" rel="noopener noreferrer">伊達じゃないよ うちの人生は▶️</a>・<a href="https://www.youtube.com/watch?v=IWph3hNX_m0&t=842s" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=fS11KZTCmgg" target="_blank" rel="noopener noreferrer">💃</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">6th</td>
      <td rowspan="2" class="dc-group">2015/4/8</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/4094/" target="_blank" rel="noopener noreferrer">Wonderful World/Ça va ? Ça va ?</a></td>
      <td><a href="https://www.youtube.com/watch?v=YlkOq6dXBEc" target="_blank" rel="noopener noreferrer">Wonderful World▶️</a>・<a href="https://www.youtube.com/watch?v=cyFB7sB6CYs" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=wTZ5cbaDGgI" target="_blank" rel="noopener noreferrer">💃</td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>gaokalab</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=XsnxU8QwJwI" target="_blank" rel="noopener noreferrer">Ça va？ Ça va？▶️</a>・<a href="https://www.youtube.com/watch?v=9APF0fLxCSQ&t=247s" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=Aqdmz2qDSk8" target="_blank" rel="noopener noreferrer">▶️（Juice=Juice in PARIS Ver.）</a>・<a href="https://www.youtube.com/watch?v=xwSTKrvxfLU" target="_blank" rel="noopener noreferrer">💃</td>
      <td>三浦徳子</td>
      <td>川辺ヒロシ・上田禎</td>
      <td>CMJK</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">7th</td>
      <td rowspan="2" class="dc-group">2016/2/3</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/4548/" target="_blank" rel="noopener noreferrer">Next is you!/カラダだけが大人になったんじゃない</a></td>
      <td><a href="https://www.youtube.com/watch?v=7sJTyrHGK1k" target="_blank" rel="noopener noreferrer">Next is you!▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=kEkIMr3nmXk" target="_blank" rel="noopener noreferrer">カラダだけが大人になったんじゃない▶️</a>・<a href="https://www.youtube.com/watch?v=DLd9NzNd9zw&t=1468s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="3" class="dc-group">8th</td>
      <td rowspan="3" class="dc-group">2016/10/26</td>
      <td rowspan="3" class="dc-group"><a href="https://helloproject.com/release/4548/" target="_blank" rel="noopener noreferrer">Dream Road〜心が躍り出してる〜/KEEP ON 上昇志向!!/明日やろうはバカやろう</a></td>
      <td><a href="https://www.youtube.com/watch?v=EC19AV19rEg" target="_blank" rel="noopener noreferrer">Dream Road～心が躍り出してる～▶️</a>・<a href="https://www.youtube.com/watch?v=FgecS6FityI&t=42s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>江上浩太郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=JzDQj4X17gI" target="_blank" rel="noopener noreferrer">KEEP ON 上昇志向!!▶️</a>・<a href="https://www.youtube.com/watch?v=kYnQxjC3dtk&t=1594s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>前山田健一</td>
      <td>前山田健一</td>
      <td>ダンス☆マン</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=SgEl8y1hxHU" target="_blank" rel="noopener noreferrer">明日やろうはバカやろう▶️</a>・<a href="https://www.youtube.com/watch?v=C3_6b8Wlwsk" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>福田花音</td>
      <td>板垣祐介</td>
      <td>板垣祐介</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">9th</td>
      <td rowspan="2" class="dc-group">2017/4/26</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/5152/" target="_blank" rel="noopener noreferrer">地団駄ダンス/Feel!感じるよ</a></td>
      <td><a href="https://www.youtube.com/watch?v=HBFvJZR7yKQ" target="_blank" rel="noopener noreferrer">地団駄ダンス▶️</a>・<a href="https://www.youtube.com/watch?v=Jt50XbJbDq8&t=2079s" target="_blank" rel="noopener noreferrer">🎥</a></td>
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
      <td rowspan="3" class="dc-group">10th</td>
      <td rowspan="3" class="dc-group">2018/4/18</td>
      <td rowspan="3" class="dc-group"><a href="https://helloproject.com/release/5586/" target="_blank" rel="noopener noreferrer">SEXY SEXY/泣いていいよ/Vivid Midnight</a></td>
      <td><a href="https://www.youtube.com/watch?v=AgxdaRvQL5Q" target="_blank" rel="noopener noreferrer">SEXY SEXY▶️</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=2I1hkkD9s6s" target="_blank" rel="noopener noreferrer">泣いていいよ▶️</a>・<a href="https://www.youtube.com/watch?v=5VofGX2mB2w&t=220s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>大森祥子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=tj8EFzQZuIk" target="_blank" rel="noopener noreferrer">Vivid Midnight▶️</a>・<a href="https://www.youtube.com/watch?v=QZeB1vyGomE&t=1214s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>児玉雨子</td>
      <td>SSEION, Tasco, Tenzo</td>
      <td>Tasco, Tenzo</td>
    </tr>
    <tr>
      <td rowspan="3" class="dc-group">11th</td>
      <td rowspan="3" class="dc-group">2019/2/13</td>
      <td rowspan="3" class="dc-group"><a href="https://helloproject.com/release/5872/" target="_blank" rel="noopener noreferrer">微炭酸/ポツリと/Good bye & Good luck!</a></td>
      <td><a href="https://www.youtube.com/watch?v=NtBZEHyB_jo" target="_blank" rel="noopener noreferrer">微炭酸▶️</a>・<a href="https://www.youtube.com/watch?v=v4JVTZ3kK-Y" target="_blank" rel="noopener noreferrer">🎥</a></td>
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
      <td rowspan="3" class="dc-group">12th</td>
      <td rowspan="3" class="dc-group">2019/6/5</td>
      <td rowspan="3" class="dc-group"><a href="https://helloproject.com/release/6156/" target="_blank" rel="noopener noreferrer">「ひとりで生きられそう」って それってねえ、褒めているの?/25歳永遠説</a></td>
      <td><a href="https://www.youtube.com/watch?v=yiYr2-6LtcU" target="_blank" rel="noopener noreferrer">「ひとりで生きられそう」って それってねえ、褒めているの？▶️</a>・<a href="https://www.youtube.com/watch?v=8vxfT-NNV1E" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=GOBwXucqSEo" target="_blank" rel="noopener noreferrer">▶️（リリック・ビデオ）</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=88fRO8entRc" target="_blank" rel="noopener noreferrer">25歳永遠説▶️</a>・<a href="https://www.youtube.com/watch?v=J7i9s17-WHw&t=698s" target="_blank" rel="noopener noreferrer">🎥</a></td>
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
      <td rowspan="4" class="dc-group">13th</td>
      <td rowspan="4" class="dc-group">2020/4/1</td>
      <td rowspan="4" class="dc-group"><a href="https://helloproject.com/release/6261/" target="_blank" rel="noopener noreferrer">ポップミュージック/好きって言ってよ</a></td>
      <td><a href="https://www.youtube.com/watch?v=IrggLg_hGE0" target="_blank" rel="noopener noreferrer">ポップミュージック▶️</a>・<a href="https://www.youtube.com/watch?v=7Z3LUlOQ9uc&t=129s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>KAN</td>
      <td>KAN</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=gYAFijpPSws" target="_blank" rel="noopener noreferrer">好きって言ってよ▶️</a>・<a href="https://www.youtube.com/watch?v=J7i9s17-WHw&t=698s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=NbBkOY4CqZU&t=1559s" target="_blank" rel="noopener noreferrer">続いていくSTORY（Symphonic Version feat. Karin）🎥</a></td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>上杉洋史</td>
    </tr>
    <tr>
      <td>Va-Va-Voom<a href="https://www.youtube.com/watch?v=R4c7o1QG7mc&t=262s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>児玉雨子</td>
      <td>Shusui/Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">14th</td>
      <td rowspan="2" class="dc-group">2021/4/28</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/6436/" target="_blank" rel="noopener noreferrer">DOWN TOWN/がんばれないよ</a></td>
      <td><a href="https://www.youtube.com/watch?v=7xD8ORNRtDI" target="_blank" rel="noopener noreferrer">DOWN TOWN▶️</a>・<a href="https://www.youtube.com/watch?v=RTfq20Dl2Eg&t=378s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>伊藤銀次</td>
      <td>山下達郎</td>
      <td>Anders Dannvik</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=lfg5k3LyEKo" target="_blank" rel="noopener noreferrer">がんばれないよ▶️</a>・<a href="https://www.youtube.com/watch?v=n47fYbqeUMM&t=265s" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=bFHWI6-4jqw" target="_blank" rel="noopener noreferrer">▶️（Juice=Juice Solo mix Ver.）</a></td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td rowspan="3" class="dc-group">15th</td>
      <td rowspan="3" class="dc-group">2021/12/22</td>
      <td rowspan="3" class="dc-group"><a href="https://helloproject.com/release/6613/" target="_blank" rel="noopener noreferrer">プラスティック・ラブ/Familia/Future Smile</a></td>
      <td><a href="https://www.youtube.com/watch?v=f6Nuvg3RuEw" target="_blank" rel="noopener noreferrer">プラスティック・ラブ▶️</a></td>
      <td>竹内まりや</td>
      <td>竹内まりや</td>
      <td>Anders Dannvik</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=6iu4E-ql5Dg" target="_blank" rel="noopener noreferrer">Familia▶️</a>・<a href="https://www.youtube.com/watch?v=5KviSHTl33U&t=665s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>イイジマケン</td>
      <td>Shusui / Shim Zeyun / tsubomi</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=IvyAieDrM1c" target="_blank" rel="noopener noreferrer">Future Smile▶️</a>・<a href="https://www.youtube.com/watch?v=1BLHrjzq1vA&t=93s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>大森祥子</td>
      <td>Shusui / Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">16th</td>
      <td rowspan="2" class="dc-group">2022/11/23</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/6853/" target="_blank" rel="noopener noreferrer">全部賭けてGO!!/イニミニマニモ〜恋のライバル宣言〜</a></td>
      <td><a href="https://www.youtube.com/watch?v=4sO4_dLLeGM" target="_blank" rel="noopener noreferrer">全部賭けてGO!!▶️</a>・<a href="https://www.youtube.com/watch?v=tBQlipoKzKU&t=179s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>大森祥子</td>
      <td>徳田光希</td>
      <td>徳田光希</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=xgoVNqwh1wY" target="_blank" rel="noopener noreferrer">イニミニマニモ～恋のライバル宣言～▶️</a>・<a href="https://www.youtube.com/watch?v=IlLJXH5LQaY&t=138s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>山崎あおい</td>
      <td>Johan Alkenas/Joacim Persson/Lisa Desmond</td>
      <td>Joacim Persson/Johan Alkenas</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">17th</td>
      <td rowspan="2" class="dc-group">2023/7/12</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/6991/" target="_blank" rel="noopener noreferrer">プライド・ブライト/FUNKY FLUSHIN'</a></td>
      <td><a href="https://www.youtube.com/watch?v=wrucm-6MqgE" target="_blank" rel="noopener noreferrer">プライド・ブライト▶️</a>・<a href="https://www.youtube.com/watch?v=vyiXRsx0gJI" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=Uks7Dyn8DAM" target="_blank" rel="noopener noreferrer">FUNKY FLUSHIN'▶️</a>・<a href="https://www.youtube.com/watch?v=pLOVDW-8SNw&t=188s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>吉田美奈子</td>
      <td>山下達郎</td>
      <td>Aksel Odenbalk</td>
    </tr>
    <tr>
      <td rowspan="4" class="dc-group">18th</td>
      <td rowspan="4" class="dc-group">2024/5/15</td>
      <td rowspan="4" class="dc-group"><a href="https://helloproject.com/release/7195/" target="_blank" rel="noopener noreferrer">トウキョウ・ブラー/ナイモノラブ/おあいこ</a></td>
      <td><a href="https://www.youtube.com/watch?v=DPImRhSUtR0" target="_blank" rel="noopener noreferrer">トウキョウ・ブラー▶️</a>・<a href="https://www.youtube.com/watch?v=1blvz_OhPSI&t=545s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>山崎あおい</td>
      <td>Erik Lidbom</td>
      <td>Erik Lidbom</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=wMy_mIgy6hM" target="_blank" rel="noopener noreferrer">ナイモノラブ▶️</a>・<a href="https://www.youtube.com/watch?v=Q7Au7tT_mwY&t=1391s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>SUIMI</td>
      <td>渡辺泰司</td>
      <td>渡辺泰司</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=B8Zsip26P3g" target="_blank" rel="noopener noreferrer">おあいこ▶️</a>・<a href="https://www.youtube.com/watch?v=74N5axt32OI&t=235s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=CTqHcX-dg-I" target="_blank" rel="noopener noreferrer">Brilliance of memories▶️</a>・<a href="https://www.youtube.com/watch?v=PiTPL5d5kSw&t=1143s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>浜田ピエール裕介</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">19th</td>
      <td rowspan="2" class="dc-group">2025/2/26</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/7376/" target="_blank" rel="noopener noreferrer">初恋の亡霊/今夜はHearty Party/a></td>
      <td><a href="https://www.youtube.com/watch?v=DquBd86wOYw" target="_blank" rel="noopener noreferrer">初恋の亡霊▶️</a>・<a href="https://www.youtube.com/watch?v=GkUijMTyJE0" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=g9EXEuk5sxo" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>西野蒟蒻</td>
      <td>KOUGA</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=DquBd86wOYw" target="_blank" rel="noopener noreferrer">今夜はHearty Party▶️</a>・<a href="https://www.youtube.com/watch?v=FxFGv8kvk8M&t=174s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>竹内まりや</td>
      <td>竹内まりや</td>
      <td>Aksel Odenbalk/Albin Hillborg</td>
    </tr>
    <tr>
      <td rowspan="2" class="dc-group">20th</td>
      <td rowspan="2" class="dc-group">2025/10/8</td>
      <td rowspan="2" class="dc-group"><a href="https://helloproject.com/release/7506/" target="_blank" rel="noopener noreferrer">四の五の言わず颯と別れてあげた/盛れ！ミ・アモーレ/a></td>
      <td><a href="https://www.youtube.com/watch?v=cnlS-vuMg40" target="_blank" rel="noopener noreferrer">四の五の言わず颯と別れてあげた▶️</a>・<a href="https://www.youtube.com/watch?v=jWz3Sc4n-Zw" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>大森祥子</td>
      <td>KOUGA</td>
      <td>荒幡亮平</td>
    </tr>
    <tr>
      <td><a href="https://www.youtube.com/watch?v=3RtbnP1onaM" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ▶️</a>・<a href="https://www.youtube.com/watch?v=mj2pkIIQboo" target="_blank" rel="noopener noreferrer">🎥</a>・<a href="https://www.youtube.com/watch?v=MlG9hptTvgQ" target="_blank" rel="noopener noreferrer">💃</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
    </tr>
  </table>

  <h2>配信シングル</h2>
  <table id="karin-disco">
    <tr>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td rowspan="2">2017/8/23</td>
      <td><a href="https://helloproject.com/release/5371/" target="_blank" rel="noopener noreferrer">Fiesta! Fiesta!</a><a href="https://www.youtube.com/watch?v=0SMjNmSwFic" target="_blank" rel="noopener noreferrer">▶️</a>・<a href="https://www.youtube.com/watch?v=8MK4ef2W-sg" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>井筒日美</td>
      <td>エリック・フクサキ</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>Wonderful World（English Ver.）</td>
      <td>イイジマケン<br>英語詞：鈴木桃子</td>
      <td>イイジマケン</td>
      <td>gaokalab</td>
    </tr>
  </table>

  <h2>配信限定</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td>1st</td>
      <td>2016/3/2</td>
      <td>大人の事情</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>2nd</td>
      <td>2017/5/19</td>
      <td><a href="https://www.youtube.com/watch?v=vXjymScSxM0&t=313s" target="_blank" rel="noopener noreferrer">Goal〜明日はあっちだよ〜🎥</a></td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>近藤薫&沢頭たかし</td>
    </tr>
    <tr>
      <td>3rd</td>
      <td>2017/6/16</td>
      <td><a href="https://www.youtube.com/watch?v=8L2yxOeE330" target="_blank" rel="noopener noreferrer">如雨露🎥</a></td>
      <td>児玉雨子</td>
      <td>YUKO</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>-</td>
      <td>2025/12/23</td>
      <td><a href="https://www.youtube.com/watch?v=KGePThef8rc" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ（BAND Live Ver.）▶️🎥</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>-</td>
      <td>2025/12/23</td>
      <td><a href="https://www.youtube.com/watch?v=G36amiUCkXU" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ（Concert 2025 Queen of Hearts Special Flush）▶️🎥</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>-</td>
      <td>2025/12/23</td>
      <td><a href="https://www.youtube.com/watch?v=swn7_j3qusY" target="_blank" rel="noopener noreferrer">盛れ！ミ・アモーレ - From THE FIRST TAKE▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>-</td>
      <td>2026/2/13</td>
      <td><a href="https://www.youtube.com/watch?v=UCacRPyEV9A" target="_blank" rel="noopener noreferrer">「ひとりで生きられそう」って それってねえ、褒めているの?- From THE FIRST TAKE▶️</a></td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
  </table>

  <h2>オリジナルアルバム</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th class="dc-wide">Disk</th>
      <th>Track</th>
      <th class="dc-wide">曲名</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td rowspan="29" class="dc-group">1st</td>
      <td rowspan="29" class="dc-group">2015/7/15</td>
      <td rowspan="29" class="dc-group">First Squeeze!</td>
      <td rowspan="12" class="dc-group">Disc.1 The Best Juice</td>
      <td>1</td>
      <td>天まで登れ!</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎<br>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>2</td>
      <td>ロマンスの途中</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>3</td>
      <td>私が言う前に抱きしめなきゃね（MEMORIAL EDIT）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>4</td>
      <td>五月雨美女がさ乱れる（MEMORIAL EDIT）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>板垣祐介<br>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>5</td>
      <td>イジワルしないで 抱きしめてよ</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>6</td>
      <td>初めてを経験中</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>AKIRA<br>ブラスアレンジ：鈴木俊介</td>
    </tr>
    <tr>
      <td>7</td>
      <td>裸の裸の裸のKISS</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>8</td>
      <td>アレコレしたい!?</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>編曲: 近藤圭一</td>
    </tr>
    <tr>
      <td>9</td>
      <td>ブラックバタフライ</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>10</td>
      <td>風に吹かれて</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>11</td>
      <td>背伸び</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>12</td>
      <td>伊達じゃないよ うちの人生は</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="11" class="dc-group">Disc.2 The Brand-New Juice</td>
      <td>1</td>
      <td>Wonderful World</td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>gaokalab</td>
    </tr>
    <tr>
      <td>2</td>
      <td><a href="https://www.youtube.com/watch?v=XBQ0v95I9RE" target="_blank" rel="noopener noreferrer">CHOICE & CHANCE▶️</a>・<a href="https://www.youtube.com/watch?v=a1MDCj0T0c4" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>3</td>
      <td><a href="https://www.youtube.com/watch?v=1SiDmKHHLro&t=2592s" target="_blank" rel="noopener noreferrer">愛・愛・傘🎥</a></td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>4</td>
      <td><a href="https://www.youtube.com/watch?v=UfwpclOArhc&t=259s" target="_blank" rel="noopener noreferrer">生まれたてのBaby Love🎥</a></td>
      <td>星部ショウ</td>
      <td>masaaki asada</td>
      <td>松井寛</td>
    </tr>
    <tr>
      <td>5</td>
      <td><a href="https://www.youtube.com/watch?v=8uLpUM-XFkQ&t=1873s" target="_blank" rel="noopener noreferrer">選ばれし私達🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>山崎淳</td>
    </tr>
    <tr>
      <td>6</td>
      <td>Ca va ? Ca va ?（サヴァ サヴァ</td>
      <td>三浦徳子</td>
      <td>川辺ヒロシ・上田禎</td>
      <td>CMJK</td>
    </tr>
    <tr>
      <td>7</td>
      <td><a href="https://www.youtube.com/watch?v=zBrio8u91Ek&t=410s" target="_blank" rel="noopener noreferrer">GIRLS BE AMBITIOUS🎥</a></td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>中島卓偉・宮永治郎</td>
    </tr>
    <tr>
      <td>8</td>
      <td><a href="https://www.youtube.com/watch?v=hfrALgcKlgc&t=845s" target="_blank" rel="noopener noreferrer">愛のダイビング🎥</a></td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>土肥真生</td>
    </tr>
    <tr>
      <td>9</td>
      <td><a href="https://www.youtube.com/watch?v=BUU99oZPrZ8&t=1287s" target="_blank" rel="noopener noreferrer">チクタク 私の旬🎥</a></td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>CMJK</td>
    </tr>
    <tr>
      <td>10</td>
      <td><a href="https://www.youtube.com/watch?v=Ln9JjDnqyIA&t=1968s" target="_blank" rel="noopener noreferrer">未来へ、さあ走り出せ!🎥</a></td>
      <td>角田崇徳</td>
      <td>KOJI oba</td>
      <td>KOJI oba</td>
    </tr>
    <tr>
      <td>11</td>
      <td><a href="https://www.youtube.com/watch?v=E2AKITM9uA0" target="_blank" rel="noopener noreferrer">続いていくSTORY▶️</a>・<a href="https://www.youtube.com/watch?v=Ln9JjDnqyIA&t=1968s" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>近藤薫・HASSE</td>
    </tr>
    <tr>
      <td rowspan="6" class="dc-group">The Cover Juice</td>
      <td>1</td>
      <td><a href="https://www.youtube.com/watch?v=5ktnsWyJOa4&t=1394s" target="_blank" rel="noopener noreferrer">Magic of Love（J=J 2015Ver.）🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>村山晋一郎</td>
    </tr>
    <tr>
      <td>2</td>
      <td>香水（J=J 2015Ver.）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>3</td>
      <td>鳴り始めた恋のBell</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>松井寛</td>
    </tr>
    <tr>
      <td>4</td>
      <td><a href="https://www.youtube.com/watch?v=INFWNZCYoVU&t=2006s" target="_blank" rel="noopener noreferrer">スクランブル🎥</a></td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木Daichi秀行</td>
    </tr>
    <tr>
      <td>5</td>
      <td>BABY! 恋にKNOCK OUT!</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>小西貴雄</td>
    </tr>
    <tr>
      <td>6</td>
      <td>ラストキッス</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>小西貴雄</td>
    </tr>
    <tr>
      <td rowspan="22" class="dc-group">2nd</td>
      <td rowspan="22" class="dc-group">2018/8/1</td>
      <td rowspan="22" class="dc-group">Juice=Juice#2 -!Una mas!-</td>
      <td rowspan="11" class="dc-group">Disc.1</td>
      <td>1</td>
      <td>Fiesta! Fiesta!</td>
      <td>井筒日美</td>
      <td>エリック・フクサキ</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>2</td>
      <td>SEXY SEXY</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>3</td>
      <td>泣いていいよ</td>
      <td>大森祥子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
    </tr>
    <tr>
      <td>4</td>
      <td>Vivid Midnight</td>
      <td>児玉雨子</td>
      <td>SEION, Tasco, Tenzo</td>
      <td>Tasco, Tenzo</td>
    </tr>
    <tr>
      <td>5</td>
      <td>地団駄ダンス</td>
      <td>児玉雨子</td>
      <td>BLACC HOLE&NOBB-D</td>
      <td>BLACC HOLE&NOBB-D with T</td>
    </tr>
    <tr>
      <td>6</td>
      <td>Feel!感じるよ</td>
      <td>三浦徳子</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
    </tr>
    <tr>
      <td>7</td>
      <td>Dream Road～心が躍り出してる～</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>江上浩太郎</td>
    </tr>
    <tr>
      <td>8</td>
      <td>KEEP ON 上昇志向!! </td>
      <td>前山田健一</td>
      <td>ダンス☆マン</td>
      <td>ダンス☆マン<br>ブラスアレンジ: 川松久芳</td>
    </tr>
    <tr>
      <td>9</td>
      <td>明日やろうはバカやろう</td>
      <td>福田花音</td>
      <td>板垣祐介</td>
      <td>板垣祐介</td>
    </tr>
    <tr>
      <td>10</td>
      <td>Next is you!</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>11</td>
      <td>カラダだけが大人になったんじゃない</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="11" class="dc-group">Disc.2</td>
      <td>1</td>
      <td><a href="https://www.youtube.com/watch?v=rgUER272LsA&t=1562s" target="_blank" rel="noopener noreferrer">Never Never Surrender🎥</a></td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>2</td>
      <td>如雨露（Album Version）</td>
      <td>児玉雨子</td>
      <td>YUKO</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>3</td>
      <td>TOKYOグライダー</td>
      <td>唐沢美帆</td>
      <td>星部ショウ</td>
      <td>松井寛</td>
    </tr>
    <tr>
      <td>4</td>
      <td>シンクロ。</td>
      <td>井筒日美</td>
      <td>星部ショウ</td>
      <td>浜田ピエール裕介</td>
    </tr>
    <tr>
      <td>5</td>
      <td><a href="https://www.youtube.com/watch?v=5Pt4FTzjaFo&t=3275s" target="_blank" rel="noopener noreferrer">あばれてっか?! ハヴアグッタイ🎥</a></td>
      <td>児玉雨子</td>
      <td>ダンス☆マン</td>
      <td>ダンス☆マン</td>
    </tr>
    <tr>
      <td>6</td>
      <td><a href="https://www.youtube.com/watch?v=pwPWvwn5wH8&t=20s" target="_blank" rel="noopener noreferrer">素直に甘えて🎥</a></td>
      <td>NOBE</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
    </tr>
    <tr>
      <td>7</td>
      <td>Goal～明日はあっちだよ～（Album Version）</td>
      <td>近藤薫</td>
      <td>近藤薫&amp;沢頭たかし</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>8</td>
      <td><a href="https://www.youtube.com/watch?v=_6ug8Bh3Zwo&t=1573s" target="_blank" rel="noopener noreferrer">銀色のテレパシー🎥</a></td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>高橋諭一</td>
    </tr>
    <tr>
      <td>9</td>
      <td><a href="https://www.youtube.com/watch?v=lXB1gMTP-ew" target="_blank" rel="noopener noreferrer">この世界は捨てたもんじゃない🎥</a></td>
      <td>井筒日美</td>
      <td>中村瑛彦</td>
      <td>中村瑛彦</td>
    </tr>
    <tr>
      <td>10</td>
      <td><a href="https://www.youtube.com/watch?v=2_toons5ciE&t=130s" target="_blank" rel="noopener noreferrer">禁断少女🎥</a></td>
      <td>大橋莉子</td>
      <td>大橋莉子</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>11</td>
      <td>大人の事情</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td rowspan="24" class="dc-group">3rd</td>
      <td rowspan="24" class="dc-group">2022/4/20</td>
      <td rowspan="24" class="dc-group">terzo</td>
      <td rowspan="16" class="dc-group">Disc.1 The Best Juice 2019-2022</td>
      <td>1</td>
      <td>微炭酸</td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td>2</td>
      <td>ポツリと</td>
      <td>中島卓偉</td>
      <td>浜田ピエール裕介</td>
      <td></td>
    </tr>
    <tr>
      <td>3</td>
      <td>Good bye & Good luck!</td>
      <td>三浦徳子</td>
      <td>KOUGA</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>4</td>
      <td>「ひとりで生きられそう」って それってねえ、褒めているの?</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>5</td>
      <td>25歳永遠説</td>
      <td>児玉雨子</td>
      <td>KOUGA</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td>6</td>
      <td><a href="https://www.youtube.com/watch?v=ykotlrfr6oI&t=5s" target="_blank" rel="noopener noreferrer">「ひとりで生きられそう」って それってねえ、褒めているの？ （New Vocal Ver.）💃</a></td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>7</td>
      <td>ポップミュージック</td>
      <td>KAN</td>
      <td>KAN</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>8</td>
      <td>好きって言ってよ</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>9</td>
      <td>Borderline</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>10</td>
      <td>Va-Va-Voom</td>
      <td>児玉雨子</td>
      <td>Shusui/Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
    <tr>
      <td>11</td>
      <td>続いていくSTORY （Symphonic Version feat. Karin）</td>
      <td>近藤薫</td>
      <td>近藤薫</td>
      <td>上杉洋史</td>
    </tr>
    <tr>
      <td>12</td>
      <td>DOWN TOWN</td>
      <td>伊藤銀次</td>
      <td>山下達郎</td>
      <td>Anders Dannvik</td>
    </tr>
    <tr>
      <td>13</td>
      <td>がんばれないよ</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td>14</td>
      <td>プラスティック・ラブ</td>
      <td>竹内まりや</td>
      <td>竹内まりや</td>
      <td>Anders Dannvik</td>
    </tr>
    <tr>
      <td>15</td>
      <td>Familia</td>
      <td>イイジマケン</td>
      <td>Shusui / Shim Zeyun / tsubomi</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>16</td>
      <td>Future Smile</td>
      <td>大森祥子</td>
      <td>Shusui / Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
    <tr>
      <td rowspan="8" class="dc-group">Disc.2 The Brand-New Juice 2022</td>
      <td>1</td>
      <td><a href="https://www.youtube.com/watch?v=5Ztjq7P88Rs&t=186s" target="_blank" rel="noopener noreferrer">GIRLS BE AMBITIOUS! 2022🎥</a></td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>中島卓偉 / 宮永治郎</td>
    </tr>
    <tr>
      <td>2</td>
      <td><a href="https://www.youtube.com/watch?v=62eLutOvbr8&t=140s" target="_blank" rel="noopener noreferrer">POPPIN' LOVE🎥</a></td>
      <td>山崎あおい</td>
      <td>Erik Lidbom / Julie Yu / Andreas Carlsson</td>
      <td>Erik Lidbom</td>
    </tr>
    <tr>
      <td>3</td>
      <td><a href="https://www.youtube.com/watch?v=rgUER272LsA&t=150s" target="_blank" rel="noopener noreferrer">STAGE～アガってみな～🎥</a></td>
      <td>徳田光希</td>
      <td>徳田光希</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>4</td>
      <td><a href="https://www.youtube.com/watch?v=iCP07zOEjFI&t=1648s" target="_blank" rel="noopener noreferrer">Mon Amour🎥</a></td>
      <td>オオヤギヒロオ / 井筒日美</td>
      <td>オオヤギヒロオ</td>
      <td>浜田ピエール裕介</td>
    </tr>
    <tr>
      <td>5</td>
      <td><a href="https://www.youtube.com/watch?v=fAjwjHgDD6c&t=2548s" target="_blank" rel="noopener noreferrer">ノクチルカ🎥</a></td>
      <td>唐沢美帆</td>
      <td>松井寛</td>
      <td>松井寛</td>
    </tr>
    <tr>
      <td>6</td>
      <td><a href="https://www.youtube.com/watch?v=TI6oxfA67_4&t=3375s" target="_blank" rel="noopener noreferrer">G.O.A.T🎥</a></td>
      <td>井筒日美</td>
      <td>Shusui / Andreas Ohrm / Henrik Smith</td>
      <td>Henrik Smith</td>
    </tr>
    <tr>
      <td>7</td>
      <td><a href="https://www.youtube.com/watch?v=38vZdC-Ws1w&t=1497s" target="_blank" rel="noopener noreferrer">雨の中の口笛🎥</a></td>
      <td>Shusui / Zeyun / tsubomi</td>
      <td>Shusui / Zeyun / tsubomi</td>
      <td>Zeyun</td>
    </tr>
    <tr>
      <td>8</td>
      <td><a href="https://www.youtube.com/watch?v=1w7e-OAlQWE" target="_blank" rel="noopener noreferrer">プラトニック・プラネット（Ultimate Juice Ver.）🎥</a></td>
      <td>児玉雨子</td>
      <td>炭竃智弘</td>
      <td>炭竃智弘</td>
    </tr>
  </table>

  <h2>ベストアルバム</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th>Track</th>
      <th class="dc-wide">曲名</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td rowspan="14" class="dc-group">1st</td>
      <td rowspan="14" class="dc-group">2023/10/11</td>
      <td rowspan="14" class="dc-group">Juicetory</td>
      <td>1</td>
      <td>ロマンスの途中（2023 10th Juice Ver.）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>2</td>
      <td>私が言う前に抱きしめなきゃね（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>3</td>
      <td>イジワルしないで 抱きしめてよ（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>4</td>
      <td>初めてを経験中（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>AKIRA<br>ブラスアレンジ: 鈴木俊介</td>
    </tr>
    <tr>
      <td>5</td>
      <td>ブラックバタフライ（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>6</td>
      <td>Wonderful World（2023 10th Juice Ver.）</td>
      <td>イイジマケン</td>
      <td>イイジマケン</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>7</td>
      <td>CHOICE&CHANCE（2023）</td>
      <td>星部ショウ</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>8</td>
      <td>Magic of Love（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>村山晋一郎</td>
    </tr>
    <tr>
      <td>9</td>
      <td>カラダだけが大人になったんじゃない（2023）</td>
      <td>つんく</td>
      <td>つんく</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>10</td>
      <td>Never Never Surrender</td>
      <td>児玉雨子</td>
      <td>星部ショウ</td>
      <td>大久保薫</td>
    </tr>
    <tr>
      <td>11</td>
      <td>微炭酸</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>KOUGA</td>
    </tr>
    <tr>
      <td>12</td>
      <td>「ひとりで生きられそう」って それってねえ、褒めているの?（2023）</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>13</td>
      <td>ポップミュージック（2023）</td>
      <td>KAN</td>
      <td>KAN</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>14</td>
      <td>ボン・ヴォヤージュ～想いの軌跡～
      <td>井筒日美</td>
      <td>Shusui/Josef Melin</td>
      <td>Josef Melin</td>
    </tr>
  </table>
  
  <h2>ミニアルバム</h2>
  <table id="karin-disco">
    <tr>
      <th></th>
      <th>発売日</th>
      <th class="dc-wide">タイトル</th>
      <th>Track</th>
      <th class="dc-wide">曲名</th>
      <th class="dc-credit">作詞</th>
      <th class="dc-credit">作曲</th>
      <th class="dc-credit">編曲</th>
    </tr>
    <tr>
      <td rowspan="14" class="dc-group">1st</td>
      <td rowspan="14" class="dc-group">2026/6/21</td>
      <td rowspan="14" class="dc-group">MORE! MORE! EP</td>
      <td>1</td>
      <td><a href="https://www.youtube.com/watch?v=66T4PgpJDn8" target="_blank" rel="noopener noreferrer">>クラクラ☆クライマックス🎥</a></td>
      <td>広瀬香美/Maria Okada</td>
      <td>広瀬香美</td>
      <td>h-wonder</td>
    </tr>
    <tr>
      <td>2</td>
      <td>盛れ!ミ・アモーレ</td>
      <td>山崎あおい</td>
      <td>山崎あおい</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>3</td>
      <td>結論から言ってちょうだい</td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>鈴木俊介</td>
    </tr>
    <tr>
      <td>4</td>
      <td><a href="https://www.youtube.com/watch?v=WvdT3_XHYIk" target="_blank" rel="noopener noreferrer">甘えんな🎥</a></td>
      <td>大森祥子</td>
      <td>炭竃智弘</td>
      <td>炭竃智弘</td>
    </tr>
    <tr>
      <td>5</td>
      <td><a href="https://www.youtube.com/watch?v=nrGLxAWIUJo" target="_blank" rel="noopener noreferrer">BLOODY BULLET▶️</a>・<a href="https://www.youtube.com/watch?v=xW3p6ZmX5CA" target="_blank" rel="noopener noreferrer">🎥</a></td>
      <td>西野蒟蒻</td>
      <td>星部ショウ</td>
      <td>平田祥一郎</td>
    </tr>
    <tr>
      <td>6</td>
      <td>ギラめきな!</td>
      <td>西野蒟蒻</td>
      <td>Simon Janlov/miwaflower</td>
      <td>Simon Janlov</td>
    </tr>
    <tr>
      <td>7</td>
      <td><a href="https://www.youtube.com/watch?v=SfPe376KI9I" target="_blank" rel="noopener noreferrer">GIRLS BE AMBITIOUS! 2026🎥</a> (Live at Zepp DiverCity(TOKYO)2026.05.06</td>
      <td>中島卓偉</td>
      <td>中島卓偉</td>
      <td>中島卓偉/宮永治郎</td>
    </tr>
  </table>

<div class="last-update">最終更新日：2026/08/14</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
