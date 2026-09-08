<?php
/**
 * IPO Event creation logger.
 *
 * Logs every new `event` post: when, by whom, from which entry point,
 * and a short backtrace — so duplicate API imports can be investigated.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the event-create log file (under uploads).
 */
function ipo_event_create_log_path() {
	$upload = wp_upload_dir();
	$dir    = trailingslashit( $upload['basedir'] ) . 'ipo-logs';

	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
		// Block direct HTTP access if the web server serves uploads.
		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Deny from all\n" );
		}
		$index = $dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}

	return $dir . '/event-create.log';
}

/**
 * Set / merge context for the next (or current) event creation.
 * Call this before wp_insert_post when you know the source (API import, etc.).
 *
 * @param array $context
 */
function ipo_event_create_log_set_context( $context ) {
	$existing = isset( $GLOBALS['ipo_event_create_log_context'] ) && is_array( $GLOBALS['ipo_event_create_log_context'] )
		? $GLOBALS['ipo_event_create_log_context']
		: array();

	$GLOBALS['ipo_event_create_log_context'] = array_merge( $existing, (array) $context );
}

/**
 * Clear creation context after logging.
 */
function ipo_event_create_log_clear_context() {
	unset( $GLOBALS['ipo_event_create_log_context'] );
}

/**
 * Build a compact backtrace (file:line + function), skipping this logger.
 *
 * @param int $limit
 * @return array
 */
function ipo_event_create_log_backtrace( $limit = 12 ) {
	$trace  = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
	$frames = array();

	foreach ( $trace as $frame ) {
		$file = isset( $frame['file'] ) ? $frame['file'] : '';
		if ( $file && false !== strpos( $file, 'ipo-event-create-log.php' ) ) {
			continue;
		}

		$fn = '';
		if ( ! empty( $frame['class'] ) ) {
			$fn = $frame['class'] . ( $frame['type'] ?? '::' ) . ( $frame['function'] ?? '' );
		} else {
			$fn = $frame['function'] ?? '';
		}

		$short_file = $file ? str_replace( array( ABSPATH, '\\' ), array( '', '/' ), $file ) : '[internal]';
		$line       = isset( $frame['line'] ) ? (int) $frame['line'] : 0;
		$frames[]   = $short_file . ':' . $line . ' ' . $fn;

		if ( count( $frames ) >= $limit ) {
			break;
		}
	}

	return $frames;
}

/**
 * Guess creation source from request / hooks / context.
 *
 * @return string
 */
function ipo_event_create_log_detect_source() {
	if ( ! empty( $GLOBALS['ipo_event_create_log_context']['source'] ) ) {
		return (string) $GLOBALS['ipo_event_create_log_context']['source'];
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_REQUEST['action'] ) ) {
		return 'ajax:' . sanitize_key( wp_unslash( $_REQUEST['action'] ) );
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return 'rest';
	}

	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return 'cron';
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return 'wp-cli';
	}

	if ( is_admin() ) {
		return 'admin';
	}

	return 'unknown';
}

/**
 * Write one structured log entry (JSON line).
 *
 * @param array $entry
 * @return bool
 */
function ipo_event_create_log_write( $entry ) {
	$path = ipo_event_create_log_path();
	$line = wp_json_encode( $entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	if ( false === $line ) {
		$line = '{"error":"json_encode_failed","time":"' . gmdate( 'c' ) . '"}';
	}

	$result = file_put_contents( $path, $line . "\n", FILE_APPEND | LOCK_EX );

	// Mirror to PHP error_log for environments that already monitor it.
	error_log( '[IPO Event Create] ' . $line );

	return false !== $result;
}

/**
 * Log an event-creation (or skip) event.
 *
 * @param array $data
 */
function ipo_event_create_log( $data ) {
	$user_id   = get_current_user_id();
	$user      = $user_id ? get_userdata( $user_id ) : false;
	$context   = isset( $GLOBALS['ipo_event_create_log_context'] ) && is_array( $GLOBALS['ipo_event_create_log_context'] )
		? $GLOBALS['ipo_event_create_log_context']
		: array();

	$request_keys = array();
	if ( ! empty( $_REQUEST ) && is_array( $_REQUEST ) ) {
		foreach ( array_keys( $_REQUEST ) as $key ) {
			$key = (string) $key;
			// Skip noisy / sensitive keys.
			if ( in_array( $key, array( 'nonce', '_wpnonce', 'cookie', 'password', 'pwd' ), true ) ) {
				continue;
			}
			$request_keys[] = $key;
		}
	}

	$entry = array_merge(
		array(
			'time'         => gmdate( 'c' ),
			'time_local'   => current_time( 'mysql' ),
			'type'         => isset( $data['type'] ) ? $data['type'] : 'created',
			'source'       => ipo_event_create_log_detect_source(),
			'post_id'      => isset( $data['post_id'] ) ? (int) $data['post_id'] : 0,
			'post_title'   => isset( $data['post_title'] ) ? (string) $data['post_title'] : '',
			'post_status'  => isset( $data['post_status'] ) ? (string) $data['post_status'] : '',
			'api_event_id' => isset( $data['api_event_id'] ) ? (string) $data['api_event_id'] : '',
			'program_id'   => isset( $data['program_id'] ) ? (int) $data['program_id'] : 0,
			'lang'         => isset( $data['lang'] ) ? (string) $data['lang'] : '',
			'message'      => isset( $data['message'] ) ? (string) $data['message'] : '',
			'user_id'      => $user_id,
			'user_login'   => $user ? $user->user_login : '',
			'request_uri'  => isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '',
			'http_method'  => isset( $_SERVER['REQUEST_METHOD'] ) ? (string) $_SERVER['REQUEST_METHOD'] : '',
			'ajax_action'  => ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_REQUEST['action'] ) )
				? sanitize_key( wp_unslash( $_REQUEST['action'] ) )
				: '',
			'request_keys' => $request_keys,
			'is_admin'     => is_admin(),
			'doing_cron'   => defined( 'DOING_CRON' ) && DOING_CRON,
			'backtrace'    => ipo_event_create_log_backtrace(),
		),
		array( 'context' => $context ),
		isset( $data['extra'] ) && is_array( $data['extra'] ) ? array( 'extra' => $data['extra'] ) : array()
	);

	ipo_event_create_log_write( $entry );

	// Persist a short note on the post itself when we have an ID.
	if ( ! empty( $entry['post_id'] ) && in_array( $entry['type'], array( 'created', 'updated_fields' ), true ) ) {
		$existing_meta = get_post_meta( $entry['post_id'], '_ipo_event_create_log', true );
		$meta_list     = is_array( $existing_meta ) ? $existing_meta : array();
		$meta_list[]   = array(
			'time'         => $entry['time_local'],
			'type'         => $entry['type'],
			'source'       => $entry['source'],
			'api_event_id' => $entry['api_event_id'],
			'user_login'   => $entry['user_login'],
			'message'      => $entry['message'],
		);
		// Keep last 20 entries per post.
		update_post_meta( $entry['post_id'], '_ipo_event_create_log', array_slice( $meta_list, -20 ) );
	}
}

/**
 * Catch every brand-new event post, regardless of entry point.
 *
 * @param int     $post_id
 * @param WP_Post $post
 * @param bool    $update
 */
function ipo_event_create_log_on_insert( $post_id, $post, $update ) {
	if ( $update || ! $post instanceof WP_Post ) {
		return;
	}
	if ( 'event' !== $post->post_type ) {
		return;
	}
	// Skip autosaves / revisions.
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$api_id     = get_field( 'event_api_id', $post_id );
	$program_id = get_field( 'related_to_program', $post_id );
	if ( is_array( $program_id ) ) {
		$program_id = ! empty( $program_id[0] ) ? (int) $program_id[0] : 0;
	}

	$context = isset( $GLOBALS['ipo_event_create_log_context'] ) && is_array( $GLOBALS['ipo_event_create_log_context'] )
		? $GLOBALS['ipo_event_create_log_context']
		: array();

	if ( ! $api_id && ! empty( $context['api_event_id'] ) ) {
		$api_id = $context['api_event_id'];
	}
	if ( ! $program_id && ! empty( $context['program_id'] ) ) {
		$program_id = (int) $context['program_id'];
	}

	$lang = '';
	if ( ! empty( $context['lang'] ) ) {
		$lang = (string) $context['lang'];
	} elseif ( has_filter( 'wpml_element_language_code' ) ) {
		$lang = apply_filters( 'wpml_element_language_code', null, array(
			'element_id'   => $post_id,
			'element_type' => 'post_event',
		) );
	}

	ipo_event_create_log(
		array(
			'type'         => 'created',
			'post_id'      => $post_id,
			'post_title'   => $post->post_title,
			'post_status'  => $post->post_status,
			'api_event_id' => $api_id ? (string) $api_id : '',
			'program_id'   => (int) $program_id,
			'lang'         => is_string( $lang ) ? $lang : '',
			'message'      => 'New event post inserted via wp_insert_post',
			'extra'        => array_filter( array(
				'featureName' => $context['featureName'] ?? '',
				'dateTime'    => $context['dateTime'] ?? '',
				'entry_point' => $context['entry_point'] ?? '',
			) ),
		)
	);

	ipo_event_create_log_clear_context();
}
add_action( 'wp_insert_post', 'ipo_event_create_log_on_insert', 20, 3 );

/**
 * Admin UI: view recent log lines under Event Table.
 */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'event-table',
		'Event Create Log',
		'Event Create Log',
		'manage_options',
		'ipo-event-create-log',
		'ipo_event_create_log_admin_page'
	);
}, 20 );

function ipo_event_create_log_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'ipo' ) );
	}

	$path = ipo_event_create_log_path();
	$cleared = false;

	if ( isset( $_POST['ipo_clear_event_create_log'] ) && check_admin_referer( 'ipo_clear_event_create_log' ) ) {
		file_put_contents( $path, '' );
		$cleared = true;
	}

	$lines = array();
	if ( file_exists( $path ) ) {
		$content = file_get_contents( $path );
		if ( is_string( $content ) && $content !== '' ) {
			$all   = array_filter( array_map( 'trim', explode( "\n", $content ) ) );
			$lines = array_slice( array_reverse( $all ), 0, 200 );
		}
	}

	$size = file_exists( $path ) ? size_format( filesize( $path ) ) : '0 B';
	?>
	<div class="wrap">
		<h1>Event Create Log</h1>
		<p>לוג יצירות אירועים (כולל ייבוא מ-API). מוצגות 200 הרשומות האחרונות.</p>
		<p><code><?php echo esc_html( $path ); ?></code> — גודל: <?php echo esc_html( $size ); ?></p>

		<?php if ( $cleared ) : ?>
			<div class="notice notice-success"><p>הלוג נוקה.</p></div>
		<?php endif; ?>

		<form method="post" style="margin-bottom: 1em;">
			<?php wp_nonce_field( 'ipo_clear_event_create_log' ); ?>
			<button type="submit" name="ipo_clear_event_create_log" class="button" onclick="return confirm('לנקות את הלוג?');">נקה לוג</button>
		</form>

		<table class="widefat striped">
			<thead>
				<tr>
					<th>זמן</th>
					<th>סוג</th>
					<th>מקור</th>
					<th>Post ID</th>
					<th>API ID</th>
					<th>כותרת</th>
					<th>משתמש</th>
					<th>הודעה / backtrace</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $lines ) ) : ?>
				<tr><td colspan="8">אין רשומות עדיין.</td></tr>
			<?php else : ?>
				<?php foreach ( $lines as $line ) :
					$entry = json_decode( $line, true );
					if ( ! is_array( $entry ) ) {
						continue;
					}
					$bt = '';
					if ( ! empty( $entry['backtrace'] ) && is_array( $entry['backtrace'] ) ) {
						$bt = implode( "\n", array_slice( $entry['backtrace'], 0, 6 ) );
					}
					?>
					<tr>
						<td style="white-space:nowrap;"><?php echo esc_html( $entry['time_local'] ?? $entry['time'] ?? '' ); ?></td>
						<td><?php echo esc_html( $entry['type'] ?? '' ); ?></td>
						<td><?php echo esc_html( $entry['source'] ?? '' ); ?><?php
						if ( ! empty( $entry['ajax_action'] ) ) {
							echo '<br><small>' . esc_html( $entry['ajax_action'] ) . '</small>';
						}
						?></td>
						<td><?php
						$pid = (int) ( $entry['post_id'] ?? 0 );
						if ( $pid ) {
							echo '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html( (string) $pid ) . '</a>';
						} else {
							echo '—';
						}
						?></td>
						<td><?php echo esc_html( (string) ( $entry['api_event_id'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( $entry['post_title'] ?? '' ); ?></td>
						<td><?php echo esc_html( $entry['user_login'] ?? '' ); ?></td>
						<td>
							<div><?php echo esc_html( $entry['message'] ?? '' ); ?></div>
							<?php if ( $bt ) : ?>
								<details><summary>backtrace</summary><pre style="max-width:420px;white-space:pre-wrap;font-size:11px;"><?php echo esc_html( $bt ); ?></pre></details>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
