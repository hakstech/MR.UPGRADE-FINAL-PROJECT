<?php
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? SITE_URL.'/admin' : SITE_URL.'/'));
    exit;
}

$error    = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL . '/';

$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email    = sanitizeEmail($_POST['email']    ?? '');
        $password = $_POST['password']               ?? '';

        if (!$email || !$password) {
            $error = 'Please enter your email and password.';
        } elseif (checkLoginRateLimit($email, $conn)) {
            $error = 'Too many failed attempts. Please wait 15 minutes before trying again.';
        } else {
            $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                recordLoginAttempt($email, true, $conn);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                if ($user['role'] === 'admin') {
                    header('Location: ' . SITE_URL . '/admin'); exit;
                }
                $redir = filter_var($_POST['redirect'] ?? $redirect, FILTER_SANITIZE_URL);
                header('Location: ' . $redir); exit;
            } else {
                recordLoginAttempt($email, false, $conn);
                $error = 'Incorrect email or password. Please try again.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Sign In | Mr. Upgrade</title>
  <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #eff6ff 0%, #f1f5f9 50%, #fff7ed 100%);
      padding: 1.5rem;
    }

    .back-home {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      color: #475569;
      font-size: .875rem;
      font-weight: 600;
      text-decoration: none;
      margin-bottom: 1rem;
      align-self: flex-start;
      padding: .4rem .2rem;
      transition: color .2s;
    }
    .back-home:hover { color: #2563eb; }
    .back-home i { font-size: .85rem; }
    .auth-card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 20px;
      padding: 2.5rem;
      box-shadow: 0 10px 50px rgba(0,0,0,.1);
      border: 1px solid #e2e8f0;
    }
    .auth-logo { text-align: center; margin-bottom: 2rem; }
    .auth-logo-banner {
      max-width: 220px; height: auto;
      display: block; margin: 0 auto;
      mix-blend-mode: multiply;
    }

    .auth-card h1 { font-size: 1.5rem; font-weight: 800;  margin-bottom: .3rem; text-align: center; }
    .auth-sub { color: #64748b; font-size: .875rem; text-align: center; margin-bottom: 1.5rem; }
    .auth-sub a { color: #2563eb; font-weight: 700; text-decoration: none; }
    .auth-sub a:hover { text-decoration: underline; }

    .alert {
      padding: .85rem 1rem; border-radius: 10px;
      margin-bottom: 1.1rem; font-size: .85rem; font-weight: 500;
      display: flex; align-items: flex-start; gap: .6rem;
    }
    .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
    .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; }

    .form-group { margin-bottom: 1.1rem; }
    .form-group label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: .4rem; color: #334155; }
    .form-group input {
      width: 100%; background: #f8fafc;
      border: 1.5px solid #e2e8f0; border-radius: 9px;
      color: #0f172a; padding: .75rem 1rem;
      font-size: .9rem; outline: none; transition: .2s;
      font-family: 'Inter', sans-serif;
    }
    .form-group input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); background: #fff; }
    .input-wrap { position: relative; }
    .input-wrap input { padding-right: 2.75rem; }
    .toggle-pwd {
      position: absolute; right: .85rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: #94a3b8; cursor: pointer; font-size: .9rem; padding: 0;
    }
    .toggle-pwd:hover { color: #2563eb; }
    .btn-submit {
      width: 100%; background: #2563eb; color: #fff;
      border: none; border-radius: 10px; padding: .85rem;
      font-size: .95rem; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
      transition: .2s; font-family: 'Inter', sans-serif;
      box-shadow: 0 2px 10px rgba(37,99,235,.3); margin-top: .25rem;
    }
    .btn-submit:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(37,99,235,.4); }
     .auth-divider {
      display: flex; align-items: center; gap: 0;
      margin: 1.25rem 0; color: #94a3b8; font-size: .78rem; font-weight: 600;
    }
    .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
    .auth-foot { text-align: center; margin-top: 1.5rem; font-size: .875rem; color: #64748b; }
    .auth-foot a { color: #2563eb; font-weight: 700; text-decoration: none; }
  </style>
</head>
<body>

  <!-- Wrapper: back link + card in one column, centered together -->
  <div style="width:100%;max-width:420px;">
    <a href="<?= SITE_URL ?>/" class="back-home">
      <i class="fa-solid fa-arrow-left"></i> Back to Home
    </a>
    <div class="auth-card">
    <div class="auth-logo">
      <img src="<?= SITE_URL ?>/assets/images/logo-dark.png"  alt="Mr. Upgrade" class="auth-logo-banner"/>
    </div>
    <h1 style="margin-bottom:30px;">Welcome <span style="color:#2563eb;">Back</span></h1>

    <div class="auth-divider"></div>

    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Email form -->
    <form method="POST" novalidate>
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>"/>
      <?= csrfField() ?>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="example@gmail.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="pwd" placeholder="Your password" required/>
          <button type="button" class="toggle-pwd" onclick="togglePwd('pwd','eyePwd')">
            <i class="fa-solid fa-eye" id="eyePwd"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In
      </button>
    </form>

    <div class="auth-foot">
      Don't have an account. <a href="<?= SITE_URL ?>/register">Create one</a>
    </div>

  </div><!-- /auth-card -->
  </div><!-- /wrapper -->

<script>
function togglePwd(id, eyeId) {
  var inp = document.getElementById(id);
  var eye = document.getElementById(eyeId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}
</script>
</body>
</html>
