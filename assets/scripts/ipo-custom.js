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

    jQuery('.slider-banner.desktop').slick({
        centerMode: true,
        infinite: true,
      margin: 30,
        slidesToShow: 2,
        slidesToScroll: 1
    });

    jQuery('.slider-banner.mobile').slick({
         infinite:  false,
rtl:  true,
  centerMode:  false,
        slidesToShow: 1.5,
        slidesToScroll: 1
    });

});


// ============================================================
// #4 | Months Popup EN (ID: 42339 | type: js)
// ============================================================

$(document).ready(function() {
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth() + 1;

    var months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    function renderMonthsList() {
        var monthsList = '<ul>';
        for(var offset = 0; offset < 12; offset++) {
            var monthIndex = (currentMonth - 1 + offset) % 12;
            var displayMonth = monthIndex + 1;
            var displayYear = (monthIndex < currentMonth - 1) ? currentYear + 1 : currentYear;
            var currentClass = (monthIndex + 1 === currentMonth) ? ' class="current-month"' : '';
            monthsList += '<li data-month="' + displayMonth + '" data-year="' + displayYear + '"' + currentClass + '>' + months[monthIndex] + '</li>';
        }
        monthsList += '</ul>';
        $("#monthsPopup").html(monthsList);
        $("#monthsPopup").css("display", "block");
    }

    $(".rendered-date").on("click", function() {
        renderMonthsList();
    });

    $(document).on("click", function(event) {
        if (!$(event.target).closest('.rendered-date').length && !$(event.target).closest('#monthsPopup').length) {
            $("#monthsPopup").css("display", "none");
        }
    });

    $(document).on("click", "#monthsPopup li", function() {
        var month = $(this).data("month");
        var year = $(this).data("year");
        window.location.href = "/en/calendar/?month=" + month + "&y=" + year;
    });

});


// ============================================================
// #5 | Months Popup (ID: 41346 | type: js)
// ============================================================

$(document).ready(function() {
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth() + 1;

    var months = [
        'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
        'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'
    ];

    $(".rendered-date").on("click", function() {
        renderMonthsList();
    });

    $(document).on("click", function(event) {
        if(!$(event.target).closest('.rendered-date').length && !$(event.target).closest('#monthsPopup').length) {
            $("#monthsPopup").css("display", "none");
        }
    });

    $(document).on("click", "#monthsPopup li", function() {
        var month = $(this).data("month");
        var year = $(this).data("year");
        window.location.href = "/לוח-שנה/?month=" + month + "&y=" + year;
    });

    function renderMonthsList() {
        var monthsList = '<ul>';
        for(var offset = 0; offset < 12; offset++) {
            var monthIndex = (currentMonth - 1 + offset) % 12;
            var displayMonth = monthIndex + 1;
            var displayYear = (monthIndex < currentMonth - 1) ? currentYear + 1 : currentYear;
            var currentClass = (monthIndex + 1 === currentMonth) ? ' class="current-month"' : '';
            monthsList += '<li data-month="' + displayMonth + '" data-year="' + displayYear + '"' + currentClass + '>' + months[monthIndex] + '</li>';
        }
        monthsList += '</ul>';
        $("#monthsPopup").html(monthsList);
        $("#monthsPopup").css("display", "block");
    }

});


// ============================================================
// #6 | Page Program JS (ID: 41342 | type: js)
// ============================================================

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
      const buttonText = button.textContent.trim();
      const programInfoDiv = button.closest('.program-info');

      if (programInfoDiv.classList.contains('show-read-more')) {
        programInfoDiv.classList.remove('show-read-more');
        readMoreButton.textContent = 'קרא עוד';
        readMoreButton.classList.remove('read-less');
      }
    }
  });
});


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

jQuery(document).ready(function ($) {
	jQuery('.slider-banner.desktop').slick({
		centerMode: true,
		infinite: true,
		margin: 30,
		slidesToShow: 2,
		slidesToScroll: 1
	});

	jQuery('.slider-banner.mobile').slick({
		infinite: false,
		rtl: true,
		centerMode: false,
		slidesToShow: 1.5,
		slidesToScroll: 1
	});
});


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
    // If .additionalDates is clicked, show the all .additional-date items and hide the .additionalDates button
    $('.additionalDates').click(function(e){
		e.preventDefault();
        $(this).parent().find('.additional-date').fadeIn(300).css("display","flex");
		$(this).parent().find('.additionalDates').fadeOut(300);
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
    inputElement.setAttribute('placeholder', 'Full name');


function isScrolledIntoView(elem) {
     var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return (elemTop-500<=docViewTop);

}





jQuery(document).ready(function($) {
  $('img[title]').each(function() { $(this).removeAttr('title'); });
});

jQuery(document).ready(function($) {
  $('.bg_set').each(function() { $(this).removeAttr('title'); });
});


