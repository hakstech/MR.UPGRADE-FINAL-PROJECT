<?php
$page_title = "Home";
$meta_desc  = "Buy iPhones, Samsung, Google Pixel &amp; laptops in Uganda at the best prices. Flexible installment plans — just 40% deposit. Located at Pioneer Mall, Kampala. Shop Mr. Upgrade today.";
require_once __DIR__ . '/../includes/header.php';

$featured = $conn->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_featured=1 AND p.is_active=1 LIMIT 8");

$cats = $conn->query("SELECT * FROM categories ORDER BY id");
?>

<section class="hero">
  <div class="container hero-inner">
    <div class="hero-text">
      <h1 class="hero-title">
        Don't Just Get a<br />
        <span class="gradient-text">Gadget.</span><br />
        Get an <span class="gradient-text">Upgrade.</span>
      </h1>
      <p class="hero-sub">Top-tier iPhones &amp; smartphones at the best prices in Uganda. Flexible weekly installment plans. Trusted. Verified. Fast.</p>
      <div class="hero-actions">
        <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-store"></i> Shop Now
        </a>
        <a href="<?= SITE_URL ?>/installment" class="btn btn-outline-home btn-lg">
          <i class="fa-solid fa-calendar-check"></i> Installment Plan
        </a>
      </div>
      <div class="hero-trust">
        <div class="trust-item"><i class="fa-solid fa-shield-halved"></i> Trusted &amp; Reliable</div>
        <div class="trust-item"><i class="fa-solid fa-tag"></i> Best Prices</div>
        <div class="trust-item"><i class="fa-solid fa-headset"></i> 24/7 Support</div>
        <div class="trust-item"><i class="fa-solid fa-bolt"></i> Fast Deals</div>
      </div>
    </div>

    <!-- RIGHT SLIDES -->
    <div class="hero-visual">
      <div class="hero-slider" id="heroSlider">
        <div class="hero-slide active">
          <img src="<?= SITE_URL ?>/assets/images/image-18.png"
               alt="Multiple iPhone 17 Pro Max models at Mr. Upgrade" />
        </div>
        <div class="hero-slide">
          <img src="<?= SITE_URL ?>/assets/images/image-81.jpg"
               alt="iPhone 14 Pro models" />
        </div>
        <div class="hero-slide">
          <img src="<?= SITE_URL ?>/assets/images/image-16.jpg"
               alt="iPhone 16 Pro at Mr. Upgrade" />
        </div>
        <div class="hero-slide">
          <img src="<?= SITE_URL ?>/assets/images/image-34.jpg"
               alt="iPhone 13 series" />
        </div>
      </div>
    </div>
  </div>
</section>

<script>
(function() {
  var slides  = document.querySelectorAll('#heroSlider .hero-slide');
  var current = 0;
  if (!slides.length) return;
  function nextSlide() {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
  }
  setInterval(nextSlide, 2500);
})();
</script>

<section class="section" style="background:var(--blue-darkpale)">
  <div class="container">
    <div class="section-header">
      <p class="section-eyebrow">Why Choose Us</p>
      <h2 class="section-title">The <span class="gradient-text">Mr. Upgrade</span> Difference</h2>
      <p class="section-sub">We're not just selling phones — we build relationships with every upgrade.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon"><i class="fa-solid fa-mobile-screen"></i></div>
        <h3>Genuine Devices</h3>
        <p>Every phone is verified authentic, factory unlocked and thoroughly tested before it reaches you.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,rgba(249,115,22,.2),rgba(249,115,22,.05));color:var(--orange)"><i class="fa-solid fa-wallet"></i></div>
        <h3>Flexible Installments</h3>
        <p>Put down just 40% deposit and spread the balance over 10, 12 or 16 weeks. Upgrade now, pay smarter.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,rgba(34,197,94,.2),rgba(34,197,94,.05));color:var(--green)"><i class="fa-solid fa-tag"></i></div>
        <h3>Best Prices in UG</h3>
        <p>We source directly so you always get the fairest market prices on iPhones and premium smartphones.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,rgba(6,182,212,.2),rgba(6,182,212,.05));color:var(--cyan)"><i class="fa-solid fa-handshake"></i></div>
        <h3>After-Sale Support</h3>
        <p>We're with you after the sale. Setup help, questions, follow-up — we've always got you covered.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,rgba(168,85,247,.2),rgba(168,85,247,.05));color:#a78bfa"><i class="fa-solid fa-bolt"></i></div>
        <h3>Fast Transactions</h3>
        <p>In-store or via WhatsApp — close the deal in minutes. We value your time as much as your money.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:linear-gradient(135deg,rgba(234,179,8,.2),rgba(234,179,8,.05));color:var(--yellow)"><i class="fa-solid fa-star"></i></div>
        <h3>Proven Track Record</h3>
        <p>Hundreds of satisfied customers across Uganda trust Mr. Upgrade for all their gadget needs.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="background:var(--blue-pale);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="section-header">
      <p class="section-eyebrow">Hot Right Now</p>
      <h2 class="section-title">Featured <span class="gradient-text">Products</span></h2>
      <p class="section-sub">Fresh stock. All unlocked. All tested.</p>
    </div>
    <div class="products-grid">
      <?php while ($p = $featured->fetch_assoc()): ?>
        <?php
          $badge = $p['condition_type'] === 'New' ? '<div class="product-badge new">New</div>' : '<div class="product-badge uk">UK Used</div>';
          $price_html = $p['price'] > 0
            ? '<span class="price-amount">' . formatPrice($p['price']) . '</span>'
            : '<span class="price-ask">Contact for Price</span>';
        ?>
        <div class="product-card">
          <?= $badge ?>
          <div class="product-img-wrap">
            <a href="<?= SITE_URL ?>/product/<?= urlencode($p['slug']) ?>">
              <img src="<?= productImageUrl($p['image']) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" />
            </a>
          </div>
          <div class="product-info">
            <p class="product-series"><?= htmlspecialchars($p['cat_name']) ?></p>
            <h3 class="product-name">
              <a href="<?= SITE_URL ?>/product/<?= urlencode($p['slug']) ?>" style="color:inherit">
                <?= htmlspecialchars($p['name']) ?>
              </a>
            </h3>
            <p class="product-storage"><?= htmlspecialchars($p['storage']) ?> · <?= htmlspecialchars($p['condition_type']) ?></p>
            <div class="product-price">
              <span class="price-from">From</span>
              <?= $price_html ?>
            </div>
            <div class="product-actions">
              <a href="<?= SITE_URL ?>/product/<?= urlencode($p['slug']) ?>" class="btn btn-outline btn-sm">Details</a>
              <?php if ($p['stock_qty'] > 0 && $p['price'] > 0): ?>
                <a href="<?= SITE_URL ?>/cart?add=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                  <i class="fa-solid fa-cart-plus"></i> Add
                </a>
              <?php else: ?>
                <a href="<?= SITE_URL ?>/contact" class="btn btn-orange btn-sm">Enquire</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    <div style="text-align:center;margin-top:2.5rem;">
      <a href="<?= SITE_URL ?>/shop" class="btn btn-outline btn-lg">
        View All Products <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="installment-banner">
      <div class="install-text">
        <p class="section-eyebrow">Pay Smarter</p>
        <h2 class="section-title" style="text-align:left">Get Your iPhone Today.<br /><span class="gradient-text">Pay in Installments.</span></h2>
        <p style="color:var(--gray-400);margin:.75rem 0 1.5rem;line-height:1.7">
          Can't pay the full price upfront? No problem. Put down just 40% deposit and spread your balance over flexible weekly payments — 10, 12 or 16 weeks.
        </p>
        <ul class="install-perks">
          <li><i class="fa-solid fa-circle-check"></i> 40% deposit to take your phone home today</li>
          <li><i class="fa-solid fa-circle-check"></i> Choose 10, 12 or 16 week payment schedule</li>
          <li><i class="fa-solid fa-circle-check"></i> Available on all iPhone models in stock</li>
          <li><i class="fa-solid fa-circle-check"></i> Fast approval — no complex paperwork</li>
        </ul>
        <a href="<?= SITE_URL ?>/installment" class="btn btn-primary btn-lg" style="margin-top:1rem">
          See Full Installment Plan <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
      <div>
        <div class="install-card">
          <div class="install-card-head">
            <i class="fa-solid fa-calculator"></i>
            <span>Example: iPhone 14 Pro 256GB</span>
          </div>
          <div class="install-card-body">
            <div class="install-row"><span>Full Price</span><span class="install-val">UGX 2,000,000</span></div>
            <div class="install-row"><span>40% Deposit</span><span class="install-val deposit">UGX 800,000</span></div>
            <div class="install-row"><span>Balance</span><span class="install-val">UGX 1,200,000</span></div>
            <div class="install-options" style="margin-top:1rem">
              <div class="install-opt"><span class="opt-weeks">10 Wks</span><span class="opt-amount">192,000</span></div>
              <div class="install-opt"><span class="opt-weeks">12 Wks</span><span class="opt-amount">160,000</span></div>
              <div class="install-opt best"><span class="opt-weeks">16 Wks</span><span class="opt-amount">120,000</span></div>
            </div>
          </div>
          <div style="padding:0 1.5rem 1.5rem">
            <a href="<?= SITE_URL ?>/shop" class="btn btn-primary" style="width:100%;justify-content:center">
              <i class="fa-solid fa-store"></i> Shop & Apply Now
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section class="section" style="background:var(--bg-section);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="section-header">
      <p class="section-eyebrow">Fresh Stock</p>
      <h2 class="section-title">Straight From <span class="gradient-text">Our Store</span></h2>
      <p class="section-sub">Real photos. Real stock. Ready for you today.</p>
    </div>
    <div class="gallery-grid">
      <?php
      $gallery_imgs = [
        ['image-31.webp', 'iPhone 17 Pro Max'],
        ['image-28.jpg',       'Google-pixel'],
        ['image-16.jpg',            'Samsung'],
        ['image-1.jpg',   'iPhone 12 pro max'],
        ['image-34.jpg',       'Google-pixel'],
        ['image-92.jpg',            'MacBook'],
        ['image-71.jpg',        'Dell Laptop'],
        ['image-66.jpg',          'HP Laptop'],
      ];
      foreach ($gallery_imgs as [$img, $alt]):
      ?>
        <div class="gallery-item">
          <img src="<?= SITE_URL ?>/assets/images/<?= htmlspecialchars($img) ?>"
               alt="<?= htmlspecialchars($alt) ?>" loading="lazy" />
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="cta-section">
  <div class="container">
    <h2>Ready for Your Upgrade?</h2>
    <p>Chat with us on WhatsApp right now — get a price quote in under a minute.</p>
    <div class="cta-buttons">
      <a href="https://wa.me/256743741601?text=Hi%20Mr.%20Upgrade%2C%20I%27m%20interested%20in%20a%20phone!" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
        <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
      </a>
      <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg btn-cta"><i class="fa-solid fa-store"></i>Browse All Phones</a>
    </div>
  </div>
      </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
