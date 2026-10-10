/**
 * Panel del buscador del header (Valento): la lupa abre/cierra el panel que
 * contiene #lv-search-input; las sugerencias las pinta pls-live-search.js.
 */
(function () {
    'use strict';

    var box = document.getElementById('vl-search');
    if (!box) return;
    var toggle = box.querySelector('.vl-search-toggle');
    var input = document.getElementById('lv-search-input');

    var panel = document.getElementById('vl-search-panel');
    var header = document.getElementById('lv-header');

    function abrir() {
        // En celular el panel es fijo: se coloca justo debajo del header real
        if (panel && header && window.innerWidth < 768) {
            panel.style.top = Math.max(8, header.getBoundingClientRect().bottom + 8) + 'px';
        } else if (panel) {
            panel.style.top = '';
        }
        box.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        setTimeout(function () { input && input.focus(); input && input.select(); }, 60);
    }

    function cerrar() {
        box.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        var dd = document.getElementById('pls-search-results');
        if (dd) { dd.hidden = true; dd.classList.remove('is-open'); dd.style.display = 'none'; }
    }

    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        box.classList.contains('is-open') ? cerrar() : abrir();
    });

    // Lupa de la barra fija: sube al header y abre el buscador
    document.querySelectorAll('[data-vl-search-top]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(abrir, 350);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && box.classList.contains('is-open')) cerrar();
    });

    // Clic fuera del panel y del desplegable de sugerencias → cerrar
    document.addEventListener('click', function (e) {
        if (!box.classList.contains('is-open')) return;
        var dd = document.getElementById('pls-search-results');
        if (box.contains(e.target) || (dd && dd.contains(e.target))) return;
        cerrar();
    });
})();
