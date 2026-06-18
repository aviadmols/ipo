<?php 

/*

* Template Name: Lobby

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

?>


   <div class="main-content">
            <section class="hero-section" style="background-image: url(<?php echo $bannerImage; ?>);">
                  <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
<div class="gradient-bottom" style="max-height: 50%;">
                                    </div>
          <div class="gradient-top" style="">
                                    </div>
                <div class="container">
                    <div class="hero-text">
                        <h1 class="header-title pb-25"><?php echo get_the_title(); ?></h1>
                      	
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
                      	<?php endif;?>
                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->


      <!-- =============== Recommended series =============== -->
            <?php $theme->the_part('Recommended-series'); ?>
        <!-- =============== upcoming area end =============== -->
        
               

            <section class="subscribe-section pt-100 pb-100">
                <div class="container">
                    <div class="subscribe-content">
                        <div class="title">
                            <?php if($subscribe_title) : ?>
                            <h2 class="lette-sapce-10"><?php echo $subscribe_title;?></h2>
                            <?php endif;?>
                        </div>
                        <div class="sb-text">

                            <?php echo $subscribe_text;?>

                          <div class="view-more pt-50">

                                <?php 
                                if( $subscribe_link ): 
                                    $link_url = $subscribe_link['url'];
                                    $link_title = $subscribe_link['title'];
                                    $link_target = $subscribe_link['target'] ? $subscribe_link['target'] : '_self';
                                    ?>
                                    <a class="white-color" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                        </div>                        
                    </div>
                </div><!-- /.container -->
            </section><!-- /.subscribe-section -->

            <section class="key-plan pt-100 pb-100">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="text">
                                <div class="title">

                                    <?php if($program_title) : ?>
                                     <h2 class="lette-sapce-10"><?php echo $program_title;?></h2>
                                    <?php endif;?>

                                    <?php if($program_subtitle) : ?>
                                    <h3 class="sub-title-simpler"><?php echo $program_subtitle;?></h3>
                                    <?php endif;?>

                                </div>   
                                <?php echo $program_text;?>

                                     <div class="view-more ">
                                <?php 
                                if( $program_link ): 
                                    $link_url = $program_link['url'];
                                    $link_title = $program_link['title'];
                                    $link_target = $program_link['target'] ? $program_link['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                                            
                            </div><!-- /.text -->
                        </div>
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $program_image = new wpstack_image($program_image);

                                    echo $program_image->get_img();

                                ?>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.key-plan -->
			
				      <!-- =============== upcoming area start =============== -->
            <?php $theme->the_part('section-upcoming'); ?>
        <!-- =============== upcoming area end =============== -->


     
            <section class="characters-section pt-100 pb-100">
                <div class="container">
                    <div class="title style-1 pb-100">

                        <?php if($podcast_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $podcast_title;?></h2>
                        <?php endif;?>

                        <?php if($podcast_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $podcast_subtitle;?></h3>
                        <?php endif;?>

                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $podcast_image = new wpstack_image($podcast_image);

                                    echo $podcast_image->get_img();

                                ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="cs-text">
                                <ul>
                                    <?php foreach($podcasts as $podcast) : ?> 
                                        <li>
                                            <div class="text">

                                                <?php if($podcast['title']) : ?>
                                                 <h3 class="Simpler"><?php echo $podcast['title'];?></h3>
                                                <?php endif;?>

                                                <?php if($podcast['subtitle']) : ?>
                                                 <p><?php echo $podcast['subtitle'];?></p>
                                                <?php endif;?>

                                            </div>
                                            <div class="link">

                                                <?php if($podcast['audio_file']) : ?>
                                                    <a href="<?php echo $podcast['audio_file']['url'];?>">להשמעה  <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
                                                <?php endif;?>

                                            </div>
                                        </li>

                                    <?php endforeach;?>

                                </ul>

                              
								       <div class="view-more pt-25">

                                        <?php 
                                        if( $podcast_link ): 
                                        $link_url = $podcast_link['url'];
                                        $link_title = $podcast_link['title'];
                                        $link_target = $podcast_link['target'] ? $podcast_link['target'] : '_self';
                                        ?>
                                        <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                    <?php endif; ?>
            </div>
                            </div><!-- /.cs-text -->
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.characters-section -->

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
                                            <h3><?php echo $video['title'];?>

                                            <?php  if ($video['subtitle']) {  echo '<small style="display: block;">' . $video['subtitle'] .'</small>'; } ?>
</h3>
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

           
        <!-- =============== meet area start =============== -->
            <?php $theme->the_part('section-artists',[
                'query'=>[
                    'tax_query' => [
                        [
                            'taxonomy' => 'artist_cat',
                            'field' => 'term_id',
                            'terms' => $artists // כינור
                        ]
                    ]
                ]
            ]); ?>
        <!-- =============== meet area end =============== -->

            <section class="cta-section text-center pt-100 pb-100">
                <div class="container">
                    <div class="thumb">
                        <img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/cta.svg' ) ); ?>" alt="Img" class="img-fluid">
                    </div>
                    <div class="buttons">
                        <a href="#" class="btn">חברי המנהלה  </a>
                        <a href="#" class="btn">חברי ההנהלה  </a>
                    </div>
                </div><!-- /.container -->
            </section><!-- /.cta-section -->

        </div><!-- /.main-content -->

<?php get_footer(); ?>