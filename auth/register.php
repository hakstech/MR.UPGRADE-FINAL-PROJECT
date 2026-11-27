<?php
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Invalid request. Please try again.';
    } else {
    $name     = sanitizeString($_POST['name']             ?? '');
    $email    = sanitizeEmail($_POST['email']             ?? '');
    $phone    = sanitizeString($_POST['phone']            ?? '');
    $password = $_POST['password']                        ?? '';
    $confirm  = $_POST['confirm_password']                ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
            $ins->bind_param('ssss', $name, $email, $phone, $hash);
            if ($ins->execute()) {
                $uid = $conn->insert_id;
                $_SESSION['user_id']    = $uid;
                $_SESSION['user_name']  = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = 'customer';
                $_SESSION['flash'] = array('type'=>'success','msg'=>'Welcome to Mr. Upgrade, '.htmlspecialchars(explode(' ',$name)[0]).'! Your account has been created.');
                header('Location: ' . SITE_URL . '/'); exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
    } // end csrf check
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Create Account | Mr. Upgrade</title>
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

    .auth-logo { text-align: center; margin-bottom: 1.75rem; }
    .auth-logo-banner {
      max-width: 220px; height: auto;
      display: block; margin: 0 auto;
      mix-blend-mode: multiply;
    }
    .auth-card h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: .3rem; text-align: center; }
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
    .auth-divider {
      display: flex; align-items: center; gap: 0;
      margin: 1.25rem 0; color: #94a3b8; font-size: .78rem; font-weight: 600;
    }
    .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
    .form-group { margin-bottom: 1rem; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
    @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }
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

    /* strength classes no longer used — replaced with inline styles to prevent layout shift */

    .btn-submit {
      width: 100%; background: #2563eb; color: #fff;
      border: none; border-radius: 10px; padding: .85rem;
      font-size: .95rem; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
      transition: .2s; font-family: 'Inter', sans-serif;
      box-shadow: 0 2px 10px rgba(37,99,235,.3); margin-top: .5rem;
    }
    .btn-submit:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(37,99,235,.4); }
    .auth-foot { text-align: center; margin-top: 1.25rem; font-size: .875rem; color: #64748b; }
    .auth-foot a { color: #2563eb; font-weight: 700; text-decoration: none; }

    .auth-terms { font-size: .73rem; color: #94a3b8; text-align: center; margin-top: .75rem; line-height: 1.5; }
  </style>
</head>
<body>

  <!-- Wrapper: back link + card in one column, centered together -->
  <div style="width:100%;max-width:440px;">
    <a href="<?= SITE_URL ?>/" class="back-home">
      <i class="fa-solid fa-arrow-left"></i> Back to Home
    </a>
    <div class="auth-card">
    <div class="auth-logo">
      <img src="<?= SITE_URL ?>/assets/images/logo-dark.png"  alt="Mr. Upgrade" class="auth-logo-banner"/>
    </div>

    <h1>Create Account</h1>
    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="auth-divider"></div>
    <form method="POST" novalidate>
      <?= csrfField() ?>
      <div class="form-row">
        <div class="form-group">
          <label>Full Name <span style="color:#dc2626">*</span></label>
          <input type="text" name="name" placeholder="John Mukasa" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required/>
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="+256 7XX XXX XXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Email Address <span style="color:#dc2626">*</span></label>
        <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
      </div>
      <div class="form-group">
        <label>Password <span style="color:#dc2626">*</span></label>
        <div class="input-wrap">
          <input type="password" name="password" id="pwd" placeholder="Min. 6 characters" required/>
          <button type="button" class="toggle-pwd" onclick="togglePwd('pwd','eyePwd')">
            <i class="fa-solid fa-eye" id="eyePwd"></i>
          </button>
        </div>
        <div style="height:4px;border-radius:2px;background:#e2e8f0;margin-top:.4rem;overflow:hidden">
          <div id="strengthFill" style="height:100%;border-radius:2px;transition:.3s;width:0"></div>
        </div>
        <div id="strengthLabel" style="font-size:.72rem;margin-top:.2rem;color:#94a3b8;min-height:1rem"></div>
      </div>
      <div class="form-group">
        <label>Confirm Password <span style="color:#dc2626">*</span></label>
        <div class="input-wrap">
          <input type="password" name="confirm_password" id="conf" placeholder="Re-enter password" required/>
          <button type="button" class="toggle-pwd" onclick="togglePwd('conf','eyeConf')">
            <i class="fa-solid fa-eye" id="eyeConf"></i>
          </button>
        </div>
        <div id="matchLabel" style="font-size:.72rem;margin-top:.2rem;min-height:1rem"></div>
      </div>
      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-user-plus"></i> Create My Account
      </button>
    </form>

    <p class="auth-terms">By creating an account you agree to our terms of service and privacy policy.</p>

    <div class="auth-foot">
      Already have an account? <a href="<?= SITE_URL ?>/login">Sign in</a>
    </div>

  </div>

<script>
function togglePwd(id, eyeId) {
  var inp = document.getElementById(id);
  var eye = document.getElementById(eyeId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}

document.getElementById('pwd').addEventListener('input', function() {
  var v = this.value, score = 0;
  if (v.length >= 6)           score++;
  if (v.length >= 10)          score++;
  if (/[A-Z]/.test(v))         score++;
  if (/[0-9]/.test(v))         score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  var colours = ['','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
  var labels  = ['','Very Weak','Weak','Fair','Strong','Very Strong'];
  var fill = document.getElementById('strengthFill');
  var lbl  = document.getElementById('strengthLabel');
  fill.style.width      = ((score/5)*100) + '%';
  fill.style.background = colours[score] || '#e2e8f0';
  lbl.textContent       = v.length ? labels[score] : '';
  lbl.style.color       = colours[score] || '#94a3b8';
  checkMatch();
});

document.getElementById('conf').addEventListener('input', checkMatch);

function checkMatch() {
  var p = document.getElementById('pwd').value;
  var c = document.getElementById('conf').value;
  var m = document.getElementById('matchLabel');
  if (!c.length) { m.textContent = ''; return; }
  if (p === c) { m.textContent = '✓ Passwords match'; m.style.color = '#16a34a'; }
  else         { m.textContent = '✗ Passwords do not match'; m.style.color = '#dc2626'; }
}
</script>
</body>
</html>
