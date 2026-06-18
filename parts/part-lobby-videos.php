<?php 


$part = new wpstack_part('lobby-videos');

$part->data['e_class'][] = 'video-section pb-100 pt-100 t-001';

$part->data['e_class_container'][] = '';


$videos_title = $part->gf('videos_title');
$videos_subtitle = $part->gf('videos_subtitle');
$videos = $part->gf('videos');
$videos_link = $part->gf('videos_link');


global $theme;
$part->build_opening_tag();




?>

<div class="title style-1 pb-100">

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
echo '<img src="http://img.youtube.com/vi/'.$videoid.'/0.jpg" width="250"/>';

}

                    ?>

                    <?php if($video['title']) : ?>
                        <h3><?php echo $video['title'];?></h3>
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



<?php

$part->build_closing_tag(); 

?>