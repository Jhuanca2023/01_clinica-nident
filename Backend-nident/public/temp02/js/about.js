/* =============================================
   About Page — Valento
   Animaciones e interacciones
   ============================================= */
'use strict';

(function () {

    /* ── IntersectionObserver helper ───────── */
    function onVisible(selector, className, threshold) {
        var els = document.querySelectorAll(selector);
        if (!els.length) return;
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add(className || 'visible');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: threshold || 0.15 });
        els.forEach(function (el) { obs.observe(el); });
    }

    /* ── Zoom hero al cargar ────────────────── */
    window.addEventListener('load', function () {
        var bg = document.querySelector('.ab-hero__bg');
        var content = document.querySelector('.ab-hero__content');
        if (bg) setTimeout(function () { bg.classList.add('zoomed'); }, 100);
        if (content) setTimeout(function () { content.classList.add('visible'); }, 200);
    });

    /* ── Animar secciones al hacer scroll ───── */
    onVisible('.ab-value-card', 'visible', 0.1);
    onVisible('.ab-team-card',  'visible', 0.1);
    onVisible('.ab-timeline__item', 'visible', 0.1);
    onVisible('.ab-story__img-wrap', 'visible', 0.15);

    /* ── Contador animado ───────────────────── */
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        var duration = 1800;
        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            // easeOutExpo
            var ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString() + (el.getAttribute('data-suffix') || '');
        }
        requestAnimationFrame(step);
    }

    var statsObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                document.querySelectorAll('.ab-stat__num').forEach(animateCounter);
                statsObs.disconnect();
            }
        });
    }, { threshold: 0.3 });

    var statsSection = document.querySelector('.ab-stats');
    if (statsSection) statsObs.observe(statsSection);

    /* ── Parallax suave en hero ─────────────── */
    window.addEventListener('scroll', function () {
        var bg = document.querySelector('.ab-hero__bg');
        if (bg) {
            var offset = window.scrollY * 0.3;
            bg.style.transform = 'scale(1) translateY(' + offset + 'px)';
        }
    }, { passive: true });

    /* ── Animar cards al cargar (delay escalonado) ── */
    document.querySelectorAll('.ab-value-card').forEach(function (card, i) {
        card.style.transitionDelay = (i * 0.1) + 's';
    });
    document.querySelectorAll('.ab-team-card').forEach(function (card, i) {
        card.style.transitionDelay = (i * 0.1) + 's';
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity .5s ease, transform .5s ease';
    });
    onVisible('.ab-team-card', null, 0.1);

    // Animar team cards con clase custom
    var teamObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e, i) {
            if (e.isIntersecting) {
                setTimeout(function () {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                }, i * 80);
                teamObs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.ab-team-card').forEach(function (c) { teamObs.observe(c); });

})();
