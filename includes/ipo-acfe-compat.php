<?php
/**
 * ACF Extended Pro 0.8.9.2 on ACF Pro 6.8: stop the checkbox crash.
 *
 * ACF 6.8 made acf_field_checkbox::$_values private. ACF Extended Pro 0.8.9.2
 * still reads it in acfe_pro_field_checkbox::render_field(), so every screen
 * that prints a checkbox field dies there with "Cannot access private property
 * acf_field_checkbox::$_values": the page editor (the "Protect page" box), the
 * field group screen (ACF Extended's own side box) and more. Whatever comes
 * after that point is never printed, ACF's scripts included, so no ACF field
 * on the screen works.
 *
 * Both plugins were updated together on 2026-11-15 and there is no newer ACF
 * Extended Pro. Until there is, ACF Extended's extra checkbox rendering is
 * taken off, and ACF prints checkbox fields the standard way. Nothing else of
 * ACF Extended is touched.
 *
 * Applies only to ACF 6.8+ with ACF Extended 0.8.9.2 or older, so it stands
 * down by itself once either plugin moves.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ipo_acfe_compat_needed() {
	return defined( 'ACF_VERSION' ) && defined( 'ACFE_VERSION' )
		&& version_compare( ACF_VERSION, '6.8', '>=' )
		&& version_compare( ACFE_VERSION, '0.8.9.2', '<=' );
}

// On every admin screen before anything is printed, and again just before a
// field renders (front-end forms, and in case ACF Extended hooks in late).
add_action( 'current_screen', 'ipo_acfe_compat_checkbox_render' );
add_action( 'acf/render_field', 'ipo_acfe_compat_checkbox_render', 1 );
function ipo_acfe_compat_checkbox_render() {

	if ( ! ipo_acfe_compat_needed() ) {
		return;
	}

	global $wp_filter;

	$tag = 'acf/render_field/type=checkbox';
	if ( empty( $wp_filter[ $tag ] ) ) {
		return;
	}

	foreach ( $wp_filter[ $tag ]->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$fn = $callback['function'];
			if ( is_array( $fn ) && is_object( $fn[0] ) && 'acfe_pro_field_checkbox' === get_class( $fn[0] ) ) {
				remove_action( $tag, $fn, $priority );
			}
		}
	}
}
