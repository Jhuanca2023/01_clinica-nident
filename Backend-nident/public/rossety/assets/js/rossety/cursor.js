/* Detalles Rossety · Cursor personalizado: punto que sigue al mouse, anillo con inercia, estados por contexto
   y corazones/destellos al hacer clic. Se desactiva en táctiles y con "reducir movimiento". */
(function () {
    var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!fine || reduce) return;

    var root = document.documentElement;
    var dot = document.createElement('div');
    var ring = document.createElement('div');
    dot.className = 'rs-cursor__dot';
    ring.className = 'rs-cursor__ring';
    ring.innerHTML = '<span class="rs-cursor__label">Ver</span>';
    document.body.appendChild(ring);
    document.body.appendChild(dot);
    root.classList.add('rs-cursor-on');

    var label = ring.querySelector('.rs-cursor__label');
    var mx = -100, my = -100, rx = -100, ry = -100, lastTrail = 0, moved = false;
    var STATES = ['rs-cursor--link', 'rs-cursor--card', 'rs-cursor--text', 'rs-cursor--dark'];

    var CARD = '.rs-card__media, .rs-catcard, .rs-hero__card, .rs-line, .js-open-detail';
    var LINK = 'a, button, summary, label, select, .rs-chip, .rs-filter__opt, [role="button"]';
    var TEXT = 'input:not([type="checkbox"]):not([type="radio"]):not([type="button"]):not([type="submit"]), textarea';
    var DARK = '.rs-footer, .rs-news, .rs-section--red, .rs-pagehero, .rs-race';

    function setState(el) {
        var s = {};
        if (el && el.closest) {
            if (el.closest(TEXT)) s['rs-cursor--text'] = 1;
            else if (el.closest(CARD)) {
                s['rs-cursor--card'] = 1;
                label.textContent = el.closest('.rs-catcard, .rs-hero__card, .rs-line') ? 'Ir' : 'Ver';
            } else if (el.closest(LINK)) s['rs-cursor--link'] = 1;
            if (el.closest(DARK)) s['rs-cursor--dark'] = 1;
        }
        STATES.forEach(function (c) { root.classList.toggle(c, !!s[c]); });
    }

    function spark(x, y, opts) {
        var s = document.createElement('span');
        s.className = 'rs-spark' + (opts.gold ? ' rs-spark--gold' : '') + (opts.trail ? ' rs-spark--trail' : '');
        s.textContent = opts.gold ? '✦' : '♥';
        s.style.setProperty('--x', x + 'px');
        s.style.setProperty('--y', y + 'px');
        s.style.setProperty('--dx', opts.dx + 'px');
        s.style.setProperty('--dy', opts.dy + 'px');
        s.style.setProperty('--r', opts.r + 'deg');
        document.body.appendChild(s);
        s.addEventListener('animationend', function () { s.remove(); });
    }

    document.addEventListener('mousemove', function (e) {
        mx = e.clientX; my = e.clientY;
        if (!moved) { moved = true; rx = mx; ry = my; root.classList.add('rs-cursor--visible'); }
        dot.style.transform = 'translate3d(' + mx + 'px,' + my + 'px,0)';
        setState(e.target);
        var now = performance.now();
        if (now - lastTrail > 90 && !root.classList.contains('rs-cursor--text')) {
            lastTrail = now;
            spark(mx - 5, my - 5, { trail: true, gold: Math.random() > .55, dx: (Math.random() - .5) * 26, dy: 14 + Math.random() * 18, r: (Math.random() - .5) * 60 });
        }
    }, { passive: true });

    document.addEventListener('mousedown', function () { root.classList.add('rs-cursor--down'); });
    document.addEventListener('mouseup', function () { root.classList.remove('rs-cursor--down'); });
    document.addEventListener('click', function (e) {
        for (var i = 0; i < 9; i++) {
            var a = (Math.PI * 2 * i) / 9 + Math.random() * .4;
            var d = 40 + Math.random() * 40;
            spark(e.clientX - 8, e.clientY - 8, { gold: i % 3 === 0, dx: Math.cos(a) * d, dy: Math.sin(a) * d - 10, r: (Math.random() - .5) * 120 });
        }
    });
    document.addEventListener('mouseleave', function () { root.classList.remove('rs-cursor--visible'); });
    document.addEventListener('mouseenter', function () { if (moved) root.classList.add('rs-cursor--visible'); });

    (function loop() {
        rx += (mx - rx) * 0.16;
        ry += (my - ry) * 0.16;
        ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
        requestAnimationFrame(loop);
    })();
})();
