<?php
/**
 * One time: the English translation of the 90th festival lobby (page 67610).
 *
 * On the first admin screen an administrator opens after this is deployed,
 * a draft English page is created and linked to the Hebrew one in WPML. It
 * gets every field of the Hebrew page, with the texts in English. The cards
 * keep the same events: ipo-season-lobby.php shows each event's program,
 * artists and venue in English.
 *
 * Runs once (option below) and never touches an English version that already
 * exists. Once the page is there this file can be deleted, with its line in
 * functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_SL_EN_SOURCE = 67610;
const IPO_SL_EN_DONE   = 'ipo_season_lobby_en_page';

add_action( 'admin_init', 'ipo_sl_create_english_page', 50 );
function ipo_sl_create_english_page() {

	if ( get_option( IPO_SL_EN_DONE ) || ! current_user_can( 'manage_options' ) || ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
		return;
	}

	$source = get_post( IPO_SL_EN_SOURCE );
	if ( ! $source || 'page' !== $source->post_type ) {
		return;
	}

	$trid = apply_filters( 'wpml_element_trid', null, $source->ID, 'post_page' );
	if ( ! $trid ) {
		return;
	}

	update_option( IPO_SL_EN_DONE, 'started', false );

	$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_page' );
	if ( ! empty( $translations['en'] ) ) {
		update_option( IPO_SL_EN_DONE, (int) $translations['en']->element_id, false );
		return;
	}

	$en_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'draft',
			'post_title'   => '90th Anniversary Festival',
			'post_name'    => '90th-festival',
			'post_author'  => get_current_user_id(),
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $en_id ) || ! $en_id ) {
		delete_option( IPO_SL_EN_DONE ); // Try again on the next admin screen.
		return;
	}

	do_action(
		'wpml_set_element_language_details',
		array(
			'element_id'           => $en_id,
			'element_type'         => 'post_page',
			'trid'                 => $trid,
			'language_code'        => 'en',
			'source_language_code' => 'he',
		)
	);

	// Every field of the Hebrew page (the template and the ACF fields with
	// their field references), the texts in English.
	foreach ( get_post_meta( $source->ID ) as $key => $values ) {
		if ( preg_match( '/^_(edit_|wpml|icl|wp_old_)/', $key ) ) {
			continue;
		}
		foreach ( $values as $value ) {
			$value = maybe_unserialize( $value );
			if ( 'sl_intro_text' === $key ) {
				$value = ipo_sl_en_intro_text();
			} elseif ( is_string( $value ) && 0 === strpos( $key, 'sl_' ) ) {
				$value = ipo_sl_en_text( $value );
			}
			add_post_meta( $en_id, $key, wp_slash( $value ) );
		}
	}

	update_option( IPO_SL_EN_DONE, (int) $en_id, false );
}

/**
 * A Hebrew text of the page in English. Anything not listed is kept as is
 * (numbers, colours, IDs, and any text that changed since this was written).
 */
function ipo_sl_en_text( $value ) {
	$text = trim( str_replace( "\r\n", "\n", $value ) );

	if ( preg_match( '/^קונצרט קאמרי מס[\'׳]\s*(\d+)$/u', $text, $m ) ) {
		return 'Chamber Concert No. ' . $m[1];
	}
	if ( preg_match( '/^קונצרט מס[\'׳]\s*(\d+)$/u', $text, $m ) ) {
		return 'Concert No. ' . $m[1];
	}

	$map = array(
		'פסטיבל ה-90'                   => 'The 90th Festival',
		'שלא תגידו שלא שמעתם'            => "you can't say you haven't heard",
		"גדולי המנצחים\nוהסולנים בעולם" => "The world's greatest\nconductors and soloists",
		'קונצרט פתיחת הפסטיבל'           => 'Festival Opening Concert',
		'לכרטיסים'                      => 'Tickets',
		'מנגנים מוצרט'                  => 'Playing Mozart',
		'רסיטל בטהובן'                  => 'Beethoven Recital',
		'מנגנים בטהובן'                 => 'Playing Beethoven',
		'קונצרטים קאמריים מיוחדים'       => 'Special Chamber Concerts',
		'כותרת'                         => '', // A placeholder left on the Hebrew page.
		'אמנים'                         => 'Artists',
	);

	return isset( $map[ $text ] ) ? $map[ $text ] : $value;
}

function ipo_sl_en_intro_text() {
	return "To mark its 90th season, from January 3 to 9, the Orchestra will hold a celebratory festival of 9 special concerts, together with great artists who have been close friends of the Orchestra for many years.\n\n"
		. "We are especially delighted to welcome Maestro Zubin Mehta back to the stage of the Charles Bronfman Auditorium for the festival's opening concert, an all-Mozart program with Lahav Shani at the piano. Lahav will also conduct Beethoven's monumental Ninth Symphony in two concerts, as well as a special Classical & Jazz concert that brings together Tchaikovsky's Nutcracker and Duke Ellington's version of it.\n\n"
		. "Joining the celebration are Martha Argerich, Maxim Vengerov, Gianandrea Noseda, Pinchas Zukerman, Amanda Forsyth, Uri Kam, Asi Matathias, the Orchestra's principal cellist Lia Chen Perlov, and principal violist Matan Noussimovitch.\n\n"
		. "Celebrating 90 beyond the concert hall, too!\n"
		. 'Throughout the festival, special events and activities will take place across the Charles Bronfman Auditorium. Details coming soon.';
}
