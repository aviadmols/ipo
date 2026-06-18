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
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/@lottiefiles/lottie-player@1.0.0/dist/lottie-player.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/2.0.2/anime.min.js"></script>
