<?php 

/*

* Template Name: Fund Commemoration

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
.footer-section a {
    color: rgba(249, 195, 196, 1);
  }

  .footer-section p {
      font-weight: 700;
  }

      .key-plan .wpstack-image img {
    width: 100%!important;
      }
    
@media (max-width: 768px){

.pt-mobile-25 {
  padding-top: 25px!important;
  }

.no-padding-mobile {
  padding-right: 0px!important;
  padding-left: 0px!important;
  }

        }
     </style>

<section class="description">
            <div class="container max-1440">
                <div class="row">
                    <div class="col-md-12">
                        <div class="content">
                      <?php echo get_field('info'); // הדפסת התוכן משדה ACF בשם 'info_left' ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>


<section class="description pt-100 pb-100" style=" background: #f9f9f9;">
    <div class="container max-1440">
        <div class="row">
            <div class="col-md-6 no-padding-mobile">
                <?php if (get_field('info_left')) : ?>
                    <div class="content">
                       <?php echo get_field('info_left'); // הדפסת התוכן משדה ACF בשם 'info_left' ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6 pt-mobile-25 no-padding-mobile">
                <?php if (get_field('info_right')) : ?>
                    <div class="content">
                        <?php echo get_field('info_right'); // הדפסת התוכן משדה ACF בשם 'info_right' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="banner pb-100 pt-100" >
    <div class="container max-1440 no-padding-mobile">
        <div class="row">
            <div class="col-md-12 no-padding-mobile">
                <?php 
                $image_desktop = get_field('banner_image_desktop'); // שדה תמונה לדסקטופ
                $image_mobile = get_field('banner_image_mobile'); // שדה תמונה למובייל
                $banner_link = get_field('banner_link'); // שדה קישור לבאנר
                ?>
                <a href="<?php echo esc_url($banner_link); ?>">
                    <picture>
                        <source media="(min-width: 768px)" srcset="<?php echo esc_url($image_desktop['url']); ?>">
                        <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_mobile['url']); ?>">
                        <img src="<?php echo esc_url($image_desktop['url']); ?>" alt="<?php echo esc_attr($image_desktop['alt']); ?>" style="
    max-width: 100%;
">
                    </picture>
                </a>
            </div>
        </div>
    </div>
</section>

    <section class=" subscribe-section footer-section pt-100 pb-100" style="text-align:center;line-height: 2;">
<div class="container">
<p style="text-align:center">היכל התרבות ע&rdquo;ש צ&rsquo;רלס ברונפמן, אולם הקונצרטים ע&rdquo;ש לואי<br />
הוברמן 1, ת&rdquo;ד 23500, תל אביב 6123401<br />
טל&rsquo; <a href="tel:036296266">03-6296266</a> | פקס <a href="tel:036296267">03-6296267</a> |  ווטסאפ <a href="tel:0504826635">050-4826635</a> <br />
דוא&quot;ל<a href="mailto:info@ipofund.co.il"> info@ipofund.co.il</a></p>

       </div>
     </section>
               </div><!-- /.main-content -->

<?php get_footer(); ?>