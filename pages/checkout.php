<?php
$page_title = "Checkout";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/pesapal.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];

// ---- Buy Now: add product directly to cart then continue to checkout ----
if (isset($_GET['buy_now'])) {
    $buy_id = (int)$_GET['buy_now'];
    if ($buy_id > 0) {
        // Verify product exists, is active and has stock
        $chk = $conn->prepare("SELECT id FROM products WHERE id=? AND is_active=1 AND stock_qty>0 AND price>0");
        $chk->bind_param('i', $buy_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            // Add to cart (or set qty to 1 if already there)
            $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,1) ON DUPLICATE KEY UPDATE quantity=1");
            $ins->bind_param('ii', $user_id, $buy_id);
            $ins->execute();
        }
    }
    // Redirect clean — removes buy_now from URL so form POST works correctly
    header('Location: ' . SITE_URL . '/checkout');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$user_id = (int)$_SESSION['user_id'];

// Load user
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Load cart
$cart_stmt = $conn->prepare("
    SELECT c.quantity, p.id as product_id, p.name, p.price, p.storage, p.image, p.condition_type, p.stock_qty
    FROM cart c JOIN products p ON c.product_id=p.id
    WHERE c.user_id=?
");
$cart_stmt->bind_param('i', $user_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    header('Location: ' . SITE_URL . '/cart');
    exit;
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$error = '';

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name']      ?? '');
    $email          = trim($_POST['email']          ?? '');
    $phone          = trim($_POST['phone']          ?? '');
    $fulfillment    = trim($_POST['fulfillment']    ?? 'pickup');
    $country        = trim($_POST['country']        ?? 'Uganda');
    $city           = trim($_POST['city']           ?? '');
    $address        = trim($_POST['address']        ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $momo_number    = ''; // No longer collected � Pesapal handles this
    $payment_type   = trim($_POST['payment_type']   ?? 'full');
    $weeks          = (int)($_POST['weeks']         ?? 0);
    $notes          = trim($_POST['notes']          ?? '');

    if ($fulfillment === 'pickup') {
        $address = 'Pick Up at Store � Pioneer Mall, Room PG52, Kampala';
    }

    if (!$full_name || !$phone || !$email || !$payment_method) {
        $error = 'Please fill in all required fields including email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($fulfillment === 'delivery' && (!$city || !$address)) {
        $error = 'Please enter your delivery city and address.';
    } elseif ($payment_type === 'installment' && !in_array($weeks, array(10,12,16))) {
        $error = 'Please select an installment plan (10, 12 or 16 weeks).';
    } else {
        foreach ($cart_items as $item) {
            if ($item['stock_qty'] < $item['quantity']) {
                $error = 'Sorry, insufficient stock for: ' . htmlspecialchars($item['name']);
                break;
            }
        }
    }

    if (!$error) {
        $conn->begin_transaction();
        try {
            $order_number = 'MRU-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $deposit_paid = 0;
            $total_amount = $subtotal;
            if ($payment_type === 'installment') {
                $deposit_paid = round($subtotal * 0.4);
            }

            $ord = $conn->prepare("INSERT INTO orders
                (user_id, order_number, full_name, email, phone, address, fulfillment, country, city,
                 payment_method, momo_number, payment_type, subtotal, total_amount, deposit_paid, notes)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $ord->bind_param('isssssssssssddds',
                $user_id, $order_number, $full_name, $email, $phone, $address,
                $fulfillment, $country, $city,
                $payment_method, $momo_number, $payment_type,
                $subtotal, $total_amount, $deposit_paid, $notes
            );
            $ord->execute();
            $order_id = $conn->insert_id;

            foreach ($cart_items as $item) {
                $line_total = $item['price'] * $item['quantity'];
                $oi = $conn->prepare("INSERT INTO order_items
                    (order_id, product_id, product_name, storage, quantity, unit_price, total_price)
                    VALUES (?,?,?,?,?,?,?)");
                $oi->bind_param('iissidd',
                    $order_id, $item['product_id'], $item['name'],
                    $item['storage'], $item['quantity'], $item['price'], $line_total
                );
                $oi->execute();
                $upd = $conn->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");
                $upd->bind_param('ii', $item['quantity'], $item['product_id']);
                $upd->execute();
            }

            if ($payment_type === 'installment') {
                $balance       = $subtotal - $deposit_paid;
                $weekly_amount = (int)ceil($balance / $weeks);
                $ip = $conn->prepare("INSERT INTO installment_plans
                    (order_id, total_price, deposit_amount, balance, weeks, weekly_payment)
                    VALUES (?,?,?,?,?,?)");
                $ip->bind_param('idddid', $order_id, $subtotal, $deposit_paid, $balance, $weeks, $weekly_amount);
                $ip->execute();
            }

            $del = $conn->prepare("DELETE FROM cart WHERE user_id=?");
            $del->bind_param('i', $user_id);
            $del->execute();

            $conn->commit();

            if (in_array($payment_method, array('mtn_momo', 'airtel_money'))) {
                try {
                    $charge_amount = ($payment_type === 'installment') ? $deposit_paid : $total_amount;
                    $name_parts  = explode(' ', trim($full_name));
                    $fname       = $name_parts[0] ?? $full_name;
                    $lname       = isset($name_parts[1]) ? implode(' ', array_slice($name_parts, 1)) : '.';
                    $pp_phone = preg_replace('/[^0-9]/', '', $phone);
                    if (substr($pp_phone, 0, 1) === '0') $pp_phone = '256' . substr($pp_phone, 1);
                    if (substr($pp_phone, 0, 3) !== '256') $pp_phone = '256' . $pp_phone;
                    $pp_phone = '+' . $pp_phone;

                    $desc = ($payment_type === 'installment')
                        ? 'Mr. Upgrade - ' . $order_number . ' (40% deposit)'
                        : 'Mr. Upgrade - ' . $order_number;

                    $pesapal   = new PesapalAPI();
                    $pp_result = $pesapal->submitOrder(array(
                        'order_number' => $order_number,
                        'amount'       => $charge_amount,
                        'description'  => $desc,
                        'email'        => $user['email'],
                        'phone'        => $pp_phone,
                        'first_name'   => $fname,
                        'last_name'    => $lname,
                        'address'      => $address,
                        'city'         => $city,
                    ));

                    if (isset($pp_result['redirect_url'])) {
                        $tracking_id  = $pp_result['order_tracking_id'] ?? '';
                        $redirect_url = $pp_result['redirect_url'];
                        $conn->prepare("UPDATE orders SET pesapal_tracking_id=?, pesapal_redirect_url=? WHERE id=?")
                             ->bind_param('ssi', $tracking_id, $redirect_url, $order_id) | null;
                        $ppu = $conn->prepare("UPDATE orders SET pesapal_tracking_id=?, pesapal_redirect_url=? WHERE id=?");
                        $ppu->bind_param('ssi', $tracking_id, $redirect_url, $order_id);
                        $ppu->execute();
                        $ppt = $conn->prepare("INSERT INTO pesapal_transactions (order_id, order_number, pesapal_tracking_id, amount) VALUES (?,?,?,?)");
                        $ppt->bind_param('issd', $order_id, $order_number, $tracking_id, $charge_amount);
                        $ppt->execute();
                        header('Location: ' . $redirect_url);
                        exit;
                    } else {
                        $pp_error = $pp_result['error']['message'] ?? 'Payment gateway error. Your order is saved � please pay manually.';
                    }
                } catch (Exception $e) {
                    $pp_error = 'Payment gateway unavailable: ' . $e->getMessage();
                }
            }
            header('Location: ' . SITE_URL . '/order-confirm?order=' . $order_number);
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Order failed. Please try again. (' . $e->getMessage() . ')';
        }
    }
}

$deposit    = round($subtotal * 0.4);
$balance_10 = (int)ceil(($subtotal - $deposit) / 10);
$balance_12 = (int)ceil(($subtotal - $deposit) / 12);
$balance_16 = (int)ceil(($subtotal - $deposit) / 16);
$sel_ptype       = isset($_POST['payment_type'])   ? $_POST['payment_type']   : 'full';
$sel_weeks       = isset($_POST['weeks'])          ? (int)$_POST['weeks']     : 16;
$sel_method      = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$sel_fulfillment = isset($_POST['fulfillment'])    ? $_POST['fulfillment']     : 'pickup';
?>

<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <a href="<?= SITE_URL ?>/cart">Cart</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>Checkout</span>
    </div>
    <h1>Checkout</h1>
  </div>
</div>

<div class="container" style="padding:2.5rem 1.25rem 5rem">

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1.5rem">
      <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" id="checkoutForm">
    <?= csrfField() ?>
  <div class="checkout-layout">
    <div>
      <div class="form-section" style="background:var(--bg-section);">
        <h3 class="form-section-title"><i class="fa-solid fa-user"></i> Contact Information</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Full Name <span style="color:var(--red)">*</span></label>
            <input type="text" name="full_name" placeholder="e.g. John Mukasa"
                   value="<?= htmlspecialchars(isset($_POST['full_name']) ? $_POST['full_name'] : $user['name']) ?>" required />
          </div>
          <div class="form-group">
            <label>Email Address <span style="color:var(--red)">*</span></label>
            <input type="email" name="email" placeholder="you@example.com"
                   value="<?= htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ($user['email'] ?? '')) ?>" required />
          </div>
        </div>
        <div class="form-group">
          <label>Phone Number <span style="color:var(--red)">*</span></label>
          <div style="display:flex;gap:.5rem;align-items:stretch">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:.75rem .9rem;display:flex;align-items:center;gap:.4rem;white-space:nowrap;font-size:.9rem;color:var(--text-muted);min-width:90px;justify-content:center;font-weight:600">
              <span>UG</span>
              <span>+256</span>
            </div>
            <input type="tel" name="phone" placeholder="7XX XXX XXX" style="flex:1"
                   value="<?= htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : ($user['phone'] ?? '')) ?>" required />
          </div>
        </div>
      </div>

      <div class="form-section" style="background:var(--bg-section);">
        <h3 class="form-section-title"><i class="fa-solid fa-truck"></i> Delivery Details</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
          <label class="payment-option <?= $sel_fulfillment==='pickup'?'selected':'' ?>"
                 onclick="selectFulfillment(this,'pickup')">
            <input type="radio" name="fulfillment" value="pickup" <?= $sel_fulfillment==='pickup'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name">
                <i class="fa-solid fa-store" style="color:var(--blue-light)"></i> &nbsp; Pick Up at Store
              </div>
              <div class="payment-option-desc">Pioneer Mall, Room PG52 &bull; Free</div>
            </div>
          </label>

          <label class="payment-option <?= $sel_fulfillment==='delivery'?'selected':'' ?>"
                 onclick="selectFulfillment(this,'delivery')">
            <input type="radio" name="fulfillment" value="delivery" <?= $sel_fulfillment==='delivery'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name">
                <i class="fa-solid fa-motorcycle" style="color:var(--orange)"></i> &nbsp; Deliver to Me
              </div>
              <div class="payment-option-desc">Kampala &amp; surrounding areas</div>
            </div>
          </label>
        </div>

        <div id="pickupInfo" style="<?= $sel_fulfillment==='delivery'?'display:none':'' ?>">
          <div style="background:rgba(37,99,235,.07);border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;display:flex;gap:.75rem;align-items:flex-start">
            <div>
              <p style="font-weight:700;font-size:.9rem;margin-bottom:.2rem">Mr. Upgrade Store</p>
              <p style="color:var(--gray-400);font-size:.825rem">Pioneer Mall, Room PG52, Kampala</p>
              <p style="color:var(--gray-400);font-size:.8rem;margin-top:.3rem">
                <i class="fa-solid fa-clock" style="color:var(--gray-400)"></i>
                Open daily 8:00 AM - 8:00 PM
              </p>
            </div>
          </div>
        </div>

        <div id="deliveryFields" style="<?= $sel_fulfillment==='delivery'?'':'display:none' ?>">

          <div class="form-row">
            <div class="form-group">
              <label>City / Area <span style="color:var(--red)">*</span></label>
              <input type="text" name="city" placeholder="e.g. Kampala, Entebbe, Wakiso"
                     value="<?= htmlspecialchars(isset($_POST['city']) ? $_POST['city'] : '') ?>" />
            </div>
            <div class="form-group">
              <label>Zone / Parish</label>
              <input type="text" name="zone" placeholder="e.g. Nakasero, Ntinda, Naalya"
                     value="<?= htmlspecialchars(isset($_POST['zone']) ? $_POST['zone'] : '') ?>" />
            </div>
          </div>
          <div class="form-group">
            <label>Street Address / Landmark <span style="color:var(--red)">*</span></label>
            <input type="text" name="address" placeholder="e.g. Opposite Freedom City, next to Shell petrol station"
                   value="<?= htmlspecialchars(isset($_POST['address']) ? $_POST['address'] : ($user['address'] ?? '')) ?>" />
          </div>
          <div style="background:rgba(249,115,22,.07);border:1px solid rgba(249,115,22,.2);border-radius:8px;padding:.75rem 1rem;font-size:.8rem;color:var(--gray-300);margin-top:.25rem">
            <i class="fa-solid fa-circle-info" style="color:var(--orange)"></i>
            Delivery fee is discussed and agreed before dispatch. We cover Kampala and surrounding areas.
          </div>
        </div>

        <div class="form-group" style="margin-top:1.25rem;margin-bottom:0">
          <label>Order Notes <span style="color:var(--gray-500);font-weight:400">(optional)</span></label>
          <textarea name="notes" rows="2" style="resize: vertical;"placeholder="Special instructions, colour preferences, best time to call..."><?= htmlspecialchars(isset($_POST['notes']) ? $_POST['notes'] : '') ?></textarea>
        </div>
      </div>

      <div class="form-section" style="background:var(--bg-section);">
        <h3 class="form-section-title"><i class="fa-solid fa-credit-card"></i> Payment Type</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
          <label class="payment-option <?= $sel_ptype==='full'?'selected':'' ?>" onclick="selectPayType(this,'full')">
            <input type="radio" name="payment_type" value="full" <?= $sel_ptype==='full'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name"><i class="fa-solid fa-wallet" style="color:var(--cyan)"></i> &nbsp;Full Payment</div>
              <div class="payment-option-desc">Pay the total amount now</div>
            </div>
          </label>
          <label class="payment-option <?= $sel_ptype==='installment'?'selected':'' ?>" onclick="selectPayType(this,'installment')">
            <input type="radio" name="payment_type" value="installment" <?= $sel_ptype==='installment'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name"><i class="fa-solid fa-calendar-check" style="color:var(--cyan)"></i> &nbsp;Installment Plan</div>
              <div class="payment-option-desc">40% deposit + weekly payments</div>
            </div>
          </label>
        </div>
        <div id="installSection" style="<?= $sel_ptype==='installment' ? '' : 'display:none' ?>">
          <p style="font-size:.85rem;color:var(--gray-400);margin-bottom:1rem">
            Deposit due today: <strong style="color:var(--orange)"><?= formatPrice($deposit) ?></strong>
          </p>
          <div class="plan-selector">
            <div class="plan-option <?= $sel_weeks===10?'selected':'' ?>"
                 data-weeks="10" data-deposit="<?= $deposit ?>" data-weekly="<?= $balance_10 ?>" onclick="selectPlan(this)">
              <span class="plan-weeks">10 Weeks</span>
              <div class="plan-amount"><?= formatPrice($balance_10) ?>/wk</div>
            </div>
            <div class="plan-option <?= $sel_weeks===12?'selected':'' ?>"
                 data-weeks="12" data-deposit="<?= $deposit ?>" data-weekly="<?= $balance_12 ?>" onclick="selectPlan(this)">
              <span class="plan-weeks">12 Weeks</span>
              <div class="plan-amount"><?= formatPrice($balance_12) ?>/wk</div>
            </div>
            <div class="plan-option <?= $sel_weeks===16?'selected':'' ?>"
                 data-weeks="16" data-deposit="<?= $deposit ?>" data-weekly="<?= $balance_16 ?>" onclick="selectPlan(this)">
              <span class="plan-weeks">16 Weeks</span>
              <div class="plan-amount" style="color:var(--green)"><?= formatPrice($balance_16) ?>/wk</div>
              <div style="font-size:.65rem;color:var(--green);margin-top:.2rem">Most Popular</div>
            </div>
          </div>
          <input type="hidden" name="weeks" id="selectedWeeks" value="<?= $sel_weeks ?>" />
        </div>
      </div>

      <div class="form-section" style="background:var(--bg-section);">
        <h3 class="form-section-title"><i class="fa-solid fa-mobile-screen"></i> Payment Method</h3>
        <div class="payment-options">

          <label class="payment-option <?= $sel_method==='mtn_momo'?'selected':'' ?>" onclick="selectMethod(this,'mtn_momo')">
            <input type="radio" name="payment_method" value="mtn_momo" <?= $sel_method==='mtn_momo'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name" style="color:#FFCC00;display:inline-flex;align-items:center;gap:10px;margin-bottom:4px;">
                <svg width="36" height="36" viewBox="0 0 64 64" xmlns="http://w3.org" role="img" aria-label="MTN MoMo">
                  <rect width="64" height="64" rx="12" fill="#FFCC00"/>
                  <ellipse cx="32" cy="32" rx="26" ry="15" fill="none" stroke="#000000" stroke-width="2.5"/>
                  <text x="32" y="36" font-family="Arial, sans-serif" font-weight="900" font-size="14" fill="#000000" text-anchor="middle">MTN</text>
                </svg>
                MTN Mobile Money
              </div>
              <div class="payment-option-desc">You'll be redirected to enter your number &amp; PIN securely.</div>
            </div>
          </label>

          <label class="payment-option <?= $sel_method==='airtel_money'?'selected':'' ?>" onclick="selectMethod(this,'airtel_money')">
            <input type="radio" name="payment_method" value="airtel_money" <?= $sel_method==='airtel_money'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name" style="color:#E40000;display:inline-flex;align-items:center;gap:10px;margin-bottom:4px;">
                <svg width="36" height="36" viewBox="0 0 100 100" xmlns="http://w3.org" role="img" aria-label="Airtel Money">
                  <rect width="100" height="100" rx="20" fill="#E40000"/>
                  <path d="M55.2 14C39.8 14 27 24.4 27 36.2c0 5.3 3.3 8.5 7.7 8.5 4.2 0 8.5-3.4 13.5-7.4 4.4-3.5 8.6-6.2 11.7-6.2 2.4 0 3.8 1.7 3.8 4.6 0 6.8-8.2 15.8-18.4 20.1-2.5 1.1-4.1.6-4.1-.7 0-1.6 1.7-3.9 4.5-6.6 2.1-2 2.9-4.1 1.9-5.6-1.1-1.6-3.8-1.6-6.6.4-4.2 3-6.7 7.6-6.7 11.4 0 4.3 3.4 6.8 8.7 6.8 14.5 0 29-14.6 29-29.8C72 20.8 65.1 14 55.2 14z" fill="#FFFFFF"/>
                  <text x="50" y="85" font-family="Arial, sans-serif" font-weight="700" font-size="25" fill="#FFFFFF" text-anchor="middle">airtel</text>
                </svg>
                Airtel Money
              </div>
              <div class="payment-option-desc">You'll be redirected to enter your number &amp; PIN securely.</div>
            </div>
          </label>

          <label class="payment-option <?= $sel_method==='cash_on_delivery'?'selected':'' ?>" onclick="selectMethod(this,'cash_on_delivery')">
            <input type="radio" name="payment_method" value="cash_on_delivery" <?= $sel_method==='cash_on_delivery'?'checked':'' ?> />
            <div class="payment-option-info">
              <div class="payment-option-name" style="color:var(--green);display:inline-flex;align-items:center;gap:10px;margin-bottom:4px;">
                <svg width="36" height="36" viewBox="0 0 100 100" xmlns="http://w3.org" role="img" aria-label="Cash on Delivery">
                  <rect width="100" height="100" rx="20" fill="#16A34A"/>
                  <path d="M28 28 H68 A5 5 0 0 1 73 33 V37 H28 A4 4 0 0 1 24 33 V32 A4 4 0 0 1 28 28 Z" fill="#DCFCE7"/>
                  <path d="M24 33 A7 7 0 0 0 31 40 H71 A5 5 0 0 1 76 45 V67 A7 7 0 0 1 69 74 H31 A7 7 0 0 1 24 67 Z" fill="#DCFCE7"/>
                  <circle cx="65" cy="57" r="4" fill="#16A34A"/>
                </svg>
                Cash on Delivery / In-Store
              </div>
              <div class="payment-option-desc">Pay cash when you pick up or when delivered.</div>
            </div>
          </label>

        </div>

        <div id="momoInfo" style="margin-top:1.25rem;<?= in_array($sel_method, array('mtn_momo','airtel_money')) ? '' : 'display:none' ?>">
          <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:1rem 1.25rem;display:flex;gap:.75rem;align-items:flex-start">
            <i class="fa-solid fa-circle-info" style="color:#d97706;margin-top:.15rem;flex-shrink:0;font-size:1rem"></i>
            <div>
              <p style="font-weight:700;font-size:.875rem;color:#92400e;margin-bottom:.25rem">
                Secure Payment via Pesapal
              </p>
              <p style="font-size:.8rem;color:#78350f;line-height:1.6">
                After placing your order you'll be redirected to Pesapal's secure payment page where you enter your mobile money number and approve the transaction with your PIN. You will <strong>not</strong> be charged until you approve.
              </p>
            </div>
          </div>
        </div>

      </div>

    </div>

    <div>
      <div class="cart-summary" style="position:sticky;top:90px;background:var(--bg-section);">
        <h3><i class="fa-solid fa-receipt" style="color:var(--blue-light)"></i> Order Summary</h3>
        <div style="margin-bottom:1rem;border-bottom:1px solid var(--border);padding-bottom:1rem">
          <?php foreach ($cart_items as $item): ?>
            <div style="display:flex;gap:.75rem;margin-bottom:.75rem;align-items:center">
              <img src="<?= productImageUrl($item['image']) ?>"
                   style="width:46px;height:46px;border-radius:8px;object-fit:cover;flex-shrink:0" alt=""/>
              <div style="flex:1;min-width:0">
                <div style="font-size:.8rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($item['name']) ?></div>
                <div style="font-size:.75rem;color:var(--gray-400)"><?= htmlspecialchars($item['storage']) ?> &times; <?= $item['quantity'] ?></div>
              </div>
              <div style="font-size:.875rem;font-weight:700;flex-shrink:0"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-row"><span>Subtotal</span><span class="val"><?= formatPrice($subtotal) ?></span></div>
        <div class="summary-row" id="deliveryFeeRow">
          <span>Delivery</span>
          <span class="val" id="deliveryFeeVal" style="color:var(--green)">Free (Pick Up)</span>
        </div>

        <div id="summaryInstallRow" style="<?= $sel_ptype==='installment' ? '' : 'display:none' ?>">
          <div class="summary-row" style="color:var(--orange)">
            <span>Deposit Today (40%)</span>
            <span class="val" id="summaryDeposit"><?= formatPrice($deposit) ?></span>
          </div>
          <div class="summary-row">
            <span>Weekly Payment</span>
            <span class="val" id="summaryWeekly"><?= formatPrice($balance_16) ?>/wk</span>
          </div>
          <div class="summary-row">
            <span>Duration</span>
            <span class="val" id="summaryWeeks">16 weeks</span>
          </div>
        </div>

        <div class="summary-row total">
          <span id="totalLabel"><?= $sel_ptype==='installment'?'Due Today':'Total' ?></span>
          <span class="val" id="cartTotal">
            <?= $sel_ptype==='installment' ? formatPrice($deposit) : formatPrice($subtotal) ?>
          </span>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:1.25rem">
          <i class="fa-solid fa-lock"></i> Place Order
        </button>
        <div style="margin-top:1rem;text-align:center;font-size:.78rem;color:var(--gray-500)">
          <i class="fa-solid fa-shield-check"></i> Your information is safe &amp; secure
        </div>
      </div>
    </div>

  </div>
  </form>
</div>

<script>
var subtotal = <?= $subtotal ?>;
var deposit  = <?= $deposit ?>;

function updateDialCode(sel) {
  var opt  = sel.options[sel.selectedIndex];
  var code = opt.getAttribute('data-code') || '+256';
  var flag = opt.getAttribute('data-flag') || '????';
  // Update phone prefix
  var pf = document.getElementById('phonePrefixFlag');
  var pc = document.getElementById('phonePrefixCode');
  if (pf) pf.textContent = flag;
  if (pc) pc.textContent = code;
  // Update momo prefix
  var mf = document.getElementById('momoPrefixFlag');
  var mc = document.getElementById('momoPrefixCode');
  if (mf) mf.textContent = flag;
  if (mc) mc.textContent = code;
}

function selectFulfillment(el, type) {
  document.querySelectorAll('label.payment-option').forEach(function(o) {
    if (o.querySelector('input[name="fulfillment"]')) o.classList.remove('selected');
  });
  el.classList.add('selected');
  var pickupInfo     = document.getElementById('pickupInfo');
  var deliveryFields = document.getElementById('deliveryFields');
  var feeVal         = document.getElementById('deliveryFeeVal');
  if (type === 'delivery') {
    pickupInfo.style.display     = 'none';
    deliveryFields.style.display = 'block';
    feeVal.textContent           = 'To be confirmed';
    feeVal.style.color           = 'var(--orange)';
  } else {
    pickupInfo.style.display     = 'block';
    deliveryFields.style.display = 'none';
    feeVal.textContent           = 'Free (Pick Up)';
    feeVal.style.color           = 'var(--green)';
  }
}

function selectPayType(el, type) {
  document.querySelectorAll('label.payment-option').forEach(function(o) {
    if (o.querySelector('input[name="payment_type"]')) o.classList.remove('selected');
  });
  el.classList.add('selected');
  var instSection = document.getElementById('installSection');
  var instRow     = document.getElementById('summaryInstallRow');
  var totalLabel  = document.getElementById('totalLabel');
  var totalEl     = document.getElementById('cartTotal');
  if (type === 'installment') {
    instSection.style.display = 'block';
    instRow.style.display     = 'block';
    totalLabel.textContent    = 'Due Today (Deposit)';
    totalEl.textContent       = 'UGX ' + Math.round(deposit).toLocaleString();
    updateInstallSummary();
  } else {
    instSection.style.display = 'none';
    instRow.style.display     = 'none';
    totalLabel.textContent    = 'Total';
    totalEl.textContent       = 'UGX ' + subtotal.toLocaleString();
  }
}

function selectPlan(el) {
  document.querySelectorAll('.plan-option').forEach(function(o) { o.classList.remove('selected'); });
  el.classList.add('selected');
  document.getElementById('selectedWeeks').value = el.dataset.weeks;
  updateInstallSummary();
}

function updateInstallSummary() {
  var plan = document.querySelector('.plan-option.selected');
  if (!plan) return;
  document.getElementById('summaryWeekly').textContent  = 'UGX ' + Math.ceil(parseFloat(plan.dataset.weekly)).toLocaleString() + '/wk';
  document.getElementById('summaryWeeks').textContent   = plan.dataset.weeks + ' weeks';
  document.getElementById('summaryDeposit').textContent = 'UGX ' + Math.round(deposit).toLocaleString();
}

function selectMethod(el, method) {
  document.querySelectorAll('label.payment-option').forEach(function(o) {
    if (o.querySelector('input[name="payment_method"]')) o.classList.remove('selected');
  });
  el.classList.add('selected');
  var info = document.getElementById('momoInfo');
  if (info) {
    info.style.display = (method === 'mtn_momo' || method === 'airtel_money') ? 'block' : 'none';
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
