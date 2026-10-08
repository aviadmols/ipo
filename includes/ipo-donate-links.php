<?php
/**
 * Donate page: editable donation links.
 *
 * Adds a URL field per country to pages using the "Donate Page" template
 * (page-templates/donate.php). The template reads them via ipo_donate_link(),
 * falling back to the defaults below while a field is still empty.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field name => [label, default URL].
 */
function ipo_donate_links_config() {
	return array(
		'donate_link_israel' => array( 'ישראל – Israel Philharmonic Foundation', 'https://donate.ipo.co.il/' ),
		'donate_link_usa'    => array( 'ארה"ב – American Friends of the IPO', 'https://secure.qgiv.com/for/togetherinmusic' ),
		'donate_link_canada' => array( 'קנדה – Canadian Friends of the IPO', 'https://www.canadahelps.org/en/dn/149737' ),
		'donate_link_uk'     => array( 'אנגליה – Israel Philharmonic Foundation UK', 'https://www.ipofoundationuk.com/donate/donation/' ),
		'donate_link_other'  => array( 'כל מדינה אחרת – Any other country', 'https://donate.ipo.co.il/' ),
	);
}

/**
 * The saved link for $name on the current page, or its default.
 */
function ipo_donate_link( $name ) {
	$config = ipo_donate_links_config();
	$url    = function_exists( 'get_field' ) ? get_field( $name ) : '';

	if ( ! $url && isset( $config[ $name ] ) ) {
		$url = $config[ $name ][1];
	}

	return esc_url( $url );
}

add_action( 'acf/init', 'ipo_register_donate_links_fields' );
function ipo_register_donate_links_fields() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array();
	foreach ( ipo_donate_links_config() as $name => $field ) {
		$fields[] = array(
			'key'           => 'field_' . $name,
			'label'         => $field[0],
			'name'          => $name,
			'type'          => 'url',
			'default_value' => $field[1],
		);
	}

	acf_add_local_field_group( array(
		'key'        => 'group_donate_links',
		'title'      => 'קישורי תרומה',
		'fields'     => $fields,
		'location'   => array(
			array(
				array(
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'page-templates/donate.php',
				),
			),
		),
		'menu_order' => 0,
		'position'   => 'normal',
		'style'      => 'default',
		'active'     => true,
	) );
}
