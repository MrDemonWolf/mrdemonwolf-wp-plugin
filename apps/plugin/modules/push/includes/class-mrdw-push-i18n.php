<?php
/**
 * Define the internationalization functionality.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_i18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'mrdw',
			false,
			dirname( MRDW_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
