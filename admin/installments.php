<?php
$page_title = "Installment Plans";
require_once __DIR__ . '/includes/admin_header.php';

$allowed_filters = array('', 'active', 'completed', 'defaulted');
$filter = (isset($_GET['status']) && in_array($_GET['status'], $allowed_filters)) ? $_GET['status'] : '';
$where  = $filter ? "WHERE ip.status='" . $conn->real_escape_string($filter) . "'" : '';

$plans = $conn->query("
    SELECT ip.*, o.order_number, o.phone, o.total_amount,
           u.name as customer_name, u.email as customer_email
    FROM installment_plans ip
    JOIN orders o ON ip.order_id=o.id
    JOIN users u ON o.user_id=u.id
    $where ORDER BY ip.created_at DESC
");
$rows = $plans->fetch_all(MYSQLI_ASSOC);
?>

<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem">
  <?php foreach([''=>'All','active'=>'Active','completed'=>'Completed','defaulted'=>'Defaulted'] as $val=>$label): ?>
    <a href="?<?= $val?'status='.$val:'' ?>" class="category-pill <?= $filter===$val?'active':'' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="data-table-wrap admin-table-desktop">
  <div class="data-table-head"><h3><?= count($rows) ?> Installment Plans</h3></div>
  <div style="overflow-x:auto">
    <table class="data-table" style="min-width:700px">
      <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Deposit</th><th>Weekly</th><th>Progress</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $p): ?>
          <tr>
            <td><a href="<?= SITE_URL ?>/admin/orders?view=<?= $p['order_id'] ?>" style="color:var(--blue);font-weight:700"><?= htmlspecialchars($p['order_number']) ?></a></td>
            <td><?= htmlspecialchars($p['customer_name']) ?></td>
            <td style="font-weight:700"><?= formatPrice($p['total_price']) ?></td>
            <td style="color:var(--orange);font-weight:700"><?= formatPrice($p['deposit_amount']) ?></td>
            <td style="color:var(--blue);font-weight:700"><?= formatPrice($p['weekly_payment']) ?>/wk</td>
            <td>
              <div style="display:flex;align-items:center;gap:.5rem">
                <span style="font-size:.8rem;font-weight:700"><?= $p['weeks_paid'] ?>/<?= $p['weeks'] ?></span>
                <div style="background:var(--border);border-radius:4px;height:6px;width:60px;overflow:hidden;flex-shrink:0">
                  <div style="height:100%;width:<?= min(100,round($p['weeks_paid']/$p['weeks']*100)) ?>%;background:var(--blue)"></div>
                </div>
              </div>
            </td>
            <td><span class="status-badge status-<?= $p['status']==='active'?'confirmed':($p['status']==='completed'?'delivered':'cancelled') ?>"><?= ucfirst($p['status']) ?></span></td>
            <td><a href="<?= SITE_URL ?>/admin/orders?view=<?= $p['order_id'] ?>" class="btn btn-primary btn-sm">Manage</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-cards-mobile">
  <div style="margin-bottom:1rem"><h3 style="font-size:1rem;font-weight:700"><?= count($rows) ?> Installment Plans</h3></div>
  <?php foreach ($rows as $p):
    $pct = min(100, round($p['weeks_paid'] / $p['weeks'] * 100));
  ?>
    <div class="admin-card">
      <div class="admin-card-header">
        <a href="<?= SITE_URL ?>/admin/orders?view=<?= $p['order_id'] ?>" style="color:var(--blue);font-weight:800;font-size:.875rem"><?= htmlspecialchars($p['order_number']) ?></a>
        <span class="status-badge status-<?= $p['status']==='active'?'confirmed':($p['status']==='completed'?'delivered':'cancelled') ?>"><?= ucfirst($p['status']) ?></span>
      </div>
      <div class="admin-card-row"><span>Customer</span><strong><?= htmlspecialchars($p['customer_name']) ?></strong></div>
      <div class="admin-card-row"><span>Total</span><strong><?= formatPrice($p['total_price']) ?></strong></div>
      <div class="admin-card-row"><span>Deposit</span><strong style="color:var(--orange)"><?= formatPrice($p['deposit_amount']) ?></strong></div>
      <div class="admin-card-row"><span>Weekly</span><strong style="color:var(--blue)"><?= formatPrice($p['weekly_payment']) ?>/wk</strong></div>
      <div class="admin-card-row"><span>Progress</span>
        <div style="display:flex;align-items:center;gap:.5rem">
          <span style="font-weight:700;font-size:.85rem"><?= $p['weeks_paid'] ?>/<?= $p['weeks'] ?> wks</span>
          <div style="background:var(--border);border-radius:4px;height:7px;width:80px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:var(--blue)"></div>
          </div>
        </div>
      </div>
      <div style="margin-top:.75rem">
        <a href="<?= SITE_URL ?>/admin/orders?view=<?= $p['order_id'] ?>" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">View & Manage</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
