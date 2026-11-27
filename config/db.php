<?php
ob_start();
require_once __DIR__ . '/security.php';

$is_live = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'mrupgrade.mmuhub.com');

if ($is_live) {
    // ---- LIVE (mrupgrade.mmuhub.com) ----
    define('DB_HOST',  'localhost');               
    define('DB_USER',  'mmuhpwvx_mrupgrade');     
    define('DB_PASS',  'MMUhubUpgrade@2026'); 
    define('DB_NAME',  'mmuhpwvx_mrupgraddatabase');     
    define('SITE_URL', 'https://mrupgrade.mmuhub.com');
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} else {
    // ---- LOCAL (WAMP — localhost) ----
    define('DB_HOST',  '127.0.0.1');
    define('DB_PORT',  3308);
    define('DB_USER',  'root');
    define('DB_PASS',  '');
    define('DB_NAME',  'mrupgrade');
    define('SITE_URL', 'http://localhost/mrupgrade');
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
}

define('SITE_NAME', 'Mr. Upgrade');
define('CURRENCY',  'UGX');

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:red;">
         <h2>Database Connection Failed</h2>
         <p>' . htmlspecialchars($conn->connect_error) . '</p>
         <p>Make sure WAMP is running and the database <strong>' . DB_NAME . '</strong> exists.</p>
         </div>');
}
$conn->set_charset('utf8mb4');

setSecurityHeaders();

secureSession();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
function requireLogin() {
    if (!isLoggedIn()) {
        ob_end_clean();
        header('Location: ' . SITE_URL . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}
function requireAdmin() {
    if (!isAdmin()) {
        ob_end_clean();
        header('Location: ' . SITE_URL . '/');
        exit;
    }
}
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}
function formatPrice($amount) {
    return CURRENCY . ' ' . number_format($amount);
}

function productImageUrl($image) {
    if (empty($image)) return SITE_URL . '/assets/images/favicon.png';
    if (strpos($image, 'uploads/') === 0) return SITE_URL . '/' . $image;
    return SITE_URL . '/assets/images/' . $image;
}
