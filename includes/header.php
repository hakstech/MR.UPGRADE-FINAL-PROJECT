<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/seo.php';

// Cart count for logged-in users
$cart_count = 0;
if (isLoggedIn()) {
    $uid = (int)$_SESSION['user_id'];
    $cart_stmt = $conn->prepare("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = ?");
    $cart_stmt->bind_param('i', $uid);
    $cart_stmt->execute();
    if ($cart_stmt) $cart_count = (int)($cart_stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | Mr. Upgrade Uganda' : "Mr. Upgrade Uganda | Best iPhone &amp; Smartphone Deals — Buy Now" ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="<?= SITE_URL ?>/assets/images/favicon.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="<?= SITE_URL ?>/assets/images/favicon.png" />
  <link rel="shortcut icon" href="<?= SITE_URL ?>/assets/images/favicon.png" />
  <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/images/favicon.png" />

  <?php seoHead(
      $page_title ?? null,
      $meta_desc  ?? null,
      $meta_image ?? null,
      $meta_url   ?? null,
      $meta_type  ?? 'website'
  ); ?>

  <?php seoLocalBusiness(); ?>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=2.1" />

  <?= $extra_head ?? '' ?>

  <script>window.SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>
<header class="navbar" id="navbar">
  <div class="container nav-inner">
    <a href="<?= SITE_URL ?>/" class="logo" aria-label="Mr. Upgrade Home">
      <img src="<?= SITE_URL ?>/assets/images/logo-white.png"
           alt="Mr. Upgrade — Don't just get a Gadget. Get an upgrade."
           class="logo-full" />
    </a>
    <nav class="nav-links" id="navLinks" role="navigation" aria-label="Main navigation">
      <a href="<?= SITE_URL ?>/"       class="<?= $current_page === 'index.php'       ? 'active' : '' ?>">Home</a>
      <a href="<?= SITE_URL ?>/shop"        class="<?= $current_page === 'shop.php'        ? 'active' : '' ?>">Shop</a>
      <a href="<?= SITE_URL ?>/installment" class="<?= $current_page === 'installment.php' ? 'active' : '' ?>">Installment</a>
      <a href="<?= SITE_URL ?>/about"       class="<?= $current_page === 'about.php'       ? 'active' : '' ?>">About Us</a>
      <a href="<?= SITE_URL ?>/store"       class="<?= $current_page === 'store.php'       ? 'active' : '' ?>">Store</a>
      <a href="<?= SITE_URL ?>/contact"     class="<?= $current_page === 'contact.php'     ? 'active' : '' ?>">Contact</a>
    </nav>
    <div class="nav-icons">

      <a href="<?= SITE_URL ?>/cart" class="nav-icon-btn" title="My Cart" aria-label="Cart">
        <i class="fa-solid fa-cart-shopping"></i>
        <?php if ($cart_count > 0): ?>
          <span class="nav-badge" aria-label="<?= $cart_count ?> items in cart"><?= $cart_count ?></span>
        <?php endif; ?>
      </a>

      <?php if (isLoggedIn()): ?>
        <div class="nav-user" style="position:relative;">
          <button class="nav-user-btn" onclick="toggleDropdown('userDrop')" aria-haspopup="true">
            <div class="nav-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
            <span class="nav-user-name"><?= htmlspecialchars(explode(' ', trim($_SESSION['user_name'] ?? 'User'))[0]) ?></span>
          </button>
          <div id="userDrop" class="user-dropdown" style="display:none;" role="menu">

            <?php if (isAdmin()): ?>
              <a href="<?= SITE_URL ?>/admin" role="menuitem" style="color:var(--blue-light)">
                <i class="fa-solid fa-gauge"></i> Admin Panel
              </a>
              <div class="dropdown-divider"></div>
              <a href="<?= SITE_URL ?>/logout" role="menuitem" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
              </a>

            <?php else: ?>
              <a href="<?= SITE_URL ?>/orders" role="menuitem">
                <i class="fa-solid fa-box"></i> My Orders
              </a>
              <a href="<?= SITE_URL ?>/profile" role="menuitem">
                <i class="fa-solid fa-user-gear"></i> My Account
              </a>
              <div class="dropdown-divider"></div>
              <a href="<?= SITE_URL ?>/logout" role="menuitem" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
              </a>
            <?php endif; ?>

          </div>
        </div>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-user"></i> Login
        </a>
      <?php endif; ?>

      <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>

    </div>
  </div>
</header>

<style>

.logo-full {
  height: 44px;
  width: auto;
  max-width: 220px;
  object-fit: contain;
  /* Remove white background from PNG on dark navbar */
  mix-blend-mode: screen;
  filter: brightness(1.05);
  transition: opacity 0.2s ease;
}
.logo-full:hover { opacity: 0.85; }

@supports not (mix-blend-mode: screen) {
  .logo-full {
    background: transparent;
    border-radius: 6px;
    padding: 2px 6px;
    background-color: rgba(255,255,255,0.08);
  }
}

.nav-user-btn {
  display: flex; align-items: center; gap: .5rem;
  background: rgba(37,99,235,.1);
  border: 0.1px solid var(--gray-600);
  border-radius: 8px;
  padding: .4rem .8rem;
  cursor: pointer;
  color: #fff;
  font-size: .875rem;
  font-weight: 600;
  transition: var(--transition);
}
.nav-user-btn:hover { background: rgba(37,99,235,.2); border-color: var(--blue); }
.nav-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(135deg, var(--blue), var(--cyan));
  display: flex; align-items: center; justify-content: center;
  font-size: .8rem; font-weight: 800; color: #fff;
}
.nav-user-name { max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.user-dropdown {
  position: absolute; right: 0; top: calc(100% + .6rem);
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  min-width: 160px;
  z-index: 9999;
  box-shadow: 0 16px 48px rgba(0,0,0,.5);
  overflow: hidden;
}
.user-dropdown a {
  display: flex; align-items: center; gap: .65rem;
  padding: .8rem 1.1rem;
  color: var(--gray-500);
  font-size: .875rem;
  font-weight: 500;
  transition: var(--transition);
}
.user-dropdown a:hover { background: rgba(37,99,235,.1); }
.user-dropdown a.logout-link { color: var(--red); }
.user-dropdown a.logout-link:hover { background: rgba(239,68,68,.08);}
.dropdown-divider { height: 1px; background: var(--border); margin: .1rem; }

.hamburger.active span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
</style>
