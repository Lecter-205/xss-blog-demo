<?php
require 'config.php';
$page_title = "Đăng nhập";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: profile.php");
        exit;
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu.";
    }
}

include 'header.php';
?>
<div class="post-detail" style="max-width:420px;margin:0 auto;">
  <h1>Đăng nhập</h1>
  <?php if ($error): ?><p style="color:#dc2626;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
  <form method="POST" class="comment-form">
    <input type="text" name="username" placeholder="Tên đăng nhập" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <button type="submit">Đăng nhập</button>
  </form>
  <p style="margin-top:14px;font-size:14px;">Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
</div>
<?php include 'footer.php'; ?>
