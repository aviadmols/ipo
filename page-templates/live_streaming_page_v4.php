<?php 

/*

* Template Name: live_streaming_page - V4

*/


get_header();

global $theme;


$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );

$default_banner = get_field('pages_placeholder_image', 'option');
$video_categories = get_field('video_categories');


?>

<section class="hero_page-section" >
        <div class="banner_image"> 
            <?php 

                if($bannerImage) {
                    echo $bannerImage;
                }elseif($default_banner) {
                    $default_banner = new wpstack_image($default_banner);
                    echo $default_banner->get_img();
                }

            ?>
        </div>      
    <div class="container">
    
    </div>
    <div class="gradient-top"></div>
</section>

<div class="main_body">
  <div class="">
  
  
    <?php 
	
		$theme->the_part('live-streaming-title-bar');
		$theme->the_part('live-streaming-featured');
	?>

	<div class="row video-categories-menu">
		<div class="container">
			<?php $i = 0; foreach($video_categories as $category){
				
				$cat_name = $category['title'];
				//$anchor = str_replace(' ','-',esc_html($cat_name));
				$anchor = "video-category-anchor-{$i}";
				echo "<a href='#{$anchor}'>{$cat_name}</a>";
				$i++;
				
			} ?>
		</div>    
	</div>  
	
	<section class="video-categories">
	
		<?php 
		
			$theme->the_part('live-streaming-categories');
		
		?>
		
	</div>    

  </div>
</div>

<?php get_footer(); ?>
