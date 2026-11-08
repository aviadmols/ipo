<?php
/**
 * Program media: a new translation opens with the original's images, each in its own language's copy.
 *
 * The image fields are Copy once (see ipo-wpml-copy-once.php), but WPML only
 * copies a field the translation doesn't have yet, and ACF saves the empty
 * image fields of the new-translation form first. So the form itself is filled
 * here, on the new-translation screen.
 *
 * Each image goes in as its duplicate in the translation's language (WPML
 * Media makes one per upload), not the original's attachment. Changing an
 * image, or its alt text and title, on one language's page leaves the other
 * page alone. Both duplicates point at the same file on disk, so replacing
 * the file itself still shows in both.
 *
 * Translations made in WPML's translation editor never reach this screen.
 * They get the images when the translation job completes, once per program.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_PROGRAM_MEDIA_TYPES  = array( 'image', 'gallery', 'file' );
const IPO_PROGRAM_MEDIA_FILLED = '_ipo_program_media_filled';

/**
 * The stored value with each attachment swapped for its version in $lang.
 * An attachment with no version in $lang stays as it is.
 */
function ipo_program_media_in_language( $value, $lang ) {
	$swap = static function ( $attachment_id ) use ( $lang ) {
		return (int) apply_filters( 'wpml_object_id', (int) $attachment_id, 'attachment', true, $lang );
	};

	if ( is_array( $value ) ) {
		return array_values( array_filter( array_map( $swap, $value ) ) );
	}

	return is_numeric( $value ) && (int) $value ? $swap( $value ) : $value;
}

/**
 * The program the new translation is made from: the source language the "+" link names, or the original.
 */
function ipo_program_media_source( $trid, $lang, $source_lang = '' ) {
	$source = 0;

	foreach ( (array) apply_filters( 'wpml_get_element_translations', null, $trid, 'post_program' ) as $translation ) {
		if ( $translation->language_code === $lang ) {
			continue;
		}
		if ( $source_lang && $translation->language_code === $source_lang ) {
			return (int) $translation->element_id;
		}
		if ( ! empty( $translation->original ) ) {
			$source = (int) $translation->element_id;
		}
	}

	return $source;
}

// The new-translation screen opens with the images already in the fields.
foreach ( IPO_PROGRAM_MEDIA_TYPES as $ipo_media_type ) {
	add_filter( 'acf/load_value/type=' . $ipo_media_type, 'ipo_program_media_prefill', 10, 3 );
}
unset( $ipo_media_type );

function ipo_program_media_prefill( $value, $post_id, $field ) {
	global $pagenow;

	if ( $value || 'post-new.php' !== $pagenow || empty( $_GET['trid'] ) || empty( $_GET['lang'] )
		|| ! is_numeric( $post_id ) || 'program' !== get_post_type( $post_id ) || empty( $field['name'] ) ) {
		return $value;
	}

	$lang        = sanitize_key( $_GET['lang'] );
	$source_lang = isset( $_GET['source_lang'] ) ? sanitize_key( $_GET['source_lang'] ) : '';
	$source      = ipo_program_media_source( (int) $_GET['trid'], $lang, $source_lang );

	if ( ! $source ) {
		return $value;
	}

	return ipo_program_media_in_language( get_post_meta( $source, $field['name'], true ), $lang );
}

// A translation from WPML's translation editor: fill the empty image fields once, when the job first completes.
add_action( 'wpml_pro_translation_completed', 'ipo_program_media_on_translation', 20 );
function ipo_program_media_on_translation( $post_id ) {
	if ( 'program' !== get_post_type( $post_id ) || get_post_meta( $post_id, IPO_PROGRAM_MEDIA_FILLED, true )
		|| ! function_exists( 'acf_get_field_groups' ) ) {
		return;
	}

	$details = apply_filters( 'wpml_post_language_details', null, $post_id );
	$trid    = apply_filters( 'wpml_element_trid', null, $post_id, 'post_program' );
	$lang    = is_array( $details ) ? (string) ( $details['language_code'] ?? '' ) : '';
	$source  = ( $trid && $lang ) ? ipo_program_media_source( $trid, $lang ) : 0;

	if ( ! $source || $source === (int) $post_id ) {
		return;
	}

	foreach ( acf_get_field_groups( array( 'post_id' => $post_id ) ) as $group ) {
		foreach ( acf_get_fields( $group ) as $field ) {
			if ( ! in_array( $field['type'], IPO_PROGRAM_MEDIA_TYPES, true ) || get_post_meta( $post_id, $field['name'], true ) ) {
				continue;
			}

			$value = ipo_program_media_in_language( get_post_meta( $source, $field['name'], true ), $lang );
			if ( $value ) {
				update_field( $field['key'], $value, $post_id );
			}
		}
	}

	update_post_meta( $post_id, IPO_PROGRAM_MEDIA_FILLED, 1 );
}
