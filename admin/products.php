<?php
$page_title = "Manage Products";
require_once __DIR__ . '/includes/admin_header.php';

$action  = isset($_GET['action']) ? $_GET['action'] : '';
$edit_id = (int)(isset($_GET['edit']) ? $_GET['edit'] : 0);
$msg = $err = '';

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $del_stmt = $conn->prepare("UPDATE products SET is_active=0 WHERE id=?");
    $del_stmt->bind_param('i', $del_id);
    $del_stmt->execute();
    header('Location: ' . SITE_URL . '/admin/products?msg=deleted');
    exit;
}

$cats_res = $conn->query("SELECT * FROM categories ORDER BY id");
$cats = array();
while ($c = $cats_res->fetch_assoc()) $cats[] = $c;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    if (!verifyCsrfToken()) { die('Invalid request.'); }
    $pid         = (int)(isset($_POST['pid']) ? $_POST['pid'] : 0);
    $category_id = (int)$_POST['category_id'];
    $name        = trim($_POST['name']);
    $model       = trim(isset($_POST['model'])          ? $_POST['model']        : '');
    $storage     = trim(isset($_POST['storage'])        ? $_POST['storage']      : '');
    $color       = trim(isset($_POST['color'])          ? $_POST['color']        : '');
    $condition   = trim(isset($_POST['condition_type']) ? $_POST['condition_type'] : 'New');
    $price       = (float)(isset($_POST['price'])       ? $_POST['price']        : 0);
    $stock       = (int)(isset($_POST['stock_qty'])     ? $_POST['stock_qty']    : 0);
    $desc        = trim(isset($_POST['description'])    ? $_POST['description']  : '');
    $featured    = isset($_POST['is_featured']) ? 1 : 0;
    $active      = isset($_POST['is_active'])   ? 1 : 0;

    if (!$name || !$category_id) {
        $err = 'Product name and category are required.';
    } else {
        $slug_base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = $slug_base;
        if ($pid === 0) {
            $i = 1;
            $slug_check = $conn->prepare("SELECT id FROM products WHERE slug=?");
            $slug_check->bind_param('s', $slug);
            $slug_check->execute();
            while ($slug_check->get_result()->num_rows > 0) {
                $slug = $slug_base . '-' . $i++;
                $slug_check->bind_param('s', $slug);
                $slug_check->execute();
            }
        }

        $image = trim(isset($_POST['current_image']) ? $_POST['current_image'] : '');
        if (!empty($_FILES['image']['name'])) {
            $upload_error = $_FILES['image']['error'];
            if ($upload_error !== UPLOAD_ERR_OK) {
                $msgs = array(
                    UPLOAD_ERR_INI_SIZE=>'File too large (server limit: '.ini_get('upload_max_filesize').').',
                    UPLOAD_ERR_FORM_SIZE=>'File too large.',
                    UPLOAD_ERR_PARTIAL=>'File only partially uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR=>'Missing temp folder.',
                    UPLOAD_ERR_CANT_WRITE=>'Cannot write to disk.',
                );
                $err = isset($msgs[$upload_error]) ? $msgs[$upload_error] : 'Upload error ('.$upload_error.').';
            } else {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, array('jpg','jpeg','png','webp'))) {
                    $err = 'Only JPG, PNG or WebP allowed.';
                } else {
                    $new_name = 'product_' . time() . '_' . mt_rand(100,999) . '.' . $ext;
                    $dest     = __DIR__ . '/../uploads/products/' . $new_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $image = 'uploads/products/' . $new_name;
                    } else {
                        $err = 'Could not save file. Check uploads/products folder.';
                    }
                }
            }
        }

        if (!$err) {
            if ($pid === 0) {
                $stmt = $conn->prepare("INSERT INTO products (category_id,name,slug,model,storage,color,condition_type,price,stock_qty,description,image,is_featured,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param('issssssdiisii', $category_id,$name,$slug,$model,$storage,$color,$condition,$price,$stock,$desc,$image,$featured,$active);
                $stmt->execute();
                $msg = 'Product added successfully!';
            } else {
                $stmt = $conn->prepare("UPDATE products SET category_id=?,name=?,model=?,storage=?,color=?,condition_type=?,price=?,stock_qty=?,description=?,image=?,is_featured=?,is_active=? WHERE id=?");
                $stmt->bind_param('isssssdiisiii', $category_id,$name,$model,$storage,$color,$condition,$price,$stock,$desc,$image,$featured,$active,$pid);
                $stmt->execute();
                $msg = 'Product updated successfully!';
                $action = '';
            }
        }
    }
}

$edit_product = null;
if ($action === 'edit' || $edit_id) {
    $id = $edit_id ?: (int)(isset($_POST['pid']) ? $_POST['pid'] : 0);
    $ep = $conn->prepare("SELECT * FROM products WHERE id=?");
    $ep->bind_param('i', $id);
    $ep->execute();
    $edit_product = $ep->get_result()->fetch_assoc();
    $action = 'edit';
}

$show_form = ($action === 'add' || $action === 'edit');

$products_res = $conn->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_active=1 ORDER BY p.category_id, p.created_at DESC");
$all_products = $products_res->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:1.25rem"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"   style="margin-bottom:1.25rem"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>
<?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?><div class="alert alert-success" style="margin-bottom:1.25rem">Product removed.</div><?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
  <h2 style="font-size:1.1rem;font-weight:700">
    <?= $show_form ? ($action==='add' ? '<i class="fa-solid fa-plus"></i> Add New Product' : '<i class="fa-solid fa-pen"></i> Edit Product') : count($all_products) . ' Active Products' ?>
  </h2>
  <?php if (!$show_form): ?>
    <a href="?action=add" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Product</a>
  <?php else: ?>
    <a href="<?= SITE_URL ?>/admin/products" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
  <?php endif; ?>
</div>

<?php if ($show_form): ?>
<!-- ============ ADD / EDIT FORM ============ -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem">
  <form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="save_product" value="1"/>
    <input type="hidden" name="pid"           value="<?= $edit_product['id'] ?? 0 ?>"/>
    <input type="hidden" name="current_image" value="<?= htmlspecialchars($edit_product['image'] ?? '') ?>"/>

    <div class="product-form-grid">
      <div class="form-group">
        <label>Product Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" required placeholder="e.g. iPhone 15 Pro Max 256GB"/>
      </div>
      <div class="form-group">
        <label>Category <span style="color:var(--red)">*</span></label>
        <select name="category_id" required>
          <option value="">Select category</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($edit_product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Model</label>
        <input type="text" name="model" value="<?= htmlspecialchars($edit_product['model'] ?? '') ?>" placeholder="e.g. iPhone 15 Pro Max"/>
      </div>
      <div class="form-group">
        <label>Storage</label>
        <input type="text" name="storage" value="<?= htmlspecialchars($edit_product['storage'] ?? '') ?>" placeholder="e.g. 256GB"/>
      </div>
      <div class="form-group">
        <label>Color</label>
        <input type="text" name="color" value="<?= htmlspecialchars($edit_product['color'] ?? '') ?>" placeholder="e.g. Natural Titanium"/>
      </div>
      <div class="form-group">
        <label>Condition</label>
        <select name="condition_type">
          <option value="New"         <?= ($edit_product['condition_type']??'')==='New'?'selected':''?>>New</option>
          <option value="UK Used"     <?= ($edit_product['condition_type']??'')==='UK Used'?'selected':''?>>UK Used</option>
          <option value="Refurbished" <?= ($edit_product['condition_type']??'')==='Refurbished'?'selected':''?>>Refurbished</option>
        </select>
      </div>
      <div class="form-group">
        <label>Price (UGX)</label>
        <input type="number" name="price" value="<?= $edit_product['price'] ?? 0 ?>" min="0" step="1000" placeholder="e.g. 2750000"/>
      </div>
      <div class="form-group">
        <label>Stock Quantity</label>
        <input type="number" name="stock_qty" value="<?= $edit_product['stock_qty'] ?? 0 ?>" min="0"/>
      </div>
    </div>

    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="3" placeholder="Product details, features, condition notes..."><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Product Image</label>
      <?php if (!empty($edit_product['image'])): ?>
        <?php $img_url = (strpos($edit_product['image'],'uploads/')===0) ? SITE_URL.'/'.$edit_product['image'] : SITE_URL.'/assets/images/'.$edit_product['image']; ?>
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.75rem;flex-wrap:wrap">
          <img src="<?= htmlspecialchars($img_url) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);flex-shrink:0"/>
          <div>
            <p style="font-size:.82rem;font-weight:600;color:var(--text-primary)">Current image</p>
            <p style="font-size:.75rem;color:var(--text-muted)">Upload below to replace.</p>
            <p style="font-size:.7rem;color:var(--text-faint);margin-top:.15rem"><?= htmlspecialchars(basename($edit_product['image'])) ?></p>
          </div>
        </div>
      <?php endif; ?>
      <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp"
             style="background:var(--bg-section);border:1.5px solid var(--border);border-radius:8px;padding:.6rem 1rem;color:var(--text-primary);width:100%"
             onchange="previewImage(this)"/>
      <small style="color:var(--text-muted);font-size:.78rem;margin-top:.3rem;display:block">
        <i class="fa-solid fa-info-circle"></i> Max 20MB &bull; JPG, PNG or WebP
      </small>
      <div id="newImgPreview" style="display:none;margin-top:.75rem">
        <p style="font-size:.78rem;font-weight:600;color:var(--blue);margin-bottom:.4rem">Preview:</p>
        <img id="newImgPreviewImg" src="" alt="Preview" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:2px solid var(--blue)"/>
      </div>
    </div>

    <!-- Toggles -->
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.5rem">
      <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem">
        <input type="checkbox" name="is_featured" value="1" <?= ($edit_product['is_featured']??0)?'checked':'' ?> style="accent-color:var(--blue);width:16px;height:16px"/>
        Featured (homepage)
      </label>
      <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem">
        <input type="checkbox" name="is_active" value="1" <?= ($edit_product['is_active']??1)?'checked':'' ?> style="accent-color:var(--green);width:16px;height:16px"/>
        Active (visible on site)
      </label>
    </div>

    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $action==='add'?'Add Product':'Save Changes' ?></button>
      <a href="<?= SITE_URL ?>/admin/products" class="btn btn-outline">Cancel</a>
    </div>
  </form>
</div>

<?php else: ?>
<!-- ============ PRODUCT LIST ============ -->

<!-- Desktop table -->
<div class="data-table-wrap admin-table-desktop">
  <div class="data-table-head">
    <h3><?= count($all_products) ?> Products</h3>
  </div>
  <div style="overflow-x:auto">
    <table class="data-table" style="min-width:650px">
      <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Cond.</th><th>★</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($all_products as $p):
          $img_url = (strpos($p['image'],'uploads/')===0) ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/'.$p['image'];
        ?>
          <tr>
            <td><img src="<?= htmlspecialchars($img_url) ?>" class="table-img" alt=""/></td>
            <td style="font-weight:600;font-size:.85rem;max-width:200px"><?= htmlspecialchars($p['name']) ?></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= htmlspecialchars($p['cat_name']) ?></td>
            <td style="font-weight:700;color:var(--blue);white-space:nowrap"><?= formatPrice($p['price']) ?></td>
            <td><span style="color:<?= $p['stock_qty']==0?'var(--red)':($p['stock_qty']<=2?'var(--orange)':'var(--green)') ?>;font-weight:700"><?= $p['stock_qty'] ?></span></td>
            <td><span class="status-badge <?= $p['condition_type']==='New'?'status-delivered':'status-confirmed' ?>"><?= htmlspecialchars($p['condition_type']) ?></span></td>
            <td><?= $p['is_featured'] ? '<i class="fa-solid fa-star" style="color:#d97706"></i>' : '—' ?></td>
            <td>
              <div style="display:flex;gap:.4rem">
                <a href="?edit=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <a href="<?= SITE_URL ?>/product/<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-outline btn-sm" title="View"><i class="fa-solid fa-eye"></i></a>
                <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm confirm-delete"
                   data-title="Delete Product?"
                   data-msg="&quot;<?= htmlspecialchars(addslashes($p['name'])) ?>&quot; will be permanently removed from the store.">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Mobile cards -->
<div class="admin-cards-mobile">
  <div style="margin-bottom:1rem">
    <h3 style="font-size:1rem;font-weight:700"><?= count($all_products) ?> Products</h3>
  </div>
  <?php foreach ($all_products as $p):
    $img_url = (strpos($p['image'],'uploads/')===0) ? SITE_URL.'/'.$p['image'] : SITE_URL.'/assets/images/'.$p['image'];
  ?>
    <div class="admin-card" style="display:flex;gap:.9rem;align-items:flex-start">
      <!-- Product image -->
      <img src="<?= htmlspecialchars($img_url) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--border);flex-shrink:0"/>
      <!-- Info -->
      <div style="flex:1;min-width:0">
        <div style="font-weight:700;font-size:.875rem;margin-bottom:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($p['name']) ?></div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.4rem"><?= htmlspecialchars($p['cat_name']) ?> &bull; <?= htmlspecialchars($p['storage']) ?></div>
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.5rem">
          <span style="font-weight:800;color:var(--blue);font-size:.9rem"><?= formatPrice($p['price']) ?></span>
          <span style="color:<?= $p['stock_qty']==0?'var(--red)':($p['stock_qty']<=2?'var(--orange)':'var(--green)') ?>;font-size:.8rem;font-weight:700">
            <?= $p['stock_qty']==0 ? '✗ Out of stock' : '✓ Stock: '.$p['stock_qty'] ?>
          </span>
        </div>
        <!-- Action buttons -->
        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
          <a href="?edit=<?= $p['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
          <a href="<?= SITE_URL ?>/product/<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i></a>
          <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm confirm-delete"
             data-title="Delete Product?"
             data-msg="&quot;<?= htmlspecialchars(addslashes($p['name'])) ?>&quot; will be permanently removed from the store.">
            <i class="fa-solid fa-trash"></i>
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>

<style>
/* Product form grid: 2-col on desktop, 1-col on mobile */
.product-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}
/* Form inputs inside admin panel */
.product-form-grid .form-group input,
.product-form-grid .form-group select,
.form-group textarea {
  width: 100%;
  background: var(--bg-section);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  color: var(--text-primary);
  padding: .7rem .9rem;
  font-size: .875rem;
  outline: none;
  transition: .2s;
}
.product-form-grid .form-group input:focus,
.product-form-grid .form-group select:focus,
.form-group textarea:focus {
  border-color: var(--blue);
  box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.product-form-grid .form-group label,
.form-group label {
  display: block;
  font-size: .82rem;
  font-weight: 600;
  margin-bottom: .4rem;
  color: var(--text-secondary);
}
@media (max-width: 600px) {
  .product-form-grid { grid-template-columns: 1fr; }
}
</style>

<script>
function previewImage(input) {
  if (!input.files || !input.files[0]) return;
  if (input.files[0].size > 20 * 1024 * 1024) {
    alert('File too large. Max 20MB.');
    input.value = '';
    document.getElementById('newImgPreview').style.display = 'none';
    return;
  }
  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('newImgPreviewImg').src = e.target.result;
    document.getElementById('newImgPreview').style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
