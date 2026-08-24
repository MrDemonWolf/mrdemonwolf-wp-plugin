<?php
/**
 * Core plugin orchestrator.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Forms
 *
 * Singleton that loads dependencies and registers all hooks via the loader.
 */
class MRDW_Forms {

	/**
	 * Singleton instance.
	 *
	 * @var MRDW_Forms|null
	 */
	private static $instance = null;

	/**
	 * Loader instance.
	 *
	 * @var MRDW_Forms_Loader
	 */
	protected $loader;

	/**
	 * Settings instance.
	 *
	 * @var MRDW_Forms_Settings
	 */
	protected $settings;

	/**
	 * REST API instance.
	 *
	 * @var MRDW_Forms_REST_API
	 */
	protected $rest_api;

	/**
	 * Entries page instance.
	 *
	 * @var MRDW_Forms_Entries_Page
	 */
	protected $entries_page;

	/**
	 * Divi submissions instance.
	 *
	 * @var MRDW_Forms_Divi_Submissions
	 */
	protected $divi_submissions;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->loader           = new MRDW_Forms_Loader();
		$this->settings         = new MRDW_Forms_Settings();
		$this->rest_api         = new MRDW_Forms_REST_API();
		$this->entries_page     = new MRDW_Forms_Entries_Page();
		$this->divi_submissions = new MRDW_Forms_Divi_Submissions();

		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MRDW_Forms
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register admin-side hooks.
	 */
	private function define_admin_hooks() {
		$this->loader->add_action( 'admin_menu', $this->entries_page, 'add_menu_pages' );
		$this->loader->add_action( 'admin_menu', $this->settings, 'add_settings_page' );
		$this->loader->add_action( 'admin_init', $this->settings, 'register_settings' );
		$this->loader->add_action( 'admin_notices', $this, 'provider_dependency_notice' );

		// Unified admin styles and scripts for all MRDW_Forms pages.
		$this->loader->add_action( 'admin_enqueue_scripts', $this->entries_page, 'enqueue_styles' );

		// CSV export handler.
		$this->loader->add_action( 'admin_init', $this->entries_page, 'handle_export' );
	}

	/**
	 * Register public-facing hooks.
	 */
	private function define_public_hooks() {
		$this->loader->add_action( 'init', $this, 'load_textdomain' );
		$this->loader->add_action( 'init', 'MRDW_Forms_Activator', 'maybe_upgrade' );
		$this->loader->add_action( 'rest_api_init', $this->rest_api, 'register_routes' );
		$this->loader->add_filter( 'rest_pre_serve_request', $this->rest_api, 'add_cors_headers', 10, 4 );

		// Capture Divi front-end form submissions.
		$this->loader->add_action( 'et_pb_contact_form_submit', $this->divi_submissions, 'save_submission', 10, 3 );

		// Create per-site table when a new multisite site is added.
		$this->loader->add_action( 'wp_initialize_site', 'MRDW_Forms_Activator', 'initialize_new_site', 10, 1 );

		// Invalidate the settings cache when settings are saved.
		$this->loader->add_action( 'update_option_' . MRDW_Forms_Settings::OPTION_NAME, 'MRDW_Forms_Settings', 'clear_cache' );
	}

	/**
	 * Load the plugin text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'mrdw', false, dirname( MRDW_PLUGIN_BASENAME ) . '/languages' );
	}

	/**
	 * Show admin notice if the configured provider is not active.
	 */
	public function provider_dependency_notice() {
		if ( ! MRDW_Forms_Activator::is_provider_available() ) {
			delete_transient( 'mrdw_forms_provider_notice' );

			$provider = MRDW_Forms_Provider_Factory::create();
			$label    = $provider->get_label();

			echo '<div class="notice notice-warning"><p>';
			printf(
				/* translators: %s: form builder name */
				esc_html__( 'MRDW_Forms requires %s to be installed and active. Please install it or change the form provider in MRDW_Forms settings.', 'mrdw' ),
				esc_html( $label )
			);
			echo '</p></div>';
		}
	}

	/**
	 * Fire all registered hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the loader instance.
	 *
	 * @return MRDW_Forms_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}
}
