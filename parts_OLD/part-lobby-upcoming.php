<?php 


$part = new wpstack_part('lobby-upcoming');

$part->data['e_class'][] = 'upcoming_area section-view pt-75 pb-75';

$part->data['e_class_container'][] = '';



global $theme;
$part->build_opening_tag();

$selected_programs = $part->gf('upcoming_selected_programs');

if(!$selected_programs){

    // Get posts of type 'event' where the 'event_date_time' field is greater than today's date. Sort the results by the closest date.
    $amount = $part->gf('upcoming_events_num');
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

$calendar_page = $part->gf('calendar_page','option');
if($calendar_page)
    $calendar_url = get_permalink($calendar_page);
else 
    $calendar_url = '#';

?>


<div class="flex-center height100">
    <div class="row animate_wow">
        <div class="col-12">
            
            <?php if($part->gf('upcoming_lottie_code')): ?>
            <div class="title_box">
                </lottie-player><?php echo $part->gf('upcoming_lottie_code'); ?>
            </div>
            <?php endif; ?>

            <div class="title style-1 pb-75">
                <?php if($part->gf('upcoming_title')): ?>
                <h2 class="lette-sapce-10"><?php echo $part->gf('upcoming_title'); ?></h2>
                <?php endif; ?>

                <?php if($part->gf('upcoming_subtitle')): ?>
                <h3 class="sub-title-simpler">
                <?php echo $part->gf('upcoming_subtitle'); ?>
                </h3>
                <?php endif; ?>

            </div>  
        </div>
    </div>
    <div class="row justify-content-lg-between <?php echo $e_class;?>">
        <?php 

        $time = 0;
        foreach($programs as $program){
            $time = $time + 0.5;
            $theme->the_part('loop-program',['post_id'=>$program,'e_class'=>'wow fadeInUp','override_home'=>true,'html_attributes'=>['data-wow-delay'=>$time.'s']]);
        }

        $see_more_btn = $part->gf('upcoming_button');
        if($see_more_btn){
            $btn_title = $see_more_btn['title'];
            $btn_url = $see_more_btn['url'];
            $btn_target = $see_more_btn['target'] ? $see_more_btn['target'] : '_self';
            $see_more_btn = '<a class="schedule" href="'.$btn_url.'" target="'.$btn_target.'"  data-aos="fade-in" data-aos-duration="500" data-aos-delay="500">'.$btn_title.' <img src="/wp-content/uploads/2022/06/left-arrow.png" alt="" /></a>';
        }

        ?>
    
    </div>
    <?php echo $see_more_btn; ?>
</div>


<?php

$part->build_closing_tag(); 

?>