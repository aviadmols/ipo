<?php 

class ajax_import_batch extends wpstack_ajax{
	
	public function filter($response,$data){

		global $theme;
		$response['console_msg'] = '';
		

		$url = (isset($data['url'])) ? $data['url'] : ''; 
		$response['console_msg'] = $data;
		$response['logs'] = '';


		$ipo_importer_he = new ipo_importer(['url'=>$url]);
		$ipo_importer_en = $ipo_importer_he;

		$response['logs'] .= 'PHP Decoding';

		//$response['console_msg'] = print_r($ipo_importer_he,true);

		$ipo_importer_he->decode_data();
		foreach($ipo_importer_he->data as $key => $item){
	
			$ipo_importer_he->extract_program($item);
			$ipo_importer_he->extract_event($item);
			
			$event_id_he = $ipo_importer_he->event_id;
			$program_id_he = $ipo_importer_he->program_id;
			
			$ipo_importer_he->update_program($item);
			$artists_he = $ipo_importer_he->update_artists($item);
			$ipo_importer_he->update_concert_type($item);
			$ipo_importer_he->update_event($item);
			
			// ENGLISH VERSION
			$eng_obj = $ipo_importer_he->get_translation($item,'en');
			if($eng_obj){
				
			  $ipo_importer_en->extract_program($eng_obj);
			  $ipo_importer_en->extract_event($eng_obj);
				
			  $event_id_en = $ipo_importer_en->event_id;
			  $program_id_en = $ipo_importer_en->program_id;
			  
			  try{
				  $ipo_importer_en->connect_to_translation($event_id_he,$event_id_en,'event');
			  } catch(Exception $ex){
				  $ipo_importer_he->add_msg('ERROR: connect_to_translation ' . $event_id_he . ' , ' . $event_id_en . ',' . 'event');
			  }
			  
			  try{
				  $ipo_importer_en->connect_to_translation($program_id_he,$program_id_en,'program');
			  } catch(Exception $ex){
				  $ipo_importer_he->add_msg('ERROR: connect_to_translation ' . $program_id_he . ' , ' . $program_id_en . ',' . 'program');
			  }
			  
			  $ipo_importer_en->update_program($eng_obj);
			  
			  $artists_en = $ipo_importer_en->update_artists($eng_obj);
			  foreach($artists_en as $key => $artist_en){
				  if(is_object($artist_en))
					  $artist_en = $artist_en->ID;
				  
				  try{
					  $ipo_importer_en->connect_to_translation($artist_en,$artists_he[$key],'artist');
				  } catch(Exception $ex){
					  $ipo_importer_he->add_msg('ERROR: connect_to_translation ' . $artist_en . ' , ' . $artists_he[$key] . ',' . 'artist');
				  }
			  
				  
				  
			  }
			  
			  $ipo_importer_en->update_concert_type($eng_obj);
			  $ipo_importer_en->update_event($eng_obj);
		
			}
			
		}

		$response['messages'] = '<p>'.implode('<br>',$ipo_importer_he->msg).implode('<br>',$ipo_importer_en->msg).'</p>';

		return $response;		      
	}

	
}
$ajax_import_batch = new ajax_import_batch('ajax_import_batch','post');

