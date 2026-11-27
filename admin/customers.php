<?php
$page_title = "Customers";
require_once __DIR__ . '/includes/admin_header.php';

$customers = $conn->query("
    SELECT u.*, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent
    FROM users u LEFT JOIN orders o ON u.id=o.user_id
    WHERE u.role='customer'
    GROUP BY u.id ORDER BY u.created_at DESC
");
$rows = $customers->fetch_all(MYSQLI_ASSOC);
?>

<div class="data-table-wrap admin-table-desktop">
  <div class="data-table-head"><h3><?= count($rows) ?> Customers</h3></div>
  <div style="overflow-x:auto">
    <table class="data-table" style="min-width:560px">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Spent</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $c): ?>
          <tr>
            <td style="font-weight:600">
              <div style="display:flex;align-items:center;gap:.6rem">
                <div style="width:32px;height:32px;border-radius:50%;background:var(--blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0"><?= strtoupper(substr($c['name'],0,1)) ?></div>
                <span><?= htmlspecialchars($c['name']) ?></span>
              </div>
            </td>
            <td style="color:var(--blue);font-size:.85rem"><?= htmlspecialchars($c['email']) ?></td>
            <td style="font-size:.85rem"><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
            <td style="font-weight:700;text-align:center"><?= $c['order_count'] ?></td>
            <td style="font-weight:700;color:var(--green)"><?= $c['total_spent'] ? formatPrice($c['total_spent']) : '—' ?></td>
            <td style="font-size:.8rem;color:var(--text-muted)"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-cards-mobile">
  <div style="margin-bottom:1rem"><h3 style="font-size:1rem;font-weight:700"><?= count($rows) ?> Customers</h3></div>
  <?php foreach ($rows as $c): ?>
    <div class="admin-card">
      <div class="admin-card-header">
        <div style="display:flex;align-items:center;gap:.6rem">
          <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--cyan));color:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:800;flex-shrink:0"><?= strtoupper(substr($c['name'],0,1)) ?></div>
          <span style="font-weight:700"><?= htmlspecialchars($c['name']) ?></span>
        </div>
        <span style="font-size:.75rem;color:var(--text-muted)"><?= date('M j, Y', strtotime($c['created_at'])) ?></span>
      </div>
      <div class="admin-card-row"><span>Email</span><span style="color:var(--blue);font-size:.85rem;word-break:break-all"><?= htmlspecialchars($c['email']) ?></span></div>
      <?php if ($c['phone']): ?>
        <div class="admin-card-row"><span>Phone</span><span><?= htmlspecialchars($c['phone']) ?></span></div>
      <?php endif; ?>
      <div class="admin-card-row"><span>Orders</span><strong><?= $c['order_count'] ?></strong></div>
      <div class="admin-card-row"><span>Total Spent</span><strong style="color:var(--green)"><?= $c['total_spent'] ? formatPrice($c['total_spent']) : '—' ?></strong></div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
