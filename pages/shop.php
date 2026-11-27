<?php
$page_title = "Shop iPhones, Samsung, Google Pixel & Laptops in Uganda";
$meta_desc  = "Browse all iPhones (XR to 16 Pro Max), Samsung Galaxy, Google Pixel and laptops at Mr. Upgrade Uganda. Best prices in Kampala with flexible weekly installment plans — just 40% deposit.";
require_once __DIR__ . '/../includes/header.php';

// ----------------------------------------------------------------
// Category image map
// ----------------------------------------------------------------
$cat_images = array(
    'iphone'        => 'image-1.jpg',
    'iphone-16'     => 'igor-omilaev-X4S-G_Q9U9g-unsplash.jpg',
    'iphone-15'     => 'igor-omilaev-lDWTfYhZ85w-unsplash.jpg',
    'iphone-14'     => 'igor-omilaev-3EYu6B86yWk-unsplash.jpg',
    'iphone-13'     => 'georgiy-lyamin-RRKIzrI1ZcY-unsplash.jpg',
    'iphone-12'     => 'onur-binay-OKjJZNTl004-unsplash.jpg',
    'iphone-11'     => 'ubeyonroad-nAXaHK_b_IM-unsplash.jpg',
    'iphone-xr'     => 'amanz-AoV1tpTXoHM-unsplash.jpg',
    'samsung'       => 'images (19).jpg',
    'google-pixel'  => 'images (27).jpg',
    'macbook'       => 'images (30).jpg',
    'hp-laptop'     => 'images (34).jpg',
    'dell-laptop'   => 'images (43).jpg',
    'lenovo-laptop' => 'images (46)..lenovo.jpg',
    'accessories'   => 'favicon.png',
);

// Coming soon categories — none, all are live
$coming_soon = array();

// ----------------------------------------------------------------
// Get all categories
// ----------------------------------------------------------------
$cats_res   = $conn->query("SELECT * FROM categories ORDER BY id");
$categories = array();
while ($c = $cats_res->fetch_assoc()) $categories[] = $c;

// ----------------------------------------------------------------
// Filters
// ----------------------------------------------------------------
$cat_slug  = trim(isset($_GET['cat'])  ? $_GET['cat']  : '');
$search    = trim(isset($_GET['q'])    ? $_GET['q']    : '');
$sort      = trim(isset($_GET['sort']) ? $_GET['sort'] : 'default');
$condition = trim(isset($_GET['cond']) ? $_GET['cond'] : '');
$per_page  = 12;
$page_num  = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$offset    = ($page_num - 1) * $per_page;

// "iphone" virtual group → match all iphone-* slugs
$iphone_group = ($cat_slug === 'iphone');

// Is this a coming-soon category with no products?
$is_coming_soon = in_array($cat_slug, $coming_soon);

// Active category display name
$active_cat_name = 'All Products';
if ($cat_slug) {
    if ($iphone_group) {
        $active_cat_name = 'All iPhones';
    } else {
        foreach ($categories as $c) {
            if ($c['slug'] === $cat_slug) {
                $active_cat_name = $c['name'];
                break;
            }
        }
    }
}

// ----------------------------------------------------------------
// Build query — PHP 7.4 compatible (no spread operator)
// ----------------------------------------------------------------
$total_products = 0;
$products       = null;
$total_pages    = 0;

if (!$is_coming_soon) {

    if ($iphone_group) {
        $iphone_slugs   = "'iphone-16','iphone-15','iphone-14','iphone-13','iphone-12','iphone-11','iphone-xr'";
        $base_where     = "p.is_active = 1 AND c.slug IN ($iphone_slugs)";
    } else {
        $base_where     = "p.is_active = 1";
    }

    $extra_where = '';
    $params      = array();
    $types       = '';

    if ($cat_slug && !$iphone_group) {
        $extra_where .= " AND c.slug = ?";
        $params[]     = $cat_slug;
        $types       .= 's';
    }
    if ($search) {
        $like         = "%" . $search . "%";
        $extra_where .= " AND (p.name LIKE ? OR p.model LIKE ? OR p.storage LIKE ?)";
        $params[]     = $like;
        $params[]     = $like;
        $params[]     = $like;
        $types       .= 'sss';
    }
    if ($condition) {
        $extra_where .= " AND p.condition_type = ?";
        $params[]     = $condition;
        $types       .= 's';
    }

    $full_where = $base_where . $extra_where;

    $sort_map = array(
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'newest'     => 'p.created_at DESC',
    );
    $order = isset($sort_map[$sort]) ? $sort_map[$sort] : 'p.is_featured DESC, p.created_at DESC';

    // Count
    $count_sql  = "SELECT COUNT(*) as total FROM products p JOIN categories c ON p.category_id=c.id WHERE $full_where";
    $count_stmt = $conn->prepare($count_sql);
    if ($types) {
        $bind_params = array_merge(array($types), $params);
        $refs = array();
        foreach ($bind_params as $k => $v) { $refs[$k] = &$bind_params[$k]; }
        call_user_func_array(array($count_stmt, 'bind_param'), $refs);
    }
    $count_stmt->execute();
    $total_products = (int)$count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages    = $total_products > 0 ? ceil($total_products / $per_page) : 0;

    // Products
    $sql        = "SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id=c.id WHERE $full_where ORDER BY $order LIMIT ? OFFSET ?";
    $stmt       = $conn->prepare($sql);
    $all_params = array_merge($params, array($per_page, $offset));
    $all_types  = $types . 'ii';
    $bp         = array_merge(array($all_types), $all_params);
    $refs2      = array();
    foreach ($bp as $k => $v) { $refs2[$k] = &$bp[$k]; }
    call_user_func_array(array($stmt, 'bind_param'), $refs2);
    $stmt->execute();
    $products = $stmt->get_result();
}

// Helper for pagination URLs
function buildQuery($overrides = array()) {
    $p = array_merge($_GET, $overrides);
    $p = array_filter($p);
    return '?' . http_build_query($p);
}
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= SITE_URL ?>/">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>Shop</span>
      <?php if ($cat_slug): ?>
        <i class="fa-solid fa-chevron-right"></i>
        <span><?= htmlspecialchars($active_cat_name) ?></span>
      <?php endif; ?>
    </div>
    <h1><?= htmlspecialchars($active_cat_name) ?></h1>
    <?php if (!$is_coming_soon): ?>
      <p><?= number_format($total_products) ?> product<?= $total_products != 1 ? 's' : '' ?> available</p>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="padding-top:2rem;padding-bottom:4rem;">

  <!-- Category Pills -->
  <div class="category-pills" style="margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="<?= SITE_URL ?>/shop"
       class="category-pill <?= !$cat_slug ? 'active' : '' ?>">All Products</a>
    <a href="<?= SITE_URL ?>/shop?cat=iphone"
       class="category-pill <?= $iphone_group || in_array($cat_slug,array('iphone-16','iphone-15','iphone-14','iphone-13','iphone-12','iphone-11','iphone-xr')) ? 'active' : '' ?>">
      <i class="fa-brands fa-apple"></i> iPhone
    </a>
    <?php foreach ($categories as $c):
        $iphone_slugs_arr = array('iphone-16','iphone-15','iphone-14','iphone-13','iphone-12','iphone-11','iphone-xr');
        if (in_array($c['slug'], $iphone_slugs_arr)) continue; // already grouped under iPhone
    ?>
      <a href="<?= SITE_URL ?>/shop?cat=<?= urlencode($c['slug']) ?>"
         class="category-pill <?= $cat_slug === $c['slug'] ? 'active' : '' ?>">
        <?= htmlspecialchars($c['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($is_coming_soon): ?>
  <!-- ============ COMING SOON ============ -->
  <div class="coming-soon-wrap">
    <?php
    $cs_img = isset($cat_images[$cat_slug]) ? $cat_images[$cat_slug] : '';
    ?>
    <div class="coming-soon-card">
      <?php if ($cs_img): ?>
        <div class="coming-soon-img">
          <img src="<?= SITE_URL ?>/assets/images/<?= htmlspecialchars($cs_img) ?>"
               alt="<?= htmlspecialchars($active_cat_name) ?>" />
          <div class="coming-soon-overlay">
            <span class="coming-soon-badge">Coming Soon</span>
          </div>
        </div>
      <?php endif; ?>
      <div class="coming-soon-info">
        <h2><?= htmlspecialchars($active_cat_name) ?></h2>
        <p>We're stocking up on <strong><?= htmlspecialchars($active_cat_name) ?></strong> devices right now.</p>
        <p>Be the first to know when they're available — reach out to us and we'll notify you!</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem;">
          <a href="https://wa.me/256743741601?text=Hi%2C+I%27m+interested+in+<?= urlencode($active_cat_name) ?>+at+Mr.+Upgrade"
             target="_blank" class="btn btn-whatsapp btn-lg">
            <i class="fa-brands fa-whatsapp"></i> Notify Me on WhatsApp
          </a>
          <a href="<?= SITE_URL ?>/shop?cat=iphone" class="btn btn-primary btn-lg">
            <i class="fa-brands fa-apple"></i> Browse iPhones Instead
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- ============ FILTERS ============ -->
  <div class="shop-controls" style="margin-bottom:2rem">
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;flex:1">
      <?php if ($cat_slug): ?>
        <input type="hidden" name="cat" value="<?= htmlspecialchars($cat_slug) ?>">
      <?php endif; ?>
      <div class="search-bar" style="flex:1;min-width:200px">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" />
      </div>
      <select name="cond" class="filter-select" onchange="this.form.submit()">
        <option value="">All Conditions</option>
        <option value="New"          <?= $condition==='New'?'selected':''?>>New</option>
        <option value="UK Used"      <?= $condition==='UK Used'?'selected':''?>>UK Used</option>
        <option value="Refurbished"  <?= $condition==='Refurbished'?'selected':''?>>Refurbished</option>
      </select>
      <select name="sort" class="filter-select" onchange="this.form.submit()">
        <option value="default"   <?= $sort==='default'?'selected':''?>>Featured First</option>
        <option value="price_asc" <?= $sort==='price_asc'?'selected':''?>>Price: Low → High</option>
        <option value="price_desc"<?= $sort==='price_desc'?'selected':''?>>Price: High → Low</option>
        <option value="newest"    <?= $sort==='newest'?'selected':''?>>Newest</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filter</button>
      <?php if ($search || $condition || $sort !== 'default'): ?>
        <a href="<?= SITE_URL ?>/shop<?= $cat_slug ? '?cat='.$cat_slug : '' ?>" class="btn btn-outline btn-sm">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Products Grid -->
  <?php if (!$products || $products->num_rows === 0): ?>
    <div class="empty-state">
      <i class="fa-solid fa-mobile-screen-button"></i>
      <h3>No products found</h3>
      <p>Try adjusting your filters or <a href="<?= SITE_URL ?>/shop" style="color:var(--blue-light)">view all products</a>.</p>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php while ($p = $products->fetch_assoc()): ?>
        <?php
          if ($p['is_featured']) {
            $badge = '<div class="product-badge hot">Hot</div>';
          } elseif ($p['condition_type'] === 'New') {
            $badge = '<div class="product-badge new">New</div>';
          } else {
            $badge = '<div class="product-badge uk">UK Used</div>';
          }
        ?>
        <div class="product-card">
          <?= $badge ?>
          <?php if ($p['stock_qty'] == 0): ?>
            <div class="product-badge" style="left:auto;right:12px;background:rgba(239,68,68,.8)">Out of Stock</div>
          <?php endif; ?>
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
            <p class="product-storage"><?= htmlspecialchars($p['storage']) ?></p>
            <div class="product-price">
              <span class="price-from">From</span>
              <?php if ($p['price'] > 0): ?>
                <span class="price-amount"><?= formatPrice($p['price']) ?></span>
              <?php else: ?>
                <span class="price-ask">Contact for Price</span>
              <?php endif; ?>
            </div>
            <div class="product-actions">
              <a href="<?= SITE_URL ?>/product/<?= urlencode($p['slug']) ?>" class="btn btn-outline btn-sm">Details</a>
              <?php if ($p['stock_qty'] > 0 && $p['price'] > 0): ?>
                <a href="<?= SITE_URL ?>/cart?add=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                  <i class="fa-solid fa-cart-plus"></i>
                </a>
              <?php else: ?>
                <a href="<?= SITE_URL ?>/contact" class="btn btn-orange btn-sm">Enquire</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="pagination" style="margin-top:3rem">
        <?php if ($page_num > 1): ?>
          <a href="<?= buildQuery(array('page' => $page_num - 1)) ?>" class="page-num"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = max(1, $page_num-2); $i <= min($total_pages, $page_num+2); $i++): ?>
          <a href="<?= buildQuery(array('page' => $i)) ?>" class="page-num <?= $i == $page_num ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page_num < $total_pages): ?>
          <a href="<?= buildQuery(array('page' => $page_num + 1)) ?>" class="page-num"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
  <?php endif; ?>

</div>

<style>
/* Coming Soon styles */
.coming-soon-wrap { max-width: 900px; margin: 0 auto; padding: 2rem 0 4rem; }
.coming-soon-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}
.coming-soon-img {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
}
.coming-soon-img img {
  width: 100%; height: 100%;
  object-fit: cover;
  filter: grayscale(20%) blur(1px);
  transform: scale(1.05);
}
.coming-soon-overlay {
  position: absolute; inset: 0;
  background: rgba(10,14,26,.5);
  display: flex; align-items: center; justify-content: center;
}
.coming-soon-badge {
  background: linear-gradient(135deg, var(--blue), var(--cyan));
  color: #fff;
  font-size: 1rem; font-weight: 800;
  padding: .6rem 1.5rem;
  border-radius: 50px;
  letter-spacing: .05em;
  text-transform: uppercase;
}
.coming-soon-info {
  padding: 3rem 2.5rem;
  display: flex; flex-direction: column; justify-content: center;
}
.coming-soon-info h2 { font-size: 2rem; font-weight: 900; margin-bottom: 1rem; }
.coming-soon-info p  { color: var(--gray-400); line-height: 1.7; margin-bottom: .5rem; }
@media (max-width: 768px) {
  .coming-soon-card { grid-template-columns: 1fr; }
  .coming-soon-img  { aspect-ratio: 16/9; }
  .coming-soon-info { padding: 2rem 1.5rem; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
