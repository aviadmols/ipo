<?php 

$event = new ipo_event($post_id);
$event_day = $event->get_day();

$event_date_time = get_field('event_date_time',$post_id);

$event_date = date('Ymd',strtotime($event_date_time));
$today_sticker = '';

if(date('Ymd') == $event_date){
    $today_sticker = '<div class="today-sticker">'. __('today', 'ipo') .'</div>';
}


?>


<li data-post_id="<?php echo $post_id; ?>" class="loop-event loop-serie-event upd2">
    <a href="<?php echo $event->get_purchase_link(true); ?>" class="overlay_link"></a>
    <?php echo $today_sticker;?>

        

        <span class="location" ><?php echo $event->get_city(); ?></span>
        <div>

            <p class="date"><?php echo $event->get_date(); ?></p>
            <p class="d-flex"><span><?php echo __(sprintf('יום %s\'',$event_day)); ?></span><span><?php echo $event->get_time(); ?></span></p>

        </div>

        
        
  </li>
    