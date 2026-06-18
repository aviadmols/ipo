<?php 

$news_ajax_config['js_file_path'] = get_stylesheet_directory_uri().'/assets/scripts/news-ajax.js';
$news_ajax_config['js_name'] = 'news-ajax-js';
$news_ajax_config['action'] = 'news_ajax_action';
$news_ajax_config['object'] = 'news_ajax_object';

add_action( 'wp_enqueue_scripts', function() use ($news_ajax_config) {
   wp_register_script( $news_ajax_config['js_name'], $news_ajax_config['js_file_path'], array('jquery') );
   wp_localize_script( $news_ajax_config['js_name'], $news_ajax_config['object'], array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   wp_enqueue_script( $news_ajax_config['js_name']  );
});

add_action( "wp_ajax_{$news_ajax_config['action']}" , $news_ajax_config['action']);
add_action( "wp_ajax_nopriv_{$news_ajax_config['action']}" , $news_ajax_config['action']);

function news_ajax_action(){

	$response = array();
		
	/* ************************* */
	/* Get data */
	/* ************************* */

	$data = news_get_results(array(
		'filter_post_tag' => null,
		'filter_practice_area' => null,
		'filter_category' => null,
		'filter_category' => null,
		'template' => null,
		'posts_per_page' => null,
		'offset' => null,
	));
	
	/* ************************* */
	/* Build response */
	/* ************************* */

	$response['test'] = print_r($data['test'],true);
	
	$response['type'] = 'success';
	$response['content'] = $data['news_html'];
	$response['count'] = $data['count'];
	$response['test'] = $data['test'];
	$response['query_data'] = print_r($data['query_data'],true);
	
	/* ************************* */
	/* Finalizing */
	/* ************************* */
	
   // Check if action was fired via Ajax call. If yes, JS code will be triggered, else the user is redirected to the post page
   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      //$response = json_encode($response);
      $response = json_encode($response, JSON_HEX_QUOT | JSON_HEX_TAG);
      echo $response;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }
   
   // don't forget to end your scripts with a die() function - very important
   die();
   
}

function news_get_results($atts = array()){
	
	$atts = shortcode_atts(array(
		'filter_post_tag' => null,
		'filter_practice_area' => null,
		'filter_category' => null,
		'posts_per_page' => null,
		'offset' => 0,
		'template' => null,
	),$atts);
	
	/* ************************* */
	/* Get the parameters */
	/* ************************* */
	
	if($atts['posts_per_page'] == null)
		if(isset($_GET['posts_per_page']) && $_GET['posts_per_page'] != '' && $_GET['posts_per_page'] != 'null'){
			$atts['posts_per_page'] = $_GET['posts_per_page'];
			
		} else {
			$atts['posts_per_page'] = get_option( 'posts_per_page' );
		}
	
	if($atts['offset'] == null)
		if(isset($_GET['offset']) && $_GET['offset'] != '' && $_GET['offset'] != 'null'){
			$atts['offset'] = $_GET['offset'];
			
		} else {
			$atts['offset'] = null;
		}
	
	if($atts['template'] == null)
		if(isset($_GET['template']) && $_GET['template'] != '' && $_GET['template'] != 'null'){
			$atts['template'] = $_GET['template'];
			
		} else {
			$atts['template'] = null;
			
		}
	
	
	if($atts['filter_practice_area'] == null)
		if(isset($_GET['filter_practice_area']) && $_GET['filter_practice_area'] != '' && $_GET['filter_practice_area'] != 'null'){
			$filter_practice_area = $_GET['filter_practice_area'];
			$tax_query_filter_practice_area = true;
		} else {
			$filter_practice_area = null;
			$tax_query_filter_practice_area = false;
		}
	
	
	if($atts['filter_category'] == null)
		if(isset($_GET['filter_category']) && $_GET['filter_category'] != '' && $_GET['filter_category'] != 'null'){
			$filter_category = $_GET['filter_category'];
			$tax_query_filter_category = true;
		} else {
			$filter_category = null;
			$tax_query_filter_category = false;
		}
	
	if($atts['filter_post_tag'] == null)
		if(isset($_GET['filter_post_tag']) && $_GET['filter_post_tag'] != '' && $_GET['filter_post_tag'] != 'null'){
			$filter_post_tag = $_GET['filter_post_tag'];
			$tax_query_filter_post_tag = true;
		} else {
			$filter_post_tag = null;
			$tax_query_filter_post_tag = false;
		}
		
	/* ************************* */
	/* Create tax queries */
	/* ************************* */
	
	if($tax_query_filter_post_tag){
		$tax_query_filter_post_tag = array(
			'taxonomy'        => 'post_tag',
			'terms'           =>  $filter_post_tag,
			'operator'        => 'IN',
		);
	} 
	
	if($tax_query_filter_category){
		$tax_query_filter_category = array(
			'taxonomy'        => 'category',
			'terms'           =>  $filter_category,
			'operator'        => 'IN',
		);
	} 
	
	if($tax_query_filter_practice_area){
		$tax_query_filter_practice_area = array(
			'taxonomy'        => 'practice-area',
			'terms'           =>  $filter_practice_area,
			'operator'        => 'IN',
		);
	} 


	/* ************************* */
	/* Setup query data */
	/* ************************* */
	
	$tax_query = array( 
		$tax_query_filter_practice_area,
		$tax_query_filter_category,
		$tax_query_filter_post_tag,
	);
	
	$e_class = '';
	
	// Excluded posts = first 2 featured posts
	$excluded = get_posts(array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'numberposts' => 2,
		'fields' => 'ids',
		'tax_query' => array(
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => 'featured',
		),
	));
	
	if(!$excluded)
		$excluded = array();
	
	$query_data = array(
		'post_type'      	=> 'post',
		'post_status'    	=> 'publish',
		'posts_per_page' 	=> $atts['posts_per_page'],
		'offset' 			=> $atts['offset'],
		'fields' 		 	=> 'ids',
		'tax_query'      	=> $tax_query,
		'exclude'      	=> $excluded,
	);
	$news_items = get_posts($query_data);

	
	/* ************************* */
	/* Build product HTML and insert into the return variable */
	/* ************************* */

	global $theme;
	
	$news_html = array();
	if($news_items)
		foreach ($news_items as $item){
			
			$template = $theme->get_part($atts['template'],$item);
			$news_html[] = $template;

		}
	
	if(is_array($news_html))
		$results_count = count($news_html);
	else 
		$results_count = -1;
	
	if(!$news_html || empty($news_html)){
		$news_html = '<p class="no-results">No results</p>';
		$e_class = 'no-results-container';
	} else {
		$news_html = implode('',$news_html);
	}
	
	$data = array(
		//'news_html' => implode('',$news_html),
		'news_html' => $news_html,
		'query_data' => $query_data,
		'count' => $results_count,
		'test' => 'this is a test',
	);
	
	return $data;
	
}

