<?php 
/*
* Template Name: Lobby Children Streaming
*/

get_header();

global $theme;

$series_title_22 = get_field('series_title_22');
$program_subtitle_22  = get_field('program_subtitle_22');
$youtube = get_field('youtube');

$series_title = get_field('series_title');
$series = get_field('series');

$videos_title = get_field('videos_title');
$videos_subtitle = get_field('videos_subtitle');
$videos = get_field('videos');
$videos_link = get_field('videos_link');

$podcast_title = get_field('podcast_title');
$podcast_subtitle = get_field('podcast_subtitle');
$podcast_image = get_field('podcast_image');
$podcasts = get_field('podcasts');
$podcast_link = get_field('podcast_link');

?>

<div class="main-content">
  <?php echo do_shortcode('[display_svg]'); ?>

  <?php if($series_title_22) : ?>
  <section class="key-plan pt-100 pb-100 video_div" id="video" style="background: #fff8de; display: block!important;">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <div class="text">
            <div class="title pb-25 pt-50">
              <?php if($series_title_22) : ?>
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
          </div>
        </div>
        <div class="col-lg-6">
          <div class="thumb" data-aos="fade-in" data-aos-duration="300" data-aos-delay="300">
            <iframe width="750" height="420" src="<?php echo $youtube; ?>" title="YouTube video player" frameborder="0" style="max-width: 100%;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif;?>

<section class="recommended-section pt-100 pb-100" id="recommended">
  <div class="container">
    <?php if ($series_title): ?>
    <div class="title pb-50">
      <h1 data-aos="fade-in" data-aos-duration="100" data-aos-delay="100"><?php echo $series_title; ?></h1>
    </div>
    <?php endif; ?>
    <div class="pt-25 pb-25">
      <div class="recommended-child" data-aos="fade-in" data-aos-duration="250" data-aos-delay="250">
        <?php foreach ($series as $serie) : ?> 
        <div class="recommended recommended_div_child">
          <div class="thumb">
            <a href="<?php echo $serie['link']; ?>" class="popup-youtube">
              <?php
              $image = $serie['image'] ?? '';
              if (!$image && strpos($serie['link'], 'youtube.com') !== false && strpos($serie['link'], 'v=') !== false) {
                parse_str(parse_url($serie['link'], PHP_URL_QUERY), $query);
                $videoid = $query['v'] ?? '';
                if ($videoid) {
                  $image = 'https://img.youtube.com/vi/' . $videoid . '/maxresdefault.jpg';
                }
              }
              ?>
              <?php if ($image): ?>
              <img src="<?php echo esc_url($image); ?>" alt="Img" class="img-fluid">
              <?php endif; ?>
              <div class="play-overlay">
                <img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Play" class="play-icon">
              </div>
            </a>
          </div>
          <div class="">
            <div class="text">
              <h4><?php echo $serie['title']; ?></h4>
            </div>
            <div>
              <p><?php echo $serie['subtitle']; ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<?php if ($videos) : ?>
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
if (!empty($video['image']) && is_object($video['image'])) {
                                            $video_image = $video['image'];
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

<?php endif; ?>


  <?php if($podcast_title) : ?>
  <section class="characters-section pt-100 pb-100" style="background: rgba(249, 195, 196, 1);">
    <div class="container">
      <div class="title pb-75">
        <?php if($podcast_title) : ?>
        <h2 class="lette-sapce-10"><?php echo $podcast_title;?></h2>
        <?php endif;?>
        <?php if($podcast_subtitle) : ?>
        <h3 class="sub-title-simpler"><?php echo $podcast_subtitle;?></h3>
        <?php endif;?>
      </div>
      <div class="row prodcastpage">
        <div class="col-lg-6">
          <div class="cs-text" style="padding-right: 0;">
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
                      <a href="<?php echo $podcast['audio_file'];?>">להשמעה <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
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
              if($podcast_link): 
                $link_url = $podcast_link['url'];
                $link_title = $podcast_link['title'];
                $link_target = $podcast_link['target'] ? $podcast_link['target'] : '_self';
              ?>
              <a class="" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><?php echo esc_html($link_title); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="thumb">
            <?php  
            $podcast_image = new wpstack_image($podcast_image);
            echo $podcast_image->get_img();
            ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif;?>
</div>

<style>
.recommended-child {
  display: grid;

  grid-template-columns: repeat(2, 1fr);
  gap: 75px 50px;
}

@media (min-width: 1450px) {
.recommended-child {
    display: grid;
  gap: 100px 150px!important;
  }
  }

@media (max-width: 768px) {
  .recommended-child {
    display: flex;
    flex-direction: column;
  }
  
  .recommended-child {
    gap: 20px!important;
}
  .recommended {
    padding: 0px !important;
    padding-bottom: 40px;
    margin-bottom: 35px;
  }
}

.recommended .thumb {
  position: relative;
  margin-bottom: 0px !important;
}

.play-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 1!important;
  transition: opacity 0.3s ease;
}

.recommended .thumb:hover .play-overlay {
  opacity: 1;
}

.play-icon {
  width: 90px;
  height: 90px;
}

.whatsapp-banner img {
  max-width: 100%!important;
}
</style>

<?php get_footer(); ?>