<?php
/**
 * IPO page cache — storing, purging, warming and the Tools screen.
 *
 * Loaded first thing in the child theme's functions.php. If the request has a
 * fresh copy it is sent right here (serve.php) and nothing else of the theme
 * runs. Otherwise one output buffer, opened before every other buffer, receives
 * the finished page (after the WebP / lazy-load / WP Rocket passes) and keeps
 * it when the page is safe to share.
 *
 * Everything is cleared when content changes (any post, term, menu, ACF options
 * page, related-programs rules), when a theme deploy is detected, and from the
 * admin bar. The home pages and the main menu pages are then rebuilt in the
 * background so the next visitor does not pay for the first render.
 *
 * Switch it off with define( 'IPO_PC_DISABLED', true ) in wp-config.php.
 *
 * @package wpstack-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/serve.php';

/* ------------------------------------------------------------------------- *
 * Serve / store
 * ------------------------------------------------------------------------- */

if ( ! is_admin() && ipo_pc_request_file() ) {
	ipo_pc_maybe_serve();

	if ( ! headers_sent() ) {
		header( 'X-IPO-Cache: MISS' );
	}
	ob_start( 'ipo_pc_store' );
}

/**
 * Output-buffer callback: keep the finished page when it is safe to share.
 */
function ipo_pc_store( $html ) {
	try {
		ipo_pc_maybe_write( $html );
	} catch ( Throwable $e ) {
		// Never let caching break the page that is being sent.
	}

	return $html;
}

function ipo_pc_maybe_write( $html ) {
	$file = ipo_pc_request_file();

	if ( ! $file || ! is_string( $html ) || strlen( $html ) < 512 || stripos( $html, '</html>' ) === false ) {
		return;
	}
	if ( 'GET' !== $_SERVER['REQUEST_METHOD'] || 200 !== http_response_code() ) {
		return;
	}
	// Only pages WordPress routed through its template loader. Screens that print
	// and die earlier (the hidden login URL, plugin endpoints) are never kept.
	if ( ! did_action( 'template_redirect' ) || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) ) {
		return;
	}
	if (
		( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) ||
		( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
		wp_doing_ajax() || wp_doing_cron() ||
		is_user_logged_in() || is_admin() ||
		is_404() || is_search() || is_preview() || is_feed() || is_trackback() || is_robots() ||
		is_customize_preview() || ( is_singular() && post_password_required() )
	) {
		return;
	}
	foreach ( headers_list() as $header ) {
		if ( stripos( $header, 'content-type:' ) === 0 && stripos( $header, 'text/html' ) === false ) {
			return;
		}
	}

	if ( ! ipo_pc_check_signature() ) {
		return;
	}

	$dir = dirname( $file );
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return;
	}

	$stamp = '<!-- IPO page cache: stored ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC -->';
	$tmp   = $file . '.' . uniqid( '', true ) . '.tmp';

	if ( false !== @file_put_contents( $tmp, $html . "\n" . $stamp ) ) {
		@rename( $tmp, $file );
	}
	@unlink( $tmp );
}

/**
 * Make sure the stored copies belong to the theme that is deployed now. After a
 * deploy everything is cleared once and the new signature recorded.
 *
 * @return bool False when the cache folder cannot be written.
 */
function ipo_pc_check_signature() {
	$sig_file = IPO_PC_DIR . '/.signature';
	$current  = ipo_pc_theme_signature();

	if ( @file_get_contents( $sig_file ) === $current ) {
		return true;
	}
	if ( ! is_dir( IPO_PC_DIR ) && ! wp_mkdir_p( IPO_PC_DIR ) ) {
		return false;
	}
	if ( ! is_file( IPO_PC_DIR . '/index.php' ) ) {
		@file_put_contents( IPO_PC_DIR . '/index.php', "<?php\n// Silence is golden.\n" );
	}

	ipo_pc_delete_pages();

	return false !== @file_put_contents( $sig_file, $current );
}

/* ------------------------------------------------------------------------- *
 * Purge
 * ------------------------------------------------------------------------- */

/**
 * Delete every stored page. Returns how many files were removed.
 */
function ipo_pc_delete_pages() {
	$count = 0;

	if ( ! is_dir( IPO_PC_DIR ) ) {
		return 0;
	}
	foreach ( (array) glob( IPO_PC_DIR . '/*', GLOB_ONLYDIR ) as $host_dir ) {
		foreach ( (array) glob( $host_dir . '/*.html' ) as $page ) {
			if ( @unlink( $page ) ) {
				$count++;
			}
		}
	}

	return $count;
}

/**
 * Queue a full purge for the end of the request, so a save that touches many
 * posts (imports, bulk edits) clears the folder once.
 */
function ipo_pc_schedule_purge() {
	static $queued = false;

	if ( $queued ) {
		return;
	}
	$queued = true;

	add_action(
		'shutdown',
		function () {
			ipo_pc_delete_pages();

			if ( ! wp_next_scheduled( 'ipo_pc_warm' ) ) {
				wp_schedule_single_event( time() + 30, 'ipo_pc_warm' );
			}
		},
		0
	);
}

add_action(
	'transition_post_status',
	function ( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
			return;
		}
		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
			return;
		}
		if ( in_array( $post->post_type, array( 'revision', 'customize_changeset', 'oembed_cache', 'user_request', 'custom_css', 'wp_global_styles' ), true ) ) {
			return;
		}
		ipo_pc_schedule_purge();
	},
	10,
	3
);

// ACF options pages (string ids) feed the header, footer, banners and sliders.
// Posts are covered above; their ACF fields are saved before the shutdown purge.
add_action(
	'acf/save_post',
	function ( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			ipo_pc_schedule_purge();
		}
	},
	20
);

foreach (
	array(
		'deleted_post',
		'created_term',
		'edited_term',
		'delete_term',
		'wp_update_nav_menu',
		'customize_save_after',
		'switch_theme',
		'activated_plugin',
		'deactivated_plugin',
		'upgrader_process_complete',
		'update_option_sidebars_widgets',
		'update_option_ipo_related_programs_zones',
		'after_rocket_clean_domain',
	) as $ipo_pc_hook
) {
	add_action( $ipo_pc_hook, 'ipo_pc_schedule_purge' );
}
unset( $ipo_pc_hook );

// WP Rocket's own page cache never wrote a file on this host; make sure it
// does not start keeping a second copy next to this one.
add_filter( 'do_rocket_generate_caching_files', '__return_false' );

/* ------------------------------------------------------------------------- *
 * Warm-up after a purge
 * ------------------------------------------------------------------------- */

/**
 * URLs rebuilt in the background after a purge: both home pages and the
 * top-level items of the theme's menus.
 */
function ipo_pc_warm_urls() {
	$urls = array( home_url( '/' ), apply_filters( 'wpml_permalink', home_url( '/' ), 'en' ) );

	foreach ( get_nav_menu_locations() as $menu_id ) {
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			if ( $item && empty( $item->menu_item_parent ) && ! empty( $item->url ) && 0 === strpos( $item->url, home_url() ) ) {
				$urls[] = $item->url;
			}
		}
	}

	$urls = array_values( array_unique( array_map( 'trailingslashit', $urls ) ) );

	return array_slice( apply_filters( 'ipo_pc_warm_urls', $urls ), 0, 12 );
}

add_action(
	'ipo_pc_warm',
	function () {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 );
		}
		$agents = array(
			'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36 IPO-Cache-Warm',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36 IPO-Cache-Warm',
		);
		foreach ( ipo_pc_warm_urls() as $url ) {
			foreach ( $agents as $agent ) {
				wp_remote_get(
					$url,
					array(
						'timeout'    => 30,
						'user-agent' => $agent,
						'sslverify'  => false,
						'cookies'    => array(),
					)
				);
			}
		}
	}
);

/* ------------------------------------------------------------------------- *
 * Admin: admin-bar purge + Tools > IPO Page Cache
 * ------------------------------------------------------------------------- */

add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'ipo-pc-purge',
				'title' => 'ניקוי מטמון',
				'href'  => wp_nonce_url( admin_url( 'admin-post.php?action=ipo_pc_purge' ), 'ipo_pc_purge' ),
			)
		);
	},
	100
);

add_action(
	'admin_post_ipo_pc_purge',
	function () {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( 'forbidden' );
		}
		check_admin_referer( 'ipo_pc_purge' );
		$count = ipo_pc_delete_pages();
		wp_schedule_single_event( time() + 5, 'ipo_pc_warm' );
		wp_safe_redirect( add_query_arg( 'ipo_pc_purged', $count, wp_get_referer() ? wp_get_referer() : admin_url( 'tools.php?page=ipo-page-cache' ) ) );
		exit;
	}
);

/**
 * The mu-plugin that serves pages before any plugin loads.
 */
function ipo_pc_loader_path() {
	return ( defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins' ) . '/ipo-page-cache.php';
}

function ipo_pc_loader_source() {
	$serve      = var_export( __DIR__ . '/serve.php', true );
	$stylesheet = var_export( get_stylesheet(), true );

	return "<?php\n/**\n * Plugin Name: IPO Page Cache (early loader)\n * Description: Serves cached pages before plugins load. Installed by the wpstack-child theme — remove it from Tools > IPO Page Cache.\n */\n\n"
		. "if ( defined( 'WP_CLI' ) || ! function_exists( 'get_option' ) || get_option( 'stylesheet' ) !== {$stylesheet} ) {\n\treturn;\n}\n"
		. "if ( is_file( {$serve} ) ) {\n\tdefine( 'IPO_PC_EARLY', true );\n\trequire_once {$serve};\n\tipo_pc_maybe_serve();\n}\n";
}

add_action(
	'admin_post_ipo_pc_loader',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'forbidden' );
		}
		check_admin_referer( 'ipo_pc_loader' );
		$path   = ipo_pc_loader_path();
		$result = 'error';

		if ( isset( $_POST['install'] ) ) {
			if ( ( is_dir( dirname( $path ) ) || wp_mkdir_p( dirname( $path ) ) ) && false !== @file_put_contents( $path, ipo_pc_loader_source() ) ) {
				$result = 'installed';
			}
		} elseif ( is_file( $path ) && @unlink( $path ) ) {
			$result = 'removed';
		}
		wp_safe_redirect( admin_url( 'tools.php?page=ipo-page-cache&ipo_pc_loader=' . $result ) );
		exit;
	}
);

// Keep an installed loader pointing at this copy of the theme.
add_action(
	'admin_init',
	function () {
		$path = ipo_pc_loader_path();
		if ( is_file( $path ) && @file_get_contents( $path ) !== ipo_pc_loader_source() && is_writable( $path ) ) {
			@file_put_contents( $path, ipo_pc_loader_source() );
		}
	}
);

add_action(
	'admin_menu',
	function () {
		add_management_page( 'IPO Page Cache', 'IPO Page Cache', 'manage_options', 'ipo-page-cache', 'ipo_pc_render_screen' );
	}
);

function ipo_pc_render_screen() {
	$pages = 0;
	$bytes = 0;
	foreach ( (array) glob( IPO_PC_DIR . '/*/*.html' ) as $page ) {
		$pages++;
		$bytes += (int) @filesize( $page );
	}
	$loader    = is_file( ipo_pc_loader_path() );
	$writable  = wp_is_writable( dirname( IPO_PC_DIR ) );
	$disabled  = defined( 'IPO_PC_DISABLED' ) && IPO_PC_DISABLED;
	$purge_url = wp_nonce_url( admin_url( 'admin-post.php?action=ipo_pc_purge' ), 'ipo_pc_purge' );
	?>
	<div class="wrap">
		<h1>IPO Page Cache</h1>
		<?php if ( isset( $_GET['ipo_pc_purged'] ) ) : ?>
			<div class="notice notice-success"><p><?php echo (int) $_GET['ipo_pc_purged']; ?> pages cleared. Home and menu pages are being rebuilt in the background.</p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['ipo_pc_loader'] ) ) : ?>
			<div class="notice notice-<?php echo 'error' === $_GET['ipo_pc_loader'] ? 'error' : 'success'; ?>"><p>Early loader: <?php echo esc_html( sanitize_key( $_GET['ipo_pc_loader'] ) ); ?>.</p></div>
		<?php endif; ?>
		<table class="widefat striped" style="max-width:720px">
			<tr><th>Status</th><td><?php echo $disabled ? 'Disabled (IPO_PC_DISABLED)' : ( $writable ? 'On' : 'Cannot write to ' . esc_html( dirname( IPO_PC_DIR ) ) ); ?></td></tr>
			<tr><th>Stored pages</th><td><?php echo (int) $pages; ?> (<?php echo esc_html( size_format( $bytes ) ); ?>)</td></tr>
			<tr><th>Folder</th><td><code><?php echo esc_html( IPO_PC_DIR ); ?></code></td></tr>
			<tr><th>Lifetime</th><td><?php echo (int) ( IPO_PC_TTL / 3600 ); ?>h, and never past local midnight</td></tr>
			<tr><th>Served from</th><td><?php echo $loader ? 'mu-plugin, before plugins load (fastest)' : 'the theme, after plugins load'; ?></td></tr>
		</table>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $purge_url ); ?>">Clear the whole cache</a>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ipo_pc_loader">
			<?php wp_nonce_field( 'ipo_pc_loader' ); ?>
			<?php if ( $loader ) : ?>
				<button class="button" name="remove" value="1">Remove the early loader</button>
			<?php else : ?>
				<button class="button" name="install" value="1">Install the early loader (serves cached pages before plugins load)</button>
			<?php endif; ?>
		</form>
		<p class="description">A cached response carries the header <code>X-IPO-Cache: HIT</code>. Logged-in users, searches, 404s and URLs with a query string (other than tracking tags and <code>event_id</code>) always get a fresh page.</p>
	</div>
	<?php
}
