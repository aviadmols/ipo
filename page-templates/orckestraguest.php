<?php 
/*
* Template Name: Orckestra Guest Page
*/

get_header(); 
$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); ?>

<div class="main_body orckestra_tm">
  <div class="container">
    <h1 class="page_title">חברי התזמורת</h1>
    <?php if(!empty($bannerImage)){ ?>
    <div class="row orckestra_banner">
      <div class=" col-md-12 col-sm-12 col-xs-12 banner_image"> <img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>" /> </div>
    </div>
    <?php } ?>
    <div class="row orckestra_body">
      <div class=" col-md-4 col-sm-3 col-xs-12 sidebar_mobile">
        <div class="panel-group" id="accordion"> 
		<?php
        $terms = get_terms( array(
		'parent' => 0,
		'orderby'    => 'count',
        'taxonomy' => 'Categories',
        'hide_empty' => false,
        ) );
		$j=0;
		 foreach ( $terms as $taxonomy ) {  
		 ?>
        <div class="panel panel-default"> 
              <div class="panel-heading">
                <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordion" href="#mcollapse_<?php echo $j; ?>"><?php echo $taxonomy->name; ?></a> </h4>
              </div>
              <div id="mcollapse_<?php echo $j; ?>" class="panel-collapse collapse">
                <div class="panel-body"> 
                    <?php
					  if ( count( get_term_children( $taxonomy->term_id, 'Categories' ) ) > 0 ) {	    
						$term_children = get_terms(	'Categories', array(  'parent' => $taxonomy->term_id, ) );
						if ( ! is_wp_error( $terms ) ) {
							foreach ( $term_children as $child ) {
								echo '<a href="#texo_'.$child->term_id.'" >' . $child->name . '</a>';
							}
						}       
                      }
					?>
                </div>
              </div>
            </div>
        <?php $j++;
        } 
        ?>  
           
      </div>
      </div> 
      
      <!--Mobile Sidebar End-->
      <div class=" col-md-8 col-sm-9 col-xs-12 orcketstrateam_body">
	  
      <?php
	  $c=0;
	  foreach ( $terms as $taxonomy ) { 
	  		 	if($c == 0)
				{
				?>
	         <div id="div_<?php echo $c; ?>" class="org_mem <?php echo $taxonomy->term_id; ?>" > 
                    <?php
					  if ( count( get_term_children( $taxonomy->term_id, 'Categories' ) ) > 0 ) {	    
						$term_children = get_terms(	'Categories', array(  'parent' => $taxonomy->term_id, ) );
						if ( ! is_wp_error( $terms ) ) {
							foreach ( $term_children as $child ) {
								echo '<h3 id="texo_'.$child->term_id.'" class="orckestra_title">' . $child->name . '</h3>'; 
								$pages = get_posts(array(
								'post_type' => 'artists',
								'numberposts' => 100,
								'tax_query' => array(
									array(
									'taxonomy' => 'Categories',
									'field' => 'id',
									'terms' => $child->term_id, // Where term_id of Term 1 is "1". 
									)
								)
								)); 
								?>
                                <?php
								for($p=0;$p<count($pages);$p++)
								{ 
								$Image = wp_get_attachment_image_src( get_post_thumbnail_id( $pages[$p]->ID ), 'full' );
								?>
                                    <div class="col-md-3 col-sm-3 col-xs-4 orckestra">
                                       
									    
									  
                                       <a href="<?php echo get_permalink($pages[$p]->ID); ?>">
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
										
										<h3><a href="<?php echo get_permalink($pages[$p]->ID); ?>"><?php echo $pages[$p]->post_title; ?></a></h3>
                                    </div>
								<?php } ?> 
                                <?php
							}
						} 
                        ?>
                       
                        <?php       
                      }
					?>
             </div> 
			  <?php } ?> 
	  <?php $c++;} ?>      
      </div> 
      <!--Body End -->
      <!--Desktop Sidebar -->
      <div class=" col-md-4 col-sm-3 col-xs-12 sidebar_desktop">
      <div class="panel-group" id="accordion"> 
		<?php
        $terms = get_terms( array(
		'parent' => 0,
		'orderby'    => 'count',
        'taxonomy' => 'Categories',
        'hide_empty' => false,
        ) );
		$i=0;

		 foreach ( $terms as $taxonomy ) {  
		 ?>
        <div class="panel panel-default"> 
              <div class="panel-heading">
                <h4 class="panel-title"> <a data-toggle="collapse" data-ID="<?php echo $i; ?>" data-cat="<?php echo $taxonomy->term_id; ?>" data-parent="#accordion" href="#collapse_<?php echo $i; ?>"><?php echo $taxonomy->name; ?></a> </h4>
              </div>
              <div id="collapse_<?php echo $i; ?>" class="panel-collapse collapse <?php if($i == '0'){ echo 'in'; } ?>">
                <div class="panel-body"> 
                    <?php
					  if ( count( get_term_children( $taxonomy->term_id, 'Categories' ) ) > 0 ) {	    
						$term_children = get_terms(	'Categories', array(  'parent' => $taxonomy->term_id, ) );
						if ( ! is_wp_error( $terms ) ) {
							foreach ( $term_children as $child ) {
								echo '<a data-texonomy="'.$child->term_id.'" href="#texo_'.$child->term_id.'" >' . $child->name . '</a>';
							}
						}
						
						
                        ?>
                        
                        <?php       
                      }
					?>
                </div>
              </div>
            </div>
        <?php $i++;
        } 
        ?>  
           
      </div>
	  <?php /*if ( is_active_sidebar( 'calendar_box' ) ) : ?>
				
			<?php dynamic_sidebar( 'calendar_box' ); ?>
				
		<?php endif; */?>
		

      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function(e) { 
    jQuery("div#accordion .panel .panel-heading .panel-title a").click(function(e) {
	var main = jQuery(this);
		window.setTimeout(function(){
			if(main.hasClass("collapsed"))
			{}
			else
			{
		jQuery("div#accordion .panel .panel-heading .panel-title a").removeClass( "active_cat" );
    	main.addClass( "active_cat" );		
		var list = new Array();		
		jQuery( "div#accordion .panel .panel-heading .panel-title a.active_cat" ).each(function( index ) { 
			list.push( jQuery( this ).data('cat'));
        });
		//console.log(list);
		if(list != '')
		{
			var params = {"cat_id":list,"action":"orchestra_post"};
			jQuery.post("<?php echo home_url() ?>/wp-admin/admin-ajax.php",params,function(data)
			{
				if(data)
				{   
					jQuery('.row.orckestra_body .orcketstrateam_body').html(data);
				} 
				else
				{
					jQuery('.valmennukset_body .container .main_product').html('');
				} 
		  	});
		}
		else
		{
			jQuery('.valmennukset_product').hide();
		}
    }
		},200)
	});
 });
/*jQuery(document).ready(function(e) {
    jQuery('#accordion .panel-heading .panel-title a').click(function(e) {
       var ID = jQuery(this).data('id'); 
		//jQuery('.orcketstrateam_body .org_mem').hide();
		//jQuery('#div_'+ID).show();
		console.log('#div_'+ID);
		 jQuery('html,body').animate({
           scrollTop: jQuery('#div_'+ID).offset().top},
        'slow');
    });
});*/
</script>
<style type="text/css">
.org_mem { clear:both;}
</style>
<?php get_footer(); ?>