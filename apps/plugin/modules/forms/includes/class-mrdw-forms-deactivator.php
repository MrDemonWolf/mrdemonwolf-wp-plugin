<?php
/**
 * Plugin deactivation handler.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Forms_Deactivator
 */
class MRDW_Forms_Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		delete_transient( 'mrdw_forms_provider_notice' );
	}
}
