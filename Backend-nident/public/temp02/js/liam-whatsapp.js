/**
 * Mensajes de pedido por WhatsApp.
 * Usa escapes Unicode para que Blade/HTML no convierta emojis a &#...; (WhatsApp los muestra literales).
 */
(function (window, document) {
    'use strict';

    function decodeEntities(str) {
        if (str == null || str === '') {
            return '';
        }
        str = String(str);
        if (str.indexOf('&') === -1) {
            return str;
        }
        var t = document.createElement('textarea');
        t.innerHTML = str;
        return t.value;
    }

    /** Separador y emojis vía \u (no caracteres literales en el HTML). */
    var SEP = Array(22).join('\u2500');
    var EMOJI_CART = '\uD83D\uDED2';
    var EMOJI_CLIP = '\uD83D\uDCCB';
    var EMOJI_MONEY = '\uD83D\uDCB0';
    var INV_EXCL = '\u00A1';

    /**
     * @param {string} codigo
     * @param {Array<{nombre:string,cantidad:number,precio:number,subtotal:number,detalle?:string}>} items
     * @param {number} total
     * @returns {string}
     */
    function buildOrderMessage(codigo, items, total) {
        var lineas = [];
        lineas.push(EMOJI_CART + ' *PEDIDO POR WHATSAPP - rossety*');
        lineas.push(EMOJI_CLIP + ' Codigo de pedido: *' + decodeEntities(codigo || '---') + '*');
        lineas.push(SEP);

        (items || []).forEach(function (item, i) {
            var nombre = decodeEntities(item.nombre || '');
            var detalle = item.detalle ? decodeEntities(item.detalle) : '';
            var linea = (i + 1) + '. ' + nombre;
            if (detalle) {
                linea += ' (' + detalle + ')';
            }
            lineas.push(linea);
            lineas.push(
                '   ' + item.cantidad +
                ' x S/ ' + Number(item.precio).toFixed(2) +
                ' = S/ ' + Number(item.subtotal).toFixed(2)
            );
        });

        lineas.push(SEP);
        lineas.push(EMOJI_MONEY + ' *TOTAL: S/ ' + Number(total).toFixed(2) + '*');
        lineas.push('');
        lineas.push('Hola, deseo coordinar el pago de este pedido. ' + INV_EXCL + 'Gracias!');

        return decodeEntities(lineas.join('\n'));
    }

    function getWaNumber() {
        if (window.HQ_WA && window.HQ_WA.numero) {
            return String(window.HQ_WA.numero).replace(/\D/g, '');
        }
        return '51964381275';
    }

    function openWhatsApp(text) {
        var msgEncoded = encodeURIComponent(decodeEntities(text));
        var waNum = getWaNumber();
        var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        var webUrl = 'https://web.whatsapp.com/send?phone=' + waNum + '&text=' + msgEncoded;

        if (!isMobile) {
            window.open(webUrl, '_blank', 'noopener,noreferrer');
            return;
        }

        var appUrl = 'whatsapp://send?phone=' + waNum + '&text=' + msgEncoded;
        var hidden = document.hidden !== undefined
            ? 'hidden'
            : (document.msHidden !== undefined ? 'msHidden' : 'webkitHidden');
        var visEvt = hidden === 'hidden'
            ? 'visibilitychange'
            : (hidden === 'msHidden' ? 'msvisibilitychange' : 'webkitvisibilitychange');
        var appAbrio = false;
        document.addEventListener(visEvt, function onVis() {
            if (document[hidden]) {
                appAbrio = true;
            }
            document.removeEventListener(visEvt, onVis);
        });
        window.location.href = appUrl;
        setTimeout(function () {
            if (!appAbrio && !document[hidden]) {
                window.open(webUrl, '_blank', 'noopener,noreferrer');
            }
        }, 1500);
    }

    window.LV_WA = {
        decodeEntities: decodeEntities,
        buildOrderMessage: buildOrderMessage,
        open: openWhatsApp,
        getNumber: getWaNumber,
    };
})(window, document);
