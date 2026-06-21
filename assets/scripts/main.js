(function($) {
    "use strict";

    setTimeout(function() {
        $("#preloader").fadeOut("slow");

    }, 400);

    $(document).ready(function() {




        var $rtl = true;
        if ($('html').attr('lang') == 'en-US') {
            $rtl = false;
        } else {
            $rtl = true;
        }

        // sticky header
        function sticky_header() {
            var wind = $(window);
            var sticky = $('header');
            wind.on('scroll', function() {
                var scroll = wind.scrollTop();
                if (scroll < 50) {
                    sticky.removeClass('sticky');
                } else {
                    sticky.addClass('sticky');
                }
            });
        }
        sticky_header();

        //  Back to top
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 600) {
                $('.back-to-top').fadeIn(200)
            } else {
                $('.back-to-top').fadeOut(200)
            }
        });

        // back to top animation
        $('.back-to-top').on('click', function(event) {
            event.preventDefault();

            $('html, body').animate({
                scrollTop: 0,
            }, 200);
        });

        // Hamburger-menu
        $('.hamburger-menu').on('click', function() {
            $('.hamburger-menu .line-top, .ofcavas-menu').toggleClass('current');
            $('.hamburger-menu .line-center').toggleClass('current');
            $('.hamburger-menu .line-bottom').toggleClass('current');
            $('header').toggleClass('current');
            $('body').toggleClass('overflow-hidden');
        });

        $('.mobile-menu > ul > li.menu-item-has-children > a').on('click', function(e) {
            e.preventDefault();
            $(this).siblings('.sub-menu').slideToggle();
            $(this).parent().toggleClass('open');
        });



        // magnificPopup Initialize
        $('.popup-youtube').magnificPopup({
            disableOn: false,
            type: 'iframe',
            removalDelay: 160,
            preloader: true,
            fixedContentPos: false
        });


        // magnificPopup Initialize
        $('.prodcastpage .link a').magnificPopup({
            disableOn: false,
            type: 'iframe',
            removalDelay: 160,
            preloader: true,
            fixedContentPos: false
        });


        setTimeout(function() {

            $('.meet-slider').owlCarousel({
                loop: true,
                autoplay: false,
                center: true,
                rtl: $rtl,
                nav: true,
                dots: false,
                navText: [
                    '<img src="/wp-content/uploads/2022/09/arrow_right.svg" alt="">',
                    '<img src="/wp-content/uploads/2022/09/arrow_left.svg" alt="">'
                ],
                responsive: {
                    0: {
                        items: 1.5
                    },
                    992: {
                        items: 4
                    },

                    1500: {
                        items: 4
                    }
                }
            });

            var $owl = $('.meet-slider  .owl-stage');

            $owl.children().each(function(index) {
                $(this).attr('data-position', index); // NB: .attr() instead of .data()
            });

            $(document).on('click', '.meet-slider .owl-item', function() {
                var $speed = 300; // in ms
                $owl.trigger('to.owl.carousel', [$(this).data('position') - 5, $speed]);
            });

            // When finished rendering, make the items appear one by one by adding aos attributes
            // data-aos="fade-in"
            // data-aos-delay="{incrementing delay}"

            var $delay = 0;
            $owl.children().each(function(index) {
                $(this).attr('data-aos', 'fade-in');
                $(this).attr('data-aos-offset', '0');
                $(this).attr('data-aos-duration', '700');
                $(this).attr('data-aos-delay', $delay);
                $delay += 200;
            });

        }, 200);





        //moreConcerts slider — LIVE pages now use Splide (.moreConcerts-splide; init in sliders-splide.js).
        // The owl init below now only matches the legacy static-demo markup still tagged .moreConcerts-slider.

        // Check if there are at least 5 items in the slider, or this is mobile



            $('.moreConcerts-slider').owlCarousel({
                loop: false,
                autoplay: false,
                rtl: $rtl,
                dots: false,
                margin: 30,
                nav: true,
                navText: [
                    '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">',
                    '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">'
                ],
                responsive: {
                    0: {
                        autoWidth: false,
                        items: 1.15,
                        margin: 16,
                        nav: false,
                    },
                    576: {
                        autoWidth: false,
                        items: 1.15,
                        loop:  false,
                        center: false,
                        margin: 16,
                        nav: false,
                    },
                    768: {
                         nav: true,
                       loop:  false,
                        items: 3,
                        margin: 30,
                    },
                    992: {
                           nav: true,
                         loop:  false,
                       center: false,
                        items: 4,
                        margin: 50,
                    },
                    1500: {
                             nav: true,
                         loop:  false,
                       center: false,
                        items: 5
                    }
                }
            });

        

        // order_area sticky toggle — rAF-throttled + passive listener.
        // (Was an unthrottled $(window).scroll that read scrollTop and queued a
        //  setTimeout on every frame, hurting mobile scroll smoothness. Thresholds
        //  600px mobile / 800px desktop are preserved exactly.)
        (function () {
            var $orderArea = $('.order_area');
            if (!$orderArea.length) { return; }
            var isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            var threshold = isMobileUA ? 600 : 800;
            var ticking = false;

            function onScrollFrame() {
                ticking = false;
                var scroll = window.pageYOffset || document.documentElement.scrollTop;
                $orderArea.toggleClass('sticky', scroll > threshold);
            }

            window.addEventListener('scroll', function () {
                if (!ticking) {
                    ticking = true;
                    window.requestAnimationFrame(onScrollFrame);
                }
            }, { passive: true });
        })();


    });

    var buttons = jQuery('.box-tabs_button');

    // if body has class rtl
    if (jQuery('body').hasClass('rtl')) {
        var originLeft = false;
    } else {
        var originLeft = true;
    }

    var $rtl = true;
    if ($('html').attr('lang') == 'en-US') {
        $rtl = false;
    } else {
        $rtl = true;
    }

    // var $grid = jQuery('.grid').isotope({
    //  itemSelector: '.grid-item',
    //  layoutMode: 'fitRows',
    //     originLeft: originLeft,
    // });
    // $grid.isotope({ filter: '' });   

    var grid = jQuery('.grid');

    buttons.click(function() {
        var el = jQuery(this);
        var has = el.hasClass('is-checked');

        buttons.removeClass('is-checked');

        if (has) {
            grid.each(function() {
                var $this = jQuery(this);
                $this.isotope({ filter: '', originLeft: originLeft });
            });

        } else {
            var filterValue = el.attr('data-filter');

            grid.each(function() {
                var $this = jQuery(this);
                $this.isotope({ filter: filterValue, originLeft: originLeft });
            });

            el.addClass('is-checked');


        }
    });

    jQuery('.toggle_link a').on('click', function(e) {
        e.preventDefault();
        jQuery(this).parent().toggleClass('open');
    });

    jQuery('.program-row .program-toggle').on('click', function(e) {
        e.preventDefault();
        jQuery(this).siblings('.program-schedule').toggleClass('open');
        jQuery(this).toggleClass('open');
    });


    $('.recommended-slider').owlCarousel({
        rtl: $rtl,
        loop: true,
        margin: 50,
        dots: false,
        autoplay: false,
        items: 5,
        slideBy: 5,
        nav: true,
        dots: false,
        navText: [
            '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">',
            '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">'
        ],

        responsive: {
            0: {
                items: 1.5,
                 navText: [
            '<img src="/wp-content/uploads/2022/09/arrow_right.svg" alt="">',
            '<img src="/wp-content/uploads/2022/09/arrow_left.svg" alt="">'
       			 ],
          nav: true,
                margin: 15,
  slideBy: 1,
            },

    
            768: {
                   navText: [
            '<img src="/wp-content/uploads/2022/09/arrow_right.svg" alt="">',
            '<img src="/wp-content/uploads/2022/09/arrow_left.svg" alt="">'
       			 ],
          nav: true,
                items: 2,
                margin: 30,
                slideBy: 1,
            },
            992: {
                nav: true,
                items: 3,
                margin: 50,
                slideBy: 3,
            },
            1500: {
                nav: true,
                items: 4,
                slideBy: 4,

            }
        }
    })


    $('.recommended-slider-child').owlCarousel({
        rtl: $rtl,
        loop: false,
        margin: 50,
        dots: false,
        autoplay: false,
        nav: false,
        dots: false,

        responsive: {
            0: {
                items: 1.5,
               nav: false,
                      dots: false,
                margin: 15,
                navText: [
                    '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">',
                    '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">'
                ],
            },
            768: {
                nav: true,
               items: 1.5,
                margin: 30,
                slideBy: 2,
                navText: [
                    '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">',
                    '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">'
                ],
            },
            992: {
                nav: false,
                items: 3,
                margin: 50,
            },
            1500: {
                nav: false,
                items: 3,

            }
        }
    })



    $('.owl-carousel.upcoming-slider').owlCarousel({
        rtl: $rtl,
        margin: 50,
        dots: false,
        rewindNav: false,
         autoplay: false,
 
        navText: [
            '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">',
            '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">'
        ],
        nav: true,
        responsive: {
           0: {
                         
                            autoWidth: true,
                        items: 1,
                        margin: 15,

        navText: [
            '<img src="/wp-content/uploads/2022/09/arrow_right.svg" alt="">',
            '<img src="/wp-content/uploads/2022/09/arrow_left.svg" alt="">'
        ],
                       nav: true,
                       loop:  false,
					  slideBy: 1,
                    },
                    576: {
                            autoWidth: true,
                          navText: [
            '<img src="/wp-content/uploads/2022/09/arrow_right.svg" alt="">',
            '<img src="/wp-content/uploads/2022/09/arrow_left.svg" alt="">'
        ],
                       nav: true,
 						 slideBy: 1,
                       loop:  false,
                       items: 1,
                   center: false,
                        margin: 15,
                    },
            768: {
                items: 3,
                   slideBy: 3,
                margin: 30,
            },
            992: {
                items: 4,
              slideBy: 4,
                margin: 50,
            },
            1500: {
                items: 5,
                
             slideBy: 5,
            }
        }
    })

const videoSliderItems = $('.video-slider').children().length;
const hasTwoVideoPosts = videoSliderItems === 2;

  if (hasTwoVideoPosts) {
    $('.video-slider').addClass('video-slider-two-items');
}
  
$('.video-slider').owlCarousel({
    rtl: $rtl,
    loop: false,
    margin: 30,
    autoplay: false,
    nav: true,
    dots: false,
    navText: [
        '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">',
        '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">'
    ],
    responsive: {
        0: {
            autoWidth: true
        },
        768: {
            slideBy: 2,
            items: 2,
            margin: 30
        },
        992: {
            slideBy: hasTwoVideoPosts ? 3 : 4,
            items: hasTwoVideoPosts ? 3 : 4,
            margin: 50
        },
        1992: {
            slideBy: hasTwoVideoPosts ? 4 : 5,
            items: hasTwoVideoPosts ? 4 : 5
        }
    }
});

    $('.owl-carousel.player-slider').owlCarousel({
        rtl: $rtl,
        loop: true,
        margin: 50,
        center: true,
        loop: true,
        dots: false,
        rewindNav: false,
        autoplay: true,
        items: 4,
        navText: [
            '<i class="prev"></i>',
            '<i class="next"></i>'
        ],
        nav: true,
        responsive: {
            0: {
                items: 2,
                margin: 30,
            },
            600: {
                items: 3,
                margin: 50,
            },
            992: {
                items: 4
            }
        }
    })

    // -------------------------------------------------------------
    // MagnificPopup
    // -------------------------------------------------------------

    $('.overlay a').magnificPopup({ type: 'iframe' });

    $('.video a').magnificPopup({ type: 'iframe' });

    $('.characters-section .link a').magnificPopup({ type: 'iframe' });
    jQuery('.proclose svg').click(function(e) {
        jQuery('.search_input').removeClass('search-activated');
    });



})(jQuery);


document.addEventListener('DOMContentLoaded', function () {
  const header = document.querySelector('.header');
    const intronlottie = document.querySelector('.intor_lottie_en');
  if (!header) { return; } // some templates (landing/Shogun) have no .header
  const headerHeight = header.offsetHeight;
  const startScroll = 49;
if ( intronlottie ){
        setTimeout(function() {
             intronlottie.setDirection(-1);
    intronlottie.play();
   }, 4000);

}

  window.addEventListener('scroll', function () {

    const scrollY = Math.max(window.scrollY - startScroll, 0);
    const scrollPercentage = Math.min(scrollY / headerHeight, 1);
    const backgroundColor = `rgba(255, 255, 255, ${scrollPercentage})`;
    const boxShadow = `0 12px 14px 0 rgba(0, 0, 0, ${scrollPercentage * 0.059})`;
    header.style.backgroundColor = backgroundColor;
    header.style.boxShadow = boxShadow;
  });

  });
const lottieElementlogo = document.querySelector('.logo-lottie');
let scrollInProgress = false;

function resetAnimationSmoothly() {
  if (!lottieElementlogo) { return; } // no .logo-lottie on this page
  if (!scrollInProgress) {
    scrollInProgress = true;
   lottieElementlogo.setDirection(-1);
    lottieElementlogo.play();
  }
}

window.addEventListener('scroll', () => {
  resetAnimationSmoothly();
});


jQuery(document).ready(function(){
    jQuery(".next_event").on("click", function(e) {
        e.preventDefault();  // prevent the default click action
        
        var nextLi = jQuery('.is-next').nextAll('li').filter(function() {
            // check if data-events attribute is defined and not an empty string
            return jQuery(this).data('events') !== undefined && jQuery(this).data('events') !== "";
        }).first();

        if(nextLi.length > 0) {
            // simulate a click on the label within the found li element
            nextLi.find('label').click();
        } else {
            // if no suitable li was found, simulate a click on .ajax-get-month-trigger a.next
            jQuery('.ajax-get-month-trigger a.next').click();
        }
    });
});