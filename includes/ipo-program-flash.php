<?php
/**
 * Program: the flash strip above the ticket cards.
 *
 * Adds an on/off switch, text and two colours to the program editor's
 * "פרטי הקונצרט" box, above its tabs.
 * parts/program-flash.php shows the strip on the program page while it is on.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_PROGRAM_DETAILS_GROUP = 'group_62b33269f2867';
const IPO_PROGRAM_FLASH_FIELDS  = array(
	'field_program_flash_enabled',
	'field_program_flash_text',
	'field_program_flash_text_color',
	'field_program_flash_bg_color',
);

add_action( 'acf/init', 'ipo_register_program_flash_fields' );
function ipo_register_program_flash_fields() {

	if ( ! function_exists( 'acf_add_local_field' ) ) {
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

	acf_add_local_field( array(
		'key'           => 'field_program_flash_enabled',
		'label'         => 'הצגת פלאח מעל הכרטיסים',
		'name'          => 'program_flash_enabled',
		'type'          => 'true_false',
		'ui'            => 1,
		'default_value' => 0,
	) );

	acf_add_local_field( array(
		'key'               => 'field_program_flash_text',
		'label'             => 'טקסט הפלאח',
		'name'              => 'program_flash_text',
		'type'              => 'text',
		'placeholder'       => 'פסטיבל ה-90',
		'conditional_logic' => $show_when_enabled,
	) );

	acf_add_local_field( array(
		'key'               => 'field_program_flash_text_color',
		'label'             => 'צבע טקסט הפלאח',
		'name'              => 'program_flash_text_color',
		'type'              => 'color_picker',
		'default_value'     => '#ffffff',
		'conditional_logic' => $show_when_enabled,
	) );

	acf_add_local_field( array(
		'key'               => 'field_program_flash_bg_color',
		'label'             => 'צבע רקע הפלאח',
		'name'              => 'program_flash_bg_color',
		'type'              => 'color_picker',
		'default_value'     => '#faa95a',
		'conditional_logic' => $show_when_enabled,
	) );
}

// The box is stored in the database, so the fields join it as it loads. Saving the
// box in the ACF field group screen would copy them into the database, so not there.
add_filter( 'acf/load_fields', 'ipo_program_flash_add_to_details_box', 10, 2 );
function ipo_program_flash_add_to_details_box( $fields, $parent ) {

	if ( IPO_PROGRAM_DETAILS_GROUP !== $parent['key'] || 'acf-field-group' === get_post_type() ) {
		return $fields;
	}

	$flash_fields = array_filter( array_map( 'acf_get_field', IPO_PROGRAM_FLASH_FIELDS ) );
	$first_tab    = array_search( 'tab', array_column( $fields, 'type' ), true );

	array_splice( $fields, false === $first_tab ? count( $fields ) : $first_tab, 0, $flash_fields );

	return $fields;
}
