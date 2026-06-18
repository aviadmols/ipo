<?php 
$artist_id = $post_id;

$post_type = get_post_type($artist_id);
$current_language = apply_filters('wpml_current_language', null);
$translated_artist_id = apply_filters('wpml_object_id', $artist_id, $post_type, false, $current_language);

if ($translated_artist_id) {
    $artist_id = $translated_artist_id;
}


if(ICL_LANGUAGE_CODE == 'en'){
  $MusicianChair = "Dedicate A Chair";

} else {
    $MusicianChair = "הקדישו כסא";
}


$artist_cats = get_the_terms($artist_id,'artist_role');
if (is_array($artist_cats)){
$artist_cats = array_map(function($cat){
    return $cat->name;
},$artist_cats);

$artist_cats = implode(', ',$artist_cats);
}
$banner = get_field('banner',$artist_id);
$image = get_field('image',$artist_id);
 

$artist_role_override = get_field('artist_role_override', $artist_id);
if($artist_role_override){
    $artist_cats = $artist_role_override;
}

$should_donor_show = true;
if(is_singular('program')){
    $should_donor_show = false;
}


// Get post featured image
$thumbnail_id = get_post_thumbnail_id($artist_id);
$thumbnail = $thumbnail_id;


if( $thumbnail ){
    $image = $thumbnail;
    // Get the image url
    $image = wp_get_attachment_image_url($image,'full');
}elseif( $image ){
    // get image from image id
    $image = wp_get_attachment_image_url($image,'full');
}else{
$url_img = 'https://www.ipo.co.il/wp-content/uploads/2023/01/LOGO_750X1000.jpg';
$image_id = attachment_url_to_postid($url_img);
    $image = wp_get_attachment_image_url($image_id,'full');
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
<a class="overlay_link" href="<?php echo $url; ?>"></a>
  
    <div class="position-relative img_box">
			<img src="<?php echo $image; ?>" class="horizontal-img" alt="<?php echo get_the_title($artist_id); ?>" data-image_id="<?php echo $thumbnail_id; ?>">	
    <div class="image-title">
<?php
$image = preg_replace('/-\d+x\d+(\.\w+)$/', '$1', $image);

$image_idd = attachment_url_to_postid($image);
if(!empty($image_idd)){

$image_title = get_the_title($image_idd);
} else {
$image = get_field('image',$artist_id);
$image_t = preg_replace('/-\d+x\d+(\.\w+)$/', '$1', $image);

  $image_title = get_the_title($image);
}
if ($image_idd != 31946){

 echo $image_title; 
}?></div>
	
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
        <?php } else {?>
  <a class="donor-cta"><?php echo $MusicianChair; ?></a>
     <?php } ?>
    </div>
</div>
<?php