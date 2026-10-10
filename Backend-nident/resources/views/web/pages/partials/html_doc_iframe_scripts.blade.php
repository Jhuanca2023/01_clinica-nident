<script>
(function() {
    function ajustarAlturaDocIframe() {
        document.querySelectorAll('.ficha-tecnica-iframe-autoheight').forEach(function(iframe) {
            try {
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc && doc.body) {
                    iframe.style.height = 'auto';
                    var body = doc.body, html = doc.documentElement;
                    var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
                    iframe.style.height = height + 'px';
                    iframe.style.overflow = 'visible';
                    iframe.style.minHeight = '120px';
                }
            } catch (e) {}
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        var iframes = document.querySelectorAll('.ficha-tecnica-iframe-autoheight');
        iframes.forEach(function(iframe) {
            try {
                if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') ajustarAlturaDocIframe();
            } catch (e) {}
            iframe.addEventListener('load', function() {
                ajustarAlturaDocIframe();
                setTimeout(ajustarAlturaDocIframe, 50);
                setTimeout(ajustarAlturaDocIframe, 150);
                setTimeout(ajustarAlturaDocIframe, 500);
                try {
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc && doc.body && typeof ResizeObserver !== 'undefined') {
                        new ResizeObserver(ajustarAlturaDocIframe).observe(doc.body);
                    }
                    if (doc) {
                        doc.querySelectorAll('img').forEach(function(img) {
                            if (!img.complete) img.addEventListener('load', ajustarAlturaDocIframe);
                        });
                    }
                } catch (e) {}
            });
        });
        window.addEventListener('resize', ajustarAlturaDocIframe);
    });
})();
</script>
