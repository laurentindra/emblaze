    const SHIPPING = 25000;
    let discount = 0;

    let cart = [
      { id: 1, name: 'Maroon Satin Wrap Romper', cat: 'Women / Essentials', price: 680000, qty: 1, badge: 'Bestseller', img: 'https://plus.unsplash.com/premium_photo-1675186049409-f9f8f60ebb5e?q=80&w=687&auto=format&fit=crop' },
      { id: 2, name: 'Aurelia Sun Medallion Set', cat: 'Jewelry / Signature', price: 450000, qty: 1, badge: '', img: 'https://images.unsplash.com/photo-1585960622850-ed33c41d6418?q=80&w=1170&auto=format&fit=crop' },
      { id: 3, name: 'High-waisted A-line Skirt', cat: 'Skirt / Limited', price: 659000, qty: 1, badge: 'Limited', img: 'https://images.unsplash.com/photo-1646054224885-f978f5798312?q=80&w=687&auto=format&fit=crop' },
    ];

    const alsoItems = [
      { name: 'Classic Heeled Ankle Boots', price: 745000, img: 'https://images.unsplash.com/photo-1610398752800-146f269dfcc8?q=80&w=687&auto=format&fit=crop' },
      { name: 'Soft Draped Midi Dress', price: 720000, img: 'https://plus.unsplash.com/premium_photo-1668485968642-30e3d15e9b9c?q=80&w=687&auto=format&fit=crop' },
      { name: 'Pearl Layered Pendant', price: 385000, img: 'https://images.unsplash.com/photo-1599071652104-99cc014ad576?auto=format&fit=crop&w=900&q=80' },
      { name: 'Pleated Wrap Midi Skirt', price: 540000, img: 'https://images.unsplash.com/photo-1567480384-f7503159ef0f?auto=format&fit=crop&w=900&q=80' },
    ];

    function fmt(n) {
      return 'Rp' + n.toLocaleString('id-ID').replace(/,/g, '.');
    }

    function renderCart() {
      const list = document.getElementById('cartList');
      list.innerHTML = '';

      cart.forEach(function(item, idx) {
        const row = document.createElement('div');
        row.className = 'cart-row glass';
        row.innerHTML =
          '<div class="cart-product">' +
            '<div class="cart-img"><img src="' + item.img + '" alt="' + item.name + '" /></div>' +
            '<div>' +
              '<div class="cart-name">' + item.name + '</div>' +
              '<div class="cart-cat">' + item.cat + '</div>' +
              (item.badge ? '<span class="cart-badge">' + item.badge + '</span>' : '') +
            '</div>' +
          '</div>' +
          '<div class="cart-unit-price">' + fmt(item.price) + '</div>' +
          '<div class="cart-qty">' +
            '<button class="qty-btn" id="minus-' + idx + '">&#8722;</button>' +
            '<span class="qty-num" id="qty-' + idx + '">' + item.qty + '</span>' +
            '<button class="qty-btn" id="plus-' + idx + '">&#43;</button>' +
          '</div>' +
          '<div class="cart-total-price" id="rowtotal-' + idx + '">' + fmt(item.price * item.qty) + '</div>' +
          '<button class="remove-btn" id="remove-' + idx + '">&#215;</button>';

        list.appendChild(row);

        document.getElementById('minus-' + idx).onclick = function() {
          if (cart[idx].qty > 1) { cart[idx].qty--; renderCart(); }
        };
        document.getElementById('plus-' + idx).onclick = function() {
          cart[idx].qty++;
          renderCart();
        };
        document.getElementById('remove-' + idx).onclick = function() {
          cart.splice(idx, 1);
          renderCart();
        };
      });

      updateTotals();
    }

    function updateTotals() {
      let subtotal = 0, count = 0;
      cart.forEach(function(i) { subtotal += i.price * i.qty; count += i.qty; });

      document.getElementById('subtotal').textContent = fmt(subtotal);
      document.getElementById('grandTotal').textContent = fmt(subtotal + SHIPPING - discount);
      document.getElementById('itemCount').textContent = count;

      const empty = cart.length === 0;
      document.getElementById('emptyCart').style.display = empty ? 'block' : 'none';
      document.getElementById('cartFooter').style.display = empty ? 'none' : 'flex';
      document.getElementById('checkoutBtn').className = 'checkout-btn' + (empty ? ' disabled' : '');
      document.getElementById('cartSubhead').textContent = empty ? 'Your cart is empty' : cart.length + ' item' + (cart.length !== 1 ? 's' : '') + ' selected';
    }

    document.getElementById('clearBtn').onclick = function() {
      cart = [];
      renderCart();
    };

    document.getElementById('promoBtn').onclick = function() {
      const code = document.getElementById('promoInput').value.trim().toUpperCase();
      const msg = document.getElementById('promoMessage');
      const row = document.getElementById('discountRow');
      if (code === 'EMBLAZE10') {
        discount = 50000;
        row.style.display = 'flex';
        document.getElementById('discountAmt').textContent = '− ' + fmt(discount);
        msg.textContent = '🎉 Promo applied! You save Rp50.000';
        msg.className = 'promo-message success';
      } else {
        discount = 0;
        row.style.display = 'none';
        msg.textContent = 'Invalid code. Try EMBLAZE10.';
        msg.className = 'promo-message error';
      }
      updateTotals();
    };

    function renderAlso() {
      const grid = document.getElementById('alsoGrid');
      grid.innerHTML = '';
      alsoItems.forEach(function(item, idx) {
        const card = document.createElement('div');
        card.className = 'also-card glass';
        card.innerHTML =
          '<div class="also-img"><img src="' + item.img + '" alt="' + item.name + '" /></div>' +
          '<div class="also-body">' +
            '<div class="also-name">' + item.name + '</div>' +
            '<div class="also-price">' + fmt(item.price) + '</div>' +
            '<button class="also-add" id="also-' + idx + '">Add to Cart</button>' +
          '</div>';
        grid.appendChild(card);

        document.getElementById('also-' + idx).onclick = function() {
          const existing = cart.find(function(i) { return i.name === item.name; });
          if (existing) {
            existing.qty++;
          } else {
            cart.push({ id: Date.now(), name: item.name, cat: 'New Addition', price: item.price, qty: 1, badge: '', img: item.img });
          }
          renderCart();
          const btn = document.getElementById('also-' + idx);
          if (btn) {
            btn.textContent = '✓ Added';
            btn.style.background = '#7c1f2d';
            btn.style.color = 'white';
            setTimeout(function() { renderAlso(); }, 1500);
          }
        };
      });
    }

    document.getElementById('menuToggle').onclick = function() {
      document.getElementById('mobileMenu').classList.toggle('show');
    };

    renderCart();
    renderAlso();