const SHIPPING = 25000;
let discount = 0;


function getCart() {
  try { return JSON.parse(localStorage.getItem('emblaze_cart')) || []; }
  catch { return []; }
}

function saveCart(cart) {
  localStorage.setItem('emblaze_cart', JSON.stringify(cart));
}

let cart = getCart();


function fmt(n) {
  return 'Rp' + n.toLocaleString('id-ID').replace(/,/g, '.');
}


function renderCart() {
  const list = document.getElementById('cartList');
  if (!list) return;
  list.innerHTML = '';

  cart.forEach(function(item, idx) {
    const row = document.createElement('div');
    row.className = 'cart-row glass';

    row.innerHTML =
      `<div class="cart-product">
        <div class="cart-img"><img src="${item.img}" /></div>
        <div>
          <div class="cart-name">${item.name}</div>
          <div class="cart-cat">${item.cat || 'Collection'}</div>
        </div>
      </div>
      <div class="cart-unit-price">${fmt(item.price)}</div>
      <div class="cart-qty">
        <button class="qty-btn" id="minus-${idx}">−</button>
        <span class="qty-num">${item.qty}</span>
        <button class="qty-btn" id="plus-${idx}">+</button>
      </div>
      <div class="cart-total-price">${fmt(item.price * item.qty)}</div>
      <button class="remove-btn" id="remove-${idx}">×</button>`;

    list.appendChild(row);

    
    document.getElementById(`minus-${idx}`).onclick = () => {
      if (cart[idx].qty > 1) {
        cart[idx].qty--;
        saveCart(cart);
        renderCart();
      }
    };

    // plus
    document.getElementById(`plus-${idx}`).onclick = () => {
      cart[idx].qty++;
      saveCart(cart);
      renderCart();
    };

    // remove
    document.getElementById(`remove-${idx}`).onclick = () => {
      cart.splice(idx, 1);
      saveCart(cart);
      renderCart();
    };
  });

  updateTotals();
}


function updateTotals() {
  let subtotal = 0;
  let count = 0;

  cart.forEach(i => {
    subtotal += i.price * i.qty;
    count += i.qty;
  });

  const grandTotal = subtotal + (cart.length ? SHIPPING : 0) - discount;

  if (document.getElementById('subtotal'))
    document.getElementById('subtotal').textContent = fmt(subtotal);

  if (document.getElementById('grandTotal'))
    document.getElementById('grandTotal').textContent = fmt(grandTotal);

  if (document.getElementById('itemCount'))
    document.getElementById('itemCount').textContent = count;

  const empty = cart.length === 0;

  if (document.getElementById('emptyCart'))
    document.getElementById('emptyCart').style.display = empty ? 'block' : 'none';

  if (document.getElementById('cartFooter'))
    document.getElementById('cartFooter').style.display = empty ? 'none' : 'flex';

  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.style.display = empty ? 'none' : 'inline-block';
  }
}

// ================= CLEAR CART =================
const clearBtn = document.getElementById('clearBtn');
if (clearBtn) {
  clearBtn.onclick = function () {
    cart = [];
    saveCart(cart);
    renderCart();
  };
}

// ================= CHECKOUT =================
// 👉 INI YANG PALING PENTING
// sekarang cuma redirect ke order.php (TIDAK kirim database di sini)

const checkoutBtn = document.getElementById('checkoutBtn');
if (checkoutBtn) {
  checkoutBtn.addEventListener('click', function () {
    window.location.href = 'order.php';
  });
}

// ================= INIT =================
renderCart();

// ================= ADD FROM RECOMMENDATIONS =================
function addToCartFromRec(id, name, price, cat, img) {
  const existing = cart.find(i => i.id === id);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ id, name, price: Number(price), cat, img, qty: 1 });
  }
  saveCart(cart);
  renderCart();

  // Visual feedback on button
  const btns = document.querySelectorAll('.also-add');
  btns.forEach(btn => {
    if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(String(id))) {
      const orig = btn.textContent;
      btn.textContent = 'Added ✓';
      btn.style.background = '#7c1f2d';
      btn.style.color = 'white';
      setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.style.color = ''; }, 1400);
    }
  });
}