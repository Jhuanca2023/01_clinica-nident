/* =============================================
   Contact Page — Valento
   ============================================= */
'use strict';

(function () {

    /* ── Hero zoom al cargar ────────────────── */
    window.addEventListener('load', function () {
        var bg = document.querySelector('.ct-hero__bg');
        var ct = document.querySelector('.ct-hero__content');
        if (bg) setTimeout(function(){ bg.classList.add('zoomed'); }, 100);
        if (ct) setTimeout(function(){ ct.classList.add('visible'); }, 200);
    });

    /* ── Animar cards al hacer scroll ────────── */
    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e, i){
            if (e.isIntersecting) {
                setTimeout(function(){
                    e.target.classList.add('visible');
                }, i * 100);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.ct-info-card').forEach(function(c){ obs.observe(c); });

    /* ── Validación en tiempo real ──────────── */
    function validateField(input) {
        var val = input.value.trim();
        var ok  = val.length > 0;
        if (input.type === 'email') {
            ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
        }
        input.style.borderColor = val.length === 0 ? '' : ok ? '#27ae60' : '#8C6B2E';
        return ok;
    }

    document.querySelectorAll('.ct-input, .ct-textarea').forEach(function(el){
        el.addEventListener('blur', function(){ validateField(this); });
        el.addEventListener('input', function(){
            if (this.style.borderColor) validateField(this);
        });
    });

    /* ── Envío del formulario ───────────────── */
    var form = document.getElementById('ct-form');
    if (form) {
        // Con action (backend) el formulario se envía al servidor; sin action (template) es solo demo.
        var envioReal = form.hasAttribute('action');

        form.addEventListener('submit', function(e){
            var fields = form.querySelectorAll('.ct-input, .ct-textarea');
            var allOk  = true;
            fields.forEach(function(f){
                if ((f.required || !envioReal) && !validateField(f)) allOk = false;
            });

            if (!document.getElementById('ct-terms').checked) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones.');
                return;
            }

            if (!allOk) {
                e.preventDefault();
                return;
            }

            var btn = document.getElementById('ct-send-btn');
            if (btn) {
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
            }

            if (envioReal) {
                return;
            }

            e.preventDefault();
            btn.disabled  = true;

            setTimeout(function(){
                btn.innerHTML = '<i class="fa fa-check"></i> ¡Mensaje enviado!';
                btn.style.background = '#27ae60';
                document.getElementById('ct-success').style.display = 'block';
                form.reset();
                fields.forEach(function(f){ f.style.borderColor = ''; });
                setTimeout(function(){
                    btn.innerHTML = '<i class="fa fa-paper-plane"></i> Enviar Mensaje';
                    btn.style.background = '';
                    btn.disabled = false;
                    document.getElementById('ct-success').style.display = 'none';
                }, 4000);
            }, 1400);
        });
    }

    /* ── Contador de caracteres en textarea ─── */
    var ta = document.getElementById('ct-message');
    var counter = document.getElementById('ct-char-count');
    if (ta && counter) {
        ta.addEventListener('input', function(){
            counter.textContent = this.value.length + ' / 500';
        });
    }

    /* ── FAQ accordion ──────────────────────── */
    document.querySelectorAll('.ct-faq-q').forEach(function(q){
        q.addEventListener('click', function(){
            var item = this.closest('.ct-faq-item');
            var isOpen = item.classList.contains('open');
            document.querySelectorAll('.ct-faq-item').forEach(function(i){ i.classList.remove('open'); });
            if (!isOpen) item.classList.add('open');
        });
    });

})();
