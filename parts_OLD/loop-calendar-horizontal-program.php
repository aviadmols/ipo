<?php 

// TODO: add all these to the event class

global $theme;

if(!isset($program_id)){
	$program_id = false;
}

if(!isset($events)){
	$events = [];
}

//echo '<!-- all events: '.print_r($events,true).' -->';

// If no program id provided, but we have events, get the program id from the first event
if(!$program_id && $events && is_array($events)){
	$event = new ipo_event($events[0]);
	$program_id = $event->get_program();
}

// if no program id provided, and no events, then check if $post_id is set
if(!$program_id && !$events && isset($post_id)){
	$event = new ipo_event($post_id);
	$program_id = $event->get_program();
}

// If no program id provided, and no events, and no post_id, then we have a problem
if(!$program_id && !$events && !isset($post_id)){
	echo '<!-- No program id provided, and no events, and no post_id -->';
	return;
}

if(is_array($events)){

	if(isset($events['events']))
		$events = $events['events'];

	$event_id = implode(',',$events);
	
}


//$event = new ipo_event($event_id);
//$program_id = $event->get_program();


$program = new ipo_program($program_id);

$program_title = $program->get_title();
$program_subtitle = $program->gf('program_subtitle');
$program_image = $program->get_image();

$program_image_src = $theme->get_image($program_image);
$program_image_alt = $theme->get_image_alt($program_image);

$program_location_str = '';



$events_html = '';

foreach($events as $event){

	$event_obj = new ipo_event($event);

	$event_date = $event_obj->get_date();

	


	$event_date_time = $event_obj->get_time();

	$date_format = get_option( 'date_format' );
	//$event_date_str = date_i18n('d/m',$event_date);
	$event_date_str = date_i18n($date_format, strtotime($event_date));

	$event_day = $event_obj->get_day();
	$event_day = __(sprintf('יום %s\'',$event_day));
	
	$event_city = $event_obj->get_city();

	$event_link = '';

	$events_html .= '
	<li data-event_id="'.$event.'" data-original-event-date="'.$event_date.'">
		<a href="'.get_permalink($event).'" class="event-link">
		<div class="">
			<p class="date">'.$event_date.'</p>
			<p class="d-flex"><span>'.$event_day.'</span><span>'.$event_date_time.'</span></p>
		</div>
		<div style="display: flex;">
			<span class="location">'.$event_city.'</span> <img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt="">
		</div>
		</a>
	</li>';
}

if($events_html)
	$events_html = '<ul>'.$events_html.'</ul>';

echo '
	<div class="item loop-event loop-calendar-horizontal-event " data-program_id="'.$program_id.'" data-event_id="'.$event_id.'" data-test="2">
		<div class="img_box">
			<div class="row">
				
			<div class="order-1 order-lg-1">
					<div class="position-relative">
						<img src="'.$program_image_src.'" class="horizontal-img" alt="'.$program_image_alt.'">
						<a class="playlist" href="#"><img src="/wp-content/uploads/2022/08/small_icons.svg" class="w-100 h-100" alt=""></a>
					</div>
				</div>
				
				<div class="col-lg-5 order-2 order-lg-2">
					<h4>'.$program_title.'</h4>
					<p class="text">'.$program_subtitle.'</p>
					'.$events_html.'
				</div>
				
					
			</div>
		</div>
	</div>
';

?>