<?php
/**
 * IPO Security — what goes into the audit log.
 *
 * Accounts (logins, failures, password changes/resets, roles, users), content
 * (posts of every type incl. ACF fields, trash/delete, media, terms, menus),
 * site configuration (options, plugins, themes, updates) and AI Bridge calls.
 *
 * Post saves are buffered per request and written once per post at shutdown,
 * so one "Update" click = one row listing every field that changed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Audit_Events {

	/** Post types never logged (noise, or logged another way). */
	const SKIP_POST_TYPES = array( 'revision', 'customize_changeset', 'oembed_cache', 'acf-field', 'nav_menu_item', 'wp_global_styles', 'user_request_log', 'scheduled-action' );

	/** Meta keys never logged. */
	const SKIP_META = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date', '_encloseme', '_pingme', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_trash_meta_comments_status', '_wp_desired_post_slug', '_wp_attachment_metadata', '_wp_attachment_backup_sizes', 'imagify_data', '_imagify_data', '_imagify_status', '_imagify_optimization_level', '_yoast_wpseo_estimated-reading-time-minutes', '_yoast_wpseo_wordproof_timestamp', '_last_editor_used_jetpack' );

	/** Options always logged, whoever changes them. Value = severity. */
	const CRITICAL_OPTIONS = array(
		'siteurl'                       => 'critical',
		'home'                          => 'critical',
		'admin_email'                   => 'critical',
		'new_admin_email'               => 'warning',
		'users_can_register'            => 'critical',
		'default_role'                  => 'critical',
		'active_plugins'                => 'warning',
		'template'                      => 'warning',
		'stylesheet'                    => 'warning',
		'whl_page'                      => 'critical',
		'wp_mail_smtp'                  => 'warning',
		'permalink_structure'           => 'warning',
		'blog_public'                   => 'warning',
		'ipo_ai_bridge_tokens'          => 'warning',
		'ipo_ai_bridge_write_tokens'    => 'critical',
		'ipo_ai_bridge_db_write_tokens' => 'critical',
		'ipo_security_settings'         => 'critical',
	);

	/** Options never logged (change on their own all the time). Regex. */
	const NOISE_OPTIONS = '/^(_transient_|_site_transient_|cron$|rewrite_rules$|ipo_audit_|ipo_ai_bridge_db_change|action_scheduler|recently_edited|recovery_|wpseo_.*(tracking|indexable|ryte|last)|rocket_|imagify_|_wpml_|wpml_.*(cache|notice|tm_)|icl_.*(cache|notices|translation_jobs)|wp_mail_smtp_(debug|notifications|review|migration|activated)|googlesitekit_.*(cache|transient)|ac_|acp_|pum_|popmake_|gtmkit_.*notif|fb_|facebook_|tt4b_|userway|health-check|wp_sweep|can_compress_scripts|auto_updater|auto_core_update|core_updater|db_upgraded|finished_|theme_mods_.*_last|sm_|_ipo_cache|ipo_page_cache|ipo_.*_last_run|.*_lock$|.*_last_(run|check|sync|update)d?$|.*_version$)/i';

	/** @var array<int,array> post_id => pending changes */
	protected static $posts = array();

	/** @var array<int,bool> users whose password change is already logged this request */
	protected static $pass_logged = array();

	/** @var array<int,WP_User> users about to be deleted */
	protected static $deleting = array();

	public static function init() {
		// Accounts.
		add_action( 'wp_login', array( __CLASS__, 'on_login' ), 10, 2 );
		add_action( 'wp_login_failed', array( __CLASS__, 'on_login_failed' ), 10, 2 );
		add_action( 'wp_logout', array( __CLASS__, 'on_logout' ) );
		add_action( 'lostpassword_post', array( __CLASS__, 'on_lostpassword_post' ), 99, 2 );
		add_action( 'retrieve_password_key', array( __CLASS__, 'on_reset_key_created' ), 10, 2 );
		add_action( 'after_password_reset', array( __CLASS__, 'on_after_password_reset' ), 10, 1 );
		add_action( 'wp_set_password', array( __CLASS__, 'on_wp_set_password' ), 10, 2 );
		add_action( 'profile_update', array( __CLASS__, 'on_profile_update' ), 10, 2 );
		add_action( 'user_register', array( __CLASS__, 'on_user_register' ) );
		add_action( 'delete_user', array( __CLASS__, 'on_before_delete_user' ), 10, 2 );
		add_action( 'deleted_user', array( __CLASS__, 'on_deleted_user' ), 10, 2 );
		add_action( 'set_user_role', array( __CLASS__, 'on_set_user_role' ), 10, 3 );
		add_action( 'add_user_role', array( __CLASS__, 'on_add_user_role' ), 10, 2 );
		add_action( 'remove_user_role', array( __CLASS__, 'on_remove_user_role' ), 10, 2 );
		add_action( 'updated_user_meta', array( __CLASS__, 'on_user_meta' ), 10, 4 );
		add_action( 'added_user_meta', array( __CLASS__, 'on_user_meta' ), 10, 4 );
		add_action( 'wp_create_application_password', array( __CLASS__, 'on_app_password_created' ), 10, 2 );
		add_action( 'wp_delete_application_password', array( __CLASS__, 'on_app_password_deleted' ), 10, 2 );

		// Content.
		add_action( 'save_post', array( __CLASS__, 'on_save_post' ), 10, 3 );
		add_action( 'post_updated', array( __CLASS__, 'on_post_updated' ), 10, 3 );
		add_action( 'update_post_meta', array( __CLASS__, 'on_update_post_meta' ), 10, 4 );
		add_action( 'added_post_meta', array( __CLASS__, 'on_added_post_meta' ), 10, 4 );
		add_action( 'delete_post_meta', array( __CLASS__, 'on_delete_post_meta' ), 10, 4 );
		add_action( 'wp_trash_post', array( __CLASS__, 'on_trash_post' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'on_untrash_post' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'on_delete_post' ), 10, 2 );
		add_action( 'add_attachment', array( __CLASS__, 'on_add_attachment' ) );
		add_action( 'created_term', array( __CLASS__, 'on_term_created' ), 10, 3 );
		add_action( 'edited_term', array( __CLASS__, 'on_term_edited' ), 10, 3 );
		add_action( 'pre_delete_term', array( __CLASS__, 'on_term_deleted' ), 10, 2 );
		add_action( 'wp_update_nav_menu', array( __CLASS__, 'on_menu_updated' ), 10, 1 );
		add_action( 'wp_delete_nav_menu', array( __CLASS__, 'on_menu_deleted' ), 10, 1 );

		// Site configuration.
		add_action( 'updated_option', array( __CLASS__, 'on_updated_option' ), 10, 3 );
		add_action( 'added_option', array( __CLASS__, 'on_added_option' ), 10, 2 );
		add_action( 'deleted_option', array( __CLASS__, 'on_deleted_option' ), 10, 1 );
		add_action( 'activated_plugin', array( __CLASS__, 'on_plugin_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'on_plugin_deactivated' ), 10, 2 );
		add_action( 'deleted_plugin', array( __CLASS__, 'on_plugin_deleted' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'on_upgrader' ), 10, 2 );
		add_action( 'switch_theme', array( __CLASS__, 'on_switch_theme' ), 10, 3 );
		add_action( 'customize_save_after', array( __CLASS__, 'on_customizer_saved' ) );
		add_action( 'export_wp', array( __CLASS__, 'on_export' ) );
		add_action( 'gform_after_save_form', array( __CLASS__, 'on_gform_saved' ), 10, 2 );

		// AI Bridge (REST namespace ipo-ai/v1) — every call, incl. rejected tokens.
		add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'on_rest_after' ), 10, 3 );

		add_action( 'shutdown', array( __CLASS__, 'flush_posts' ), 1 );
	}

	/** Human labels (Hebrew) for the log screen and alert e-mails. */
	public static function labels() {
		return array(
			'login_success'            => 'התחברות',
			'login_failed'             => 'ניסיון התחברות שנכשל',
			'login_blocked'            => 'התחברות נחסמה (נעילה)',
			'logout'                   => 'התנתקות',
			'lockout'                  => 'כתובת IP ננעלה',
			'password_reset_requested' => 'בקשת איפוס סיסמה',
			'password_reset_blocked'   => 'בקשת איפוס סיסמה נחסמה',
			'password_reset_completed' => 'סיסמה אופסה דרך קישור',
			'password_changed'         => 'סיסמה שונתה',
			'email_changed'            => 'כתובת מייל של משתמש שונתה',
			'user_updated'             => 'פרטי משתמש עודכנו',
			'user_created'             => 'משתמש נוצר',
			'user_deleted'             => 'משתמש נמחק',
			'user_role_changed'        => 'תפקיד משתמש שונה',
			'user_caps_changed'        => 'הרשאות משתמש שונו ישירות',
			'app_password_created'     => 'נוצרה סיסמת אפליקציה',
			'app_password_deleted'     => 'נמחקה סיסמת אפליקציה',
			'2fa_code_sent'            => 'קוד אימות נשלח',
			'2fa_success'              => 'אימות דו-שלבי הצליח',
			'2fa_failed'               => 'קוד אימות שגוי',
			'2fa_mail_failed'          => 'שליחת קוד אימות נכשלה',
			'post_created'             => 'תוכן נוצר',
			'post_updated'             => 'תוכן עודכן',
			'post_trashed'             => 'תוכן הועבר לפח',
			'post_restored'            => 'תוכן שוחזר מהפח',
			'post_deleted'             => 'תוכן נמחק לצמיתות',
			'media_uploaded'           => 'קובץ הועלה',
			'term_created'             => 'מונח נוצר',
			'term_updated'             => 'מונח עודכן',
			'term_deleted'             => 'מונח נמחק',
			'menu_updated'             => 'תפריט עודכן',
			'menu_deleted'             => 'תפריט נמחק',
			'option_updated'           => 'הגדרה שונתה',
			'option_added'             => 'הגדרה נוספה',
			'option_deleted'           => 'הגדרה נמחקה',
			'plugin_activated'         => 'תוסף הופעל',
			'plugin_deactivated'       => 'תוסף כובה',
			'plugin_deleted'           => 'תוסף נמחק',
			'software_updated'         => 'עדכון/התקנת תוכנה',
			'theme_switched'           => 'ערכת עיצוב הוחלפה',
			'customizer_saved'         => 'התאמה אישית נשמרה',
			'export_downloaded'        => 'יצוא תוכן הורד',
			'form_saved'               => 'טופס Gravity נשמר',
			'ai_bridge_call'           => 'קריאת AI Bridge',
			'ai_bridge_write'          => 'שינוי דרך AI Bridge',
			'ai_bridge_denied'         => 'קריאת AI Bridge נדחתה',
			'xmlrpc_blocked'           => 'XML-RPC נחסם',
		);
	}

	/* ================================================================== */
	/* Helpers                                                             */
	/* ================================================================== */

	protected static function short( $value, $max = 300 ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR );
		}
		$value = (string) $value;
		if ( function_exists( 'mb_strlen' ) && mb_strlen( $value ) > $max ) {
			return mb_substr( $value, 0, $max ) . '… (' . mb_strlen( $value ) . ' chars)';
		}
		return strlen( $value ) > $max ? substr( $value, 0, $max ) . '…' : $value;
	}

	protected static function is_secret_name( $name ) {
		return (bool) preg_match( '/(pass|secret|token|api_?key|apikey|licen[cs]e|private|salt|smtp|auth|credential)/i', (string) $name );
	}

	protected static function user_label( $user ) {
		$user = $user instanceof WP_User ? $user : get_userdata( (int) $user );
		return $user ? $user->user_login . ' (' . $user->user_email . ')' : '';
	}

	protected static function actor_id() {
		return function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
	}

	/* ================================================================== */
	/* Accounts                                                            */
	/* ================================================================== */

	public static function on_login( $login, $user ) {
		IPO_Audit_Log::log(
			'login_success',
			array(
				'user'         => $user,
				'object_type'  => 'user',
				'object_id'    => $user->ID,
				'object_label' => self::user_label( $user ),
			)
		);
	}

	public static function on_login_failed( $username, $error = null ) {
		$user  = get_user_by( 'login', $username );
		$user  = $user ? $user : ( is_email( $username ) ? get_user_by( 'email', $username ) : false );
		$codes = is_wp_error( $error ) ? $error->get_error_codes() : array();
		if ( in_array( 'ipo_locked', $codes, true ) ) {
			return; // Logged once per lock as login_blocked.
		}
		$is_admin_target = $user && user_can( $user, 'manage_options' );

		IPO_Audit_Log::log(
			'login_failed',
			array(
				'severity'     => $is_admin_target ? 'warning' : 'notice',
				'object_type'  => 'user',
				'object_id'    => $user ? $user->ID : '',
				'object_label' => $user ? self::user_label( $user ) : 'לא קיים: ' . $username,
				'details'      => array(
					'username_tried' => self::short( $username, 60 ),
					'user_exists'    => (bool) $user,
					'reason'         => $codes,
				),
			)
		);
	}

	public static function on_logout( $user_id = 0 ) {
		$user_id = $user_id ? $user_id : self::actor_id();
		if ( ! $user_id ) {
			return;
		}
		IPO_Audit_Log::log(
			'logout',
			array(
				'user'         => $user_id,
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
			)
		);
	}

	/**
	 * Every "lost password" submission, including ones for users that do not
	 * exist (shows someone probing), with the outcome.
	 *
	 * @param WP_Error      $errors    Errors so far.
	 * @param WP_User|false $user_data User the request resolved to.
	 */
	public static function on_lostpassword_post( $errors, $user_data = false ) {
		$tried = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$codes = is_wp_error( $errors ) ? $errors->get_error_codes() : array();

		IPO_Audit_Log::log(
			$codes ? 'password_reset_blocked' : 'password_reset_requested',
			array(
				'severity'     => $user_data && user_can( $user_data, 'manage_options' ) ? 'warning' : 'notice',
				'object_type'  => 'user',
				'object_id'    => $user_data ? $user_data->ID : '',
				'object_label' => $user_data ? self::user_label( $user_data ) : 'לא קיים: ' . $tried,
				'details'      => array(
					'value_entered' => self::short( $tried, 100 ),
					'user_found'    => (bool) $user_data,
					'errors'        => $codes,
				),
			)
		);
	}

	/** A reset key was generated (by the lost-password form or by an admin "send reset link"). */
	public static function on_reset_key_created( $user_login, $key = '' ) {
		$user = get_user_by( 'login', $user_login );
		if ( ! $user ) {
			return;
		}
		// The lost-password form path is already logged by on_lostpassword_post.
		if ( did_action( 'lostpassword_post' ) ) {
			return;
		}
		IPO_Audit_Log::log(
			'password_reset_requested',
			array(
				'severity'     => user_can( $user, 'manage_options' ) ? 'warning' : 'notice',
				'object_type'  => 'user',
				'object_id'    => $user->ID,
				'object_label' => self::user_label( $user ),
				'details'      => array( 'via' => 'reset key generated outside the lost-password form (e.g. admin "Send password reset")' ),
			)
		);
	}

	public static function on_after_password_reset( $user ) {
		self::$pass_logged[ $user->ID ] = true;
		IPO_Audit_Log::log(
			'password_reset_completed',
			array(
				'severity'     => user_can( $user, 'manage_options' ) ? 'critical' : 'warning',
				'user'         => $user,
				'object_type'  => 'user',
				'object_id'    => $user->ID,
				'object_label' => self::user_label( $user ),
				'details'      => array( 'method' => 'reset link from e-mail' ),
			)
		);
	}

	/** wp_set_password() writes the hash directly (no profile_update), so catch it here. */
	public static function on_wp_set_password( $password, $user_id = 0 ) {
		$user_id = (int) $user_id;
		if ( ! $user_id ) {
			return;
		}
		// The reset flow fires after_password_reset after this; let that one log it.
		if ( did_action( 'validate_password_reset' ) || ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'rp', 'resetpass' ), true ) ) ) { // phpcs:ignore
			return;
		}
		self::log_password_change( $user_id, 'wp_set_password()' );
	}

	protected static function log_password_change( $user_id, $method ) {
		if ( ! empty( self::$pass_logged[ $user_id ] ) ) {
			return;
		}
		self::$pass_logged[ $user_id ] = true;

		$target   = get_userdata( $user_id );
		$actor    = self::actor_id();
		$by_other = $actor !== (int) $user_id;
		$admin    = $target && user_can( $target, 'manage_options' );

		IPO_Audit_Log::log(
			'password_changed',
			array(
				'severity'     => ( $admin && ( $by_other || ! $actor ) ) ? 'critical' : ( $admin ? 'warning' : 'notice' ),
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $target ),
				'details'      => array(
					'method'         => $method,
					'changed_by'     => $actor ? ( $by_other ? 'another user' : 'the user themself' ) : 'no logged-in user (code / cron / API)',
					'changed_by_id'  => $actor,
				),
			)
		);
	}

	/**
	 * @param int     $user_id  User.
	 * @param WP_User $old_user User before the update.
	 */
	public static function on_profile_update( $user_id, $old_user = null ) {
		global $wpdb;
		if ( ! $old_user instanceof WP_User ) {
			return;
		}

		$fresh = $wpdb->get_row( $wpdb->prepare( "SELECT user_login, user_pass, user_email, user_url, display_name, user_nicename FROM {$wpdb->users} WHERE ID = %d", $user_id ) );
		if ( ! $fresh ) {
			return;
		}

		if ( $fresh->user_pass !== $old_user->user_pass ) {
			self::log_password_change( $user_id, 'profile / user edit screen / REST / plugin (wp_update_user)' );
		}

		if ( $fresh->user_email !== $old_user->user_email ) {
			IPO_Audit_Log::log(
				'email_changed',
				array(
					'severity'     => user_can( $user_id, 'manage_options' ) ? 'critical' : 'warning',
					'object_type'  => 'user',
					'object_id'    => $user_id,
					'object_label' => $fresh->user_login,
					'details'      => array(
						'from' => $old_user->user_email,
						'to'   => $fresh->user_email,
					),
				)
			);
		}

		$changed = array();
		foreach ( array( 'user_login', 'user_url', 'display_name', 'user_nicename' ) as $field ) {
			if ( (string) $fresh->$field !== (string) $old_user->$field ) {
				$changed[ $field ] = array( 'from' => $old_user->$field, 'to' => $fresh->$field );
			}
		}
		if ( $changed ) {
			IPO_Audit_Log::log(
				'user_updated',
				array(
					'severity'     => isset( $changed['user_login'] ) ? 'warning' : 'info',
					'object_type'  => 'user',
					'object_id'    => $user_id,
					'object_label' => $fresh->user_login,
					'details'      => $changed,
				)
			);
		}
	}

	public static function on_user_register( $user_id ) {
		$user  = get_userdata( $user_id );
		$admin = $user && in_array( 'administrator', (array) $user->roles, true );
		IPO_Audit_Log::log(
			'user_created',
			array(
				'severity'     => $admin ? 'critical' : 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user ),
				'details'      => array( 'roles' => $user ? array_values( (array) $user->roles ) : array() ),
			)
		);
	}

	public static function on_before_delete_user( $user_id, $reassign = null ) {
		$user = get_userdata( $user_id );
		if ( $user ) {
			self::$deleting[ $user_id ] = $user;
		}
	}

	public static function on_deleted_user( $user_id, $reassign = null ) {
		$user = isset( self::$deleting[ $user_id ] ) ? self::$deleting[ $user_id ] : null;
		IPO_Audit_Log::log(
			'user_deleted',
			array(
				'severity'     => $user && in_array( 'administrator', (array) $user->roles, true ) ? 'critical' : 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => $user ? self::user_label( $user ) : '',
				'details'      => array(
					'roles'          => $user ? array_values( (array) $user->roles ) : array(),
					'content_moved_to' => $reassign,
				),
			)
		);
	}

	public static function on_set_user_role( $user_id, $role, $old_roles = array() ) {
		// user_register already logs the initial role of a brand-new user.
		if ( doing_action( 'user_register' ) || ( empty( $old_roles ) && self::is_new_user( $user_id ) ) ) {
			return;
		}
		IPO_Audit_Log::log(
			'user_role_changed',
			array(
				'severity'     => ( 'administrator' === $role || in_array( 'administrator', (array) $old_roles, true ) ) ? 'critical' : 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
				'details'      => array(
					'from' => array_values( (array) $old_roles ),
					'to'   => $role,
				),
			)
		);
	}

	protected static function is_new_user( $user_id ) {
		$user = get_userdata( $user_id );
		return $user && strtotime( $user->user_registered . ' UTC' ) > time() - 120;
	}

	public static function on_add_user_role( $user_id, $role ) {
		if ( doing_action( 'set_user_role' ) || doing_action( 'user_register' ) || self::is_new_user( $user_id ) ) {
			return;
		}
		IPO_Audit_Log::log(
			'user_role_changed',
			array(
				'severity'     => 'administrator' === $role ? 'critical' : 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
				'details'      => array( 'added_role' => $role ),
			)
		);
	}

	public static function on_remove_user_role( $user_id, $role ) {
		if ( doing_action( 'set_user_role' ) ) {
			return;
		}
		IPO_Audit_Log::log(
			'user_role_changed',
			array(
				'severity'     => 'administrator' === $role ? 'critical' : 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
				'details'      => array( 'removed_role' => $role ),
			)
		);
	}

	/** Capabilities written straight into usermeta (bypassing the role API) — a classic backdoor. */
	public static function on_user_meta( $meta_id, $user_id, $meta_key, $value ) {
		global $wpdb;
		if ( $meta_key !== $wpdb->prefix . 'capabilities' ) {
			return;
		}
		if ( doing_action( 'set_user_role' ) || doing_action( 'add_user_role' ) || doing_action( 'remove_user_role' ) || doing_action( 'user_register' ) ) {
			return;
		}
		// WP_User::set_role / add_role go through update_user_meta inside those actions'
		// callers; only log writes that did not come through the role API.
		$trace = function_exists( 'wp_debug_backtrace_summary' ) ? wp_debug_backtrace_summary( null, 0, false ) : array();
		foreach ( (array) $trace as $frame ) {
			if ( false !== strpos( $frame, 'WP_User->' ) ) {
				return;
			}
		}
		$value = maybe_unserialize( $value );
		IPO_Audit_Log::log(
			'user_caps_changed',
			array(
				'severity'     => ( is_array( $value ) && ! empty( $value['administrator'] ) ) ? 'critical' : 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
				'details'      => array(
					'capabilities' => is_array( $value ) ? array_keys( array_filter( $value ) ) : $value,
					'call_stack'   => array_slice( (array) $trace, 0, 12 ),
				),
			)
		);
	}

	public static function on_app_password_created( $user_id, $item = array() ) {
		IPO_Audit_Log::log(
			'app_password_created',
			array(
				'severity'     => 'critical',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
				'details'      => array( 'name' => isset( $item['name'] ) ? $item['name'] : '' ),
			)
		);
	}

	public static function on_app_password_deleted( $user_id, $item = array() ) {
		IPO_Audit_Log::log(
			'app_password_deleted',
			array(
				'severity'     => 'warning',
				'object_type'  => 'user',
				'object_id'    => $user_id,
				'object_label' => self::user_label( $user_id ),
				'details'      => array( 'name' => isset( $item['name'] ) ? $item['name'] : '' ),
			)
		);
	}

	/* ================================================================== */
	/* Content                                                             */
	/* ================================================================== */

	protected static function track_post( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return false;
		}
		$type = get_post_type( $post_id );
		if ( ! $type || in_array( $type, self::SKIP_POST_TYPES, true ) || false !== strpos( $type, 'log' ) ) {
			return false;
		}
		if ( 'auto-draft' === get_post_status( $post_id ) ) {
			return false;
		}
		if ( ! isset( self::$posts[ $post_id ] ) ) {
			if ( count( self::$posts ) >= 400 ) {
				return false; // Huge batch (importer): cap memory, the first 400 posts are enough to trace it.
			}
			self::$posts[ $post_id ] = array(
				'created' => false,
				'fields'  => array(),
				'meta'    => array(),
				'more'    => 0,
			);
		}
		return true;
	}

	public static function on_save_post( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}
		if ( ! self::track_post( $post_id ) ) {
			return;
		}
		if ( ! $update ) {
			self::$posts[ $post_id ]['created'] = true;
		}
	}

	/**
	 * @param int     $post_id Post.
	 * @param WP_Post $after   After.
	 * @param WP_Post $before  Before.
	 */
	public static function on_post_updated( $post_id, $after, $before ) {
		// Trashing is logged by on_trash_post; the status flip that follows is the same action.
		if ( 'trash' === $after->post_status && 'trash' !== $before->post_status ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! self::track_post( $post_id ) ) {
			return;
		}
		if ( 'auto-draft' === $before->post_status ) {
			self::$posts[ $post_id ]['created'] = true;
		}
		$fields = array(
			'post_title'    => 300,
			'post_status'   => 50,
			'post_name'     => 200,
			'post_excerpt'  => 300,
			'post_author'   => 20,
			'post_date'     => 30,
			'post_parent'   => 20,
			'menu_order'    => 20,
			'post_password' => 0,
			'comment_status'=> 20,
		);
		foreach ( $fields as $field => $max ) {
			if ( (string) $before->$field !== (string) $after->$field ) {
				self::$posts[ $post_id ]['fields'][ $field ] = $max
					? array( 'from' => self::short( $before->$field, $max ), 'to' => self::short( $after->$field, $max ) )
					: 'changed';
			}
		}
		if ( $before->post_content !== $after->post_content ) {
			self::$posts[ $post_id ]['fields']['post_content'] = array(
				'from_length' => strlen( $before->post_content ),
				'to_length'   => strlen( $after->post_content ),
				'note'        => 'Full previous text is in the WordPress revisions of this post.',
			);
		}
	}

	protected static function skip_meta( $meta_key, $value = null ) {
		if ( in_array( $meta_key, self::SKIP_META, true ) ) {
			return true;
		}
		// ACF reference keys: "_fieldname" => "field_abc123".
		if ( '_' === substr( $meta_key, 0, 1 ) && is_string( $value ) && 0 === strpos( $value, 'field_' ) ) {
			return true;
		}
		return false;
	}

	protected static function add_meta_change( $post_id, $meta_key, $from, $to ) {
		if ( ! self::track_post( $post_id ) ) {
			return;
		}
		$meta = &self::$posts[ $post_id ]['meta'];
		if ( isset( $meta[ $meta_key ] ) ) {
			$meta[ $meta_key ]['to'] = self::short( $to );
			return;
		}
		if ( count( $meta ) >= 80 ) {
			self::$posts[ $post_id ]['more']++;
			return;
		}
		$meta[ $meta_key ] = array(
			'from' => null === $from ? null : self::short( $from ),
			'to'   => null === $to ? null : self::short( $to ),
		);
	}

	public static function on_update_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( self::skip_meta( $meta_key, $meta_value ) ) {
			return;
		}
		$old = get_metadata_by_mid( 'post', $meta_id );
		$old = $old ? $old->meta_value : null;
		if ( maybe_serialize( $old ) === maybe_serialize( $meta_value ) ) {
			return;
		}
		self::add_meta_change( $post_id, $meta_key, $old, $meta_value );
	}

	public static function on_added_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( self::skip_meta( $meta_key, $meta_value ) || '' === $meta_value || null === $meta_value ) {
			return;
		}
		// Brand-new posts: only the post row matters, not every default field.
		if ( isset( self::$posts[ $post_id ] ) && self::$posts[ $post_id ]['created'] ) {
			return;
		}
		self::add_meta_change( $post_id, $meta_key, null, $meta_value );
	}

	public static function on_delete_post_meta( $meta_ids, $post_id, $meta_key, $meta_value ) {
		if ( doing_action( 'before_delete_post' ) || doing_action( 'delete_post' ) || self::skip_meta( $meta_key, $meta_value ) ) {
			return;
		}
		$old = get_post_meta( $post_id, $meta_key, true );
		self::add_meta_change( $post_id, $meta_key, $old, null );
	}

	public static function flush_posts() {
		foreach ( self::$posts as $post_id => $change ) {
			if ( ! $change['created'] && ! $change['fields'] && ! $change['meta'] ) {
				continue;
			}
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}
			$details = array( 'post_type' => $post->post_type, 'status' => $post->post_status );
			if ( $change['fields'] ) {
				$details['fields'] = $change['fields'];
			}
			if ( $change['meta'] ) {
				$details['meta'] = $change['meta'];
			}
			if ( $change['more'] ) {
				$details['more_meta_changes_not_listed'] = $change['more'];
			}
			$details['edit_link'] = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

			$severity = 'info';
			if ( in_array( $post->post_type, array( 'custom-css-js', 'acf-field-group', 'popup' ), true ) ) {
				$severity = 'warning'; // Code / structure that runs on every page.
			}

			IPO_Audit_Log::log(
				$change['created'] ? 'post_created' : 'post_updated',
				array(
					'severity'     => $severity,
					'object_type'  => $post->post_type,
					'object_id'    => $post_id,
					'object_label' => $post->post_title,
					'details'      => $details,
				)
			);
		}
		self::$posts = array();
	}

	protected static function post_event( $event, $post_id, $severity = 'notice', $extra = array() ) {
		$post = get_post( $post_id );
		if ( ! $post || in_array( $post->post_type, self::SKIP_POST_TYPES, true ) || 'auto-draft' === $post->post_status ) {
			return;
		}
		unset( self::$posts[ $post_id ] );
		IPO_Audit_Log::log(
			$event,
			array(
				'severity'     => $severity,
				'object_type'  => $post->post_type,
				'object_id'    => $post_id,
				'object_label' => $post->post_title,
				'details'      => array_merge( array( 'status_before' => $post->post_status ), $extra ),
			)
		);
	}

	public static function on_trash_post( $post_id ) {
		self::post_event( 'post_trashed', $post_id, 'notice' );
	}

	public static function on_untrash_post( $post_id ) {
		self::post_event( 'post_restored', $post_id, 'notice' );
	}

	public static function on_delete_post( $post_id, $post = null ) {
		$post = $post ? $post : get_post( $post_id );
		if ( $post && 'attachment' === $post->post_type ) {
			self::post_event( 'post_deleted', $post_id, 'notice', array( 'file' => get_attached_file( $post_id ) ) );
			return;
		}
		self::post_event( 'post_deleted', $post_id, 'warning' );
	}

	public static function on_add_attachment( $post_id ) {
		unset( self::$posts[ $post_id ] );
		IPO_Audit_Log::log(
			'media_uploaded',
			array(
				'object_type'  => 'attachment',
				'object_id'    => $post_id,
				'object_label' => get_the_title( $post_id ),
				'details'      => array(
					'file' => wp_basename( (string) get_attached_file( $post_id ) ),
					'mime' => get_post_mime_type( $post_id ),
				),
			)
		);
	}

	public static function on_term_created( $term_id, $tt_id, $taxonomy ) {
		if ( 'nav_menu' === $taxonomy ) {
			return;
		}
		$term = get_term( $term_id, $taxonomy );
		IPO_Audit_Log::log( 'term_created', array( 'object_type' => $taxonomy, 'object_id' => $term_id, 'object_label' => $term && ! is_wp_error( $term ) ? $term->name : '' ) );
	}

	public static function on_term_edited( $term_id, $tt_id, $taxonomy ) {
		if ( 'nav_menu' === $taxonomy ) {
			return;
		}
		$term = get_term( $term_id, $taxonomy );
		IPO_Audit_Log::log( 'term_updated', array( 'object_type' => $taxonomy, 'object_id' => $term_id, 'object_label' => $term && ! is_wp_error( $term ) ? $term->name : '' ) );
	}

	public static function on_term_deleted( $term_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );
		IPO_Audit_Log::log( 'term_deleted', array( 'severity' => 'notice', 'object_type' => $taxonomy, 'object_id' => $term_id, 'object_label' => $term && ! is_wp_error( $term ) ? $term->name : '' ) );
	}

	public static function on_menu_updated( $menu_id ) {
		static $done = array();
		if ( isset( $done[ $menu_id ] ) ) {
			return;
		}
		$done[ $menu_id ] = true;
		$menu = wp_get_nav_menu_object( $menu_id );
		IPO_Audit_Log::log( 'menu_updated', array( 'object_type' => 'nav_menu', 'object_id' => $menu_id, 'object_label' => $menu ? $menu->name : '' ) );
	}

	public static function on_menu_deleted( $menu_id ) {
		IPO_Audit_Log::log( 'menu_deleted', array( 'severity' => 'warning', 'object_type' => 'nav_menu', 'object_id' => $menu_id ) );
	}

	/* ================================================================== */
	/* Site configuration                                                  */
	/* ================================================================== */

	protected static function option_severity( $option ) {
		if ( isset( self::CRITICAL_OPTIONS[ $option ] ) ) {
			return self::CRITICAL_OPTIONS[ $option ];
		}
		global $wpdb;
		if ( $option === $wpdb->prefix . 'user_roles' ) {
			return 'critical';
		}
		if ( preg_match( self::NOISE_OPTIONS, $option ) ) {
			return null;
		}
		// Other options only when a person saved a setting: logged in, a POST, not the heartbeat.
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : '';
		if ( ! self::actor_id() || 'POST' !== $method || ( isset( $_POST['action'] ) && 'heartbeat' === $_POST['action'] ) ) { // phpcs:ignore
			return null;
		}
		return in_array( IPO_Audit_Log::context(), array( 'admin', 'ajax', 'rest' ), true ) ? 'notice' : null;
	}

	protected static function option_values( $option, $old, $new ) {
		if ( self::is_secret_name( $option ) ) {
			return array( 'value' => 'changed (not stored: may contain a secret)' );
		}
		if ( 'active_plugins' === $option && is_array( $old ) && is_array( $new ) ) {
			return array(
				'added'   => array_values( array_diff( $new, $old ) ),
				'removed' => array_values( array_diff( $old, $new ) ),
			);
		}
		if ( in_array( $option, array( 'ipo_ai_bridge_tokens' ), true ) && is_array( $old ) && is_array( $new ) ) {
			$labels = function ( $list ) {
				return array_map(
					function ( $t ) {
						return ( isset( $t['label'] ) ? $t['label'] : '' ) . ' ' . ( isset( $t['prefix'] ) ? $t['prefix'] : '' );
					},
					$list
				);
			};
			$o = $labels( $old );
			$n = $labels( $new );
			if ( $o === $n ) {
				return null; // Only last_used moved.
			}
			return array( 'added' => array_values( array_diff( $n, $o ) ), 'removed' => array_values( array_diff( $o, $n ) ) );
		}
		return array(
			'from' => self::short( $old, 600 ),
			'to'   => self::short( $new, 600 ),
		);
	}

	public static function on_updated_option( $option, $old, $new ) {
		$severity = self::option_severity( $option );
		if ( ! $severity ) {
			return;
		}
		$values = self::option_values( $option, $old, $new );
		if ( null === $values ) {
			return;
		}
		IPO_Audit_Log::log( 'option_updated', array( 'severity' => $severity, 'object_type' => 'option', 'object_id' => $option, 'object_label' => $option, 'details' => $values ) );
	}

	public static function on_added_option( $option, $value ) {
		$severity = self::option_severity( $option );
		if ( ! $severity ) {
			return;
		}
		IPO_Audit_Log::log( 'option_added', array( 'severity' => $severity, 'object_type' => 'option', 'object_id' => $option, 'object_label' => $option, 'details' => self::is_secret_name( $option ) ? array() : array( 'value' => self::short( $value, 600 ) ) ) );
	}

	public static function on_deleted_option( $option ) {
		$severity = self::option_severity( $option );
		if ( ! $severity ) {
			return;
		}
		IPO_Audit_Log::log( 'option_deleted', array( 'severity' => $severity, 'object_type' => 'option', 'object_id' => $option, 'object_label' => $option ) );
	}

	public static function on_plugin_activated( $plugin, $network = false ) {
		IPO_Audit_Log::log( 'plugin_activated', array( 'severity' => 'critical', 'object_type' => 'plugin', 'object_id' => $plugin, 'object_label' => $plugin ) );
	}

	public static function on_plugin_deactivated( $plugin, $network = false ) {
		IPO_Audit_Log::log( 'plugin_deactivated', array( 'severity' => 'warning', 'object_type' => 'plugin', 'object_id' => $plugin, 'object_label' => $plugin ) );
	}

	public static function on_plugin_deleted( $plugin, $deleted = true ) {
		IPO_Audit_Log::log( 'plugin_deleted', array( 'severity' => 'warning', 'object_type' => 'plugin', 'object_id' => $plugin, 'object_label' => $plugin, 'details' => array( 'success' => (bool) $deleted ) ) );
	}

	public static function on_upgrader( $upgrader, $extra ) {
		$extra = is_array( $extra ) ? $extra : array();
		$items = array();
		foreach ( array( 'plugins', 'themes' ) as $k ) {
			if ( ! empty( $extra[ $k ] ) ) {
				$items = array_merge( $items, (array) $extra[ $k ] );
			}
		}
		if ( ! empty( $extra['plugin'] ) ) {
			$items[] = $extra['plugin'];
		}
		if ( ! empty( $extra['theme'] ) ) {
			$items[] = $extra['theme'];
		}
		$installing = isset( $extra['action'] ) && 'install' === $extra['action'];
		IPO_Audit_Log::log(
			'software_updated',
			array(
				'severity'     => $installing ? 'critical' : 'notice',
				'object_type'  => isset( $extra['type'] ) ? $extra['type'] : '',
				'object_label' => implode( ', ', $items ),
				'details'      => array(
					'action' => isset( $extra['action'] ) ? $extra['action'] : '',
					'type'   => isset( $extra['type'] ) ? $extra['type'] : '',
					'items'  => $items,
				),
			)
		);
	}

	public static function on_switch_theme( $new_name, $new_theme = null, $old_theme = null ) {
		IPO_Audit_Log::log( 'theme_switched', array( 'severity' => 'critical', 'object_type' => 'theme', 'object_label' => $new_name, 'details' => array( 'from' => $old_theme ? $old_theme->get( 'Name' ) : '' ) ) );
	}

	public static function on_customizer_saved() {
		IPO_Audit_Log::log( 'customizer_saved', array( 'severity' => 'notice', 'object_type' => 'customizer' ) );
	}

	public static function on_export( $args = array() ) {
		IPO_Audit_Log::log( 'export_downloaded', array( 'severity' => 'warning', 'object_type' => 'export', 'details' => is_array( $args ) ? $args : array() ) );
	}

	public static function on_gform_saved( $form, $is_new ) {
		IPO_Audit_Log::log( 'form_saved', array( 'severity' => 'notice', 'object_type' => 'gravity_form', 'object_id' => isset( $form['id'] ) ? $form['id'] : '', 'object_label' => isset( $form['title'] ) ? $form['title'] : '', 'details' => array( 'new' => (bool) $is_new ) ) );
	}

	/* ================================================================== */
	/* AI Bridge                                                           */
	/* ================================================================== */

	/**
	 * @param mixed           $response Response or WP_Error.
	 * @param array           $handler  Route handler.
	 * @param WP_REST_Request $request  Request.
	 * @return mixed
	 */
	public static function on_rest_after( $response, $handler, $request ) {
		$route = $request->get_route();
		if ( 0 !== strpos( $route, '/ipo-ai/v1' ) ) {
			return $response;
		}

		try {
			$token = $request->get_param( '_ipo_ai_token' );
			$label = is_array( $token ) ? trim( ( isset( $token['label'] ) ? $token['label'] : '' ) . ' ' . ( isset( $token['prefix'] ) ? $token['prefix'] : '' ) ) : '';

			$tool = '';
			$args = array();
			if ( '/ipo-ai/v1/mcp' === $route && 'POST' === $request->get_method() ) {
				$body = $request->get_json_params();
				$tool = isset( $body['method'] ) ? (string) $body['method'] : '';
				if ( 'tools/call' === $tool && isset( $body['params']['name'] ) ) {
					$tool = (string) $body['params']['name'];
					$args = isset( $body['params']['arguments'] ) && is_array( $body['params']['arguments'] ) ? $body['params']['arguments'] : array();
				}
			} else {
				$tool = $request->get_method() . ' ' . $route;
				$args = $request->get_params();
			}

			if ( in_array( $tool, array( 'initialize', 'notifications/initialized', 'initialized', 'ping', 'tools/list' ), true ) ) {
				return $response;
			}

			unset( $args['_ipo_ai_token'], $args['ipo_ai_token'] );
			if ( isset( $args['content'] ) ) {
				$args['content'] = '(' . strlen( (string) $args['content'] ) . ' bytes, sha1 ' . sha1( (string) $args['content'] ) . ')';
			}
			foreach ( $args as $k => $v ) {
				$args[ $k ] = self::short( $v, 'sql' === $k ? 1500 : 400 );
			}

			$denied = is_wp_error( $response ) && in_array( $response->get_error_code(), array( 'ipo_ai_unauthorized', 'rest_forbidden' ), true );
			$is_write = (bool) preg_match( '/(write|update|delete|revert|POST \/ipo-ai\/v1\/(file|db\/))/i', $tool ) && false === strpos( $tool, 'db_changes' );

			$status = is_wp_error( $response ) ? $response->get_error_code() : ( $response instanceof WP_REST_Response ? $response->get_status() : 200 );
			if ( $response instanceof WP_REST_Response ) {
				$data = $response->get_data();
				if ( is_array( $data ) && isset( $data['result']['isError'] ) && $data['result']['isError'] ) {
					$status = 'tool error';
				}
			}

			IPO_Audit_Log::log(
				$denied ? 'ai_bridge_denied' : ( $is_write ? 'ai_bridge_write' : 'ai_bridge_call' ),
				array(
					'severity'     => $denied ? 'warning' : ( $is_write ? 'warning' : 'info' ),
					'object_type'  => 'ai_bridge',
					'object_id'    => substr( $tool, 0, 64 ),
					'object_label' => $label ? 'token: ' . $label : 'no valid token',
					'details'      => array(
						'tool'   => $tool,
						'args'   => $args,
						'result' => $status,
					),
				)
			);
		} catch ( \Throwable $e ) {
			unset( $e );
		}

		return $response;
	}
}
