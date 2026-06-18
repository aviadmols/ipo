<?php 
/*
* Template Name: Orckestra Team Page - NEW
*/

get_header(); 

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' ); 
global $theme;

$bannerImage = wp_get_attachment_image_url( get_post_thumbnail_id( get_the_ID() ), 'full' );
if (empty($bannerImage) ){
  $bannerImage = ipo_theme_uri( 'includes/images/bg/1.jpg' );
  
}


$class = '';
$white = get_field('white');

?>



<div class="orckestra_tm">

<section class="hero_page-section hide-pc dispaly_for_mobile" style="" >

<div class="gradient-bottom" > </div>
  <div class="gradient-top"> </div>
        <div class="banner_image"> 
            <?php 

$banner_image_mobile  = get_field('banner_image_mobile_main');
           echo '<img src="'.$bannerImage.'" />';

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero-section{
     min-height: 265px;
background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}
.hero-section {
    padding: 0px!important;
    }
    
.hero-section>.container {
    position: absolute;
    bottom: 10px;
}

}
</style>'; 
}

?>
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
        </div>      
<div class="container flex-pc max-1440" style="background: #fff; padding-top: 15px;">
   <div class="content width-25">
<h1 class="title-pages" style="    font-size: 60px!important; color: #000000; margin-right: 0px!important; margin-left: 0px!important; letter-spacing: 3px!important;">
<?php echo get_the_title(); ?></h1>
       
                </div>
</div>
</section>
<section class="hero-section" style="background-image: url(<?php echo $bannerImage; ?>); ">
     <?php
           if($white) {
   echo '<div class="gradient-bottom" >
                                    </div>';
             
               echo '<div class="gradient-top" >
                                    </div>';
                                      
                                      
}?>
            <div class="container">


                <?php
      if($white) {
$class = 'class="white-text"';
}?>
            <?php if($hide_banner_content != true): ?>
                <div class="content">
                    <h1 <?php echo $class; ?>><?php the_title();?></h1>
                    <p <?php echo $class;  ?></p>
                </div>
            <?php endif; ?>

</section>

<?php 

$tax = get_field('filter_by_tax');
if(!$tax)
	$tax = 'artist_cat';


$currently_open_term = '';
$hide_empty = true;

if(isset($_GET['term'])){
	$currently_open_term = $_GET['term'];
	$currently_open_term_id = '';



	// Check if string, if not, get term by ID
	if(!is_numeric($currently_open_term)){
		$currently_open_term = get_term_by('slug', $currently_open_term, $tax);
		$currently_open_term_id = $currently_open_term->term_id;
	} else {
		$currently_open_term_id = intval($currently_open_term);
		$currently_open_term = get_term_by('id', $currently_open_term_id, $tax);
	}

} else {
	// Get the first term
	$currently_open_term = get_terms( array(
		'taxonomy' => $tax,
		'hide_empty' => $hide_empty,
		'parent' => 0
	));
	$currently_open_term = $currently_open_term[0];
	$currently_open_term_id = $currently_open_term->term_id;
}



// Get all parent terms
$terms = get_terms( array(
	'taxonomy' => $tax,
	'hide_empty' => $hide_empty,
  'orderby' => 'menu_order',
	'parent' => 0
));


// Get only selected terms
$body_terms = [];
$body_terms[] = $currently_open_term;

?>

<section class="artists-tax-grid" data-filter-by="<?php echo $tax;?>">
	<div class="container">  
		
		<!-- start sidebar -->
		<div class="sidebar" data-aos="fade-in"  data-aos-duration="1000" data-aos-delay="100">
			<div class="sidebar-widget">
				<ul class="tax-list tax-list-collapsable">
					<?php foreach($terms as $term) : ?>

							<?php 
							
							// Check if term is currently open
							$class = [];
							$panel_body_class = [];
							$children = [];
							$collapse_btn = '';


							if($currently_open_term_id == $term->term_id || $currently_open_term->parent == $term->term_id){
								$class[] = 'ipo-open';
								
							} else {
								$class[] = 'ipo-collapsed';
								$panel_body_class[] = 'collapse';
							}

							// If term as children, get them
							$children = get_terms( array(
								'taxonomy' => $tax,
								'hide_empty' => $hide_empty,
								'parent' => $term->term_id
							));
							if($children){
								$class[] = 'ipo-has-children';
								$collapse_btn = '
									<a class="collapse-controller">
										<i><img src="<?php echo ipo_arrow_icon_url(); ?>" class="arrow" alt=""></i>
									</a>
								';
							} else {
								$class[] = 'ipo-childless';
							}

							$link = '#term-'.$term->term_id.'';

							// new link - redirect
							$current_page_url = get_permalink();
							$link = $current_page_url.'?term='.$term->term_id;

							// Start the list item
							echo '
								<li class="panel  panel-default '.implode(' ',$class).'" data-term-id="'.$term->term_id.'">
									<div class="panel-heading">
										<a class="panel-title" href="'.$link.'">
											'.$term->name.'
										</a>
										'.$collapse_btn.'
									</div>
								';

							?>


							<?php if($children) : ?>
									<ul class="panel-body list-unstyled panel-collapse <?php echo implode(' ',$panel_body_class); ?>">
										<?php foreach($children as $child) : ?>

											<?php 
											
											$child_link = '#term-'.$child->term_id.'';

											// new link - redirect
											//$child_link = $current_page_url.'?term='.$term->term_id.'#term-'.$child->term_id.'';
											
											?>

											<li class="panel-inner panel" data-term-id="<?php echo $child->term_id;?>">
												<a href="<?php echo $child_link;?>"><?php echo $child->name;?></a>
											</li>

										<?php endforeach;?>
									</ul>
							<?php endif;?>

							<?php 
							
							// Close the list item
							echo '</li>';
							
							?>

						</li>
					<?php endforeach;?>
				</ul>
			</div>
		</div>
		<!-- end sidebar -->

		<!-- start content -->
		<div class="content">
			<?php foreach($body_terms as $term): ?>
				
				<?php 
				
					$term_class = [];
					
					// If term has children, get them
					$child_terms = get_terms( array(
						'taxonomy' => $tax,
						'hide_empty' => $hide_empty,
						'parent' => $term->term_id,
 'orderby' => 'menu_order'
					));

					// If term is currently open
					if($currently_open_term_id == $term->term_id || $currently_open_term->parent == $term->term_id){
						$term_class[] = 'ipo-open';
					} else {
						$term_class[] = 'ipo-collapsed';
					}

					if($child_terms){
						$term_class[] = 'ipo-has-children';
					} else {
						$term_class[] = 'ipo-childless';
					}

					// Start the list item
					echo '
						<div class="ipo-term '.implode(' ',$term_class).'" data-term-id="'.$term->term_id.'">
							<div class="anchor" id="term-'.$term->term_id.'"></div>
							<h2 class="ipo-term-title" data-aos="fade-in"  data-aos-duration="1000" data-aos-delay="100">'.$term->name.'</h2>
						';



						// If term has children, get them
						if($child_terms){

							echo '<ul class="ipo-term-children orckestra-row">';
							foreach($child_terms as $child_term){

								$term_child_class = [];

								// Get posts
								$posts = get_posts(array(
									'post_type' => 'artist',
									'posts_per_page' => 100,
									'fields' => 'ids',
	'orderby' => 'menu_order',
                        'order' => 'ASC',
									'tax_query' => array(
										array(
											'taxonomy' => $tax,
											'field' => 'term_id',
											'terms' => $child_term->term_id
										)
									)
								));

								$posts_html = '';

								if($posts){
									$term_child_class[] = 'ipo-has-posts';
									foreach($posts as $post){
										$posts_html .= $theme->get_part('loop-artist-team',$post);
									}
									$posts_html = '<ul class="ipo-term-posts">'.$posts_html.'</ul>';
								}

								// Subterms
								echo '
								<li class="ipo-term-child '.implode(' ',$term_child_class).'" data-term-id="term-'.$child_term->term_id.'">
									<div class="anchor" id="term-'.$child_term->term_id.'"></div>
									<h3 data-aos="fade-in"  data-aos-duration="1000" data-aos-delay="300"><a href="#'.$child_term->slug.'">'.$child_term->name.'</a></h3>
									'.$posts_html.'
								</li>';
							}
							echo '</ul>';
						}

					// Close the list item
					echo '</div>';

				?>

			<?php endforeach; ?>
		</div>
		<!-- end content -->

		
	</div>
</section>

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

</script>

<?php get_footer(); ?>