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
