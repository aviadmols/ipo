<?php
/**
 * IPO Security — self-maintenance.
 *
 * Things that drift back after updates or that nobody remembers to check:
 *  - Public files that leak versions / server paths (debug.log, readme.html,
 *    license.txt, wp-config-sample.php) are deleted, again after every core update.
 *  - Plugins that do nothing here but add attack surface are deactivated once
 *    (an admin can turn them back on; we never fight that).
 *  - AI Bridge tokens that were never used, or not for 90 days, are revoked.
 *  - wp-content/uploads is scanned daily for PHP files (a classic web shell
 *    hiding place); new ones raise a critical alert.
 *  - Administrators and editors must use passwords of 12+ characters.
 *
 * Every action is written to the audit log.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Security_Maintenance {

	const CRON_HOOK          = 'ipo_sec_daily';
	const PRUNED_OPTION      = 'ipo_sec_plugins_pruned';
	const UPLOAD_PHP_OPTION  = 'ipo_sec_upload_php_known';
	const UPLOAD_SCAN_OPTION = 'ipo_sec_upload_php_scan';
	const EXEC_CHECK_TRANSIENT = 'ipo_sec_uploads_exec';
	const EXEC_PROBE_PREFIX  = 'ipo-exec-probe-';
	const PRIVILEGED_ROLES  = array( 'administrator', 'editor' );
	const MIN_PASSWORD_CHARS = 12;
	const TOKEN_UNUSED_DAYS  = 3;
	const TOKEN_IDLE_DAYS    = 90;

	/**
	 * Plugin file => reason. The WooCommerce add-ons need WooCommerce, which is not installed.
	 */
	const UNUSED_PLUGINS = array(
		'facebook-for-woocommerce/facebook-for-woocommerce.php' => 'requires WooCommerce (not active)',
		'tiktok-for-business/tiktok-for-woocommerce.php'        => 'requires WooCommerce (not active)',
		'health-check/health-check.php'                         => 'troubleshooting tool, not needed on a live site',
		'regenerate-thumbnails/regenerate-thumbnails.php'       => 'one-off tool',
		'wp-sweep/wp-sweep.php'                                 => 'one-off DB clean-up tool that can delete data',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'schedule' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'daily' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_tick' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'remove_exposed_files' ), 99 );
		add_action( 'admin_post_ipo_security_run_checks', array( __CLASS__, 'handle_run_checks' ) );

		add_action( 'user_profile_update_errors', array( __CLASS__, 'check_profile_password' ), 10, 3 );
		add_action( 'validate_password_reset', array( __CLASS__, 'check_reset_password' ), 10, 2 );
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 10 * MINUTE_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	public static function daily() {
		self::remove_exposed_files();
		self::revoke_stale_bridge_tokens();
		self::scan_uploads_for_php();
		self::check_uploads_exec( true );
	}

	/** "Run checks now" button on the security settings tab. */
	public static function handle_run_checks() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'אין הרשאה.' );
		}
		check_admin_referer( 'ipo_security_run_checks' );
		self::scan_uploads_for_php();
		self::check_uploads_exec( true );
		wp_safe_redirect( admin_url( 'tools.php?page=' . IPO_Security_Admin::PAGE_SLUG . '&tab=settings&ipo_msg=checked' ) );
		exit;
	}

	/** nginx rule the host has to add when uploads run PHP. */
	public static function nginx_rule() {
		return "location ~* ^/wp-content/uploads/.*\\.(php\\d?|phtml|phar|phps|pht)$ {\n    deny all;\n}";
	}

	/** An admin screen: do the quick jobs at most every 12 hours, and the one-time plugin clean-up. */
	public static function admin_tick() {
		if ( ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
			return;
		}
		self::prune_unused_plugins();
		if ( get_transient( 'ipo_sec_admin_tick' ) ) {
			return;
		}
		set_transient( 'ipo_sec_admin_tick', 1, 12 * HOUR_IN_SECONDS );
		self::remove_exposed_files();
		self::revoke_stale_bridge_tokens();
	}

	/* ------------------------------------------------------------------ */
	/* Exposed files                                                       */
	/* ------------------------------------------------------------------ */

	public static function remove_exposed_files() {
		$files = array(
			'readme.html'          => ABSPATH . 'readme.html',
			'license.txt'          => ABSPATH . 'license.txt',
			'wp-config-sample.php' => ABSPATH . 'wp-config-sample.php',
		);
		// Only while nothing writes to it on purpose.
		if ( ! ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ) {
			$files['wp-content/debug.log'] = WP_CONTENT_DIR . '/debug.log';
		}

		foreach ( $files as $label => $path ) {
			if ( ! file_exists( $path ) ) {
				continue;
			}
			$bytes = (int) @filesize( $path ); // phpcs:ignore
			$ok    = @unlink( $path ); // phpcs:ignore
			IPO_Audit_Log::log(
				'exposed_file_removed',
				array(
					'severity'     => $ok ? 'notice' : 'warning',
					'object_type'  => 'file',
					'object_id'    => $label,
					'object_label' => $ok ? 'removed (publicly readable)' : 'could not remove (check file permissions)',
					'details'      => array( 'bytes' => $bytes ),
				)
			);
		}
	}

	/* ------------------------------------------------------------------ */
	/* Unused plugins                                                      */
	/* ------------------------------------------------------------------ */

	public static function prune_unused_plugins() {
		if ( get_option( self::PRUNED_OPTION ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$woo  = is_plugin_active( 'woocommerce/woocommerce.php' );
		$done = array();
		foreach ( self::UNUSED_PLUGINS as $file => $reason ) {
			if ( $woo && false !== strpos( $file, 'woocommerce' ) ) {
				continue;
			}
			if ( is_plugin_active( $file ) ) {
				deactivate_plugins( $file, false, false );
				$done[ $file ] = $reason;
			}
		}

		update_option( self::PRUNED_OPTION, gmdate( 'Y-m-d H:i:s' ), false );

		if ( $done ) {
			IPO_Audit_Log::log(
				'unused_plugins_deactivated',
				array(
					'severity'     => 'notice',
					'object_type'  => 'plugin',
					'object_id'    => (string) count( $done ),
					'object_label' => 'security clean-up (can be re-activated from the Plugins screen)',
					'details'      => $done,
				)
			);
		}
	}

	/* ------------------------------------------------------------------ */
	/* AI Bridge tokens                                                    */
	/* ------------------------------------------------------------------ */

	public static function revoke_stale_bridge_tokens() {
		if ( ! class_exists( 'IPO_AI_Tokens' ) ) {
			return;
		}
		$now = time();
		foreach ( IPO_AI_Tokens::all() as $t ) {
			if ( empty( $t['id'] ) ) {
				continue;
			}
			$created = isset( $t['created'] ) ? (int) $t['created'] : 0;
			$used    = isset( $t['last_used'] ) ? (int) $t['last_used'] : 0;

			$never_used = ! $used && $created && $created < $now - self::TOKEN_UNUSED_DAYS * DAY_IN_SECONDS;
			$idle       = $used && $used < $now - self::TOKEN_IDLE_DAYS * DAY_IN_SECONDS;
			if ( ! $never_used && ! $idle ) {
				continue;
			}
			if ( IPO_AI_Tokens::revoke( $t['id'] ) ) {
				IPO_Audit_Log::log(
					'ai_token_revoked',
					array(
						'severity'     => 'notice',
						'object_type'  => 'ai_token',
						'object_id'    => (string) $t['id'],
						'object_label' => isset( $t['label'] ) ? (string) $t['label'] : '',
						'details'      => array(
							'reason'    => $never_used ? 'never used' : 'unused for ' . self::TOKEN_IDLE_DAYS . ' days',
							'created'   => $created ? gmdate( 'Y-m-d', $created ) : '',
							'last_used' => $used ? gmdate( 'Y-m-d', $used ) : '',
						),
					)
				);
			}
		}
	}

	/* ------------------------------------------------------------------ */
	/* PHP files inside uploads                                            */
	/* ------------------------------------------------------------------ */

	public static function scan_uploads_for_php() {
		$uploads = wp_get_upload_dir();
		$base    = isset( $uploads['basedir'] ) ? $uploads['basedir'] : '';
		if ( ! $base || ! is_dir( $base ) ) {
			return;
		}

		$found = array();
		$seen  = 0;
		try {
			$it = new RecursiveIteratorIterator(
				new RecursiveCallbackFilterIterator(
					new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ),
					function ( $file ) {
						// Our own page cache only holds .html files.
						return ! ( $file->isDir() && 'ipo-page-cache' === $file->getFilename() );
					}
				)
			);
			foreach ( $it as $file ) {
				if ( ++$seen > 300000 ) {
					break;
				}
				if ( ! preg_match( '/\.(php\d?|phtml|phar|phps|pht)$/i', $file->getFilename() ) ) {
					continue;
				}
				$size = (int) $file->getSize();
				// The empty "Silence is golden" index.php files plugins drop are harmless.
				if ( 'index.php' === strtolower( $file->getFilename() ) && $size < 100 ) {
					continue;
				}
				$rel           = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $base ) ) ), '/' );
				$found[ $rel ] = $size . '|' . $file->getMTime();
			}
		} catch ( \Throwable $e ) {
			return;
		}

		// Our own exec probe (check_uploads_exec) lives for a fraction of a second.
		foreach ( array_keys( $found ) as $rel ) {
			if ( 0 === strpos( basename( $rel ), self::EXEC_PROBE_PREFIX ) ) {
				unset( $found[ $rel ] );
			}
		}

		// Classify every current file, so the status screen always shows the full picture.
		$suspects = array();
		foreach ( array_keys( $found ) as $rel ) {
			if ( ! self::is_compiled_twig( $base . '/' . $rel, $rel ) ) {
				$suspects[] = $rel;
			}
		}

		$known = get_option( self::UPLOAD_PHP_OPTION, null );
		update_option( self::UPLOAD_PHP_OPTION, $found, false );
		update_option(
			self::UPLOAD_SCAN_OPTION,
			array(
				'time'     => time(),
				'total'    => count( $found ),
				'suspects' => array_slice( $suspects, 0, 100 ),
			),
			false
		);

		$new = is_array( $known ) ? array_diff_assoc( $found, $known ) : $found;
		if ( ! $new ) {
			return;
		}

		$new_suspects = array_values( array_intersect( array_keys( $new ), $suspects ) );
		$new_twig     = array_values( array_diff( array_keys( $new ), $suspects ) );

		if ( $new_suspects ) {
			IPO_Audit_Log::log(
				'php_in_uploads',
				array(
					'severity'     => 'critical',
					'object_type'  => 'file',
					'object_id'    => 'uploads',
					'object_label' => count( $new_suspects ) . ' PHP file(s) in wp-content/uploads — uploads should never hold code; check and delete',
					'details'      => array_slice( $new_suspects, 0, 50 ),
				)
			);
		}
		if ( $new_twig ) {
			IPO_Audit_Log::log(
				'php_in_uploads',
				array(
					'severity'     => 'notice',
					'object_type'  => 'file',
					'object_id'    => 'uploads',
					'object_label' => count( $new_twig ) . ' compiled WPML Twig template(s) (cache, checked clean)',
					'details'      => array_slice( $new_twig, 0, 50 ),
				)
			);
		}
	}

	/**
	 * A WPML Twig cache file: compiled template class, nothing that runs input or hidden code.
	 * Anything that fails one check stays "suspect" (critical).
	 *
	 * @param string $path Absolute path.
	 * @param string $rel  Path relative to uploads.
	 */
	public static function is_compiled_twig( $path, $rel ) {
		if ( 0 !== strpos( $rel, 'cache/wpml/twig/' ) || ! is_file( $path ) || filesize( $path ) > 2 * MB_IN_BYTES ) {
			return false;
		}
		$code = (string) @file_get_contents( $path ); // phpcs:ignore
		if ( 0 !== strpos( $code, '<?php' ) ) {
			return false;
		}

		// Twig 1 "Twig_Template", or a namespaced \Twig\Template (WPML prefixes it, e.g. WPML\Core\Twig\Template),
		// either written out or imported with "use ...Twig\Template;" and extended as "Template".
		$extends_full = preg_match( '/\bclass\s+__TwigTemplate_\w+\s+extends\s+\\\\?(?:[A-Za-z0-9_]+\\\\)*(?:Twig\\\\Template|Twig_Template)\b/', $code );
		$extends_use  = preg_match( '/\bclass\s+__TwigTemplate_\w+\s+extends\s+Template\b/', $code )
			&& preg_match( '/^use\s+(?:[A-Za-z0-9_]+\\\\)*Twig\\\\Template\s*;/m', $code );
		if ( ! $extends_full && ! $extends_use ) {
			return false;
		}

		$danger = '/\b(?:eval|assert|system|exec|shell_exec|passthru|popen|proc_open|pcntl_exec|base64_decode|gzinflate|gzuncompress|gzdecode|str_rot13|create_function|move_uploaded_file|file_put_contents|fsockopen|curl_exec)\s*\(|\$_(?:POST|GET|REQUEST|COOKIE|FILES|SERVER)\b/i';
		return ! preg_match( $danger, $code );
	}

	/* ------------------------------------------------------------------ */
	/* Does the web server run PHP inside uploads?                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Drop a tiny PHP file in uploads, request it over HTTP, delete it at once.
	 *
	 * @return array{status:string,code:int,time:int} status: executes | blocked | unknown
	 */
	public static function check_uploads_exec( $force = false ) {
		$cached = get_transient( self::EXEC_CHECK_TRANSIENT );
		if ( ! $force && is_array( $cached ) ) {
			return $cached;
		}

		$result  = array( 'status' => 'unknown', 'code' => 0, 'time' => time() );
		$uploads = wp_get_upload_dir();
		if ( empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) || ! wp_is_writable( $uploads['basedir'] ) ) {
			set_transient( self::EXEC_CHECK_TRANSIENT, $result, HOUR_IN_SECONDS );
			return $result;
		}

		$name = self::EXEC_PROBE_PREFIX . wp_generate_password( 16, false ) . '.php';
		$file = trailingslashit( $uploads['basedir'] ) . $name;
		// The marker is only assembled when PHP runs, so a server that sends the file as text never matches.
		$ok = false !== @file_put_contents( $file, "<?php echo 'IPO-EXEC-' . 'RAN';\n" ); // phpcs:ignore

		if ( $ok ) {
			$response = wp_remote_get(
				trailingslashit( $uploads['baseurl'] ) . $name,
				array(
					'timeout'     => 10,
					'redirection' => 0,
					'sslverify'   => apply_filters( 'https_local_ssl_verify', false ),
					'headers'     => array( 'Cache-Control' => 'no-cache' ),
				)
			);
			@unlink( $file ); // phpcs:ignore

			if ( ! is_wp_error( $response ) ) {
				$code = (int) wp_remote_retrieve_response_code( $response );
				$body = (string) wp_remote_retrieve_body( $response );

				$result['code'] = $code;
				if ( false !== strpos( $body, 'IPO-EXEC-RAN' ) ) {
					$result['status'] = 'executes';
				} elseif ( in_array( $code, array( 401, 403, 404, 405, 410 ), true ) || false !== strpos( $body, "'IPO-EXEC-' . 'RAN'" ) ) {
					$result['status'] = 'blocked';
				}
			}
		}

		set_transient( self::EXEC_CHECK_TRANSIENT, $result, 12 * HOUR_IN_SECONDS );
		return $result;
	}

	/* ------------------------------------------------------------------ */
	/* Password policy for privileged accounts                             */
	/* ------------------------------------------------------------------ */

	protected static function weak_reason( $pass, $login ) {
		$pass = (string) $pass;
		if ( function_exists( 'mb_strlen' ) ? mb_strlen( $pass ) < self::MIN_PASSWORD_CHARS : strlen( $pass ) < self::MIN_PASSWORD_CHARS ) {
			return sprintf( 'לפחות %d תווים', self::MIN_PASSWORD_CHARS );
		}
		if ( $login && false !== stripos( $pass, (string) $login ) ) {
			return 'בלי שם המשתמש בתוך הסיסמה';
		}
		if ( preg_match( '/^(\d+|[a-z]+|(.)\2+)$/i', $pass ) ) {
			return 'שילוב של אותיות, ספרות או סימנים';
		}
		return '';
	}

	protected static function is_privileged( $user_id, $new_role = '' ) {
		if ( $new_role && in_array( $new_role, self::PRIVILEGED_ROLES, true ) ) {
			return true;
		}
		$user = $user_id ? get_userdata( $user_id ) : false;
		return $user && array_intersect( self::PRIVILEGED_ROLES, (array) $user->roles );
	}

	/**
	 * Profile / user-edit / add-user screens.
	 *
	 * @param WP_Error $errors Errors.
	 * @param bool     $update Existing user.
	 * @param stdClass $user   User data from the form.
	 */
	public static function check_profile_password( $errors, $update, $user ) {
		if ( empty( $_POST['pass1'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$role = isset( $user->role ) ? (string) $user->role : '';
		if ( ! self::is_privileged( isset( $user->ID ) ? (int) $user->ID : 0, $role ) ) {
			return;
		}
		$reason = self::weak_reason( wp_unslash( $_POST['pass1'] ), isset( $user->user_login ) ? $user->user_login : '' ); // phpcs:ignore
		if ( $reason ) {
			$errors->add( 'ipo_weak_password', '<strong>שגיאה:</strong> סיסמה של מנהל או עורך חייבת להיות חזקה יותר — ' . $reason . '.' );
		}
	}

	/**
	 * "Set a new password" screen after a reset link.
	 *
	 * @param WP_Error         $errors Errors.
	 * @param WP_User|WP_Error $user   User.
	 */
	public static function check_reset_password( $errors, $user ) {
		if ( empty( $_POST['pass1'] ) || ! $user instanceof WP_User || ! self::is_privileged( $user->ID ) ) { // phpcs:ignore
			return;
		}
		$reason = self::weak_reason( wp_unslash( $_POST['pass1'] ), $user->user_login ); // phpcs:ignore
		if ( $reason ) {
			$errors->add( 'ipo_weak_password', '<strong>שגיאה:</strong> סיסמה של מנהל או עורך חייבת להיות חזקה יותר — ' . $reason . '.' );
		}
	}
}
