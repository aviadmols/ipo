<?php 

global $theme;

$calendar_page = $theme->gf('calendar_page');
if($calendar_page) $calendar_page = get_permalink($calendar_page);
else $calendar_page = "#";

$calendar_title = get_field('calendar_title');


$calendar_link_label = 'ללוח הקונצרטים המלא';
// if WPML const is defined and is english
if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en'){
    $calendar_link_label = 'Full Calendar';
}

?>

<section class="calendar_area section-view pt-75 pb-75" data-load-on-view>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="title_box">
                            <div class="d-flex">
							    <div class="title style-1 pb-75" style="align-items: end;">

                                    <?php if($calendar_title) : ?>
                                        <h2 class="lette-sapce-10  ml10 animate-letters"><?php echo $calendar_title;?> </h2>
                                    <?php endif;?>

                                    <div class="date ajax-get-month-trigger"  data-aos="fade-in" data-aos-duration="1000" data-aos-delay="1000">
                             	   
                                    <a href="#" class="prev trigger"><img src="<?php echo ipo_arrow_icon_url(); ?>" alt="" style=" transform: rotate(180deg);"></a>
                                    <?php $calendar = new ipo_calendar(); echo $calendar->get_rendered_date('today'); ?>
									       <a href="#" class="next trigger"><img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>
									
                                </div>
                                </div>  
							    
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="wrapper ajax-calendar-row"  data-aos="fade-in" data-aos-duration="750" data-aos-delay="750">
                            <?php $theme->the_part('calendar-horizontal'); ?>
                        </div>
                    </div>
                </div>     
  
                <?php $theme->the_part('calendar-horizontal-events'); ?>


                
                <a class="schedule" data-full-calendar-link href="<?php echo esc_url( $calendar_page ); ?>" ><?php echo $calendar_link_label; ?> <img src="<?php echo ipo_arrow_icon_url(); ?>" alt=""></a>


            </div>
        </section>