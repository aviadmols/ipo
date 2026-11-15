<?php
/**
 * IPO Security — audit log storage.
 *
 * One row per event in {prefix}ipo_audit_log. Rows are never edited and there
 * is no UI to delete them; a daily cron drops rows older than RETENTION_DAYS.
 * Critical events also e-mail the addresses set on the security settings page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Audit_Log {

	const DB_VERSION        = '1';
	const DB_VERSION_OPTION = 'ipo_audit_log_db_version';
	const SETTINGS_OPTION   = 'ipo_security_settings';
	const RETENTION_DAYS    = 400;
	const CRON_HOOK         = 'ipo_audit_log_prune';

	/** Severity levels, lowest first. */
	const SEVERITIES = array( 'info', 'notice', 'warning', 'critical' );

	/** @var bool Guards against logging while we are logging. */
	protected static $writing = false;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ), 1 );
		add_action( self::CRON_HOOK, array( __CLASS__, 'prune' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ipo_audit_log';
	}

	public static function maybe_install() {
		if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
			return;
		}

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				created_at datetime NOT NULL,
				severity varchar(10) NOT NULL DEFAULT 'info',
				event varchar(64) NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				user_login varchar(60) NOT NULL DEFAULT '',
				user_role varchar(100) NOT NULL DEFAULT '',
				ip varchar(45) NOT NULL DEFAULT '',
				user_agent varchar(255) NOT NULL DEFAULT '',
				context varchar(20) NOT NULL DEFAULT '',
				object_type varchar(40) NOT NULL DEFAULT '',
				object_id varchar(64) NOT NULL DEFAULT '',
				object_label varchar(255) NOT NULL DEFAULT '',
				request varchar(255) NOT NULL DEFAULT '',
				details longtext NULL,
				PRIMARY KEY  (id),
				KEY created_at (created_at),
				KEY event (event),
				KEY user_id (user_id),
				KEY object (object_type, object_id),
				KEY ip (ip),
				KEY severity (severity)
			) {$charset};"
		);

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, true );
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	public static function prune() {
		global $wpdb;
		$table  = self::table();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - self::RETENTION_DAYS * DAY_IN_SECONDS );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s LIMIT 50000", $cutoff ) );
	}

	/* ------------------------------------------------------------------ */
	/* Request helpers                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Client IP. REMOTE_ADDR only: forwarded headers can be forged by the caller.
	 * (WordPress itself records REMOTE_ADDR in session tokens on this host and it
	 * holds the real client address.)
	 */
	public static function ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	public static function user_agent() {
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
		return substr( sanitize_text_field( $ua ), 0, 255 );
	}

	public static function context() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return 'cli';
		}
		if ( wp_doing_cron() ) {
			return 'cron';
		}
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return 'xmlrpc';
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return 'rest';
		}
		if ( wp_doing_ajax() ) {
			return 'ajax';
		}
		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return 'login';
		}
		if ( is_admin() ) {
			return 'admin';
		}
		return 'front';
	}

	public static function request_line() {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
		$uri    = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		// Never store secrets that may ride in the query string.
		$uri = preg_replace( '/((?:key|pass|pwd|password|token|ipo_ai_token|_wpnonce|code)=)[^&\s]*/i', '$1***', $uri );
		return substr( strtoupper( $method ) . ' ' . $uri, 0, 255 );
	}

	/* ------------------------------------------------------------------ */
	/* Writing                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Record one event.
	 *
	 * @param string $event Machine name, e.g. password_changed.
	 * @param array  $args  severity, object_type, object_id, object_label, details (array), user (WP_User|int) to override the actor.
	 */
	public static function log( $event, array $args = array() ) {
		if ( self::$writing ) {
			return;
		}
		self::$writing = true;

		try {
			global $wpdb;

			$severity = isset( $args['severity'] ) && in_array( $args['severity'], self::SEVERITIES, true ) ? $args['severity'] : 'info';

			$user = null;
			if ( isset( $args['user'] ) ) {
				$user = $args['user'] instanceof WP_User ? $args['user'] : get_userdata( (int) $args['user'] );
			} elseif ( function_exists( 'wp_get_current_user' ) && did_action( 'set_current_user' ) ) {
				$current = wp_get_current_user();
				$user    = $current && $current->ID ? $current : null;
			}

			$details = isset( $args['details'] ) && is_array( $args['details'] ) ? $args['details'] : array();
			if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$details['_x_forwarded_for'] = substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ), 0, 200 );
			}

			$row = array(
				'created_at'   => gmdate( 'Y-m-d H:i:s' ),
				'severity'     => $severity,
				'event'        => substr( sanitize_key( $event ), 0, 64 ),
				'user_id'      => $user ? (int) $user->ID : 0,
				'user_login'   => $user ? substr( $user->user_login, 0, 60 ) : '',
				'user_role'    => $user ? substr( implode( ',', (array) $user->roles ), 0, 100 ) : '',
				'ip'           => self::ip(),
				'user_agent'   => self::user_agent(),
				'context'      => self::context(),
				'object_type'  => isset( $args['object_type'] ) ? substr( (string) $args['object_type'], 0, 40 ) : '',
				'object_id'    => isset( $args['object_id'] ) ? substr( (string) $args['object_id'], 0, 64 ) : '',
				'object_label' => isset( $args['object_label'] ) ? substr( wp_strip_all_tags( (string) $args['object_label'] ), 0, 255 ) : '',
				'request'      => self::request_line(),
				'details'      => $details ? wp_json_encode( $details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR ) : null,
			);

			$suppress = $wpdb->suppress_errors( true );
			$wpdb->insert( self::table(), $row );
			$wpdb->suppress_errors( $suppress );

			if ( 'critical' === $severity ) {
				self::alert( $row, $details );
			}
		} catch ( \Throwable $e ) {
			// Logging must never break a request.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'IPO audit log: ' . $e->getMessage() ); // phpcs:ignore
			}
		}

		self::$writing = false;
	}

	/* ------------------------------------------------------------------ */
	/* Settings + alerts                                                   */
	/* ------------------------------------------------------------------ */

	public static function settings() {
		$defaults = array(
			'alert_emails' => get_option( 'admin_email' ),
			'two_factor'   => 0,
		);
		$saved = get_option( self::SETTINGS_OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	/** @return string[] */
	public static function alert_recipients() {
		$raw  = (string) self::settings()['alert_emails'];
		$list = array_filter( array_map( 'trim', preg_split( '/[\s,;]+/', $raw ) ), 'is_email' );
		return array_values( array_unique( $list ) );
	}

	/**
	 * E-mail a critical event. At most one mail per event+object per 10 minutes.
	 */
	protected static function alert( array $row, array $details ) {
		$to = self::alert_recipients();
		if ( ! $to ) {
			return;
		}

		$throttle = 'ipo_sec_alert_' . md5( $row['event'] . '|' . $row['object_id'] );
		if ( get_transient( $throttle ) ) {
			return;
		}
		set_transient( $throttle, 1, 10 * MINUTE_IN_SECONDS );

		$site    = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$labels  = IPO_Audit_Events::labels();
		$title   = isset( $labels[ $row['event'] ] ) ? $labels[ $row['event'] ] : $row['event'];
		$subject = sprintf( '[%s] התראת אבטחה: %s', $site, $title );

		$offset = (float) get_option( 'gmt_offset' );
		$local  = gmdate( 'Y-m-d H:i:s', strtotime( $row['created_at'] . ' UTC' ) + (int) ( $offset * HOUR_IN_SECONDS ) );

		unset( $details['_x_forwarded_for'] );
		$lines = array(
			'אירוע: ' . $title . ' (' . $row['event'] . ')',
			'זמן: ' . $local . ' (שעון האתר) / ' . $row['created_at'] . ' UTC',
			'מבצע: ' . ( $row['user_login'] ? $row['user_login'] . ' #' . $row['user_id'] : 'לא מחובר / מערכת' ),
			'יעד: ' . trim( $row['object_type'] . ' ' . $row['object_id'] . ' ' . $row['object_label'] ),
			'IP: ' . $row['ip'],
			'דפדפן: ' . $row['user_agent'],
			'הקשר: ' . $row['context'] . ' — ' . $row['request'],
			'',
			'פרטים:',
			wp_json_encode( $details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			'',
			'יומן הפעולות המלא: ' . admin_url( 'tools.php?page=' . IPO_Security_Admin::PAGE_SLUG ),
		);

		wp_mail( $to, $subject, implode( "\n", $lines ) );
	}
}
