<?php 
global $theme;

$series_title = get_field('series_title');
$series = get_field('series');
$series_button = get_field('series_button');


?>

<section class="recommended-section pt-100 pb-100">
                <div class="container">
                    <div class="title pb-50">

                        <?php if($series_title) : ?>
                            <h1><?php echo $series_title;?></h1>
                        <?php endif;?>

                    </div>
                    <div class="slider-content pt-25 pb-25">
                        <div class="owl-carousel recommended-slider">

                            <?php 

                                foreach ($series as $post) {
                                    $theme->the_part('loop-series-lobby', $post);
                                }
                            ?>

                        </div><!-- /.recommended-slider -->                        
                    </div><!-- /.recommended-content -->

                    <div class="view-more">

                                <?php 
                                if( $series_button ): 
                                    $link_url = $series_button['url'];
                                    $link_title = $series_button['title'];
                                    $link_target = $series_button['target'] ? $series_button['target'] : '_self';
                                    ?>
                                    <a class="schedule" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"  data-aos="fade-in" data-aos-duration="500" data-aos-delay="500"><?php echo esc_html( $link_title ); ?> <img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>
                                <?php endif; ?>
            </div>
                </div><!-- /.container -->
            </section><!-- /.recommended-section -->