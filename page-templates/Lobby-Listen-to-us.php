<?php 

/*

* Template Name: Lobby Listen to us

*/


get_header();

global $theme;

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$video_categories = get_field('video_categories');

$subtitle = get_field('subtitle');

$subscribe_title = get_field('subscribe_title');
$subscribe_text = get_field('subscribe_text');
$subscribe_link = get_field('subscribe_link');

$program_title = get_field('program_title');
$program_subtitle = get_field('program_subtitle');
$program_text = get_field('program_text');
$program_link = get_field('program_link');
$program_image = get_field('program_image');


$podcast_title = get_field('podcast_title');
$podcast_subtitle = get_field('podcast_subtitle');
$podcast_image = get_field('podcast_image');
$podcasts = get_field('podcasts');
$podcast_link = get_field('podcast_link');


$videos_title = get_field('videos_title');
$videos_subtitle = get_field('videos_subtitle');
$videos = get_field('videos');
$videos_link = get_field('videos_link');

$artists = get_field('artists');
$bannerImage = wp_get_attachment_image_url( get_post_thumbnail_id( get_the_ID() ), 'full' );
if (empty($bannerImage) ){
  $bannerImage = ipo_theme_uri( 'includes/images/bg/1.jpg' );
  
}

$banner_image_mobile  = get_field('banner_image_mobile_main');

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


   <div class="main-content">
            <section class="hero-section" style="background-image: url(<?php echo $bannerImage; ?>);">
                  <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
                <div class="container">
                    <div class="hero-text">
                        <h1 class="header-title pb-25"><?php echo get_the_title(); ?></h1>
                      	
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
                      	<?php endif;?>
                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->




			


     
        
            <section class="video-section pb-100">
                <div class="container">
                    <div class="title style-1 pb-100">

                        <?php if($videos_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            <?php foreach($videos as $video) : ?>
                                <div class="video">
                                    <div class="thumb">
    								 <div class="overlay-background">
                                    </div>
                                        <?php  
                                            $video_image = new wpstack_image($video['image']);

                                            echo $video_image->get_img();

                                        ?>

                                        <?php if($video['title']) : ?>
                                            <h3><?php echo $video['title'];?></h3>
                                        <?php endif;?>

                                        
                                    </div>
                                    <div class="overlay">

                                        <?php if($video['video_url']) : ?>
                                            <a href="<?php echo $video['video_url'];?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
                                </div><!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   <div class="view-more pt-25">
                                <?php 
                                    if( $videos_link ): 
                                    $link_url = $videos_link['url'];
                                    $link_title = $videos_link['title'];
                                    $link_target = $videos_link['target'] ? $videos_link['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     
     
     
         <section class="video-section pb-100">
                <div class="container">
                    <div class="title style-1 pb-100">

                        <?php if($videos_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            <?php foreach($videos as $video) : ?>
                                <div class="video">
                                    <div class="thumb">
    								 <div class="overlay-background">
                                    </div>
                                        <?php  
                                            $video_image = new wpstack_image($video['image']);

                                            echo $video_image->get_img();

                                        ?>

                                        <?php if($video['title']) : ?>
                                            <h3><?php echo $video['title'];?></h3>
                                        <?php endif;?>

                                        
                                    </div>
                                    <div class="overlay">

                                        <?php if($video['video_url']) : ?>
                                            <a href="<?php echo $video['video_url'];?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
                                </div><!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   <div class="view-more pt-25">
                                <?php 
                                    if( $videos_link ): 
                                    $link_url = $videos_link['url'];
                                    $link_title = $videos_link['title'];
                                    $link_target = $videos_link['target'] ? $videos_link['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     
     
     
         <section class="video-section pb-100">
                <div class="container">
                    <div class="title style-1 pb-100">

                        <?php if($videos_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            <?php foreach($videos as $video) : ?>
                                <div class="video">
                                    <div class="thumb">
    								 <div class="overlay-background">
                                    </div>
                                        <?php  
                                            $video_image = new wpstack_image($video['image']);

                                            echo $video_image->get_img();

                                        ?>

                                        <?php if($video['title']) : ?>
                                            <h3><?php echo $video['title'];?></h3>
                                        <?php endif;?>

                                        
                                    </div>
                                    <div class="overlay">

                                        <?php if($video['video_url']) : ?>
                                            <a href="<?php echo $video['video_url'];?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
                                </div><!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   <div class="view-more pt-25">
                                <?php 
                                    if( $videos_link ): 
                                    $link_url = $videos_link['url'];
                                    $link_title = $videos_link['title'];
                                    $link_target = $videos_link['target'] ? $videos_link['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     
     
     
         <section class="video-section pb-100">
                <div class="container">
                    <div class="title style-1 pb-100">

                        <?php if($videos_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            <?php foreach($videos as $video) : ?>
                                <div class="video">
                                    <div class="thumb">
    								 <div class="overlay-background">
                                    </div>
                                        <?php  
                                            $video_image = new wpstack_image($video['image']);

                                            echo $video_image->get_img();

                                        ?>

                                        <?php if($video['title']) : ?>
                                            <h3><?php echo $video['title'];?></h3>
                                        <?php endif;?>

                                        
                                    </div>
                                    <div class="overlay">

                                        <?php if($video['video_url']) : ?>
                                            <a href="<?php echo $video['video_url'];?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
                                </div><!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   <div class="view-more pt-25">
                                <?php 
                                    if( $videos_link ): 
                                    $link_url = $videos_link['url'];
                                    $link_title = $videos_link['title'];
                                    $link_target = $videos_link['target'] ? $videos_link['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     

            <section class="thumb-content">
                <div class="thumb">
                    <img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/img2.png' ) ); ?>" alt="Img" class="img-fluid">
                </div>
            </section><!-- /.video-section -->

           
   

        </div><!-- /.main-content -->

<?php get_footer(); ?>