<?php
  $title = "ハロプロメンバーカラー色空間配置"; 
  $description = "ハロプロメンバーのメンバーカラーのHEXが公開されたので、HS色空間に配置してみました。Lは無視しているので、同じ色のメンバーは同じ位置にプロットされます。";  
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
<h2>Hello! Project Member Colors</h2>

<div class="filters" id="filters"></div>

<div class="wheel-wrap">
  <canvas id="wheel" width="320" height="320"></canvas>
</div>

<div class="info-panel" id="info-panel">
  <span class="info-empty">メンバーのドットをクリック</span>
</div>

<div class="footer"><br>L無視・HSのみでプロット<br>各グループをクリックするとそのグループだけがフィルタされます。<br>#$記述は一般的なものではなく、私が別の文脈で提案したもので以下を読んでください。<br>https://note.com/yxgxmxn/n/n5b084dc0d700</div>

<script>
const members = [
  {name:"櫻井梨央",group:"モーニング娘。'26",color:"ミルクティー",hex:"#d3bba8"},
  {name:"長野桃羽",group:"アンジュルム",color:"イエロー",hex:"#F7EA48"},
  {name:"西村乙輝",group:"つばきファクトリー",color:"イエロー",hex:"#F7EA48"},
  {name:"広本瑠璃",group:"OCHA NORMA",color:"イエロー",hex:"#F7EA48"},
  {name:"上村麗菜",group:"ロージークロニクル",color:"イエロー",hex:"#F7EA48"},
  {name:"伊勢鈴蘭",group:"アンジュルム",color:"オレンジ",hex:"#fc4c02"},
  {name:"段原瑠々",group:"Juice=Juice",color:"オレンジ",hex:"#fc4c02"},
  {name:"清野桃々姫",group:"BEYOOOOONDS",color:"オレンジ",hex:"#fc4c02"},
  {name:"植村葉純",group:"ロージークロニクル",color:"オレンジ",hex:"#fc4c02"},
  {name:"岡村ほまれ",group:"モーニング娘。'26",color:"デイジー",hex:"#fdda24"},
  {name:"江端妃咲",group:"Juice=Juice",color:"デイジー",hex:"#fdda24"},
  {name:"江口紗耶",group:"BEYOOOOONDS",color:"デイジー",hex:"#fdda24"},
  {name:"松本わかな",group:"アンジュルム",color:"ホワイト",hex:"#ffffff"},
  {name:"井上玲音",group:"Juice=Juice",color:"ホワイト",hex:"#ffffff"},
  {name:"石井泉羽",group:"つばきファクトリー",color:"ホワイト",hex:"#ffffff"},
  {name:"中山夏月姫",group:"OCHA NORMA",color:"ホワイト",hex:"#ffffff"},
  {name:"橋田歩果",group:"ロージークロニクル",color:"ホワイト",hex:"#ffffff"},
  {name:"豫風瑠乃",group:"つばきファクトリー",color:"マスタード",hex:"#ffcd00"},
  {name:"平山遊季",group:"アンジュルム",color:"ライトグリーン",hex:"#d0df00"},
  {name:"北原もも",group:"OCHA NORMA",color:"ライトグリーン",hex:"#d0df00"},
  {name:"小林萌花",group:"BEYOOOOONDS",color:"グリーン",hex:"#007749"},
  {name:"小野瑞歩",group:"つばきファクトリー",color:"エメラルドグリーン",hex:"#00b2a9"},
  {name:"川名凜",group:"アンジュルム",color:"グリーン",hex:"#00B140"},
  {name:"山﨑愛生",group:"モーニング娘。'26",color:"ブライトグリーン",hex:"#00B140"},
  {name:"林仁愛",group:"Juice=Juice",color:"ブライトグリーン",hex:"#00B140"},
  {name:"土居楓奏",group:"つばきファクトリー",color:"ブライトグリーン",hex:"#00B140"},
  {name:"島川波菜",group:"ロージークロニクル",color:"ブライトグリーン",hex:"#00B140"},
  {name:"井上春華",group:"モーニング娘。'26",color:"ミントグリーン",hex:"#00c7b1"},
  {name:"遠藤彩加里",group:"Juice=Juice",color:"ミントグリーン",hex:"#00c7b1"},
  {name:"高瀬くるみ",group:"BEYOOOOONDS",color:"ミントグリーン",hex:"#00c7b1"},
  {name:"松永里愛",group:"Juice=Juice",color:"ロイヤルブルー",hex:"#0033a0"},
  {name:"福田真琳",group:"つばきファクトリー",color:"ロイヤルブルー",hex:"#0033a0"},
  {name:"筒井澪心",group:"OCHA NORMA",color:"ロイヤルブルー",hex:"#0033a0"},
  {name:"相馬優芽",group:"ロージークロニクル",color:"ブルー",hex:"#005eb8"},
  {name:"里吉うたの",group:"BEYOOOOONDS",color:"ミディアムブルー",hex:"#005eb8"},
  {name:"後藤花",group:"アンジュルム",color:"シーブルー",hex:"#59cbe8"},
  {name:"前田こころ",group:"BEYOOOOONDS",color:"シーブルー",hex:"#59cbe8"},
  {name:"斉藤円香",group:"OCHA NORMA",color:"シーブルー",hex:"#59cbe8"},
  {name:"有澤一華",group:"Juice=Juice",color:"ライトブルー",hex:"#b9d9eb"},
  {name:"松原ユリヤ",group:"ロージークロニクル",color:"ライトブルー",hex:"#b9d9eb"},
  {name:"野中美希",group:"モーニング娘。'26",color:"パープル",hex:"#582c83"},
  {name:"石山咲良",group:"Juice=Juice",color:"パープル",hex:"#582c83"},
  {name:"河西結心",group:"つばきファクトリー",color:"パープル",hex:"#582c83"},
  {name:"平井美葉",group:"BEYOOOOONDS",color:"パープル",hex:"#582c83"},
  {name:"西﨑美空",group:"OCHA NORMA",color:"パープル",hex:"#582c83"},
  {name:"小田さくら",group:"モーニング娘。'26",color:"ラベンダー",hex:"#A05EB5"},
  {name:"入江里咲",group:"Juice=Juice",color:"ライトパープル",hex:"#c1a7e2"},
  {name:"谷本安美",group:"つばきファクトリー",color:"ライトパープル",hex:"#c1a7e2"},
  {name:"村越彩菜",group:"ロージークロニクル",color:"ライトパープル",hex:"#c1a7e2"},
  {name:"弓桁朱琴",group:"モーニング娘。'26",color:"ピュアレッド",hex:"#db0025"},
  {name:"下井谷幸穂",group:"アンジュルム",color:"ホットピンク",hex:"#da1884"},
  {name:"西田汐里",group:"BEYOOOOONDS",color:"ホットピンク",hex:"#da1884"},
  {name:"米村姫良々",group:"OCHA NORMA",color:"イタリアンレッド",hex:"#e70033"},
  {name:"小野田紗栞",group:"つばきファクトリー",color:"ピーチ",hex:"#f99fc9"},
  {name:"橋迫鈴",group:"アンジュルム",color:"ピュアレッド",hex:"#e70033"},
  {name:"川嶋美楓",group:"Juice=Juice",color:"ピュアレッド",hex:"#e70033"},
  {name:"吉田姫杷",group:"ロージークロニクル",color:"ピュアレッド",hex:"#e70033"},
  {name:"牧野真莉愛",group:"モーニング娘。'26",color:"ピンク",hex:"#F57EB6"},
  {name:"為永幸音",group:"アンジュルム",color:"ピンク",hex:"#F57EB6"},
  {name:"工藤由愛",group:"Juice=Juice",color:"ピンク",hex:"#F57EB6"},
  {name:"岡村美波",group:"BEYOOOOONDS",color:"ピンク",hex:"#F57EB6"},
  {name:"窪田七海",group:"OCHA NORMA",color:"ピンク",hex:"#F57EB6"},
  {name:"小野田華凜",group:"ロージークロニクル",color:"ピンク",hex:"#F57EB6"},
  {name:"村田結生",group:"つばきファクトリー",color:"ライトピンク",hex:"#f2d4d7"},
  {name:"秋山眞緒",group:"つばきファクトリー",color:"ライトレッド",hex:"#e70033"},
];

const groups = ["全体", "モーニング娘。'26", "アンジュルム", "Juice=Juice", "つばきファクトリー", "OCHA NORMA", "BEYOOOOONDS", "ロージークロニクル"];
let currentGroup = "全体";

const W = 320, CX = W/2, CY = W/2;
const R_OUT = 148, R_IN = 52;

function hexToRgb(hex) {
  const h = hex.replace('#','');
  return [parseInt(h.slice(0,2),16), parseInt(h.slice(2,4),16), parseInt(h.slice(4,6),16)];
}

function rgbToHsl(r,g,b) {
  r/=255; g/=255; b/=255;
  const max=Math.max(r,g,b), min=Math.min(r,g,b);
  let h,s,l=(max+min)/2;
  if(max===min){ h=0; s=0; }
  else {
    const d=max-min;
    s = l>0.5 ? d/(2-max-min) : d/(max+min);
    switch(max){
      case r: h=((g-b)/d+(g<b?6:0))/6; break;
      case g: h=((b-r)/d+2)/6; break;
      case b: h=((r-g)/d+4)/6; break;
    }
  }
  return [Math.round(h*360), Math.round(s*100), Math.round(l*100)];
}

function rgbTo8rgb(r,g,b) {
  const f = v => v===0?0:v===255?7:Math.round(v*7/255);
  return [f(r),f(g),f(b)];
}

// 全メンバーにHSL・座標を付与
const allPlotted = members.map(m => {
  const [r,g,b] = hexToRgb(m.hex);
  const [h,s,l] = rgbToHsl(r,g,b);
  const [r8,g8,b8] = rgbTo8rgb(r,g,b);
  const sat = s/100;
  const radius = sat < 0.05 ? R_IN + 4 : R_IN + sat*(R_OUT-R_IN);
  const angle = (h/360)*Math.PI*2 - Math.PI/2;
  const rawX = CX + Math.cos(angle)*radius;
  const rawY = CY + Math.sin(angle)*radius;
  const dx = rawX-CX, dy = rawY-CY;
  const dist = Math.sqrt(dx*dx+dy*dy);
  const clamp = Math.min(dist, R_OUT-8);
  const a2 = Math.atan2(dy,dx);
  return {
    ...m, h, s, l, r8, g8, b8,
    hex: m.hex.toUpperCase(),
    px: CX+Math.cos(a2)*clamp,
    py: CY+Math.sin(a2)*clamp
  };
});

function getPlotted() {
  if(currentGroup === "全体") return allPlotted;
  return allPlotted.filter(m => m.group === currentGroup);
}

const canvas = document.getElementById('wheel');
const ctx = canvas.getContext('2d');

function hslToRgbDraw(h,s,l) {
  const c=(1-Math.abs(2*l-1))*s;
  const x=c*(1-Math.abs((h/60)%2-1));
  const m=l-c/2;
  let r=0,g=0,b=0;
  if(h<60){r=c;g=x;}
  else if(h<120){r=x;g=c;}
  else if(h<180){g=c;b=x;}
  else if(h<240){g=x;b=c;}
  else if(h<300){r=x;b=c;}
  else{r=c;b=x;}
  return [Math.round((r+m)*255),Math.round((g+m)*255),Math.round((b+m)*255)];
}

function drawWheel() {
  ctx.clearRect(0,0,W,W);
  const steps=360;
  for(let i=0;i<steps;i++) {
    const angle=(i/steps)*Math.PI*2-Math.PI/2;
    const nextAngle=((i+1)/steps)*Math.PI*2-Math.PI/2;
    const hue=(i/steps)*360;
    const grad=ctx.createRadialGradient(CX,CY,R_IN,CX,CY,R_OUT);
    const [r1,g1,b1]=hslToRgbDraw(hue,0,0.5);
    const [r2,g2,b2]=hslToRgbDraw(hue,1,0.5);
    grad.addColorStop(0,`rgb(${r1},${g1},${b1})`);
    grad.addColorStop(1,`rgb(${r2},${g2},${b2})`);
    ctx.beginPath();
    ctx.moveTo(CX+Math.cos(angle)*R_IN,CY+Math.sin(angle)*R_IN);
    ctx.arc(CX,CY,R_OUT,angle,nextAngle);
    ctx.arc(CX,CY,R_IN,nextAngle,angle,true);
    ctx.closePath();
    ctx.fillStyle=grad;
    ctx.fill();
  }

  const plotted = getPlotted();

  // グループフィルター時は全体をグレーで薄く表示
  if(currentGroup !== "全体") {
    const hexGroups = {};
    allPlotted.forEach(m => {
      const key = m.hex;
      if(!hexGroups[key]) hexGroups[key] = [];
      hexGroups[key].push(m);
    });
    const drawnGray = new Set();
    allPlotted.forEach(m => {
      if(drawnGray.has(m.hex)) return;
      drawnGray.add(m.hex);
      ctx.beginPath();
      ctx.arc(m.px, m.py, 6, 0, Math.PI*2);
      ctx.fillStyle = 'rgba(180,180,180,0.3)';
      ctx.fill();
    });
  }

  // フィルター後のメンバーをグループ化して描画
  const hexGroups = {};
  plotted.forEach(m => {
    const key = m.hex;
    if(!hexGroups[key]) hexGroups[key] = [];
    hexGroups[key].push(m);
  });

  const drawn = new Set();
  plotted.forEach(m => {
    const key = m.hex;
    if(drawn.has(key)) return;
    drawn.add(key);
    const count = hexGroups[key].length;
    const r = count > 1 ? 10 : 7;

    ctx.beginPath();
    ctx.arc(m.px, m.py, r, 0, Math.PI*2);
    ctx.fillStyle = m.hex;
    ctx.fill();
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(m.px, m.py, r, 0, Math.PI*2);
    ctx.strokeStyle = 'rgba(0,0,0,0.3)';
    ctx.lineWidth = 1;
    ctx.stroke();

    if(count > 1) {
      const [rv,gv,bv] = hexToRgb(m.hex);
      const lum = 0.299*rv+0.587*gv+0.114*bv;
      ctx.fillStyle = lum>160?'#000':'#fff';
      ctx.font = 'bold 9px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(count, m.px, m.py);
    }
  });

  // 選択強調
  if(selected) {
    const selCount = plotted.filter(p=>p.hex===selected.hex).length;
    const selR = selCount>1?10:7;
    ctx.beginPath();
    ctx.arc(selected.px, selected.py, selR+4, 0, Math.PI*2);
    ctx.strokeStyle = '#111';
    ctx.lineWidth = 2.5;
    ctx.stroke();
  }
}

let selected = null;

canvas.addEventListener('click', e => {
  const rect = canvas.getBoundingClientRect();
  const mx = e.clientX-rect.left;
  const my = e.clientY-rect.top;
  const plotted = getPlotted();
  let closest=null, minDist=16;
  plotted.forEach(m => {
    const d = Math.sqrt((mx-m.px)**2+(my-m.py)**2);
    if(d<minDist){minDist=d;closest=m;}
  });
  selected = closest;
  drawWheel();
  updatePanel();
});

function updatePanel() {
  const panel = document.getElementById('info-panel');
  if(!selected){
    panel.innerHTML='<span class="info-empty">メンバーのドットをクリック</span>';
    return;
  }
  const m = selected;
  const code8 = `#$${m.r8}${m.g8}${m.b8}`;
  const plotted = getPlotted();
  const sameHex = plotted.filter(p=>p.hex===m.hex);
  const names = sameHex.map(p=>p.name).join('、');
  const groups = sameHex.map(p=>p.group).filter((v,i,a)=>a.indexOf(v)===i).join(' / ');
  const colorName = sameHex.map(p=>p.color).filter((v,i,a)=>a.indexOf(v)===i).join(' / ');
  panel.innerHTML=`
    <div class="info-swatch" style="background:${m.hex}"></div>
    <div class="info-text">
      <div class="info-name" style="font-size:${sameHex.length>2?'12px':'14px'}">${names}</div>
      <div class="info-group">${groups}</div>
      <div class="info-color-name">${colorName}</div>
      <div class="info-codes">
        <div class="info-code">HEX <span>${m.hex}</span></div>
        <div class="info-code">8RGB <span>${code8}</span></div>
        <div class="info-code">HSL <span>${m.s===0?`— , — , ${m.l}%`:`${m.h}° , ${m.s}% , ${m.l}%`}</span></div>
      </div>
    </div>
  `;
}

// フィルターボタン生成
const filtersEl = document.getElementById('filters');
groups.forEach(g => {
  const btn = document.createElement('button');
  btn.className = 'filter-btn' + (g==='全体'?' active':'');
  btn.textContent = g;
  btn.addEventListener('click', () => {
    currentGroup = g;
    selected = null;
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    drawWheel();
    updatePanel();
  });
  filtersEl.appendChild(btn);
});

drawWheel();
</script>
<div class="last-update">最終更新日：2026/06/13</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>