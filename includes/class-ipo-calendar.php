<?php

class ipo_calendar_day{
	public $events;
	
	//public $date;
	public $date_raw;
	public $day_in_week;
	public $day_in_week_name;
	public $day;
	public $month;
	public $month_name;
	public $year;
	public $is_current;
	public $is_past_or_future;
	
	public function __construct($day,$month,$year) {
		
		$this->is_current = false;
		$this->events = false;
		
		if($day == 'today') $day = date('d');
		if($month == 'today') $month = date('m');
		if($year == 'today') $year = date('Y');
		
		$this->day = $day;
		$this->month = $month;
		$this->year = $year;
		
		
		$date_raw = strtotime($this->year . '-' . $this->month . '-' . $this->day);
		$this->day_in_week = date('w',$date_raw);
		$this->day_in_week_name = date_i18n('D',$date_raw);
		$this->month_name_full = date_i18n('F',$date_raw);
		$this->month_name = date_i18n('M',$date_raw);
		
		if( date('d')==$day && date('m')==$month && date('Y')==$year  ){
			$this->is_current = true;
			$this->is_past_or_future = 'current';
		} else {
			$today = date('Y-m-d');
			if( strtotime($today) > $date_raw ){
				$this->is_past_or_future = 'past';
			} else {
				$this->is_past_or_future = 'future';
			}
			//echo $day.'-'.$month.'-'.$year;
		}
		
		
		

		
	}
}

class ipo_calendar{
	
	public $days;
	public $months;
	public $years;
	
	public $current_year;
	public $current_month;
	public $current_day;
	public $current_day_of_week;
	
	public function __construct() {
		
		// Setup current date
		$this->current_year = date('Y');
		$this->current_month = date('m');
		$this->current_day = date('d');
		$this->current_day_of_week = date('l');
		
		// Set variables
		$this->days = array();
		$this->months = array();
		$this->years = array();
		
	}
	
	public function get_events($args = array()){


				
		$args = shortcode_atts( 
			array(
				'date'   => false,
				'date_end'   => false,
				'day'   => false,
				'month'   => false,
				'year'   => false,
			), 
			$args
		);

		$meta_query = array();
		
		$end_of_month = false;		
		if($args['date_end'] == 'end_of_month'){
			$end_of_month = true;
		}
		
		switch(true){
			
			// Case - have date but no end date, select one day
			case ($args['date'] && !$args['date_end']):{
				$args['date_end'] = date('Y-m-d', strtotime($args['date'] . ' +1 day'));
				break;
			}
			
			// Case - has a day
			case ($args['day'] || $args['date'] == 'today'):{

				$skip = false;
				if($args['day']=='today'){
					$args['day'] = date('d');
				} else if($args['day']==false){
					$skip = true;
				} 
				if(!$skip){
					$args['date'] = date('Y').'-'.date('m').'-'.$args['day'];
					$args['date_end'] = date('Y-m-d', strtotime($args['date'] . ' +1 day'));
				}
			}
			
			// Case - has a month
			case ($args['month'] ):{
				
				$skip = false;
				
				 if($args['month']=='today'){
					$args['month'] = date('m');
				}  else if($args['month']==false){
					$skip = true;
				} 
				
				if(!$skip){
					if(!$args['day']) 
						$args['day'] = 1;
					
					$args['date'] = date('Y').'-'.$args['month'].'-'.$args['day'];
					$args['date_end'] = date('Y-m-d', strtotime($args['date'] . ' +1 month'));
				}
			}
			
			// Case - has a year
			case ($args['year']):{
				
				
				$skip = false;
				if($args['year']=='today'){
					$args['year'] = date('Y');
				} else if($args['year']==false){
					$skip = true;
				} 
				if(!$skip){
					if(!$args['day']) 
						$args['day'] = 1;
					
					if(!$args['month']) 
						$args['month'] = 1;

					$args['date'] = $args['year'].'-'.$args['month'].'-'.$args['day'];
					$args['date_end'] = date('Y-m-d', strtotime($args['date'] . ' +1 month'));
				}
				
				

			}

		}
		
		if($end_of_month){
			$args['date_end'] = date("Y-m-d", strtotime($args['date']));
		}

		// There is a bug where the date_end selects the final day as the first day of the next month
		// We need to make it so that the date_end is the last day of the month
		if($args['date_end']){
			
			// We must compare the date $args['date'] and $args['date_end'] to see if they are in the same month. 
			// if not, we need to change the date_end to the last day of the month

			$date_start = new DateTime($args['date']);
			$date_start = $date_start->format('Y-m-d');
			
			$date_start_month = new DateTime($date_start);
			$date_start_month = $date_start_month->format('m');



			$date_end = new DateTime($args['date_end']);
			$date_end = $date_end->format('Y-m-d');

			$date_end_new = new DateTime($args['date_end']);
			$date_end_new->modify('-1 day');

			$date_end_month = new DateTime($date_end);
			$date_end_month = $date_end_month->format('m');

			/*
			echo $args['date'] . ' : ' . $date_start_month;
			echo '<br>';
			echo $args['date_end'] . ' : ' . $date_end_month;
			*/

			// Now we check if date_end_month is the same as date_start_month
			// If not, we need to change the date_end to the last day of the month

			if($date_start_month != $date_end_month){
				$args['date_end'] = $date_end_new->format('Y-m-d');
			}

		}
		
		$program_meta = array(
			array(
				'key'     => 'related_to_program',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => 'related_to_program',
				'value'   => '',
				'compare' => '!=',
			),
		);

		if ($args['date'] && $args['date_end']){
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'         => 'event_date',
					'compare'     => 'BETWEEN',
					'value'       => array( $args['date'], $args['date_end'] ),
					'type'        => 'DATETIME'
				),
				$program_meta[0],
				$program_meta[1],
			);
		} else {
			$meta_query = array_merge(
				array( 'relation' => 'AND' ),
				$program_meta
			);
		}
		
		// Limit results: when date range is set use 200, else safety cap of 500
		$posts_limit = ($args['date'] && $args['date_end']) ? 200 : 500;
		
		$query = array(
			'post_type' => 'event',
			'fields' => 'ids',
			'post_status' => 'publish',
			'numberposts' => $posts_limit,
			'order'          => 'ASC',
			'orderby'        => 'meta_value',
			'meta_key'       => 'event_date',
			'meta_type'      => 'DATETIME',
			'meta_query' 	=> $meta_query,
			'suppress_filters' => false,
		);


		
	

		$events = get_posts($query);

		return function_exists( 'ipo_filter_events_with_program' )
			? ipo_filter_events_with_program( $events )
			: $events;
				
	}
	
	public function get_days($month = false, $year = false, $offset = 0){
		
		if(!$month) $month = $this->current_month;
		if(!$year) $year = $this->current_year;
		
		if($month == 'today') $month = date('m');
		if($year == 'today') $year = date('Y');
		
		$month = str_pad($month,2,"0", STR_PAD_LEFT);
		
		$n_days = $this->get_how_many_days($month,$year);
		
		$days = array();
		
		
		$i = 0;
		while($i<$n_days){
			$days[] = new ipo_calendar_day($i+1,$month,$year);
			$i++;
		}
		
		$this->days = $days;
		
		if($offset > 0){
			$pos = 0;
		} else if ($offset < 0){
			$pos = $offset;
			$offset = null;
		} else {
			$pos = 0;
			$offset = null;
		}
		
		$days = array_slice($days,$pos,$offset);
		//echo 'array_slice(days,'.$pos.','.$offset.') of '.$month.'-'.$year.' result='.count($days).' prev='.count($this->days).';<br>';
		
		
		return $days;
		
	}
	
	public function get_events_after($date=false){
		if(!$date)
			$date = date('Y-m-d');
		return $events_this_month = $this->get_events(array('date'=>$date,'date_end'=>'end_of_month'));
		
	}
	
	public function get_days_in_slots_html($template,$month = false, $year = false){
		
		
		
		//$days_html = $this->get_days_html($template,$month,$year);
		$n_days_slots = 6 * 7;
		$try_to_limit_to = 5 * 7;
		
		
		if($month == 'today' || $month == false){
			$month = date('m');
		}
		
		if($year == 'today' || $year == false){
			$year = date('Y');
		}
		
		$start_date = $year.'-'.$month.'-01';
		
		//$days_in_month = count($days_html);
		
		$month_start_date = strtotime($start_date);
		$month_start_day = date('w',$month_start_date);
		$month_end_day = date('t',$month_start_date);
		$day_slots = array();
		
		// STOPED HERE - determine next and prev month
		/*
		$prev_year = $year - 1;
		if($prev_month == 0){
			$prev_month = 12;
		}
		
		$prev_month = $month - 1;
		if($prev_month == 0){
			$prev_month = 12;
		}
		*/
		


		
		$prev_month_slots = $month_start_day; // The amount of days we give the prev month is the number of the day the month starts on
		$this_month_slots = $month_end_day; // The slots are the same as the amount of days
		$next_month_slots = $n_days_slots - $prev_month_slots - $this_month_slots; // This is what's left
		
		if($next_month_slots >= 7){
			$next_month_slots = $next_month_slots - 7;
		}
		
		
		//echo 'start_date: ' . $start_date . '<br>';
		//echo 'month_start_date: ' . $month_start_date . '<br>';
		//echo 'month_start_day: ' . $month_start_day . '<br>';
		//echo 'month_end_day: ' . $month_end_day . '<br><br>';
		
		//echo 'total_slots: ' . $n_days_slots . '<br>';
		//echo 'prev_month_slots: ' . $prev_month_slots . '<br>';
		//echo 'this_month_slots: ' . $this_month_slots . '<br>';
		//echo 'next_month_slots: ' . $next_month_slots . '<br><br>';
		
		$this_month_days = $this->get_days_html($template,$month,$year);

		
		$t_month = $month;
		$t_year = $year;
		$t_month--;
		if($t_month==0){
			$t_month = 12;
			$t_year--;
		}

		if($prev_month_slots > 0){
			$prev_month_days = $this->get_days_html($template,$t_month,$t_year,-$prev_month_slots);
		} else {
			$prev_month_days = array();
		}
		
		// $prev_month_days = $this->get_days_html($template,$t_month,$t_year,-$prev_month_slots);
		
		$t_month = $month;
		$t_year = $year;
		$t_month++;
		if($t_month==13){
			$t_month = 1;
			$t_year++;
		}

		if($next_month_slots > 0){
			$next_month_days = $this->get_days_html($template,$t_month,$t_year,$next_month_slots);
		} else {
			$next_month_days = array();
		}
		
		// $next_month_days = $this->get_days_html($template,$t_month,$t_year,$next_month_slots);
		
		$slots_filled_with_days = array_merge($prev_month_days,$this_month_days,$next_month_days);

		/*
		while($prev_month_slots>0){
			
			// Create HTML for prev and next months
			//$days_prev_month_html = $this->get_days_html($template,$prev_month,$year);
			
			$day_slots[] = 'SLOT '.$slot_counter.' PREV MONTH DAY' . '<br>';
			$prev_month_slots--;
			$slot_counter++;
		}
		
		while($this_month_slots>0){
			$day_slots[] = 'SLOT '.$slot_counter.' THIS MONTH DAY' . '<br>';
			$this_month_slots--;
			$day_counter++;
			$slot_counter++;
		}
		
		while($next_month_slots>0){
			$day_slots[] = 'SLOT '.$slot_counter.' NEXT MONTH DAY' . '<br>';
			$next_month_slots--;
			$slot_counter++;
		}
		*/
		
		
		
		return $slots_filled_with_days;
		
	}
	
	public function get_days_html($template,$month = false, $year = false, $offset = 0){




		global $theme;
		

		$days = $this->get_days($month,$year, $offset);
		
		// Get this time frame events
		
		$events_this_month = $this->get_events(array('month'=>$month,'year'=>$year));
		$events_by_days = array();
		foreach($events_this_month as $event){
			$date = get_field('event_date',$event);
			$day = date('d',strtotime($date));
			if(isset($events_by_days[intval($day)]))
				$events_by_days[intval($day)] .= ','.$event;
			else 
				$events_by_days[intval($day)] = $event;



		}


		
		
		$days_html = array();
		foreach($days as $day){
			if(isset($events_by_days[$day->day])){
				$day->events = $events_by_days[$day->day];
			}
			
			$days_html[] = $theme->get_part($template,array('ipo_calendar_day'=>$day));
		}
		return $days_html;
	}
	
	public function get_how_many_days($month = 'today',$year = 'today'){
		if($month == 'today') $month = date('m');
		if($year == 'today') $year = date('Y');
		return cal_days_in_month(CAL_GREGORIAN,$month,$year);
	}
	
	public function get_rendered_date($time='now'){
		if($time == 'today' || $time == 'now'){
			$time = date('d-m-Y');
		}

		if(!is_int($time))
			$time = strtotime($time);
		
		$year = date('Y',$time);
		$month = date('m',$time);


		// Check WPML current lang constant
		if(ICL_LANGUAGE_CODE == 'en'){
			$month_str = date('F',$time);
		} else {
			$month_str = date_i18n('F',$time);
		}

		//return '<p class="rendered-date date-il8n" data-t=1 data-month="'.$month.'" data-year="'.$year.'">'.$month_str.' '.$year.'</p>';
		
		// Get april month
		//$april = date('d-m-Y',strtotime('April 1, 2023'));
		//$month_str = date_i18n('M',strtotime($april));
		
		//$month_str = date_i18n('M',strtotime($time));

		// Make sure to use full month name
		/*
		switch($month_str){
			case 'Jan':
				$month_str = 'January';
				break;
			case 'Feb':
				$month_str = 'February';
				break;
			case 'Mar':
				$month_str = 'March';
				break;
			case 'Apr':
				$month_str = 'April';
				break;
			case 'May':
				$month_str = 'May';
				break;
			case 'Jun':
				$month_str = 'June';
				break;
			case 'Jul':
				$month_str = 'July';
				break;
			case 'Aug':
				$month_str = 'August';
				break;
			case 'Sep':
				$month_str = 'September';
				break;
			case 'Oct':
				$month_str = 'October';
				break;
			case 'Nov':
				$month_str = 'November';
				break;
			case 'Dec':
				$month_str = 'December';
				break;
		}
		*/
		// Hebrew
		//print_r($month_str);

		switch(trim($month_str)){
			case 'ינו':
				$month_str = 'ינואר';
				break;
			case 'פבר':
				$month_str = 'פברואר';
				break;
			case 'מרץ':
				$month_str = 'מרץ';
				break;
			case 'אפר':
				$month_str = 'אפריל';
				break;
			case 'מאי':
				$month_str = 'מאי';
				break;
			case 'יונ':
				$month_str = 'יוני';
				break;
			case 'יול':
				$month_str = 'יולי';
				break;
			case 'אוג':
				$month_str = 'אוגוסט';
				break;
			case 'ספט':
				$month_str = 'ספטמבר';
				break;
			case 'אוק':
				$month_str = 'אוקטובר';
				break;
			case 'נוב':
				$month_str = 'נובמבר';
				break;
			case 'דצמ':
				$month_str = 'דצמבר';
				break;
		}
		
		return '<p class="rendered-date date-il8n" data-t=1 data-month="'.$month.'" data-year="'.$year.'">'.$month_str.' '.$year.' <svg xmlns="http://www.w3.org/2000/svg" width="25px" height="25px" viewBox="0 0 24 24" fill="none">
<path d="M17 14C17.5523 14 18 13.5523 18 13C18 12.4477 17.5523 12 17 12C16.4477 12 16 12.4477 16 13C16 13.5523 16.4477 14 17 14Z" fill="#1C274C"/>
<path d="M17 18C17.5523 18 18 17.5523 18 17C18 16.4477 17.5523 16 17 16C16.4477 16 16 16.4477 16 17C16 17.5523 16.4477 18 17 18Z" fill="#1C274C"/>
<path d="M13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.5523 12 13 12.4477 13 13Z" fill="#1C274C"/>
<path d="M13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17Z" fill="#1C274C"/>
<path d="M7 14C7.55229 14 8 13.5523 8 13C8 12.4477 7.55229 12 7 12C6.44772 12 6 12.4477 6 13C6 13.5523 6.44772 14 7 14Z" fill="#1C274C"/>
<path d="M7 18C7.55229 18 8 17.5523 8 17C8 16.4477 7.55229 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z" fill="#1C274C"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M7 1.75C7.41421 1.75 7.75 2.08579 7.75 2.5V3.26272C8.412 3.24999 9.14133 3.24999 9.94346 3.25H14.0564C14.8586 3.24999 15.588 3.24999 16.25 3.26272V2.5C16.25 2.08579 16.5858 1.75 17 1.75C17.4142 1.75 17.75 2.08579 17.75 2.5V3.32709C18.0099 3.34691 18.2561 3.37182 18.489 3.40313C19.6614 3.56076 20.6104 3.89288 21.3588 4.64124C22.1071 5.38961 22.4392 6.33855 22.5969 7.51098C22.75 8.65018 22.75 10.1058 22.75 11.9435V14.0564C22.75 15.8941 22.75 17.3498 22.5969 18.489C22.4392 19.6614 22.1071 20.6104 21.3588 21.3588C20.6104 22.1071 19.6614 22.4392 18.489 22.5969C17.3498 22.75 15.8942 22.75 14.0565 22.75H9.94359C8.10585 22.75 6.65018 22.75 5.51098 22.5969C4.33856 22.4392 3.38961 22.1071 2.64124 21.3588C1.89288 20.6104 1.56076 19.6614 1.40314 18.489C1.24997 17.3498 1.24998 15.8942 1.25 14.0564V11.9436C1.24998 10.1058 1.24997 8.65019 1.40314 7.51098C1.56076 6.33855 1.89288 5.38961 2.64124 4.64124C3.38961 3.89288 4.33856 3.56076 5.51098 3.40313C5.7439 3.37182 5.99006 3.34691 6.25 3.32709V2.5C6.25 2.08579 6.58579 1.75 7 1.75ZM5.71085 4.88976C4.70476 5.02502 4.12511 5.27869 3.7019 5.7019C3.27869 6.12511 3.02502 6.70476 2.88976 7.71085C2.86685 7.88123 2.8477 8.06061 2.83168 8.25H21.1683C21.1523 8.06061 21.1331 7.88124 21.1102 7.71085C20.975 6.70476 20.7213 6.12511 20.2981 5.7019C19.8749 5.27869 19.2952 5.02502 18.2892 4.88976C17.2615 4.75159 15.9068 4.75 14 4.75H10C8.09318 4.75 6.73851 4.75159 5.71085 4.88976ZM2.75 12C2.75 11.146 2.75032 10.4027 2.76309 9.75H21.2369C21.2497 10.4027 21.25 11.146 21.25 12V14C21.25 15.9068 21.2484 17.2615 21.1102 18.2892C20.975 19.2952 20.7213 19.8749 20.2981 20.2981C19.8749 20.7213 19.2952 20.975 18.2892 21.1102C17.2615 21.2484 15.9068 21.25 14 21.25H10C8.09318 21.25 6.73851 21.2484 5.71085 21.1102C4.70476 20.975 4.12511 20.7213 3.7019 20.2981C3.27869 19.8749 3.02502 19.2952 2.88976 18.2892C2.75159 17.2615 2.75 15.9068 2.75 14V12Z" fill="#1C274C"/>
</svg></p>
  <div id="monthsPopup" style="display:none;"> </div>

';
	}

	public function get_events_html($args = array(),$template = 'loop-calendar-list-event'){
		$events = $this->get_events($args);

		$checked_events = [];

		// Make sure they are unique by ID, $event->get_id()

		// if parameter debug is set to true, print the event object
		/*
		if(isset($_GET['debug']) && $_GET['debug'] == 'true'){

			$events = array_unique($events);
			print_r($events);

		}
		*/

		$events_html = array();
		global $theme;
		foreach($events as $event){
			$events_html[] = $theme->get_part('loop-calendar-list-event',$event);
		}
		return $events_html;
	}

	public function get_next_program($current_program_id){
		
		// Get all programs 

	}

}