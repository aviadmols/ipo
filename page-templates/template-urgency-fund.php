<?php 
/*
* Template Name: Urgency Fund
*/


get_header();

$banner = get_the_post_thumbnail_url(get_the_ID(),'full'); 
$mobile_banner = get_field('bg_mobile');
global $theme;


$donate_label = get_field('donate_label');
if(!$donate_label){
	if(apply_filters( 'wpml_current_language', null ) =='en'){
		$donate_label = 'DONATE NOW';
	} else {
		$donate_label = 'תרמו עכשיו';
	}
}
$donate_url = get_field('donate_url');
if(!$donate_url){
	if(apply_filters( 'wpml_current_language', null ) =='en'){
		$donate_url = 'https://ipo.pres.global/donations?lang=en';
	} else {
		$donate_url = 'https://ipo.pres.global/donations?lang=he';
	}
}

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );



?>

<div class="main_body">

	<?php if(!empty($bannerImage)){ 
		
		$banner_image_src = $bannerImage[0];
		$banner_text = get_field('banner_text');
		$args = array(
			'banner_image_src' => $banner_image_src,
			'banner_text' => $banner_text,
			'white' => true,
		);
		$theme->the_part('page-banner',$args);
		
		/*
	
	?>
		<div class="row urgency-fund">		
			<div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> 
				<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
			</div>		
		</div>
	<?php */
	
	} ?>

  <div class="container">
 
		<div class="row row-about">
			<div class="col-md-3 col-sm-4 col-xs-12">  
				<?php the_field('left_content'); ?>	
			</div>
			<div class="col-md-9 col-sm-8 col-xs-12">
				<?php the_field('right_content'); ?>
			</div>
		</div>	
		
	</div>	
	
	<div class="bgwrapper">	
		<div class="container">	
	  
			<div class="row row-how-to-support">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<?php the_field('about_text'); ?>
				</div>
			</div>	

			<div class="seprator"></div>

			<div class="row row-special-projects">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<?php the_field('special_projects_text'); ?>
				</div>
				<div class="flex-container col-md-12 col-sm-12 col-xs-12">
				<div class="col-md-3 col-sm-6 col-xs-12">
					<div  class="special-project-banner">
						<img src="<?php echo esc_url(get_field('digital_content_image')['url']); ?>" alt="<?php echo esc_attr(get_field('digital_content_image')['alt']); ?>" />
					</div>
					<div class="special-project-title">
						<h4><?php the_field('digital_content_title'); ?></h4>
					</div>
					<div class="seprator"></div>

					<div class="special-project-content">
						<?php the_field('digital_content_texts'); ?>
						
					</div>
					
					<a class="cta mobile-only" href="<?php echo $donate_url; ?>"><?php echo $donate_label; ?></a>

				</div>
				<div class="col-md-3 col-sm-6 col-xs-12">
					<div  class="special-project-banner">
						<img src="<?php echo esc_url(get_field('educational_projects_image')['url']); ?>" alt="<?php echo esc_attr(get_field('educational_projects_image')['alt']); ?>" />
					</div>
					<div class="special-project-title">
						<h4><?php the_field('educational_projects_title'); ?></h4>
					</div>
					<div class="seprator"></div>

					<div class="special-project-content">
						<?php the_field('educational_projects_texts'); ?>
					</div>
					
					<a class="cta mobile-only" href="<?php echo $donate_url; ?>"><?php echo $donate_label; ?></a>

				</div>
				<div class="col-md-3 col-sm-6 col-xs-12">
					<div  class="special-project-banner">
						<img src="<?php echo esc_url(get_field('musicians_chair_image')['url']); ?>" alt="<?php echo esc_attr(get_field('musicians_chair_image')['alt']); ?>" />
					</div>
					<div class="special-project-title">
						<h4><?php the_field('musicians_chair_title'); ?></h4>
					</div>
					<div class="seprator"></div>

					<div class="special-project-content">
						<?php the_field('musicians_chair_texts'); ?>
					</div>
					
					<a class="cta mobile-only" href="<?php echo $donate_url; ?>"><?php echo $donate_label; ?></a>

				</div>
				<div class="col-md-3 col-sm-6 col-xs-12">
					<div  class="special-project-banner">
						<img src="<?php echo esc_url(get_field('seat_forever_image')['url']); ?>" alt="<?php echo esc_attr(get_field('seat_forever_image')['alt']); ?>" />
					</div>
					<div class="special-project-title">
						<h4><?php the_field('seat_forever_title'); ?></h4>
					</div>
					<div class="seprator"></div>

					<div class="special-project-content">
						<?php the_field('seat_forever_texts'); ?>
					</div>
					
					<a class="cta mobile-only" href="<?php echo $donate_url; ?>"><?php echo $donate_label; ?></a>

				</div>
			</div>	
			
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div class="special-project-bottom desktop-only">
						<?php the_field('special_projects_bottom_texts'); ?>
					</div>
				</div>
			
			</div>	
		
		</div>
	</div>
	

	<?php if(get_field('article_title')): ?>

	<div class="container ipo-urgency-fund-article">

		<div class="row row-title">
			<div class="special-project-title col-md-12 col-sm-12 col-xs-12">
				<h3><?php the_field('article_title'); ?></h3>
			</div>
		</div>	

		<div class="row row-media">
			<div class="col-md-3 col-sm-4 col-xs-12">  
				<h4><?php the_field('article_subtitle'); ?>	</h4>
			</div>
			<div class="col-md-9 col-sm-8 col-xs-12">
				<?php 
				
			
				//preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',  $match);
				$youtube_id = get_youtube_video_ID(get_field('article_video'));

				?>
				<style>.ipo-embed-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; } .ipo-embed-container iframe, .ipo-embed-container object, .ipo-embed-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style>
				<div class='ipo-embed-container'>
					<iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" frameborder='0' allowfullscreen></iframe>
				</div>
				
			</div>
		</div>	
		
		<?php if(get_field('article_content_first')): ?>
		
		<div class="row row-content">
			<div class="col-md-3 col-sm-4 col-xs-12">  
				<h5 class="article_heading"><?php the_field('article_heading'); ?>	</h5>
			</div>
			
			<div class="col-md-9 col-sm-8 col-xs-12">
				<?php the_field('article_content_first'); ?>
				<?php 
					echo do_shortcode( '[toggle_link link_text="Read More" content_tag="div" link_content="' . get_field('article_content') . '"]' );
				
				?>
			</div>
			
		</div>	
		
		<?php endif; ?>
		
	</div>	
	
	<?php endif; ?>
	
</div>
  
<?php get_footer(); 
