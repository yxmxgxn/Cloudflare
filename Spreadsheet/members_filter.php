<!-- フィルタUI -->
<div id="hp-filter">
  <div class="filter-group">
    <span class="filter-label">現役／卒業</span>
    <button class="filter-btn active" data-type="all">全員</button>
    <button class="filter-btn" data-type="status" data-value="active">現役</button>
    <button class="filter-btn" data-type="status" data-value="graduated">卒業済み</button>
  </div>
  <div class="filter-group">
    <span class="filter-label">現役グループ</span>
    <button class="filter-btn" data-type="group" data-value="モーニング娘。">モーニング娘。</button>
    <button class="filter-btn" data-type="group" data-value="アンジュルム">アンジュルム</button>
    <button class="filter-btn" data-type="group" data-value="Juice=Juice">Juice=Juice</button>
    <button class="filter-btn" data-type="group" data-value="つばきファクトリー">つばきファクトリー</button>
    <button class="filter-btn" data-type="group" data-value="BEYOOOOONDS">BEYOOOOONDS</button>
    <button class="filter-btn" data-type="group" data-value="OCHA NORMA">OCHA NORMA</button>
    <button class="filter-btn" data-type="group" data-value="ロージークロニクル">ロージークロニクル</button>
  </div>
  <div class="filter-group">
    <span class="filter-label">解散・活動休止・改名</span>
    <button class="filter-btn" data-type="group" data-value="太陽とシスコムーン">太陽とシスコムーン</button>
    <button class="filter-btn" data-type="group" data-value="ココナッツ娘。">ココナッツ娘。</button>
    <button class="filter-btn" data-type="group" data-value="カントリー">カントリー娘。 / カントリー・ガールズ</button>
    <button class="filter-btn" data-type="group" data-value="メロン記念日">メロン記念日</button>
    <button class="filter-btn" data-type="group" data-value="Berryz工房">Berryz工房</button>
    <button class="filter-btn" data-type="group" data-value="℃-ute">℃-ute</button>
    <button class="filter-btn" data-type="group" data-value="スマイレージ">スマイレージ</button>
    <button class="filter-btn" data-type="group" data-value="こぶしファクトリー">こぶしファクトリー</button>
　</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const btns = document.querySelectorAll('.filter-btn');
  const rows = document.querySelectorAll('#hp-members tbody tr');

  // グループ列のインデックス（0始まり）: 名前=0, グループ=1
  const GROUP_COL = 1;

  function resetActive() {
    btns.forEach(b => b.classList.remove('active'));
  }

  function showAll() {
    rows.forEach(r => r.style.display = '');
  }

  btns.forEach(btn => {
    btn.addEventListener('click', function () {
      const type  = this.dataset.type;
      const value = this.dataset.value;

      resetActive();
      this.classList.add('active');

      if (type === 'all') {
        showAll();
        return;
      }

      if (type === 'status') {
        rows.forEach(r => {
          const isActive = r.classList.contains('active-member');
          r.style.display = (value === 'active' ? isActive : !isActive) ? '' : 'none';
        });
        return;
      }

      if (type === 'group') {
        rows.forEach(r => {
          const cells = r.querySelectorAll('td');
          if (!cells.length) return;
          const group = cells[GROUP_COL].textContent;
          r.style.display = group.includes(value) ? '' : 'none';
        });
      }
    });
  });
});
</script>
