<?php

class ipo_events_api{
	
	public $url;
	public $events;
	
	function __construct() {
        $this->url = 'https://ipo.pres.global/api/presentations';
    }
	
	public function init($debug=false){

		

		/*
		$ch = curl_init();
		$options = [
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $this->url
		];
		
		curl_setopt_array($ch, $options);
		$json = json_decode(curl_exec($ch));
		
		$this->events = $json->presentations;
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
		}

		curl_close($ch);
		*/

		// Curl request, but with a 5s timeout
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		

		$json = json_decode(curl_exec($ch));
		$this->events = $json->presentations;

		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			print_r($error_msg);
		}
		curl_close($ch);

		// If there was an error or the json is empty, try loading cached version from the DB
		if($error_msg || empty($json)){
			$this->events = get_option('ipo_events');
		} else {
			// If there was no error, save the json to the DB
			update_option('ipo_events',$json);
		}


		if($debug){
			print_r($error_msg);
			print_r($json);
		}

	}
	
	public function get_event_data_url($event_id,$lang = false){
		if($lang == false){
			$lang = ICL_LANGUAGE_CODE;
		}
		return $this->url . '/'. $event_id .'?lang=' . $lang;
	}

	public function get_event($event_id){
		if(is_array($this->events))
			foreach($this->events as $event){
				if(intval($event->id) == $event_id)
					return $event;
			}
		return false;
	}

public function get_price_range($event_id) {
    $url = $this->get_event_data_url($event_id);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return 'Error: ' . curl_error($ch); // Handle error properly
    }
    curl_close($ch);

    $json = json_decode($response);
    if (!isset($json->presentation->priceLevels)) {
        return 'No price levels available';
    }

    $min_price = null;
    $max_price = null;

    foreach ($json->presentation->priceLevels as $level) {
        if ($min_price === null || $level->minPrice < $min_price) {
            $min_price = $level->minPrice;
        }
        if ($max_price === null || $level->maxPrice > $max_price) {
            $max_price = $level->maxPrice;
        }
    }

    if ($min_price !== null && $max_price !== null) {
        return $min_price . '-' . $max_price;
    }

    return '';
}
	
}