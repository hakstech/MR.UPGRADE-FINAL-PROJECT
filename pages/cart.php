<?php
$page_title = "My Cart";
require_once __DIR__ . '/../config/db.php';
requireLogin();
require_once __DIR__ . '/../includes/header.php';

$user_id = (int)$_SESSION['user_id'];
$user_id = (int)$_SESSION['user_id'];

// Add to cart via GET
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    $chk = $conn->prepare("SELECT id, stock_qty FROM products WHERE id=? AND is_active=1 AND price>0");
    $chk->bind_param('i', $product_id);
    $chk->execute();
    $prod = $chk->get_result()->fetch_assoc();
    if ($prod && $prod['stock_qty'] > 0) {
        $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
        $ins->bind_param('ii', $user_id, $product_id);
        $ins->execute();
    }
    // Always redirect to cart after adding
    header('Location: ' . SITE_URL . '/cart?added=1');
    ob_end_flush();
    exit;
}

// Fetch cart items
$cart_res = $conn->prepare("
    SELECT c.id as cart_id, c.quantity,
           p.id as product_id, p.name, p.slug, p.image, p.price, p.storage, p.condition_type, p.stock_qty
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$cart_res->bind_param('i', $user_id);
$cart_res->execute();
$cart_items_res = $cart_res->get_result();

$subtotal = 0;
$items    = array();
while ($item = $cart_items_res->fetch_assoc()) {
    $item['line_total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['line_total'];
    $items[] = $item;
}
?>

<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>My Cart</span>
    </div>
    <h1>My Cart</h1>
    <p><?= count($items) ?> item<?= count($items) != 1 ? 's' : '' ?> in your cart</p>
  </div>
</div>

<div class="container" style="padding:2.5rem 1.25rem 5rem;">

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-cart-shopping"></i>
      <h3>Your cart is empty</h3>
      <p>Browse our collection and add some phones to your cart.</p>
      <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg" style="margin-top:1.5rem">
        <i class="fa-solid fa-store" style="color:var(--white); font-size:2.5rem; margin-top:.5rem"></i> Shop Now
      </a>
    </div>
  <?php else: ?>
    <div class="cart-layout">

      <!-- Cart Items -->
      <div class="cart-items">
        <?php foreach ($items as $item): ?>
          <div class="cart-item" id="cart-row-<?= $item['product_id'] ?>">
            <img src="<?= productImageUrl($item['image']) ?>"
                 alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img" />
            <div class="cart-item-info">
              <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="cart-item-meta">
                <?= htmlspecialchars($item['storage']) ?> &middot;
                <span class="product-condition <?= $item['condition_type']==='New' ? 'new-cond' : 'uk-cond' ?>">
                  <?= htmlspecialchars($item['condition_type']) ?>
                </span>
              </div>
              <div class="cart-item-price" id="line-<?= $item['product_id'] ?>"><?= formatPrice($item['line_total']) ?></div>
              <div class="qty-control">
                <button class="qty-btn" data-type="dec" data-product="<?= $item['product_id'] ?>">&#8722;</button>
                <span class="qty-num" id="qty-<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                <button class="qty-btn" data-type="inc" data-product="<?= $item['product_id'] ?>">+</button>
                <span style="color:var(--gray-400);font-size:.8rem;margin-left:.5rem">&times; <?= formatPrice($item['price']) ?></span>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.75rem">
              <button onclick="confirmRemoveItem(<?= $item['product_id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>')"
                      class="btn btn-danger btn-sm" title="Remove from cart">
                <i class="fa-solid fa-trash"></i>
              </button>
              <a href="<?= SITE_URL ?>/product/<?= urlencode($item['slug']) ?>" class="btn btn-outline btn-sm">View</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Order Summary -->
      <div class="cart-summary">
        <h3><i class="fa-solid fa-receipt" style="color:var(--blue-light)"></i> Order Summary</h3>
        <div class="summary-row"><span>Subtotal</span><span class="val" id="cartSubtotal"><?= formatPrice($subtotal) ?></span></div>
        <div class="summary-row"><span>Delivery</span><span class="val">Discussed at checkout</span></div>
        <div class="summary-row total"><span>Total</span><span class="val" id="cartTotal"><?= formatPrice($subtotal) ?></span></div>

        <a href="<?= SITE_URL ?>/checkout" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:1.25rem">
          <i class="fa-solid fa-lock"></i> Proceed to Checkout
        </a>
        <a href="<?= SITE_URL ?>/shop" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-top:.75rem">
          <i class="fa-solid fa-arrow-left"></i> Continue Shopping
        </a>

        <div style="margin-top:1.25rem;padding:1rem;background:rgba(37,99,235,.08);border:1px solid var(--border);border-radius:10px;">
          <p style="font-size:.8rem;color:var(--blue-light);font-weight:600;margin-bottom:.35rem">
            <i class="fa-solid fa-info-circle"></i> Installment Available
          </p>
          <p style="font-size:.78rem;color:var(--gray-400)">
            Choose to pay in installments at checkout. 40% deposit today, balance over 10&ndash;16 weeks.
          </p>
        </div>

        <a href="https://wa.me/256743741601?text=Hi%2C+I+need+help+with+my+cart." target="_blank"
           class="btn btn-whatsapp btn-sm" style="width:100%;justify-content:center;margin-top:.75rem">
          <i class="fa-brands fa-whatsapp"></i> Need Help? Chat Us
        </a>
      </div>

    </div>
  <?php endif; ?>
</div>

<!-- ============ CART REMOVE MODAL ============ -->
<div id="cartModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:1rem;">
  <!-- Backdrop -->
  <div onclick="closeCartModal()"
       style="position:absolute;inset:0;background:rgba(10,14,26,.5);backdrop-filter:blur(4px)"></div>
  <!-- Dialog -->
  <div id="cartModalDialog"
       style="position:relative;z-index:1;background:#fff;border-radius:16px;width:100%;max-width:380px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);transform:scale(.95);opacity:0;transition:transform .2s ease,opacity .2s ease;">

    <!-- Top section -->
    <div style="padding:2rem 2rem 1rem;text-align:center;background:linear-gradient(135deg,#fef2f2,#fff)">
      <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;border:2px solid #fecaca;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.4rem;color:#dc2626;">
        <i class="fa-solid fa-cart-shopping"></i>
      </div>
      <h3 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:.4rem">Remove Item?</h3>
      <p id="cartModalMsg" style="font-size:.875rem;color:#64748b;line-height:1.6"></p>
    </div>

    <!-- Buttons -->
    <div style="padding:1rem 1.75rem 1.5rem;display:flex;gap:.75rem;">
      <button onclick="closeCartModal()"
              style="flex:1;padding:.75rem;border:1.5px solid #e2e8f0;background:#fff;border-radius:9px;font-size:.9rem;font-weight:600;cursor:pointer;color:#334155;transition:.2s;font-family:inherit"
              onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
        <i class="fa-solid fa-xmark" style="margin-right:.3rem"></i>Keep It
      </button>
      <button id="cartModalConfirm"
              style="flex:1;padding:.75rem;background:#dc2626;border:none;border-radius:9px;font-size:.9rem;font-weight:700;cursor:pointer;color:#fff;display:flex;align-items:center;justify-content:center;gap:.4rem;transition:.2s;font-family:inherit"
              onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
        <i class="fa-solid fa-trash"></i>Remove
      </button>
    </div>

  </div>
</div>

<script>
var _pendingRemoveId = null;

function confirmRemoveItem(productId, productName) {
  _pendingRemoveId = productId;
  var modal   = document.getElementById('cartModal');
  var dialog  = document.getElementById('cartModalDialog');
  var msgEl   = document.getElementById('cartModalMsg');
  var confirm = document.getElementById('cartModalConfirm');

  msgEl.textContent = 'Remove "' + productName + '" from your cart?';

  // Wire confirm button
  confirm.onclick = function() {
    closeCartModal();
    removeCartItem(_pendingRemoveId);
  };

  modal.style.display = 'flex';
  setTimeout(function() {
    dialog.style.transform = 'scale(1)';
    dialog.style.opacity   = '1';
  }, 10);
  document.body.style.overflow = 'hidden';
}

function closeCartModal() {
  var modal  = document.getElementById('cartModal');
  var dialog = document.getElementById('cartModalDialog');
  dialog.style.transform = 'scale(.95)';
  dialog.style.opacity   = '0';
  setTimeout(function() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }, 200);
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeCartModal();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
