jQuery(document).ready(function($) {


    // Go over all h1 and h2 elements, wrap each letter in a span '.letter' and then the whole container in a span '.letters'
    $('.home .animate-letters').each(function() {
      

        var $this = $(this);
        var $letters = $(this);
        var $rtl = $('html').attr('lang') == 'en-US' ? false : true;
        // Find if the element already has a child with class 'letters'

            if ($this.find('.letters').length) {
                // select the existing element
                $letters = $this.find('.letters');
            } else {
                // Wrap the text inside the element in a span '.letters'
                $this.wrapInner('<span class="letters"></span>');
                // Select the newly created element
                $letters = $this.find('.letters');
            }


            // Split the text inside .letters into spans '.letter'
            $letters.each(function() {
                // Replace the text (a-z, numbers, apostrophes and even spaces) with spans like this <span class='letter'>$&</span>
                $(this).html($(this).text().replace(/([^\x00-\x80]|\w|'| )/g, "<span class='letter'>$&</span>"));
            });

            // If RTL is set to false, reverse the order of the letters
            if (!$rtl) {
                // Get letter spans
            }

        
        // Add the class 'title-rendered' to the element
        $this.addClass('title-rendered');

        // Add unique ID to the element
        $this.attr('id', 'title-rendered-' + Math.floor(Math.random() * 1000000));

    });

    $(window).on('scroll load', function() {

        $('.title-rendered:not(.title-animated)').each(function() {
            if (isScrolledIntoView($(this), 120)) {

                $(this).addClass('title-animated');

                // Get unique ID of the element
                var element_id = $(this).attr('id');

                // get the element selector from $(this).find('.letter')
                var letter_selector = '#' + element_id + ' .letter';
                var parent_selector = '#' + element_id;

                var delay = 0;

                // Set timeout

                setTimeout(function() {

                    anime.timeline({ loop: false })
                        .add({
                            targets: letter_selector,
                            rotateY: [-90, 0],
                            rotateX: [0, 0],
                            duration: 1000,
                            delay: (el, i) => 50 * i
                        });
                    // add class 'title-animated' to the element
                }, delay);

            } else {

            }
        });

    });

    function tryPlayLinksAreaLottie($section) {
        if (!$section || !$section.length || $section.data('links-lottie-played')) return;
        var el = $section.find('#links_areaLottie, [id*="links_areaLottie"]').get(0) || $section.find('lottie-player').get(0);
        if (el && typeof el.play === 'function') {
            el.play();
            $section.data('links-lottie-played', true);
        }
    }

    $(window).on('scroll load', function() {
        $('[data-load-on-view]').each(function() {
            var $el = $(this);
            if (isScrolledIntoView($el, 900)) {
                $el.addClass('loaded');
                if ($el.hasClass('links_area')) tryPlayLinksAreaLottie($el);
            } else {
                $el.removeClass('loaded');
            }
        });
    });

    // When cached or not logged in, Lottie DOM may appear late: retry playing links_area Lottie
    setTimeout(function() {
        $('.links_area.loaded').each(function() { tryPlayLinksAreaLottie($(this)); });
    }, 800);
    setTimeout(function() {
        $('.links_area.loaded').each(function() { tryPlayLinksAreaLottie($(this)); });
    }, 2000);
});

/* ************************ */
/* REVEAL ANIMATION */
/* ************************ */
/*
jQuery(document).ready(function($) {

    $(".animate_wow").addClass("wow fadeInUp");
    var lastScrollTop = 0;
    $(window).scroll(function(event) {
        var st = $(this).scrollTop();
        if (st > lastScrollTop) {
            // downscroll code
            $(".animate_wow").addClass("wow fadeInUp").removeClass("fadeInDown");
        } else {
            // upscroll code
            $(".animate_wow").addClass("wow fadeInDown").removeClass("fadeInUp");
        }
        lastScrollTop = st;
    });

    // Helper function for add element box list in WOW
    WOW.prototype.addBox = function(element) {
        this.boxes.push(element);
    };

    var wow = new WOW({
        boxClass: 'wow', // default
        animateClass: 'animated', // default
        offset: 100, // default
        mobile: true, // default
        live: true // default
    });

    wow.init();

    // $('.wow').on('scrollSpy:exit', function() {
    // 	$(this).css({
    // 		'visibility': 'hidden',
    // 		'animation-name': 'none'
    // 	}).removeClass('animated');
    // 	wow.addBox(this);
    // }).scrollSpy();

});
*/