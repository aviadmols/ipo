<?php 

if(!isset($post_id))
    $post_id = get_the_ID();

$link = get_permalink($post_id);
$featured_image_id = get_post_thumbnail_id($post_id);
$program_banner_image  = get_field('program_banner_image', $post_id);

$banner_image_id = get_field('banner_image', $post_id);
$title = get_the_title($post_id);

// If featured image exists, use it. If not, use banner image.



if($featured_image_id){
    $banner_image = new wpstack_image($featured_image_id);
}elseif ($program_banner_image){
$banner_image = new wpstack_image($program_banner_image);
}else{
    $banner_image = new wpstack_image($banner_image_id);
}


$event = new ipo_event($post_id);


?>


<div class="recommended">
    <div class="thumb">

        <?php if($banner_image) : ?> 
            <?php echo $banner_image->get_img(); ?>
        <?php endif;?>

    </div><!-- /.thumb -->
    <div class="rd-text">
        <div class="text">
            <h4><a href="<?php echo $link; ?>">   <?php echo $title; ?> </a></h4>
        </div>
        <div class="link">
            <a href="<?php echo $link; ?>"><?php echo $event->get_city(); ?></a>
        </div>
    </div>
</div><!-- /.recommended -->