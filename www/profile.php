<?php
require 'config.php';
$page_title = "Hồ sơ cá nhân";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include 'header.php';
?>
<div class="post-detail" style="max-width:500px;margin:0 auto;">
  <h1>Hồ sơ cá nhân</h1>
  <p><b>Tên đăng nhập:</b> <?php echo htmlspecialchars($user['username']); ?></p>
  <p><b>Email:</b> <?php echo htmlspecialchars($user['email']); ?></p>
  <p><b>Thành viên từ:</b> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
  <p style="margin-top:20px;"><a href="logout.php">Đăng xuất</a></p>
</div>
<?php include 'footer.php'; ?>
