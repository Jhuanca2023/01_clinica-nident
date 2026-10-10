/* Pinta bloques CMS de Rossety encima del HTML de respaldo. */
(function () {
    var RS = window.RS;
    if (!RS || !RS.data) return;
    var D = RS.data;

    function br(s) {
        return RS.esc(s || '').replace(/\n/g, '<br />');
    }

    if (D.slides && D.slides.length) {
        var hero = document.querySelector('.js-hero .swiper-wrapper');
        if (hero) {
            hero.innerHTML = D.slides.map(function (s) {
                return '<div class="swiper-slide"><div class="rs-hero__item"><div class="rs-hero__text">' +
                    '<div class="rs-hero__eyebrow">' + RS.esc(s.eyebrow || '') + '</div>' +
                    '<h2 class="rs-hero__title">' + br(s.titulo) + '</h2>' +
                    '<p class="rs-hero__desc">' + br(s.descripcion) + '</p>' +
                    '<a class="rs-hero__btn" href="' + RS.esc(s.url || '/tienda') + '">' + RS.esc(s.cta || 'Ver') + '</a>' +
                    '</div><div class="rs-hero__media"><img src="' + RS.esc(s.imagen) + '" alt="" /></div></div></div>';
            }).join('');
        }
    }

    if (D.heroCards && D.heroCards.length) {
        var side = document.querySelector('.rs-hero__side');
        if (side) {
            side.innerHTML = D.heroCards.map(function (c) {
                return '<a class="rs-hero__card" href="' + RS.esc(c.url || '/tienda') + '">' +
                    '<div class="rs-hero__card-text"><span class="rs-hero__badge">' + RS.esc(c.badge || '') + '</span>' +
                    '<h3 class="rs-hero__card-title">' + br(c.titulo) + '</h3>' +
                    '<p class="rs-hero__card-price">Desde <span>' + RS.esc(c.precio || '') + '</span></p></div>' +
                    '<img src="' + RS.esc(c.imagen) + '" alt="" /></a>';
            }).join('');
        }
    }

    if (D.pasos && D.pasos.length) {
        var steps = document.querySelector('.rs-steps');
        if (steps) {
            steps.innerHTML = D.pasos.map(function (p) {
                return '<div class="rs-step"><h3 class="rs-step__title">' + RS.esc(p.titulo) + '</h3><p class="rs-step__text">' + RS.esc(p.texto) + '</p></div>';
            }).join('');
        }
    }

    if (D.motivos && D.motivos.length) {
        var why = document.querySelector('.rs-why');
        if (why) {
            why.innerHTML = D.motivos.map(function (m) {
                return '<div class="rs-why__item"><div class="rs-why__icon"><i class="ph ' + RS.esc(m.icono || 'ph-heart') + '"></i></div>' +
                    '<h3 class="rs-why__title">' + RS.esc(m.titulo) + '</h3><p class="rs-why__text">' + RS.esc(m.texto) + '</p></div>';
            }).join('');
        }
    }

    if (D.cuentas && D.cuentas.length) {
        var pay = document.querySelector('.rs-pay');
        if (pay) {
            pay.innerHTML = D.cuentas.map(function (c) {
                var kind = String(c.banco || '').toLowerCase().indexOf('yape') >= 0 ? 'yape' : 'bcp';
                return '<div class="rs-pay__card"><div class="rs-pay__logo rs-pay__logo--' + kind + '">' + RS.esc(c.banco) + '</div><div>' +
                    '<p class="rs-pay__label">' + RS.esc(c.tipo || '') + '</p>' +
                    '<p class="rs-pay__number">' + RS.esc(c.numero) + '</p>' +
                    '<p class="rs-pay__owner">A nombre de: ' + RS.esc(c.titular) + '</p></div></div>';
            }).join('');
        }
    }

    if (D.negocio && D.negocio.pagos) {
        D.negocio.pagos = D.cuentas && D.cuentas.length ? D.cuentas : D.negocio.pagos;
    }
})();
