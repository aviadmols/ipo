<?php 

/*

* Template Name: live_streaming_page - V5

*/


get_header();

global $theme;

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$video_categories = get_field('video_categories');


?>


<div class="main_body">
  <div class="">
  
	<div class="row live-streaming-banner">	
		<?php if(!empty($bannerImage)){ ?>	
			<div class=" col-md-12 col-sm-12 col-xs-12 banner_image">
				<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
			</div>				
		<?php } ?>
	</div>
  
    <?php 
	
		$theme->the_part('live-streaming-concerts-title-bar');

		$theme->the_part('live-streaming-concerts');
	?>

  </div>
</div>

<?php get_footer(); ?>