    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    menuToggle.addEventListener('click', () => mobileMenu.classList.toggle('show'));
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', () => mobileMenu.classList.remove('show'));
    });

    const cards = Array.from(document.querySelectorAll('.product-card'));
    const chips = document.querySelectorAll('.chip');
    const sortSelect = document.getElementById('sortSelect');
    const productCount = document.getElementById('productCount');
    const emptyState = document.getElementById('emptyState');
    const grid = document.getElementById('productGrid');

    let currentFilter = 'all';

    setTimeout(() => {
      cards.forEach((card, i) => {
        setTimeout(() => card.classList.add('visible'), i * 60);
      });
    }, 100);

    function updateCount() {
      const visible = cards.filter(c => !c.classList.contains('hidden')).length;
      productCount.textContent = `Showing ${visible} product${visible !== 1 ? 's' : ''}`;
      emptyState.style.display = visible === 0 ? 'block' : 'none';
    }

    function applyFilter(filter) {
      currentFilter = filter;
      cards.forEach(card => {
        const cat = card.dataset.category;
        const match = filter === 'all' || cat === filter;
        card.classList.toggle('hidden', !match);
      });
      updateCount();
    }

    function applySort(value) {
      const sorted = [...cards].filter(c => !c.classList.contains('hidden'));
      const all = [...cards];

      all.forEach(c => c.remove());

      let toSort = cards.filter(c => {
        const cat = currentFilter === 'all' ? true : c.dataset.category === currentFilter;
        return cat;
      });

      let rest = cards.filter(c => currentFilter !== 'all' && c.dataset.category !== currentFilter);

      if (value === 'price-asc') {
        toSort.sort((a, b) => +a.dataset.price - +b.dataset.price);
      } else if (value === 'price-desc') {
        toSort.sort((a, b) => +b.dataset.price - +a.dataset.price);
      } else if (value === 'name') {
        toSort.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
      }

      [...toSort, ...rest].forEach(c => grid.insertBefore(c, emptyState));
      updateCount();
    }

    chips.forEach(chip => {
      chip.addEventListener('click', () => {
        chips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        applyFilter(chip.dataset.filter);
        applySort(sortSelect.value);
      });
    });

    sortSelect.addEventListener('change', () => applySort(sortSelect.value));

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.1 });

    cards.forEach(card => observer.observe(card));

    updateCount();