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

		$this->assertIsArray( $message );

		$array = $message;
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

		$array = $message;
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

		$array = $message;
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

		$array = $message;
		$this->assertArrayNotHasKey( 'mutableContent', $array );
		$this->assertArrayNotHasKey( 'richContent', $array );
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

		$array = $message;
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

		$array = $message;
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

		$array = $message;
		$this->assertSame( 42, $array['data']['post_id'] );
		$this->assertSame( 'https://example.com/img.jpg', $array['richContent']['image'] );
	}

	/**
	 * Test build_message with empty params.
	 */
	public function test_build_message_empty_params() {
		$message = MRDW_Push_Expo::build_message( array() );

		$array = $message;
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

		$array = $message;
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


	// ── Token validation ────────────────────────────────────────

	/**
	 * Both bracketed forms Expo issues are accepted.
	 */
	public function test_is_valid_token_accepts_both_bracketed_forms() {
		$this->assertTrue( MRDW_Push_Expo::is_valid_token( 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]' ) );
		$this->assertTrue( MRDW_Push_Expo::is_valid_token( 'ExpoPushToken[xxxxxxxxxxxxxxxxxxxxxx]' ) );
	}

	/**
	 * Older SDKs issued bare UUIDs.
	 */
	public function test_is_valid_token_accepts_bare_uuid() {
		$this->assertTrue( MRDW_Push_Expo::is_valid_token( 'f4e0b2c1-8a3d-4c5e-9f10-2b3c4d5e6f70' ) );
	}

	/**
	 * Anything else is rejected, including near-misses.
	 */
	public function test_is_valid_token_rejects_malformed() {
		foreach ( array(
			'',
			'invalid-token',
			'ExponentPushToken[unterminated',
			'ExponentPushToken[]',
			'ExponentPushTokenxxxxxxxxxxxxxxxxxx',
			12345,
			null,
			array( 'ExponentPushToken[x]' ),
		) as $bad ) {
			$this->assertFalse(
				MRDW_Push_Expo::is_valid_token( $bad ),
				'Expected rejection of: ' . var_export( $bad, true )
			);
		}
	}

	// ── Image URL validation ────────────────────────────────────

	/**
	 * A normal HTTPS media URL is accepted unchanged.
	 */
	public function test_validate_image_url_accepts_https() {
		$url = 'https://example.com/wp-content/uploads/2026/08/photo.jpg';
		$this->assertSame( $url, MRDW_Push_Expo::validate_image_url( $url ) );
	}

	/**
	 * Plain HTTP is refused by iOS App Transport Security, so reject it here
	 * rather than shipping a notification whose image silently never appears.
	 */
	public function test_validate_image_url_rejects_plain_http() {
		$this->assertSame( '', MRDW_Push_Expo::validate_image_url( 'http://example.com/photo.jpg' ) );
	}

	/**
	 * A handset on mobile data cannot reach these hosts.
	 */
	public function test_validate_image_url_rejects_unreachable_hosts() {
		foreach ( array(
			'https://localhost/photo.jpg',
			'https://mysite.local/photo.jpg',
			'https://staging.test/photo.jpg',
			'https://192.168.1.10/photo.jpg',
			'https://10.0.0.5/photo.jpg',
			'https://127.0.0.1/photo.jpg',
		) as $bad ) {
			$this->assertSame( '', MRDW_Push_Expo::validate_image_url( $bad ), 'Expected rejection of: ' . $bad );
		}
	}

	/**
	 * The image_url column is varchar(500); a longer URL would be truncated.
	 */
	public function test_validate_image_url_rejects_overlong() {
		$long = 'https://example.com/' . str_repeat( 'a', 500 ) . '.jpg';
		$this->assertSame( '', MRDW_Push_Expo::validate_image_url( $long ) );
	}

	/**
	 * Junk input never reaches the payload.
	 */
	public function test_validate_image_url_rejects_junk() {
		foreach ( array( '', '   ', 'not a url', 'ftp://example.com/x.jpg', null, 42 ) as $bad ) {
			$this->assertSame( '', MRDW_Push_Expo::validate_image_url( $bad ) );
		}
	}

	/**
	 * An unusable image URL must not silently poison the rest of the message.
	 */
	public function test_build_message_drops_unusable_image() {
		$message = MRDW_Push_Expo::build_message( array(
			'title'     => 'Test',
			'body'      => 'Body',
			'image_url' => 'http://example.com/photo.jpg',
		) );

		$this->assertSame( 'Test', $message['title'] );
		$this->assertArrayNotHasKey( 'richContent', $message );
		$this->assertArrayNotHasKey( 'mutableContent', $message );
	}

	/**
	 * mutableContent is what makes APNs invoke the app's Notification Service
	 * Extension; without it an iOS image is never rendered.
	 */
	public function test_build_message_sets_mutable_content_with_image() {
		$message = MRDW_Push_Expo::build_message( array(
			'title'     => 'Test',
			'body'      => 'Body',
			'image_url' => 'https://example.com/photo.jpg',
		) );

		$this->assertTrue( $message['mutableContent'] );
		$this->assertSame( 'https://example.com/photo.jpg', $message['richContent']['image'] );
	}

	// ── Batching ────────────────────────────────────────────────

	/**
	 * Expo rejects a request carrying more than 100 messages, so a larger
	 * audience must be split across several requests.
	 */
	public function test_send_chunks_at_one_hundred_tokens() {
		$tokens = array();
		for ( $i = 0; $i < 250; $i++ ) {
			$tokens[] = sprintf( 'ExponentPushToken[%022d]', $i );
		}

		Functions\when( 'get_option' )->justReturn( '' );

		$batches = array();

		Functions\when( 'wp_remote_post' )->alias(
			function ( $url, $args ) use ( &$batches ) {
				$body      = json_decode( $args['body'], true );
				$batches[] = count( $body['to'] );

				$tickets = array_map(
					function () {
						return array( 'status' => 'ok', 'id' => 'ticket-' . uniqid( '', true ) );
					},
					$body['to']
				);

				return array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode( array( 'data' => $tickets ) ),
				);
			}
		);

		MRDW_Push_Expo::send( $tokens, array( 'title' => 'T', 'body' => 'B' ) );

		$this->assertSame( array( 100, 100, 50 ), $batches );
	}

	/**
	 * A transport failure is counted as failure for every token in the batch
	 * rather than being reported as a silent success.
	 */
	public function test_send_counts_transport_failure() {
		Functions\when( 'get_option' )->justReturn( '' );
		Functions\when( 'wp_remote_post' )->justReturn(
			array(
				'response' => array( 'code' => 500 ),
				'body'     => '{"errors":[{"message":"boom"}]}',
			)
		);

		$result = MRDW_Push_Expo::send(
			array( 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]' ),
			array( 'title' => 'T', 'body' => 'B' )
		);

		$this->assertSame( 0, $result['success_count'] );
		$this->assertSame( 1, $result['failed_count'] );
	}
}
