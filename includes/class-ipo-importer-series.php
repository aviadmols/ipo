<?php 

if ( ! function_exists( 'post_exists' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' );
}

class ipo_importer {
	
	public $msg;
	public $data;
	
	public $artist_plan_id;
	public $raw_url;

	public $importer_lang;
	
	public function __construct($url,$amount,$offset) {

		$this->raw_url = $url . '?num_posts='.$amount.'&offset='.$offset.'&msg_state=false';

		// Get page content from raw_url using wp_remote_get
		
		$this->data = wp_remote_get( $this->raw_url );
		$this->importer_lang = 'en';

		// Get response body from $this->data
		$this->data = wp_remote_retrieve_body( $this->data );

		/*
		// Get content from raw_url using CURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->raw_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$this->data = curl_exec($ch);
		curl_close($ch);

		// check if curl returned an error
		if (curl_errno($ch)) {
			$this->add_msg('Curl error: ' . curl_error($ch));
		}
		*/


		$this->msg = [];

		$this->add_msg('url: ' . print_r($this->raw_url,true));
		//$this->add_msg('data: ' . print_r($this->data,true));

	}
	
	public function add_msg($string){
		$format = 'Y-m-d H:i:s';
		$date = date($format);
		// Modify time to be Israel Zone
		$date = date($format, strtotime($date . ' +3 hours'));
		$this->msg[] = '<tr><td>' . $date . ' </td><td> ' . $string . '</td></tr>';
	}
	

	public function decode_data(){

		//$this->add_msg('decoding data - ' . print_r($this->data),true);

		if(is_array($this->data)){
			$this->add_msg('data not encoded. Containing ' . count($this->data) . ' nodes');
			return true;
		} else {
			$this->add_msg('data encoded. Decoding...');
			//$this->add_msg(print_r($this->data,true));
		}

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
	

	public function update_artist_plan($item){



		/*
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
		
		$program = new ipo_program($this->program_id);
		$program->update_furthest_date();
		
		// Get name of the image file
		$url = $item->banner_image;
		$filename = basename($url);

		// remove the extension
		$filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);

		// Check if attachment with image with same filename exists
		$attachment = get_page_by_title( $filename, OBJECT, 'attachment' );
		if($attachment){
			// Attachment exists, set it as featured image
			$this->add_msg('attachment exists as image id ['.$attachment->ID.'], setting as banner');
			update_field('program_banner_image',$attachment->ID,$this->program_id);
		} else {
			// Upload banner image
			$img = wp_insert_attachment_from_url($item->banner_image);
			if(is_wp_error($img)){
				$this->add_msg('Program banner cannot be updated ['. $img->get_error_message() .']');
			}else if(!$img){
				$this->add_msg('Program banner from url ['.$item->banner_image.'] could not be updated ['. $img .']');
			}else{
				update_field('program_banner_image',$img,$this->program_id);
				$this->add_msg('Program banner updated ['. $img .']');
			}
		}


		
		// Update event details
		update_field('program_length_concert',$item->event_length,$this->program_id);
		update_field('program_price_range',$item->event_price_range,$this->program_id);
		*/
	}
	
	public function insert_post($args){
		$res = wp_insert_post( $args );
		return $res;
	}
	
	public function extract_and_update_serie($item){

		if(is_object($item)){
			
			$item = (array) $item;
			//$this->add_msg('file is object: ' . print_r($item,true));
			//return false;
		}

		$title = $item ['post_title'];
		$content = $item ['post_content'];
		$featured_image_url = $item ['featured_image_url'];
		$featured_image_alt = $item ['featured_image_alt'];



		$serie_id = false;

		// Check if this serie already exist based on the post_title
		$serie = get_page_by_title( $title, OBJECT, 'serie' );
		if($serie){
			// serie exists, update it
			$this->add_msg('serie exists as post id ['.$serie->ID.']['.$serie->post_title.'], updating it');
			$serie_id = $serie->ID;
		} else {
			// serie does not exist, create it
			$this->add_msg('serie ['.$title.'] does not exist, creating it');
			$args = [
				'post_title' => $title,
				'post_status' => 'publish',
				'post_type' => 'serie',
			];
			$serie_id = $this->insert_post($args);
			if(is_wp_error($serie_id)){
				$this->add_msg('serie cannot be created ['. $serie_id->get_error_message() .']');
				return false;
			}else if(!$serie_id){
				$this->add_msg('serie cannot be created ['. $serie_id .']');
				return false;
			}else{
				$this->add_msg('serie created ['. $serie_id .']');
			}
		}

		// Update content into "content" acf field
		$content = htmlspecialchars_decode($content);
		update_field('description',$content,$serie_id);



		// Updating artist plan banner
		$featured_image_title = basename($featured_image_url);
		$featured_image_title = sanitize_file_name( pathinfo( $featured_image_title, PATHINFO_FILENAME ) );

		$main_attachment = get_page_by_title( $featured_image_title, OBJECT, 'attachment' );
		if($main_attachment){
			// Attachment exists, set it as featured image
			$this->add_msg('main_attachment exists as image id ['.$main_attachment->ID.'], setting as banner');
			update_field('banner_image',$main_attachment->ID,$serie_id);
		} else {
			// Upload banner image
			$img = wp_insert_attachment_from_url($featured_image_url);
			if(is_wp_error($img)){
				$this->add_msg('main_attachment cannot be updated ['. $img->get_error_message() .']');
			}else if(!$img){
				$this->add_msg('main_attachment from url ['.$item->banner_image.'] could not be updated ['. $img .']');
			}else{
				// update acf field 'banner_image'
				update_field('banner_image',$img,$serie_id);
				$this->add_msg('main_attachment uploaded and updated ['. $img .']');
			}
		}


		if($serie_id){


			$events = $item ['selected_events'];
			$event_ids = [];
			foreach($events as $event){
				$title = $event;
				// Find event by title
				$event = get_page_by_title( $title, OBJECT, 'event' );
				// If event found, add it to the event_ids array
				if($event){
					$event_ids[] = $event->ID;
					$this->add_msg('event ['.$title.'] found as post id ['.$event->ID.']['.$event->post_title.']');
				}
			}

			
			update_field('events', $event_ids, $serie_id);
			update_field('imported_field', true, $serie_id);

			return $serie_id;


		}	

		return false;

		/*
		// Let's check if the event exist
		$event_id = false;
		$event_title = $item->title . ' | ' . $item->event_date . ' ' . $item->location->event_time;
		$existing_event = post_exists( $event_title );
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
		*/
	}
	
	public function get_serie_translation($item,$lang){
	
		if(isset($item->post_en)){
			$eng_obj = $item->post_en;
			return $eng_obj;
		} else {
			return false;
		}
	
	}
	
	public function connect_serie_to_translation($original_id,$translation_id,$post_type,$lang='en'){
		
        $wpml_element_type = apply_filters( 'wpml_element_type', $post_type );
        $get_language_args = array('element_id' => $original_id, 'element_type' => $post_type );
        $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );


         
        $set_language_args = array(
            'element_id'    => $translation_id,
            'element_type'  => $wpml_element_type,
            'trid'   => $original_post_language_info->trid,
            'language_code'   => 'en',
            'source_language_code' => $original_post_language_info->language_code
        );

		$this->add_msg('connect_serie_to_translation: '.print_r($set_language_args,true));

		$this->add_msg(print_r($wpml_element_type,true));
		$this->add_msg(print_r($get_language_args,true));
		$this->add_msg(print_r($original_post_language_info,true));

        do_action( 'wpml_set_element_language_details', $set_language_args );
		
	}

}