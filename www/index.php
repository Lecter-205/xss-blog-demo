<?php
require 'config.php';
$page_title = "Trang chủ";

$categoryLabels = [
  'cong-nghe'  => 'Công nghệ',
  'kinh-te'    => 'Kinh tế',
  'tai-chinh'  => 'Tài chính',
  'y-te'       => 'Y tế',
  'giao-duc'   => 'Giáo dục',
  'the-thao'   => 'Thể thao',
  'moi-truong' => 'Môi trường',
  'phap-luat'  => 'Pháp luật',
];

$availableCategories = $pdo->query("SELECT DISTINCT category FROM posts")->fetchAll(PDO::FETCH_COLUMN);

include 'header.php';
?>

<div class="hero">
  <h1>Chào mừng đến với TechViet Blog</h1>
  <p>Kiến thức công nghệ, mẹo vặt văn phòng và trải nghiệm thực tế - cập nhật mỗi tuần.</p>
</div>

<div class="latest-heading">
  <h2 id="about" style="margin-bottom:0;">Bài viết mới nhất</h2>
  <div class="filter-bar" aria-label="Lọc bài viết theo lĩnh vực">
    <label for="category-filter">Lĩnh vực:</label>
    <select class="filter-select" id="category-filter" name="category">
      <option value="">Tất cả</option>
      <?php foreach ($categoryLabels as $key => $label): ?>
        <?php if (in_array($key, $availableCategories, true)): ?>
          <option value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<p class="filter-status" id="filter-status">Hiển thị tất cả bài viết</p>

<?php
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
foreach ($posts as $post):
    $catKey = $post['category'];
    $catLabel = isset($categoryLabels[$catKey]) ? $categoryLabels[$catKey] : $catKey;
?>
  <article class="post-card" data-category="<?php echo htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8'); ?>">
    <span class="post-category"><?php echo htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8'); ?></span>
    <h2><a href="post.php?slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
    <div class="post-meta">Tác giả <?php echo htmlspecialchars($post['author']); ?> · <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></div>
    <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
    <a class="read-more" href="post.php?slug=<?php echo urlencode($post['slug']); ?>">Đọc tiếp &rarr;</a>
  </article>
<?php endforeach; ?>

<script>
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


  // // 1) Whitelist: chỉ nhận các lĩnh vực có thật trong <select>
  // var allowed = [];
  // if (select) {
  //   for (var k = 0; k < select.options.length; k++) {
  //     if (select.options[k].value) allowed.push(select.options[k].value);
  //   }
  // }
  // if (allowed.indexOf(category) === -1) category = '';

  // if (select) {
  //   select.value = category;
  //   select.addEventListener('change', function () {
  //     window.location.href = this.value
  //       ? 'index.php?category=' + encodeURIComponent(this.value)
  //       : 'index.php';
  //   });
  // }

  // // 2) Ghi nhãn bằng textContent / text node, KHÔNG dùng innerHTML
  // status.textContent = '';
  // if (!category) {
  //   status.textContent = 'Hiển thị tất cả bài viết';
  //   return;
  // }
  // status.appendChild(document.createTextNode('Đang lọc theo lĩnh vực: '));
  // var label = document.createElement('strong');
  // var opt = select.querySelector('option[value="' + category + '"]');
  // label.textContent = opt ? opt.textContent : category;
  // status.appendChild(label);

  // // 3) Lọc bằng thuộc tính hidden
  // var cards = document.querySelectorAll('.post-card[data-category]');
  // for (var i = 0; i < cards.length; i++) {
  //   cards[i].hidden = cards[i].getAttribute('data-category') !== category;
  // }

})();
</script>

<?php include 'footer.php'; ?>
