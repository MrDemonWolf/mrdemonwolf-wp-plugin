<?php
/**
 * GitHub-backed update channels.
 *
 * Stable sites track published releases; nightly sites additionally accept
 * pre-releases. Both are served from the same GitHub repository via
 * plugin-update-checker, so no update server is required.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Class MRDW_Updater
 */
class MRDW_Updater {

	/**
	 * Repository the update checker reads releases from.
	 */
	const REPOSITORY_URL = 'https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin/';

	/**
	 * Optional wp-config.php constant naming the desired channel.
	 */
	const CHANNEL_CONSTANT = 'MRDW_UPDATE_CHANNEL';

	/**
	 * Supported channels.
	 */
	const CHANNEL_STABLE  = 'stable';
	const CHANNEL_NIGHTLY = 'nightly';

	/**
	 * Resolve the update channel for this site.
	 *
	 * Defaults to stable; only an explicit, recognised opt-in selects nightly.
	 *
	 * @return string One of the CHANNEL_* constants.
	 */
	public static function channel() {
		$channel = self::CHANNEL_STABLE;

		if ( defined( self::CHANNEL_CONSTANT ) ) {
			$configured = strtolower( trim( (string) constant( self::CHANNEL_CONSTANT ) ) );
			if ( self::CHANNEL_NIGHTLY === $configured ) {
				$channel = self::CHANNEL_NIGHTLY;
			}
		}

		/**
		 * Filter the resolved update channel.
		 *
		 * @param string $channel Either 'stable' or 'nightly'.
		 */
		$channel = (string) apply_filters( 'mrdw_update_channel', $channel );

		return self::CHANNEL_NIGHTLY === $channel ? self::CHANNEL_NIGHTLY : self::CHANNEL_STABLE;
	}

	/**
	 * Whether this site is tracking nightly builds.
	 *
	 * @return bool
	 */
	public static function is_nightly() {
		return self::CHANNEL_NIGHTLY === self::channel();
	}

	/**
	 * Wire up the update checker.
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! class_exists( PucFactory::class ) ) {
			return;
		}

		$checker = PucFactory::buildUpdateChecker(
			self::REPOSITORY_URL,
			MRDW_PLUGIN_FILE,
			'mrdemonwolf-wp-plugin'
		);

		$api = $checker->getVcsApi();

		// Prefer the built plugin zip attached to the release over GitHub's
		// auto-generated source archive, which has no Composer dependencies.
		$api->enableReleaseAssets( '/mrdemonwolf-wp-plugin.*\.zip$/i' );

		if ( self::is_nightly() ) {
			// Nightly builds are published as pre-releases. RELEASE_FILTER_ALL
			// stops plugin-update-checker skipping them; the callback accepts
			// every release so the newest one simply wins on version compare.
			$api->setReleaseFilter(
				static function () {
					return true;
				},
				$api::RELEASE_FILTER_ALL
			);
		}
	}
}
