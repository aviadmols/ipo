<?php
/**
 * HEAD third-party libraries (printed via wp_head).
 *
 * Origin: WordPress admin code-manager snippet "#6 | HEADER" (ID: 27610).
 * De-duplicated: only the libraries actually used by this theme are kept.
 *   - AOS css (scroll animations)
 *   - <lottie-player> custom element (used for Lottie animations)
 *   - anime.js (letter/title animations)
 *
 * Dropped vs the original snippet:
 *   - bodymovin 5.7.7 and lottie-web 5.7.14  (redundant — only the
 *     <lottie-player> custom element is used in this theme)
 *   - fullPage.js                            (verified unused in this theme)
 *
 * @package wpstack-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php /* AOS 2.3.1 css is self-hosted: the unpkg copy opened a new connection (~900ms on mobile) on the critical render path. */ ?>
<link href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/styles/aos.css?ver=2.3.1' ); ?>" rel="stylesheet">
<?php /* Guard: skip a custom-element re-definition (e.g. lottie-player loaded twice) so it
         can't throw "NotSupportedError: the name 'lottie-player' has already been used".
         Works regardless of load order/source (incl. a still-active duplicate code snippet). */ ?>
<script>(function(){var d=customElements.define.bind(customElements);customElements.define=function(n,c,o){if(!customElements.get(n)){try{d(n,c,o);}catch(e){}}};})();</script>
<script defer src="https://unpkg.com/@lottiefiles/lottie-player@1.0.0/dist/lottie-player.js" crossorigin="anonymous"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/animejs/2.0.2/anime.min.js"></script>
