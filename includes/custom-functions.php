<?php 
function get_youtube_video_ID($youtube_video_url) {
	preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_video_url, $match);
	$youtube_id = $match[1];
	return $youtube_id;
}

add_shortcode( 'toggle_link', 'special_project_toggle_shortcode' );
function special_project_toggle_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'link_text' => 'no foo',
        'link_content' => 'default baz',
        'content_tag' => 'p',
    ), $atts, 'toggle_link' );
	

	
 
    return "
	<div class='toggle_link'>
		<a href='#'>
			{$atts['link_text']} 
			<i class='fa fa-angle-left' aria-hidden='true'></i>
			<i class='fa fa-angle-down' aria-hidden='true'></i>
		</a>
		<{$atts['content_tag']} class='toggle-content'>{$atts['link_content']}</{$atts['content_tag']} >
	</div>";
}

function get_video($url,$args = array()){
    //echo $url;
    
    if(isset($args['width'])) $width = $args['width']; else $width = 360;
    if(isset($args['height'])) $height = $args['height']; else $height = 260;
    if(isset($args['attributes'])) $attributes = $args['attributes']; else $attributes = "frameborder='0' allowfullscreen";
    
    $video_type = videoType($url);
    if($video_type=='youtube'){

        if(isset($args['parameters'])) $parameters = $args['parameters']; else $parameters = '?rel=0&loop=1';
        $id = get_youtube_video_ID($url);
        
        return "
           <iframe width='$width' height='$height' src='https://www.youtube.com/embed/$id$parameters' $attributes ></iframe>
        ";
        
    } else if ($video_type=='medici'){
        
        return "
           <iframe width='$width' height='$height' src='$url' $attributes ></iframe>
        ";

    }
    
}

 
function videoType($url) {
    if (strpos($url, 'youtube') > 0) {
        return 'youtube';
    } elseif (strpos($url, 'vimeo') > 0) {
        return 'vimeo';
    } elseif (strpos($url, 'medici.tv') > 0) {
		return 'medici';
    } else {
        return 'unknown';
    }
}

class submenu_wrap extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        
        global $menu_id;

        $bg_css = '';
        $bg_class = '';     

        $icon = get_field('icon', $menu_id);
        $layout = get_field('layout', $menu_id);

        if($depth == 0 && $icon) {
            $item_image = new wpstack_image($icon);
            $output .= "<ul class='sub-menu layout-".$layout."'><div class='container' ><li class='menu-item menu-item-image'><div class='menu-image-container'>".$item_image->get_bg_img()."</div></li>";
        } else {
            $output .= '<ul class="sub-menu">';
        }

        
    }
    function end_lvl( &$output, $depth = 0, $args = array() ) {

        global $menu_id;

        $icon = get_field('icon', $menu_id);

        if($depth == 0 && $icon) {
            $output .= "</div></ul>";
        } else {
            $output .= '</ul>';
        }
        
    }
}

function add_menu_id( $item_output, $item, $depth, $args ) {
    global $menu_id;
    $menu_id = $item->ID;
    return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'add_menu_id', 10, 4);


function ipo_get_banner_image($id = false,$size = 'large') {
    if(!$id) {
        $id = get_the_ID();
    }
    $bannerImage = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $size );
    return $bannerImage;
}



function wpml_make_post_duplicate_language( $master_post_id, $lang_to ) {
    global $sitepress;
    $master_post = get_post( $master_post_id );
    if ( 'auto-draft' === $master_post->post_status || 'revision' === $master_post->post_type ) {
        return;
    }
    $active_langs = $sitepress->get_active_languages();
    if (array_key_exists($lang_to, $active_langs)) {
      $trid      = $sitepress->get_element_trid( $master_post->ID, 'post_' . $master_post->post_type );
      $lang_from = $sitepress->get_source_language_by_trid( $trid );
      if ( $lang_from == $lang_to ) {
          return;
      }
      $sitepress->make_duplicate( $master_post_id, $lang_to );
    }
  }
  add_action( 'wpml_make_post_duplicate_lang', 'wpml_make_post_duplicate_language', 10, 2 );