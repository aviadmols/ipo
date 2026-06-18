<?php 

class ajax_get_events extends wpstack_ajax{
	
	public function filter($response,$data){

		global $theme;
		if(is_null($theme))
			$theme = new wpstack_theme();

		$response['content'] = '';
		$events_html = '';

		$events = (isset($data['events'])) ? $data['events'] : array(); 
		if($events && is_string($events)){
			$events = explode(',',$events);
		} else if(is_array($events) && !empty($events)){
			//$events = $data['events'];
		}


		$events = array_unique($events);



		$events_by_program = [];
		// Go through the events and group them by program, but only if the day is the same
		foreach($events as $event){

			if ( ! ipo_event_has_program( $event ) ) {
				continue;
			}

			$event_obj = new ipo_event($event);
			$program_id = $event_obj->get_program();
			$event_date = $event_obj->get_date('Ymd');

			$key = $program_id.'_'.$event_date;

			// If the event is on the same day as the previous event, add it to the array
			if(isset($events_by_program[$key]) && $events_by_program[$key]['date'] == $event_date){
				$events_by_program[$key]['events'][] = $event;
			} else {

				$events_by_program[$key] = array(
					'program_id' => $program_id,
					'date' => $event_date,
					'events' => array($event)
				);
			}

			

		}

		//echo '<!-- events_o: '.print_r($events,true).' -->';
		//echo '<!-- events_n: '.print_r($events_by_program,true).' -->';
		
		/*
		foreach($events as $event){

			$events_html .= $theme->get_part('loop-calendar-horizontal-program', $event);
			
		}
		*/

		foreach($events_by_program as $key => $events){
			$events_html .= $theme->get_part('loop-calendar-horizontal-program', array(
				'program_id' => $events['program_id'],
				'events' => $events['events']
			));
		}

		$response['data'] = $data;
		$response['data']['count'] = count($events);
		$response['data']['content'] = $events_html;
		
		
		if(!isset($response['message']))
			$response['message'] = '';
 		$response['message'] .= 'requested ['.$response['data']['count'].'] events ' . implode(',',$events) ;
		//$response['message'] .= print_r($response,true);
		
		return $response;		      
	}

	
}
$ajax_get_events = new ajax_get_events('ajax_get_events','get');

