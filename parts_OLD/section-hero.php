<?php 

$hero_video = get_field('hero_video_mp4');
$hero_lottie = get_field('hero_lottie_code');

global $theme;
$hero_banner = $theme->get_asset('ipohome.jpg');
$section_css = '';
$section_class = '';
if($hero_banner){
    $section_css = 'background-image:url('.$hero_banner.');';
    $section_class = 'has-bg';
}

if($section_css){
    $section_css = 'style="'.$section_css.'"';
}


if($hero_video){
    // Get video src from file id
    $video_src = wp_get_attachment_url($hero_video);
    

    $hero_video = '
    <video id="main-video" data-play="false" data-id="1" class="bg-video main-video video" autoplay="" playsinline="" muted="" loop="">
    <source src="'.$video_src.'" type="video/mp4">
    </video>'; 

}
?>

        <section class="hero_area  section-view <?php echo $section_class; ?>"  data-aos="fade-in"  data-aos-duration="400"  <?php echo $section_css; ?> >
		<?php echo $hero_video; ?>
            <div class="container" data-aos="fade-in"  data-aos-duration="1000"  data-aos-delay="600" data-aos-anchor-placement="center-bottom">

            <?php echo $hero_lottie; ?>
            

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
    height: 20px;" class="no"></lottie-player>
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