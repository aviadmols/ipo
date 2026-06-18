<?php 

if(!isset($post_id))
    $post_id = get_the_ID();

$link = get_permalink($post_id);
$featured_image_id = get_post_thumbnail_id($post_id);
$banner_image_id = get_field('banner_image', $post_id);
$title = get_the_title($post_id);
$program_banner_image  = get_field('program_banner_image', $post_id);;

// If featured image exists, use it. If not, use banner image.
if($featured_image_id){
    $banner_image = new wpstack_image($featured_image_id);
}elseif ($program_banner_image){
$banner_image = new wpstack_image($program_banner_image);
}else{
    $banner_image = new wpstack_image($banner_image_id);
}
// Details
$subtitle = get_field('subtitle', $post_id);

?>



<div class="loop-serie loop">
    <div class="loop-serie-image">
        <a href="<?php echo $link; ?>">
            <?php echo $banner_image->get_bg_img(); ?>
        </a>
    </div>
    <div class="loop-serie-content">
        <div class="loop-serie-title">
            <a href="<?php echo $link; ?>">
                <?php echo $title; ?>
            </a>
        </div>
        <div class="loop-serie-details">
            <?php 

            if($subtitle)
                echo '<div class="subtitle">'.$subtitle.'</div>';

            ?>
        </div>
    </div>
    <div class="read-more">
        <a href="<?php echo $link; ?>">
            <?php echo __('למידע נוסף','ipo'); ?>
        </a>
    </div>

</div>

<style>



</style>