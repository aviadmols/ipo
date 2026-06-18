<?php
/**
 * Serie: display upcoming programs by category.
 *
 * Adds a checkbox to the `serie` post type. When enabled, a "קטגוריות תוכניות"
 * (category_program) selector is shown. The front-end (single-serie.php) then
 * displays the upcoming programs from that category instead of the events pulled
 * from the ACF `events` field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the ACF fields on the `serie` post type:
 *  - use_category_programs (checkbox)
 *  - category_programs_term (category_program taxonomy selector, shown when the checkbox is on)
 */
add_action( 'acf/init', 'ipo_register_serie_category_programs_fields' );
function ipo_register_serie_category_programs_fields() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_serie_category_programs',
		'title'    => 'הצגת תוכניות לפי קטגוריה',
		'fields'   => array(
			array(
				'key'           => 'field_use_category_programs',
				'label'         => 'הצג תוכניות לפי קטגוריה',
				'name'          => 'use_category_programs',
				'type'          => 'true_false',
				'instructions'  => 'סמן כדי להציג את התוכניות הבאות מתוך קטגוריית תוכניות נבחרת, במקום רשימת האירועים (events).',
				'ui'            => 1,
				'ui_on_text'    => 'כן',
				'ui_off_text'   => 'לא',
				'default_value' => 0,
			),
			array(
				'key'               => 'field_category_programs_term',
				'label'             => 'קטגוריית תוכניות',
				'name'              => 'category_programs_term',
				'type'              => 'taxonomy',
				'instructions'      => 'בחר קטגוריית תוכניות. יוצגו התוכניות הבאות (עם אירועים עתידיים) מתוך הקטגוריה.',
				'taxonomy'          => 'category_program',
				'field_type'        => 'select',
				'add_term'          => 0,
				'save_terms'        => 0,
				'load_terms'        => 0,
				'return_format'     => 'id',
				'multiple'          => 0,
				'allow_null'        => 1,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_use_category_programs',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'serie',
				),
			),
		),
		'menu_order' => 0,
		'position'   => 'normal',
		'style'      => 'default',
		'active'     => true,
	) );
}

/**
 * Get the nearest upcoming event ID for each program in a given category_program term.
 *
 * One event per program (its nearest future event), sorted ascending by date. The
 * returned IDs are drop-in compatible with the events loop in single-serie.php, which
 * derives the program from each event via $event->get_program().
 *
 * @param int|WP_Term $term category_program term (ID or object).
 * @return array Event post IDs.
 */
function ipo_get_upcoming_event_ids_by_program_category( $term ) {

	$term_id = is_object( $term ) ? $term->term_id : intval( $term );
	if ( ! $term_id ) {
		return array();
	}

	// Programs in the selected category (filtered to the current WPML language).
	$programs = get_posts( array(
		'post_type'        => 'program',
		'post_status'      => 'publish',
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'suppress_filters' => false,
		'tax_query'        => array(
			array(
				'taxonomy' => 'category_program',
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	) );

	if ( empty( $programs ) ) {
		return array();
	}

	$rows = array();
	$now  = time();

	foreach ( $programs as $program_id ) {

		$event_ids = get_related_event_ids( $program_id );
		if ( ! is_array( $event_ids ) || empty( $event_ids ) ) {
			continue;
		}

		// Find this program's nearest upcoming event.
		$nearest_id   = false;
		$nearest_time = false;

		foreach ( $event_ids as $event_id ) {

			$event_date_time = get_field( 'event_date_time', $event_id );
			if ( ! $event_date_time ) {
				continue;
			}

			$ts = strtotime( $event_date_time );
			if ( $ts < $now ) {
				continue; // Passed event.
			}

			if ( false === $nearest_time || $ts < $nearest_time ) {
				$nearest_time = $ts;
				$nearest_id   = $event_id;
			}
		}

		if ( $nearest_id ) {
			$rows[] = array(
				'event_id' => $nearest_id,
				'time'     => $nearest_time,
			);
		}
	}

	// Order programs by their nearest event date (soonest first).
	usort( $rows, function ( $a, $b ) {
		return $a['time'] <=> $b['time'];
	} );

	return array_column( $rows, 'event_id' );
}
