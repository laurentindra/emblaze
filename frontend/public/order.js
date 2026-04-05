document.addEventListener('DOMContentLoaded', function () {

  // Mobile menu
  document.getElementById('menuToggle').addEventListener('click', () => {
    document.getElementById('mobileMenu').classList.toggle('show');
  });

  // Payment & shipping option selection
  document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function () {
      const group = this.closest('.payment-options');
      group.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
      this.classList.add('selected');

      const pay = this.dataset.pay;
      const cardFields = document.getElementById('cardFields');
      if (cardFields) {
        cardFields.classList.toggle('show', pay === 'card');
      }

      const method = this.dataset.method;
      if (method) {
        const costs = { regular: 25000, express: 55000, same: 85000 };
        shippingCost = costs[method];
        updateTotals();
      }
    });
  });

  // Card number formatting
  const cardNumber = document.getElementById('cardNumber');
  if (cardNumber) {
    cardNumber.addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').substring(0, 16);
      this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });
  }

  // Qty buttons — use event delegation
  document.getElementById('cartItems').addEventListener('click', function (e) {
    const btn = e.target.closest('.qty-btn');
    if (!btn) return;
    const delta = btn.textContent.trim() === '+' ? 1 : -1;
    const qtyEl = btn.parentElement.querySelector('.qty-num');
    let qty = parseInt(qtyEl.textContent) + delta;
    if (qty < 1) qty = 1;
    qtyEl.textContent = qty;
    updateTotals();
  });

  // Promo button
  document.querySelector('.promo-btn').addEventListener('click', applyPromo);

  // Place order button
  document.querySelector('.place-order-btn').addEventListener('click', placeOrder);

  // Init
  updateTotals();
});

let shippingCost = 25000;
let discount = 0;

function formatRp(n) {
  return 'Rp' + n.toLocaleString('id-ID').replace(/,/g, '.');
}

function updateTotals() {
  let subtotal = 0;
  document.querySelectorAll('.cart-item').forEach(item => {
    const price = parseInt(item.querySelector('.cart-item-price').dataset.price);
    const qty = parseInt(item.querySelector('.qty-num').textContent);
    subtotal += price * qty;
  });

  document.getElementById('subtotal').textContent = formatRp(subtotal);
  document.getElementById('shippingCost').textContent = formatRp(shippingCost);
  document.getElementById('grandTotal').textContent = formatRp(subtotal + shippingCost - discount);
}

function applyPromo() {
  const code = document.getElementById('promoInput').value.trim().toUpperCase();
  const discountRow = document.getElementById('discountRow');
  const discountAmt = document.getElementById('discountAmt');

  if (code === 'EMBLAZE10') {
    discount = 50000;
    discountRow.style.display = 'flex';
    discountAmt.textContent = '− ' + formatRp(discount);
    updateTotals();
  } else {
    discount = 0;
    discountRow.style.display = 'none';
    alert('Promo code not valid.');
  }
}

function placeOrder() {
  const orderId = 'EMB-' + Math.floor(10000 + Math.random() * 90000);
  document.getElementById('orderId').textContent = 'Order #' + orderId;
  document.getElementById('successOverlay').classList.add('show');
}