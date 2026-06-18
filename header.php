<?php
/**
 * Theme Header
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
	<!--<meta name="viewport" content="width=device-width, initial-scale=1.0"/>-->


	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,700;1,400&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
	<link rel='stylesheet' id='classic-theme-styles-css' href='/wp-content/themes\wpstack-child\assets\styles\spacing.css' type='text/css' media='all' />
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
		<!-- Icons & Favicons -->
		<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
		<link href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-icon-touch.png" rel="apple-touch-icon" />	

	<?php } ?>
	<?php wp_head(); ?>
</head>

<?php
	$custom_body_class = !is_front_page() ? 'inner-page concert_body' : 'home-page';
  if( get_field('hero_white') ) {
    $custom_body_class = $custom_body_class . ' white-intro  ';
  }


  if (!is_singular('program') && !get_field('big_img_on_mobile')  ){
        $custom_body_class = $custom_body_class . ' no_menu_overlay_mobile  ';
  }
  
  
  if (is_page(60524)) {
    $custom_body_class = '';
}
  
  $template_name = get_page_template_slug();
   $custom_body_class = $custom_body_class .  $template_name;
  if (is_page_template('history.php') && !get_field('big_img_on_mobile')) {
        $custom_body_class = $custom_body_class . ' no_menu_overlay_mobile  ';
  }
  
  

	$custom_main_class =  !is_front_page() ? 'margin-header_height rel' : '';
?>

<body <?php body_class($custom_body_class); ?>>
<?php do_action('after_body_open_tag'); ?>
<?php //echo do_shortcode('[ccj id="accessibility_script"]'); ?><!-- LLK -->

<div id="page" class="site">

	
	<?php 
		global $theme;
		$theme->the_part('headers/header-1');
	?>
	
		
	<main class="site-content <?php echo $custom_main_class;?>">