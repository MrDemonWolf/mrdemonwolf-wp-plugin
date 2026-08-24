<?php
/**
 * Tests for device groups functionality.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';

class Test_MRDW_Push_Groups extends MRDW_Push_TestCase {

	/**
	 * Test create_group returns ID.
	 */
	public function test_create_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->insert_id = 5;

		$wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );

		$result = MRDW_Push_DB::create_group( array(
			'name'        => 'Beta Testers',
			'description' => 'Early access group',
		) );

		$this->assertSame( 5, $result );
	}

	/**
	 * Test create_group returns false on failure.
	 */
	public function test_create_group_failure() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';
		$wpdb->insert_id = 0;

		$wpdb->shouldReceive( 'insert' )->once()->andReturn( false );

		$result = MRDW_Push_DB::create_group( array(
			'name' => 'Test',
		) );

		$this->assertFalse( $result );
	}

	/**
	 * Test update_group.
	 */
	public function test_update_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

		$result = MRDW_Push_DB::update_group( 5, array(
			'name'        => 'Updated Name',
			'description' => 'Updated description',
		) );

		$this->assertTrue( $result );
	}

	/**
	 * Test delete_group removes assignments and group.
	 */
	public function test_delete_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		// Delete from device_groups pivot + groups table.
		$wpdb->shouldReceive( 'delete' )->twice()->andReturn( 1 );

		$result = MRDW_Push_DB::delete_group( 5 );
		$this->assertTrue( $result );
	}

	/**
	 * Test get_group returns group object.
	 */
	public function test_get_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$group = new stdClass();
		$group->id   = 5;
		$group->name = 'Beta Testers';

		$wpdb->shouldReceive( 'get_row' )->once()->andReturn( $group );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$result = MRDW_Push_DB::get_group( 5 );
		$this->assertSame( 'Beta Testers', $result->name );
	}

	/**
	 * Test get_all_groups returns array.
	 */
	public function test_get_all_groups() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$groups = array(
			(object) array( 'id' => 1, 'name' => 'Group A' ),
			(object) array( 'id' => 2, 'name' => 'Group B' ),
		);

		$wpdb->shouldReceive( 'get_results' )->once()->andReturn( $groups );

		$result = MRDW_Push_DB::get_all_groups();
		$this->assertCount( 2, $result );
	}

	/**
	 * Test assign_devices_to_group.
	 */
	public function test_assign_devices_to_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		// Uses $wpdb->replace() for each device.
		$wpdb->shouldReceive( 'replace' )->twice()->andReturn( 1 );

		MRDW_Push_DB::assign_devices_to_group( 5, array( 1, 2 ) );
		$this->assertTrue( true );
	}

	/**
	 * Test remove_devices_from_group.
	 */
	public function test_remove_devices_from_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'delete' )->twice()->andReturn( 1 );

		MRDW_Push_DB::remove_devices_from_group( 5, array( 1, 2 ) );
		$this->assertTrue( true );
	}

	/**
	 * Test set_group_devices clears and reassigns.
	 */
	public function test_set_group_devices() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		// First, delete all existing assignments.
		$wpdb->shouldReceive( 'delete' )->once()->andReturn( 1 );

		// Then replace (insert) new assignments.
		$wpdb->shouldReceive( 'replace' )->twice()->andReturn( 1 );

		MRDW_Push_DB::set_group_devices( 5, array( 10, 20 ) );
		$this->assertTrue( true );
	}

	/**
	 * Test get_group_device_ids returns array of IDs.
	 */
	public function test_get_group_device_ids() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'get_col' )->once()->andReturn( array( '1', '2', '3' ) );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$result = MRDW_Push_DB::get_group_device_ids( 5 );
		$this->assertCount( 3, $result );
	}

	/**
	 * Test get_device_groups returns groups for a device.
	 */
	public function test_get_device_groups() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$groups = array(
			(object) array( 'id' => 1, 'name' => 'Beta' ),
			(object) array( 'id' => 2, 'name' => 'VIP' ),
		);

		$wpdb->shouldReceive( 'get_results' )->once()->andReturn( $groups );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$result = MRDW_Push_DB::get_device_groups( 42 );
		$this->assertCount( 2, $result );
		$this->assertSame( 'Beta', $result[0]->name );
	}

	/**
	 * Test get_tokens_by_target with group target.
	 */
	public function test_get_tokens_by_target_group() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'get_col' )->once()->andReturn( array(
			'ExponentPushToken[group1]',
			'ExponentPushToken[group2]',
		) );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$tokens = MRDW_Push_DB::get_tokens_by_target( 'group', array( 5 ) );
		$this->assertCount( 2, $tokens );
	}

	/**
	 * Test get_tokens_by_target with specific target.
	 */
	public function test_get_tokens_by_target_specific() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldReceive( 'get_col' )->once()->andReturn( array(
			'ExponentPushToken[specific1]',
		) );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$tokens = MRDW_Push_DB::get_tokens_by_target( 'specific', array( 42 ) );
		$this->assertCount( 1, $tokens );
	}

	/**
	 * Test get_groups_with_counts includes device count.
	 */
	public function test_get_groups_with_counts() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$groups = array(
			(object) array( 'id' => 1, 'name' => 'Beta', 'device_count' => 12 ),
			(object) array( 'id' => 2, 'name' => 'VIP', 'device_count' => 38 ),
		);

		$wpdb->shouldReceive( 'get_results' )->once()->andReturn( $groups );

		$result = MRDW_Push_DB::get_groups_with_counts();
		$this->assertCount( 2, $result );
		$this->assertSame( 12, $result[0]->device_count );
	}
}
