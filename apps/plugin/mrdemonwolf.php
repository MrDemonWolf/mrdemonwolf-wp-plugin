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
 * Description:       Connects the MrDemonWolf site to the MrDemonWolf app. Accepts form submissions over the REST API (Divi, WPForms, Gravity Forms) and sends Expo push notifications. One plugin, two switchable modules.
 * Version:           2.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.3
 * Author:            MrDemonWolf, Inc.
 * Author URI:        https://mrdemonwolf.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mrdw
 * Domain Path:       /languages
 * Update URI:        https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRDW_VERSION', '2.1.0' );
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

// Module roots.
define( 'MRDW_FORMS_DIR', MRDW_PLUGIN_DIR . 'modules/forms/' );
define( 'MRDW_FORMS_URL', MRDW_PLUGIN_URL . 'modules/forms/' );
define( 'MRDW_PUSH_DIR', MRDW_PLUGIN_DIR . 'modules/push/' );
define( 'MRDW_PUSH_URL', MRDW_PLUGIN_URL . 'modules/push/' );

/**
 * Load the Forms module.
 */
function mrdw_load_forms_module() {
	require_once MRDW_FORMS_DIR . 'includes/providers/class-mrdw-forms-provider.php';
	require_once MRDW_FORMS_DIR . 'includes/providers/class-mrdw-forms-provider-divi.php';
	require_once MRDW_FORMS_DIR . 'includes/providers/class-mrdw-forms-provider-wpforms.php';
	require_once MRDW_FORMS_DIR . 'includes/providers/class-mrdw-forms-provider-gravityforms.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-provider-factory.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-loader.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-activator.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-deactivator.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-settings.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-appcheck.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-entry-store.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-entries-list-table.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-entries-page.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-divi-submissions.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms-rest-api.php';
	require_once MRDW_FORMS_DIR . 'includes/class-mrdw-forms.php';
}

/**
 * Register the Forms module's hooks.
 */
function mrdw_run_forms_module() {
	mrdw_load_forms_module();
	MRDW_Forms::get_instance()->run();
}

/**
 * Load the Push module.
 */
function mrdw_load_push_module() {
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-loader.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-i18n.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-db.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-activator.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-deactivator.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-expo.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-notification.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push-cron.php';
	require_once MRDW_PUSH_DIR . 'includes/class-mrdw-push.php';
	require_once MRDW_PUSH_DIR . 'rest-api/class-mrdw-push-rest-controller.php';

	if ( is_admin() ) {
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin-dashboard.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin-send.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin-devices.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin-groups.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin-history.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-admin-settings.php';
		require_once MRDW_PUSH_DIR . 'admin/class-mrdw-push-meta-box.php';
	}
}

/**
 * Register the Push module's hooks.
 */
function mrdw_run_push_module() {
	mrdw_load_push_module();

	$plugin = new MRDW_Push();
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
		MRDW_Forms_Activator::activate();
	}

	if ( MRDW_Modules::is_enabled( 'push' ) ) {
		mrdw_load_push_module();
		MRDW_Push_Activator::activate();
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

	if ( MRDW_Modules::is_enabled( 'forms' ) && class_exists( 'MRDW_Forms_Deactivator' ) ) {
		MRDW_Forms_Deactivator::deactivate();
	}

	if ( MRDW_Modules::is_enabled( 'push' ) && class_exists( 'MRDW_Push_Deactivator' ) ) {
		MRDW_Push_Deactivator::deactivate();
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
		MRDW_Forms_Activator::activate();
	}

	if ( $newly_enabled( 'push' ) ) {
		mrdw_load_push_module();
		MRDW_Push_Activator::activate();
	}
}
add_action( 'update_option_' . MRDW_Modules::OPTION_NAME, 'mrdw_on_modules_changed', 10, 2 );
