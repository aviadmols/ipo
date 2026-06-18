<?php 

class ajax_get_month extends wpstack_ajax{
	
	public function filter($response,$data){

		global $theme;
		// If theme is null, redeclare it
		if(is_null($theme)){
			$theme = new wpstack_theme();
		}

		$response['content'] = '';
		
		$month = (isset($data['month'])) ? $data['month'] : date_i18n('m'); 
		$year = (isset($data['year'])) ? $data['year'] : date_i18n('Y'); 

		$response['data']['content'] = $theme->get_part('calendar-horizontal',array('month'=>$month));
		$response['data']['rendered_date'] = date_i18n('M Y',strtotime('01-'.$month.'-'.$year));
		$response['data']['year'] = $year;
		$response['data']['month'] = $month;
		
		if(!isset($response['message'])){
			$response['message'] = '';
		}

 		$response['message'] .= 'requested month: ' . $month . ' and year: '.$year;
		
		return $response;		      
	}

	
}
$ajax_get_month = new ajax_get_month('ajax_get_month','get');

