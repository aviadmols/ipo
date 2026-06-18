<?php 
/*
* Template Name: Podcast Page
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$history = get_field('IPO_history');
?>


   <link href="<?php echo esc_url( ipo_theme_uri( 'assets/styles/lity.css' ) ); ?>"  rel="stylesheet"/>


<div class="main_body history_tmp ">
	<?php if(!empty($bannerImage)){ ?>
		<div class="row orckestra_banner">		
			<div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> 
				<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
			</div>		
		</div>
	<?php } ?>

	<div class="container">    
		<!--<h1 class="page_title"><?php echo get_the_title(); ?></h1>-->
		<div class=" max-1440 prodcastpage">
	
			<?php
			$podcasts = get_field('podcasts');
			if (!empty($podcasts)) {
				$i = 0;
				echo '<div class="cs-text  mt-100 mb-100"><ul>';
				foreach ($podcasts as $p) {
					$i++;
					echo "<li><div class='display-prod'>";
					echo '<div class="playsection">' . $p['podcast_code'] . '</div>';
                  
                  
					echo '<div class="text"><h3 class="Simpler">' .$p['second_text'].'</h3><p>'.$p['third_text'].'</p></div>
                                            <div class="link"><a href="'.$p['link'].'">להשמעה  <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
                                                 </div>';
								echo "</div></li>";
					if ($i %3 ==0 ) {
						echo '</ul><ul>';
					}
				}
				echo "</div></div>";
			}
			?>
		</div>
	</div>

  <script src="<?php echo esc_url( ipo_theme_uri( 'assets/scripts/lity.js' ) ); ?>"></script>
	<?php get_footer(); ?>