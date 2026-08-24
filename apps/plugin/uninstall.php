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

require_once plugin_dir_path( __FILE__ ) . 'modules/push/includes/class-tailsignal-db.php';

/**
 * Remove all plugin data for the current site.
 */
function mrdw_uninstall_site() {
	global $wpdb;

	// ---- Forms module (formerly PackRelay) ----
	$wpdb->query( sprintf( 'DROP TABLE IF EXISTS `%s`', esc_sql( $wpdb->prefix . 'packrelay_entries' ) ) );

	delete_option( 'packrelay_settings' );
	delete_option( 'packrelay_db_version' );
	delete_transient( 'packrelay_provider_notice' );

	// ---- Push module (formerly TailSignal) ----
	TailSignal_DB::drop_tables();

	$options = array(
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

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	global $wp_roles;

	if ( isset( $wp_roles ) ) {
		foreach ( $wp_roles->roles as $role_name => $role_info ) {
			$role = get_role( $role_name );
			if ( $role && $role->has_cap( 'tailsignal_manage' ) ) {
				$role->remove_cap( 'tailsignal_manage' );
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
	delete_option( 'mrdemonwolf_modules' );

	// Sweep any leftover transients belonging to either module.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
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
