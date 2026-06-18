<?php 
/*
* Template Name: Tour_Template
*/

get_header();
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$ID = get_the_ID();
?>
<div class="main_body young_page">
	
<?php if(!empty($bannerImage)){ ?>
		<div class="row orckestra_banner">		
				<div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> 
					<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
				</div>		
		</div> <!--orckestra_banner over -->
<?php } ?>

	<div class="container">
	 <div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12 general_info">
	     <div class="row">
		 
		 <div class="col-md-4 col-sm-4 col-xs-12 general_title mobile_section">
			<h1> <?php the_title(); ?> </h1>
		 </div>
		
		<div class="col-md-8 col-sm-8 col-xs-12 general_desc">
			<div class="tour_detail">
				<?php the_content();?>
			</div>
		</div>
		
		<div class="col-md-4 col-sm-4 col-xs-12 general_title desktop_section">
			<h1><?php the_title(); ?></h1>
		</div>
		
		 </div> <!-- inner row over -->
	    </div> <!-- general_info over -->
	 </div> <!-- outer row over -->
	 
	 <div class="row">
	 <div class="col-md-12 col-sm-12 col-xs-12 tour_info">
	   <div class="row">
	   
		<div class="col-md-8 col-sm-8 col-xs-12 tour_image">
		   <?php $timage = get_field('tour_image'); 
			      if($timage){
		     ?>
			<img src="<?php echo $timage;?>" alt="Tour image">
			<?php } ?>
			
		</div> <!--tour_image over -->
		
		<div class="col-md-4 col-sm-4 col-xs-12 tour_desc">
				<?php $ttitle  = get_field('tour_name'); 
					  $ttime   = get_field('tour_time');
					  $tdetail = get_field('tour_detail'); 
					  $tlink   = get_field('button_link');					  
				?>
				
			<h1><?php echo $ttitle; ?></h1>
			
			<h1><?php echo $ttime; ?></h1>
			
			<div class = "description">
				<?php echo $tdetail; ?>
			</div>
			
			<?php if(ICL_LANGUAGE_CODE=='en'){ ?>
			
		    <a href="<?php echo $tlink;?>" class="tour_more">Read More ></a>
			   
			<?php  }  else { ?>
			
			<a href="<?php echo $tlink; ?>" class="tour_more"> קרא עוד ></a>
				
			<?php } ?>
			
		</div> <!--tour_desc over -->	  
	  </div> 	
	</div> <!--tour_info over --> 
</div>
  
	<div class="row">
	 <div class="col-md-12 col-sm-12 col-xs-12 prev_tour">
	  <?php $ptitle = get_field('previous_tour_title');?>
	    <h2> <?php echo $ptitle;?> </h2>
		<?php   
				$args = array(
					'post_type'=>'tour',
					'posts_per_page' => 100,
					'post_status'=>'publish'
					);
					
			$the_query = new WP_Query($args);
			if($the_query->have_posts()): while($the_query->have_posts()):
			
			$the_query->the_post();
		     ?>
			 
		<div class="col-md-4 col-sm-4 col-xs-12 tour_list">
		      <?php
					$tid    = get_the_ID();
				    $tsdate = get_post_meta($tid,'tour_start_date',true);
					$tedate = get_post_meta($tid,'tour_end_date',true);
					$sedate = date('d/m/y',strtotime($tsdate));
					$eedate = date('d/m/y',strtotime($tedate));
			  ?>
	        
			<div class ="tlist_img">
			   <?php 
			  $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) , 'evnt_thumb' );
			  if (!empty($img)) { ?> 
			  <a href="<?php the_permalink();?>">
			  <img src="<?php echo $img[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> <?php } ?>
			  </a>
			 </div>
			 
		    <p class="tlist_date"> 
				<?php echo $sedate." - ".$eedate;?>
			</p>
			  
			<div class = "tlist_title">
			 
			 <?php  $substr =  get_the_title();?>
				<a href="<?php the_permalink();?>">
					<h1><?php echo $substr;?></h1>
				</a>
				
			</div>
			   
			<div class ="tlist_desc">
			<?php 
			/*	$dcont = wp_trim_words( get_the_content(),8, '' );
				echo $dcont;*/
				
				$dcont = get_the_excerpt();
				echo $dcont;

			?>
			</div> 
			   
		 </div>
		 <?php endwhile; endif; wp_reset_postdata();?>
		 
		</div> <!--prev_tour over -->
		
	</div>
	
  </div> <!--container over -->
  
</div> <!--main_body over -->

<?php  get_footer();?>
