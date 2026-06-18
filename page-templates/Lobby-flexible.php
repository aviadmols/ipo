<?php
/*
Template Name: Lobby - Flexible

*/


get_header();



if(has_flexible('page_builder')):
    
    the_flexible('page_builder');
    
endif;

get_footer();