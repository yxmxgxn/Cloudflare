<?php
// 誤りの報告・追加のご要望フォーム(匿名・Turnstile)。/api/feedback に送る。
// common/footer.php と、専用のヘッダー・フッターを持つページ(hello-kenshusei-blog.php)から読み込む。
// 見た目は css/feedback.css。
?>
<?php
  // Turnstile のサイトキー(公開鍵なのでHTMLに出て問題ない)。
  // 空のままだと Bot 対策なしでフォームだけ動く。
  $turnstile_sitekey = '0x4AAAAAAEQYtZkFWnqAaw6I';
?>
<div class="fb-area">
<details class="fb">
  <summary>誤りの報告・追加のご要望</summary>
  <div class="fb-body">
    <p class="fb-note">匿名で送れます。お返事はしませんが、すべて読んでいます。<br>
      表の誤りや抜け、追加してほしい項目、サイトへのご要望など、細かいことでもお気軽にどうぞ。</p>
    <div class="fb-kinds">
      <button type="button" class="fb-kind" data-kind="error">誤り</button>
      <button type="button" class="fb-kind" data-kind="add">追加希望</button>
      <button type="button" class="fb-kind" data-kind="other">その他</button>
    </div>
    <textarea class="fb-text" rows="4" maxlength="2000" placeholder="例）3行目の日付が違うようです"></textarea>
    <div class="fb-turnstile"></div>
    <p class="fb-actions">
      <button type="button" class="fb-send">送信</button>
      <span class="fb-msg"></span>
    </p>
  </div>
</details>
</div>

<script>
(function () {
  var root = document.querySelector('.fb');
  if (!root) return;

  var sitekey = <?php echo json_encode($turnstile_sitekey); ?>;
  var widgetId = null, scriptAdded = false, kind = null;
  var ta = root.querySelector('.fb-text');
  var msg = root.querySelector('.fb-msg');
  var sendBtn = root.querySelector('.fb-send');

  function resetTurnstile() {
    if (sitekey && window.turnstile && widgetId !== null) turnstile.reset(widgetId);
  }

  // Turnstile は開いたときに初めて読み込む(全ページの初期表示を重くしない)
  root.addEventListener('toggle', function () {
    if (!root.open || scriptAdded || !sitekey) return;
    scriptAdded = true;
    var s = document.createElement('script');
    s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=fbTurnstileReady';
    s.async = true;
    s.defer = true;
    document.head.appendChild(s);
  });

  window.fbTurnstileReady = function () {
    widgetId = turnstile.render(root.querySelector('.fb-turnstile'), { sitekey: sitekey });
  };

  root.querySelectorAll('.fb-kind').forEach(function (btn) {
    btn.addEventListener('click', function () {
      kind = (kind === btn.dataset.kind) ? null : btn.dataset.kind;  // もう一度押すと解除
      root.querySelectorAll('.fb-kind').forEach(function (o) {
        o.classList.toggle('is-on', o.dataset.kind === kind);
      });
    });
  });

  sendBtn.addEventListener('click', function () {
    var body = ta.value.trim();
    if (!body) { msg.textContent = '内容を入力してください'; return; }

    sendBtn.disabled = true;
    msg.textContent = '送信中…';

    var token = '';
    if (sitekey && window.turnstile && widgetId !== null) {
      token = turnstile.getResponse(widgetId) || '';
    }

    fetch('/api/feedback', {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify({ body: body, kind: kind, page: location.pathname, token: token })
    }).then(function (r) {
      if (r.ok) {
        root.querySelector('.fb-body').innerHTML =
          '<p class="fb-thanks">送信しました。ありがとうございます。</p>';
        return null;
      }
      return r.json().catch(function () { return {}; });
    }).then(function (d) {
      if (!d) return;
      msg.textContent = d.error === 'rate'
        ? '短い間に送りすぎのようです。少し時間をおいてください'
        : d.error === 'turnstile'   // 開いた直後で Turnstile がまだ読み込めていない
        ? '確認中です。少し待ってからもう一度送信してください'
        : '送信できませんでした。時間をおいて試してください';
      sendBtn.disabled = false;
      resetTurnstile();
    }).catch(function () {
      msg.textContent = '送信できませんでした。通信環境をご確認ください';
      sendBtn.disabled = false;
      resetTurnstile();
    });
  });
})();
</script>
