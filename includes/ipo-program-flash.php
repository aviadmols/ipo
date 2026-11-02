<?php
/**
 * Program: the flash strip above the ticket cards.
 *
 * The on/off switch, text and two colours are ACF fields in the "פרטי הקונצרט"
 * box, editable from the ACF screen like the rest of the box. This file creates
 * them once, on the first admin load after deploy.
 * parts/program-flash.php shows the strip on the program page while it is on.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_PROGRAM_FLASH_FIELDS_CREATED = 'ipo_program_flash_fields_created';

add_action( 'admin_init', 'ipo_create_program_flash_fields' );
function ipo_create_program_flash_fields() {

	if ( get_option( IPO_PROGRAM_FLASH_FIELDS_CREATED ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_update_field' ) ) {
		return;
	}

	$group = acf_get_field_group( 'group_62b33269f2867' );
	if ( ! $group ) {
		return;
	}

	update_option( IPO_PROGRAM_FLASH_FIELDS_CREATED, 1, false );

	$show_when_enabled = array(
		array(
			array(
				'field'    => 'field_program_flash_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$fields = array(
		array(
			'key'           => 'field_program_flash_enabled',
			'label'         => 'הצגת פלאח מעל הכרטיסים',
			'name'          => 'program_flash_enabled',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 0,
		),
		array(
			'key'               => 'field_program_flash_text',
			'label'             => 'טקסט הפלאח',
			'name'              => 'program_flash_text',
			'type'              => 'text',
			'placeholder'       => 'פסטיבל ה-90',
			'conditional_logic' => $show_when_enabled,
		),
		array(
			'key'               => 'field_program_flash_text_color',
			'label'             => 'צבע טקסט הפלאח',
			'name'              => 'program_flash_text_color',
			'type'              => 'color_picker',
			'default_value'     => '#ffffff',
			'conditional_logic' => $show_when_enabled,
		),
		array(
			'key'               => 'field_program_flash_bg_color',
			'label'             => 'צבע רקע הפלאח',
			'name'              => 'program_flash_bg_color',
			'type'              => 'color_picker',
			'default_value'     => '#faa95a',
			'conditional_logic' => $show_when_enabled,
		),
	);

	foreach ( $fields as $field ) {
		if ( acf_get_field_post( $field['key'] ) ) {
			continue;
		}

		// Same order as "Banner title color", the last field above the tabs. Equal
		// orders sort by title, which keeps these after it and in this sequence.
		acf_update_field( array_merge( $field, array(
			'parent'              => $group['ID'],
			'menu_order'          => 8,
			'wpml_cf_preferences' => 3,
		) ) );
	}
}
