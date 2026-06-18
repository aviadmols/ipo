<?php 

/*

* Template Name: live_streaming_page

*/

get_header(); 



$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );



$ID = get_the_ID();

?>



<div class="main_body behind_notes live_streamed_concert">

	<div class="row orckestra_banner">	

		<?php if(!empty($bannerImage)){ ?>	



			<div class=" col-md-12 col-sm-12 col-xs-12 banner_image">

				<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 

				

				<?php 

					$btitle  = get_field('banner_title');

					$bstitle = get_field('banner_sub_title');	

				?>

					  

				<div class="live_streamed_contant">					

					<h2> <?php echo $btitle;?>  </h2>

					<p>  <?php echo $bstitle;?> </p>				

				</div>

				

			</div>				

			<?php } else { ?>

			

		<div class=" col-md-12 col-sm-12 col-xs-12 banner_image">

			<!-- <iframe width="1200" height="500" src="<?php echo $bannervideo.'?rel=0&autoplay=1&loop=1'; ?>" frameborder="0" allowfullscreen> -->
			<iframe width="1200" height="500" src="<?php echo $bannervideo; ?>" frameborder="0" allowfullscreen>

			</iframe>

			

		</div>

				<?php } ?>

	</div>

	

	<div class="container live_streamed">

		<div class="col-md-12 col-sm-12 col-xs-12">

		

			<div class="row subscription">	

			

				<div class="col-md-4 col-sm-4 col-xs-12 mobile_section">		<h2> <?php the_title();?> </h2>

				</div>

				

				<div class="col-md-8 col-sm-8 col-xs-12 stream_detail">			<?php the_content();?>				

				</div>

				

				<div class="col-md-4 col-sm-4 col-xs-12 desktop_section">

						<h2> <?php the_title();?> </h2>

				</div>			

			 </div>			

		 </div>		

	 </div>

	 

	<div class="container live_streamed_video">		

		<div class="row">			

		 <div class="col-md-12 col-sm-12 col-xs-12">				

		  <div class="row subscription">		

			<div class="col-md-8 col-sm-8 col-xs-12 lvideo">

			

				<?php 
				$mainvideo = get_field('gallery_video');
				if (!empty($mainvideo)) {  /* ?>
				
					<iframe width="750" height="400" src="<?php echo $mainvideo.'?rel=0&loop=1'; ?>" frameborder="0" allowfullscreen>
					</iframe>	
					
				 <?php */ 
				 
				echo get_video($mainvideo,array(
					'width' => 750,
					'height' => 420,
				)); 

				 } else { 
					$mainimage = get_field('gallery_image');
					echo "<img src='".$mainimage."' />";
				}
				?>

				

				

				

			</div>

			

			<div class="col-md-4 col-sm-4 col-xs-12 ">						<?php 

				$brdtail = get_field('gallery_video_detail');

				echo $brdtail;				

			?>					

			 </div>				

		   </div>				

		</div>			

	</div>	

</div>



	<div class="container">

		<div class="row subscription">

			<div class="col-md-12 col-sm-12 col-xs-12">

			

				<div class="behind_notes_heading">

				<?php $head = get_field('broadcast_section_title');?>

					<h2> <?php echo $head;?> </h2>

					<hr>

				</div>	

				

		 <div class="row subscription">

		  <?php  $args = array(

			     'post_type'=>'video',

				 'posts_per_page' => 100 );

					

				 $the_query = new WP_Query($args);

					

			if($the_query->have_posts()): while($the_query->have_posts()):

			

			$the_query->the_post();		

			$fvid = get_the_ID();

			

			$reallink = get_post_meta($fvid,'youtube_video',true);
			if (empty($reallink)) continue;		     ?>

					<div class="col-md-4 col-sm-6 col-xs-12">

						<div class="subscription_div">

							<div class="series_img">

								<?php
								//preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $reallink, $matches);	
								/* <iframe width="360" height="260" src="https://www.youtube.com/embed/<?php echo $matches[1].'?rel=0&loop=1'; ?>" frameborder="0" allowfullscreen></iframe> */
								?>											
									
								<?php echo get_video($reallink,array(
									'width' => 350,
									'height' => 200,
								)); ?>

							

							</div>

							<?php

							$sedate = $eedate = '';

							$tid    = get_the_ID();

							$tsdate = get_post_meta($tid,'start_date',true);

							$tedate = get_post_meta($tid,'end_date',true);

							if (!empty($tsdate)) {

								$sedate = date('d/m/y',strtotime($tsdate));

							}

							if (!empty($tedate)) {

								$eedate = date('d/m/y',strtotime($tedate));

							}

							$substr =  get_the_title();

							$vlink  = get_the_permalink();						

							?>

							<p class="vlist_date"> 

								<?php echo $sedate; ?>

								<?php if (!empty($eedate))

									echo " - ".$eedate;?>

							</p>

			

							<h5 class="post_title"> 
								<?php echo $substr;?>
							</h5>

							

							<?php 

							$dcont = wp_trim_words(get_the_content(),8,'');

							?>

							<div class="sub_title">

								<?php echo $dcont;?>

							</div>								

						</div>

					</div>	

			<?php endwhile; endif; wp_reset_postdata();?>

                </div>		

	    </div>		

	</div>		

				

		<div class="col-md-12 col-sm-12 col-xs-12 last_section">

				<?php if(ICL_LANGUAGE_CODE=='en'){?>

				

				<div class="col-md-4 col-sm-4 col-xs-12">

					

				</div>

				

				<div class="col-md-8 col-sm-8 col-xs-12">

					<div class ="description_text">

						<?php echo get_field('last_section');?>

					</div>

			    </div>

				

				<?php } else { ?>

				

				<div class="col-md-8 col-sm-8 col-xs-12">

					<div class ="description_text">

						<?php echo get_field('last_section');?>

					</div>

				</div>

				

				<div class="col-md-4 col-sm-4 col-xs-12">

				

				</div>

				<?php } ?>

		</div><!-- last_section over -->

	</div>

</div>

<?php get_footer(); ?>

