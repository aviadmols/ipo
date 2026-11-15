<?php
/**
 * IPO Security — hardening.
 *
 *  - Login lockout per IP (5 failures / 15 min => 20 min lock; 3 locks / 24h => 24h lock).
 *  - Lost-password rate limit per IP and per account; no "user does not exist" leak.
 *  - Same error for wrong user and wrong password.
 *  - XML-RPC off, application passwords off, theme/plugin file editor off.
 *  - No user enumeration (?author=N, /author/x/, REST /wp/v2/users, sitemaps, oEmbed).
 *  - Password change => other sessions of that user are logged out.
 *  - Password-reset keys and 2FA codes are scrubbed from the Email Log table.
 *  - Basic security headers; WordPress version hidden.
 *  - Redactor used by the AI Bridge so DB reads never return hashes / secrets / reset keys.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Security_Hardening {

	const FAIL_LIMIT        = 5;
	const FAIL_WINDOW       = 900;   // 15 min.
	const LOCK_SHORT        = 1200;  // 20 min.
	const LOCK_LONG         = 86400; // 24 h.
	const LOCKS_BEFORE_LONG = 3;

	const RESET_PER_IP_HOUR   = 5;
	const RESET_PER_USER_HOUR = 3;

	/** @var bool */
	protected static $scrub_email_log = false;

	public static function init() {
		// File editor off (theme functions.php runs before any admin screen checks this).
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		// Login lockout.
		add_filter( 'authenticate', array( __CLASS__, 'pre_auth_lock_check' ), 1, 3 );
		add_filter( 'authenticate', array( __CLASS__, 'post_auth_lock_check' ), 99, 3 );
		add_action( 'wp_login_failed', array( __CLASS__, 'count_failure' ), 5, 2 );
		add_action( 'wp_login', array( __CLASS__, 'clear_failures' ), 5, 2 );
		add_filter( 'login_errors', array( __CLASS__, 'generic_login_error' ) );

		// Lost password.
		add_action( 'lostpassword_post', array( __CLASS__, 'limit_reset_requests' ), 10, 2 );
		add_filter( 'lostpassword_errors', array( __CLASS__, 'hide_unknown_user_on_reset' ), 99, 2 );

		// XML-RPC + application passwords.
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', array( __CLASS__, 'no_xmlrpc_methods' ) );
		add_filter( 'wp_headers', array( __CLASS__, 'strip_pingback_header' ) );
		add_filter( 'wp_is_application_passwords_available', '__return_false' );

		// User enumeration.
		add_action( 'template_redirect', array( __CLASS__, 'block_author_scans' ), 1 );
		add_filter( 'rest_request_before_callbacks', array( __CLASS__, 'block_rest_users' ), 10, 3 );
		add_filter( 'wp_sitemaps_add_provider', array( __CLASS__, 'no_user_sitemap' ), 10, 2 );
		add_filter( 'oembed_response_data', array( __CLASS__, 'strip_oembed_author' ) );

		// Sessions.
		add_action( 'profile_update', array( __CLASS__, 'logout_other_sessions_on_password_change' ), 20, 2 );
		add_action( 'after_password_reset', array( __CLASS__, 'logout_all_sessions' ), 20, 1 );

		// Email Log scrubbing.
		add_filter( 'wp_mail', array( __CLASS__, 'watch_mail' ), 1 );
		add_action( 'shutdown', array( __CLASS__, 'scrub_email_log' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'scrub_email_log_once' ) );
		add_action( 'admin_init', array( __CLASS__, 'remove_public_debug_log' ) );

		// Headers + version.
		add_action( 'send_headers', array( __CLASS__, 'security_headers' ) );
		add_action( 'admin_init', array( __CLASS__, 'security_headers' ) );
		add_action( 'login_init', array( __CLASS__, 'security_headers' ) );
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	/* ================================================================== */
	/* Login lockout                                                       */
	/* ================================================================== */

	protected static function ip_key( $prefix ) {
		return 'ipo_sec_' . $prefix . '_' . md5( IPO_Audit_Log::ip() );
	}

	/** Seconds left on this IP's lock, 0 if not locked. */
	public static function lock_remaining() {
		$until = (int) get_transient( self::ip_key( 'lock' ) );
		return $until > time() ? $until - time() : 0;
	}

	protected static function locked_error() {
		$minutes = max( 1, (int) ceil( self::lock_remaining() / 60 ) );
		return new WP_Error(
			'ipo_locked',
			sprintf( '<strong>הגישה נחסמה זמנית</strong> בגלל ניסיונות התחברות רבים שנכשלו. נסו שוב בעוד %d דקות.', $minutes )
		);
	}

	/** Locked IP: skip the password check entirely (saves CPU under brute force). */
	public static function pre_auth_lock_check( $user, $username = '', $password = '' ) {
		if ( '' !== (string) $username && self::lock_remaining() ) {
			remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
			remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
			remove_filter( 'authenticate', 'wp_authenticate_application_password', 20 );
		}
		return $user;
	}

	public static function post_auth_lock_check( $user, $username = '', $password = '' ) {
		if ( '' === (string) $username || ! self::lock_remaining() ) {
			return $user;
		}
		if ( ! get_transient( self::ip_key( 'lock_logged' ) ) ) {
			set_transient( self::ip_key( 'lock_logged' ), 1, self::lock_remaining() );
			IPO_Audit_Log::log(
				'login_blocked',
				array(
					'severity'     => 'warning',
					'object_type'  => 'ip',
					'object_id'    => IPO_Audit_Log::ip(),
					'object_label' => 'user tried: ' . substr( sanitize_text_field( $username ), 0, 60 ),
				)
			);
		}
		return self::locked_error();
	}

	public static function count_failure( $username, $error = null ) {
		if ( is_wp_error( $error ) && in_array( 'ipo_locked', $error->get_error_codes(), true ) ) {
			return;
		}
		$key   = self::ip_key( 'fail' );
		$count = (int) get_transient( $key ) + 1;
		set_transient( $key, $count, self::FAIL_WINDOW );

		if ( $count < self::FAIL_LIMIT ) {
			return;
		}

		delete_transient( $key );
		$locks_key = self::ip_key( 'locks' );
		$locks     = (int) get_transient( $locks_key ) + 1;
		set_transient( $locks_key, $locks, DAY_IN_SECONDS );

		$duration = $locks >= self::LOCKS_BEFORE_LONG ? self::LOCK_LONG : self::LOCK_SHORT;
		set_transient( self::ip_key( 'lock' ), time() + $duration, $duration );
		self::remember_lock( IPO_Audit_Log::ip(), time() + $duration );

		$target = get_user_by( 'login', $username );
		$target = $target ? $target : ( is_email( $username ) ? get_user_by( 'email', $username ) : false );

		IPO_Audit_Log::log(
			'lockout',
			array(
				'severity'     => ( $target && user_can( $target, 'manage_options' ) && $locks >= 2 ) ? 'critical' : 'warning',
				'object_type'  => 'ip',
				'object_id'    => IPO_Audit_Log::ip(),
				'object_label' => 'last user tried: ' . substr( sanitize_text_field( $username ), 0, 60 ),
				'details'      => array(
					'failures'       => self::FAIL_LIMIT,
					'window_minutes' => self::FAIL_WINDOW / 60,
					'locked_minutes' => $duration / 60,
					'locks_last_24h' => $locks,
				),
			)
		);
	}

	public static function clear_failures( $login = '', $user = null ) {
		delete_transient( self::ip_key( 'fail' ) );
	}

	/** Keep a small list of active locks so admins can see/unlock them on the settings page. */
	protected static function remember_lock( $ip, $until ) {
		$locks = get_option( 'ipo_sec_active_locks', array() );
		$locks = is_array( $locks ) ? $locks : array();
		foreach ( $locks as $k => $v ) {
			if ( (int) $v < time() ) {
				unset( $locks[ $k ] );
			}
		}
		$locks[ $ip ] = $until;
		if ( count( $locks ) > 200 ) {
			$locks = array_slice( $locks, -200, null, true );
		}
		update_option( 'ipo_sec_active_locks', $locks, false );
	}

	/** @return array<string,int> ip => until */
	public static function active_locks() {
		$locks = get_option( 'ipo_sec_active_locks', array() );
		$locks = is_array( $locks ) ? $locks : array();
		return array_filter(
			$locks,
			function ( $until ) {
				return (int) $until > time();
			}
		);
	}

	public static function unlock_ip( $ip ) {
		$hash = md5( $ip );
		foreach ( array( 'lock', 'locks', 'fail', 'lock_logged' ) as $p ) {
			delete_transient( 'ipo_sec_' . $p . '_' . $hash );
		}
		$locks = self::active_locks();
		unset( $locks[ $ip ] );
		update_option( 'ipo_sec_active_locks', $locks, false );
	}

	/** Same message for "no such user" and "wrong password". */
	public static function generic_login_error( $message ) {
		global $errors;
		if ( ! is_wp_error( $errors ) ) {
			return $message;
		}
		$codes = $errors->get_error_codes();
		if ( in_array( 'ipo_locked', $codes, true ) ) {
			return $message;
		}
		if ( array_intersect( $codes, array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
			return '<strong>שגיאה:</strong> שם המשתמש או הסיסמה שגויים.';
		}
		return $message;
	}

	/* ================================================================== */
	/* Lost password                                                       */
	/* ================================================================== */

	/**
	 * @param WP_Error      $errors    Errors.
	 * @param WP_User|false $user_data Account.
	 */
	public static function limit_reset_requests( $errors, $user_data = false ) {
		// Admin-initiated "Send password reset" from the users screen is not limited.
		if ( is_admin() && current_user_can( 'edit_users' ) ) {
			return;
		}

		$ip_key = self::ip_key( 'reset' );
		$ip_n   = (int) get_transient( $ip_key );
		$u_key  = $user_data ? 'ipo_sec_reset_u_' . (int) $user_data->ID : '';
		$u_n    = $u_key ? (int) get_transient( $u_key ) : 0;

		if ( $ip_n >= self::RESET_PER_IP_HOUR || $u_n >= self::RESET_PER_USER_HOUR ) {
			$errors->add( 'ipo_reset_limited', '<strong>שגיאה:</strong> נשלחו יותר מדי בקשות לאיפוס סיסמה. נסו שוב בעוד שעה.' );
			return;
		}

		set_transient( $ip_key, $ip_n + 1, HOUR_IN_SECONDS );
		if ( $u_key ) {
			set_transient( $u_key, $u_n + 1, HOUR_IN_SECONDS );
		}
	}

	/**
	 * "There is no account with that username" tells an attacker which names exist.
	 * Show the normal "check your e-mail" screen instead.
	 */
	public static function hide_unknown_user_on_reset( $errors, $user_data = false ) {
		if ( ! is_wp_error( $errors ) || is_admin() ) {
			return $errors;
		}
		$codes = $errors->get_error_codes();
		// Core adds "invalidcombo" only after this filter, when $user_data is empty and
		// there are no other errors; "invalid_email" is added before it.
		if ( ! $user_data && ( array() === $codes || array( 'invalid_email' ) === $codes ) ) {
			wp_safe_redirect( site_url( 'wp-login.php?checkemail=confirm', 'login' ) );
			exit;
		}
		return $errors;
	}

	/* ================================================================== */
	/* XML-RPC                                                             */
	/* ================================================================== */

	public static function no_xmlrpc_methods( $methods ) {
		return array();
	}

	public static function strip_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/* ================================================================== */
	/* User enumeration                                                    */
	/* ================================================================== */

	public static function block_author_scans() {
		if ( is_user_logged_in() ) {
			return;
		}
		if ( isset( $_GET['author'] ) || is_author() ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}

	public static function block_rest_users( $response, $handler, $request ) {
		if ( 0 === strpos( $request->get_route(), '/wp/v2/users' ) && ! is_user_logged_in() ) {
			return new WP_Error( 'rest_user_cannot_view', 'Sorry, you are not allowed to list users.', array( 'status' => 401 ) );
		}
		return $response;
	}

	public static function no_user_sitemap( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	}

	public static function strip_oembed_author( $data ) {
		unset( $data['author_name'], $data['author_url'] );
		return $data;
	}

	/* ================================================================== */
	/* Sessions                                                            */
	/* ================================================================== */

	public static function logout_other_sessions_on_password_change( $user_id, $old_user = null ) {
		global $wpdb;
		if ( ! $old_user instanceof WP_User || ! class_exists( 'WP_Session_Tokens' ) ) {
			return;
		}
		$hash = $wpdb->get_var( $wpdb->prepare( "SELECT user_pass FROM {$wpdb->users} WHERE ID = %d", $user_id ) );
		if ( ! $hash || $hash === $old_user->user_pass ) {
			return;
		}
		$sessions = WP_Session_Tokens::get_instance( $user_id );
		if ( get_current_user_id() === (int) $user_id ) {
			$sessions->destroy_others( wp_get_session_token() );
		} else {
			$sessions->destroy_all();
		}
	}

	public static function logout_all_sessions( $user ) {
		if ( $user instanceof WP_User && class_exists( 'WP_Session_Tokens' ) ) {
			WP_Session_Tokens::get_instance( $user->ID )->destroy_all();
		}
	}

	/* ================================================================== */
	/* Email Log scrubbing                                                 */
	/* ================================================================== */

	public static function watch_mail( $atts ) {
		$message = isset( $atts['message'] ) && is_string( $atts['message'] ) ? $atts['message'] : '';
		if ( preg_match( '/(action=rp|[?&;]key=[A-Za-z0-9]{8,}|IPO-CODE:)/', $message ) ) {
			self::$scrub_email_log = true;
		}
		return $atts;
	}

	public static function scrub_email_log() {
		if ( self::$scrub_email_log ) {
			self::run_scrub( true );
		}
	}

	public static function scrub_email_log_once() {
		if ( get_option( 'ipo_sec_email_log_scrubbed' ) === '1' || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		self::run_scrub( false );
		update_option( 'ipo_sec_email_log_scrubbed', '1', false );
	}

	/**
	 * wp-content/debug.log was readable by anyone over HTTP (a stale 2023 log with
	 * server paths). Remove it the first time an administrator opens wp-admin,
	 * unless debug logging is switched on right now (then someone is using it).
	 */
	public static function remove_public_debug_log() {
		if ( ! current_user_can( 'manage_options' ) || ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ) {
			return;
		}
		$file = WP_CONTENT_DIR . '/debug.log';
		if ( ! file_exists( $file ) ) {
			return;
		}
		$bytes = (int) filesize( $file );
		$ok    = @unlink( $file ); // phpcs:ignore
		IPO_Audit_Log::log(
			'option_deleted',
			array(
				'severity'     => $ok ? 'notice' : 'warning',
				'object_type'  => 'file',
				'object_id'    => 'wp-content/debug.log',
				'object_label' => $ok ? 'public debug.log removed' : 'could not remove public debug.log',
				'details'      => array( 'bytes' => $bytes ),
			)
		);
	}

	protected static function run_scrub( $recent_only ) {
		global $wpdb;
		$table = $wpdb->prefix . 'email_log';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}
		$recent = $recent_only ? " AND sent_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)" : '';
		$suppress = $wpdb->suppress_errors( true );
		// phpcs:disable WordPress.DB.PreparedSQL
		$wpdb->query( "UPDATE {$table} SET message = REGEXP_REPLACE(message, '([?&;]key=)[A-Za-z0-9]{8,}', '\\\\1[REDACTED]') WHERE message REGEXP '[?&;]key=[A-Za-z0-9]{8,}'{$recent}" );
		$wpdb->query( "UPDATE {$table} SET message = REGEXP_REPLACE(message, '(IPO-CODE: )[0-9]{6}', '\\\\1******') WHERE message LIKE '%IPO-CODE: %'{$recent}" );
		// phpcs:enable
		$wpdb->suppress_errors( $suppress );
	}

	/* ================================================================== */
	/* Headers                                                             */
	/* ================================================================== */

	public static function security_headers() {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000' );
		}
		if ( is_admin() || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) ) {
			header( 'X-Frame-Options: SAMEORIGIN' );
		}
	}

	/* ================================================================== */
	/* Redactor (AI Bridge read results)                                   */
	/* ================================================================== */

	/**
	 * Strip secrets from rows returned by the AI Bridge read-only SQL tool.
	 *
	 * @param array $rows Rows (ARRAY_A).
	 * @return array
	 */
	public static function redact_rows( $rows ) {
		if ( ! is_array( $rows ) ) {
			return $rows;
		}
		$secret_cols = array( 'user_pass', 'user_activation_key' );
		foreach ( $rows as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$row_is_secret_option = false;
			foreach ( array( 'option_name', 'meta_key' ) as $name_col ) {
				if ( isset( $row[ $name_col ] ) && preg_match( '/(smtp|password|passwd|secret|api_?key|apikey|licen[cs]e_?key|private_?key|auth_?key|_salt$|client_secret|access_token|refresh_token|application_passwords|ipo_ai_bridge_tokens)/i', (string) $row[ $name_col ] ) ) {
					$row_is_secret_option = true;
				}
			}
			foreach ( $row as $col => $value ) {
				if ( in_array( strtolower( (string) $col ), $secret_cols, true ) ) {
					$rows[ $i ][ $col ] = '' === (string) $value ? '' : '[redacted]';
					continue;
				}
				if ( ! is_string( $value ) || '' === $value ) {
					continue;
				}
				if ( $row_is_secret_option && in_array( $col, array( 'option_value', 'meta_value' ), true ) ) {
					$rows[ $i ][ $col ] = '[redacted: secret setting]';
					continue;
				}
				$rows[ $i ][ $col ] = self::redact_value( $value );
			}
		}
		return $rows;
	}

	public static function redact_value( $value ) {
		// Password / token hashes.
		if ( preg_match( '/^\$(P|H|wp|2[abxy]|argon2i?d?)\$/', $value ) ) {
			return '[redacted hash]';
		}
		// Hashes inside serialized data (e.g. token lists).
		$value = preg_replace( '/\$(?:P|H|wp\$2y|2[abxy])\$[A-Za-z0-9.\/$]{20,}/', '[redacted hash]', $value );
		// Password-reset keys in links.
		$value = preg_replace( '/([?&;]key=)[A-Za-z0-9]{8,}/', '$1[REDACTED]', $value );
		$value = preg_replace( '/(IPO-CODE: )[0-9]{6}/', '$1******', $value );
		// Secret-looking keys inside serialized arrays: s:N:"smtp_pass";s:M:"value";
		$value = preg_replace_callback(
			'/(s:\d+:"[^"]*(?:pass|secret|token|api_?key|apikey|private|client_secret)[^"]*";)s:\d+:"(.*?)";/i',
			function ( $m ) {
				return $m[1] . 's:3:"***";';
			},
			$value
		);
		// Same for JSON: "password":"value".
		$value = preg_replace( '/("[^"]*(?:pass|secret|token|api_?key|apikey|private)[^"]*"\s*:\s*)"[^"]*"/i', '$1"***"', $value );
		return $value;
	}
}
