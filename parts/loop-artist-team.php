<?php 

if(!isset($post_id))
    $post_id = get_the_ID();

$image = get_field('image', $post_id);
if(!$image){
    // Get featured image ID
    $image = get_post_thumbnail_id($post_id);
    // If no featured image, get placeholder image 'artist_default_avatar'
    if(!$image){
        $image = get_field('artist_default_avatar', 'option');
    }
}


//$wpstack_img = new wpstack_image($image,['size' => 'medium']);
$wpstack_img = new wpstack_image($image,['size' => 'loop-artist-team-large']);
$musician_type_terms = get_the_terms( $post_id, 'musician_type' );
$name = get_the_title($post_id);
$title = $musician_type_terms[0]->name;
$musician_type_terms_ids = wp_list_pluck( $musician_type_terms, 'term_id' );
$musician_type_terms_ids = implode(',', $musician_type_terms_ids);

?>


<li class="orckestra loop-artist-team" data-aos="fade-in"  data-aos-duration="1000" data-aos-delay="200" data-terms="<?php echo $musician_type_terms_ids;?>">
    <div class="media">
        <a class="orch_team" href="<?php echo get_permalink($post_id)?>">
 <?php

        $wpstack_img->the_bg_img(); ?>
        </a>
    </div>
    <div class="details">
        <h4><a class="orch_team" href="<?php echo get_permalink($post_id) ?>"><?php echo $name;?></a></h4>
        <p><?php  echo $title; ?></p>
    </div>
</li>



<?php 

/*


if ( !empty($image)) { ?>
    <a href="<?php echo get_permalink($pages[$p]->ID)?>"> 
    <?php	 
    $wpstack_img->the_bg_img(['size' => 'large']);
    ?>
    </a>
    <?php 
}else{

    //$iamge_id = 26624;
    //$wpstack_img = new wpstack_image($iamge_id);
    

?>
<a href="<?php echo get_permalink($pages[$p]->ID)?>" class="no-img"> 

    
<?php
    //$wpstack_img->the_bg_img(['size' => 'large']);
}
?></a> 
<?php $name = $pages[$p]->post_title;?>
    <h3><a class="orch_team" href="<?php echo get_permalink($pages[$p]->ID) ?>"><?php echo $name;?></a></h3>
    
    <?php 


    $roles =get_the_terms( $pages[$p]->ID, 'musician_type' );
    //$roles =get_the_terms( $pages[$p]->ID, 'artist_role' );

    if($roles){
        echo '<span class="role">'.$roles[0]->name.'</span>';
    }


        $position_2 = get_field('position_2', $pages[$p]->ID);
        if (!empty($position_2))
            echo '<span class="role position-2">'.$position_2.'</span>';
    ?>
*/

?>