<?php 

/*

* Template Name: Fund Musical Education

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

.sub-title-simpler {
padding-right: 0px!important;
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

        .sub-title-simpler {
padding-right: 0px!important;
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
.key-plan h3 {
    font-family: 'Simpler';
    font-size: 20px!important;
  }
        }

.key-plan h3 {
    font-family: 'Simpler';
    font-size: 30px;
letter-spacing: 0;
    font-weight: bold;
  line-height: 1.2;
        }
     </style>

<?php
// וודא כי קיימים 'blocks'
if( have_rows('blocks') ):
    // אינדקס עבור בדיקת זוגיות האיטרציה
    $index = 0;
    while ( have_rows('blocks') ) : the_row();
        // החלפת ערך ה-row-reverse בכל איטרציה
        $row_class = ($index % 2 == 0) ? '' : 'row-reverse';
  $style_class = ($index % 2 == 0) ? '' : 'style="background: #f7f7f7;"'; 
        // שליפת ערכי שדות מתת-השדות של ה-repeater
        $title = get_sub_field('title');
        $content = get_sub_field('content');
        $image = get_sub_field('image');
        $link = get_sub_field('link');
        ?>
        <section class="key-plan pt-100 pb-100" <?php echo $style_class; ?> >
            <div class="container">
                <div class="row align-items-center <?php echo $row_class; ?>">
                    <div class="col-lg-6">
                        <div class="text">
                            <div class="title">
                                <h3 class=""><?php echo esc_html($title); ?></h3>
                            </div>   
                            <p><?php echo $content; ?></p>
                            <div class="view-more">
                                <a class="" href="<?php echo esc_url($link); ?>" target="_self">
                                    קרא עוד <img src="<?php echo ipo_arrow_icon_url(); ?>" alt="">
                                </a>
                            </div>
                        </div><!-- /.text -->
                    </div>
                    <div class="col-lg-6">
                        <div class="thumb">
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" title="<?php echo esc_attr($image['title']); ?>" style="width: 100%;">
                        </div>
                    </div>
                </div><!-- /.row -->
            </div><!-- /.container -->
        </section>
        <?php
        $index++; // עדכון האינדקס בכל איטרציה
    endwhile;
endif;
?>

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