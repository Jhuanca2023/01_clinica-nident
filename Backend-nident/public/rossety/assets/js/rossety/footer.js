/* Detalles Rossety · Footer: las columnas aparecen con animación al acercarse al final de la página */
(function () {
    var footer = document.querySelector('.rs-footer');
    if (!footer) return;
    var done = false;

    function show() {
        if (done) return;
        done = true;
        footer.classList.add('rs-footer--in');
        window.removeEventListener('scroll', check);
        window.removeEventListener('resize', check);
    }
    function check() {
        if (footer.getBoundingClientRect().top < window.innerHeight * 0.9) show();
    }

    window.addEventListener('scroll', check, { passive: true });
    window.addEventListener('resize', check);
    check();
})();
