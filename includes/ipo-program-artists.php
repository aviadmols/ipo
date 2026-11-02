<?php
/**
 * Program artists: a translated program gets the original's artists, in its own language.
 *
 * The list is filled once, on the new-translation screen and again on save for
 * programs that reach the save with an empty list. After that each language
 * edits its own list, as with every other field (see ipo-wpml-copy-once.php).
 *
 * An artist with no version in the program's language stays as it is. The
 * program page shows it the same way, and the next save swaps it for the
 * translation once that exists.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_PROGRAM_ARTISTS_FIELD  = 'field_62b3326a12de5';
const IPO_PROGRAM_ARTISTS_FILLED = '_ipo_program_artists_filled';

/**
 * The stored value as artist IDs. Some rows hold one bare ID instead of an array.
 */
function ipo_program_artists_ids( $post_id ) {
	return array_values( array_filter( array_map( 'intval', (array) get_post_meta( $post_id, 'program_artists', true ) ) ) );
}

function ipo_program_artists_in_language( $artist_ids, $lang ) {
	$translated = array();

	foreach ( $artist_ids as $artist_id ) {
		$translated[] = (int) apply_filters( 'wpml_object_id', $artist_id, 'artist', true, $lang );
	}

	return array_values( array_unique( $translated ) );
}

function ipo_program_artists_translations( $trid ) {
	return (array) apply_filters( 'wpml_get_element_translations', null, $trid, 'post_program' );
}

// The new-translation screen opens with the artists already in the field.
add_filter( 'acf/load_value/key=' . IPO_PROGRAM_ARTISTS_FIELD, 'ipo_program_artists_prefill' );
function ipo_program_artists_prefill( $value ) {
	global $pagenow;

	if ( $value || 'post-new.php' !== $pagenow || empty( $_GET['trid'] ) || empty( $_GET['lang'] ) ) {
		return $value;
	}

	$lang = sanitize_key( $_GET['lang'] );

	foreach ( ipo_program_artists_translations( (int) $_GET['trid'] ) as $translation ) {
		if ( ! empty( $translation->original ) && $translation->language_code !== $lang ) {
			return ipo_program_artists_in_language( ipo_program_artists_ids( $translation->element_id ), $lang );
		}
	}

	return $value;
}

// Late enough for WPML to know the post's language, which it sets after ACF has saved.
add_action( 'wpml_after_save_post', 'ipo_program_artists_on_save', 20 );
add_action( 'wpml_pro_translation_completed', 'ipo_program_artists_on_save', 20 );
function ipo_program_artists_on_save( $post_id ) {

	if ( 'program' !== get_post_type( $post_id ) || ! function_exists( 'update_field' ) ) {
		return;
	}

	$trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_program' );
	if ( ! $trid ) {
		return;
	}

	$original = null;
	$current  = null;
	foreach ( ipo_program_artists_translations( $trid ) as $translation ) {
		if ( ! empty( $translation->original ) ) {
			$original = $translation;
		}
		if ( (int) $translation->element_id === (int) $post_id ) {
			$current = $translation;
		}
	}

	if ( ! $original || ! $current || $original === $current ) {
		return;
	}

	$stored     = ipo_program_artists_ids( $post_id );
	$artist_ids = $stored;

	if ( ! $artist_ids && ! get_post_meta( $post_id, IPO_PROGRAM_ARTISTS_FILLED, true ) ) {
		$artist_ids = ipo_program_artists_ids( $original->element_id );
		if ( $artist_ids ) {
			update_post_meta( $post_id, IPO_PROGRAM_ARTISTS_FILLED, 1 );
		}
	}

	$artist_ids = ipo_program_artists_in_language( $artist_ids, $current->language_code );

	if ( $artist_ids !== $stored ) {
		update_field( IPO_PROGRAM_ARTISTS_FIELD, $artist_ids, $post_id );
	}
}
