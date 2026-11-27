<?php
$page_title = "My Account";
require_once __DIR__ . '/includes/admin_header.php';

$user_id = (int)$_SESSION['user_id'];

// Load current user
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success_info = '';
$error_info   = '';
$success_pass = '';
$error_pass   = '';
$active_tab   = isset($_POST['tab']) ? $_POST['tab'] : 'info';

// Flash messages from redirect
if (isset($_GET['updated']))   { $success_info = 'Your profile has been updated successfully.'; }
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
                header('Location: ' . SITE_URL . '/admin/profile?updated=1');
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
            header('Location: ' . SITE_URL . '/admin/profile?pwdchanged=1');
            exit;
        }
    }
}
?>

<!-- Profile Header Card -->
<div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#0f172a 100%);border-radius:var(--radius-lg);padding:1.75rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.5rem;color:#fff;border:1px solid rgba(37,99,235,.3)">
  <!-- Avatar with orange ring -->
  <div style="position:relative;flex-shrink:0">
    <div style="width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,var(--orange),#f59e0b);border:3px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:900;color:#fff;box-shadow:0 0 0 3px rgba(249,115,22,.35)">
      <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
    <div style="position:absolute;bottom:2px;right:2px;width:13px;height:13px;background:#22c55e;border-radius:50%;border:2px solid #0f172a"></div>
  </div>
  <div style="flex:1">
    <h2 style="font-size:1.25rem;font-weight:800;margin-bottom:.25rem"><?= htmlspecialchars($user['name']) ?></h2>
    <p style="color:rgba(255,255,255,.55);font-size:.85rem;margin-bottom:.4rem"><?= htmlspecialchars($user['email']) ?></p>
    <?php if ($user['phone']): ?>
      <p style="color:rgba(255,255,255,.45);font-size:.78rem;margin-bottom:.4rem">
        <i class="fa-solid fa-phone" style="color:var(--orange);margin-right:.35rem"></i><?= htmlspecialchars($user['phone']) ?>
      </p>
    <?php endif; ?>
    <span style="background:linear-gradient(135deg,var(--orange),#f59e0b);padding:.2rem .8rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#fff">
      ⚙ Administrator
    </span>
  </div>
  <div style="text-align:right;flex-shrink:0">
    <div style="font-size:.68rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem">Member since</div>
    <div style="font-weight:800;font-size:.95rem;color:var(--orange)"><?= date('M Y', strtotime($user['created_at'])) ?></div>
  </div>
</div>

<!-- Tabs -->
<div style="display:flex;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:2rem;background:var(--bg-card)">
  <button onclick="switchTab('info')" id="tab-info"
    style="flex:1;padding:.85rem;border:none;cursor:pointer;font-weight:600;font-size:.875rem;border-right:1px solid var(--border);transition:.2s"
    class="admin-profile-tab <?= $active_tab==='info' ? 'apt-blue' : '' ?>">
    <i class="fa-solid fa-user-pen"></i> Profile Info
  </button>
  <button onclick="switchTab('password')" id="tab-password"
    style="flex:1;padding:.85rem;border:none;cursor:pointer;font-weight:600;font-size:.875rem;transition:.2s"
    class="admin-profile-tab <?= $active_tab==='password' ? 'apt-orange' : '' ?>">
    <i class="fa-solid fa-lock"></i> Change Password
  </button>
</div>

<!-- TAB: Profile Info -->
<div id="panel-info" style="<?= $active_tab==='password' ? 'display:none' : '' ?>">

  <?php if ($success_info): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success_info) ?></div>
  <?php endif; ?>
  <?php if ($error_info): ?>
    <div class="alert alert-error" style="margin-bottom:1.5rem"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_info) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="tab" value="info"/>
    <div class="data-table-wrap" style="padding:1.75rem">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">
        <div class="form-group" style="margin:0">
          <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.45rem;color:var(--text-secondary)">Full Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                 style="width:100%;background:var(--bg-section);border:1.5px solid var(--border);border-radius:8px;color:var(--text-primary);padding:.75rem 1rem;font-size:.9rem;outline:none"/>
        </div>
        <div class="form-group" style="margin:0">
          <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.45rem;color:var(--text-secondary)">Phone Number</label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+256 7XX XXX XXX"
                 style="width:100%;background:var(--bg-section);border:1.5px solid var(--border);border-radius:8px;color:var(--text-primary);padding:.75rem 1rem;font-size:.9rem;outline:none"/>
        </div>
      </div>
      <div class="form-group" style="margin:0 0 1.5rem">
        <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.45rem;color:var(--text-secondary)">Email Address <span style="color:var(--red)">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
               style="width:100%;background:var(--bg-section);border:1.5px solid var(--border);border-radius:8px;color:var(--text-primary);padding:.75rem 1rem;font-size:.9rem;outline:none"/>
        <small style="color:var(--text-muted);font-size:.78rem;margin-top:.3rem;display:block">
          <i class="fa-solid fa-info-circle"></i> Changing email updates your login credentials.
        </small>
      </div>
      <button type="submit" name="update_info" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> Save Changes
      </button>
    </div>
  </form>
</div>

<!-- TAB: Change Password -->
<div id="panel-password" style="<?= $active_tab==='info' ? 'display:none' : '' ?>">

  <?php if ($success_pass): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success_pass) ?></div>
  <?php endif; ?>
  <?php if ($error_pass): ?>
    <div class="alert alert-error" style="margin-bottom:1.5rem"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_pass) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="tab" value="password"/>
    <div class="data-table-wrap" style="padding:1.75rem">

      <?php foreach([
        ['cur_pwd',  'eye_cur',  'current_password',  'Current Password'],
        ['new_pwd',  'eye_new',  'new_password',       'New Password'],
        ['conf_pwd', 'eye_conf', 'confirm_password',   'Confirm New Password'],
      ] as [$fid, $eid, $fname, $flabel]): ?>
        <div class="form-group" style="margin-bottom:1.25rem">
          <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.45rem;color:var(--text-secondary)">
            <?= $flabel ?> <span style="color:var(--red)">*</span>
          </label>
          <div style="position:relative">
            <input type="password" name="<?= $fname ?>" id="<?= $fid ?>" required
                   style="width:100%;background:var(--bg-section);border:1.5px solid var(--border);border-radius:8px;color:var(--text-primary);padding:.75rem 2.75rem .75rem 1rem;font-size:.9rem;outline:none"/>
            <button type="button" onclick="togglePwd('<?= $fid ?>','<?= $eid ?>')"
              style="position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.9rem;padding:0">
              <i class="fa-solid fa-eye" id="<?= $eid ?>"></i>
            </button>
          </div>
          <?php if ($fid === 'new_pwd'): ?>
            <div id="strengthBar" style="height:4px;border-radius:2px;margin-top:.5rem;background:var(--border);overflow:hidden">
              <div id="strengthFill" style="height:100%;width:0;border-radius:2px;transition:.3s"></div>
            </div>
            <small id="strengthLabel" style="font-size:.75rem;color:var(--text-muted)"></small>
          <?php endif; ?>
          <?php if ($fid === 'conf_pwd'): ?>
            <small id="matchLabel" style="font-size:.75rem;margin-top:.3rem;display:block"></small>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <!-- Requirements -->
      <div style="background:var(--bg-section);border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:1.5rem">
        <p style="font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.5rem">Password must:</p>
        <div id="req-length" class="pwd-req"><i class="fa-solid fa-circle fa-xs"></i> Be at least 6 characters</div>
        <div id="req-match"  class="pwd-req"><i class="fa-solid fa-circle fa-xs"></i> Match the confirmation field</div>
      </div>

      <button type="submit" name="update_password" class="btn btn-orange">
        <i class="fa-solid fa-key"></i> Change Password
      </button>
    </div>
  </form>
</div>

<style>
.admin-profile-tab { background:var(--bg-section); color:var(--text-muted); }
.admin-profile-tab:hover:not(.apt-blue):not(.apt-orange) { background:var(--border); color:var(--text-primary); }
.apt-blue   { background:var(--blue)   !important; color:#fff !important; }
.apt-orange { background:var(--orange) !important; color:#fff !important; }
.pwd-req { font-size:.8rem; color:var(--text-muted); display:flex; align-items:center; gap:.5rem; padding:.2rem 0; transition:.2s; }
.pwd-req.ok { color:var(--green); }
/* Focus styles for inputs inside admin panel */
#panel-info input:focus, #panel-password input:focus {
  border-color: var(--blue) !important;
  box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
</style>

<script>
function switchTab(tab) {
  document.getElementById('panel-info').style.display     = tab === 'info'     ? 'block' : 'none';
  document.getElementById('panel-password').style.display = tab === 'password' ? 'block' : 'none';
  document.querySelectorAll('.admin-profile-tab').forEach(function(b) {
    b.classList.remove('apt-blue','apt-orange');
  });
  document.getElementById('tab-' + tab).classList.add(tab === 'info' ? 'apt-blue' : 'apt-orange');
}

function togglePwd(inputId, eyeId) {
  var inp = document.getElementById(inputId);
  var eye = document.getElementById(eyeId);
  inp.type  = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}

// Password strength
var newPwd = document.getElementById('new_pwd');
if (newPwd) {
  newPwd.addEventListener('input', function() {
    var val   = this.value;
    var fill  = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');
    var score = 0;
    if (val.length >= 6)             score++;
    if (val.length >= 10)            score++;
    if (/[A-Z]/.test(val))           score++;
    if (/[0-9]/.test(val))           score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;
    var colours = ['','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    var labels  = ['','Very Weak','Weak','Fair','Strong','Very Strong'];
    fill.style.width      = ((score/5)*100) + '%';
    fill.style.background = colours[score] || '#e2e8f0';
    label.textContent     = val.length ? labels[score] : '';
    label.style.color     = colours[score] || 'var(--text-muted)';
    document.getElementById('req-length').classList.toggle('ok', val.length >= 6);
    checkMatch();
  });
}

var confPwd = document.getElementById('conf_pwd');
if (confPwd) { confPwd.addEventListener('input', checkMatch); }

function checkMatch() {
  var np  = document.getElementById('new_pwd') ? document.getElementById('new_pwd').value : '';
  var cp  = document.getElementById('conf_pwd') ? document.getElementById('conf_pwd').value : '';
  var ml  = document.getElementById('matchLabel');
  var req = document.getElementById('req-match');
  if (!ml) return;
  if (!cp.length) { ml.textContent = ''; req.classList.remove('ok'); return; }
  var ok = np === cp;
  ml.textContent  = ok ? '✓ Passwords match' : '✗ Passwords do not match';
  ml.style.color  = ok ? 'var(--green)' : 'var(--red)';
  req.classList.toggle('ok', ok);
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
