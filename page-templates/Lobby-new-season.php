<?php 
/*
* Template Name: Lobby New season
*/
get_header();


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
$class = '';

$videos_title = get_field('videos_title');
$videos_subtitle = get_field('videos_subtitle');
$videos = get_field('videos');
$videos_link = get_field('videos_link');
$white = get_field('white');
$link_banner = get_field('link_banner');
$link_banner_02 = get_field('link_banner_02');
$class = '';
$white = get_field('white');

$class = '';
$bannerImage = wp_get_attachment_image_url( get_post_thumbnail_id( get_the_ID() ), 'full' );
if (empty($bannerImage) ){
  $bannerImage = ipo_theme_uri( 'includes/images/bg/1.jpg' );
  }

$banner_image_mobile  = get_field('banner_image_mobile_main');
$color_header = get_field('color_header');
if ($color_header){
echo '<style>.hero-text *{
text-shadow: none!important;
  color:'. $color_header.'!important;
  }</style>';
}

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
if ($banner_image_mobile) {
           echo '<img src="'. wp_get_attachment_url($banner_image_mobile).'" />';
}else{
  echo '<img src="'.$bannerImage.'" />';
}
if ($banner_image_mobile) {
 echo '<style>
 @media (max-width: 768px){
 
 .key-plan.pt-50 {
 padding-top: 0px!important;
 }

.upcoming_area   .owl-stage-outer {
    position: relative;
    overflow: visible!important;
}
 
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
<section class="hero-section" style="background-image: url(<?php echo $bannerImage; ?>); position: relative; ">
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
      <?php
           if($white) {
   echo '<div class="gradient-bottom" style="max-height: 30%;" >
                                    </div>';
             
               echo '<div class="gradient-top" style="" >
                                    </div>';
                                      
                                      
}?>
                              <?php
      if($white) {
$class = 'white-text';
}?>

                <div class="container" style="z-index: 3;">
                    <div class="hero-text">
                        <h1 class="header-title pb-25 <?php echo $class; ?>"><?php echo get_the_title(); ?></h1>
                
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text <?php echo $class; ?>"><?php echo $subtitle;?></h3>
                      	<?php endif;?>

                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->






   <section class="key-plan pt-50">
<?php 
$program_title = get_field('program_title');
$program_subtitle = get_field('program_subtitle');
$program_text = get_field('program_text');
$program_link = get_field('program_link');
$program_image = get_field('program_image');
?>
                <div class="container">
                    <div class=" align-items-center">
                        <div class="">
                            <div class="text"  style="    max-width: 100%;">
                                <div class="title" >

                                    <?php if($program_title) : ?>
                                     <h2 class="lette-sapce-10" data-aos-duration="100" data-aos-delay="100"><?php echo $program_title;?></h2>
                                    <?php endif;?>

                                    <?php if($program_subtitle) : ?>
                                    <h3 class="sub-title-simpler" data-aos-duration="150" data-aos-delay="150"><?php echo $program_subtitle;?></h3>
                                    <?php endif;?>

                                </div>   
                              <div data-aos-duration="200" data-aos-delay="200">
                                <?php echo $program_text;?>
                              </div>
                                     <div class="view-more " data-aos-duration="250" data-aos-delay="250">
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
                            <div class="thumb"  data-aos="fade-in" data-aos-duration="300" data-aos-delay="300">
                                <?php  

                                    $program_image = new wpstack_image($program_image);

                                    echo $program_image->get_img();

                                ?>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.key-plan -->


      <!-- =============== video area start =============== -->
            <?php if (!empty(get_field('video_banner_mp4'))) { $theme->the_part('section-video-banner'); }?>
        <!-- =============== video area end =============== -->

      

      <!-- =============== upcoming area start =============== -->
            <?php $theme->the_part('section-upcoming'); ?>
        <!-- =============== upcoming area end =============== -->
		
		
        


    <section class="subscribe-section pt-100 pb-100">
<?php 
$subscribe_title = get_field('subscribe_title');
$subscribe_text = get_field('subscribe_text');
$subscribe_link = get_field('subscribe_link');
?>
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


      <!-- =============== Recommended series =============== -->
            <?php $theme->the_part('Recommended-series'); ?>
        <!-- =============== upcoming area end =============== -->


<!-- /.cta-section -->
            <section class="cta-section text-center pt-100 pb-100">
                <div class="container">
                    
<?php 
$link_banner = get_field('link_banner');
$link_banner_02 = get_field('link_banner_02');
?>
                    <div class="buttons">
                             <?php  if( $link_banner ): 
                                   $link_banner_url = $link_banner['url'];
                                    $link_title_banner = $link_banner['title'];
                                    $link_target_banner = $link_banner['target'] ? $link_banner['target'] : '_self';
                                    ?>
                                   <a  class="btn" href="<?php echo esc_url( $link_banner_url ); ?>" target="<?php echo esc_attr($link_target_banner); ?>"><?php echo esc_html($link_title_banner); ?></a>
                                <?php endif; ?>
                      
                       <?php  if( $link_banner_02): 
                                   $link_banner_02_url = $link_banner_02['url'];
                                    $link_title_banner_02 = $link_banner_02['title'];
                                    $link_target_banner_02 = $link_banner_02['target'] ? $link_banner_02['target'] : '_self';
                                    ?>
                                   <a  class="btn" href="<?php echo esc_url( $link_banner_02_url ); ?>" target="<?php echo esc_attr($link_target_banner_02); ?>"><?php echo esc_html($link_title_banner_02); ?></a>
                                <?php endif; ?>
               
                    </div>
                </div><!-- /.container -->
            </section><!-- /.cta-section -->

<?php get_footer(); ?>