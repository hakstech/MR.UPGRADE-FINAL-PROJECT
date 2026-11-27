<?php
// ============================================================
// EXAMPLE CONFIG FILE — copy this to db.php and fill in values
// DO NOT commit db.php to Git — it contains real credentials
// ============================================================

if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'mrupgrade.mmuhub.com') {
    // ---- LIVE SERVER ----
    define('DB_HOST',  'localhost');
    define('DB_USER',  'YOUR_CPANEL_DB_USER');
    define('DB_PASS',  'YOUR_CPANEL_DB_PASSWORD');
    define('DB_NAME',  'YOUR_CPANEL_DB_NAME');
    define('SITE_URL', 'https://mrupgrade.mmuhub.com');
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} else {
    // ---- LOCAL (WAMP) ----
    define('DB_HOST',  '127.0.0.1');
    define('DB_PORT',  3308);
    define('DB_USER',  'root');
    define('DB_PASS',  '');
    define('DB_NAME',  'mrupgrade');
    define('SITE_URL', 'http://localhost/mrupgrade');
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
}
