<?php

/* 
 * Function: on_program_post_publish_func
 * 
 * Description: 
 * This function is called when a program is published.
 * It will create a new event for the program based on the selected API event
 * This only works for Hebrew programs, as the English programs are not connected to the API
 * 
 */

//add_action('save_post', 'on_program_post_publish_func',20,3);
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

        if($debug) echo '<br><br>api events:<br>';
        if($debug) print_r($api_events);

        $existing_events_api_ids = $program->get_events_api_ids();

        if($debug) echo '<br><br>existing events api ids:<br>';
        if($debug) print_r($existing_events_api_ids);


        $existing_events = $program->get_events();

        if($debug) echo '<br><br>existing events:<br>';
        if($debug) print_r($existing_events); 

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

        if($debug) echo '<br><br>found events:<br>';
        if($debug) print_r($found_events);

        if($debug) echo '<br><br>create events:<br>';
        if($debug) print_r($create_events);
        

        // Create new events
        $created_events = array();
        foreach($create_events as $api_event_id){
            $event = new ipo_event();
            $event->create($api_event_id);
            $created_events[] = $event;
        }

        // Merge the found events with the new events
        $new_events = array_merge($found_events,$created_events);

        if($debug) echo '<br><br>new events:<br>';
        if($debug) print_r($new_events);

        // Update the program with the new events
        $program->update_events($new_events);

        // Update furthest event date
        $program->update_furthest_date();

        // Update program details
        $program->update_details();

        //die();

    }

    add_action('save_post', 'on_program_post_publish_func',10,3);

}

/* 
 * Function: on_en_program_post_publish_func
 * 
 * Description: 
 * This function is called when english program is published.
 * If no events are connected to the program, it will check for HE version of the program and copy the events from there
 * we will go over each event and check if it has EN version, if not, we will create it
 * we will then update the program with the new events
 * 
 */

//add_action('save_post', 'on_en_program_post_publish_func',20,3);
function on_en_program_post_publish_func( $post_id, $data=false, $update=false ) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }


    remove_action('save_post', 'on_en_program_post_publish_func');
    $post_type = get_post_type($post_id);
    if($post_type == 'program'){
        
        $program = new ipo_program($post_id);

        $debug = false;

        // Get program language
        $program_language = $program->get_lang();
        if($program_language == 'he'){
            return;
        }


        // Get the HE version of the program
        $translation_he_version = icl_object_id($post_id,'program',false,'he');
        if(!$translation_he_version){
            return;
        } else {
        }

        $he_program = new ipo_program($translation_he_version);

        // Get the events from the HE version
        $he_events = $he_program->get_events();

        // Check if these events have EN version

        $en_events = array();
        if($he_events){
            foreach($he_events as $he_event){
                $title = get_the_title($he_event);
                $post_type = get_post_type($he_event);
                // Get the language of the event
                $wpml_lang = apply_filters( 'wpml_post_language_details', NULL, $he_event );
                $lang = 'n/a';
                if(isset($wpml_lang['language_code'])){
                    $lang = $wpml_lang['language_code'];
                }

                $ipo_he_event = new ipo_event($he_event);
                $he_event_api_id = $ipo_he_event->get_api_id();

                if($he_event_api_id){
                    // Search for other posts with the same api id, but in EN specifically
                    $args= [
                        'post_type' => 'event',
                        'meta_key' => 'event_api_id',
                        'numberposts' => -1,
                        'fields' => 'ids',
                        'meta_value' => $he_event_api_id,
                    ];

                    // Filter only EN posts with WPML API
                    $args['suppress_filters'] = false;

                    $results = get_posts($args);

                    // Filtering only EN posts with WPML API
                    $results_filtered = array_filter($results, function ($post_id) {
                        return apply_filters('wpml_post_language_details', NULL, $post_id)['language_code'] === 'en';
                    });

                    $results = $results_filtered;

                    if($results){
                        $en_events[] = $results[0];
                    } else {
                        // Not found, we must create it
                        $en_event = new ipo_event();
                        $en_event = $en_event->create($he_event_api_id);
                        echo 'created event: '.print_r($en_event,true).'<br>';
                    }

                }

                ///echo 'he event id: '.$he_event.' title: '.$title.' post type: '.$post_type. ' language: '. $lang.' api id: '.$he_event_api_id.' he_events: '.print_r($he_events,true).' en_events: '.print_r($en_events,true).'<br>';
            }
        }
        die();
        /*
        

        if($he_events){
            foreach($he_events as $he_event){

                $en_event = icl_object_id($he_event->ID,'event',false,'en');

                if($debug) echo 'he event id: '.$he_event->ID.'<br>';

                if($en_event){

                    // If the event has EN version, first check if it is connected to the hebrew event
                    $en_event = new ipo_event($en_event);
                    $translation = $en_event->wpml_get_translation();
                    if($translation['he'] != $he_event->ID){

                        // If the EN event is not connected to the HE event, connect it
                        if($debug) echo 'connecting event '.$en_event->ID.' to '.$he_event->ID.'<br>';
                        $en_event->wpml_connect($he_event);

                    }


                    // add it to the array
                    $en_events[] = $en_event;

                } else {    
                    
                    if($debug) echo 'no en version found. Creating<br>';
                    // If the event does not have EN version, create it
                    $event = new ipo_event();
                    $he_event_api_id = get_field('event_api_id',$he_event);
                    $event_post = $event->create($he_event_api_id,$translation_he_version,'draft');
                    $event_id = $event_post->ID;

                    if($debug) echo 'created event id: '.$event_id.'<br>';

                    // Get trid for the HE event
                    $trid = apply_filters('wpml_element_trid', NULL, $he_event, 'post_event');

                    if($debug) echo 'trid: '.$trid.'<br>';

                    // Set up WPML args to connect the EN event to the HE event
                    $wpml_args = array(
                        'element_id' => $event_id,
                        'element_type' => 'post_event', // Change this to the appropriate post type for your events
                        'trid' => $trid,
                        'language_code' => 'en',
                        'source_language_code' => 'he'
                    );
                    do_action('wpml_set_element_language_details', $wpml_args );

                    if($debug) echo 'connected event '.$event_id.' to '.$he_event->ID.'<br>';


                    // Connect $event_id to $he_event


                    // Update the event with the details from the HE event, just duplicate the ACFs if they are not set
                    $fields_to_check = [
                        'event_date_time',
                        'ipo_api_select_event',
                        'event_date',
                        'event_time',
                        'event_api_id',
                        'event_featured_name',
                        'imported_hall',
                        'imported_location',
                        'event_price_range',
                    ];

                    foreach($fields_to_check as $field){
                        $field_value = get_field($field,$he_event->ID);
                        if($field_value){
                            update_field($field,$field_value,$event_id);
                        }
                    }

                    if($debug) echo 'updated event '.$event_id.' with details from '.$he_event->ID.'<br>';

                    // related_to_program field
                    $related_to_program = update_field('related_to_program',$translation_he_version,$event_id);

                    if($debug) echo 'updated event '.$event_id.' related_to_program with '.$translation_he_version.'<br>';

                    $en_events[] = $event_id;
                }
            } 

        } else {
            if($debug) echo 'no events found in hebrew version. Aborting<br>';
            return;
        }

        */

        // Update the program with the new events
        if($en_events)
            $program->update_events($en_events);
        

    }
    add_action('save_post', 'on_en_program_post_publish_func',10,3);
}


// TODO: dont create dupliactes, based on API ID
// TODO: when event is created without program, it must stay draft




 /*
add_action('acfe/fields/button/name=ipo_create_events', 'ipo_create_events', 10, 2);
function ipo_create_events($field, $post_id){

    // retrieve field input value 'my_field'

    // Get data from data.events
    if(isset($_POST['acf']['field_6196707dc0b98'])){
        $events = $_POST['acf']['field_6196707dc0b98'];
        // Create events
        $created = array();
        foreach($events as $event){
            $ipo_event = new ipo_event();
            $id = $ipo_event->create($event,$post_id);
            // if id is object, its a post 

            $created[] = $id;
        }
        $results = print_r($created, true);
    } else {
        $events = false;
        $results = 'No events selected';
    }

    // send json success message
    wp_send_json_success("Success! My events are: {$results}");
    
}

// Add JS to admin, inline script for the ipo_create_events button
add_action('admin_footer', 'ipo_create_events_js');
function ipo_create_events_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            acf.addFilter('acfe/fields/button/data/name=ipo_create_events', function(data, $el){
                
                // Get currently selected items from the acf field 'ipo_api_select_event'
                // data-name="ipo_api_select_event" is the name of the field, it is select2 field

                // add custom key
                //data.events = selected_items_array;
                //console.log(data); 
                
                // return
                return data;
            
            });
            acf.addAction('acfe/fields/button/success/name=ipo_create_events', function(response, $el, data){
                
                    // json success was sent
                    if(response.success){
                        
                        var post_ids = data.acf.field_6196707dc0b98;
                        // now we need to add this value in the 'data-name="ipo_created_events"'
                        
                        
                    }
                
            });
        });
    </script>
    <?php
}
*/