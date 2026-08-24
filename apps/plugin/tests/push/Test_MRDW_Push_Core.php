<?php
/**
 * Tests for MRDW_Push core class.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-loader.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-i18n.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-expo.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-notification.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-cron.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/rest-api/class-mrdw-push-rest-controller.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin-settings.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin-send.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin-groups.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin-devices.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin-history.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin-dashboard.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-meta-box.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push.php';

class Test_MRDW_Push_Core extends MRDW_Push_TestCase {

	/**
	 * Test get_loader returns MRDW_Push_Loader instance.
	 */
	public function test_get_loader_returns_loader_instance() {
		Functions\when( 'is_admin' )->justReturn( false );

		$plugin = new MRDW_Push();
		$this->assertInstanceOf( MRDW_Push_Loader::class, $plugin->get_loader() );
	}

	/**
	 * Test run triggers loader run.
	 */
	public function test_run_triggers_loader_run() {
		Functions\when( 'is_admin' )->justReturn( false );

		// After run(), add_action and add_filter should be called.
		Functions\expect( 'add_action' )->atLeast()->once();
		Functions\expect( 'add_filter' )->atLeast()->once();

		$plugin = new MRDW_Push();
		$plugin->run();
		$this->assertTrue( true );
	}

	/**
	 * Test REST hooks are registered.
	 */
	public function test_rest_hooks_registered() {
		Functions\when( 'is_admin' )->justReturn( false );

		$registered_actions = array();
		$registered_filters = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturnUsing( function( $hook ) use ( &$registered_filters ) {
			$registered_filters[] = $hook;
		} );

		$plugin = new MRDW_Push();
		$plugin->run();

		$this->assertContains( 'rest_api_init', $registered_actions );
		$this->assertContains( 'rest_pre_serve_request', $registered_filters );
	}

	/**
	 * Test notification hooks registered.
	 */
	public function test_notification_hooks_registered() {
		Functions\when( 'is_admin' )->justReturn( false );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		$this->assertContains( 'transition_post_status', $registered_actions );
	}

	/**
	 * Test cron hooks registered.
	 */
	public function test_cron_hooks_registered() {
		Functions\when( 'is_admin' )->justReturn( false );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		$this->assertContains( 'mrdw_push_check_receipts', $registered_actions );
		$this->assertContains( 'mrdw_push_send_scheduled', $registered_actions );
	}

	/**
	 * Test i18n hook registered.
	 */
	public function test_i18n_hook_registered() {
		Functions\when( 'is_admin' )->justReturn( false );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		$this->assertContains( 'plugins_loaded', $registered_actions );
	}

	/**
	 * Test admin hooks registered when is_admin true.
	 */
	public function test_admin_hooks_registered_when_admin() {
		Functions\when( 'is_admin' )->justReturn( true );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		$this->assertContains( 'admin_menu', $registered_actions );
		$this->assertContains( 'admin_enqueue_scripts', $registered_actions );
		$this->assertContains( 'admin_init', $registered_actions );
		$this->assertContains( 'add_meta_boxes', $registered_actions );
		$this->assertContains( 'save_post', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_send_notification', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_cancel_scheduled', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_save_group', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_delete_group', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_delete_all_notifications', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_update_device', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_toggle_dev', $registered_actions );
		$this->assertContains( 'wp_ajax_mrdw_push_quick_send', $registered_actions );
	}

	/**
	 * Test admin hooks NOT registered when is_admin false.
	 */
	public function test_admin_hooks_not_registered_when_not_admin() {
		Functions\when( 'is_admin' )->justReturn( false );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		$this->assertNotContains( 'admin_menu', $registered_actions );
		$this->assertNotContains( 'admin_init', $registered_actions );
		$this->assertNotContains( 'wp_ajax_mrdw_push_send_notification', $registered_actions );
	}

	/**
	 * Test non-admin still has base hooks.
	 */
	public function test_non_admin_still_has_base_hooks() {
		Functions\when( 'is_admin' )->justReturn( false );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		// Base hooks should always be there.
		$this->assertContains( 'plugins_loaded', $registered_actions );
		$this->assertContains( 'rest_api_init', $registered_actions );
		$this->assertContains( 'transition_post_status', $registered_actions );
		$this->assertContains( 'mrdw_push_check_receipts', $registered_actions );
	}

	/**
	 * Test constructor initializes loader.
	 */
	public function test_constructor_initializes_loader() {
		Functions\when( 'is_admin' )->justReturn( false );

		$plugin = new MRDW_Push();
		$loader = $plugin->get_loader();

		$this->assertNotNull( $loader );
	}

	/**
	 * Test admin enqueue hooks registered when admin.
	 */
	public function test_admin_enqueue_hooks_registered() {
		Functions\when( 'is_admin' )->justReturn( true );

		$registered_actions = array();

		Functions\expect( 'add_action' )->andReturnUsing( function( $hook ) use ( &$registered_actions ) {
			$registered_actions[] = $hook;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		// admin_enqueue_scripts registered twice (styles + scripts).
		$count = array_count_values( $registered_actions );
		$this->assertGreaterThanOrEqual( 2, $count['admin_enqueue_scripts'] );
	}

	/**
	 * Test total hook count for non-admin context.
	 */
	public function test_non_admin_hook_count() {
		Functions\when( 'is_admin' )->justReturn( false );

		$action_count = 0;

		Functions\expect( 'add_action' )->andReturnUsing( function() use ( &$action_count ) {
			$action_count++;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		// plugins_loaded + rest_api_init + transition_post_status + 2 cron = 5.
		// Updates are handled by MRDW_Updater (plugin-update-checker), not this module.
		$this->assertSame( 5, $action_count );
	}

	/**
	 * Test total hook count for admin context.
	 */
	public function test_admin_hook_count() {
		Functions\when( 'is_admin' )->justReturn( true );

		$action_count = 0;

		Functions\expect( 'add_action' )->andReturnUsing( function() use ( &$action_count ) {
			$action_count++;
		} );
		Functions\expect( 'add_filter' )->andReturn( true );

		$plugin = new MRDW_Push();
		$plugin->run();

		// 5 base + admin_menu + 2 enqueue + admin_footer + admin_init
		// + add_meta_boxes + save_post + quick_send + send + cancel + save_group + delete_group
		// + delete_all + update_device + toggle_dev = 5 + 15 = 20.
		$this->assertSame( 20, $action_count );
	}
}
