<?php 

class ajax_process_posts extends wpstack_ajax{
	
	public function filter($response,$data){

		// SECURITY: this is a bulk write tool (updates ACF fields). The parent
		// wpstack_ajax base class is not present in this child theme (lives in the
		// parent theme) and is assumed NOT to enforce capability/nonce, so gate
		// explicitly here.
		if ( ! current_user_can('manage_options') ) {
			wp_send_json_error('forbidden');
		}
		// TODO: wire a nonce from the caller JS and verify with
		// check_ajax_referer('ajax_process_posts','nonce').

		global $theme;
		$response['console_msg'] = '';


		$offset = (isset($data['offset'])) ? intval($data['offset']) : 0;
		$amount = (isset($data['amount'])) ? intval($data['amount']) : 0;

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

