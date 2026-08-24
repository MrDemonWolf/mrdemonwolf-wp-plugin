<?php
/**
 * Expo Push Service client.
 *
 * Talks to the Expo Push API directly over the WordPress HTTP API rather than
 * through a Composer SDK. Expo's push service is two unauthenticated JSON
 * endpoints, and the available PHP SDK does not expose `richContent` — the
 * field this plugin needs for featured images — so wrapping it cost more than
 * it saved.
 *
 * @link https://docs.expo.dev/push-notifications/sending-notifications/
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Push_Expo
 */
class MRDW_Push_Expo {

	/**
	 * Expo Push API endpoints.
	 */
	const PUSH_ENDPOINT     = 'https://exp.host/--/api/v2/push/send';
	const RECEIPTS_ENDPOINT = 'https://exp.host/--/api/v2/push/getReceipts';

	/**
	 * Expo rejects requests carrying more than 100 messages.
	 */
	const PUSH_CHUNK_SIZE = 100;

	/**
	 * Expo rejects receipt requests carrying more than 1000 ticket IDs.
	 */
	const RECEIPT_CHUNK_SIZE = 1000;

	/**
	 * Seconds to wait on an Expo request.
	 */
	const REQUEST_TIMEOUT = 30;

	/**
	 * The image_url column is varchar(500); anything longer would be truncated.
	 */
	const MAX_IMAGE_URL_LENGTH = 500;

	/**
	 * Reset cached state. Retained for the test suite.
	 */
	public static function reset_instance() {
		// No client is cached any more; the access token is read per request.
	}

	/**
	 * Validate an Expo push token.
	 *
	 * Mirrors Expo's own format: a bracketed ExponentPushToken/ExpoPushToken, or
	 * a bare UUID for tokens issued by older SDKs.
	 *
	 * @param mixed $token The token to validate.
	 * @return bool
	 */
	public static function is_valid_token( $token ) {
		if ( ! is_string( $token ) || strlen( $token ) < 15 ) {
			return false;
		}

		if ( (bool) preg_match( '/^Expo(nent)?PushToken\[[^\[\]]+\]$/', $token ) ) {
			return true;
		}

		return (bool) preg_match(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
			$token
		);
	}

	/**
	 * Validate an image URL for use as a rich notification attachment.
	 *
	 * The device downloads this URL itself, so it has to be publicly reachable
	 * over HTTPS. A plain-HTTP or private-network URL is not an error Expo
	 * reports — the notification simply arrives without its image — so reject
	 * those here instead of failing silently on the handset.
	 *
	 * @param mixed $url Candidate URL.
	 * @return string The URL when usable, or an empty string.
	 */
	public static function validate_image_url( $url ) {
		if ( ! is_string( $url ) || '' === trim( $url ) ) {
			return '';
		}

		$url = trim( $url );

		if ( strlen( $url ) > self::MAX_IMAGE_URL_LENGTH ) {
			return '';
		}

		$parts = wp_parse_url( $url );

		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		// iOS App Transport Security refuses plain HTTP attachments.
		if ( 'https' !== strtolower( $parts['scheme'] ) ) {
			return '';
		}

		$host = strtolower( $parts['host'] );

		// Hosts that exist only inside the site's own network can never be
		// fetched by a phone on mobile data.
		if ( 'localhost' === $host || preg_match( '/\.(local|test|internal|localhost)$/', $host ) ) {
			return '';
		}

		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			$public = filter_var(
				$host,
				FILTER_VALIDATE_IP,
				FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
			);
			if ( false === $public ) {
				return '';
			}
		}

		return $url;
	}

	/**
	 * Build the Expo message payload shared by every recipient in a send.
	 *
	 * @param array $params Message parameters (title, body, data, image_url).
	 * @return array Payload without the `to` field.
	 */
	public static function build_message( $params ) {
		$message = array(
			'title' => (string) ( $params['title'] ?? '' ),
			'body'  => (string) ( $params['body'] ?? '' ),
			'sound' => 'default',
		);

		$data = array();
		if ( ! empty( $params['data'] ) ) {
			$parsed = is_string( $params['data'] ) ? json_decode( $params['data'], true ) : $params['data'];
			if ( is_array( $parsed ) ) {
				$data = $parsed;
			}
		}

		if ( ! empty( $data ) ) {
			$message['data'] = $data;
		}

		// Rich notification image. Android renders this without any app change;
		// iOS needs a Notification Service Extension in the app, and only runs
		// it when mutableContent tells APNs the payload may be intercepted.
		$image_url = self::validate_image_url( $params['image_url'] ?? '' );
		if ( '' !== $image_url ) {
			$message['richContent']    = array( 'image' => $image_url );
			$message['mutableContent'] = true;
		}

		/**
		 * Filter the Expo message payload before it is sent.
		 *
		 * @param array $message The payload, minus the `to` field.
		 * @param array $params  The parameters it was built from.
		 */
		return apply_filters( 'mrdw_push_message', $message, $params );
	}

	/**
	 * Send push notifications.
	 *
	 * @param array $tokens Array of Expo push tokens.
	 * @param array $params Message parameters (title, body, data, image_url).
	 * @return array Result with ticket_ids, ticket_token_map, success_count, failed_count, stale_tokens.
	 */
	public static function send( $tokens, $params ) {
		$result = array(
			'ticket_ids'       => array(),
			'ticket_token_map' => array(),
			'success_count'    => 0,
			'failed_count'     => 0,
			'stale_tokens'     => array(),
		);

		if ( empty( $tokens ) ) {
			return $result;
		}

		$valid_tokens           = array_values( array_filter( $tokens, array( __CLASS__, 'is_valid_token' ) ) );
		$result['failed_count'] = count( $tokens ) - count( $valid_tokens );

		if ( empty( $valid_tokens ) ) {
			return $result;
		}

		$message = self::build_message( $params );

		foreach ( array_chunk( $valid_tokens, self::PUSH_CHUNK_SIZE ) as $chunk_tokens ) {
			$payload = array_merge( $message, array( 'to' => $chunk_tokens ) );

			$tickets = self::request( self::PUSH_ENDPOINT, $payload );

			if ( null === $tickets || ! is_array( $tickets ) ) {
				$result['failed_count'] += count( $chunk_tokens );
				continue;
			}

			foreach ( $tickets as $index => $ticket ) {
				if ( isset( $ticket['status'] ) && 'ok' === $ticket['status'] ) {
					++$result['success_count'];

					if ( isset( $ticket['id'] ) ) {
						$result['ticket_ids'][] = $ticket['id'];
						if ( isset( $chunk_tokens[ $index ] ) ) {
							$result['ticket_token_map'][ $ticket['id'] ] = $chunk_tokens[ $index ];
						}
					}
					continue;
				}

				++$result['failed_count'];

				// Expo reports a permanently dead token here; drop it so we stop
				// paying to deliver to a device that uninstalled the app.
				if ( isset( $ticket['details']['error'] ) && 'DeviceNotRegistered' === $ticket['details']['error'] ) {
					$stale = $ticket['details']['expoPushToken'] ?? ( $chunk_tokens[ $index ] ?? null );
					if ( $stale ) {
						$result['stale_tokens'][] = $stale;
					}
				}
			}
		}

		if ( ! empty( $result['stale_tokens'] ) ) {
			MRDW_Push_DB::deactivate_tokens( $result['stale_tokens'] );
		}

		return $result;
	}

	/**
	 * Check receipt status for ticket IDs.
	 *
	 * @param array $ticket_ids Array of Expo ticket IDs.
	 * @return array Receipts keyed by ticket ID.
	 */
	public static function check_receipts( $ticket_ids ) {
		if ( empty( $ticket_ids ) ) {
			return array();
		}

		$receipts = array();

		foreach ( array_chunk( array_values( $ticket_ids ), self::RECEIPT_CHUNK_SIZE ) as $chunk ) {
			$data = self::request( self::RECEIPTS_ENDPOINT, array( 'ids' => $chunk ) );

			if ( is_array( $data ) ) {
				// Keyed by ticket ID, so merge preserves the association.
				$receipts = array_merge( $receipts, $data );
			}
		}

		return $receipts;
	}

	/**
	 * POST a JSON body to Expo and return the decoded `data` member.
	 *
	 * @param string $endpoint Expo endpoint URL.
	 * @param array  $body     Request body.
	 * @return array|null Decoded data, or null on failure.
	 */
	private static function request( $endpoint, $body ) {
		$headers = array(
			'Accept'          => 'application/json',
			'Accept-Encoding' => 'gzip, deflate',
			'Content-Type'    => 'application/json',
		);

		// Optional: raises rate limits and is required by projects that have
		// enabled Expo's enhanced push security.
		$access_token = MRDW_Secrets::expo_access_token();
		if ( '' !== $access_token ) {
			$headers['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
				'timeout' => self::REQUEST_TIMEOUT,
			)
		);

		if ( is_wp_error( $response ) ) {
			self::log( 'request failed: ' . $response->get_error_message() );
			return null;
		}

		$code    = (int) wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code > 299 ) {
			$detail = '';
			if ( isset( $decoded['errors'][0]['message'] ) ) {
				$detail = ' - ' . $decoded['errors'][0]['message'];
			}
			self::log( 'HTTP ' . $code . $detail );
			return null;
		}

		if ( ! is_array( $decoded ) || ! isset( $decoded['data'] ) || ! is_array( $decoded['data'] ) ) {
			self::log( 'unexpected response shape' );
			return null;
		}

		return $decoded['data'];
	}

	/**
	 * Log an Expo transport problem when debugging is enabled.
	 *
	 * @param string $message Message to record.
	 */
	private static function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'MRDW Push (Expo): ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
