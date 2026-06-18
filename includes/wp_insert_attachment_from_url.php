<?php

/**
 * Insert an attachment from a URL address.
 *
 * @param  string   $url            The URL address.
 * @param  int|null $parent_post_id The parent post ID (Optional).
 * @return int|false                The attachment ID on success. False on failure.
 */
 
function get_attachment_id_by_title( $title ) {
    global $wpdb;

    $attachments = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_title = '$title' AND post_type = 'attachment' ", OBJECT );
    //print_r($attachments);
    if ( $attachments ){

        $attachment_url = $attachments[0]->ID;

    }else{
        return false;
    }

    return $attachment_url;
}

function wp_insert_attachment_from_url( $url, $parent_post_id = null , $alt = '' ) {

	if ( ! class_exists( 'WP_Http' ) ) {
		require_once ABSPATH . WPINC . '/class-http.php';
	}
	
	
	$http     = new WP_Http();
	$response = $http->request( $url );

	

	if(is_wp_error($response)){
		return $response;
	}
	
	if ( 200 !== $response['response']['code'] ) {
		return -1;
	}

	$upload = wp_upload_bits( basename( $url ), null, $response['body'] );
	if ( ! empty( $upload['error'] ) ) {
		return -2;
	}

	$file_path        = $upload['file'];
	$file_name        = basename( $file_path );
	$file_type        = wp_check_filetype( $file_name, null );
	$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
	$wp_upload_dir    = wp_upload_dir();
	
	// Check if a file with similar name already exist
	

	//$file_name = 'https://www.ipo.co.il/wp-content/uploads/2017/03/בל-5.jpg';
	$title_to_check = basename($file_name);
	$title_to_check = pathinfo($title_to_check)['filename'];
	$title_to_check = preg_replace('/-\d+$/', '', $title_to_check);
	$title_to_check = sanitize_file_name($title_to_check);

	$exists = get_attachment_id_by_title($title_to_check);
	$exists_1 = get_attachment_id_by_title($title_to_check.'-1');
	$exists_2 = get_attachment_id_by_title($title_to_check.'-2');
	$exists_3 = get_attachment_id_by_title($title_to_check.'-3');
	$exists_4 = get_attachment_id_by_title($title_to_check.'-4');

	if($exists)return $exists;
	if($exists_1)return $exists_1;
	if($exists_2)return $exists_2;
	if($exists_3)return $exists_3;
	if($exists_4)return $exists_4;

	$post_info = array(
		'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
		'post_mime_type' => $file_type['type'],
		'post_title'     => $attachment_title,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	// Create the attachment.
	$attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

	// Include image.php.
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Add alt tag to the attachment
	if($alt)
		update_post_meta($attach_id, '_wp_attachment_image_alt', $alt);

	// Generate the attachment metadata.
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

	// Assign metadata to attachment.
	wp_update_attachment_metadata( $attach_id, $attach_data );

	

	return $attach_id;

}
