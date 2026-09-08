<?php
/**
 * REST + lightweight MCP endpoints for IPO AI Bridge.
 *
 * Read-only database access for Cursor / Claude via Bearer token.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_AI_REST {

	const NS = 'ipo-ai/v1';

	/** Token IDs that are allowed to write. Managed on the AI Bridge admin page. */
	const WRITE_TOKENS_OPTION = 'ipo_ai_bridge_write_tokens';

	/** Only these extensions may be written. Everything else is refused. */
	const WRITABLE_EXTENSIONS = array( 'css', 'js', 'php', 'json', 'txt', 'md', 'svg', 'html' );

	/** Largest file this endpoint will accept, in bytes. */
	const MAX_WRITE_BYTES = 2097152;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		$auth = array( __CLASS__, 'permission_check' );

		register_rest_route(
			self::NS,
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'status' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NS,
			'/schema',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'schema' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NS,
			'/tables',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'tables' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NS,
			'/table/(?P<table>[a-zA-Z0-9_]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'describe_table' ),
				'permission_callback' => $auth,
				'args'                => array(
					'table' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/query',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'query' ),
				'permission_callback' => $auth,
				'args'                => array(
					'sql' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'limit' => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 100,
					),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/wp-query',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'wp_query' ),
				'permission_callback' => $auth,
			)
		);

		// Lightweight MCP JSON-RPC (tools/list + tools/call) for Cursor / Claude HTTP MCP.
		register_rest_route(
			self::NS,
			'/mcp',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'mcp' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'mcp_info' ),
					'permission_callback' => $auth,
				),
			)
		);

		// --- Theme file access. Reading needs any token; writing needs one that was
		// --- explicitly granted write access on the AI Bridge admin page.
		$write_auth = array( __CLASS__, 'write_permission_check' );

		register_rest_route(
			self::NS,
			'/files',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_files' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NS,
			'/file',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'read_file' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'write_file' ),
					'permission_callback' => $write_auth,
				),
			)
		);

		register_rest_route(
			self::NS,
			'/file/revert',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'revert_file' ),
				'permission_callback' => $write_auth,
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public static function permission_check( $request ) {
		$token = IPO_AI_Tokens::bearer_from_request();
		$auth  = IPO_AI_Tokens::authenticate( $token );

		if ( ! $auth ) {
			return new WP_Error(
				'ipo_ai_unauthorized',
				'Invalid or missing IPO AI Bridge token.',
				array( 'status' => 401 )
			);
		}

		$request->set_param( '_ipo_ai_token', $auth );
		return true;
	}

	/**
	 * Same as permission_check, plus the token must be on the write list.
	 *
	 * Write access is off for every token until it is granted one by one on the
	 * admin page, so a leaked read token cannot be used to change files.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public static function write_permission_check( $request ) {
		$allowed = self::permission_check( $request );

		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}

		$token      = $request->get_param( '_ipo_ai_token' );
		$write_ids  = get_option( self::WRITE_TOKENS_OPTION, array() );
		$write_ids  = is_array( $write_ids ) ? $write_ids : array();

		if ( empty( $token['id'] ) || ! in_array( $token['id'], $write_ids, true ) ) {
			return new WP_Error(
				'ipo_ai_read_only',
				'This token is read-only. Grant it write access under Tools -> AI Bridge.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Resolve a theme-relative path to a real one inside the child theme.
	 *
	 * Everything hangs off this: the path is resolved against the real theme
	 * directory and then checked to still be inside it, so '../', symlinks and
	 * absolute paths cannot reach anything else on the server.
	 *
	 * @param string $relative Path relative to the child theme root.
	 * @param bool   $must_exist Whether the file has to exist already.
	 * @return string|WP_Error Absolute path.
	 */
	protected static function resolve_theme_path( $relative, $must_exist = true ) {
		$relative = is_string( $relative ) ? trim( $relative ) : '';
		$relative = ltrim( str_replace( '\\', '/', $relative ), '/' );

		if ( '' === $relative ) {
			return new WP_Error( 'ipo_ai_bad_path', 'A path is required.', array( 'status' => 400 ) );
		}

		$root = realpath( get_stylesheet_directory() );
		if ( ! $root ) {
			return new WP_Error( 'ipo_ai_no_theme', 'Cannot resolve the theme directory.', array( 'status' => 500 ) );
		}

		$target = $root . '/' . $relative;
		$real   = realpath( $target );

		if ( $must_exist ) {
			if ( ! $real || ! is_file( $real ) ) {
				return new WP_Error( 'ipo_ai_not_found', 'No such file: ' . $relative, array( 'status' => 404 ) );
			}
		} else {
			// A new file has no realpath yet, so verify its parent directory instead.
			$parent = realpath( dirname( $target ) );
			if ( ! $parent ) {
				return new WP_Error( 'ipo_ai_no_dir', 'Directory does not exist for: ' . $relative, array( 'status' => 400 ) );
			}
			if ( 0 !== strpos( $parent . '/', $root . '/' ) && $parent !== $root ) {
				return new WP_Error( 'ipo_ai_outside', 'Path escapes the theme directory.', array( 'status' => 403 ) );
			}
			return $parent . '/' . basename( $target );
		}

		if ( 0 !== strpos( $real, $root . DIRECTORY_SEPARATOR ) && $real !== $root ) {
			return new WP_Error( 'ipo_ai_outside', 'Path escapes the theme directory.', array( 'status' => 403 ) );
		}

		return $real;
	}

	/**
	 * @param string $path Absolute path.
	 * @return bool
	 */
	protected static function is_writable_extension( $path ) {
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		return in_array( $ext, self::WRITABLE_EXTENSIONS, true );
	}

	/**
	 * Copy the current contents aside before overwriting.
	 *
	 * Backups live in uploads so a bad write can always be undone, and they are
	 * what /file/revert restores from.
	 *
	 * @param string $path Absolute path of the file about to change.
	 * @return string|false Backup path, or false when there was nothing to back up.
	 */
	protected static function backup_file( $path ) {
		if ( ! file_exists( $path ) ) {
			return false;
		}

		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'ipo-ai-backups';

		if ( ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		// Keep the folder from being browsable.
		if ( ! file_exists( $dir . '/index.php' ) ) {
			file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" );
		}

		$name   = str_replace( '/', '__', ltrim( str_replace( realpath( get_stylesheet_directory() ), '', $path ), '/\\' ) );
		$backup = $dir . '/' . $name . '.' . gmdate( 'Ymd-His' ) . '.bak';

		return copy( $path, $backup ) ? $backup : false;
	}

	/**
	 * Reject PHP that would fatal the site before it ever reaches disk.
	 *
	 * token_get_all() with TOKEN_PARSE runs the real parser, so a syntax error
	 * surfaces here as a ParseError instead of as a white screen.
	 *
	 * @param string $content File contents.
	 * @return true|WP_Error
	 */
	protected static function validate_php_syntax( $content ) {
		try {
			token_get_all( $content, TOKEN_PARSE );
		} catch ( ParseError $e ) {
			return new WP_Error(
				'ipo_ai_php_parse_error',
				'Refused: PHP syntax error - ' . $e->getMessage(),
				array( 'status' => 422 )
			);
		}

		return true;
	}

	/**
	 * GET /files?dir=assets/styles — list files in a theme directory.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function list_files( $request ) {
		$dir  = (string) $request->get_param( 'dir' );
		$root = realpath( get_stylesheet_directory() );

		if ( '' === trim( $dir ) ) {
			$target = $root;
		} else {
			$target = realpath( $root . '/' . ltrim( str_replace( '\\', '/', $dir ), '/' ) );
			if ( ! $target || ! is_dir( $target ) ) {
				return new WP_Error( 'ipo_ai_not_found', 'No such directory: ' . $dir, array( 'status' => 404 ) );
			}
			if ( 0 !== strpos( $target, $root . DIRECTORY_SEPARATOR ) && $target !== $root ) {
				return new WP_Error( 'ipo_ai_outside', 'Path escapes the theme directory.', array( 'status' => 403 ) );
			}
		}

		$entries = array();

		foreach ( (array) scandir( $target ) as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$full = $target . '/' . $entry;

			$entries[] = array(
				'name'     => $entry,
				'type'     => is_dir( $full ) ? 'dir' : 'file',
				'size'     => is_file( $full ) ? filesize( $full ) : null,
				'modified' => gmdate( 'Y-m-d H:i:s', (int) filemtime( $full ) ) . ' UTC',
				'writable' => is_file( $full ) ? ( is_writable( $full ) && self::is_writable_extension( $full ) ) : null,
			);
		}

		return rest_ensure_response(
			array(
				'dir'     => '' === trim( $dir ) ? '/' : $dir,
				'count'   => count( $entries ),
				'entries' => $entries,
			)
		);
	}

	/**
	 * GET /file?path=assets/styles/ipo-custom.css
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function read_file( $request ) {
		$relative = (string) $request->get_param( 'path' );
		$path     = self::resolve_theme_path( $relative, true );

		if ( is_wp_error( $path ) ) {
			return $path;
		}

		$content = file_get_contents( $path );

		if ( false === $content ) {
			return new WP_Error( 'ipo_ai_read_failed', 'Could not read: ' . $relative, array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'path'     => $relative,
				'bytes'    => strlen( $content ),
				'sha1'     => sha1( $content ),
				'modified' => gmdate( 'Y-m-d H:i:s', (int) filemtime( $path ) ) . ' UTC',
				'content'  => $content,
			)
		);
	}

	/**
	 * POST /file  { path, content, expect_sha1? }
	 *
	 * Pass expect_sha1 to make the write conditional on the file still holding
	 * what you last read — that way two writers cannot silently clobber each other.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function write_file( $request ) {
		$relative = (string) $request->get_param( 'path' );
		$content  = $request->get_param( 'content' );
		$create   = (bool) $request->get_param( 'create' );

		if ( ! is_string( $content ) ) {
			return new WP_Error( 'ipo_ai_bad_content', 'content must be a string.', array( 'status' => 400 ) );
		}

		if ( strlen( $content ) > self::MAX_WRITE_BYTES ) {
			return new WP_Error(
				'ipo_ai_too_large',
				sprintf( 'Refused: %d bytes exceeds the %d byte limit.', strlen( $content ), self::MAX_WRITE_BYTES ),
				array( 'status' => 413 )
			);
		}

		$path = self::resolve_theme_path( $relative, ! $create );

		if ( is_wp_error( $path ) ) {
			return $path;
		}

		if ( ! self::is_writable_extension( $path ) ) {
			return new WP_Error(
				'ipo_ai_bad_extension',
				'Refused: only these extensions may be written - ' . implode( ', ', self::WRITABLE_EXTENSIONS ),
				array( 'status' => 403 )
			);
		}

		if ( 'php' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
			$valid = self::validate_php_syntax( $content );
			if ( is_wp_error( $valid ) ) {
				return $valid;
			}
		}

		$existed = file_exists( $path );

		if ( $existed && ! is_writable( $path ) ) {
			return new WP_Error( 'ipo_ai_not_writable', 'File is not writable: ' . $relative, array( 'status' => 403 ) );
		}

		$expect = $request->get_param( 'expect_sha1' );

		if ( $expect && $existed ) {
			$current = sha1( (string) file_get_contents( $path ) );
			if ( $current !== $expect ) {
				return new WP_Error(
					'ipo_ai_conflict',
					'Refused: the file changed since you read it (now ' . $current . ').',
					array( 'status' => 409 )
				);
			}
		}

		$backup  = self::backup_file( $path );
		$written = file_put_contents( $path, $content );

		if ( false === $written ) {
			return new WP_Error( 'ipo_ai_write_failed', 'Could not write: ' . $relative, array( 'status' => 500 ) );
		}

		clearstatcache( true, $path );

		return rest_ensure_response(
			array(
				'ok'      => true,
				'path'    => $relative,
				'created' => ! $existed,
				'bytes'   => $written,
				'sha1'    => sha1( $content ),
				'backup'  => $backup ? basename( $backup ) : null,
			)
		);
	}

	/**
	 * POST /file/revert  { path }
	 *
	 * Restores the most recent backup taken for that file.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function revert_file( $request ) {
		$relative = (string) $request->get_param( 'path' );
		$path     = self::resolve_theme_path( $relative, true );

		if ( is_wp_error( $path ) ) {
			return $path;
		}

		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'ipo-ai-backups';
		$name    = str_replace( '/', '__', ltrim( str_replace( realpath( get_stylesheet_directory() ), '', $path ), '/\\' ) );
		$matches = glob( $dir . '/' . $name . '.*.bak' );

		if ( empty( $matches ) ) {
			return new WP_Error( 'ipo_ai_no_backup', 'No backup found for: ' . $relative, array( 'status' => 404 ) );
		}

		sort( $matches );
		$latest = end( $matches );

		if ( ! copy( $latest, $path ) ) {
			return new WP_Error( 'ipo_ai_revert_failed', 'Could not restore: ' . $relative, array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'ok'       => true,
				'path'     => $relative,
				'restored' => basename( $latest ),
			)
		);
	}

	/**
	 * @return WP_REST_Response
	 */
	public static function status() {
		global $wpdb;

		return rest_ensure_response(
			array(
				'ok'           => true,
				'site'         => get_bloginfo( 'name' ),
				'url'          => home_url( '/' ),
				'wp_version'   => get_bloginfo( 'version' ),
				'db_prefix'    => $wpdb->prefix,
				'php_version'  => PHP_VERSION,
				'mode'         => 'read-only',
				'endpoints'    => array(
					'status'    => rest_url( self::NS . '/status' ),
					'schema'    => rest_url( self::NS . '/schema' ),
					'tables'    => rest_url( self::NS . '/tables' ),
					'query'     => rest_url( self::NS . '/query' ),
					'wp-query'  => rest_url( self::NS . '/wp-query' ),
					'mcp'       => rest_url( self::NS . '/mcp' ),
				),
			)
		);
	}

	/**
	 * @return WP_REST_Response
	 */
	public static function schema() {
		global $wpdb;

		$tables = $wpdb->get_col( 'SHOW TABLES' );
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$types = array();
		foreach ( $post_types as $pt ) {
			$types[] = array(
				'name'  => $pt->name,
				'label' => $pt->label,
			);
		}

		return rest_ensure_response(
			array(
				'db_name'    => DB_NAME,
				'db_prefix'  => $wpdb->prefix,
				'table_count'=> is_array( $tables ) ? count( $tables ) : 0,
				'tables'     => $tables,
				'post_types' => $types,
			)
		);
	}

	/**
	 * @return WP_REST_Response
	 */
	public static function tables() {
		global $wpdb;
		$tables = $wpdb->get_col( 'SHOW TABLES' );
		return rest_ensure_response(
			array(
				'tables' => is_array( $tables ) ? $tables : array(),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function describe_table( $request ) {
		global $wpdb;

		$table = $request['table'];
		if ( ! self::table_exists( $table ) ) {
			return new WP_Error( 'ipo_ai_unknown_table', 'Table not found.', array( 'status' => 404 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name validated against SHOW TABLES.
		$columns = $wpdb->get_results( "DESCRIBE `{$table}`", ARRAY_A );

		return rest_ensure_response(
			array(
				'table'   => $table,
				'columns' => $columns,
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function query( $request ) {
		$sql   = (string) $request['sql'];
		$limit = (int) $request['limit'];
		if ( $limit < 1 ) {
			$limit = 100;
		}
		if ( $limit > 500 ) {
			$limit = 500;
		}

		$safe = self::validate_readonly_sql( $sql, $limit );
		if ( is_wp_error( $safe ) ) {
			return $safe;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- validated read-only SELECT/SHOW/DESCRIBE.
		$rows = $wpdb->get_results( $safe, ARRAY_A );

		if ( null === $rows && $wpdb->last_error ) {
			return new WP_Error(
				'ipo_ai_sql_error',
				$wpdb->last_error,
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response(
			array(
				'sql'   => $safe,
				'count' => is_array( $rows ) ? count( $rows ) : 0,
				'rows'  => $rows ? $rows : array(),
			)
		);
	}

	/**
	 * Limited WP_Query helper (read-only content inspection).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function wp_query( $request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) || array() === $params ) {
			$params = $request->get_params();
		}
		if ( ! is_array( $params ) ) {
			$params = array();
		}

		return rest_ensure_response( self::run_wp_query( $params ) );
	}

	/**
	 * @param array<string,mixed> $params Query params.
	 * @return array<string,mixed>
	 */
	public static function run_wp_query( array $params ) {
		$allowed = array(
			'post_type',
			'post_status',
			'posts_per_page',
			'paged',
			's',
			'p',
			'name',
			'orderby',
			'order',
			'meta_key',
			'meta_value',
			'cat',
			'tag',
			'tax_query',
			'meta_query',
			'date_query',
			'author',
			'post__in',
			'post__not_in',
		);

		$query_args = array( 'no_found_rows' => false );
		foreach ( $allowed as $key ) {
			if ( array_key_exists( $key, $params ) ) {
				$query_args[ $key ] = $params[ $key ];
			}
		}

		if ( empty( $query_args['posts_per_page'] ) ) {
			$query_args['posts_per_page'] = 20;
		}
		$query_args['posts_per_page'] = min( 100, max( 1, (int) $query_args['posts_per_page'] ) );

		if ( empty( $query_args['post_status'] ) ) {
			$query_args['post_status'] = 'any';
		}

		$q     = new WP_Query( $query_args );
		$items = array();

		foreach ( $q->posts as $post ) {
			$items[] = array(
				'ID'            => $post->ID,
				'post_type'     => $post->post_type,
				'post_status'   => $post->post_status,
				'post_title'    => $post->post_title,
				'post_name'     => $post->post_name,
				'post_date'     => $post->post_date,
				'post_modified' => $post->post_modified,
				'guid'          => $post->guid,
				'permalink'     => get_permalink( $post ),
			);
		}

		return array(
			'found' => (int) $q->found_posts,
			'count' => count( $items ),
			'posts' => $items,
		);
	}

	/**
	 * @return WP_REST_Response
	 */
	public static function mcp_info() {
		return rest_ensure_response(
			array(
				'name'        => 'IPO AI Bridge',
				'transport'   => 'json-rpc',
				'description' => 'POST JSON-RPC 2.0 methods: initialize, tools/list, tools/call, ping',
				'endpoint'    => rest_url( self::NS . '/mcp' ),
			)
		);
	}

	/**
	 * Minimal MCP-compatible JSON-RPC handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function mcp( $request ) {
		$body = $request->get_json_params();
		if ( ! is_array( $body ) ) {
			return new WP_Error( 'ipo_ai_bad_json', 'Expected JSON-RPC body.', array( 'status' => 400 ) );
		}

		// Batch support.
		if ( array_keys( $body ) === range( 0, count( $body ) - 1 ) ) {
			$out = array();
			foreach ( $body as $msg ) {
				$out[] = self::mcp_handle_one( is_array( $msg ) ? $msg : array() );
			}
			return rest_ensure_response( $out );
		}

		return rest_ensure_response( self::mcp_handle_one( $body ) );
	}

	/**
	 * @param array<string,mixed> $msg JSON-RPC message.
	 * @return array<string,mixed>
	 */
	protected static function mcp_handle_one( array $msg ) {
		$id     = array_key_exists( 'id', $msg ) ? $msg['id'] : null;
		$method = isset( $msg['method'] ) ? (string) $msg['method'] : '';
		$params = isset( $msg['params'] ) && is_array( $msg['params'] ) ? $msg['params'] : array();

		if ( '' === $method ) {
			return self::mcp_error( $id, -32600, 'Invalid Request' );
		}

		switch ( $method ) {
			case 'initialize':
				return self::mcp_result(
					$id,
					array(
						'protocolVersion' => '2024-11-05',
						'capabilities'    => array(
							'tools' => array( 'listChanged' => false ),
						),
						'serverInfo'      => array(
							'name'    => 'ipo-ai-bridge',
							'version' => '1.0.0',
						),
					)
				);

			case 'notifications/initialized':
			case 'initialized':
				return self::mcp_result( $id, new stdClass() );

			case 'ping':
				return self::mcp_result( $id, new stdClass() );

			case 'tools/list':
				return self::mcp_result(
					$id,
					array(
						'tools' => self::mcp_tools(),
					)
				);

			case 'tools/call':
				$name = isset( $params['name'] ) ? (string) $params['name'] : '';
				$args = isset( $params['arguments'] ) && is_array( $params['arguments'] ) ? $params['arguments'] : array();
				try {
					$text = self::mcp_call_tool( $name, $args );
					return self::mcp_result(
						$id,
						array(
							'content' => array(
								array(
									'type' => 'text',
									'text' => $text,
								),
							),
							'isError' => false,
						)
					);
				} catch ( Exception $e ) {
					return self::mcp_result(
						$id,
						array(
							'content' => array(
								array(
									'type' => 'text',
									'text' => $e->getMessage(),
								),
							),
							'isError' => true,
						)
					);
				}

			default:
				return self::mcp_error( $id, -32601, 'Method not found: ' . $method );
		}
	}

	/**
	 * @return array<int, array<string,mixed>>
	 */
	protected static function mcp_tools() {
		return array(
			array(
				'name'        => 'db_status',
				'description' => 'Site and DB connection status (read-only).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => new stdClass(),
				),
			),
			array(
				'name'        => 'db_schema',
				'description' => 'List WordPress DB tables and public post types.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => new stdClass(),
				),
			),
			array(
				'name'        => 'db_describe_table',
				'description' => 'DESCRIBE a single MySQL table (must exist on this site).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'table' => array(
							'type'        => 'string',
							'description' => 'Exact table name, e.g. wp_posts',
						),
					),
					'required'   => array( 'table' ),
				),
			),
			array(
				'name'        => 'db_query',
				'description' => 'Run a read-only SQL query (SELECT / SHOW / DESCRIBE / EXPLAIN only).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'sql'   => array(
							'type'        => 'string',
							'description' => 'Read-only SQL statement',
						),
						'limit' => array(
							'type'        => 'integer',
							'description' => 'Max rows (default 100, max 500)',
						),
					),
					'required'   => array( 'sql' ),
				),
			),
			array(
				'name'        => 'wp_query',
				'description' => 'Run a limited WP_Query and return post summaries.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'post_type'      => array( 'type' => 'string' ),
						'post_status'    => array( 'type' => 'string' ),
						'posts_per_page' => array( 'type' => 'integer' ),
						's'              => array( 'type' => 'string' ),
						'paged'          => array( 'type' => 'integer' ),
					),
				),
			),
			array(
				'name'        => 'fs_list',
				'description' => 'List files in a child-theme directory.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'dir' => array( 'type' => 'string', 'description' => 'Theme-relative directory, e.g. assets/styles' ),
					),
				),
			),
			array(
				'name'        => 'fs_read',
				'description' => 'Read a child-theme file.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'path' => array( 'type' => 'string', 'description' => 'Theme-relative path, e.g. assets/styles/ipo-custom.css' ),
					),
					'required'   => array( 'path' ),
				),
			),
			array(
				'name'        => 'fs_write',
				'description' => 'Write a child-theme file. Needs a token with write access. Backs up the previous version and refuses PHP that does not parse.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'path'        => array( 'type' => 'string', 'description' => 'Theme-relative path' ),
						'content'     => array( 'type' => 'string', 'description' => 'Full new contents of the file' ),
						'expect_sha1' => array( 'type' => 'string', 'description' => 'Optional: only write if the file still has this sha1' ),
						'create'      => array( 'type' => 'boolean', 'description' => 'Allow creating a file that does not exist yet' ),
					),
					'required'   => array( 'path', 'content' ),
				),
			),
			array(
				'name'        => 'fs_revert',
				'description' => 'Restore the most recent backup of a child-theme file.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'path' => array( 'type' => 'string', 'description' => 'Theme-relative path' ),
					),
					'required'   => array( 'path' ),
				),
			),
		);
	}

	/**
	 * @param string               $name Tool name.
	 * @param array<string,mixed>  $args Arguments.
	 * @return string JSON text payload.
	 * @throws Exception On failure.
	 */
	protected static function mcp_call_tool( $name, array $args ) {
		switch ( $name ) {
			case 'db_status':
				$response = self::status();
				return wp_json_encode( $response->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

			case 'db_schema':
				$response = self::schema();
				return wp_json_encode( $response->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

			case 'db_describe_table':
				$req = new WP_REST_Request( 'GET' );
				$req->set_param( 'table', isset( $args['table'] ) ? $args['table'] : '' );
				$result = self::describe_table( $req );
				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}
				return wp_json_encode( $result->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

			case 'db_query':
				$req = new WP_REST_Request( 'POST' );
				$req->set_param( 'sql', isset( $args['sql'] ) ? $args['sql'] : '' );
				$req->set_param( 'limit', isset( $args['limit'] ) ? (int) $args['limit'] : 100 );
				$result = self::query( $req );
				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}
				return wp_json_encode( $result->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

			case 'wp_query':
				return wp_json_encode( self::run_wp_query( $args ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

			case 'fs_list':
			case 'fs_read':
			case 'fs_write':
			case 'fs_revert':
				$req = new WP_REST_Request( 'fs_list' === $name || 'fs_read' === $name ? 'GET' : 'POST' );

				foreach ( $args as $key => $value ) {
					$req->set_param( $key, $value );
				}

				// tools/call carries the same Bearer token the MCP request was
				// authenticated with, so re-run the permission check for writes.
				if ( 'fs_write' === $name || 'fs_revert' === $name ) {
					$allowed = self::write_permission_check( $req );
					if ( is_wp_error( $allowed ) ) {
						throw new Exception( $allowed->get_error_message() );
					}
				}

				$map    = array(
					'fs_list'   => 'list_files',
					'fs_read'   => 'read_file',
					'fs_write'  => 'write_file',
					'fs_revert' => 'revert_file',
				);
				$result = call_user_func( array( __CLASS__, $map[ $name ] ), $req );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				return wp_json_encode( $result->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

			default:
				throw new Exception( 'Unknown tool: ' . $name );
		}
	}

	/**
	 * @param mixed                $id Result id.
	 * @param array|object|mixed   $result Result payload.
	 * @return array<string,mixed>
	 */
	protected static function mcp_result( $id, $result ) {
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => $result,
		);
	}

	/**
	 * @param mixed  $id Error id.
	 * @param int    $code Error code.
	 * @param string $message Message.
	 * @return array<string,mixed>
	 */
	protected static function mcp_error( $id, $code, $message ) {
		return array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => array(
				'code'    => $code,
				'message' => $message,
			),
		);
	}

	/**
	 * @param string $table Table name.
	 * @return bool
	 */
	protected static function table_exists( $table ) {
		global $wpdb;
		$tables = $wpdb->get_col( 'SHOW TABLES' );
		return is_array( $tables ) && in_array( $table, $tables, true );
	}

	/**
	 * Validate and normalize a read-only SQL statement.
	 *
	 * @param string $sql   Raw SQL.
	 * @param int    $limit Max rows for SELECT.
	 * @return string|WP_Error
	 */
	public static function validate_readonly_sql( $sql, $limit = 100 ) {
		$sql = trim( $sql );
		$sql = preg_replace( '/^\xEF\xBB\xBF/', '', $sql );

		// Strip block/line comments (simple).
		$sql = preg_replace( '/\/\*.*?\*\//s', ' ', $sql );
		$sql = preg_replace( '/--.*?$/m', ' ', $sql );
		$sql = preg_replace( '/#.*?$/m', ' ', $sql );
		$sql = trim( $sql );

		if ( '' === $sql ) {
			return new WP_Error( 'ipo_ai_empty_sql', 'Empty SQL.', array( 'status' => 400 ) );
		}

		// Single statement only.
		$trimmed = rtrim( $sql, "; \t\n\r\0\x0B" );
		if ( false !== strpos( $trimmed, ';' ) ) {
			return new WP_Error( 'ipo_ai_multi_sql', 'Multiple SQL statements are not allowed.', array( 'status' => 400 ) );
		}
		$sql = $trimmed;

		$upper = strtoupper( ltrim( $sql ) );
		$allowed_starts = array( 'SELECT', 'SHOW', 'DESCRIBE', 'DESC ', 'EXPLAIN' );
		$ok = false;
		foreach ( $allowed_starts as $start ) {
			if ( 0 === strpos( $upper, $start ) ) {
				$ok = true;
				break;
			}
		}
		if ( ! $ok ) {
			return new WP_Error(
				'ipo_ai_not_readonly',
				'Only SELECT, SHOW, DESCRIBE, and EXPLAIN are allowed.',
				array( 'status' => 400 )
			);
		}

		$blocked_pattern = '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE|REPLACE|GRANT|REVOKE|CALL|LOAD_FILE|OUTFILE|DUMPFILE|HANDLER|MERGE|RENAME|SLEEP|BENCHMARK)\b/i';
		if ( preg_match( $blocked_pattern, $sql ) ) {
			return new WP_Error( 'ipo_ai_forbidden_sql', 'Forbidden keyword in SQL.', array( 'status' => 400 ) );
		}
		if ( preg_match( '/\bINTO\s+(OUTFILE|DUMPFILE)\b/i', $sql ) ) {
			return new WP_Error( 'ipo_ai_forbidden_sql', 'INTO OUTFILE/DUMPFILE is not allowed.', array( 'status' => 400 ) );
		}

		// Enforce LIMIT on SELECT (not on SHOW/DESCRIBE/EXPLAIN).
		if ( 0 === strpos( $upper, 'SELECT' ) && ! preg_match( '/\bLIMIT\s+\d+/i', $sql ) ) {
			$sql .= ' LIMIT ' . (int) $limit;
		}

		return $sql;
	}
}
