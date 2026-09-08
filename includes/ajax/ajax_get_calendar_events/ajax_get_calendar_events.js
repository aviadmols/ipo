jQuery(window).on('load', function() {

    (function($) {

        var calendarRequest = null;

        var monthNamesHe = [
            'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
            'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'
        ];
        var monthNamesEn = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        function isHebrew() {
            var lang = ($('html').attr('lang') || '').toLowerCase();
            return lang === 'he' || lang === 'he-il' || lang.indexOf('he') === 0;
        }

        function monthLabel(month, year) {
            var names = isHebrew() ? monthNamesHe : monthNamesEn;
            var idx = parseInt(month, 10) - 1;
            if (idx < 0 || idx > 11) {
                return month + ' ' + year;
            }
            return names[idx] + ' ' + year;
        }

        function updateDateLabelOptimistic($container, month, year) {
            var $dateEl = $container.find('.current-month .rendered-date').first();
            if (!$dateEl.length) {
                $container.find('.current-month .date').html(
                    '<p class="rendered-date date-il8n" data-t="1" data-month="' + month + '" data-year="' + year + '">' +
                    monthLabel(month, year) + '</p><div id="monthsPopup" style="display:none;"></div>'
                );
                return;
            }
            $dateEl.attr('data-month', month).attr('data-year', year);
            var $svg = $dateEl.children('svg').detach();
            $dateEl.text(monthLabel(month, year) + ' ');
            if ($svg.length) {
                $dateEl.append($svg);
            }
        }

        function showCalendarSkeleton($container) {
            var skeleton = '';
            for (var i = 0; i < 35; i++) {
                skeleton += '<li class="loop-day skeleton-day"><div class="contents"><label><span></span></label></div></li>';
            }
            $container.find('.calendar-days').html(skeleton);
            $container.find('.calendar-events ul.events').empty();
            $container.find('.calendar-events .no-results').hide();
        }

        window.request_calendar_events = function(month, year) {

            var $ajax_container = $('.ipo-calendar.calendar-full');
            if (!$ajax_container.length) {
                return;
            }

            month = parseInt(month, 10);
            year = parseInt(year, 10);
            if (!month || !year) {
                return;
            }

            if (calendarRequest && calendarRequest.readyState !== 4) {
                calendarRequest.abort();
            }

            $ajax_container.addClass('loading');
            if (typeof window.closeCalendarPopups === 'function') {
                window.closeCalendarPopups($ajax_container);
            }
            updateDateLabelOptimistic($ajax_container, month, year);
            showCalendarSkeleton($ajax_container);

            var $calendar_type = $ajax_container.attr('data-calendar-type') || 'normal';

            var $data = {
                action: 'ajax_get_calendar_events',
                month: month,
                year: year,
                calendar_type: $calendar_type
            };

            var $url = '?month=' + month + '&y=' + year + '&layout=' + encodeURIComponent($calendar_type);
            window.history.pushState({}, '', $url);

            calendarRequest = $.ajax({
                url: ajax_get_calendar_events.ajaxurl,
                type: 'get',
                dataType: 'json',
                data: $data,
                success: function(response) {

                    var $resp = response.data;

                    $('.calendar-full .calendar-events .no-results').hide();

                    $('.calendar-full .current-month a.prev').attr('data-month', $resp.prev_month);
                    $('.calendar-full .current-month a.prev').attr('data-year', $resp.prev_year);
                    $('.calendar-full .current-month a.next').attr('data-month', $resp.next_month);
                    $('.calendar-full .current-month a.next').attr('data-year', $resp.next_year);

                    $('.calendar-full .current-month a.prev').attr('href', $resp.prev_month_url);
                    $('.calendar-full .current-month a.next').attr('href', $resp.next_month_url);

                    $('.calendar-full .calendar-header .date').html($resp.current_date);

                    $('.calendar-full .calendar-days').html($resp.days);

                    $('.calendar-full .calendar-events ul.events').html($resp.list_events_html);

                    if (!$resp.list_events_html) {
                        $('.calendar-full .calendar-events .no-results').show();
                    }

                    $ajax_container.removeClass('loading');

                    if (typeof reveal_events === 'function') {
                        reveal_events($ajax_container);
                    }

                },
                error: function(xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }
                    $ajax_container.removeClass('loading');
                }
            });

        };

        $(document).on('click', '.calendar-full .current-month a.prev, .calendar-full .current-month a.next', function(e) {
            e.preventDefault();

            var $month = $(this).attr('data-month');
            var $year = $(this).attr('data-year');

            window.request_calendar_events($month, $year);
        });

    })(jQuery);

});
