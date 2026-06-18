/**
 * moreConcerts related-programs slider — Splide.
 * ---------------------------------------------------------------------------
 * Replaces the legacy owlCarousel init for THIS module only. Runs on the pages
 * that render `.moreConcerts-splide` (single-program / single-artist .t3 /
 * Simple_page). RTL matches the theme convention used elsewhere: right-to-left
 * unless the <html lang> is "en-US" (same rule as the $rtl flag in main.js).
 * Markup:  single-program.php / single-artist.php / page-templates/Simple_page.php
 * Styles:  assets/styles/moreconcerts.css
 */
(function () {
    'use strict';

    function initMoreConcerts() {
        if (typeof Splide === 'undefined') { return; }

        var isEn = document.documentElement.getAttribute('lang') === 'en-US';
        var dir = isEn ? 'ltr' : 'rtl';

        // Reuse the same arrow SVGs the old owl nav used.
        var arrowPrev = '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">';
        var arrowNext = '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">';

        var sliders = document.querySelectorAll('.moreConcerts-splide');
        for (var i = 0; i < sliders.length; i++) {
            var el = sliders[i];
            if (el.dataset.splideMounted === '1') { continue; }                  // never double-bind
            if (!el.querySelector('.splide__list > .splide__slide')) {
                el.style.visibility = 'visible'; // nothing to mount: clear the FOUC guard so it can't stay hidden
                continue;
            }

            var splide = new Splide(el, {
                type: 'slide',
                direction: dir,
                perPage: 5,
                perMove: 1,
                gap: '50px',
                arrows: true,
                pagination: false,
                drag: true,
                speed: 500,
                breakpoints: {
                    1499: { perPage: 4, gap: '50px' },
                    991:  { perPage: 3, gap: '30px' },
                    767:  { perPage: 1.15, gap: '16px', arrows: false }
                }
            });

            splide.on('arrows:mounted', function (prev, next) {
                if (prev) { prev.innerHTML = arrowPrev; }
                if (next) { next.innerHTML = arrowNext; }
            });

            splide.mount();
            el.dataset.splideMounted = '1';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMoreConcerts);
    } else {
        initMoreConcerts();
    }
})();
