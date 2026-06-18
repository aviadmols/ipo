<?php


add_filter( 'wp_insert_post_data' , 'on_program_title_on_save_func' , 99, 2 );
function on_program_title_on_save_func( $data , $postarr ) {
    // Change post title FIXED BY AVIAD
	
	if($data['post_type'] != 'program'){
		return $data;
	}
	
	if(!$data['post_title'] || $data['post_title'] == 'Auto Draft' || $data['post_title'] == ''){
		$new_title = '';
		if(isset($postarr['acf']['field_61c33ad815e99'])){
			$first_event = $postarr['acf']['field_61c33ad815e99'][0];
			$event_name = get_field('event_featured_name',$first_event);
			if($event_name) $new_title = $event_name;
		}
		$data['post_title'] = $new_title;
		$data['post_name'] = $new_title;
	} else {
		//$data['post_name'] = sanitize_title($data['post_title']);
	}
    return $data;
}



add_action('save_post', 'on_program_post_publish_func',10,2);
function on_program_post_publish_func($post_id,$post_data) {
	
    if( get_post_type( $post_id ) == 'program' ) {
		    $current_api_events = get_field('ipo_api_select_event', $post_id);
    $old_api_events = get_post_meta($post_id, '_prev_ipo_api_select_event', true);

    if ($current_api_events === $old_api_events) return; 
		
        
		$api_events = get_field('ipo_api_select_event',$post_id);
		$ipo_events_api = new ipo_events_api();
		$ipo_events_api->init();
		
		$selected_events = array();
		$main_title = '';
		
		if(is_array($api_events) && !empty($api_events))	
		foreach($api_events as $event){
			
			if(!$event)
				continue;
			
			$existing_events_args = array(
			   'fields' => 'ids',
			   'post_status'   => 'publish',
			   'post_type'   => 'event',
			   // WPML filter
			   'suppress_filters' => false,
			   'meta_query'  => array(
				 array(
				 'key' => 'event_api_id',
				 'value' => $event
				 )
			   )
			 );
			 
			$existing_events = get_posts( $existing_events_args );

			// Add debug message here to error log
			error_log('existing_events for program '.$post_id.' : '.print_r($existing_events,true));

			if( empty($existing_events) ) {
				

			
				$ipo_event = $ipo_events_api->get_event($event);
				$event_name = $ipo_event->featureName;
				$date_time = $ipo_event->dateTime;
				$date = strtotime($date_time);
				$date = date('Y-m-d', $date);   
				$event_price_range = '';
				
				$main_title = $event_name;

				// Get current WPML language
				$post_language_slug = '';
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$post_language_slug = ' ['.ICL_LANGUAGE_CODE.']';
				}
				$title = $event_name . ' | ' . $date_time  . $post_language_slug;

				if($ipo_event){
					$last_event_min_price = $ipo_event->memberOnlyPrice;
					$last_event_max_price = $ipo_event->maxPrice;
				} else {
					$last_event_min_price = false;
					$last_event_max_price = false;
				}

				$event_price_range = get_field('event_price_range',$event);
				if(!$event_price_range){
					$event_price_range = '';
					
					$min_price = $last_event_min_price;
					$max_price = $last_event_max_price;
					if($min_price && $max_price){
						$event_price_range = $min_price.'-'.$max_price;
					} else if ($min_price || $max_price){
						$event_price_range = $min_price . $max_price;
					} else {
						$event_price_range = $min_price.'?'.$max_price;
					}
					if($event_price_range){
						$event_price_range .= __(' ש"ח','ipo');
					}
					
				}		

				remove_action('save_post', 'on_event_post_publish_func');
	
				$event_post_id = wp_insert_post(array (
				   'post_type' => 'event',
				   'post_title' => $title,
				   'post_status' => 'publish',
				));
				
				
				if ($event_post_id) {
					$selected_events[] = $event_post_id;
					update_field('event_date',$date,$event_post_id);
					update_field('event_date_time',$date_time,$event_post_id);
					update_field('event_api_id',$event,$event_post_id);
					update_field('event_featured_name',$event_name,$event_post_id);
					update_field('ipo_api_select_event',$event,$event_post_id);
 $current_related_program = get_field('related_to_program', $event_post_id);
    if (empty($current_related_program)) {
					update_field('related_to_program',$post_id,$event_post_id);	
    }

update_field('event_price_range',$event_price_range,$event_post_id);
				
				} 

				add_action('save_post', 'on_event_post_publish_func');


			} else {

				if($existing_events)
				foreach($existing_events as $existing_event){
					
					$selected_events[] = $existing_event;
					
					$programs = get_field('related_to_program',$existing_event);
					if($programs && is_array($programs)){
						$programs[] = intval($post_id);
						$programs = array_unique($programs);
					} else {
						$programs = intval($post_id);
					}
		  $current_related_program = get_field('related_to_program', $existing_event);
    if (empty($current_related_program)) {

					$result = update_field('related_to_program',$programs,$existing_event);
    }
				}
				
				


			}
			 
		}


		
		$last_event_duration = false;
		$selected_events = array_unique($selected_events);
		if(isset($selected_events[0])){
			$event_apid = get_field('event_api_id',$selected_events[0]); 
			$event_for_data = $ipo_events_api->get_event($event_apid);
			if($event_for_data){
				$last_event_duration = $event_for_data->durationInMinutes;
			} 
				
			if(!empty($selected_events))
				update_field('field_61c33ad815e99',$selected_events,$post_id);
		}

		$event_length_concert = get_field('program_length_concert',$post_id);

			
		if(!$event_length_concert){
			$time = $last_event_duration;
			if ($time < 1){
				
			} else {
				
				
				
				$hours = floor($time / 60);
				$minutes = ($time % 60);
				if(!$hours)
					$hours = '00';
				if(!$minutes)
					$minutes = '00';
				
				$event_length_concert = $hours . ':' . $minutes;
				
				update_field('field_62b3326a0b913',$event_length_concert,$post_id);
				
			}
				

		}
		
		
		// SAVE NEW TITLE

		if(!$post_data->post_title){
			
			static $updated = false;
			if ($updated) {return;}
			$updated = true;
			
			wp_update_post( [
				'ID'         => $post_id,
				'post_title' => $main_title,
			]);
		}
		
		$events = get_field('ipo_created_events',$post_id);
		if($events){


			$farthest_date = '';
			foreach( $events as $event ){

				$event_date = get_field('event_date_time',$event);
				if(strtotime($event_date) > strtotime($farthest_date)){
					$farthest_date = date('Y-m-d',strtotime($event_date));
				}
			}
			update_field('field_633b132f99155',$farthest_date,$post_id);

		}


		// Enable save_post action
		add_action('save_post', 'on_event_post_publish_func');
    }
	
	
}