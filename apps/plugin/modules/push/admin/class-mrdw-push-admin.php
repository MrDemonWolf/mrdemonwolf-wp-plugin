<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Admin {

	/**
	 * Register the admin menu pages.
	 */
	public function add_menu_pages() {
		// Dashboard.
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'Push', 'mrdw' ),
			__( 'Push', 'mrdw' ),
			'mrdw_manage',
			'mrdw-push',
			array( $this, 'render_dashboard_page' )
		);

		// Send Notification.
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'Send Notification', 'mrdw' ),
			__( 'Send', 'mrdw' ),
			'mrdw_manage',
			'mrdw-push-send',
			array( $this, 'render_send_page' )
		);

		// Devices.
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'Devices', 'mrdw' ),
			__( 'Devices', 'mrdw' ),
			'mrdw_manage',
			'mrdw-push-devices',
			array( $this, 'render_devices_page' )
		);

		// Groups.
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'Groups', 'mrdw' ),
			__( 'Groups', 'mrdw' ),
			'mrdw_manage',
			'mrdw-push-groups',
			array( $this, 'render_groups_page' )
		);

		// History.
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'History', 'mrdw' ),
			__( 'History', 'mrdw' ),
			'mrdw_manage',
			'mrdw-push-history',
			array( $this, 'render_history_page' )
		);

		// Settings.
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'Push Settings', 'mrdw' ),
			__( 'Push Settings', 'mrdw' ),
			'mrdw_manage',
			'mrdw-push-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_styles( $hook ) {
		if ( ! $this->is_mrdw_push_page( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'mrdw-push-tailwind',
			MRDW_PUSH_URL . 'admin/css/mrdw-push-tailwind.css',
			array(),
			MRDW_VERSION,
			'all'
		);

		wp_enqueue_style(
			'mrdw-push-admin',
			MRDW_PUSH_URL . 'admin/css/mrdw-push-admin.css',
			array( 'mrdw-push-tailwind' ),
			MRDW_VERSION,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Enqueue on MRDW_Push pages and post editor.
		$is_mrdw_push = $this->is_mrdw_push_page( $hook );
		$is_post_edit  = in_array( $hook, array( 'post.php', 'post-new.php' ), true );

		// On post edit screens, only enqueue if post type is in mrdw_push_post_types.
		if ( $is_post_edit ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( $screen ) {
				$supported_types = apply_filters( 'mrdw_push_post_types', array( 'post', 'portfolio' ) );
				if ( ! in_array( $screen->post_type, $supported_types, true ) ) {
					$is_post_edit = false;
				}
			}
		}

		if ( ! $is_mrdw_push && ! $is_post_edit ) {
			return;
		}

		// Load WP Media Library on Send page and post editor (before our script).
		if ( 'mrdw_page_mrdw-push-send' === $hook || $is_post_edit ) {
			wp_enqueue_media();
		}

		$deps = array( 'jquery' );

		// Load Chart.js on dashboard page only (before our script, as a dependency).
		if ( 'mrdw_page_mrdw-push' === $hook ) {
			wp_enqueue_script(
				'chartjs',
				MRDW_PUSH_URL . 'admin/js/vendor/chart.min.js',
				array(),
				'4.4.7',
				true
			);
			$deps[] = 'chartjs';
		}

		wp_enqueue_script(
			'mrdw-push-admin',
			MRDW_PUSH_URL . 'admin/js/mrdw-push-admin.js',
			$deps,
			MRDW_VERSION,
			true
		);

		wp_localize_script(
			'mrdw-push-admin',
			'mrdwPush',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'rest_url'   => rest_url( 'mrdw/v1/' ),
				'nonce'      => wp_create_nonce( 'mrdw_push_nonce' ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
				'strings'    => array(
					'choose_image'               => __( 'Choose Image', 'mrdw' ),
					'use_image'                  => __( 'Use this image', 'mrdw' ),
					'confirm_delete'             => __( 'Are you sure you want to delete this?', 'mrdw' ),
					'sending'                    => __( 'Sending...', 'mrdw' ),
					'sent'                       => __( 'Sent!', 'mrdw' ),
					'error'                      => __( 'An error occurred.', 'mrdw' ),
					'scheduled'                  => __( 'Scheduled!', 'mrdw' ),
					'schedule'                   => __( 'Schedule', 'mrdw' ),
					'schedule_datetime_required' => __( 'Please choose a date and time before scheduling.', 'mrdw' ),
					'confirm'                    => __( 'Confirm', 'mrdw' ),
					'cancel'                     => __( 'Cancel', 'mrdw' ),
					'dismiss'                    => __( 'Dismiss', 'mrdw' ),
					/* translators: 1: selected count, 2: total count */
					'selected_count'             => __( '%1$s / %2$s selected', 'mrdw' ),
					'cancelled'                  => __( 'Cancelled.', 'mrdw' ),
					'confirm_delete_all'         => __( 'Are you sure you want to delete ALL notification history? This cannot be undone.', 'mrdw' ),
					'deleting'                   => __( 'Deleting...', 'mrdw' ),
					'delete_all_history'         => __( 'Delete All History', 'mrdw' ),
				),
			)
		);
	}

	/**
	 * Check if the current WP admin color scheme is a dark variant.
	 *
	 * @return bool True if the admin is using a dark color scheme.
	 */
	public function is_dark_admin_scheme() {
		$dark_schemes = array( 'midnight', 'blue', 'coffee', 'ectoplasm', 'ocean', 'sunrise', 'modern' );
		$scheme       = get_user_option( 'admin_color' );

		return in_array( $scheme, $dark_schemes, true );
	}

	/**
	 * Add data-theme attribute to #mrdw-push-app wrappers for dark WP admin schemes.
	 *
	 * Hooked to admin_footer to inject a small inline script.
	 */
	public function maybe_add_dark_theme_attr() {
		$hook = get_current_screen();
		if ( ! $hook ) {
			return;
		}

		$is_mrdw_push   = $this->is_mrdw_push_page( $hook->id );
		$supported_types = apply_filters( 'mrdw_push_post_types', array( 'post', 'portfolio' ) );
		$is_post_edit    = 'post' === $hook->base && in_array( $hook->post_type, $supported_types, true );

		if ( ! $is_mrdw_push && ! $is_post_edit ) {
			return;
		}

		if ( ! $this->is_dark_admin_scheme() ) {
			return;
		}

		echo '<script>document.querySelectorAll("#mrdw-push-app").forEach(function(el){el.setAttribute("data-theme","dark")});</script>' . "\n";
	}

	/**
	 * Check if the current page is a MRDW_Push admin page.
	 *
	 * @param string $hook The current admin page hook.
	 * @return bool
	 */
	private function is_mrdw_push_page( $hook ) {
		$pages = array(
			'mrdw_page_mrdw-push',
			'mrdw_page_mrdw-push-send',
			'mrdw_page_mrdw-push-devices',
			'mrdw_page_mrdw-push-groups',
			'mrdw_page_mrdw-push-history',
			'mrdw_page_mrdw-push-settings',
		);

		return in_array( $hook, $pages, true );
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard_page() {
		$dashboard = new MRDW_Push_Admin_Dashboard();
		$dashboard->render();
	}

	/**
	 * Render the send notification page.
	 */
	public function render_send_page() {
		$send = new MRDW_Push_Admin_Send();
		$send->render();
	}

	/**
	 * Render the devices page.
	 */
	public function render_devices_page() {
		$devices = new MRDW_Push_Admin_Devices();
		$devices->render();
	}

	/**
	 * Render the groups page.
	 */
	public function render_groups_page() {
		$groups = new MRDW_Push_Admin_Groups();
		$groups->render();
	}

	/**
	 * Render the history page.
	 */
	public function render_history_page() {
		$history = new MRDW_Push_Admin_History();
		$history->render();
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		include MRDW_PUSH_DIR . 'admin/partials/settings.php';
	}
}
