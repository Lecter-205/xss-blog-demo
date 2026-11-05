<?php
require 'config.php';
$page_title = "Trang chủ";
include 'header.php';
?>

<div class="hero">
  <h1>Chào mừng đến với TechViet Blog</h1>
  <p>Kiến thức công nghệ, mẹo vặt văn phòng và trải nghiệm thực tế - cập nhật mỗi tuần.</p>
</div>

<h2 id="about" style="margin-bottom:16px;">Bài viết mới nhất</h2>

<?php
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
foreach ($posts as $post):
?>
  <article class="post-card">
    <h2><a href="post.php?slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
    <div class="post-meta">Tác giả <?php echo htmlspecialchars($post['author']); ?> · <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></div>
    <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
    <a class="read-more" href="post.php?slug=<?php echo urlencode($post['slug']); ?>">Đọc tiếp &rarr;</a>
  </article>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
