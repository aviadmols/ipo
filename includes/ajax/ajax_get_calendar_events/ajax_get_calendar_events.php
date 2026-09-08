<?php 

class ajax_get_calendar_events extends wpstack_ajax{
	
	public function filter($response,$data){

		global $theme;
		$response['content'] = '';
		
		$events = (isset($data['events'])) ? $data['events'] : array(); 

		$month = isset($data['month']) ? intval($data['month']) : 0;
		$year = 0;
		if (isset($data['year'])) {
			$year = intval($data['year']);
		} elseif (isset($data['y'])) {
			$year = intval($data['y']);
		}

		if (!$month && isset($_GET['month'])) {
			$month = intval(sanitize_text_field($_GET['month']));
		}
		if (!$year) {
			if (isset($_GET['y'])) {
				$year = intval(sanitize_text_field($_GET['y']));
			} elseif (isset($_GET['year'])) {
				$year = intval(sanitize_text_field($_GET['year']));
			}
		}

		if (!$month) {
			$month = (int) date('m');
		}
		if (!$year) {
			$year = (int) date('Y');
		}

		$layout = 'normal';
		if (isset($data['calendar_type'])) {
			$layout = sanitize_text_field($data['calendar_type']);
		} elseif (isset($_GET['layout'])) {
			$layout = sanitize_text_field($_GET['layout']);
		}
		
		$calendar = new ipo_calendar();
		$days = $calendar->get_days_in_slots_html('loop-calendar-full-day', $month, $year);

		// Reuse current-month events already fetched for the grid
		$list_events = is_array($calendar->last_month_events)
			? $calendar->last_month_events
			: null;
		$list_events_html = $calendar->get_events_html(
			$list_events !== null
				? array('events' => $list_events)
				: array('month' => $month, 'year' => $year)
		);

		$prev_month = $month - 1;
		$next_month = $month + 1;
		
		$next_year = $year;
		$prev_year = $year;
		
		if ($prev_month == 0) {
			$prev_month = 12;
			$prev_year = $year - 1;
		}
		
		if ($next_month == 13) {
			$next_month = 1;
			$next_year = $year + 1;
		}
		
		$prev_month_url = '?month=' . $prev_month . '&y=' . $prev_year . '&layout=' . rawurlencode($layout);
		$next_month_url = '?month=' . $next_month . '&y=' . $next_year . '&layout=' . rawurlencode($layout);

		$current_timestamp = strtotime(sprintf('01-%02d-%04d', $month, $year));
		$current_date = $calendar->get_rendered_date($current_timestamp);

		$days_html = is_array($days) ? implode('', $days) : (string) $days;
		$list_html = is_array($list_events_html) ? implode('', $list_events_html) : (string) $list_events_html;
		
		$response['data'] = $data;
		$response['data']['count'] = is_array($list_events) ? count($list_events) : count($events);
		$response['data']['days'] = $days_html;
		$response['data']['list_events_html'] = $list_html;
		$response['data']['current_date'] = $current_date;
		$response['data']['prev_month_url'] = $prev_month_url;
		$response['data']['next_month_url'] = $next_month_url;
		$response['data']['prev_month'] = $prev_month;
		$response['data']['next_month'] = $next_month;
		$response['data']['prev_year'] = $prev_year;
		$response['data']['next_year'] = $next_year;
		$response['data']['month'] = $month;
		$response['data']['year'] = $year;

		
		
		return $response;		      
	}

	
}
$ajax_get_calendar_events = new ajax_get_calendar_events('ajax_get_calendar_events','get');
