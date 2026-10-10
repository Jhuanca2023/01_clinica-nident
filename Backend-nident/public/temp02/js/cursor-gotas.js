/**
 * Cursor "onda" (solo home): un punto que sigue al mouse y un aro que lo
 * persigue con retraso. Sobre enlaces/botones el aro se agranda y al hacer
 * clic se expande una onda. Se omite en pantallas táctiles y con
 * "reducir movimiento".
 */
(function () {
    'use strict';

    var finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!finePointer || reduceMotion) return;

    var CLICKABLE = 'a, button, [role="button"], label, select, summary, .swiper-button-next, .swiper-button-prev, [onclick]';
    var TEXT = 'input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]), textarea, [contenteditable="true"]';

    var dot = document.createElement('div');
    var ring = document.createElement('div');
    dot.className = 'cursor-onda__dot';
    ring.className = 'cursor-onda__ring';
    dot.setAttribute('aria-hidden', 'true');
    ring.setAttribute('aria-hidden', 'true');
    document.body.appendChild(ring);
    document.body.appendChild(dot);
    document.documentElement.classList.add('cursor-onda');

    var mouse = { x: -100, y: -100 };
    var pos = { x: -100, y: -100 };
    var visible = false;

    function place(el, x, y) {
        el.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0)';
    }

    // El aro alcanza al punto con suavizado (efecto de arrastre)
    function tick() {
        pos.x += (mouse.x - pos.x) * 0.16;
        pos.y += (mouse.y - pos.y) * 0.16;
        place(ring, pos.x, pos.y);
        requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);

    function setVisible(v) {
        if (visible === v) return;
        visible = v;
        document.documentElement.classList.toggle('cursor-onda--visible', v);
    }

    document.addEventListener('mousemove', function (e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
        if (!visible) {
            // Primer movimiento: el aro aparece donde está el mouse (sin viajar desde la esquina)
            pos.x = mouse.x;
            pos.y = mouse.y;
        }
        place(dot, mouse.x, mouse.y);
        setVisible(true);

        var target = e.target instanceof Element ? e.target : null;
        document.documentElement.classList.toggle('cursor-onda--hover', !!(target && target.closest(CLICKABLE)));
        document.documentElement.classList.toggle('cursor-onda--text', !!(target && target.closest(TEXT)));
    }, { passive: true });

    document.addEventListener('mouseleave', function () { setVisible(false); });
    document.addEventListener('mouseenter', function () { setVisible(true); });
    window.addEventListener('blur', function () { setVisible(false); });

    document.addEventListener('mousedown', function (e) {
        document.documentElement.classList.add('cursor-onda--down');

        // Onda que se expande y se desvanece desde el punto del clic
        var wave = document.createElement('div');
        wave.className = 'cursor-onda__wave';
        wave.setAttribute('aria-hidden', 'true');
        wave.style.left = e.clientX + 'px';
        wave.style.top = e.clientY + 'px';
        document.body.appendChild(wave);
        wave.addEventListener('animationend', function () { wave.remove(); });
    }, { passive: true });

    document.addEventListener('mouseup', function () {
        document.documentElement.classList.remove('cursor-onda--down');
    });
})();
