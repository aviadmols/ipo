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

/* ==========================================================================
   Upcoming programs — ordering by the nearest date
   ========================================================================== */

/**
 * Turn an ACF post reference into a post ID.
 *
 * ACF relationship fields hand back IDs, numeric strings or WP_Post objects
 * depending on how the field is configured, so normalize before using it.
 *
 * @param mixed $value
 * @return int Post ID, or 0 when the value is not a post reference.
 */
if ( ! function_exists( 'ipo_normalize_post_id' ) ) {
	function ipo_normalize_post_id( $value ) {

		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		if ( $value instanceof WP_Post ) {
			return (int) $value->ID;
		}

		if ( is_array( $value ) && isset( $value['ID'] ) ) {
			return (int) $value['ID'];
		}

		return 0;
	}
}

/**
 * The earliest event in a list that has not happened yet.
 *
 * Uses time() so it agrees with ipo_event::is_passed() and with the date
 * filtering inside parts/loop-program.php — all three read the naive
 * 'Y-m-d H:i:s' ACF value through strtotime() in the same timezone.
 *
 * @param array $event_ids
 * @return int|false Unix timestamp, or false when every event is in the past.
 */
if ( ! function_exists( 'ipo_get_next_event_timestamp' ) ) {
	function ipo_get_next_event_timestamp( $event_ids ) {

		$now  = time();
		$next = false;

		foreach ( (array) $event_ids as $event_id ) {

			$event_date_time = get_field( 'event_date_time', $event_id );
			if ( ! $event_date_time ) {
				continue;
			}

			$timestamp = strtotime( $event_date_time );
			if ( ! $timestamp || $timestamp < $now ) {
				continue;
			}

			if ( false === $next || $timestamp < $next ) {
				$next = $timestamp;
			}
		}

		return $next;
	}
}

/**
 * Order programs by their nearest upcoming date, dropping the ones that are over.
 *
 * A program that runs on several dates is placed by its FIRST date that is still
 * ahead — so once that date passes the program falls back to its next one and the
 * list re-orders itself, with no editing needed.
 *
 * Events are read through get_related_event_ids(), the same source
 * parts/loop-program.php renders from, so a card's position in the list and the
 * first date printed on the card can never disagree.
 *
 * @param array $programs Program IDs or ACF post references.
 * @return array Program IDs, nearest date first.
 */
if ( ! function_exists( 'ipo_sort_programs_by_next_event' ) ) {
	function ipo_sort_programs_by_next_event( $programs ) {

		$dated    = array();
		$undated  = array();
		$position = 0;

		foreach ( (array) $programs as $program ) {

			$program_id = ipo_normalize_post_id( $program );
			if ( ! $program_id ) {
				continue;
			}

			$event_ids = get_related_event_ids( $program_id );

			// No events at all (an artist_plan, for instance) — there is nothing to
			// sort it by, so keep it and let it sit after everything that has a date.
			if ( empty( $event_ids ) ) {
				$undated[] = $program_id;
				continue;
			}

			$next = ipo_get_next_event_timestamp( $event_ids );

			// It has dates, but all of them are behind us — this is the one case we drop.
			if ( false === $next ) {
				continue;
			}

			$dated[] = array(
				'id'    => $program_id,
				'next'  => $next,
				'order' => $position++, // keeps the original order for same-date programs
			);
		}

		usort(
			$dated,
			function( $a, $b ) {
				if ( $a['next'] === $b['next'] ) {
					return $a['order'] <=> $b['order'];
				}
				return $a['next'] <=> $b['next'];
			}
		);

		return array_merge( array_column( $dated, 'id' ), $undated );
	}
}
