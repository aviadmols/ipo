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
	
	// TODO
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
	
	public function extract_and_update_artist_plan($item){

		if(is_object($item)){
			
			$item = (array) $item;
			//$this->add_msg('file is object: ' . print_r($item,true));
			//return false;
		}

		$title = $item ['post_title'];
		$content = $item ['post_content'];
		$featured_image_url = $item ['featured_image_url'];
		$featured_image_alt = $item ['featured_image_alt'];



		$artist_plan_id = false;

		// Check if this artist_plan already exist based on the post_title
		$artist_plan = get_page_by_title( $title, OBJECT, 'artist_plan' );
		if($artist_plan){
			// Artist_plan exists, update it
			$this->add_msg('artist_plan exists as post id ['.$artist_plan->ID.']['.$artist_plan->post_title.'], updating it');
			$artist_plan_id = $artist_plan->ID;
		} else {
			// Artist_plan does not exist, create it
			$this->add_msg('artist_plan ['.$title.'] does not exist, creating it');
			$args = [
				'post_title' => $title,
				'post_status' => 'publish',
				'post_type' => 'artist_plan',
			];
			$artist_plan_id = $this->insert_post($args);
			if(is_wp_error($artist_plan_id)){
				$this->add_msg('artist_plan cannot be created ['. $artist_plan_id->get_error_message() .']');
				return false;
			}else if(!$artist_plan_id){
				$this->add_msg('artist_plan cannot be created ['. $artist_plan_id .']');
				return false;
			}else{
				$this->add_msg('artist_plan created ['. $artist_plan_id .']');
			}
		}

		// Update content into "content" acf field
		$content = htmlspecialchars_decode($content);
		update_field('content',$content,$artist_plan_id);



		// Updating artist plan banner
		$featured_image_title = basename($featured_image_url);
		$featured_image_title = sanitize_file_name( pathinfo( $featured_image_title, PATHINFO_FILENAME ) );

		$main_attachment = get_page_by_title( $featured_image_title, OBJECT, 'attachment' );
		if($main_attachment){
			// Attachment exists, set it as featured image
			$this->add_msg('main_attachment exists as image id ['.$main_attachment->ID.'], setting as banner');
			update_field('banner_image',$main_attachment->ID,$artist_plan_id);
		} else {
			// Upload banner image
			$img = wp_insert_attachment_from_url($featured_image_url);
			if(is_wp_error($img)){
				$this->add_msg('main_attachment cannot be updated ['. $img->get_error_message() .']');
			}else if(!$img){
				$this->add_msg('main_attachment from url ['.$item->banner_image.'] could not be updated ['. $img .']');
			}else{
				// update acf field 'banner_image'
				update_field('banner_image',$img,$artist_plan_id);
				$this->add_msg('main_attachment uploaded and updated ['. $img .']');
			}
		}

		$programs = $item ['programs'];

		if($artist_plan_id){

			/*
			$rows = get_field('programs', $artist_plan_id );
			if(!$rows){
				$rows = [];
			}
			*/
			$rows = [];
			
			foreach($programs as $program){
				
				$row = [];

				if(is_object($program)){
					
					$program = (array) $program;
					//$this->add_msg('program is object: ' . print_r($program,true));
					//return false;
				}

				$row['title'] = $program['program_title'];
				$row['date_text'] = $program['program_date_text'];

				$row['artist_text'] = $program['program_artist_text'];
				// Decode html entities
				$row['artist_text'] = htmlspecialchars_decode($row['artist_text']);



				// Check if attachment with image with same filename exists
				$filename = $program['program_image_url'];
				$this->add_msg('program_image_url: ['. $filename .']');

				$file_title = basename($filename);
				$file_title = sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) );

				$attachment = get_page_by_title( $file_title, OBJECT, 'attachment' );
				if($attachment){
					// Attachment exists, set it as featured image
					$this->add_msg('attachment exists as image id ['.$attachment->ID.'], setting as banner');
					$row['image'] = $attachment->ID;
				} else {
					// Upload banner image
					$img = wp_insert_attachment_from_url($program['program_image_url']);
					if(is_wp_error($img)){
						$this->add_msg('Program banner cannot be updated ['. $img->get_error_message() .']');
					}else if(!$img){
						$this->add_msg('Program banner from url ['.$item->banner_image.'] could not be updated ['. $img .']');
					}else{
						$row['image'] = $img;
						$this->add_msg('Program banner uploaded and updated ['. $img .']');
					}
				}
				

				/*
				$row['artist_text'] = $program['program_image_url'];
				$row['artist_text'] = $program['program_image_alt'];
				*/

				$row['details'] = $program['program_detail'];

				$event_tel_aviv = $program['event_tel_aviv'];
				$event_haifa = $program['event_haifa'];
				$event_jerusalem = $program['event_jerusalem'];
				
				/* 
				 * EVENTS TEL AVIV 
				 */

				$events_tel_aviv = [];
				foreach($event_tel_aviv as $event){

					if(is_object($event)){
						$event = (array) $event;
					}
					$title = $event['post_title'];
					$date_time = $event['events_datetime'];
					$title_to_search = $title . ' | ' . $date_time;
					// Find event post by title_to_search
					$event_found = get_page_by_title( $title_to_search, OBJECT, 'event' );
					if($event_found){
						$this->add_msg('event_tel_aviv found ['.$event_found->ID.']['.$title_to_search.']');
						$events_tel_aviv[] = $event_found->ID;
					} else 
						$this->add_msg('event_tel_aviv not found ['.$title_to_search.']');
				}

				if(!empty($events_tel_aviv)){
					$row['events_tel_aviv'] = $events_tel_aviv;
				}

				/* 
				 * EVENTS JERUSALEM 
				 */

				$events_jerusalem = [];
				foreach($event_jerusalem as $event){

					if(is_object($event)){
						$event = (array) $event;
					}
					$title = $event['post_title'];
					$date_time = $event['events_datetime'];
					$title_to_search = $title . ' | ' . $date_time;
					// Find event post by title_to_search
					$event_found = get_page_by_title( $title_to_search, OBJECT, 'event' );
					if($event_found){
						$this->add_msg('event_jerusalem found ['.$event_found->ID.']['.$title_to_search.']');
						$events_jerusalem[] = $event_found->ID;
					} else 
						$this->add_msg('event_jerusalem not found ['.$title_to_search.']');
				}

				if(!empty($events_jerusalem)){
					$row['events_jerusalem'] = $events_jerusalem;
				}

				/* 
				 * EVENTS HAIFA 
				 */

				$events_haifa = [];
				foreach($event_haifa as $event){

					if(is_object($event)){
						$event = (array) $event;
					}
					$title = $event['post_title'];
					$date_time = $event['events_datetime'];
					$title_to_search = $title . ' | ' . $date_time;
					// Find event post by title_to_search
					$event_found = get_page_by_title( $title_to_search, OBJECT, 'event' );
					if($event_found){
						$this->add_msg('event_haifa found ['.$event_found->ID.']['.$title_to_search.']');
						$events_haifa[] = $event_found->ID;
					} else 
						$this->add_msg('event_haifa not found ['.$title_to_search.']');
				}

				if(!empty($events_haifa)){
					$row['events_haifa'] = $events_haifa;
				}

				$rows[] = $row;

			}

			// Update the 'program' repeater field with key 'field_6359365cbb95a'
			update_field('field_6359365cbb95a', $rows, $artist_plan_id);
			update_field('imported_field', true, $artist_plan_id);

			return $artist_plan_id;


		}	

		$this->add_msg('extract_artist_plan of ' . $title);

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
	
	public function get_artist_plan_translation($item,$lang){
	
		if(isset($item->post_en)){
			$eng_obj = $item->post_en;
			return $eng_obj;
		} else {
			return false;
		}
	
	}
	
	public function connect_artist_plan_to_translation($original_id,$translation_id,$post_type,$lang='en'){
		
        $wpml_element_type = apply_filters( 'wpml_element_type', $post_type );
        $get_language_args = array('element_id' => $original_id, 'element_type' => $post_type );
        $original_post_language_info = apply_filters( 'wpml_element_language_details', null, $get_language_args );
         
        $set_language_args = array(
            'element_id'    => $translation_id,
            'element_type'  => $wpml_element_type,
            'trid'   => $original_post_language_info->trid,
            'language_code'   => 'en',
            'source_language_code' => 'he'
        );

		$this->add_msg('connect_serie_to_translation: '.print_r($set_language_args,true));
 
		$this->add_msg(print_r($wpml_element_type,true));
		$this->add_msg(print_r($get_language_args,true));
		$this->add_msg(print_r($original_post_language_info,true));

        do_action( 'wpml_set_element_language_details', $set_language_args );
		
	}

}