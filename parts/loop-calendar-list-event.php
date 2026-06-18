<?php 

// TODO: add all these to the event class

if(isset($post_id)){
	$event_id = $post_id;
} 

global $theme;

$program_id = get_field('related_to_program',$event_id);
if(is_array($program_id)) $program_id = $program_id[0];

// check get_permalink($program_id) to see if it exists
// if it doesn't, then return
if(get_permalink($program_id) == false){
	return;
}

		
	
$post_status = get_post_status($program_id);

$program = new ipo_program($program_id);
$event = new ipo_event($event_id);


$program_title = $program->get_title();
$program_subtitle = $program->gf('program_subtitle');
$program_image = $program->get_image();
$program_type = $program->get_type();
if(is_object($program_type)){
	$program_type = $program_type->term_id;
}

$program_image_src = $theme->get_image($program_image);
$program_image_alt = $theme->get_image_alt($program_image);
$event_location = $event->get_location();
/*
$location_name = $event_location->name;
$location_parent_name = '';
$location_gradnparent_name = '';
$location_gradnparent_id = false;

if($event_location->parent){
	$parent = get_term($event_location->parent);
	$location_parent_name =  $parent->name;
	if($parent->parent){
		$gradnparent = $parent->parent;
		$gradnparent = get_term($gradnparent);
		$location_gradnparent_name = $gradnparent->name;
		$location_gradnparent_id = $gradnparent->term_id;
	}
}


$event_location_str = $location_gradnparent_name;
*/
//$event_location_str = $event->get_location_str();
$event_location_str = $event->get_city();
//$event_location_str_mobile = 'ברונפמן, ת"א';




$event_location_str_mobile = $event->get_city();
$location_gradnparent_id = $event->get_city();


/*
$event_location_str = '';
if(isset($event_location->parent) && $event_location->parent){
	$parent = $event_location->parent;
	if(isset($event_location->parent) && $event_location->parent){
		
	}
}
*/
	
$event_date = $event->gf('event_date'); 
$event_date_time = $event->gf('event_date_time');

$is_today = '';
if(date('Y-m-d') == date('Y-m-d',strtotime($event_date))){
	$is_today = '<span class="today">';
if(ICL_LANGUAGE_CODE == 'he'){
$is_today = $is_today . 'היום';
}else {
  $is_today = $is_today . 'Today';
}


$is_today = $is_today .'</span>';
}

$event_date_time = date('H:i',strtotime($event_date_time));

/*
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

if($event_day != 'ש'){
	$event_day = 'יום '.$event_day;
} else {
	$event_day = 'שבת';
}
*/
if (date('Y-m-d') <= date('Y-m-d', strtotime($event_date)) || date('m') != date('m', strtotime($event_date))) {


if(ICL_LANGUAGE_CODE == 'he'){
	if($event->get_day() == 'ש'){
		$event_day = 'יום שבת';
	} else {
		$event_day = 'יום ' . $event->get_day() . "'";
	}

} else {
$timestamp = strtotime($event_date);
	$event_day = date('D',$timestamp);

}
	

$event_link = get_permalink($event_id);

$program_artists = get_field('program_artists',$program_id);

$artists_title = '';
if(is_array($program_artists)){
	$artists_title = array();
	foreach($program_artists as $artist){
		$artist = get_post($artist);
		$artists_title[] = $artist->post_title;
	}
	$artists_title = implode(', ',$artists_title);
	// Remove all " and ' characters
	$artists_title = str_replace('"','',$artists_title);
	$artists_title = str_replace("'","",$artists_title);
}

$passed_event_str = 'אירוע עבר';
$event_btn_str = 'לפרטים';

if(ICL_LANGUAGE_CODE == 'en'){
	$passed_event_str = 'Passed';
	$event_btn_str = 'Details';
}

$btn = '';
// If event is in the past
if(strtotime($event_date) < strtotime(date('Y-m-d'))){

	$btn = '<a class="event-link disabled" href="'.$event_link.'">'. $passed_event_str .'</a>';

} else {
	
	$btn = '<a class="event-link" href="'.$event_link.'">'.$event_btn_str.'</a>';

}

echo '
	<li class="item loop-event loop-calendar-list-event '. $post_status .'" data-artist="'.$artists_title.'" data-keywords="test-keywords keyword" data-program_id="'.$program_id.'" data-event_id="'.$event_id.'" data-event_type="'.$program_type.'" data-event_location="'.$location_gradnparent_id.'">
		
		<a class="overlay-link" href="'.$event_link.'"></a>
	
		<div class="img_box">
			<div class="row">
				<div class="ipo-list-right">
					<div class="ipo-program-details">
						'.$is_today.'
						<h4>'.$program_title.'</h4>
						<p class="text">'.$program_subtitle.'</p>
						'.$events_html.'
					</div>
					<div class="ipo-program-image">
							'.$theme->get_the_bg_image($program_image).'
							<a class="playlist" href="#"><img src="/wp-content/uploads/2022/06/playlist.png" class="w-100 h-100" alt=""></a>
					</div>
				</div>
				<div class="ipo-list-left desktop-only">
					<div class="ipo-event-details">
					
						<span class="date">'.date('d.m.Y',strtotime($event_date)).'</span>
						<div class="time-and-day">
							<span class="time">'.$event_date_time.'</span>
							<i class="sep">|</i>
							<span class="day">'.$event_day.'</span>
						</div>
						
					</div>
					<div class="ipo-event-location">
						<a href="'.$event_link.'"><span>'.$event_location_str.'</span> <img src="'. ipo_arrow_icon_url() .'" class="arrow" alt=""></a>
					</div>
				</div>
				<div class="ipo-list-left mobile-only">

					<div class="event-date">
						<span class="date">'.date('d.m',strtotime($event_date)).'</span>
					</div>
					<div class="ipo-event-details">
						<div class="time-and-day">
							<span class="time">'.$event_date_time.'</span>
							<i class="sep">|</i>
							<span class="day">'.$event_day.'</span>
						</div>

						<div class="ipo-event-location">
							<span>'.$event_location_str_mobile.'</span>
						</div>
					</div>
					'.$btn.'
					
				</div>
			</div>
		</div>
	</li>
';
}
?>