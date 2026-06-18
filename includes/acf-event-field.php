<?php

function ipo_acf_load_events_from_api( $field ) {
    
    // reset choices
    $field['choices'] = array();
	
	$events_api = new ipo_events_api();
	$events_api->url = 'https://ipo.pres.global/api/presentations';
	$events_api->init();
	
	$event_api_id = get_field('event_api_id');
	if($event_api_id)
		$field['default_value'] = $event_api_id;
	
	if($events_api->events){
		foreach($events_api->events as $event){
			
			$value = $event->id;
			$label = '#' . $event->id.' | ' . $event->featureName . ' | ' . $event->dateTime ;
			$field['choices'][ $value ] = $label;
			
		}
	} else {
		$field['choices'][ 0 ] = 'No events found';
		// Add the default value if it is saved in the database
		if($event_api_id)
			$field['choices'][ $event_api_id ] = $event_api_id;
	}
	

    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=ipo_api_select_event', 'ipo_acf_load_events_from_api');


function ipo_acf_related_program_html( $field ) {

    if ($field['key'] == "field_62b20365ed487"){
		
		echo '<div class="actions-wraper">';
		
		$program = get_field('field_62b20365ed487');
		if($program) {
			$link = get_permalink($program);
			$title = get_the_title($program);
			
			if($link){
				echo '<a class="program-link" target="_blank" href="'.$link.'">'.__('עמוד התכנית','ipo').': '.$title.'</a>';
			}
		} else {
			// Suggest program
			/*
			$feature_name = get_field('event_featured_name');
			// Find program with similar name
			$program = get_page_by_title($feature_name, OBJECT, 'program');
			if($program){
				$title = get_the_title($program);
				echo '<a class="program-link quick-select" data-program_id="'.$program->ID.'" target="_blank">לחצו כאן לבחירה מהירה: '.$title.'</a>';
			} 
			*/
		}


		$event_api_id = get_field('field_61c2f00ba1358');
		$event_data_url = '';
		if($event_api_id){
			$api = new ipo_events_api();
			$event_data_url = $api->get_event_data_url($event_api_id);
			echo '<a class="event-api-data" target="_blank" href="'.$event_data_url.'" style="display: block;">api data packet</a>';
			//echo '['.$event_api_id.']'.print_r($api_packet,true);
		} 

		echo '</div>';
    }

}
add_action( 'acf/render_field', 'ipo_acf_related_program_html', 10, 1 );


/*

function wpdocs_register_meta_boxes() {
    add_meta_box( 'meta-box-id', __( 'Events Fields', 'textdomain' ), 'wpdocs_my_display_callback', 'events' );
}
add_action( 'add_meta_boxes', 'wpdocs_register_meta_boxes' );

function wpdocs_my_display_callback( $post ) {

		$ch = curl_init();
		$options = [
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => 'https://ipo.pres.global/api/presentations'
		];

		curl_setopt_array($ch, $options);
		$json = json_decode(curl_exec($ch));
		curl_close($ch);
		$events = $json->presentations;
		
		//print_r($events);

		//$saved_event_name = get_post_meta($post->ID, 'event_selection_name', true);
		$saved_event_id = get_post_meta($post->ID, 'event_selection_id', true);
		
		$saved_event_label = '';
		if(!$saved_event_id){
			$saved_event_label = 'none';
		} else {
			$saved_event_label = $saved_event_id; 
		}
		echo "Currently selected: [".$saved_event_label."]";
	?>
	<select name="event_selection_id" id="display_name">
		<option value="0">Not selected</option>
		<?php 
		foreach($events as $event): 
			
			$extra = '';
			if($event->id == $saved_event_id) 
				$extra = 'selected';
			
			echo "<option value='{$event->id}' {$extra}>[{$event->id}] {$event->dateTime} - {$event->featureName} - {$event->maxPrice}</option>";
		
		endforeach; 
		?>
	</select>
<?php
}



add_action( 'save_post', 'wpdocs_save_meta_box' );
function wpdocs_save_meta_box( $post_id ) {
    
	$event_id = sanitize_text_field($_POST['event_selection_id']);
	
	$post = get_post($post_id);
	if($post->post_type == 'events')
	{
		if(ICL_LANGUAGE_CODE=='en') {
			$api_url = 'https://ipo.pres.global/api/presentations?lang=en';
		} else {
			$api_url = 'https://ipo.pres.global/api/presentations';	
		}
		
		$ch = curl_init();
		$options = [
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $api_url,
		];

		curl_setopt_array($ch, $options);
		$json = json_decode(curl_exec($ch));
		curl_close($ch);
		$events = $json->presentations;
		
		foreach($events as $event) {
			if($event->id == $event_id)
			{
				$dd = substr($event->dateTime,0,11); 
				$event_time = date('H:i',$dd);
				$event_date = date('d-m-Y',$dd);
				
				if(ICL_LANGUAGE_CODE=='en'){
					$url = "https://ipo.pres.global/order/".$event->id."/?lang=en";
					$lcode = "en";
				} else {
					$url = "https://ipo.pres.global/order/".$event->id."/";
					$lcode = "he";
				}

				$final = $event_date." ".$event_time; //api threw work
				$final = get_post_meta($post_id,'events_date',true)." ".get_post_meta($post_id,'event_time',true);//acf threw work

				update_post_meta($post_id, 'mashup_var_buy_url_custom', $url);				
				update_post_meta($post_id, 'Language', $lcode);
							

				
			}
		}		
		//update_post_meta($post_id, 'event_display_name', $event_id);
		update_post_meta($post_id, 'event_selection_id', $event_id);		
	}
}

*/