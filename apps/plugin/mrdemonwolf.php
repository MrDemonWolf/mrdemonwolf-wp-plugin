<?php
/**
 * MrDemonWolf — the official plugin for mrdemonwolf.com.
 *
 * @package    MrDemonWolf
 * @author     MrDemonWolf, Inc.
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       MrDemonWolf
 * Plugin URI:        https://mrdemonwolf.github.io/mrdemonwolf-wp-plugin/
 * Description:       Connects the MrDemonWolf site to the MrDemonWolf app. Accepts form submissions over the REST API (Divi, WPForms, Gravity Forms) and sends Expo push notifications. Successor to PackRelay and TailSignal.
 * Version:           1.4.0
 * Requires at least: 6.0
 * Requires PHP:      8.3
 * Author:            MrDemonWolf, Inc.
 * Author URI:        https://mrdemonwolf.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mrdemonwolf
 * Domain Path:       /languages
 * Update URI:        https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRDW_VERSION', '1.4.0' );
define( 'MRDW_PLUGIN_FILE', __FILE__ );
define( 'MRDW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MRDW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MRDW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once MRDW_PLUGIN_DIR . 'vendor/autoload.php';

require_once MRDW_PLUGIN_DIR . 'includes/class-mrdw-modules.php';
require_once MRDW_PLUGIN_DIR . 'includes/class-mrdw-secrets.php';
require_once MRDW_PLUGIN_DIR . 'includes/class-mrdw-conflict.php';
require_once MRDW_PLUGIN_DIR . 'includes/class-mrdw-updater.php';
require_once MRDW_PLUGIN_DIR . 'includes/class-mrdw-admin.php';

/**
 * The Forms module was PackRelay; the Push module was TailSignal. Their code is
 * carried over unchanged, so the constants they expect are mapped onto the
 * module directories rather than the files being rewritten.
 */
define( 'PACKRELAY_VERSION', MRDW_VERSION );
define( 'PACKRELAY_PLUGIN_FILE', MRDW_PLUGIN_FILE );
define( 'PACKRELAY_PLUGIN_DIR', MRDW_PLUGIN_DIR . 'modules/forms/' );
define( 'PACKRELAY_PLUGIN_URL', MRDW_PLUGIN_URL . 'modules/forms/' );
define( 'PACKRELAY_PLUGIN_BASENAME', MRDW_PLUGIN_BASENAME );

define( 'TAILSIGNAL_VERSION', MRDW_VERSION );
define( 'TAILSIGNAL_PLUGIN_FILE', MRDW_PLUGIN_FILE );
define( 'TAILSIGNAL_PLUGIN_DIR', MRDW_PLUGIN_DIR . 'modules/push/' );
define( 'TAILSIGNAL_PLUGIN_URL', MRDW_PLUGIN_URL . 'modules/push/' );
define( 'TAILSIGNAL_PLUGIN_BASENAME', MRDW_PLUGIN_BASENAME );

/**
 * Load the Forms module.
 */
function mrdw_load_forms_module() {
	require_once PACKRELAY_PLUGIN_DIR . 'includes/providers/class-packrelay-provider.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/providers/class-packrelay-provider-divi.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/providers/class-packrelay-provider-wpforms.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/providers/class-packrelay-provider-gravityforms.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-provider-factory.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-loader.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-activator.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-deactivator.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-settings.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-appcheck.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-entry-store.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-entries-list-table.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-entries-page.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-divi-submissions.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay-rest-api.php';
	require_once PACKRELAY_PLUGIN_DIR . 'includes/class-packrelay.php';
}

/**
 * Register the Forms module's hooks.
 */
function mrdw_run_forms_module() {
	mrdw_load_forms_module();
	PackRelay::get_instance()->run();
}

/**
 * Load the Push module.
 */
function mrdw_load_push_module() {
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-loader.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-i18n.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-db.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-activator.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-deactivator.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-expo.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-notification.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal-cron.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'includes/class-tailsignal.php';
	require_once TAILSIGNAL_PLUGIN_DIR . 'rest-api/class-tailsignal-rest-controller.php';

	if ( is_admin() ) {
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin-dashboard.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin-send.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin-devices.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin-groups.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin-history.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-admin-settings.php';
		require_once TAILSIGNAL_PLUGIN_DIR . 'admin/class-tailsignal-meta-box.php';
	}
}

/**
 * Register the Push module's hooks.
 */
function mrdw_run_push_module() {
	mrdw_load_push_module();

	$plugin = new TailSignal();
	$plugin->run();
}

/**
 * Boot the plugin once WordPress has loaded every active plugin.
 *
 * Runs late enough that the conflict check can see the full active-plugin list,
 * and early enough that modules can still hook init and rest_api_init.
 */
function mrdw_bootstrap() {
	if ( MRDW_Conflict::has_conflict() ) {
		add_action( 'admin_notices', array( 'MRDW_Conflict', 'render_notice' ) );
		add_action( 'network_admin_notices', array( 'MRDW_Conflict', 'render_notice' ) );

		return;
	}

	MRDW_Updater::init();
	MRDW_Admin::init();

	if ( MRDW_Modules::is_enabled( 'forms' ) ) {
		mrdw_run_forms_module();
	}

	if ( MRDW_Modules::is_enabled( 'push' ) ) {
		mrdw_run_push_module();
	}
}
add_action( 'plugins_loaded', 'mrdw_bootstrap', 5 );

/**
 * Run the activator for every enabled module.
 */
function mrdw_activate() {
	if ( MRDW_Conflict::has_conflict() ) {
		return;
	}

	if ( MRDW_Modules::is_enabled( 'forms' ) ) {
		mrdw_load_forms_module();
		PackRelay_Activator::activate();
	}

	if ( MRDW_Modules::is_enabled( 'push' ) ) {
		mrdw_load_push_module();
		TailSignal_Activator::activate();
	}
}
register_activation_hook( __FILE__, 'mrdw_activate' );

/**
 * Run the deactivator for every enabled module.
 */
function mrdw_deactivate() {
	if ( MRDW_Conflict::has_conflict() ) {
		return;
	}

	if ( MRDW_Modules::is_enabled( 'forms' ) && class_exists( 'PackRelay_Deactivator' ) ) {
		PackRelay_Deactivator::deactivate();
	}

	if ( MRDW_Modules::is_enabled( 'push' ) && class_exists( 'TailSignal_Deactivator' ) ) {
		TailSignal_Deactivator::deactivate();
	}
}
register_deactivation_hook( __FILE__, 'mrdw_deactivate' );

/**
 * Run a module's activator the first time it is switched on.
 *
 * Enabling a module after install still needs its database tables and
 * capabilities created, which normally only happens at plugin activation.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $value     New option value.
 */
function mrdw_on_modules_changed( $old_value, $value ) {
	MRDW_Modules::clear_cache();

	$was = is_array( $old_value ) ? $old_value : array();
	$now = is_array( $value ) ? $value : array();

	$newly_enabled = static function ( $module ) use ( $was, $now ) {
		$before = ! array_key_exists( $module, $was ) || ! empty( $was[ $module ] );
		$after  = ! empty( $now[ $module ] );

		return $after && ! $before;
	};

	if ( $newly_enabled( 'forms' ) ) {
		mrdw_load_forms_module();
		PackRelay_Activator::activate();
	}

	if ( $newly_enabled( 'push' ) ) {
		mrdw_load_push_module();
		TailSignal_Activator::activate();
	}
}
add_action( 'update_option_' . MRDW_Modules::OPTION_NAME, 'mrdw_on_modules_changed', 10, 2 );
