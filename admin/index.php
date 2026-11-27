<?php
$page_title = "Dashboard";
require_once __DIR__ . '/includes/admin_header.php';

// Stats
$stats = [
  'total_orders'    => $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'],
  'pending_orders'  => $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'],
  'total_revenue'   => $conn->query("SELECT SUM(total_amount) as s FROM orders WHERE status NOT IN ('cancelled')")->fetch_assoc()['s'] ?? 0,
  'total_customers' => $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'],
  'total_products'  => $conn->query("SELECT COUNT(*) as c FROM products WHERE is_active=1")->fetch_assoc()['c'],
  'low_stock'       => $conn->query("SELECT COUNT(*) as c FROM products WHERE stock_qty <= 2 AND is_active=1")->fetch_assoc()['c'],
];

$recent_orders = $conn->query("
  SELECT o.*, u.name as customer_name, COUNT(oi.id) as item_count
  FROM orders o JOIN users u ON o.user_id=u.id
  LEFT JOIN order_items oi ON o.id=oi.order_id
  GROUP BY o.id ORDER BY o.created_at DESC LIMIT 8
");

$low_stock_res = $conn->query("SELECT * FROM products WHERE stock_qty <= 2 AND is_active=1 ORDER BY stock_qty ASC LIMIT 6");
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-info">
      <label>Total Orders</label>
      <div class="stat-num gradient-text"><?= number_format($stats['total_orders']) ?></div>
      <div class="stat-sub"><?= $stats['pending_orders'] ?> pending</div>
    </div>
    <div class="stat-icon si-blue"><i class="fa-solid fa-box"></i></div>
  </div>
  <div class="stat-card">
    <div class="stat-info">
      <label>Total Revenue</label>
      <div class="stat-num gradient-text">UGX <?= number_format($stats['total_revenue']) ?></div>
      <div class="stat-sub">All confirmed orders</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-info">
      <label>Customers</label>
      <div class="stat-num gradient-text"><?= number_format($stats['total_customers']) ?></div>
      <div class="stat-sub">Registered accounts</div>
    </div>
    <div class="stat-icon si-cyan"><i class="fa-solid fa-users"></i></div>
  </div>
  <div class="stat-card">
    <div class="stat-info">
      <label>Products</label>
      <div class="stat-num gradient-text"><?= $stats['total_products'] ?></div>
      <div class="stat-sub" style="color:<?= $stats['low_stock']>0?'var(--orange)':'var(--gray-400)' ?>"><?= $stats['low_stock'] ?> low stock</div>
    </div>
    <div class="stat-icon si-orange"><i class="fa-solid fa-mobile-screen"></i></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

  <div class="data-table-wrap" style="grid-column:1/-1">
    <div class="data-table-head">
      <h3>Recent Orders</h3>
      <a href="<?= SITE_URL ?>/admin/orders" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div style="overflow-x:auto">
      <table class="data-table">
        <thead><tr>
          <th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Type</th><th>Status</th><th>Date</th><th></th>
        </tr></thead>
        <tbody>
          <?php while ($o = $recent_orders->fetch_assoc()): ?>
            <tr>
              <td><a href="<?= SITE_URL ?>/admin/orders?view=<?= $o['id'] ?>" style="color:var(--blue-light);font-weight:700"><?= htmlspecialchars($o['order_number']) ?></a></td>
              <td><?= htmlspecialchars($o['customer_name']) ?></td>
              <td><?= $o['item_count'] ?></td>
              <td style="font-weight:700"><?= formatPrice($o['total_amount']) ?></td>
              <td style="font-size:.8rem"><?php $pm_map=['mtn_momo'=>'MTN MoMo','airtel_money'=>'Airtel','cash_on_delivery'=>'Cash/COD']; echo isset($pm_map[$o['payment_method']]) ? $pm_map[$o['payment_method']] : htmlspecialchars($o['payment_method']); ?></td>
              <td><span class="status-badge" style="background:<?= $o['payment_type']==='installment'?'rgba(6,182,212,.15)':'rgba(34,197,94,.15)' ?>;color:<?= $o['payment_type']==='installment'?'var(--cyan)':'var(--green)' ?>">
                <?= ucfirst($o['payment_type']) ?>
              </span></td>
              <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
              <td style="font-size:.8rem;color:var(--gray-400)"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
              <td>
                <a href="<?= SITE_URL ?>/admin/orders?view=<?= $o['id'] ?>" class="btn btn-primary btn-sm">View</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($low_stock_res->num_rows > 0): ?>
  <div class="data-table-wrap">
    <div class="data-table-head">
      <h3 style="color:var(--orange)"><i class="fa-solid fa-triangle-exclamation"></i> Low Stock Alert</h3>
      <a href="<?= SITE_URL ?>/admin/products" class="btn btn-outline btn-sm">Manage</a>
    </div>
    <table class="data-table">
      <thead><tr><th>Product</th><th>Stock</th><th></th></tr></thead>
      <tbody>
        <?php while ($lp = $low_stock_res->fetch_assoc()): ?>
          <tr>
            <td style="font-size:.85rem;font-weight:600"><?= htmlspecialchars($lp['name']) ?></td>
            <td><span style="color:<?= $lp['stock_qty']==0?'var(--red)':'var(--orange)' ?>;font-weight:800"><?= $lp['stock_qty'] ?></span></td>
            <td><a href="<?= SITE_URL ?>/admin/products?edit=<?= $lp['id'] ?>" class="btn btn-orange btn-sm">Update</a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
