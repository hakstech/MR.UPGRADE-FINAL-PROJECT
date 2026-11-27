<?php
// ---- Session Hardening ----
function secureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha256');
        session_start();
    }
    if (!isset($_SESSION['_last_regen'])) {
        session_regenerate_id(true);
        $_SESSION['_last_regen'] = time();
    } elseif (time() - $_SESSION['_last_regen'] > 300) {
        session_regenerate_id(true);
        $_SESSION['_last_regen'] = time();
    }
}

// ---- Security Headers ----
function setSecurityHeaders() {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://api.pesapal.com https://cybqa.pesapal.com;");
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token = null) {
    $submitted = $token ?? ($_POST['csrf_token'] ?? '');
    if (empty($submitted) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $submitted);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '"/>';
}

function checkLoginRateLimit($email, $conn) {
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $window  = 15 * 60; // 15 minutes
    $max_att = 5;        // max attempts

    $conn->query("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");

    // Count recent attempts for this IP or email
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM login_attempts WHERE (ip_address=? OR email=?) AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param('ss', $ip, $email);
    $stmt->execute();
    $cnt = (int)$stmt->get_result()->fetch_assoc()['cnt'];

    return $cnt >= $max_att; 
}

function recordLoginAttempt($email, $success, $conn) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if ($success) {
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address=? OR email=?");
        $stmt->bind_param('ss', $ip, $email);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?,?)");
        $stmt->bind_param('ss', $ip, $email);
        $stmt->execute();
    }
}

function sanitizeString($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function sanitizeInt($val) {
    return (int)$val;
}

function sanitizeEmail($val) {
    return filter_var(trim($val), FILTER_SANITIZE_EMAIL);
}

function sanitizeFloat($val) {
    return (float)filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function validateUploadedImage($file) {
    $allowed_types = array('image/jpeg','image/png','image/webp');
    $allowed_exts  = array('jpg','jpeg','png','webp');
    $max_size      = 20 * 1024 * 1024; // 20MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('ok'=>false,'msg'=>'Upload error code: ' . $file['error']);
    }
    if ($file['size'] > $max_size) {
        return array('ok'=>false,'msg'=>'File too large. Maximum 20MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_types)) {
        return array('ok'=>false,'msg'=>'Invalid file type. Only JPG, PNG, WebP allowed.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) {
        return array('ok'=>false,'msg'=>'Invalid file extension.');
    }

    $img_info = @getimagesize($file['tmp_name']);
    if ($img_info === false) {
        return array('ok'=>false,'msg'=>'File is not a valid image.');
    }

    return array('ok'=>true,'mime'=>$mime,'ext'=>$ext);
}

function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
