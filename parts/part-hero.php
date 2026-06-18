<?php 

/* 

@part-data : slug : hero

@part-data : label : Hero

@part-data : fieldgroup_id : 


*/




$part = new wpstack_part('hero');

$part->data['e_class'][] = 'hero-section';

$part->data['e_class_container'][] = '';



global $theme;
$part->build_opening_tag();

$subtitle = $part->gf('subtitle');



?>



<div class="hero-text">
    <h1 class="header-title pb-25"><?php echo get_the_title(); ?></h1>
    
    <?php if($subtitle) : ?>
    <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
    <?php endif;?>
    
</div><!-- /.hero-text -->

<?php

$part->build_closing_tag(); 

?>