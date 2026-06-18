<?php 

/*

* Template Name: Lobby Education

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


$program_title_1 = get_field('program_title_1');
$program_subtitle_1 = get_field('program_subtitle_1');
$program_text_1 = get_field('program_text_1');
$program_link_1 = get_field('program_link_1');
$program_image_1 = get_field('program_image_1');



$program_title_2 = get_field('program_title_2');
$program_subtitle_2 = get_field('program_subtitle_2');
$program_text_2 = get_field('program_text_2');
$program_link_2 = get_field('program_link_2');
$program_image_2 = get_field('program_image_2');


$program_title_3 = get_field('program_title_3');
$program_subtitle_3 = get_field('program_subtitle_3');
$program_text_3 = get_field('program_text_3');
$program_link_3 = get_field('program_link_3');
$program_image_3 = get_field('program_image_3');

$program_title_4 = get_field('program_title_4');
$program_subtitle_4 = get_field('program_subtitle_4');
$program_text_4 = get_field('program_text_4');
$program_link_4 = get_field('program_link_4');
$program_image_4 = get_field('program_image_4');


$podcast_title = get_field('podcast_title');
$podcast_subtitle = get_field('podcast_subtitle');
$podcast_image = get_field('podcast_imag');
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
<div class="container flex-pc max-1440" style="background: #fff; padding-bottom: 0px; padding-top: 15px;">
   <div class="content width-25">
<h1 class="title-pages" style="    font-size: 60px!important; color: #000000; margin-right: 0px!important; margin-left: 0px!important; letter-spacing: 3px!important;">
<?php echo get_the_title(); if ( get_field('secoundtitle')) { echo '<br>'; echo  get_field('secoundtitle');}?></h1>
       
                </div>
</div>
</section>
            <section class="hero-section" style="background-image: url(<?php echo $bannerImage; ?>);">

              <div class="gradient-bottom"></div>
              <div class="gradient-top"></div>
                  <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
                <div class="container">
                    <div class="hero-text">
                        <h1 class="header-title pb-25"><?php echo get_the_title(); if ( get_field('secoundtitle')) { echo '<br>'; echo  get_field('secoundtitle');}?></h1>
                      	
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
                      	<?php endif;?>
                      
                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->
     
      <style>
      .key-plan .wpstack-image img {
    width: 100%!important;
      }
      
     </style>

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
			
     <?php if($subscribe_title) : ?>
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
          <?php endif; ?>
     
        <section class="key-plan pt-100 pb-100" style="background: #f7f7f7;">
                <div class="container">
                    <div class="row align-items-center row-reverse">
                        <div class="col-lg-6">
                            <div class="text">
                                <div class="title">

                                    <?php if($program_title_1) : ?>
                                     <h2 class="lette-sapce-10"><?php echo $program_title_1;?></h2>
                                    <?php endif;?>

                                    <?php if($program_subtitle_1) : ?>
                                    <h3 class="sub-title-simpler"><?php echo $program_subtitle_1;?></h3>
                                    <?php endif;?>

                                </div>   
                                <?php echo $program_text_1;?>

                                     <div class="view-more ">
                                <?php 
                                if( $program_link_1 ): 
                                    $link_url_1 = $program_link_1['url'];
                                    $link_title_1 = $program_link_1['title'];
                                    $link_target_1 = $program_link_1['target'] ? $program_link_1['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url_1 ); ?>" target="<?php echo esc_attr( $link_target_1 ); ?>"><?php echo esc_html( $link_title_1 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                                            
                            </div><!-- /.text -->
                        </div>
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $program_image_1 = new wpstack_image($program_image_1);

                                    echo $program_image_1->get_img();

                                ?>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.key-plan -->
			
     
     
     
        <section class="key-plan pt-100 pb-100">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="text">
                                <div class="title">

                                    <?php if($program_title_2) : ?>
                                     <h2 class="lette-sapce-10"><?php echo $program_title_2;?></h2>
                                    <?php endif;?>

                                    <?php if($program_subtitle_2) : ?>
                                    <h3 class="sub-title-simpler"><?php echo $program_subtitle_2;?></h3>
                                    <?php endif;?>

                                </div>   
                                <?php echo $program_text_2;?>

                                     <div class="view-more ">
                                <?php 
                                if( $program_link_2 ): 
                                    $link_url_2 = $program_link_2['url'];
                                    $link_title_2 = $program_link_2['title'];
                                    $link_target_2 = $program_link_2['target'] ? $program_link_2['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url_2 ); ?>" target="<?php echo esc_attr( $link_target_2 ); ?>"><?php echo esc_html( $link_title_2 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                                            
                            </div><!-- /.text -->
                        </div>
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $program_image_2 = new wpstack_image($program_image_2);

                                    echo $program_image_2->get_img();

                                ?>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.key-plan -->
			
     
     
        <section class="key-plan pt-100 pb-100" style="background: #f7f7f7;">
                <div class="container">
                    <div class="row align-items-center row-reverse">
                        <div class="col-lg-6">
                            <div class="text">
                                <div class="title">

                                    <?php if($program_title_3) : ?>
                                     <h2 class="lette-sapce-10"><?php echo $program_title_3;?></h2>
                                    <?php endif;?>

                                    <?php if($program_subtitle_3) : ?>
                                    <h3 class="sub-title-simpler"><?php echo $program_subtitle_3;?></h3>
                                    <?php endif;?>

                                </div>   
                                <?php echo $program_text_3;?>

                                     <div class="view-more ">
                                <?php 
                                if( $program_link_3 ): 
                                    $link_url_3 = $program_link_3['url'];
                                    $link_title_3 = $program_link_3['title'];
                                    $link_target_3 = $program_link_3['target'] ? $program_link_3['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url_3 ); ?>" target="<?php echo esc_attr( $link_target_3 ); ?>"><?php echo esc_html( $link_title_3 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                <?php endif; ?>
            </div>
                                            
                            </div><!-- /.text -->
                        </div>
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $program_image_3 = new wpstack_image($program_image_3);

                                    echo $program_image_3->get_img();

                                ?>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.key-plan -->

               </div><!-- /.main-content -->

<?php get_footer(); ?>