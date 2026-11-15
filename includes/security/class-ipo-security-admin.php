<?php
/**
 * IPO Security — admin screens.
 *
 * Tools -> אבטחה ויומן פעולות : log viewer (filters + CSV), settings, IP locks.
 * Every edit screen gets a "היסטוריית שינויים" box with that item's last changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_Security_Admin {

	const PAGE_SLUG = 'ipo-security';
	const CAP       = 'manage_options';
	const PER_PAGE  = 50;

	/** Quick filter groups. */
	const GROUPS = array(
		'security' => array( 'label' => 'אבטחה בלבד', 'severity_min' => 'warning' ),
		'accounts' => array( 'label' => 'חשבונות וכניסות', 'events' => array( 'login_success', 'login_failed', 'login_blocked', 'logout', 'lockout', 'password_reset_requested', 'password_reset_blocked', 'password_reset_completed', 'password_changed', 'email_changed', 'user_updated', 'user_created', 'user_deleted', 'user_role_changed', 'user_caps_changed', 'app_password_created', 'app_password_deleted', '2fa_code_sent', '2fa_success', '2fa_failed', '2fa_mail_failed' ) ),
		'content'  => array( 'label' => 'תוכן', 'events' => array( 'post_created', 'post_updated', 'post_trashed', 'post_restored', 'post_deleted', 'media_uploaded', 'term_created', 'term_updated', 'term_deleted', 'menu_updated', 'menu_deleted', 'form_saved' ) ),
		'settings' => array( 'label' => 'הגדרות ותוספים', 'events' => array( 'option_updated', 'option_added', 'option_deleted', 'plugin_activated', 'plugin_deactivated', 'plugin_deleted', 'software_updated', 'theme_switched', 'customizer_saved', 'export_downloaded' ) ),
		'bridge'   => array( 'label' => 'AI Bridge', 'events' => array( 'ai_bridge_call', 'ai_bridge_write', 'ai_bridge_denied' ) ),
	);

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_ipo_security_save', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_ipo_security_unlock', array( __CLASS__, 'unlock' ) );
		add_action( 'admin_post_ipo_security_test_mail', array( __CLASS__, 'test_mail' ) );
		add_action( 'admin_post_ipo_security_csv', array( __CLASS__, 'csv' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'history_box' ), 10, 2 );
	}

	public static function menu() {
		add_management_page( 'אבטחה ויומן פעולות', 'אבטחה ויומן פעולות', self::CAP, self::PAGE_SLUG, array( __CLASS__, 'render' ) );
	}

	protected static function url( $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => self::PAGE_SLUG ), $args ), admin_url( 'tools.php' ) );
	}

	protected static function local_time( $utc ) {
		return get_date_from_gmt( $utc, 'd/m/Y H:i:s' );
	}

	/* ================================================================== */
	/* Query                                                               */
	/* ================================================================== */

	/** @return array filters from the request */
	protected static function filters() {
		$f = array();
		foreach ( array( 'from', 'to', 'user', 'event', 'severity', 'otype', 'oid', 'ip', 's', 'group' ) as $k ) {
			$f[ $k ] = isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		}
		return $f;
	}

	/** @return array{0:string,1:array} WHERE sql + args */
	protected static function where( array $f ) {
		global $wpdb;
		$w    = array( '1=1' );
		$args = array();

		if ( $f['from'] && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f['from'] ) ) {
			$w[]    = 'created_at >= %s';
			$args[] = get_gmt_from_date( $f['from'] . ' 00:00:00' );
		}
		if ( $f['to'] && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f['to'] ) ) {
			$w[]    = 'created_at <= %s';
			$args[] = get_gmt_from_date( $f['to'] . ' 23:59:59' );
		}
		if ( '' !== $f['user'] ) {
			if ( ctype_digit( $f['user'] ) ) {
				$w[]    = 'user_id = %d';
				$args[] = (int) $f['user'];
			} else {
				$w[]    = '(user_login = %s OR object_label LIKE %s)';
				$args[] = $f['user'];
				$args[] = '%' . $wpdb->esc_like( $f['user'] ) . '%';
			}
		}
		if ( $f['event'] ) {
			$w[]    = 'event = %s';
			$args[] = $f['event'];
		}
		if ( $f['severity'] && in_array( $f['severity'], IPO_Audit_Log::SEVERITIES, true ) ) {
			$w[]    = 'severity = %s';
			$args[] = $f['severity'];
		}
		if ( $f['otype'] ) {
			$w[]    = 'object_type = %s';
			$args[] = $f['otype'];
		}
		if ( '' !== $f['oid'] ) {
			$w[]    = 'object_id = %s';
			$args[] = $f['oid'];
		}
		if ( $f['ip'] ) {
			$w[]    = 'ip LIKE %s';
			$args[] = $wpdb->esc_like( $f['ip'] ) . '%';
		}
		if ( $f['s'] ) {
			$like   = '%' . $wpdb->esc_like( $f['s'] ) . '%';
			$w[]    = '(object_label LIKE %s OR details LIKE %s OR request LIKE %s OR user_agent LIKE %s)';
			$args   = array_merge( $args, array( $like, $like, $like, $like ) );
		}
		if ( $f['group'] && isset( self::GROUPS[ $f['group'] ] ) ) {
			$g = self::GROUPS[ $f['group'] ];
			if ( isset( $g['severity_min'] ) ) {
				$w[] = "severity IN ('warning','critical')";
			}
			if ( isset( $g['events'] ) ) {
				$w[]  = 'event IN (' . implode( ',', array_fill( 0, count( $g['events'] ), '%s' ) ) . ')';
				$args = array_merge( $args, $g['events'] );
			}
		}
		return array( implode( ' AND ', $w ), $args );
	}

	protected static function fetch( array $f, $limit, $offset ) {
		global $wpdb;
		$table              = IPO_Audit_Log::table();
		list( $where, $args ) = self::where( $f );
		$args[]             = (int) $limit;
		$args[]             = (int) $offset;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d", $args ) );
	}

	protected static function count( array $f ) {
		global $wpdb;
		$table              = IPO_Audit_Log::table();
		list( $where, $args ) = self::where( $f );
		$sql                = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
		return (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_var( $sql ) );
	}

	/* ================================================================== */
	/* Page                                                                */
	/* ================================================================== */

	public static function render() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'אין הרשאה.' );
		}
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'log'; // phpcs:ignore
		?>
		<div class="wrap ipo-sec">
			<h1>אבטחה ויומן פעולות</h1>
			<?php self::notices(); ?>
			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( self::url() ); ?>" class="nav-tab <?php echo 'log' === $tab ? 'nav-tab-active' : ''; ?>">יומן פעולות</a>
				<a href="<?php echo esc_url( self::url( array( 'tab' => 'settings' ) ) ); ?>" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>">הגדרות ומצב אבטחה</a>
			</nav>
			<?php self::styles(); ?>
			<?php 'settings' === $tab ? self::render_settings() : self::render_log(); ?>
		</div>
		<?php
	}

	protected static function notices() {
		$msg = isset( $_GET['ipo_msg'] ) ? sanitize_key( $_GET['ipo_msg'] ) : ''; // phpcs:ignore
		$map = array(
			'saved'      => array( 'success', 'ההגדרות נשמרו.' ),
			'unlocked'   => array( 'success', 'כתובת ה-IP שוחררה.' ),
			'checked'    => array( 'success', 'הבדיקות הורצו. התוצאות מעודכנות בטבלת מצב האבטחה.' ),
			'mail_ok'    => array( 'success', 'מייל בדיקה נשלח. אם הגיע לתיבה — אפשר להפעיל אימות דו-שלבי בבטחה.' ),
			'mail_fail'  => array( 'error', 'שליחת מייל הבדיקה נכשלה. אל תפעילו אימות דו-שלבי לפני שהמייל עובד.' ),
		);
		if ( isset( $map[ $msg ] ) ) {
			printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $map[ $msg ][0] ), esc_html( $map[ $msg ][1] ) );
		}
	}

	protected static function styles() {
		?>
		<style>
			.ipo-sec .ipo-filters{display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end;margin:16px 0 8px;background:#fff;padding:12px;border:1px solid #dcdcde}
			.ipo-sec .ipo-filters label{display:flex;flex-direction:column;font-size:12px;gap:2px}
			.ipo-sec .ipo-groups a{margin-inline-end:10px}
			.ipo-sec .ipo-groups a.current{font-weight:700;color:#000}
			.ipo-sec table.ipo-log td{vertical-align:top}
			.ipo-sec .sev{display:inline-block;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:600}
			.ipo-sec .sev-info{background:#f0f0f1;color:#50575e}
			.ipo-sec .sev-notice{background:#e5f0fa;color:#135e96}
			.ipo-sec .sev-warning{background:#fcf0e3;color:#8a4b00}
			.ipo-sec .sev-critical{background:#facfd2;color:#8a1f11}
			.ipo-sec tr.row-critical{background:#fff5f5}
			.ipo-sec pre{white-space:pre-wrap;word-break:break-word;max-height:420px;overflow:auto;background:#f6f7f7;padding:8px;direction:ltr;text-align:left;font-size:11px}
			.ipo-sec .muted{color:#787c82;font-size:11px}
			.ipo-sec .ltr{direction:ltr;unicode-bidi:embed}
			.ipo-sec .ipo-status td:first-child{width:28px;font-size:16px}
		</style>
		<?php
	}

	protected static function render_log() {
		$f      = self::filters();
		$paged  = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1; // phpcs:ignore
		$total  = self::count( $f );
		$rows   = self::fetch( $f, self::PER_PAGE, ( $paged - 1 ) * self::PER_PAGE );
		$labels = IPO_Audit_Events::labels();
		$pages  = max( 1, (int) ceil( $total / self::PER_PAGE ) );
		?>
		<p class="ipo-groups">
			<a href="<?php echo esc_url( self::url() ); ?>" class="<?php echo $f['group'] ? '' : 'current'; ?>">הכל</a>
			<?php foreach ( self::GROUPS as $key => $g ) : ?>
				<a href="<?php echo esc_url( self::url( array( 'group' => $key ) ) ); ?>" class="<?php echo $f['group'] === $key ? 'current' : ''; ?>"><?php echo esc_html( $g['label'] ); ?></a>
			<?php endforeach; ?>
		</p>

		<form method="get" class="ipo-filters">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
			<input type="hidden" name="group" value="<?php echo esc_attr( $f['group'] ); ?>">
			<label>מתאריך<input type="date" name="from" value="<?php echo esc_attr( $f['from'] ); ?>"></label>
			<label>עד תאריך<input type="date" name="to" value="<?php echo esc_attr( $f['to'] ); ?>"></label>
			<label>משתמש (שם או מזהה)<input type="text" name="user" value="<?php echo esc_attr( $f['user'] ); ?>" size="14"></label>
			<label>אירוע
				<select name="event">
					<option value="">— הכל —</option>
					<?php foreach ( $labels as $k => $l ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $f['event'], $k ); ?>><?php echo esc_html( $l ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>חומרה
				<select name="severity">
					<option value="">— הכל —</option>
					<?php foreach ( IPO_Audit_Log::SEVERITIES as $s ) : ?>
						<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $f['severity'], $s ); ?>><?php echo esc_html( $s ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>סוג יעד<input type="text" name="otype" value="<?php echo esc_attr( $f['otype'] ); ?>" size="10" placeholder="program / user / option"></label>
			<label>מזהה יעד<input type="text" name="oid" value="<?php echo esc_attr( $f['oid'] ); ?>" size="8"></label>
			<label>IP<input type="text" name="ip" value="<?php echo esc_attr( $f['ip'] ); ?>" size="14" class="ltr"></label>
			<label>חיפוש חופשי<input type="search" name="s" value="<?php echo esc_attr( $f['s'] ); ?>" size="18"></label>
			<button class="button button-primary">סינון</button>
			<a class="button" href="<?php echo esc_url( self::url() ); ?>">ניקוי</a>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( array( 'action' => 'ipo_security_csv' ), array_filter( $f ) ), admin_url( 'admin-post.php' ) ), 'ipo_security_csv' ) ); ?>">ייצוא CSV</a>
		</form>

		<p class="muted"><?php echo esc_html( number_format_i18n( $total ) ); ?> רשומות. הזמנים בשעון האתר. הרשומות נשמרות <?php echo (int) IPO_Audit_Log::RETENTION_DAYS; ?> יום ואי אפשר למחוק אותן מהממשק.</p>

		<table class="widefat striped ipo-log">
			<thead><tr><th style="width:120px">זמן</th><th style="width:70px">חומרה</th><th style="width:170px">אירוע</th><th style="width:150px">מבצע</th><th>יעד</th><th style="width:120px">IP</th><th style="width:40%">פרטים</th></tr></thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="7">אין רשומות.</td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $r ) : ?>
				<?php
				$details = $r->details ? json_decode( $r->details, true ) : array();
				$target  = self::target_html( $r );
				?>
				<tr class="row-<?php echo esc_attr( $r->severity ); ?>">
					<td><span title="<?php echo esc_attr( $r->created_at . ' UTC' ); ?>"><?php echo esc_html( self::local_time( $r->created_at ) ); ?></span><br><span class="muted">#<?php echo (int) $r->id; ?></span></td>
					<td><span class="sev sev-<?php echo esc_attr( $r->severity ); ?>"><?php echo esc_html( $r->severity ); ?></span></td>
					<td><a href="<?php echo esc_url( self::url( array( 'event' => $r->event ) ) ); ?>"><?php echo esc_html( isset( $labels[ $r->event ] ) ? $labels[ $r->event ] : $r->event ); ?></a><br><span class="muted ltr"><?php echo esc_html( $r->event ); ?></span></td>
					<td>
						<?php if ( $r->user_id ) : ?>
							<a href="<?php echo esc_url( self::url( array( 'user' => $r->user_id ) ) ); ?>"><?php echo esc_html( $r->user_login ); ?></a><br><span class="muted"><?php echo esc_html( $r->user_role ); ?></span>
						<?php else : ?>
							<span class="muted">לא מחובר / מערכת</span>
						<?php endif; ?>
						<br><span class="muted"><?php echo esc_html( $r->context ); ?></span>
					</td>
					<td><?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput -- built with esc_* in target_html(). ?></td>
					<td class="ltr"><a href="<?php echo esc_url( self::url( array( 'ip' => $r->ip ) ) ); ?>"><?php echo esc_html( $r->ip ); ?></a></td>
					<td>
						<?php echo esc_html( self::summary( $r, $details ) ); ?>
						<details><summary class="muted">הצג הכל</summary>
							<pre><?php echo esc_html( wp_json_encode( $details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></pre>
							<div class="muted ltr"><?php echo esc_html( $r->request ); ?></div>
							<div class="muted ltr"><?php echo esc_html( $r->user_agent ); ?></div>
						</details>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'current' => $paged,
							'total'   => $pages,
						)
					)
				);
				?>
			</div></div>
		<?php endif; ?>
		<?php
	}

	protected static function target_html( $r ) {
		$label = $r->object_label ? $r->object_label : $r->object_id;
		$html  = '<span class="muted">' . esc_html( $r->object_type ) . ( '' !== $r->object_id ? ' #' . esc_html( $r->object_id ) : '' ) . '</span><br>';
		if ( 'user' === $r->object_type && $r->object_id && get_userdata( (int) $r->object_id ) ) {
			return $html . '<a href="' . esc_url( get_edit_user_link( (int) $r->object_id ) ) . '">' . esc_html( $label ) . '</a>';
		}
		if ( ctype_digit( (string) $r->object_id ) && post_type_exists( $r->object_type ) ) {
			$link = get_edit_post_link( (int) $r->object_id, 'raw' );
			$hist = self::url( array( 'otype' => $r->object_type, 'oid' => $r->object_id ) );
			return $html . ( $link ? '<a href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a>' : esc_html( $label ) ) . ' · <a class="muted" href="' . esc_url( $hist ) . '">היסטוריה</a>';
		}
		return $html . esc_html( $label );
	}

	/** One-line summary of what changed. */
	protected static function summary( $r, $d ) {
		if ( ! is_array( $d ) ) {
			return '';
		}
		$parts = array();
		if ( ! empty( $d['fields'] ) ) {
			$parts[] = 'שדות: ' . implode( ', ', array_keys( $d['fields'] ) );
		}
		if ( ! empty( $d['meta'] ) ) {
			$keys    = array_keys( $d['meta'] );
			$parts[] = 'ACF/meta: ' . implode( ', ', array_slice( $keys, 0, 8 ) ) . ( count( $keys ) > 8 ? ' +' . ( count( $keys ) - 8 ) : '' );
		}
		foreach ( array( 'method', 'changed_by', 'reason', 'from', 'to', 'tool', 'result', 'username_tried', 'added', 'removed', 'added_role', 'removed_role' ) as $k ) {
			if ( isset( $d[ $k ] ) && '' !== $d[ $k ] && array() !== $d[ $k ] ) {
				$v       = is_scalar( $d[ $k ] ) ? (string) $d[ $k ] : wp_json_encode( $d[ $k ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				$parts[] = $k . ': ' . ( strlen( $v ) > 120 ? substr( $v, 0, 120 ) . '…' : $v );
			}
		}
		if ( isset( $d['args']['sql'] ) ) {
			$parts[] = 'sql: ' . substr( (string) $d['args']['sql'], 0, 140 );
		}
		return implode( ' | ', $parts );
	}

	/* ================================================================== */
	/* Settings tab                                                        */
	/* ================================================================== */

	protected static function render_settings() {
		$s     = IPO_Audit_Log::settings();
		$locks = IPO_Security_Hardening::active_locks();
		$ok    = '✅';
		$no    = '⚠️';

		$admins = get_users( array( 'role' => 'administrator', 'fields' => array( 'ID', 'user_login', 'user_email' ) ) );
		?>
		<h2>מצב אבטחה</h2>
		<table class="widefat ipo-status" style="max-width:900px">
			<tbody>
				<tr><td><?php echo $ok; ?></td><td>יומן פעולות פעיל — כניסות, כשלונות, סיסמאות, משתמשים, תפקידים, כל שמירת תוכן (כולל שדות ACF), מחיקות, הגדרות, תוספים, עדכונים וכל קריאה ל-AI Bridge.</td></tr>
				<tr><td><?php echo $ok; ?></td><td>נעילת IP אחרי <?php echo (int) IPO_Security_Hardening::FAIL_LIMIT; ?> ניסיונות כושלים ב-15 דקות (20 דקות; אחרי 3 נעילות ביממה — 24 שעות).</td></tr>
				<tr><td><?php echo $ok; ?></td><td>הגבלת בקשות איפוס סיסמה (<?php echo (int) IPO_Security_Hardening::RESET_PER_USER_HOUR; ?> לחשבון / <?php echo (int) IPO_Security_Hardening::RESET_PER_IP_HOUR; ?> ל-IP בשעה), בלי לחשוף אם משתמש קיים.</td></tr>
				<tr><td><?php echo $ok; ?></td><td>אותה הודעת שגיאה לשם משתמש שגוי ולסיסמה שגויה.</td></tr>
				<tr><td><?php echo $ok; ?></td><td>XML-RPC כבוי, סיסמאות אפליקציה כבויות, עורך הקבצים בממשק כבוי.</td></tr>
				<tr><td><?php echo $ok; ?></td><td>חסימת איתור שמות משתמשים (?author=, /author/, REST users, sitemap, oEmbed).</td></tr>
				<tr><td><?php echo $ok; ?></td><td>שינוי סיסמה מנתק את כל החיבורים האחרים של אותו משתמש.</td></tr>
				<tr><td><?php echo $ok; ?></td><td>קישורי איפוס סיסמה וקודי אימות מושחרים בטבלת Email Log.</td></tr>
				<tr><td><?php echo $ok; ?></td><td>AI Bridge: קריאות SQL לא מחזירות hash-ים של סיסמאות, מפתחות איפוס או סודות (SMTP וכו'), וכל קריאה נרשמת ביומן.</td></tr>
				<tr><td><?php echo $s['two_factor'] ? $ok : $no; ?></td><td>אימות דו-שלבי במייל למנהלים ועורכים: <strong><?php echo $s['two_factor'] ? 'פעיל' : 'כבוי'; ?></strong></td></tr>
				<?php
				$tz_city = (string) get_option( 'timezone_string' );
				?>
				<tr><td><?php echo $tz_city ? $ok : $no; ?></td><td>אזור זמן: <strong class="ltr"><?php echo esc_html( wp_timezone_string() ); ?></strong>
					<?php if ( ! $tz_city ) : ?>
						— הפרש קבוע, בלי מעבר שעון קיץ/חורף, ולכן חלק מהשנה כל השעות באתר וביומן זזות בשעה. לתקן ב<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>">הגדרות ← כללי</a>: לבחור "ירושלים".
					<?php endif; ?>
				</td></tr>
				<?php
				if ( class_exists( 'IPO_Security_Maintenance' ) ) :
					$exec = IPO_Security_Maintenance::check_uploads_exec();
					$scan = get_option( IPO_Security_Maintenance::UPLOAD_SCAN_OPTION );
					?>
					<tr><td><?php echo 'blocked' === $exec['status'] ? $ok : $no; ?></td><td>
						הרצת PHP בתיקיית ההעלאות:
						<?php if ( 'blocked' === $exec['status'] ) : ?>
							<strong>חסומה</strong> (HTTP <?php echo (int) $exec['code']; ?>).
						<?php elseif ( 'executes' === $exec['status'] ) : ?>
							<strong style="color:#b32d2e">השרת מריץ קבצי PHP מתוך wp-content/uploads.</strong>
							קובץ זדוני שיעלה לשם (דרך תוסף פגיע או טופס) ירוץ. האתר עובד על nginx, ולכן ‎.htaccess לא עוזר: צריך לבקש מחברת האחסון להוסיף לשרת את הכלל הבא, לפני הבלוק שמעביר קבצי PHP ל-php-fpm:
							<pre class="ltr" style="background:#f6f7f7;padding:8px;margin:6px 0;white-space:pre-wrap"><?php echo esc_html( IPO_Security_Maintenance::nginx_rule() ); ?></pre>
						<?php else : ?>
							<strong>לא ידוע</strong> — הבדיקה לא הצליחה (HTTP <?php echo (int) $exec['code']; ?>; ייתכן שהשרת חוסם פניות לעצמו).
						<?php endif; ?>
						<span class="muted">נבדק: <?php echo esc_html( wp_date( 'd/m/Y H:i', (int) $exec['time'] ) ); ?></span>
					</td></tr>
					<tr><td><?php echo ( is_array( $scan ) && empty( $scan['suspects'] ) ) ? $ok : $no; ?></td><td>
						קבצי PHP בתוך wp-content/uploads:
						<?php if ( ! is_array( $scan ) ) : ?>
							עוד לא נסרק.
						<?php else : ?>
							<?php echo (int) $scan['total']; ?> בסך הכול,
							<?php echo (int) $scan['total'] - count( $scan['suspects'] ); ?> מהם תבניות Twig מקומפלות של WPML (מטמון תקין, נבדקו שאין בהן קוד חשוד).
							<?php if ( $scan['suspects'] ) : ?>
								<br><strong style="color:#b32d2e">חשודים — לבדוק ולמחוק:</strong>
								<ul class="ltr" style="margin:4px 0 0 0">
									<?php foreach ( $scan['suspects'] as $rel ) : ?>
										<li><code><?php echo esc_html( $rel ); ?></code></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<span class="muted">נסרק: <?php echo esc_html( wp_date( 'd/m/Y H:i', (int) $scan['time'] ) ); ?></span>
						<?php endif; ?>
					</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php if ( class_exists( 'IPO_Security_Maintenance' ) ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:8px 0 16px">
				<?php wp_nonce_field( 'ipo_security_run_checks' ); ?>
				<input type="hidden" name="action" value="ipo_security_run_checks">
				<button class="button">להריץ עכשיו את בדיקות ההעלאות</button>
			</form>
		<?php endif; ?>

		<h2>הגדרות</h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ipo_security_save' ); ?>
			<input type="hidden" name="action" value="ipo_security_save">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="ipo-alerts">מיילים להתראות אבטחה</label></th>
					<td>
						<textarea id="ipo-alerts" name="alert_emails" rows="3" cols="50" class="ltr"><?php echo esc_textarea( $s['alert_emails'] ); ?></textarea>
						<p class="description">כתובת בכל שורה. נשלחת התראה מיידית על: שינוי/איפוס סיסמה של מנהל, משתמש מנהל חדש, שינוי תפקיד למנהל, הפעלת תוסף, שינוי כתובת האתר או מייל המנהל, נעילות חוזרות על חשבון מנהל ועוד.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">אימות דו-שלבי</th>
					<td>
						<label><input type="checkbox" name="two_factor" value="1" <?php checked( ! empty( $s['two_factor'] ) ); ?>> לדרוש קוד במייל בכל כניסה של מנהל או עורך (מכשיר מוכר — פעם ב-<?php echo (int) IPO_Two_Factor::TRUST_DAYS; ?> יום)</label>
						<p class="description">לפני ההפעלה: שלחו מייל בדיקה (למטה) וודאו שהוא מגיע. אם המייל נתקע ואין גישה, אפשר לכבות זמנית דרך wp-config.php: <code class="ltr">define( 'IPO_DISABLE_2FA', true );</code></p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'שמירה' ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:24px">
			<?php wp_nonce_field( 'ipo_security_test_mail' ); ?>
			<input type="hidden" name="action" value="ipo_security_test_mail">
			<button class="button">שלח לי מייל בדיקה (<?php echo esc_html( wp_get_current_user()->user_email ); ?>)</button>
		</form>

		<h2>כתובות IP נעולות כרגע</h2>
		<?php if ( ! $locks ) : ?>
			<p>אין.</p>
		<?php else : ?>
			<table class="widefat" style="max-width:600px"><thead><tr><th>IP</th><th>נעול עד</th><th></th></tr></thead><tbody>
			<?php foreach ( $locks as $ip => $until ) : ?>
				<tr>
					<td class="ltr"><a href="<?php echo esc_url( self::url( array( 'ip' => $ip ) ) ); ?>"><?php echo esc_html( $ip ); ?></a></td>
					<td><?php echo esc_html( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', (int) $until ), 'd/m/Y H:i' ) ); ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'ipo_security_unlock' ); ?>
							<input type="hidden" name="action" value="ipo_security_unlock">
							<input type="hidden" name="ip" value="<?php echo esc_attr( $ip ); ?>">
							<button class="button button-small">שחרור</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody></table>
		<?php endif; ?>

		<h2>משתמשים עם הרשאת מנהל (<?php echo count( $admins ); ?>)</h2>
		<p class="description">כל מנהל יכול לשנות סיסמה לכל משתמש אחר. כדאי להשאיר הרשאת מנהל רק למי שבאמת צריך.</p>
		<ul>
			<?php foreach ( $admins as $a ) : ?>
				<li><a href="<?php echo esc_url( get_edit_user_link( $a->ID ) ); ?>"><?php echo esc_html( $a->user_login ); ?></a> — <span class="ltr"><?php echo esc_html( $a->user_email ); ?></span> · <a href="<?php echo esc_url( self::url( array( 'user' => $a->ID ) ) ); ?>">פעולות</a></li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/* ================================================================== */
	/* Actions                                                             */
	/* ================================================================== */

	public static function save_settings() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'אין הרשאה.' );
		}
		check_admin_referer( 'ipo_security_save' );

		$emails = isset( $_POST['alert_emails'] ) ? sanitize_textarea_field( wp_unslash( $_POST['alert_emails'] ) ) : '';
		$valid  = array_filter( array_map( 'trim', preg_split( '/[\s,;]+/', $emails ) ), 'is_email' );

		update_option(
			IPO_Audit_Log::SETTINGS_OPTION,
			array(
				'alert_emails' => implode( "\n", array_unique( $valid ) ),
				'two_factor'   => empty( $_POST['two_factor'] ) ? 0 : 1,
			),
			false
		);
		wp_safe_redirect( self::url( array( 'tab' => 'settings', 'ipo_msg' => 'saved' ) ) );
		exit;
	}

	public static function unlock() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'אין הרשאה.' );
		}
		check_admin_referer( 'ipo_security_unlock' );
		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			IPO_Security_Hardening::unlock_ip( $ip );
			IPO_Audit_Log::log( 'option_updated', array( 'severity' => 'notice', 'object_type' => 'ip', 'object_id' => $ip, 'object_label' => 'IP unlocked by admin' ) );
		}
		wp_safe_redirect( self::url( array( 'tab' => 'settings', 'ipo_msg' => 'unlocked' ) ) );
		exit;
	}

	public static function test_mail() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'אין הרשאה.' );
		}
		check_admin_referer( 'ipo_security_test_mail' );
		$user = wp_get_current_user();
		$ok   = wp_mail( $user->user_email, '[' . wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) . '] בדיקת מייל לאימות דו-שלבי', "מייל זה מאשר שהאתר מצליח לשלוח אליך מיילים.\nאפשר להפעיל אימות דו-שלבי." );
		wp_safe_redirect( self::url( array( 'tab' => 'settings', 'ipo_msg' => $ok ? 'mail_ok' : 'mail_fail' ) ) );
		exit;
	}

	public static function csv() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( 'אין הרשאה.' );
		}
		check_admin_referer( 'ipo_security_csv' );

		$f    = self::filters();
		$rows = self::fetch( $f, 50000, 0 );

		IPO_Audit_Log::log( 'export_downloaded', array( 'severity' => 'notice', 'object_type' => 'audit_log', 'object_label' => 'audit log CSV', 'details' => array( 'filters' => array_filter( $f ), 'rows' => count( $rows ) ) ) );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="ipo-audit-log-' . gmdate( 'Ymd-His' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // Excel: UTF-8 BOM for Hebrew.
		fputcsv( $out, array( 'id', 'time_site', 'time_utc', 'severity', 'event', 'user_id', 'user_login', 'user_role', 'ip', 'context', 'object_type', 'object_id', 'object_label', 'request', 'user_agent', 'details' ) );
		foreach ( $rows as $r ) {
			$line = array( $r->id, self::local_time( $r->created_at ), $r->created_at, $r->severity, $r->event, $r->user_id, $r->user_login, $r->user_role, $r->ip, $r->context, $r->object_type, $r->object_id, $r->object_label, $r->request, $r->user_agent, $r->details );
			// Neutralise spreadsheet formulas.
			$line = array_map(
				function ( $v ) {
					$v = (string) $v;
					return ( '' !== $v && in_array( $v[0], array( '=', '+', '-', '@' ), true ) ) ? "'" . $v : $v;
				},
				$line
			);
			fputcsv( $out, $line );
		}
		fclose( $out );
		exit;
	}

	/* ================================================================== */
	/* Edit-screen history box                                             */
	/* ================================================================== */

	public static function history_box( $post_type, $post = null ) {
		if ( ! current_user_can( self::CAP ) || ! $post instanceof WP_Post || in_array( $post_type, IPO_Audit_Events::SKIP_POST_TYPES, true ) ) {
			return;
		}
		add_meta_box( 'ipo-audit-history', 'היסטוריית שינויים', array( __CLASS__, 'render_history_box' ), $post_type, 'side', 'low' );
	}

	public static function render_history_box( $post ) {
		global $wpdb;
		$table = IPO_Audit_Log::table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT id, created_at, event, user_login, details FROM {$table} WHERE object_type = %s AND object_id = %s ORDER BY id DESC LIMIT 15", $post->post_type, (string) $post->ID ) );
		$labels = IPO_Audit_Events::labels();
		if ( ! $rows ) {
			echo '<p>עדיין אין שינויים ביומן לפריט זה.</p>';
			return;
		}
		echo '<ul style="margin:0">';
		foreach ( $rows as $r ) {
			$d      = $r->details ? json_decode( $r->details, true ) : array();
			$fields = array_merge( isset( $d['fields'] ) ? array_keys( $d['fields'] ) : array(), isset( $d['meta'] ) ? array_keys( $d['meta'] ) : array() );
			printf(
				'<li style="border-bottom:1px solid #eee;padding:4px 0"><strong>%s</strong> · %s<br><span style="color:#787c82">%s</span>%s</li>',
				esc_html( isset( $labels[ $r->event ] ) ? $labels[ $r->event ] : $r->event ),
				esc_html( $r->user_login ? $r->user_login : 'מערכת' ),
				esc_html( get_date_from_gmt( $r->created_at, 'd/m/Y H:i' ) ),
				$fields ? '<br><span style="font-size:11px">' . esc_html( implode( ', ', array_slice( $fields, 0, 10 ) ) . ( count( $fields ) > 10 ? '…' : '' ) ) . '</span>' : ''
			);
		}
		echo '</ul>';
		printf( '<p><a href="%s">לכל ההיסטוריה ←</a></p>', esc_url( self::url( array( 'otype' => $post->post_type, 'oid' => $post->ID ) ) ) );
	}
}
