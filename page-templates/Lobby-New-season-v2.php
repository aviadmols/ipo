<?php 
/*
* Template Name: Lobby New season V2
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



$program_title_2 = get_field('program_title_2');
$program_subtitle_2 = get_field('program_subtitle_2');
$program_text_2 = get_field('program_text_2');
$program_link_2 = get_field('program_link_2');
$program_image_2 = get_field('program_image_2');



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

$banner_image_mobile  = get_field('main_banner_image_mobile');
$color_header = get_field('color_header');
$background_color = get_field('background_color_1');
$btn_download_bg = $background_color ? $background_color : '#ffc5d7';
$colo_btn = get_field('COLO_BTN');
$btn_cta_color = $colo_btn ? $colo_btn : '#431F4C';
$is_english = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en')
    || apply_filters('wpml_current_language', null) === 'en';
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

[lang="he-IL"] .recommended-slider .owl-next img, [lang="he-IL"] .recommended-slider .owl-prev img {
    transform: rotate(0deg)!important;
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
$banner_image_mobile  = get_field('main_banner_image_mobile');
if ($banner_image_mobile) {
           echo '<img src="'. $banner_image_mobile .'"  style="min-height: 25vh !important;" />';
}else{
  echo '<img src="'.$bannerImage.'" />';
}
if ($banner_image_mobile) {
 echo '<style>
 @media (max-width: 768px){
 
 .key-plan.pt-50 {
 padding-top: 0px!important;
 }

 .hero-section{
     min-height: 265px;
background-image: url(' . $banner_image_mobile .')!important;
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
                        <h1 class="header-title pb-25 <?php echo $class; ?>" style="text-shadow: none !important;"><?php echo get_the_title(); ?></h1>
                
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text <?php echo $class; ?>"><?php echo $subtitle;?></h3>
                      	<?php endif;?>

                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->



   <section class="subscribe-section program-info  pt-50 pb-50" style="background-color: #f9f9f9;">
     
     <style>
       .subscribe-section  * {
         color: #000!important;
       }

   .program-info .read-more-text {
                                display: none;
 }
     .program-info.show-read-more .read-more-text {
          display: block;
  }
       
   
       
       
       .program-info.show-read-more  .text_read_more {
        display: none!important; 
       }

       
          
       .program-info.show-read-more  .text_read_lass {
        display: block!important; 
       }

.text_read_lass {
  display: none;
       }

.subscribe-content .title {
    display: flex;
    align-items: flex-start!important;
       }
     </style>
<?php 
$program_title = get_field('program_title');

?>
                <div class="container">
                    <div class="subscribe-content">
                        <div class="title">
                            <?php if($program_title) : ?>
                            <h2 class="lette-sapce-10" style="color: #000 !important;"><?php echo $program_title; ?></h2>
                            <?php endif;?>
                        </div>
                        <div class="sb-text">

                            <?php echo get_field('program_description');
       $is_read_more = get_field('program_description_is_read_more');
                            $read_more = get_field('program_description_read_more');
 if($is_read_more && $read_more){
$readmoretext = 'לקריאה נוספת';
if(ICL_LANGUAGE_CODE == 'en'){
   $readmoretext  = 'Read More';

}
                                                

                                echo '<div class="read-more-text" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="300">'. do_shortcode($read_more) .'</div>';
                                echo '<a  class="read-more" style="cursor: pointer;"><span class="text_read_more">'. $readmoretext .'</span><span class="text_read_lass">קרא פחות</span> <img src="'. ipo_arrow_icon_url() .'" alt=""></a>';

                              
                            }
?>
               
                        </div>                        
                    </div>
                </div><!-- /.container -->
            </section><!-- /.subscribe-section -->


      <!-- =============== video area start =============== -->
            <?php if (!empty(get_field('video_banner_mp4'))) : ?>
                <?php $theme->the_part('section-video-banner'); ?>
            <?php endif; ?>
        <!-- =============== video area end =============== -->

      

 
		      <!-- =============== Recommended series =============== -->
            <?php $theme->the_part('Recommended-series'); ?>
        <!-- =============== upcoming area end =============== -->
		
        
     <!-- women of jazz banner start -->
<?php

$banner_desktop = get_field('banner_image');
$banner_mobile = get_field('banner_image_mobile');
$Linknbanner = get_field('Season_Plan');
if( $banner_desktop || $banner_mobile ):

?>

<div class="womenOfJazz_banner section-view">
	
<?php 
echo '<a href="'. $Linknbanner .'">';
if( $banner_desktop ){
    $banner_desktop = new wpstack_image($banner_desktop);
    echo'<img src="'.$banner_desktop->get_src().'" class="w-100  hide-mobile " alt="'.$banner_desktop->get_alt().'"     data-aos="fade-in" data-aos-offset="100" data-aos-duration="500" loading="lazy">';
}

if( $banner_mobile ){
    $banner_mobile = new wpstack_image($banner_mobile);
    echo'<img src="'.$banner_mobile->get_src().'" class="w-100 hide-pc" alt="'.$banner_mobile->get_alt().'" data-aos="fade-in" data-aos-offset="100" data-aos-duration="500" loading="lazy" >';
}
echo '</a>';
?>

				<div class="btn_download" style="
    background: <?php echo esc_attr($btn_download_bg); ?>;
    display: flex;
    justify-content: center;
    padding-top: 25px;
    padding-bottom: 25px;
    margin-top: -1px;
">
<a href="<?php echo $Linknbanner; ?>" class="btn_cta" style="
    font-size: 1.8rem;
    color: <?php echo esc_attr($btn_cta_color); ?>;
    font-weight: 400;
    padding: 1rem 3rem;
    background: transparent;
    /* margin-left: 3.5rem; */
    border: 1px solid <?php echo esc_attr($btn_cta_color); ?>;
    border-radius: 3.3rem;
">
<?php echo get_field('season_program'); ?>
</a>

<?php





    $url_c = get_field('season_program_link');

?>

<a href="<?php echo $url_c; ?>" class="btn_cta" style="
    font-size: 1.8rem;
     color: <?php echo esc_attr($btn_cta_color); ?>;
    font-weight: 400;
    padding: 1rem 3rem;
    background: transparent;
    /* margin-left: 3.5rem; */
    border: 1px solid <?php echo esc_attr($btn_cta_color); ?>;
    border-radius: 3.3rem;
">
<?php echo get_field('Concert_schedule'); ?>
</a>
</div>
</div>


<?php endif; ?>
     

        <!-- women of jazz banner end -->

      <!-- =============== upcoming area start =============== -->
            <?php $theme->the_part('section-upcoming'); ?>
        <!-- =============== upcoming area end =============== -->



<section class="banners_2_section" style="
    background-color: #f9f9f9;
">
  <div>

<?php if( have_rows('slider_banner') ): ?>

    <!-- Slider for desktop -->
    <div class="banners_2 desktop pt-50  pb-50" data-aos="fade-up" data-aos-duration="750" >
        <?php while( have_rows('slider_banner') ): the_row();
            $image = get_sub_field('img_pc');
            $link = get_sub_field('link');
        ?>
            <div>
                <a href="<?php echo esc_url($link); ?>">
                    <img src="<?php echo esc_url($image); ?>" alt="" data-aos="fade-up" data-aos-duration="750" loading="lazy">
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Slider for mobile -->
    <div class="banners_2  mobile pb-50 pt-50" data-aos="fade-up" data-aos-duration="750">
        <?php while( have_rows('slider_banner') ): the_row();
            $image = get_sub_field('img_mobile');
            $link = get_sub_field('link');
        ?>
            <div>
                <a href="<?php echo esc_url($link); ?>">
                    <img src="<?php echo esc_url($image); ?>" alt="" data-aos="fade-up" data-aos-duration="750" loading="lazy">
                </a>
            </div>
        <?php endwhile; ?>
    </div>

<?php endif; ?>
  </div>
     </section>



  <!-- =============== Recommended series 2 =============== -->
<?php 
$series_title_2 = get_field('series_title_2');
$series_2 = get_field('series_2');

?>

<section class="recommended-section recommended  recommended-section_costume pt-50">
    <div class="container">
         <div class="slider-content pt-25 pb-25">
            <div class="owl-carousel recommended-slider">
                <?php foreach ($series_2 as $item) : ?>
                    <div class="series-item">
                        <?php if (!empty($item['image'])) : ?>
                            <a href="<?php echo esc_html($item['link']); ?>"><img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>"></a>
                        <?php endif; ?>
                        <?php if (!empty($item['title'])) : ?>
<div class="text">
            <h4><a href="<?php echo esc_html($item['link']); ?>"><?php echo esc_html($item['title']); ?></a></h4>
        </div>
                        <?php endif; ?>
                        <?php if (!empty($item['description'])) : ?>
                            <p><a href="<?php echo esc_html($item['link']); ?>"><?php echo esc_html($item['description']); ?></a></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div><!-- /.recommended-slider -->                        
        </div><!-- /.recommended-content -->

    
    </div><!-- /.container -->
</section><!-- /.recommended-section -->
<!-- =============== upcoming area end 2 =============== -->



         <?php if($program_text) : ?>
   <section class="key-plan pb-50">
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
      <?php endif; ?>



<script>
 document.addEventListener('DOMContentLoaded', function() {
    var readMoreBtn = document.querySelector('.program-info .read-more');
    if (readMoreBtn) {
        readMoreBtn.addEventListener('click', function() {
            var programInfo = document.querySelector('.program-info');
            if (programInfo) {
                // בודק אם ה-class 'show-read-more' כבר מוסף לאלמנט
                if (programInfo.classList.contains('show-read-more')) {
                    programInfo.classList.remove('show-read-more'); // מסיר את ה-class אם קיים
                } else {
                    programInfo.classList.add('show-read-more'); // מוסיף את ה-class אם לא קיים
                }
            }
        });
    }
});


</script>

<script>
(function () {
    var shells = document.querySelectorAll('.recommended-section .slider-content');
    shells.forEach(function (shell) {
        shell.classList.add('lobby-slider-shell--loading');
    });
})();

jQuery(function ($) {
    var $shells = $('.recommended-section .slider-content');

    $shells.each(function (index) {
        var $shell = $(this);
        var $slider = $shell.find('.owl-carousel').first();
        var delay = 80 + index * 80;

        function revealSlider() {
            if ($shell.hasClass('lobby-slider-shell--ready')) {
                return;
            }

            setTimeout(function () {
                $shell
                    .removeClass('lobby-slider-shell--loading')
                    .addClass('lobby-slider-shell--ready');
            }, delay);
        }

        if ($slider.length) {
            $slider.on('initialized.owl.carousel refreshed.owl.carousel', revealSlider);
        }

        setTimeout(revealSlider, 1000);
    });
});
</script>


<style>

  
  .banners_2 {
    
    display: flex;
    width: 100%;
    justify-content: space-between;
}


  .banners_2>div img {
   width: 100%!important; 
  }

  
  .banners_2>div {
  width: 48%!important;

}



  .banners_2_section {
       overflow-x: auto!important;
  }

@media (min-width: 768px){

  .hero-section {
    max-height: 17vw !important;
    height: 17vw!important;
    padding: 0px!important;
  }
.img_box h4, .recommended h4 {
    font-weight: 700;
    font-size: 22px!important;
  }
 .banners_2.mobile {
display: none!important;
  }

body:not(.white-intro) .hero-text h1 {
    text-align: center!important;
}


 .banners_2_section>div{
        margin-right: auto!important;
        padding-left: 15px;
        padding-right: 15px;
        max-width: calc(100% - 80px);
        margin-left: auto!important;
}
  }


.btn_download  {
    display: flex;
    justify-content: center;
    padding-top: 25px;
    padding-bottom: 25px;
    margin-top: -1px;
}

.btn_download a{
    font-size: 1.8rem;
    color: <?php echo esc_attr($btn_cta_color); ?>;
    font-weight: 400;
    padding: 1rem 3rem;
    background: transparent;
      border: 1px solid <?php echo esc_attr($btn_cta_color); ?>;
    border-radius: 3.3rem;
}


@media (max-width: 768px){
    body.no_menu_overlay_mobile header {
        background: transparent !important;

  }

    .no_menu_overlay_mobile .hero_page-section .ipo-breadcrumbs-div {
        display: none;
    }

    .hero_page-section .container {
        display: none;
    }
  
  .pt-50 {
    padding-top: 25px !important;
}

.btn_cta {
    width: 219px!important;
    margin-left: auto !important;
    margin-right: auto !important;
    margin-bottom: 10px!important;
}

.btn_download {
    display: block !important;
  }



  .banners_2>div {
    min-width: 60vw!important;
    margin-right: 25px;
  }

.recommended-section_costume .owl-item.cloned {
display: none!important;
  }
  
    .banners_2{
       overflow-x: auto;
  }

.recommended-section_costume .owl-nav {
  display: none!important;
  }

.banners_2>div {
    min-width: 60vw;
      width: 45% !important;
    margin-right: 25px;
}
 .upcoming_area .owl-item:first-child{
    margin-right: 0 !important;
  }

body.page-template-lobby-new-season-v2 .upcoming_area {
    overflow-x: visible !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area > .container {
    margin-right: 0 !important;
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}

body.page-template-lobby-new-season-v2 .upcoming_area .container_upcaming {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    width: 100%;
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
    box-sizing: border-box;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .container_upcaming::-webkit-scrollbar {
    display: none;
}

body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-slider,
body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-slider.owl-carousel,
body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-scroll-mobile .upcoming-slider {
    display: flex !important;
    flex-wrap: nowrap;
    overflow: visible !important;
    width: max-content;
    margin: 0 !important;
    transform: none !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-slider .loop-program.item,
body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-slider .owl-item,
body.page-template-lobby-new-season-v2:not(.home) .upcoming_area .owl-carousel.owl-drag .owl-item {
    min-width: 240px !important;
    max-width: 240px !important;
    width: 240px !important;
    margin-right: 15px;
    flex-shrink: 0;
    margin-left: 0 !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-slider .owl-stage-outer,
body.page-template-lobby-new-season-v2 .upcoming_area .upcoming-slider .owl-stage {
    display: flex !important;
    overflow: visible !important;
    transform: none !important;
    width: auto !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .owl-nav {
    display: none !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .img_box {
    height: auto !important;
    display: grid;
    width: 240px !important;
    max-width: 240px !important;
    margin-top: 0 !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .img_box .additionalDates {
    float: none !important;
    position: relative !important;
}

body.page-template-lobby-new-season-v2 .upcoming_area .title.style-1.pb-75,
.upcoming_area .title.style-1.pb-75 {
    padding-bottom: 25px !important;
}


 .banners_2.desktop {
    display: none!important;
}
  
    body:not(.page-template-template-home) .subscribe-content h2.lette-sapce-10 {
        font-size: 28px !important;
        color: #000000;
letter-spacing: 0!important;
        font-family: 'Simpler'!important;
  }

  .subscribe-content p, .subscribe-content a{
    font-size: 16px!important;
  }
  .key-plan  {
    padding-top: 25px!important;
  }

.subscribe-section {
    padding-top: 50px !important;
    margin-top: 0px !important;
  }
  
  .slider-banner {
    padding-right: 15px!important;
  }

.recommended-section_costume [lang="en-US"] .owl-stage-outer .owl-stage:first-child,
[lang="en-US"] .recommended-section_costume .owl-stage-outer .owl-stage:first-child  {
    padding-right: 20px;
}

.recommended-section_costume [lang="he-IL"] .owl-stage-outer .owl-stage:first-child,
[lang="he-IL"] .recommended-section_costume .owl-stage-outer .owl-stage:first-child  {
    padding-left: 20px;
}



.recommended-section_costume .owl-stage-outer *::-webkit-scrollbar {
    display: none;
}

.recommended-section_costume .owl-stage-outer *{
    scrollbar-width: none; 
    -ms-overflow-style: none; 
}

.recommended-section_costume .owl-stage-outer *::-ms-scrollbar {
    display: none;
}

.recommended-section_costume .owl-item>div * {
         overflow: unset!important;
  }
.recommended-section_costume .owl-carousel *:not(.owl-item ){
   
  
    transition: none!important;
    transform: none!important;
        overflow-x: auto;
}
  
  .recommended-section_costume .owl-item  {
    min-width: 220px;
	margin-left: 20px!important;
  }

    html .img_box li .arrow {
        width: 5px;
        margin-top: -2px!important;
    }

.recommended h4 {
    font-size: 20px!important;
  }

.recommended-section_costume .owl-carousel,
.recommended-section_costume .owl-stage-outer,
.recommended-section_costume .owl-stage{
   
  
    transition: none!important;
    transform: none!important;
        overflow-x: auto!important;
}

.recommended-section_costume .owl-carousel .owl-stage {
    transform: none !important;
  }
.recommended:not(.recommended_div_child) {
        padding-left: 0!important;
    }
[lang="en-US"] .upcoming_area>.container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: -25px!important;
  
    width: calc(100% + 25px) !important;
}

body.page-template-lobby-new-season-v2[lang="en-US"] .upcoming_area > .container,
html[lang="en-US"] body.page-template-lobby-new-season-v2 .upcoming_area > .container {
    margin-right: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}
  
  [lang="he-IL"]  .upcoming_area>.container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: -25px!important;
  
    width: calc(100% + 25px) !important;
}

body.page-template-lobby-new-season-v2[lang="he-IL"] .upcoming_area > .container,
html[lang="he-IL"] body.page-template-lobby-new-season-v2 .upcoming_area > .container {
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}
  
  }

.recommended  .rd-text, .recommended  .text, .recommended p{
    padding-right: 5px;
    padding-left: 5px;
}  
  
  
  .btn_cta {
    min-width: 219px;
    margin-left: 25px;
	margin-right: 25px;
    display: flex;
    align-items: center !important;
    justify-content: center;
  }
 @media (min-width: 600px) {
    .slider-banner .slick-slide img {
        padding-left: 0!important;
        padding-right: 0px!important;
   
    }
.recommended .thumb {
    margin-bottom: 0 !important;
}



   .recommended-slider .owl-nav {
    padding: 0px !important;
   }
   .recommended-section {
    padding-bottom: 50px!important;
   }

.slick-initialized .slick-slide:first-child {
    padding-left: 15px !important;
}

.slick-initialized .slick-slide:nth-child(2) {
    padding-right: 15px !important;
}

  }


.recommended-section  {
    background-color: #eefbf1!important;
}

.recommended .owl-stage img {
    width: 100%;
    height: auto !important;
    aspect-ratio: 1280 / 772 !important;
    min-height: 0px !important;
    object-fit: cover;
}

  
  [lang="en-US"]  .additionalDates img {
   transform: rotate(180deg)!important;
  }

.recommended-slider .owl-next img, .recommended-slider  .owl-prev img {
    transform: rotate(180deg);
  }

.lobby-slider-shell--loading,
.recommended-section .slider-content:not(.lobby-slider-shell--ready) {
    position: relative;
    min-height: 280px;
}

.lobby-slider-shell--loading .owl-carousel,
.recommended-section .slider-content:not(.lobby-slider-shell--ready) .owl-carousel {
    opacity: 0;
    visibility: hidden;
    transform: translateY(12px);
}

.lobby-slider-shell--loading::before,
.recommended-section .slider-content:not(.lobby-slider-shell--ready)::before {
    content: '';
    position: absolute;
    top: 24px;
    right: 15px;
    left: 15px;
    height: calc(100% - 48px);
    min-height: 200px;
    border-radius: 10px;
    background: linear-gradient(110deg, #f3f3f3 8%, #ececec 18%, #f3f3f3 33%);
    background-size: 200% 100%;
    animation: lobby-slider-shimmer 1.5s ease-in-out infinite;
    z-index: 1;
    pointer-events: none;
}

.lobby-slider-shell--ready .owl-carousel {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.6s ease, transform 0.6s ease;
    transform: translateY(0);
}

.lobby-slider-shell--ready::before {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.35s ease;
}

@keyframes lobby-slider-shimmer {
    0% {
        background-position: 100% 0;
    }
    100% {
        background-position: -100% 0;
    }
}




@media (max-width: 768px) {
.img_box .additionalDates {
    max-height: 50px;
  }

 .hero_page-section .container {
    display: none!important;
  }
  
.open-search img {
    filter: invert(1)!important;
}

.img_box .additionalDates .arrow {
    max-width: 5px;
  }
  
    .lobby-slider-shell--loading,
    .recommended-section .slider-content:not(.lobby-slider-shell--ready) {
        min-height: 320px;
    }
}


     </style>
<?php get_footer(); ?>