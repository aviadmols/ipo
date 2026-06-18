<div class="calendar-slider ipo-calendar-events ajax-calendar-events">
	<?php 
	
	global $theme;
	
	$calendar = new ipo_calendar();
	$events = $calendar->get_events();

	$ajax_get_events = new ajax_get_events('ajax_get_events','get');
	$content = $ajax_get_events->render_response(array('events'=>$events))['data']['content'];
	if($content)
		echo $content;
	else 
		$theme->the_part('events_no_results');
	
	?>
</div>