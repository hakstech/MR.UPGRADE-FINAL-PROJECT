<a href="https://wa.me/256743741601?text=Hi%20Mr.%20Upgrade%2C%20I%27d%20like%20to%20enquire%20about%20a%20phone."
   class="wa-float" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp" aria-label="Chat on WhatsApp">
  <i class="fa-brands fa-whatsapp"></i>
</a>

<footer class="footer">
  <div class="container footer-grid">

    <!-- Brand -->
    <div class="footer-brand">
      <a href="<?= SITE_URL ?>/" class="footer-logo-link" aria-label="Mr. Upgrade Home">
        <img src="<?= SITE_URL ?>/assets/images/favicon.png"
             alt="Mr. Upgrade"
             class="footer-icon-img" />
        <div class="footer-brand-text">
          <span class="footer-brand-name">Mr.<span class="accent">UPGRADE</span></span>
          <span class="footer-brand-tag">Upgrade Smarter. Pay Better.</span>
        </div>
      </a>
      <p>Uganda's premier gadget destination<br />
         Don't just get a Gadget. Get an <strong>upgrade</strong></p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/mrupgrade02?obrf=ZTEydDJoNTJ5OHh3" aria-label="Instagram" target="_blank"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://wa.me/256743741601" aria-label="WhatsApp" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i></a>
        <a href="https://www.tiktok.com/@upgradeplug1?_r=1&_d=f4ldb3318dij40&sec_uid=MS4wLjABAAAA-5SIXM1x6WAEs_23brLlnGGpGXrTNt3kVFIImSw7dYhb9DCvqEwQfTx0IdWOD9wR&share_author_id=7611910719902614549&sharer_language=en&source=h5_m&u_code=f27kl05f1hbgjm&timestamp=1794854948&user_id=7611910719902614549&sec_user_id=MS4wLjABAAAA-5SIXM1x6WAEs_23brLlnGGpGXrTNt3kVFIImSw7dYhb9DCvqEwQfTx0IdWOD9wR&item_author_type=1&utm_source=whatsapp_business&utm_campaign=client_share&utm_medium=android&share_iid=7706393398685288212&share_link_id=2d108a00-576c-489c-a468-de9b03514c55&share_app_id=1233&ugbiz_name=ACCOUNT&ug_btm=b8727%2Cb7360&social_share_type=5&enable_checksum=1" aria-label="TikTok" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
        <a href="https://x.com/mrupgrade02" aria-label="Twitter/X" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="footer-col">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="<?= SITE_URL ?>/"><i class="fa-solid fa-chevron-right fa-xs"></i> Home</a></li>
        <li><a href="<?= SITE_URL ?>/shop"><i class="fa-solid fa-chevron-right fa-xs"></i> Shop All Phones</a></li>
        <li><a href="<?= SITE_URL ?>/installment"><i class="fa-solid fa-chevron-right fa-xs"></i> Installment Plan</a></li>
        <li><a href="<?= SITE_URL ?>/about"><i class="fa-solid fa-chevron-right fa-xs"></i> About Us</a></li>
        <li><a href="<?= SITE_URL ?>/contact"><i class="fa-solid fa-chevron-right fa-xs"></i> Contact</a></li>
        <?php if (!isLoggedIn()): ?>
        <li><a href="<?= SITE_URL ?>/register"><i class="fa-solid fa-chevron-right fa-xs"></i> Create Account</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- iPhone Series -->
    <div class="footer-col">
      <h4>Shop by Category</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/shop?cat=iphone"><i class="fa-solid fa-chevron-right fa-xs"></i> iPhone</a></li>
          <li><a href="<?= SITE_URL ?>/shop?cat=samsung"><i class="fa-solid fa-chevron-right fa-xs"></i> Samsung</a></li>
          <li><a href="<?= SITE_URL ?>/shop?cat=google-pixel"><i class="fa-solid fa-chevron-right fa-xs"></i> Google Pixel</a></li>
          <li><a href="<?= SITE_URL ?>/shop?cat=macbook"><i class="fa-solid fa-chevron-right fa-xs"></i> MacBook</a></li>
          <li><a href="<?= SITE_URL ?>/shop?cat=hp-laptop"><i class="fa-solid fa-chevron-right fa-xs"></i> HP Laptop</a></li>
          <li><a href="<?= SITE_URL ?>/shop?cat=dell-laptop"><i class="fa-solid fa-chevron-right fa-xs"></i> Dell Laptop</a></li>
          <li><a href="<?= SITE_URL ?>/shop?cat=lenovo-laptop"><i class="fa-solid fa-chevron-right fa-xs"></i> Lenovo Laptop</a></li>
        </ul>

    </div>

    <!-- Contact -->
    <div class="footer-col">
      <h4>Get In Touch</h4>
      <div class="footer-contact-item">
        <i class="fa-solid fa-phone"></i>
        <span>+256 743 741 601</span>
      </div>
      <div class="footer-contact-item">
        <i class="fa-brands fa-whatsapp" style="color:#25d366"></i>
        <span>WhatsApp Us Anytime</span>
      </div>
      <div class="footer-contact-item">
        <i class="fa-solid fa-envelope"></i>
        <span>info@mrupgradeug.com</span>
      </div>
      <div class="footer-contact-item">
        <i class="fa-solid fa-location-dot" style="color:var(--red)"></i>
        <span>Pioneer Mall, Room PG52, Kampala</span>
      </div>
      <div style="margin-top:1.25rem;">
        <a href="https://wa.me/256743741601?text=Hi%20Mr.%20Upgrade!" class="btn btn-whatsapp" target="_blank" rel="noopener">
          <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
        </a>
      </div>
    </div>

  </div>

  <div class="footer-bottom" style=" background-color: var(--blue);color:var(--white);">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
      <p>&copy; <?= date('Y') ?> <strong>Mr. Upgrade</strong>. All rights reserved.</p>
      <p style="color:var(--white);font-size:.78rem;">Don't just get a Gadget. Get an upgrade.</p>
    </div>
  </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

<style>
.footer-logo-link {
  display: flex; align-items: center; gap: .75rem;
  margin-bottom: 1rem;
  text-decoration: none;
}
.footer-icon-img {
  width: 52px; height: 52px;
  object-fit: contain;
  background: rgba(255,255,255,.05);
  border-radius: 12px;
  padding: 4px;
  border: 0.1px solid var(--blue);
}
.footer-brand-text { display: flex; flex-direction: column; }
.footer-brand-name {
  font-size: 1.1rem; font-weight: 800; letter-spacing: .02em; color: #fff;
}
.footer-brand-tag {
  font-size: .72rem; color: var(--blue-light); font-weight: 600;
  letter-spacing: .03em;
}
</style>
</body>
</html>
