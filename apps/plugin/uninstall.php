<?php
/**
 * Uninstall handler.
 *
 * Removes everything both modules created, whether or not they were enabled at
 * the time of deletion, so no orphaned tables or options are left behind. On
 * multisite this runs for every site.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'modules/push/includes/class-mrdw-push-db.php';

/**
 * Remove all plugin data for the current site.
 */
function mrdw_uninstall_site() {
	global $wpdb;

	// ---- Forms module (formerly PackRelay) ----
	$wpdb->query( sprintf( 'DROP TABLE IF EXISTS `%s`', esc_sql( $wpdb->prefix . 'mrdw_forms_entries' ) ) );

	delete_option( 'mrdw_forms_settings' );
	delete_option( 'mrdw_forms_db_version' );
	delete_transient( 'mrdw_forms_provider_notice' );

	// ---- Push module (formerly TailSignal) ----
	MRDW_Push_DB::drop_tables();

	$options = array(
		'mrdw_push_auto_notify',
		'mrdw_push_expo_access_token',
		'mrdw_push_default_title',
		'mrdw_push_default_body',
		'mrdw_push_use_featured_image',
		'mrdw_push_dev_mode',
		'mrdw_push_db_version',
		'mrdw_push_portfolio_auto_notify',
		'mrdw_push_portfolio_default_title',
		'mrdw_push_portfolio_default_body',
		'mrdw_push_portfolio_use_featured_image',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	global $wp_roles;

	if ( isset( $wp_roles ) ) {
		foreach ( $wp_roles->roles as $role_name => $role_info ) {
			$role = get_role( $role_name );
			if ( $role && $role->has_cap( 'mrdw_manage' ) ) {
				$role->remove_cap( 'mrdw_manage' );
			}
		}
	}

	delete_post_meta_by_key( '_mrdw_push_notify' );
	delete_post_meta_by_key( '_mrdw_push_notified' );
	delete_post_meta_by_key( '_mrdw_push_custom_title' );
	delete_post_meta_by_key( '_mrdw_push_custom_body' );
	delete_post_meta_by_key( '_mrdw_push_include_image' );

	wp_unschedule_hook( 'mrdw_push_check_receipts' );
	wp_unschedule_hook( 'mrdw_push_send_scheduled' );

	// ---- Pre-2.0.0 data ----
	// Sites that ran 1.4.0, PackRelay or TailSignal still hold tables and options
	// under the old names. 2.0.0 renamed everything without migrating, so remove
	// those here too rather than leaving them orphaned in the database.
	$legacy_tables = array(
		'packrelay_entries',
		'tailsignal_devices',
		'tailsignal_device_meta',
		'tailsignal_groups',
		'tailsignal_device_groups',
		'tailsignal_notifications',
		'tailsignal_notification_history',
	);

	foreach ( $legacy_tables as $legacy_table ) {
		$wpdb->query( sprintf( 'DROP TABLE IF EXISTS `%s`', esc_sql( $wpdb->prefix . $legacy_table ) ) );
	}

	$legacy_options = array(
		'packrelay_settings',
		'packrelay_db_version',
		'mrdemonwolf_modules',
		'tailsignal_auto_notify',
		'tailsignal_expo_access_token',
		'tailsignal_default_title',
		'tailsignal_default_body',
		'tailsignal_use_featured_image',
		'tailsignal_dev_mode',
		'tailsignal_db_version',
		'tailsignal_portfolio_auto_notify',
		'tailsignal_portfolio_default_title',
		'tailsignal_portfolio_default_body',
		'tailsignal_portfolio_use_featured_image',
	);

	foreach ( $legacy_options as $legacy_option ) {
		delete_option( $legacy_option );
	}

	if ( isset( $wp_roles ) ) {
		foreach ( $wp_roles->roles as $legacy_role_name => $legacy_role_info ) {
			$legacy_role = get_role( $legacy_role_name );
			if ( $legacy_role && $legacy_role->has_cap( 'tailsignal_manage' ) ) {
				$legacy_role->remove_cap( 'tailsignal_manage' );
			}
		}
	}

	delete_post_meta_by_key( '_tailsignal_notify' );
	delete_post_meta_by_key( '_tailsignal_notified' );
	delete_post_meta_by_key( '_tailsignal_custom_title' );
	delete_post_meta_by_key( '_tailsignal_custom_body' );
	delete_post_meta_by_key( '_tailsignal_include_image' );

	wp_unschedule_hook( 'tailsignal_check_receipts' );
	wp_unschedule_hook( 'tailsignal_send_scheduled' );

	// ---- Shared plugin options ----
	delete_option( 'mrdw_modules' );

	// Sweep any leftover transients belonging to either module.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_mrdw_forms_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_mrdw_forms_' ) . '%',
			$wpdb->esc_like( '_transient_mrdw_push_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_mrdw_push_' ) . '%',
			$wpdb->esc_like( '_transient_packrelay_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_packrelay_' ) . '%',
			$wpdb->esc_like( '_transient_tailsignal_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_tailsignal_' ) . '%'
		)
	);
}

if ( is_multisite() ) {
	$mrdw_site_ids = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $mrdw_site_ids as $mrdw_site_id ) {
		switch_to_blog( $mrdw_site_id );
		mrdw_uninstall_site();
		restore_current_blog();
	}
} else {
	mrdw_uninstall_site();
}
