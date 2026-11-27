
    </div><!-- /admin-content -->
  </div><!-- /admin-main -->

  <!-- Sidebar overlay (mobile) -->
  <div id="adminOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;" onclick="closeSidebar()"></div>

</div><!-- /admin-layout -->

<!-- ============================================================
     GLOBAL DELETE CONFIRMATION MODAL
     Usage: <a href="url" class="confirm-delete" data-title="Delete Product?" data-msg="This cannot be undone.">Delete</a>
     ============================================================ -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:1rem;">
  <!-- Backdrop -->
  <div id="deleteModalBackdrop"
       onclick="closeDeleteModal()"
       style="position:absolute;inset:0;background:rgba(10,14,26,.55);backdrop-filter:blur(4px)"></div>
  <!-- Dialog -->
  <div id="deleteModalDialog"
       style="position:relative;z-index:1;background:#fff;border-radius:16px;padding:0;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;transform:scale(.95);transition:transform .2s ease,opacity .2s ease;opacity:0">
    <!-- Header strip -->
    <div style="background:linear-gradient(135deg,#fef2f2,#fff);padding:1.75rem 1.75rem 1rem;text-align:center">
      <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;border:2px solid #fecaca;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.5rem;color:#dc2626">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 id="deleteModalTitle" style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:.35rem">Delete Item?</h3>
      <p  id="deleteModalMsg"   style="font-size:.875rem;color:#64748b;line-height:1.6">This action cannot be undone.</p>
    </div>
    <!-- Buttons -->
    <div style="padding:1rem 1.75rem 1.5rem;display:flex;gap:.75rem">
      <button onclick="closeDeleteModal()"
              style="flex:1;padding:.75rem;border:1.5px solid #e2e8f0;background:#fff;border-radius:9px;font-size:.9rem;font-weight:600;cursor:pointer;color:#334155;transition:.2s;font-family:inherit"
              onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
        <i class="fa-solid fa-xmark" style="margin-right:.35rem"></i>Cancel
      </button>
      <a id="deleteModalConfirm" href="#"
         style="flex:1;padding:.75rem;background:#dc2626;border:none;border-radius:9px;font-size:.9rem;font-weight:700;cursor:pointer;color:#fff;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:.4rem;transition:.2s;font-family:inherit"
         onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
        <i class="fa-solid fa-trash-can"></i> Delete
      </a>
    </div>
  </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

<script>
// =====================================================
// DELETE CONFIRMATION MODAL
// =====================================================
function openDeleteModal(href, title, msg) {
  var modal   = document.getElementById('deleteModal');
  var dialog  = document.getElementById('deleteModalDialog');
  var titleEl = document.getElementById('deleteModalTitle');
  var msgEl   = document.getElementById('deleteModalMsg');
  var confirm = document.getElementById('deleteModalConfirm');

  titleEl.textContent  = title || 'Delete Item?';
  msgEl.textContent    = msg   || 'This action cannot be undone.';
  confirm.href         = href;

  modal.style.display  = 'flex';
  // Trigger animation
  setTimeout(function() {
    dialog.style.transform = 'scale(1)';
    dialog.style.opacity   = '1';
  }, 10);
  document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
  var modal  = document.getElementById('deleteModal');
  var dialog = document.getElementById('deleteModalDialog');
  dialog.style.transform = 'scale(.95)';
  dialog.style.opacity   = '0';
  setTimeout(function() {
    modal.style.display   = 'none';
    document.body.style.overflow = '';
  }, 200);
}

// Close on ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeDeleteModal();
});

// Wire up all .confirm-delete links automatically
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.confirm-delete').forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      openDeleteModal(
        this.getAttribute('href'),
        this.getAttribute('data-title') || 'Delete Item?',
        this.getAttribute('data-msg')   || 'Are you sure you want to delete this? This action cannot be undone.'
      );
    });
  });
});

// =====================================================
// ADMIN SIDEBAR TOGGLE
// =====================================================
var adminHamburger = document.getElementById('adminHamburger');
var adminSidebar   = document.getElementById('adminSidebar');
var adminOverlay   = document.getElementById('adminOverlay');

if (adminHamburger) {
  adminHamburger.addEventListener('click', function(e) {
    e.stopPropagation();
    var isOpen = adminSidebar.classList.toggle('open');
    adminHamburger.classList.toggle('active', isOpen);
    adminOverlay.style.display = isOpen ? 'block' : 'none';
  });
}

function closeSidebar() {
  if (adminSidebar)   adminSidebar.classList.remove('open');
  if (adminHamburger) adminHamburger.classList.remove('active');
  if (adminOverlay)   adminOverlay.style.display = 'none';
}

if (adminSidebar) {
  adminSidebar.querySelectorAll('a').forEach(function(link) {
    link.addEventListener('click', function() {
      if (window.innerWidth <= 768) closeSidebar();
    });
  });
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeSidebar();
    closeDeleteModal();
  }
});
</script>
</body>
</html>
