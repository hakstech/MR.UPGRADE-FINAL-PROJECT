<?php
$page_title = "My Account";
require_once __DIR__ . '/../config/db.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$user_id = (int)$_SESSION['user_id'];

// Load current user
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success_info  = '';
$error_info    = '';
$success_pass  = '';
$error_pass    = '';
$active_tab    = isset($_POST['tab']) ? $_POST['tab'] : 'info';

// Flash messages from redirect
if (isset($_GET['updated']))  { $success_info = 'Your profile has been updated successfully.'; }
if (isset($_GET['pwdchanged'])){ $success_pass = 'Password changed successfully.'; $active_tab = 'password'; }

// ----------------------------------------------------------------
// Handle Profile Info Update
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $active_tab = 'info';
    if (!verifyCsrfToken()) {
        $error_info = 'Invalid request. Please try again.';
    } else {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!$name || !$email) {
            $error_info = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_info = 'Please enter a valid email address.';
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=?");
            $chk->bind_param('si', $email, $user_id);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error_info = 'That email is already in use by another account.';
            } else {
                $upd = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
                $upd->bind_param('sssi', $name, $email, $phone, $user_id);
                $upd->execute();
                $_SESSION['user_name']  = $name;
                $_SESSION['user_email'] = $email;
                header('Location: ' . SITE_URL . '/profile?updated=1');
                exit;
            }
        }
    }
}

// ----------------------------------------------------------------
// Handle Password Change
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $active_tab = 'password';
    if (!verifyCsrfToken()) {
        $error_pass = 'Invalid request. Please try again.';
    } else {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass     = $_POST['new_password']     ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';
        if (!$current_pass || !$new_pass || !$confirm_pass) {
            $error_pass = 'Please fill in all password fields.';
        } elseif (!password_verify($current_pass, $user['password'])) {
            $error_pass = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 6) {
            $error_pass = 'New password must be at least 6 characters.';
        } elseif ($new_pass !== $confirm_pass) {
            $error_pass = 'New passwords do not match.';
        } elseif ($new_pass === $current_pass) {
            $error_pass = 'New password must be different from your current password.';
        } else {
            $hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $upd  = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $upd->bind_param('si', $hash, $user_id);
            $upd->execute();
            header('Location: ' . SITE_URL . '/profile?pwdchanged=1');
            exit;
        }
    }
}
?>

<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>My Account</span>
    </div>
    <h1>My <span class="gradient-text">Account</span></h1>
    <p>Manage your profile, email and password</p>
  </div>
</div>

<div class="container" style="padding:3rem 1.25rem 5rem;max-width:900px">

  <!-- Profile header card -->
  <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#0f172a 100%);border-radius:var(--radius-lg);padding:2rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.5rem;color:#fff;border:1px solid rgba(37,99,235,.3)">
    <!-- Avatar with orange ring -->
    <div style="position:relative;flex-shrink:0">
      <div style="width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg,var(--orange),#f59e0b);border:4px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.9rem;font-weight:900;color:#fff;box-shadow:0 0 0 3px rgba(249,115,22,.4)">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
      </div>
      <!-- Online dot -->
      <div style="position:absolute;bottom:3px;right:3px;width:14px;height:14px;background:#22c55e;border-radius:50%;border:2px solid #0f172a"></div>
    </div>
    <div style="flex:1">
      <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:.3rem"><?= htmlspecialchars($user['name']) ?></h2>
      <p style="color:rgba(255,255,255,.6);font-size:.875rem;margin-bottom:.5rem"><?= htmlspecialchars($user['email']) ?></p>
      <?php if ($user['phone']): ?>
        <p style="color:rgba(255,255,255,.5);font-size:.8rem;margin-bottom:.5rem"><i class="fa-solid fa-phone" style="color:var(--orange);margin-right:.35rem"></i><?= htmlspecialchars($user['phone']) ?></p>
      <?php endif; ?>
      <!-- Role badge — orange -->
      <span style="background:linear-gradient(135deg,var(--orange),#f59e0b);padding:.25rem .9rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#fff">
        <?= $user['role'] === 'admin' ? '⚙ Admin' : '👤 Customer' ?>
      </span>
    </div>
    <!-- Member since — orange accent -->
    <div style="text-align:right;flex-shrink:0">
      <div style="font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem">Member since</div>
      <div style="font-weight:800;font-size:1rem;color:var(--orange)"><?= date('M Y', strtotime($user['created_at'])) ?></div>
    </div>
  </div>

  <!-- Tabs -->
  <div style="display:flex;gap:0;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:2rem;background:var(--bg-card)">
    <button onclick="switchTab('info')" id="tab-info"
      style="flex:1;padding:1rem;border:none;cursor:pointer;font-weight:600;font-size:.9rem;transition:.2s;border-right:1px solid var(--border)"
      class="profile-tab <?= $active_tab==='info' ? 'active-tab-blue' : '' ?>">
      <i class="fa-solid fa-user-pen"></i> Profile Info
    </button>
    <button onclick="switchTab('password')" id="tab-password"
      style="flex:1;padding:1rem;border:none;cursor:pointer;font-weight:600;font-size:.9rem;transition:.2s"
      class="profile-tab <?= $active_tab==='password' ? 'active-tab-orange' : '' ?>">
      <i class="fa-solid fa-lock"></i> Change Password
    </button>
  </div>

  <!-- TAB: Profile Info -->
  <div id="panel-info" style="<?= $active_tab==='password' ? 'display:none' : '' ?>">

    <?php if ($success_info): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem">
        <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success_info) ?>
      </div>
    <?php endif; ?>
    <?php if ($error_info): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_info) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="tab" value="info" />
      <div class="form-section">
        <h3 class="form-section-title"><i class="fa-solid fa-user"></i> Personal Information</h3>

        <div class="form-row">
          <div class="form-group">
            <label>Full Name <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required placeholder="Your full name"/>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+256 7XX XXX XXX"/>
          </div>
        </div>

        <div class="form-group">
          <label>Email Address <span style="color:var(--red)">*</span></label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="your@email.com"/>
          <small style="color:var(--text-muted);font-size:.78rem;margin-top:.3rem;display:block">
            <i class="fa-solid fa-info-circle"></i> Changing your email will update your login credentials.
          </small>
        </div>

        <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
          <button type="submit" name="update_info" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
          </button>
          <?php if (!isAdmin()): ?>
            <a href="<?= SITE_URL ?>/orders" class="btn btn-outline">
              <i class="fa-solid fa-box"></i> View My Orders
            </a>
          <?php else: ?>
            <a href="<?= SITE_URL ?>/admin" class="btn btn-outline">
              <i class="fa-solid fa-gauge"></i> Go to Dashboard
            </a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <!-- TAB: Change Password -->
  <div id="panel-password" style="<?= $active_tab==='info' ? 'display:none' : '' ?>">

    <?php if ($success_pass): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem">
        <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success_pass) ?>
      </div>
    <?php endif; ?>
    <?php if ($error_pass): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_pass) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="tab" value="password" />
      <div class="form-section">
        <h3 class="form-section-title"><i class="fa-solid fa-lock" style="color:var(--orange)"></i> Change Password</h3>

        <div class="form-group">
          <label>Current Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative">
            <input type="password" name="current_password" id="cur_pwd" placeholder="Enter your current password" required />
            <button type="button" onclick="togglePwd('cur_pwd','eye_cur')"
              style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.9rem;padding:0">
              <i class="fa-solid fa-eye" id="eye_cur"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label>New Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative">
            <input type="password" name="new_password" id="new_pwd" placeholder="Minimum 6 characters" required />
            <button type="button" onclick="togglePwd('new_pwd','eye_new')"
              style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.9rem;padding:0">
              <i class="fa-solid fa-eye" id="eye_new"></i>
            </button>
          </div>
          <!-- Password strength bar -->
          <div id="strengthBar" style="height:4px;border-radius:2px;margin-top:.5rem;background:var(--border);overflow:hidden">
            <div id="strengthFill" style="height:100%;width:0;border-radius:2px;transition:.3s"></div>
          </div>
          <small id="strengthLabel" style="font-size:.75rem;color:var(--text-muted)"></small>
        </div>

        <div class="form-group">
          <label>Confirm New Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative">
            <input type="password" name="confirm_password" id="conf_pwd" placeholder="Re-enter new password" required />
            <button type="button" onclick="togglePwd('conf_pwd','eye_conf')"
              style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.9rem;padding:0">
              <i class="fa-solid fa-eye" id="eye_conf"></i>
            </button>
          </div>
          <small id="matchLabel" style="font-size:.75rem;margin-top:.3rem;display:block"></small>
        </div>

        <!-- Requirements checklist -->
        <div style="background:var(--bg-section);border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:1.5rem">
          <p style="font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.5rem">Password must:</p>
          <div id="req-length"  class="pwd-req"><i class="fa-solid fa-circle fa-xs"></i> Be at least 6 characters</div>
          <div id="req-match"   class="pwd-req"><i class="fa-solid fa-circle fa-xs"></i> Match the confirmation field</div>
        </div>

        <button type="submit" name="update_password" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-key"></i> Change Password
        </button>
      </div>
    </form>
  </div>

</div>

<style>
.profile-tab {
  background: var(--bg-card);
  color: var(--text-muted);
}
.profile-tab:hover:not(.active-tab-blue):not(.active-tab-orange) {
  background: var(--bg-section);
  color: var(--text-primary);
}
/* Blue for Profile Info tab */
.active-tab-blue {
  background: var(--blue) !important;
  color: #fff !important;
}
/* Orange for Change Password tab */
.active-tab-orange {
  background: var(--orange) !important;
  color: #fff !important;
}
.pwd-req {
  font-size: .8rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: .2rem 0;
  transition: .2s;
}
.pwd-req.ok { color: var(--green); }
.pwd-req.ok i { color: var(--green); }
</style>

<script>
function switchTab(tab) {
  document.getElementById('panel-info').style.display     = tab === 'info'     ? 'block' : 'none';
  document.getElementById('panel-password').style.display = tab === 'password' ? 'block' : 'none';
  // Reset all tabs
  document.querySelectorAll('.profile-tab').forEach(function(b) {
    b.classList.remove('active-tab-blue', 'active-tab-orange');
  });
  // Apply correct colour
  if (tab === 'info') {
    document.getElementById('tab-info').classList.add('active-tab-blue');
  } else {
    document.getElementById('tab-password').classList.add('active-tab-orange');
  }
}

function togglePwd(inputId, eyeId) {
  var inp = document.getElementById(inputId);
  var eye = document.getElementById(eyeId);
  if (inp.type === 'password') {
    inp.type = 'text';
    eye.className = 'fa-solid fa-eye-slash';
  } else {
    inp.type = 'password';
    eye.className = 'fa-solid fa-eye';
  }
}

// Password strength
document.getElementById('new_pwd').addEventListener('input', function() {
  var val    = this.value;
  var fill   = document.getElementById('strengthFill');
  var label  = document.getElementById('strengthLabel');
  var reqLen = document.getElementById('req-length');
  var score  = 0;

  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  var colours = ['','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
  var labels  = ['','Very Weak','Weak','Fair','Strong','Very Strong'];
  var pct     = (score / 5) * 100;

  fill.style.width      = pct + '%';
  fill.style.background = colours[score] || '#e2e8f0';
  label.textContent     = val.length > 0 ? labels[score] : '';
  label.style.color     = colours[score] || 'var(--text-muted)';

  // Requirement: length
  if (val.length >= 6) {
    reqLen.classList.add('ok');
  } else {
    reqLen.classList.remove('ok');
  }
  checkMatch();
});

// Confirm match
document.getElementById('conf_pwd').addEventListener('input', checkMatch);

function checkMatch() {
  var np   = document.getElementById('new_pwd').value;
  var cp   = document.getElementById('conf_pwd').value;
  var ml   = document.getElementById('matchLabel');
  var reqM = document.getElementById('req-match');
  if (cp.length === 0) { ml.textContent = ''; reqM.classList.remove('ok'); return; }
  if (np === cp) {
    ml.textContent  = '✓ Passwords match';
    ml.style.color  = 'var(--green)';
    reqM.classList.add('ok');
  } else {
    ml.textContent  = '✗ Passwords do not match';
    ml.style.color  = 'var(--red)';
    reqM.classList.remove('ok');
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
