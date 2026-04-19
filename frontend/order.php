<?php
session_start();
// Disable display_errors for POST (AJAX) requests to prevent HTML errors corrupting JSON
ini_set('display_errors', 0);
error_reporting(0);
include 'koneksi.php';

// Require login to access order page
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Please login first."]);
        exit;
    }
    header('Location: login.php');
    exit;
}

$isLoggedIn = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    header('Content-Type: application/json');

    $user_id         = $_SESSION['user_id'];
    $shipping_method = $_POST['shipping_method'] ?? 'regular';
    $payment_method  = $_POST['payment_method']  ?? 'transfer';
    $address         = trim($_POST['address'] ?? '');

    try {
        $items = json_decode($_POST['cart_items'] ?? '[]', true);
        if (empty($items)) {
            echo json_encode(["success" => false, "message" => "Cart is empty."]);
            exit;
        }

        foreach ($items as $item) {
            // Sanitize: product_id must be a valid DB int, not a JS timestamp
            $rawId    = $item['product_id'] ?? null;
            $productId = ($rawId !== null && $rawId < 2147483647) ? intval($rawId) : null;

            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, product_id, quantity, total_price, shipping_method, payment_method, status, shipping_address)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt->execute([
                $user_id,
                $productId,
                intval($item['qty'] ?? 1),
                floatval($item['price'] ?? 0) * intval($item['qty'] ?? 1),
                $shipping_method,
                $payment_method,
                $address
            ]);
        }

        $order_id = 'EMB-' . str_pad($pdo->lastInsertId(), 5, '0', STR_PAD_LEFT);
        echo json_encode(["success" => true, "order_id" => $order_id, "method" => $payment_method]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order | Emblaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="order.css" />
</head>
<body>
  <div class="bg-orb"></div>

  <header>
    <div class="container navbar">
      <a href="index.php" style="text-decoration:none;">
        <div class="brand-logo">Emblaze</div>
        <div class="brand-sub">Effortless Confidence</div>
      </a>

      <nav class="nav-links" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="index.php#collection">Collection</a>
        <a href="index.php#about">About</a>
        <a href="products.php">Products</a>
        <a href="index.php#contact">Contact</a>
      </nav>

      <div style="display:flex; align-items:center; gap:12px;">
        <a href="products.php" class="btn btn-secondary desktop-only">Shop Now</a>
        <a href="cart.php" class="login-icon" title="Cart">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </a>
        <a href="profile.php" class="login-icon" title="Profile">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </a>
        <button class="menu-toggle" id="menuToggle">☰</button>
      </div>
    </div>
  </header>

  <main class="order-page">
    <div class="container">

      <div class="breadcrumb">
        <a href="index.php">Home</a> &rsaquo;
        <a href="products.php">Products</a> &rsaquo;
        <span>Order</span>
      </div>

      <div class="steps">
        <div class="step done">
          <div class="step-num">✓</div>
          <span>Cart</span>
        </div>
        <div class="step-line"></div>
        <div class="step active">
          <div class="step-num">2</div>
          <span>Details</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
          <div class="step-num">3</div>
          <span>Payment</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
          <div class="step-num">4</div>
          <span>Confirm</span>
        </div>
      </div>

      <div class="order-grid">

        <div>

          <div class="form-card glass">
            <div class="section-label">Step 01</div>
            <div class="section-heading">Shipping <em>Details</em></div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input class="form-input" type="text" id="first_name" placeholder="Nadira" />
              </div>
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input class="form-input" type="text" id="last_name" placeholder="Aurellia" />
              </div>
            </div>

            <div class="form-row full">
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="form-input" type="email" id="email" placeholder="your@email.com" />
              </div>
            </div>

            <div class="form-row full">
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input class="form-input" type="tel" id="phone" placeholder="+62 812 0000 0000" />
              </div>
            </div>

            <div class="form-row full">
              <div class="form-group">
                <label class="form-label">Street Address</label>
                <input class="form-input" type="text" id="address" placeholder="Jl. Merdeka No. 12" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">City</label>
                <input class="form-input" type="text" id="city" placeholder="Jakarta" />
              </div>
              <div class="form-group">
                <label class="form-label">Postal Code</label>
                <input class="form-input" type="text" id="postal" placeholder="12345" />
              </div>
            </div>

            <div class="form-row full">
              <div class="form-group">
                <label class="form-label">Province</label>
                <select class="form-select" id="province">
                  <option value="">Select province</option>
                  <option>DKI Jakarta</option>
                  <option>Jawa Barat</option>
                  <option>Jawa Tengah</option>
                  <option>Jawa Timur</option>
                  <option>Banten</option>
                  <option>Bali</option>
                  <option>Sumatera Utara</option>
                  <option>Sulawesi Selatan</option>
                </select>
              </div>
            </div>

            <div class="form-row full">
              <div class="form-group">
                <label class="form-label">Delivery Note (Optional)</label>
                <input class="form-input" type="text" placeholder="e.g. Leave at front door" />
              </div>
            </div>
          </div>

          <div class="form-card glass">
            <div class="section-label">Step 02</div>
            <div class="section-heading">Shipping <em>Method</em></div>

            <div class="payment-options" id="shippingOptions">
              <div class="payment-option selected" data-method="regular" onclick="selectShipping(this,'regular',25000)">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">Regular Shipping</div>
                  <div class="payment-desc">Estimated 3-5 business days</div>
                </div>
                <div class="payment-icon" style="font-size:14px; color:#7c1f2d; font-weight:500;">Rp25.000</div>
              </div>
              <div class="payment-option" data-method="express" onclick="selectShipping(this,'express',55000)">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">Express Shipping</div>
                  <div class="payment-desc">Estimated 1-2 business days</div>
                </div>
                <div class="payment-icon" style="font-size:14px; color:#7c1f2d; font-weight:500;">Rp55.000</div>
              </div>
              <div class="payment-option" data-method="same" onclick="selectShipping(this,'same',85000)">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">Same Day Delivery</div>
                  <div class="payment-desc">Only available within Jakarta</div>
                </div>
                <div class="payment-icon" style="font-size:14px; color:#7c1f2d; font-weight:500;">Rp85.000</div>
              </div>
            </div>
          </div>

          <div class="form-card glass">
            <div class="section-label">Step 03</div>
            <div class="section-heading">Payment <em>Method</em></div>

            <div class="payment-options" id="paymentOptions">
              <div class="payment-option selected" data-pay="transfer" onclick="selectPayment(this,'transfer')">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">Bank Transfer</div>
                  <div class="payment-desc">BCA, Mandiri, BNI, BRI</div>
                </div>
                <svg class="pay-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events:none"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
              </div>
              <div class="payment-option" data-pay="qris" onclick="selectPayment(this,'qris')">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">QRIS</div>
                  <div class="payment-desc">Scan QR with any payment app</div>
                </div>
                <svg class="pay-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events:none"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
              </div>
              <div class="payment-option" data-pay="ewallet" onclick="selectPayment(this,'ewallet')">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">E-Wallet</div>
                  <div class="payment-desc">GoPay, OVO, DANA, ShopeePay</div>
                </div>
                <svg class="pay-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events:none"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M16 13a1 1 0 1 0 2 0 1 1 0 0 0-2 0z" fill="currentColor"/></svg>
              </div>
              <div class="payment-option" data-pay="cod" onclick="selectPayment(this,'cod')">
                <div class="payment-radio"></div>
                <div class="payment-label">
                  <div class="payment-name">Cash on Delivery</div>
                  <div class="payment-desc">Pay when your order arrives</div>
                </div>
                <svg class="pay-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events:none"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/></svg>
              </div>
            </div>

            <div class="card-fields" id="cardFields">
              <div class="form-row full" style="margin-top:4px;">
                <div class="form-group">
                  <label class="form-label">Card Number</label>
                  <input class="form-input" type="text" placeholder="0000 0000 0000 0000" maxlength="19" id="cardNumber" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Expiry Date</label>
                  <input class="form-input" type="text" placeholder="MM / YY" maxlength="7" />
                </div>
                <div class="form-group">
                  <label class="form-label">CVV</label>
                  <input class="form-input" type="text" placeholder="•••" maxlength="3" />
                </div>
              </div>
              <div class="form-row full">
                <div class="form-group">
                  <label class="form-label">Cardholder Name</label>
                  <input class="form-input" type="text" placeholder="As on card" />
                </div>
              </div>
            </div>
          </div>

        </div>

        <div>
          <div class="order-summary glass">
            <div class="summary-title">Order Summary</div>

            <div class="cart-items" id="cartItems"></div>

            <div class="promo-wrap">
              <input class="promo-input" type="text" placeholder="Promo code" id="promoInput" />
              <button class="promo-btn" onclick="applyPromo()">Apply</button>
            </div>

            <div class="summary-totals">
              <div class="total-row">
                <span>Subtotal</span>
                <span id="subtotal">Rp1.130.000</span>
              </div>
              <div class="total-row">
                <span>Shipping</span>
                <span id="shippingCost">Rp25.000</span>
              </div>
              <div class="total-row" id="discountRow" style="display:none; color:#3a7a5a;">
                <span>Discount</span>
                <span id="discountAmt">− Rp0</span>
              </div>
              <div class="total-row grand">
                <span>Total</span>
                <span id="grandTotal">Rp1.155.000</span>
              </div>
            </div>

            <button class="place-order-btn" onclick="placeOrder()">Place Order</button>
            <div class="secure-note">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Secured & encrypted checkout
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <div class="success-overlay" id="successOverlay">
    <div class="success-box">
      <div class="success-icon">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <h2>Order <em>Placed!</em></h2>
      <p>Thank you for shopping with Emblaze. Your order is being prepared with care and will be on its way soon.</p>
      <div class="success-order-id" id="orderId">Order #EMB-00000</div>
      <div class="success-actions">
        <a href="index.php" class="btn-outline">Back to Home</a>
        <a href="products.php" class="btn-filled">Continue Shopping</a>
      </div>
    </div>
  </div>

    <script src="order.js?v=<?= time() ?>"></script>
</body>
</html>