<?php 

/*

* Template Name: Fund Join US

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
        
   .btn.sersess {
    margin-top: 15px!important;
        }
.row.session h3 {
    margin-right: 0px!important;
}


@media  (max-width: 768px) {
.series-Contents {
    padding-top: 0px;
    margin-top: 25px;
}
        }
     </style>
<section class="series-Contents pt-100 pb-100" >
		
		    <div class="container max-1440">
<?php

// בדוק אם ישנם 'cards' והם לא ריקים
if( have_rows('cards') ):

    // פתח div של השורה
    

    // לולאה דרך כל ה-'cards'
    while( have_rows('cards') ): the_row();

        // שמור את כל המשתנים מהשדות המותאמים
        $title = get_sub_field('title');
        $info = get_sub_field('info');
        $subtitle = get_sub_field('subtitle');
        $img = get_sub_field('img');
        $btnurl = get_sub_field('btnurl');
        $btntext = get_sub_field('btntext');

        // הדפס את המשתנים בתבנית המתאימה
        ?>
      <div class="row session rtl event-36048 aos-init aos-animate" data-aos="fade-in" data-aos-duration="20" data-aos-delay="100">
        <div class="col-md-4 col-sm-4 col-xs-12 aos-init aos-animate" data-aos="fade-in" data-aos-duration="20" data-aos-delay="200">
            <h3><a href="<?php echo esc_url($btnurl); ?>" role="link"><?php echo esc_html($title); ?></a></h3>
       <p> <a href="<?php echo esc_url($btnurl); ?>" role="link"><strong><?php echo esc_html($subtitle); ?></strong></a></p>
        </div>

        <div class="col-md-4 col-sm-4 col-xs-12 aos-init aos-animate" data-aos="fade-in" data-aos-duration="500" data-aos-delay="300">
            <a href="<?php echo esc_url($btnurl); ?>" role="link">
                <div class="wpstack-bg-image  size-large" id="" data-post_id="40107">
                    <div class="bg_set" style="background-image: url(<?php echo esc_url($img); ?>);background-size:cover;"></div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4 col-sm-4 col-xs-12 aos-init aos-animate" data-aos="fade-in" data-aos-duration="500" data-aos-delay="400">
          <div class="sess_desc ev-30090">
    <?php echo $info; ?>
          </div>
            <a class="btn series_play mt-25 sersess " href="<?php echo esc_url($btnurl); ?>">
            <?php echo esc_html($btntext); ?></a>
        </div>
        
        <?php
                  echo '</div>';
    endwhile;

    // סגור div של השורה


endif;

?>
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