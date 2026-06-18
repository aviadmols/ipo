<?php 
/*
* Template Name: Musicians old
*/
get_header();

global $theme;

$page_id = get_the_ID();

$categories = array();
$musicians = array();
$musician_instruments = array();

$instruments = get_terms([
    'taxonomy' => 'instrument',
    'hide_empty' => true,
    'parent' => 0
]);

$terms = get_terms([
    'taxonomy' => 'musician_type',
    'hide_empty' => false,
]);

foreach($terms as $category) {
    $id = $category->term_id;
    $categories[$id] = [
        'id'            => $id,
        'key'           => 'category-' . $id,
        'title'         => $category->name,
    ];  
}

$cat_text_def_str = 'הקדש כיסא נגן עם תרומה שנתית למשך חמש שנים';
if(ICL_LANGUAGE_CODE == 'en'){
    $cat_text_def_str = 'Donate a seat with a five-year annual contribution';
}


$bannerImage = ipo_get_banner_image();

?>


<section class="hero_page-section" >
		<div class="banner_image"> 
			<img src="<?php echo $bannerImage[0]; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
		</div>		
	<div class="container">
	
	</div>
	<div class="gradient-top"></div>
</section>

<div class="first-section">
	<div class="container">
		<div class="title-tabs clearfix_2">
			<div class="title_h3">

                <?php if(get_field('category_text', $page_id)):?>
                    <h3><?php the_field('category_text', $page_id);?></h3>
                <?php else:?>
                    <h3><?php $cat_text_def_str; ?></h3>
                <?php endif;?>

			</div>
			<div class="right-tabs">
				<div class="box-tabs_margin">
					<ul id="filters" class="box-tabs">
						<?php 

                            $new_cat_array = array();

                            $filter_data = '.artist_box.loop-artist';
                        
                            foreach($categories as $category) {
                             $term = get_term_by( 'id', $category['id'], 'musician_type' );

                             if($term->description) : 

                             $new_cat_array[] = $category;

                             if($category['title'] != 'נגן מן השורה' && $category['title'] != 'All Other Musicians') {
                                $filter_data .= ':not(.category-' . $category['id'] . ')';
                             }
                        ?>
						<?php endif; } 

                            foreach($new_cat_array as $category) {
                                $term = get_term_by( 'id', $category['id'], 'musician_type' );

                                if($category['title'] == 'נגן מן השורה' || $category['title'] == 'All Other Musicians') {
                                    $data_filter = $filter_data;
                                }else{
                                    $data_filter = '.category-' . $category['id'];
                                }
                                ?>
                                <li>
                                    <button class="box-tabs_button" data-filter="<?php echo $data_filter; ?>">
                                        <span><?php echo $category['title'];?></span>
                                    </button>
                                    <p>
                                        <?php 
                                           
                                            echo $term->description;
                                        ?>
                                    </p>
                                </li>
                                <?php
                            }
                        ?>
					</ul>
				</div>
			</div>
		</div>
		<div class="title_h2">
            <?php if(get_field('list_text', $page_id)):?>
                <h2><?php the_field('list_text', $page_id);?></h2>
            <?php endif;?>
		</div>
		 <?php 
		    // Loop through all the instruments taxonomy
		    foreach($instruments as $instrument) {
                $parent_id = $instrument->term_id;
                $parent_instrument_title = $instrument->name;
                
                echo '<div class="loop-instrument">';
                echo '<div class="instrument-title title_h2">';
                echo '<h2>'. $parent_instrument_title .'</h2>';
                echo '</div>';
                
                $termchildren = get_terms('instrument', array('child_of' => $parent_id ));

                
                
                // display parent posts when there's no child
                if( empty($termchildren) ){
                    $parent_term = get_term_by( 'id', $parent_id, 'instrument' );

                   $args = array(
                        'posts_per_page'   => 5,
                        'suppress_filters' => true,
                        'post_type' => 'artist',
                        'fields' => 'ids',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'instrument',
                                'field'    => 'slug',
                                'terms'    => $parent_term->slug
                            ),
                        )
                    );
                    
                    
                    
                    echo '<div class="overflow-hidden">';
                    echo '<div class="list-musicians_margin">';
                    echo '<div class="list-musicians grid">';
                    
                    //get parent posts
                    foreach(get_posts($args) as $post) {
                    	
                        $id = $post->ID;
                        $url = get_permalink($id).'?type=donation';
                    
                        $theme->the_part('loop-artist',['post_id' => $id, 'url' => $url]);

                    }
                    
                    echo '</div></div></div>';
                }
                
                // Loop through child instruments taxonomy
                foreach ( $termchildren as $child ) {
                    $child_term = get_term_by( 'id', $child->term_id, 'instrument' );

                   $args = array(
                        'posts_per_page'   => -1,
                        'suppress_filters' => true,
                        'post_type' => 'artist',
                        'fields' => 'ids',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'instrument',
                                'field'    => 'slug',
                                'terms'    => $child_term->slug
                            ),
                        )
                    );
                    
                    
                    
                    $child_title = $child_term->name;
                    
                    echo '<h3 class="child-instrument-title">'. $child_title . '</h3>';
                    
                    echo '<div class="overflow-hidden">';
                    echo '<div class="list-musicians_margin">';
                    echo '<div class="list-musicians grid">';
                    
                    //get child tax posts
                    foreach(get_posts($args) as $post) {

                        if(is_object($post))
                            $id = $post->ID;
                        else
                            $id = $post;

                        $url = get_permalink($id).'?type=donation';
                        
                        //echo '['.print_r($url,true).']';
                        $theme->the_part('loop-artist',['post_id' => $id, 'url' => $url]);
                    }
                    
                    echo '</div></div></div>';
                    
                }
                
                echo '</div>';

            }
            ?>

	</div>
</div>

<?php get_footer();?>