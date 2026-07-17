<?php
  $title = "Xポスト前後検索";
  $description = "XのポストURLを貼るだけで、そのポストの前後(±6時間〜7日)に投稿者が何を言っていたかを検索するコマンドとリンクを生成するツールです。日時の入力は不要です。";
  $jsonld_type = "WebApplication";
  include $_SERVER['DOCUMENT_ROOT'] . '/common/header.php';
?>

<main>
  <h1>Xポスト前後検索</h1>
  XのポストURLを貼るだけで、そのポストの前後に投稿者が何を言っていたかを検索するコマンドとリンクを生成します。<br>
  ポストIDから投稿時刻を逆算するので、日時を手入力する必要はありません。時刻はポストの瞬間を中心に秒単位で正確に切り出します。<br>

  <div id="ctx-controls">
    <input type="text" id="ctx-url" placeholder="https://x.com/ユーザー名/status/0000000000" autocomplete="off">
  </div>

  <div id="ctx-options">
    <label>前後の範囲
      <select id="ctx-range">
        <option value="21600">±6時間</option>
        <option value="43200">±12時間</option>
        <option value="86400" selected>±1日</option>
        <option value="259200">±3日</option>
        <option value="604800">±7日</option>
      </select>
    </label>
    <label><input type="checkbox" id="ctx-noreply"> リプライを除外</label>
    <label><input type="checkbox" id="ctx-noquote"> 引用を除外</label>
    <label><input type="checkbox" id="ctx-withrt"> RTも含める</label>
  </div>

  <div id="ctx-result" hidden>
    <p id="ctx-time-box">投稿日時 (JST)：<strong id="ctx-time"></strong></p>
    <div id="ctx-cmd-row">
      <code id="ctx-cmd"></code>
      <button id="ctx-copy" type="button">コピー</button>
    </div>
    <a id="ctx-open" target="_blank" rel="noopener noreferrer">Xの「最新」タブでこの検索を開く</a>
  </div>
  <p id="ctx-err" hidden></p>

  <h2>仕組み</h2>
  XのポストIDには投稿時刻がミリ秒単位で埋め込まれています。このツールはIDから時刻を逆算し、隠し検索演算子 <code>since_time:</code> / <code>until_time:</code>(unix秒指定)で前後の期間を切り出します。<br>
  日付指定(<code>since:</code>/<code>until:</code>)と違いUTCとの時差の影響を受けず、ポストの瞬間を中心とした正確な範囲で検索できます。<br>
  なお、検索はX上で行われるため、鍵アカウント・削除済みポストは表示されません。URLのユーザー名部分は現在のものと異なる場合があります(改名時など)。

<script>
(function () {
  var EPOCH = 1288834974657n;
  function $(id) { return document.getElementById(id); }
  function pad(n) { return String(n).padStart(2, "0"); }

  function fail(msg) {
    $("ctx-result").hidden = true;
    $("ctx-err").textContent = msg;
    $("ctx-err").hidden = msg === "";
  }

  function update() {
    var m = $("ctx-url").value.match(/(?:x|twitter)\.com\/([A-Za-z0-9_]+)\/status(?:es)?\/(\d+)/);
    if (!m) {
      fail($("ctx-url").value.trim() === "" ? "" :
        "URLからユーザー名とポストIDを読み取れませんでした。x.com/ユーザー名/status/番号 の形のURLを貼ってください。");
      return;
    }
    var user = m[1];
    var id = BigInt(m[2]);
    if (user.toLowerCase() === "i") {
      fail("このURL形式にはユーザー名が含まれていません。ポストを開いて x.com/ユーザー名/status/番号 の形のURLをコピーしてください。");
      return;
    }
    if (id < 300000000000000n) {
      fail("2010年11月以前の古いポストはIDに時刻が含まれていないため、このツールでは扱えません。");
      return;
    }
    $("ctx-err").hidden = true;
    $("ctx-result").hidden = false;

    var ms = Number((id >> 22n) + EPOCH);
    var sec = Math.floor(ms / 1000);
    var r = parseInt($("ctx-range").value, 10);

    var jst = new Date(ms + 9 * 3600 * 1000);
    $("ctx-time").textContent =
      jst.getUTCFullYear() + "-" + pad(jst.getUTCMonth() + 1) + "-" + pad(jst.getUTCDate()) +
      " " + pad(jst.getUTCHours()) + ":" + pad(jst.getUTCMinutes()) + ":" + pad(jst.getUTCSeconds());

    var q = "from:" + user + " since_time:" + (sec - r) + " until_time:" + (sec + r);
    if ($("ctx-noreply").checked) q += " -filter:replies";
    if ($("ctx-noquote").checked) q += " -filter:quote";
    if ($("ctx-withrt").checked) q += " include:nativeretweets";

    $("ctx-cmd").textContent = q;
    $("ctx-open").href = "https://x.com/search?q=" + encodeURIComponent(q) + "&f=live";
  }

  ["ctx-url", "ctx-range", "ctx-noreply", "ctx-noquote", "ctx-withrt"].forEach(function (id) {
    $(id).addEventListener(id === "ctx-url" ? "input" : "change", update);
  });

  $("ctx-copy").addEventListener("click", function () {
    navigator.clipboard.writeText($("ctx-cmd").textContent);
    $("ctx-copy").textContent = "コピーしました";
    setTimeout(function () { $("ctx-copy").textContent = "コピー"; }, 1500);
  });

  update();
})();
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/footer.php'; ?>
