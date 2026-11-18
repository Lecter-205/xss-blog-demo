(function () {
  var params = new URLSearchParams(window.location.search);
  var category = params.get('category');
  var select = document.getElementById('category-filter');
  var status = document.getElementById('filter-status');

  // 1) Whitelist: chỉ nhận các lĩnh vực có thật trong <select>
  var allowed = [];
  if (select) {
    for (var k = 0; k < select.options.length; k++) {
      if (select.options[k].value) allowed.push(select.options[k].value);
    }
  }
  if (allowed.indexOf(category) === -1) category = '';

  if (select) {
    select.value = category;
    select.addEventListener('change', function () {
      window.location.href = this.value
        ? 'index.php?category=' + encodeURIComponent(this.value)
        : 'index.php';
    });
  }

  // 2) Ghi nhãn bằng textContent / text node, KHÔNG dùng innerHTML
  status.textContent = '';
  if (!category) {
    status.textContent = 'Hiển thị tất cả bài viết';
    return;
  }
  status.appendChild(document.createTextNode('Đang lọc theo lĩnh vực: '));
  var label = document.createElement('strong');
  var opt = select.querySelector('option[value="' + category + '"]');
  label.textContent = opt ? opt.textContent : category;
  status.appendChild(label);

  // 3) Lọc bằng thuộc tính hidden
  var cards = document.querySelectorAll('.post-card[data-category]');
  for (var i = 0; i < cards.length; i++) {
    cards[i].hidden = cards[i].getAttribute('data-category') !== category;
  }

})();
