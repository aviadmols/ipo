<?php 
/*
* Template Name: History Page
*/

get_header(); 

$history = get_field('IPO_history');

$bannerImage = wp_get_attachment_image( get_post_thumbnail_id( get_the_ID() ), 'full' );
$default_banner = get_field('pages_placeholder_image', 'option');
$banner_image_mobile  = get_field('banner_image_mobile_main');

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero_page-section{
     min-height: 265px;
background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}
.hero_page-section {
    padding: 0px!important;
    }
    
.hero_page-section>.container {
    position: absolute;
    bottom: 10px;
}
.hero_page-section img{
display: none!important;
}
}
</style>'; 
}

?>

<section class="hero_page-section" >
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
<div class="gradient-bottom" > </div>
  <div class="gradient-top"> </div>
        <div class="banner_image"> 
            <?php 

                if($bannerImage) {
                    echo $bannerImage;
                }elseif($default_banner) {
                    $default_banner = new wpstack_image($default_banner);
                    echo $default_banner->get_img();
                }

            ?>
        </div>      

   
</section>


		
       
        <!-- =============== Hero area end =============== -->


        <!-- =============== Section area start =============== -->
        <section class="about_area mt-100 mb-100">
            <div class="container flex-pc max-1440">
   <div class="content width-25">
                    <h1 class="title-pages"><?php the_title();if ( get_field('secoundtitle')) { echo '<br>'; echo  get_field('secoundtitle');}?></h1>

                    <?php if($position):?>
                        <p><?php echo $position;?></p>
                    <?php endif;?>

                </div>
           	<div class="pt-20 pr-50 page-txt ">
							<?php  echo the_content();?>
						</div>					
                </div>
            </div>
        </section>
        <!-- =============== about area end =============== -->




	<?php get_footer(); ?>