
function getCart() {
  return JSON.parse(localStorage.getItem('emblaze_cart')) || [];
}

let cart = getCart();
let shippingCost = 25000;
let discount = 0;


function fmt(n) {
  return 'Rp' + Number(n).toLocaleString('id-ID').replace(/,/g, '.');
}


function renderOrderSummary() {
  const container = document.getElementById('cartItems');
  if (!container) return;
  container.innerHTML = '';
  let subtotal = 0;

  cart.forEach(item => {
    subtotal += item.price * item.qty;
    const div = document.createElement('div');
    div.className = 'summary-item';
    div.innerHTML = `<div>${item.name} x${item.qty}</div><div>${fmt(item.price * item.qty)}</div>`;
    container.appendChild(div);
  });

  updateTotals(subtotal);
}

function updateTotals(subtotal) {
  if (subtotal === undefined) {
    subtotal = 0;
    cart.forEach(item => subtotal += item.price * item.qty);
  }
  const grand = subtotal + shippingCost - discount;
  const el = id => document.getElementById(id);
  if (el('subtotal')) el('subtotal').textContent = fmt(subtotal);
  if (el('shippingCost')) el('shippingCost').textContent = fmt(shippingCost);
  if (el('grandTotal')) el('grandTotal').textContent = fmt(grand);
}


function selectShipping(el, method, cost) {
  document.querySelectorAll('#shippingOptions .payment-option').forEach(function (o) {
    o.classList.remove('selected');
  });
  el.classList.add('selected');
  shippingCost = cost;
  updateTotals();
}


var selectedPayment = 'transfer';
function selectPayment(el, pay) {
  document.querySelectorAll('#paymentOptions .payment-option').forEach(function (o) {
    o.classList.remove('selected');
  });
  el.classList.add('selected');
  selectedPayment = pay;
}



function placeOrder() {
  if (cart.length === 0) {
    alert('Your cart is empty. Please add items before ordering.');
    return;
  }

  var shippingEl = document.querySelector('#shippingOptions .payment-option.selected');
  var shipping_method = shippingEl ? shippingEl.getAttribute('data-method') : 'regular';
  var payment_method = selectedPayment || 'transfer';

  var address = document.querySelector('input[placeholder="Jl. Merdeka No. 12"]');
  address = address ? address.value : '';

  var total = 0;
  cart.forEach(function (item) { total += item.price * item.qty; });
  total += shippingCost - discount;

  var btn = document.querySelector('.place-order-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Processing...'; }

  fetch('order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      action: 'place_order',
      cart_items: JSON.stringify(cart.map(function (item) {
        return { product_id: item.id || null, qty: item.qty, price: item.price };
      })),
      total_price: total,
      shipping_method: shipping_method,
      payment_method: payment_method,
      address: address
    })
  })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success) {
        localStorage.removeItem('emblaze_cart');
        window.location.href = 'payment.php?order_id=' + encodeURIComponent(data.order_id) + '&method=' + encodeURIComponent(data.method || payment_method);
      } else {
        alert('Failed: ' + (data.message || 'Unknown error'));
        if (btn) { btn.disabled = false; btn.textContent = 'Place Order'; }
      }
    })
    .catch(function (err) {
      console.error(err);
      alert('Connection error. Please try again.');
      if (btn) { btn.disabled = false; btn.textContent = 'Place Order'; }
    });
}


function applyPromo() {
  const code = document.getElementById('promoInput')?.value.trim().toUpperCase();
  const promos = { 'EMBLAZE10': 0.10, 'SAVE20': 0.20 };
  let subtotal = 0;
  cart.forEach(i => subtotal += i.price * i.qty);

  if (promos[code]) {
    discount = Math.round(subtotal * promos[code]);
    const row = document.getElementById('discountRow');
    const amt = document.getElementById('discountAmt');
    if (row) row.style.display = 'flex';
    if (amt) amt.textContent = '− ' + fmt(discount);
    updateTotals(subtotal);
  } else {
    alert('Promo code not valid.');
  }
}


renderOrderSummary();