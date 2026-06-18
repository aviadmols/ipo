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

// 13/3/2023 - sagi kupesok - changed format, removed year from date by request
$event_date_str = date_i18n('d/m',$event_date);


$program = new ipo_program($event->get_program());


$days_ru = [
    'Sun' => 'Вс',
    'Mon' => 'Пн',
    'Tue' => 'Вт',
    'Wed' => 'Ср',
    'Thu' => 'Чт',
    'Fri' => 'Пт',
    'Sat' => 'Сб',
    'א' => 'Вс',
    'ב' => 'Пн',
    'ג' => 'Вт',
    'ד' => 'Ср',
    'ה' => 'Чт',
    'ו' => 'Пт',
    'ש' => 'Сб',
];

$days_he = [
    'Sun' => 'יום א׳',
    'Mon' => 'יום ב׳',
    'Tue' => 'יום ג׳',
    'Wed' => 'יום ד׳',
    'Thu' => 'יום ה׳',
    'Fri' => 'יום ו׳',
    'Sat' => 'יום שבת',
];

if (get_field('Russian_lang')) {
    $event_day_str = $days_ru[date('D', $event_date)] ?? '';
} elseif (ICL_LANGUAGE_CODE == 'he') {
    $event_day_str = $days_he[date('D', $event_date)] ?? '';
} else {
    $event_day_str = date('D', $event_date);
}

$program_length_concert = $program->gf('program_length_concert');




$strings_to_remove = [
	'ILS',
	'ils',
	'nis',
	'NIS',
	'₪',
	'ש"ח',
];

  
$event_price_range = $event->gf('event_price_range');


// $event_price_range = str_replace($strings_to_remove,'',$event_price_range);

// add shekel sign
if($event_price_range)
  if(ICL_LANGUAGE_CODE == 'he'){
	$event_price_range = $event_price_range . '';
 } else {

    $event_price_range = $event_price_range . '';
    }

// add passed class if event is in the past
$passed_class = '';
if($event->is_passed()){
	$passed_class = 'passed';
}

$hall = $event->gf('imported_hall');



?>
<?php if(!$event->is_passed()): ?>
<!-- <?php echo $event_day_str; ?> -->      
<div id="event-<?php echo $post_id; ?>" class="row align-items-center loop-event time-horizontal space-between <?php echo $passed_class;?>" data-post_id="<?php echo $post_id; ?>" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay=250">
               
	<div class="col-max-width-250">
		<div class="row">

			<div class="concert-headline">
				<h2 class="hide-mobile">
					                                      
                 <?php  if ($event_date_str):?>
					<span class="event_date_str"><?php echo $event_date_str; ?></span> 
 <?php  endif;  ?>
                                          <?php  if ($event_day_str):?>
					<span class="event_day"><?php echo $event_day_str; ?></span>
                                          <?php  endif;  ?>
                                       <?php  if ($event->get_time()):?>
					<span class="event_time"><?php echo $event->get_time(); ?></span> 
                                          <?php  endif;  ?>
  <?php  if ($event->get_city()):?>
					<span class="event_city"><?php echo $event->get_city();?></span>
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
					<span><?php echo $event->get_time(); ?></span> | 
                                          <?php  endif;  ?>
                                          <?php  if ($event_day_str):?>
					<span><?php echo $event_day_str; ?></span>
                                          <?php  endif;  ?>
                                   
  <?php  if ($event->get_city()):?>
                                       <br>

					<span class="city_time"><?php echo $event->get_city();?></span>

<?php $btntext = get_field('btntext', $post_id); if ($btntext): ?>
    <span class="btntext-note-mobile"><?php echo $btntext; ?></span>
<?php endif; ?>

                                        <?php  endif;  ?>


					<?php if($program_length_concert): ?>
                                       
					<span class="icon_box" style="font-weight: 400; font-size: 13px; display: block; margin-top: 6px; min-width: 100%;display: flex;">
												<?php echo $program_length_concert; ?> 
					</span>
					<?php endif; ?>

 <?php
                                                                                                                                                     $btntext = get_field('btntext', $post_id);

$sold_out = get_field('sold_out', $post_id);
  
  
  if ($sold_out){
echo '<span>'. $sold_out .' </span>';
  } ?>
                       		</p>
                                                           </div>
				
	
                      </div>
			<div class="div-infoconcert ">
				<div class="d-flex align-items-bottom time_zone_flex">
					<?php if($event->get_location()): ?>

						<?php 
							/*
							if($hall){
								$hall = '<span class="hall">' . $hall . '</span>';
							}
							*/
							
						?>


					<div class="icon_box location_box">
						<div class="icon">
							<?php $theme->the_asset('icons_location.svg'); ?>
						</div>
						<p>	
							<?php 
							echo $event->get_event_location_icon_str();
							/*
							<span class="location"><?php $location_str; ?></span>
							<?php echo $hall; ?>
							*/

							?>
						</p>
						
					</div>
					<?php endif;?>

					<?php if($program_length_concert): ?>
					<div class="icon_box hide-mobile">
						<div class="icon">
							<img src="/wp-content/uploads/2022/08/icons_time-2x-3-e1661392161999.png" class="img-fluid" alt="">
						</div>
						<p><?php echo $program_length_concert; ?></p>
					</div>
					<?php endif; if($event_price_range): ?>
					<div class="icon_box  hide-mobile">
						<div class="icon">
							<img src="/wp-content/uploads/2022/08/icons_price-2x-3-e1661392095708.png" class="img-fluid" alt="">
						</div>
						<p><?php echo $event_price_range; ?></p>
					</div>
					<?php endif; ?>
                                                                                                                               
                                                                                                                               
  <?php if($event_price_range): ?>
					<div class="icon_box  hide-pc">
						<div class="icon">
							<img src="/wp-content/uploads/2022/08/icons_price-2x-3-e1661392095708.png" class="img-fluid" alt="">
						</div>
						<p><?php echo $event_price_range; ?></p>
					</div>
					<?php endif; if($program_length_concert): ?>
					<div class="icon_box  hide-pc">
						<div class="icon">
							<img src="/wp-content/uploads/2022/08/icons_time-2x-3-e1661392161999.png" class="img-fluid" alt="">
						</div>
						<p><?php echo $program_length_concert; ?></p>
					</div>
               
	<?php endif; ?>
				</div>
			</div>

		
		</div>
	</div>
<div class="text-left <?php if(get_field('Hiding_purchase')) { echo 'Hiding_purchase'; } ?> <?php if(get_field('btntext')) { echo 'has-btntext'; } ?>" 
style="text-align: left!important; <?php if($btntext) { echo 'display:grid;'; } ?>" data-t="2">



		<?php echo $event->get_purchase_link(); ?>
<?php if ($btntext): ?>
    <span class="btntext-note"><?php echo $btntext; ?></span>
<?php endif; ?>

<?php   $donation_event = get_field('donation_event',$post_id);
 if($donation_event != ''): ?>
        <div class=""  data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" style="margin-top: 5px;">
			<p style="text-align: center;font-weight: 500;">
				<?php echo $donation_event; ?>
				
			</p>
		</div>
	<?php endif; ?>
	</div>

</div>

<?php endif; ?>