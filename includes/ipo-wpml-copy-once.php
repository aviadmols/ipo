<?php
/**
 * WPML fields set to "Copy" become "Copy once", so each language is edited on its own.
 *
 * A Copy field is locked in the translation and overwritten there every time
 * the original is saved: fix the English second title, and the next Hebrew
 * save puts the Hebrew one back. Copy once fills the field when the
 * translation is created and leaves it alone after that (ACFML 1.9+).
 *
 * This covers post fields and taxonomy-term fields. It also unlocks the ones a
 * plugin's wpml-config.xml fixed to Copy (hero slider, Yoast), which WPML would
 * otherwise put back. WPML Media's own flags (_wpml_media_*) keep their
 * setting: they steer media duplication, not content.
 *
 * Runs on every admin request and only saves when it finds a Copy entry, so a
 * field set back to Copy, by hand or by a plugin update, is switched again.
 */

const IPO_WPML_COPY      = 1;
const IPO_WPML_COPY_ONCE = 3;

// ACF's own setting, which ACFML reads for new fields and new repeater rows.
add_filter( 'acf/load_field', 'ipo_acf_field_copy_once', 20 );
function ipo_acf_field_copy_once( $field ) {
	if ( isset( $field['wpml_cf_preferences'] ) && IPO_WPML_COPY === (int) $field['wpml_cf_preferences'] ) {
		$field['wpml_cf_preferences'] = IPO_WPML_COPY_ONCE;
	}

	return $field;
}

// WPML's own list, the one it copies by.
add_action( 'admin_init', 'ipo_wpml_fields_copy_once' );
function ipo_wpml_fields_copy_once() {
	global $sitepress, $iclTranslationManagement;

	if ( ! is_object( $sitepress ) || ! method_exists( $sitepress, 'get_setting' ) ) {
		return;
	}

	$tm = $sitepress->get_setting( 'translation-management' );
	if ( ! is_array( $tm ) ) {
		return;
	}

	$changed = [];
	foreach ( [ 'custom_fields_translation', 'custom_term_fields_translation' ] as $list ) {
		foreach ( (array) ( $tm[ $list ] ?? [] ) as $key => $mode ) {
			if ( IPO_WPML_COPY !== (int) $mode || 0 === strpos( (string) $key, '_wpml_media_' ) ) {
				continue;
			}
			$tm[ $list ][ $key ]      = IPO_WPML_COPY_ONCE;
			$changed[ $list ][ $key ] = $mode;
		}
	}

	if ( ! $changed ) {
		return;
	}

	$locked = (array) ( $tm['custom_fields_readonly_config'] ?? [] );
	foreach ( array_keys( $changed['custom_fields_translation'] ?? [] ) as $key ) {
		if ( in_array( (string) $key, $locked, true ) ) {
			$changed['custom_fields_unlocked_config'][ $key ] = $tm['custom_fields_unlocked_config'][ $key ] ?? null;
			$tm['custom_fields_unlocked_config'][ $key ]      = 1;
		}
	}

	// The first run's previous values, kept for undoing it.
	add_option( 'ipo_wpml_copy_once_backup', $changed, '', 'no' );

	$sitepress->set_setting( 'translation-management', $tm, true );

	// WPML's translation management keeps its own copy of these settings.
	if ( is_object( $iclTranslationManagement ) && isset( $iclTranslationManagement->settings ) ) {
		$iclTranslationManagement->settings = $tm;
	}
}
