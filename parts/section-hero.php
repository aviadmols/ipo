<?php 


if (is_page(268) || is_page(31130)) {
    echo do_shortcode('[ipo_slider]');
    ?>

<style>
      .ipo-hero-slider-shell--fade-mobile.ipo-hero-slider-shell--loading .ipo-hero-slider {
        opacity: 1!important;
  }
</style>
    <?php
} else {
  
$hero_video = get_field('hero_video_mp4');
$video_mp4_mobile  = get_field('video_mp4_mobile');
$hero_lottie = get_field('hero_lottie_code');

global $theme;
$hero_banner = $theme->get_asset('');
$section_css = '';
$section_class = '';
if($hero_banner){
    $section_css = 'background: #000;';
    $section_class = 'has-bg';
}

if($section_css){
    $section_css = 'style="'.$section_css.'"';
}

if($hero_video){
    // Get video src from file id
    $video_src = wp_get_attachment_url($hero_video);

    $video_src_mobile = $video_mp4_mobile ? wp_get_attachment_url($video_mp4_mobile) : $video_src;

    $hero_video = '




    <video id="main-video" playsinline data-play="false" data-id="1" class="bg-video main-video video" autoplay muted loop>
        <source id="main-video-source" src="'.$video_src.'" type="video/mp4">
    </video>
    '; 
}

$img_hero_pc = get_field('img_hero_pc');
$img_hero_mobile = get_field('img_hero_mobile');

// בדוק האם השדה 'img_hero_pc' קיים ואז הצג אותו למחשבים
if($img_hero_pc) {
    echo '<img src="' . $img_hero_pc  . '" alt="Hero PC" class="hero-image hero-image-pc" />';
}

if($img_hero_mobile) {
    echo '<img src="' . $img_hero_mobile . '" alt="Hero Mobile" class="hero-image hero-image-mobile" />';
}
if($img_hero_pc) {
echo '<style>
.hero_area {
    display: none!important;
}

@media (min-width: 768px) {
    .hero-image-pc {
width: 100%;
        display: block;
    }
    
  .hero-image-mobile {
        display: none!important;
    }
}

@media (max-width: 767px) {
    .hero-image-mobile {
width: 100%;
        display: block!important;
    }

   .hero-image-pc {
        display: none!important;
    }
}
</style>';
}
?>

        <section class="hero_area  section-view <?php echo $section_class; ?>"  data-aos="fade-in"  data-aos-duration="400"  <?php echo $section_css; ?> >

<div class="gradient-bottom" style="max-height: 30%; ">
                                    </div>
          <div class="gradient-top" style="max-height: 25%;display: none;">
                                    </div>
		<?php echo $hero_video; ?>
            <div class="container" data-aos="fade-in"  data-aos-duration="1000"  data-aos-delay="600" data-aos-anchor-placement="center-bottom">

            <?php echo $hero_lottie;

 ?>
            

<div class="hero-btn" >
<div class="container text-center">

    <ul class="social_links-hero">
        <?php 
        $social_icons = get_field('social_icons', 'option');
            if($social_icons) {
                foreach($social_icons as $item){
                    $icon =  $item['icon'];
                    $link =  $item['link'];

                    if($icon)
                        $icon = $theme->get_the_image($icon);

                        
                    echo '<li><a href="'.$link.'" target="_blank">'.$icon.'</a></li>';
                }
            }
            
        ?>
        
    </ul>
    <?php 
                
                $hero_button = get_field('hero_button');
                if($hero_button){
                    $target = $hero_button['target'] ? $hero_button['target'] : '_self';
                    $hero_button = '<a class="btn" href="'.$hero_button['url'].'" target="'.$target.'" >'.$hero_button['title'].'</a>';
                    echo $hero_button;
                }
                
                ?>
                <div class="play">
                    <a  href="javascript:void(0);" onclick="pauseVid()">

 <lottie-player id="play_lottie" src="/wp-content/uploads/2022/07/פליי-פאוז.json" style="        width: 19px;
    height: 20px;" class="off"></lottie-player>
</a>
                    <a href="javascript:void(0);" onclick="soundplay()"> <lottie-player id="speaker_lottie" src="/wp-content/uploads/2022/07/אייקון-רמקול-השתק.json" style="        width: 19px;
    height: 20px;" class="no"></lottie-player></a>
                </div>
</div>
</div>
            </div>
			<div class="gradient-top"></div>
			<div class="gradient-bottom"></div>
        </section>

   <?php 
}
?>