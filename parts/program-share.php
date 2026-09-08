<?php
/**
 * Share buttons for single program pages (WhatsApp, Facebook, Email).
 */

if ( ! isset( $post_id ) || ! $post_id ) {
	$post_id = get_the_ID();
}

$program      = new ipo_program( $post_id );
$title        = $program->get_title();
$subtitle     = $program->get_subtitle();
if ( ! $subtitle ) {
	$subtitle = get_field( 'program_subtitle', $post_id );
}
$permalink   = get_permalink( $post_id );
$share_title = trim( wp_strip_all_tags( (string) $title ) );
$share_desc  = trim( wp_strip_all_tags( (string) $subtitle ) );

// Build share body with clear line breaks (title / description / link).
$share_parts = array_filter( array( $share_title, $share_desc, $permalink ) );
$share_body  = implode( "\n\n", $share_parts );

$lang = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : 'he';

if ( $lang === 'en' ) {
	$label_whatsapp = 'Share on WhatsApp';
	$label_facebook = 'Share on Facebook';
	$label_email    = 'Share by Email';
	$share_heading  = 'Share';
} else {
	$label_whatsapp = 'שיתוף בוואטסאפ';
	$label_facebook = 'שיתוף בפייסבוק';
	$label_email    = 'שיתוף במייל';
	$share_heading  = 'שיתוף';
}

// Do not pass WhatsApp/mailto URLs through esc_url() — it strips %0A newlines.
$whatsapp_url = 'https://api.whatsapp.com/send?text=' . rawurlencode( $share_body );
$facebook_url = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $permalink );
$email_url    = 'mailto:?subject=' . rawurlencode( $share_title ) . '&body=' . rawurlencode( $share_body );
?>

<section class="program-share-area" data-aos="fade-in" data-aos-offset="0" data-aos-duration="500" data-aos-delay="200" aria-label="<?php echo esc_attr( $share_heading ); ?>">
	<div class="container custom max-1440">
		<div class="program-share">
			<span class="program-share__label"><?php echo esc_html( $share_heading ); ?></span>
			<ul class="program-share__list">
				<li>
					<a class="program-share__btn program-share__btn--whatsapp"
					   href="<?php echo esc_attr( $whatsapp_url ); ?>"
					   target="_blank"
					   rel="noopener noreferrer"
					   aria-label="<?php echo esc_attr( $label_whatsapp ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
					</a>
				</li>
				<li>
					<a class="program-share__btn program-share__btn--facebook"
					   href="<?php echo esc_url( $facebook_url ); ?>"
					   target="_blank"
					   rel="noopener noreferrer"
					   aria-label="<?php echo esc_attr( $label_facebook ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
					</a>
				</li>
				<li>
					<a class="program-share__btn program-share__btn--email"
					   href="<?php echo esc_attr( $email_url ); ?>"
					   aria-label="<?php echo esc_attr( $label_email ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
					</a>
				</li>
			</ul>
		</div>
	</div>
</section>
