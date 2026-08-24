<?php
/**
 * Fired during plugin activation.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		// Create database tables.
		MRDW_Push_DB::create_tables();

		// Set default options.
		$defaults = array(
			'mrdw_push_auto_notify'                  => '1',
			'mrdw_push_expo_access_token'            => '',
			'mrdw_push_default_title'                => 'New from {site_name}',
			'mrdw_push_default_body'                 => '{post_title}',
			'mrdw_push_use_featured_image'           => '1',
			'mrdw_push_dev_mode'                     => '0',
			'mrdw_push_db_version'                   => MRDW_VERSION,
			'mrdw_push_portfolio_auto_notify'        => '1',
			'mrdw_push_portfolio_default_title'      => 'New Project: {post_title}',
			'mrdw_push_portfolio_default_body'       => '{post_title} by {author_name}',
			'mrdw_push_portfolio_use_featured_image' => '1',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}

		// Add custom capability to administrator role.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'mrdw_manage' );
		}

		// Re-schedule pending scheduled notifications. Deactivation clears
		// their cron events; without this they would sit in 'scheduled'
		// status forever. Overdue ones are sent shortly after reactivation.
		$notifications = MRDW_Push_DB::get_scheduled_notifications();
		foreach ( $notifications as $notification ) {
			$args = array( (int) $notification->id );
			if ( wp_next_scheduled( 'mrdw_push_send_scheduled', $args ) ) {
				continue;
			}
			$timestamp = (int) get_gmt_from_date( $notification->scheduled_at, 'U' );
			wp_schedule_single_event( max( $timestamp, time() + MINUTE_IN_SECONDS ), 'mrdw_push_send_scheduled', $args );
		}
	}
}
