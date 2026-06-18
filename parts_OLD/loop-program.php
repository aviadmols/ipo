<?php

global $theme;
if(!isset($post_id))
    $post_id = get_the_ID();


$program = new ipo_program($post_id);

//$title = get_the_title($post_id);
$title = $program->get_title();

$permalink = get_the_permalink($post_id);
$program_banner_image = get_field('program_banner_image',$post_id);
$ipo_created_events = get_field('ipo_created_events',$post_id);
$subtitle = get_field('subtitle',$post_id);


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
$html_attributes_str = '';
foreach($html_attributes as $key=>$value){
    $html_attributes_str .= $value ;
}

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

?>

<div class="loop-program item post-<?php echo $post_id;?> <?php echo $e_class; ?>"   data-aos="fade-up"  data-aos-duration="2500" data-aos-delay="<?php echo $html_attributes_str; ?>">  
    <div class="img_box">

            <?php $program->the_playlist(); ?>
    
            <a href="<?php echo $permalink;?>" class="link img-link ">
			
    <div class="position-relative">
                <?php echo $banner_bg; ?>
        </div>
            <?php if($title){ ?>
                <h4 data-aos="fade-in"  data-aos-duration="500" data-aos-delay="<?php  $html_attributes_str = $html_attributes_str + 50; echo $html_attributes_str; ?>"><?php echo $title;?></h4>
            <?php } ?>
            <p class="text"><?php echo $subtitle; ?></p>
        </a>
        <ul>
            <?php $i = 1; foreach($ipo_created_events as $ipo_created_event){ ?>
                <?php

                if($i > 4)
                    break;

                $event = new ipo_event($ipo_created_event);
                $event_location = $event->get_location_str();
                $event_city = $event->get_city();
                
                $event_date = $event->get_date();
                $event_time = $event->get_time();

                $event_datetime = $event->gf('event_date_time');


                $event_day = $event->get_day();
                $event_day = __(sprintf('יום %s\'',$event_day));


                $date_format = get_option( 'date_format' );
                //$event_date_str = date_i18n('d/m',$event_date);
                $event_date_str = date_i18n($date_format,strtotime($event_datetime));

                ?>
                <li data-aos="fade-in"  data-aos-duration="500" data-aos-delay="<?php $html_attributes_str = $html_attributes_str + 50; echo $html_attributes_str; ?>">
                    <a href="<?php echo get_the_permalink($ipo_created_event);?> " class="event-link" data-datetime="<?php echo $event_datetime; ?>">
                        <div>
                            <p class="date"><?php echo $event_date_str; ?></p>

                            <?php if($event_date) : ?>
                            <p class="d-flex"><span><?php echo $event_day; ?></span><span><?php echo $event_time;?></span></p>
                            <?php endif;?>

                        </div>

                        <div>
                        

                        <?php if($event_city){ ?>
                            <span class="location" ><?php echo $event_city;?></span><img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt="">
                        <?php } ?>

                        </div>
                    </a>
                </li>
            <?php $i++;} ?>
        </ul>

        <?php if($total_ipo_created_events > 4){ ?>
            <a class="additionalDates" href="<?php echo $permalink;?>" data-aos="fade-in"  data-aos-duration="500" data-aos-delay="<?php  $html_attributes_str = $html_attributes_str + 50; echo $html_attributes_str; ?>"><span>תאריכים
                נוספים</span> <img src="/wp-content/uploads/2022/06/left-arrow.png" class="arrow" alt="">
            </a>
        <?php } ?>
        
    </div>
</div>