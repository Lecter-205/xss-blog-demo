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

<script src="/blog/filter-secure.js"></script>

<?php include 'footer.php'; ?>
