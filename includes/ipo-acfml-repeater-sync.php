<?php
/**
 * ACFML repeater sync: on while a post is created, off from then on.
 *
 * ACFML mirrors every row insert, delete and move in a repeater to the post's
 * other languages, matching rows by value. Hebrew and English rows hold
 * different values (each language has its own image IDs, English carries
 * credits Hebrew doesn't), so the mirror moves the wrong rows. It emptied the
 * series links on the kids' page and scrambled the academy musicians twice.
 *
 * ACFML keeps the setting per translation group (trid) in one option. A group
 * with no entry takes ACFML_REPEATER_SYNC_DEFAULT, which ACFML treats as on.
 */

/**
 * The new-post screen, or the first save from it (the post is still an auto-draft).
 */
function ipo_acfml_is_first_save() {
	global $pagenow;

	return 'post-new.php' === $pagenow
		|| ( isset( $_POST['original_post_status'] ) && 'auto-draft' === $_POST['original_post_status'] );
}

// A group with no entry is off, except while its first post is created.
if ( ! defined( 'ACFML_REPEATER_SYNC_DEFAULT' ) && ! ipo_acfml_is_first_save() ) {
	define( 'ACFML_REPEATER_SYNC_DEFAULT', false );
}

// Any entry left on is switched off on the next admin request. The first save
// stores the checkbox (on by default), and the redirect back to the editor
// switches it off, so later saves leave the other languages alone.
add_action( 'admin_init', 'ipo_acfml_sync_off' );
function ipo_acfml_sync_off() {
	$sync = get_option( 'acfml_synchronise_repeater_fields' );

	if ( is_array( $sync ) && array_filter( $sync ) ) {
		update_option( 'acfml_synchronise_repeater_fields', array_fill_keys( array_keys( $sync ), false ) );
	}
}

// Posts past their first save don't show the checkbox, since it would be switched off again.
add_action( 'add_meta_boxes', 'ipo_acfml_hide_sync_checkbox', 11 );
function ipo_acfml_hide_sync_checkbox( $post_type ) {
	if ( ipo_acfml_is_first_save()
		|| ! class_exists( 'ACFML\Repeater\Sync\CheckboxUI' )
		|| ! defined( 'ACFML\Repeater\Sync\CheckboxUI::META_BOX_ID' ) ) {
		return;
	}

	remove_meta_box( \ACFML\Repeater\Sync\CheckboxUI::META_BOX_ID, $post_type, 'normal' );
}
