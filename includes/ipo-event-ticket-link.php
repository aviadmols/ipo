<?php
/**
 * Event: own ticket purchase link.
 *
 * Adds a URL field to the event editor. ipo_event::get_purchase_link() uses it
 * before the program's "ticket_purchase" link and the ticketing system order
 * page, so events created by hand (no event_api_id) can still sell tickets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'ipo_register_event_ticket_link_field' );
function ipo_register_event_ticket_link_field() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'        => 'group_event_ticket_link',
		'title'      => 'לינק לרכישת כרטיסים',
		'fields'     => array(
			array(
				'key'          => 'field_event_ticket_link',
				'label'        => 'לינק לרכישת כרטיסים',
				'name'         => 'event_ticket_link',
				'type'         => 'url',
				'instructions' => 'לאירוע שנוצר ידנית (בלי ID מהמערכת), או כדי להחליף את הלינק של האירוע. אם ריק – משתמשים בלינק של התוכנית, ואם גם הוא ריק – בלינק של המערכת לפי ה-ID.',
			),
		),
		'location'   => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'event',
				),
			),
		),
		'menu_order' => 0,
		'position'   => 'side',
		'style'      => 'default',
		'active'     => true,
	) );
}
