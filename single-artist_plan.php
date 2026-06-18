<?php


get_header();

// Get title of term
$post_id = get_the_ID();

// Check if new theme is enabled
$new_theme = get_field('new-theme', $post_id);


if ($new_theme) {
    // New theme fields
    $new_theme_banner_desktop_image = get_field('new_theme_banner_desktop_image', $post_id);
    $new_theme_banner_mobile_image = get_field('new_theme_banner_mobile_image', $post_id);
  $new_theme_banner_color = get_field('new_theme_banner_color', $post_id);
    $new_theme_banner_title_text = get_field('new_theme_banner_title_text', $post_id);
    $new_theme_banner_title_color = get_field('new_theme_banner_title_color', $post_id);
    $new_theme_events_repeater = get_field('new_theme_events_repeater', $post_id);
    
    
    // Include new theme template
    include 'page-templates/new-theme-artist-plan.php';
    return;
}

// Original theme fields
$subtitle = get_field('subtitle',$post_id);
$content = get_field('content',$post_id);
$content_more = get_field('content_more',$post_id);
$programs = get_field('programs',$post_id);
$SVG_Mobile_img  = get_field('SVG_Mobile_img',$post_id);
$banner_image = get_field('banner_image',$post_id);
$banner_image = new wpstack_image($banner_image);
$program_banner_image_mobile  = get_field('program_banner_image',$post_id);

$white = get_field('white');



global $theme;

$color_header = get_field('color_header');

if ($color_header) { 
  echo '<style>
  .hero-text h1 {

    color: ' . $color_header .'!important;

}


body:not(.white-intro) .hero-text h1 {
    text-shadow: unset!important;
}

.sub-title-simpler {
    color: ' . $color_header .'!important;
}
  
  </style>
  ';
}

?>


   <style>
.hero_area-content .content {
      position: unset!important;
}

h1 {
    color: #fff!important;
}
</style>
  <!-- =============== Hero area start =============== -->
		
 
	    <section class="hero_area-content" style="background-image: url(<?php echo $banner_image->get_src(); ?>); z-index: -1; position: relative;     height: 20vw;" >

             
<div class="gradient-bottom" style="max-height: 50%;">
                                    </div>
          <div class="gradient-top" style="max-height: 25%;">
                                    </div>

            <div class="container">


                <?php
if ($program_banner_image_mobile){
  echo '<style> @media(max-width: 768px){ .hero_area-content { background-image: url('.wp_get_attachment_url($program_banner_image_mobile) .')!important;}
section.hero_area-content {
    min-height: 70vw!important;
    background-position: bottom center!important;
}
.hero_area-content .content {
    padding-bottom: 15px!important;
}

.hero_area-content  h1 {
    font-size: 7rem!important;
}

}</style>';
}
              
              
              if ($SVG_Mobile_img){
  echo '<style> @media(max-width: 768px){ .hero_area-content { background-image: url('. $SVG_Mobile_img .')!important;}
section.hero_area-content {
    min-height: 70vw!important;
    background-position: bottom center!important;
}
.hero_area-content .content {
    padding-bottom: 15px!important;
}

.hero_area-content  h1 {
    font-size: 7rem!important;
}

}</style>';
}

      if($white) {
$class = 'class="white-text"';
}?>
            <?php if($hide_banner_content != true): ?>
                <div class="content">
                    <h1 <?php echo $class; ?>><?php the_title();?></h1>
                                        </div>
            <?php endif; ?>

            </div>
			<div class="gradient-top"></div>
        </section>
		
        <?php if($description): ?>
		<section class="description">
            <div class="container max-1440">
                <div class="row">
                    <div class="col-md-12">
                        <div class="content">
                            <?php echo $description; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
		<?php endif; ?>



<section class="events-Contents">

    <div class="container max-1440">
         
        <div class="artist-plan-content infopro">
             
       <?php if($subtitle) echo '<p>'.$subtitle.'</p>'; ?>
          
            <?php echo $content; ?>
		 </div>	
			<?php 
$readmoretext = 'לקריאה נוספת';
$readlessetext = 'קרא פחות';
if(ICL_LANGUAGE_CODE == 'en'){
   $readmoretext  = 'Read More';
$readlessetext = 'Read less';
}
 if($content_more) {  echo '<div class="content_more">'; echo $content_more; echo '</div>';  echo '<div class="readmore"><a onclick="onclickreadmore()"  class="read-more"  style="    margin-top: 10px; cursor: pointer;"  data-aos="fade-in" data-aos-duration="500" data-aos-delay="500">
'. $readmoretext .'<img src="/wp-content/uploads/2022/06/left-arrow.png" alt="" ></a></div>';} ?>
       


<script>
    
   
function onclickreadmore() {
	
	if ( jQuery('.content_more').hasClass('active' )) {
		
		   jQuery('.content_more').removeClass('active');
   
      jQuery('.readmore').removeClass('active');
		}else{
   jQuery('.content_more').addClass('active');
   
      jQuery('.readmore').addClass('active');
}
}
</script>
    <style>
	
section.events-Contents .name-contents {
    align-items: flex-start;
}
		
		

		
		.content_more {
    height: 0;
    overflow: hidden;
		}

	.content_more.active {
		   height: auto!important;
	 -webkit-line-clamp: 800!important;
		}

		
		
		.content .ipo-breadcrumbs-wrapper.container {
    padding-left: 0;
			margin-top: 20px;
		}
		
		.content .ipo-breadcrumbs {
    color: #fff!important;
		}


h4.program-toggle.open:after {
    transform: translate(0%, -50%) rotate(180deg);
    top: 50%;
}
		
		.readmore button{
    font-size: 16px;
    font-weight: 600;
    background-color: var(--e-global-color-233f972 );
    padding-top: 0px!important;
    padding-left: 0px!important;
        outline: none!important;
color: #000!important;
    background: transparent;
    border: none!important;
        font-family: 'Source Sans Pro';
}

.img_box h4 {
    height: auto!important;
		}

.readmore {
     opacity: 1;
     transition: all 1s;
}

.readmore.active {
     opacity: 0;
     transition: all 1s;
}
		</style>
    <?php 
    $i=0;


    $tlv_string = 'תל אביב';
    $jerusalem_string = 'ירושלים';
    $haifa_string = 'חיפה';
    $item_number_str = 'תוכנית מס׳';
    $intermission_str = 'הפסקה';
    $artists_str = 'אמנים';
    $program_toggle_str = 'התוכנית | האמנים';
    $additional_dates_str = 'תאריכים<br> נוספים';
    $the_program_str = 'התוכנית';
    if(ICL_LANGUAGE_CODE == 'en') {
        $tlv_string = 'Tel Aviv';
        $jerusalem_string = 'Jerusalem';
        $haifa_string = 'Haifa';
        $item_number_str = 'Program No.';
        $intermission_str = 'Intermission';
        $artists_str = 'Artists';
        $program_toggle_str = 'Program | Artists';
        $additional_dates_str = 'Additional<br> Dates';
        $the_program_str = 'The Program';
    }


    foreach($programs as $program):
        $i++;

        $title = $program['title'];
        $date_text = $program['date_text'];
        $artist_text = $program['artist_text'];
        $image = $program['image'];
        $details = $program['details'];
        $events_tel_aviv = $program['events_tel_aviv'];
        $events_jerusalem = $program['events_jerusalem'];
        $events_haifa = $program['events_haifa'];
        $program_schedule = $program['program_schedule'];
        $artists = $program['artists'];

        $program_banner_image = new wpstack_image($image);

        $location_titles = '';

        $events = array();
        if($events_tel_aviv) {
            $events[] = $events_tel_aviv;
            $location_titles .= '<li>' . $tlv_string . '</li>';
        }
        if($events_jerusalem) {
            $events[] = $events_jerusalem;
            $location_titles .= '<li>' . $jerusalem_string . '</li>';
        }
        if($events_haifa) {
            $events[] = $events_haifa;
            $location_titles .= '<li>' . $haifa_string . '</li>';
        }

        $events_merged = array_merge(...$events);



        ?>
            <div class="artist_plan-loop-item img_box img_Contents" data-item_id="<?php echo $i; ?>">

                <div class="name-contents">
                    <div class="position-relative">
                        <a href="#" class="link img-link ">

                        <?php if($program_banner_image) : ?>
                            <div class="media">
                                <?php echo $program_banner_image->get_bg_img(['size'=>'full']); ?>
                            </div>
                        <?php endif; ?>
                        </a>
                            

                    </div>
                    
                        
                        <div class="details">

                            <?php 
                            
                            if(!$title){
                                $title = $item_number_str . ' ' . $i;
                            }
                            
                            ?>

                            <?php if($title): ?>
                            <h3><?php echo $title; ?></h3>
                            <?php endif; ?>

                            <?php if($date_text): ?>
                            <p><?php echo $date_text; ?></p>
                            <?php endif; ?>

                            <?php if($artist_text): ?>
                            <p><?php echo $artist_text; ?></p>
                            <?php endif; ?>

                            <?php if($details): ?>
                            <div class="details-para"><?php echo $details; ?></div>
                            <?php endif; ?>

                            <?php if($location_titles): ?>
                            <ul class="cities"><?php echo $location_titles; ?></ul>
                            <?php endif; ?> 



                        </div>
                        
                    
                </div>
                
                <div class="events">
                <?php 
                    $i = 1;
                    foreach($events_merged as $event){
                        $event_e_class =  '';
                        if($i > 4) {
                            $event_e_class = 'additional-date';
                            //echo 1;
                        }
                        $theme->the_part('loop-serie-event',['post_id'=>$event,'e_class'=>$event_e_class]);

                        $i++;
                    }
                ?>
                 <?php if(count($events_merged) > 4): ?>
                <a class="additionalDates" href="#"><span><?php echo $additional_dates_str; ?><img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt=""></span>
                    
                </a>
                <?php endif; ?>
                </div>
                
                <?php if($program_schedule || $artists): ?>
                <div class="program-row">
                    <h4 class="program-toggle  open"><?php echo $program_toggle_str; ?></h4>
                    <div class="program-schedule open">
                        <?php if($program_schedule): ?>
                        <div class="artists">
                            <h4><?php echo $the_program_str; ?></h4>
                            <ul>
                                <?php foreach($program_schedule as $schedule): ?>
                                    <?php 
                                        $break = $schedule['break'];
                                    ?>
                                    <?php if($break): ?>
                                        <li class="break">
                                            <img src="/wp-content/themes/wpstack-child/assets/images/Icon_mug-hot.svg" alt=""> 
                                            <span>  <?php echo $intermission_str; ?></span> 
                                        </li>
                                    <?php else: ?>
                                        <li>
                                            <strong><?php echo $schedule['artist']; ?></strong> 
                                            <span><?php echo $schedule['performance']; ?></span> 
                                        </li>
                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php if($artists): ?>
                        <div class="artists">
                            <h4><?php echo $artists_str; ?></h4>
                            <ul>
                                <?php foreach($artists as $artist): ?>
                                    <?php 
                                        $artist_name = get_the_title($artist);
                                        $artist_role = get_the_terms($artist,'artist_role');

                                        if($artist_role) {
                                            $artist_role = $artist_role[0]->name;
                                        }

                                    ?>
                                    <li> <strong><?php echo $artist_name; ?></strong> <span><?php echo $artist_role; ?></span> </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                    </div>

                </div>
                <?php endif; ?>

               

            </div>

        <?php 
    endforeach; 
    ?>

    </div>

</section>

<?php get_footer();?>


<style>
	
	.min-height-100 {
		height: 100vh;
	}
    .hero_area-content {

       
        background-size: contain !important;
        max-height: 770px;
        background-repeat: no-repeat !important;
        display: flex;

        align-items: flex-end;
        background: rgba(239, 163, 139, 1);
    }

    .hero_area-content p {
        font-family: 'SimplerPro-Light';
        font-size: 28px;
        font-family: 'Simpler';
        font-weight: 300;
        line-height: 1.8;
        margin-right: 1rem;
        color: #fff;
    }

    .hero_area-content>.container {
        padding-bottom: 0px;
    }


    .img_box.img_Contents {
        justify-content: space-between;
        display: flex;

        padding-top: 5rem;
        padding-bottom: 5rem;
        border-bottom: 1px solid rgba(206, 206, 206, 1);
        margin-bottom: 5rem;
        margin-top: 5rem;
        align-items: end;
    }


    .img_box.img_Contents ul {
        display: flex;
        justify-content: space-between;
    }

    .img_Content .img_box a:not(.link) {
        display: inline-flex;
        align-items: center;
        text-align: center;
        flex-flow: wrap;
        justify-content: center;
        align-items: center;
    }

    .img_box.img_Contents li {
        margin-left: 20px;
    }


    .img_box.img_Contents li p,
    .img_box.img_Contents li .location,
    .img_box.img_Contents li div {
        width: 100% !important;
        justify-content: center;
        font-size: 2rem;
        text-align: center;
        margin-left: 0px !important;
        margin-right: 0px !important;
    }

    .img_box.img_Contents li {
           height: 130px;
    width: 162px;
    border-radius: 15px;
    border: 1px solid #eee;
    }

    .img_box.img_Contents li .location {
        margin-bottom: 5px;
    }

    .additionalDates {
        position: relative;
        font-weight: 900;
        max-width: 75px;
        line-height: 1;
        display: flex;
        font-size: 20px;
        height: 40px;
    }


    .additionalDates img {
        position: absolute;
        bottom: 0px;
        left: 0px;
    }

	
.panel-heading h4{
    padding-bottom: 0px!important;
    margin-bottom: 0px!important;
}
	

    @media (max-width: 1500px) {
        .img_box .position-relative {
            margin-left: 25px;
        }
    }
  
  
  
    @media (max-width: 768px) {

.hero_area-content p {
      font-size: 20px!important;
      }

    .img_box.img_Contents li p, .img_box.img_Contents li .location, .img_box.img_Contents li div{
    text-align: unset!important;  
  }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const additionalDatesButton = document.querySelector('.additionalDates');
    if (additionalDatesButton) {
        additionalDatesButton.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelectorAll('.loop-event.loop-serie-event.additional-date').forEach(function (el) {
                el.style.display = 'block';
            });

            additionalDatesButton.style.opacity = 0;
        });
    }
});

</script>