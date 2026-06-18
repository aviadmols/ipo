<?php 

if(!isset($e_class))
	$e_class = '';

if($ipo_calendar_day->is_current){
	$e_class .= ' current-day active ';
} else {
	$e_class .= ' '.$ipo_calendar_day->is_past_or_future.' ';
}


$day_name = date('D', strtotime($ipo_calendar_day->year.'-'.$ipo_calendar_day->month.'-'.$ipo_calendar_day->day));

// If hebrew
if(ICL_LANGUAGE_CODE == 'he'){
	
	$day_name = $day_name == 'Sun' ? "יום א'" : $day_name;
	$day_name = $day_name == 'Mon' ? "יום ב'" : $day_name;
	$day_name = $day_name == 'Tue' ? "יום ג'" : $day_name;
	$day_name = $day_name == 'Wed' ? "יום ד'" : $day_name;
	$day_name = $day_name == 'Thu' ? "יום ה'" : $day_name;
	$day_name = $day_name == 'Fri' ? "יום ו'" : $day_name;
	$day_name = $day_name == 'Sat' ? 'שבת' : $day_name;

} 

echo '
	<li class="loop-day loop-calendar-day-horizontal splide__slide '.$e_class.' loop-calendar calendar-day" data-ajax-trigger data-events="'.$ipo_calendar_day->events.'" data-day="'.$ipo_calendar_day->day.'"  data-month="'.$ipo_calendar_day->month.'"  data-year="'.$ipo_calendar_day->year.'">
		<label>
			<span>'.$ipo_calendar_day->day.'</span>
			<p>'.$day_name.'</p>
		</label>
	</li>
';

?>

