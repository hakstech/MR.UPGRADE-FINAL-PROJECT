<?php
$page_title = "Our Store";
$meta_desc  = "Visit Mr. Upgrade at Pioneer Mall, Room PG52, Kampala. See our latest stock in person and get the best smartphone deals.";
require_once __DIR__ . '/../includes/header.php';

$gallery = array(
    array('image-4.jpeg', ''),
    array('image-42.jpeg', ''),
    array('image-38.jpeg', ''),
    array('image-64.jpeg', ''),
    array('image-25.jpeg', ''),
    array('image-81.jpg',  ''),
    array('image-75.jpg',  ''),
    array('image-43.jpg',  ''),
    array('image-68.jpg',  ''),
    array('image-67.jpeg', ''),
    array('image-52.jpeg', ''),
    array('image-32.jpg',  ''),
    array('image-28.jpg',  ''),
    array('image-20.jpeg', ''),
    array('image-27.jpeg', ''),
    array('image-34.jpg',  ''),
    array('image-16.jpg',  ''),
    array('image-29.jpeg', ''),
    array('image-41.jpg',  ''),
    array('image-90.jpg',  ''),
    array('image-45.jpg',  ''),
    array('image-59.jpg',  ''),
    array('image-47.jpg',  ''),
    array('image-92.jpg',  ''),
    array('image-91.jpg',  ''),
    array('image-96.jpg',  ''),
    array('image-44.jpg',  ''),
    array('image-72.jpg',  ''),
    array('image-74.jpg',  ''),
    array('image-70.jpg',  ''),
    array('image-95.jpg',  ''),
    array('image-93.jpg',  ''),
);
?>

<section class="store-hero">
  <div class="store-hero-bg">
    <div class="store-hero-overlay"></div>
  </div>
  <div class="container store-hero-content">
    <h1>Visit Our <span class="gradient-text">Store</span></h1>
    <p>Walk in, see the phones in person, test them in your hands and walk out with your upgrade today.</p>
    <div class="store-hero-meta">
      <div class="store-meta-item">
        <i class="fa-solid fa-location-dot"></i>
        <div>
          <span class="meta-label">Location</span>
          <span class="meta-val">Pioneer Mall, Room PG52 Kampala</span>
        </div>
      </div>
      <div class="store-meta-divider"></div>
      <div class="store-meta-item">
        <i class="fa-solid fa-clock"></i>
        <div>
          <span class="meta-label">Open Daily</span>
          <span class="meta-val">Mon – Sat: 8:00 AM – 9:00 PM</span>
        </div>
      </div>
      <div class="store-meta-divider"></div>
      <div class="store-meta-item">
        <i class="fa-brands fa-whatsapp" style="color:#25d366"></i>
        <div>
          <span class="meta-label">WhatsApp</span>
          <span class="meta-val">+256 784 041 256</span>
        </div>
      </div>
    </div>
    <div class="store-hero-actions">
      <a href="https://wa.me/256743741601?text=Hi%20Mr.%20Upgrade%2C%20I%27m%20coming%20to%20the%20store%20soon!"
         target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
        <i class="fa-brands fa-whatsapp"></i> Chat Before You Visit
      </a>
      <a href="https://maps.google.com/?q=Pioneer+Mall+Kampala+Uganda"
         target="_blank" rel="noopener" class="btn btn-outline-white btn-lg">
        <i class="fa-solid fa-map-location-dot"></i> Get Directions
      </a>
    </div>
  </div>
</section>

<section class="section" style="background:var(--bg-section)">
  <div class="container">
    <div class="section-header">
      <p class="section-eyebrow">Fresh Stock</p>
      <h2 class="section-title">Straight From <span class="gradient-text">Our Store</span></h2>
      <p class="section-sub">Real photos. Real stock. All verified, tested and ready for you today.</p>
    </div>
    <div class="store-gallery-grid">
      <?php foreach ($gallery as $i => $item): ?>
        <div class="store-gallery-item"
             onclick="openLightbox('<?= SITE_URL ?>/assets/images/<?= htmlspecialchars($item[0]) ?>', '<?= htmlspecialchars($item[1]) ?>')">
          <img src="<?= SITE_URL ?>/assets/images/<?= htmlspecialchars($item[0]) ?>"
               alt="<?= htmlspecialchars($item[1]) ?>" loading="lazy" />
          <div class="store-gallery-overlay">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="cta-section">
  <div class="container">
    <h2>Ready to Visit Us?</h2>
    <p>Find us at <strong>Pioneer Mall, Room PG52</strong>, Kampala. Walk in anytime, no appointment needed.</p>
    <div class="cta-buttons">
      <a href="https://wa.me/256743741601?text=Hi%2C+I+want+to+visit+the+store" target="_blank" class="btn btn-whatsapp btn-lg">
        <i class="fa-brands fa-whatsapp"></i> Chat Us First
      </a>
      <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg btn-cta">
        <i class="fa-solid fa-store"></i> Browse Online First
      </a>
    </div>
  </div>
</section>

<div id="lightbox" class="lightbox" onclick="closeLightbox()">
  <div class="lightbox-inner" onclick="event.stopPropagation()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fa-solid fa-xmark"></i></button>
    <img id="lightboxImg" src="" alt="" />
    <p id="lightboxCaption"></p>
  </div>
</div>

<style>
/* Store Hero */
.store-hero {
  position: relative;
  min-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  overflow: hidden;
}
.store-hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background: var(--blue);
}

.store-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, rgba(10,14,26,.55) 0%, rgba(10,14,26,.75) 50%, rgba(10,14,26,.95) 100%);
}
.store-hero-content {
  position: relative;
  z-index: 1;
  padding: 120px 1.25rem 4rem;
}
.store-hero-logo {
  width: 72px; height: 72px;
  background: rgba(255,255,255,.08);
  border: 2px solid rgba(255,255,255,.2);
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.5rem;
}
.store-hero-logo img { width: 44px; height: 44px; object-fit: contain; }
.store-hero-content h1 {
  font-size: clamp(2.5rem,6vw,4rem);
  font-weight: 900;
  margin-bottom: .75rem;
  color: #fff;
}
.store-hero-content > p {
  color: rgba(255,255,255,.75);
  font-size: 1.1rem;
  max-width: 540px;
  margin: 0 auto 2.5rem;
  line-height: 1.7;
}
.store-hero-meta {
  display: inline-flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 16px;
  padding: 1.25rem 2rem;
  margin-bottom: 2rem;
}
.store-meta-item {
  display: flex; align-items: center; gap: .75rem; color: #fff;
}
.store-meta-item i { font-size: 1.3rem; color: var(--orange); flex-shrink: 0; }
.meta-label { display:block; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.5); margin-bottom:.15rem; }
.meta-val   { display:block; font-size:.9rem; font-weight:600; color:#fff; }
.store-meta-divider { width:1px; height:40px; background:rgba(255,255,255,.15); flex-shrink:0; }
.store-hero-actions { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }

/* Gallery */
.store-gallery-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-auto-rows: 220px;
  gap: .75rem;
}
.store-gallery-item {
  position: relative;
  border-radius: var(--radius);
  overflow: hidden;
  cursor: pointer;
  border: 1px solid var(--border);
  transition: var(--transition);
}
.store-gallery-item img { width:100%; height:100%; object-fit:cover; transition:.4s ease; display:block; }
.store-gallery-item:hover img { transform:scale(1.06); }
.store-gallery-overlay {
  position:absolute; inset:0;
  background:rgba(10,14,26,.4);
  display:flex; align-items:center; justify-content:center;
  opacity:0; transition:.3s ease;
  color:#fff; font-size:1.5rem;
}
.store-gallery-item:hover .store-gallery-overlay { opacity:1; }

/* Lightbox */
.lightbox { display:none; position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:9999; align-items:center; justify-content:center; padding:1rem; cursor:zoom-out; }
.lightbox.open { display:flex; }
.lightbox-inner { position:relative; max-width:600px; width:100%; cursor:default; }
.lightbox-inner img { width:100%; max-height:80vh; object-fit:contain; border-radius:var(--radius); display:block; }
.lightbox-close { position:absolute; top:-16px; right:-16px; width:36px; height:36px; background:var(--red); border:none; border-radius:50%; color:#fff; font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:1; transition:.2s; }
.lightbox-close:hover { transform:scale(1.1); }
#lightboxCaption { color:rgba(255,255,255,.7); text-align:center; margin-top:.75rem; font-size:.875rem; }

@media (max-width: 768px) {
  .store-gallery-grid { grid-template-columns:repeat(2,1fr); grid-auto-rows:160px; }
  .store-gallery-item:nth-child(5n+1) { grid-column:span 1; }
  .store-hero-meta { gap:.75rem; padding:1rem 1.25rem; }
  .store-meta-divider { display:none; }
}
@media (max-width: 480px) {
  .store-gallery-grid { grid-auto-rows:130px; }
}
</style>

<script>
function openLightbox(src, caption) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightboxCaption').textContent = caption;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.getElementById('lightboxImg').src = '';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeLightbox();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
