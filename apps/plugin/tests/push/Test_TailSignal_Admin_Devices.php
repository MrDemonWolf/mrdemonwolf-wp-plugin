<?php
/**
 * Tests for TailSignal_Admin_Devices AJAX handlers.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-tailsignal-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-tailsignal-admin-devices.php';

class Test_TailSignal_Admin_Devices extends TailSignal_TestCase {

	protected function tearDown(): void {
		$_POST = array();
		$_GET  = array();
		parent::tearDown();
	}

	// ── handle_update_device ────────────────────────────────────

	/**
	 * Test handle_update_device checks nonce.
	 */
	public function test_update_device_checks_nonce() {
		Functions\expect( 'check_ajax_referer' )
			->with( 'tailsignal_nonce', 'nonce' )
			->once();

		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_update_device();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_update_device checks permission.
	 */
	public function test_update_device_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )
			->with( 'tailsignal_manage' )
			->andReturn( false );

		$error_data = null;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$error_data ) {
			$error_data = $data;
			throw new \RuntimeException( 'denied' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_update_device();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'Permission', $error_data['message'] );
	}

	/**
	 * Test handle_update_device rejects missing device_id.
	 */
	public function test_update_device_missing_device_id() {
		$_POST = array( 'device_id' => '0' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$error_data = null;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$error_data ) {
			$error_data = $data;
			throw new \RuntimeException( 'invalid' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_update_device();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'Invalid', $error_data['message'] );
	}

	/**
	 * Test handle_update_device success.
	 */
	public function test_update_device_success() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'device_id'  => '5',
			'user_label' => 'My Phone',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$wpdb->shouldReceive( 'update' )->andReturn( 1 );

		$success_data = null;
		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function( $data ) use ( &$success_data ) {
			$success_data = $data;
			throw new \RuntimeException( 'success' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_update_device();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'updated', $success_data['message'] );
	}

	/**
	 * Test handle_update_device failure.
	 */
	public function test_update_device_failure() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'device_id'  => '5',
			'user_label' => 'My Phone',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$wpdb->shouldReceive( 'update' )->andReturn( false );

		$error_data = null;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$error_data ) {
			$error_data = $data;
			throw new \RuntimeException( 'failed' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_update_device();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'Failed', $error_data['message'] );
	}

	/**
	 * Test handle_update_device calls DB with correct args.
	 */
	public function test_update_device_correct_db_args() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'device_id'  => '7',
			'user_label' => 'Test Label',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$update_args = null;
		$wpdb->shouldReceive( 'update' )
			->once()
			->andReturnUsing( function() use ( &$update_args ) {
				$update_args = func_get_args();
				return 1;
			} );

		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function() {
			throw new \RuntimeException( 'success' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_update_device();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertSame( 'wp_tailsignal_devices', $update_args[0] );
		$this->assertSame( 'Test Label', $update_args[1]['user_label'] );
		$this->assertSame( array( 'id' => 7 ), $update_args[2] );
	}

	// ── handle_toggle_dev ───────────────────────────────────────

	/**
	 * Test handle_toggle_dev checks nonce.
	 */
	public function test_toggle_dev_checks_nonce() {
		Functions\expect( 'check_ajax_referer' )
			->with( 'tailsignal_nonce', 'nonce' )
			->once();

		Functions\expect( 'current_user_can' )->andReturn( false );

		$exited = false;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function() use ( &$exited ) {
			$exited = true;
			throw new \RuntimeException( 'denied' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_toggle_dev();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertTrue( $exited );
	}

	/**
	 * Test handle_toggle_dev checks permission.
	 */
	public function test_toggle_dev_checks_permission() {
		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( false );

		$error_data = null;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$error_data ) {
			$error_data = $data;
			throw new \RuntimeException( 'denied' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_toggle_dev();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'Permission', $error_data['message'] );
	}

	/**
	 * Test handle_toggle_dev rejects missing device_id.
	 */
	public function test_toggle_dev_missing_device_id() {
		$_POST = array( 'device_id' => '0' );

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$error_data = null;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$error_data ) {
			$error_data = $data;
			throw new \RuntimeException( 'invalid' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_toggle_dev();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'Invalid', $error_data['message'] );
	}

	/**
	 * Test handle_toggle_dev sets is_dev to 1.
	 */
	public function test_toggle_dev_sets_dev_on() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'device_id' => '5',
			'is_dev'    => '1',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$update_args = null;
		$wpdb->shouldReceive( 'update' )
			->once()
			->andReturnUsing( function() use ( &$update_args ) {
				$update_args = func_get_args();
				return 1;
			} );

		$success_data = null;
		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function( $data ) use ( &$success_data ) {
			$success_data = $data;
			throw new \RuntimeException( 'success' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_toggle_dev();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'marked as dev', $success_data['message'] );
		$this->assertSame( 1, $update_args[1]['is_dev'] );
	}

	/**
	 * Test handle_toggle_dev sets is_dev to 0.
	 */
	public function test_toggle_dev_sets_dev_off() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'device_id' => '5',
			'is_dev'    => '0',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$update_args = null;
		$wpdb->shouldReceive( 'update' )
			->once()
			->andReturnUsing( function() use ( &$update_args ) {
				$update_args = func_get_args();
				return 1;
			} );

		$success_data = null;
		Functions\expect( 'wp_send_json_success' )->once()->andReturnUsing( function( $data ) use ( &$success_data ) {
			$success_data = $data;
			throw new \RuntimeException( 'success' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_toggle_dev();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'removed from dev', $success_data['message'] );
		$this->assertSame( 0, $update_args[1]['is_dev'] );
	}

	/**
	 * Test handle_toggle_dev failure.
	 */
	public function test_toggle_dev_failure() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$_POST = array(
			'device_id' => '5',
			'is_dev'    => '1',
		);

		Functions\expect( 'check_ajax_referer' )->once();
		Functions\expect( 'current_user_can' )->andReturn( true );

		$wpdb->shouldReceive( 'update' )->andReturn( false );

		$error_data = null;
		Functions\expect( 'wp_send_json_error' )->once()->andReturnUsing( function( $data ) use ( &$error_data ) {
			$error_data = $data;
			throw new \RuntimeException( 'failed' );
		} );

		$devices = new TailSignal_Admin_Devices();
		try {
			$devices->handle_toggle_dev();
		} catch ( \RuntimeException $e ) {
			// Expected.
		}
		$this->assertStringContainsString( 'Failed', $error_data['message'] );
	}
}
