jQuery(window).on('load', function() {

    (function($) {

        function request_events_ajax($events) {

            $ajax_container = $('.ajax-calendar-events');
            $ajax_container.addClass('loading');
            $ajax_container.closest('.calendar_area').addClass('section-loading');

            $data = {
                action: 'ajax_get_events',
                events: $events,
            };

            console.log('starting get_events ajax for events:' + $events);

            $.ajax({
                url: ajax_get_events.ajaxurl,
                type: "get",
                dataType: "json",
                data: $data,
                success: function(response) {

                    console.log('AJAX success, messages ' + response.message);

                    $ajax_container.html(response.data.content);

                    if (!response.data.count) {
                        $ajax_container.addClass('no-more-results');
                    }

                    reveal_events($ajax_container);

                    //$ajax_container.removeClass('loading');
                    $ajax_container.closest('.calendar_area').removeClass('section-loading');

                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log('AJAX Failed ' + xhr.status);
                    console.log(xhr.responseText);
                    $ajax_container.removeClass('loading');
                    $ajax_container.closest('.calendar_area').removeClass('section-loading');

                }
            });


        }



        $('body').on('click', '[data-ajax-trigger][data-events] label', function(e) {
            console.log('clicked');
            $events = $(this).closest('[data-ajax-trigger][data-events]').attr('data-events');

            console.log($events);
            // if ($events)
            //     request_events_ajax($events);


            toggleDaysEvents($events);

        });

        $(document).on('click', '.calendar_area .splide__arrow[disabled] svg', function(e) {
            //$(document).on('click', '.calendar_area .splide__arrow svg', function(e) {

            // ---- ADDED ----
            // Check if .splide__list, which is the child of .splide__track is at the edge of the container
            // If it is, then we need to load the next month
            /*
            $parent = $(this).closest('.splide__arrow');
            $splide_track = $(this).closest('.splide__track');
            $splide_list = $splide_track.find('.splide__list');

            $splide_list_width = $splide_list.width();
            $splide_track_width = $splide_track.width();

            //$splide_list_left = $splide_list.position().left;
            // Find different solution. $splide_list is not defined
            $splide_list_left = $splide_track.position().left;
            $splide_list_right = $splide_list_left + $splide_list_width;

            $splide_track_left = $splide_track.position().left;
            $splide_track_right = $splide_track_left + $splide_track_width;

            console.log('splide_list_left ' + $splide_list_left);
            console.log('splide_list_right ' + $splide_list_right);
            console.log('splide_track_left ' + $splide_track_left);
            console.log('splide_track_right ' + $splide_track_right);

            if ($parent.hasClass('splide__arrow--prev') && $splide_list_left >= $splide_track_left) {
                e.preventDefault();
                console.log('prev');
                $('.calendar_area .ajax-get-month-trigger .prev.trigger').trigger('click');
                $parent.addClass('load-next');

            } else if ($parent.hasClass('splide__arrow--next') && $splide_list_right <= $splide_track_right) {
                e.preventDefault();
                console.log('next');
                $('.calendar_area .ajax-get-month-trigger .next.trigger').trigger('click');
                $parent.addClass('load-next');

            }


            */
            // ---- ----- ----


            $parent = $(this).closest('.splide__arrow');

            if ($parent.hasClass('splide__arrow--prev') && $parent.hasClass('load-next')) {
                $('.calendar_area .ajax-get-month-trigger .prev.trigger').trigger('click');
            } else if ($parent.hasClass('splide__arrow--next') && $parent.hasClass('load-next')) {
                $('.calendar_area .ajax-get-month-trigger .next.trigger').trigger('click');
            }

            $parent.addClass('load-next');

        });

    })(jQuery);

});

function toggleDaysEvents($events) {

    console.log('running toggleDaysEvents');

    $ajax_container = $('.ajax-calendar-events');
    $ajax_container.addClass('loading');
    $ajax_container.closest('.calendar_area').addClass('section-loading');

    if ($events) {

        // hide .events-no-results
        $('.calendar_area .events-no-results').hide();

        $i = 1;

        $events = $events.split(',');

        console.log($events);


        $ajax_container.find('.loop-calendar-horizontal-event').each(function(k, v) {


            //if ($events.indexOf($(this).attr('data-event_id')) == -1) {
            // If the event is not in the list of events to show, hide it. 
            // The 'data-event_id' can be ab implode of multiple event ids, so we need to check each one.

            $event_id = $(this).attr('data-event_id');
            // Check if coma separated list
            if ($event_id.indexOf(',') != -1) {
                $event_id = $event_id.split(',');
            } else {
                $event_id = [$event_id];
            }

            $event_id = $event_id.filter(function(e) {
                return e
            });

            $show = false;

            $event_id.forEach(function($id) {

                if ($events.indexOf($id) != -1) {

                    $show = true;

                }

            });

            if (!$show) {

                $(this).hide();
                $(this).removeClass('revealed');

            } else {

                console.log('showing ' + $(this).attr('data-event_id'));

                $(this).show();


                var el = this;
                var speed = 200;

                $c = $i + 1;

                // if(window.innerWidth > 1600){
                // $ajax_container.removeClass('loading');
                // $ajax_container.closest('.calendar_area').removeClass('section-loading');

                // remove load-next class from arrows
                $('.calendar_area .splide__arrow').removeClass('load-next');
                //    }

                jQuery(el).addClass('revealed');
                console.log('revealed ' + $i + ' with delay ' + $c * speed);


                $i++;
            }





        });



        // unslick first 
        if ($('.calendar-slider').hasClass('slick-initialized')) {
            $('.calendar-slider').slick('unslick');
        }

        $ajax_container.addClass('loading');

        setTimeout(() => {

            $ajax_container.closest('.calendar_area').addClass('section-loading');

            calendar_slider();

            $('.calendar-slider .loop-calendar-horizontal-event:not(.revealed)').hide();

            $ajax_container.removeClass('loading');
            $ajax_container.closest('.calendar_area').removeClass('section-loading');

            // remove load-next class from arrows
            $('.calendar_area .splide__arrow').removeClass('load-next');

        }, 1000);

    } else {
        console.log('no events');

        // unslick first 
        if ($('.calendar-slider').hasClass('slick-initialized')) {
            $('.calendar-slider').slick('unslick');
        }

        setTimeout(() => {

            // hide all events
            $ajax_container.find('.loop-calendar-horizontal-event').hide();
            $ajax_container.find('.loop-calendar-horizontal-event').removeClass('revealed');

            $ajax_container.removeClass('loading');
            $ajax_container.closest('.calendar_area').removeClass('section-loading');

            // remove load-next class from arrows
            $('.calendar_area .splide__arrow').removeClass('load-next');

            // show .events-no-results
            $('.calendar_area .events-no-results').show();

        }, 1000);

    }


}

function calendar_slider() {

    console.log('running calendar_slider');
    $rtl = true;
    // Check html lang
    if ($('html').attr('lang') == 'en-US') {
        $rtl = false;
    }
    // Guard against double-init: re-initializing Slick on an already
    // initialized element skips buildOut() and leaves $slides null, which
    // throws "initADA: Cannot read properties of null (reading 'add')".
    if ($('.calendar-slider').hasClass('slick-initialized')) {
        $('.calendar-slider').slick('unslick');
    }
    $('.calendar-slider').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        // autoplay: true,
        autoplaySpeed: 2000,
        arrows:  true,
  nav: true,
    navText: [
                    '<img src="/wp-content/uploads/2022/09/arrow_right.svg" alt="">',
                    '<img src="/wp-content/uploads/2022/09/arrow_left.svg" alt="">'
                ],
infinite: false,
        dots: false,
        rtl: $rtl,
        slide: '.revealed',
        variableWidth: true,
        swipe: true,
        responsive: [{
                breakpoint: 990,
                settings: {
                    slidesToShow: 2,
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 1,
                    variableWidth: false,
                }
            }
        ]
    });

}