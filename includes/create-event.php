<?php



add_action('save_post', 'on_event_post_publish_func',10,3);
function on_event_post_publish_func($post_id,$data=false,$update=false) {
	
	remove_action('save_post', 'on_event_post_publish_func');
	
	$post_type = get_post_type($post_id);
	if($post_type == 'event'){
		
		$api_event = get_field('ipo_api_select_event',$post_id);


		$date_time = '';
		$event_api_id = '';
		$event_name = '';
		$event_date = '';

		$new_title = '';
		$new_slug = '';

		$current_wpml_lang = ICL_LANGUAGE_CODE;
		$current_wpml_lang_slug = '';
		if($current_wpml_lang == 'he'){
			$current_wpml_lang_slug = 'he';
		} else {
			$current_wpml_lang_slug = 'en';
		}

			
		$events_api = new ipo_events_api();
		$events_api->init();
		$api_event = $events_api->get_event($api_event);


		if(!$update){

			$date_time = $api_event->dateTime;

			$event_date = date('Y-m-d',strtotime($date_time));
			
			$event_api_id = $api_event->id;
			$event_name = $api_event->featureName;


			if($date_time && $event_api_id && $event_name){
						
				update_field('event_date_time',$date_time,$post_id);
				update_field('event_api_id',$event_api_id,$post_id);
				update_field('event_date',$event_date,$post_id);
				update_field('event_featured_name',$event_name,$post_id);
				update_field('imported_hall',$api_event->locationName,$post_id);
				update_field('imported_location',$api_event->locationCity,$post_id);

				update_field('event_price_range',$events_api->get_price_range($api_event->id) ,$post_id);

				$new_title = $event_name.' | '.$date_time . ' ['.$current_wpml_lang_slug.']';
				$new_slug = $event_api_id.'-'.$date_time;

			}

		} else {

			$date_time = get_field('event_date_time',$post_id);

			$event_date = date('Y-m-d',strtotime($date_time));

			$event_api_id = get_field('event_api_id',$post_id);
			$event_name = get_field('event_featured_name',$post_id);

			$imported_location = get_field('imported_location',$post_id);
			$imported_hall = get_field('imported_hall',$post_id);

			if(!$imported_hall){
				$imported_hall = $api_event->locationName;
				update_field('imported_hall',$imported_hall,$post_id);
			}

			if(!$imported_location){

				// Check if locationCity exist and not empty
				if(!isset($api_event->locationCity) || empty($api_event->locationCity)){
					// Check for venueCity item
					if(isset($api_event->venueCity) && !empty($api_event->venueCity)){
						$api_event->locationCity = $api_event->venueCity;
					}
				}

				$imported_location = $api_event->locationCity;
				update_field('imported_location',$imported_location,$post_id);
			} else {

			}

			// Price range update
			//update_field('event_price_range',$events_api->get_price_range($api_event->id) ,$post_id);
			$price_range = $events_api->get_price_range($api_event->id);
			$existing_price_range = get_field('event_price_range',$post_id);
			if($price_range && !$existing_price_range){
				update_field('event_price_range',$price_range ,$post_id);
			}

			if($date_time && $event_api_id && $event_name){
						
				$new_title = $event_name.' | '.$date_time.' ['.$current_wpml_lang_slug.']';
				$new_slug = $event_api_id.'-'.$date_time;

			}

		}


		// Update modified title and slug
		if($new_title && $new_slug){
			
			// Update post title
			
			$post = array(
				'ID' => $post_id,
				'post_title' => $new_title,
				'post_name' => $new_slug
			);
			
			$result_id = wp_update_post($post);

			add_action('save_post', 'on_event_post_publish_func');
			return $result_id;
			
		}

		add_action('save_post', 'on_event_post_publish_func');
		
	}
	

}



