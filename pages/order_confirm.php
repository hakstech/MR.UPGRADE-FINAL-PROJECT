<?php
$page_title = "Order Confirmed";
require_once __DIR__ . '/../config/db.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$order_number = trim($_GET['order'] ?? '');
if (!$order_number) { header('Location: ' . SITE_URL . '/'); exit; }

$stmt = $conn->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id=u.id WHERE o.order_number=? AND o.user_id=?");
$stmt->bind_param('si', $order_number, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) { header('Location: ' . SITE_URL . '/orders'); exit; }

// Get installment if exists
$ip_stmt = $conn->prepare("SELECT * FROM installment_plans WHERE order_id=?");
$ip_stmt->bind_param('i', $order['id']);
$ip_stmt->execute();
$install = $ip_stmt->get_result()->fetch_assoc();

// Get items
$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
$items_stmt->bind_param('i', $order['id']);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container" style="padding:4rem 1.25rem 5rem">
  <div class="order-confirm">

    <!-- Success Icon -->
    <div class="confirm-icon"><i class="fa-solid fa-check"></i></div>

    <h1 style="font-size:2rem;font-weight:900;margin-bottom:.5rem">Order Confirmed! 🎉</h1>
    <p style="color:var(--gray-400);font-size:1rem;margin-bottom:2rem">
      Thank you, <strong><?= htmlspecialchars($order['full_name']) ?></strong>! Your order has been received and is being processed.
    </p>

    <!-- Order Number -->
    <div class="order-number">
      <div style="font-size:.8rem;color:var(--gray-400);margin-bottom:.25rem">Order Number</div>
      <span><?= htmlspecialchars($order['order_number']) ?></span>
    </div>

    <!-- Order Details Card -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;text-align:left;margin:1.5rem 0">

      <!-- Items -->
      <h4 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1rem">Items Ordered</h4>
      <?php foreach ($order_items as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.875rem">
          <span><?= htmlspecialchars($item['product_name']) ?> (<?= htmlspecialchars($item['storage']) ?>) × <?= $item['quantity'] ?></span>
          <span style="font-weight:700"><?= formatPrice($item['total_price']) ?></span>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;padding:.75rem 0 0;font-weight:800">
        <span>Total</span>
        <span style="color:var(--blue-light)"><?= formatPrice($order['total_amount']) ?></span>
      </div>

      <div style="border-top:1px solid var(--border);margin-top:1rem;padding-top:1rem">
        <h4 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:.75rem">Order Info</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.875rem">
          <div><span style="color:var(--gray-400)">Payment:</span><br/><strong>
            <?php
            $pm_labels = ['mtn_momo'=>'MTN Mobile Money','airtel_money'=>'Airtel Money','cash_on_delivery'=>'Cash on Delivery'];
            echo isset($pm_labels[$order['payment_method']]) ? $pm_labels[$order['payment_method']] : htmlspecialchars($order['payment_method']);
            ?>
          </strong></div>
          <div><span style="color:var(--gray-400)">Type:</span><br/><strong><?= ucfirst(str_replace('_',' ',$order['payment_type'])) ?></strong></div>
          <div><span style="color:var(--gray-400)">Deliver to:</span><br/><strong><?= nl2br(htmlspecialchars($order['address'])) ?></strong></div>
          <div><span style="color:var(--gray-400)">Status:</span><br/><span class="status-badge status-pending"><?= ucfirst($order['status']) ?></span></div>
        </div>
      </div>

      <!-- Installment Plan Details -->
      <?php if ($install): ?>
        <div style="border-top:1px solid var(--border);margin-top:1rem;padding-top:1rem;background:rgba(37,99,235,.06);border-radius:8px;padding:1rem;margin-top:1rem">
          <h4 style="color:var(--cyan);font-size:.9rem;font-weight:700;margin-bottom:.75rem"><i class="fa-solid fa-calendar-check"></i> Your Installment Plan</h4>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.875rem">
            <div><span style="color:var(--gray-400)">Deposit Paid:</span><br/><strong style="color:var(--orange)"><?= formatPrice($install['deposit_amount']) ?></strong></div>
            <div><span style="color:var(--gray-400)">Remaining Balance:</span><br/><strong><?= formatPrice($install['balance']) ?></strong></div>
            <div><span style="color:var(--gray-400)">Duration:</span><br/><strong><?= $install['weeks'] ?> weeks</strong></div>
            <div><span style="color:var(--gray-400)">Weekly Payment:</span><br/><strong style="color:var(--cyan)"><?= formatPrice($install['weekly_payment']) ?>/wk</strong></div>
          </div>
        </div>
      <?php endif; ?>

      <!-- MoMo Instruction -->
      <?php if (in_array($order['payment_method'], ['mtn_momo','airtel_money'])): ?>
        <div style="margin-top:1rem;padding:1rem;background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.3);border-radius:8px">
          <p style="font-size:.875rem;color:var(--orange);font-weight:700;margin-bottom:.3rem">
            <i class="fa-solid fa-mobile-screen"></i> Payment Prompt
          </p>
          <p style="font-size:.8rem;color:var(--gray-300)">
            We will send a <?= $order['payment_method'] === 'airtel_money' ? 'Airtel Money' : 'MTN MoMo' ?> payment prompt to
            <strong><?= htmlspecialchars($order['momo_number']) ?></strong> shortly.
            Please keep your phone close and approve the prompt. Contact us on WhatsApp if you don't receive it within 5 minutes.
          </p>
        </div>
      <?php endif; ?>

    </div>

    <!-- Action Buttons -->
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1rem">
      <a href="<?= SITE_URL ?>/orders" class="btn btn-outline btn-lg">
        <i class="fa-solid fa-box"></i> View My Orders
      </a>
      <a href="https://wa.me/256743741601?text=Hi%2C+I+just+placed+order+<?= urlencode($order['order_number']) ?>" target="_blank" class="btn btn-whatsapp btn-lg">
        <i class="fa-brands fa-whatsapp"></i> Chat Support
      </a>
      <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-store"></i> Continue Shopping
      </a>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
