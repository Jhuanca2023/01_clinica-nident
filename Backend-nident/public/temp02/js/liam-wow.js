/**
 * Liam Vercceli — WOW.js initializer with mobile-safe fallbacks.
 * WOW hides .wow elements until scroll; on mobile that often never fires
 * (preloader, address bar, touch scroll). IntersectionObserver + sync fixes it.
 */
(function (window, document) {
    'use strict';

    var REVEALED = 'data-liam-wow-done';
    var observer = null;

    function revealBox(el) {
        if (!el || el.getAttribute(REVEALED)) {
            return;
        }
        el.setAttribute(REVEALED, '1');
        el.style.visibility = 'visible';
        el.style.animationName = '';
        el.classList.add('animated', 'wow--revealed');

        if (window.liamWow && typeof window.liamWow.show === 'function') {
            try {
                window.liamWow.show(el);
                return;
            } catch (e) { /* fallback below */ }
        }
    }

    function revealInViewport() {
        var vh = window.innerHeight || document.documentElement.clientHeight || 0;
        document.querySelectorAll('.wow:not([' + REVEALED + '])').forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < vh - 10 && rect.bottom > 10) {
                revealBox(el);
            }
        });
    }

    function bindObserver() {
        if (!('IntersectionObserver' in window)) {
            return;
        }
        if (observer) {
            observer.disconnect();
        }
        observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    revealBox(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -4% 0px',
            threshold: 0.05
        });

        document.querySelectorAll('.wow').forEach(function (el) {
            observer.observe(el);
        });
    }

    function initWow() {
        if (typeof window.WOW !== 'function') {
            revealInViewport();
            return;
        }

        window.liamWow = new window.WOW({
            boxClass: 'wow',
            animateClass: 'animated',
            offset: 24,
            mobile: true,
            live: true,
            callback: function (box) {
                box.setAttribute(REVEALED, '1');
                box.classList.add('wow--revealed');
            }
        });

        window.liamWow.init();
        bindObserver();
        revealInViewport();

        if (window.liamWow.sync) {
            window.liamWow.sync();
        }
    }

    function scheduleInit() {
        var done = false;
        function run() {
            if (done) {
                return;
            }
            done = true;
            initWow();
            [200, 600, 1200].forEach(function (ms) {
                setTimeout(function () {
                    if (window.liamWow && window.liamWow.sync) {
                        window.liamWow.sync();
                    }
                    revealInViewport();
                }, ms);
            });
        }

        if (document.readyState === 'complete') {
            setTimeout(run, 900);
        } else {
            window.addEventListener('load', function () {
                setTimeout(run, 900);
            }, { once: true });
        }

        setTimeout(run, 3200);
    }

    scheduleInit();

    window.addEventListener('resize', function () {
        if (window.liamWow && window.liamWow.sync) {
            window.liamWow.sync();
        }
        revealInViewport();
    }, { passive: true });

    window.addEventListener('scroll', revealInViewport, { passive: true });

    window.liamWowRefresh = function () {
        if (window.liamWow && window.liamWow.sync) {
            window.liamWow.sync();
        }
        bindObserver();
        revealInViewport();
    };
})(window, document);
