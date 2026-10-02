<?php
/**
 * IPO page cache — the serving half.
 *
 * Plain PHP with no WordPress calls, so the same code can answer a request from
 * three places, earliest first:
 *   - wp-content/mu-plugins/ipo-page-cache.php (before any plugin loads; the
 *     loader is installed from Tools > IPO Page Cache),
 *   - the top of the child theme's functions.php (after plugins, before the
 *     theme and init — works with no installation at all).
 *
 * The storing / purging half lives in page-cache.php.
 *
 * Pages are kept as flat files under wp-content/uploads/ipo-page-cache, one per
 * URL and device type (WPML prints a touch-device class on mobile, so a phone
 * and a desktop never share a copy).
 *
 * @package wpstack-child
 */

if ( defined( 'IPO_PC_LOADED' ) ) {
	return;
}
define( 'IPO_PC_LOADED', true );

if ( ! defined( 'IPO_PC_DIR' ) ) {
	define( 'IPO_PC_DIR', ( defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : dirname( __DIR__, 4 ) ) . '/uploads/ipo-page-cache' );
}

// Logged-out pages embed nonces (Facebook signals, forms) that WordPress honours
// for 12–24h, so no copy may outlive 10h. Copies also expire at local midnight:
// the calendar and the upcoming strips are built around "today".
if ( ! defined( 'IPO_PC_TTL' ) ) {
	define( 'IPO_PC_TTL', 10 * 3600 );
}
if ( ! defined( 'IPO_PC_TIMEZONE' ) ) {
	define( 'IPO_PC_TIMEZONE', 'Asia/Jerusalem' );
}

/**
 * Query arguments that never change the page; a URL carrying only these is
 * served the cached copy of the bare URL.
 */
function ipo_pc_ignored_query_args() {
	return array(
		'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id',
		'fbclid', 'gclid', 'gbraid', 'wbraid', 'gad_source', 'msclkid', 'ttclid', 'twclid',
		'_ga', '_gl', 'mc_cid', 'mc_eid', 'igshid', 'srsltid',
	);
}

/**
 * Query arguments that select a different page and get their own copy. Every
 * calendar link points at a programme with ?event_id=, so those pages are
 * cached per event instead of never.
 */
function ipo_pc_keyed_query_args() {
	return array( 'event_id' );
}

/**
 * Paths (decoded, matched as regex against the URL path) that are never cached.
 * The full calendar page was already excluded in WP Rocket.
 */
function ipo_pc_excluded_paths() {
	return array(
		'#^/(en/)?(wp-admin|wp-includes|wp-content|wp-json)(/|$)#',
		'#\.(php|xml|xsl|txt|json|ico|map)$#i',
		'#/feed/?$#',
		'#^/(en/)?לוח-שנה/?$#u',
	);
}

/**
 * Whether the current request may use the cache, and which file it maps to.
 *
 * @return string|false Absolute cache file path, or false to bypass.
 */
function ipo_pc_request_file() {
	static $file = null;

	if ( null !== $file ) {
		return $file;
	}
	$file = false;

	if ( PHP_SAPI === 'cli' || ( defined( 'IPO_PC_DISABLED' ) && IPO_PC_DISABLED ) ) {
		return false;
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	if ( 'GET' !== $method && 'HEAD' !== $method ) {
		return false;
	}

	foreach ( array_keys( $_COOKIE ) as $name ) {
		if ( preg_match( '/^(wordpress_logged_in_|wordpress_sec_|wp-postpass_|comment_author_|wordpress_no_cache|woocommerce_items_in_cart|wp_woocommerce_session_)/', $name ) ) {
			return false;
		}
	}

	$uri   = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
	$parts = explode( '?', $uri, 2 );
	$path  = rawurldecode( $parts[0] );

	if ( '' === $path || '/' !== $path[0] || strpos( $path, '..' ) !== false ) {
		return false;
	}

	foreach ( ipo_pc_excluded_paths() as $re ) {
		if ( preg_match( $re, $path ) ) {
			return false;
		}
	}

	$key_args = '';

	if ( isset( $parts[1] ) && '' !== $parts[1] ) {
		parse_str( $parts[1], $args );
		$args = array_diff_key( $args, array_flip( ipo_pc_ignored_query_args() ) );
		foreach ( array_keys( $args ) as $key ) {
			if ( 0 === strpos( $key, 'utm_' ) ) {
				unset( $args[ $key ] );
			}
		}
		foreach ( ipo_pc_keyed_query_args() as $key ) {
			if ( isset( $args[ $key ] ) ) {
				if ( ! is_string( $args[ $key ] ) || ! ctype_digit( $args[ $key ] ) ) {
					return false;
				}
				$key_args .= '&' . $key . '=' . $args[ $key ];
				unset( $args[ $key ] );
			}
		}
		if ( ! empty( $args ) ) {
			return false;
		}
	}

	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( preg_replace( '/:\d+$/', '', $_SERVER['HTTP_HOST'] ) ) : '';
	if ( '' === $host || ! preg_match( '/^[a-z0-9.-]+$/', $host ) ) {
		return false;
	}

	$https = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== strtolower( $_SERVER['HTTPS'] ) )
		|| ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) );

	$device = ipo_pc_is_mobile() ? 'm' : 'd';
	$file   = IPO_PC_DIR . '/' . $host . '/' . md5( ( $https ? 'https' : 'http' ) . '|' . $path . $key_args ) . '-' . $device . '.html';

	return $file;
}

/**
 * Same test as wp_is_mobile(), which is what decides WPML's touch-device class.
 */
function ipo_pc_is_mobile() {
	if ( isset( $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ) ) {
		return '?1' === $_SERVER['HTTP_SEC_CH_UA_MOBILE'];
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

	return '' !== $ua && (bool) preg_match( '/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi/', $ua );
}

/**
 * Fingerprint of the deployed theme. A panel "git pull" rewrites .git/index and
 * the folders it touches; a live edit changes the folder's file. Any change
 * makes every stored copy stale (they reference the old markup and assets).
 */
function ipo_pc_theme_signature() {
	static $sig = null;

	if ( null !== $sig ) {
		return $sig;
	}
	$theme = dirname( __DIR__, 2 );
	$stamp = '';
	foreach ( array( '.git/index', 'functions.php', 'header.php', 'footer.php', 'includes', 'includes/page-cache', 'parts', 'parts/headers', 'page-templates', 'assets/styles', 'assets/scripts' ) as $rel ) {
		$stamp .= (int) @filemtime( $theme . '/' . $rel ) . '|';
	}
	$sig = md5( $stamp );

	return $sig;
}

/**
 * Whether a stored copy is still good.
 */
function ipo_pc_is_fresh( $file ) {
	$mtime = @filemtime( $file );

	if ( ! $mtime || $mtime < time() - IPO_PC_TTL ) {
		return false;
	}

	try {
		$midnight = new DateTime( 'today', new DateTimeZone( IPO_PC_TIMEZONE ) );
		if ( $mtime < $midnight->getTimestamp() ) {
			return false;
		}
	} catch ( Exception $e ) {
		return false;
	}

	return @file_get_contents( IPO_PC_DIR . '/.signature' ) === ipo_pc_theme_signature();
}

/**
 * Send the cached copy and stop, or return to let WordPress build the page.
 */
function ipo_pc_maybe_serve() {
	$file = ipo_pc_request_file();

	if ( ! $file || ! is_file( $file ) || ! ipo_pc_is_fresh( $file ) ) {
		return;
	}

	$mtime = filemtime( $file );

	if ( ! headers_sent() ) {
		// Plugins that ran before us may have queued a per-visitor cookie (e.g. _fbp).
		header_remove( 'Set-Cookie' );
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-IPO-Cache: HIT' );
		header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $mtime ) . ' GMT' );

		$since = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : false;
		if ( $since && $since >= $mtime ) {
			http_response_code( 304 );
			exit;
		}
	}

	if ( 'HEAD' !== $_SERVER['REQUEST_METHOD'] ) {
		readfile( $file );
	}
	exit;
}
