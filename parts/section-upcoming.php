<?php 

global $theme;

$selected_programs = get_field('upcoming_selected_programs');

if (!$selected_programs) {
    // Get posts of type 'event' where the 'event_date_time' field is greater than today's date. Sort the results by the closest date.
    $amount = 20;
    $events = get_posts(array(
        'post_type' => 'event',
        'meta_key' => 'event_date_time',
        'meta_value' => date('Y-m-d H:i:s'),
        'meta_compare' => '>',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'fields' => 'ids',
        'posts_per_page' => $amount,
        'suppress_filters' => false
    ));

    $programs = [];
    foreach ($events as $event) {
        $event = new ipo_event($event);

        if ($event->is_passed()) {
            continue;
        }

        $program = $event->get_program();
        if ( ! $program || ! ipo_event_has_program( $event->get_id() ) ) {
            continue;
        }

        $programs[] = $program;
    }

    // Remove duplicates
    $programs = array_unique($programs);

} else {
    $programs = $selected_programs;
}

// Remove duplicates if $selected_programs contains duplicates
$programs = array_unique($programs);

$count = count($programs);
$e_class = '';



if ($count <= 4) {
   $e_class = 'row_04';
}
// If less than 5 items, duplicate the items to fill the slider
if ($count <= 5) {
    // Duplicate the array to ensure at least 5 items without introducing duplicates
    $programs = array_merge($programs, $programs);
    $programs = array_unique($programs);
    $count = count($programs);
    $e_class = 'owl-carousel owl-theme upcoming-slider';
}

if ($count > 5) {
    $e_class = 'owl-carousel owl-theme upcoming-slider justify-content-lg-between';
}

$calendar_page = get_field('calendar_page', 'option');
$calendar_url = $calendar_page ? get_permalink($calendar_page) : '#';
?>

<section class="upcoming_area section-view pt-75 pb-75" data-items-count="<?php echo $count; ?>" data-items-ids="<?php echo implode(',', $programs); ?>">
    <div class="container">
        <div class="flex-center height100">
            <div class="row">
                <div class="">
					   <div class="title style-1 pb-75">
                                    <h2 class="lette-sapce-10 ml10 animate-letters" >
 <span class="text-wrapper">
   <span class="letters"><?php the_field('upcoming_title'); ?></span></span></h2>
                                    <h3 class="sub-title-simpler" data-aos="fade-in" data-aos-duration="400"  data-aos-delay="1500">
									<?php the_field('upcoming_subtitle'); ?>
									</h3>
                                </div>  
                </div>
            </div>
              </div>
             </div>
  <div class="container_upcaming">
             <div class="row <?php echo $e_class;?>"  data-aos="fade-in"  data-aos-duration="400" data-aos-delay="500">
                <?php 

                $time = 0;
$i = 1;
                foreach($programs as $program){
                    $time = 0;
                    $theme->the_part('loop-program',['post_id'=>$program,'e_class'=>'wow fadeInUp','override_home'=>true,'html_attributes'=>[$time]]);
  //if ($i++ == 4) break;
                }

                $see_more_btn = get_field('upcoming_button');
                if($see_more_btn){
                    $btn_title = $see_more_btn['title'];
                    $btn_url = $see_more_btn['url'];
                    $btn_target = $see_more_btn['target'] ? $see_more_btn['target'] : '_self';
                    $see_more_btn = '<a class="schedule" href="'.$btn_url.'" target="'.$btn_target.'" data-aos="fade-in" data-aos-duration="500" data-aos-delay="500">'.$btn_title.' <img src="'. ipo_arrow_icon_url() .'" alt="" /></a>';
                }

                ?>
               
    </div>
            </div>
     <div class="container" style="justify-content: flex-start!important;">
            <?php echo $see_more_btn; ?>
        </div>
        </div>
</section>


<style>
  @media (min-width: 1800px) {
    .container_upcaming {
        max-width: calc(100% - 160px);
    }
}
  

@media (min-width: 1100px) and (max-width: 1799px) {
    .container_upcaming {
        max-width: calc(100% - 80px);
    }
}


@media (min-width: 768px) and (max-width: 991px) {
    .container_upcaming {
        max-width: 100% !important;
    }
}


.container_upcaming {
    width: 100%;
    padding-left: 15px;
margin-left: auto;
margin-right: auto;
    padding-right: 15px;
  }


</style>