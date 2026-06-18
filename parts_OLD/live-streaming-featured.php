<div class="container live_streamed_video">		
	<div class="row">			
	 <div class="col-md-12 col-sm-12 col-xs-12">				

	  <div class="row subscription">		
		<div class="col-md-8 col-sm-8 col-xs-12 lvideo">

			<?php 
			$mainvideo = get_field('featured_embed');
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

		

		<div class="col-md-4 col-sm-4 col-xs-12 n-1">						<?php 

			$brdtail = get_field('featured_content');

			echo $brdtail;				

		?>					

		 </div>				

	   </div>	
		
		<?php if(get_field('featured_bottom_content')): ?>
			<div class="row extra-live-streaming-text text-under-video">
				
				<div class="col-md-12 col-sm-12 col-xs-12">			

					<?php the_field('featured_bottom_content'); ?>

				</div>	
				
			</div>
		<?php endif; ?>

	</div>			

</div>	

</div>
	