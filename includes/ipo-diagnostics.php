<?php
/**
 * Diagnostics for blank pages.
 *
 * Since the Gravity Forms 3.1.2 auto-update (2026-11-13) every page that renders
 * a form from inside an ACF field answers 200 with an empty body, and there is
 * no live PHP error log. This records, to a theme file that cannot be opened
 * over HTTP (it starts with <?php exit; ?>):
 *   - PHP fatal errors (message, file, line, memory),
 *   - front-end GET pages that are about to be sent empty, with the output
 *     buffer stack at shutdown,
 *   - HTML filters whose regex failed (preg_last_error_msg()).
 *
 * Read it with the AI Bridge (fs_read ipo-diagnostics-log.php) or over FTP.
 * Capped at 300 KB. Safe to delete the log file at any time.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ipo_diag_log' ) ) {
	function ipo_diag_log( array $entry ) {
		$file = get_stylesheet_directory() . '/ipo-diagnostics-log.php';
		if ( file_exists( $file ) && filesize( $file ) > 300000 ) {
			return;
		}
		if ( ! file_exists( $file ) ) {
			@file_put_contents( $file, "<?php exit; ?>\n" ); // phpcs:ignore
		}
		$entry = array_merge(
			array(
				't'   => gmdate( 'Y-m-d H:i:s' ),
				'uri' => isset( $_SERVER['REQUEST_URI'] ) ? substr( (string) $_SERVER['REQUEST_URI'], 0, 300 ) : '',
			),
			$entry
		);
		@file_put_contents( $file, json_encode( $entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR ) . "\n", FILE_APPEND | LOCK_EX ); // phpcs:ignore
	}
}

register_shutdown_function(
	function () {
		$error = error_get_last();
		$fatal = $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true );

		$buffers = array();
		$used    = 0;
		foreach ( (array) ob_get_status( true ) as $status ) {
			$buffers[] = array( isset( $status['name'] ) ? $status['name'] : '', isset( $status['buffer_used'] ) ? $status['buffer_used'] : 0 );
			$used     += isset( $status['buffer_used'] ) ? (int) $status['buffer_used'] : 0;
		}

		$empty_page = function_exists( 'did_action' )
			&& did_action( 'template_redirect' )
			&& ! is_admin()
			&& ! wp_doing_ajax()
			&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			&& isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD']
			&& 200 === http_response_code()
			&& $used < 2000
			&& ! headers_sent();

		if ( $fatal || $empty_page ) {
			ipo_diag_log(
				array(
					'kind'       => $fatal ? 'fatal' : 'empty_page',
					'error'      => $error,
					'buffers'    => $buffers,
					'ob_level'   => ob_get_level(),
					'mem_peak'   => memory_get_peak_usage( true ),
					'mem_limit'  => ini_get( 'memory_limit' ),
					'preg_error' => preg_last_error_msg(),
				)
			);
		}
	}
);
