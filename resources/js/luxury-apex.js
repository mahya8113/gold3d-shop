(() => {
  const root = document.documentElement;
  let raf = 0;
  window.addEventListener('pointermove', e => {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(() => {
      root.style.setProperty('--mx', `${e.clientX}px`);
      root.style.setProperty('--my', `${e.clientY}px`);
    });
  }, {passive:true});
})();
