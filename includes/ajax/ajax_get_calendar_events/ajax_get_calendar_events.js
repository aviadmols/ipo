jQuery(window).on('load', function() {

    (function($) {

        function request_calendar_events(month, year) {

            $ajax_container = $('.ipo-calendar.calendar-full');
            $ajax_container.addClass('loading');

            $calendar_type = $ajax_container.attr('data-calendar-type');

            $data = {
                action: 'ajax_get_calendar_events',
                month: month,
                year: year,
                calendar_type: $calendar_type

            };

            // build url query string from month, year and calendar type
            $url = '?month=' + month + '&y=' + year + '&layout=' + $calendar_type;

            // update url
            window.history.pushState({}, '', $url);


            $.ajax({
                url: ajax_get_calendar_events.ajaxurl,
                type: "get",
                dataType: "json",
                data: $data,
                success: function(response) {

                    $data = response.data;

                    $('.calendar-full .calendar-events .no-results').hide();

                    $('.calendar-full .current-month a.prev').attr('data-month', $data.prev_month);
                    $('.calendar-full .current-month a.prev').attr('data-year', $data.prev_year);
                    $('.calendar-full .current-month a.next').attr('data-month', $data.next_month);
                    $('.calendar-full .current-month a.next').attr('data-year', $data.next_year);

                    $('.calendar-full .current-month a.prev').attr('href', $data.prev_month_url);
                    $('.calendar-full .current-month a.next').attr('href', $data.next_month_url);

                    $('.calendar-full .calendar-header .date').html($data.current_date);

                    $('.calendar-full .calendar-days').html($data.days);

                    $('.calendar-full .calendar-events ul.events').html($data.list_events_html);

                    if ($data.list_events_html == '') {
                        $('.calendar-full .calendar-events .no-results').show();
                    }


                    $ajax_container.removeClass('loading');

                    reveal_events($ajax_container);


                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log('AJAX Failed ' + xhr.status);
                    console.log(xhr.responseText);
                    $ajax_container.removeClass('loading');

                }
            });


        }

        $(document).on('click', '.page-template-template-calendar-full-ajax .calendar-full .current-month a', function(e) {
            e.preventDefault();

            $month = $(this).attr('data-month');
            $year = $(this).attr('data-year');

            request_calendar_events($month, $year);
        });

    })(jQuery);

});