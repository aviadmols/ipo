<?php
/**
 * Program artists follow the Hebrew program, in each translation's own language.
 *
 * Every other field is edited per language (see ipo-wpml-copy-once.php); the
 * artist list is the exception. Saving the Hebrew program sets the list on its
 * translations, and saving a translation puts the Hebrew list back, so an
 * edit made in English doesn't last. The new-translation screen opens with
 * the list already in the field.
 *
 * A Hebrew program with no artists leaves its translations' lists alone,
 * unless that save is the one that emptied it: some older English programs
 * carry artists the Hebrew page never had.
 *
 * An artist with no version in the translation's language stays as it is. The
 * program page shows it the same way, and the next save swaps it for the
 * translation once that exists.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_PROGRAM_ARTISTS_FIELD  = 'field_62b3326a12de5';
const IPO_PROGRAM_ARTISTS_SOURCE = 'he';

/**
 * The stored value as artist IDs. Some rows hold one bare ID instead of an array.
 */
function ipo_program_artists_ids( $post_id ) {
	return ipo_program_artists_normalize( get_post_meta( $post_id, 'program_artists', true ) );
}

function ipo_program_artists_normalize( $value ) {
	return array_values( array_filter( array_map( 'intval', (array) $value ) ) );
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

/**
 * The Hebrew program in a translation group, or null when there is none.
 */
function ipo_program_artists_source( $translations ) {
	foreach ( $translations as $translation ) {
		if ( IPO_PROGRAM_ARTISTS_SOURCE === $translation->language_code && ! empty( $translation->element_id ) ) {
			return $translation;
		}
	}

	return null;
}

// The new-translation screen opens with the artists already in the field.
add_filter( 'acf/load_value/key=' . IPO_PROGRAM_ARTISTS_FIELD, 'ipo_program_artists_prefill' );
function ipo_program_artists_prefill( $value ) {
	global $pagenow;

	if ( $value || 'post-new.php' !== $pagenow || empty( $_GET['trid'] ) || empty( $_GET['lang'] ) ) {
		return $value;
	}

	$lang   = sanitize_key( $_GET['lang'] );
	$source = ipo_program_artists_source( ipo_program_artists_translations( (int) $_GET['trid'] ) );

	if ( ! $source || $source->language_code === $lang ) {
		return $value;
	}

	return ipo_program_artists_in_language( ipo_program_artists_ids( $source->element_id ), $lang );
}

// Translations say where their artists come from. Only on the program editor,
// so the note never reaches the field group screen and gets saved into the field.
add_filter( 'acf/load_field/key=' . IPO_PROGRAM_ARTISTS_FIELD, 'ipo_program_artists_instructions' );
function ipo_program_artists_instructions( $field ) {
	global $pagenow, $typenow;

	if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) && 'program' === $typenow
		&& IPO_PROGRAM_ARTISTS_SOURCE !== apply_filters( 'wpml_current_language', null ) ) {
		$field['instructions'] = trim( $field['instructions'] . ' האמנים מתעדכנים אוטומטית מהדף בעברית. שינוי כאן יוחלף בשמירה.' );
	}

	return $field;
}

// Which Hebrew programs had their list changed by this request's save.
$GLOBALS['ipo_program_artists_changed'] = array();

add_filter( 'acf/update_value/key=' . IPO_PROGRAM_ARTISTS_FIELD, 'ipo_program_artists_note_change', 10, 2 );
function ipo_program_artists_note_change( $value, $post_id ) {
	if ( is_numeric( $post_id ) && ipo_program_artists_normalize( $value ) !== ipo_program_artists_ids( $post_id ) ) {
		$GLOBALS['ipo_program_artists_changed'][ (int) $post_id ] = true;
	}

	return $value;
}

// Late enough for WPML to know the post's language, which it sets after ACF has saved.
add_action( 'wpml_after_save_post', 'ipo_program_artists_on_save', 20 );
add_action( 'wpml_pro_translation_completed', 'ipo_program_artists_on_save', 20 );
function ipo_program_artists_on_save( $post_id ) {

	if ( 'program' !== get_post_type( $post_id ) || wp_is_post_revision( $post_id ) || ! function_exists( 'update_field' ) ) {
		return;
	}

	$trid = apply_filters( 'wpml_element_trid', null, $post_id, 'post_program' );
	if ( ! $trid ) {
		return;
	}

	$translations = ipo_program_artists_translations( $trid );
	$source       = ipo_program_artists_source( $translations );
	if ( ! $source ) {
		return;
	}

	$source_id  = (int) $source->element_id;
	$artist_ids = ipo_program_artists_ids( $source_id );

	if ( ! $artist_ids && empty( $GLOBALS['ipo_program_artists_changed'][ $source_id ] ) ) {
		return;
	}

	// Saving Hebrew updates every translation; saving a translation updates only that one.
	$targets = ( (int) $post_id === $source_id ) ? $translations : array();
	foreach ( $translations as $translation ) {
		if ( (int) $translation->element_id === (int) $post_id && (int) $post_id !== $source_id ) {
			$targets = array( $translation );
		}
	}

	foreach ( $targets as $translation ) {
		$target_id = (int) $translation->element_id;
		if ( ! $target_id || $target_id === $source_id ) {
			continue;
		}

		$wanted = ipo_program_artists_in_language( $artist_ids, $translation->language_code );
		if ( $wanted !== ipo_program_artists_ids( $target_id ) ) {
			update_field( IPO_PROGRAM_ARTISTS_FIELD, $wanted, $target_id );
		}
	}
}
