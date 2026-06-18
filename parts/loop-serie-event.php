<?php 

$event = new ipo_event($post_id);
$event_day = $event->get_day();

$event_date_time = get_field('event_date_time',$post_id);
$event_date = date('Ymd', strtotime($event_date_time));
$today_sticker = '';

if (!isset($e_class))
    $e_class = '';

$today_str = 'היום';
$passed_str = 'אירוע עבר';
$sale_msg = 'מכירת הכרטיסים תחל ב-1.9';

if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en') {
    $today_str = 'Today';
    $passed_str = 'Event passed';
    $sale_msg = 'Ticket sales will open on September 1st';
}

if (date('Ymd') == $event_date) {
    $today_sticker = '<div class="today-sticker">' . $today_str . '</div>';
    $e_class .= ' today';
}

if ($event->is_passed()) {
    $today_sticker = '<div class="today-sticker passed">' . $passed_str . '</div>';
    $e_class .= ' passed';
}

$no_sale = get_field('no_sale');
?>


<li data-post_id="<?php echo $post_id; ?>" class="loop-event loop-serie-event <?php echo $e_class; ?>">

    <?php if (!$no_sale): ?>
        <a href="<?php echo $event->get_purchase_link(true); ?>" class="overlay_link"></a>
    <?php endif; ?>

    <?php echo $today_sticker; ?>

    <span class="location">
        <span class="hide-mobile"><?php echo $event->get_city(); ?></span>
        <span class="hide-pc"><?php if ($no_sale){ echo (ICL_LANGUAGE_CODE == 'en') ? 'Tickets' : 'כרטיסים';} else  {  echo $no_sale; } ?></span>
    </span>

    <div>
        <p class="date"><?php echo $event->get_date(); ?></p>
        <p class="d-flex">
            <span>
                <?php
                if (ICL_LANGUAGE_CODE == 'en') {
                    switch ($event_day) {
                        case 'א': $event_day = 'Sunday'; break;
                        case 'ב': $event_day = 'Monday'; break;
                        case 'ג': $event_day = 'Tuesday'; break;
                        case 'ד': $event_day = 'Wednesday'; break;
                        case 'ה': $event_day = 'Thursday'; break;
                        case 'ו': $event_day = 'Friday'; break;
                        case 'ש': $event_day = 'Saturday'; break;
                    }
                } else {
                    switch ($event_day) {
                        case 'א': $event_day = 'יום ראשון'; break;
                        case 'ב': $event_day = 'יום שני'; break;
                        case 'ג': $event_day = 'יום שלישי'; break;
                        case 'ד': $event_day = 'יום רביעי'; break;
                        case 'ה': $event_day = 'יום חמישי'; break;
                        case 'ו': $event_day = 'יום שישי'; break;
                        case 'ש': $event_day = 'שבת'; break;
                    }
                }
                echo $event_day;
                ?>
            </span>
            <span><?php echo $event->get_time(); ?></span>
            <span class="hide-pc location_mobile" style="padding-right: 0.8rem; padding-left: 0.8rem;"><?php echo $event->get_city(); ?></span>
        </p>

        <p>
            <span class="hide-mobile" style="border: none!important; margin-right: 0px!important; margin-left: 0px!important; border-radius: 25px; background: #e5e5e5;padding: 5px 15px; display: inline-block; margin-top: 10px;">
                <?php echo $no_sale ? $sale_msg : ((ICL_LANGUAGE_CODE == 'en') ? 'Order Tickets' : 'לרכישת כרטיסים'); ?>
            </span>
        </p>
    </div>
</li>