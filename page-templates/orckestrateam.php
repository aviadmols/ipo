<?php 
/*
* Template Name: Orckestra Team Page
*/

get_header(); 

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); ?>


<div class="orckestra_tm">

  <style>
.panel-title {
    font-size: 2.2rem;
    font-weight: 900;
}

    .panel:first-child {
      display: none;
    }

.orckestra_title {
    flex-basis: 100%;
    text-align: right;
    margin-bottom: 10px;
    border-bottom: none!important;
}

.sidebar_stickey {
    position: sticky;
    padding-bottom: 20px;
    top: 0;
    margin-top: 80px;
    }
    
.panel-default > .panel-heading {
  padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    background-color: #fff;
}
  </style>
            <section class="hero-section" style="background-image: url(<?php echo esc_url( ipo_theme_uri( 'includes/images/bg/1.jpg' ) ); ?>);">
                <div class="container">
                    <div class="hero-text">
                        <h1 class="header-title pb-25"><?php echo get_the_title(); ?></h1>
                      	
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
                      	<?php endif;?>
                      
                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->

	<?php 

	$tax = get_field('filter_by_tax');
	if(!$tax)
		$tax = 'artist_cat';

	$terms = get_terms( array(
	'parent' => 0,
	'taxonomy' => $tax,
	'hide_empty' => false,
	'suppress_filters' => false,
	) );
	$i=0;


	?>
		
	<div class="container">   
		<div class="row orckestra_body pb-100" data-filter-by-tax="<?php echo $tax; ?>">
		
		  <!--Desktop Sidebar -->
		  <div class="  sidebar_desktop sidebar_stickey mb-50" data-top="40">
		
		  <div class="panel-group accordion2" id="accordion2" > 
			<?php


			 foreach ( $terms as $taxonomy ) {  
			 ?>
			<div class="panel ipo-collapsed panel-default" data-filter-term="<?php echo $taxonomy->term_id; ?>"> 
				  <div class="panel-heading">
					<h4 class="panel-title"> 
						<a class="" data-cat="<?php echo $taxonomy->term_id; ?>"  data-ID="<?php echo $i; ?>"  href="#div_<?php echo $i; ?>"><?php echo $taxonomy->name; ?></a>
					</h4>

					<?php 
					
					// If the current term has children, display the expand/collapse button
					if ( count( get_term_children( $taxonomy->term_id, $tax ) ) > 0 ) { ?>
						<a class="collapse-controller">
							<img src="<?php echo ipo_arrow_icon_url(); ?>" class="arrow" alt="">
						</a>
					<?php } ?>
		
				  </div>
				  <div id="mcollapse_<?php echo $i; ?>" class="panel-collapse collapse <?php if($i == '0'){ echo 'in'; } ?>">
					<div class="panel-body"> 
						<?php
						  if ( count( get_term_children( $taxonomy->term_id, $tax ) ) > 0 ) {	    
							$term_children = get_terms(	$tax, array(  'suppress_filters' => false, 'parent' => $taxonomy->term_id, 'hide_empty' => false) );
							if ( ! is_wp_error( $terms ) ) {
								foreach ( $term_children as $child ) {
									echo '
									<div class="side-nav-item">
										<a class="" data-texonomy="'.$child->term_id.'" href="#texo_'.$child->term_id.'" >' . $child->name . '</a>
									</div>';
								}
							}       
						  }
						?>
					</div>
				  </div>
				</div>
			<?php $i++;
			} 
			?>  
			   
		  </div>
		  </div>
		  <!--Desktop Sidebar End-->
		  
		  <!--Body Start-->
		  <div class=" orcketstrateam_body">
		  <?php
		  $c=0;

			

		  foreach ( $terms as $taxonomy ) {   

				$pages = [];
					
					?>
				 <div id="parent_div_<?php echo $c; ?>" class="org_mem <?php echo $taxonomy->term_id; ?>" > 

				 		<div class="anchor" id="div_<?php echo $c; ?>"></div>

						<?php
						  
							// echo '<h3 id="texo_'.$taxonomy->term_id.'" class="orckestra_title">' . $taxonomy->name . '</h3>'; 
							// $pages = get_posts(array(
							// 'post_type' => 'artist',
							// 'posts_per_page' => 100,
							// 'orderby' => 'menu_order',
							// 'order' => 'ASC',
							// 'suppress_filters' => false,
							// 'tax_query' => array(
							// 	array(
							// 	'taxonomy' => 'artist_cat',
							// 	'field' => 'id',
							// 	'terms' => $taxonomy->term_id, // Where term_id of Term 1 is "1". 
							// 	)
							// )
							// )); 
							?>

							<?php

						$artists_row = [];

						if ( count( get_term_children( $taxonomy->term_id, $tax ) ) > 0 ) {	

							$term_children = get_terms(	$tax, array(  'suppress_filters' => false, 'parent' => $taxonomy->term_id, 'hide_empty' => false) );
							if ( ! is_wp_error( $terms ) ) {
								echo '<h2 id="texo_'.$taxonomy->term_id.'" class="orckestra_title tax-parebt">' . $taxonomy->name . '</h2>'; 
								foreach ( $term_children as $child ) {
									echo '<h3 id="texo_'.$child->term_id.'" class="orckestra_title tax-child">' . $child->name . '</h3>';

								$artists_row = get_posts(array(
									'post_type' => 'artist',
									'posts_per_page' => 100,
									'orderby' => 'menu_order',
									'order' => 'ASC',
									'suppress_filters' => false,
									'tax_query' => array(
										array(
										'taxonomy' => $tax,
										'field' => 'id',
										'terms' => $child->term_id, // Where term_id of Term 1 is "1". 
										)
									)
								));  

								$pages = array_merge($pages, $artists_row);

									
								}
							}       
						}else{
						  	echo '<h2 id="texo_'.$taxonomy->term_id.'" class="orckestra_title tax-parent parent-no-children">' . $taxonomy->name . '</h2>'; 
							$artists_row = get_posts(array(
								'post_type' => 'artist',
								'posts_per_page' => 100,
								'orderby' => 'menu_order',
								'order' => 'ASC',
								'suppress_filters' => false,
								'tax_query' => array(
									array(
									'taxonomy' => $tax,
									'field' => 'id',
									'terms' => $taxonomy->term_id, // Where term_id of Term 1 is "1". 
									)
								)
								)); 

								$pages = array_merge($pages, $artists_row);
						  }


						?>

						<div class="orckestra-row">
							<?php
							for($p=0;$p<count($pages);$p++){ 
								$image = get_field('image', $pages[$p]->ID);
								
								$wpstack_img = new wpstack_image($image);
								

							?>
								<div class="orckestra">
										
								<?php if ( !empty($image)) { ?>
									<a href="<?php echo get_permalink($pages[$p]->ID)?>"> 
									<?php	 
									//echo wp_get_attachment_image($image,'full');
									$wpstack_img->the_bg_img(['size' => 'large']);
									?>
									</a>
									<?php 
								}else{

									//$iamge_id = 26624;
									//$wpstack_img = new wpstack_image($iamge_id);
									

								?>
								<a href="<?php echo get_permalink($pages[$p]->ID)?>" class="no-img"> 
								
									
								<?php
									//$wpstack_img->the_bg_img(['size' => 'large']);
								}
								?></a> 
								<?php $name = $pages[$p]->post_title;?>
									<h3><a class="orch_team" href="<?php echo get_permalink($pages[$p]->ID) ?>"><?php echo $name;?></a></h3>
									
									<?php 


									$roles =get_the_terms( $pages[$p]->ID, 'musician_type' );
									//$roles =get_the_terms( $pages[$p]->ID, 'artist_role' );

									if($roles){
										echo '<span class="role">'.$roles[0]->name.'</span>';
									}


										$position_2 = get_field('position_2', $pages[$p]->ID);
										if (!empty($position_2))
											echo '<span class="role position-2">'.$position_2.'</span>';
									?>
								</div>
							<?php } ?> 
						</div>
				 </div>
				 	<?php $c++;?>
				 	<?php }?>

		  </div> 
		  <!--Body End -->
		  
		  
		</div>
	</div>
</div>
<script type="text/javascript">
// Code for team hide and show

// wrap for wp
jQuery(document).ready(function( $ ){
    // whcn clicking on .panel-group > .panel > .panel-heading do something
	$trigger = $('.panel .collapse-controller');
	$trigger.click(function(){

		

		$panel_collapse = $(this).closest('.panel').find('.panel-collapse');
		$panel = $(this).closest('.panel');

		//console.log($panel);

		if($panel.hasClass('ipo-collapsed')){
			$panel.removeClass('ipo-collapsed');
			// Slide down all the link lists
			$panel_collapse.slideDown();
			//$panel_collapse.removeClass('collapse');
		} else {
			$panel.addClass('ipo-collapsed');
			// Slide up all the link lists
			$panel_collapse.slideUp();
			//$panel_collapse.addClass('collapse');
		}
	});

});


/*Code For Stickey Sidebar*/
<?php 
if(ICL_LANGUAGE_CODE == 'he'){	
}
else{
	?>
	jQuery(window).scroll(function(e) {
		var shareheight = jQuery(".sidebar_stickey").height();
		var sOffset = jQuery(".orckestra_body").height();
		var orckestra_body = jQuery(".orckestra_body").offset().top;;
		var sc = jQuery(window).scrollTop(); 
		if(sc >= sOffset || sc <= orckestra_body ){
			jQuery(".sidebar_stickey").css({
				'top': 'auto',
				'position': 'relative'
			});
		}else
		{
			jQuery(".sidebar_stickey").css({
				'top': '40px',
				'position': 'fixed'
			});
		}
	});
	<?php
}
?>

</script>
<style type="text/css">
.org_mem { clear:both;}
</style>


<style>
.org_mem  {
       display: flex;
    flex-flow: wrap;
    width: 100%;
    min-width: 100%;
}

.orcketstrateam_body {
    width: 100%!important;
}

.orckestra img {
    height: 15vw;
	width: 20vw;
    max-width: 100%;
	 
    object-position: center center;
    object-fit: cover!important;
}

.orcketstrateam_body {
    -webkit-box-flex: unset!important;
flex: auto!important;
    max-width: 100%!important;
}
.orckestra {
        margin-bottom: 25px;
width: 24%!important;
display: inline-block;
-webkit-box-flex: unset!important;
flex: auto!important;
    max-width: 100%!important;
}
.orch_team {
    font-family: 'Simpler';
    letter-spacing: 0!important;
    font-size: 2.5rem;
    word-break: unset!important;
    line-height: 1.6!important;
    width: 100%!important;
    font-weight: bold;
    display: block;
    margin-top: 10px;
}

.panel-group {
    display: flex;
}
	
	 .sidebar_stickey{
    position: sticky;
    padding-bottom: 20px;
    top: -33px;
    background: #fff;
    width: 100%;
}
	
.panel-group>div{
    margin-left: 15px;
}
</style>
<?php get_footer(); ?>