<?php
// config/db.php - Database connection helper & Secure Session Configuration

if (session_status() === PHP_SESSION_NONE) {
    // Prevent JavaScript from accessing session cookies (XSS protection)
    ini_set('session.cookie_httponly', 1);
    // Mandate cookies for session handling to prevent session ID in URL
    ini_set('session.use_only_cookies', 1);
    // Use secure cookies only when HTTPS is enabled
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    // Set strict SameSite attribute to prevent CSRF on cookie transfers
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

$host = 'localhost';
$db   = 'mechaforge_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}
?>
