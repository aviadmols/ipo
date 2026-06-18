<?php 

/*

* Template Name: Lobby listen from home

*/


get_header();

global $theme;

$bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
$video_categories = get_field('video_categories');

$subtitle = get_field('subtitle');

$subscribe_title = get_field('subscribe_title');
$subscribe_text = get_field('subscribe_text');
$subscribe_link = get_field('subscribe_link');

$program_title = get_field('program_title');
$program_subtitle = get_field('program_subtitle');
$program_text = get_field('program_text');
$program_link = get_field('program_link');
$program_image = get_field('program_image');


$podcast_title = get_field('podcast_title');
$podcast_subtitle = get_field('podcast_subtitle');
$podcast_image = get_field('podcast_image');
$podcasts = get_field('podcasts');
$podcast_link = get_field('podcast_link');


$videos_title = get_field('videos_title');
$videos_subtitle = get_field('videos_subtitle');
$videos = get_field('videos');

$selected_videos = get_field('videos-post');
$selected_videos_02 = get_field('videos-post_02');
$selected_videos_03 = get_field('videos-post_03');
$selected_videos_04 = get_field('videos-post_04');
$selected_videos_05 = get_field('videos-post_5');
$videos_link = get_field('videos_link');


$videos_title_2 = get_field('videos_title_2');
$videos_subtitle_2 = get_field('videos_subtitle_2');
$videos_2 = get_field('videos_2');
$videos_link_2 = get_field('videos_link_2');


$videos_title_3 = get_field('videos_title_3');
$videos_subtitle_3 = get_field('videos_subtitle_3');
$videos_3 = get_field('videos_3');
$videos_link_3 = get_field('videos_link_3');


$videos_title_4 = get_field('videos_title_4');
$videos_subtitle_4 = get_field('videos_subtitle_4');
$videos_4 = get_field('videos_4');
$videos_link_4 = get_field('videos_link_4');


$videos_title_5 = get_field('videos_title_5');
$videos_subtitle_5 = get_field('videos_subtitle_5');
$videos_5 = get_field('videos_5');
$videos_link_5 = get_field('videos_link_5');




$artists = get_field('artists');
$bannerImage = wp_get_attachment_image_url( get_post_thumbnail_id( get_the_ID() ), 'full' );
if (empty($bannerImage) ){
  $bannerImage = ipo_theme_uri( 'includes/images/bg/1.jpg' );
  
}

$banner_image_mobile  = get_field('banner_image_mobile_main');

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero-section{
     min-height: 265px;
background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}
.hero-section {
    padding: 0px!important;
    }
    
.hero-section>.container {
    position: absolute;
    bottom: 10px;
}

}
</style>'; 
}

?>


   <div class="main-content">
<section class="hero_page-section hide-pc dispaly_for_mobile" style=";" >

<div class="gradient-bottom" > </div>
  <div class="gradient-top"> </div>
        <div class="banner_image"> 
            <?php 

$banner_image_mobile  = get_field('banner_image_mobile_main');
           echo '<img src="'.$bannerImage.'" />';

if ($banner_image_mobile) {
 
 echo '<style>
 @media (max-width: 768px){
 .hero-section{
     min-height: 265px;
background-image: url(' . wp_get_attachment_url($banner_image_mobile) .')!important;
}
.hero-section {
    padding: 0px!important;
    }
    
.hero-section>.container {
    position: absolute;
    bottom: 10px;
}

}
</style>'; 
}

?>
      <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
        </div>      
<div class="container flex-pc max-1440" style="background: #fff; padding-top: 15px;">
   <div class="content width-25">
<h1 class="title-pages" style="    font-size: 60px!important; color: #000000; margin-right: 0px!important; margin-left: 0px!important; letter-spacing: 3px!important;">
<?php echo get_the_title(); ?></h1>
       
                </div>
</div>
</section>
     

            <section class="hero-section" style="background-image: url(<?php echo $bannerImage; ?>);">
                               <?php echo do_shortcode('[ipo-breadcrumbs]'); ?>
<div class="gradient-bottom" style="max-height: 50%;">
                                    </div>
          <div class="gradient-top" style="">
                                    </div>
                <div class="container">
                    <div class="hero-text">
                        <h1 class="header-title pb-25"><?php echo get_the_title(); ?></h1>
                      	
                      	<?php if($subtitle) : ?>
                        <h3 class="sub-title-simpler white-text"><?php echo $subtitle;?></h3>
                      	<?php endif;?>
                      
                      
                    </div><!-- /.hero-text -->
                </div><!-- /.container -->
            </section><!-- /.hero-section -->


    <style>
      .key-plan .wpstack-image img {
    width: 100%!important;
      }
      
     </style>

     
     
     
    


       <section class="video-section pb-50 pt-100">
                <div class="container">
                    <div class="title style-1 pb-50">

                        <?php if($videos_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            <?php 
                          
                              foreach( $selected_videos as  $video_arg): ?>

                          <?php  

        $subtitle = get_field('subtitle', $video_arg);
        // Clean $subtitle from HTML except <br>
        $subtitle = strip_tags($subtitle, '<br>');
              $youtube_video = get_field('youtube_video', $video_arg);                
                   $start_date = get_field('start_date', $video_arg);
                              $bio = get_field('bio', $video_arg);
                              $title = get_the_title($video_arg);
                          ?>
                                <div class="video">
                                    <div class="thumb"  style="position: relative;">
    							

                                        <?php  
                                                                 

 $fetch=explode("v=", $youtube_video);
 $videoid=$fetch[1];
                                       if( $videoid){
 echo '<img src="http://img.youtube.com/vi/'.$videoid.'/sddefault.jpg" width="250" height="200"/>';
                                       }         else {
                                        // Get featured image id
                                        $image_id = get_post_thumbnail_id($video_arg);
                                        // Get image url
                                        $image_url = wp_get_attachment_image_src($image_id, 'medium');
                                        $image_url = $image_url[0];
                      echo '<img src="'.$image_url.'" width="250" height="200"/>';                   
                                      }
?>


  <div class="overlay">

                                        <?php if($youtube_video) : ?>
                                            <a href="<?php echo $youtube_video;?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
           
                                        
                                    </div>
                                  
        <div class="sub-video" style="padding: 15px;">
              <a href="<?php echo $youtube_video;?>">
                            <?php if( $title) : ?>
                                            <h3 style="padding: 0; position: unset; text-align: revert; color: #000;"><?php echo  $title;?></h3>
                                         <p><?php echo  $subtitle;?></p>
                                        <?php endif;?>
          </a>
                      </div>
                                </div>
                  
<!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   
                                <?php 
                                    if( $videos_link ): 
                                    echo '<div class="view-more pt-25">';
                                    $link_url = $videos_link['url'];
                                    $link_title = $videos_link['title'];
                                    $link_target = $videos_link['target'] ? $videos_link['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                    <?php echo '</div>'; ?>
                                    <?php endif; ?>
            
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     

           
     
        <section class="video-section pb-50 pt-100" style="    background-color: #f9f6ff;">
                <div class="container">
                    <div class="title style-1 pb-50">

                        <?php if($videos_title_2) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title_2;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle_2) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle_2;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                         
                            <?php 
                          
                              foreach( $selected_videos_02 as  $video_arg): ?>

                          <?php  

        $subtitle = get_field('subtitle', $video_arg);
        // Clean $subtitle from HTML except <br>
        $subtitle = strip_tags($subtitle, '<br>');
              $youtube_video = get_field('youtube_video', $video_arg);                
                   $start_date = get_field('start_date', $video_arg);
                              $bio = get_field('bio', $video_arg);
                              $title = get_the_title($video_arg);
                          ?>
                                <div class="video">
                                    <div class="thumb"  style="position: relative;">
    								

                                        <?php  
                                                                 

 $fetch=explode("v=", $youtube_video);
 $videoid=$fetch[1];
                                      if( $videoid){
 echo '<img src="http://img.youtube.com/vi/'.$videoid.'/sddefault.jpg" width="250" height="200"/>';
                                      }         else {
                      echo '<img src="/" width="250" height="200"/>';                   
                                      }

?>


  <div class="overlay">

                                        <?php if($youtube_video) : ?>
                                            <a href="<?php echo $youtube_video;?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
           
                                        
                                    </div>
                                  
        <div class="sub-video" style="padding: 15px;">
              <a href="<?php echo $youtube_video;?>">
                            <?php if( $title) : ?>
                                            <h3 style="padding: 0; position: unset; text-align: revert; color: #000;"><?php echo  $title;?></h3>
                                         <p><?php echo  $subtitle;?></p>
                                        <?php endif;?>
          </a>
                      </div>
                                </div>
                  
<!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   
                                <?php 
                                    if( $videos_link_2 ): 
                                    echo '<div class="view-more pt-25">';
                                    $link_url_2 = $videos_link_2['url'];
                                    $link_title_2 = $videos_link_2['title'];
                                    $link_target_2 = $videos_link_2['target'] ? $videos_link_2['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url_2 ); ?>" target="<?php echo esc_attr( $link_target_2 ); ?>"><?php echo esc_html( $link_title_2 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                    <?php echo '</div>'; ?>
                                    <?php endif; ?>
            
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     
     
     
        <section class="video-section pb-50 pt-100" style="    background-color: #eefbf1;">
                <div class="container">
                    <div class="title style-1 pb-50">

                        <?php if($videos_title_3) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title_3;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle_3) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle_3;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            
                            <?php 
                          
                              foreach( $selected_videos_03 as  $video_arg): ?>

                          <?php  

        $subtitle = get_field('subtitle', $video_arg);
        // Clean $subtitle from HTML except <br>
        $subtitle = strip_tags($subtitle, '<br>');
              $youtube_video = get_field('youtube_video', $video_arg);                
                   $start_date = get_field('start_date', $video_arg);
                              $bio = get_field('bio', $video_arg);
                              $title = get_the_title($video_arg);
                          ?>
                                <div class="video">
                                    <div class="thumb"  style="position: relative;">
    								

                                        <?php  
                                                                 

 $fetch=explode("v=", $youtube_video);
 $videoid=$fetch[1];
                                       if( $videoid){
 echo '<img src="http://img.youtube.com/vi/'.$videoid.'/sddefault.jpg" width="250" height="200"/>';
                                       }         else {
                      echo '<img src="/" width="250" height="200"/>';                   
                                      }
?>


  <div class="overlay">

                                        <?php if($youtube_video) : ?>
                                            <a href="<?php echo $youtube_video;?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
           
                                        
                                    </div>
                                  
        <div class="sub-video" style="padding: 15px;">
              <a href="<?php echo $youtube_video;?>">
                            <?php if( $title) : ?>
                                            <h3 style="padding: 0; position: unset; text-align: revert; color: #000;"><?php echo  $title;?></h3>
                                         <p><?php echo  $subtitle;?></p>
                                        <?php endif;?>
          </a>
                      </div>
                                </div>
                  
<!-- /.video -->
                            <?php endforeach;?>


                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   
                                <?php 
                                    if( $videos_link_3 ): 
                                    echo '<div class="view-more pt-25">';
                                    $link_url_3 = $videos_link_3['url'];
                                    $link_title_3 = $videos_link_3['title'];
                                    $link_target_3 = $videos_link_3['target'] ? $videos_link_3['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url_3 ); ?>" target="<?php echo esc_attr( $link_target_3 ); ?>"><?php echo esc_html( $link_title_3 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                    </div>
                                    <?php endif; ?>
            
                </div><!-- /.container -->
            </section><!-- /.video-section -->
     
     
     
     
        <section class="video-section pb-50 pt-100" style="background-color: #c4f0cf;">
                <div class="container">
                    <div class="title style-1 pb-50">

                        <?php if($videos_title_4) : ?>
                         <h2 class="lette-sapce-10"><?php echo $videos_title_4;?></h2>
                        <?php endif;?>

                        <?php if($videos_subtitle_4) : ?>
                        <h3 class="sub-title-simpler"><?php echo $videos_subtitle_4;?></h3>
                        <?php endif;?>


                    </div>

                    <div class="slider-content">
                        <div class="owl-carousel video-slider">

                            
                            <?php 
                          
                              foreach( $selected_videos_04 as  $video_arg): ?>

                          <?php  

        $subtitle = get_field('subtitle', $video_arg);
        // Clean $subtitle from HTML except <br>
        $subtitle = strip_tags($subtitle, '<br>');
              $youtube_video = get_field('youtube_video', $video_arg);                
                   $start_date = get_field('start_date', $video_arg);
                              $bio = get_field('bio', $video_arg);
                              $title = get_the_title($video_arg);
                          ?>
                                <div class="video">
                                    <div class="thumb"  style="position: relative;">
    							

                                        <?php  
                                                                 

 $fetch=explode("v=", $youtube_video);
 $videoid=$fetch[1];
                                       if( $videoid){
 echo '<img src="http://img.youtube.com/vi/'.$videoid.'/sddefault.jpg" width="250" height="200"/>';
                                       }
                                      else {
                      echo '<img src="/" width="250" height="200"/>';                   
                                      }
?>


  <div class="overlay">

                                        <?php if($youtube_video) : ?>
                                            <a href="<?php echo $youtube_video;?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
           
                                        
                                    </div>
                                  
        <div class="sub-video" style="padding: 15px;">
              <a href="<?php echo $youtube_video;?>">
                            <?php if( $title) : ?>
                                            <h3 style="padding: 0; position: unset; text-align: revert; color: #000;"><?php echo  $title;?></h3>
                                         <p><?php echo  $subtitle;?></p>
                                        <?php endif;?>
          </a>
                      </div>
                                </div>
                  
<!-- /.video -->
                            <?php endforeach;?>



                        </div><!-- /.video-slider -->                        
                    </div><!-- /.slider-content -->

					   
                                <?php 
                                    if( $videos_link_4 ): 
                                    echo '<div class="view-more pt-25">';
                                    $link_url_4 = $videos_link_4['url'];
                                    $link_title_4 = $videos_link_4['title'];
                                    $link_target_4 = $videos_link_4['target'] ? $videos_link_4['target'] : '_self';
                                    ?>
                                    <a class="" href="<?php echo esc_url( $link_url_4 ); ?>" target="<?php echo esc_attr( $link_target_4 ); ?>"><?php echo esc_html( $link_title_4 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                    </div>
                                    <?php endif; ?>
            
                </div><!-- /.container -->
            </section><!-- /.video-section -->
	

 <section class="video-section pb-50 pt-100" style="background-color: #f9f6ff;" id="Hanukkah">
    <div class="container">
        <div class="title style-1 pb-50">

            <?php if($videos_title_5) : ?>
                <h2 class="lette-sapce-10"><?php echo $videos_title_5;?></h2>
            <?php endif;?>

            <?php if($videos_subtitle_5) : ?>
                <h3 class="sub-title-simpler"><?php echo $videos_subtitle_5;?></h3>
            <?php endif;?>

        </div>

        <div class="slider-content">
            <div class="owl-carousel video-slider">
                <?php foreach( $selected_videos_05 as $video_arg): ?>
                    <?php  
                        $subtitle = get_field('subtitle', $video_arg);
                        $subtitle = strip_tags($subtitle, '<br>');
                        $youtube_video = get_field('youtube_video', $video_arg);                
                        $start_date = get_field('start_date', $video_arg);
                        $bio = get_field('bio', $video_arg);
                        $title = get_the_title($video_arg);
                    ?>
                 <div class="video">
                                    <div class="thumb"  style="position: relative;">
    							

                                        <?php  
                                                                 

 $fetch=explode("v=", $youtube_video);
 $videoid=$fetch[1];
                                       if( $videoid){
 echo '<img src="http://img.youtube.com/vi/'.$videoid.'/sddefault.jpg" width="250" height="200"/>';
                                       }         else {
                                        // Get featured image id
                                        $image_id = get_post_thumbnail_id($video_arg);
                                        // Get image url
                                        $image_url = wp_get_attachment_image_src($image_id, 'medium');
                                        $image_url = $image_url[0];
                      echo '<img src="'.$image_url.'" width="250" height="200"/>';                   
                                      }
?>


  <div class="overlay">

                                        <?php if($youtube_video) : ?>
                                            <a href="<?php echo $youtube_video;?>"><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play1.svg' ) ); ?>" alt="Img" class="img-fluid video-link"></a>
                                        <?php endif;?>

                                    </div>
           
                                        
                                    </div>
                                  
        <div class="sub-video" style="padding: 15px;">
              <a href="<?php echo $youtube_video;?>">
                            <?php if( $title) : ?>
                                            <h3 style="padding: 0; position: unset; text-align: revert; color: #000;"><?php echo  $title;?></h3>
                                         <p><?php echo  $subtitle;?></p>
                                        <?php endif;?>
          </a>
                      </div>
                                </div>
                  
<!-- /.video -->
                <?php endforeach;?>
            </div><!-- /.video-slider -->                        
        </div><!-- /.slider-content -->

        <?php 
            if( $videos_link_5 ): 
                echo '<div class="view-more pt-25">';
                $link_url_5 = $videos_link_5['url'];
                $link_title_5 = $videos_link_5['title'];
                $link_target_5 = $videos_link_5['target'] ? $videos_link_5['target'] : '_self';
                ?>
                <a class="" href="<?php echo esc_url( $link_url_5 ); ?>" target="<?php echo esc_attr( $link_target_5 ); ?>"><?php echo esc_html( $link_title_5 ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
            </div>
        <?php endif; ?>
    </div><!-- /.container -->
</section><!-- /.video-section -->

     
            <section class="characters-section pt-100 pb-100">
                <div class="container">
                    <div class="title style-1 pb-50">

                        <?php if($podcast_title) : ?>
                         <h2 class="lette-sapce-10"><?php echo $podcast_title;?></h2>
                        <?php endif;?>

                        <?php if($podcast_subtitle) : ?>
                        <h3 class="sub-title-simpler"><?php echo $podcast_subtitle;?></h3>
                        <?php endif;?>

                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="thumb">
                                <?php  

                                    $podcast_image = new wpstack_image($podcast_image);

                                    echo $podcast_image->get_img();

                                ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="cs-text">

                                <ul>
                                    <?php foreach($podcasts as $podcast) : ?> 

                                 
                                        <li style=" position: relative;">
                                           <a href="<?php echo $podcast['audio_file']['url'];?>" style=" position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px;"></a>
                                            <div class="text">

                                                <?php if($podcast['title']) : ?>
                                                 <h3 class="Simpler"><?php echo $podcast['title'];?></h3>
                                                <?php endif;?>

                                                <?php if($podcast['subtitle']) : ?>
                                                 <p><?php echo $podcast['subtitle'];?></p>
                                                <?php endif;?>

                                            </div>
                                            <div class="link">

                                                <?php if($podcast['audio_file']) : ?>
                                                    <a href="<?php echo $podcast['audio_file']['url'];?>">להשמעה  <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
                                                <?php endif;?>

                                            </div>
                                        </li>

                                    <?php endforeach;?>

                                </ul>

                              
								       

                                        <?php 
                                        if( $podcast_link ): 
                                            echo '<div class="view-more pt-25">';
                                        $link_url = $podcast_link['url'];
                                        $link_title = $podcast_link['title'];
                                        $link_target = $podcast_link['target'] ? $podcast_link['target'] : '_self';
                                        ?>
                                        <a class="" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                                        </div>
                                        <?php endif; ?>
            
                            </div><!-- /.cs-text -->
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container -->
            </section><!-- /.characters-section -->


<?php
if(ICL_LANGUAGE_CODE == 'en'){
$page_id = '';
} else {
  $page_id = 29317;
}
$podcast_title_child = get_field('podcast_title', $page_id);
$podcast_subtitle_child = get_field('podcast_subtitle', $page_id);
$podcast_image_child = get_field('podcast_image', $page_id);
$podcasts_child = get_field('podcasts', $page_id);
$podcast_link_child = get_field('podcast_link', $page_id); 
?>

<?php if($podcast_title_child) : ?>
    <section class="characters-section pt-100 pb-100" style="background: rgba(249, 195, 196, 1);">
        <div class="container">
            <div class="title style-1 pb-75">

                <?php if($podcast_title_child) : ?>
                    <h2 class="lette-sapce-10"><?php echo $podcast_title_child;?></h2>
                <?php endif;?>

                <?php if($podcast_subtitle_child) : ?>
                    <h3 class="sub-title-simpler"><?php echo $podcast_subtitle_child;?></h3>
                <?php endif;?>

            </div>

            <div class="row prodcastpage">

                <div class="col-lg-6">
                    <div class="cs-text" style="padding-right: 0;">
                        <ul>
                            <?php foreach($podcasts_child as $podcast_child) : ?> 
                                <?php if(isset($podcast_child['audio_file'])) : ?>
                                    <div class="link">
                                        <a href="<?php echo $podcast_child['audio_file'];?>">
                                <?php endif;?>
                                <li>
                                    <div class="text">

                                        <?php if($podcast_child['title']) : ?>
                                            <h3 class="Simpler"><?php echo $podcast_child['title'];?></h3>
                                        <?php endif;?>

                                        <?php if($podcast_child['subtitle']) : ?>
                                            <p style="font-weight: 300!important;"><?php echo $podcast_child['subtitle'];?></p>
                                        <?php endif;?>

                                    </div>
                                    <div class="link">

                                        <?php if(isset($podcast_child['audio_file'])) : ?>
                                            <a href="<?php echo $podcast_child['audio_file'];?>">להשמעה  <span><img src="<?php echo esc_url( ipo_theme_uri( 'includes/images/others/play.svg' ) ); ?>" alt="Img" class="img-fluid"></span></a>
                                        <?php endif;?>

                                    </div>
                                </li>
                                <?php if(isset($podcast_child['audio_file'])) : ?>
                                        </a>
                                    </div>
                                <?php endif;?>
                            <?php endforeach;?>

                        </ul>

                        <div class="view-more pt-25">
                            <?php 
                            if($podcast_link_child): 
                                $link_url_child = $podcast_link_child['url'];
                                $link_title_child = $podcast_link_child['title'];
                                $link_target_child = $podcast_link_child['target'] ? $podcast_link_child['target'] : '_self';
                            ?>
                                <a href="<?php echo esc_url($link_url_child); ?>" target="<?php echo esc_attr($link_target_child); ?>"><?php echo esc_html($link_title_child); ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="thumb">
                        <?php  
                        $podcast_image_obj_child = new wpstack_image($podcast_image_child);
                        echo $podcast_image_obj_child->get_img();
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </section>
<?php endif;?>




         <section class="thumb-content">
                <div class="thumb">
					<a href="<?php echo get_field('Banner_link'); ?>">
                    <img src="<?php echo get_field('image_pc'); ?>" alt="Img" class="img-fluid" style="width: 100%;">
					</a>
                </div>
            </section><!-- /.video-section -->

      

        </div><!-- /.main-content -->

<?php get_footer(); ?>