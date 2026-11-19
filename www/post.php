<?php
require 'config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $page_title = "Không tìm thấy bài viết";
    include 'header.php';
    echo "<p>Bài viết không tồn tại.</p>";
    include 'footer.php';
    exit;
}

$page_title = $post['title'];
include 'header.php';

// Xử lý gửi bình luận mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, name, comment) VALUES (?, ?, ?)");
    $stmt->execute([$post['id'], $_POST['name'], $_POST['comment']]);
    header("Location: post.php?slug=" . urlencode($slug) . "#comments");
    exit;
}

$cstmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
$cstmt->execute([$post['id']]);
$comments = $cstmt->fetchAll();
?>

<article class="post-detail">
  <h1><?php echo htmlspecialchars($post['title']); ?></h1>
  <div class="post-meta">Tác giả <?php echo htmlspecialchars($post['author']); ?> · <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></div>
  <div class="post-content" id="post-content"><?php echo htmlspecialchars($post['content']); ?></div>
</article>

<section class="comments-section" id="comments">
  <h3>Bình luận (<?php echo count($comments); ?>)</h3>

  <?php foreach ($comments as $c): ?>
    <div class="comment-item">
      <?php
      //echo '<span class="comment-name">' . $c['name'] . '</span>';
      echo '<span class="comment-name">' . htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') . '</span>';
      echo '<span class="comment-date">' . date('d/m/Y H:i', strtotime($c['created_at'])) . '</span>';
      //echo '<div class="comment-body">' . $c['comment'] . '</div>';
      echo '<div class="comment-body">' . htmlspecialchars($c['comment'], ENT_QUOTES, 'UTF-8') . '</div>';
      ?>
    </div>
  <?php endforeach; ?>

  <!-- <form class="comment-form" method="POST" style="margin-top:20px;"> -->
  <form class="comment-form mt-20" method="POST">
    <input type="text" name="name" placeholder="Tên của bạn" required>
    <textarea name="comment" rows="3" placeholder="Viết bình luận..." required></textarea>
    <button type="submit">Gửi bình luận</button>
  </form>
</section>

<?php include 'footer.php'; ?>
