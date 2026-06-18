<?php

/* Template Name: Home */ 

get_header();

global $theme;


$artists = get_field('artists');


?>



        <!-- =============== Hero area start =============== -->
            <?php $theme->the_part('section-hero'); ?>
        <!-- =============== Hero area end =============== -->

	

        <!-- women of jazz banner start -->
            <?php $slider_banner = get_field('slider_banner_2');

if ($slider_banner) {
    $theme->the_part('section-banner-slider-2');
} else {
    $theme->the_part('section-banner');
} ?>
        <!-- women of jazz banner end -->


       

        <!-- =============== upcoming area start =============== -->
            <?php $theme->the_part('section-upcoming'); ?>
        <!-- =============== upcoming area end =============== -->
		
 <!-- =============== Hero area start =============== -->
        <?php    $theme->the_part('section-banner-slider'); ?>
        <!-- =============== Hero area end =============== -->		
	

        <!-- =============== links area start =============== -->
            <?php $theme->the_part('section-tags'); ?>
        <!-- =============== links area end =============== -->
        
        <!-- =============== Calendar area start =============== -->
            <?php $theme->the_part('section-calendar'); ?>
        <!-- =============== Calendar area end =============== -->

        <!-- =============== video area start =============== -->
            <?php if (!empty(get_field('video_banner_mp4'))) { $theme->the_part('section-video-banner'); }?>
        <!-- =============== video area end =============== -->

      
     
            <!-- =============== meet area start =============== -->
            <?php $theme->the_part('section-artists',[
                'query'=>[
                    'tax_query' => [
                        [
                            'taxonomy' => 'instrument',
                            'field' => 'term_id',
                            'terms' => $artists // כינור
                        ]
                    ]
                ]
            ]); ?>
        <!-- =============== meet area end =============== -->
     

<?php get_footer();?>