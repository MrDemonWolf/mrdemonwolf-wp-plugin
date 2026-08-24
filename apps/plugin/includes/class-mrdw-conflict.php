<?php
/**
 * Legacy plugin conflict guard.
 *
 * PackRelay and TailSignal were merged into this plugin verbatim, so their
 * class names and REST routes are identical. Loading this plugin alongside
 * either predecessor would be a fatal redeclare, so detect that first and
 * bail with an admin notice instead.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Conflict
 */
class MRDW_Conflict {

	/**
	 * Predecessor plugin files mapped to their display names.
	 *
	 * @var array<string, string>
	 */
	const LEGACY_PLUGINS = array(
		'packrelay/packrelay.php'   => 'PackRelay',
		'tailsignal/tailsignal.php' => 'TailSignal',
	);

	/**
	 * Names of any conflicting plugins that are currently active.
	 *
	 * Reads the active-plugin options directly rather than is_plugin_active(),
	 * which lives in wp-admin/includes/plugin.php and is not loaded this early.
	 *
	 * @return string[]
	 */
	public static function active_legacy_plugins() {
		$active = get_option( 'active_plugins', array() );
		if ( ! is_array( $active ) ) {
			$active = array();
		}

		if ( is_multisite() ) {
			$network_active = get_site_option( 'active_sitewide_plugins', array() );
			if ( is_array( $network_active ) ) {
				$active = array_merge( $active, array_keys( $network_active ) );
			}
		}

		$found = array();
		foreach ( self::LEGACY_PLUGINS as $file => $name ) {
			if ( in_array( $file, $active, true ) ) {
				$found[] = $name;
			}
		}

		return $found;
	}

	/**
	 * Whether loading should be aborted.
	 *
	 * @return bool
	 */
	public static function has_conflict() {
		return array() !== self::active_legacy_plugins();
	}

	/**
	 * Print the conflict notice on admin screens.
	 */
	public static function render_notice() {
		$conflicts = self::active_legacy_plugins();
		if ( array() === $conflicts ) {
			return;
		}

		$message = sprintf(
			/* translators: %s: comma-separated list of conflicting plugin names. */
			_n(
				'MrDemonWolf is inactive because %s is still active. That plugin is now built in — deactivate it to finish the upgrade. Your existing settings and data are preserved.',
				'MrDemonWolf is inactive because %s are still active. Those plugins are now built in — deactivate them to finish the upgrade. Your existing settings and data are preserved.',
				count( $conflicts ),
				'mrdemonwolf'
			),
			implode( ', ', $conflicts )
		);

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html( $message )
		);
	}
}
