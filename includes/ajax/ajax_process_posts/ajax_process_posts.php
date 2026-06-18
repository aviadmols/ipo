<?php 

class ajax_process_posts extends wpstack_ajax{
	
	public function filter($response,$data){

		global $theme;
		$response['console_msg'] = '';
		

		$offset = (isset($data['offset'])) ? $data['offset'] : ''; 
		$amount = (isset($data['amount'])) ? $data['amount'] : ''; 

		$response['console_msg'] = $data;
		$response['messages'] = '';
		$response['logs'] = '';

		$events = get_posts(array(
			'post_type' => 'event',
			'posts_per_page' => $amount,
			'offset' => $offset,
			'orderby' => 'date',
			'order' => 'DESC',
		));

		foreach ($events as $event) {
			$event_obj = new ipo_event($event->ID);
			$program = $event_obj->get_program();
			$program_obj = new ipo_program($program);
			$furtherest_date = $program_obj->get_latest_date();
			update_field('field_633b132f99155',$furtherest_date,$program);
			$response['logs'] .= '<p>processed event <a href="'.get_permalink($event->ID).'">['.$event->ID.']</a> | program <a href="'.get_permalink($program).'">['.$program.']</a> with furthest date: '. $furtherest_date .' </p>';
		}

		

		return $response;		      
	}

	
}
$ajax_process_posts = new ajax_process_posts('ajax_process_posts','post');

