<?php


get_header();

global $theme;

$program = new ipo_program(get_the_ID());
$program_image = $program->get_image();
$program_image = new wpstack_image($program_image);

if (isset($_GET['p']) && !empty($_GET['p'])) {
    $post_id = $_GET['p'];
} else {
 $post_id = get_the_ID(); 
}

if (is_preview()) {
    $preview = wp_get_post_autosave($post_id);
    if ($preview) {
        $post_id = $preview->ID;
    }
}

$donation_event = '';
$program_related_programs = '';
$events = get_related_event_ids($post_id);
$more_dates_str = 'תאריכים נוספים';
$the_program_section_title_str = 'התוכנית';
$artists_section_str = 'אמנים';
$break_str = 'הפסקה';

if (get_field('Russian_lang')) {
    $more_dates_str = 'Дополнительные даты';
    $the_program_section_title_str = 'Программа';
    $artists_section_str = 'Артисты';
    $break_str = 'Антракт';
} elseif (ICL_LANGUAGE_CODE == 'en') {
    $more_dates_str = 'More dates';
    $the_program_section_title_str = 'The Program';
    $artists_section_str = 'Artists';
    $break_str = 'Intermission';
}

if (get_field('Russian_lang')) {
    echo "<style>
    @media (min-width: 768px){
        h1.header-title {
            font-size: 9rem!important;
            font-family: 'Open Sans'!important;
        }

        .time_zone h2 {
            font-size: 50px!important;
        }

        .hero_area p {
            font-weight: 600;
            font-size: 30px!important;
        }

        .order_area h2 {
            font-size: 7rem;
        }
    }

    @media (max-width: 768px){
        .header-title {
            font-size: 50px!important;
        }

        .order_area h2 {
            letter-spacing: 2.45px!important;
            font-size: 35px!important;
        }

        .content p {
            font-size: 15px!important;
        }

        h2 {
            font-size: 45px!important;
        }
    }
    </style>";
}

$program_artists = get_field('program_artists', $post_id);
$artist_img = get_field('artist_img', $post_id);
$artist_img_info = get_field('artist_img_info', $post_id);
$artist_img_subinfo = get_field('artist_img_subinfo', $post_id);
$program_plan = get_field('program_plan', $post_id);
$program_banner_image_Main  = get_field('program_banner_image_Main', $post_id);
$program_banner_image_mobile  = get_field('program_banner_image_mobile', $post_id);
$banner_title_color = get_field('banner_title_color', $post_id);
$hidden_title = (bool) get_field('hidden_title', $post_id);

$title_style = '';
if ($banner_title_color) {
    $title_style = 'style="color:' . $banner_title_color . ';  text-shadow: none!important; "';
}

?>

<!-- =============== Hero area start =============== -->
<section class="hero_area" style="background-image: url(<?php if ($program_banner_image_Main) { echo wp_get_attachment_image_url($program_banner_image_Main, ''); } else { echo $program_image->get_src(); } ?>); max-height: 770px; background-size: cover;" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="0">
    <?php echo do_shortcode('[ipo-breadcrumbs]'); 
    if ($program_banner_image_mobile) {
        echo '<style> @media(max-width: 768px){ .hero_area { background-image: url(' . wp_get_attachment_url($program_banner_image_mobile) . ')!important;}}</style>';
    }
    ?>
    <div class="container">
        <?php if (!$hidden_title) : ?>
        <div class="content">
            <h1 class="header-title" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="250" <?php echo $title_style; ?>><?php echo $program->get_title(); ?></h1>
            <p data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="500" <?php echo $title_style; ?>><?php echo $program->get_subtitle(); ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>
<!-- =============== Hero area end =============== -->

<?php

$reordered = [];
$current_date = new DateTime(); // תאריך נוכחי

foreach ($events as $event_id) {
    $donation_event = get_field('donation_event', $event_id);
    $event = new ipo_event($event_id);
    $event_date = get_field('event_date', $event_id);
    $event_date_time = get_field('event_date_time', $event_id); // שליפת התאריך והשעה מ-ACF

    // המרת תאריך ושעה לפורמט הנכון ובדיקת תאריך
    $event_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $event_date_time);
    if ($event_datetime && $event_datetime >= $current_date) {
        $reordered[] = ['date' => $event_date, 'date_time' => $event_date_time, 'event' => $event];
    }
}

// מיון הפוסטים לפי התאריכים מהקרוב לרחוק, כולל שעה
usort($reordered, function($a, $b) {
    $timestamp_a = strtotime($a['date_time']);
    $timestamp_b = strtotime($b['date_time']);
    return $timestamp_a <=> $timestamp_b;
});

?>

<!-- =============== Time Zone area start =============== -->
<section class="timeZone_area active" <?php echo 'data-items-count="' . count($reordered) . '"'; ?> data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="300">
    <div class="container custom max-1440">
        <div class="time_zone active" id="time_zone">
            <?php
            foreach ($reordered as $event) {
                $event = $event['event'];
                $event_id = $event->get_id();
                $theme->the_part('loop-program-event', $event_id);
            }
            ?>
            <?php if (count($reordered) > 3) : ?>
                <div class="moreevent"></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!-- =============== Time Zone area end =============== -->

        <!-- =============== about area start =============== -->
        <section class="program-info pt-100 ">
            <div class="container max-1440">
                <div class="row justify-content-between">
                 
                    <div class="col-lg-6" style=" padding-right: 0px;">

  <!-- =============== plan / playlist area start =============== -->
        <?php if($program_plan) : ?>
        <section id="playlist-section" class="plan_area section-view min-height-0">
            <div class="anchor" id="playlist"></div>
            <div class="">
                <div class="">
                    <div class="pb-75">
                        <h2  data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay=""><?php echo $the_program_section_title_str; ?></h2>
                        <ul>
                            <?php 
                                
                                $i = 1;
                          $delay = 100;
                                foreach($program_plan as $plan){
                                    $title = $plan['title'];
                                    $subtitle = $plan['subtitle'];
                                    $audio_track = $plan['audio_track'];
									$break = $plan['break'];

                                    if($title){
                                        $title = '<h4>'.$title.'</h4>';
                                    }

                                    if($subtitle){
                                        $subtitle = '<p>'.$subtitle.'</p>';
                                    }

                                    if($audio_track){
                                        $audio_track = '<a href="'.$audio_track.'"><span>להשמעה</span> <img src="/wp-content/uploads/2022/06/right-arrow.png" class="arrow" alt=""></a>';
                                    }

									  if($break){
                                    echo '
<li class="break">
                                            <img src="/wp-content/themes/wpstack-child/assets/images/Icon_mug-hot.svg" alt=""> 
                                            <span>  '.$break_str.'</span>';
									  } else {
                                    echo    '</li>
<li class="fade-in-bottom one" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="'. $delay .'">
                                                <div class="d-flex align-items-center">
                                                    <h3>0'.$i.'</h3>
													<div class="playlist_subtitle">
                                                    '.$title.'
                                                    '.$subtitle.'
                                                    </div>
													</div>
                                                    <div>
                                                    '.$audio_track.'
                                                </div>
                                            </li>';
                                               $i++;
}
                             
                                   $delay = 100 +  $delay;
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <!-- =============== plan area end =============== -->
                        <div class="content">
                          <?php
$program_about_title = get_field('program_about_title', $post_id);

if (ICL_LANGUAGE_CODE === 'en' && $program_about_title === 'על המופע') {
    $program_about_title = 'About the Program';
}
                       if (get_field('program_description',$post_id)){
                          if($program_about_title):?>
                            <h2 class="pb-25" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay=""><?php echo $program_about_title; ?></h2>
                          <?php endif; ?>
                            
                            <?php echo '<div class="main-text">'.do_shortcode($theme->gf('program_description')) . '</div>'; ?>

                            <?php 

                            // if 'program_description_is_read_more' is set to true and 'program_description_read_more' is not empty
                            $is_read_more = $theme->gf('program_description_is_read_more');
                            $read_more = $theme->gf('program_description_read_more');

                            if($is_read_more && $read_more){
$readmoretext = 'לקריאה נוספת';
if(ICL_LANGUAGE_CODE == 'en'){
   $readmoretext  = 'Read More';

}
                                                

                                echo '<div class="read-more-text" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="300">'. do_shortcode($read_more) .'</div>';
                                echo '<a  class="read-more" style="cursor: pointer;">'. $readmoretext .' <img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>';

                              
                            }


                       }
                            ?>

                                            <style>
                        
                            .program-info .read-more-text {
                                display: none;
                            }
                            .program-info.show-read-more .read-more-text {
                                display: block;
                            }

                            </style>

                        </div>
                   
                    </div>
					
					   <div class="col-lg-5">
 <?php  if(!empty($artist_img)):        ?>
  <h2 class="artist"><?php echo $artists_section_str; ?></h2>
  <div class=" max-550">
<img src="<?php echo $artist_img; ?>" style="max-width: 100%;">
<div class="content">
        <p><strong><?php echo $artist_img_info; ?></strong></p>
 <p><?php echo $artist_img_subinfo; ?></p>
         </div>
  </div>
                        <?php  elseif(!empty($program_artists)):        ?>
                        <h2 class="artist"><?php echo $artists_section_str; ?></h2>
                        <div class="row justify-content-between max-550">

                            <?php foreach($program_artists as $artist_id){ 
						
                                $theme->the_part('loop-artist',$artist_id);
                            } ?>

                        </div>
    <?php endif; 
                      ?>
                    </div>
                </div>
            </div>
        </section>
        <!-- =============== about area end =============== -->

      

        <!-- =============== More concerts area start =============== -->

        <?php 

            // בדיקה אם יש קטגוריה למוצר הנוכחי
            $program_related_programsnew = get_field('program_related_programs',get_the_ID());
            $ids = array();

            // אם יש program_related_programs, נשתמש בו (עדיפות ראשונה)
            if (!empty($program_related_programsnew)) {
                $ids = is_array($program_related_programsnew) ? $program_related_programsnew : ($program_related_programsnew ? [$program_related_programsnew] : []);
            } else {
                // אם אין program_related_programs, נבדוק אם יש קטגוריה
                $current_categories = wp_get_post_terms($post_id, 'category_program', array('fields' => 'ids'));

                if (!empty($current_categories) && !is_wp_error($current_categories)) {
                    // אם יש קטגוריה, שליפת כל הפוסטים מאותה קטגוריה (חוץ מהפוסט הנוכחי)
                    $args = array(
                        'post_type' => 'program',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                        'post__not_in' => array($post_id),
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'category_program',
                                'field'    => 'term_id',
                                'terms'    => $current_categories,
                            ),
                        ),
                    );
                    $category_query = new WP_Query($args);
                    if ($category_query->have_posts()) {
                        $ids = wp_list_pluck($category_query->posts, 'ID');
                    }
                    wp_reset_postdata();
                }

                // אם אין קטגוריה או אין פוסטים בקטגוריה, נשתמש ב-fallback
                if (empty($ids)) {
                    $home_id = get_option('page_on_front');
                    $home_id = (int) get_option('page_on_front');
                    $fallback = get_field('upcoming_selected_programs', $home_id);
                    $ids = is_array($fallback) ? $fallback : ($fallback ? [$fallback] : []);
                }
            }

        if(!empty($ids)):
        ?>

        <section class="moreConcerts container max-1440 upcoming_area">
            <div class="">
                <div class="">
                    <div class="" style="width: 100%;">
					<?php if  (get_field('title_programs',$post_id)) {
	echo '<h2>' . get_field('title_programs',$post_id) . '</h2>';
} else {
if (ICL_LANGUAGE_CODE == 'he') {
    echo '<h2>אולי יעניין אותך גם</h2>';
} else {
    echo '<h2>You may also like</h2>';
}
} ?>
                    </div>
                </div>

             <!-- slider start -->
<div class="owl-carousel owl-theme moreConcerts-slider">

    <?php 
  
 foreach ($ids as $rel):

    $related_id = is_object($rel) ? $rel->ID : (int) $rel;
    $related_post = get_post($related_id);

    if (
        $related_post &&
        $related_post->post_status === 'publish' &&
        in_array($related_post->post_type, ['program', 'artist_plan'])
    ):

        // שליפת כל האירועים המשויכים לתוכנית
        $related_events = get_related_event_ids($related_id);

        $has_future_events = false;

        if (is_array($related_events) && !empty($related_events)) {
            $now = new DateTime();

            foreach ($related_events as $event_id) {
                $event_date_time = get_field('event_date_time', $event_id);
                if ($event_date_time) {
                    $event_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $event_date_time);
                    if ($event_datetime && $event_datetime >= $now) {
                        $has_future_events = true;
                        break; // מספיק אירוע אחד עתידי
                    }
                }
            }
        }

        // דילוג אם אין אירועים עתידיים (רק ל-program)
        if ($related_post->post_type === 'program' && !$has_future_events) {
            continue;
        }
    ?>
        <div class="item <?php
            $program = new ipo_program($related_id);
            echo $related_post->post_type;
            echo $related_id;
        ?>">
            <?php $theme->the_part('loop-program', $related_id); ?>
        </div>

    <?php 
    endif;

endforeach;
 ?>
</div>

                <!-- slider end -->

   <?php 
$home_id_2 = get_option('page_on_front');
$series_button = get_field('upcoming_button', $home_id_2);
                      
    $link_url = $series_button['url'];
    $link_title = $series_button['title'];
    $link_target = $series_button['target'] ? $series_button['target'] : '_self';
                                    ?>
         
                                    <a class="schedule" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"  data-aos="fade-in" data-aos-duration="500" data-aos-delay="500"><?php echo esc_html( $link_title ); ?> <img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>
                                
            </div>
        </section>
<?php else: ?>

        <section class="moreConcerts container max-1440">
</section>
<?php endif;?>
        <!-- =============== More concerts area end =============== -->

        <?php 


        if( is_object($event) && !$event->is_passed() && !get_field('Hiding_purchase') ){
            $theme->the_part('closest-event-floater',['closest_event' => $closest_event]);
        }
        
         
        
        ?>
        <?php get_footer();?>
		

<style>
  
  .order_area, html, body, .site {
    overflow: unset!important;
}
</style>
		
		<script>

          if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {

} else{
 document.querySelector('.moreevent').innerHTML = '<button onclick="onclickreadmore()" class="readmore"><?php echo   $more_dates_str; ?></button>';


          
}
          function onclickreadmore() {
   jQuery('.time_zone').addClass('active');
  jQuery(".order_area").removeClass("sticky");
      jQuery('.moreevent').addClass('active');
}
          
          
jQuery(window).on('load', function() {
  const findOverflows = () => {
        const documentWidth = document.documentElement.offsetWidth;

        document.querySelectorAll('.time_zone p').forEach(element => {
            const box = element.getBoundingClientRect();

            if (element.offsetHeight > 32) { // check if the height is greater than 38px (19px*2)
                element.classList.add("hoverflow");
               } else {
                // your element doesn't have overflow
            }
        });
    };

  findOverflows();
});

		</script>




<style>


@media (min-width: 768px){
.loop-event{
    display: none;
}

  }
  
.loop-event:nth-child(1), .loop-event:nth-child(2), .loop-event:nth-child(3){
    
display: flex;
}


   .time_zone.active .loop-event{
display: flex!important;
  }
  
  .moreevent.active {
    display: none!important;
  }
  
  .time_zone {
    padding-bottom: 0px!important;
  }

.moreevent {
    background: #fff;
    display: flex; 
    bottom: 0px;
    left: 0px;
    right: 0px;
    align-items: center;
    justify-content: center;
    padding-top: 15px;
    padding-bottom: 15px;
    border-top: 1px solid #eee;
}

  .moreevent .readmore {
display: flex!important;
    font-size: 18px;
    align-items: center;
    color: #000000;
    text-align: right;
    line-height: 28px;
    text-decoration: none!important;
    font-weight: 900;
border: none!important
    letter-spacing: 1px;
    padding: 1rem 4.5rem;
        line-height: 1!important;
    transition: .2s;
        background: transparent!important;
        cursor: pointer;
    border: none!important;
  }
 .moreevent.active{
  opacity: 0!important;
  }

  .time_zone {
    max-height: 1800px!important;
  }

.time_zone {
      position: relative;

     overflow: hidden;
}
  
  .time_zone.active {
 
    height: auto!important;
}
</style>