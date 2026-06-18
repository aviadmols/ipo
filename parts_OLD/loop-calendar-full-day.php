<?php 

global $theme;

if(!isset($e_class))
	$e_class = '';

if($ipo_calendar_day->is_current){
	$e_class .= ' current-day active ';
} else {
	$e_class .= ' '.$ipo_calendar_day->is_past_or_future.' ';
}

$day_numeric = $ipo_calendar_day->day;
if($day_numeric == 1){
	$day_numeric = $day_numeric . '.' . ltrim($ipo_calendar_day->month,"0");
}
$events_popup = '';
$events_html = '';
$events_popup_html = '';
if($ipo_calendar_day->events){
	
	
	$events = explode(',',$ipo_calendar_day->events);
	
	if($events && is_string($events)){
		$events = array($events);
	}
	
	$has_events = 0;


	// First we reorder them by time
	$events_by_hour = [];
	foreach($events as $event){
		$event = new ipo_event($event);
		$time = $event->get_time(false);
		$strtotime = strtotime($time);
		$events_by_hour[$strtotime] = $event;
	}

	/*
	echo '<div class="events_by_hour" style="display:none;">';
	print_r($events_by_hour);
	echo '</div>';
	*/

	foreach($events as $event){

		if(!is_object($event))
			$event = new ipo_event($event);
		
		
		$program = $event->get_program();
		if(!$program)
			continue;
		//print_r($program);

		$has_events++;
		
		$program = new ipo_program($program);
		
		$link = $program->get_link();
		$image = $program->get_image('thumbnail');
		
		$image = $theme->get_the_bg_image($image,['size'=>'thumbnail']);

		// <span class="day">'. __( sprintf("יום %s'",$event->get_day()),'ipo').'</span>

		$events_popup_html .= '
			<li>
				<div class="media">'.$image.'</div>
				<div class="details">
					<div class="meta">
						<div class="time">
							<span class="time">'.$event->get_time().'</span> | <span class="location">'.$event->get_location_str().'</span>
						</div>
						<div class="title">
							<a href="'.$link.'">
								'.$program->get_title().'
								<div style="display: flex;" class="link-arrow">
									<img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt="">
								</div>
							</a>
						</div>
						<div class="subtitle">
							'.$program->gf('program_subtitle').'
						</div>
					</div>
				</div>
			</li>
		';
		$events_html .= '
			<div class="event" data-event_id="'.$event->post->ID.'" data-program_id="'.$program->post->ID.'">
				<a class="overlay-link" href="'.$link.'"></a>
				'.$image.'
			</div>
		';
	
	}

	if($has_events > 1){
		$e_class .= ' multi-event ';
	}

	if($has_events)
		$e_class .= ' has-events ';

	if($events){
		$events_popup = '
		<div class="calendar-hint-popup">
			<div class="popup-header">
				<div class="day">'.$day_numeric.'</div>
				<div class="month">'.$ipo_calendar_day->month_name_full.'</div>
				<span class="sep">|</span>
				<div class="weekday">
					'.__('יום','ipo').' '. $event->get_day_name() .'
				</div>
			</div>
			<ul class="events-list">'.$events_popup_html.'</ul>
		</div>';
	}

}

echo '
	<li class="loop-day '.$e_class.'" data-events="'.$ipo_calendar_day->events.'" data-day="'.$ipo_calendar_day->day.'">
		<div class="contents">
			'.$events_html.'
			
			<label><span>'.$day_numeric.'</span></label>
		</div>
		'.$events_popup.'
	</li>
';

?>