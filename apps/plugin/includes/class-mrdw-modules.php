<?php
/**
 * Module registry and enable/disable gate.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Modules
 *
 * Tracks which feature modules are switched on. Both modules ship enabled;
 * disabling one keeps its data intact but stops it registering any hooks.
 */
class MRDW_Modules {

	/**
	 * Option storing the per-module enabled flags.
	 */
	const OPTION_NAME = 'mrdemonwolf_modules';

	/**
	 * Every module this plugin knows about.
	 *
	 * @var string[]
	 */
	const MODULES = array( 'forms', 'push' );

	/**
	 * Runtime cache so repeat calls do not re-hit the options API.
	 *
	 * @var array<string, bool>|null
	 */
	private static $cache = null;

	/**
	 * Default state: every module on.
	 *
	 * @return array<string, bool>
	 */
	public static function defaults() {
		return array_fill_keys( self::MODULES, true );
	}

	/**
	 * Current state for every known module.
	 *
	 * @return array<string, bool>
	 */
	public static function all() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$stored = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$state = array();
		foreach ( self::MODULES as $module ) {
			// Unknown key means "never configured", which defaults to enabled.
			$state[ $module ] = array_key_exists( $module, $stored )
				? (bool) $stored[ $module ]
				: true;
		}

		self::$cache = $state;

		return $state;
	}

	/**
	 * Whether a given module should load.
	 *
	 * @param string $module Module slug.
	 * @return bool
	 */
	public static function is_enabled( $module ) {
		$state = self::all();

		return isset( $state[ $module ] ) && $state[ $module ];
	}

	/**
	 * Sanitize the settings form payload.
	 *
	 * Unchecked checkboxes are absent from $_POST, so a missing key means off.
	 *
	 * @param mixed $input Raw option value from the Settings API.
	 * @return array<string, bool>
	 */
	public static function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$clean = array();
		foreach ( self::MODULES as $module ) {
			$clean[ $module ] = ! empty( $input[ $module ] );
		}

		return $clean;
	}

	/**
	 * Drop the runtime cache. Hooked to the option update.
	 */
	public static function clear_cache() {
		self::$cache = null;
	}
}
