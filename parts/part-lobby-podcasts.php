<?php 


$part = new wpstack_part('lobby-podcasts');

$part->data['e_class'][] = 'characters-section pt-100 pb-100';

$part->data['e_class_container'][] = '';



global $theme;
$part->build_opening_tag();


$podcast_title = $part->gf('podcast_title');
$podcast_subtitle = $part->gf('podcast_subtitle');
$podcast_image = $part->gf('podcast_image');
$podcasts = $part->gf('podcasts');
$podcast_link = $part->gf('podcast_link');





?>


<div class="title style-1 pb-100">

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
                    <li>
                        <div class="text">

                            <?php if($podcast['title']) : ?>
                                <h3 class="Simpler"><?php echo $podcast['title'];?></h3>
                            <?php endif;?>

                            <?php if($podcast['subtitle']) : ?>
                                <p><?php echo $podcast['subtitle'];?></p>
                            <?php endif;?>

                        </div>
                        <div class="link">

                            <?php if($podcast['audio_file']) : ?>
                                <a href="<?php echo $podcast['audio_file']['url'];?>">להשמעה  <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
                            <?php endif;?>

                        </div>
                    </li>

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


<?php

$part->build_closing_tag(); 

?>