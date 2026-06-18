<?php 
/*
* Template Name: ACTION - IPO IMPORTER
*/


/*

1. Loop through events
2. Generate data for a program
3. Check if program exist, insert if not, update if does
4. Create the event under the program

- Translations
- Redirections


*/

get_header();

/*
$ipo_importer_he = new ipo_importer();
$ipo_importer_en = new ipo_importer();

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
	

*/

?>
<style>
	.site > *:not(.site-content){
		display:none;
	}
</style>
<div style="margin: 2%; padding: 2%; max-width: calc(100vw - 4% - 4%);">
	
	<form action="/action-ipo-importer" method="post">

	<?php /*
		<textarea style="width: 100%; min-height: 10vh;" name="json_input"><?php echo $ipo_importer_he->input; ?></textarea>
		<!-- A textfield  -->
		<input type="text" name="file_url" placeholder="file url here" value="<?php echo $ipo_importer_he->file_url; ?>" />
*/ ?>
		<input type="text" name="raw_url" placeholder="raw url here" value="<?php //echo $ipo_importer_he->raw_url; ?>" />

		<input id="ajax-import-start" type="submit" value="Start">

	</form>

	<div class="ajax-import-msg-log" style="color: green; width: 100%; margin-bottom: 50px; min-height: 20vh; color: white; background-color: black;">

	</div>
	
	<div class="ajax-import-msg-container" style="width: 100%; min-height: 20vh; color: white; background-color: black;">
	<?php //echo implode('<br>',$ipo_importer_he->msg); ?><br>
	<?php //echo implode('<br>',$ipo_importer_en->msg); ?>
	</div>


	
</div>


<?php //get_footer(); 

if(!function_exists('array_insert_after')){
  function array_insert_after( array $array, $key, array $new ) {
	$keys = array_keys( $array );
	$index = array_search( $key, $keys );
	$pos = false === $index ? count( $array ) : $index + 1;
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
  }
}




get_footer();