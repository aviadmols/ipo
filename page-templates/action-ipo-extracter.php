<?php 
/*
* Template Name: ACTION - IPO EXTRACTOR
*/

//get_header(); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function remove_utf8_bom($text){
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

$num_posts = 10;
$offset = 0;
$raw = false;
$is_event= true;

if(isset($_GET['raw'])){
	$raw = true;
}


if(isset($_GET['num_posts']) && $_GET['num_posts']){
	$num_posts = (int) $_GET['num_posts'];
}
if(isset($_GET['offset']) && $_GET['offset']){
	$offset = (int) $_GET['offset'];
}
if(isset($_GET['posts']) && $_GET['posts']){
	$post_ids = (int) $_GET['posts'];
	$post_ids = explode(',',$post_ids);
} else $post_ids = array();

if(isset($_GET['post']) && $_GET['post']){

	$post = $_GET['post'];
	if(is_string($post)){
		// Locate post by title or slug
		$post = get_page_by_title($post,OBJECT,['events','page','orckestra_member']);
		if($post){
			$post_ids = array($post->ID);
			$is_event = false;
		}
	}
	
} 

if(!$raw) echo '<div style="margin: 50px; padding: 50px;">';

	if($is_event):

	$filename = 'export_data.txt';
	// If $_GET['delete_file'] == 'true', delete the file
	if(isset($_GET['delete_file']) && $_GET['delete_file'] == 'true'){
		unlink($filename);
		if(!$raw) echo 'File deleted. <a href="' . get_permalink() . '?num_posts='.$num_posts.'&offset='.$offset.'">Go back</a>';
	} else {
	
		$time_start = microtime(true);

		$args = array(
			'post_type' => 'events',
			'numberposts' => $num_posts,
			'post_status' => 'publish',
			'fields' => 'ids',
			'offset' => $offset,
			'orderby' => 'meta_value',
			'meta_key' => 'events_date',
			'order' => 'DESC',
			'suppress_filters' => false,
			'post__in' => $post_ids
		);

		// GET ONLY HEB RESULTS
		
		global $sitepress;
		$current_lang = $sitepress->get_current_language();  // Say it's "en"
		$sitepress->switch_lang('he');
		$events = get_posts($args);
		$sitepress->switch_lang($current_lang);
		
		
		
		$objects = array();
		foreach($events as $event){
			
	
			$objects[] = export_event($event);
			
			
		}
	
		$encoded_json = json_encode($objects,JSON_UNESCAPED_UNICODE);
		$encoded_base64 = base64_encode($encoded_json);
		
		
		if($raw){
		
			echo $encoded_base64;

		} else {

			if (!file_exists($filename)) {
				file_put_contents($filename, $encoded_base64);
			} else {
		
				$encoded_json_base64_old = file_get_contents($filename);
				//$encoded_json_old = base64_decode($encoded_json_base64_old);
				
				//$encoded_json = json_encode( array_merge( json_decode($encoded_json_old) , json_decode($encoded_json)) );
				
				//$encoded_json_base64 = base64_encode($encoded_json);
		
				//$encoded_json_base64 = $encoded_json_base64_old . ',' . $encoded_base64;
		
				file_put_contents($filename, ','.$encoded_base64, FILE_APPEND | LOCK_EX);
		
			}
				
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			$filesize = filesize($filename);
			$filesize = human_filesize($filesize);
			
			$file_encoded = base64_encode(json_encode($objects,JSON_UNESCAPED_UNICODE));

		}
		
		//echo '<textarea style="width: 100%; min-height: 10vh;" >'.$encoded_base64.'</textarea>';
		if(!$raw) echo '<textarea style="width: 100%; min-height: 30vh;" >'.print_r(json_decode(base64_decode($encoded_base64),false, 512, JSON_UNESCAPED_UNICODE),true).'</textarea>';
			
		$offset = $offset + $num_posts;
		
		
		
		if(!$raw) {

			echo 'DONE in '.$time.' seconds. File size: '.$filesize.'<br>';
			echo '<a href="https://ipo.co.il/export_data.txt" target="_blank" download>Download</a><br>';
			echo '<a href="https://ipo.co.il/ipo-extractor/?delete_file=true&num_posts='.$num_posts.'&offset='.$offset.'">Delete File</a><br>';
			
			echo '<a href="https://ipo.co.il/ipo-extractor/?num_posts='.$num_posts.'&offset='.$offset.'">Next</a><br>';

		}
	}

	endif;
		
	if(!$raw) echo '</div>';
	

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function export_event($event,$handle_translation=true){
	
	$event_post = get_post($event);
	
	if($handle_translation)
		$event_obj['lang'] = 'he';
	else
		$event_obj['lang'] = 'en';
	
	$event_obj['title'] = get_the_title( $event );
	$event_obj['content'] = $event_post->post_content;
	$event_obj['banner_image'] = get_field('event_banner_image',$event)['url'];
	$event_obj['banner_video'] = get_field('event_banner_video',$event);
	$event_obj['type'] = get_field('event_type',$event);
	
	$event_obj['event_date'] = get_field('events_date',$event);
	$event_obj['event_end_date'] = get_field('event_end_date',$event);
	
	$event_obj['event_length'] = get_field('event_length_concert',$event);
	$event_obj['event_price_range'] = get_field('event_price_range',$event);
	
	$artists = get_field('connected_artists',$event);
	$artists_array = [];
	if($artists)
		foreach($artists as $artist){
			
			if(isset($artist['artist']) && isset($artist['artist']->ID)){

				$artist = $artist['artist'];
				// Check if post type is artist or orchesktra_members
				if($artist->post_type == 'artist'){

					$artist->categories = get_the_terms($artist->ID,'Categories');

				} else if($artist->post_type == 'orckestra_member'){

					$artist->categories = get_the_terms($artist->ID,'orckestra_member_category');
					$artist->content = get_field('awards',$artist->ID);

				}

				$artist->image = wp_get_attachment_url( get_post_thumbnail_id( $artist->ID ) );
				
				
				

				$artists_array[] = $artist;

			}
		}
	$event_obj['artists'] = $artists_array;
	
	$event_obj['location']['venue'] = get_field('event_venue',$event);
	$event_obj['location']['event_time'] = get_field('event_time',$event);
	$event_obj['location']['event_hall'] = get_field('event_hall',$event);
	
	$event_obj['program_content'] = get_field('editor1',$event);
	$event_obj['api_id'] = get_field('event_selection_id',$event);
	
	
	// Does have translation?
	
	if($handle_translation){
			
		$id_en = apply_filters( 'wpml_object_id', $event, 'events', false, 'en' );
		//$id_he = apply_filters( 'wpml_object_id', $event, 'events', false, 'he' );
		$post_en = false;
		
		/*
		if($id_en){
			$post_en = get_post($post_en);
		} else {
			$id_en = false;
		}
		*/
		
		$event_obj['wpml_en'] = ['ID'=>$id_en,'data'=>export_event($id_en,false)];
	
	}
		
	
	return $event_obj;

}

?>