<?php 
/*
* Template Name: Simple page
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
 @media (min-width: 768px){
.max-1200 {
max-width: 1200px!important;
margin-left: auot;
margin-right: auto;
padding-right: 25px;
padding-left: 25px;
}
}

</style>'; 
}

?>

<style>
 @media (min-width: 768px){
.max-1200 {
max-width: 1200px!important;
margin-left: auot;
margin-right: auto;
padding-right: 25px;
padding-left: 25px;
}
.moreConcerts {
    padding-top: 0px!important;
}

}

 @media (max-width: 768px){
.hero_page-section {
    min-height: 25vh!important;
  }

.page-txt h4 {
    font-size: 20px!important;
    display: block;
    line-height: 1.4!important;
}


   .moreConcerts {
    padding-bottom: 100px!important; 
   }
  }


iframe{
    max-width: 100%;
}
</style>
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
            <div class="container max-1200">
   <div class="content">
                    <h1 class="title-pages"><?php the_title();if ( get_field('secoundtitle')) { echo '<br>'; echo  get_field('secoundtitle');}?></h1>

                    <?php if($position):?>
                        <p><?php echo $position;?></p>
                    <?php endif;?>

                </div>
           	<div class="pt-20 page-txt ">
							<?php  echo the_content();?>
						</div>					
                </div>
            </div>
        </section>
        <!-- =============== about area end =============== -->

 <?php 

           $program_related_programsnew = get_field('program_related_programs');

        if(!empty($program_related_programsnew)):
        ?>

        <section class="moreConcerts container max-1200 pb-100">
            <div class="">
                <div class="">
                    <div class="" style="width: 100%;">
					<?php if  (get_field('title_programs',$post_id)) {
	echo '<h2>' . get_field('title_programs',$post_id) . '</h2>';
} else {
 echo '<h2>לרכישת כרטיסים</h2>';
} ?>
                    </div>
                </div>

             <!-- slider start -->
         <div class="splide moreConcerts-splide">
           <div class="splide__track">
             <ul class="splide__list">
            <?php 
        
                foreach($program_related_programsnew as $related_program_id){ 

                    $ipo_created_events = get_related_event_ids($related_program_id);

                    if($ipo_created_events){
                        echo '<li class="splide__slide">';
                        $theme->the_part('loop-program', $related_program_id);
                        echo '</li>';
                    }
                    

                }
            ?>

             </ul>
           </div>
           </div>
                <!-- slider end -->

            </div>
        </section>
<?php else: ?>

        <section class="moreConcerts container max-1200">
</section>
<?php endif;?>
        <!-- =============== More concerts area end =============== -->



	<?php get_footer(); ?>