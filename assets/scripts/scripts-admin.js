jQuery(document).ready(function($) {

    $('.wp-admin').on('change', '[data-name="related_to_program"] select', function() {
        $('.program-link').hide();
    });

    $('.wp-admin').on('click', '.acf-fields > .acf-field.ipo-disabled', function() {
        // Throw alert() dialog with buttons "accept" and "cancel" 
        var r = confirm("This field is disabled. Do you want to enable it?");
        if (r == true) {
            $(this).removeClass('ipo-disabled');
        }
    });

    // find all .acf-icon.edit-post items
    $('.wp-admin').find('.acf-icon.edit-post').each(function() {
        // wait 2s
        setTimeout(function() {
            // Get item data-id
            var id = $(this).attr('data-id');
            // change href to be the edit link
            $(this).attr('href', 'post.php?post=' + id + '&action=edit');
        }, 2000);
    });


});