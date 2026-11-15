<?php
/**
 * IPO Security — two-step login by e-mail code for administrators and editors.
 *
 * Off until enabled under Tools -> אבטחה ויומן פעולות. After the password is
 * accepted, the fresh session is revoked and a 6-digit code (10 min, 5 tries)
 * is e-mailed. A signed "trusted device" cookie skips the step for 30 days;
 * changing the password invalidates every trusted device.
 *
 * Emergency off switch (if mail stops working): add to wp-config.php
 *     define( 'IPO_DISABLE_2FA', true );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Two_Factor {

	const META         = 'ipo_2fa_challenge';
	const ACTION       = 'ipo_2fa';
	const CODE_TTL     = 600;
	const MAX_TRIES    = 5;
	const RESEND_WAIT  = 60;
	const TRUST_DAYS   = 30;
	const ROLES        = array( 'administrator', 'editor' );

	/** @var string Session token created by the login being intercepted. */
	protected static $new_token = '';

	public static function init() {
		add_action( 'set_logged_in_cookie', array( __CLASS__, 'capture_token' ), 10, 6 );
		add_action( 'wp_login', array( __CLASS__, 'intercept' ), 0, 2 );
		add_action( 'login_form_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	public static function enabled() {
		if ( defined( 'IPO_DISABLE_2FA' ) && IPO_DISABLE_2FA ) {
			return false;
		}
		return ! empty( IPO_Audit_Log::settings()['two_factor'] );
	}

	public static function required_for( WP_User $user ) {
		return (bool) array_intersect( self::ROLES, (array) $user->roles );
	}

	public static function capture_token( $cookie, $expire, $expiration, $user_id, $scheme, $token = '' ) {
		self::$new_token = (string) $token;
	}

	/* ------------------------------------------------------------------ */

	protected static function cookie_name() {
		return 'ipo_2fa_trust_' . COOKIEHASH;
	}

	protected static function trust_sig( WP_User $user, $expires ) {
		return hash_hmac( 'sha256', $user->ID . '|' . $expires . '|' . substr( $user->user_pass, -12 ), wp_salt( 'auth' ) );
	}

	protected static function is_trusted( WP_User $user ) {
		$name = self::cookie_name();
		if ( empty( $_COOKIE[ $name ] ) ) {
			return false;
		}
		$parts = explode( '|', sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) );
		if ( 3 !== count( $parts ) || (int) $parts[0] !== (int) $user->ID || (int) $parts[1] < time() ) {
			return false;
		}
		return hash_equals( self::trust_sig( $user, (int) $parts[1] ), $parts[2] );
	}

	protected static function set_trusted( WP_User $user ) {
		$expires = time() + self::TRUST_DAYS * DAY_IN_SECONDS;
		setcookie(
			self::cookie_name(),
			$user->ID . '|' . $expires . '|' . self::trust_sig( $user, $expires ),
			array(
				'expires'  => $expires,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	protected static function hmac( $value ) {
		return hash_hmac( 'sha256', (string) $value, wp_salt( 'auth' ) );
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Password accepted. Hold the login until the code is entered.
	 *
	 * @param string  $login User login.
	 * @param WP_User $user  User.
	 */
	public static function intercept( $login, $user ) {
		if ( ! $user instanceof WP_User || ! self::enabled() || ! self::required_for( $user ) ) {
			return;
		}
		if ( self::is_trusted( $user ) ) {
			return;
		}

		// Undo the session WordPress just created.
		if ( self::$new_token && class_exists( 'WP_Session_Tokens' ) ) {
			WP_Session_Tokens::get_instance( $user->ID )->destroy( self::$new_token );
		}
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );

		$in_login_form = isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'];
		if ( ! $in_login_form ) {
			// Logins outside the login screen (custom code) cannot show the code form: refuse.
			IPO_Audit_Log::log( '2fa_failed', array( 'severity' => 'warning', 'user' => $user, 'object_type' => 'user', 'object_id' => $user->ID, 'object_label' => $user->user_login, 'details' => array( 'reason' => 'login outside wp-login.php refused (2FA required)' ) ) );
			wp_die( 'נדרש אימות דו-שלבי. יש להתחבר דרך מסך הכניסה.', 403 );
		}

		$remember = ! empty( $_POST['rememberme'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$redirect = ! empty( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : admin_url(); // phpcs:ignore

		$nonce = wp_generate_password( 40, false );
		$sent  = self::new_code( $user, $nonce, $remember, $redirect );

		self::render( $user, $nonce, $sent ? '' : 'mail' );
		exit;
	}

	/**
	 * Create (or replace) the pending code and e-mail it.
	 */
	protected static function new_code( WP_User $user, $nonce, $remember, $redirect ) {
		$code = str_pad( (string) random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );

		update_user_meta(
			$user->ID,
			self::META,
			array(
				'code'     => self::hmac( $code ),
				'nonce'    => self::hmac( $nonce ),
				'expires'  => time() + self::CODE_TTL,
				'tries'    => 0,
				'sent'     => time(),
				'remember' => (bool) $remember,
				'redirect' => $redirect,
			)
		);

		$site    = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$offset  = (float) get_option( 'gmt_offset' );
		$when    = gmdate( 'd/m/Y H:i', time() + (int) ( $offset * HOUR_IN_SECONDS ) );
		$message = implode(
			"\n",
			array(
				'שלום ' . $user->display_name . ',',
				'',
				'קוד האימות שלך לכניסה לאתר:',
				'IPO-CODE: ' . $code,
				'',
				'הקוד בתוקף ל-10 דקות.',
				'ניסיון הכניסה: ' . $when . ' מכתובת IP ' . IPO_Audit_Log::ip(),
				'',
				'אם לא ניסית להתחבר עכשיו — מישהו יודע את הסיסמה שלך. החלף סיסמה מיד ועדכן את מנהל האתר.',
			)
		);

		$ok = wp_mail( $user->user_email, sprintf( '[%s] קוד אימות לכניסה', $site ), $message );

		IPO_Audit_Log::log(
			$ok ? '2fa_code_sent' : '2fa_mail_failed',
			array(
				'severity'     => $ok ? 'info' : 'warning',
				'user'         => $user,
				'object_type'  => 'user',
				'object_id'    => $user->ID,
				'object_label' => $user->user_login,
			)
		);
		return $ok;
	}

	/**
	 * POST from the code form (wp-login.php?action=ipo_2fa).
	 */
	public static function handle() {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
			wp_safe_redirect( wp_login_url() );
			exit;
		}

		// phpcs:disable WordPress.Security.NonceVerification -- the per-login secret below is the CSRF token.
		$user_id = isset( $_POST['uid'] ) ? absint( $_POST['uid'] ) : 0;
		$nonce   = isset( $_POST['n'] ) ? sanitize_text_field( wp_unslash( $_POST['n'] ) ) : '';
		$code    = isset( $_POST['code'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['code'] ) ) : '';
		$resend  = ! empty( $_POST['resend'] );
		$trust   = ! empty( $_POST['trust'] );
		// phpcs:enable

		$user      = $user_id ? get_userdata( $user_id ) : false;
		$challenge = $user ? get_user_meta( $user_id, self::META, true ) : array();

		if ( ! $user || ! is_array( $challenge ) || empty( $challenge['nonce'] ) || ! hash_equals( $challenge['nonce'], self::hmac( $nonce ) ) ) {
			self::restart( 'expired' );
		}

		if ( IPO_Security_Hardening::lock_remaining() ) {
			delete_user_meta( $user_id, self::META );
			self::restart( 'locked' );
		}

		if ( $resend ) {
			if ( time() - (int) $challenge['sent'] < self::RESEND_WAIT ) {
				self::render( $user, $nonce, 'wait' );
				exit;
			}
			$sent = self::new_code( $user, $nonce, ! empty( $challenge['remember'] ), (string) $challenge['redirect'] );
			self::render( $user, $nonce, $sent ? 'resent' : 'mail' );
			exit;
		}

		if ( (int) $challenge['expires'] < time() ) {
			delete_user_meta( $user_id, self::META );
			self::restart( 'expired' );
		}

		if ( 6 !== strlen( $code ) || ! hash_equals( $challenge['code'], self::hmac( $code ) ) ) {
			$challenge['tries'] = (int) $challenge['tries'] + 1;
			IPO_Audit_Log::log( '2fa_failed', array( 'severity' => 'warning', 'user' => $user, 'object_type' => 'user', 'object_id' => $user_id, 'object_label' => $user->user_login, 'details' => array( 'try' => $challenge['tries'] ) ) );
			IPO_Security_Hardening::count_failure( $user->user_login, new WP_Error( 'ipo_2fa_wrong_code' ) );

			if ( $challenge['tries'] >= self::MAX_TRIES ) {
				delete_user_meta( $user_id, self::META );
				self::restart( 'tries' );
			}
			update_user_meta( $user_id, self::META, $challenge );
			self::render( $user, $nonce, 'wrong' );
			exit;
		}

		// Success.
		delete_user_meta( $user_id, self::META );
		IPO_Security_Hardening::clear_failures();
		wp_set_auth_cookie( $user_id, ! empty( $challenge['remember'] ) );
		wp_set_current_user( $user_id );
		if ( $trust ) {
			self::set_trusted( $user );
		}

		IPO_Audit_Log::log( '2fa_success', array( 'user' => $user, 'object_type' => 'user', 'object_id' => $user_id, 'object_label' => $user->user_login, 'details' => array( 'trusted_device_saved' => $trust ) ) );
		IPO_Audit_Log::log( 'login_success', array( 'user' => $user, 'object_type' => 'user', 'object_id' => $user_id, 'object_label' => $user->user_login . ' (' . $user->user_email . ')', 'details' => array( 'two_factor' => true ) ) );

		$redirect = ! empty( $challenge['redirect'] ) ? (string) $challenge['redirect'] : admin_url();
		wp_safe_redirect( apply_filters( 'login_redirect', $redirect, $redirect, $user ) );
		exit;
	}

	const RESTART_MESSAGES = array(
		'expired' => 'פג תוקף הקוד או תהליך הכניסה. יש להתחבר מחדש.',
		'locked'  => 'הגישה נחסמה זמנית בגלל ניסיונות רבים שנכשלו.',
		'tries'   => 'יותר מדי קודים שגויים. יש להתחבר מחדש.',
	);

	protected static function restart( $code ) {
		$url = add_query_arg( 'ipo_2fa_msg', $code, wp_login_url() );
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Code entry screen, drawn with the normal login page chrome.
	 */
	protected static function render( WP_User $user, $nonce, $state = '' ) {
		$messages = array(
			''       => array( 'message', 'שלחנו קוד בן 6 ספרות לכתובת ' . self::mask_email( $user->user_email ) . '.' ),
			'resent' => array( 'message', 'נשלח קוד חדש לכתובת ' . self::mask_email( $user->user_email ) . '.' ),
			'wrong'  => array( 'error', 'הקוד שגוי. נסו שוב.' ),
			'wait'   => array( 'error', 'יש להמתין דקה לפני שליחת קוד נוסף.' ),
			'mail'   => array( 'error', 'שליחת המייל עם הקוד נכשלה. נסו "שלח קוד חדש" או פנו למנהל האתר.' ),
		);
		list( $type, $text ) = isset( $messages[ $state ] ) ? $messages[ $state ] : $messages[''];

		$action = site_url( 'wp-login.php?action=' . self::ACTION, 'login_post' );

		if ( function_exists( 'login_header' ) ) {
			$errors = new WP_Error();
			if ( 'error' === $type ) {
				$errors->add( 'ipo_2fa', esc_html( $text ) );
				login_header( 'אימות דו-שלבי', '', $errors );
			} else {
				login_header( 'אימות דו-שלבי', '<p class="message">' . esc_html( $text ) . '</p>' );
			}
		} else {
			echo '<!doctype html><html dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>אימות דו-שלבי</title></head><body><p>' . esc_html( $text ) . '</p>';
		}
		?>
		<form name="ipo2faform" id="loginform" action="<?php echo esc_url( $action ); ?>" method="post" autocomplete="off">
			<input type="hidden" name="uid" value="<?php echo (int) $user->ID; ?>">
			<input type="hidden" name="n" value="<?php echo esc_attr( $nonce ); ?>">
			<p>
				<label for="ipo-2fa-code">קוד אימות</label>
				<input type="text" name="code" id="ipo-2fa-code" class="input" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" autofocus style="letter-spacing:.3em;text-align:center;font-size:24px">
			</p>
			<p class="forgetmenot">
				<label><input type="checkbox" name="trust" value="1" checked> לזכור את המכשיר הזה ל-<?php echo (int) self::TRUST_DAYS; ?> יום</label>
			</p>
			<p class="submit">
				<input type="submit" class="button button-primary button-large" value="אימות וכניסה">
			</p>
		</form>
		<form action="<?php echo esc_url( $action ); ?>" method="post" style="margin-top:12px;text-align:center">
			<input type="hidden" name="uid" value="<?php echo (int) $user->ID; ?>">
			<input type="hidden" name="n" value="<?php echo esc_attr( $nonce ); ?>">
			<input type="hidden" name="resend" value="1">
			<button type="submit" class="button-link">שלח קוד חדש</button>
		</form>
		<?php
		if ( function_exists( 'login_footer' ) ) {
			login_footer( 'ipo-2fa-code' );
		} else {
			echo '</body></html>';
		}
	}

	protected static function mask_email( $email ) {
		list( $name, $domain ) = array_pad( explode( '@', $email, 2 ), 2, '' );
		$keep = max( 1, min( 3, strlen( $name ) - 2 ) );
		return substr( $name, 0, $keep ) . str_repeat( '•', max( 2, strlen( $name ) - $keep ) ) . '@' . $domain;
	}
}

/** Show the restart message on the normal login screen. */
add_filter(
	'login_message',
	function ( $message ) {
		$code = isset( $_GET['ipo_2fa_msg'] ) ? sanitize_key( wp_unslash( $_GET['ipo_2fa_msg'] ) ) : ''; // phpcs:ignore
		if ( $code && isset( IPO_Two_Factor::RESTART_MESSAGES[ $code ] ) ) {
			$message .= '<div id="login_error" class="notice notice-error">' . esc_html( IPO_Two_Factor::RESTART_MESSAGES[ $code ] ) . '</div>';
		}
		return $message;
	}
);
