<?php 

/*

* Template Name: Template - serie

*/


get_header();

global $theme;

?>


<div class="main_body">

  <div class="">
  
	<div class="row series-banner">	
		<?php $theme->the_component('series-banner'); ?>
	</div>
  
	<div class="row series-content">	
		<?php $theme->the_component('series-content'); ?>
	</div>
	
    <div class="row series-videos">	
		<?php $theme->the_component('series-videos'); ?>
	</div>

  </div>
</div>

<?php get_footer(); ?>
