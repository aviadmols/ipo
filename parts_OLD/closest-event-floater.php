<?php 

if(!isset($closest_event))
    $closest_event = '';

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

                    $title .= get_field('event_featured_name',$closest_event);

                    $month = date_i18n('m',strtotime($date));
                    $day = date_i18n('d',strtotime($date));

                    $day_name = date_i18n('l',strtotime($date));


                    $time = date_i18n('H:i',strtotime($date));

                    $title .= ' <br><span>'.$day.'/'.$month.'</span> <span>'.$day_name.'</span><span>'.$time.'</span><span class="m_0">'.$location.'</span>';



                }



            ?>
            
            <?php if($closest_event) : ?>
                <div class="" data-closest-event="<?php echo $closest_event;?>">
                    <div>
                        <h2> <?php echo $title;?> </h2>
                    </div>
                </div>
                <div class="btn-cta">
                  <?php echo $permalink; ?>
                           </div>
            <?php endif; ?>

        </div>
    </div>
</section>
<!-- =============== Order tickets area end =============== -->