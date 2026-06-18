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


// ============================================================
// Moved from ipo-custom snippet #3 (admin code-manager ID: 44812) — admin-only
// Binds #event_update_btnn click -> fetch_presentation_data AJAX
// ============================================================

jQuery(document).ready(function($) {
    $('#event_update_btnn').click(function(e) {
        e.preventDefault();
        var nonce = $('#event_update_nonce').val(); // Retrieving the nonce
        var apiEventId = jQuery('#acf-field_61c2f00ba1358').val(); // Using the correct selector
        var lang = jQuery('.icl_box_paragraph select').val();

        jQuery.ajax({
            url: ajaxurl, // Using WordPress's ajaxurl
            type: 'POST',
            data: {
                action: 'fetch_presentation_data', // The action name you defined in PHP
                nonce: nonce,
                apiEventId: apiEventId // Sending the ID to the PHP script
            },
            success: function(response) {
                if (response.success) {
                    // Assuming response is a JSON object as described
                    var presentation = response.data.presentation;

                    // Verify that presentation object and its properties are defined
                    if (!presentation) {
                        alert('שגיאה: נתוני האירוע לא נמצאו');
                        return;
                    }

                    var locationName = presentation.locationName ? presentation.locationName : 'Unknown Location';
                    var locationCity = presentation.locationCity ? presentation.locationCity : 'Unknown City';
                    var venueName = presentation.venueName ? presentation.venueName : 'Unknown Venue';

                    if (lang === 'en') {
                        // Updating locationName based on specified criteria
                        if (locationName.trim().match(/ברונפמן/)) {
                            locationName = 'Charles Bronfman Auditorium';
                        } else if (locationName.trim().match(/רבקה קראון/)) {
                            locationName = 'Rebecca Crown Hall';
                        }

                        // Updating locationCity based on specified criteria
                        if (locationCity.trim().includes('תל אביב')) {
                            locationCity = 'Tel Aviv';
                        } else if (locationCity.trim().includes('ירושלים')) {
                            locationCity = 'Jerusalem';
                        } else if (locationCity.trim().includes('חיפה')) {
                            locationCity = 'Haifa';
                        }

                        // Updating venueName based on specified criteria
                        var venueNameParts = venueName.split('-');
                        var importedHall = venueNameParts.length > 1 ? venueNameParts[1].trim() : venueName;
                        if (importedHall.trim().includes('צוקר')) {
                            importedHall = 'Zucker hall';
                        } else if (importedHall.trim().includes('אכסדרת ההיכל')) {
                            importedHall = 'The Lowy concert hall';
                        }
                    } else {
                        // Keep the original logic for splitting venueName
                        var venueNameParts = venueName.split('-');
                        var importedHall = venueNameParts.length > 1 ? venueNameParts[1].trim() : venueName;
                    }

                    jQuery('div[data-name="event_date_time"] input').val(presentation.dateTime);
                    jQuery('div[data-name="event_date"] input').val(new Date(presentation.dateTime).toISOString().split('T')[0]);
                    jQuery('div[data-name="event_api_id"] input').val(presentation.id);
                    jQuery('div[data-name="event_featured_name"] input').val(presentation.featureName);
                    jQuery('div[data-name="imported_location"] input').val(locationName + ',' + locationCity);
                    jQuery('div[data-name="imported_hall"] input').val(importedHall);

                    var minPrices = presentation.priceLevels.map(function(level) { return level.minPrice; });
                    var maxPrices = presentation.priceLevels.map(function(level) { return level.maxPrice; });
                    var minPrice = minPrices.length ? Math.min.apply(Math, minPrices) : null;
                    var maxPrice = maxPrices.length ? Math.max.apply(Math, maxPrices) : null;

                    var priceDisplay = '';
                    if (minPrice !== null && maxPrice !== null) {
                        if (lang === 'en') {
                            priceDisplay = minPrice === maxPrice ? `${minPrice} nis` : `${minPrice}-${maxPrice} nis`;
                        } else {
                            priceDisplay = minPrice === maxPrice ? `ש"ח ${minPrice}` : `ש"ח ${minPrice}-${maxPrice}`;
                        }
                    } else {
                        alert('האירוע עודכן בהצלחה אבל אין מחירים לכרטיסים');
                    }

                    jQuery('div[data-name="event_price_range"] input').val(priceDisplay);

                    // Update post title
                    jQuery('input[name="post_title"]').val(presentation.featureName + ' | ' + presentation.dateTime + ' [' + lang + ']');

                    if (priceDisplay !== '') {
                        alert('האירוע עודכן בהצלחה!');
                    }
                } else {
                    alert('שגיאה: ' + response.data);
                }
            },
            error: function(error) {
                console.log(error);
                alert('שגיאה בעת משיכת המידע');
            }
        });
    });
});
