<?php
$page_title = "Orders";
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCsrfToken()) { die('Invalid request.'); }
    $oid     = (int)$_POST['order_id'];
    // Whitelist allowed status values — no raw input in SQL
    $allowed_status  = array('pending','confirmed','processing','shipped','delivered','cancelled');
    $allowed_payment = array('unpaid','deposit_paid','paid');
    $status  = in_array($_POST['status'], $allowed_status)  ? $_POST['status']  : 'pending';
    $pstatus = in_array($_POST['payment_status'], $allowed_payment) ? $_POST['payment_status'] : 'unpaid';
    $upd = $conn->prepare("UPDATE orders SET status=?, payment_status=? WHERE id=?");
    $upd->bind_param('ssi', $status, $pstatus, $oid);
    $upd->execute();
    header('Location: ' . SITE_URL . '/admin/orders?view=' . $oid . '&msg=updated');
    exit;
}

if (isset($_GET['view'])) {
    $oid = (int)$_GET['view'];
    $ord_stmt = $conn->prepare("SELECT o.*, u.name as cname, u.email as cemail FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
    $ord_stmt->bind_param('i', $oid);
    $ord_stmt->execute();
    $order = $ord_stmt->get_result()->fetch_assoc();

    if ($order) {
        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
        $items_stmt->bind_param('i', $oid);
        $items_stmt->execute();
        $order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $ip_stmt = $conn->prepare("SELECT * FROM installment_plans WHERE order_id=?");
        $ip_stmt->bind_param('i', $oid);
        $ip_stmt->execute();
        $install = $ip_stmt->get_result()->fetch_assoc();
        ?>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success" style="margin-bottom:1rem">Order status updated.</div><?php endif; ?>

        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
          <a href="<?= SITE_URL ?>/admin/orders" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> All Orders</a>
          <h2 style="font-size:1rem;font-weight:700;color:var(--blue)"><?= htmlspecialchars($order['order_number']) ?></h2>
          <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
          <span class="status-badge status-<?= $order['payment_status']==='paid'?'paid':($order['payment_status']==='deposit_paid'?'deposit':'unpaid') ?>"><?= ucfirst(str_replace('_',' ',$order['payment_status'])) ?></span>
        </div>

        <div class="order-detail-grid">
          <div class="data-table-wrap" style="padding:1.25rem">
            <h4 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:1rem">Customer & Delivery</h4>
            <?php foreach([['Customer',$order['cname']],['Email',$order['cemail']],['Phone',$order['phone']],['Address',$order['address']],['Date',date('M j, Y H:i',strtotime($order['created_at']))]] as [$l,$v]): ?>
              <div style="display:flex;gap:.75rem;margin-bottom:.6rem;font-size:.875rem;flex-wrap:wrap">
                <span style="color:var(--text-muted);min-width:80px;flex-shrink:0"><?= $l ?>:</span>
                <span style="font-weight:600;word-break:break-word"><?= htmlspecialchars($v) ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="data-table-wrap" style="padding:1.25rem">
            <h4 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:1rem">Payment Info</h4>
            <?php
            $pm_labels = ['mtn_momo'=>'MTN Mobile Money','airtel_money'=>'Airtel Money','cash_on_delivery'=>'Cash on Delivery'];
            $pm_label = isset($pm_labels[$order['payment_method']]) ? $pm_labels[$order['payment_method']] : $order['payment_method'];
            foreach([['Method',$pm_label],['Type',ucfirst(str_replace('_',' ',$order['payment_type']))],['MoMo No.',$order['momo_number']?:'N/A'],['Total',formatPrice($order['total_amount'])],['Deposit',$order['deposit_paid']>0?formatPrice($order['deposit_paid']):'N/A']] as [$l,$v]):
            ?>
              <div style="display:flex;gap:.75rem;margin-bottom:.6rem;font-size:.875rem;flex-wrap:wrap">
                <span style="color:var(--text-muted);min-width:80px;flex-shrink:0"><?= $l ?>:</span>
                <span style="font-weight:600;word-break:break-word"><?= htmlspecialchars($v) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="data-table-wrap" style="margin-top:1.5rem">
          <div class="data-table-head"><h3>Order Items</h3></div>
          <div style="overflow-x:auto">
            <table class="data-table" style="min-width:400px">
              <thead><tr><th>Product</th><th>Storage</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
              <tbody>
                <?php foreach ($order_items as $oi): ?>
                  <tr>
                    <td style="font-weight:600"><?= htmlspecialchars($oi['product_name']) ?></td>
                    <td><?= htmlspecialchars($oi['storage']) ?></td>
                    <td><?= $oi['quantity'] ?></td>
                    <td><?= formatPrice($oi['unit_price']) ?></td>
                    <td style="font-weight:700;color:var(--blue)"><?= formatPrice($oi['total_price']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <?php if ($install): ?>
        <div style="background:var(--blue-pale);border:1px solid var(--border-blue);border-radius:var(--radius);padding:1.25rem;margin-top:1.5rem">
          <h4 style="color:var(--blue);margin-bottom:1rem;font-size:.9rem"><i class="fa-solid fa-calendar-check"></i> Installment Plan</h4>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;margin-bottom:1rem">
            <?php foreach([['Total',formatPrice($install['total_price'])],['Deposit',formatPrice($install['deposit_amount'])],['Balance',formatPrice($install['balance'])],['Duration',$install['weeks'].' wks'],['Weekly',formatPrice($install['weekly_payment'])],['Paid',$install['weeks_paid'].'/'.$install['weeks']],['Status',ucfirst($install['status'])]] as [$l,$v]): ?>
              <div style="background:#fff;padding:.7rem;border-radius:8px;border:1px solid var(--border)">
                <div style="font-size:.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em"><?= $l ?></div>
                <div style="font-weight:700;margin-top:.2rem;color:var(--blue)"><?= htmlspecialchars($v) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <form method="POST" style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap">
            <?= csrfField() ?>
            <input type="hidden" name="update_install" value="1"/>
            <input type="hidden" name="install_id" value="<?= $install['id'] ?>"/>
            <div>
              <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.3rem">Weeks Paid</label>
              <input type="number" name="weeks_paid" value="<?= $install['weeks_paid'] ?>" min="0" max="<?= $install['weeks'] ?>" style="width:80px;background:#fff;border:1.5px solid var(--border);border-radius:6px;color:var(--text-primary);padding:.4rem .6rem"/>
            </div>
            <div>
              <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.3rem">Status</label>
              <select name="install_status" style="background:#fff;border:1.5px solid var(--border);border-radius:6px;color:var(--text-primary);padding:.4rem .6rem">
                <option value="active"    <?= $install['status']==='active'?'selected':''?>>Active</option>
                <option value="completed" <?= $install['status']==='completed'?'selected':''?>>Completed</option>
                <option value="defaulted" <?= $install['status']==='defaulted'?'selected':''?>>Defaulted</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Update Plan</button>
          </form>
        </div>
        <?php endif; ?>

        <div class="data-table-wrap" style="padding:1.25rem;margin-top:1.5rem">
          <h4 style="margin-bottom:1rem;font-size:.9rem;color:var(--orange)"><i class="fa-solid fa-pen-to-square"></i> Update Order Status</h4>
          <form method="POST" style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap">
            <?= csrfField() ?>
            <input type="hidden" name="update_status" value="1"/>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>"/>
            <div>
              <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.3rem">Order Status</label>
              <select name="status" class="filter-select">
                <?php foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $order['status']===$s?'selected':''?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.3rem">Payment Status</label>
              <select name="payment_status" class="filter-select">
                <option value="unpaid"       <?= $order['payment_status']==='unpaid'?'selected':''?>>Unpaid</option>
                <option value="deposit_paid" <?= $order['payment_status']==='deposit_paid'?'selected':''?>>Deposit Paid</option>
                <option value="paid"         <?= $order['payment_status']==='paid'?'selected':''?>>Fully Paid</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
          </form>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_install'])) {
            if (!verifyCsrfToken()) { die('Invalid request.'); }
            $iid   = (int)$_POST['install_id'];
            $wpaid = (int)$_POST['weeks_paid'];
            $allowed_ist = array('active','completed','defaulted');
            $ist = in_array($_POST['install_status'], $allowed_ist) ? $_POST['install_status'] : 'active';
            $upd_ip = $conn->prepare("UPDATE installment_plans SET weeks_paid=?, status=? WHERE id=?");
            $upd_ip->bind_param('isi', $wpaid, $ist, $iid);
            $upd_ip->execute();
            header('Location: ' . SITE_URL . '/admin/orders?view=' . $oid . '&msg=updated');
            exit;
        }
        require_once __DIR__ . '/includes/admin_footer.php';
        exit;
    }
}

$allowed_statuses = array('','pending','confirmed','processing','shipped','delivered','cancelled');
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $allowed_statuses) ? $_GET['status'] : '';
$where = $filter_status ? "WHERE o.status='" . $conn->real_escape_string($filter_status) . "'" : '';
$orders_res = $conn->query("
    SELECT o.*, u.name as cname, COUNT(oi.id) as item_count
    FROM orders o JOIN users u ON o.user_id=u.id
    LEFT JOIN order_items oi ON o.id=oi.order_id
    $where
    GROUP BY o.id ORDER BY o.created_at DESC
");
?>

<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem">
  <?php foreach([''=>'All','pending'=>'Pending','confirmed'=>'Confirmed','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $val=>$label): ?>
    <a href="?<?= $val?'status='.$val:'' ?>" class="category-pill <?= $filter_status===$val?'active':'' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="data-table-wrap admin-table-desktop">
  <div class="data-table-head"><h3><?= number_format($orders_res->num_rows) ?> Orders</h3></div>
  <div style="overflow-x:auto">
    <table class="data-table" style="min-width:700px">
      <thead><tr>
        <th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Type</th><th>Status</th><th>Date</th><th></th>
      </tr></thead>
      <tbody>
        <?php
        $orders_res2 = $conn->query("SELECT o.*, u.name as cname, COUNT(oi.id) as item_count FROM orders o JOIN users u ON o.user_id=u.id LEFT JOIN order_items oi ON o.id=oi.order_id $where GROUP BY o.id ORDER BY o.created_at DESC");
        while ($o = $orders_res2->fetch_assoc()):
          $pm_map=['mtn_momo'=>'MTN','airtel_money'=>'Airtel','cash_on_delivery'=>'COD'];
        ?>
          <tr>
            <td><a href="?view=<?= $o['id'] ?>" style="color:var(--blue);font-weight:700"><?= htmlspecialchars($o['order_number']) ?></a></td>
            <td><?= htmlspecialchars($o['cname']) ?></td>
            <td style="font-weight:700"><?= formatPrice($o['total_amount']) ?></td>
            <td style="font-size:.8rem"><?= isset($pm_map[$o['payment_method']]) ? $pm_map[$o['payment_method']] : $o['payment_method'] ?></td>
            <td><span class="status-badge" style="background:<?= $o['payment_type']==='installment'?'rgba(6,182,212,.12)':'rgba(34,197,94,.12)' ?>;color:<?= $o['payment_type']==='installment'?'#0891b2':'#16a34a' ?>"><?= ucfirst($o['payment_type']) ?></span></td>
            <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= date('M j', strtotime($o['created_at'])) ?></td>
            <td><a href="?view=<?= $o['id'] ?>" class="btn btn-primary btn-sm">View</a></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="admin-cards-mobile">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h3 style="font-size:1rem;font-weight:700"><?= number_format($orders_res->num_rows) ?> Orders</h3>
  </div>
  <?php
  $orders_res3 = $conn->query("SELECT o.*, u.name as cname FROM orders o JOIN users u ON o.user_id=u.id $where ORDER BY o.created_at DESC");
  while ($o = $orders_res3->fetch_assoc()):
    $pm_map=['mtn_momo'=>'MTN MoMo','airtel_money'=>'Airtel','cash_on_delivery'=>'Cash/COD'];
  ?>
    <div class="admin-card">
      <div class="admin-card-header">
        <a href="?view=<?= $o['id'] ?>" style="color:var(--blue);font-weight:800;font-size:.9rem"><?= htmlspecialchars($o['order_number']) ?></a>
        <span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
      </div>
      <div class="admin-card-row"><span>Customer</span><strong><?= htmlspecialchars($o['cname']) ?></strong></div>
      <div class="admin-card-row"><span>Total</span><strong style="color:var(--blue)"><?= formatPrice($o['total_amount']) ?></strong></div>
      <div class="admin-card-row"><span>Payment</span><span><?= isset($pm_map[$o['payment_method']]) ? $pm_map[$o['payment_method']] : $o['payment_method'] ?> &bull; <?= ucfirst($o['payment_type']) ?></span></div>
      <div class="admin-card-row"><span>Date</span><span><?= date('M j, Y', strtotime($o['created_at'])) ?></span></div>
      <div style="margin-top:.75rem">
        <a href="?view=<?= $o['id'] ?>" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">View & Manage</a>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
