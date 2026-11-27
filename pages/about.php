<?php
$page_title = "About Us";
$meta_desc  = "Learn about Mr. Upgrade — Uganda's trusted gadget store. Our story, mission and commitment to quality.";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i><span>About Us</span>
    </div>
    <h1>About <span class="gradient-text">Mr. Upgrade</span></h1>
    <p>Uganda's Premier Gadget Destination</p>
  </div>
</div>

<div class="container" style="padding:4rem 1.25rem 5rem">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;margin-bottom:5rem">
    <div>
      <p class="section-eyebrow">Our Story</p>
      <h2 class="section-title" style="text-align:left">We Started with a <span class="gradient-text">Simple Idea</span></h2>
      <p style="color:var(--gray-400);line-height:1.8;margin-bottom:1.25rem">
        Mr. Upgrade was born out of a simple observation. Ugandans wanted premium smartphones but the prices felt out of reach and the market was full of unverified devices.
      </p>
      <p style="color:var(--gray-400);line-height:1.8;margin-bottom:1.25rem">
        We set out to change that. By sourcing directly and keeping our operations lean, we bring you the best iPhones, smartphones and Laptops at the fairest prices in Kampala — right here at Pioneer Mall, Room PG52 and with our flexible installment plan, the phone you want is closer than you think.
      </p>
      <p style="color:var(--gray-400);line-height:1.8">
        Every device we sell is verified, tested and unlocked. Every customer gets honest advice, real after-sale support and a team that actually cares about your upgrade.
      </p>
    </div>
    <div style="border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--border);aspect-ratio:1">
      <img src="<?= SITE_URL ?>/assets/images/image-81.jpg"
           alt="Mr. Upgrade store" style="width:100%;height:100%;object-fit:cover"/>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:5rem">
    <div class="feature-card" style="border-color:var(--blue)">
      <div class="feature-icon"><i class="fa-solid fa-bullseye"></i></div>
      <h3>Our Mission</h3>
      <p>To make premium smartphones accessible to every Ugandan through honest pricing, genuine products and flexible payment plans — because everyone deserves an upgrade.</p>
    </div>
    <div class="feature-card" style="border-color:var(--cyan)">
      <div class="feature-icon" style="color:var(--cyan);background:rgba(6,182,212,.1)"><i class="fa-solid fa-eye"></i></div>
      <h3>Our Vision</h3>
      <p>To be East Africa's most trusted smartphone retailer — known not just for prices, but for integrity, service and the relationship we build with every customer.</p>
    </div>
  </div>

  <!-- Values -->
  <div style="margin-bottom:5rem;">
    <div class="section-header">
      <p class="section-eyebrow">What We Stand For</p>
      <h2 class="section-title">Our <span class="gradient-text">Core Values</span></h2>
    </div>
    <div class="features-grid">
      <?php foreach([
        ['fa-shield-halved','Authenticity','We only sell genuine, fully unlocked devices — no fakes, no clones, no shortcuts.','var(--green)'],
        ['fa-handshake','Integrity','Honest prices. Honest advice. No hidden fees or misleading descriptions.','var(--blue-light)'],
        ['fa-users','Community','Our customers are our community. We grow when you upgrade.','var(--cyan)'],
        ['fa-headset','Service','We don\'t disappear after the sale. Call us, WhatsApp us — we\'re here.','var(--orange)'],
        ['fa-lightbulb','Innovation','We stay ahead of the market so you always have access to the latest tech.','#a78bfa'],
        ['fa-tag','Affordability','Premium shouldn\'t mean unaffordable. We make top-tier tech accessible for every budget.','var(--yellow)'],
      ] as [$icon,$title,$desc,$color]): ?>
        <div class="feature-card">
          <div class="feature-icon" style="color:<?= $color ?>;background:rgba(37,99,235,.06)"><i class="fa-solid <?= $icon ?>"></i></div>
          <h3><?= $title ?></h3>
          <p><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Gallery -->
  <div style="margin-bottom:5rem">
    <div class="section-header">
      <p class="section-eyebrow">Our Stock</p>
      <h2 class="section-title">Real Phones. <span class="gradient-text">Real Store.</span></h2>
    </div>
    <div class="gallery-grid">
      <?php $imgs = ['image-83.jpg','image-34.jpg','image-1.jpg','image-16.jpg','image-90.jpg','image-47.jpg','image-61.jpg','image-96.jpg'];
      foreach ($imgs as $img): ?>
        <div class="gallery-item">
          <img src="<?= SITE_URL ?>/assets/images/<?= htmlspecialchars($img) ?>" alt="Mr. Upgrade stock" loading="lazy"/>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="cta-section" style="border-radius:var(--radius-lg)">
    <div style="padding:2rem 2rem">
      <h2>Ready for Your Upgrade?</h2>
      <p>Visit us in-store or chat with us on WhatsApp — we'll find the perfect phone for your budget.</p>
      <div class="cta-buttons">
        <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg btn-cta"><i class="fa-solid fa-store"></i> Shop Now</a>
        <a href="https://wa.me/256743741601" target="_blank" class="btn btn-whatsapp btn-lg"><i class="fa-brands fa-whatsapp"></i> Chat Us</a>
      </div>
    </div>
  </div>

</div>

<style>
@media(max-width:768px){
  div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
