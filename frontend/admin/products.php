<?php
require 'auth.php';
include '../koneksi.php';

$success = $_SESSION['flash_success'] ?? '';
$errMsg  = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$totalProducts = count($products);


$categories = ['Women', 'Men', 'Jewelry', 'Skirt', 'Footwear', 'Outerwear', 'Tops', 'Dress', 'Other'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products | Emblaze Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="admin-body">

<?php include 'sidebar.php'; ?>

<div class="admin-main">
  <div class="admin-topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="topbar-breadcrumb">
        <span class="breadcrumb-root">Admin</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Products</span>
      </div>
    </div>
    <div class="topbar-right">
      <div class="admin-avatar-wrap">
        <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_name'], 0, 1)) ?></div>
        <span class="admin-name-label"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
      </div>
    </div>
  </div>

  <div class="page-header">
    <div>
      <h1 class="page-title">Product <em>Management</em></h1>
      <p class="page-sub"><?= $totalProducts ?> products in catalog</p>
    </div>
    <button class="btn-admin-primary" onclick="openModal('addModal')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Product
    </button>
  </div>

  <?php if ($success): ?>
    <div class="flash-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($errMsg): ?>
    <div class="flash-error"><?= htmlspecialchars($errMsg) ?></div>
  <?php endif; ?>

  <div class="toolbar-admin">
    <input type="text" id="searchProduct" class="search-input-admin" placeholder="Search products..." oninput="filterProducts()" />
    <select id="filterCategory" class="filter-select-admin" onchange="filterProducts()">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= strtolower($cat) ?>"><?= $cat ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="table-card glass-admin">
    <div class="table-wrap">
      <table class="admin-table" id="productsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Image</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Added</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="productsBody">
          <?php if (empty($products)): ?>
            <tr><td colspan="8" class="empty-admin">No products found. Add your first product!</td></tr>
          <?php else: ?>
          <?php foreach ($products as $i => $p): ?>
          <tr data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>" data-cat="<?= strtolower(htmlspecialchars($p['category'])) ?>">
            <td><?= $i + 1 ?></td>
            <td>
              <div class="product-thumb-wrap">
                <?php if ($p['image_url']): ?>
                  <img src="../../<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                       class="product-thumb" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <?php endif; ?>
                <div class="product-thumb-placeholder" style="<?= $p['image_url'] ? 'display:none' : '' ?>">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
              </div>
            </td>
            <td>
              <div class="product-name-cell"><?= htmlspecialchars($p['name']) ?></div>
              <div class="product-desc-cell"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 50)) ?>...</div>
            </td>
            <td><span class="badge-cat"><?= htmlspecialchars($p['category']) ?></span></td>
            <td>Rp<?= number_format($p['price'], 0, ',', '.') ?></td>
            <td>
              <span class="stock-badge <?= $p['stock'] == 0 ? 'stock-out' : ($p['stock'] <= 5 ? 'stock-low' : 'stock-ok') ?>">
                <?= $p['stock'] == 0 ? 'Out of Stock' : $p['stock'] ?>
              </span>
            </td>
            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
            <td>
              <div class="action-btns">
                <button class="table-action-btn table-action-edit" title="Edit"
                  onclick="openEdit(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', '<?= addslashes(htmlspecialchars($p['description'])) ?>', '<?= $p['price'] ?>', '<?= $p['stock'] ?>', '<?= addslashes(htmlspecialchars($p['category'])) ?>', '<?= addslashes(htmlspecialchars($p['image_url'] ?? '')) ?>')">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </button>
                <form method="POST" action="products_action.php" onsubmit="return confirm('Delete this product?')" style="display:inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="table-action-btn table-action-delete" title="Delete">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    Delete
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Add New Product</h3>
      <button class="modal-close" onclick="closeModal('addModal')">×</button>
    </div>
    <form method="POST" action="products_action.php" class="modal-form" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="form-row-modal">
        <div class="form-group-modal">
          <label class="form-label-admin">Product Name *</label>
          <input type="text" name="name" class="form-input-admin" placeholder="e.g. Classic White Dress" required />
        </div>
        <div class="form-group-modal">
          <label class="form-label-admin">Category *</label>
          <select name="category" class="form-input-admin" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>"><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group-modal">
        <label class="form-label-admin">Description</label>
        <textarea name="description" class="form-textarea-admin" rows="3" placeholder="Product description..."></textarea>
      </div>
      <div class="form-row-modal">
        <div class="form-group-modal">
          <label class="form-label-admin">Price (Rp) *</label>
          <input type="number" name="price" class="form-input-admin" placeholder="250000" min="0" step="1000" required />
        </div>
        <div class="form-group-modal">
          <label class="form-label-admin">Stock *</label>
          <input type="number" name="stock" class="form-input-admin" placeholder="10" min="0" required />
        </div>
      </div>
      <div class="form-group-modal">
        <label class="form-label-admin">Product Image</label>
        <input type="file" name="image" class="form-input-admin file-input-admin" accept="image/*" />
        <p class="form-hint-admin">JPG, PNG, WEBP — max 2MB</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-admin-secondary" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" class="btn-admin-primary">Add Product</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 class="modal-title">Edit Product</h3>
      <button class="modal-close" onclick="closeModal('editModal')">×</button>
    </div>
    <form method="POST" action="products_action.php" class="modal-form" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editId">
      <input type="hidden" name="existing_image" id="editExistingImage">
      <div class="form-row-modal">
        <div class="form-group-modal">
          <label class="form-label-admin">Product Name *</label>
          <input type="text" name="name" id="editName" class="form-input-admin" required />
        </div>
        <div class="form-group-modal">
          <label class="form-label-admin">Category *</label>
          <select name="category" id="editCategory" class="form-input-admin" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>"><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group-modal">
        <label class="form-label-admin">Description</label>
        <textarea name="description" id="editDescription" class="form-textarea-admin" rows="3"></textarea>
      </div>
      <div class="form-row-modal">
        <div class="form-group-modal">
          <label class="form-label-admin">Price (Rp) *</label>
          <input type="number" name="price" id="editPrice" class="form-input-admin" min="0" step="1000" required />
        </div>
        <div class="form-group-modal">
          <label class="form-label-admin">Stock *</label>
          <input type="number" name="stock" id="editStock" class="form-input-admin" min="0" required />
        </div>
      </div>
      <div class="form-group-modal">
        <label class="form-label-admin">Product Image</label>
        <div id="editImagePreview" class="edit-img-preview" style="display:none">
          <img id="editCurrentImg" src="" alt="Current" style="height:60px; border-radius:8px; object-fit:cover;" />
          <span class="edit-img-label">Current image</span>
        </div>
        <input type="file" name="image" class="form-input-admin file-input-admin" accept="image/*" id="editImageFile" />
        <p class="form-hint-admin">Leave empty to keep existing image</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-admin-secondary" onclick="closeModal('editModal')">Cancel</button>
        <button type="submit" class="btn-admin-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script src="admin.js"></script>
<script>
function openEdit(id, name, desc, price, stock, cat, img) {
  document.getElementById('editId').value            = id;
  document.getElementById('editName').value          = name;
  document.getElementById('editDescription').value   = desc;
  document.getElementById('editPrice').value         = price;
  document.getElementById('editStock').value         = stock;
  document.getElementById('editExistingImage').value = img;


  const catSelect = document.getElementById('editCategory');
  for (let opt of catSelect.options) {
    opt.selected = opt.value.toLowerCase() === cat.toLowerCase();
  }

  
  const preview = document.getElementById('editImagePreview');
  const imgEl   = document.getElementById('editCurrentImg');
  if (img) {
    imgEl.src = '../../' + img;
    preview.style.display = 'flex';
  } else {
    preview.style.display = 'none';
  }

  // Reset file input
  document.getElementById('editImageFile').value = '';

  openModal('editModal');
}

function filterProducts() {
  const q   = document.getElementById('searchProduct').value.toLowerCase();
  const cat = document.getElementById('filterCategory').value.toLowerCase();
  document.querySelectorAll('#productsBody tr[data-name]').forEach(row => {
    const nameMatch = row.dataset.name.includes(q);
    const catMatch  = cat === '' || row.dataset.cat === cat;
    row.style.display = (nameMatch && catMatch) ? '' : 'none';
  });
}
</script>
</body>
</html>
