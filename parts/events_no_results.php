<div class="events-no-results"  data-aos="fade-in" data-aos-duration="750" data-aos-delay="750">
	<div class="events-no-results_in">
		

	<?php 
		
		global $theme;

 echo '<img src="https://www.ipo.co.il/wp-content/uploads/2023/07/violin_case_illustration.svg">';
		$theme->tf('calendar_horizontal_events_no_results');
	
	?>


<?php
$lang = get_locale(); // מזהה את השפה הנוכחית של האתר
?>

<a class="next_event aos-init aos-animate" href="#" target="_self" data-aos="fade-in" data-aos-duration="500" data-aos-delay="500">
    <?php if ($lang === 'en_US') : ?>
        Show the nearest concert
    <?php else : ?>
         
הציגו את הקונצרט הכי קרוב
    <img src="<?php echo ipo_arrow_icon_url(); ?>" alt="">    
<?php endif; ?>

</a>
</div>
	</div>