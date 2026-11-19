<?php

session_start();

// header("Content-Security-Policy: default-src 'self'; script-src 'self';");
// header("X-Content-Type-Options: nosniff");

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
