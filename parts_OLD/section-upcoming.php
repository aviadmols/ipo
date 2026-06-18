<?php 

global $theme;

$selected_programs = get_field('upcoming_selected_programs');

if(!$selected_programs){

    // Get posts of type 'event' where the 'event_date_time' field is greater than today's date. Sort the results by the closest date.
    $amount = get_field('upcoming_events_num');
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
    foreach($events as $event){
        $event = new ipo_event($event);
        $program = $event->get_program();
        $programs[] = $program;;
    }

    $programs = array_unique($programs);

} else {
    $programs = $selected_programs;
}




$count = count($programs);
$e_class = '';

// If less than 5 items, duplicate the items to fill the slider
if($count <= 5){
    $programs = array_merge($programs,$programs);
    $count = count($programs);
}

if ($count > 5) {
    $e_class = 'owl-carousel owl-theme upcoming-slider';
}

$calendar_page = get_field('calendar_page','option');
if($calendar_page)
    $calendar_url = get_permalink($calendar_page);
else 
    $calendar_url = '#';

?>

<section class="upcoming_area section-view pt-75 pb-75">
    <div class="container">
        <div class="flex-center height100">
            <div class="row">
                <div class="">
					   <div class="title style-1 pb-75">
                                    <h2 class="lette-sapce-10 ml10 animate-letters" >
 <span class="text-wrapper">
   <span class="letters"><?php the_field('upcoming_title'); ?></span></span></h2>
                                    <h3 class="sub-title-simpler" data-aos="fade-in" data-aos-duration="3000" data-aos-delay="3000">
									<?php the_field('upcoming_subtitle'); ?>
									</h3>
                                </div>  
                </div>
            </div>
            <div class="row justify-content-lg-between <?php echo $e_class;?>"  data-aos="fade-in"  data-aos-duration="500" data-aos-delay="750">
                <?php 

                $time = 0;
                foreach($programs as $program){
                    $time = $time + 200 + 300;
                    $theme->the_part('loop-program',['post_id'=>$program,'e_class'=>'wow fadeInUp','override_home'=>true,'html_attributes'=>[$time]]);
                }

                $see_more_btn = get_field('upcoming_button');
                if($see_more_btn){
                    $btn_title = $see_more_btn['title'];
                    $btn_url = $see_more_btn['url'];
                    $btn_target = $see_more_btn['target'] ? $see_more_btn['target'] : '_self';
                    $see_more_btn = '<a class="schedule" href="'.$btn_url.'" target="'.$btn_target.'" data-aos="fade-in" data-aos-duration="500" data-aos-delay="500">'.$btn_title.' <img src="/wp-content/uploads/2022/06/left-arrow.png" alt="" /></a>';
                }

                ?>
            
            </div>
            <?php echo $see_more_btn; ?>
        </div>
    </div>
</section>