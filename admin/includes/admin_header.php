<?php
require_once __DIR__ . '/../../config/db.php';
requireLogin();
requireAdmin();

$admin_page   = basename($_SERVER['PHP_SELF']);
$pending_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$unread_msgs    = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE is_read=0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title ?? 'Admin') ?> | Mr. Upgrade Admin</title>
  <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= time() ?>"/>
  <script>window.SITE_URL='<?= SITE_URL ?>';</script>
</head>
<body>
<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-logo">
      <img src="<?= SITE_URL ?>/assets/images/favicon.png" alt="Mr. Upgrade"/>
      <div class="admin-logo-text">Mr. Upgrade<small>Admin Panel</small></div>
    </div>
    <nav class="admin-nav">
      <div class="admin-nav-group">Main</div>
      <a href="<?= SITE_URL ?>/admin"       class="<?= $admin_page==='index.php'?'active':''?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
      </a>

      <div class="admin-nav-group">Catalogue</div>
      <a href="<?= SITE_URL ?>/admin/products"    class="<?= $admin_page==='products.php'?'active':''?>">
        <i class="fa-solid fa-mobile-screen"></i> Products
      </a>
      <a href="<?= SITE_URL ?>/admin/products?action=add" class="<?= ($admin_page==='products.php'&&($_GET['action']??'')==='add')?'active':''?>">
        <i class="fa-solid fa-plus"></i> Add Product
      </a>

      <div class="admin-nav-group">Sales</div>
      <a href="<?= SITE_URL ?>/admin/orders"      class="<?= $admin_page==='orders.php'?'active':''?>">
        <i class="fa-solid fa-box"></i> Orders
        <?php if ($pending_orders > 0): ?><span class="admin-badge"><?= $pending_orders ?></span><?php endif; ?>
      </a>
      <a href="<?= SITE_URL ?>/admin/installments" class="<?= $admin_page==='installments.php'?'active':''?>">
        <i class="fa-solid fa-calendar-check"></i> Installments
      </a>

      <div class="admin-nav-group">Customers</div>
      <a href="<?= SITE_URL ?>/admin/customers"   class="<?= $admin_page==='customers.php'?'active':''?>">
        <i class="fa-solid fa-users"></i> Customers
      </a>
      <a href="<?= SITE_URL ?>/admin/messages"    class="<?= $admin_page==='messages.php'?'active':''?>">
        <i class="fa-solid fa-envelope"></i> Messages
        <?php if ($unread_msgs > 0): ?><span class="admin-badge"><?= $unread_msgs ?></span><?php endif; ?>
      </a>

      <div class="admin-nav-group" style="margin-top:auto"></div>
      <a href="<?= SITE_URL ?>/admin/profile" class="<?= $admin_page==='profile.php'?'active':''?>">
        <i class="fa-solid fa-user-gear"></i> My Account
      </a>
      <a href="<?= SITE_URL ?>/" target="_blank">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
      </a>
      <a href="<?= SITE_URL ?>/logout" style="color:#f87171">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div style="display:flex;align-items:center;gap:1rem">
        <button class="admin-hamburger" id="adminHamburger" aria-label="Toggle navigation">
          <span></span><span></span><span></span>
        </button>
        <h1 class="admin-page-title"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h1>
      </div>
      <div style="display:flex;align-items:center;gap:.75rem">
        <a href="<?= SITE_URL ?>/admin/orders" class="nav-icon-btn admin-nav-icon-btn" title="Pending Orders">
          <i class="fa-solid fa-box"></i>
          <?php if ($pending_orders > 0): ?><span class="nav-badge"><?= $pending_orders ?></span><?php endif; ?>
        </a>
        <div class="admin-topbar-user">
          <div class="admin-topbar-avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
          <span class="admin-topbar-name"><?= htmlspecialchars(explode(' ',$_SESSION['user_name'])[0]) ?></span>
        </div>
      </div>
    </div>
    <div class="admin-content">
