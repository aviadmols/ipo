<?php
/**
 * "Lobby Children Streaming" template: optional sections, first used on the Shesh Besh page.
 *
 * Adds a tab of fields to the template's ACF box (group_67e8ea86e270d): an
 * image in place of the main video, a row of ensemble members (photo, name,
 * title), a long text that opens and closes, and a switch that hides the
 * Videos and Podcasts sections. Each one shows only when it's filled in or
 * switched on, so the other pages on this template look the same as before.
 *
 * This file creates the fields once, on the first admin load after deploy.
 * page-templates/Child-streaming.php shows them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IPO_STREAMING_EXTRA_FIELDS_CREATED = 'ipo_streaming_extra_fields_created';

add_action( 'admin_init', 'ipo_create_streaming_extra_fields' );
function ipo_create_streaming_extra_fields() {

	if ( get_option( IPO_STREAMING_EXTRA_FIELDS_CREATED ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_update_field' ) ) {
		return;
	}

	$group = acf_get_field_group( 'group_67e8ea86e270d' );
	if ( ! $group ) {
		return;
	}

	update_option( IPO_STREAMING_EXTRA_FIELDS_CREATED, 1, false );

	// The box's last field (Podcasts > Link) has menu_order 18.
	$fields = array(
		array(
			'key'   => 'field_streaming_extra_tab',
			'label' => 'אזורים נוספים',
			'name'  => '',
			'type'  => 'tab',
		),
		array(
			'key'           => 'field_streaming_hero_image',
			'label'         => 'תמונה במקום הווידאו הראשי',
			'name'          => 'streaming_hero_image',
			'type'          => 'image',
			'instructions'  => 'כשיש תמונה, היא מוצגת ליד הכותרת במקום הווידאו.',
			'return_format' => 'id',
			'preview_size'  => 'medium',
		),
		array(
			'key'          => 'field_streaming_members_title',
			'label'        => 'כותרת לשורת התמונות',
			'name'         => 'streaming_members_title',
			'type'         => 'text',
			'instructions' => 'לא חובה.',
		),
		array(
			'key'          => 'field_streaming_members',
			'label'        => 'שורת תמונות (שם + טייטל)',
			'name'         => 'streaming_members',
			'type'         => 'repeater',
			'instructions' => 'מוצגת מתחת לחלק הראשון. 6 בשורה במחשב.',
			'layout'       => 'table',
			'button_label' => 'הוספת תמונה',
			'sub_fields'   => array(
				array(
					'key'           => 'field_streaming_member_image',
					'label'         => 'תמונה',
					'name'          => 'image',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'   => 'field_streaming_member_name',
					'label' => 'שם',
					'name'  => 'name',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_streaming_member_title',
					'label' => 'טייטל',
					'name'  => 'title',
					'type'  => 'text',
				),
			),
		),
		array(
			'key'          => 'field_streaming_more_label',
			'label'        => 'טקסט הכפתור לפתיחת הטקסט הארוך',
			'name'         => 'streaming_more_label',
			'type'         => 'text',
			'instructions' => 'ריק: "לקריאה נוספת" בעברית, "Read more" באנגלית.',
		),
		array(
			'key'          => 'field_streaming_more_text',
			'label'        => 'טקסט ארוך (נפתח ונסגר)',
			'name'         => 'streaming_more_text',
			'type'         => 'wysiwyg',
			'instructions' => 'מוצג סגור מתחת לשורת התמונות, עם חץ שפותח וסוגר.',
			'tabs'         => 'all',
			'toolbar'      => 'full',
			'media_upload' => 0,
		),
		array(
			'key'           => 'field_streaming_hide_below_clips',
			'label'         => 'הסתרת Videos ו-Podcasts',
			'name'          => 'streaming_hide_below_clips',
			'type'          => 'true_false',
			'instructions'  => 'מסתיר את כל מה שמתחת לקליפים (Video Grid View).',
			'ui'            => 1,
			'default_value' => 0,
		),
	);

	$order = 19;
	foreach ( $fields as $field ) {
		$sub_fields = isset( $field['sub_fields'] ) ? $field['sub_fields'] : array();
		unset( $field['sub_fields'] );

		$saved = acf_get_field_post( $field['key'] )
			? acf_get_field( $field['key'] )
			: acf_update_field( array_merge( $field, array(
				'parent'              => $group['ID'],
				'menu_order'          => $order,
				'wpml_cf_preferences' => 3,
			) ) );

		$order++;

		if ( ! $sub_fields || empty( $saved['ID'] ) ) {
			continue;
		}

		foreach ( $sub_fields as $sub_order => $sub_field ) {
			if ( acf_get_field_post( $sub_field['key'] ) ) {
				continue;
			}

			acf_update_field( array_merge( $sub_field, array(
				'parent'              => $saved['ID'],
				'menu_order'          => $sub_order,
				'wpml_cf_preferences' => 3,
			) ) );
		}
	}
}
