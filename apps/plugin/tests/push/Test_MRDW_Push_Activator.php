<?php
/**
 * Tests for MRDW_Push_Activator and MRDW_Push_Deactivator.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-activator.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-deactivator.php';

class Test_MRDW_Push_Activator extends MRDW_Push_TestCase {

	/**
	 * Test activate creates tables and sets defaults.
	 */
	public function test_activate() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		// create_tables calls dbDelta.
		Functions\expect( 'dbDelta' )->andReturn( array() );

		// get_option returns false for new options.
		Functions\expect( 'get_option' )
			->andReturn( false );

		// add_option called for each default.
		Functions\expect( 'add_option' )->times( 11 );

		// get_role + add_cap.
		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->with( 'mrdw_manage' )->once();

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( $role );

		MRDW_Push_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate skips existing options.
	 */
	public function test_activate_skips_existing_options() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		// Options already exist (get_option returns non-false).
		Functions\expect( 'get_option' )->andReturn( '1' );

		// add_option should NOT be called.
		// (Brain Monkey will verify no unexpected calls.)

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( $role );

		MRDW_Push_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate handles null administrator role.
	 */
	public function test_activate_no_admin_role() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->times( 11 );

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( null );

		// Should not throw even without admin role.
		MRDW_Push_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test deactivate clears all cron events including per-notification args.
	 */
	public function test_deactivate_clears_cron() {
		Functions\expect( 'wp_unschedule_hook' )
			->with( 'mrdw_push_check_receipts' )
			->once();

		Functions\expect( 'wp_unschedule_hook' )
			->with( 'mrdw_push_send_scheduled' )
			->once();

		MRDW_Push_Deactivator::deactivate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate re-schedules pending scheduled notifications.
	 */
	public function test_activate_reschedules_pending() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );
		Functions\expect( 'get_option' )->andReturn( '1' );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		$notification               = new stdClass();
		$notification->id           = 7;
		$notification->scheduled_at = '2099-01-01 10:00:00';

		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $notification ) );

		Functions\expect( 'wp_next_scheduled' )
			->with( 'mrdw_push_send_scheduled', array( 7 ) )
			->andReturn( false );

		Functions\expect( 'wp_schedule_single_event' )
			->with( strtotime( '2099-01-01 10:00:00' ), 'mrdw_push_send_scheduled', array( 7 ) )
			->once();

		MRDW_Push_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate skips re-scheduling when an event already exists.
	 */
	public function test_activate_skips_already_scheduled() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );

		Functions\expect( 'dbDelta' )->andReturn( array() );
		Functions\expect( 'get_option' )->andReturn( '1' );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		$notification               = new stdClass();
		$notification->id           = 7;
		$notification->scheduled_at = '2099-01-01 10:00:00';

		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $notification ) );

		Functions\expect( 'wp_next_scheduled' )
			->with( 'mrdw_push_send_scheduled', array( 7 ) )
			->andReturn( 1234567890 );

		// wp_schedule_single_event should NOT be called.
		MRDW_Push_Activator::activate();
		$this->assertTrue( true );
	}

	/**
	 * Test activate sets all 11 default options.
	 */
	public function test_activate_sets_all_defaults() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		MRDW_Push_Activator::activate();

		// Verify all 11 options are set.
		$expected_keys = array(
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

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $added_options, "Missing option: $key" );
		}
		$this->assertCount( 11, $added_options );
	}

	/**
	 * Test activate sets mrdw_push_db_version to MRDW_VERSION.
	 */
	public function test_activate_sets_db_version() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		MRDW_Push_Activator::activate();

		$this->assertSame( MRDW_VERSION, $added_options['mrdw_push_db_version'] );
	}

	/**
	 * Test activate sets correct default values for portfolio options.
	 */
	public function test_activate_portfolio_defaults() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		MRDW_Push_Activator::activate();

		$this->assertSame( '1', $added_options['mrdw_push_portfolio_auto_notify'] );
		$this->assertSame( 'New Project: {post_title}', $added_options['mrdw_push_portfolio_default_title'] );
		$this->assertSame( '{post_title} by {author_name}', $added_options['mrdw_push_portfolio_default_body'] );
		$this->assertSame( '1', $added_options['mrdw_push_portfolio_use_featured_image'] );
	}

	/**
	 * Test activate sets correct default values for base options.
	 */
	public function test_activate_base_defaults() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );

		$added_options = array();
		Functions\expect( 'get_option' )->andReturn( false );
		Functions\expect( 'add_option' )->andReturnUsing( function( $key, $value ) use ( &$added_options ) {
			$added_options[ $key ] = $value;
		} );

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )->once();
		Functions\expect( 'get_role' )->andReturn( $role );

		MRDW_Push_Activator::activate();

		$this->assertSame( '1', $added_options['mrdw_push_auto_notify'] );
		$this->assertSame( '', $added_options['mrdw_push_expo_access_token'] );
		$this->assertSame( 'New from {site_name}', $added_options['mrdw_push_default_title'] );
		$this->assertSame( '{post_title}', $added_options['mrdw_push_default_body'] );
		$this->assertSame( '1', $added_options['mrdw_push_use_featured_image'] );
		$this->assertSame( '0', $added_options['mrdw_push_dev_mode'] );
	}

	/**
	 * Test activate adds capability to administrator role.
	 */
	public function test_activate_adds_capability() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_charset_collate' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		Functions\expect( 'dbDelta' )->andReturn( array() );
		Functions\expect( 'get_option' )->andReturn( '1' ); // Options exist.

		$role = Mockery::mock( 'WP_Role' );
		$role->shouldReceive( 'add_cap' )
			->with( 'mrdw_manage' )
			->once();

		Functions\expect( 'get_role' )
			->with( 'administrator' )
			->andReturn( $role );

		MRDW_Push_Activator::activate();
		$this->assertTrue( true );
	}
}
