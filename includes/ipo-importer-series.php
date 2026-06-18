<?php 

// Importer class

$import_processor = new wpstack_post_processor('import',[
	'fields' => [
		[
			'name' => 'amount',
			'type' => 'number',
			'value' => '3',
		],[
			'name' => 'offset',
			'type' => 'number',
			'value' => '0',
		],[
			'name' => 'keep_going',
			'type' => 'checkbox',
			'value' => '1',
		],
	]
]);



add_filter('wpstack_post_processor_import','import_processor_action',10,2);
function import_processor_action($response,$data){
	/*
	if(!isset($response['controls'])){
		$response['controls'] = [];
	}
*/

	//$count_results = 0;
	//$count_processed = 0;
	$keep_going = false;
	$messages = '';

	// Declaring objects
	if(isset($data['amount']))
		$amount = $data['amount'];
	else $amount = 1;

	if(isset($data['offset']))
		$offset = $data['offset'];
	else 
		$offset = 0;

	$amount = 3;

	$ipo_importer_he = new ipo_importer('https://ipoold.local/extractor-custom',$amount,$offset);
	$ipo_importer_en = $ipo_importer_he;

	$ipo_importer_he->importer_lang = 'he';
	$ipo_importer_en->importer_lang = 'en';

	// Decoding

	//$ipo_importer_en->add_msg('DEBUG: raw_data ' . print_r($ipo_importer_he->data,true));

	$ipo_importer_he->decode_data();

    //$count_results = count($ipo_importer_he->data);
    if(isset($response['amount']))
        $count_results = $response['amount'] + count($ipo_importer_he->data);
    else
        $count_results = count($ipo_importer_he->data);

	// Create control in the following format: 0: {name: 'amount', value: '1'}

	$response['amount'] = $count_results;

	//$ipo_importer_en->add_msg('DEBUG: data ' . print_r($ipo_importer_he->data,true));

	
	foreach($ipo_importer_he->data as $key => $item){
	
		$serie_id_he = $ipo_importer_he->extract_and_update_serie($item);		

		$eng_obj = $ipo_importer_en->get_serie_translation($item,'en');
		if($eng_obj){

		  global $sitepress;
		  $sitepress->switch_lang('he');
			
		  $serie_id_en = $ipo_importer_en->extract_and_update_serie($eng_obj);
		  
		  try{
			  $ipo_importer_en->connect_serie_to_translation($serie_id_he,$serie_id_en,'serie');
			  $ipo_importer_en->add_msg('Connected serie_id_he='.$serie_id_he.' to serie_id_en='.$serie_id_en);
		  } catch(Exception $ex){
			  $ipo_importer_en->add_msg('ERROR: connect_to_translation ' . $serie_id_he . ' , ' . $serie_id_en . ',' . 'serie');
		  }

	
		}
		
		
	}
	
	// Building messages

	//$messages .= 'Data: '.print_r($json,true);
    //$messages = '<p>'.implode('<br>',$ipo_importer_he->msg).'<br><br>'.implode('<br>',$ipo_importer_en->msg).'</p>';

	$messages .= implode('',$ipo_importer_he->msg);
	//$messages .= implode('',$ipo_importer_en->msg);



	// Finalizing response

	$response['count_results'] = $count_results;
	//$response['count_processed'] = $count_processed;
	$response['keep_going'] = $keep_going;
	$response['messages'] = $messages;
	$response['data'] = $data;
	
	return $response;
	
}
