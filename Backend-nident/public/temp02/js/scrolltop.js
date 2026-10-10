/* =============================================
   Scroll To Top - Valento
   Actualiza el anillo y el % de lectura, y
   vuelve arriba al hacer clic.
   ============================================= */

(function () {
    'use strict';

    var btn = document.getElementById('lv-scrolltop');
    if (!btn) return;

    var bar = btn.querySelector('.lv-scrolltop__bar');
    var pct = btn.querySelector('.lv-scrolltop__pct');
    var ticking = false;

    function update() {
        var top = window.scrollY || document.documentElement.scrollTop;
        var max = document.documentElement.scrollHeight - window.innerHeight;
        var value = max > 0 ? Math.min(100, Math.max(0, Math.round(top / max * 100))) : 0;

        if (bar) bar.style.strokeDashoffset = 100 - value;
        if (pct) pct.textContent = value + '%';
        btn.classList.toggle('visible', top > 300);
        btn.classList.toggle('is-end', value >= 100);
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }

    btn.addEventListener('click', function () {
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
})();
