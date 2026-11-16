<?php
// Copy file này thành config.php rồi điền mật khẩu DB thật của bạn.
// config.php đã nằm trong .gitignore, sẽ không bị đẩy lên git.

ini_set('session.cookie_httponly', 0);
session_start();

$DB_HOST = "localhost";
$DB_NAME = "blogs";
$DB_USER = "userapp";
$DB_PASS = "password";

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi hệ thống, vui lòng thử lại sau.");
}
