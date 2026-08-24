<?php
/**
 * Provider factory.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Forms_Provider_Factory
 *
 * Creates provider instances based on settings.
 */
class MRDW_Forms_Provider_Factory {

	/**
	 * Provider class mapping.
	 *
	 * @var array
	 */
	private static $providers = array(
		'divi'         => 'MRDW_Forms_Provider_Divi',
		'wpforms'      => 'MRDW_Forms_Provider_WPForms',
		'gravityforms' => 'MRDW_Forms_Provider_GravityForms',
	);

	/**
	 * Cached available providers.
	 *
	 * @var array|null
	 */
	private static $available_cache = null;

	/**
	 * Clear the available providers cache.
	 */
	public static function clear_cache() {
		self::$available_cache = null;
	}

	/**
	 * Create a provider instance.
	 *
	 * @param string|null $slug Optional provider slug. Reads from settings if null.
	 * @return MRDW_Forms_Provider
	 */
	public static function create( $slug = null ) {
		if ( null === $slug ) {
			$settings = MRDW_Forms_Settings::get_settings();
			$slug     = $settings['form_provider'] ?? 'divi';
		}

		if ( isset( self::$providers[ $slug ] ) ) {
			$class = self::$providers[ $slug ];
			return new $class();
		}

		return new MRDW_Forms_Provider_Divi();
	}

	/**
	 * Get all available providers with their labels.
	 *
	 * @return array Slug => label mapping.
	 */
	public static function get_available_providers() {
		if ( null !== self::$available_cache ) {
			return self::$available_cache;
		}

		$available = array();

		foreach ( self::$providers as $slug => $class ) {
			$instance           = new $class();
			$available[ $slug ] = array(
				'label'     => $instance->get_label(),
				'available' => $instance->is_available(),
			);
		}

		self::$available_cache = $available;

		return $available;
	}
}
