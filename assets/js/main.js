// Mobile nav toggle
(function () {
  const burger = document.querySelector('[data-burger]');
  const nav = document.querySelector('[data-nav]');
  if (!burger || !nav) return;
  burger.addEventListener('click', () => {
    const open = nav.classList.toggle('is-open');
    burger.setAttribute('aria-expanded', String(open));
  });
})();

// Intersection reveal
(function () {
  const els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window) || !els.length) {
    els.forEach((el) => el.classList.add('is-visible'));
    return;
  }
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add('is-visible');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  els.forEach((el) => io.observe(el));
})();

// Sticky header shadow on scroll
(function () {
  const header = document.querySelector('[data-header]');
  if (!header) return;
  const onScroll = () => {
    if (window.scrollY > 6) {
      header.style.boxShadow = '0 4px 14px rgba(2,6,23,0.06)';
    } else {
      header.style.boxShadow = 'none';
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// Set current year in footer
(function () {
  const y = document.getElementById('year');
  if (y) y.textContent = new Date().getFullYear();
})();

// Back-to-top button + subtle hero parallax
(function () {
  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Create back-to-top button
  const btn = document.createElement('button');
  btn.className = 'to-top';
  btn.setAttribute('type', 'button');
  btn.setAttribute('aria-label', 'Revenir en haut de page');
  btn.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 5l-7 7h4v7h6v-7h4z"/></svg>';
  document.body.appendChild(btn);

  const onScroll = () => {
    const show = window.scrollY > 240;
    btn.classList.toggle('is-visible', show);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  btn.addEventListener('click', () => {
    if (prefersReduced) {
      window.scrollTo(0, 0);
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });

  // Subtle parallax on hero media
  if (!prefersReduced) {
    const media = document.querySelector('.media-image');
    if (media) {
      const parallax = () => {
        const y = Math.min(20, window.scrollY * 0.06);
        media.style.transform = `translateY(${y}px)`;
      };
      window.addEventListener('scroll', parallax, { passive: true });
      parallax();
    }
  }
})();
