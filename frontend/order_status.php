<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$order_id = htmlspecialchars($_GET['order_id'] ?? 'EMB-00000');
$method   = htmlspecialchars($_GET['method'] ?? 'transfer');

$methodLabels = [
    'transfer' => 'Bank Transfer',
    'qris'     => 'QRIS',
    'ewallet'  => 'E-Wallet',
    'cod'      => 'Cash on Delivery',
];
$methodLabel = $methodLabels[$method] ?? ucfirst($method);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Status | Emblaze</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .status-page { padding: 60px 0 100px; }

    .status-hero { text-align: center; margin-bottom: 48px; }
    .status-heading { font-family: 'Cormorant Garamond', serif; font-size: clamp(32px, 4vw, 50px); font-weight: 300; color: var(--text-dark); margin-bottom: 10px; }
    .status-heading em { font-style: italic; color: #9f1d2e; }
    .order-id-chip { display: inline-block; background: rgba(159,29,46,0.07); border: 1px solid rgba(159,29,46,0.18); color: #7c1f2d; font-size: 13px; letter-spacing: 0.18em; padding: 8px 24px; border-radius: 999px; margin-top: 8px; }

    .status-card { max-width: 580px; margin: 0 auto 32px; border-radius: 28px; padding: 40px; background: rgba(255,255,255,0.88); backdrop-filter: blur(20px); border: 1px solid rgba(231,200,191,0.4); box-shadow: 0 8px 32px rgba(204,177,170,0.14); text-align: center; }


    .status-icon-wrap { width: 88px; height: 88px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
    .status-icon-wrap.pending  { background: linear-gradient(135deg, #fef3c7, #fde68a); animation: pulse 2s ease-in-out infinite; }
    .status-icon-wrap.processing,
    .status-icon-wrap.confirmed { background: linear-gradient(135deg, #d1fae5, #a7f3d0); }
    .status-icon-wrap.shipped   { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
    .status-icon-wrap.delivered { background: linear-gradient(135deg, #9f1d2e, #c4455b); }
    .status-icon-wrap.cancelled { background: linear-gradient(135deg, #fee2e2, #fecaca); }

    @keyframes pulse {
      0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(253,211,77,0.4); }
      50% { transform: scale(1.04); box-shadow: 0 0 0 16px rgba(253,211,77,0); }
    }

    .status-title { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 300; color: var(--text-dark); margin-bottom: 10px; }
    .status-title em { font-style: italic; color: #9f1d2e; }
    .status-desc { font-size: 14px; color: var(--text-muted); line-height: 1.7; margin-bottom: 24px; }
    .status-badge { display: inline-block; padding: 6px 20px; border-radius: 999px; font-size: 12px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 24px; }
    .badge-pending    { background: rgba(253,211,77,0.15); color: #b45309; border: 1px solid rgba(253,211,77,0.4); }
    .badge-processing,
    .badge-confirmed  { background: rgba(52,211,153,0.12); color: #065f46; border: 1px solid rgba(52,211,153,0.3); }
    .badge-shipped    { background: rgba(59,130,246,0.12); color: #1e40af; border: 1px solid rgba(59,130,246,0.3); }
    .badge-delivered  { background: rgba(159,29,46,0.08); color: #7c1f2d; border: 1px solid rgba(159,29,46,0.2); }
    .badge-cancelled  { background: rgba(239,68,68,0.1); color: #b91c1c; border: 1px solid rgba(239,68,68,0.25); }

 
    .progress-track { display: flex; align-items: flex-start; justify-content: center; gap: 0; margin: 32px 0; }
    .prog-step { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; max-width: 90px; }
    .prog-dot { width: 34px; height: 34px; border-radius: 50%; border: 2px solid #e7c8bf; background: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #c4a99a; transition: all 0.4s; position: relative; z-index: 1; }
    .prog-dot.done  { background: linear-gradient(135deg, #9f1d2e, #c4455b); border-color: #c4455b; color: white; }
    .prog-dot.active { background: linear-gradient(135deg, #fbbf24, #f59e0b); border-color: #f59e0b; color: white; }
    .prog-label { font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); text-align: center; line-height: 1.3; }
    .prog-label.done, .prog-label.active { color: var(--text-dark); font-weight: 500; }
    .prog-line { flex: 1; height: 2px; background: #e7c8bf; margin-top: 17px; transition: background 0.4s; max-width: 60px; }
    .prog-line.done { background: linear-gradient(to right, #c4455b, #9f1d2e); }

  
    .order-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; text-align: left; margin-top: 24px; }
    .info-item { background: rgba(252,244,242,0.8); border-radius: 14px; padding: 14px 16px; border: 1px solid rgba(231,200,191,0.3); }
    .info-label { font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 5px; }
    .info-val { font-size: 14px; font-weight: 500; color: var(--text-dark); }


    .polling-note { font-size: 12px; color: var(--text-muted); margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .dot-pulse { width: 7px; height: 7px; border-radius: 50%; background: #9f1d2e; animation: dotpulse 1.3s ease-in-out infinite; }
    @keyframes dotpulse { 0%, 100% { opacity: 0.2; } 50% { opacity: 1; } }

    /* Action buttons */
    .status-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 28px; }
    .btn-main { padding: 14px 32px; border: none; border-radius: 999px; background: linear-gradient(135deg, #9f1d2e, #c4455b); color: white; font-family: 'Jost', sans-serif; font-size: 14px; font-weight: 500; letter-spacing: 0.06em; cursor: pointer; box-shadow: 0 10px 28px rgba(159,29,46,0.26); text-decoration: none; transition: transform 0.25s, box-shadow 0.25s; display: inline-flex; align-items: center; }
    .btn-main:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(159,29,46,0.32); }
    .btn-ghost { padding: 14px 32px; border: 1px solid #e7c8bf; border-radius: 999px; background: transparent; font-family: 'Jost', sans-serif; font-size: 14px; color: var(--text-soft); cursor: pointer; text-decoration: none; transition: background 0.2s; display: inline-flex; align-items: center; }
    .btn-ghost:hover { background: white; }

    .breadcrumb { font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 36px; }
    .breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .breadcrumb span { color: #7c1f2d; }
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
        <a href="products.php">Products</a>
        <a href="profile.php">My Orders</a>
      </nav>
      <div style="display:flex; align-items:center; gap:12px;">
        <a href="profile.php" class="login-icon" title="Profile">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </a>
        <button class="menu-toggle" id="menuToggle">&#9776;</button>
      </div>
    </div>
  </header>

  <main class="status-page">
    <div class="container">

      <div class="breadcrumb">
        <a href="index.php">Home</a> &rsaquo;
        <a href="products.php">Products</a> &rsaquo;
        <a href="cart.php">Cart</a> &rsaquo;
        <span>Order Status</span>
      </div>

      <div class="status-hero">
        <h1 class="status-heading">Order <em>Status</em></h1>
        <div class="order-id-chip"><?= $order_id ?></div>
      </div>

      <!-- Status Card (updated by JS) -->
      <div class="status-card" id="statusCard">

        <!-- Icon -->
        <div class="status-icon-wrap pending" id="statusIcon">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.8" id="statusIconSvg">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>

        <div class="status-badge badge-pending" id="statusBadge">Awaiting Confirmation</div>
        <div class="status-title" id="statusTitle">Payment <em>Submitted</em></div>
        <div class="status-desc" id="statusDesc">
          Your payment has been submitted. Our team is reviewing and will confirm your order shortly. This page will update automatically.
        </div>

        <!-- Progress Track -->
        <div class="progress-track">
          <div class="prog-step">
            <div class="prog-dot done" id="p1">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="prog-label done">Ordered</div>
          </div>
          <div class="prog-line" id="l1"></div>
          <div class="prog-step">
            <div class="prog-dot active" id="p2">2</div>
            <div class="prog-label active" id="pl2">Confirming</div>
          </div>
          <div class="prog-line" id="l2"></div>
          <div class="prog-step">
            <div class="prog-dot" id="p3">3</div>
            <div class="prog-label" id="pl3">Processing</div>
          </div>
          <div class="prog-line" id="l3"></div>
          <div class="prog-step">
            <div class="prog-dot" id="p4">4</div>
            <div class="prog-label" id="pl4">Shipped</div>
          </div>
          <div class="prog-line" id="l4"></div>
          <div class="prog-step">
            <div class="prog-dot" id="p5">5</div>
            <div class="prog-label" id="pl5">Delivered</div>
          </div>
        </div>

        <!-- Order Info -->
        <div class="order-info-grid">
          <div class="info-item">
            <div class="info-label">Payment Method</div>
            <div class="info-val" id="infoPayment"><?= $methodLabel ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Order Date</div>
            <div class="info-val" id="infoDate">Loading...</div>
          </div>
          <div class="info-item">
            <div class="info-label">Shipping</div>
            <div class="info-val" id="infoShipping">Loading...</div>
          </div>
          <div class="info-item">
            <div class="info-label">Total Amount</div>
            <div class="info-val" id="infoTotal">Loading...</div>
          </div>
        </div>

        <div class="polling-note" id="pollingNote">
          <span class="dot-pulse"></span>
          Checking for updates every 10 seconds
        </div>

        <div class="status-actions" id="statusActions">
          <a href="profile.php" class="btn-ghost">View My Orders</a>
          <a href="products.php" class="btn-main">Continue Shopping</a>
        </div>

      </div>

    </div>
  </main>

  <script>
    var orderId   = '<?= $order_id ?>';
    var pollTimer = null;

    var statusConfig = {
      pending: {
        iconClass: 'pending',
        iconColor: '#92400e',
        iconSvg: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        badge: 'Awaiting Confirmation', badgeClass: 'badge-pending',
        title: 'Payment <em>Submitted</em>',
        desc: 'Your payment has been submitted. Our team is reviewing and will confirm your order shortly. This page updates automatically.',
        steps: [1, 0, 0, 0, 0], polling: true
      },
      processing: {
        iconClass: 'processing',
        iconColor: '#065f46',
        iconSvg: '<polyline points="20 6 9 17 4 12"/>',
        badge: 'Payment Confirmed', badgeClass: 'badge-processing',
        title: 'Order <em>Confirmed</em>',
        desc: 'Great news! Admin has confirmed your payment. Your order is now being prepared and packed with care.',
        steps: [2, 2, 0, 0, 0], polling: false
      },
      confirmed: {
        iconClass: 'confirmed',
        iconColor: '#065f46',
        iconSvg: '<polyline points="20 6 9 17 4 12"/>',
        badge: 'Payment Confirmed', badgeClass: 'badge-confirmed',
        title: 'Order <em>Confirmed</em>',
        desc: 'Great news! Admin has confirmed your payment. Your order is now being prepared and packed with care.',
        steps: [2, 2, 0, 0, 0], polling: false
      },
      shipped: {
        iconClass: 'shipped',
        iconColor: '#1e40af',
        iconSvg: '<path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        badge: 'On The Way', badgeClass: 'badge-shipped',
        title: 'Order <em>Shipped</em>',
        desc: 'Your order is on its way! The courier is delivering your package. Expected to arrive soon.',
        steps: [2, 2, 2, 0, 0], polling: false
      },
      delivered: {
        iconClass: 'delivered',
        iconColor: 'white',
        iconSvg: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        badge: 'Delivered', badgeClass: 'badge-delivered',
        title: 'Order <em>Delivered</em>',
        desc: 'Your order has been delivered. Thank you for shopping with Emblaze. We hope you love your purchase!',
        steps: [2, 2, 2, 2, 2], polling: false
      },
      cancelled: {
        iconClass: 'cancelled',
        iconColor: '#b91c1c',
        iconSvg: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
        badge: 'Cancelled', badgeClass: 'badge-cancelled',
        title: 'Order <em>Cancelled</em>',
        desc: 'This order has been cancelled. If you have questions, please contact our support team.',
        steps: [2, 0, 0, 0, 0], polling: false
      }
    };

    function updateUI(cfg, data) {
      // Icon
      var iconWrap = document.getElementById('statusIcon');
      iconWrap.className = 'status-icon-wrap ' + cfg.iconClass;
      document.getElementById('statusIconSvg').setAttribute('stroke', cfg.iconColor);
      document.getElementById('statusIconSvg').innerHTML = cfg.iconSvg;

      // Badge & texts
      var badge = document.getElementById('statusBadge');
      badge.textContent = cfg.badge;
      badge.className = 'status-badge ' + cfg.badgeClass;
      document.getElementById('statusTitle').innerHTML = cfg.title;
      document.getElementById('statusDesc').textContent = cfg.desc;

      // Progress dots: 0=upcoming 1=active 2=done
      var dots  = ['p1','p2','p3','p4','p5'];
      var lines = ['l1','l2','l3','l4'];
      var labels= ['pl2','pl3','pl4','pl5'];
      var labelTexts = ['Confirming','Processing','Shipped','Delivered'];

      for (var i = 0; i < 5; i++) {
        var el = document.getElementById(dots[i]);
        el.className = 'prog-dot';
        var s = cfg.steps[i];
        if (s === 2) { el.classList.add('done'); el.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'; }
        else if (s === 1) { el.classList.add('active'); el.textContent = i + 1; }
        else { el.textContent = i + 1; }
      }
      for (var j = 0; j < 4; j++) {
        var line = document.getElementById(lines[j]);
        line.className = 'prog-line' + (cfg.steps[j] === 2 ? ' done' : '');
        var lbl = document.getElementById(labels[j]);
        lbl.className = 'prog-label' + (cfg.steps[j+1] === 2 || cfg.steps[j+1] === 1 ? ' done' : '');
        lbl.textContent = labelTexts[j];
      }

      // Info
      if (data) {
        document.getElementById('infoPayment').textContent = data.payment_method ? data.payment_method.charAt(0).toUpperCase() + data.payment_method.slice(1) : '—';
        document.getElementById('infoDate').textContent    = data.order_date    || '—';
        document.getElementById('infoShipping').textContent = data.shipping_method ? data.shipping_method.charAt(0).toUpperCase() + data.shipping_method.slice(1) : '—';
        document.getElementById('infoTotal').textContent   = data.total_price   || '—';
      }

      document.getElementById('pollingNote').style.display = cfg.polling ? 'flex' : 'none';
    }

    function checkStatus() {
      fetch('order_status_api.php?order_id=' + encodeURIComponent(orderId))
        .then(function(r) { return r.json(); })
        .then(function(data) {
          var cfg = statusConfig[data.status] || statusConfig['pending'];
          updateUI(cfg, data);
          if (cfg.polling) {
            pollTimer = setTimeout(checkStatus, 10000);
          }
        })
        .catch(function() {
        
          pollTimer = setTimeout(checkStatus, 15000);
        });
    }

    // Initial load
    checkStatus();
  </script>
  <script src="script.js"></script>
</body>
</html>
