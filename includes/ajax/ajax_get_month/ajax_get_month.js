jQuery(window).on('load', function() {

    (function($) {

        function request_month_ajax($month, $year) {

            $ajax_container = $('.ajax-calendar-row');
            $ajax_container.addClass('loading');

            $data = {
                action: 'ajax_get_month',
                month: $month,
                year: $year,
            };

            console.log('starting get_month ajax for month:' + $month);

            $('.ajax-get-month-trigger').addClass('date-loading');
            setTimeout(function() {

                // Make $month and $year a string
                $month_str = $month.toString();
                $year_str = $year.toString();

                // Add a leading zero to $month_str
                if ($month_str.length < 2) {
                    $month_str = '0' + $month_str;
                }

                // Check if the html language is set to herbew. After that format the date from $month and $year to look like: Sep 2022
                /*
                if ($('html').attr('lang') == 'he-IL') {
                    $month_str = $month_str.replace('01', 'ינו');
                    $month_str = $month_str.replace('02', 'פבר');
                    $month_str = $month_str.replace('03', 'מרץ');
                    $month_str = $month_str.replace('04', 'אפר');
                    $month_str = $month_str.replace('05', 'מאי');
                    $month_str = $month_str.replace('06', 'יוני');
                    $month_str = $month_str.replace('07', 'יולי');
                    $month_str = $month_str.replace('08', 'אוג');
                    $month_str = $month_str.replace('09', 'ספט');
                    $month_str = $month_str.replace('10', 'אוק');
                    $month_str = $month_str.replace('11', 'נוב');
                    $month_str = $month_str.replace('12', 'דצמ');
                } else {
                    $month_str = $month_str.replace('01', 'Jan');
                    $month_str = $month_str.replace('02', 'Feb');
                    $month_str = $month_str.replace('03', 'Mar');
                    $month_str = $month_str.replace('04', 'Apr');
                    $month_str = $month_str.replace('05', 'May');
                    $month_str = $month_str.replace('06', 'Jun');
                    $month_str = $month_str.replace('07', 'Jul');
                    $month_str = $month_str.replace('08', 'Aug');
                    $month_str = $month_str.replace('09', 'Sep');
                    $month_str = $month_str.replace('10', 'Oct');
                    $month_str = $month_str.replace('11', 'Nov');
                    $month_str = $month_str.replace('12', 'Dec');
                }
                */

                // Same as above but with the full month name
                if ($('html').attr('lang') == 'he-IL') {
                    $month_str = $month_str.replace('01', 'ינואר');
                    $month_str = $month_str.replace('02', 'פברואר');
                    $month_str = $month_str.replace('03', 'מרץ');
                    $month_str = $month_str.replace('04', 'אפריל');
                    $month_str = $month_str.replace('05', 'מאי');
                    $month_str = $month_str.replace('06', 'יוני');
                    $month_str = $month_str.replace('07', 'יולי');
                    $month_str = $month_str.replace('08', 'אוגוסט');
                    $month_str = $month_str.replace('09', 'ספטמבר');
                    $month_str = $month_str.replace('10', 'אוקטובר');
                    $month_str = $month_str.replace('11', 'נובמבר');
                    $month_str = $month_str.replace('12', 'דצמבר');
                } else {
                    $month_str = $month_str.replace('01', 'January');
                    $month_str = $month_str.replace('02', 'February');
                    $month_str = $month_str.replace('03', 'March');
                    $month_str = $month_str.replace('04', 'April');
                    $month_str = $month_str.replace('05', 'May');
                    $month_str = $month_str.replace('06', 'June');
                    $month_str = $month_str.replace('07', 'July');
                    $month_str = $month_str.replace('08', 'August');
                    $month_str = $month_str.replace('09', 'September');
                    $month_str = $month_str.replace('10', 'October');
                    $month_str = $month_str.replace('11', 'November');
                    $month_str = $month_str.replace('12', 'December');
                }

                $rendered = $month_str + ' ' + $year_str;


                $('.ajax-get-month-trigger .rendered-date').attr('data-month', $month);
                $('.ajax-get-month-trigger .rendered-date').attr('data-year', $year);
                $('.ajax-get-month-trigger .rendered-date').text($rendered);
                setTimeout(function() {
                    $('.ajax-get-month-trigger').removeClass('date-loading');
                }, 100);
            }, 500);



            $.ajax({
                url: ajax_get_month.ajaxurl,
                type: "get",
                dataType: "json",
                data: $data,
                success: function(response) {

                    console.log('AJAX success, messages: ' + response.message);

                    $ajax_container.html(response.data.content);
                    //$('.ajax-get-month-trigger .rendered-date').attr('data-month',response.data.month);
                    //$('.ajax-get-month-trigger .rendered-date').attr('data-year',response.data.year);
                    //$('.ajax-get-month-trigger .rendered-date').text(response.data.rendered_date);

                    $splide_calendar = new Splide('.calendar-row').mount();
                    //setTimeout(function() {
                    //$(document).trigger("item-revealed", $(this));

                    //console.log('item-revealed');
                    //}, 100);

                    $ajax_container.removeClass('loading');

                    select_next_day_with_events();

                    // find [data-full-calendar-link] and add the current month and year to the link href as query string parameters: ?month=1&y=2021
                    $full_calendar_link = $('[data-full-calendar-link]');
                    $full_calendar_link_href = $full_calendar_link.attr('href');
                    // Remove the query string parameters from the href
                    $full_calendar_link_href = $full_calendar_link_href.split('?')[0];
                    // Add the query string parameters to the href
                    $full_calendar_link_href = $full_calendar_link_href + '?month=' + $month + '&y=' + $year;
                    $full_calendar_link.attr('href', $full_calendar_link_href);


                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log('AJAX Failed ' + xhr.status);
                    console.log(xhr.responseText);
                    $ajax_container.removeClass('loading');

                }
            });


        }

        $('body').on('click', '.ajax-get-month-trigger .trigger', function(e) {

            e.preventDefault();


            $month = $(this).parent().find('.rendered-date').attr('data-month');
            $year = $(this).parent().find('.rendered-date').attr('data-year');

            console.log('clicked. Current date calculated: ' + $month + '/' + $year);

            if ($(this).hasClass('next')) {
                $month++;
            } else if ($(this).hasClass('prev')) {
                $month--;
            }

            if ($month < 1) {
                $month = 12;
                $year--;
            }

            if ($month > 12) {
                $month = 1;
                $year++;
            }

            if ($month && $year)
                request_month_ajax($month, $year);
        });

    })(jQuery);

});