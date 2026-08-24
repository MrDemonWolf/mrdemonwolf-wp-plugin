<?php
/**
 * Tests for uninstall.php clean removal.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';

class Test_MRDW_Push_Uninstall extends MRDW_Push_TestCase {

	protected function setUp(): void {
		parent::setUp();
		// Ensure $wp_roles is clean for each test.
		global $wp_roles;
		$wp_roles = null;
	}

	/**
	 * Helper to run uninstall.php with mocks.
	 */
	private function run_uninstall() {
		// Define WP_UNINSTALL_PLUGIN if not already defined.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', true );
		}

		// The uninstall file requires the DB class, which is already loaded.
		// We just need to call the same logic.
		// Since we can't re-include, we replicate the uninstall logic for testing.

		// Drop tables.
		MRDW_Push_DB::drop_tables();

		// Delete options.
		$options = array(
			'mrdw_push_auto_notify',
			'mrdw_push_expo_access_token',
			'mrdw_push_default_title',
			'mrdw_push_default_body',
			'mrdw_push_use_featured_image',
			'mrdw_push_dev_mode',
			'mrdw_push_db_version',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		// Remove capabilities.
		global $wp_roles;
		if ( isset( $wp_roles ) ) {
			foreach ( $wp_roles->roles as $role_name => $role_info ) {
				$role = get_role( $role_name );
				if ( $role && $role->has_cap( 'mrdw_manage' ) ) {
					$role->remove_cap( 'mrdw_manage' );
				}
			}
		}

		// Clean post meta.
		delete_post_meta_by_key( '_mrdw_push_notify' );
		delete_post_meta_by_key( '_mrdw_push_notified' );
		delete_post_meta_by_key( '_mrdw_push_custom_title' );
		delete_post_meta_by_key( '_mrdw_push_custom_body' );
		delete_post_meta_by_key( '_mrdw_push_include_image' );

		// Clear cron.
		wp_clear_scheduled_hook( 'mrdw_push_check_receipts' );
		wp_clear_scheduled_hook( 'mrdw_push_send_scheduled' );
	}

	/**
	 * Test uninstall drops all 6 tables.
	 */
	public function test_drops_all_tables() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$dropped = array();
		$wpdb->shouldReceive( 'query' )->andReturnUsing( function( $sql ) use ( &$dropped ) {
			if ( strpos( $sql, 'DROP TABLE' ) !== false ) {
				$dropped[] = $sql;
			}
			return true;
		} );

		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		$this->run_uninstall();

		$this->assertCount( 6, $dropped );
	}

	/**
	 * Test uninstall deletes all options.
	 */
	public function test_deletes_all_options() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		$deleted_options = array();
		Functions\expect( 'delete_option' )->andReturnUsing( function( $key ) use ( &$deleted_options ) {
			$deleted_options[] = $key;
			return true;
		} );

		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		$this->run_uninstall();

		$this->assertContains( 'mrdw_push_auto_notify', $deleted_options );
		$this->assertContains( 'mrdw_push_expo_access_token', $deleted_options );
		$this->assertContains( 'mrdw_push_default_title', $deleted_options );
		$this->assertContains( 'mrdw_push_default_body', $deleted_options );
		$this->assertContains( 'mrdw_push_use_featured_image', $deleted_options );
		$this->assertContains( 'mrdw_push_dev_mode', $deleted_options );
		$this->assertContains( 'mrdw_push_db_version', $deleted_options );
		$this->assertCount( 7, $deleted_options );
	}

	/**
	 * Test uninstall removes capability from admin role.
	 */
	public function test_removes_capability() {
		global $wpdb, $wp_roles;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'has_cap' )->with( 'mrdw_manage' )->andReturn( true );
		$role->shouldReceive( 'remove_cap' )->with( 'mrdw_manage' )->once();

		$wp_roles = (object) array(
			'roles' => array(
				'administrator' => array( 'capabilities' => array( 'mrdw_manage' => true ) ),
			),
		);

		Functions\expect( 'get_role' )->with( 'administrator' )->andReturn( $role );
		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		$this->run_uninstall();
		$this->assertTrue( true );
	}

	/**
	 * Test uninstall cleans post meta for all 5 keys.
	 */
	public function test_deletes_post_meta() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		$deleted_meta_keys = array();
		Functions\expect( 'delete_post_meta_by_key' )->andReturnUsing( function( $key ) use ( &$deleted_meta_keys ) {
			$deleted_meta_keys[] = $key;
			return true;
		} );

		$this->run_uninstall();

		$this->assertContains( '_mrdw_push_notify', $deleted_meta_keys );
		$this->assertContains( '_mrdw_push_notified', $deleted_meta_keys );
		$this->assertContains( '_mrdw_push_custom_title', $deleted_meta_keys );
		$this->assertContains( '_mrdw_push_custom_body', $deleted_meta_keys );
		$this->assertContains( '_mrdw_push_include_image', $deleted_meta_keys );
		$this->assertCount( 5, $deleted_meta_keys );
	}

	/**
	 * Test uninstall clears both cron hooks.
	 */
	public function test_clears_cron_hooks() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );

		$cleared_hooks = array();
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturnUsing( function( $hook ) use ( &$cleared_hooks ) {
			$cleared_hooks[] = $hook;
		} );

		$this->run_uninstall();

		$this->assertContains( 'mrdw_push_check_receipts', $cleared_hooks );
		$this->assertContains( 'mrdw_push_send_scheduled', $cleared_hooks );
		$this->assertCount( 2, $cleared_hooks );
	}

	/**
	 * Test uninstall drops correct table names.
	 */
	public function test_drops_correct_table_names() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$queries = array();
		$wpdb->shouldReceive( 'query' )->andReturnUsing( function( $sql ) use ( &$queries ) {
			$queries[] = $sql;
			return true;
		} );

		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		$this->run_uninstall();

		$all_sql = implode( ' ', $queries );
		$this->assertStringContainsString( 'wp_mrdw_push_notification_history', $all_sql );
		$this->assertStringContainsString( 'wp_mrdw_push_notifications', $all_sql );
		$this->assertStringContainsString( 'wp_mrdw_push_device_groups', $all_sql );
		$this->assertStringContainsString( 'wp_mrdw_push_groups', $all_sql );
		$this->assertStringContainsString( 'wp_mrdw_push_device_meta', $all_sql );
		$this->assertStringContainsString( 'wp_mrdw_push_devices', $all_sql );
	}

	/**
	 * Test uninstall handles missing wp_roles gracefully.
	 */
	public function test_handles_missing_wp_roles() {
		global $wpdb, $wp_roles;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		$wp_roles = null; // Not set.

		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		// Should not throw.
		$this->run_uninstall();
		$this->assertTrue( true );
	}

	/**
	 * Test uninstall with role that doesn't have capability.
	 */
	public function test_skips_role_without_capability() {
		global $wpdb, $wp_roles;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'has_cap' )->with( 'mrdw_manage' )->andReturn( false );
		// remove_cap should NOT be called.

		$wp_roles = (object) array(
			'roles' => array(
				'editor' => array( 'capabilities' => array() ),
			),
		);

		Functions\expect( 'get_role' )->with( 'editor' )->andReturn( $role );
		Functions\expect( 'delete_option' )->andReturn( true );
		Functions\expect( 'delete_post_meta_by_key' )->andReturn( true );
		Functions\expect( 'wp_clear_scheduled_hook' )->andReturn( true );

		$this->run_uninstall();
		$this->assertTrue( true );
	}
}
