<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$order_id = htmlspecialchars($_GET['order_id'] ?? 'EMB-00000');
$method   = $_GET['method'] ?? 'transfer';

$methodLabels = [
    'transfer' => 'Bank Transfer',
    'qris'     => 'QRIS',
    'ewallet'  => 'E-Wallet',
    'cod'      => 'Cash on Delivery',
];
$methodLabel = $methodLabels[$method] ?? 'Payment';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment | Emblaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .pay-page { padding: 52px 0 100px; }

    .steps { display: flex; align-items: center; gap: 0; margin-bottom: 48px; }
    .step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .step-num { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Jost', sans-serif; font-size: 13px; font-weight: 500; border: 1px solid #e7c8bf; color: #c4a99a; background: white; }
    .step.done .step-num { background: #7c1f2d; color: white; border-color: #7c1f2d; }
    .step.active .step-num { background: linear-gradient(135deg, #9f1d2e, #c4455b); color: white; border: none; box-shadow: 0 6px 18px rgba(159,29,46,0.3); }
    .step span { font-size: 11px; letter-spacing: 0.12em; color: #b09090; text-transform: uppercase; }
    .step.active span, .step.done span { color: #7c1f2d; }
    .step-line { flex: 1; height: 1px; background: linear-gradient(to right, #e7c8bf, #f3e4e0); margin: 0 8px 18px; }

    .pay-grid { display: grid; grid-template-columns: 1fr 360px; gap: 32px; align-items: start; }

    .pay-card { border-radius: 28px; padding: 36px; background: rgba(255,255,255,0.88); backdrop-filter: blur(20px); border: 1px solid rgba(231,200,191,0.4); box-shadow: 0 8px 32px rgba(204,177,170,0.12); margin-bottom: 24px; }

    .section-label { font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; color: #b09080; margin-bottom: 6px; }
    .section-heading { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 300; color: var(--text-dark); margin-bottom: 28px; }
    .section-heading em { font-style: italic; color: #9f1d2e; }

    /* Bank Transfer */
    .bank-table { width: 100%; border-collapse: collapse; }
    .bank-table tr { border-bottom: 1px solid rgba(231,200,191,0.35); }
    .bank-table tr:last-child { border-bottom: none; }
    .bank-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; }
    .bank-logo { display: flex; align-items: center; gap: 14px; }
    .bank-badge { width: 44px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; letter-spacing: 0.05em; color: white; }
    .bank-name { font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 500; color: var(--text-dark); }
    .bank-acct { font-size: 13px; color: #7c1f2d; font-weight: 600; letter-spacing: 0.08em; cursor: pointer; user-select: all; transition: opacity .2s; }
    .bank-acct:hover { opacity: 0.7; }
    .bank-an { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .copy-hint { font-size: 11px; color: #c4a99a; margin-top: 16px; font-style: italic; }

    /* qrisnya kak */
    .qris-wrap { display: flex; flex-direction: column; align-items: center; gap: 20px; padding: 10px 0; }
    .qris-img { width: 260px; height: auto; border-radius: 20px; box-shadow: 0 12px 36px rgba(0,0,0,0.10); border: 3px solid rgba(231,200,191,0.5); }
    .qris-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; width: 100%; margin-top: 8px; }
    .qris-step { text-align: center; padding: 14px 8px; background: rgba(252,244,242,0.8); border-radius: 14px; border: 1px solid rgba(231,200,191,0.3); }
    .qris-step-num { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg,#9f1d2e,#c4455b); color: white; font-size: 12px; font-weight: 600; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; }
    .qris-step p { font-size: 11px; color: var(--text-muted); line-height: 1.4; }

    /* iwalet */
    .ewallet-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ew-card { padding: 18px 20px; border-radius: 16px; background: rgba(252,244,242,0.8); border: 1px solid rgba(231,200,191,0.35); }
    .ew-name { font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px; }
    .ew-number { font-size: 18px; font-weight: 600; color: #7c1f2d; letter-spacing: 0.05em; }
    .ew-an { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

    /* sopi cod sopi cod */
    .cod-wrap { text-align: center; padding: 20px 0; }
    .cod-icon-wrap { width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg,#f4d4ca,#fff1e9); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    .cod-text { font-size: 15px; color: var(--text-soft); line-height: 1.7; }


    .pay-steps-list { display: flex; flex-direction: column; gap: 14px; margin-top: 4px; }
    .pay-step-item { display: flex; align-items: flex-start; gap: 14px; }
    .pay-step-circle { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#9f1d2e,#c4455b); color: white; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .pay-step-text { font-size: 14px; color: var(--text-soft); line-height: 1.5; }
    .pay-step-text strong { color: var(--text-dark); display: block; margin-bottom: 2px; }


    .order-summary { border-radius: 28px; padding: 32px; background: rgba(255,255,255,0.88); backdrop-filter: blur(20px); border: 1px solid rgba(231,200,191,0.4); box-shadow: 0 8px 32px rgba(204,177,170,0.12); position: sticky; top: 90px; }
    .summary-title { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 300; color: var(--text-dark); margin-bottom: 20px; }
    .order-id-box { background: linear-gradient(135deg,rgba(159,29,46,0.06),rgba(196,69,91,0.04)); border: 1px solid rgba(159,29,46,0.15); border-radius: 14px; padding: 16px 18px; margin-bottom: 20px; text-align: center; }
    .order-id-label { font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: #b09080; margin-bottom: 6px; }
    .order-id-val { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #7c1f2d; }
    .method-box { display: flex; align-items: center; gap: 10px; padding: 14px 18px; background: rgba(252,244,242,0.8); border-radius: 14px; border: 1px solid rgba(231,200,191,0.35); margin-bottom: 20px; }
    .method-box svg { flex-shrink: 0; color: #9f1d2e; }
    .method-box-label { font-size: 11px; color: var(--text-muted); }
    .method-box-val { font-size: 14px; font-weight: 500; color: var(--text-dark); }
    .pay-note { font-size: 12px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px; }
    .divider-soft { height: 1px; background: linear-gradient(to right, transparent, #efcfc7, transparent); margin: 20px 0; }
    .confirm-btn { width: 100%; padding: 17px; border: none; border-radius: 999px; background: linear-gradient(135deg, #9f1d2e 0%, #c4455b 48%, #f1d7cc 100%); color: white; font-family: 'Jost', sans-serif; font-size: 15px; font-weight: 500; letter-spacing: 0.08em; cursor: pointer; box-shadow: 0 12px 30px rgba(159,29,46,0.28); transition: transform 0.28s, box-shadow 0.28s; display: block; text-align: center; text-decoration: none; }
    .confirm-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(159,29,46,0.34); }
    .secure-note { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; color: var(--text-muted); margin-top: 14px; }

    .breadcrumb { font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 32px; }
    .breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .breadcrumb span { color: #7c1f2d; }

    @media (max-width: 900px) {
      .pay-grid { grid-template-columns: 1fr; }
      .order-summary { position: static; }
      .qris-steps { grid-template-columns: repeat(2,1fr); }
      .ewallet-grid { grid-template-columns: 1fr; }
    }
  </style>
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
        <a href="products.php">Products</a>
      </nav>
      <div style="display:flex; align-items:center; gap:12px;">
        <a href="profile.php" class="login-icon" title="Profile">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </a>
        <button class="menu-toggle" id="menuToggle">&#9776;</button>
      </div>
    </div>
  </header>

  <main class="pay-page">
    <div class="container">

      <div class="breadcrumb">
        <a href="index.php">Home</a> &rsaquo;
        <a href="cart.php">Cart</a> &rsaquo;
        <a href="order.php">Order</a> &rsaquo;
        <span>Payment</span>
      </div>

      <div class="steps">
        <div class="step done"><div class="step-num">&#10003;</div><span>Cart</span></div>
        <div class="step-line"></div>
        <div class="step done"><div class="step-num">&#10003;</div><span>Details</span></div>
        <div class="step-line"></div>
        <div class="step active"><div class="step-num">3</div><span>Payment</span></div>
        <div class="step-line"></div>
        <div class="step"><div class="step-num">4</div><span>Confirm</span></div>
      </div>

      <div class="pay-grid">

        <!-- LEFT: Payment Instructions -->
        <div>

          <?php if ($method === 'transfer'): ?>
          <div class="pay-card">
            <div class="section-label">Step 03</div>
            <div class="section-heading">Bank <em>Transfer</em></div>

            <?php
            $banks = [
              ['label'=>'BCA', 'color'=>'#005BAC', 'acct'=>'1234 5678 90', 'an'=>'Emblaze Fashion Store'],
              ['label'=>'Mandiri', 'color'=>'#003087', 'acct'=>'9876 5432 10', 'an'=>'Emblaze Fashion Store'],
              ['label'=>'BNI', 'color'=>'#F68B1F', 'acct'=>'1122 3344 55', 'an'=>'Emblaze Fashion Store'],
              ['label'=>'BRI', 'color'=>'#003CA6', 'acct'=>'5566 7788 99', 'an'=>'Emblaze Fashion Store'],
            ];
            foreach ($banks as $b):
            ?>
            <div class="bank-row">
              <div class="bank-logo">
                <div class="bank-badge" style="background:<?= $b['color'] ?>"><?= $b['label'] ?></div>
                <div>
                  <div class="bank-name">Bank <?= $b['label'] ?></div>
                  <div class="bank-an"><?= $b['an'] ?></div>
                </div>
              </div>
              <div class="bank-acct" title="Click to select"><?= $b['acct'] ?></div>
            </div>
            <?php endforeach; ?>
            <p class="copy-hint">Click the account number to select and copy.</p>

            <div class="divider-soft"></div>
            <div class="pay-steps-list">
              <div class="pay-step-item">
                <div class="pay-step-circle">1</div>
                <div class="pay-step-text"><strong>Open your banking app</strong>Transfer to the account number above using any bank or app.</div>
              </div>
              <div class="pay-step-item">
                <div class="pay-step-circle">2</div>
                <div class="pay-step-text"><strong>Enter exact amount</strong>Match the total in your order summary including shipping fee.</div>
              </div>
              <div class="pay-step-item">
                <div class="pay-step-circle">3</div>
                <div class="pay-step-text"><strong>Confirm your payment</strong>Click the button on the right once transfer is done.</div>
              </div>
            </div>
          </div>

          <?php elseif ($method === 'qris'): ?>
          <div class="pay-card">
            <div class="section-label">Step 03</div>
            <div class="section-heading">Scan <em>QRIS</em></div>
            <div class="qris-wrap">
              <?php
              $qrisFile = null;
              if (file_exists(__DIR__ . '/uploads/qris.jpg'))  $qrisFile = 'uploads/qris.jpg';
              elseif (file_exists(__DIR__ . '/uploads/qris.png')) $qrisFile = 'uploads/qris.png';
              ?>
              <?php if ($qrisFile): ?>
                <img src="<?= $qrisFile ?>" alt="QRIS Code" class="qris-img" />
              <?php else: ?>
                <div style="width:260px;height:260px;border-radius:20px;background:rgba(252,244,242,0.8);border:2px dashed #e7c8bf;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#c4a99a" stroke-width="1.2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                  <p style="font-size:12px;color:#c4a99a;text-align:center;">Place qris.jpg in<br>uploads/ folder</p>
                </div>
              <?php endif; ?>

              <div class="qris-steps">
                <div class="qris-step"><div class="qris-step-num">1</div><p>Open your payment app</p></div>
                <div class="qris-step"><div class="qris-step-num">2</div><p>Tap Scan QR Code</p></div>
                <div class="qris-step"><div class="qris-step-num">3</div><p>Review payment details</p></div>
                <div class="qris-step"><div class="qris-step-num">4</div><p>Complete the payment</p></div>
              </div>
            </div>
          </div>

          <?php elseif ($method === 'ewallet'): ?>
          <div class="pay-card">
            <div class="section-label">Step 03</div>
            <div class="section-heading">E-Wallet <em>Transfer</em></div>
            <div class="ewallet-grid">
              <?php
              $wallets = [
                ['name'=>'GoPay',     'number'=>'0821 4455 6677', 'an'=>'Emblaze Store'],
                ['name'=>'OVO',       'number'=>'0812 3344 5566', 'an'=>'Emblaze Store'],
                ['name'=>'DANA',      'number'=>'0856 7788 9900', 'an'=>'Emblaze Store'],
                ['name'=>'ShopeePay', 'number'=>'0878 1122 3344', 'an'=>'Emblaze Store'],
              ];
              foreach ($wallets as $w):
              ?>
              <div class="ew-card">
                <div class="ew-name"><?= $w['name'] ?></div>
                <div class="ew-number"><?= $w['number'] ?></div>
                <div class="ew-an"><?= $w['an'] ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="divider-soft"></div>
            <div class="pay-steps-list" style="margin-top:0;">
              <div class="pay-step-item">
                <div class="pay-step-circle">1</div>
                <div class="pay-step-text"><strong>Open your e-wallet app</strong>Choose your preferred wallet from the options above.</div>
              </div>
              <div class="pay-step-item">
                <div class="pay-step-circle">2</div>
                <div class="pay-step-text"><strong>Transfer exact amount</strong>Send to the number above and include your order ID in the note.</div>
              </div>
              <div class="pay-step-item">
                <div class="pay-step-circle">3</div>
                <div class="pay-step-text"><strong>Confirm below</strong>Click Confirm Payment after successful transfer.</div>
              </div>
            </div>
          </div>

          <?php elseif ($method === 'cod'): ?>
          <div class="pay-card">
            <div class="section-label">Step 03</div>
            <div class="section-heading">Cash on <em>Delivery</em></div>
            <div class="cod-wrap">
              <div class="cod-icon-wrap">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9f1d2e" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
              </div>
              <p class="cod-text">
                Your order has been placed successfully.<br>
                Our courier will deliver your package and collect payment upon arrival.
              </p>
            </div>
            <div class="divider-soft"></div>
            <div class="pay-steps-list">
              <div class="pay-step-item">
                <div class="pay-step-circle">1</div>
                <div class="pay-step-text"><strong>Order confirmed</strong>We will prepare your package within 1 business day.</div>
              </div>
              <div class="pay-step-item">
                <div class="pay-step-circle">2</div>
                <div class="pay-step-text"><strong>Courier on the way</strong>You will receive a delivery notification via SMS or app.</div>
              </div>
              <div class="pay-step-item">
                <div class="pay-step-circle">3</div>
                <div class="pay-step-text"><strong>Receive and pay</strong>Hand cash to the courier in the exact order total amount.</div>
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>

        <!-- RIGHT: Order Summary Sidebar -->
        <div>
          <div class="order-summary">
            <div class="summary-title">Order Summary</div>

            <div class="order-id-box">
              <div class="order-id-label">Order ID</div>
              <div class="order-id-val"><?= $order_id ?></div>
            </div>

            <div class="method-box">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
              <div>
                <div class="method-box-label">Payment Method</div>
                <div class="method-box-val"><?= $methodLabel ?></div>
              </div>
            </div>

            <p class="pay-note">
              Please complete your payment within <strong>24 hours</strong>. Orders unpaid after this period will be automatically cancelled.
            </p>

            <div class="divider-soft"></div>

            <?php if ($method === 'cod'): ?>
              <a href="order_status.php?order_id=<?= urlencode($order_id) ?>&method=<?= urlencode($method) ?>" class="confirm-btn">Track My Order</a>
            <?php else: ?>
              <a href="order_status.php?order_id=<?= urlencode($order_id) ?>&method=<?= urlencode($method) ?>" class="confirm-btn">Confirm Payment</a>
            <?php endif; ?>

            <div class="secure-note">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Secured and encrypted checkout
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script src="script.js"></script>
</body>
</html>
