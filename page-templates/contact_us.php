<?php 
/*Template name:Contact Us Page*/
get_header();

$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );

$default_banner = get_field('pages_placeholder_image', 'option');

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

<div class="main_body ">
	<div class="container">
	
		<div class="row" style="
    align-items: center;
    justify-content: center;
">
			<div class=" col-md-8 col-sm-8 col-xs-12 contact_form"> 
				<h1 class="page_title"><?php the_title();?></h1>  
                 <div class="contact_content"> <?php the_content();?> </div>
				 <?php 
				 $form = get_field('form_shortcode');
				 if(!empty($form)){  
				 echo do_shortcode($form);} 
				 ?>
			</div>
		
		</div>
	</div>
</div>
<?php 
get_footer();
?>