<?php 
/*
* Template Name: Landing Page
* Author: Sagi.K / sagi@8scope.com
* Date: 11/6/20
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );


$title = get_field('banner_title');
if(!$title)
	$title = get_the_title();

if(!empty($bannerImage)){
	$bannerImage = $bannerImage[0];
	$bannerClass = 'bg-set';
} else {
	$bannerImage = '';
	$bannerClass = 'bg-not-set';
}

?>

<style>
	<?php //echo strip_tags(get_field('css')); ?>
</style>

<div class="main_body">

	<div class="row ipo-lp-banner">		
		<div class=" col-md-12 col-sm-12 col-xs-12 banner_image <?php echo $bannerClass; ?> " style="background-image: url(<?php echo $bannerImage; ?>)"> 
			<div class="container">
				<h1 class="page-title"><?php echo $title; ?></h1>
			</div>		
		</div>		
	</div>

	<div class="container">    
		
		<main class="col-md-8 col-sm-8 col-xs-12">
			<div class="content">
				<?php the_content(); ?>
			</div>
		</main>
		<aside class="col-md-4 col-sm-4 col-xs-12">
			<div class="content">
				<div class="sidebar-content">
					<?php echo do_shortcode(get_field('sidebar_content')); ?>
				</div>
			</div>
		</aside>
		
	</div>
	
</div>

<?php get_footer(); ?>