<?php 

if(!isset($e_class))
	$e_class = '';

if($ipo_calendar_day->is_current){
	$e_class .= ' current-day active ';
} else {
	$e_class .= ' '.$ipo_calendar_day->is_past_or_future.' ';
}


echo '
	<li class="loop-day loop-calendar-day-horizontal splide__slide '.$e_class.' loop-calendar calendar-day" data-ajax-trigger data-events="'.$ipo_calendar_day->events.'" data-day="'.$ipo_calendar_day->day.'"  data-month="'.$ipo_calendar_day->month.'"  data-year="'.$ipo_calendar_day->year.'">
		<label>'.$ipo_calendar_day->day.'</label>
	</li>
';

?>

