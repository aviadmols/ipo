<?php 

if(!isset($closest_event))
    $closest_event = '';
  // Reorder by date
global $theme;

$order_btn_str = '';
if( get_field('Russian_lang') ) {
    $order_btn_str = 'Купить билеты';
    $order_btn_str_mobile = 'Купить билеты'; 
} else {
    
    if (ICL_LANGUAGE_CODE == 'he') {
        $order_btn_str = 'בחירת תאריכים';
        $order_btn_str_mobile = 'לבחירה';
    } else {
        $order_btn_str = 'Order tickets';
        $order_btn_str_mobile = 'Tickets';
    }
}


$program = new ipo_program(get_the_ID());
            $reordered = [];
$events = $program->get_events();
            foreach($events as $event_id){
                
                $event = new ipo_event($event_id);
                $date = $event->get_date();
                $reordered[] = ['date' => $date, 'event' => $event];
            }

            // Reorder from old to new
            usort($reordered, function($a, $b) {
                //echo $a['date'] . ' - ' . $b['date'] . ' = ' . ($a['date'] <=> $b['date']) ;
                $date_a = $a['date'];
                $date_b = $b['date'];
                // Turn . into -
                $date_a = str_replace('.', '-', $date_a);
                $date_b = str_replace('.', '-', $date_b);
                // Take the last number and add 20 to it
                $date_a = substr($date_a, 0, -2) . '20' . substr($date_a, -2);
                $date_b = substr($date_b, 0, -2) . '20' . substr($date_b, -2);
                // strtotime
                $date_a = strtotime($date_a);
                $date_b = strtotime($date_b);
                return $date_a <=> $date_b;
            });

    $end_date = end($reordered);
                      $date_end_date =  $end_date['date'];

      $start_date = $reordered[0];
                      $start_date  =  $start_date['date'];
                     
                   

?>

<!-- =============== Order tickets area start =============== -->
<section class="order_area pt-25 pb-25">
    <div class="container max-1440">
        <div class="row align-items-center space-between">

            <?php 
                /*
                $closest_event = get_posts([
                    'post_type' => 'event',
                    'posts_per_page' => 1,
                    'meta_key' => 'event_date_time',
                    'orderby' => 'meta_value',
                    'order' => 'ASC',
                    'fields' => 'ids',
                    'meta_query' => [
                        [
                            'key' => 'event_date_time',
                            'value' => date('Y-m-d H:i:s'),
                            'compare' => '>=',
                        ],
                        // [
                        //     'key' => 'ipo_api_select_event',
                        //     'value' => $program_id,
                        // ]
                    ],
                    'suppress_filters' => false,
                ]);
                */
                
                if($closest_event){

                    if(is_array($closest_event))
                        $closest_event = $closest_event[0];

                    if(is_object($closest_event)){
                        $event_obj = $closest_event;
                        $closest_event = $closest_event->post->ID;
                    }

if (isset($_GET['event_id'])) {
    $closest_event = $_GET['event_id'];
$start_date = 0;
$date_end_date = 0;
}
                    if(!isset($event_obj))
                        $event_obj = new ipo_event($closest_event);

                    
                        
                    $date = get_field('event_date_time',$closest_event);
                    $date = date_i18n('Y-m-d H:i:s',strtotime($date));

                    //$permalink = get_the_permalink($closest_event);
                    $permalink = $event_obj->get_purchase_link(); 

                    $location = get_field('imported_location',$closest_event);

                    $location = explode(',',$location);

                    // get the last location
                    $location = end($location);

                    $title = '';
 
                   //$title .= get_the_title();

                    $month = date_i18n('m',strtotime($date));
                    $day = date_i18n('d',strtotime($date));

                $day_name = date_i18n('l', strtotime($date));

$days_ru = [
    'Sunday' => 'Вос',
    'Monday' => 'Пн',
    'Tuesday' => 'Вто',
    'Wednesday' => 'Сре',
    'Thursday' => 'Чет',
    'Friday' => 'Пят',
    'Saturday' => 'Суб', 
];

if( get_field('Russian_lang') ) {
    $day_name = array_key_exists($day_name, $days_ru) ? $days_ru[$day_name] : $day_name;
}


                    $time = date_i18n('H:i',strtotime($date));
if($start_date != $date_end_date){
   $title .= ' <span class="first">'.$start_date.' - '.$date_end_date.'</span>';
}else{  
         $title .= ' <br><span class="first">'.$day.'/'.$month.'</span> <span class="day_name">'.$day_name.'</span><span>'.$time.'</span><span class="m_0">'.$location.'</span>';
  }     



                }



            ?>
            
            <?php if($closest_event) : ?>


         <?php if($start_date != $date_end_date): ?>
                <div class="" data-closest-event="<?php echo $closest_event;?>">
                    <div>
                        <h2> <?php echo $title;?> </h2>
                    </div>
                </div>
                <div class="btn-order-area">
        
<a class="btn" href="#time_zone"><span class="hide-mobile"><?php echo  $order_btn_str; ?></span> <span class="hide-pc"><?php echo $order_btn_str_mobile; ?></span></a>
                     </div>
            <?php endif; ?>
          
                  <?php if($start_date == $date_end_date): ?>
                <div class="" data-closest-event="<?php echo $closest_event;?>">
                    <div>
                        <h2> <?php echo $title;?> </h2>
                    </div>
                </div>
                <div class="btn-order-area">
               <?php echo $permalink;?>   </div>
            <?php endif; ?>
          
            <?php endif; ?>

        </div>
    </div>
</section>
<!-- =============== Order tickets area end =============== -->