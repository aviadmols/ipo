<?php

global $theme;

if(!isset($month)){
	
	if(isset($_GET['month']))
		$month = sanitize_text_field($_GET['month']);
	else 
		$month = date('m');
	
}

if(!isset($year)){
	
	if(isset($_GET['y']))
		$year = sanitize_text_field($_GET['y']);
	else 
		$year = date('Y');
	
}

if(!isset($layout)){
	
	if(isset($_GET['layout']))
		$layout = sanitize_text_field($_GET['layout']);
	else 
		$layout = 'normal';
	
}

if($layout == 'normal'){
	$switch = 0;
}else{
	$switch = 1;
}

$calendar = new ipo_calendar();
$days = $calendar->get_days_in_slots_html('loop-calendar-full-day',$month,$year);


$year = 'today';
$month = 'today';

if(isset($_GET['month'])){
	$month = intval(sanitize_text_field($_GET['month']));
}

if(isset($_GET['y'])){
	$year = intval(sanitize_text_field($_GET['y']));
}

$list_events_html = $calendar->get_events_html(array('month'=>$month,'year'=>$year));


$month_start_day = '';
$month_end_day = '';

//print_r($year);
if($year == 'today'){
	$year = date('Y');
}
if($month == 'today'){
	$month = date('m');
}


$prev_month = $month - 1;
$next_month = $month + 1;


$next_year = $year;
$prev_year = $year;

if($prev_month == 0){
	$prev_month = 12;
	$prev_year = $year - 1;
}

if($next_month == 13){
	$next_month = 1;
	$next_year = $year + 1;
}



$prev_month_url = '?month='.$prev_month.'&y='.$prev_year;
$next_month_url = '?month='.$next_month.'&y='.$next_year;

$current_date = date_i18n('M',strtotime('01-'.$month.'-'.$year)) . ' ' . $year;

$event_type = get_terms(array(
	'taxonomy' => 'event_type',
	'hide_empty' => true,
));

$location = get_terms(array(
	'taxonomy' => 'location',
	'hide_empty' => 1,
));
?>
 
<div class="container">

<div class="calendar-full ipo-calendar" data-calendar-type="<?php echo $layout; ?>">

		<div class="calendar-header">
			
			<div class="current-month">
				<a class="prev" data-month="<?php echo $prev_month;?>" data-year="<?php echo $prev_year;?>" href="<?php echo $prev_month_url; ?>"><i class="arrow-right"></i></a>
				<span class="date">
					<?php echo $current_date; ?>
				</span>
				<a class="next" data-month="<?php echo $next_month;?>" data-year="<?php echo $next_year;?>" href="<?php echo $next_month_url; ?>"><i class="arrow-left"></i></a>
			</div>
			
			<div class="calendar-layout-controls">
				<div class="switch-container" data-selected-option="<?php echo $switch;?>">
					<a class="switch-option">לוח שנה</a>
					<a class="switch-option">רשימה</a>
				</div>
			</div>

		</div>
		
		<ul class="calendar-days">
			
			<?php 
			
			foreach($days as $day){
				echo $day;
			}
			
			?>

			
		</ul>
		
		<div class="calendar-events">
			
			<div class="calendar-events-header">
				
				<div class="search-field">
					<i class="icon icon-search"><?php $theme->the_asset('icons_search.svg'); ?></i>
					<input type="text" placeholder="חיפוש לפי קונצרט, אמן, יצירה, מלחין">
				</div>
				
				<div class="filters">
				
					<div class="filter-type filter">

						<a class="open-filter" data-trigger> 
							<i class="icon icon-filter"><?php $theme->the_asset('filter.svg'); ?></i>
							<span>כל הסוגים</span>
						</a>
						<div class="panel">
							<ul class="options">

								<li class="active"><a data-filter-val="all">הכל</a></li>
								<?php 
									foreach($event_type as $type){
										echo '<li><a data-filter-val="'.$type->term_id.'">'.$type->name.'</a></li>';
									}
								?>
							</ul>
							<a class="close">
								<i class="icon-close">X</i>
							</a>
						</div>
					</div>
					<div class="filter-location filter">
						<a class="open-filter" data-trigger> 
							<i class="icon icon-filter"><?php $theme->the_asset('location.svg'); ?></i>
							<span>כל המיקומים</span>
						</a>
						<div class="panel">
							<ul class="options">
								<li class="active"><a data-filter-val="all">הכל</a></li>
								<?php 
									foreach($location as $loc){
										echo '<li><a data-filter-val="'.$loc->term_id.'">'.$loc->name.'</a></li>';
									}
								?>
							</ul>
							<a class="close">
								<i class="icon-close">X</i>
							</a>
						</div>
					</div>
					
				</div>
				
			</div>
			
			<div class="calendar-events-body">
				<div class="inner-wrapper">
					<ul class="events">
						<?php foreach( $list_events_html as $event) {
							
							echo $event;
							
						} ?>
					</ul>

					<div class="no-results">
						<?php the_field('calendar_horizontal_events_no_results', 'option'); ?>
					</div>
				</div>
			</div>
			
		</div>
		
</div>



</div>