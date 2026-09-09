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

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_shortcode( 'ipo_search', array( __CLASS__, 'shortcode' ) );

		// Rebuild triggers.
		add_action( IPO_Search_Index::CRON_HOOK, array( 'IPO_Search_Index', 'build_all' ) );
		add_action( 'save_post', array( __CLASS__, 'schedule_rebuild_on_save' ), 10, 2 );
		add_action( 'admin_post_ipo_search_rebuild', array( __CLASS__, 'handle_manual_rebuild' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );

		if ( ! wp_next_scheduled( IPO_Search_Index::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', IPO_Search_Index::CRON_HOOK );
		}
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

	/**
	 * Queue a rebuild after content that the index covers is edited.
	 *
	 * Debounced through a single cron event rather than rebuilt inline: saving
	 * a program should not make the editor wait for ~700 programs to be walked.
	 *
	 * @param int     $post_id Post.
	 * @param WP_Post $post    Post object.
	 */
	public static function schedule_rebuild_on_save( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! $post || ! in_array( $post->post_type, array( 'program', 'event', 'artist', 'serie', 'page' ), true ) ) {
			return;
		}

		if ( wp_next_scheduled( IPO_Search_Index::CRON_HOOK ) ) {
			// Already queued; let that one pick the change up.
			return;
		}

		wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, IPO_Search_Index::CRON_HOOK );
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

		$report = IPO_Search_Index::report();
		?>
		<div class="wrap" dir="rtl">
			<h1>אינדקס החיפוש</h1>

			<?php if ( ! empty( $_GET['rebuilt'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>האינדקס נבנה מחדש.</p></div>
			<?php endif; ?>

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
					נבנה מחדש אוטומטית פעמיים ביום, וגם כ־5 דקות אחרי עריכה של תוכנית, אירוע, אמן, סדרה או עמוד.
				</p>
			</div>
		</div>
		<?php
	}
}

IPO_Search::init();
