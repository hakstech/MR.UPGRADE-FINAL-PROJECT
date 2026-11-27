<?php
ob_start();
require_once __DIR__ . '/../config/db.php';
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
ob_end_clean();
header('Location: ' . SITE_URL . '/');
exit;
