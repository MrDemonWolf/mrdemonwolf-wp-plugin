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

	// ---- Shared plugin options ----
	delete_option( 'mrdw_modules' );

	// Sweep any leftover transients belonging to either module.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_mrdw_forms_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_mrdw_forms_' ) . '%',
			$wpdb->esc_like( '_transient_mrdw_push_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_mrdw_push_' ) . '%'
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
