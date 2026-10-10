/* Detalles Rossety · Pantalla de carga: progreso real (carga de la página) + avance suave, mensajes rotativos,
   corazones flotantes y salida con cortinas. Más breve en visitas repetidas de la misma sesión. */
(function () {
    var loader = document.querySelector('.rs-loader');
    if (!loader) return;

    var count = loader.querySelector('.js-loader-count');
    var msg = loader.querySelector('.js-loader-msg');
    var hearts = loader.querySelector('.js-loader-hearts');
    var MESSAGES = ['Preparando tus detalles...', 'Atando los lazos...', 'Acomodando las flores...', 'Envolviendo con cariño...', '¡Todo listo para sorprender!'];

    var seen = false;
    try { seen = sessionStorage.getItem('rs-loaded') === '1'; } catch (e) { /* sin almacenamiento */ }
    var minTime = seen ? 350 : 900;
    var start = performance.now();
    var progress = 0, target = 8, loaded = false, finished = false, lastMsg = -1;

    document.body.classList.add('rs-no-scroll');

    /* Corazones que suben por el fondo */
    for (var i = 0; i < 16; i++) {
        var h = document.createElement('span');
        h.className = 'rs-loader__heart';
        h.textContent = i % 4 === 0 ? '✦' : '♥';
        h.style.left = Math.round(Math.random() * 100) + '%';
        h.style.fontSize = (12 + Math.random() * 22) + 'px';
        h.style.animationDuration = (5 + Math.random() * 5) + 's';
        h.style.animationDelay = (-Math.random() * 8) + 's';
        h.style.setProperty('--rot', ((Math.random() - .5) * 90) + 'deg');
        hearts.appendChild(h);
    }

    function setMsg(n) {
        if (n === lastMsg) return;
        lastMsg = n;
        msg.classList.add('rs-loader__msg--swap');
        setTimeout(function () { msg.textContent = MESSAGES[n]; msg.classList.remove('rs-loader__msg--swap'); }, 220);
    }

    function finish() {
        if (finished) return;
        finished = true;
        progress = 100;
        render();
        setMsg(MESSAGES.length - 1);
        try { sessionStorage.setItem('rs-loaded', '1'); } catch (e) { /* sin almacenamiento */ }
        setTimeout(function () {
            loader.classList.add('rs-loader--done');
            document.dispatchEvent(new Event('rs:ready'));
            document.body.classList.remove('rs-no-scroll');
            setTimeout(function () { loader.classList.add('rs-loader--gone'); }, 1100);
        }, 250);
    }

    function render() {
        loader.style.setProperty('--p', progress.toFixed(1));
        count.textContent = Math.round(progress) + '%';
    }

    function tick() {
        if (finished) { clearInterval(timer); return; }
        var elapsed = performance.now() - start;
        var timeShare = Math.min(elapsed / minTime, 1) * (loaded ? 100 : 88);
        target = Math.max(target, timeShare);
        progress += (target - progress) * 0.22;
        if (loaded && progress > 99.4 && elapsed >= minTime) { clearInterval(timer); finish(); return; }
        render();
        setMsg(Math.min(MESSAGES.length - 2, Math.floor(progress / (100 / (MESSAGES.length - 1)))));
    }

    function onLoaded() { loaded = true; }
    if (document.readyState === 'complete') onLoaded();
    else window.addEventListener('load', onLoaded);

    setTimeout(function () { onLoaded(); progress = Math.max(progress, 99.5); }, 7000); /* por si algún recurso externo tarda demasiado */
    setMsg(0);
    render();
    var timer = setInterval(tick, 40);
})();
