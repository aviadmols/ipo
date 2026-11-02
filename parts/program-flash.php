<?php
// The strip above the ticket cards. Its settings are in includes/ipo-program-flash.php.

$flash_text = get_field( 'program_flash_text', $post_id );

if ( ! get_field( 'program_flash_enabled', $post_id ) || ! $flash_text ) {
	return;
}

$flash_text_color = get_field( 'program_flash_text_color', $post_id );
$flash_bg_color   = get_field( 'program_flash_bg_color', $post_id );
?>
<div class="program-flash" style="color:<?php echo esc_attr( $flash_text_color ); ?>;background-color:<?php echo esc_attr( $flash_bg_color ); ?>"><?php echo esc_html( $flash_text ); ?></div>
