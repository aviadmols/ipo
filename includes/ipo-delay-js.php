<?php
/**
 * Delay JavaScript until the visitor interacts, and lazy-load inline
 * background images — the two things WP Rocket still did for this site once
 * the theme took over page caching (includes/page-cache/).
 *
 * Scripts: every executable <script> becomes type="ipo/delay" and is run by
 * assets/scripts/ipo-delay-loader.js on the first keydown / touch / mouse /
 * wheel, in document order. Kept running immediately:
 *   - non-JS blocks (JSON-LD, speculation rules, templates);
 *   - the "-js-extra" blocks WordPress prints for wp_localize_script (plain
 *     data the delayed scripts read);
 *   - anything marked data-ipo-nodelay or matching ipo_delay_js_keep.
 * Google Tag Manager / gtag are delayed too — they were the largest blocking
 * cost. To run them on page load again:
 *   add_filter( 'ipo_delay_js_analytics', '__return_false' );
 *
 * Backgrounds: style="background-image:url(...)" moves to data-ipo-bg and is
 * restored when the element comes within 300px of the viewport.
 *
 * Both stand down while WP Rocket is active (it does the same job) and for
 * logged-in users. Append ?ipo-nodelay to a URL to see a page without them.
 *
 * @package wpstack-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ipo_dj_enabled' ) ) {
	function ipo_dj_enabled() {
		if ( defined( 'WP_ROCKET_VERSION' ) || is_user_logged_in() || isset( $_GET['ipo-nodelay'] ) ) {
			return false;
		}

		return (bool) apply_filters( 'ipo_delay_js', true );
	}
}

/**
 * Substrings that keep a script running on page load.
 */
if ( ! function_exists( 'ipo_dj_keep_patterns' ) ) {
	function ipo_dj_keep_patterns() {
		$keep = array(
			'data-ipo-nodelay',
			// Gravity Forms' bootstrap only defines gform and listens for its own events.
			'var gform;gform||',
		);

		if ( ! apply_filters( 'ipo_delay_js_analytics', true ) ) {
			$keep = array_merge( $keep, array( 'googletagmanager.com', 'gtmkit', 'gtag(', "'gtm.start'", 'dataLayer' ) );
		}

		return apply_filters( 'ipo_delay_js_keep', $keep );
	}
}

/**
 * Turn every delayable <script> into type="ipo/delay" and add the loader.
 */
if ( ! function_exists( 'ipo_dj_delay_scripts' ) ) {
	function ipo_dj_delay_scripts( $html ) {
		$keep    = ipo_dj_keep_patterns();
		$delayed = 0;

		$html = preg_replace_callback(
			'#<script\b([^>]*)>([\s\S]*?)</script>#i',
			function ( $m ) use ( $keep, &$delayed ) {
				$attrs = $m[1];
				$body  = $m[2];
				$type  = '';

				if ( preg_match( '/\stype\s*=\s*(["\']?)([^"\'\s>]*)\1/i', $attrs, $t ) ) {
					$type = strtolower( $t[2] );
				}
				if ( '' !== $type && ! in_array( $type, array( 'text/javascript', 'application/javascript', 'module' ), true ) ) {
					return $m[0];
				}
				if ( preg_match( '/\sid\s*=\s*["\'][^"\']*-js-extra["\']/i', $attrs ) ) {
					return $m[0];
				}

				$has_src = (bool) preg_match( '/\ssrc\s*=/i', $attrs );
				if ( ! $has_src && '' === trim( $body ) ) {
					return $m[0];
				}

				foreach ( $keep as $needle ) {
					if ( stripos( $attrs, $needle ) !== false || ( '' !== $body && stripos( $body, $needle ) !== false ) ) {
						return $m[0];
					}
				}

				$attrs = preg_replace( '/\stype\s*=\s*(["\']?)[^"\'\s>]*\1/i', '', $attrs );
				if ( 'module' === $type ) {
					$attrs .= ' data-ipo-type="module"';
				}
				$attrs = preg_replace( '/\ssrc\s*=/i', ' data-ipo-src=', $attrs, 1 );

				$delayed++;

				return '<script type="ipo/delay"' . $attrs . '>' . $body . '</script>';
			},
			$html
		);

		if ( $delayed ) {
			$loader = ipo_dj_inline_asset( 'ipo-delay-loader.js' );
			if ( $loader ) {
				$html = preg_replace_callback(
					'#<head\b[^>]*>#i',
					function ( $h ) use ( $loader ) {
						return $h[0] . '<script data-ipo-nodelay>' . $loader . '</script>';
					},
					$html,
					1
				);
			}
		}

		return $html;
	}
}

/**
 * Move inline background images to data-ipo-bg and add the observer.
 */
if ( ! function_exists( 'ipo_dj_lazy_backgrounds' ) ) {
	function ipo_dj_lazy_backgrounds( $html ) {
		$count = 0;

		$html = preg_replace_callback(
			'#<(?!script|style|link|meta)([a-z][a-z0-9-]*)\b([^>]*?)\sstyle\s*=\s*(["\'])([^"\']*?)background-image\s*:\s*(url\([^)]*\))\s*;?([^"\']*)\3([^>]*)>#i',
			function ( $m ) use ( &$count ) {
				if ( stripos( $m[2] . $m[7], 'data-ipo-bg' ) !== false || stripos( $m[2] . $m[7], 'data-ipo-eager' ) !== false ) {
					return $m[0];
				}
				$count++;
				$q     = $m[3];
				$style = trim( $m[4] . $m[6] );
				$bg    = str_replace( $q, '"' === $q ? '&quot;' : '&#039;', $m[5] );

				return '<' . $m[1] . $m[2] . ' style=' . $q . $style . $q . ' data-ipo-bg=' . $q . $bg . $q . $m[7] . '>';
			},
			$html
		);

		if ( $count ) {
			$script = ipo_dj_inline_asset( 'ipo-lazy-bg.js' );
			if ( $script ) {
				$pos = strripos( $html, '</body>' );
				if ( false !== $pos ) {
					$html = substr_replace( $html, '<script data-ipo-nodelay>' . $script . '</script>', $pos, 0 );
				}
			}
		}

		return $html;
	}
}

/**
 * Stylesheets the home page can paint without: removing any of them leaves the
 * first screen pixel-identical on mobile (412x860) and desktop (1366x900),
 * checked one by one on 2026-10-02. They load right after first paint instead
 * of blocking it. Other templates show forms / carousels / galleries at the top,
 * so this is the home page only.
 */
if ( ! function_exists( 'ipo_dj_home_deferred_styles' ) ) {
	function ipo_dj_home_deferred_styles() {
		return apply_filters(
			'ipo_home_deferred_styles',
			array(
				'style-messages-parent-css',
				'ipo-search-css',
				'owl-carousel-min-css',
				'magnific-popup-css',
				'splide-min-css',
				'animate-css',
				'fancybox-parent-css',
				'gform_basic-css',
				'gform_theme_components-css',
			)
		);
	}
}

if ( ! function_exists( 'ipo_dj_defer_styles' ) ) {
	function ipo_dj_defer_styles( $html, $ids ) {
		if ( empty( $ids ) ) {
			return $html;
		}

		return preg_replace_callback(
			'#<link\b[^>]*\brel\s*=\s*["\']stylesheet["\'][^>]*>#i',
			function ( $m ) use ( $ids ) {
				$tag = $m[0];

				if ( ! preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', $tag, $id ) || ! in_array( $id[1], $ids, true ) ) {
					return $tag;
				}

				$async = preg_replace( '/\smedia\s*=\s*(["\'])[^"\']*\1/i', '', $tag );
				$async = preg_replace( '#\s*/?>$#', ' media="print" onload="this.media=\'all\';this.onload=null">', $async );

				return $async . '<noscript>' . $tag . '</noscript>';
			},
			$html
		);
	}
}

/**
 * Lazy-load iframes (video embeds) that do not say otherwise.
 */
if ( ! function_exists( 'ipo_dj_lazy_iframes' ) ) {
	function ipo_dj_lazy_iframes( $html ) {
		return preg_replace_callback(
			'#<iframe\b[^>]*>#i',
			function ( $m ) {
				return preg_match( '/\bloading\s*=/i', $m[0] ) ? $m[0] : preg_replace( '/<iframe\b/i', '<iframe loading="lazy"', $m[0], 1 );
			},
			$html
		);
	}
}

if ( ! function_exists( 'ipo_dj_inline_asset' ) ) {
	function ipo_dj_inline_asset( $file ) {
		static $cache = array();

		if ( ! isset( $cache[ $file ] ) ) {
			$path = get_stylesheet_directory() . '/assets/scripts/' . $file;
			$code = is_file( $path ) ? (string) file_get_contents( $path ) : '';
			// Strip the leading doc comment; the rest is already compact.
			$cache[ $file ] = trim( preg_replace( '#^\s*/\*[\s\S]*?\*/#', '', $code ) );
		}

		return $cache[ $file ];
	}
}

/**
 * Entry point, called from ipo_perf_filter_html() on the finished page.
 */
if ( ! function_exists( 'ipo_dj_filter_html' ) ) {
	function ipo_dj_filter_html( $html ) {
		if ( ! ipo_dj_enabled() ) {
			return $html;
		}

		$html = ipo_dj_lazy_iframes( $html );
		$html = ipo_dj_lazy_backgrounds( $html );

		if ( is_front_page() ) {
			$html = ipo_dj_defer_styles( $html, ipo_dj_home_deferred_styles() );
		}

		return ipo_dj_delay_scripts( $html );
	}
}
