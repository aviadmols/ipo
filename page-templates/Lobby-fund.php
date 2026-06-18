<?php 

/*

* Template Name: Lobby fund

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
     <section class="pt-100 pb-100" style="background: #f7f7f7;">
<div class="container">

<?php if ( have_rows( 'buttons_cta' ) ): ?>
    <div class=" cta-cards-container  orchestra-loop-list">
        <?php 
        // לולאה דרך כל רשומת תת-שדה בשדה החזרה 'buttons_cta'
        while ( have_rows( 'buttons_cta' ) ): the_row(); 
            // נטען כל אחד מהתת-שדות
            $image = get_sub_field( 'img' ); // מערך תמונה
            $title = get_sub_field( 'title' ); // טקסט
            $link = get_sub_field( 'Links' ); // URL
        ?>
            <div class="orckestra cta_card aos-init aos-animate" data-aos="fade-in" data-aos-duration="1000" data-aos-delay="200">
                <div class="media">
                    <?php if ( $link && $image ): ?>
                        <a class="orch_team" href="<?php echo esc_url( $link ); ?>">
                            <div class="wpstack-bg-image  size-loop-artist-team-large" data-post_id="30382">
                                <div class="bg_set" style="background-image: url('<?php echo esc_url( $image['url'] ); ?>'); background-size:cover;" title="<?php echo esc_attr( $image['alt'] ); ?>"></div>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="details">
                    <h4>
                        <?php if ( $link ): ?>
                            <a class="orch_team" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
                        <?php endif; ?>
                    </h4>
                    <!-- תוכן נוסף יכול להיות כאן, כמו פרטי הנגן ראשי -->
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>


<style>
/* עיצוב כרטיסיות ה-CTA */
.cta-cards-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 עמודות בדסקטופ */
    grid-gap: 50px; /* רווח בין הכרטיסיות */
}

  .cta_card {
        overflow: hidden;
    position: relative;
  }

.cta-card {
    background-size: cover;
    background-position: center;
}

  .details {
    position: absolute;
    bottom: 0px;
    right: 0px;
}
    
 .details * {
    color: #fff;
}
 .details {
    padding: 10px;
    background-image: linear-gradient(-180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,01) 100%)!important;
    position: absolute;
    bottom: 0px;
    width: 100%;
}


.cta-card img {
    max-width: 100%;
    height: auto;
    display: block;
}

.cta-card h3 {
    text-align: center;
    /* עיצוב נוסף לכותרת אם צריך */
}
@media (min-width: 768px) {
.media {
    height: 350px!important;
  }
  }
/* עיצוב עבור מכשירים ניידים */
@media (max-width: 768px) {
    .cta-cards-container {
        grid-template-columns: repeat(2, 1fr); 
    grid-gap: 20px; 
    }
.details * {
    font-size: 18px!important;
  }

  .media {
    height: 200px!important;
  }
}

.footer-section a {
    color: rgba(249, 195, 196, 1);
  }

  .footer-section p {
      font-weight: 700;
  }

</style>
       
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