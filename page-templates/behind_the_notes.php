<?php 
/*
* Template Name: behind_the_notes
*/

get_header(); 
$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );
$bvideo = get_field('video_field');
$ID = get_the_ID();

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

<div class="main_body behind_notes">

	<div class="container">
		<div class="subscription">
			<div class="col-md-12 col-sm-12 col-xs-12 behind_notes_video">
			<?php if($bimage != ''){ ?>
				<div class="row subscription">
				
				<img src="<?php echo $bimage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>" class="img-responsive"/> 	
			
				</div>	
				
				<?php } else { ?> 
				
						
				<?php } ?>
				
				<div class="row subscription video_description">	
					<?php /*
					<div class="col-md-4 col-sm-4 col-xs-12 mobile_section">
						<h2> <?php the_title();?> </h2>
						<?php $stitle = get_field('behind_sub_title');?>
						<p>  <?php echo $stitle; ?> </p>
					</div> */ ?>
					
	<div class="col-md-4 col-sm-4 col-xs-12 ">
						<h2> <?php the_title();?> </h2>
						<?php $stitle = get_field('behind_sub_title');?>
						<p> <?php echo $stitle; ?> </p>
	<?php the_content();?>
					</div>

					<div class="col-md-8 col-sm-8 col-xs-12 behind_detail">

	<div class="row  desktop_video">		
					<iframe width="600" height="400" src="<?php echo $bvideo.'?rel=0&loop=1'; ?>" frameborder="0" allowfullscreen>
					</iframe>
				</div>	

					
					</div>
					
				

				</div>	
			</div>	
		</div>	
  </div>
	
	<div class="container">
		<div class="row subscription">
			<div class="col-md-12 col-sm-12 col-xs-12">
				
				<div class="behind_notes_heading">
					<?php $sstitle = get_field('second_section_title');?>
					
					<h2> <?php echo $sstitle; ?> </h2>
					<hr>
				</div>
				
				
				<div class="row subscription">
				<?php 
				$posts = get_field('select_members');
				
				if( $posts ):




				foreach( 	$posts as $post):setup_postdata($post);
				$vid_url = get_field('youtube_link');
				$image = get_field('image');
				?>
				
					<div class="col-md-4 col-sm-6 col-xs-12 pull-right">
						<div class="subscription_div">
						
							<div class="series_img">
								<a href="<?php the_permalink();?>" class="wcag-underline">
                                               <div class="overlay-background">

                                    </div>
									<?php 
									if (!empty($vid_url)) {
										preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $vid_url, $matches);	

										?>								
                                  
                                  <div class="overlay">

                           
                                            <a href="https://www.youtube.com/embed/<?php echo $matches[1].'?rel=0&loop=1'; ?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        
                                    </div>
									<?php } if (!empty($image)) {  

						
                                       $attachment_image = wp_get_attachment_url( $image );
                                      echo '<img src="' . $attachment_image .'">';
							

									} else {
											$image = new wpstack_image($image,['size'=>'medium']);
        echo '<img src="/">';
												
									}?>
								   </a> 
							</div>
							
							<h5 class="post_title member_name">
							<?php $substr =  get_the_title();
								  $pieces = explode(",", $substr);
								
							?>							
							<a href="<?php the_permalink();?>" class="wcag-underline" ><?php echo $pieces[0];?> </a>
							</h5>
							
							
							<div class = "sub_title"><?php echo $pieces[1];?></div>
							
						</div>
					</div>
					<?php endforeach;wp_reset_postdata();endif; ?>
                </div>
				
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>