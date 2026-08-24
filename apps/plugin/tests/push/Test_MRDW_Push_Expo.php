<?php
/**
 * Tests for MRDW_Push_Expo.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-expo.php';

class Test_MRDW_Push_Expo extends MRDW_Push_TestCase {

	protected function setUp(): void {
		parent::setUp();
		MRDW_Push_Expo::reset_instance();
	}

	/**
	 * Test is_valid_token validates Expo tokens.
	 */
	public function test_is_valid_token() {
		$this->assertTrue( MRDW_Push_Expo::is_valid_token( 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]' ) );
		$this->assertFalse( MRDW_Push_Expo::is_valid_token( 'invalid-token' ) );
		$this->assertFalse( MRDW_Push_Expo::is_valid_token( '' ) );
	}

	/**
	 * Test build_message creates correct message.
	 */
	public function test_build_message() {
		$message = MRDW_Push_Expo::build_message( array(
			'title' => 'Test Title',
			'body'  => 'Test Body',
		) );

		$this->assertInstanceOf( \ExpoSDK\ExpoMessage::class, $message );

		$array = $message->toArray();
		$this->assertSame( 'Test Title', $array['title'] );
		$this->assertSame( 'Test Body', $array['body'] );
		$this->assertSame( 'default', $array['sound'] );
	}

	/**
	 * Test build_message with image URL uses richContent field.
	 */
	public function test_build_message_with_image() {
		$message = MRDW_Push_Expo::build_message( array(
			'title'     => 'Test',
			'body'      => 'Body',
			'image_url' => 'https://example.com/image.jpg',
		) );

		$array = $message->toArray();
		$this->assertArrayHasKey( 'richContent', $array );
		$this->assertSame( 'https://example.com/image.jpg', $array['richContent']['image'] );
	}

	/**
	 * Test build_message with image URL sets richContent field (not top-level image).
	 */
	public function test_build_message_with_image_sets_rich_content_field() {
		$message = MRDW_Push_Expo::build_message( array(
			'title'     => 'Test',
			'body'      => 'Body',
			'image_url' => 'https://example.com/image.jpg',
		) );

		$array = $message->toArray();
		$this->assertSame( 'https://example.com/image.jpg', $array['richContent']['image'] );
		$this->assertArrayNotHasKey( 'image', $array );
		$this->assertArrayNotHasKey( 'data', $array );
	}

	/**
	 * Test build_message without image does not enable mutableContent.
	 */
	public function test_build_message_without_image_no_mutable_content() {
		$message = MRDW_Push_Expo::build_message( array(
			'title' => 'Test',
			'body'  => 'Body',
		) );

		$array = $message->toArray();
		$this->assertFalse( $array['mutableContent'] );
	}

	/**
	 * Test build_message with custom JSON data.
	 */
	public function test_build_message_with_custom_data() {
		$message = MRDW_Push_Expo::build_message( array(
			'title' => 'Test',
			'body'  => 'Body',
			'data'  => '{"screen":"home","id":123}',
		) );

		$array = $message->toArray();
		$this->assertArrayHasKey( 'data', $array );
		$this->assertSame( 'home', $array['data']['screen'] );
		$this->assertSame( 123, $array['data']['id'] );
	}

	/**
	 * Test build_message with array data (not JSON string).
	 */
	public function test_build_message_with_array_data() {
		$message = MRDW_Push_Expo::build_message( array(
			'title' => 'Test',
			'body'  => 'Body',
			'data'  => array( 'screen' => 'profile', 'user_id' => 5 ),
		) );

		$array = $message->toArray();
		$this->assertArrayHasKey( 'data', $array );
		$this->assertSame( 'profile', $array['data']['screen'] );
		$this->assertSame( 5, $array['data']['user_id'] );
	}

	/**
	 * Test build_message with both data and image_url.
	 */
	public function test_build_message_with_data_and_image() {
		$message = MRDW_Push_Expo::build_message( array(
			'title'     => 'Test',
			'body'      => 'Body',
			'data'      => '{"post_id":42}',
			'image_url' => 'https://example.com/img.jpg',
		) );

		$array = $message->toArray();
		$this->assertSame( 42, $array['data']['post_id'] );
		$this->assertSame( 'https://example.com/img.jpg', $array['richContent']['image'] );
	}

	/**
	 * Test build_message with empty params.
	 */
	public function test_build_message_empty_params() {
		$message = MRDW_Push_Expo::build_message( array() );

		$array = $message->toArray();
		$this->assertSame( '', $array['title'] );
		$this->assertSame( '', $array['body'] );
		$this->assertSame( 'default', $array['sound'] );
	}

	/**
	 * Test build_message with invalid JSON data (falls through).
	 */
	public function test_build_message_invalid_json_data() {
		$message = MRDW_Push_Expo::build_message( array(
			'title' => 'Test',
			'body'  => 'Body',
			'data'  => 'not valid json{',
		) );

		$array = $message->toArray();
		// Invalid JSON should not set data key.
		$this->assertArrayNotHasKey( 'data', $array );
	}

	/**
	 * Test send with empty tokens returns zero counts.
	 */
	public function test_send_empty_tokens() {
		$result = MRDW_Push_Expo::send( array(), array(
			'title' => 'Test',
			'body'  => 'Body',
		) );

		$this->assertEmpty( $result['ticket_ids'] );
		$this->assertSame( 0, $result['success_count'] );
		$this->assertSame( 0, $result['failed_count'] );
	}

	/**
	 * Test send filters out invalid tokens.
	 */
	public function test_send_filters_invalid_tokens() {
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( '' );

		$result = MRDW_Push_Expo::send(
			array( 'invalid-token-1', 'invalid-token-2' ),
			array( 'title' => 'Test', 'body' => 'Body' )
		);

		$this->assertSame( 2, $result['failed_count'] );
		$this->assertSame( 0, $result['success_count'] );
	}

	/**
	 * Test get_instance creates instance without access token.
	 */
	public function test_get_instance_without_token() {
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( '' );

		$expo = MRDW_Push_Expo::get_instance();
		$this->assertInstanceOf( \ExpoSDK\Expo::class, $expo );
	}

	/**
	 * Test get_instance creates instance with access token.
	 */
	public function test_get_instance_with_token() {
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( 'my-expo-token' );

		$expo = MRDW_Push_Expo::get_instance();
		$this->assertInstanceOf( \ExpoSDK\Expo::class, $expo );
	}

	/**
	 * Test get_instance returns same instance (singleton).
	 */
	public function test_get_instance_singleton() {
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( '' );

		$expo1 = MRDW_Push_Expo::get_instance();
		$expo2 = MRDW_Push_Expo::get_instance();
		$this->assertSame( $expo1, $expo2 );
	}

	/**
	 * Test reset_instance clears singleton.
	 */
	public function test_reset_instance() {
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( '' );

		$expo1 = MRDW_Push_Expo::get_instance();
		MRDW_Push_Expo::reset_instance();

		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->andReturn( '' );

		$expo2 = MRDW_Push_Expo::get_instance();
		$this->assertNotSame( $expo1, $expo2 );
	}

	/**
	 * Test check_receipts with empty ticket IDs.
	 */
	public function test_check_receipts_empty() {
		$result = MRDW_Push_Expo::check_receipts( array() );
		$this->assertEmpty( $result );
	}

	/**
	 * Test instance is recreated when the access token option changes.
	 */
	public function test_get_instance_recreated_on_token_change() {
		Functions\expect( 'get_option' )
			->with( 'mrdw_push_expo_access_token', '' )
			->twice()
			->andReturn( '', 'new-token' );

		$expo1 = MRDW_Push_Expo::get_instance();
		$expo2 = MRDW_Push_Expo::get_instance();

		$this->assertNotSame( $expo1, $expo2 );
	}

	/**
	 * Test send result includes the ticket_token_map key for receipt cleanup.
	 */
	public function test_send_result_shape_includes_token_map() {
		$result = MRDW_Push_Expo::send( array(), array( 'title' => 'T', 'body' => 'B' ) );

		$this->assertArrayHasKey( 'ticket_token_map', $result );
		$this->assertSame( array(), $result['ticket_token_map'] );
	}
}
