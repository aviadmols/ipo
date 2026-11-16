<?php
/**
 * Season lobby: event cards that fill themselves from the event and its program.
 *
 * Template: page-templates/Lobby-season-events.php ("Lobby עונה – כרטיסי אירועים").
 * Styles:   assets/styles/season-lobby.css (loaded on that template only).
 *
 * Each card row picks an event. From the event's program ("related_to_program")
 * the card takes the title, image, artists (name + role) and the pieces
 * (program_plan); from the event, the date, the venue and the ticket link.
 * Every value has an override field on the row, and a filled override wins.
 *
 * Two sections use the same card fields: the main grid (image beside the text)
 * and the carousel below it (image above the text, Splide).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_SL_TEMPLATE       = 'page-templates/Lobby-season-events.php';
const IPO_SL_DEFAULT_BUTTON = '#ef5a41';

/* -------------------------------------------------------------------------
 * Fields
 * ---------------------------------------------------------------------- */

/**
 * The fields of one card row. $p keeps the keys unique per section.
 */
function ipo_sl_card_fields( $p ) {
	return array(
		array(
			'key'   => "field_ipo_sl_{$p}_acc_source",
			'label' => 'אירוע',
			'type'  => 'accordion',
			'open'  => 1,
		),
		array(
			'key'           => "field_ipo_sl_{$p}_event",
			'label'         => 'אירוע',
			'name'          => 'event',
			'type'          => 'post_object',
			'instructions'  => 'הכרטיס מושך מהאירוע את התאריך, האולם והלינק לכרטיסים, ומהתוכנית שהאירוע משויך אליה את הכותרת, התמונה, האמנים והיצירות. בלי חיפוש מוצגים רק אירועים עתידיים.',
			'post_type'     => array( 'event' ),
			'return_format' => 'id',
			'allow_null'    => 1,
			'ui'            => 1,
		),
		array(
			'key'          => "field_ipo_sl_{$p}_label",
			'label'        => 'כותרת עליונה',
			'name'         => 'label',
			'type'         => 'text',
			'placeholder'  => "קונצרט מס' 1",
			'instructions' => 'ריק = שם התוכנית.',
		),
		array(
			'key'           => "field_ipo_sl_{$p}_image",
			'label'         => 'תמונה',
			'name'          => 'image',
			'type'          => 'image',
			'return_format' => 'id',
			'preview_size'  => 'medium',
			'instructions'  => 'ריק = תמונת התוכנית.',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => "field_ipo_sl_{$p}_image_mobile",
			'label'         => 'תמונה למובייל',
			'name'          => 'image_mobile',
			'type'          => 'image',
			'return_format' => 'id',
			'preview_size'  => 'medium',
			'instructions'  => 'ריק = התמונה הרגילה.',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => "field_ipo_sl_{$p}_button_color",
			'label'         => 'צבע הכפתור',
			'name'          => 'button_color',
			'type'          => 'color_picker',
			'default_value' => IPO_SL_DEFAULT_BUTTON,
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'          => "field_ipo_sl_{$p}_button_text",
			'label'        => 'טקסט הכפתור',
			'name'         => 'button_text',
			'type'         => 'text',
			'placeholder'  => 'לכרטיסים',
			'instructions' => 'ריק = "לכרטיסים".',
			'wrapper'      => array( 'width' => '50' ),
		),

		array(
			'key'   => "field_ipo_sl_{$p}_acc_override",
			'label' => 'דריסת התוכן שנמשך (לא חובה)',
			'type'  => 'accordion',
			'open'  => 0,
		),
		array(
			'key'          => "field_ipo_sl_{$p}_artists",
			'label'        => 'אמנים',
			'name'         => 'artists',
			'type'         => 'repeater',
			'instructions' => 'ריק = האמנים של התוכנית. שורה אחת או יותר מחליפות את כל הרשימה.',
			'layout'       => 'table',
			'button_label' => 'הוספת אמן',
			'sub_fields'   => array(
				array(
					'key'   => "field_ipo_sl_{$p}_artist_name",
					'label' => 'שם',
					'name'  => 'name',
					'type'  => 'text',
				),
				array(
					'key'   => "field_ipo_sl_{$p}_artist_role",
					'label' => 'תפקיד',
					'name'  => 'role',
					'type'  => 'text',
				),
			),
		),
		array(
			'key'          => "field_ipo_sl_{$p}_program_heading",
			'label'        => 'כותרת התוכנית',
			'name'         => 'program_heading',
			'type'         => 'text',
			'placeholder'  => 'תוכנית שכולה מוצרט',
			'instructions' => 'ריק = "תוכנית".',
		),
		array(
			'key'          => "field_ipo_sl_{$p}_program",
			'label'        => 'יצירות',
			'name'         => 'program',
			'type'         => 'repeater',
			'instructions' => 'ריק = היצירות של התוכנית. שורה אחת או יותר מחליפות את כל הרשימה.',
			'layout'       => 'table',
			'button_label' => 'הוספת יצירה',
			'sub_fields'   => array(
				array(
					'key'   => "field_ipo_sl_{$p}_program_composer",
					'label' => 'מלחין',
					'name'  => 'composer',
					'type'  => 'text',
				),
				array(
					'key'   => "field_ipo_sl_{$p}_program_piece",
					'label' => 'יצירה',
					'name'  => 'piece',
					'type'  => 'text',
				),
			),
		),
		array(
			'key'          => "field_ipo_sl_{$p}_date",
			'label'        => 'תאריך ושעה',
			'name'         => 'date',
			'type'         => 'text',
			'placeholder'  => "יום א' | 3.1 | 20:00",
			'instructions' => 'ריק = מהאירוע.',
			'wrapper'      => array( 'width' => '50' ),
		),
		array(
			'key'          => "field_ipo_sl_{$p}_location",
			'label'        => 'מקום',
			'name'         => 'location',
			'type'         => 'text',
			'placeholder'  => 'היכל התרבות תל אביב',
			'instructions' => 'ריק = מהאירוע.',
			'wrapper'      => array( 'width' => '50' ),
		),
		array(
			'key'          => "field_ipo_sl_{$p}_ticket_link",
			'label'        => 'לינק לרכישת כרטיסים',
			'name'         => 'ticket_link',
			'type'         => 'url',
			'instructions' => 'ריק = הלינק של האירוע (או של התוכנית, או של מערכת הכרטיסים). לינק כאן מציג תמיד כפתור פעיל, גם כשהאירוע אזל או עבר.',
		),
	);
}

/**
 * One section: heading, subheading, artist settings and the repeater of cards.
 */
function ipo_sl_section_fields( $p, $tab_label, $defaults ) {
	return array(
		array(
			'key'   => "field_ipo_sl_{$p}_tab",
			'label' => $tab_label,
			'type'  => 'tab',
		),
		array(
			'key'           => "field_ipo_sl_{$p}_title",
			'label'         => 'כותרת',
			'name'          => "{$p}_title",
			'type'          => 'text',
			'default_value' => $defaults['title'],
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => "field_ipo_sl_{$p}_subtitle",
			'label'         => 'כותרת משנה',
			'name'          => "{$p}_subtitle",
			'type'          => 'textarea',
			'rows'          => 2,
			'new_lines'     => 'br',
			'default_value' => $defaults['subtitle'],
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => "field_ipo_sl_{$p}_max_artists",
			'label'         => 'כמה אמנים להציג בכל כרטיס',
			'name'          => "{$p}_max_artists",
			'type'          => 'number',
			'min'           => 0,
			'default_value' => $defaults['max_artists'],
			'instructions'  => 'לפי הסדר בתוכנית. 0 = כולם. לא חל על אמנים שהוזנו ידנית.',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => "field_ipo_sl_{$p}_artists_heading",
			'label'         => 'כותרת מעל האמנים',
			'name'          => "{$p}_artists_heading",
			'type'          => 'text',
			'default_value' => $defaults['artists_heading'],
			'instructions'  => 'למשל "אמנים". ריק = בלי כותרת.',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'          => "field_ipo_sl_{$p}_cards",
			'label'        => 'אירועים',
			'name'         => "{$p}_cards",
			'type'         => 'repeater',
			'layout'       => 'block',
			'collapsed'    => "field_ipo_sl_{$p}_event",
			'button_label' => 'הוספת אירוע',
			'sub_fields'   => ipo_sl_card_fields( $p ),
		),
	);
}

/**
 * Create the field group in ACF once, on the first admin load after deploy.
 *
 * Field groups defined only in code (acf_add_local_field_group) don't reach the
 * edit screens on this site, the same reason the program flash fields moved
 * into ACF (includes/ipo-program-flash.php). Once created, the group is an
 * ordinary ACF field group: it shows under ACF -> Field Groups and is edited
 * there. Delete the option ipo_season_lobby_fields_created to create it again.
 */
const IPO_SL_FIELDS_CREATED = 'ipo_season_lobby_fields_created';

add_action( 'admin_init', 'ipo_sl_create_field_group' );
function ipo_sl_create_field_group() {

	if ( get_option( IPO_SL_FIELDS_CREATED ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_import_field_group' ) ) {
		return;
	}

	update_option( IPO_SL_FIELDS_CREATED, 1, false );

	// Already there (created by hand or by an earlier run): leave it as it is.
	if ( acf_get_field_group( 'group_ipo_season_lobby' ) ) {
		return;
	}

	acf_import_field_group( ipo_sl_field_group() );
}

/**
 * Move a group created as "normal" under the title, once.
 *
 * The first version created the group in the "normal" position, which the
 * edit screen never reaches while ACF Extended Pro 0.8.9.2 crashes on ACF 6.8
 * (see ipo_sl_field_group()). The same crash stops the field group screen
 * before its Position setting, so it can't be changed by hand either.
 */
const IPO_SL_FIELDS_UNDER_TITLE = 'ipo_season_lobby_fields_under_title';

add_action( 'admin_init', 'ipo_sl_move_field_group_under_title', 20 );
function ipo_sl_move_field_group_under_title() {

	if ( get_option( IPO_SL_FIELDS_UNDER_TITLE ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_update_field_group' ) ) {
		return;
	}

	$group = acf_get_field_group( 'group_ipo_season_lobby' );
	if ( ! $group || empty( $group['ID'] ) ) {
		return;
	}

	update_option( IPO_SL_FIELDS_UNDER_TITLE, 1, false );

	if ( 'acf_after_title' !== $group['position'] ) {
		$group['position'] = 'acf_after_title';
		acf_update_field_group( $group );
	}
}

/**
 * Put the card fields of a group that already exists in the order
 * ipo_sl_card_fields() lists them, once. In both sections (main grid and
 * carousel), the top title and the two images moved from the override
 * accordion into the event one.
 */
const IPO_SL_FIELDS_ORDER = 'ipo_season_lobby_fields_order_2';

add_action( 'admin_init', 'ipo_sl_reorder_card_fields', 30 );
function ipo_sl_reorder_card_fields() {

	if ( get_option( IPO_SL_FIELDS_ORDER ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_update_field' ) ) {
		return;
	}

	if ( ! acf_get_field_group( 'group_ipo_season_lobby' ) ) {
		return;
	}

	update_option( IPO_SL_FIELDS_ORDER, 1, false );

	foreach ( array( 'sl_featured', 'sl_carousel' ) as $prefix ) {
		$order = 0;

		foreach ( ipo_sl_card_fields( $prefix ) as $wanted ) {
			$field = acf_get_field( $wanted['key'] );

			if ( $field && ! empty( $field['ID'] ) && (int) $field['menu_order'] !== $order ) {
				$field['menu_order'] = $order;
				acf_update_field( $field );
			}

			$order++;
		}
	}
}

/**
 * The field group, as ACF imports it.
 */
function ipo_sl_field_group() {

	$intro = array(
		array(
			'key'   => 'field_ipo_sl_intro_tab',
			'label' => 'באנר ופתיח',
			'type'  => 'tab',
		),
		array(
			'key'           => 'field_ipo_sl_hero_image',
			'label'         => 'באנר עליון',
			'name'          => 'sl_hero_image',
			'type'          => 'image',
			'return_format' => 'id',
			'preview_size'  => 'medium',
			'instructions'  => 'ריק = התמונה הראשית של העמוד.',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'           => 'field_ipo_sl_hero_image_mobile',
			'label'         => 'באנר עליון למובייל',
			'name'          => 'sl_hero_image_mobile',
			'type'          => 'image',
			'return_format' => 'id',
			'preview_size'  => 'medium',
			'instructions'  => 'ריק = הבאנר הרגיל.',
			'wrapper'       => array( 'width' => '50' ),
		),
		array(
			'key'          => 'field_ipo_sl_intro_title',
			'label'        => 'כותרת הפתיח',
			'name'         => 'sl_intro_title',
			'type'         => 'text',
			'instructions' => 'ריק = שם העמוד.',
		),
		array(
			'key'          => 'field_ipo_sl_intro_text',
			'label'        => 'טקסט הפתיח',
			'name'         => 'sl_intro_text',
			'type'         => 'wysiwyg',
			'tabs'         => 'all',
			'toolbar'      => 'basic',
			'media_upload' => 0,
		),
	);

	$featured = ipo_sl_section_fields(
		'sl_featured',
		'אירועים מרכזיים',
		array(
			'title'           => 'שלא תגידו שלא שמעתם',
			'subtitle'        => "גדולי המנצחים\nוהסולנים בעולם",
			'max_artists'     => 2,
			'artists_heading' => '',
		)
	);

	$carousel = ipo_sl_section_fields(
		'sl_carousel',
		'קרוסלה',
		array(
			'title'           => '',
			'subtitle'        => '',
			'max_artists'     => 6,
			'artists_heading' => 'אמנים',
		)
	);

	return array(
		'key'                   => 'group_ipo_season_lobby',
		'title'                 => 'עמוד עונה – כרטיסי אירועים',
		'fields'                => array_merge( $intro, $featured, $carousel ),
		'location'              => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => IPO_SL_TEMPLATE,
				),
			),
		),
		'menu_order'            => 0,
		// Under the title, not "normal": with ACF Extended Pro 0.8.9.2 on ACF 6.8
		// the edit screen dies while printing the sidebar (Protect page checkbox),
		// and every "normal" box comes after that point. Boxes under the title
		// are printed before it, which is why "תמונה ראשית בהירה" still shows.
		'position'              => 'acf_after_title',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => array( 'the_content' ),
		'active'                => true,
	);
}

/**
 * The event picker: upcoming events first (soonest on top), and every event
 * once the editor searches.
 */
add_filter( 'acf/fields/post_object/query', 'ipo_sl_event_picker_query', 10, 2 );
function ipo_sl_event_picker_query( $args, $field ) {
	if ( 0 !== strpos( $field['key'], 'field_ipo_sl_' ) ) {
		return $args;
	}

	$args['meta_key'] = 'event_date_time';
	$args['orderby']  = 'meta_value';

	if ( empty( $args['s'] ) ) {
		$args['order']      = 'ASC';
		$args['meta_query'] = array(
			array(
				'key'     => 'event_date_time',
				'value'   => current_time( 'Y-m-d 00:00:00' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			),
		);
	} else {
		$args['order'] = 'DESC';
	}

	return $args;
}

/**
 * Picker label: "07.01.2027 19:00 | <program title>", which reads better than
 * the imported event title.
 */
add_filter( 'acf/fields/post_object/result', 'ipo_sl_event_picker_label', 10, 3 );
function ipo_sl_event_picker_label( $text, $post, $field ) {
	if ( 0 !== strpos( $field['key'], 'field_ipo_sl_' ) || 'event' !== $post->post_type ) {
		return $text;
	}

	$date_time  = get_post_meta( $post->ID, 'event_date_time', true );
	$program_id = ipo_sl_normalize_id( get_post_meta( $post->ID, 'related_to_program', true ) );
	$name       = $program_id ? get_the_title( $program_id ) : get_post_meta( $post->ID, 'event_featured_name', true );

	if ( ! $name ) {
		return $text;
	}

	$date = $date_time ? date( 'd.m.Y H:i', strtotime( $date_time ) ) : '';

	return trim( $date . ' | ' . $name, ' |' );
}

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'ipo_sl_enqueue', 30 );
function ipo_sl_enqueue() {
	if ( ! is_page_template( IPO_SL_TEMPLATE ) ) {
		return;
	}

	$rel  = 'assets/styles/season-lobby.css';
	$file = get_stylesheet_directory() . '/' . $rel;

	wp_enqueue_style(
		'ipo-season-lobby',
		get_stylesheet_directory_uri() . '/' . $rel,
		array(),
		file_exists( $file ) ? filemtime( $file ) : null
	);
}

/**
 * The carousel. Printed after the footer scripts so Splide is defined by the
 * time it runs, including when ipo-delay-js runs every script on the first
 * interaction. Until it runs, the CSS shows the cards as a scrolling row.
 */
add_action( 'wp_footer', 'ipo_sl_carousel_script', 100 );
function ipo_sl_carousel_script() {
	if ( ! is_page_template( IPO_SL_TEMPLATE ) ) {
		return;
	}
	?>
<script>
(function () {
	function mount() {
		if (typeof Splide === 'undefined') { return; }
		var isEn = document.documentElement.getAttribute('lang') === 'en-US';
		var arrowLeft  = '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_7.svg" alt="">';
		var arrowRight = '<img src="/wp-content/uploads/2023/02/small_icons_arrow_copy_6.svg" alt="">';
		var els = document.querySelectorAll('.ipo-sl-splide');
		for (var i = 0; i < els.length; i++) {
			var el = els[i];
			if (el.dataset.mounted === '1') { continue; }
			el.dataset.mounted = '1';
			var splide = new Splide(el, {
				type: 'slide',
				direction: isEn ? 'ltr' : 'rtl',
				perPage: 3,
				perMove: 1,
				gap: '40px',
				pagination: false,
				arrows: true,
				speed: 500,
				breakpoints: {
					1199: { perPage: 2, gap: '30px' },
					767:  { perPage: 1, gap: '16px', arrows: false, padding: isEn ? { right: '15%' } : { left: '15%' } }
				}
			});
			splide.on('arrows:mounted', function (prev, next) {
				prev.innerHTML = isEn ? arrowLeft : arrowRight;
				next.innerHTML = isEn ? arrowRight : arrowLeft;
			});
			splide.mount();
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', mount);
	} else {
		mount();
	}
})();
</script>
	<?php
}

/* -------------------------------------------------------------------------
 * Data
 * ---------------------------------------------------------------------- */

/**
 * A post ID from whatever ACF or postmeta handed over: an ID, a numeric
 * string, a WP_Post, or an array holding one of those.
 */
function ipo_sl_normalize_id( $value ) {
	if ( is_array( $value ) ) {
		$value = reset( $value );
	}
	if ( $value instanceof WP_Post ) {
		return (int) $value->ID;
	}
	if ( is_array( $value ) && isset( $value['ID'] ) ) {
		return (int) $value['ID'];
	}

	return is_numeric( $value ) ? (int) $value : 0;
}

/**
 * An attachment ID from an image value: an ID, an ACF image array or a URL.
 */
function ipo_sl_image_id( $value ) {
	if ( is_array( $value ) ) {
		$value = isset( $value['ID'] ) ? $value['ID'] : ( isset( $value['id'] ) ? $value['id'] : 0 );
	}
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}
	if ( is_string( $value ) && '' !== $value ) {
		return (int) attachment_url_to_postid( $value );
	}

	return 0;
}

function ipo_sl_is_en() {
	return defined( 'ICL_LANGUAGE_CODE' ) && 'he' !== ICL_LANGUAGE_CODE;
}

/**
 * The same post in the page's language, or the original when it has none.
 */
function ipo_sl_translate( $post_id, $type ) {
	return $post_id ? (int) apply_filters( 'wpml_object_id', $post_id, $type, true ) : 0;
}

/**
 * Artists as [ name, role ] from the program, in program order.
 */
function ipo_sl_program_artists( $program_id, $max ) {
	$artists = array();

	foreach ( (array) get_field( 'program_artists', $program_id, false ) as $artist_id ) {
		$artist_id = ipo_sl_translate( ipo_sl_normalize_id( $artist_id ), 'artist' );
		if ( ! $artist_id || 'publish' !== get_post_status( $artist_id ) ) {
			continue;
		}

		$role = get_field( 'artist_role_override', $artist_id );
		if ( ! $role ) {
			$terms = get_the_terms( $artist_id, 'artist_role' );
			$role  = is_array( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';
		}

		$artists[] = array(
			'name' => get_the_title( $artist_id ),
			'role' => $role,
		);

		if ( $max > 0 && count( $artists ) >= $max ) {
			break;
		}
	}

	return $artists;
}

/**
 * Pieces as [ composer, piece ] from the program plan, without the break rows.
 */
function ipo_sl_program_pieces( $program_id ) {
	$pieces = array();

	foreach ( (array) get_field( 'program_plan', $program_id ) as $row ) {
		if ( ! is_array( $row ) || ! empty( $row['break'] ) ) {
			continue;
		}
		$composer = isset( $row['title'] ) ? trim( wp_strip_all_tags( $row['title'] ) ) : '';
		$piece    = isset( $row['subtitle'] ) ? trim( wp_strip_all_tags( $row['subtitle'] ) ) : '';
		if ( '' === $composer && '' === $piece ) {
			continue;
		}
		$pieces[] = array(
			'composer' => $composer,
			'piece'    => $piece,
		);
	}

	return $pieces;
}

/**
 * "יום א׳ | 3.1 | 20:00" in Hebrew, "Sun | 3.1 | 20:00" otherwise.
 */
function ipo_sl_event_date( $event_id ) {
	$date_time = get_field( 'event_date_time', $event_id );
	$ts        = $date_time ? strtotime( $date_time ) : false;
	if ( ! $ts ) {
		return '';
	}

	if ( ipo_sl_is_en() ) {
		$day = date( 'D', $ts );
	} else {
		$letters = array( 'א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ש' );
		$day     = 'יום ' . $letters[ (int) date( 'w', $ts ) ] . '׳';
	}

	return $day . ' | ' . date( 'j.n', $ts ) . ' | ' . date( 'H:i', $ts );
}

/**
 * The venue: the parent of the event's hall term ("Charles Bronfman
 * Auditorium, Tel Aviv"), else the hall term, else the imported venue text.
 */
function ipo_sl_event_location( $event_id ) {
	$terms = wp_get_post_terms( $event_id, 'location' );

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$term = $terms[0];
		if ( $term->parent ) {
			$parent = get_term( $term->parent, 'location' );
			if ( $parent && ! is_wp_error( $parent ) ) {
				return $parent->name;
			}
		}
		return $term->name;
	}

	$location = (string) get_field( 'imported_location', $event_id );

	return trim( str_replace( ',', ', ', $location ) );
}

/**
 * The ticket button: [ url, text, active ]. No text means no button.
 *
 * An override link always gives an active button. Otherwise: sold out shows
 * the event's sold-out text, a past event says so, a program with purchase
 * hidden shows its own note, and the link is the event's, then the program's,
 * then the ticketing system's order page.
 */
function ipo_sl_ticket( $row, $event_id, $program_id ) {
	$is_en = ipo_sl_is_en();
	$text  = ! empty( $row['button_text'] ) ? $row['button_text'] : ( $is_en ? 'Tickets' : 'לכרטיסים' );

	if ( ! empty( $row['ticket_link'] ) ) {
		return array( $row['ticket_link'], $text, true );
	}

	if ( ! $event_id ) {
		return array( '', '', false );
	}

	$sold_out = get_field( 'sold_out', $event_id );
	if ( $sold_out ) {
		return array( '', $sold_out, false );
	}

	$event = new ipo_event( $event_id );
	if ( $event->is_passed() ) {
		return array( '', $is_en ? 'Event Passed' : 'אירוע עבר', false );
	}

	if ( $program_id && get_field( 'Hiding_purchase', $program_id ) ) {
		$note = get_field( 'button_to_purchasetxt', $program_id );
		return array( '', $note ? $note : ( $is_en ? 'Coming soon' : 'בקרוב' ), false );
	}

	$url = get_field( 'event_ticket_link', $event_id );
	if ( ! $url && $program_id ) {
		$url = get_field( 'ticket_purchase', $program_id );
	}
	if ( ! $url ) {
		$api_id = get_field( 'event_api_id', $event_id );
		if ( $api_id ) {
			$url = 'https://ipo.pres.global/order/' . rawurlencode( $api_id ) . ( $is_en ? '?lang=en' : '' );
		}
	}

	return $url ? array( $url, $text, true ) : array( '', '', false );
}

/**
 * Everything one card shows, with the row's overrides applied.
 */
function ipo_sl_card_data( $row, $max_artists ) {
	$is_en = ipo_sl_is_en();

	$event_id = ipo_sl_translate( ipo_sl_normalize_id( isset( $row['event'] ) ? $row['event'] : 0 ), 'event' );
	if ( $event_id && 'event' !== get_post_type( $event_id ) ) {
		$event_id = 0;
	}

	$program_id = $event_id ? ipo_sl_normalize_id( get_field( 'related_to_program', $event_id, false ) ) : 0;
	$program_id = ipo_sl_translate( $program_id, 'program' );
	if ( $program_id && 'program' !== get_post_type( $program_id ) ) {
		$program_id = 0;
	}
	$program = $program_id ? new ipo_program( $program_id ) : null;

	// Title.
	$label = ! empty( $row['label'] ) ? $row['label'] : '';
	if ( ! $label && $program ) {
		$label = $program->get_title();
	}
	if ( ! $label && $event_id ) {
		$label = get_field( 'event_featured_name', $event_id );
	}

	// Images.
	$image = ipo_sl_image_id( isset( $row['image'] ) ? $row['image'] : 0 );
	if ( ! $image && $program ) {
		$image = ipo_sl_image_id( $program->get_image() );
	}
	$image_mobile = ipo_sl_image_id( isset( $row['image_mobile'] ) ? $row['image_mobile'] : 0 );

	// Artists.
	$artists = array();
	if ( ! empty( $row['artists'] ) && is_array( $row['artists'] ) ) {
		foreach ( $row['artists'] as $artist ) {
			if ( ! empty( $artist['name'] ) || ! empty( $artist['role'] ) ) {
				$artists[] = array(
					'name' => isset( $artist['name'] ) ? $artist['name'] : '',
					'role' => isset( $artist['role'] ) ? $artist['role'] : '',
				);
			}
		}
	} elseif ( $program_id ) {
		$artists = ipo_sl_program_artists( $program_id, (int) $max_artists );
	}

	// Pieces.
	$pieces = array();
	if ( ! empty( $row['program'] ) && is_array( $row['program'] ) ) {
		foreach ( $row['program'] as $piece ) {
			if ( ! empty( $piece['composer'] ) || ! empty( $piece['piece'] ) ) {
				$pieces[] = array(
					'composer' => isset( $piece['composer'] ) ? $piece['composer'] : '',
					'piece'    => isset( $piece['piece'] ) ? $piece['piece'] : '',
				);
			}
		}
	} elseif ( $program_id ) {
		$pieces = ipo_sl_program_pieces( $program_id );
	}

	$program_heading = ! empty( $row['program_heading'] ) ? $row['program_heading'] : ( $is_en ? 'Program' : 'תוכנית' );

	// When and where.
	$date     = ! empty( $row['date'] ) ? $row['date'] : ( $event_id ? ipo_sl_event_date( $event_id ) : '' );
	$location = ! empty( $row['location'] ) ? $row['location'] : ( $event_id ? ipo_sl_event_location( $event_id ) : '' );

	list( $ticket_url, $ticket_text, $ticket_active ) = ipo_sl_ticket( $row, $event_id, $program_id );

	$color = ! empty( $row['button_color'] ) ? sanitize_hex_color( $row['button_color'] ) : '';

	return array(
		'label'           => $label,
		'image'           => $image,
		'image_mobile'    => $image_mobile,
		'link'            => $program_id ? add_query_arg( 'event_id', $event_id, get_permalink( $program_id ) ) : '',
		'artists'         => $artists,
		'program_heading' => $program_heading,
		'pieces'          => $pieces,
		'date'            => $date,
		'location'        => $location,
		'ticket_url'      => $ticket_url,
		'ticket_text'     => $ticket_text,
		'ticket_active'   => $ticket_active,
		'color'           => $color ? $color : IPO_SL_DEFAULT_BUTTON,
	);
}

/* -------------------------------------------------------------------------
 * Markup
 * ---------------------------------------------------------------------- */

/**
 * <picture> with an optional mobile source. $size is the desktop image size.
 */
function ipo_sl_picture( $image, $image_mobile, $alt, $size = 'large' ) {
	if ( ! $image && ! $image_mobile ) {
		return '';
	}
	if ( ! $image ) {
		$image = $image_mobile;
	}

	$html = '<picture>';
	if ( $image_mobile && $image_mobile !== $image ) {
		$mobile_src = wp_get_attachment_image_url( $image_mobile, 'large' );
		if ( $mobile_src ) {
			$html .= '<source media="(max-width: 767px)" srcset="' . esc_url( $mobile_src ) . '">';
		}
	}
	$html .= wp_get_attachment_image(
		$image,
		$size,
		false,
		array(
			'alt'     => $alt,
			'loading' => 'lazy',
		)
	);
	$html .= '</picture>';

	return $html;
}

/**
 * One card. $variant: "featured" (image beside the text) or "carousel"
 * (image above it).
 */
function ipo_sl_render_card( $card, $variant, $artists_heading = '' ) {
	$picture = ipo_sl_picture( $card['image'], $card['image_mobile'], wp_strip_all_tags( $card['label'] ) );
	?>
	<article class="ipo-sl-card ipo-sl-card--<?php echo esc_attr( $variant ); ?>" style="--ipo-sl-btn: <?php echo esc_attr( $card['color'] ); ?>;">

		<?php if ( $picture ) : ?>
			<div class="ipo-sl-card__media">
				<?php if ( $card['link'] ) : ?>
					<a href="<?php echo esc_url( $card['link'] ); ?>" tabindex="-1" aria-hidden="true"><?php echo $picture; // phpcs:ignore WordPress.Security.EscapeOutput -- built from wp_get_attachment_image ?></a>
				<?php else : ?>
					<?php echo $picture; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="ipo-sl-card__body">
			<div class="ipo-sl-card__inner">

				<?php if ( $card['label'] ) : ?>
					<p class="ipo-sl-card__label">
						<?php if ( $card['link'] ) : ?>
							<a href="<?php echo esc_url( $card['link'] ); ?>"><?php echo esc_html( $card['label'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $card['label'] ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( $card['artists'] ) : ?>
					<?php if ( $artists_heading ) : ?>
						<p class="ipo-sl-card__small-heading"><?php echo esc_html( $artists_heading ); ?></p>
					<?php endif; ?>
					<ul class="ipo-sl-card__artists">
						<?php foreach ( $card['artists'] as $artist ) : ?>
							<li>
								<?php if ( $artist['name'] ) : ?>
									<span class="ipo-sl-card__artist-name"><?php echo esc_html( $artist['name'] ); ?></span>
								<?php endif; ?>
								<?php if ( $artist['role'] ) : ?>
									<span class="ipo-sl-card__artist-role"><?php echo esc_html( $artist['role'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $card['pieces'] ) : ?>
					<div class="ipo-sl-card__program">
						<p class="ipo-sl-card__program-heading"><?php echo esc_html( $card['program_heading'] ); ?></p>
						<ul>
							<?php foreach ( $card['pieces'] as $piece ) : ?>
								<li>
									<?php if ( $piece['composer'] ) : ?>
										<strong><?php echo esc_html( rtrim( $piece['composer'], ': ' ) ); ?><?php echo $piece['piece'] ? ':' : ''; ?></strong>
									<?php endif; ?>
									<?php echo esc_html( $piece['piece'] ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( $card['date'] || $card['location'] ) : ?>
					<div class="ipo-sl-card__when">
						<?php if ( $card['date'] ) : ?>
							<p class="ipo-sl-card__date"><?php echo esc_html( $card['date'] ); ?></p>
						<?php endif; ?>
						<?php if ( $card['location'] ) : ?>
							<p class="ipo-sl-card__location"><?php echo esc_html( $card['location'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $card['ticket_active'] ) : ?>
					<a class="ipo-sl-card__btn" href="<?php echo esc_url( $card['ticket_url'] ); ?>"><?php echo esc_html( $card['ticket_text'] ); ?></a>
				<?php elseif ( $card['ticket_text'] ) : ?>
					<span class="ipo-sl-card__btn is-disabled"><?php echo esc_html( $card['ticket_text'] ); ?></span>
				<?php endif; ?>

			</div>
		</div>
	</article>
	<?php
}
