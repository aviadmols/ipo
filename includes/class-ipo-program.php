<?php

class ipo_program{
	
	public $post;
	
	public function __construct($post = false) {

		if(!$post){
			$this->post = null;
		} else if(is_int($post)){
			$this->post = get_post($post);
		} else {
			$this->post = $post;
		}
		
	}

	public function add_event($event_id){
		$program_id = $this->post->ID;
		$ipo_created_events = get_field('ipo_created_events',$program_id);
		if(!$ipo_created_events){
			$ipo_created_events = array();
		}
		$ipo_created_events[] = $event_id;
		update_field('ipo_created_events',$ipo_created_events,$program_id);
	}
	
	public function update_events($events,$append = true){

		if(isset($this->post->ID)){
			$program_id = $this->post->ID;
			$ipo_created_events = get_field('ipo_created_events',$program_id);
		} else {
			$program_id = false;
			$ipo_created_events = false;
		}

		if(!$ipo_created_events){
			$ipo_created_events = array();
		}
		if($append && is_array($ipo_created_events) && is_array($events) && count($ipo_created_events) > 0){
			$ipo_created_events = array_merge($ipo_created_events,$events);
		} else {
			$ipo_created_events = $events;
		}

		//$ipo_created_events_ids = array();

		// Get existing events
		if($append)
			$ipo_created_events_ids = get_field('ipo_created_events',$program_id);
		else
			$ipo_created_events_ids = array();

		// $event->post->ID
		foreach($events as $event){

			$created_event = false;
			if(is_int($event)){
				$created_event = $event;
			} else {
				$created_event = $event->post->ID;
			}

			// Check the WPML language of the event
			$event_language = apply_filters( 'wpml_post_language_details', NULL, $created_event );

			// Check the current language of the program
			$program_language = apply_filters( 'wpml_post_language_details', NULL, $program_id );

			// If the event language is different from the program language, then we need to skip this event
			if($event_language['language_code'] != $program_language['language_code']){
				continue;
			}

			$ipo_created_events_ids[] = $created_event;

		}

		// remove duplicates
		$ipo_created_events_ids = array_unique($ipo_created_events_ids);
	
		update_field('ipo_created_events',$ipo_created_events_ids,$program_id);

	}
	
	public function has_events() {
    if (!isset($this->post->ID))
        return false;

    $post_id = $this->post->ID;
    $ipo_created_events = get_related_event_ids($post_id);

    if (is_array($ipo_created_events))
        $total_ipo_created_events = count($ipo_created_events);
    else
        $total_ipo_created_events = 0;

    $has_events = false;

    if ($total_ipo_created_events > 0) {
        foreach ($ipo_created_events as $ipo_created_event) {
            $event = new ipo_event($ipo_created_event);
            $event_is_in_past = $event->is_passed();

            if (!$event_is_in_past) {
                $has_events = true;
                break;
            }
        }
    } else {
        $has_events = false;
    }

    return $has_events;
}

	public function get_playlist(){
		$html = '';
		if($this->gf('program_plan')){
			$html = '<a class="playlist" href="'.$this->get_link().'#playlist" style="z-index: 1;"><img src="/wp-content/uploads/2022/08/small_icons.svg" class="w-100" alt=""></a>';
		}
		return $html;
	}

	public function the_playlist(){
		$playlist = $this->get_playlist();
		echo $playlist;
	}
	
	public function get_link(){
		return get_permalink($this->post->ID);
	}
	
	
	public function get_title(){
		// Check if 'program_title_override' is set
		$program_title_override = $this->gf('program_title_override');
		if($program_title_override){
			return $program_title_override;
		}
		return get_the_title($this->post->ID);
	}
	
	public function get_subtitle(){
		return $this->gf('program_subtitle');
	}
	
	public function get_type(){
		
		$event_type = wp_get_post_terms($this->post->ID,'event_type');
		if($event_type && is_array($event_type)){
			$event_type = $event_type[0];
		} else return '';

		return $event_type;
	}
	
	public function get_image(){
		$program_image = get_field('program_banner_image',$this->post->ID);
		if(!$program_image)
			$program_image = get_field('placeholder_program_image','option');
		
		return $program_image;
	}
	
	
	public function gf($key,$default = ''){
		$val = get_field($key,$this->post->ID);
		if($val == ''){
			$val = $default;
		}
		return $val;
	}
	
	public function get_events(){
		$events = $this->gf('ipo_created_events');
		// events is an array of ids. Check that all are actual events
		if(!is_array($events))
			return array();
		foreach($events as $key => $event){
			$post_type = get_post_type($event);
			if($post_type != 'event'){
				unset($events[$key]);
			}
		}
		return $events;
	}
	
	public function get_events_api_ids(){
		$events = $this->get_events();
		$events_api_ids = array();
		foreach($events as $event){
			$event_api_id = get_field('event_api_id',$event);
			if($event_api_id){
				$events_api_ids[] = $event_api_id;
			}
		}
		return $events_api_ids;
	}
	
	public function get_locations(){
		$events = $this->get_events();
		$locations = array();
		foreach($events as $event){
			$event_location = wp_get_post_terms($event,'location');
			if($event_location && is_array($event_location)){
				$locations[] = $event_location;
			}
		}
		return array_unique($locations);
	}

	public function get_latest_date(){

		$farthest_date = '';
		$events = $this->get_events();

		foreach( $events as $event ){

			$event_date = get_field('event_date_time',$event);
			$event_date = strtotime($event_date);
			if($event_date > strtotime($farthest_date)){
				$farthest_date = date('Y-m-d',$event_date);
			}
		}

		return $farthest_date;
	}

	public function update_title($title){
		$post_data = array(
			'ID' => $this->post->ID,
			'post_title' => $title,
		);
		wp_update_post($post_data);
	}

	public function update_details(){
		
		
		// Get 1 event
		$events = $this->get_events();
		$event = $events[0];
		$event = new ipo_event($event);


		// Get event details
		$program_title = $event->get_name();


		// Furthest date
		$this->update_furthest_date();


		// Update program title - if title is not set, then use event feature name
		//$event_name = $event->get_name();

		/*
		$title = $this->get_title();

		if(!$title){
			echo 'Program title is not set. Updating to '.$event_name.'<br>';
			$this->update_title($event_name);
			echo 'Updated program title to '.$event_name.'<br>';
		}
		*/


	}

	public function update_furthest_date(){

		$events = $this->get_events();

		$farthest_date = '';
		foreach( $events as $event ){

			$event_date = get_field('event_date_time',$event);
			if(strtotime($event_date) > strtotime($farthest_date)){
				// Get format from wordpress options
				$format = 'Y-m-d';
				$farthest_date = date($format,strtotime($event_date));
			}
		}
		update_field('farthest_date',$farthest_date,$this->post->ID);

		

	}

	public function get_furthest_date(){
		return $this->gf('furthest_date');
	}

	public function get_api_events(){
		$events = $this->gf('ipo_api_select_event');
		return $events;
	}
	
	public function find_existing($name){
		// Find existing program by title
		$program = get_page_by_title($name,'OBJECT','program');
		if($program){
			$this->post = $program;
			return new ipo_program($program);
		} else {
			return false;
		}
	}

	public function get_lang(){
		// Get post language from WPML
		$lang = apply_filters( 'wpml_post_language_details', NULL, $this->post->ID );
		if($lang){
			return $lang['language_code'];
		} else {
			return 'he';
		}
	}
	

}