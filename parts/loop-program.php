<?php

global $theme;
if(!isset($post_id))
    return;

//if any post type other than program is passed, return


$program = new ipo_program($post_id);

//$title = get_the_title($post_id);
$title = $program->get_title();

$permalink = get_the_permalink($post_id);
$program_banner_image = get_field('program_banner_image',$post_id);
$ipo_created_events = get_related_event_ids($post_id);

$post_type = get_post_type($post_id);

if ($post_type != 'artist_plan'){
if (!is_array($ipo_created_events) || empty($ipo_created_events)) {
    return;
}
}

$reordered = [];
$current_date = new DateTime();

foreach ($ipo_created_events as $event_id) {
    $event_date_time = get_field('event_date_time', $event_id);

    if ($event_date_time) {
        $event_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $event_date_time);
        if ($event_datetime && $event_datetime >= $current_date) {
            $reordered[] = ['date_time' => $event_date_time, 'event_id' => $event_id];
        }
    }
}

usort($reordered, function($a, $b) {
    $timestamp_a = strtotime($a['date_time']);
    $timestamp_b = strtotime($b['date_time']);
    return $timestamp_a <=> $timestamp_b;
});

$sorted_event_ids = array_column($reordered, 'event_id');

$ipo_created_events = $sorted_event_ids;


$subtitle = get_field('program_subtitle',$post_id);

$program_label = get_field('program_label',$post_id);


if(ICL_LANGUAGE_CODE == 'en' && $program_label == 'כרטיסים אחרונים'){
$program_label = 'Last Tickets';
}



$limit = 3;

if(!isset($override_home)){
    $override_home = false;
}

if(is_array($ipo_created_events))
    $total_ipo_created_events = count($ipo_created_events);
else
    $total_ipo_created_events = 0;

/*
if(isset($params['html_attributes']))
    $html_attributes = $params['html_attributes'];
else 
    $html_attributes = [];
*/



// Convert $html_attributes array into a string to be used as attributes of the html element
$html_attributes_str = 0;


if(!isset($e_class)){
    $e_class = '';
} else {
    if(is_array($e_class)){
        $e_class = implode(' ',$e_class);
    }
}


if(!$program_banner_image){
    $program_banner_image = get_post_thumbnail_id($post_id);
}

if($override_home){

    $program_banner_image_override = get_field('program_override_home_image',$post_id);
    if($program_banner_image_override){
        $program_banner_image = $program_banner_image_override;
    }

} 

$banner_bg = new wpstack_image($program_banner_image);
$banner_bg = $banner_bg->get_bg_img();

if(is_array($post_id)){
    $post_id = $post_id[0];
}

if(is_object($post_id)){
    $post_id = $post_id->ID;
}



if(!$program->has_events() && $post_type != 'artist_plan'){
    return;
}


if( $post_type == 'artist_plan'){
$subtitle = get_field('subtitle',$post_id);
}

?>

<div class="loop-program item  post-<?php echo $post_id;?> <?php echo $e_class; ?>"   data-aos="fade-up"  data-aos-duration="750" data-aos-delay="<?php echo $html_attributes_str; ?>">  
   
    <div class="img_box">

            <?php $program->the_playlist(); ?>
    
            <a href="<?php echo $permalink;?>" class="link img-link ">
			
    <div class="position-relative">
<?php if($program_label){
    $program_label = '<span class="program-label">'.$program_label.'</span>';

echo $program_label;
}  ?>

                <?php echo $banner_bg; ?>
        </div>
            <?php if($title){ ?>
<?php
if (!isset($delay_counter)) {
    $delay_counter = 0;
}
$delay_counter += 25;
?>
<h4 data-aos="fade-in" data-aos-duration="500" data-aos-delay="<?php echo $delay_counter; ?>">
    <?php echo $title; ?>
</h4>
            <?php } ?>
            <p class="text"><?php echo $subtitle; ?></p>
        </a>
        


            <?php 
if($post_type == 'artist_plan'){
 echo '<ul><li style="border-bottom: 3px solid rgba(0, 0, 0, 1)!important;" ></li></ul><a class="additionalDates aos-init aos-animate" href="'. $permalink .'" data-aos="fade-in" data-aos-duration="500" data-aos-delay="125"><span>
              '.  get_field('more_atist_text',$post_id) .' </span> <img src="'. ipo_arrow_icon_url() .'" class="arrow" alt="">
            </a>';
}            
            $has_events = false;

            echo '<ul>';
            $i = 1; foreach($sorted_event_ids as $ipo_created_event){ ?>
            
                <?php

                if($i > $limit)
                    break;

                $event = new ipo_event($ipo_created_event);

                // Check if event is in the past
                $event_is_in_past = $event->is_passed();
                if($event_is_in_past){
                    continue;
                } else {
                    $has_events = true;
                }

                $event_location = $event->get_location_str();
                $event_city = $event->get_city();
                
                $event_date = $event->get_date();
                $event_time = $event->get_time();

                $event_datetime = $event->gf('event_date_time');

                $more_dates_str = 'תאריכים נוספים';
                if(ICL_LANGUAGE_CODE == 'en'){
                    $more_dates_str = 'More Dates';

                }
                
                $event_day = $event->get_day();

                if (ICL_LANGUAGE_CODE == 'he') {
                    if($event_day == 'ש')
                        $event_day = 'שבת';
                    else 
                        $event_day = sprintf('יום %s\'',$event_day);
                } else {

                    switch($event_day){
                        case 'ש':
                            $event_day = 'Saturday';
                            break;
                        case 'א':
                            $event_day = 'Sunday';
                            break;
                        case 'ב':
                            $event_day = 'Monday';
                            break;
                        case 'ג':
                            $event_day = 'Tuesday';
                            break;
                        case 'ד':
                            $event_day = 'Wednesday';
                            break;
                        case 'ה':
                            $event_day = 'Thursday';
                            break;
                        case 'ו':
                            $event_day = 'Friday';
                            break;
                    }

                }





                //$event_day = __(sprintf('יום %s\'',$event_day),'ipo');


                $date_format = get_option( 'date_format' );
                //$event_date_str = date_i18n('d/m',$event_date);
                $event_date_str = date_i18n($date_format,strtotime($event_datetime));


                ?>
                <li data-aos="fade-in"  data-aos-duration="500" data-aos-delay="<?php $html_attributes_str = $html_attributes_str + 25; echo $html_attributes_str; ?>">
                    <a href="<?php echo get_the_permalink($ipo_created_event);?> " class="event-link" data-datetime="<?php echo $event_datetime; ?>">
                        <div>
                            <p class="date"><?php echo $event_date_str; ?></p>

                            <?php if($event_date) : ?>
                            <p class="d-flex"><span><?php echo $event_day; ?></span><span><?php echo $event_time;?></span></p>
                            <?php endif;?>

                        </div>

                        <div style="display: inline-flex;">
                        

                        <?php if($event_city){ ?>
                            <span class="location" ><?php echo $event_city;?></span><img src="<?php echo ipo_arrow_icon_url(); ?>" class="arrow" alt="">
                        <?php } ?>

                        </div>
                    </a>
                </li>
            <?php $i++;} ?>
        </ul>

        <?php if($total_ipo_created_events > $limit){ ?>
            <a class="additionalDates" href="<?php echo $permalink;?>" data-aos="fade-in"  data-aos-duration="500" data-aos-delay="<?php  $html_attributes_str = $html_attributes_str + 25; echo $html_attributes_str; ?>"><span>
               <?php echo $more_dates_str; ?>
</span> <img src="<?php echo ipo_arrow_icon_url(); ?>" class="arrow" alt="">
            </a>
        <?php } ?>
        
    </div>
</div>