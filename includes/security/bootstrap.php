<?php
/**
 * IPO Security — bootstrap.
 *
 * Audit log + login/account hardening + optional e-mail 2FA.
 * Admin screen: Tools -> אבטחה ויומן פעולות.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-ipo-audit-log.php';
require_once __DIR__ . '/class-ipo-audit-events.php';
require_once __DIR__ . '/class-ipo-security-hardening.php';
require_once __DIR__ . '/class-ipo-two-factor.php';
require_once __DIR__ . '/class-ipo-security-admin.php';

IPO_Audit_Log::init();
IPO_Audit_Events::init();
IPO_Security_Hardening::init();
IPO_Two_Factor::init();
IPO_Security_Admin::init();
