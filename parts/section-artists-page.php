<?php 

if(isset($params['query'])) $query = $params['query'];
else $query = [];


$number_of_artists = get_field('number_of_artists') ? get_field('number_of_artists') : 20;

$query = shortcode_atts([
    'post_type' => 'artist',
    'posts_per_page' => $number_of_artists,
    'orderby' => 'rand',
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

if($artists): ?>
    <section class="meet_area section-view">
        <div>
                
            <div class="container animate_wow">
                <h2 class="lette-sapce-10">
                   
                </h2>
            </div>

            <!-- slider start -->
            <div class="">

                <?php 


                    foreach($artists as $artist){



                        $title = get_the_title($artist);
                        $instrument = get_the_terms($artist, 'instrument');
                        $instrument = $instrument[0]->name;
                        $image = get_field('image', $artist);

                        // get image url from image id
                        if($image){
                            $image = wp_get_attachment_image($image, 'full');

                        }else{
                            $image = get_the_post_thumbnail($artist);
                        }


                        if($title){
                            $title = '<h3>'.$title.'</h3>';
                        }

                        if($instrument){
                            $instrument = '<p>'.$instrument.'</p>';
                        }

                        if (!$image) {
                            $e_class = 'image-empty';
                        }else{
                            $e_class = 'has-image';
                        }
                        

                        
                        echo '<div class="item '.$e_class.' ">
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
                ?>
                
            </div>
            <!-- slider end -->

            <div class="container">
                <a class="schedule" href="#"  data-aos="fade-in" data-aos-duration="500" data-aos-delay="500">לכל חברי התזמורת<img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
            </div>

        </div>
    </section>
<?php endif; ?>