<?php
/*
* Template Name: Lobby עונה – כרטיסי אירועים
*/

/**
 * Season lobby: banner, intro, a grid of main events and a carousel of more
 * events. Each card fills itself from the chosen event and its program; the
 * fields and the card markup are in includes/ipo-season-lobby.php.
 */

get_header();

$page_id = get_the_ID();

// Banner.
$hero_image        = ipo_sl_image_id( get_field( 'sl_hero_image' ) );
$hero_image_mobile = ipo_sl_image_id( get_field( 'sl_hero_image_mobile' ) );
if ( ! $hero_image ) {
	$hero_image = (int) get_post_thumbnail_id( $page_id );
}

// Intro.
$intro_title = get_field( 'sl_intro_title' );
if ( ! $intro_title ) {
	$intro_title = get_the_title( $page_id );
}
$intro_text = get_field( 'sl_intro_text' );

$sections = array(
	'sl_featured' => 'featured',
	'sl_carousel' => 'carousel',
);
?>

<div class="main-content ipo-sl">

	<?php if ( $hero_image || $hero_image_mobile ) : ?>
		<section class="ipo-sl-hero">
			<?php
			$hero_picture = ipo_sl_picture( $hero_image, $hero_image_mobile, get_the_title( $page_id ), 'full' );
			// The banner is above the fold: load it right away.
			echo str_replace( 'loading="lazy"', 'loading="eager" fetchpriority="high"', $hero_picture ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</section>
	<?php endif; ?>

	<?php if ( $intro_title || $intro_text ) : ?>
		<section class="ipo-sl-intro">
			<div class="ipo-sl-wrap ipo-sl-intro__grid">
				<?php if ( $intro_title ) : ?>
					<h1 class="ipo-sl-intro__title"><?php echo esc_html( $intro_title ); ?></h1>
				<?php endif; ?>
				<?php if ( $intro_text ) : ?>
					<div class="ipo-sl-intro__text"><?php echo wp_kses_post( $intro_text ); ?></div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	foreach ( $sections as $prefix => $variant ) :

		$rows = get_field( $prefix . '_cards' );
		if ( empty( $rows ) || ! is_array( $rows ) ) {
			continue;
		}

		$title           = get_field( $prefix . '_title' );
		$subtitle        = get_field( $prefix . '_subtitle' );
		$max_artists     = get_field( $prefix . '_max_artists' );
		$max_artists     = ( '' === $max_artists || null === $max_artists ) ? ( 'featured' === $variant ? 2 : 6 ) : (int) $max_artists;
		$artists_heading = (string) get_field( $prefix . '_artists_heading' );

		$cards = array();
		foreach ( $rows as $row ) {
			$cards[] = ipo_sl_card_data( $row, $max_artists );
		}
		?>

		<section class="ipo-sl-section ipo-sl-section--<?php echo esc_attr( $variant ); ?>">
			<div class="ipo-sl-wrap">

				<?php if ( $title || $subtitle ) : ?>
					<div class="ipo-sl-head">
						<?php if ( $title ) : ?>
							<h2 class="ipo-sl-head__title"><?php echo esc_html( $title ); ?></h2>
						<?php endif; ?>
						<?php if ( $subtitle ) : ?>
							<p class="ipo-sl-head__subtitle"><?php echo wp_kses( $subtitle, array( 'br' => array() ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( 'featured' === $variant ) : ?>

					<div class="ipo-sl-grid">
						<?php
						foreach ( $cards as $card ) {
							ipo_sl_render_card( $card, $variant, $artists_heading );
						}
						?>
					</div>

				<?php else : ?>

					<div class="splide ipo-sl-splide" aria-label="<?php echo esc_attr( $title ? $title : get_the_title( $page_id ) ); ?>">
						<div class="splide__track">
							<ul class="splide__list">
								<?php foreach ( $cards as $card ) : ?>
									<li class="splide__slide">
										<?php ipo_sl_render_card( $card, $variant, $artists_heading ); ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>

				<?php endif; ?>

			</div>
		</section>

	<?php endforeach; ?>

</div>

<?php get_footer(); ?>
