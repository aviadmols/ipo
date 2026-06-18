<?php

// When post type 'program' is updated, the field 'ipo_created_events' should behave as bidirectional
add_action('acf/save_post', 'ipo_program_bidirectional_events', 20);
function ipo_program_bidirectional_events( $post_id ) {
    // bail early if not a program
    if( get_post_type($post_id) !== 'program' ) {
        return;
    }

    // Disable action to prevent infinite loop
    remove_action('acf/save_post', 'ipo_program_bidirectional_events', 20);

    $field_name = "ipo_created_events";
    $events = get_field($field_name, $post_id, false);

    if(is_array($events)){
        foreach($events as $event_id){
            // update field 'related_to_program' on event
            update_field('field_62b20365ed487', $post_id, $event_id);
        }
    }

    // re-enable action
    add_action('acf/save_post', 'ipo_program_bidirectional_events', 20);
}

