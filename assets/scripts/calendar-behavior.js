jQuery(document).ready(function($) {


    const $splide_calendar = new Splide('.calendar-row', {
        breakpoints: {
            500: {
                perPage: 5,
            },
        }
    }).mount();

    // $('body').on('click', '.calendar-day[data-events]:not([data-events=""]) label', function() {
    //     $(this).closest('.ipo-calendar').find('.calendar-day').removeClass('active');
    //     $(this).closest('.calendar-day').addClass('active');
    // });

    $('body').on('click', '.calendar-day[data-events] label', function() {
        $(this).closest('.ipo-calendar').find('.calendar-day').removeClass('active');
        $(this).closest('.calendar-day').addClass('active');
        // Skip auto-scroll when this click was triggered by code on load (user didn't click).
        if ($('.calendar_area').data('programmatic-click')) return;
        // Also scroll to the selected day with 150px offset. Only on mobile.
        if ($(window).width() < 500) {
            $('html, body').animate({
                scrollTop: $(this).closest('.calendar-day').offset().top - 100
            }, 500);
        }
    });
    $('.calendar_area .ipo-calendar ul.numCount .active').removeClass('active');


    // Run select_next_day_with_events() 2.5s after page load; then keep page at top (no scroll to calendar).
    setTimeout(function() {
        var scrollY = window.scrollY || window.pageYOffset;
        select_next_day_with_events('today');
        setTimeout(function() {
            window.scrollTo(0, Math.min(scrollY, 10));
        }, 350);
    }, 2500);

    /*
    $(document).on("item-revealed", {}, function(event, element) {

        //console.log('item-revealed');
        //console.log(element);
        if ($(element).hasClass('calendar_area')) {

            setTimeout(function() {



                $selected_item = $(element).find('.calendar-day.future[data-events]:not([data-events=""]):first');

                console.log('auto selection set');
                console.log($selected_item);

                $selected_item.addClass('active');
                $index = $selected_item.index();
                $splide_calendar.go($index);
                $ajax_container = $('.ajax-calendar-events');
                reveal_events($ajax_container);

            }, 500);


        }


    });
	*/



});

function select_next_day_with_events($type = 'start') {

    const $splide_calendar = new Splide('.calendar-row', {
        breakpoints: {
            500: {
                perPage: 5,
            },
            380: {
                perPage: 4,
            },
        }
    }).mount();


    if ($type == 'start') {
        // Loads the first day of the month
        $selected_item = $('body').find('.calendar_area .calendar-day:first');
    } else if ($type == 'first-event') {
        // Loads the first event of the month
        $selected_item = $('body').find('.calendar_area .calendar-day.future[data-events]:not([data-events=""]):first');
    } else if ($type == 'today') {
        // If today is in the current month, load today
        if ($('body').find('.calendar_area .calendar-day.current-day').length > 0) {
            $selected_item = $('body').find('.calendar_area .calendar-day.current-day');
        } else {
            // If today is not in the current month, load the first day of the month
            $selected_item = $('body').find('.calendar_area .calendar-day:first');
        }
    }


    // $selected_item = $('body').find('.calendar_area .calendar-day.future[data-events]:not([data-events=""]):first');
    // $selected_item = $('body').find('.calendar_area .calendar-day:first');
    console.log('auto selection set');
    //console.log($selected_item);
    $selected_item.addClass('active');
    $index = $selected_item.index();

    console.log('index: ' + $index);


    $splide_calendar.go($index);
    $('.calendar_area').data('programmatic-click', true);
    $('.calendar_area .calendar-day:nth-child(' + ($index + 1) + ')').click();
    setTimeout(function() { $('.calendar_area').removeData('programmatic-click'); }, 600);
    $ajax_container = $('.ajax-calendar-events');

    // reveal_events($ajax_container);


    $events = $selected_item.attr('data-events');

    // trigger activated event
    $(document).trigger('item_activated', $events);

}

function reveal_events($container) {
    //console.log('reveal_events triggered');
    $reveal_delay = 500;
    $i = 1;



    $container.find('.loop-calendar-horizontal-event').each(function(k, v) {
        var el = this;
        var speed = 500;

        const $splide_calendar = new Splide('.calendar-row', {
            breakpoints: {
                500: {
                    perPage: 5,
                },
            }
        }).mount();

        $c = k + 1;
        setTimeout(function() {

            jQuery(el).addClass('revealed');
            console.log('revealed ' + k + ' with delay ' + $c * speed);

        }, $c * speed);
    });



}


/* SWITCHER */

jQuery(document).ready(function($) {

    // If the page is loaded with ?layout parameter set, add it to the .current-month a links
    if (window.location.href.indexOf('?layout=') > -1) {
        $('.current-month a').each(function() {
            var url = $(this).attr('href');
            var $val = window.location.href.split('?layout=')[1];
            // Check if the URL already has a ?layout parameter
            if (url.indexOf('?layout=') > -1) {
                // Replace the ?layout parameter with the current one
                $(this).attr('href', url.replace(/(layout=)[^\&]+/, '$1' + $val));
            } else {
                // Add the ?layout parameter
                $(this).attr('href', url + '&layout=' + $val);
            }
        });
    }


    $('body').on('click', '.switch-container[data-selected-option] a', function() {

        $switch_container = $(this).closest('.switch-container[data-selected-option]');
        $selected_option = $switch_container.attr('data-selected-option');
        if ($selected_option == '1') {
            $switch_container.attr('data-selected-option', '0');
            $('.calendar-full').attr('data-calendar-type', 'normal');

            var url = new URL(window.location.href);
            url.searchParams.set('layout', 'normal');
            window.history.pushState({}, '', url);

            // Add ?layout=normal to URL (if not already there) on .current-month a
            $('.current-month a').each(function() {
                var url = $(this).attr('href');
                var $val = 'normal';
                // Check if the URL already has a ?layout parameter
                if (url.indexOf('?layout=') > -1) {
                    // Replace the ?layout parameter with the current one
                    $(this).attr('href', url.replace(/(layout=)[^\&]+/, '$1' + $val));
                } else {
                    // Add the ?layout parameter
                    $(this).attr('href', url + '&layout=' + $val);
                }
            });



        } else { // 0
            $switch_container.attr('data-selected-option', '1');
            $('.calendar-full').attr('data-calendar-type', 'events');

            var url = new URL(window.location.href);
            url.searchParams.set('layout', 'events');
            window.history.pushState({}, '', url);

            // Add ?layout=events to URL (if not already there) on .current-month a
            $('.current-month a').each(function() {
                var url = $(this).attr('href');
                var $val = 'events';
                // Check if the URL already has a ?layout parameter
                if (url.indexOf('?layout=') > -1) {
                    // Replace the ?layout parameter with the current one
                    $(this).attr('href', url.replace(/(layout=)[^\&]+/, '$1' + $val));
                } else {
                    // Add the ?layout parameter
                    $(this).attr('href', url + '&layout=' + $val);
                }
            });

        }

    });

});