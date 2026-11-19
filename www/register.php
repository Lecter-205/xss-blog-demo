<?php
require 'config.php';
$page_title = "Đăng ký";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = "Tên đăng nhập đã tồn tại.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $email]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            header("Location: profile.php");
            exit;
        }
    }
}

include 'header.php';
?>
<!-- <div class="post-detail" style="max-width:420px;margin:0 auto;"> -->
<div class="post-detail card-narrow">
  <h1>Đăng ký tài khoản</h1>
  <?php if ($error): ?><p class="error-text"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
  <form method="POST" class="comment-form">
    <input type="text" name="username" placeholder="Tên đăng nhập" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <button type="submit">Đăng ký</button>
  </form>
  <p class="form-note">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>
<?php include 'footer.php'; ?>
