<div class="calendar-slider ipo-calendar-events ajax-calendar-events">
	<?php 
	
	global $theme;
	
	$calendar = new ipo_calendar();
	// Load only current month events for initial view (avoids loading all events)
	$events = $calendar->get_events(array('month' => date('m'), 'year' => date('Y')));

	$ajax_get_events = new ajax_get_events('ajax_get_events','get');
	$content = $ajax_get_events->render_response(array('events'=>$events))['data']['content'];
	if($content)
		echo $content;
	
	$theme->the_part('events_no_results');
		
	
	?>

	<div class="loader">
		<div class="loader-container">
			<div class="circle"></div>
			<div class="circle"></div>
			<div class="circle"></div>
		</div>
	</div>

	
</div>