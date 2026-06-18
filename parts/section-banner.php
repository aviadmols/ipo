<?php

$banner_desktop = get_field('banner_image');
$banner_mobile = get_field('banner_image_mobile');
$Linknbanner = get_field('Linknbanner');
if( $banner_desktop || $banner_mobile ):

?>

<div class="womenOfJazz_banner section-view pt-50">
	
<?php 
echo '<a href="'. $Linknbanner .'">';
if( $banner_desktop ){
    $banner_desktop = new wpstack_image($banner_desktop);
    echo'<img src="'.$banner_desktop->get_src().'" class="w-100 hide-mobile " alt="'.$banner_desktop->get_alt().'"     data-aos="fade-in" data-aos-offset="100" data-aos-duration="500" loading="lazy">';
}

if( $banner_mobile ){
    $banner_mobile = new wpstack_image($banner_mobile);
    echo'<img src="'.$banner_mobile->get_src().'" class="w-100 hide-pc" alt="'.$banner_mobile->get_alt().'" data-aos="fade-in" data-aos-offset="100" data-aos-duration="500" loading="lazy" >';
}
echo '</a>';
?>
</div>


<?php endif; ?>