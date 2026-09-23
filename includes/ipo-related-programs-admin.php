<?php
/**
 * Settings screen for the program-card zones, under the Event Table menu.
 *
 * The screen lists every place a zone renders in — the zone default, each
 * program category, each page built on a template that includes the module —
 * and edits one place at a time next to a live preview of what that place
 * will show.
 *
 * The screen is a small script app. PHP hands it the settings, the places and
 * every published program (~750, with the next date of each worked out in one
 * query); it saves the whole option back over AJAX, and asks the server for a
 * preview through ipo_related_programs_compute() — the same function the
 * modules render from — so the preview cannot drift from the site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		$hook = add_submenu_page(
			'event-table',
			'מודולי תוכניות מקושרות',
			'תוכניות מקושרות',
			'manage_options',
			'ipo-related-programs',
			'ipo_related_programs_admin_page'
		);

		if ( $hook ) {
			add_action( 'load-' . $hook, 'ipo_related_programs_admin_assets' );
		}
	},
	20
);

add_action( 'wp_ajax_ipo_related_programs_save', 'ipo_related_programs_admin_ajax_save' );
add_action( 'wp_ajax_ipo_related_programs_preview', 'ipo_related_programs_admin_ajax_preview' );

/**
 * The ACF field each page zone reads its on-page pick from. The flexible
 * lobby keeps it inside a flexible-content row, so there is nothing flat to
 * read there.
 */
function ipo_related_programs_admin_page_pick_field( $zone_key ) {
	$fields = array(
		'home_upcoming' => 'upcoming_selected_programs',
		'simple_page'   => 'program_related_programs',
	);

	return isset( $fields[ $zone_key ] ) ? $fields[ $zone_key ] : '';
}

function ipo_related_programs_admin_assets() {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	wp_enqueue_style( 'ipo-rp-heebo', 'https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;700;900&display=swap', array(), null );
	wp_enqueue_style(
		'ipo-related-programs-admin',
		$uri . '/assets/styles/ipo-related-programs-admin.css',
		array(),
		filemtime( $dir . '/assets/styles/ipo-related-programs-admin.css' )
	);
	wp_enqueue_script(
		'ipo-related-programs-admin',
		$uri . '/assets/scripts/ipo-related-programs-admin.js',
		array(),
		filemtime( $dir . '/assets/scripts/ipo-related-programs-admin.js' ),
		true
	);
}

function ipo_related_programs_admin_categories() {
	$categories = get_terms(
		array(
			'taxonomy'   => 'category_program',
			'hide_empty' => false,
		)
	);

	return is_wp_error( $categories ) ? array() : $categories;
}

/**
 * Language code of each post in $ids, from WPML's table in one query.
 */
function ipo_related_programs_admin_languages( $ids, $post_type ) {
	global $wpdb;

	$ids   = array_filter( array_map( 'intval', (array) $ids ) );
	$table = $wpdb->prefix . 'icl_translations';

	if ( empty( $ids ) || $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return array();
	}

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT element_id, language_code FROM {$table} WHERE element_type = %s AND element_id IN (" . implode( ',', $ids ) . ')',
			'post_' . $post_type
		)
	);

	$languages = array();

	foreach ( $rows as $row ) {
		$languages[ (int) $row->element_id ] = $row->language_code;
	}

	return $languages;
}

/**
 * Every published program for the pickers: title, language, categories and the
 * next date still ahead.
 *
 * The dates come from one grouped query rather than ~750 calls to
 * ipo_related_programs_next_event(). The comparison is on the stored string,
 * which sorts correctly with or without seconds, against the same naive clock
 * ipo_get_next_event_timestamp() uses.
 */
function ipo_related_programs_admin_programs() {
	global $wpdb;

	$programs = get_posts(
		array(
			'post_type'        => 'program',
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => true,
		)
	);

	if ( empty( $programs ) ) {
		return array();
	}

	$ids       = wp_list_pluck( $programs, 'ID' );
	$languages = ipo_related_programs_admin_languages( $ids, 'program' );

	$next_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT rel.meta_value AS program_id, MIN(dt.meta_value) AS next_date, COUNT(*) AS upcoming
			 FROM {$wpdb->postmeta} rel
			 INNER JOIN {$wpdb->postmeta} dt ON dt.post_id = rel.post_id AND dt.meta_key = 'event_date_time'
			 INNER JOIN {$wpdb->posts} ev ON ev.ID = rel.post_id AND ev.post_type = 'event' AND ev.post_status = 'publish'
			 WHERE rel.meta_key = 'related_to_program' AND dt.meta_value >= %s
			 GROUP BY rel.meta_value",
			gmdate( 'Y-m-d H:i:s' )
		)
	);

	$next = array();

	foreach ( $next_rows as $row ) {
		$next[ (int) $row->program_id ] = array(
			'date'     => ipo_related_programs_admin_format_date( strtotime( $row->next_date ) ),
			'ts'       => (int) strtotime( $row->next_date ),
			'upcoming' => (int) $row->upcoming,
		);
	}

	$terms = wp_get_object_terms( $ids, 'category_program', array( 'fields' => 'all_with_object_id' ) );
	$cats  = array();

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$cats[ (int) $term->object_id ][] = (int) $term->term_id;
		}
	}

	$out = array();

	foreach ( $programs as $program ) {
		$id    = (int) $program->ID;
		$out[] = array(
			'id'    => $id,
			't'     => html_entity_decode( get_the_title( $program ), ENT_QUOTES, 'UTF-8' ),
			'l'     => isset( $languages[ $id ] ) ? $languages[ $id ] : '',
			'c'     => isset( $cats[ $id ] ) ? $cats[ $id ] : array(),
			'd'     => isset( $next[ $id ] ) ? $next[ $id ]['date'] : '',
			'ts'    => isset( $next[ $id ] ) ? $next[ $id ]['ts'] : 0,
			'n'     => isset( $next[ $id ] ) ? $next[ $id ]['upcoming'] : 0,
		);
	}

	return $out;
}

/**
 * Event dates are stored as naive local strings and read with strtotime(), so
 * gmdate() prints them back as they were entered.
 */
function ipo_related_programs_admin_format_date( $timestamp ) {
	return $timestamp ? gmdate( 'd.m.y · H:i', (int) $timestamp ) : '';
}

/**
 * Every place, grouped by zone.
 *
 * Pages are found by the template they use, and a page and its translations
 * are folded into one place keyed by the default-language page — see
 * ipo_related_programs_page_key().
 */
function ipo_related_programs_admin_places() {
	$zones      = ipo_related_programs_zones();
	$categories = ipo_related_programs_admin_categories();
	$templates  = (array) wp_get_theme()->get_page_templates(); // file => name
	$places     = array();

	foreach ( $zones as $zone_key => $zone ) {
		$places[ $zone_key ] = array();

		if ( ! empty( $zone['category_rules'] ) ) {
			foreach ( $categories as $category ) {
				$places[ $zone_key ][] = array(
					'type'  => 'category',
					'key'   => (int) $category->term_id,
					'title' => html_entity_decode( $category->name, ENT_QUOTES, 'UTF-8' ),
					'sub'   => sprintf( 'תוכניות בקטגוריה · %d', (int) $category->count ),
				);
			}
		}

		if ( empty( $zone['templates'] ) ) {
			continue;
		}

		$pages = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => array( 'publish', 'private', 'draft', 'pending', 'future' ),
				'posts_per_page'   => -1,
				'suppress_filters' => true,
				'meta_query'       => array(
					array(
						'key'     => '_wp_page_template',
						'value'   => $zone['templates'],
						'compare' => 'IN',
					),
				),
			)
		);

		$languages   = ipo_related_programs_admin_languages( wp_list_pluck( $pages, 'ID' ), 'page' );
		$pick_field  = ipo_related_programs_admin_page_pick_field( $zone_key );
		$grouped     = array();
		$home_ids    = array_filter( array( (int) get_option( 'page_on_front' ) ) );

		foreach ( $pages as $page ) {
			$key      = ipo_related_programs_page_key( $page->ID );
			$template = get_post_meta( $page->ID, '_wp_page_template', true );
			$pick     = $pick_field ? get_post_meta( $page->ID, $pick_field, true ) : array();

			$grouped[ $key ][] = array(
				'id'       => (int) $page->ID,
				'lang'     => isset( $languages[ $page->ID ] ) ? $languages[ $page->ID ] : '',
				'title'    => html_entity_decode( get_the_title( $page ), ENT_QUOTES, 'UTF-8' ) ?: '(ללא כותרת)',
				'status'   => $page->post_status,
				'template' => isset( $templates[ $template ] ) ? $templates[ $template ] : basename( $template, '.php' ),
				'view'     => get_permalink( $page ),
				'edit'     => get_edit_post_link( $page->ID, 'raw' ),
				'pick'     => is_array( $pick ) ? count( array_filter( $pick ) ) : 0,
				'is_home'  => in_array( (int) $page->ID, $home_ids, true ) || $template === 'page-templates/template-home.php',
			);
		}

		$zone_places = array();

		foreach ( $grouped as $key => $versions ) {
			// The default-language page leads, so its title names the place.
			usort(
				$versions,
				function ( $a, $b ) use ( $key ) {
					return ( $b['id'] === $key ) <=> ( $a['id'] === $key );
				}
			);

			$published = array_filter(
				$versions,
				function ( $version ) {
					return $version['status'] === 'publish';
				}
			);

			$zone_places[] = array(
				'type'      => 'page',
				'key'       => (int) $key,
				'title'     => $versions[0]['title'],
				'sub'       => $versions[0]['template'],
				'versions'  => $versions,
				'published' => ! empty( $published ),
				'is_home'   => (bool) array_filter( wp_list_pluck( $versions, 'is_home' ) ),
			);
		}

		// Home first, then live pages, then drafts; alphabetical within each.
		usort(
			$zone_places,
			function ( $a, $b ) {
				return array( $b['is_home'], $b['published'], $a['title'] ) <=> array( $a['is_home'], $a['published'], $b['title'] );
			}
		);

		$places[ $zone_key ] = array_merge( $places[ $zone_key ], $zone_places );
	}

	return $places;
}

function ipo_related_programs_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'ipo' ) );
	}

	$zones = array();

	foreach ( ipo_related_programs_zones() as $zone_key => $zone ) {
		$zones[] = array(
			'key'           => $zone_key,
			'label'         => $zone['label'],
			'short'         => $zone['short_label'],
			'description'   => $zone['description'],
			'pageRules'     => ! empty( $zone['templates'] ),
			'categoryRules' => ! empty( $zone['category_rules'] ),
			'pickLabel'     => $zone['page_pick_label'],
			'pickDefault'   => (int) $zone['page_pick_default'],
		);
	}

	$categories = array();

	foreach ( ipo_related_programs_admin_categories() as $category ) {
		$categories[] = array(
			'id'    => (int) $category->term_id,
			'name'  => html_entity_decode( $category->name, ENT_QUOTES, 'UTF-8' ),
			'count' => (int) $category->count,
		);
	}

	$languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
	$languages = is_array( $languages ) ? array_keys( $languages ) : array( 'he' );

	$boot = array(
		'ajax'        => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'ipo_related_programs' ),
		'zones'       => $zones,
		'places'      => ipo_related_programs_admin_places(),
		'store'       => ipo_related_programs_sanitize_store( get_option( IPO_RELATED_PROGRAMS_OPTION, array() ) ),
		'defaults'    => ipo_related_programs_ruleset_defaults(),
		'categories'  => $categories,
		'programs'    => ipo_related_programs_admin_programs(),
		'languages'   => $languages,
		'defaultLang' => apply_filters( 'wpml_default_language', null ) ?: 'he',
	);
	?>
	<div class="wrap ipo-rp-wrap" dir="rtl">
		<h1 class="screen-reader-text">מודולי תוכניות מקושרות</h1>
		<div id="ipo-rp-app" class="ipo-rp" aria-live="polite">
			<p class="ipo-rp-loading">טוען…</p>
		</div>
		<noscript><p>המסך הזה דורש JavaScript.</p></noscript>
	</div>
	<script>window.ipoRelatedPrograms = <?php echo wp_json_encode( $boot ); ?>;</script>
	<?php
}

function ipo_related_programs_admin_ajax_guard() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'ipo_related_programs', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'אין הרשאה, או שפג תוקף העמוד. יש לרענן.' ), 403 );
	}
}

/**
 * The posted JSON, decoded. It arrives as one field so a settings tree with
 * dozens of places does not run into max_input_vars.
 */
function ipo_related_programs_admin_json( $field ) {
	$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
	$raw = json_decode( is_string( $raw ) ? $raw : '', true );

	return is_array( $raw ) ? $raw : array();
}

function ipo_related_programs_admin_ajax_save() {
	ipo_related_programs_admin_ajax_guard();

	$store = ipo_related_programs_sanitize_store( ipo_related_programs_admin_json( 'store' ) );

	update_option( IPO_RELATED_PROGRAMS_OPTION, $store );

	wp_send_json_success(
		array(
			'store' => $store,
			'saved' => wp_date( 'H:i' ),
		)
	);
}

/**
 * What one place would show under the rules on screen, saved or not.
 *
 * Runs in the requested language, in the context of that place's page (or a
 * sample program for the program zone), and reads that page's own ACF pick —
 * so the preview is what a visitor there would see.
 */
function ipo_related_programs_admin_ajax_preview() {
	ipo_related_programs_admin_ajax_guard();

	$zones    = ipo_related_programs_zones();
	$zone_key = isset( $_POST['zone'] ) ? sanitize_key( wp_unslash( $_POST['zone'] ) ) : '';

	if ( ! isset( $zones[ $zone_key ] ) ) {
		wp_send_json_error( array( 'message' => 'אזור לא מוכר.' ) );
	}

	$zone      = $zones[ $zone_key ];
	$lang      = isset( $_POST['lang'] ) ? sanitize_key( wp_unslash( $_POST['lang'] ) ) : 'he';
	$type      = isset( $_POST['place_type'] ) ? sanitize_key( wp_unslash( $_POST['place_type'] ) ) : 'default';
	$place_key = isset( $_POST['place_key'] ) ? (int) $_POST['place_key'] : 0;
	$sample    = isset( $_POST['sample'] ) ? (int) $_POST['sample'] : 0;
	$rule      = ipo_related_programs_admin_json( 'rule' );
	$ruleset   = ipo_related_programs_sanitize_ruleset( $rule );
	$respect   = ! empty( $rule['respect_page_pick'] );

	do_action( 'wpml_switch_language', $lang );

	$context = array(
		'post_id' => 0,
		'title'   => '',
		'url'     => '',
		'note'    => '',
	);
	$manual_pick = array();

	if ( $type === 'page' && $place_key ) {
		$page_id = (int) apply_filters( 'wpml_object_id', $place_key, 'page', false, $lang );

		if ( ! $page_id ) {
			wp_send_json_success(
				array(
					'items'   => array(),
					'context' => array_merge( $context, array( 'note' => 'לעמוד הזה אין גרסה בשפה הזו.' ) ),
				)
			);
		}

		$pick_field       = ipo_related_programs_admin_page_pick_field( $zone_key );
		$manual_pick      = $pick_field && function_exists( 'get_field' ) ? (array) get_field( $pick_field, $page_id, false ) : array();
		$context['post_id'] = $page_id;
		$context['title']   = get_the_title( $page_id );
		$context['url']     = get_permalink( $page_id );
	} elseif ( ! empty( $zone['category_rules'] ) ) {
		// A program page needs a program to stand on. Use the one picked, or
		// the soonest program in the category being edited.
		if ( $sample ) {
			$sample = (int) apply_filters( 'wpml_object_id', $sample, 'program', true, $lang );
		} else {
			$args = array(
				'post_type'        => 'program',
				'post_status'      => 'publish',
				'posts_per_page'   => 40,
				'fields'           => 'ids',
				'suppress_filters' => false,
			);

			if ( $type === 'category' && $place_key ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'category_program',
						'field'    => 'term_id',
						'terms'    => array( $place_key ),
					),
				);
			}

			$candidates = get_posts( $args );

			if ( function_exists( 'ipo_sort_programs_by_next_event' ) ) {
				$candidates = ipo_sort_programs_by_next_event( $candidates );
			}

			$sample = $candidates ? (int) reset( $candidates ) : 0;
		}

		if ( $sample ) {
			$manual_pick        = function_exists( 'get_field' ) ? (array) get_field( 'program_related_programs', $sample, false ) : array();
			$context['post_id'] = $sample;
			$context['title']   = get_the_title( $sample );
			$context['url']     = get_permalink( $sample );
		}
	}

	$manual_pick = array_values( array_filter( array_map( 'ipo_related_programs_normalize_id', $manual_pick ) ) );
	$items       = ipo_related_programs_compute( $ruleset, $respect, $context['post_id'], $manual_pick );
	$out         = array();

	// The promoted IDs are stored in whichever language they were picked in.
	// Which stored ID lands on each card, so un-pinning from an English preview
	// removes the Hebrew entry behind it.
	$promoted_refs = array();

	foreach ( $ruleset['promoted_ids'] as $stored_id ) {
		$promoted_refs[ (int) apply_filters( 'wpml_object_id', $stored_id, 'program', true ) ][] = $stored_id;
	}

	foreach ( $items as $item ) {
		$id       = $item['id'];
		$language = apply_filters( 'wpml_post_language_details', null, $id );

		$out[] = array(
			'id'       => $id,
			'title'    => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
			'lang'     => is_array( $language ) && ! empty( $language['language_code'] ) ? $language['language_code'] : '',
			'date'     => ipo_related_programs_admin_format_date( $item['next_event'] ),
			'promoted' => $item['promoted'],
			'refs'     => isset( $promoted_refs[ $id ] ) ? $promoted_refs[ $id ] : array(),
			'source'   => $item['source'],
			'thumb'    => get_the_post_thumbnail_url( $id, 'thumbnail' ) ?: '',
			'view'     => get_permalink( $id ),
			'edit'     => get_edit_post_link( $id, 'raw' ),
		);
	}

	$context['title']     = html_entity_decode( (string) $context['title'], ENT_QUOTES, 'UTF-8' );
	$context['pick']      = count( $manual_pick );
	$context['pick_used'] = $respect && ! empty( $manual_pick );
	$context['fallback']  = empty( $out ) && $zone_key !== 'single_program';

	wp_send_json_success(
		array(
			'items'   => $out,
			'context' => $context,
		)
	);
}
