<?php 

class ajax_get_calendar_events extends wpstack_ajax{
	
	public function filter($response,$data){

		global $theme;
		$response['content'] = '';
		
		$events = (isset($data['events'])) ? $data['events'] : array(); 

		$month = (isset($data['month'])) ? $data['month'] : date('m');
		$year = (isset($data['year'])) ? $data['year'] : date('Y');

		if(!isset($month)){
	
			if(isset($_GET['month']))
				$month = sanitize_text_field($_GET['month']);
			else 
				$month = date('m');
			
		}
		
		if(!isset($year)){
			
			if(isset($_GET['year']))
				$year = sanitize_text_field($_GET['year']);
			else 
				$year = date('Y');
			
		}
		
		if(!isset($layout)){
			
			if(isset($_GET['layout']))
				$layout = sanitize_text_field($_GET['layout']);
			else 
				$layout = 'normal';
			
		}
		
		$calendar = new ipo_calendar();
		$days = $calendar->get_days_in_slots_html('loop-calendar-full-day',$month,$year);
		
		
		$year = 'today';
		$month = 'today';
		
		if(isset($_GET['month'])){
			$month = intval(sanitize_text_field($_GET['month']));
		}
		
		if(isset($_GET['year'])){
			$year = intval(sanitize_text_field($_GET['year']));
		}
		
		$list_events_html = $calendar->get_events_html(array('month'=>$month,'year'=>$year));
		
		
		$month_start_day = '';
		$month_end_day = '';
		
		//print_r($year);
		if($year == 'today'){
			$year = date('Y');
		}
		if($month == 'today'){
			$month = date('m');
		}
		
		
		$prev_month = $month - 1;
		$next_month = $month + 1;
		
		
		$next_year = $year;
		$prev_year = $year;
		
		if($prev_month == 0){
			$prev_month = 12;
			$prev_year = $year - 1;
		}
		
		if($next_month == 13){
			$next_month = 1;
			$next_year = $year + 1;
		}
		
		
		
		$prev_month_url = '?month='.$prev_month.'&year='.$prev_year;
		$next_month_url = '?month='.$next_month.'&year='.$next_year;
		
		$current_date = date_i18n('M',strtotime('01-'.$month.'-'.$year)) . ' ' . $year;
		
		
		$response['data'] = $data;
		$response['data']['count'] = count($events);
		$response['data']['content'] = $events_html;
		$response['data']['days'] = $days;
		$response['data']['list_events_html'] = $list_events_html;
		$response['data']['current_date'] = $current_date;
		$response['data']['prev_month_url'] = $prev_month_url;
		$response['data']['next_month_url'] = $next_month_url;
		$response['data']['prev_month'] = $prev_month;
		$response['data']['next_month'] = $next_month;
		$response['data']['prev_year'] = $prev_year;
		$response['data']['next_year'] = $next_year;

		
		
		return $response;		      
	}

	
}
$ajax_get_calendar_events = new ajax_get_calendar_events('ajax_get_calendar_events','get');

