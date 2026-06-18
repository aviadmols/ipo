<?php 

$artist_id = $post_id;

$artist_cats = get_the_terms($artist_id,'artist_role');
if (is_array($artist_cats)){
$artist_cats = array_map(function($cat){
    return $cat->name;
},$artist_cats);

$artist_cats = implode(', ',$artist_cats);
}
$banner = get_field('banner',$artist_id);
$image = get_field('image',$artist_id);

$artist_role_override = get_field('artist_role_override',$post_id);
if($artist_role_override){
    $artist_cats = $artist_role_override;
}

$should_donor_show = true;
if(is_singular('program')){
    $should_donor_show = false;
}


// Get post featured image
$thumbnail = get_post_thumbnail_id($artist_id);


if( $thumbnail ){
    $image = $thumbnail;
    // Get the image url
    $image = wp_get_attachment_image_url($image,'medium');
}elseif( $image ){
    // get image from image id
    $image = wp_get_attachment_image_url($image,'medium');
}else{
    $image = wp_get_attachment_image_url($banner,'medium');
}

if($image){
    $bg_image = 'style="background-image: url('.$image.')"';
    $bg_class = 'bg-set';
}else{
    $bg_image = '';
    $bg_class = '';
}

$musician_type = wp_get_post_terms($post_id, 'musician_type');
$musician_type_class = '';
if( $musician_type ){
    $musician_type_class = 'category-'.$musician_type[0]->term_id;
}

$donor = get_field('donor', $post_id); 
$donor_text = get_field('donor_text',$post_id);
if(!$donor_text){
    if( apply_filters( 'wpml_current_language', NULL ) == 'en') {
        $donor_text = 'Donor Recognition Line';   
    } else {
        $donor_text = 'קו הכרה בתורמים';   
    }
}

if(!isset($url))
    $url = get_permalink($artist_id);

$artist_program_only_title= '';
// if current post is program
if(is_singular('program')){
    $artist_program_only_title = get_field('position_opera',$artist_id);
}

?>


<div class="artist_box loop-artist <?php echo $musician_type_class;?>  " data-post_id="<?php echo $artist_id; ?>">
    <div class="img_box" style="background-image: url(<?php echo $image; ?>)";>
        <a class="overlay_link" href="<?php echo $url; ?>"></a>
    </div>
    <div class="content">
        <p><strong><?php echo get_the_title($artist_id); ?></strong></p>

        <?php if ($artist_cats){ ?>
            <p class="title"><?php echo $artist_cats; ?></p>
        <?php } ?>
        
        <?php if ($artist_program_only_title){ ?>
            <p class="title artist_program_only_title"><?php echo $artist_program_only_title; ?></p>
        <?php } ?>

        <?php if ($donor && $should_donor_show){ ?>
            <p class="donor-text"><?php echo $donor_text; ?></p>
        <?php } ?>


    </div>
</div>
<?php