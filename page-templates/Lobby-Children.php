<?php 

/*

* Template Name: Lobby Children

*/


get_header();

global $theme;

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$video_categories = get_field('video_categories');
$series_title = get_field('series_title');
$subtitle = get_field('subtitle');
$overlay = get_field('overlay');

$series = get_field('series');


$subscribe_title = get_field('subscribe_title');
$subscribe_text = get_field('subscribe_text');
$subscribe_link = get_field('subscribe_link');
$youtube = get_field('youtube');

$program_title = get_field('program_title');
$program_subtitle = get_field('program_subtitle');
$program_text = get_field('program_text');
$program_link = get_field('program_link');
$program_image = get_field('program_image');


$series_title_22 = get_field('series_title_22');
$program_subtitle_22  = get_field('program_subtitle_22');



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


$videos_title = get_field('videos_title');
$videos_subtitle = get_field('videos_subtitle');
$videos = get_field('videos');
$videos_link = get_field('videos_link');
$class = '';
$white = get_field('white');
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

<style>
  @media (max-width: 768px){
  .open-search img{
        filter: invert(0%) brightness(0%);
  }
  }
</style>


   <div class="main-content">


  <?php echo do_shortcode('[display_svg]'); ?>


  <?php if($series_title_22) : ?>
  
 <section class="key-plan pt-100 pb-100 video_div" id="video" style="background: #fff8de; display: block!important;">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="text">
                                <div class="title pb-25 pt-50" >

                                    <?php if($program_title) : ?>
                                     <h2 class="lette-sapce-10 title_video" data-aos-duration="100" data-aos-delay="100"><?php echo $series_title_22; ?></h2>
                                    <?php endif;?>

                      
<style>
@media (min-width: 768px){
h2.lette-sapce-10.title_video {
    font-size: 75px!important;
    color: #000000;
    text-align: right;
    letter-spacing: 8.28px!important;
  }
  }

  @media (max-width: 768px){
.video_div iframe{
height: 210px !important;
  }
  }

    
                                  </style>
                                </div>   
                              <div data-aos-duration="200" data-aos-delay="200">
                                <?php echo $program_subtitle_22; ?>
                              </div>
                       
                                            
                            </div><!-- /.text -->
                        </div>
                        <div class="col-lg-6">
                            <div class="thumb"  data-aos="fade-in" data-aos-duration="300" data-aos-delay="300">

                       <iframe width="750" height="420"  src="<?php  echo $youtube; ?>" title="YouTube video player" frameborder="0"  style="max-width: 100%;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.key-plan -->
   <?php endif;?>

 <!-- =============== Recommended series =============== -->
     
     <style>
     .recommended-slider-child {
         display: flex;
		 justify-content: space-between;
       }
            .recommended-slider-child .owl-stage{
           display: flex;
       }
       
       
          .recommended-slider-child button{
                border: none!important;
    background: transparent;
       }
       .recommended {
             padding-left: 40px;
       }
       

@media (max-width: 768px){
  .recommended-section h1 {
   display: none!important; 
  }
  
       }
     </style>
          <section class="recommended-section pt-100 pb-100" style="" id="recommended" >
                <div class="container">
                  <?php if ($series_title): ?>
                    <div class="title pb-50">
                        <h1 data-aos="fade-in" data-aos-duration="100" data-aos-delay="100"><?php echo $series_title;?></h1>
                    </div>
                      <?php endif; ?>
                    <div class=" pt-25 pb-25">
                        <div class=" recommended-child" data-aos="fade-in" data-aos-duration="250" data-aos-delay="250">
                             <?php foreach($series as $serie) : ?> 
                     <div class="recommended recommended_div_child">
                                <div class="thumb">
									   <a href="<?php echo  $serie['link'];?>">
										<img src="<?php echo  $serie['image'];?>" alt="Img" class="img-fluid">
                                         </a>
                                </div><!-- /.thumb -->
                                <div class="rd-text">
                                    <div class="text">
                                        <h4><a href="<?php echo  $serie['link'];?>"><?php echo  $serie['title'];?></a></h4>
                                  </div>
                                    <div class="link">
                                        <a href="<?php echo  $serie['link'];?>"><?php echo  $serie['location'];?> </a>
                                    </div>
                                
                                </div>
                          	<div>
									<p>
										<a href="<?php echo  $serie['link'];?>"><?php echo  $serie['subtitle'];?></a>
                                      </p>
                                  </div>
  </div>
 <?php endforeach;?>
							
					
                    </div><!-- /.recommended-content -->

                       </div><!-- /.container -->
            </section>


<?php
    $whatsapp_image = get_field('whatsapp_image');
    $whatsapp_image_mobile = get_field('whatsapp_image_mobile');
    $whatsapp_link = get_field('WhatsApp_link');

    // בדיקה אם יש תמונה לשדה ה-ACF של המובייל
    if ($whatsapp_image_mobile) {
        ?>
        <style>
            .whatsapp-banner {
                display: none;
            }
            @media (max-width: 768px) {
                .whatsapp-banner {
                    display: block;
                }
                .whatsapp-banner-desktop {
                    display: none;
                }
            }
        </style>
        <div class="whatsapp-banner">
            <a href="<?php echo esc_url($whatsapp_link); ?>" target="_blank">
                <img src="<?php echo esc_url($whatsapp_image_mobile); ?>" alt="WhatsApp Mobile Banner" style=" max-width: 100%;" >
            </a>
        </div>
        <div class="whatsapp-banner-desktop">
            <a href="<?php echo esc_url($whatsapp_link); ?>">
                <img src="<?php echo esc_url($whatsapp_image); ?>" alt="WhatsApp Desktop Banner" style=" max-width: 100%;">
            </a>
        </div>
        <?php
    }
   ?>

   


   <!-- =============== upcoming area start =============== -->
            <?php $theme->the_part('section-upcoming'); ?>
        <!-- =============== upcoming area end =============== -->
		

            <section class="video-section pb-100 pt-100" style="background: #f9f9f9;">
                <div class="container">
                    <div class="title style-1 pb-75">

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
if ($video['image']){
                                            $video_image = new wpstack_image($video['image']);
                                             echo $video_image->get_img();
} else {

 $fetch=explode("v=", $video['video_url']);
 $videoid = $fetch[1];
 echo '<img src="http://img.youtube.com/vi/'.$videoid.'/maxresdefault.jpg" width="250"/>';

}

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


 



   <?php if($podcast_title) : ?>
			       <section class="characters-section pt-100 pb-100" style="background: rgba(249, 195, 196, 1);">
                <div class="container">
                    <div class="title style-1 pb-75">

                        <?php if($podcast_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $podcast_title;?></h2>
                        <?php endif;?>

                        <?php if($podcast_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $podcast_subtitle;?></h3>
                        <?php endif;?>

                    </div>

                    <div class="row prodcastpage">

    <div class="col-lg-6">
                            <div class="cs-text " style="  padding-right: 0;">
                                <ul>
                                    <?php foreach($podcasts as $podcast) : ?> 
                                           <?php if(isset($podcast['audio_file'])) : ?>
                                    <div class="link">
                                                    <a href="<?php echo $podcast['audio_file'];?>">
                                                <?php endif;?>
                                        <li>
                                            <div class="text">

                                                <?php if($podcast['title']) : ?>
                                                 <h3 class="Simpler"><?php echo $podcast['title'];?></h3>
                                                <?php endif;?>

                                                <?php if($podcast['subtitle']) : ?>
                                                 <p style="font-weight: 300!important;"><?php echo $podcast['subtitle'];?></p>
                                                <?php endif;?>

                                            </div>
                                            <div class="link">

                                                <?php if(isset($podcast['audio_file'])) : ?>
                                                    <a href="<?php echo $podcast['audio_file'];?>">להשמעה  <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
                                                <?php endif;?>

                                            </div>
                                        </li>
    <?php if(isset($podcast['audio_file'])) : ?>
                                                 
                                                    </a>
                                       </div>
                                                <?php endif;?>
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
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $podcast_image = new wpstack_image($podcast_image);

                                    echo $podcast_image->get_img();

                                ?>
                            </div>
                        </div>
                    
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.characters-section -->
			  
             <?php endif;?>
            
   
      <section class="key-plan pt-100 pb-100" style="background: #fff8de;">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <div class="text">
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


          
       
        </div><!-- /.main-content -->
     
     <style>
       .recommended-child {
             display: flex;
    flex-flow: wrap;
       }
       

 @media (min-width: 768px){
.recommended {
    max-width: calc(33% - 20px);
   }

.recommended {
    padding: 0!important;
}

.recommended .thumb {
    margin-bottom: 0px !important;
}

.recommended-child {
    gap: 40px 20px;
}
       }

       @media (max-width: 600px){
.recommended img {
    height: auto!important;
}
         
.recommended {
    padding: 0px !important;
    padding-bottom: 40px;
}
         .recommended {
    margin-bottom: 35px;
               padding-left: 0px!important;
             padding-right: 0px!important;
         }

.owl-carousel .owl-stage-outer {
    overflow: visible!important;
         }
         }
       }
       
       .whatsapp-banner img {
         max-width: 100%!important;
       }
       
     </style>

<?php get_footer(); ?>