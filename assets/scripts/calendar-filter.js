$(document).ready(function() {

    function filterEvents() {
        console.log('start filter events');

        var filterText_desktop = $('.calendar-events-header > .search-field input.desktop-only').val().toLowerCase();
        var filterText_mobile = $('.calendar-events-header > .search-field input.mobile-only').val().toLowerCase();

        // Use depending on resolution, 768
        var filterText = filterText_desktop;
        if ($(window).width() < 768) {
            filterText = filterText_mobile;
        }


        var filterEventType = $('.filter-type li.active a').data('filter-val');
        var filterEventLocation = $('.filter-location li.active a').data('filter-val');
        var filterEventLocation_text = $('.filter-location li.active a').text();



        $('.loop-calendar-list-event').each(function() {

            // add newline to the console
            console.log('-');
            console.log('-');


            var eventText = $(this).find('.ipo-program-details h4').text().toLowerCase();
            var dataArtist = $(this).data('artist').toLowerCase();
            var event_type = $(this).data('event_type');
            var event_location = $(this).data('event_location');

            if (filterEventType == 'all') {
                event_type = 'all';
            }

            if (filterEventLocation == 'all') {
                event_location = 'all';
            }

            if (filterText.length < 3) {
                filterText = '';
            }

            var condition = false;

            /*
            Condition:
            We have 3 filters, text, event type and event location
            If any of the filters are set, then we need to check if the event matches the filter
            If the event matches the filter, then we show the event
            We break down the condition into 3 parts, text, event type and event location
            */

            console.log('working on event ' + eventText);
            // Starting with text.
            // If the filter text is empty, then we show all events
            if (filterText.length == 0) {
                condition = true;
                console.log('filterText is empty');
            } else {
                console.log('filterText is not empty');
                // If the filter text is not empty, then we check if the event text, artist or location matches the filter text
                if (eventText.indexOf(filterText) > -1 || dataArtist.indexOf(filterText) > -1 || event_location.indexOf(filterText) > -1) {
                    condition = true;
                    console.log('eventText or dataArtist or event_location matches filterText');
                } else {
                    console.log('eventText or dataArtist or event_location does not match filterText');
                    condition = false;
                }
            }

            // Now the event type
            // If the event type is not set to all, then we check if the event type matches the filter event type
            if (filterEventType != 'all') {
                console.log('filterEventType is not all');
                if (event_type == filterEventType) {
                    //condition = true;
                    console.log('event_type matches filterEventType');
                } else {
                    console.log('event_type does not match filterEventType');
                    condition = false;
                }
            } else {
                //condition = true;
                console.log('filterEventType is all');
            }

            // Now the event location
            // If the event location is not set to all, then we check if the event location matches the filter event location
            if (filterEventLocation != 'all') {
                console.log('filterEventLocation is not all');
                if (event_location == filterEventLocation) {
                    condition = true;
                    console.log('event_location matches filterEventLocation');
                } else {
                    // We have a special case for the event location filter
                    // Check if filterEventLocation_text is in the event location
                    if (filterEventLocation_text.length > 0 && event_location.indexOf(filterEventLocation_text) > -1) {
                        //condition = true;
                        console.log('event_location matches filterEventLocation_text');
                    } else {
                        console.log('event_location does not match filterEventLocation_text');
                        condition = false;
                    }

                }
            } else {
                //condition = true;
                console.log('filterEventLocation is all');
            }






            /*
            if ((eventText.indexOf(filterText) > -1 || dataArtist.indexOf(filterText) > -1 || dataArtist.indexOf(filterText) > -1) && event_type == filterEventType && event_location == filterEventLocation) {
                condition = true;
            }

            // Check if filterEventLocation text is in the event location
            if (filterEventLocation.length > 0 && event_location.indexOf(filterEventLocation) > -1) {
                condition = true;
            }

            // Check if filterEventLocation text is in the event location
            if (filterEventLocation_text.length > 0 && event_location.indexOf(filterEventLocation_text) > -1) {

                // If event type is anything but all, then check if it matches the filter
                if (filterEventType != 'all') {
                    if (event_type == filterEventType) {
                        condition = true;
                    }
                } else {
                    condition = true;
                }
            }
            */

            if (condition) {
                $(this).show();
                $(this).addClass('visible');
            } else {
                $(this).hide();
                $(this).removeClass('visible');
            }


        });

        if ($('.loop-calendar-list-event.visible').length > 0) {
            $('.no-results').hide();
        } else {
            $('.no-results').show();
        }

    }

    $('.calendar-events-header > .search-field input').on('keyup', function() {
        filterEvents();
    });


    $(document).on('click', '.filter .panel ul.options li', function() {
        $parent = $(this).closest('.filter');
        $parent.find('ul.options li').removeClass('active');
        $(this).addClass('active');
        $parent.addClass('selected');

        $val = $(this).find('a').data('filter-val');
        $text = $(this).find('a').text();


        $parent.find('.open-filter span').text($text);


        filterEvents();
    });

    $(document).on('click', '.filter .panel .close', function() {
        $(this).closest('.filter').removeClass('open');
    });

    $(document).on('click', '.calendar-events-header .filter > a', function() {
        $('.calendar-events-header .filter').removeClass('open');
        $(this).closest('.filter').addClass('open');
    });

    // hide filter when click outside of it
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter').length) {
            $('.filter').removeClass('open');
        }
    });

    // Search filter - X icon

    // Create invisible "x" icon
    $('.calendar-events-header > .search-field').append('<span style="display:none;" class="clear-search">x</span>');


    // Detect change on the .search-field input field
    $('.calendar-events-header > .search-field input').on('keyup', function() {
        // if content is not empty, show the "x" icon
        if ($(this).val().length > 0) {
            $('.calendar-events-header > .search-field .clear-search').show();
        } else {
            $('.calendar-events-header > .search-field .clear-search').hide();
        }
    });

    // Clear the search field when the "x" icon is clicked
    $('.calendar-events-header > .search-field .clear-search').on('click', function() {
        $('.calendar-events-header > .search-field input').val('');
        $(this).hide();
    });

});