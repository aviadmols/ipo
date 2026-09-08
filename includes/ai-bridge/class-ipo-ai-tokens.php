<?php
/**
 * Secure API token storage for IPO AI Bridge.
 *
 * Tokens are shown in plaintext only once at creation.
 * Only a hash is stored in the database.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_AI_Tokens {

	const OPTION_KEY = 'ipo_ai_bridge_tokens';

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function all() {
		$tokens = get_option( self::OPTION_KEY, array() );
		return is_array( $tokens ) ? $tokens : array();
	}

	/**
	 * Create a new token. Returns plaintext once; only hash is stored.
	 *
	 * @param string $label Human label (e.g. "Cursor", "Claude").
	 * @return array{id:string,label:string,token:string,created:int,last_used:int|null}|WP_Error
	 */
	public static function create( $label = '' ) {
		$label = sanitize_text_field( $label );
		if ( '' === $label ) {
			$label = 'AI Client';
		}

		$plaintext = 'ipo_' . bin2hex( random_bytes( 24 ) );
		$id        = wp_generate_uuid4();

		$record = array(
			'id'        => $id,
			'label'     => $label,
			'hash'      => wp_hash_password( $plaintext ),
			'prefix'    => substr( $plaintext, 0, 12 ),
			'created'   => time(),
			'last_used' => null,
			'created_by'=> get_current_user_id(),
		);

		$tokens   = self::all();
		$tokens[] = $record;
		update_option( self::OPTION_KEY, $tokens, false );

		return array(
			'id'        => $id,
			'label'     => $label,
			'token'     => $plaintext,
			'created'   => $record['created'],
			'last_used' => null,
		);
	}

	/**
	 * @param string $id Token UUID.
	 * @return bool
	 */
	public static function revoke( $id ) {
		$id     = sanitize_text_field( $id );
		$tokens = self::all();
		$next   = array();
		$found  = false;

		foreach ( $tokens as $token ) {
			if ( isset( $token['id'] ) && $token['id'] === $id ) {
				$found = true;
				continue;
			}
			$next[] = $token;
		}

		if ( $found ) {
			update_option( self::OPTION_KEY, $next, false );
		}

		return $found;
	}

	/**
	 * Validate Bearer token and touch last_used.
	 *
	 * @param string $plaintext Raw token from Authorization header.
	 * @return array<string,mixed>|false Matched token record without hash, or false.
	 */
	public static function authenticate( $plaintext ) {
		$plaintext = is_string( $plaintext ) ? trim( $plaintext ) : '';
		if ( '' === $plaintext || 0 !== strpos( $plaintext, 'ipo_' ) ) {
			return false;
		}

		$tokens = self::all();
		$changed = false;

		foreach ( $tokens as $index => $token ) {
			if ( empty( $token['hash'] ) ) {
				continue;
			}
			if ( ! wp_check_password( $plaintext, $token['hash'] ) ) {
				continue;
			}

			$tokens[ $index ]['last_used'] = time();
			update_option( self::OPTION_KEY, $tokens, false );

			unset( $token['hash'] );
			return $token;
		}

		return false;
	}

	/**
	 * Extract Bearer token from current request.
	 *
	 * @return string
	 */
	public static function bearer_from_request() {
		$header = '';

		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$header = wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] );
		} elseif ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
			$header = wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
			foreach ( $headers as $key => $value ) {
				if ( strtolower( $key ) === 'authorization' ) {
					$header = $value;
					break;
				}
			}
		}

		if ( preg_match( '/Bearer\s+(\S+)/i', $header, $m ) ) {
			return $m[1];
		}

		// Fallback query param for tools that cannot set headers (discourage in UI).
		if ( isset( $_GET['ipo_ai_token'] ) ) {
			return sanitize_text_field( wp_unslash( $_GET['ipo_ai_token'] ) );
		}

		return '';
	}
}
