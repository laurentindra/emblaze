const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');

menuToggle.addEventListener('click', () => {
  mobileMenu.classList.toggle('show');
});

document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', () => mobileMenu.classList.remove('show'));
});

const chips = document.querySelectorAll('.chip');
const products = document.querySelectorAll('.product-card');

chips.forEach(chip => {
  chip.addEventListener('click', () => {
    chips.forEach(c => c.classList.remove('active'));
    chip.classList.add('active');

    const filter = chip.dataset.filter;
    products.forEach(product => {
      const categories = product.dataset.category;
      const visible = filter === 'all' || categories.includes(filter);
      product.style.display = visible ? 'block' : 'none';
    });
  });
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('show');
    }
  });
}, { threshold: 0.12 });
 
document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

const subscribeForm = document.getElementById('subscribeForm');
const emailInput = document.getElementById('emailInput');

subscribeForm.addEventListener('submit', function(e) {
  e.preventDefault();
  const email = emailInput.value.trim();
  if (!email) return;
  alert('Terima kasih. ' + email + ' sudah terdaftar di Emblaze circle.');
  emailInput.value = '';
});

const filterButtons = document.querySelectorAll('.chip');
const categories = document.querySelectorAll('.category-card');

filterButtons.forEach(button => {
  button.addEventListener('click', () => {
    filterButtons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    const filter = button.dataset.filter;

    categories.forEach(category => {
      const categoryType = category.dataset.category;
      const isVisible = filter === 'all' || categoryType === filter;
      category.style.display = isVisible ? 'block' : 'none';
    });
  });
});

/* --- KONEKSI DATABASE EMBLAZE (TAMBAHAN GEMINI) --- */
async function sendOrderToDatabase(orderData) {
  try {
    const response = await fetch('http://192.168.1.15:3000/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(orderData)
    });
    const result = await response.json();
    if (response.ok) {
      alert('Data belanjaan berhasil masuk ke Database!');
    } else {
      alert('Gagal simpan: ' + result.message);
    }
  } catch (error) {
    console.error('Server mati, Cher!');
  }
}

const checkoutBtn = document.getElementById('checkoutBtn');
if (checkoutBtn) {
  checkoutBtn.addEventListener('click', () => {
    const dataBelanja = {
      user_id: 1, 
      total_price: 150000,
      cart_items: [
        { product_id: 1, quantity: 1, price_at_purchase: 150000 }
      ]
    };
    sendOrderToDatabase(dataBelanja);
  });
}    