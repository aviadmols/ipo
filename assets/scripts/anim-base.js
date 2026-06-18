function isScrolledIntoView(elem, $extra = 0) {
    var docViewTop = jQuery(window).scrollTop();
    var docViewBottom = docViewTop + jQuery(window).height();

    if (jQuery(elem).length) {

        var elemTop = jQuery(elem).offset().top;
        var elemBottom = elemTop + jQuery(elem).height();

        return ((elemBottom <= (docViewBottom + $extra)) && ((elemTop + $extra) >= docViewTop));

    } else {
        return false;
    }
}

function throttle(func, timeFrame = 200) {
    var lastTime = 0;
    return function() {
        var now = new Date();
        if (now - lastTime >= timeFrame) {
            func();
            lastTime = now;
        }
    };
}