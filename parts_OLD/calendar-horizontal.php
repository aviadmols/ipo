<?php

if(!isset($month))
	$month = 'today';

$calendar = new ipo_calendar();
$days = $calendar->get_days_html('loop-calendar-horizontal-day',$month);

/*
$events = $calendar->get_events(array('day'=>'17'));
print_r($events);
//$events = $calendar->get_events();
foreach($events as $event){
	//echo get_field('event_date',$event).'<br>';
}
*/


?>
 
 
<div class="calendar-row splide ipo-calendar" data-splide='{"type":"slider","perPage":8,"direction":"rtl","speed":500}'>
	<div class="splide__track">
		<ul class="numCount splide__list">
			
			<?php 
			
			foreach($days as $day){
				echo $day;
			}
			
			?>
			<!--
			<li class="active">10</li>
			<li>11</li>
			<li><span>12</span></li>
			<li>13</li>
			<li><span>14</span></li>
			<li>15</li>
			<li>16</li>
			<li>17</li>
			-->
			
		</ul>
	</div>
<!-- <a href="#"><img src="/wp-content/uploads/2022/06/left-arrow.png" alt=""></a> -->
</div>
