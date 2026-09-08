// ============================================================
// #1 | דף צעירים  – הגעה לאזור המבוקש (ID: 51744 | type: js)
// ============================================================

// Prevent automatic scroll to hash on load
if (window.location.hash === "#contact") {
    // Save the hash and clear it temporarily to prevent auto-scroll
    const hash = window.location.hash;

    history.replaceState(null, null, ' '); // Temporarily clear hash

    // Wait for the page to fully load
    window.addEventListener("load", function() {
        // Restore the hash in the URL and scroll smoothly to the contact section
        history.replaceState(null, null, hash);
        const contactElement = document.querySelector(hash);
        if (contactElement) {
            contactElement.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });
}


// ============================================================
// #2 | עונה חדשה בפילהרמונית (ID: 46507 | type: js)
// ============================================================

jQuery(document).ready(function($){

    var isRtl = $('html').attr('lang') !== 'en-US';

    jQuery('.slider-banner.desktop').not('.slick-initialized').slick({
        centerMode: true,
        infinite: true,
        margin: 30,
        slidesToShow: 2,
        slidesToScroll: 1,
        rtl: isRtl
    });

    jQuery('.slider-banner.mobile').not('.slick-initialized').slick({
        infinite: false,
        rtl: isRtl,
        centerMode: false,
        slidesToShow: 1.5,
        slidesToScroll: 1
    });

});


// ============================================================
// #4+#5 | Months Popup (unified EN/HE — AJAX on full calendar)
// ============================================================

$(document).ready(function() {
    // Detach popup to body + fixed position so cells under it cannot steal clicks
    var popupCloseTimer = null;

    function resetDetachedPopup($popup) {
        if (!$popup || !$popup.length) {
            return;
        }
        var $home = $popup.data('home-day');
        $popup.removeClass('is-fixed-open').removeAttr('style');
        if ($home && $home.length) {
            $home.append($popup);
        }
        $popup.removeData('home-day');
    }

    function closeCalendarPopups($scope) {
        clearTimeout(popupCloseTimer);
        popupCloseTimer = null;
        var $cal = $scope && $scope.length ? $scope : $('.calendar-full');
        $cal.removeClass('popup-open');
        $cal.find('.loop-day.popup-active').removeClass('popup-active');
        $('body > .calendar-hint-popup.is-fixed-open').each(function() {
            resetDetachedPopup($(this));
        });
    }

    function positionFixedPopup($popup, $day) {
        var rect = $day[0].getBoundingClientRect();
        var index = $day.index();
        var col = index % 7;
        var flipCol = col <= 3; // nth-child(7n+1..4)
        var lang = ($('html').attr('lang') || '').toLowerCase();
        var isEn = lang.indexOf('en') === 0;
        // HE default opens right; flip cols open left. EN is inverted.
        var openRight = isEn ? flipCol : !flipCol;
        var popupWidth = $popup.outerWidth() || 520;
        var popupHeight = $popup.outerHeight() || 240;
        var earlyRow = index < 14;
        var top = earlyRow ? (rect.top - 20) : (rect.bottom - popupHeight + 40);
        top = Math.max(8, Math.min(top, window.innerHeight - popupHeight - 8));
        var left = openRight ? (rect.right - 12) : (rect.left - popupWidth + 12);
        left = Math.max(8, Math.min(left, window.innerWidth - popupWidth - 8));

        $popup.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px',
            right: 'auto',
            bottom: 'auto',
            transform: 'none',
            zIndex: 100000,
            opacity: 1,
            pointerEvents: 'auto',
            margin: 0
        });
    }

    function openCalendarPopup($day) {
        var $cal = $day.closest('.calendar-full');
        if (!$cal.length || !$day.hasClass('has-events')) {
            return;
        }

        clearTimeout(popupCloseTimer);
        popupCloseTimer = null;

        $cal.find('.loop-day.popup-active').not($day).removeClass('popup-active');
        $('body > .calendar-hint-popup.is-fixed-open').each(function() {
            var $p = $(this);
            var $home = $p.data('home-day');
            if (!$home || $home[0] !== $day[0]) {
                resetDetachedPopup($p);
            }
        });

        var $popup = $day.children('.calendar-hint-popup').first();
        if (!$popup.length) {
            $popup = $('body > .calendar-hint-popup.is-fixed-open').filter(function() {
                var $home = $(this).data('home-day');
                return $home && $home[0] === $day[0];
            }).first();
        }
        if (!$popup.length) {
            return;
        }

        $day.addClass('popup-active');
        $cal.addClass('popup-open');

        if (!$popup.hasClass('is-fixed-open')) {
            $popup.data('home-day', $day);
            $('body').append($popup);
            $popup.addClass('is-fixed-open');
        }

        positionFixedPopup($popup, $day);
    }

    function scheduleClosePopup($day) {
        clearTimeout(popupCloseTimer);
        popupCloseTimer = setTimeout(function() {
            var $cal = $day.closest('.calendar-full');
            var $floating = $('body > .calendar-hint-popup.is-fixed-open').filter(function() {
                var $home = $(this).data('home-day');
                return $home && $home[0] === $day[0];
            });
            $day.removeClass('popup-active');
            resetDetachedPopup($floating);
            if ($cal.length && !$cal.find('.loop-day.popup-active').length) {
                $cal.removeClass('popup-open');
            }
        }, 180);
    }

    $(document).on('mouseenter focusin', '.calendar-full .loop-day.has-events', function() {
        openCalendarPopup($(this));
    });

    $(document).on('mouseleave', '.calendar-full .loop-day.has-events', function(e) {
        var $day = $(this);
        var related = e.relatedTarget;
        if (related && $(related).closest('.calendar-hint-popup.is-fixed-open').length) {
            return;
        }
        scheduleClosePopup($day);
    });

    $(document).on('mouseenter', 'body > .calendar-hint-popup.is-fixed-open', function() {
        clearTimeout(popupCloseTimer);
        popupCloseTimer = null;
        var $home = $(this).data('home-day');
        if ($home && $home.length) {
            $home.addClass('popup-active');
            $home.closest('.calendar-full').addClass('popup-open');
        }
    });

    $(document).on('mouseleave', 'body > .calendar-hint-popup.is-fixed-open', function(e) {
        var $home = $(this).data('home-day');
        var related = e.relatedTarget;
        if ($home && $home.length && related && $.contains($home[0], related)) {
            return;
        }
        if ($home && $home.length) {
            scheduleClosePopup($home);
        } else {
            resetDetachedPopup($(this));
        }
    });

    $(document).on('click', '.calendar-hint-popup a[href]:not([href="#"])', function(e) {
        e.stopPropagation();
    });

    $(window).on('scroll.calendarPopup resize.calendarPopup', function() {
        var $popup = $('body > .calendar-hint-popup.is-fixed-open').first();
        if (!$popup.length) {
            return;
        }
        var $home = $popup.data('home-day');
        if ($home && $home.length) {
            positionFixedPopup($popup, $home);
        }
    });

    window.closeCalendarPopups = closeCalendarPopups;

    var monthsHe = [
        'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
        'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'
    ];
    var monthsEn = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    function isHebrewLang() {
        var lang = ($('html').attr('lang') || '').toLowerCase();
        return lang === 'he' || lang === 'he-il' || lang.indexOf('he') === 0;
    }

    function getMonthNames() {
        return isHebrewLang() ? monthsHe : monthsEn;
    }

    function getCalendarNavBase() {
        var $date = $('.calendar-full .rendered-date').first();
        if ($date.length) {
            var month = parseInt($date.attr('data-month'), 10);
            var year = parseInt($date.attr('data-year'), 10);
            if (month && year) {
                return { month: month, year: year };
            }
        }
        var now = new Date();
        return { month: now.getMonth() + 1, year: now.getFullYear() };
    }

    function renderMonthsList($popup, baseMonth, baseYear) {
        var months = getMonthNames();
        var monthsList = '<ul>';
        for (var offset = 0; offset < 12; offset++) {
            var monthIndex = (baseMonth - 1 + offset) % 12;
            var displayMonth = monthIndex + 1;
            var displayYear = (monthIndex < baseMonth - 1) ? baseYear + 1 : baseYear;
            var currentClass = (monthIndex + 1 === baseMonth && displayYear === baseYear) ? ' class="current-month"' : '';
            monthsList += '<li data-month="' + displayMonth + '" data-year="' + displayYear + '"' + currentClass + '>' + months[monthIndex] + '</li>';
        }
        monthsList += '</ul>';
        $popup.html(monthsList);
        $popup.css('display', 'block');
    }

    $(document).on('click', '.rendered-date', function(e) {
        // Only handle month popup on calendar UI (full calendar or homepage month trigger)
        if (!$(this).closest('.calendar-full, .ajax-get-month-trigger').length) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();

        var $el = $(this);
        var baseMonth = parseInt($el.attr('data-month'), 10);
        var baseYear = parseInt($el.attr('data-year'), 10);
        if (!baseMonth || !baseYear) {
            var nav = getCalendarNavBase();
            baseMonth = nav.month;
            baseYear = nav.year;
        }

        var $popup = $el.siblings('#monthsPopup');
        if (!$popup.length) {
            $popup = $el.parent().find('#monthsPopup').first();
        }
        if (!$popup.length) {
            $popup = $('#monthsPopup').first();
        }
        renderMonthsList($popup, baseMonth, baseYear);
    });

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.rendered-date').length && !$(event.target).closest('#monthsPopup').length) {
            $('#monthsPopup').css('display', 'none');
        }
    });

    $(document).on('click', '#monthsPopup li', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var month = $(this).data('month');
        var year = $(this).data('year');
        $('#monthsPopup').css('display', 'none');

        // Full calendar: navigate via AJAX when available
        if ($('.ipo-calendar.calendar-full').length && typeof window.request_calendar_events === 'function') {
            window.request_calendar_events(month, year);
            return;
        }

        // Homepage / other: go to full calendar page for the selected month
        if (isHebrewLang()) {
            window.location.href = '/לוח-שנה/?month=' + month + '&y=' + year;
        } else {
            window.location.href = '/en/calendar/?month=' + month + '&y=' + year;
        }
    });

});


// ============================================================
// #6 | Page Program JS (ID: 41342 | type: js)
// ============================================================

// Scoped in an IIFE so the top-level `const readMoreButtons`/`readLessButtons`
// don't leak to the global scope and collide with a still-active duplicate of
// this snippet (origin code-manager #41342), which caused
// "Identifier 'readMoreButtons' has already been declared".
(function () {
const readMoreButtons = document.querySelectorAll('.read-more');
const readLessButtons = document.querySelectorAll('.read-less');

readMoreButtons.forEach(button => {
  button.addEventListener('click', () => {
    if (document.documentElement.lang === 'he' || document.documentElement.lang === 'he-IL') {
      const buttonText = button.textContent.trim();
      const programInfoDiv = button.closest('.program-info');

      if (programInfoDiv.classList.contains('show-read-more')) {
        programInfoDiv.classList.remove('show-read-more');
        button.textContent = 'לקריאה נוספת';
        button.classList.remove('read-less');
      } else {
        programInfoDiv.classList.add('show-read-more');
        button.textContent = 'לקרוא פחות';
        button.classList.add('read-less');
      }
    }
    
       if (document.documentElement.lang === 'en' || document.documentElement.lang === 'en-US') {
      const buttonText = button.textContent.trim();
      const programInfoDiv = button.closest('.program-info');

      if (programInfoDiv.classList.contains('show-read-more')) {
        programInfoDiv.classList.remove('show-read-more');
        button.textContent = 'Read More';
        button.classList.remove('read-less');
      } else {
        programInfoDiv.classList.add('show-read-more');
        button.textContent = 'Read less';
        button.classList.add('read-less');
      }
    }
  });
});

readLessButtons.forEach(button => {
  button.addEventListener('click', () => {
    if (document.documentElement.lang === 'he') {
      const programInfoDiv = button.closest('.program-info');

      if (programInfoDiv && programInfoDiv.classList.contains('show-read-more')) {
        programInfoDiv.classList.remove('show-read-more');
        const readMoreButton = programInfoDiv.querySelector('.read-more');
        if (readMoreButton) {
          readMoreButton.textContent = 'קרא עוד';
          readMoreButton.classList.remove('read-less');
        }
      }
    }
  });
});
})();


// ============================================================
// #7 | Home Page JS (ID: 41333 | type: js)
// ============================================================

var videoElement = document.getElementById("main-video");
var videoSource = document.getElementById("main-video-source");
var attemptInterval;

if (videoElement && videoSource) {
	var originalSrc = videoSource.src;
	var mobileSrc = "https://www.ipo.co.il/wp-content/uploads/2025/09/6x9-46-15.mp4";

	function checkMobile() {
		var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

		videoSource.src = isMobile ? mobileSrc : originalSrc;
		videoElement.load();
	}

	checkMobile();

	videoElement.addEventListener("canplay", function () {
		var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

		if (!isMobile) return;

		attemptInterval = setInterval(function () {
			videoElement.play().then(function () {
				clearInterval(attemptInterval);
			}).catch(function () {});
		}, 500);
	});
}

// NOTE: .slider-banner sliders are already initialized by snippet #2 above.
// The duplicate slick() init that used to live here caused Slick to run
// twice on the same elements, which throws "initADA: Cannot read properties
// of null (reading 'add')" on the second pass. Removed to prevent double-init.


// ============================================================
// #8 | JS Home page (ID: 34616 | type: js)
// ============================================================

function soundplay() {
	const vid = document.getElementById("main-video");

	if (!vid) return;

	jQuery('#speaker_lottie').get(0)?.play();

	if (vid.muted === false) {
		jQuery('#speaker_lottie').get(0)?.play();
		vid.muted = true;
	} else {
		vid.muted = false;
		jQuery('#speaker_lottie').get(0)?.stop();
	}
}

function removeDiv() {
	jQuery('html').removeClass("play_section");

	const video = document.getElementById("play-background");

	if (!video) return;

	video.pause();
	jQuery('.video_area').removeClass("play");
}

function pauseVid() {
	const video = document.getElementById("main-video");

	if (!video) return;

	jQuery('#play_lottie').get(0)?.play();

	if (video.paused) {
		jQuery('#play_lottie').get(0)?.play();
		video.play();
	} else {
		jQuery('#play_lottie').get(0)?.stop();
		video.pause();
	}
}

window.addEventListener('load', function () {
	jQuery('#speaker_lottie').get(0)?.play();
	jQuery('#play_lottie').get(0)?.play();
});

function jsplyvideo() {
	const video = document.getElementById("play-background");

	if (!video) return;

	jQuery('.video_area').addClass("play");
	jQuery('html').addClass("play_section");
	video.play();
}

jQuery(window).scroll(function () {
	jQuery('lottie-player:not(.no0)').each(function () {
		if (isScrolledIntoView(this) === true) {
			jQuery(this).get(0)?.play();
		}
	});
});

window.addEventListener('load', function () {
	setTimeout(function () {
		jQuery('.logo-lottie').get(0)?.play();
		jQuery('#intor_lottie').get(0)?.play();
	}, 2500);

	setTimeout(function () {
		jQuery('.home').addClass('show');
	}, 1000);
});


// ============================================================
// #9 | Artist plan additionalDates (ID: 32660 | type: js)
// ============================================================

// wordpress jquery wrap
jQuery(document).ready(function($){
    // Expand in-page additional dates only when sibling .additional-date items exist
    // (artist plan cards). Otherwise allow normal navigation (program cards → #time_zone).
    $('.additionalDates').click(function(e){
		var $parent = $(this).parent();
		if ($parent.find('.additional-date').length) {
			e.preventDefault();
			$parent.find('.additional-date').fadeIn(300).css("display","flex");
			$parent.find('.additionalDates').fadeOut(300);
		}
    });
});


// ============================================================
// #10 | Submenu – diagonal support (ID: 32413 | type: js)
// ============================================================

// jQuery wrap for wordpress
jQuery(document).ready(function($) {
    // If user is mouse menu item with children (.menu-item-has-children)
    $('.menu-item-has-children').hover(function() {
        // Add class .hover to menu item
        $(this).addClass('hover');
    }, function() {
        // Give  a little time to user to click on submenu
        setTimeout(function() {
            // Remove class .hover from menu item
            $('.menu-item-has-children').removeClass('hover');
        }, 1000);
    });
});


// ============================================================
// #11 | Sticky black slip (ID: 32404 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
	
	if($('.order_area').length && $('.timeZone_area .moreevent').length){
		$(window).scroll(function() {    
			var scroll = $(window).scrollTop() + $(window).height();

			var p = $(".timeZone_area .moreevent");
			var offset = p.offset().top;

			if (scroll >= (offset + 100)) {
				$(".order_area").addClass("sticky");
			} else {
				$(".order_area").removeClass("sticky");
			}
		});
		
		jQuery('.order_area .btn').click(function(e) {
			e.preventDefault();
			jQuery('html, body').animate({
				scrollTop: jQuery("#time_zone").offset().top - 100
			}, 100);
		});
	}

	// Expand hidden program dates (mobile + desktop).
	$(document).on('click', '.js-more-dates, .timeZone_area .moreevent .readmore', function (e) {
		e.preventDefault();
		$('.time_zone').addClass('active');
		$('.order_area').removeClass('sticky');
		$('.moreevent').addClass('active');
	});
	
});


// ============================================================
// #12 | Mobile search trigger (ID: 32281 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
    $('.mobile-search-toggle').on('click', function(e) {

        e.preventDefault();

        if( $('.mobile-header .width-33.t1').is(':visible') ) {
            $('.mobile-header .width-33.t1').slideUp();
            return;
        } else {
            $('.mobile-header .width-33.t1').slideDown();
        }

    });
});


// ============================================================
// #13 | Musican form validation (ID: 32218 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
    if ( $('.musician-form .validation_message').length > 0 ) {
		$('.musician-form').show();
	}
});


// ============================================================
// #14 | Set fixed width (ID: 32204 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
    // On page load find all .menu-pc > .menu > .menu-item and get width of each
    $elements = $('.menu-pc > .menu > .menu-item');
    $elements.each(function() {
        // Get the width of the .menu-item element
        var itemWidth = $(this).width();
        // Add 5% to the width of the .menu-item element
        itemWidth = itemWidth + (itemWidth * 0.05);
        // Set the width of the .menu-item element to the width of the .menu-item element
        $(this).css('width', itemWidth);
    });
});


// ============================================================
// #15 | Reveal item on load (ID: 32093 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
    $(document).on('item_activated', function(events){
		$events = $('[data-ajax-trigger][data-events].active').attr('data-events');
// 		if($events){
			toggleDaysEvents( $events );
// 		}
		
	});
});


// ============================================================
// #16 | Header (ID: 30604 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){

	// Define throttle function if it doesn't exist
	if (typeof throttle !== 'function') {
		function throttle(func, wait, options) {
			var context, args, result;
			var timeout = null;
			var previous = 0;
			if (!options) options = {};
			var later = function() {
				previous = options.leading === false ? 0 : Date.now();
				timeout = null;
				result = func.apply(context, args);
				if (!timeout) context = args = null;
			};
			return function() {
				var now = Date.now();
				if (!previous && options.leading === false) previous = now;
				var remaining = wait - (now - previous);
				context = this;
				args = arguments;
				if (remaining <= 0 || remaining > wait) {
					if (timeout) {
						clearTimeout(timeout);
						timeout = null;
					}
					previous = now;
					result = func.apply(context, args);
					if (!timeout) context = args = null;
				} else if (!timeout && options.trailing !== false) {
					timeout = setTimeout(later, remaining);
				}
				return result;
			};
		};
	}


	function set_submenu_position() {
		// Check .site > header height
		var headerHeight = $('.site > header').height();
		// Check the offset of the header from the top of the page
		var headerOffset = $('.site > header').offset().top;
		// Calcalate the menu position
		var menuPosition = headerHeight;
		// Set the menu position
		$('.desktop-menu .menu > li > .sub-menu').css('top', menuPosition);
	}

	// Set the menu position on page load
	set_submenu_position();

	// Set the menu position on window resize with a 500ms throttle
	$(window).resize(throttle( set_submenu_position, 500 ) );


});


// ============================================================
// #17 | Search toggler (ID: 30597 | type: js)
// ============================================================

jQuery(document).ready(function($){
    $('.promagnifier').on('click',function(e){
		e.preventDefault();
		$(this).closest('.search_input').addClass('search-activated');
		$('input.orig').focus();
	});
	
 $('.mobile-search-toggle').on('click',function(e){
		e.preventDefault();
		 if ($(this).hasClass('open')){
			 $('input.orig').val('');
			 document.activeElement.blur();
    $("input").blur();
	$(this).removeClass('open'); 
	$('body').removeClass('search-open'); 
				 
			
		 }else{
		$(this).addClass('open');
			 	$('body').addClass('search-open'); 
		 }
		$('input.orig').focus();
	});
	
	
	$('input.orig').focusout(function(){
  $('.search_input').removeClass('search-activated'); 
});
});


// ============================================================
// #19 | Menu children class setup (ID: 29591 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
	
    // .menu > .menu-item > .sub-menu > .container > .menu-item

	$elements_to_check = $('.menu > .menu-item > .sub-menu > .container > .menu-item > .sub-menu');

	// Check if the element exists
	if ( $elements_to_check.length > 0 ) {
		$elements_to_check.each(function() {
			// Check how many children the element has
			if ( $(this).children().length > 1 ) {
				// If the element has children, add the class
				$(this).closest('.menu-item').addClass('ipo-has-children');
			} else if( $(this).children().length == 1 ) {
				// If the element has one child, add the class
				$(this).closest('.menu-item').addClass('ipo-has-one-child');
			} else {
				// If the element has no children, add the class
				$(this).closest('.menu-item').addClass('ipo-has-no-children');
			}

		});
	}

	$elements_to_check = $('.desktop-menu .menu > li > .sub-menu');
	// Check how many $(this).find('.container > .menu-item') elements there are

	// Check if the element exists
	if ( $elements_to_check.length > 0 ) {
		$elements_to_check.each(function() {
			// Check how many children the element has
			$children_count = $(this).find('.container > .menu-item').length;
			$(this).closest('.menu-item').attr('data-children-count', $children_count);

		});
	}


	// Check how many children each sub-menu has. Then add 'data-children-count-r' to the closest .menu-item
	$elements_to_check = $('.desktop-menu .sub-menu');
	if( $elements_to_check.length > 0 ) {
		$elements_to_check.each(function() {
			// Find only direct children
			$children_count = $(this).children('.menu-item').length;
			$(this).closest('.menu-item').attr('data-children-count-r', $children_count);
		});
	}


	
	// Parent: .desktop-menu .menu > .menu-item > .sub-menu > .container > .menu-item > .sub-menu
	// Child: .desktop-menu .menu > .menu-item > .sub-menu > .container > .menu-item > .sub-menu > .menu-item

	// Check every parent width and compare it to the sum of the children widths.
	// If the parent width is smaller than the sum of the children widths, add the class 'ipo-has-overflowing-children'
	$(window).on('load resize', function() {

	
		$parent_elements = $('.desktop-menu .menu > .menu-item > .sub-menu > .container > .menu-item > .sub-menu');
		$parent_elements.each(function() {
			$parent_width = $(this).width();
			$children_width = 0;
			$(this).children('.sub-menu').each(function() {
				$children_width += $(this).width();
			});
		
			// Allow 20% margin of error
			$parent_width = $parent_width * 1.2;
			if ( $parent_width < $children_width ) {
				$(this).closest('.menu-item').addClass('ipo-has-overflowing-children');
			} else {
				$(this).closest('.menu-item').removeClass('ipo-has-overflowing-children');
			}

			/*

			*/
		});
	});

	
});


// ============================================================
// #20 | Toggle (ID: 29544 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
    $('.toggle_link > a').on('click', function(e){
		e.preventDefault();
		$(this).closest('.toggle_link').find('.toggle-content').slideToggle();

	});
});


// ============================================================
// #21 | Sticky Sidebar (ID: 29436 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
    if ($('.sections-nav').length) {
        $headerHeight = 0;
        $wpadminbarHeight = 0;
    
        if ($('header').length) {
            $headerHeight = $('header').height();
        }
    
        if ($('#wpadminbar').length) {
            $wpadminbarHeight = $('#wpadminbar').height();
        }

        var Sticky = new hcSticky('.sections-nav', {
            innerSticker: '.sections-nav li',
            top: $headerHeight + $wpadminbarHeight + 20,
            bottomEnd: $headerHeight + $wpadminbarHeight + 40,
            followScroll: false,
            responsive: {
                1199: {
                    top: $headerHeight + $wpadminbarHeight + 20,
                    bottomEnd: $headerHeight + $wpadminbarHeight + 40,
                },
                768: {
                    disable: true
                }
            }
        });
	}
	
	// orchestra team page 
	if ($('.panel-group').length) {
        $headerHeight = 0;
        $wpadminbarHeight = 0;
    
        if ($('header').length) {
            $headerHeight = $('header').height();
        }
    
        if ($('#wpadminbar').length) {
            $wpadminbarHeight = $('#wpadminbar').height();
        }

        var Sticky = new hcSticky('.panel-group', {
            innerSticker: '.panel-group .panel',
            top: $headerHeight + $wpadminbarHeight + 20,
            bottomEnd: $headerHeight + $wpadminbarHeight + 40,
            followScroll: false,
            responsive: {
                1199: {
                    top: $headerHeight + $wpadminbarHeight + 20,
                    bottomEnd: $headerHeight + $wpadminbarHeight + 40,
                },
                768: {
                    disable: true
                }
            }
        });
	}
});


// ============================================================
// #22 | Short descriptions (ID: 29037 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
        var text = $('.details-para').text();
        var textLength = text.length;
        var max_len = 50;
        if (textLength > max_len) {

            // Find len, the length of the text to be displayed after the closest space to the max_len
            var len = text.substr(0, max_len).lastIndexOf(' ');

            var originalText = $('.details-para').text();
            var firstPart = originalText.substring(0, len);
            var secondPart = originalText.substring(len, textLength);

            if($('html').attr('lang') == 'he-IL') {
                var btn_text_read_more = 'קרא עוד';
                var btn_text_read_less = 'קרא פחות';
            } else {
                var btn_text_read_more = 'Read more';
                var btn_text_read_less = 'Read less';
            }
            

            var newDiv = '<div class="firstPart">' + firstPart + '</div><span class="sep">...</span><div style="display:none;" class="secondPart">' + secondPart + '</div>' + '<div class="readMore">' + btn_text_read_more + '</div>';
            $('.details-para').html(newDiv);
        }
        $('.readMore').click(function() {
            if($(this).closest('.details-para').hasClass('showContent')) {
                $(this).closest('.details-para').removeClass('showContent');
                $(this).text(btn_text_read_more);
                //$('.secondPart').slideUp(200);
                $(this).closest('.details-para').find('.secondPart').css('display', 'none');
                
            } else {
                $(this).closest('.details-para').addClass('showContent');
                
                $(this).text(btn_text_read_less);
            
                //$('.secondPart').slideDown(200);
                $(this).closest('.details-para').find('.secondPart').css('display', 'inline');
            }
        });



    });


// ============================================================
// #23 | Header menu (ID: 28201 | type: js)
// ============================================================

jQuery(document).ready(function( $ ){
	// Basic throttle function
	// Check if defined
	if (typeof throttle !== 'function') {
	function throttle(fn, threshhold, scope) {
		threshhold || (threshhold = 250);
		var last,
			deferTimer;
		return function () {
			var context = scope || this;

			var now = +new Date,
				args = arguments;
			if (last && now < last + threshhold) {
				// hold on to it
				clearTimeout(deferTimer);
				deferTimer = setTimeout(function () {
					last = now;
					fn.apply(context, args);
				}, threshhold);
			} else {
				last = now;
				fn.apply(context, args);
			}
		};
	}
	}

	// fn to set the menu position
	function setMenuPosition() {
		$('.desktop-menu .menu > li > ul.sub-menu').each(function () {

			$(this).css('right', 'unset');
			$(this).css('top', 'unset');

			$headerWidth = $('header').width();
			$headerHeight = $('header').height();

			$(this).css('width', $headerWidth);

			$top = $(this).offset().top;
			$left = $(this).offset().left;

			$(this).css('right', $left);
			$(this).css('top', $headerHeight);
		});
	}

	// Call the function on resize or chrome responsive mode change
	$(window).on('resize', throttle(setMenuPosition, 250));
	// Call the function when menu item is hovered
	$('.desktop-menu .menu > li').on('mouseenter', setMenuPosition);


});


// ============================================================
// #25 | Aviad JS (ID: 489 | type: js)
// ============================================================

/* Add your JavaScript code here.

If you are using the jQuery library, then don't forget to wrap your code inside jQuery.ready() as follows:

jQuery(document).ready(function( $ ){
    // Your code in here
});

--

If you want to link a JavaScript file that resides on another server (similar to
<script src="https://example.com/your-js-file.js"></script>), then please use
the "Add HTML Code" page, as this is a HTML code that links a JavaScript file.

End of comment */ 



       var inputElement = document.querySelector('[lang="en-US"] input[name="input_3"]');
    if (inputElement) { inputElement.setAttribute('placeholder', 'Full name'); }


// isScrolledIntoView removed here — use the canonical definition in anim-base.js,
// which supports the $extra offset param that callers (anim-reveal.js: 120/900) pass.
// This duplicate dropped the param and silently broke reveal thresholds site-wide.





jQuery(document).ready(function($) {
  $('img[title]').each(function() { $(this).removeAttr('title'); });
});

jQuery(document).ready(function($) {
  $('.bg_set').each(function() { $(this).removeAttr('title'); });
});


