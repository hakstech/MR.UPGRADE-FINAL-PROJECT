<?php
require_once __DIR__ . '/../includes/header.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . SITE_URL . '/shop'); exit; }

$stmt = $conn->prepare("SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id=c.id WHERE p.slug=? AND p.is_active=1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header('Location: ' . SITE_URL . '/shop'); exit; }

$page_title = $p['name'];
$meta_desc  = $p['description'] ? substr(strip_tags($p['description']), 0, 160) : "Buy {$p['name']} at Mr. Upgrade Uganda. Best price in Kampala.";
$meta_image = productImageUrl($p['image']);
$meta_url   = SITE_URL . '/product/' . urlencode($p['slug']);
$meta_type  = 'product';

// Calculate installment plan
$deposit_40  = $p['price'] * 0.4;
$balance     = $p['price'] - $deposit_40;
$plan_10wk   = $balance > 0 ? ceil($balance / 10)  : 0;
$plan_12wk   = $balance > 0 ? ceil($balance / 12)  : 0;
$plan_16wk   = $balance > 0 ? ceil($balance / 16)  : 0;

// Related products (same category)
$related = $conn->prepare("SELECT * FROM products WHERE category_id=? AND id!=? AND is_active=1 LIMIT 4");
$related->bind_param('ii', $p['category_id'], $p['id']);
$related->execute();
$related_products = $related->get_result();

// Handle add to cart from this page
$cart_msg = '';
if (isset($_GET['added'])) $cart_msg = 'success';
?>

<?php seoProduct($p, $p['cat_name']); ?>
<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <a href="<?= SITE_URL ?>/shop">Shop</a>
      <i class="fa-solid fa-chevron-right"></i>
      <a href="<?= SITE_URL ?>/shop?cat=<?= urlencode($p['cat_slug']) ?>"><?= htmlspecialchars($p['cat_name']) ?></a>
      <i class="fa-solid fa-chevron-right"></i>
      <span><?= htmlspecialchars($p['name']) ?></span>
    </div>
  </div>
</div>

<div class="container" style="padding:2.5rem 1.25rem 5rem">

  <?php if ($cart_msg === 'success'): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem">
      <i class="fa-solid fa-check-circle"></i> Added to cart successfully!
      <a href="<?= SITE_URL ?>/cart" style="color:inherit;font-weight:700;margin-left:.5rem">View Cart ?</a>
    </div>
  <?php endif; ?>

  <div class="product-detail-grid">

    <!-- Image -->
    <div class="product-detail-img-wrap">
      <img src="<?= productImageUrl($p['image']) ?>"
           alt="<?= htmlspecialchars($p['name']) ?>" id="mainImg" />
      <?php $cond_class = $p['condition_type']==='New' ? 'new' : 'uk'; ?>
      <div class="product-badge <?= $cond_class ?>" style="position:absolute;top:16px;left:16px;font-size:.8rem;">
        <?= htmlspecialchars($p['condition_type']) ?>
      </div>
    </div>

    <!-- Info -->
    <div class="product-detail-info">
      <p class="product-series" style="font-size:.9rem;margin-bottom:.35rem"><?= htmlspecialchars($p['cat_name']) ?></p>
      <h1 style="font-size:clamp(1.5rem,3vw,2.2rem);font-weight:900;margin-bottom:.5rem"><?= htmlspecialchars($p['name']) ?></h1>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem">
        <span style="background:rgba(37,99,235,.1);border:1px solid var(--border);border-radius:6px;padding:.3rem .8rem;font-size:.8rem;color:var(--blue-light)">
          <i class="fa-solid fa-hard-drive"></i> <?= htmlspecialchars($p['storage']) ?>
        </span>
        <?php if ($p['color']): ?>
        <span style="background:rgba(37,99,235,.1);border:1px solid var(--border);border-radius:6px;padding:.3rem .8rem;font-size:.8rem;color:var(--cyan)">
          <i class="fa-solid fa-palette"></i> <?= htmlspecialchars($p['color']) ?>
        </span>
        <?php endif; ?>
        <span style="background:<?= $p['stock_qty']>0?'rgba(34,197,94,.1)':'rgba(239,68,68,.1)' ?>;border:1px solid <?= $p['stock_qty']>0?'rgba(34,197,94,.3)':'rgba(239,68,68,.3)' ?>;border-radius:6px;padding:.3rem .8rem;font-size:.8rem;color:<?= $p['stock_qty']>0?'var(--green)':'var(--red)' ?>">
          <?= $p['stock_qty'] > 0 ? '? In Stock ('.$p['stock_qty'].' left)' : '? Out of Stock' ?>
        </span>
      </div>

      <?php if ($p['price'] > 0): ?>
        <div class="product-price" style="margin-bottom:1.5rem">
          <span style="font-size:.85rem;color:var(--gray-400);display:block;margin-bottom:.2rem">Price</span>
          <span style="font-size:2.2rem;font-weight:900;color:var(--blue-light)"><?= formatPrice($p['price']) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($p['description']): ?>
        <p style="color:var(--gray-400);line-height:1.7;margin-bottom:1.5rem"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
      <?php endif; ?>

      <!-- Action Buttons -->
      <?php if ($p['stock_qty'] > 0 && $p['price'] > 0): ?>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem">
          <a href="<?= SITE_URL ?>/cart?add=<?= $p['id'] ?>" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-cart-plus"></i> Add to Cart
          </a>
          <a href="<?= SITE_URL ?>/checkout?buy_now=<?= $p['id'] ?>" class="btn btn-orange btn-lg">
            <i class="fa-solid fa-bolt"></i> Buy Now
          </a>
        </div>
      <?php else: ?>
        <a href="https://wa.me/256743741601?text=Hi%2C+I%27m+interested+in+the+<?= urlencode($p['name']) ?>" target="_blank" class="btn btn-whatsapp btn-lg" style="margin-bottom:2rem">
          <i class="fa-brands fa-whatsapp"></i> Enquire on WhatsApp
        </a>
      <?php endif; ?>

      <!-- Installment Plan Preview -->
      <?php if ($p['price'] > 0): ?>
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem">
        <h4 style="margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;color:var(--cyan)">
          <i class="fa-solid fa-calendar-check"></i> Installment Plan Available
        </h4>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1rem">
          <div style="text-align:center;background:var(--bg-card2);border-radius:8px;padding:.75rem;border:1px solid var(--border)">
            <div style="font-size:.7rem;color:var(--gray-400);text-transform:uppercase">Deposit (40%)</div>
            <div style="font-size:1rem;font-weight:800;color:var(--orange);margin-top:.25rem"><?= formatPrice($deposit_40) ?></div>
          </div>
          <div style="text-align:center;background:var(--bg-card2);border-radius:8px;padding:.75rem;border:1px solid var(--border)">
            <div style="font-size:.7rem;color:var(--gray-400);text-transform:uppercase">10 Weeks</div>
            <div style="font-size:1rem;font-weight:800;color:var(--cyan);margin-top:.25rem"><?= formatPrice($plan_10wk) ?>/wk</div>
          </div>
          <div style="text-align:center;background:var(--bg-card2);border-radius:8px;padding:.75rem;border:1px solid var(--border)">
            <div style="font-size:.7rem;color:var(--gray-400);text-transform:uppercase">16 Weeks</div>
            <div style="font-size:1rem;font-weight:800;color:var(--cyan);margin-top:.25rem"><?= formatPrice($plan_16wk) ?>/wk</div>
          </div>
        </div>
        <a href="<?= SITE_URL ?>/installment" style="font-size:.8rem;color:var(--blue-light)">View full installment details ?</a>
      </div>
      <?php endif; ?>

      <!-- Trust badges -->
      <div style="display:flex;gap:1rem;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--gray-400)">
          <i class="fa-solid fa-shield-check" style="color:var(--green)"></i> Verified Authentic
        </div>
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--gray-400)">
          <i class="fa-solid fa-rotate" style="color:var(--blue-light)"></i> Fully Unlocked
        </div>
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--gray-400)">
          <i class="fa-solid fa-headset" style="color:var(--cyan)"></i> After-Sale Support
        </div>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if ($related_products->num_rows > 0): ?>
  <div style="margin-top:4rem">
    <div class="section-header" style="text-align:left;margin-bottom:1.5rem">
      <h2 class="section-title" style="font-size:1.6rem">More from <span class="gradient-text"><?= htmlspecialchars($p['cat_name']) ?></span></h2>
    </div>
    <div class="products-grid">
      <?php while ($r = $related_products->fetch_assoc()): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <a href="<?= SITE_URL ?>/product/<?= urlencode($r['slug']) ?>">
              <img src="<?= productImageUrl($r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy"/>
            </a>
          </div>
          <div class="product-info">
            <h3 class="product-name" style="font-size:.9rem"><a href="<?= SITE_URL ?>/product/<?= urlencode($r['slug']) ?>" style="color:inherit"><?= htmlspecialchars($r['name']) ?></a></h3>
            <div class="product-price">
              <?php if ($r['price'] > 0): ?>
                <span class="price-amount" style="font-size:1rem"><?= formatPrice($r['price']) ?></span>
              <?php else: ?>
                <span class="price-ask">Contact for Price</span>
              <?php endif; ?>
            </div>
            <a href="<?= SITE_URL ?>/product/<?= urlencode($r['slug']) ?>" class="btn btn-outline btn-sm" style="margin-top:.5rem">View</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
.product-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: start;
}
.product-detail-img-wrap {
  position: relative;
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--border);
  aspect-ratio: 1;
  background: var(--bg-card);
}
.product-detail-img-wrap img {
  width: 100%; height: 100%;
  object-fit: cover;
}
@media (max-width: 768px) {
  .product-detail-grid { grid-template-columns: 1fr; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
