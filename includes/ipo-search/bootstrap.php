<?php
/**
 * IPO Search — a static-index search box for the header.
 *
 * Replaces the Ajax Search Pro widget in the desktop header. Ajax Search Pro
 * itself is left installed and untouched, so anywhere else it is used keeps
 * working and reverting is a one-line change.
 *
 * Results are read from a JSON file built by IPO_Search_Index, never from a
 * live query, so typing costs one cached download instead of an admin-ajax
 * round trip per keystroke.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-ipo-search-index.php';

class IPO_Search {

	const HANDLE = 'ipo-search';

	/** Hour of the day (site time, 0-23) the nightly build runs at. */
	const OPTION_HOUR = 'ipo_search_build_hour';

	/** Default build hour: the quietest part of the night. */
	const DEFAULT_HOUR = 4;

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_shortcode( 'ipo_search', array( __CLASS__, 'shortcode' ) );

		// The build is deliberately NOT tied to saving a post. Editors publish in
		// bursts, and rebuilding the whole index on each one would mean walking a
		// few thousand posts over and over for no benefit. It runs once a night,
		// or on demand from the button.
		add_action( IPO_Search_Index::CRON_HOOK, array( 'IPO_Search_Index', 'build_all' ) );
		add_action( 'admin_post_ipo_search_rebuild', array( __CLASS__, 'handle_manual_rebuild' ) );
		add_action( 'admin_post_ipo_search_save_hour', array( __CLASS__, 'handle_save_hour' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );

		self::ensure_schedule();
	}

	/**
	 * @return int Hour of day, 0-23.
	 */
	public static function build_hour() {
		$hour = get_option( self::OPTION_HOUR, self::DEFAULT_HOUR );
		$hour = is_numeric( $hour ) ? (int) $hour : self::DEFAULT_HOUR;
		return max( 0, min( 23, $hour ) );
	}

	/**
	 * Next time the given hour comes round, in the site's own timezone.
	 *
	 * wp_schedule_event() wants a UTC timestamp, but the hour the user picked is
	 * the hour they see on the clock — so the conversion has to go through
	 * wp_timezone() rather than assuming the server runs on local time.
	 *
	 * @param int $hour Hour of day, 0-23.
	 * @return int Unix timestamp.
	 */
	protected static function next_run( $hour ) {
		$timezone = wp_timezone();
		$now      = new DateTime( 'now', $timezone );
		$next     = new DateTime( 'today ' . sprintf( '%02d:00:00', $hour ), $timezone );

		if ( $next <= $now ) {
			$next->modify( '+1 day' );
		}

		return $next->getTimestamp();
	}

	/**
	 * Make sure a daily build is queued for the configured hour.
	 *
	 * @param bool $force Reschedule even if one is already queued.
	 */
	public static function ensure_schedule( $force = false ) {
		$existing = wp_next_scheduled( IPO_Search_Index::CRON_HOOK );

		if ( $existing && ! $force ) {
			return;
		}

		if ( $existing ) {
			wp_unschedule_event( $existing, IPO_Search_Index::CRON_HOOK );
		}

		wp_schedule_event( self::next_run( self::build_hour() ), 'daily', IPO_Search_Index::CRON_HOOK );
	}

	public static function handle_save_hour() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}
		check_admin_referer( 'ipo_search_save_hour' );

		$hour = isset( $_POST['build_hour'] ) ? (int) $_POST['build_hour'] : self::DEFAULT_HOUR;
		update_option( self::OPTION_HOUR, max( 0, min( 23, $hour ) ), false );

		self::ensure_schedule( true );

		wp_safe_redirect( add_query_arg( array( 'page' => 'ipo-search', 'saved' => '1' ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public static function assets() {
		$url = IPO_Search_Index::url();

		// Nothing to search yet — do not ship the UI, so the old widget keeps
		// working until the first index has been built.
		if ( ! $url ) {
			return;
		}

		$base = get_stylesheet_directory_uri();
		$dir  = get_stylesheet_directory();

		wp_enqueue_style(
			self::HANDLE,
			$base . '/assets/styles/ipo-search.css',
			array(),
			@filemtime( $dir . '/assets/styles/ipo-search.css' )
		);

		wp_enqueue_script(
			self::HANDLE,
			$base . '/assets/scripts/ipo-search.js',
			array(),
			@filemtime( $dir . '/assets/scripts/ipo-search.js' ),
			true
		);

		$is_hebrew = ! defined( 'ICL_LANGUAGE_CODE' ) || 'he' === ICL_LANGUAGE_CODE;

		wp_localize_script(
			self::HANDLE,
			'IPO_SEARCH',
			array(
				'index' => $url,
				'rtl'   => $is_hebrew ? 1 : 0,
				'i18n'  => $is_hebrew
					? array(
						'placeholder' => 'חיפוש',
						'concerts'    => 'קונצרטים',
						'artists'     => 'אמנים',
						'series'      => 'סדרות',
						'pages'       => 'עמודים',
						'past'        => 'קונצרטים שהיו',
						'showPast'    => 'הצגת קונצרטים שהיו',
						'hidePast'    => 'הסתרת קונצרטים שהיו',
						'noResults'   => 'לא נמצאו תוצאות עבור',
						'more'        => 'עוד תאריכים',
						'close'       => 'סגירה',
					)
					: array(
						'placeholder' => 'Search',
						'concerts'    => 'Concerts',
						'artists'     => 'Artists',
						'series'      => 'Series',
						'pages'       => 'Pages',
						'past'        => 'Past concerts',
						'showPast'    => 'Show past concerts',
						'hidePast'    => 'Hide past concerts',
						'noResults'   => 'No results for',
						'more'        => 'more dates',
						'close'       => 'Close',
					),
			)
		);
	}

	/**
	 * The markup the header drops in place of the Ajax Search Pro shortcode.
	 *
	 * @return string
	 */
	public static function shortcode() {
		if ( ! IPO_Search_Index::url() ) {
			return '';
		}

		ob_start();
		?>
		<div class="ipo-search" data-ipo-search>
			<button type="button" class="ipo-search__toggle" aria-label="חיפוש" aria-expanded="false">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
					<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
					<line x1="16.5" y1="16.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
				</svg>
			</button>
		</div>
		<?php
		return trim( ob_get_clean() );
	}

	public static function menu() {
		add_management_page(
			'IPO Search index',
			'IPO Search',
			'manage_options',
			'ipo-search',
			array( __CLASS__, 'render_admin' )
		);
	}

	public static function handle_manual_rebuild() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}
		check_admin_referer( 'ipo_search_rebuild' );

		IPO_Search_Index::build_all();

		wp_safe_redirect( add_query_arg( array( 'page' => 'ipo-search', 'rebuilt' => '1' ), admin_url( 'tools.php' ) ) );
		exit;
	}

	public static function render_admin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}

		$report    = IPO_Search_Index::report();
		$error     = IPO_Search_Index::last_error();
		$next      = wp_next_scheduled( IPO_Search_Index::CRON_HOOK );
		$hour      = self::build_hour();
		?>
		<div class="wrap" dir="rtl">
			<h1>אינדקס החיפוש</h1>

			<?php if ( ! empty( $_GET['rebuilt'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>האינדקס נבנה מחדש.</p></div>
			<?php endif; ?>

			<?php if ( ! empty( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>שעת הבנייה נשמרה.</p></div>
			<?php endif; ?>

			<?php if ( $error ) : ?>
				<div class="notice notice-error">
					<p><strong>הבנייה האחרונה נכשלה</strong> (<?php echo esc_html( gmdate( 'Y-m-d H:i', (int) $error['when'] ) . ' UTC' ); ?>)</p>
					<p>
						שלב: <code><?php echo esc_html( $error['stage'] ); ?></code><br />
						<?php echo esc_html( $error['message'] ); ?><br />
						<code><?php echo esc_html( $error['file'] . ':' . $error['line'] ); ?></code><br />
						שיא זיכרון: <?php echo esc_html( $error['peak_memory'] ); ?>
						מתוך <?php echo esc_html( $error['limit'] ); ?>
					</p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width:720px;padding:16px 20px;margin-bottom:16px;">
				<h2 style="margin-top:0;">שעת בנייה יומית</h2>
				<p>
					האינדקס נבנה פעם ביום בשעה שתבחר. הוא <strong>לא</strong> נבנה בכל פרסום —
					בנייה סורקת את כל הפוסטים באתר, ואין טעם לעשות זאת שוב אחרי כל עריכה.
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ipo_search_save_hour' ); ?>
					<input type="hidden" name="action" value="ipo_search_save_hour" />
					<label for="build_hour"><strong>שעה</strong></label>
					<select name="build_hour" id="build_hour">
						<?php for ( $h = 0; $h < 24; $h++ ) : ?>
							<option value="<?php echo (int) $h; ?>" <?php selected( $h, $hour ); ?>>
								<?php echo esc_html( sprintf( '%02d:00', $h ) ); ?>
							</option>
						<?php endfor; ?>
					</select>
					<?php submit_button( 'שמירה', 'secondary', 'submit', false ); ?>
				</form>
				<p style="color:#646970;margin-bottom:0;">
					<?php if ( $next ) : ?>
						הבנייה הבאה:
						<strong><?php echo esc_html( wp_date( 'Y-m-d H:i', $next ) ); ?></strong>
						(שעון האתר)
					<?php else : ?>
						אין בנייה מתוזמנת כרגע.
					<?php endif; ?>
				</p>
			</div>

			<div class="card" style="max-width:720px;padding:16px 20px;">
				<?php if ( empty( $report ) ) : ?>
					<p>האינדקס עדיין לא נבנה. עד שייבנה, תיבת החיפוש החדשה לא נטענת והחיפוש הישן ממשיך לעבוד.</p>
				<?php else : ?>
					<p>
						נבנה לאחרונה:
						<strong><?php echo esc_html( gmdate( 'Y-m-d H:i', (int) $report['built'] ) . ' UTC' ); ?></strong>
						(<?php echo esc_html( $report['seconds'] ); ?> שניות)
					</p>
					<table class="widefat striped">
						<thead>
							<tr><th>שפה</th><th>קרובים</th><th>שהיו</th><th>אמנים</th><th>סדרות</th><th>עמודים</th></tr>
						</thead>
						<tbody>
						<?php foreach ( (array) $report['counts'] as $lang => $c ) : ?>
							<tr>
								<td><code><?php echo esc_html( $lang ? $lang : '—' ); ?></code></td>
								<td><?php echo (int) $c['upcoming']; ?></td>
								<td><?php echo (int) $c['past']; ?></td>
								<td><?php echo (int) $c['artists']; ?></td>
								<td><?php echo (int) $c['series']; ?></td>
								<td><?php echo (int) $c['pages']; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px;">
					<?php wp_nonce_field( 'ipo_search_rebuild' ); ?>
					<input type="hidden" name="action" value="ipo_search_rebuild" />
					<?php submit_button( 'בנה עכשיו', 'primary', 'submit', false ); ?>
				</form>

				<p style="color:#646970;margin-top:12px;">
					הבנייה עובדת במנות ומשחררת זיכרון בין מנה למנה, כדי שגם אתר עם הרבה מאוד
					פוסטים יבנה 2014 לאט, אבל בלי ליפול.
				</p>
			</div>
		</div>
		<?php
	}
}

IPO_Search::init();
