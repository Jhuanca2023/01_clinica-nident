@push('scripts')
<script>
function jhUpdateFixedNavHeight() {
    var $nav = $('header nav.navbar.validnavs.navbar-sticky');
    if ($(window).width() <= 1023 && $nav.length) {
        document.documentElement.style.setProperty('--jh-nav-height', $nav.outerHeight() + 'px');
        $('header').css('min-height', $nav.outerHeight() + 'px');
    } else {
        $('header').css('min-height', '');
    }
}
$(window).on('load resize', jhUpdateFixedNavHeight);

function jhToggleScrollTopBtn() {
    var $btn = $('.jh-scroll-top-btn');
    if ($(window).scrollTop() > 300) { $btn.addClass('is-visible'); } else { $btn.removeClass('is-visible'); }
}
$(window).on('scroll', jhToggleScrollTopBtn);
$(document).on('click', '.jh-scroll-top-btn', function() {
    $('html, body').animate({ scrollTop: 0 }, 500, 'swing');
});

function jhHeroSlideAnimations($carousel, relatedTarget) {
    var animEndEv = 'webkitAnimationEnd animationend';
    var $elems = relatedTarget
        ? $(relatedTarget).find("[data-animation^='animated']")
        : $carousel.find('.carousel-item.active').find("[data-animation^='animated']");
    $elems.each(function() {
        var $el = $(this);
        var type = $el.data('animation');
        if (!type) return;
        $el.addClass(type).one(animEndEv, function() { $el.removeClass(type); });
    });
}

function jhInitHeroCarousel() {
    var el = document.getElementById('bootcarousel');
    if (!el || typeof bootstrap === 'undefined') return;
    var items = el.querySelectorAll('.carousel-item');
    if (items.length < 2) {
        jhHeroSlideAnimations($(el));
        return;
    }
    var cfg = window.jhSliderConfig || {};
    var $el = $(el);
    var autoplay = ($el.data('jhAutoplay') || cfg.autoplay || 'S') === 'S';
    var interval = parseInt($el.data('jhInterval'), 10) || parseInt(cfg.interval, 10) || 5000;
    var pauseHover = $el.data('jhPauseHover') || cfg.pause_on_hover || 'S';
    var pause = pauseHover === 'S' ? 'hover' : false;
    var instance = bootstrap.Carousel.getOrCreateInstance(el, {
        interval: autoplay ? interval : false,
        ride: autoplay ? 'carousel' : false,
        pause: pause,
        wrap: true,
        touch: true
    });
    if (autoplay) {
        instance.cycle();
    }
    jhHeroSlideAnimations($(el));
    el.addEventListener('slide.bs.carousel', function(e) {
        jhHeroSlideAnimations($(el), e.relatedTarget);
    });
}

$(document).ready(function() {
    jhUpdateFixedNavHeight();
    jhToggleScrollTopBtn();
    jhInitHeroCarousel();
    var $clientsCarousel = $('.jh-clients-area .jh-clients-carousel');
    if ($clientsCarousel.length) {
        if ($clientsCarousel.hasClass('owl-loaded')) {
            $clientsCarousel.trigger('destroy.owl.carousel');
        }
        $clientsCarousel.owlCarousel({
            loop: true,
            margin: 24,
            nav: true,
            dots: true,
            autoplay: true,
            autoplayTimeout: 3500,
            autoplayHoverPause: true,
            smartSpeed: 600,
            navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            responsive: {
                0:   { items: 2 },
                576: { items: 3 },
                768: { items: 4 },
                1024:{ items: 5 }
            }
        });
    }
});

function jhOpenImageModal(imgSrc, imgTitle) {
    if (!imgSrc) return;
    $.magnificPopup.open({
        items: { src: imgSrc }, type: 'image', closeOnContentClick: true, closeBtnInside: false, fixedContentPos: true,
        mainClass: 'mfp-with-zoom mfp-img-mobile',
        image: { verticalFit: true, titleSrc: function() { return imgTitle || ''; } },
        zoom: { enabled: true, duration: 300, easing: 'ease-in-out',
            opener: function(el) { return el.is('img') ? el : el.find('img'); }
        }
    });
}

$(document).on('click', '.jh-product-popup, .jh-about-popup', function(e) {
    e.preventDefault();
    e.stopPropagation();
    jhOpenImageModal($(this).attr('href'), $(this).attr('title') || '');
});

function jhMaquinariaModalFromFlip($flip) {
    if (!$flip || !$flip.length) return;
    var src = $flip.attr('data-modal-src') || $flip.data('modalSrc') || '';
    var title = $flip.attr('data-modal-title') || $flip.data('modalTitle') || '';
    jhOpenImageModal(src, title);
}

$(document).on('click', '.jh-maquinaria-popup-trigger, .jh-maquinaria-flip[data-modal-src]', function(e) {
    if ($(e.target).closest('.jh-maquinaria-wa, .jh-btn-whatsapp').length) return;
    e.preventDefault();
    e.stopPropagation();
    var $flip = $(this).closest('.jh-maquinaria-flip');
    if (!$flip.length && $(this).is('.jh-maquinaria-flip[data-modal-src]')) {
        $flip = $(this);
    }
    jhMaquinariaModalFromFlip($flip);
});

$(document).on('keydown', '.jh-maquinaria-popup-trigger[role="button"]', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        jhMaquinariaModalFromFlip($(this).closest('.jh-maquinaria-flip'));
    }
});

document.querySelectorAll('[data-play-target]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = btn.getAttribute('data-play-target');
        var card = document.querySelector('[data-video-card="' + id + '"]');
        if (!card) return;
        var poster = card.querySelector('[data-poster-for="' + id + '"]');
        var player = card.querySelector('[data-player="' + id + '"]');
        if (poster) poster.classList.add('is-hidden');
        if (player) {
            player.classList.remove('is-hidden');
            var vid = player.querySelector('video');
            if (vid) { vid.play(); return; }
            var ytBox = player.querySelector('[data-yt-lazy]');
            if (ytBox && !ytBox.querySelector('iframe')) {
                var ytId = ytBox.getAttribute('data-yt-lazy');
                var iframe = document.createElement('iframe');
                iframe.src = 'https://www.youtube.com/embed/' + ytId + '?rel=0&autoplay=1';
                iframe.setAttribute('allowfullscreen', '');
                iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
                iframe.setAttribute('title', 'Video');
                ytBox.appendChild(iframe);
            }
        }
    });
});
</script>
@endpush
