<?php 
/*
* Template Name: Musicians
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


$bannerImage =  wp_get_attachment_image_url( get_post_thumbnail_id( get_the_ID() ), 'full' );

?>


<section class="hero_page-section" >
		<div class="banner_image"> 
			<img src="<?php echo $bannerImage; ?>" alt="<?php echo get_post_meta( get_post_thumbnail_id( $ID ), '_wp_attachment_image_alt', true) ;?>"/> 
		</div>		
	<div class="container">
	
	</div>
	<div class="gradient-top"></div>
</section>

		
<div class="first-section">
	<div class="container">

		<div class="title_h2">
         
                <h2><?php $title = get_the_title( $page_id );
  echo $title;?></h2>
     
         
		</div>
      
               <div class="text_cont">
            <?php       $content = get_the_content(); echo $content;
 ?>
              </div>
      <div class="title-tabs clearfix_2">
			<div class="title_h3 pb-25">

                <?php if(get_field('category_text', $page_id)):?>
                    <h3 class="sub-title-simpler "><?php the_field('category_text', $page_id);?></h3>
                <?php else:?>
                    <h3 class="sub-title-simpler "><?php $cat_text_def_str; ?></h3>
                <?php endif;?>

              
			</div>

			<div class="right-tabs ">
			<div class="box-tabs_margin">
    <ul id="filters" class="box-tabs">

        <?php
        $is_english = (ICL_LANGUAGE_CODE === 'en');

        // קטגוריות לסינון (IDs)
        if ($is_english) {
            $level_7500_ids = ['278' , '280'];
            $level_10000_ids = ['281'];
        } else {
            $level_7500_ids = ['185', '186'];
            $level_10000_ids = ['94'];
        }

        // לייצר את הסינון ל"כסא נגן ראשי" ו"כסא נגן ראשון"
        $filter_7500 = implode(', ', array_map(fn($id) => ".category-{$id}", $level_7500_ids));
        $filter_10000 = implode(', ', array_map(fn($id) => ".category-{$id}", $level_10000_ids));

        // הכנה לפילטר של "כל הנגנים"
        $all_exclude_filters = array_merge($level_7500_ids, $level_10000_ids);
        $filter_all = '.artist_box.loop-artist' . implode('', array_map(fn($id) => ":not(.category-{$id})", $all_exclude_filters));
        ?>

        <!-- כפתור: כל הנגנים -->
        <li>
            <button class="box-tabs_button" data-filter="<?php echo $filter_all; ?>">
                <?php echo $is_english ? 'Tutti Musicians' : 'כל הנגנים'; ?>
            </button>
            <p><?php echo $is_english ? '$5,000' : '18,000 ₪'; ?></p>
        </li>

        <!-- כפתור: Principal and assistant Principal Position -->
        <li>
            <button class="box-tabs_button" data-filter="<?php echo $filter_7500; ?>">
                <?php echo $is_english ? 'Principal and assistant Principal Position' : 'כיסא נגן ראשון ומשנה לנגן ראשון'; ?>
            </button>
            <p><?php echo $is_english ? '$10,000' : '36,000 ₪'; ?></p>
        </li>

        <!-- כפתור: Concertmaster -->
        <li>
            <button class="box-tabs_button" data-filter="<?php echo $filter_10000; ?>">
                <?php echo $is_english ? 'Concertmaster' : 'כיסא נגן ראשי'; ?>
            </button>
            <p><?php echo $is_english ? '$15,000' : '54,000 ₪'; ?></p>
        </li>

    </ul>
</div>

			</div>
		</div>
      <div class="artist_grid list-musicians grid" data-aos="fade-in" data-aos-duration="400" >
	<?php 
// Initialize array to track displayed post IDs
$displayed_posts = array();

// Loop through all the instruments taxonomy
foreach($instruments as $instrument) {
    $parent_id = $instrument->term_id;
    $parent_instrument_title = $instrument->name;

    // Check if the current instrument should be excluded
    if (in_array($parent_id, [392, 399])) {
        continue;
    }

    $termchildren = get_terms('instrument', array('child_of' => $parent_id ));

    // Display parent posts when there's no child
    if (empty($termchildren)) {
        $parent_term = get_term_by('id', $parent_id, 'instrument');

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

        // Get parent posts
        foreach (get_posts($args) as $post) {
            $id = $post->ID;

            // Skip if this post has already been displayed
            if (in_array($id, $displayed_posts)) {
                continue;
            }

            // Add the post ID to the array to track it
            $displayed_posts[] = $id;

            $url = get_permalink($id).'?type=donation';
            $theme->the_part('loop-artist', ['post_id' => $id, 'url' => $url]);
        }

        echo '</div></div></div>';
    }

    // Loop through child instruments taxonomy
    foreach ($termchildren as $child) {
        $child_term = get_term_by('id', $child->term_id, 'instrument');

        // Skip if the child term should be excluded
        if (in_array($child->term_id, [392, 399])) {
            continue;
        }

        $args = array(
            'posts_per_page'   => 100,
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

        // Get child taxonomy posts
        foreach (get_posts($args) as $post) {
            $id = is_object($post) ? $post->ID : $post;

            // Skip if this post has already been displayed
            if (in_array($id, $displayed_posts)) {
                continue;
            }

            // Add the post ID to the array to track it
            $displayed_posts[] = $id;

            $url = get_permalink($id).'?type=donation';
            $theme->the_part('loop-artist', ['post_id' => $id, 'url' => $url]);
        }
    }
}
?>

  </div>
	</div>
</div>

<?php get_footer();?>