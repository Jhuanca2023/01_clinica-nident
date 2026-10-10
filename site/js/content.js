(() => {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const root = document.documentElement;
  const loader = document.querySelector('.page-loader');
  let navigating = false;
  // Arrival feedback follows an internal page transition, never an initial slow load.
  let arriving = false;
  try { arriving = sessionStorage.getItem('nident-navigation') === '1'; sessionStorage.removeItem('nident-navigation'); } catch { /* Storage is optional. */ }
  if (arriving && !reduced.matches) {
    root.classList.add('is-arriving');
    setTimeout(() => { root.classList.remove('is-arriving'); root.classList.add('page-enter'); }, 240);
  } else if (!reduced.matches) root.classList.add('page-enter');
  document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank' || link.hasAttribute('download')) return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin || !url.pathname.endsWith('.html') || (url.pathname === location.pathname && url.search === location.search)) return;
    if (reduced.matches) return;
    event.preventDefault();
    if (navigating) return;
    navigating = true;
    root.classList.add('is-leaving');
    loader.setAttribute('aria-hidden', 'false');
    loader.setAttribute('role', 'status');
    try { sessionStorage.setItem('nident-navigation', '1'); } catch { /* Optional. */ }
    setTimeout(() => location.assign(url.href), 260);
  });
  window.addEventListener('pageshow', () => {
    root.classList.remove('is-leaving'); navigating = false;
    loader.setAttribute('aria-hidden', 'true');
  });

  const topButton = document.querySelector('.back-to-top');
  const ring = topButton?.querySelector('.scroll-progress');
  let scrolling = false, returning = false;
  function updateScroll() {
    const maximum = document.documentElement.scrollHeight - innerHeight;
    const progress = maximum > 0 ? Math.min(1, Math.max(0, scrollY / maximum)) : 0;
    ring.style.strokeDashoffset = String(163.36 * (1 - progress));
    topButton.title = `Volver arriba · ${Math.round(progress * 100)} % leído`;
    if (returning && scrollY < 3) { returning = false; topButton.classList.remove('returning'); document.querySelector('.n-nav a')?.focus({ preventScroll: true }); }
    topButton.hidden = scrollY < 400 && !returning;
    scrolling = false;
  }
  if (topButton) {
    window.addEventListener('scroll', () => { if (!scrolling) { scrolling = true; requestAnimationFrame(updateScroll); } }, { passive: true });
    window.addEventListener('resize', updateScroll);
    topButton.addEventListener('click', () => {
      returning = true; topButton.classList.add('returning');
      window.scrollTo({ top: 0, behavior: reduced.matches ? 'instant' : 'smooth' });
      setTimeout(() => { returning = false; topButton.classList.remove('returning'); updateScroll(); }, 1600);
    });
    updateScroll();
  }

  const testimonials = [...document.querySelectorAll('.testimonial-slide')];
  if (testimonials.length) {
    let selected = 0;
    function show(index) {
      const next = (index + testimonials.length) % testimonials.length;
      if (next === selected) return;
      const previous = selected; selected = next;
      testimonials[previous].classList.remove('active');
      testimonials[previous].setAttribute('aria-hidden', 'true');
      testimonials[next].hidden = false;
      testimonials[next].setAttribute('aria-hidden', 'false');
      requestAnimationFrame(() => testimonials[next].classList.add('active'));
      setTimeout(() => { if (selected !== previous) testimonials[previous].hidden = true; }, reduced.matches ? 0 : 230);
      document.querySelectorAll('[data-testimonial]').forEach((dot, i) => dot.setAttribute('aria-pressed', String(i === next)));
    }
    document.querySelector('[data-testimonial-prev]').addEventListener('click', () => show(selected - 1));
    document.querySelector('[data-testimonial-next]').addEventListener('click', () => show(selected + 1));
    document.querySelectorAll('[data-testimonial]').forEach((dot, i) => dot.addEventListener('click', () => show(i)));
  }

  const paths = {
    Instagram: '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".7" fill="currentColor"/>',
    Facebook: '<path d="M14 21V12h3l.5-4H14V6c0-1 .5-2 2-2h2V1h-3c-3 0-5 2-5 5v2H7v4h3v9"/>',
    YouTube: '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 6 3-6 3z"/>',
    TikTok: '<path d="M14 3v12a4 4 0 1 1-4-4M14 3c0 4 3 6 6 6"/>'
  };
  document.querySelectorAll('.social-icons a, .community-links a').forEach(link => {
    const name = link.textContent.trim();
    if (!paths[name]) return;
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', 'brand-icon'); svg.setAttribute('viewBox', '0 0 24 24'); svg.setAttribute('fill', 'none'); svg.setAttribute('stroke', 'currentColor'); svg.setAttribute('stroke-width', '1.5'); svg.setAttribute('aria-hidden', 'true');
    svg.innerHTML = paths[name]; link.prepend(svg);
  });
})();
