<?php
/**
 * The core plugin class.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var MRDW_Push_Loader
	 */
	protected $loader;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->loader = new MRDW_Push_Loader();
		$this->set_locale();
		$this->define_rest_hooks();
		$this->define_notification_hooks();
		$this->define_cron_hooks();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		}
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {
		$i18n = new MRDW_Push_i18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register REST API hooks.
	 */
	private function define_rest_hooks() {
		$rest = new MRDW_Push_REST_Controller();
		$this->loader->add_action( 'rest_api_init', $rest, 'register_routes' );
		$this->loader->add_filter( 'rest_pre_serve_request', $rest, 'serve_csv_response', 10, 4 );
	}

	/**
	 * Register notification hooks.
	 */
	private function define_notification_hooks() {
		$notification = new MRDW_Push_Notification();
		$this->loader->add_action( 'transition_post_status', $notification, 'on_post_published', 10, 3 );
	}

	/**
	 * Register cron hooks.
	 */
	private function define_cron_hooks() {
		$cron = new MRDW_Push_Cron();
		$this->loader->add_action( 'mrdw_push_check_receipts', $cron, 'check_receipts' );
		$this->loader->add_action( 'mrdw_push_send_scheduled', $cron, 'send_scheduled_notification' );
	}

	/**
	 * Register admin hooks.
	 */
	private function define_admin_hooks() {
		$admin = new MRDW_Push_Admin();
		$this->loader->add_action( 'admin_menu', $admin, 'add_menu_pages' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_footer', $admin, 'maybe_add_dark_theme_attr' );

		// Settings.
		$settings = new MRDW_Push_Admin_Settings();
		$this->loader->add_action( 'admin_init', $settings, 'register_settings' );

		// Meta box.
		$meta_box = new MRDW_Push_Meta_Box();
		$this->loader->add_action( 'add_meta_boxes', $meta_box, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $meta_box, 'save_meta_box', 10, 2 );
		$this->loader->add_action( 'wp_ajax_mrdw_push_quick_send', $meta_box, 'handle_quick_send' );

		// Admin AJAX handlers.
		$send = new MRDW_Push_Admin_Send();
		$this->loader->add_action( 'wp_ajax_mrdw_push_send_notification', $send, 'handle_send' );
		$this->loader->add_action( 'wp_ajax_mrdw_push_cancel_scheduled', $send, 'handle_cancel_scheduled' );

		// Groups AJAX.
		$groups = new MRDW_Push_Admin_Groups();
		$this->loader->add_action( 'wp_ajax_mrdw_push_save_group', $groups, 'handle_save_group' );
		$this->loader->add_action( 'wp_ajax_mrdw_push_delete_group', $groups, 'handle_delete_group' );

		// History AJAX.
		$history = new MRDW_Push_Admin_History();
		$this->loader->add_action( 'wp_ajax_mrdw_push_delete_all_notifications', $history, 'handle_delete_all' );

		// Devices AJAX.
		$devices = new MRDW_Push_Admin_Devices();
		$this->loader->add_action( 'wp_ajax_mrdw_push_update_device', $devices, 'handle_update_device' );
		$this->loader->add_action( 'wp_ajax_mrdw_push_toggle_dev', $devices, 'handle_toggle_dev' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the loader.
	 *
	 * @return MRDW_Push_Loader The loader.
	 */
	public function get_loader() {
		return $this->loader;
	}
}
