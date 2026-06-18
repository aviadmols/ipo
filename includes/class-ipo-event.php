<?php

class ipo_event{
	
	public $post;
	
	public function __construct($post = false) {
		
		//print_r($this->post);
		
		if(!$post){
			$this->post = false;
		} else if(is_int($post)){
			$this->post = get_post($post);
		} else if(is_string($post) && is_numeric($post)) {
			$this->post = get_post(intval($post));
		} else {
			$this->post = $post;
		}

		return $this->post;
		
	}

	public function get_id(){
		if(isset($this->post->ID)){
			return $this->post->ID;
		} else {
			return false;
		}
	}

	public function get_api_id(){
		return $this->gf('event_api_id');
	}

	public function create($event_api_id,$program_id = false,$status='publish'){

		$event_api = new ipo_events_api();
		$event_api->init();
		$event = $event_api->get_event($event_api_id);

		// Check if event exists
		$program_lang = apply_filters( 'wpml_post_language_details', NULL, $program_id );

		if(isset($program_lang['language_code']))
			$program_lang_code = $program_lang['language_code'];
		else
			$program_lang_code = 'he';

		$existing_event = $this->get_event_by_api_id($event_api_id,$program_lang_code);
		if($existing_event){
			return $existing_event;
		}


		$event_date_time = $event->dateTime; // 2023-06-16 14:00

		// Time in 00:00 format
		$time = date('H:i',strtotime($event_date_time));

		// Date in dd-mm-yyyy format
		$event_date = date('d-m-Y',strtotime($event_date_time));

		$event_featured_name = $event->featureName;
		//$venue_name = $event->venueName;
		$location_name = $event->locationName;
		$location_city = $event->venueCity;
 
		$min_price = $event->memberOnlyPrice;
		$max_price = $event->maxPrice;

		$event_price_range = $min_price.'-'.$max_price;

		// If featured name is empty or the date time is empty, log an error and skip this event
		if($event_featured_name == '' || $event_date_time == ''){
			// Log error
			error_log('Error creating event '.$event_api_id.' has no featured name or date time. Skipping...');
			return false;
		}

		// Get  WPML language
		$lang = ICL_LANGUAGE_CODE;

		$title =  $event_featured_name.' | '.$event_date_time . ' [' . $lang . ']';

		// Create post object
		$event_post_data = array(
		  'post_title'    => $title,
		  'post_content'  => '',
		  'post_status'   => $status,
		  'post_author'   => get_current_user_id(),
		  'post_type'	  => 'event',
		);

		// Insert the post into the database
		$post_id = wp_insert_post( $event_post_data );

		// Update ACF fields
		update_field('ipo_api_select_event',$event_api_id,$post_id);
		update_field('event_date_time',$event_date_time,$post_id);
		update_field('event_date',$event_date,$post_id);
		update_field('event_time',$time,$post_id);
		update_field('event_featured_name',$event_featured_name,$post_id);
		update_field('imported_location',$location_city,$post_id);
		update_field('imported_hall',$location_name,$post_id);
		update_field('event_price_range',$event_price_range,$post_id);
		update_field('event_api_id',$event_api_id,$post_id);

	if ($program_id) {
    // Get the current value of the field
    $current_value = get_field('related_to_program', $post_id);

    // Update only if the current value is empty
    if (empty($current_value)) {
        update_field('related_to_program', $program_id, $post_id);
    }
} else {
    // Check if similar program (same title) exists
    $program = new ipo_program();
    $program_id = $program->find_existing($event_featured_name);
    if ($program_id) {
        // Get the current value of the field
        $current_value = get_field('related_to_program', $post_id);

        // Update only if the current value is empty
        if (empty($current_value)) {
            update_field('related_to_program', $program_id, $post_id);
        }
    }
}
		// Add event to program
		if($program_id){
			$program = new ipo_program($program_id);
			$program->add_event($post_id);
		}

		// Add location terms
		$city = $this->get_city($location_city);
		
		// Search the $location_city string in all location tax terms starting with the nested children going up to the parent
		$location_terms = get_terms( array(
		    'taxonomy' => 'location',
		    'hide_empty' => false,
		    'orderby' => 'parent',
		    'order' => 'DESC',
		) );


		$location_match = false;
		foreach($location_terms as $location_term){

			// Check if location_name is part of an actual term
if ($location_name !== '') {
			$result = strpos($location_term->name,$location_name);
			//echo 'Searching for ['.$location_name.'] in ['.$location_term->name.']...<br>';

			if($result !== false){
				//echo 'Found '.$location_city.' in '.$location_name.'<br>';
				$location_match = $location_term->term_id;
				// Add location term to post
				wp_set_post_terms( $post_id, $location_match, 'location' );
				break;
			}
}
		}

		// return post id
		return $post_id;


	}

	public function get_by_api_id($api_id){
		$events = get_posts(array(
			'post_type' => 'event',
			'meta_key' => 'event_api_id',
			'meta_value' => $api_id,
			'posts_per_page' => 1,
			'suppress_filters' => false
		));
		if(count($events) > 0){
			return new ipo_event($events[0]);
		} else {
			return false;
		}
	}

	public function gf($key,$default = ''){
		
		//print_r($key);
		//print_r($key);
		$val = get_field($key,$this->post->ID);
		if($val == ''){
			$val = $default;
		}
		return $val;
	}

	public function get_location_str(){
		$location = $this->get_location();
		if(is_object($location)){
			return $location->name;
		} else {
			return $location;
		}
	}

	public function get_date($format=false){
		$date = $this->gf('event_date');
		//echo 'date: '.print_r($date,true).'<br>';
		// Apply WP date format
		if(!$format)
			$format = get_option('date_format');

		$date = date_i18n($format,strtotime($date));

		return $date;
	}

	public function get_purchase_link($url_only = false){


$sold_out = $this->gf('sold_out');
  
  
  if ($sold_out){

return '<a class="btn disabled" href="#">'. $sold_out .'</a>';
  }


		$current_user = wp_get_current_user();

	$str_ticket_order_he = 'לרכישת כרטיסים';
$str_ticket_order_en = 'Order Tickets';
$str_ticket_order_ru = 'Купить билеты'; // Russian translation for 'Order Tickets'
$str_ticket_order_he_mobile = 'כרטיסים';
$str_ticket_order_en_mobile = 'Tickets';
$str_ticket_order_ru_mobile = 'Билеты'; // Russian translation for 'Tickets'
$str_passed_he = 'אירוע עבר';
$str_passed_en = 'Event Passed';
$str_passed_ru = 'Событие прошло'; // Russian translation for 'Event Passed'

$str_ticket_order = '';
$str_ticket_order_mobile = '';
$str_passed = '';

if(  get_field('Russian_lang') ) {
    $str_ticket_order = $str_ticket_order_ru;
    $str_ticket_order_mobile = $str_ticket_order_ru_mobile;
    $str_passed = $str_passed_ru;
} elseif(ICL_LANGUAGE_CODE == 'he') {
    $str_ticket_order = $str_ticket_order_he;
    $str_ticket_order_mobile = $str_ticket_order_he_mobile;
    $str_passed = $str_passed_he;
} else {
    $str_ticket_order = $str_ticket_order_en;
    $str_ticket_order_mobile = $str_ticket_order_en_mobile;
    $str_passed = $str_passed_en;
}





		if($this->is_passed()){


			if($url_only)
				return false;
			else
				return '<a class="btn disabled" href="#">'.$str_passed.'</a>';

		}



		$apid = $this->gf('event_api_id');
		if(!$apid){
			// Check if event has 
		}


if(get_field('Hiding_purchase')) {
    $text = '';
    if (empty(get_field('button_to_purchasetxt'))) {
        if (ICL_LANGUAGE_CODE == 'he') {
            $text = 'מכירת הכרטיסים תחל ב-1.9';
        } else {
            $text = 'Ticket sales will open on September 1st';
        }
    } else {
        $text = get_field('button_to_purchasetxt');
    }
    return '<p><strong style="margin-top: 5px; display: block; text-align: center;">' . $text . '</strong></p>';
}





		if($apid){
			$url = 'https://ipo.pres.global/order/'.$apid;
		if(ICL_LANGUAGE_CODE == 'he'){
        
        }else{
            $url = $url .'?lang=en';
        }

 if(get_field('ticket_purchase')) { 
$url = get_field('ticket_purchase');

} 

			if($url_only)
				return $url;
			else
				return '<a class="btn" href="'. $url .'"><span class="hide-mobile">'.$str_ticket_order.'</span><span class="hide-pc">'.$str_ticket_order_mobile.'</span></a>';
			
		}
	}

	public function get_day(){
		$event_date_time = $this->gf('event_date_time');
		$event_day = date('w',strtotime($event_date_time));
		switch($event_day){
			case 0: { $event_day = __('א','ipo'); break; }
			case 1: { $event_day = __('ב','ipo'); break; }
			case 2: { $event_day = __('ג','ipo'); break; }
			case 3: { $event_day = __('ד','ipo'); break; }
			case 4: { $event_day = __('ה','ipo'); break; }
			case 5: { $event_day = __('ו','ipo'); break; }
			case 6: { $event_day = __('ש','ipo'); break; }
		}
		return $event_day;
	}
  
  	public function get_day_name(){
		$event_date_time = $this->gf('event_date_time');
		$event_day = date('w',strtotime($event_date_time));
		switch($event_day){
			case 0: { $event_day = __('ראשון','ipo'); break; }
			case 1: { $event_day = __('שני','ipo'); break; }
			case 2: { $event_day = __('שלישי','ipo'); break; }
			case 3: { $event_day = __('רביעי','ipo'); break; }
			case 4: { $event_day = __('חמישי','ipo'); break; }
			case 5: { $event_day = __('שישי','ipo'); break; }
			case 6: { $event_day = __('שבת','ipo'); break; }
		}
		return $event_day;
	}


	public function get_time($formatted=true){

		//$default_time_format = "H:i";
		


		//$default_date_format = "d/m/Y";

		$event_date_time = $this->gf('event_date_time');
		if($formatted)
			return date('H:i',strtotime($event_date_time));
		else 
			return $event_date_time;

	}

	public function get_event_location_icon_str(){

		$imported_location = $this->gf('imported_location');
		$imported_hall = $this->gf('imported_hall');

		// Check if location term exists
		$event_location = wp_get_post_terms($this->post->ID,'location');

		// Get all ancestors terms <parent> <grandparent> <great grandparent>
		$ancestors = get_ancestors($event_location[0]->term_id,'location');
		if(isset($ancestors[0]))
			$event_location[1] = get_term($ancestors[0],'location');

		if(isset($ancestors[1]))
			$event_location[2] = get_term($ancestors[1],'location');


		$location = '';
		$hall = '';

		$locations_icon_str = '';

		if(isset($event_location[0])){
			
			// The first one is the city, not used here
			// The second one is the location
			if(isset($event_location[1]))
				$location = $event_location[1]->name;
			// The third one is the hall
			if(isset($event_location[2]))
				$hall = $event_location[0]->name;

		}  else {
		if($imported_location && $imported_hall) {
			$location = $imported_location;
			$hall = $imported_hall;
		} 
		} 
		
		$locations_icon_str .= '<span class="location">'.$location.'</span>';
		$locations_icon_str .= '<span class="hall">'.$hall.'</span>';

		// <span class="location"></span>
		// <span class="hall"></span>

		return $locations_icon_str;

	}
	
	public function get_location( $text_only = false ){

		$event_location = wp_get_post_terms($this->post->ID,'location');
		if(isset($event_location[0])){
			if($text_only){
				$location = $event_location[0]->name;


			} else {
				$location = $event_location[0];
			}
		} else {

			$imported_location = $this->gf('imported_location');
			$imported_hall = $this->gf('imported_hall');
			$location = $imported_location;

		}

		return $location;

	}

	public function get_city($location=false){

		if(!$location)
			$location = $this->get_location();

		if(is_string($location)){
			/*
			$extracted_children = explode(',',$location);
			$i = count($extracted_children);
			if($i > 1){
				$city = $extracted_children[$i-1];
				$city = trim($city);
			} else {
				$city = $location;
			}

			print_r($extracted_children);
			print_r($city);
			*/

			$city = $location;

			// Check if we have any term with the same name, or partial name of term from the 'location' taxonomy
			$all_terms_for_comaprison = get_terms('location',array('hide_empty'=>false));
			
			// Check if the current city string is part of any of the terms strings
			foreach($all_terms_for_comaprison as $term){
if ($city !== '') {
				if(strpos($term->name,$city) !== false){

					// Get the ancestor of the term
					$ancestors = get_ancestors($term->term_id,'location');
					if(isset($ancestors[0])){
						$city = get_term($ancestors[0],'location')->name;
					} else {
						if(isset($term->name))
							$city = $term->name;
						else
							$city = $term;
					}
				}
			}
			}
if ($city !== '') {

			if(strpos($city,'ת"א') !== false){
				$city = 'תל אביב';
			}

			if(strpos($city,'תל אביב') !== false){
				$city = 'תל אביב';
			}

			// Check if the string has the word ירושלים
			if(strpos($city,'ירושלים') !== false){
				$city = 'ירושלים';
			}

			// Check if the string has the word חיפה
			if(strpos($city,'חיפה') !== false){
				$city = 'חיפה';
			}

			if(strpos($city,'Tel Aviv') !== false || strpos($city,'Tel-Aviv') !== false || strpos($city,'tel aviv') !== false || strpos($city,'tel-aviv') !== false){
				$city = 'Tel Aviv';
			}

			if(strpos($city,'Jerusalem') !== false || strpos($city,'jerusalem') !== false){
				$city = 'Jerusalem';
			}

			if(strpos($city,'Haifa') !== false || strpos($city,'haifa') !== false){
				$city = 'Haifa';
			}
}
			return $city;

		} else {

			// Pull from the highest ancestor term 'location'
			$ancestors = get_ancestors($location->term_id,'location');
			if(isset($ancestors[1]))
				$location = get_term($ancestors[1],'location');

			return $location->name;
		}
	}
	
	public function get_link(){
		$event_id = $this->post->ID;
		$program = $this->get_program();
		$program_permalink = get_permalink($program->ID);
		$event_permalink = $program_permalink.'?event_id='.$event_id;
		return $event_permalink;
	}

	public function get_program($first_one = true) {
		
		$programs = $this->gf('related_to_program');
		
		
		if($first_one)
			if(is_array($programs))
				$programs = $programs[0];
		
		return $programs;
	}

	// is the event in the past?
	public function is_passed(){
		$event_date_time = $this->gf('event_date_time');
		$event_date_time = strtotime($event_date_time);
		$now = time();
		if($event_date_time < $now)
			return true;
		else
			return false;
	}

	public function get_name(){
		return $this->gf('event_featured_name');
	}
	
	public function get_event_by_api_id($api_id,$lang=false){

		$events = $this->get_events_by_api_id($api_id,$lang);
		if(isset($events[0]))
			$event = $events[0];
		else
			$event = false;


		return $event;

	}
	
	public function get_events_by_api_id($api_id,$lang=false){

		$tax_query = [];
		if($lang){
			$tax_query = array(
				array(
					'taxonomy' => 'language', // WPML taxonomy for languages
					'field' => 'slug', // Query by slug
					'terms' => $lang, // The language slug you want
				),
			);
		}

		// Get only of the current language in WPML
		$events = get_posts(array(
			'post_type' => 'event',
			'meta_key' => 'event_api_id',
			'meta_value' => $api_id,
			'posts_per_page' => 2,
			'suppress_filters' => false,
			'tax_query' => $tax_query,
			'fields' => 'ids',
		));

		return $events;

	}

}