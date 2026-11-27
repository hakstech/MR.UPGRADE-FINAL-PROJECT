// ============================================================
// Mr. Upgrade – Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // --- Sticky Navbar Shadow ---
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      navbar?.classList.add('scrolled');
    } else {
      navbar?.classList.remove('scrolled');
    }
  });

  // --- Hamburger Menu ---
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');
  hamburger?.addEventListener('click', () => {
    navLinks?.classList.toggle('open');
    hamburger.classList.toggle('active');
  });

  // --- Ticker Duplication (seamless loop) ---
  const ticker = document.querySelector('.ticker');
  if (ticker) {
    ticker.innerHTML += ticker.innerHTML;
  }

  // --- Animate on Scroll ---
  const animEls = document.querySelectorAll('.feature-card, .product-card, .stat-card, .gallery-item');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });
  animEls.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });

  // --- Cart Quantity Controls ---
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const type      = this.dataset.type;
      const productId = this.dataset.product;
      const numEl     = document.getElementById('qty-' + productId);
      if (!numEl) return;
      let current = parseInt(numEl.textContent);
      if (type === 'inc') current++;
      else if (type === 'dec' && current > 1) current--;
      numEl.textContent = current;
      updateCartQty(productId, current);
    });
  });

  // --- Payment Option Selection ---
  document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function () {
      document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
      this.classList.add('selected');
      const radio = this.querySelector('input[type=radio]');
      if (radio) radio.checked = true;

      // Show/hide momo number field
      const momoFields = document.getElementById('momoFields');
      const method = radio?.value;
      if (momoFields) {
        momoFields.style.display = (method === 'mtn_momo' || method === 'airtel_money') ? 'block' : 'none';
        const label = document.getElementById('momoLabel');
        if (label) label.textContent = method === 'airtel_money' ? 'Airtel Money Number' : 'MTN MoMo Number';
      }
    });
  });

  // --- Installment Plan Selector ---
  document.querySelectorAll('.plan-option').forEach(opt => {
    opt.addEventListener('click', function () {
      document.querySelectorAll('.plan-option').forEach(o => o.classList.remove('selected'));
      this.classList.add('selected');
      const weeks  = this.dataset.weeks;
      const hidden = document.getElementById('selectedWeeks');
      if (hidden) hidden.value = weeks;
      updateInstallmentSummary();
    });
  });

  // --- Payment type toggle (Full vs Installment) ---
  const payTypeFull = document.getElementById('payTypeFull');
  const payTypeInst = document.getElementById('payTypeInst');
  const installSection = document.getElementById('installSection');
  payTypeFull?.addEventListener('change', () => { if(installSection) installSection.style.display='none'; });
  payTypeInst?.addEventListener('change', () => { if(installSection) installSection.style.display='block'; updateInstallmentSummary(); });

  // --- Category filter pills ---
  document.querySelectorAll('.category-pill').forEach(pill => {
    pill.addEventListener('click', function() {
      document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // --- Close dropdown on outside click ---
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-user')) {
      const drops = document.querySelectorAll('.user-dropdown');
      drops.forEach(d => d.style.display = 'none');
    }
  });

});

// --- Toggle dropdown ---
function toggleDropdown(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// --- Update cart qty via AJAX ---
function updateCartQty(productId, qty) {
  fetch(window.SITE_URL + '/pages/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update&product_id=${productId}&qty=${qty}`
  }).then(r => r.json()).then(data => {
    if (data.total) document.getElementById('cartTotal').textContent = data.total;
    if (data.subtotal) document.getElementById('cartSubtotal').textContent = data.subtotal;
    const badge = document.querySelector('.nav-badge');
    if (badge && data.count !== undefined) badge.textContent = data.count;
  }).catch(() => {});
}

// --- Remove cart item ---
function removeCartItem(productId) {
  fetch(window.SITE_URL + '/pages/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=remove&product_id=${productId}`
  }).then(r => r.json()).then(data => {
    const row = document.getElementById('cart-row-' + productId);
    if (row) row.remove();
    if (data.count === 0) location.reload();
    if (data.total) document.getElementById('cartTotal').textContent = data.total;
    const badge = document.querySelector('.nav-badge');
    if (badge) badge.textContent = data.count || '';
  });
}

// --- Update installment summary on checkout ---
function updateInstallmentSummary() {
  const selectedPlan = document.querySelector('.plan-option.selected');
  const totalEl = document.getElementById('orderTotal');
  if (!selectedPlan || !totalEl) return;
  const weeks   = parseInt(selectedPlan.dataset.weeks);
  const deposit = parseFloat(selectedPlan.dataset.deposit);
  const weekly  = parseFloat(selectedPlan.dataset.weekly);
  const dEl  = document.getElementById('summaryDeposit');
  const wEl  = document.getElementById('summaryWeekly');
  const wkEl = document.getElementById('summaryWeeks');
  if (dEl)  dEl.textContent  = 'UGX ' + deposit.toLocaleString();
  if (wEl)  wEl.textContent  = 'UGX ' + weekly.toLocaleString() + '/week';
  if (wkEl) wkEl.textContent = weeks + ' weeks';
}

// --- Flash message auto-hide ---
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    a.style.transition = 'opacity 0.5s';
    a.style.opacity    = '0';
    setTimeout(() => a.remove(), 500);
  });
}, 4000);
