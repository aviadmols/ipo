$(window).on('load resize', throttle(function() {
    if ($('.loop-event.time-horizontal').length) {
        // if exist and is an array, loop through them all
        $('.loop-event.time-horizontal').each(function() {
            // Get the width of the .concert-headline element
            var headlineWidth = $(this).find('.concert-headline').width();
            $(this).find('.div-infoconcert').css('width', 'calc(100% - 50px - ' + headlineWidth + 'px)');
        });
    }
}));