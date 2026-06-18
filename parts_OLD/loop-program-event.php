<?php 

global $theme;

$event = new ipo_event( $post_id );

$date = $event->get_date();
// if is d.m.y is only 2 digits for year, break it down to variables
if(strlen($date) == 8){
	$day = substr($date,0,2);
	$month = substr($date,3,2);
	$year = substr($date,6,2);
	$event_date = strtotime($day.'-'.$month.'-20'.$year);
} else {
	$event_date = strtotime($date);
}

$date_format = get_option( 'date_format' );
//$event_date_str = date_i18n('d/m',$event_date);
$event_date_str = date_i18n($date_format,$event_date);


$program = new ipo_program($event->get_program());


// Check if current WPML language is Hebrew
if(ICL_LANGUAGE_CODE == 'he'){
	$event_day_str = __('יום','ipo') . ' ' . $event->get_day() . "'";
} else {
	$event_day_str = date_i18n('D',$event_date);
}

$program_length_concert = $program->gf('program_length_concert');
//$program_price_range = $program->gf('program_price_range');


$event_price_range = $event->gf('event_price_range');
$strings_to_remove = [
	'ILS',
	'ils',
	'nis',
	'NIS',
	'ש"ח',
	'₪',
];

$event_price_range = str_replace($strings_to_remove,'',$event_price_range);

// add shekel sign
$event_price_range = $event_price_range . ' ₪';



// add passed class if event is in the past
$passed_class = '';
if($event->is_passed()){
	$passed_class = 'passed';
}

$hall = $event->gf('imported_hall');
if($hall){
	$hall = '<span class="hall">' . $hall . '</span>';
}


?>

                
<div id="event-<?php echo $post_id; ?>" class="row align-items-center loop-event time-horizontal space-between <?php echo $passed_class;?>" data-post_id="<?php echo $post_id; ?>" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay=250">
               
	<div class="col-max-width-250">
		<div class="row">

			<div class="concert-headline">
				<h2 class="hide-mobile">
					                                      
                 <?php  if ($event_date_str):?>
					<span><?php echo $event_date_str; ?></span> 
 <?php  endif;  ?>
                                          <?php  if ($event_day_str):?>
					<span><?php echo $event_day_str; ?></span>
                                          <?php  endif;  ?>
                                       <?php  if ($event->get_time()):?>
					<span><?php echo $event->get_time(); ?></span> 
                                          <?php  endif;  ?>
  <?php  if ($event->get_city()):?>
					<span><?php echo $event->get_city();?></span>
                                         <?php  endif;  ?>
				</h2>
                                       
                                       <div class="hide-pc info-prog-mobile">

                                       	<h2 >
					                                      
                 <?php  if ($event_date_str):?>
					<span><?php echo $event_date_str; ?></span> 

 <?php  endif;  ?>
</h2>
	<p class="hide-pc ">
                                 <?php  if ($event->get_time()):?>
					<span><?php echo $event->get_time(); ?></span> 
                                          <?php  endif;  ?> | 
                                          <?php  if ($event_day_str):?>
					<span><?php echo $event_day_str; ?></span>
                                          <?php  endif;  ?>
                             
  <?php  if ($event->get_city()):?>
                                       <br>
					<span><?php echo $event->get_city();?></span>
                                         <?php  endif;  ?>
                       		</p>
                                                           </div>
				
	
                      </div>
			<div class="div-infoconcert ">
				<div class="d-flex align-items-bottom time_zone_flex">
					<?php if($event->get_location()): ?>
					<div class="icon_box location_box">
						<div class="icon">
							<?php $theme->the_asset('icons_location.svg'); ?>
						</div>
						<p>
							<span class="location"><?php echo $event->get_location(); ?></span>
							<?php echo $hall; ?>
						</p>
						
					</div>
					<?php endif;?>

					<?php if($program_length_concert): ?>
					<div class="icon_box">
						<div class="icon">
							<img src="/wp-content/uploads/2022/08/icons_time-2x-3-e1661392161999.png" class="img-fluid" alt="">
						</div>
						<p><?php echo $program_length_concert; ?></p>
					</div>
					<?php endif; if($event_price_range): ?>
					<div class="icon_box">
						<div class="icon">
							<img src="/wp-content/uploads/2022/08/icons_price-2x-3-e1661392095708.png" class="img-fluid" alt="">
						</div>
						<p><?php echo $event_price_range; ?></p>
					</div>
					<?php endif; ?>
				</div>
			</div>

		
		</div>
	</div>

	<div class="text-left" style="text-align: left!important;">
	    <?php if($event->is_passed()): ?>
			<a class="btn disabled" href="#">אירוע עבר</a>
		<?php else: ?>
		<?php echo $event->get_purchase_link(); ?>
		<?php endif; ?>
	</div>

</div>