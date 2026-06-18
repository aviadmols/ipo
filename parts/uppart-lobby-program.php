<?php 


$part = new wpstack_part('lobby-program');

$part->data['e_class'][] = 'key-plan pt-100 pb-100';

$part->data['e_class_container'][] = '';



global $theme;
$part->build_opening_tag();

$program_title = $part->gf('program_title');
$program_subtitle = $part->gf('program_subtitle');
$program_text = $part->gf('program_text');
$program_link = $part->gf('program_link');
$program_image = $part->gf('program_image');


?>


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


<?php

$part->build_closing_tag(); 

?>