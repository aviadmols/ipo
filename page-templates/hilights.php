<?php 
/*
* Template Name: hilights
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
// $bvideo = get_field('video_field');
$ID = get_the_ID();



$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );
$default_banner = get_field('pages_placeholder_image', 'option');

?>

<div class="main_body behind_notes">
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
	<div class="container">
		<div class="subscription">
			<div class="col-md-12 col-sm-12 col-xs-12 behind_notes_video">	
				<div class="row subscription video_description content_part ">	
					<div class="col-md-4 col-sm-4 col-xs-12 mobile_section">
						<h2> <?php the_title();?> </h2>
						<?php $stitle = get_field('behind_sub_title');?>
						<p>  <?php echo $stitle; ?> </p>
					</div>
					
					<div class="col-md-8 col-sm-8 col-xs-12 behind_detail">
						<?php the_content();?>
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
					$members = get_field('select_orchestra_members');
					
					if (!empty($members)) :
						foreach ($members as $row):
							$image = $row['image'];
							$image_obj = wp_get_attachment_image_src($image , 'medium');
							$image_obj_full = wp_get_attachment_image_src($image , 'full');
							$link = $row['link'];
							$title = $row['title'];
							$desc = $row['description'];
							?>
					
							<div class="col-md-4 col-sm-6 col-xs-12 pull-right">
								<div class="subscription_div">
									<div class="series_img">
										
										
										<?php 
											//if link
											if($link) {
												echo '<a href="'.$link.'" class="wcag-underline" >';
											} else {
												echo '<a href="'.$image_obj_full[0].'" data-fancybox="gallery" data-caption="'.$title.'">';
											}
											/* <img src="<?php echo $image_obj[0];?>" alt="<?php echo get_post_meta($image, '_wp_attachment_image_alt',true);?>"/> */
											// echo '<img src="'.$image_obj[0].'" alt="'.get_post_meta($image, '_wp_attachment_image_alt',true).'" data-fancybox="gallery" data-caption="'.$title.'"/>';
											
											// Echo the image with the caption. medium image size is used for the thumbnail and full image size is used for the full image.
											
											echo '<img src="'.$image_obj[0].'" alt="'.get_post_meta($image, '_wp_attachment_image_alt',true).'" data-caption="'.$title.'"/>';
											echo '</a>';
											
										?>
									   	</a> 
									</div>
								
									<h5 class="post_title member_name">				
										<a href="<?php echo $link;?>" class="wcag-underline" >
											<?php echo $title;?>
										</a>
									</h5>
								
									<div class = "sub_title"><?php echo $desc;?></div>
								</div>
							</div>
						<?php endforeach;
						wp_reset_postdata();
					endif; ?>
                </div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
