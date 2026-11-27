<?php
$page_title = "Contact Us";
$meta_desc  = "Contact Mr. Upgrade Uganda. Reach us via WhatsApp, phone or email for the best smartphone deals.";
require_once __DIR__ . '/../includes/header.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Invalid request. Please try again.';
    } else {
    $name    = sanitizeString($_POST['name']    ?? '');
    $email   = sanitizeEmail($_POST['email']    ?? '');
    $phone   = sanitizeString($_POST['phone']   ?? '');
    $subject = sanitizeString($_POST['subject'] ?? '');
    $message = sanitizeString($_POST['message'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$message) {
        $error = 'Please fill in your name, email and message.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $subject, $message);
        $stmt->execute();
        $success = "Thanks {$name}! We've received your message and will get back to you shortly. Or chat us on WhatsApp for a faster response.";
    }
    } 
}
?>

<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i><span>Contact</span>
    </div>
    <h1>Get In <span class="gradient-text">Touch</span></h1>
    <p>We'd love to hear from you. Let's find your perfect upgrade.</p>
  </div>
</div>

<div class="container" style="padding:3rem 1.25rem 5rem">
  <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:3rem;align-items:start">
    <div>
      <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.5rem">Contact <span class="gradient-text">Information</span></h2>
      <p style="color:var(--gray-400);margin-bottom:2rem;font-size:.93rem;">Reach us through any of these channels. WhatsApp is the fastest</p>

      <?php foreach([
        ['fa-brands fa-whatsapp','WhatsApp (Fastest)','Chat with us instantly on WhatsApp for quotes, availability and support.','https://wa.me/256743741601','Chat Now →','var(--green)'],
        ['fa-solid fa-phone','Phone','Call us directly during business hours.','tel:+256743741601','+256 743 741 601','var(--blue-light)'],
        ['fa-solid fa-envelope','Email','Send us an email for detailed enquiries.','mailto:info@mrupgradeug.com','info@mrupgradeug.com','var(--cyan)'],
        ['fa-solid fa-location-dot','Location','Come visit us in person. We\'d love to meet you.','https://maps.google.com/?q=Pioneer+Mall+Kampala+Uganda','Pioneer Mall, Room PG52 Kampala','var(--orange)'],
      ] as [$icon,$title,$desc,$link,$val,$color]): ?>
        <div style="display:flex;gap:1rem;margin-bottom:1.5rem;padding:1.25rem;background:var(--bg-card2);border:1px solid var(--border);border-radius:var(--radius);transition:.2s ease" onmouseover="this.style.borderColor='var(--blue)'" onmouseout="this.style.borderColor='var(--border)'">
          <div style="width:44px;height:44px;background:rgba(37,99,235,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:<?= $color ?>;font-size:1.2rem">
            <i class="<?= $icon ?>"></i>
          </div>
          <div>
            <div style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:.25rem"><?= $title ?></div>
            <div style="font-size:.85rem;color:var(--gray-300);margin-bottom:.35rem"><?= $desc ?></div>
            <a href="<?= $link ?>" style="color:<?= $color ?>;font-weight:700;font-size:.875rem" <?= (substr($link,0,4)==='http')?'target="_blank" rel="noopener"':'' ?>><?= $val ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div>
      <div style="background:var(--blue-pale);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.5rem">
        <h3 style="font-size:1.25rem;font-weight:800;margin-bottom:.5rem">Send Us a Message</h3>
        <p style="color:var(--gray-400);font-size:.875rem;margin-bottom:1.5rem">Fill in the form below and we'll get back to you ASAP.</p>

        <?php if ($success): ?>
          <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <?= csrfField() ?>
          <div class="form-row">
            <div class="form-group">
              <label>Your Name <span style="color:var(--red)">*</span></label>
              <input type="text" name="name" placeholder="John Mukasa" value="<?= htmlspecialchars($_POST['name'] ?? (isLoggedIn() ? $_SESSION['user_name'] : '')) ?>" required/>
            </div>
            <div class="form-group">
              <label>Email Address <span style="color:var(--red)">*</span></label>
              <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? (isLoggedIn() ? $_SESSION['user_email'] : '')) ?>" required/>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="tel" name="phone" placeholder="+256 7XX XXX XXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
            </div>
            <div class="form-group">
              <label>Subject</label>
              <select name="subject">
                <option value="">Select a topic</option>
                <option value="Product Enquiry"      <?= ($_POST['subject']??'')==='Product Enquiry'?'selected':''?>>Product Enquiry</option>
                <option value="Installment Plan"     <?= ($_POST['subject']??'')==='Installment Plan'?'selected':''?>>Installment Plan</option>
                <option value="Order Support"        <?= ($_POST['subject']??'')==='Order Support'?'selected':''?>>Order Support</option>
                <option value="Price Quote"          <?= ($_POST['subject']??'')==='Price Quote'?'selected':''?>>Price Quote</option>
                <option value="Other"                <?= ($_POST['subject']??'')==='Other'?'selected':''?>>Other</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Message <span style="color:var(--red)">*</span></label>
            <textarea name="message" rows="5" placeholder="Tell us which phone you're interested in, your budget, or any questions you have..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
            <i class="fa-solid fa-paper-plane"></i> Send Message
          </button>
        </form>

        <div style="margin-top:1.25rem;text-align:center">
          <p style="font-size:.8rem;color:var(--gray-500)">Or for instant help:</p>
          <a href="https://wa.me/256743741601" target="_blank" class="btn btn-whatsapp btn-lg" style="margin-top:.5rem;justify-content:center">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp Us Now
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media(max-width:768px){div[style*="grid-template-columns:1fr 1.4fr"]{grid-template-columns:1fr!important}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
