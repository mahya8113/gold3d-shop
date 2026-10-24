import './hero-3d.js';
import './three-viewer.js';

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

// Luxury micro-interactions: subtle, fast and disabled for reduced-motion users.
const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
if (!reducedMotion) {
  const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: .08 });
  $$('.product-card, .category-tile, .market-main-card, .market-mini, .market-chart-card, .atelier-panel, .architecture-card').forEach(el => {
    el.classList.add('reveal-on-scroll');
    revealObserver.observe(el);
  });
}

// Global confirm handling.
$$('[data-confirm]').forEach(el => el.addEventListener('click', event => {
  if (!confirm(el.dataset.confirm)) event.preventDefault();
}));

// Keyboard-first search: Ctrl/Cmd + K.
const searchInput = $('.header-search input');
document.addEventListener('keydown', event => {
  if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    searchInput?.focus();
    searchInput?.select();
  }
  if (event.key === 'Escape' && document.activeElement === searchInput) searchInput.blur();
});

// Automatically dismiss server toasts, while respecting assistive technologies.
setTimeout(() => {
  $$('.toast').forEach(el => {
    el.classList.add('toast-out');
    setTimeout(() => el.remove(), 450);
  });
}, 5200);

// Lightweight client-side wishlist feedback for future AJAX endpoints.
document.addEventListener('click', event => {
  const button = event.target.closest('[data-heart]');
  if (!button) return;
  button.classList.toggle('is-liked');
  button.textContent = button.classList.contains('is-liked') ? '♥' : '♡';
});

// Smart image loading and graceful failure state for remote real-product photography.
$$('img').forEach(img => {
  if (!img.hasAttribute('loading') && !img.closest('.hero-ultra-stage')) img.loading = 'lazy';
  img.addEventListener('error', () => img.classList.add('image-fallback'), { once: true });
});

// A tiny performance telemetry hook for the health/debug panel.
window.gold3dRuntime = {
  startedAt: performance.now(),
  api: '/api/health',
  catalogApi: '/api/catalog',
};
