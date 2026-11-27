<?php
$page_title = "My Orders";
require_once __DIR__ . '/../config/db.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';
$user_id = (int)$_SESSION['user_id'];

$orders_res = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id=oi.order_id
    WHERE o.user_id=?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders_res->bind_param('i', $user_id);
$orders_res->execute();
$orders = $orders_res->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>My Orders</span>
    </div>
    <h1>My Orders</h1>
    <p><?= count($orders) ?> order<?= count($orders) != 1 ? 's' : '' ?> placed</p>
  </div>
</div>

<div class="container" style="padding:2.5rem 1.25rem 5rem">

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-box-open"></i>
      <h3>No orders yet</h3>
      <p>You haven't placed any orders. Start shopping now!</p>
      <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg" style="margin-top:1.5rem">
        <i class="fa-solid fa-store"></i> Shop Now
      </a>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <?php foreach ($orders as $order): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
          <!-- Order Header -->
          <div style="padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;border-bottom:1px solid var(--border);background:rgba(37,99,235,.04)">
            <div>
              <div style="font-size:.75rem;color:var(--gray-400);margin-bottom:.2rem">Order Number</div>
              <div style="font-weight:800;color:var(--blue-light)"><?= htmlspecialchars($order['order_number']) ?></div>
            </div>
            <div>
              <div style="font-size:.75rem;color:var(--gray-400);margin-bottom:.2rem">Date</div>
              <div style="font-weight:600"><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
            </div>
            <div>
              <div style="font-size:.75rem;color:var(--gray-400);margin-bottom:.2rem">Items</div>
              <div style="font-weight:600"><?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></div>
            </div>
            <div>
              <div style="font-size:.75rem;color:var(--gray-400);margin-bottom:.2rem">Total</div>
              <div style="font-weight:800;color:var(--white)"><?= formatPrice($order['total_amount']) ?></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.4rem;align-items:flex-end">
              <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
              <span class="status-badge status-<?= $order['payment_status'] === 'paid' ? 'paid' : ($order['payment_status'] === 'deposit_paid' ? 'deposit' : 'unpaid') ?>">
                <?= ucfirst(str_replace('_', ' ', $order['payment_status'])) ?>
              </span>
            </div>
          </div>
          <!-- Order Body -->
          <div style="padding:1.25rem 1.5rem">
            <?php
            $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
            $items_stmt->bind_param('i', $order['id']);
            $items_stmt->execute();
            $ois = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach ($ois as $oi):
            ?>
              <div style="display:flex;justify-content:space-between;font-size:.875rem;padding:.35rem 0;border-bottom:1px solid rgba(255,255,255,.04)">
                <span><?= htmlspecialchars($oi['product_name']) ?> <?= htmlspecialchars($oi['storage']) ?> × <?= $oi['quantity'] ?></span>
                <span style="font-weight:700"><?= formatPrice($oi['total_price']) ?></span>
              </div>
            <?php endforeach; ?>

            <div style="display:flex;justify-content:space-between;margin-top:1rem;gap:.75rem;flex-wrap:wrap">
              <div style="font-size:.8rem;color:var(--gray-400)">
                <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($order['address']) ?> &nbsp;·&nbsp;
                <?php
                $pm_labels = ['mtn_momo'=>'MTN MoMo','airtel_money'=>'Airtel Money','cash_on_delivery'=>'Cash on Delivery'];
                $pm_label = isset($pm_labels[$order['payment_method']]) ? $pm_labels[$order['payment_method']] : $order['payment_method'];
                ?>
                <i class="fa-solid fa-credit-card"></i> <?= $pm_label ?>
                <?php if ($order['payment_type'] === 'installment'): ?> (Installment)<?php endif; ?>
              </div>
              <a href="https://wa.me/256743741601?text=Hi%2C+I+want+to+enquire+about+order+<?= urlencode($order['order_number']) ?>" target="_blank" class="btn btn-whatsapp btn-sm">
                <i class="fa-brands fa-whatsapp"></i> Track / Support
              </a>
            </div>

            <!-- Installment details if available -->
            <?php
            $ip = $conn->prepare("SELECT * FROM installment_plans WHERE order_id=?");
            $ip->bind_param('i', $order['id']);
            $ip->execute();
            $iplan = $ip->get_result()->fetch_assoc();
            if ($iplan):
            ?>
              <div style="margin-top:.75rem;padding:.75rem;background:rgba(6,182,212,.06);border:1px solid rgba(6,182,212,.2);border-radius:8px;font-size:.8rem">
                <strong style="color:var(--cyan)"><i class="fa-solid fa-calendar"></i> Installment Plan</strong>
                — Deposit: <?= formatPrice($iplan['deposit_amount']) ?>
                | <?= formatPrice($iplan['weekly_payment']) ?>/week for <?= $iplan['weeks'] ?> weeks
                | Paid: <?= $iplan['weeks_paid'] ?>/<?= $iplan['weeks'] ?> weeks
                | Status: <strong style="color:<?= $iplan['status']==='active'?'var(--green)':($iplan['status']==='completed'?'var(--blue-light)':'var(--red)') ?>"><?= ucfirst($iplan['status']) ?></strong>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
