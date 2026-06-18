<?php

// Write WP PHP filter to change permalink of post type 'event' to '#'

add_filter('post_type_link', 'ipo_event_permalink', 1, 3);
function ipo_event_permalink($url, $post, $leavename) {
    if ($post->post_type == 'event'){
        
        $ipo_event = new ipo_event($post->ID);
        $program_id = $ipo_event->get_program();

        if(!$program_id)
            return '#'. $post->ID;

        $program = get_post($program_id);
        $slug = $program->post_name;
        
        // Check the post language
        $lang = apply_filters( 'wpml_post_language_details', NULL, $post->ID );
        if(isset($lang['language_code']) && $lang['language_code'] != 'he'){
            $lang = $lang['language_code'];
        } else {
            $lang = '';
        }
        

        //$program_link = get_site_url() . $lang . '/program/' . $slug;
        $program_link = get_permalink($program_id);
        

        //echo 'getting permalink for program '.$program.': '.$program_link;
        //die();

        //$url = get_field('event_api_id', $post->ID);
        $url = $program_link.'?event_id='.$post->ID;
       
    }
        
    return $url;
}
