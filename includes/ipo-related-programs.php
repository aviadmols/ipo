<?php
/**
 * The program-card modules, driven from one settings screen.
 *
 * Four modules on the site render rows of program cards. They are registered
 * here as "zones", and each zone gets its own rules under Event Table > Related
 * Programs. Below a zone's default sit the "places" it appears in:
 *
 * - single_program: one override per program category, so a children's
 *   programme can pull from a different set of categories than a classical one.
 * - the page zones: one override per page that renders the module. The
 *   upcoming strip in parts/section-upcoming.php alone sits on the home page and
 *   a dozen lobby templates, and they used to share a single setting.
 *
 * Page overrides are keyed by the page in the site's default language, so the
 * Hebrew page and its English translation are one place with one set of rules.
 * Program IDs in the rules are mapped onto the language being viewed.
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
 * The zones the settings screen is built for.
 *
 * `page_pick_label` names the ACF field the zone already reads, so the option
 * to keep honouring it can say which field it means. `templates` lists the page
 * templates that render the zone, which is how the screen finds its places.
 */
function ipo_related_programs_zones() {
	return array(
		'single_program' => array(
			'label'             => 'עמוד תוכנית — אולי יעניין אותך גם',
			'short_label'       => 'עמודי תוכנית',
			'description'       => 'המודול בתחתית כל עמוד תוכנית.',
			'category_rules'    => true,
			'templates'         => array(),
			'page_pick_label'   => 'program_related_programs שבתוכנית',
			'page_pick_default' => 0,
		),
		'home_upcoming'  => array(
			'label'             => 'אירועים קרובים — דף הבית ועמודי הלובי',
			'short_label'       => 'אירועים קרובים',
			'description'       => 'רצועת האירועים הקרובים: דף הבית, לובי הקונצרטים, ילדים, עונה חדשה, קאמרי וצעירים.',
			'category_rules'    => false,
			'templates'         => array(
				'page-templates/template-home.php',
				'page-templates/Lobby.php',
				'page-templates/Lobby-Children.php',
				'page-templates/Lobby-new-season.php',
				'page-templates/Lobby-New-season-v2.php',
				'page-templates/Lobby-New-season-v3.php',
				'page-templates/Chamber-Concerts.php',
				'page-templates/youngpage_template.php',
			),
			'page_pick_label'   => 'upcoming_selected_programs שבדף',
			'page_pick_default' => 1,
		),
		'lobby_upcoming' => array(
			'label'             => 'לובי גמיש — אירועים קרובים',
			'short_label'       => 'לובי גמיש',
			'description'       => 'בלוק האירועים הקרובים בעמודים שנבנו בתבנית Lobby - Flexible.',
			'category_rules'    => false,
			'templates'         => array( 'page-templates/Lobby-flexible.php' ),
			'page_pick_label'   => 'upcoming_selected_programs שבבלוק',
			'page_pick_default' => 1,
		),
		'simple_page'    => array(
			'label'             => 'עמוד Simple — לרכישת כרטיסים',
			'short_label'       => 'עמודי Simple',
			'description'       => 'רצועת הכרטיסים בתבנית Simple page.',
			'category_rules'    => false,
			'templates'         => array( 'page-templates/Simple_page.php' ),
			'page_pick_label'   => 'program_related_programs שבעמוד',
			'page_pick_default' => 1,
		),
	);
}

/**
 * Where the programs come from.
 *
 * same_category only means something on a program page; on the page zones it
 * finds no terms and leaves the module to its own fallback, which is what the
 * page zones did before `upcoming` existed.
 */
function ipo_related_programs_modes() {
	return array( 'same_category', 'upcoming', 'categories', 'manual' );
}

/**
 * One set of rules. Every zone has one as its default, and each of its places
 * can hold one more.
 */
function ipo_related_programs_ruleset_defaults() {
	return array(
		'mode'                 => 'same_category',
		'categories'           => array(),
		'manual_ids'           => array(),
		'promoted_ids'         => array(), // in the order they are shown
		'exclude_ids'          => array(),
		'require_future_event' => 1,
		'max_items'            => 0, // 0 = no limit
		'order'                => 'date_asc', // date_asc | date_desc
	);
}

function ipo_related_programs_sanitize_ruleset( $ruleset ) {
	$clean = array_merge( ipo_related_programs_ruleset_defaults(), is_array( $ruleset ) ? $ruleset : array() );

	foreach ( array( 'categories', 'manual_ids', 'promoted_ids', 'exclude_ids' ) as $list ) {
		// array_unique keeps the first occurrence, so the promoted order holds.
		$clean[ $list ] = array_values( array_unique( array_filter( array_map( 'intval', (array) $clean[ $list ] ) ) ) );
	}

	$clean['require_future_event'] = (int) ! empty( $clean['require_future_event'] );
	$clean['max_items']            = max( 0, (int) $clean['max_items'] );

	if ( ! in_array( $clean['mode'], ipo_related_programs_modes(), true ) ) {
		$clean['mode'] = 'same_category';
	}

	if ( ! in_array( $clean['order'], array( 'date_asc', 'date_desc' ), true ) ) {
		$clean['order'] = 'date_asc';
	}

	return array_intersect_key( $clean, ipo_related_programs_ruleset_defaults() );
}

/**
 * A place's override: a ruleset plus whether it is switched on and whether it
 * honours the page's own ACF pick.
 */
function ipo_related_programs_sanitize_override( $override, $zone ) {
	$override = is_array( $override ) ? $override : array();
	$rule     = ipo_related_programs_sanitize_ruleset( $override );

	$rule['enabled']           = empty( $override['enabled'] ) ? 0 : 1;
	$rule['respect_page_pick'] = isset( $override['respect_page_pick'] )
		? (int) ! empty( $override['respect_page_pick'] )
		: (int) $zone['page_pick_default'];

	return $rule;
}

/**
 * Sanitise the whole option, as posted by the screen or as read back.
 * Overrides are kept when switched off, so unticking one does not throw away a
 * set-up that may be wanted again.
 */
function ipo_related_programs_sanitize_store( $saved ) {
	$saved = is_array( $saved ) ? $saved : array();
	$store = array();

	foreach ( ipo_related_programs_zones() as $zone_key => $zone ) {
		$zone_saved = isset( $saved[ $zone_key ] ) && is_array( $saved[ $zone_key ] ) ? $saved[ $zone_key ] : array();

		$store[ $zone_key ] = array(
			'respect_page_pick' => isset( $zone_saved['respect_page_pick'] )
				? (int) ! empty( $zone_saved['respect_page_pick'] )
				: (int) $zone['page_pick_default'],
			'default'           => ipo_related_programs_sanitize_ruleset( isset( $zone_saved['default'] ) ? $zone_saved['default'] : array() ),
			'by_category'       => array(),
			'by_page'           => array(),
		);

		$groups = array(
			'by_category' => ! empty( $zone['category_rules'] ),
			'by_page'     => ! empty( $zone['templates'] ),
		);

		foreach ( $groups as $group => $supported ) {
			if ( ! $supported || empty( $zone_saved[ $group ] ) || ! is_array( $zone_saved[ $group ] ) ) {
				continue;
			}

			foreach ( $zone_saved[ $group ] as $key => $override ) {
				$key = (int) $key;

				if ( $key ) {
					$store[ $zone_key ][ $group ][ $key ] = ipo_related_programs_sanitize_override( $override, $zone );
				}
			}
		}
	}

	return $store;
}

/**
 * All zone settings, with every key present whether saved or not, and only the
 * overrides that are switched on.
 */
function ipo_related_programs_get_settings() {
	static $settings = null;

	if ( $settings !== null ) {
		return $settings;
	}

	$settings = ipo_related_programs_sanitize_store( get_option( IPO_RELATED_PROGRAMS_OPTION, array() ) );

	foreach ( $settings as $zone_key => $zone_settings ) {
		foreach ( array( 'by_category', 'by_page' ) as $group ) {
			foreach ( $zone_settings[ $group ] as $key => $override ) {
				if ( empty( $override['enabled'] ) ) {
					unset( $settings[ $zone_key ][ $group ][ $key ] );
					continue;
				}

				// A category rule is a mapping — "for a programme in this
				// category, show these" — so "same category" is not a choice
				// there. Leaving the radio on that default used to make a
				// filled-in category list do nothing at all.
				if ( $group === 'by_category' && ! in_array( $override['mode'], array( 'categories', 'manual', 'upcoming' ), true ) ) {
					$settings[ $zone_key ][ $group ][ $key ]['mode'] = 'categories';
				}
			}
		}
	}

	return $settings;
}

function ipo_related_programs_get_zone_settings( $zone_key ) {
	$settings = ipo_related_programs_get_settings();

	return isset( $settings[ $zone_key ] ) ? $settings[ $zone_key ] : null;
}

/**
 * The page a per-page override is stored under: the page in the default
 * language, so a page and its translations share one place.
 */
function ipo_related_programs_page_key( $post_id ) {
	$post_id = (int) $post_id;

	if ( ! $post_id ) {
		return 0;
	}

	$default_language = apply_filters( 'wpml_default_language', null );

	if ( ! $default_language ) {
		return $post_id;
	}

	$mapped = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), true, $default_language );

	return $mapped ? (int) $mapped : $post_id;
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
 * The rules that apply to this render, and which place they came from.
 *
 * A page with its own override uses it. On the single-program zone the current
 * programme's categories are checked in the order the taxonomy returns them,
 * and the first one with an override wins. Everything else uses the zone
 * default.
 *
 * @return array ruleset, respect_page_pick, place ('default' | 'category:ID' | 'page:ID').
 */
function ipo_related_programs_resolve( $zone_key, $zone_settings, $post_id ) {
	$resolved = array(
		'ruleset'           => $zone_settings['default'],
		'respect_page_pick' => $zone_settings['respect_page_pick'],
		'place'             => 'default',
	);

	if ( ! $post_id ) {
		return $resolved;
	}

	if ( ! empty( $zone_settings['by_page'] ) ) {
		$page_key = ipo_related_programs_page_key( $post_id );

		if ( isset( $zone_settings['by_page'][ $page_key ] ) ) {
			$override = $zone_settings['by_page'][ $page_key ];

			return array(
				'ruleset'           => ipo_related_programs_sanitize_ruleset( $override ),
				'respect_page_pick' => $override['respect_page_pick'],
				'place'             => 'page:' . $page_key,
			);
		}
	}

	$zones = ipo_related_programs_zones();

	if ( empty( $zones[ $zone_key ]['category_rules'] ) || empty( $zone_settings['by_category'] ) ) {
		return $resolved;
	}

	$terms = wp_get_post_terms( $post_id, 'category_program', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $terms ) ) {
		return $resolved;
	}

	// category_program is not registered with WPML — there are five terms and
	// both languages share them, so an English programme carries the same term
	// ids a Hebrew one does and the stored rule matches directly.
	foreach ( $terms as $term_id ) {
		if ( isset( $zone_settings['by_category'][ (int) $term_id ] ) ) {
			$override = $zone_settings['by_category'][ (int) $term_id ];

			return array(
				'ruleset'           => ipo_related_programs_sanitize_ruleset( $override ),
				'respect_page_pick' => $override['respect_page_pick'],
				'place'             => 'category:' . (int) $term_id,
			);
		}
	}

	return $resolved;
}

/**
 * Programs, in the current language, with an event still ahead. Same query the
 * modules fell back to before they had settings, without its 20-event cap.
 */
function ipo_related_programs_upcoming_pool() {
	$event_ids = get_posts(
		array(
			'post_type'        => 'event',
			'post_status'      => 'publish',
			'meta_key'         => 'event_date_time',
			'meta_value'       => date( 'Y-m-d H:i:s' ),
			'meta_compare'     => '>',
			'orderby'          => 'meta_value',
			'order'            => 'ASC',
			'fields'           => 'ids',
			'posts_per_page'   => -1,
			'suppress_filters' => false,
		)
	);

	if ( empty( $event_ids ) ) {
		return array();
	}

	update_meta_cache( 'post', $event_ids );

	$programs = array();

	foreach ( $event_ids as $event_id ) {
		$program_id = (int) get_post_meta( $event_id, 'related_to_program', true );

		if ( $program_id ) {
			$programs[ $program_id ] = $program_id;
		}
	}

	return array_values( $programs );
}

/**
 * Programs the rules allow, before dates are considered.
 */
function ipo_related_programs_build_pool( $ruleset, $post_id ) {
	$pool  = array();
	$terms = array();

	if ( $ruleset['mode'] === 'same_category' && $post_id ) {
		$terms = wp_get_post_terms( $post_id, 'category_program', array( 'fields' => 'ids' ) );
		$terms = is_wp_error( $terms ) ? array() : $terms;
	} elseif ( $ruleset['mode'] === 'categories' ) {
		// Shared across languages — see ipo_related_programs_resolve().
		$terms = array_map( 'intval', $ruleset['categories'] );
	} elseif ( $ruleset['mode'] === 'upcoming' ) {
		$pool = ipo_related_programs_upcoming_pool();
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

	return $pool;
}

/**
 * The programs a set of rules produces, with the reason each one is there.
 *
 * Shared by the modules and by the preview on the settings screen, so what the
 * screen shows is what the page renders.
 *
 * @param array $ruleset           A sanitised ruleset.
 * @param bool  $respect_page_pick Whether a filled-in $manual_pick replaces the pool.
 * @param int   $post_id           The program or page being viewed.
 * @param int[] $manual_pick       The place's own ACF selection.
 * @return array[] id, next_event, promoted, source ('pool' | 'manual' | 'page_pick' | 'promoted').
 */
function ipo_related_programs_compute( $ruleset, $respect_page_pick, $post_id, $manual_pick = array() ) {
	$manual_pick = array_values( array_filter( array_map( 'ipo_related_programs_normalize_id', (array) $manual_pick ) ) );
	$sources     = array();

	// A selection made on the page itself wins when the place honours it; the
	// rules below still order it and drop what has finished.
	if ( $respect_page_pick && ! empty( $manual_pick ) ) {
		$sources = array_fill_keys( $manual_pick, 'page_pick' );
	} else {
		foreach ( ipo_related_programs_build_pool( $ruleset, $post_id ) as $id ) {
			$sources[ (int) $id ] = 'pool';
		}

		// Hand-picked programs join the pool whatever the source rule says.
		foreach ( ipo_related_programs_translate_ids( $ruleset['manual_ids'] ) as $id ) {
			if ( ! isset( $sources[ $id ] ) ) {
				$sources[ $id ] = 'manual';
			}
		}
	}

	// Promoted programs always join, and lead in the order they were set.
	$promoted = ipo_related_programs_translate_ids( $ruleset['promoted_ids'] );

	foreach ( $promoted as $id ) {
		if ( ! isset( $sources[ $id ] ) ) {
			$sources[ $id ] = 'promoted';
		}
	}

	$excluded = ipo_related_programs_translate_ids( $ruleset['exclude_ids'] );

	if ( $post_id ) {
		$excluded[] = (int) $post_id;
	}

	$promoted_rank = array_flip( $promoted );
	$items         = array();
	$position      = 0;

	foreach ( $sources as $program_id => $source ) {
		$program_id = (int) $program_id;

		if ( ! $program_id || in_array( $program_id, $excluded, true ) ) {
			continue;
		}

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
			'id'         => $program_id,
			'next_event' => $next_event,
			// A promoted program only leads while it still has a date ahead.
			'promoted'   => isset( $promoted_rank[ $program_id ] ) && $next_event !== null,
			'rank'       => isset( $promoted_rank[ $program_id ] ) ? $promoted_rank[ $program_id ] : 0,
			'source'     => $source,
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

			if ( $a['promoted'] ) {
				return $a['rank'] <=> $b['rank'];
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

	return $items;
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

	$post_id  = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
	$resolved = ipo_related_programs_resolve( $zone_key, $zone_settings, $post_id );

	$items = ipo_related_programs_compute(
		$resolved['ruleset'],
		$resolved['respect_page_pick'],
		$post_id,
		isset( $args['manual_pick'] ) ? $args['manual_pick'] : array()
	);

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
