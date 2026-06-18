<?php


get_header();

global $theme;

?>

<section class="hero_page-section">
<div class="banner_image"> 
    <img src="/wp-content/uploads/2022/11/Header_1600x375_0123.jpg" alt=""> 
</div>      
<div class="container">

</div>
<div class="gradient-top"></div>
</section>


<section class="area-404">
    <div class="container">
        <div class="content">
            <h1 class="header-title"><?php echo __('404', 'ipo');?></h1>
            <p><?php echo __('העמוד לא נמצא', 'ipo');?></p>
            <a href="/"><?php echo __('דף הבית', 'ipo');?></a>
        </div>
    </div>
</section>

<?php get_footer();?>
		
		