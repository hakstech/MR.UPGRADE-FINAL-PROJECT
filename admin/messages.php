<?php
$page_title = "Contact Messages";
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../config/africastalking.php';

if (isset($_GET['read'])) {
    $conn->query("UPDATE contacts SET is_read=1 WHERE id=" . (int)$_GET['read']);
    header('Location: ' . SITE_URL . '/admin/messages');
    exit;
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM contacts WHERE id=" . (int)$_GET['delete']);
    header('Location: ' . SITE_URL . '/admin/messages?deleted=1');
    exit;
}

$sms_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_sms'])) {
    if (!verifyCsrfToken()) { die('Invalid request.'); }
    $contact_id  = (int)($_POST['contact_id']  ?? 0);
    $sms_local   = trim($_POST['sms_local']     ?? '');  // digits after +256
    $sms_message = trim($_POST['sms_message']   ?? '');

    $sms_local = preg_replace('/[^0-9]/', '', $sms_local);
    if (strlen($sms_local) === 10 && substr($sms_local,0,1)==='0') {
        $sms_local = substr($sms_local, 1);
    }
    $sms_to = '+256' . $sms_local;

    if (strlen($sms_local) !== 9) {
        $sms_result = array('success' => false, 'message' => 'Enter exactly 9 digits after +256 (e.g. 743741601).');
    } elseif (!$sms_message) {
        $sms_result = array('success' => false, 'message' => 'Message cannot be empty.');
    } else {
        $result = sendSMS($sms_to, $sms_message);
        $status   = $result['success'] ? 'sent' : 'failed';
        $resp_str = json_encode($result['data']);
        $log = $conn->prepare("INSERT INTO sms_logs (contact_id, sent_to, message, status, response) VALUES (?,?,?,?,?)");
        $log->bind_param('issss', $contact_id, $sms_to, $sms_message, $status, $resp_str);
        $log->execute();
        $sms_result = $result;
    }

    header('Location: ' . SITE_URL . '/admin/messages?view=' . $contact_id . '&sms=' . (isset($sms_result['success']) && $sms_result['success'] ? 'sent' : 'failed'));
    exit;
}

if (isset($_GET['del_sms'])) {
    $del_sms_id = (int)$_GET['del_sms'];
    $view_back  = (int)($_GET['contact'] ?? 0);
    $conn->query("DELETE FROM sms_logs WHERE id=$del_sms_id");
    header('Location: ' . SITE_URL . '/admin/messages?view=' . $view_back);
    exit;
}
$view_msg = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $vstmt = $conn->prepare("SELECT * FROM contacts WHERE id=?");
    $vstmt->bind_param('i', $view_id);
    $vstmt->execute();
    $view_msg = $vstmt->get_result()->fetch_assoc();
    if ($view_msg && !$view_msg['is_read']) {
        $conn->query("UPDATE contacts SET is_read=1 WHERE id=" . (int)$view_msg['id']);
        $view_msg['is_read'] = 1;
    }

    $sms_hist = $conn->prepare("SELECT * FROM sms_logs WHERE contact_id=? ORDER BY sent_at DESC LIMIT 10");
    $sms_hist->bind_param('i', $view_id);
    $sms_hist->execute();
    $sms_history = $sms_hist->get_result()->fetch_all(MYSQLI_ASSOC);
}

$msgs = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
?>

<?php if (isset($_GET['deleted'])): ?>
  <div class="alert alert-success" style="margin-bottom:1rem"><i class="fa-solid fa-check-circle"></i> Message deleted.</div>
<?php endif; ?>
<?php if (isset($_GET['sms'])): ?>
  <div class="alert <?= $_GET['sms']==='sent' ? 'alert-success' : 'alert-error' ?>" style="margin-bottom:1rem">
    <?php if ($_GET['sms']==='sent'): ?>
      <i class="fa-solid fa-check-circle"></i> SMS sent successfully!
    <?php else: ?>
      <i class="fa-solid fa-circle-exclamation"></i> SMS failed to send. Check the phone number and try again.
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="msg-layout">
  <div class="msg-list">
    <div class="data-table-wrap">
      <div class="data-table-head">
        <h3>Messages <span style="color:var(--text-muted);font-weight:400;font-size:.85rem">(<?= $msgs->num_rows ?>)</span></h3>
      </div>
      <?php if ($msgs->num_rows === 0): ?>
        <div class="empty-state" style="padding:2rem">
          <i class="fa-solid fa-envelope-open"></i>
          <p>No messages yet.</p>
        </div>
      <?php else: ?>
        <div class="msg-items">
          <?php while ($m = $msgs->fetch_assoc()):
            $is_active = isset($_GET['view']) && (int)$_GET['view'] === (int)$m['id'];
          ?>
            <a href="?view=<?= $m['id'] ?>" class="msg-item <?= !$m['is_read'] ? 'unread' : '' ?> <?= $is_active ? 'active' : '' ?>">
              <div class="msg-avatar"><?= strtoupper(substr($m['name'],0,1)) ?></div>
              <div class="msg-item-body">
                <div class="msg-item-top">
                  <span class="msg-sender"><?= htmlspecialchars($m['name']) ?></span>
                  <span class="msg-date"><?= date('M j', strtotime($m['created_at'])) ?></span>
                </div>
                <?php if ($m['subject']): ?>
                  <div class="msg-subject"><?= htmlspecialchars($m['subject']) ?></div>
                <?php endif; ?>
                <div class="msg-preview"><?= htmlspecialchars(substr($m['message'],0,55)) ?>...</div>
              </div>
              <?php if (!$m['is_read']): ?>
                <div class="msg-unread-dot"></div>
              <?php endif; ?>
            </a>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="msg-detail">
    <?php if ($view_msg): ?>

      <div class="data-table-wrap">
        <div class="data-table-head" style="flex-wrap:wrap;gap:.5rem">
          <div>
            <h3><?= htmlspecialchars($view_msg['subject'] ?: 'No Subject') ?></h3>
            <p style="font-size:.8rem;color:var(--text-muted);margin-top:.2rem">
              From <strong><?= htmlspecialchars($view_msg['name']) ?></strong>
              &bull; <?= date('M j, Y \a\t g:i A', strtotime($view_msg['created_at'])) ?>
            </p>
          </div>
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a href="?delete=<?= $view_msg['id'] ?>"
               class="btn btn-danger btn-sm confirm-delete"
               data-title="Delete Message?"
               data-msg="This message from <?= htmlspecialchars(addslashes($view_msg['name'])) ?> will be permanently deleted.">
              <i class="fa-solid fa-trash"></i> Delete
            </a>
            <a href="<?= SITE_URL ?>/admin/messages" class="btn btn-outline btn-sm">
              <i class="fa-solid fa-arrow-left"></i> Back
            </a>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
          <div class="msg-info-card">
            <div class="msg-info-label"><i class="fa-solid fa-user"></i> Name</div>
            <div class="msg-info-val"><?= htmlspecialchars($view_msg['name']) ?></div>
          </div>
          <div class="msg-info-card">
            <div class="msg-info-label"><i class="fa-solid fa-envelope"></i> Email</div>
            <div class="msg-info-val" style="word-break:break-all;font-size:.82rem"><?= htmlspecialchars($view_msg['email']) ?></div>
          </div>
          <?php if ($view_msg['phone']): ?>
          <div class="msg-info-card">
            <div class="msg-info-label"><i class="fa-solid fa-phone"></i> Phone</div>
            <div class="msg-info-val"><?= htmlspecialchars($view_msg['phone']) ?></div>
          </div>
          <?php endif; ?>
          <?php if ($view_msg['subject']): ?>
          <div class="msg-info-card">
            <div class="msg-info-label"><i class="fa-solid fa-tag"></i> Subject</div>
            <div class="msg-info-val"><?= htmlspecialchars($view_msg['subject']) ?></div>
          </div>
          <?php endif; ?>
        </div>

        <div style="padding:1.5rem;border-bottom:1px solid var(--border)">
          <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.75rem">Message</p>
          <div style="background:var(--bg-section);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;font-size:.9rem;line-height:1.75;color:var(--text-primary);white-space:pre-wrap"><?= htmlspecialchars($view_msg['message']) ?></div>
        </div>

        <div style="padding:1.5rem;border-bottom:1px solid var(--border)">
          <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:1rem">Reply Via</p>
          <div class="reply-options">
            <a href="mailto:<?= urlencode($view_msg['email']) ?>?subject=Re: <?= urlencode($view_msg['subject'] ?: 'Your Message') ?>&body=Hi <?= urlencode($view_msg['name']) ?>,%0D%0A%0D%0AThank you for reaching out to Mr. Upgrade.%0D%0A%0D%0A"
               class="reply-btn" target="_blank">
              <div class="reply-icon">
                <svg width="20" height="20" viewBox="0 0 48 48">
                  <path fill="#EA4335" d="M24 24L4 10h40z"/><path fill="#FBBC05" d="M44 10v28H28V24z"/>
                  <path fill="#34A853" d="M4 38V10l20 14z"/><path fill="#4285F4" d="M28 24v14H4l20-14z"/>
                </svg>
              </div>
              <div class="reply-info">
                <div class="reply-name">Email / Gmail</div>
                <div class="reply-val"><?= htmlspecialchars($view_msg['email']) ?></div>
              </div>
              <i class="fa-solid fa-arrow-up-right-from-square reply-arrow"></i>
            </a>

            <?php if ($view_msg['phone']): ?>
              <?php
                $wa_num = preg_replace('/[^0-9]/', '', $view_msg['phone']);
                if (substr($wa_num,0,1) === '0') $wa_num = '256' . substr($wa_num,1);
                $wa_msg = urlencode("Hi " . $view_msg['name'] . ", thank you for contacting Mr. Upgrade! We received your message and are happy to help.");
              ?>
              <a href="https://wa.me/<?= $wa_num ?>?text=<?= $wa_msg ?>"
                 class="reply-btn reply-whatsapp" target="_blank" rel="noopener">
                <div class="reply-icon" style="background:#25d366">
                  <i class="fa-brands fa-whatsapp" style="color:#fff;font-size:1.2rem"></i>
                </div>
                <div class="reply-info">
                  <div class="reply-name">WhatsApp</div>
                  <div class="reply-val"><?= htmlspecialchars($view_msg['phone']) ?></div>
                </div>
                <i class="fa-solid fa-arrow-up-right-from-square reply-arrow"></i>
              </a>
            <?php endif; ?>

            <?php if ($view_msg['phone']): ?>
              <div class="reply-btn reply-sms" onclick="toggleSmsForm()" style="cursor:pointer">
                <div class="reply-icon" style="background:linear-gradient(135deg,#2563eb,#0891b2)">
                  <i class="fa-solid fa-comment-sms" style="color:#fff;font-size:1.1rem"></i>
                </div>
                <div class="reply-info">
                  <div class="reply-name">Send SMS</div>
                  <div class="reply-val"><?= htmlspecialchars($view_msg['phone']) ?> &bull; via Africa's Talking</div>
                </div>
                <i class="fa-solid fa-chevron-down reply-arrow" id="smsChevron"></i>
              </div>
              <div id="smsForm" style="display:none;background:var(--bg-section);border:1.5px solid var(--blue);border-radius:var(--radius);padding:1.25rem;margin-top:-.5rem">
                <form method="POST">
                  <?= csrfField() ?>
                  <input type="hidden" name="send_sms" value="1"/>
                  <input type="hidden" name="contact_id" value="<?= $view_msg['id'] ?>"/>
                  <div style="margin-bottom:.75rem">
                    <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.4rem">
                      <i class="fa-solid fa-phone" style="color:var(--blue)"></i> To (phone number)
                    </label>
                    <div style="display:flex;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:#fff">
                      <span style="background:#eff6ff;padding:.65rem .9rem;font-size:.875rem;font-weight:700;color:#2563eb;border-right:1.5px solid var(--border);white-space:nowrap;display:flex;align-items:center;gap:.35rem">
                        UG +256
                      </span>
                      <input type="tel" name="sms_local" id="smsLocal" maxlength="9"
                             placeholder="7XXXXXXXX"
                             value="<?php
                               $raw = preg_replace('/[^0-9]/', '', $view_msg['phone']);
                               if (substr($raw,0,3)==='256') $raw = substr($raw,3);
                               elseif (substr($raw,0,1)==='0') $raw = substr($raw,1);
                               echo htmlspecialchars($raw);
                             ?>"
                             oninput="this.value=this.value.replace(/[^0-9]/g,'').substring(0,9)"
                             style="flex:1;border:none;outline:none;padding:.65rem .9rem;font-size:.875rem;color:var(--text-primary);background:transparent;font-family:inherit"/>
                    </div>
                    <input type="hidden" name="sms_to" id="smsToHidden"/>
                    <small style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;display:block">
                      Enter digits after +256 — e.g. <strong>7XXXXXXXX</strong>
                    </small>
                  </div>
                  <div style="margin-bottom:.85rem">
                    <label style="font-size:.8rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:.4rem">
                      <i class="fa-solid fa-comment" style="color:var(--blue)"></i> Message
                      <span id="smsCount" style="font-weight:400;color:var(--text-muted);float:right">0 / 160</span>
                    </label>
                    <textarea name="sms_message" id="smsText" rows="3" maxlength="160"
                              placeholder="Type your SMS reply here... (max 160 characters)"
                              oninput="document.getElementById('smsCount').textContent=this.value.length+' / 160'"
                              style="width:100%;background:#fff;border:1.5px solid var(--border);border-radius:8px;color:var(--text-primary);padding:.65rem .9rem;font-size:.875rem;outline:none;resize:vertical;font-family:inherit;min-height:80px"><?php
                      echo htmlspecialchars("Hi " . $view_msg['name'] . ", thank you for contacting Mr. Upgrade. We will get back to you shortly. - Mr. Upgrade");
                    ?></textarea>
                  </div>
                  <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
                    <button type="submit" class="btn btn-primary btn-sm" onclick="buildSmsTo()">
                      <i class="fa-solid fa-paper-plane"></i> Send SMS
                    </button>
                    <button type="button" onclick="toggleSmsForm()" class="btn btn-outline btn-sm">Cancel</button>
                    <span style="font-size:.72rem;color:var(--text-muted)">
                      <i class="fa-solid fa-info-circle"></i> Africa's Talking Sandbox
                    </span>
                  </div>
                </form>
              </div>
            <?php else: ?>

              <div class="reply-btn" style="opacity:.5;cursor:not-allowed">
                <div class="reply-icon" style="background:var(--bg-section)">
                  <i class="fa-solid fa-comment-sms" style="color:var(--text-muted);font-size:1.1rem"></i>
                </div>
                <div class="reply-info">
                  <div class="reply-name">Send SMS</div>
                  <div class="reply-val" style="color:var(--red)">No phone number provided</div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($sms_history)): ?>
        <div style="padding:1.5rem">
          <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.75rem">
            <i class="fa-solid fa-clock-rotate-left"></i> SMS History (<?= count($sms_history) ?>)
          </p>
          <div style="display:flex;flex-direction:column;gap:.6rem">
            <?php foreach ($sms_history as $log): ?>
              <div style="background:var(--bg-section);border:1px solid var(--border);border-radius:8px;padding:.85rem 1rem;display:flex;gap:.75rem;align-items:flex-start">
                <div style="flex-shrink:0;margin-top:.1rem">
                  <?php if ($log['status']==='sent'): ?>
                    <i class="fa-solid fa-circle-check" style="color:var(--green);font-size:1rem"></i>
                  <?php else: ?>
                    <i class="fa-solid fa-circle-xmark" style="color:var(--red);font-size:1rem"></i>
                  <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0">
                  <div style="font-size:.82rem;color:var(--text-primary);margin-bottom:.25rem"><?= htmlspecialchars($log['message']) ?></div>
                  <div style="font-size:.72rem;color:var(--text-muted)">
                    To: <?= htmlspecialchars($log['sent_to']) ?>
                    &bull; <?= date('M j, Y g:i A', strtotime($log['sent_at'])) ?>
                    &bull; <span style="color:<?= $log['status']==='sent'?'var(--green)':'var(--red)' ?>;font-weight:600"><?= ucfirst($log['status']) ?></span>
                  </div>
                </div>
                <a href="?del_sms=<?= $log['id'] ?>&contact=<?= $view_msg['id'] ?>"
                   class="confirm-delete"
                   data-title="Delete SMS Log?"
                   data-msg="Remove this SMS history entry?"
                   title="Delete"
                   style="flex-shrink:0;width:24px;height:24px;border-radius:50%;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:var(--red);font-size:.7rem;text-decoration:none;transition:.2s;margin-top:.1rem"
                   onmouseover="this.style.background='var(--red)';this.style.color='#fff'"
                   onmouseout="this.style.background='rgba(239,68,68,.1)';this.style.color='var(--red)'">
                  <i class="fa-solid fa-xmark"></i>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>

    <?php else: ?>
      <div class="data-table-wrap" style="display:flex;align-items:center;justify-content:center;min-height:300px">
        <div class="empty-state">
          <i class="fa-regular fa-envelope" style="font-size:3rem;color:var(--border);margin-bottom:1rem"></i>
          <h3 style="color:var(--text-secondary)">Select a message</h3>
          <p>Click any message on the left to view it here.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

</div>

<style>
.msg-layout { display:grid; grid-template-columns:300px 1fr; gap:1.5rem; align-items:start; }
.msg-items  { display:flex; flex-direction:column; }

.msg-item {
  display:flex; align-items:flex-start; gap:.85rem;
  padding:.9rem 1.1rem; border-bottom:1px solid var(--border);
  text-decoration:none; color:inherit; transition:.15s; position:relative;
}
.msg-item:hover { background:var(--bg-section); }
.msg-item.active { background:var(--blue-pale); border-left:3px solid var(--blue); }
.msg-item.unread { background:rgba(37,99,235,.04); }

.msg-avatar {
  width:36px; height:36px; border-radius:50%;
  background:linear-gradient(135deg,var(--blue),var(--cyan));
  color:#fff; font-weight:800; font-size:.9rem;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.msg-item-body { flex:1; min-width:0; }
.msg-item-top  { display:flex; justify-content:space-between; align-items:center; margin-bottom:.15rem; }
.msg-sender    { font-weight:600; font-size:.85rem; color:var(--text-primary); }
.msg-date      { font-size:.7rem; color:var(--text-muted); flex-shrink:0; }
.msg-subject   { font-size:.78rem; font-weight:600; color:var(--text-secondary); margin-bottom:.12rem; }
.msg-preview   { font-size:.75rem; color:var(--text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.msg-unread-dot { width:8px; height:8px; border-radius:50%; background:var(--blue); flex-shrink:0; margin-top:.35rem; }

.msg-info-card { background:var(--bg-section); border:1px solid var(--border); border-radius:8px; padding:.7rem .9rem; }
.msg-info-label { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:.25rem; }
.msg-info-val   { font-size:.85rem; font-weight:600; color:var(--text-primary); }

.reply-options { display:flex; flex-direction:column; gap:.6rem; }
.reply-btn {
  display:flex; align-items:center; gap:.85rem;
  padding:.85rem 1rem;
  background:var(--bg-body);
  border:1.5px solid var(--border);
  border-radius:var(--radius);
  text-decoration:none; color:inherit; transition:.2s;
}
.reply-btn:hover { border-color:var(--blue); background:var(--blue-pale); transform:translateY(-1px); }
.reply-whatsapp:hover { border-color:#25d366; background:#f0fdf4; }
.reply-sms:hover { border-color:var(--blue); }
.reply-icon { width:40px; height:40px; border-radius:10px; background:var(--bg-section); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.reply-info { flex:1; }
.reply-name { font-weight:700; font-size:.875rem; color:var(--text-primary); }
.reply-val  { font-size:.78rem; color:var(--text-muted); margin-top:.1rem; }
.reply-arrow { color:var(--text-muted); font-size:.75rem; flex-shrink:0; }

@media (max-width:900px) {
  .msg-layout { grid-template-columns:1fr; }
  .msg-items  { max-height:280px; overflow-y:auto; }
}
</style>

<script>
function toggleSmsForm() {
  var form    = document.getElementById('smsForm');
  var chevron = document.getElementById('smsChevron');
  if (!form) return;
  var isOpen  = form.style.display !== 'none';
  form.style.display = isOpen ? 'none' : 'block';
  if (chevron) chevron.className = isOpen ? 'fa-solid fa-chevron-down reply-arrow' : 'fa-solid fa-chevron-up reply-arrow';
}

function buildSmsTo() {
  var local  = document.getElementById('smsLocal');
  var hidden = document.getElementById('smsToHidden');
  if (local && hidden) {
    var digits = local.value.replace(/[^0-9]/g,'');
    hidden.value = '+256' + digits;
  }
}

(function() {
  var t = document.getElementById('smsText');
  var c = document.getElementById('smsCount');
  if (t && c) c.textContent = t.value.length + ' / 160';
})();
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
