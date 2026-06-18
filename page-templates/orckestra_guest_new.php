<?php 
/*
* Template Name: Orchestra_Guests
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); ?>
		
<?php 
global $newid;
$posts = get_field('select_artist');
if( $posts ): 
    foreach( $posts as $post):
        $ID=get_the_ID();                           
        $newid[]=$ID;
    endforeach; 
    wp_reset_postdata();?>
<?php endif; ?> 		
			
			
			
<div class="main_body orckestra_tm">
  <div class="container">
    <h1 class="page_title"><?php the_title();?></h1>
    <?php if(!empty($bannerImage)){ ?>
    <div class="row orckestra_banner">
      <div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> <img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>" /> </div>
    </div>
    <?php } ?>
    <div class="row orckestra_body">
    

      <?php
	  $artists = get_field('select_artist');
	  $cats = array();
	  $artist_cats = '';
	  $post_id = array();
	  foreach($artists as $artist) {	
	  	$post_id[] = $artist->ID;	  
		  $artist_cats = get_the_terms($artist->ID,'Categories');
		  if($artist_cats) {
		  foreach($artist_cats as $artist_cat) {
			  $cats[] = $artist_cat->term_id;
		  }	
		  }
	  }	
	  $cats = array_unique($cats); 	  
	  ?>
	  <div class=" col-md-4 col-sm-3 col-xs-12 sidebar_mobile">
      <div class="panel-group" id="accordion"> 
		<?php
		if($cats) {		
		 $c=0;	 
		 foreach ( $cats as $cat ) {
			$arti_cat = get_term_by('term_id', $cat, 'Categories');						 		 								
		?>
			
			<div class="panel panel-default"> 
				<div class="panel-heading">
					<h4 class="panel-title"> 
					<a class="mover" data-texonomy="<?php echo $arti_cat->term_id; ?>" data-cat="<?php echo $arti_cat->term_id; ?>" data-parent="#accordion" href="#texo_<?php echo $arti_cat->term_id; ?>">
					<?php echo $arti_cat->name; ?></a> 
					</h4>
				</div>
			</div>
		<?php $c++; } ?>
        <?php } ?>
		</div>
	 
		<?php 
		$post_ids_string = implode( ',', $newid );		
		?>
		<input type="hidden" id="poid" value="<?php echo $post_ids_string; ?>">
      </div> 
	  
      <div class=" col-md-8 col-sm-9 col-xs-12 orcketstrateam_body">
      <?php
	  $m=0; 
	  foreach ( $cats as $cat ) { 
		  $arti_cat = get_term_by('term_id', $cat, 'Categories');  	  		 	
				?>
	         <div id="div_<?php echo $m; ?> #texo_<?php echo $arti_cat->term_id; ?>" class="org_mem <?php echo $arti_cat->term_id; ?>" > 
                    <?php																		
					echo '<h3 id="texo_'.$arti_cat->term_id.'" class="orckestra_title">' . $arti_cat->name . '</h3>'; 
					
					$artistslists = get_posts(array(															
					'post_type' => 'artists',
					'numberposts' => -1,
					'post__in'=>$post_id,
					'orderby' => 'menu_order',
                         'order'     => 'DESC',
					'tax_query' => array(
						array(
						'taxonomy' => 'Categories',
						'field' => 'term_id',
						'terms' => $cat 							
						)
					)
					)); 

					?>
					
					<?php	
					if($artistslists) {				
					foreach($artistslists as $artistslist)
					{ 
					$Image = wp_get_attachment_image_src( get_post_thumbnail_id( $artistslist->ID ), 'artist_thumb' );
					$cat_lister= get_the_terms($artistslist->ID,'Categories');								
					?>
						<div class="col-md-4 col-sm-4 col-xs-4 orckestra cat-<?php foreach ( $cat_lister as $c ) { echo $c->term_id.' ';}?>">
						  
						   <a href="<?php echo get_permalink($artistslist->ID); ?>">
							<?php if ( $Image[0]!='') { 
							?>
							<img src="<?php echo $Image[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"><?php 
							}
							else
							{
							?>
							<img src="<?php echo get_template_directory_uri(); ?>/image/image-placeholder.jpg" alt="Image Placeholder">
							<?php
							}
							?> 
						   </a>
							
							<h3><a href="<?php echo get_permalink($artistslist->ID); ?>"><?php $title = $artistslist->post_title;
								$titles =explode(",",$title); ?>
								<b><?php echo $titles[0]; ?></b></br>
								<?php echo $titles[1]; ?>
							</a></h3>
						</div>
					<?php } } ?>                                               
             </div>		 
	  <?php $m++; }  ?>      
      </div> 
      <!--Body End -->
      <!--Desktop Sidebar -->	  	  	 
	  
	  
	<div class=" col-md-4 col-sm-3 col-xs-12 sidebar_desktop">
		<div class="panel-group" id="accordion"> 
		<?php
		if($cats) {		
		 $c=0;	 
		 foreach ( $cats as $cat ) {
			$arti_cat = get_term_by('term_id', $cat, 'Categories');						 		 								
		?>
			
			<div class="panel panel-default"> 
				<div class="panel-heading">
					<h4 class="panel-title"> 
					<a class="mover" data-texonomy="<?php echo $arti_cat->term_id; ?>" data-cat="<?php echo $arti_cat->term_id; ?>" data-parent="#accordion" href="#texo_<?php echo $arti_cat->term_id; ?>">
					<?php echo $arti_cat->name; ?></a> 
					</h4>
				</div>
			</div>
		<?php $c++; } ?>
        <?php } ?>
		</div>
	 
		<?php 
		$post_ids_string = implode( ',', $newid );		
		?>
		<input type="hidden" id="poid" value="<?php echo $post_ids_string; ?>">
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