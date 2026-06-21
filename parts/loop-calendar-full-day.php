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
	$c_programs = 0;

	//echo '<div class="events_by_hour" style="display:none;">';
	// First we reorder them by time
	$events_by_hour = [];
	foreach($events as $event){
		$event_id = $event;
		$event = new ipo_event($event);
		$time = $event->get_time(false);
		$strtotime = strtotime($time);

		while(isset($events_by_hour[$strtotime])){
			$strtotime++;
		}

		$events_by_hour[$strtotime] = $event;
		/*
		if(is_sagi() && $event_id == 26284) {
			echo 'time: ['.$time.'] strtotime: ['.$strtotime.'] title: ['.get_the_title ($event->get_id()).']<br>';
		}
		*/
	}

	// Reorder by time
	ksort($events_by_hour);

	//echo '</div>';
	
	
	// 26284

	
	

	$collected_programs = [];
	
	foreach($events_by_hour as $key => $event){

		if(!is_object($event))
			$event = new ipo_event($event);
		
		
		$program = $event->get_program();
$post_status = get_post_status($program);


		if(!$program)
			continue;
		//print_r($program);

		$has_events++;

		
		$program = new ipo_program($program);
		// if no program, continue

		if(!$program->post)
			continue;


	
		//$link = $program->get_link();

		$link = get_permalink($event->get_id());
if ($post_status == 'draft') {
$link = '#';

}
		$image = $program->get_image('thumbnail');
		
		$image = $theme->get_the_bg_image($image,['size'=>'thumbnail']);

		// <span class="day">'. __( sprintf("יום %s'",$event->get_day()),'ipo').'</span>

		$events_popup_html .= '
			<li class="event-single-item '.$post_status.'" data-event-id="'.$event->get_id().'">
				<a class="overlay-link" href="'.$link.'"></a>
				<div class="media"><a href="'.$link.'">'.$image.'</a></div>
				<div class="details">
					<div class="meta">
						<div class="time">
							<span class="time">'.$event->get_time().'</span> | <span class="location">'.$event->get_city().'</span>
						</div>
						<div class="title">
							<a href="'.$link.'">
								'.$program->get_title().'
								<div style="display: flex;" class="link-arrow">
									<img src="'.ipo_arrow_icon_url().'" class="arrow" alt="">
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


		// Check if current program is already in the list

		//if(in_array($program->post->ID,$collected_programs))
		//	continue;

		if($collected_programs){
			foreach($collected_programs as $collected_program){
				if($collected_program['program']->post->ID == $program->post->ID){
					//echo 'Program already in list';
					continue 2;
				}
			}
		}

		// Collect the program
		$event_date = $event->get_date();
		$event_raw_date = strtotime($event_date);

		$collected_programs[] = [
			'program' => $program,
			'date' => $event_date,
		];

		$events_html .= '
			<div class="event ' .$post_status.'" data-event_id="'.$event->post->ID.'" data-program_id="'.$program->post->ID.'" >
				<a class="overlay-link" href="'.$link.'"></a>
				'.$image.'
			</div>
		';
	
	}

	if(count($collected_programs) > 1){
		$e_class .= ' multi-event ';
	}

	if($has_events)
		$e_class .= ' has-events ';

	if($events){

		$day_str = $event->get_day_name();

		if(ICL_LANGUAGE_CODE == 'he'){
			
			
		} else {
			
			switch($day_str){
				case 'ראשון':
					$day_str = 'Sunday';
					break;
				case 'שני':
					$day_str = 'Monday';
					break;
				case 'שלישי':
					$day_str = 'Tuesday';
					break;
				case 'רביעי':
					$day_str = 'Wednesday';
					break;
				case 'חמישי':
					$day_str = 'Thursday';
					break;
				case 'שישי':
					$day_str = 'Friday';
					break;
				case 'שבת':
					$day_str = 'Saturday';
					break;
			}

		}

		$events_popup = '
		<div class="calendar-hint-popup">
			<div class="popup-header">
				<div class="day">'.$day_numeric.'</div>
				<div class="month">'.$ipo_calendar_day->month_name_full.'</div>
				<span class="sep">|</span>
				<div class="weekday">
					'. $day_str .'
				</div>
			</div>
			<ul class="events-list">'.$events_popup_html.'</ul>
		</div>';
	}

}

echo '
	<li class="loop-day '.$e_class.'" data-events="'.$ipo_calendar_day->events.'" data-day="'.$ipo_calendar_day->day.'">
		<div class="contents">
			<div class="events-container">
				'.$events_html.'
			</div>
			<label><span>'.$day_numeric.'</span></label>
		</div>
		'.$events_popup.'
	</li>
';

?>