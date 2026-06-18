<?php 
$video_banner_lottie = get_field('video_banner_lottie');
$video_banner_mp4 = get_field('video_banner_mp4');

$Titlevideo = get_field('Title-video');
$Titlevideo02 = get_field('itlevideo02');
$caver_image = get_field('caver_image');
?>

<section class="video_area " data-aos="fade-in" data-aos-offset="500" data-aos-duration="500"  data-aos-delay="500">
<div class="backgroun-image" style="background:url(<?php echo $caver_image; ?>) no-repeat center;background-size: cover!important;">
  <div class="overlay-background" style="height: 100%;">
                                    </div>
            <div class="container" style="position: relative; z-index: 2;">
        
                <!-- video start -->
                <a class="play-youtube" onclick="jsplyvideo()" href="javascript:void(0);" ><img src="/wp-content/uploads/2022/06/play-icon.png"
                        class="w-100" alt=""></a>
                <!-- video end -->
        
                <div class="row justify-content-end">
                    <div class="col-12">
                        <div class="text animate_wow">
                          <?php if ( $Titlevideo): ?>
                          <h2 class="lette-sapce-10 ml10 animate-letters" style="color: #ffc5c5; line-height: 1.1;"  data-aos="fade-in" data-aos-duration="50" data-aos-delay="50">
<?php echo $Titlevideo; ?></h2>
                                       <h2 class="lette-sapce-10 ml10 animate-letters" style="color: #ffc5c5;    line-height: 1.1;" data-aos="fade-in" data-aos-duration="1050" data-aos-delay="1050">
<?php echo $Titlevideo02; ?></h2>
                           <?php else: ?>
                          <?php echo $video_banner_lottie; ?><
              <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <?php 
                if($video_banner_mp4){
                    // Get video src from file id
                    $video_src = wp_get_attachment_url($video_banner_mp4);
                    $video_banner_mp4 = '
                    <video id="play-background" data-play="true" data-id="1" class="play-background" playsinline="" loop="" controls>
                    <source src="'.$video_src.'" type="video/mp4">
                    </video>'; 

                    echo $video_banner_mp4;

                }
            ?>

  
  <span id="close" onclick="removeDiv(this)" style="  opacity: 0; position: absolute;
 color: #fff;
    font-family: inherit;
    font-size: 40px;
	left: 25px;
	top: 10px;
	crus
	"> <img src="/wp-content/uploads/2023/02/x.svg" style="    width: 48px;" /> </span>
	


        </section>