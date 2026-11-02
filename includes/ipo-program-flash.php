<?php
/**
 * Program: the flash strip above the ticket cards.
 *
 * Adds an on/off switch, text and two colours to the program editor.
 * parts/program-flash.php shows the strip on the program page while it is on.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'ipo_register_program_flash_fields' );
function ipo_register_program_flash_fields() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$show_when_enabled = array(
		array(
			array(
				'field'    => 'field_program_flash_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	acf_add_local_field_group( array(
		'key'        => 'group_program_flash',
		'title'      => 'פלאח מעל הכרטיסים',
		'fields'     => array(
			array(
				'key'           => 'field_program_flash_enabled',
				'label'         => 'הצגת הפלאח',
				'name'          => 'program_flash_enabled',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
			),
			array(
				'key'               => 'field_program_flash_text',
				'label'             => 'טקסט',
				'name'              => 'program_flash_text',
				'type'              => 'text',
				'placeholder'       => 'פסטיבל ה-90',
				'conditional_logic' => $show_when_enabled,
			),
			array(
				'key'               => 'field_program_flash_text_color',
				'label'             => 'צבע טקסט',
				'name'              => 'program_flash_text_color',
				'type'              => 'color_picker',
				'default_value'     => '#ffffff',
				'conditional_logic' => $show_when_enabled,
			),
			array(
				'key'               => 'field_program_flash_bg_color',
				'label'             => 'צבע רקע',
				'name'              => 'program_flash_bg_color',
				'type'              => 'color_picker',
				'default_value'     => '#faa95a',
				'conditional_logic' => $show_when_enabled,
			),
		),
		'location'   => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'program',
				),
			),
		),
		'menu_order' => 0,
		'position'   => 'side',
		'style'      => 'default',
		'active'     => true,
	) );
}
