<?php
/**
 * Front-end performance tweaks applied to the final HTML.
 *
 * 1. Serve Imagify's WebP copies.
 *    Imagify already generates a "<file>.jpg.webp" next to every upload, but its
 *    "display next-gen" option is off, so pages still ship the original JPG/PNG
 *    (the mobile hero alone is ~1MB as JPG vs ~160KB as WebP). For every uploads
 *    URL inside an image-bearing tag we swap in the .webp sibling - only when that
 *    file really exists on disk, so a missing WebP never breaks an image.
 *
 * 2. Prioritise the LCP image.
 *    The first hero slide ships loading="eager" but no fetchpriority, so on mobile
 *    it queues behind ~30 stylesheets. The first eager <img> gets
 *    fetchpriority="high"; lazy images lose a stray fetchpriority="high" (core adds
 *    it to the first content image, e.g. an artist photo far below the fold).
 *
 * Disable either part with:
 *   add_filter( 'ipo_perf_serve_webp', '__return_false' );
 *   add_filter( 'ipo_perf_lcp_priority', '__return_false' );
 *
 * @package wpstack-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the .webp sibling of an uploads JPG/PNG URL, or the URL unchanged.
 */
if ( ! function_exists( 'ipo_perf_webp_url' ) ) {
	function ipo_perf_webp_url( $url ) {
		static $cache   = array();
		static $uploads = null;

		if ( isset( $cache[ $url ] ) ) {
			return $cache[ $url ];
		}
		if ( null === $uploads ) {
			$dir     = wp_get_upload_dir();
			$uploads = array(
				'path' => untrailingslashit( (string) wp_parse_url( $dir['baseurl'], PHP_URL_PATH ) ),
				'dir'  => untrailingslashit( $dir['basedir'] ),
			);
		}

		$webp = $url;
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( $path && 0 === strpos( $path, $uploads['path'] . '/' ) ) {
			$file = $uploads['dir'] . rawurldecode( substr( $path, strlen( $uploads['path'] ) ) ) . '.webp';
			if ( is_file( $file ) ) {
				$webp = $url . '.webp';
			}
		}

		$cache[ $url ] = $webp;
		return $webp;
	}
}

if ( ! function_exists( 'ipo_perf_filter_html' ) ) {
	function ipo_perf_filter_html( $buffer ) {
		if ( ! is_string( $buffer ) || stripos( $buffer, '<html' ) === false ) {
			return $buffer;
		}

		if ( apply_filters( 'ipo_perf_serve_webp', true ) ) {
			$host        = preg_quote( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '#' );
			$upload_path = preg_quote( untrailingslashit( (string) wp_parse_url( wp_get_upload_dir()['baseurl'], PHP_URL_PATH ) ), '#' );
			// Absolute, protocol-relative or root-relative uploads URL ending in .jpg/.jpeg/.png.
			$url_re = '#(?<=["\'\s(,])(?:(?:https?:)?//' . $host . ')?' . $upload_path . '/[^"\'\s,()<>?]+?\.(?:jpe?g|png)(?=[\s"\'),])#i';

			// Only tags that display images. <meta>/<link>/<a href> keep the original
			// (og:image, downloads, lightbox originals).
			$buffer = preg_replace_callback(
				'#<(?:img|source|picture|div|section|span|figure|li|article|header|aside)\b[^>]*>#i',
				function ( $m ) use ( $url_re ) {
					if ( stripos( $m[0], '/uploads/' ) === false ) {
						return $m[0];
					}
					return preg_replace_callback(
						$url_re,
						function ( $u ) {
							return ipo_perf_webp_url( $u[0] );
						},
						$m[0]
					);
				},
				$buffer
			);
		}

		if ( apply_filters( 'ipo_perf_lcp_priority', true ) ) {
			$lcp_done = false;
			$buffer   = preg_replace_callback(
				'#<img\b[^>]*>#i',
				function ( $m ) use ( &$lcp_done ) {
					$tag = $m[0];
					if ( ! $lcp_done && preg_match( '/\bloading\s*=\s*["\']?eager\b/i', $tag ) ) {
						$lcp_done = true;
						if ( ! preg_match( '/\bfetchpriority\s*=/i', $tag ) ) {
							$tag = preg_replace( '/<img\b/i', '<img fetchpriority="high"', $tag, 1 );
						}
						return $tag;
					}
					if ( preg_match( '/\bloading\s*=\s*["\']?lazy\b/i', $tag ) ) {
						$tag = preg_replace( '/\s+fetchpriority\s*=\s*["\']?high["\']?/i', '', $tag );
					}
					return $tag;
				},
				$buffer
			);
		}

		// Delay JS / lazy backgrounds / lazy iframes — includes/ipo-delay-js.php.
		if ( function_exists( 'ipo_dj_filter_html' ) ) {
			$buffer = ipo_dj_filter_html( $buffer );
		}

		return $buffer;
	}
}

if ( ! function_exists( 'ipo_perf_buffer_start' ) ) {
	function ipo_perf_buffer_start() {
		if ( is_admin() || is_feed() || wp_doing_ajax() || is_customize_preview() ) {
			return;
		}
		ob_start( 'ipo_perf_filter_html' );
	}
}
add_action( 'template_redirect', 'ipo_perf_buffer_start', 0 );
