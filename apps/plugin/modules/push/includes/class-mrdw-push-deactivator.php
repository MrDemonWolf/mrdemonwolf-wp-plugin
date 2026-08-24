<?php
/**
 * Fired during plugin deactivation.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		// Clear all scheduled cron events regardless of args —
		// wp_clear_scheduled_hook() with no args only clears events scheduled
		// with an empty args array, which misses every per-notification event.
		wp_unschedule_hook( 'mrdw_push_check_receipts' );
		wp_unschedule_hook( 'mrdw_push_send_scheduled' );
	}
}
