<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo isset($page_title) ? $page_title . ' - TechViet Blog' : 'TechViet Blog'; ?></title>
<link rel="stylesheet" href="/blog/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap">
    <a href="/blog/index.php" class="logo">TechViet<span>Blog</span></a>
    <nav>
      <a href="/blog/index.php">Trang chủ</a>
      <a href="/blog/index.php#about">Giới thiệu</a>
      <a href="/blog/index.php#contact">Liên hệ</a>
    </nav>
    <form class="search-form" action="/blog/search.php" method="GET">
      <input type="text" name="q" placeholder="Tìm bài viết..." required>
      <button type="submit">Tìm</button>
    </form>
    <div class="auth-area">
      <?php if (isset($_SESSION['username'])): ?>
        <a href="/blog/profile.php">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="/blog/logout.php">Đăng xuất</a>
      <?php else: ?>
        <a href="/blog/login.php">Đăng nhập</a>
        <a href="/blog/register.php">Đăng ký</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="wrap content">
