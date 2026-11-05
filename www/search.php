<?php
require 'config.php';
$page_title = "Kết quả tìm kiếm";
include 'header.php';

$q = $_GET['q'] ?? '';

// Truy vấn an toàn (prepared statement) - không có SQL Injection
$stmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE ? OR content LIKE ?");
$like = "%$q%";
$stmt->execute([$like, $like]);
$results = $stmt->fetchAll();
?>

<?php // Lưu ý nội bộ: $q được in trực tiếp bên dưới, không qua htmlspecialchars() ?>
<h2 class="search-results-heading">Kết quả tìm kiếm cho "<?php echo $q; ?>" (<?php echo count($results); ?> bài viết)</h2>

<?php if (empty($results)): ?>
  <p>Không tìm thấy bài viết nào phù hợp. Hãy thử từ khóa khác.</p>
<?php else: ?>
  <?php foreach ($results as $post): ?>
    <article class="post-card">
      <h2><a href="post.php?slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
      <div class="post-meta">Tác giả <?php echo htmlspecialchars($post['author']); ?> · <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></div>
      <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
    </article>
  <?php endforeach; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>
