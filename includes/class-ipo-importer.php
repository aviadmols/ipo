<?php 

if ( ! function_exists( 'post_exists' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' );
}

class ipo_importer {
	
	public $msg;
	public $data;
	public $input;
	
	public $event_id;
	public $program_id;
	public $file_url;
	public $raw_url;
	public $ended;

	
	public function __construct($data = false) {

		$this->ended = false;

		$raw_url = false;
		$file_url = false;
		$json_input = false;

		if(isset($_POST['raw_url']))
			$raw_url = sanitize_text_field($_POST['raw_url']);

		if(isset($_POST['file_url']))
			$file_url = sanitize_text_field($_POST['file_url']);

		if(isset($_POST['json_input']))
			$json_input = sanitize_text_field($_POST['json_input']);

		if(is_array($data)){
			if(isset($data['url'])) $raw_url = $data['url'];
			if(isset($data['file'])) $file_url = $data['file'];
			if(isset($data['input'])) $json_input = $data['input'];
			$data = false;
		}

		if(!$data){

			if($raw_url){

				$this->raw_url = $raw_url;

				// Get content from raw_url using CURL
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->raw_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$data = curl_exec($ch);
				curl_close($ch);

				


			} else if($file_url){
				// Escape $_POST['file_url'] as text
				$this->file_url = $file_url;
				// Get the file contents from the URL
				$data = file_get_contents($this->file_url);

			} else if(isset($json_input)){
				$data = $json_input;
			}
		}
		$this->msg = [];
		$this->data = $data;
		$this->input = $data;
		$this->data_key = 0;

	}
	
	public function add_msg($string){
		$this->msg[] = $string;
	}
	
	public function decode_data(){

		// If the $this->data string has ',' in it, explode it into array
		$json = '';
		if(strpos($this->data, ',') !== false){
			$data_array = explode(',',$this->data);
			// Each data_array item will be base64_decoded and then json_decoded
			foreach($data_array as $key => $data_item){
				$data_array[$key] = json_decode(base64_decode($data_item),512, JSON_INVALID_UTF8_IGNORE);
			}
			$json = $data_array;
		} else {
			$package = base64_decode($this->data);
			$json = json_decode($package,false, 512, JSON_INVALID_UTF8_IGNORE);
		}



		$this->data = $json;
		if($json)
			$this->add_msg('data decoded with ' . count($this->data) . ' nodes');
		else
			$this->add_msg('error decoding data: '.json_last_error());
	}
	
	public function update_artists($item){
		
		// update artists
		$program_artists = $item->artists;
		$artists_to_add = [];
		foreach($program_artists as $artist){
			
			// check if artist exist
			$artist_id = false;
			
			$existing_artist = post_exists( $artist->post_title , '', '', 'artist' );
			if ( ! $existing_artist ) {
				$artist_data = (array) $artist;
				
				unset($artist_data['ID']);
				unset($artist_data['guid']);
				unset($artist_data['post_author']);
				
				$artist_data['post_type'] = 'artist';
				
				$artist_id = $this->insert_post( $artist_data );
				if($artist_id){
					
					$this->add_msg( 'Created artist '. $artist_id .': '. $artist_data['post_title'] );
					
				} else {
					
					if( is_wp_error( $artist_id ) ) {
						$this->add_msg( 'ERROR: $this->insert_post - ' . $artist_id->get_error_message() );
					} else {
						$this->add_msg( 'ERROR: $this->insert_post - unknown error for data '.print_r($artist_id,true) );
					}
					
				}
				
			} else {
				
				$this->add_msg( 'Artist ' . $existing_artist .' exists, added' );
				$artist_id = $existing_artist;
				
			}
			
					
			// Update artist image
			$img_url = $artist->image;
			if($img_url){
				$result = wp_insert_attachment_from_url($img_url,$artist_id);
				if(is_wp_error($result)){
					$result = $result->get_error_message();
					$this->add_msg(  'ERROR: wp_insert_attachment_from_url ['.$img_url.'] - ' . $result );
				} else {
					$res = set_post_thumbnail($artist_id,$result);
					$this->add_msg(  'Image ['.$result.'] added to artist ['.$artist_id.'] with state ['. print_r($res,true) .']');
					
				}
			} else {
				$this->add_msg(  'Skipping image');
			}
			
			// Update artist content
			update_field('main_text',$artist->post_content,$artist_id);
			
			// Update artist categories
			foreach($artist->categories as $artist_cat){
		
				$term_id = term_exists($artist_cat->name,'artist_cat');
				if(!$term_id){
					// Doesnt exist
					$term_id = wp_insert_term($artist_cat->name, 'artist_cat',['slug'=>$artist_cat->slug]);
					if (!is_wp_error($term_id)) {
						$term_id = $term_id['term_id'];
					}
				} else {
					// Exist
				}
				
				$term_id = (int)$term_id['term_id'];
				wp_set_object_terms($artist_id, [$term_id], 'artist_cat');
				$this->add_msg(  'updating artist '.$artist_id.' with artist_cat term: '.$term_id);
				
			}
			
			//update_field('short_desc',$artist->position,$artist_id);
			
			// Add to list of artists to add
			$artists_to_add[] = $artist_id;
		}
		
		$existing_program_artists = get_field('program_artists',$this->program_id);
		if($existing_program_artists){
			$existing_program_artists = array_merge($existing_program_artists,$artists_to_add);
		} else {
			$existing_program_artists = $artists_to_add;
		}
		
		foreach ($existing_program_artists as $key => $number) {
			if ($number === 0) {
				unset($existing_program_artists[$key]);
			}
		}
		
		$existing_program_artists = array_unique($existing_program_artists);
		$this->add_msg(  'Adding artists '.implode(',',$existing_program_artists));
		update_field('program_artists',$existing_program_artists,$this->program_id);
		
		return $artists_to_add;
	}
	
	public function update_program($item){

		
		// Append connected events to the program
		$connected_events = get_field('ipo_created_events',$this->program_id);
		if(!$connected_events){
			$connected_events = [];
		}
		
		$connected_events[] = $this->event_id;
		$connected_events = array_unique($connected_events);
		
		$this->add_msg('updating program '.$this->program_id.' with events: '.implode(',',$connected_events).' | adding '.$this->event_id);
		
		update_field('ipo_created_events',$connected_events,$this->program_id);
		
		// update content
		$content = htmlentities($item->content, null, 'utf-8');
		$content = str_replace("&nbsp;", "", $content);
		$content = html_entity_decode($content);
		update_field('program_description',$content,$this->program_id);
		
		
		// Upload banner image
		$img = wp_insert_attachment_from_url($item->banner_image);
		if(is_wp_error($img)){
			$this->add_msg('Program banner cannot be updated ['. $img->get_error_message() .']');
		}else{
			update_field('program_banner_image',$img,$this->program_id);
			$this->add_msg('Program banner updated ['. $img .']');
		}
		
		// Update event details
		update_field('program_length_concert',$item->event_length,$this->program_id);
		update_field('program_price_range',$item->event_price_range,$this->program_id);
		
	}
	
	public function update_concert_type($item){
		
		// update concert type
		$program_type = $item->type->label;
		$term_id = term_exists($program_type,'event_type');
		
		if(!$term_id || is_wp_error($term_id)){
			// Doesnt exist
			$term_id = wp_insert_term($program_type, 'event_type');
			if (!is_wp_error($term_id)) {
				$term_id = $term_id['term_id'];
			} else {
				
				$this->add_msg('failed updating program '.$this->program_id.' with event_type term ['. $term_id->get_error_message() .']');
				$term_id = false;
				
			}
		} else {
			// Exist
		}
		
		if($term_id){
			$term_id = (int)$term_id['term_id'];
			wp_set_object_terms($this->program_id, [$term_id], 'event_type');
			$this->add_msg('updating program '.$this->program_id.' with event_type term: '.$term_id);
		}
		
	}
	
	public function update_event($item) {
    
    // Update event related program only if the current value is empty
    $current_related_program = get_field('related_to_program', $this->event_id);
    if (empty($current_related_program)) {
        update_field('related_to_program', $this->program_id, $this->event_id);
    }
    
    // Update API ID
    update_field('event_api_id', $item->api_id, $this->event_id);
    update_field('ipo_api_select_event', $item->api_id, $this->event_id);
    
    // Update date & time
    $event_date_time = $item->event_date . ' ' . $item->location->event_time;
    update_field('event_date_time', $event_date_time, $this->event_id);
    update_field('event_date', $item->event_date, $this->event_id);
    
    // Update featured name
    update_field('event_featured_name', $item->title, $this->event_id);
    
    // Update locations
    update_field('imported_location', $item->location->venue, $this->event_id);
    update_field('imported_hall', $item->location->event_hall, $this->event_id);
}

	
	public function insert_post($args){
		$res = wp_insert_post( $args );
		return $res;
	}
	
	public function extract_event($item){

		$event_id = false;
		$event_title = $item->title . ' | ' . $item->event_date . ' ' . $item->location->event_time;
		$existing_event = false;

		if ( ! empty( $item->api_id ) ) {
			$ipo_event = new ipo_event();
			$existing_event = $ipo_event->get_event_by_api_id( $item->api_id );
		}

		if ( ! $existing_event ) {
			$existing_event = post_exists( $event_title, '', '', 'event' );
		}

		if ( ! $existing_event ) {
			
			// Event does not exist ,let's add it
			$event_data = [
				'post_title' => $event_title,
				'post_status' => 'publish',
				'post_type' => 'event',
			];
			$event_id = $this->insert_post( $event_data );
			if($event_id){
				
				$this->add_msg( 'Created event '. $event_id .': '. $event_title );
				
			} else {
				
				
				if( is_wp_error( $event_id ) ) {
					$this->add_msg( 'ERROR: $this->insert_post - ' . $event_id->get_error_message() );
				} else {
					$this->add_msg( 'ERROR: $this->insert_post - unknown error for data ' . print_r($event_id,true) );
				}
				
			}
		
		} else {
			
			$event_id = $existing_event;
			$this->add_msg( 'event '. $existing_event .' already exist: '. $item->title );
			$this->ended = true;
			
		}
		
		$this->event_id = $event_id;
		return $event_id;
		
	}
	
	public function extract_program($item){
		
		// Check if this program exist
		
		$event_id = false;
		$program_id = false;
		$api_id = $item->api_id;
		
		$existing_program = post_exists( $item->title );

		if ( ! $existing_program ) {

			// Collect data for program and add it
			
			$program_data = [
				'post_title' => $item->title,
				'post_status' => 'publish',
				'post_type' => 'program',
			];
			
			$program_id = $this->insert_post( $program_data );
			if($program_id){
				
				$this->add_msg( 'Created program '. $program_id .': '. $item->title );

			} else {
				
				if( is_wp_error( $program_id ) ) {
					$this->add_msg( 'ERROR: $this->insert_post - ' . $program_id->get_error_message() );
				} else {
					$this->add_msg( 'ERROR: $this->insert_post - unknown error for data ' . print_r($program_data,true) );
				}
				
			}

		} else {
			
			$program_id = $existing_program;
			$this->add_msg( 'program '. $existing_program .' already exist: '. $item->title );

		}
		
		$this->program_id = $program_id;
		return $program_id;
		
	}
	
	public function get_translation($item,$lang){
	
		if(isset($item->wpml_en->data)){
			$eng_obj = $item->wpml_en->data;
			return $eng_obj;
		} else {
			return false;
		}
	
	}
	
	public function connect_to_translation($original_id,$translation_id,$post_type,$lang='en'){
		
		
        $wpml_element_type = apply_filters( 'wpml_element_type', $post_type );
        $get_language_args = array('element_id' => $original_id, 'element_type' => $post_type );
        $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
         
        $set_language_args = array(
            'element_id'    => $translation_id,
            'element_type'  => $wpml_element_type,
            'trid'   => $original_post_language_info->trid,
            'language_code'   => $lang,
            'source_language_code' => $original_post_language_info->language_code
        );
 
        do_action( 'wpml_set_element_language_details', $set_language_args );
	}

}