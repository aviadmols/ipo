<?php
/**
 * FOOTER third-party scripts (printed via wp_footer, late priority).
 *
 * Origin: WordPress admin code-manager snippet "#5 | FOOTER" (ID: 27612).
 * De-duplicated: only AOS js + its init are kept.
 *
 * Dropped vs the original snippet:
 *   - fullPage.js  (verified unused in this theme)
 *
 * @package wpstack-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ once: true });</script>
