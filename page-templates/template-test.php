<?php

/* Template Name: TEST Template */

// Get all published 'program' post types
$programs = get_posts([
    'post_type' => 'program',
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);

// Loop through each program, print their title and a line break

foreach ($programs as $program) {

    // get the 'ipo_created_events' field
    $ipo_created_events = get_field('ipo_created_events', $program->ID);
    
    // if the field is not empty
    if(!empty($ipo_created_events)) {
        
        // loop through the array
        foreach($ipo_created_events as $ipo_created_event) {
            
            if(is_int($ipo_created_event)) {
                $ipo_created_event = get_post($ipo_created_event);
            }
            // Check if the event has 'related_to_program' field set
            $related_to_program = get_field('related_to_program', $ipo_created_event->ID);

            // If the field is not set, set it to the current program
            if(empty($related_to_program)) {
                echo 'Updating ' . $ipo_created_event->post_title . ' to ' . $program->post_title . ' <br>';
                update_field('field_62b20365ed487', $program->ID, $ipo_created_event->ID);
            }

            
        }
        
    }

}

// Now loop through all events
$events = get_posts([
    'post_type' => 'event',
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);

foreach ($events as $event) {
    // Check if the event has 'related_to_program' field set
    $related_to_program = get_field('related_to_program', $event->ID);

    // If the field is not set, set it to the current program
    if(empty($related_to_program)) {
        // Get event_featured_name field
        $event_featured_name = get_field('event_featured_name', $event->ID);
        // If the field is not empty, search for program with similar name
        if(!empty($event_featured_name)) {
            $program = get_posts([
                'post_type' => 'program',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                's' => $event_featured_name,
            ]);
            // If program is found, set the 'related_to_program' field to the program
            if(!empty($program)) {
                echo 'Updating ' . $event->post_title . ' to ' . $program[0]->post_title . ' <br>';
                update_field('field_62b20365ed487', $program[0]->ID, $event->ID);
            }
        }
    }
}