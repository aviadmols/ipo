<?php
/**
 * The related-programs module on a single program page ("You may also like").
 *
 * Everything the module shows is decided here, from one global settings screen
 * under the Event Table menu. The template only renders what this file returns.
 *
 * Before this existed the template picked its own list: a manual ACF field if
 * the program had one, otherwise a category query with no orderby — which fell
 * to WordPress's post_date DESC default, so the cards came out in the order the
 * posts happened to be created rather than by concert date.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_RELATED_PROGRAMS_OPTION = 'ipo_related_programs_settings';

/**
 * Settings as stored, with every key guaranteed present.
 */
function ipo_related_programs_defaults() {
	return array(
		'mode'                 => 'same_category', // same_category | categories | manual
		'categories'           => array(),
		'manual_ids'           => array(),
		'promoted_ids'         => array(),
		'exclude_ids'          => array(),
		'require_future_event' => 1,
		'max_items'            => 0, // 0 = no limit
		'order'                => 'date_asc', // date_asc | date_desc
	);
}

function ipo_related_programs_get_settings() {
	$saved = get_option( IPO_RELATED_PROGRAMS_OPTION, array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$settings = array_merge( ipo_related_programs_defaults(), $saved );

	foreach ( array( 'categories', 'manual_ids', 'promoted_ids', 'exclude_ids' ) as $list ) {
		$settings[ $list ] = array_values( array_filter( array_map( 'intval', (array) $settings[ $list ] ) ) );
	}

	$settings['require_future_event'] = (int) ! empty( $settings['require_future_event'] );
	$settings['max_items']            = max( 0, (int) $settings['max_items'] );

	if ( ! in_array( $settings['mode'], array( 'same_category', 'categories', 'manual' ), true ) ) {
		$settings['mode'] = 'same_category';
	}

	if ( ! in_array( $settings['order'], array( 'date_asc', 'date_desc' ), true ) ) {
		$settings['order'] = 'date_asc';
	}

	return $settings;
}

/**
 * event_date_time is stored in two shapes: most rows carry seconds
 * ("2026-11-08 17:30:00") but a few hundred do not ("2026-11-08 17:30").
 * createFromFormat with a fixed 'Y-m-d H:i:s' returns false on the short ones,
 * which silently dropped those events from every future-date check.
 */
function ipo_parse_event_datetime( $value ) {
	if ( ! is_string( $value ) ) {
		return null;
	}

	$value = trim( $value );

	if ( $value === '' ) {
		return null;
	}

	foreach ( array( 'Y-m-d H:i:s', 'Y-m-d H:i' ) as $format ) {
		$parsed = DateTime::createFromFormat( $format, $value );
		if ( $parsed instanceof DateTime ) {
			return $parsed;
		}
	}

	return null;
}

/**
 * Timestamp of a program's soonest event that has not passed, or null.
 */
function ipo_related_programs_next_event_timestamp( $program_id ) {
	if ( ! function_exists( 'get_related_event_ids' ) ) {
		return null;
	}

	$event_ids = get_related_event_ids( $program_id );

	if ( ! is_array( $event_ids ) || empty( $event_ids ) ) {
		return null;
	}

	$now  = new DateTime();
	$next = null;

	foreach ( $event_ids as $event_id ) {
		$event_datetime = ipo_parse_event_datetime( get_field( 'event_date_time', $event_id ) );

		if ( ! $event_datetime || $event_datetime < $now ) {
			continue;
		}

		$timestamp = $event_datetime->getTimestamp();

		if ( $next === null || $timestamp < $next ) {
			$next = $timestamp;
		}
	}

	return $next;
}

/**
 * Map IDs saved in the settings onto the language being viewed. The settings
 * are global and stored with whichever IDs the admin picked, so without this a
 * Hebrew pick would never match on the English side of the site.
 */
function ipo_related_programs_translate_ids( $ids, $type = 'program' ) {
	$translated = array();

	foreach ( (array) $ids as $id ) {
		$id = (int) $id;

		if ( ! $id ) {
			continue;
		}

		$mapped       = apply_filters( 'wpml_object_id', $id, $type, true );
		$translated[] = $mapped ? (int) $mapped : $id;
	}

	return array_values( array_unique( $translated ) );
}

/**
 * The pool of programs the rules allow, before dates are considered.
 */
function ipo_related_programs_build_pool( $post_id, $settings ) {
	$pool = array();

	if ( $settings['mode'] === 'same_category' ) {
		$terms = wp_get_post_terms( $post_id, 'category_program', array( 'fields' => 'ids' ) );
		$terms = is_wp_error( $terms ) ? array() : $terms;
	} elseif ( $settings['mode'] === 'categories' ) {
		$terms = ipo_related_programs_translate_ids( $settings['categories'], 'category_program' );
	} else {
		$terms = array();
	}

	if ( ! empty( $terms ) ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'post__not_in'   => array( $post_id ),
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'category_program',
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				),
			)
		);

		$pool = $query->posts;
	}

	// Hand-picked programs join the pool whatever the category rule says.
	$pool = array_merge( $pool, ipo_related_programs_translate_ids( $settings['manual_ids'] ) );

	$excluded   = ipo_related_programs_translate_ids( $settings['exclude_ids'] );
	$excluded[] = (int) $post_id;

	$pool = array_diff( array_map( 'intval', $pool ), $excluded );

	return array_values( array_unique( $pool ) );
}

/**
 * The final, ordered list the template renders.
 *
 * Each entry is array( 'id', 'post_type', 'next_event', 'promoted', 'position' ).
 */
function ipo_related_programs_get_items( $post_id ) {
	$settings = ipo_related_programs_get_settings();
	$pool     = ipo_related_programs_build_pool( $post_id, $settings );
	$promoted = ipo_related_programs_translate_ids( $settings['promoted_ids'] );

	$items    = array();
	$position = 0;

	foreach ( $pool as $program_id ) {
		$program_post = get_post( $program_id );

		if (
			! $program_post ||
			$program_post->post_status !== 'publish' ||
			! in_array( $program_post->post_type, array( 'program', 'artist_plan' ), true )
		) {
			continue;
		}

		$next_event = ipo_related_programs_next_event_timestamp( $program_id );

		// artist_plan carries no event dates of its own and is kept regardless.
		if ( $settings['require_future_event'] && $program_post->post_type === 'program' && $next_event === null ) {
			continue;
		}

		$items[] = array(
			'id'         => (int) $program_id,
			'post_type'  => $program_post->post_type,
			'next_event' => $next_event,
			// A promoted program only jumps the queue if it survived the rules
			// above and still has a date ahead of it.
			'promoted'   => in_array( (int) $program_id, $promoted, true ) && $next_event !== null,
			'position'   => $position++,
		);
	}

	$direction = $settings['order'] === 'date_desc' ? -1 : 1;

	usort(
		$items,
		function ( $a, $b ) use ( $direction ) {
			if ( $a['promoted'] !== $b['promoted'] ) {
				return $a['promoted'] ? -1 : 1;
			}

			// Anything without a date sorts last whichever direction is chosen.
			$a_date = $a['next_event'] === null ? PHP_INT_MAX : $a['next_event'];
			$b_date = $b['next_event'] === null ? PHP_INT_MAX : $b['next_event'];

			if ( $a_date !== $b_date ) {
				if ( $a_date === PHP_INT_MAX || $b_date === PHP_INT_MAX ) {
					return $a_date <=> $b_date;
				}

				return ( $a_date <=> $b_date ) * $direction;
			}

			// position keeps same-date items in their original order.
			return $a['position'] <=> $b['position'];
		}
	);

	if ( $settings['max_items'] > 0 ) {
		$items = array_slice( $items, 0, $settings['max_items'] );
	}

	return $items;
}
