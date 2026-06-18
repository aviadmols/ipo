<?php 

if(isset($params['query'])) $query = $params['query'];
else $query = [];


$number_of_artists = get_field('number_of_artists') ? get_field('number_of_artists') : 20;

$query = shortcode_atts([
    'post_type' => 'artist',
    'posts_per_page' => $number_of_artists,
    'orderby' => 'menu_order',
'order'=>'ASC',
    'tax_query' => []
], $query);

$artists = get_posts($query);

/*
$artist_category = get_field('artists');

$tax_query = array();

if($artist_category){
    $tax_query = array(
        array(
            'taxonomy' => 'artist_cat',
            'field' => 'term_id',
            'terms' => $artist_category
        )
    );
}


$artists = get_posts(array(
    'post_type' => 'artist',
    'posts_per_page' => $number_of_artists,
    'tax_query' => $tax_query
));

*/

$artists_title = get_field('artists_title');
$artists_link = get_field('artists_link');


if($artists): ?>
    <section class="meet_area section-view pt-100 pb-100">
        <div>
                
            <div class="container">
                <?php if($artists_title) : ?>
                 <h2 class="lette-sapce-10 ml10 animate-letters"><?php echo $artists_title;?></h2>
              
                  <?php else: ?>
    <h2 class="lette-sapce-10 ml10 animate-letters">           
הכירו את הנגנים שלנו
 </h2>
                <?php endif;?>
            </div>

            <!-- slider start -->
            <div class="owl-carousel owl-theme meet-slider" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500"  data-aos-delay="0">

                <?php 

$delay = 200;
                    foreach($artists as $artist){

					

                        $title = get_the_title($artist);
                        $instrument = get_the_terms($artist, 'instrument');
                        $instrument = $instrument[0]->name;
                        $image = get_field('image', $artist);

                        // get image url from image id
                        if($image){
                            $image = wp_get_attachment_image($image, 'thumbnail');

                        }else{
                            $image = get_the_post_thumbnail($artist);
                        }


                        if($title){
                            $title = '<h3 data-aos="fade-in" data-aos-offset="0" data-aos-duration="500"  data-aos-delay="100">'.$title.'</h3>';
                        }

                        if($instrument){
                            $instrument = '<p data-aos="fade-in" data-aos-offset="0" data-aos-duration="500"  data-aos-delay="150">'.$instrument.'</p>';
                        }

                        if (!$image) {
                            $e_class = 'image-empty';
                        }else{
                            $e_class = 'has-image';
                        }
                        

                        	if($image) {
                        echo '<div class="item '.$e_class.' " data-aos="fade-in" data-aos-offset="0" data-aos-duration="500"  data-aos-delay="300">
                                    <div class="img_box">
                                        
                                        <div class="img position-relative">
                                            '.$image.'
                                            <a class="overlay-link" href="'. get_permalink($artist) .'"></a>
                                        </div>
                                        <div class="content">
                                            '.$title.'
                                            '.$instrument.'
                                        </div>
                                    </div>
                                </div>';
                            }

$delay = $delay + 50;

                    }
                ?>
                
            </div>
            <!-- slider end -->

            <div class="container">
                <?php 
                    if( $artists_link ): 
                    $link_url = $artists_link['url'];
                    $link_title = $artists_link['title'];
                    $link_target = $artists_link['target'] ? $artists_link['target'] : '_self';
                    ?>
                    <a class="schedule" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>
                <?php endif; ?>
            </div>

        </div>
    </section>
<?php endif; ?>