<?php
/**
 * IPO AI Bridge — bootstrap.
 *
 * Generates API tokens so Cursor / Claude can query the site DB (read-only)
 * via REST and a lightweight MCP JSON-RPC endpoint.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-ipo-ai-tokens.php';
require_once __DIR__ . '/class-ipo-ai-rest.php';
require_once __DIR__ . '/class-ipo-ai-admin.php';

IPO_AI_REST::init();
IPO_AI_Admin::init();
