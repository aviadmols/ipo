<?php 



add_action('save_post', 'on_program_post_publish_func',20,3);
function on_program_post_publish_func( $post_id, $data=false, $update=false ) {


    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }


    remove_action('save_post', 'on_program_post_publish_func');




    $debug = false;
    if($debug) echo 'on_program_post_publish_func DEBUG:<br>';


    $post_type = get_post_type($post_id);
    if($post_type == 'program'){

 
        
        $program = new ipo_program($post_id);

        $api_events = $program->get_api_events();

        $existing_events_api_ids = $program->get_events_api_ids();


        $existing_events = $program->get_events();

        // Merge all and clear duplicates
        $new_api_events = array_unique(array_merge($api_events,$existing_events_api_ids));

        if($debug) echo '<br><br>new_api_events:<br>';
        if($debug) print_r($new_api_events);

        // Check if these events are already in the database, search by API id
        $found_events = array();
        $create_events = array();
        foreach($new_api_events as $api_event_id){
            $event = new ipo_event();
            $event = $event->get_by_api_id($api_event_id);

            if($debug) echo '<br><br>event:<br><pre>';
            if($debug) print_r($event);
            if($debug) echo '</pre>';
            
            if($event){
				 $related_to_program = get_field('related_to_program', $event->ID);
        if (!empty($related_to_program)) {
            continue; 
        }
                $found_events[] = $event;
            } else {
                $create_events[] = $api_event_id;
            }
        }

        if($debug) echo '<br><br>found events:<br><pre>';
        if($debug) print_r($found_events);
        if($debug) echo '</pre>';

        if($debug) echo '<br><br>create events:<br><pre>';
        if($debug) print_r($create_events);
        if($debug) echo '</pre>';

        // Create new events
        $created_events = array();
        foreach($create_events as $api_event_id){
            $event = new ipo_event();
            $event->create($api_event_id , $post_id);
            $created_events[] = $event;
        }

        if($debug) echo '<br><br>create events:<br><pre>';
        if($debug) print_r($created_events);
        if($debug) echo '</pre>';

        // Merge the found events with the new events
        $new_events = array_merge($found_events,$created_events);

        if($debug) echo '<br><br>new events:<br><pre>';
        if($debug) print_r($new_events);
        if($debug) echo '</pre>';

        // Update the program with the new events
        $program->update_events($new_events);

        // Update furthest event date
        $program->update_furthest_date();

        // Update program details
        $program->update_details();

        


    }

    add_action('save_post', 'on_program_post_publish_func',20,3);

}