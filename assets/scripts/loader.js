jQuery(document).ready(function($) {

    // Run function 'test' after everything is loaded
    $(window).on('load', function() {
        // Wait 500ms before running function 'test'
        setTimeout(function() {
            // Add class 'finished-loading' to body
            $('body').addClass('finished-loading');

        }, 0);

    });

});


   jQuery(window).on('load', function() {
        // Wait 500ms before running function 'test'
        setTimeout(function() {
            // Add class 'finished-loading' to body
            jQuery('body').addClass('finished-loading');
        }, 100);

    });