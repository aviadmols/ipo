<?php

/* Template Name: Calendar  */ 

get_header();

global $theme;

?>

<section class="first-section">
	<?php $theme->the_part('calendar-full'); ?>
</section>

<!-- =============== Order tickets area end =============== -->

<?php get_footer();?>


<script>
$('header').addClass('sticky');


$(function() {
    var header = $("header");

    $(window).scroll(function() {
        var scroll = $(window).scrollTop();
        if (scroll >= 0) {
            header.addClass("sticky");
        } 
    });
});
</script>