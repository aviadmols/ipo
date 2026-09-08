<?php
/**
 * Admin UI for IPO AI Bridge — generate tokens and copy Cursor/Claude config.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IPO_AI_Admin {

	const PAGE_SLUG = 'ipo-ai-bridge';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_ipo_ai_create_token', array( __CLASS__, 'handle_create' ) );
		add_action( 'admin_post_ipo_ai_revoke_token', array( __CLASS__, 'handle_revoke' ) );
		add_action( 'admin_post_ipo_ai_toggle_write', array( __CLASS__, 'handle_toggle_write' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_new_token' ) );
	}

	public static function menu() {
		add_management_page(
			'AI Bridge (Cursor / Claude)',
			'AI Bridge',
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	public static function handle_create() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}
		check_admin_referer( 'ipo_ai_create_token' );

		$label  = isset( $_POST['token_label'] ) ? sanitize_text_field( wp_unslash( $_POST['token_label'] ) ) : 'Cursor';
		$result = IPO_AI_Tokens::create( $label );

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => self::PAGE_SLUG,
						'ai_err'  => rawurlencode( $result->get_error_message() ),
					),
					admin_url( 'tools.php' )
				)
			);
			exit;
		}

		set_transient(
			'ipo_ai_new_token_' . get_current_user_id(),
			$result,
			5 * MINUTE_IN_SECONDS
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'     => self::PAGE_SLUG,
					'ai_created' => '1',
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	public static function handle_revoke() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}
		check_admin_referer( 'ipo_ai_revoke_token' );

		$id = isset( $_POST['token_id'] ) ? sanitize_text_field( wp_unslash( $_POST['token_id'] ) ) : '';
		IPO_AI_Tokens::revoke( $id );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => self::PAGE_SLUG,
					'ai_revoked' => '1',
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	/**
	 * Grant or take away write access for a single token.
	 *
	 * Kept separate from token creation on purpose: write access is a decision
	 * made per token, after the fact, and can be taken back without revoking it.
	 */
	public static function handle_toggle_write() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}
		check_admin_referer( 'ipo_ai_toggle_write' );

		$id      = isset( $_POST['token_id'] ) ? sanitize_text_field( wp_unslash( $_POST['token_id'] ) ) : '';
		$enable  = ! empty( $_POST['enable'] );
		$current = get_option( IPO_AI_REST::WRITE_TOKENS_OPTION, array() );
		$current = is_array( $current ) ? $current : array();

		if ( $enable ) {
			if ( $id && ! in_array( $id, $current, true ) ) {
				$current[] = $id;
			}
		} else {
			$current = array_values( array_diff( $current, array( $id ) ) );
		}

		update_option( IPO_AI_REST::WRITE_TOKENS_OPTION, $current, false );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => self::PAGE_SLUG,
					'ai_write'  => $enable ? 'on' : 'off',
				),
				admin_url( 'tools.php' )
			)
		);
		exit;
	}

	public static function maybe_show_new_token() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'tools_page_' . self::PAGE_SLUG !== $screen->id ) {
			return;
		}
		if ( empty( $_GET['ai_created'] ) ) {
			return;
		}

		$fresh = get_transient( 'ipo_ai_new_token_' . get_current_user_id() );
		if ( ! $fresh || empty( $fresh['token'] ) ) {
			return;
		}
		delete_transient( 'ipo_ai_new_token_' . get_current_user_id() );

		$token   = esc_html( $fresh['token'] );
		$mcp_url = esc_url( rest_url( IPO_AI_REST::NS . '/mcp' ) );
		$status  = esc_url( rest_url( IPO_AI_REST::NS . '/status' ) );

		$cursor_json = wp_json_encode(
			array(
				'mcpServers' => array(
					'ipo-wordpress-db' => array(
						'url'     => rest_url( IPO_AI_REST::NS . '/mcp' ),
						'headers' => array(
							'Authorization' => 'Bearer ' . $fresh['token'],
						),
					),
				),
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);

		$claude_cmd = sprintf(
			'claude mcp add --transport http ipo-wordpress-db %s --header "Authorization: Bearer %s"',
			rest_url( IPO_AI_REST::NS . '/mcp' ),
			$fresh['token']
		);

		echo '<div class="notice notice-success is-dismissible" style="padding:12px 16px;">';
		echo '<p><strong>הטוקן נוצר בהצלחה — העתק אותו עכשיו. הוא לא יוצג שוב.</strong></p>';
		echo '<p><code style="font-size:14px;user-select:all;word-break:break-all;">' . $token . '</code></p>';
		echo '<p><strong>בדיקה מהירה:</strong><br><code>curl -H "Authorization: Bearer ' . $token . '" "' . $status . '"</code></p>';
		echo '<p><strong>Cursor — הוסף ל־mcp.json:</strong></p>';
		echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:12px;overflow:auto;direction:ltr;text-align:left;">' . esc_html( $cursor_json ) . '</pre>';
		echo '<p><strong>Claude Code:</strong></p>';
		echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:12px;overflow:auto;direction:ltr;text-align:left;">' . esc_html( $claude_cmd ) . '</pre>';
		echo '<p style="color:#646970;">MCP endpoint: <code>' . $mcp_url . '</code></p>';
		echo '</div>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}

		$tokens  = IPO_AI_Tokens::all();
		$mcp_url = rest_url( IPO_AI_REST::NS . '/mcp' );
		$query_url = rest_url( IPO_AI_REST::NS . '/query' );
		?>
		<div class="wrap" dir="rtl">
			<h1>AI Bridge — חיבור Cursor / Claude ל־DB</h1>
			<p>
				מנגנון זה מייצר טוקן מאובטח שמאפשר לכלי AI (Cursor / Claude) לגשת לנתוני האתר
				<strong>לקריאה בלבד</strong> דרך REST / MCP — בלי לחשוף סיסמת MySQL.
			</p>

			<?php if ( ! empty( $_GET['ai_revoked'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>הטוקן בוטל.</p></div>
			<?php endif; ?>
			<?php if ( ! empty( $_GET['ai_err'] ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( wp_unslash( $_GET['ai_err'] ) ); ?></p></div>
			<?php endif; ?>

			<div class="card" style="max-width:720px;padding:16px 20px;margin-top:16px;">
				<h2 style="margin-top:0;">יצירת טוקן חדש</h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ipo_ai_create_token' ); ?>
					<input type="hidden" name="action" value="ipo_ai_create_token" />
					<p>
						<label for="token_label"><strong>שם / תווית</strong></label><br />
						<input type="text" class="regular-text" id="token_label" name="token_label" value="Cursor" placeholder="Cursor / Claude" />
					</p>
					<?php submit_button( 'צור טוקן', 'primary', 'submit', false ); ?>
				</form>
			</div>

			<div class="card" style="max-width:720px;padding:16px 20px;margin-top:16px;">
				<h2 style="margin-top:0;">טוקנים פעילים</h2>
				<?php if ( empty( $tokens ) ) : ?>
					<p>אין טוקנים עדיין.</p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th>תווית</th>
								<th>קידומת</th>
								<th>נוצר</th>
								<th>שימוש אחרון</th>
								<th>כתיבה</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$write_ids = get_option( IPO_AI_REST::WRITE_TOKENS_OPTION, array() );
						$write_ids = is_array( $write_ids ) ? $write_ids : array();
						?>
						<?php foreach ( $tokens as $t ) : ?>
							<?php $can_write = ! empty( $t['id'] ) && in_array( $t['id'], $write_ids, true ); ?>
							<tr>
								<td><?php echo esc_html( isset( $t['label'] ) ? $t['label'] : '' ); ?></td>
								<td><code><?php echo esc_html( isset( $t['prefix'] ) ? $t['prefix'] . '…' : '' ); ?></code></td>
								<td><?php echo esc_html( isset( $t['created'] ) ? gmdate( 'Y-m-d H:i', (int) $t['created'] ) . ' UTC' : '' ); ?></td>
								<td><?php echo ! empty( $t['last_used'] ) ? esc_html( gmdate( 'Y-m-d H:i', (int) $t['last_used'] ) . ' UTC' ) : '—'; ?></td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
										<?php wp_nonce_field( 'ipo_ai_toggle_write' ); ?>
										<input type="hidden" name="action" value="ipo_ai_toggle_write" />
										<input type="hidden" name="token_id" value="<?php echo esc_attr( $t['id'] ); ?>" />
										<input type="hidden" name="enable" value="<?php echo $can_write ? '' : '1'; ?>" />
										<?php if ( $can_write ) : ?>
											<button type="submit" class="button button-small" style="color:#b32d2e;">✔ מופעלת — לבטל</button>
										<?php else : ?>
											<button type="submit" class="button button-small">קריאה בלבד — לאפשר</button>
										<?php endif; ?>
									</form>
								</td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('לבטל את הטוקן?');">
										<?php wp_nonce_field( 'ipo_ai_revoke_token' ); ?>
										<input type="hidden" name="action" value="ipo_ai_revoke_token" />
										<input type="hidden" name="token_id" value="<?php echo esc_attr( $t['id'] ); ?>" />
										<?php submit_button( 'בטל', 'delete small', 'submit', false ); ?>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div class="card" style="max-width:720px;padding:16px 20px;margin-top:16px;">
				<h2 style="margin-top:0;">איך מתחברים</h2>
				<ol style="line-height:1.7;">
					<li>צור טוקן למעלה והעתק אותו מיד.</li>
					<li>
						<strong>Cursor:</strong> Settings → Tools &amp; MCP → New MCP Server / ערוך <code>mcp.json</code>
						והדבק את ה־JSON שמופיע אחרי יצירת הטוקן (כולל ה־Bearer).
					</li>
					<li>
						<strong>Claude Code:</strong> הרץ את פקודת <code>claude mcp add ...</code> שמופיעה אחרי יצירת הטוקן.
					</li>
					<li>
						אפשר גם לקרוא ישירות ל־REST:
						<code style="direction:ltr;display:inline-block;">POST <?php echo esc_html( $query_url ); ?></code>
						עם כותרת <code>Authorization: Bearer …</code> וגוף
						<code>{"sql":"SELECT ID, post_title FROM <?php global $wpdb; echo esc_html( $wpdb->posts ); ?> LIMIT 5"}</code>
					</li>
				</ol>
				<p>
					<strong>MCP URL:</strong>
					<code style="direction:ltr;"><?php echo esc_html( $mcp_url ); ?></code>
				</p>
				<p style="color:#b32d2e;">
					שים לב: הטוקן נותן גישת קריאה לנתוני האתר. אל תשתף אותו בציבור, ובטל אותו אם דלף.
				</p>
			</div>
		</div>
		<?php
	}
}
