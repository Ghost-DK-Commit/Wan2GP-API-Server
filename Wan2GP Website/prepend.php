<?php
if (defined('DB_HOST')) return;

define('DB_HOST', 'localhost');
define('DB_NAME', 'suno8');
define('DB_USER', 'root');
define('DB_PASS', '');

$is_ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
define('SESSION_SECURE', $is_ssl);
define('BASE_URL', '/suno8');
define('CSRF_SECRET', 'a7c3e2f1b8d4c6a9e5f2d7b3c8a1e6f4d9b5c2a8e3f7d1b4c9a6e2f8d5b3c7a1');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => $is_ssl,
        'cookie_samesite' => 'Lax',
    ]);
}

function getDB() {
    global $pdo;
    return $pdo;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUser() {
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
