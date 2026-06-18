<?php 
/*
* Template Name: Orchestraguests_template
*/

get_header();

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); ?>
		
<?php 
if( have_rows('category') ): 
while ( have_rows('category') ) : the_row();

global $newid;
$posts = get_sub_field('ar_artist');
if( $posts ): 
    foreach( $posts as $post):
        $ID=get_the_ID();                           
        $newid[]=$ID;
    endforeach; 
    wp_reset_postdata();?>
<?php endif; endwhile;endif; ?> 		
			
			
			
<div class="main_body orckestra_tm">
      <?php if(!empty($bannerImage)){ ?>
    <div class="row orckestra_banner">
      <div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> <img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>" /> </div>
    </div>
    <?php } ?>
  
  <div class="container">
        
    <div class="row orckestra_body">
    
       <div class=" col-md-4 col-sm-3 col-xs-12 sidebar_mobile">
       <div class="panel-group" id="accordion">
		<?php
		if( have_rows('category') ): 
		while ( have_rows('category') ) : the_row();
		$artists = get_sub_field('ar_artist');?>
		
		<?php  $category_name = get_sub_field('ar_name'); ?>	  
			<div class="panel panel-default"> 
				<div class="panel-heading">
					<h4 class="panel-title"> 
					 <?php  $category_name = get_sub_field('ar_name'); ?>
					<a class="mover" data-texonomy="<?php echo $category_name; ?>" data-cat="<?php echo $category_name; ?>" data-parent="#accordion" href="#texo_<?php echo $category_name; ?>">
					<?php echo $category_name;?>
					</a> 
					</h4>
				</div>
			</div>
			<?php  endwhile;endif;?>
		</div>
		<input type="hidden" id="poid" value="">
      </div>
	  <div class=" col-md-8 col-sm-9 col-xs-12 orcketstrateam_body">
      <?php
	  if( have_rows('category') ): 
      while ( have_rows('category') ) : the_row();
	  $artists = get_sub_field('ar_artist');
	  $cats = array();
	  $artist_cats = '';
	  $post_id = array();
	  foreach($artists as $artist) {	
	  	$post_id[] = $artist->ID;	  
		  /*$artist_cats = get_the_terms($artist->ID,'Categories');
		  if($artist_cats) {
		  foreach($artist_cats as $artist_cat) {
			  $cats[] = $artist_cat->term_id;
		  }	
		  }*/
	  }	
	  $cats = array_unique($cats);
 	  $category_name = get_sub_field('ar_name');
	  ?>
	 
	  
      
	         <div id="div_ #texo_<?php echo $category_name;?>" class="org_mem <?php echo $category_name?>" > 
                    <?php				    
					echo '<h3 id="texo_'.$category_name.'" class="orckestra_title">'.$category_name.'</h3>'; 
					$artistslists = get_posts(array(															
					'post_type' => 'artist',
					'numberposts' => 100,
					'post__in'=>$post_id,
					'orderby' => 'post__in',
					'order'=>'ASC',
					'fields' => 'ids',
					)); 

					?>
					
					<?php	
					if($artistslists) {				
						foreach($artistslists as $artistslist){ 
							$id = $artistslist;
	                        $url = get_permalink($id).'?type=donation';
	                    
	                        $theme->the_part('loop-artist',['post_id' => $id, 'url' => $url]);
						 } 

					} ?>   
				
             </div>		 
	    
     
	  <?php   endwhile;endif; ?>	   
      <!--Body End -->
      <!--Desktop Sidebar -->	
 </div> 
	  
	   
	
    </div>
  </div>
</div>

<style type="text/css">
.org_mem { clear:both;}
</style>
<script>
jQuery(document).ready(function($) {
$(function() {
  $('.mover').click(function() {
	if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
	  var target = $(this.hash);
	  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
	  if (target.length) {
		$('html, body').animate({
		  scrollTop: target.offset().top
		}, 1000);
		return false;
	  }
	}
  });
});
});		
</script>  
<?php get_footer(); ?>