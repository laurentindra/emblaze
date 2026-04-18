<?php
include 'koneksi.php';

// Fetch all products from DB
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products | Emblaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="products.css"
</head>
<body>
  <div class="bg-orb"></div>

  <header>
    <div class="container navbar">
      <div>
        <div class="brand-logo">Emblaze</div>
        <div class="brand-sub">Effortless Confidence</div>
      </div>

      <nav class="nav-links" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="index.php#collection">Collection</a>
        <a href="index.php#about">About</a>
        <a href="products.php" class="active">Products</a>
        <a href="index.php#contact">Contact</a>
      </nav>

      <div style="display:flex; align-items:center; gap:12px;">
        <a href="index.php#collection" class="btn btn-secondary desktop-only">Shop Now</a>
        <a href="cart.php" class="login-icon" title="Cart">
  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
    <line x1="3" y1="6" x2="21" y2="6"/>
    <path d="M16 10a4 4 0 0 1-8 0"/>
  </svg>
</a>

        <a href="#login" class="login-icon" title="Login">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </a>
        <button class="menu-toggle" id="menuToggle">☰</button>
      </div>
    </div>
  </header>

  <main>
    <section class="page-hero">
      <div class="container">
        <div class="page-hero-inner">
          <div>
            <div class="breadcrumb">Home &rsaquo; <span>Products</span></div>
            <h1>The Full <em>Collection</em></h1>
          </div>
          <div class="product-count" id="productCount">Showing 12 products</div>
        </div>
      </div>
    </section>

    <div class="container">
      <div class="toolbar">
        <div class="chip-list" id="filterChips">
          <button class="chip active" data-filter="all">All</button>
          <button class="chip" data-filter="women">Women</button>
          <button class="chip" data-filter="men">Men</button>
          <button class="chip" data-filter="jewelry">Jewelry</button>
          <button class="chip" data-filter="skirt">Skirt</button>
          <button class="chip" data-filter="footwear">Footwear</button>
        </div>

        <div class="sort-wrap">
          <span>Sort by</span>
          <select class="sort-select" id="sortSelect">
            <option value="default">Featured</option>
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
            <option value="name">Name A–Z</option>
          </select>
        </div>
      </div>

      <div class="product-grid" id="productGrid">

  <?php foreach ($products as $p): ?>
  <article class="product-card glass"
    data-id="<?= $p['id'] ?>"
    data-category="<?= htmlspecialchars($p['category']) ?>"
    data-price="<?= $p['price'] ?>"
    data-name="<?= htmlspecialchars($p['name']) ?>">

    <div class="product-image">
      <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" />
      <?php if ($p['stock'] <= 5 && $p['stock'] > 0): ?>
        <span class="product-badge">Limited</span>
      <?php elseif ($p['stock'] == 0): ?>
        <span class="product-badge">Sold Out</span>
      <?php endif; ?>
    </div>

    <div class="product-body">
      <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
      <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
      <div class="product-price">Rp<?= number_format($p['price'], 0, ',', '.') ?></div>
      <?php if ($p['stock'] > 0): ?>
        <button class="full-btn" <?php if($p['stock']==0) echo 'disabled'; ?>>Add to Cart</button>
      <?php else: ?>
        <button class="full-btn" disabled style="opacity:0.5;cursor:not-allowed;">Out of Stock</button>
      <?php endif; ?>
    </div>
  </article>
  <?php endforeach; ?>

  <div class="empty-state" id="emptyState">
    <h3>No products found</h3>
    <p>Try selecting a different category.</p>
  </div>

</div>
    </div>
  </main>

  <footer>
    <div class="container">© 2026 Emblaze. Designed with soft minimal elegance.</div>
  </footer>

  <script src="products.js"></script>
</body>
</html>