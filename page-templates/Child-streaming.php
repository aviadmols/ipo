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

// Optional sections (includes/ipo-streaming-extra-fields.php). Empty on pages that don't use them.
$hero_image       = get_field('streaming_hero_image');
$members_title    = get_field('streaming_members_title');
$members          = get_field('streaming_members');
$more_text        = get_field('streaming_more_text');
$more_label       = get_field('streaming_more_label');
$hide_below_clips = get_field('streaming_hide_below_clips');

if (!$more_label) {
  $more_label = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en') ? 'Read more' : 'לקריאה נוספת';
}

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
            <?php if ($hero_image) : ?>
            <?php echo wp_get_attachment_image($hero_image, 'full', false, array('class' => 'img-fluid streaming-hero-image', 'alt' => esc_attr(wp_strip_all_tags($series_title_22)))); ?>
            <?php else : ?>
            <iframe width="750" height="420" src="<?php echo $youtube; ?>" title="YouTube video player" frameborder="0" style="max-width: 100%;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif;?>

  <?php if ($members) : ?>
  <section class="streaming-members-section pt-100">
    <div class="container">
      <?php if ($members_title) : ?>
      <div class="title pb-50">
        <h2 class="lette-sapce-10" data-aos="fade-in" data-aos-duration="100" data-aos-delay="100"><?php echo esc_html($members_title); ?></h2>
      </div>
      <?php endif; ?>
      <div class="streaming-members" data-aos="fade-in" data-aos-duration="250" data-aos-delay="250">
        <?php foreach ($members as $member) : ?>
        <div class="streaming-member">
          <div class="streaming-member-image">
            <?php if (!empty($member['image'])) : ?>
            <?php echo wp_get_attachment_image($member['image'], 'large', false, array('alt' => esc_attr($member['name'] ?? ''))); ?>
            <?php endif; ?>
          </div>
          <div class="content">
            <?php if (!empty($member['name'])) : ?>
            <p><strong><?php echo esc_html($member['name']); ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($member['title'])) : ?>
            <p class="title"><?php echo esc_html($member['title']); ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($more_text) : ?>
  <section class="streaming-more-section pt-50">
    <div class="container">
      <button type="button" class="streaming-more-toggle" aria-expanded="false" aria-controls="streaming-more-text">
        <span><?php echo esc_html($more_label); ?></span>
        <svg class="streaming-more-arrow" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="streaming-more-text" id="streaming-more-text" hidden>
        <?php echo $more_text; ?>
      </div>
    </div>
  </section>
  <script>
  (function () {
    var button = document.querySelector('.streaming-more-toggle');
    var text = document.getElementById('streaming-more-text');
    if (!button || !text) return;
    button.addEventListener('click', function () {
      var open = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', open ? 'false' : 'true');
      text.hidden = open;
    });
  })();
  </script>
  <?php endif; ?>

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


<?php if ($videos && !$hide_below_clips) : ?>
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


  <?php if($podcast_title && !$hide_below_clips) : ?>
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

.streaming-hero-image {
  display: block;
  width: 100%;
  height: auto;
}

/* Centred, so a row with fewer than 6 photos sits in the middle. */
.streaming-members-section .title,
.streaming-member .content,
.streaming-more-section .container {
  text-align: center;
}

.streaming-members {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px 20px;
}

.streaming-member {
  flex: 0 0 calc((100% - 5 * 20px) / 6);
}

@media (max-width: 1199px) {
  .streaming-member {
    flex-basis: calc((100% - 2 * 20px) / 3);
  }
}

@media (max-width: 575px) {
  .streaming-members {
    gap: 25px 15px;
  }

  .streaming-member {
    flex-basis: calc((100% - 15px) / 2);
  }
}

.streaming-member-image {
  aspect-ratio: 3 / 4;
  background: #eee;
  overflow: hidden;
  margin-bottom: 12px;
}

.streaming-member-image img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.streaming-member .content p {
  margin-bottom: 0;
  line-height: 1.3;
}

.streaming-member .content .title {
  margin-top: 4px;
}

.streaming-more-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 0;
  border: 0;
  background: none;
  color: inherit;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.streaming-more-arrow {
  transition: transform 0.3s ease;
}

.streaming-more-toggle[aria-expanded="true"] .streaming-more-arrow {
  transform: rotate(180deg);
}

.streaming-more-text {
  padding-top: 25px;
  max-width: 900px;
  margin: 0 auto;
}
</style>

<?php get_footer(); ?>