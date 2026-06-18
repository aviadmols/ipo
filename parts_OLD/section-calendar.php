<?php 

global $theme;

$calendar_page = $theme->gf('calendar_page');
if($calendar_page) $calendar_page = get_permalink($calendar_page);
else $calendar_page = "#";

$calendar_title = get_field('calendar_title');


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

                                    <div class="date ajax-get-month-trigger"  data-aos="fade-in" data-aos-duration="2000" data-aos-delay="2000">
                             	   
                                    <a href="#" class="prev trigger"><img src="/wp-content/uploads/2022/06/left-arrow.png" alt="" style=" transform: rotate(180deg);"></a>
                                    <?php $calendar = new ipo_calendar(); echo $calendar->get_rendered_date('today'); ?>
									       <a href="#" class="next trigger"><img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>
									
                                </div>
                                </div>  
							    
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="wrapper ajax-calendar-row"  data-aos="fade-in" data-aos-duration="2500" data-aos-delay="2500">
                            <?php $theme->the_part('calendar-horizontal'); ?>
                        </div>
                    </div>
                </div>     
  
                <?php $theme->the_part('calendar-horizontal-events'); ?>

                
                <a class="schedule" data-full-calendar-link href="<?php echo esc_url( $calendar_page ); ?>" >ללוח הקונצרטים המלא <img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a>


            </div>
        </section>