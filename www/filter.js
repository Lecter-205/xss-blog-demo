(function () {

  var params = new URLSearchParams(window.location.search);
  var category = params.get('category');
  var select = document.getElementById('category-filter');
  var status = document.getElementById('filter-status');

  // Chọn option tương ứng khi chuyển trang kèm ?category=...
  if (select) {
    if (category) select.value = category; // gán thuộc tính DOM, không tạo markup
    select.addEventListener('change', function () {
      window.location.href = this.value
        ? 'index.php?category=' + encodeURIComponent(this.value)
        : 'index.php';
    });
  }

  if (!category) return;

  // Giá trị đọc từ URL được ghi thẳng vào innerHTML -> DOM-based XSS
  status.innerHTML = 'Đang lọc theo lĩnh vực: <strong>' + category + '</strong>';

  var cards = document.querySelectorAll('.post-card[data-category]');
  for (var i = 0; i < cards.length; i++) {
    cards[i].hidden = cards[i].getAttribute('data-category') !== category;
  }
})();
