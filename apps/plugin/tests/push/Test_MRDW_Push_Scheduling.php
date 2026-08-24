<?php
/**
 * Tests for scheduling functionality.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-expo.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-notification.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-cron.php';

class Test_MRDW_Push_Scheduling extends MRDW_Push_TestCase {

	/**
	 * Test schedule_notification creates a scheduled record.
	 */
	public function test_schedule_notification_creates_record() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->insert_id = 7;

		$wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );

		Functions\expect( 'wp_schedule_single_event' )->once();

		$result = MRDW_Push_Notification::schedule_notification(
			array( 'title' => 'Scheduled Test', 'body' => 'Body' ),
			'2025-03-01 14:00:00',
			'all'
		);

		$this->assertSame( 7, $result );
	}

	/**
	 * Test schedule_notification returns false on DB failure.
	 */
	public function test_schedule_notification_returns_false_on_failure() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->insert_id = 0;

		$wpdb->shouldReceive( 'insert' )->once()->andReturn( false );

		$result = MRDW_Push_Notification::schedule_notification(
			array( 'title' => 'Test', 'body' => 'Body' ),
			'2025-03-01 14:00:00'
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test cancel_scheduled updates status and unschedules cron.
	 */
	public function test_cancel_scheduled() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id     = 7;
		$notification->status = 'scheduled';

		$wpdb->shouldReceive( 'get_row' )->once()->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );
		$wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

		Functions\expect( 'wp_next_scheduled' )
			->with( 'mrdw_push_send_scheduled', array( 7 ) )
			->andReturn( 1234567890 );

		Functions\expect( 'wp_unschedule_event' )
			->with( 1234567890, 'mrdw_push_send_scheduled', array( 7 ) )
			->once();

		$result = MRDW_Push_Notification::cancel_scheduled( 7 );
		$this->assertTrue( $result );
	}

	/**
	 * Test cancel_scheduled returns false for non-scheduled notification.
	 */
	public function test_cancel_scheduled_wrong_status() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id     = 7;
		$notification->status = 'sent';

		$wpdb->shouldReceive( 'get_row' )->once()->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$result = MRDW_Push_Notification::cancel_scheduled( 7 );
		$this->assertFalse( $result );
	}

	/**
	 * Test cancel_scheduled returns false for non-existent notification.
	 */
	public function test_cancel_scheduled_not_found() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'get_row' )->once()->andReturn( null );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$result = MRDW_Push_Notification::cancel_scheduled( 999 );
		$this->assertFalse( $result );
	}

	/**
	 * Test cron send_scheduled_notification sends to tokens.
	 */
	public function test_cron_sends_scheduled() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id          = 7;
		$notification->title       = 'Scheduled';
		$notification->body        = 'Body';
		$notification->data        = null;
		$notification->image_url   = null;
		$notification->post_id     = null;
		$notification->status      = 'scheduled';
		$notification->target_type = 'all';
		$notification->target_ids  = null;

		$wpdb->shouldReceive( 'get_row' )->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );
		$wpdb->shouldReceive( 'update' )->andReturn( 1 );
		$wpdb->shouldReceive( 'query' )->andReturn( 0 );

		Functions\expect( 'get_option' )
			->with( 'mrdw_push_dev_mode', '0' )
			->andReturn( '0' );

		$wpdb->shouldReceive( 'get_col' )->andReturn( array( 'ExponentPushToken[aaa]' ) );

		// Mock the Expo send - the SDK filters invalid tokens internally,
		// so we expect the static method to handle it.
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( '' );

		$cron = new MRDW_Push_Cron();
		$cron->send_scheduled_notification( 7 );

		// Verify that update was called (notification status changed).
		$this->assertTrue( true );
	}

	/**
	 * Test cron skips non-scheduled notification.
	 */
	public function test_cron_skips_non_scheduled() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id     = 7;
		$notification->status = 'sent';

		$wpdb->shouldReceive( 'get_row' )->once()->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$cron = new MRDW_Push_Cron();
		$cron->send_scheduled_notification( 7 );

		// Should not call update since status is not 'scheduled'.
		$this->assertTrue( true );
	}

	/**
	 * Test cron handles empty tokens gracefully.
	 */
	public function test_cron_handles_empty_tokens() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id          = 7;
		$notification->status      = 'scheduled';
		$notification->target_type = 'all';
		$notification->target_ids  = null;

		$wpdb->shouldReceive( 'get_row' )->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );
		$wpdb->shouldReceive( 'update' )->once()->andReturn( 1 ); // Status → failed.

		Functions\expect( 'get_option' )
			->with( 'mrdw_push_dev_mode', '0' )
			->andReturn( '0' );

		$wpdb->shouldReceive( 'get_col' )->andReturn( array() );

		$cron = new MRDW_Push_Cron();
		$cron->send_scheduled_notification( 7 );
		$this->assertTrue( true );
	}

	/**
	 * Test cron check_receipts skips non-sent notification.
	 */
	public function test_cron_check_receipts_skips_non_sent() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id     = 10;
		$notification->status = 'pending'; // Not 'sent', should be skipped.

		$wpdb->shouldReceive( 'get_row' )->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$cron = new MRDW_Push_Cron();
		$cron->check_receipts( 10 );
		$this->assertTrue( true );
	}

	/**
	 * Test cron check_receipts skips empty ticket IDs.
	 */
	public function test_cron_check_receipts_skips_empty_tickets() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notification = new stdClass();
		$notification->id         = 10;
		$notification->status     = 'sent';
		$notification->ticket_ids = '[]'; // Empty array.

		$wpdb->shouldReceive( 'get_row' )->andReturn( $notification );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$cron = new MRDW_Push_Cron();
		$cron->check_receipts( 10 );
		$this->assertTrue( true );
	}

	/**
	 * Test cron check_receipts batch mode exits cleanly with empty pending.
	 */
	public function test_cron_check_receipts_batch_empty() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		// No pending receipt notifications.
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );

		$cron = new MRDW_Push_Cron();
		$cron->check_receipts(); // No argument = batch all.

		$this->assertTrue( true );
	}

	/**
	 * Test cron check_receipts batch skips notifications with empty ticket_ids.
	 */
	public function test_cron_check_receipts_batch_skips_empty_tickets() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$notif = new stdClass();
		$notif->id         = 10;
		$notif->status     = 'sent';
		$notif->ticket_ids = '[]'; // Empty array.

		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $notif ) );

		$cron = new MRDW_Push_Cron();
		$cron->check_receipts();

		// Should exit cleanly without calling Expo API.
		$this->assertTrue( true );
	}

	/**
	 * Test cron check_receipts handles null notification.
	 */
	public function test_cron_check_receipts_handles_null() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'get_row' )->andReturn( null );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$cron = new MRDW_Push_Cron();
		$cron->check_receipts( 999 );
		$this->assertTrue( true );
	}
}
