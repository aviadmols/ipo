<?php
global $theme;

?>

<?php if( have_rows('slider_banner') ): ?>

    <!-- Slider for desktop -->
    <div class="slider-banner desktop pb-100" data-aos="fade-up" data-aos-duration="750" >
        <?php while( have_rows('slider_banner') ): the_row();
            $image = get_sub_field('img_pc');
            $link = get_sub_field('link');
        ?>
            <div>
                <a href="<?php echo esc_url($link); ?>">
                    <img src="<?php echo esc_url($image); ?>" alt="" data-aos="fade-up" data-aos-duration="750" loading="lazy">
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Slider for mobile -->
    <div class="slider-banner mobile pt-50 pb-50" data-aos="fade-up" data-aos-duration="750">
        <?php while( have_rows('slider_banner') ): the_row();
            $image = get_sub_field('img_mobile');
            $link = get_sub_field('link');
        ?>
            <div>
                <a href="<?php echo esc_url($link); ?>">
                    <img src="<?php echo esc_url($image); ?>" alt="" data-aos="fade-up" data-aos-duration="750" loading="lazy">
                </a>
            </div>
        <?php endwhile; ?>
    </div>

<?php endif; ?>



<style>
  


  
  .slider-banner button {
    display: none!important;
  }
 

 .slider-banner  .slick-slide img {
    width: 100%;
}

/* Hide the sliders by default */
 .slider-banner.desktop,  .slider-banner.mobile {
    display: none;
}
  

/* Show the appropriate slider based on screen size */
@media screen and (min-width: 600px) {
     .slider-banner.desktop {
        display: flex;
    }
  
   .slider-banner  .slick-slide img {
padding-right: 15px;
padding-left: 15px;
  }
}

@media screen and (max-width: 599px) {
     .slider-banner.mobile {
        display: block;
    }
}
  
</style>
<?php