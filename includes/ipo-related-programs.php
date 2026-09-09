<?php
/**
 * The program-card modules, driven from one settings screen.
 *
 * Four places on the site render rows of program cards, each with its own idea
 * of where the list comes from. They are registered here as "zones", and each
 * zone gets its own rules under Event Table > Related Programs.
 *
 * The single-program zone can also carry per-category rules, so a children's
 * programme can pull from a different set of categories than a classical one.
 *
 * Dates come from ipo_get_next_event_timestamp() in custom-functions.php, which
 * the home and lobby modules already sorted by. It reads event_date_time with
 * strtotime(), so it handles both shapes the field is stored in — the few
 * hundred rows saved without seconds included.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_RELATED_PROGRAMS_OPTION = 'ipo_related_programs_zones';

/**
 * The zones a settings tab is built for.
 *
 * `page_pick_label` names the ACF field the zone already reads, so the option
 * to keep honouring it can say which field it means.
 */
function ipo_related_programs_zones() {
	return array(
		'single_program' => array(
			'label'           => 'עמוד תוכנית — אולי יעניין אותך גם',
			'description'     => 'המודול בתחתית כל עמוד תוכנית.',
			'category_rules'  => true,
			'page_pick_label' => 'program_related_programs שבתוכנית',
			'page_pick_default' => 0,
		),
		'home_upcoming'  => array(
			'label'           => 'דף הבית — אירועים קרובים',
			'description'     => 'רצועת האירועים הקרובים בדף הבית.',
			'category_rules'  => false,
			'page_pick_label' => 'upcoming_selected_programs שבדף',
			'page_pick_default' => 1,
		),
		'lobby_upcoming' => array(
			'label'           => 'עמודי לובי — אירועים קרובים',
			'description'     => 'רצועת האירועים הקרובים בעמודי הלובי.',
			'category_rules'  => false,
			'page_pick_label' => 'upcoming_selected_programs שבעמוד',
			'page_pick_default' => 1,
		),
		'simple_page'    => array(
			'label'           => 'עמוד Simple — לרכישת כרטיסים',
			'description'     => 'רצועת הכרטיסים בתבנית Simple page.',
			'category_rules'  => false,
			'page_pick_label' => 'program_related_programs שבעמוד',
			'page_pick_default' => 1,
		),
	);
}

/**
 * One set of rules. Every zone has one of these as its default, and the
 * single-program zone can hold one more per category.
 */
function ipo_related_programs_ruleset_defaults() {
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

function ipo_related_programs_sanitize_ruleset( $ruleset ) {
	$clean = array_merge( ipo_related_programs_ruleset_defaults(), is_array( $ruleset ) ? $ruleset : array() );

	foreach ( array( 'categories', 'manual_ids', 'promoted_ids', 'exclude_ids' ) as $list ) {
		$clean[ $list ] = array_values( array_filter( array_map( 'intval', (array) $clean[ $list ] ) ) );
	}

	$clean['require_future_event'] = (int) ! empty( $clean['require_future_event'] );
	$clean['max_items']            = max( 0, (int) $clean['max_items'] );

	if ( ! in_array( $clean['mode'], array( 'same_category', 'categories', 'manual' ), true ) ) {
		$clean['mode'] = 'same_category';
	}

	if ( ! in_array( $clean['order'], array( 'date_asc', 'date_desc' ), true ) ) {
		$clean['order'] = 'date_asc';
	}

	return $clean;
}

/**
 * All zone settings, with every key present whether saved or not.
 */
function ipo_related_programs_get_settings() {
	$saved = get_option( IPO_RELATED_PROGRAMS_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	$settings = array();

	foreach ( ipo_related_programs_zones() as $zone_key => $zone ) {
		$zone_saved = isset( $saved[ $zone_key ] ) && is_array( $saved[ $zone_key ] ) ? $saved[ $zone_key ] : array();

		$settings[ $zone_key ] = array(
			'respect_page_pick' => isset( $zone_saved['respect_page_pick'] )
				? (int) ! empty( $zone_saved['respect_page_pick'] )
				: (int) $zone['page_pick_default'],
			'default'           => ipo_related_programs_sanitize_ruleset( isset( $zone_saved['default'] ) ? $zone_saved['default'] : array() ),
			'by_category'       => array(),
		);

		if ( ! $zone['category_rules'] ) {
			continue;
		}

		$by_category = isset( $zone_saved['by_category'] ) && is_array( $zone_saved['by_category'] ) ? $zone_saved['by_category'] : array();

		foreach ( $by_category as $term_id => $ruleset ) {
			$term_id = (int) $term_id;

			// An override is only stored once it has been switched on, so an
			// untouched category falls through to the zone default.
			if ( ! $term_id || empty( $ruleset['enabled'] ) ) {
				continue;
			}

			$settings[ $zone_key ]['by_category'][ $term_id ] = ipo_related_programs_sanitize_ruleset( $ruleset );
		}
	}

	return $settings;
}

function ipo_related_programs_get_zone_settings( $zone_key ) {
	$settings = ipo_related_programs_get_settings();

	return isset( $settings[ $zone_key ] ) ? $settings[ $zone_key ] : null;
}

/**
 * Map IDs saved in the settings onto the language being viewed. The settings
 * are global and hold whichever language the admin picked in, so without this
 * a Hebrew pick would never match on the English side of the site.
 */
function ipo_related_programs_translate_ids( $ids, $type = 'program' ) {
	$translated = array();

	foreach ( (array) $ids as $id ) {
		$id = ipo_related_programs_normalize_id( $id );

		if ( ! $id ) {
			continue;
		}

		$mapped       = apply_filters( 'wpml_object_id', $id, $type, true );
		$translated[] = $mapped ? (int) $mapped : $id;
	}

	return array_values( array_unique( $translated ) );
}

/**
 * ACF hands back post objects, arrays or plain IDs depending on the field's
 * return format, and the four zones are not consistent about it.
 */
function ipo_related_programs_normalize_id( $value ) {
	if ( function_exists( 'ipo_normalize_post_id' ) ) {
		return (int) ipo_normalize_post_id( $value );
	}

	if ( is_object( $value ) && isset( $value->ID ) ) {
		return (int) $value->ID;
	}

	if ( is_array( $value ) && isset( $value['ID'] ) ) {
		return (int) $value['ID'];
	}

	return (int) $value;
}

/**
 * The ruleset that applies to this render.
 *
 * On the single-program zone the current programme's own categories are
 * checked first, in the order the taxonomy returns them, and the first one
 * with an override wins. Everything else uses the zone default.
 */
function ipo_related_programs_resolve_ruleset( $zone_key, $zone_settings, $post_id ) {
	$zones = ipo_related_programs_zones();

	if ( empty( $zones[ $zone_key ]['category_rules'] ) || empty( $zone_settings['by_category'] ) || ! $post_id ) {
		return $zone_settings['default'];
	}

	$terms = wp_get_post_terms( $post_id, 'category_program', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $terms ) ) {
		return $zone_settings['default'];
	}

	foreach ( $terms as $term_id ) {
		// Overrides are stored against whichever language the admin was in.
		foreach ( array( (int) $term_id, (int) apply_filters( 'wpml_object_id', $term_id, 'category_program', true ) ) as $candidate ) {
			if ( $candidate && isset( $zone_settings['by_category'][ $candidate ] ) ) {
				return $zone_settings['by_category'][ $candidate ];
			}
		}

		// The override may equally have been saved against a translation of
		// this term, so try mapping the stored keys forward as well.
		foreach ( $zone_settings['by_category'] as $stored_term => $ruleset ) {
			$mapped = apply_filters( 'wpml_object_id', $stored_term, 'category_program', true );

			if ( $mapped && (int) $mapped === (int) $term_id ) {
				return $ruleset;
			}
		}
	}

	return $zone_settings['default'];
}

/**
 * Programs the rules allow, before dates are considered.
 */
function ipo_related_programs_build_pool( $ruleset, $post_id ) {
	$pool = array();

	if ( $ruleset['mode'] === 'same_category' && $post_id ) {
		$terms = wp_get_post_terms( $post_id, 'category_program', array( 'fields' => 'ids' ) );
		$terms = is_wp_error( $terms ) ? array() : $terms;
	} elseif ( $ruleset['mode'] === 'categories' ) {
		$terms = ipo_related_programs_translate_ids( $ruleset['categories'], 'category_program' );
	} else {
		$terms = array();
	}

	if ( ! empty( $terms ) ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'program',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
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
	return array_merge( $pool, ipo_related_programs_translate_ids( $ruleset['manual_ids'] ) );
}

/**
 * The ordered list of program IDs a zone should render.
 *
 * @param string $zone_key   One of ipo_related_programs_zones().
 * @param array  $args       post_id:     the program or page being viewed.
 *                           manual_pick: the zone's own ACF selection, if any.
 * @return int[] Program IDs, in render order.
 */
function ipo_related_programs_get_ids( $zone_key, $args = array() ) {
	$zone_settings = ipo_related_programs_get_zone_settings( $zone_key );

	if ( ! $zone_settings ) {
		return array();
	}

	$post_id     = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
	$manual_pick = isset( $args['manual_pick'] ) ? (array) $args['manual_pick'] : array();
	$manual_pick = array_values( array_filter( array_map( 'ipo_related_programs_normalize_id', $manual_pick ) ) );

	$ruleset = ipo_related_programs_resolve_ruleset( $zone_key, $zone_settings, $post_id );

	// A selection made on the page itself wins when the zone is set to honour
	// it; the rules below still order it and drop what has finished.
	if ( $zone_settings['respect_page_pick'] && ! empty( $manual_pick ) ) {
		$pool = $manual_pick;
	} else {
		$pool = ipo_related_programs_build_pool( $ruleset, $post_id );
	}

	$excluded = ipo_related_programs_translate_ids( $ruleset['exclude_ids'] );

	if ( $post_id ) {
		$excluded[] = $post_id;
	}

	$pool = array_values( array_unique( array_diff( array_map( 'ipo_related_programs_normalize_id', $pool ), $excluded ) ) );

	$promoted = ipo_related_programs_translate_ids( $ruleset['promoted_ids'] );

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

		$next_event = ipo_related_programs_next_event( $program_id );

		// artist_plan carries no event dates of its own and is kept regardless.
		if ( $ruleset['require_future_event'] && $program_post->post_type === 'program' && $next_event === null ) {
			continue;
		}

		$items[] = array(
			'id'         => (int) $program_id,
			'next_event' => $next_event,
			// A promoted program only jumps the queue once it has passed the
			// rules above and still has a date ahead of it.
			'promoted'   => in_array( (int) $program_id, $promoted, true ) && $next_event !== null,
			'position'   => $position++,
		);
	}

	$direction = $ruleset['order'] === 'date_desc' ? -1 : 1;

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

	if ( $ruleset['max_items'] > 0 ) {
		$items = array_slice( $items, 0, $ruleset['max_items'] );
	}

	return array_column( $items, 'id' );
}

/**
 * Timestamp of a program's soonest event still ahead, or null.
 */
function ipo_related_programs_next_event( $program_id ) {
	if ( ! function_exists( 'get_related_event_ids' ) || ! function_exists( 'ipo_get_next_event_timestamp' ) ) {
		return null;
	}

	$event_ids = get_related_event_ids( $program_id );

	if ( empty( $event_ids ) ) {
		return null;
	}

	$next = ipo_get_next_event_timestamp( $event_ids );

	return false === $next ? null : (int) $next;
}
