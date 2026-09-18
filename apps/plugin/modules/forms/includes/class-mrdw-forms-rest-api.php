<?php
/**
 * REST API endpoint registration and handling.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Forms_REST_API
 *
 * Registers REST endpoints for form submission and field retrieval.
 */
class MRDW_Forms_REST_API {

	/** Submission bounds. */
	const MAX_FIELDS       = 100;
	const MAX_FIELD_LENGTH = 10000;
	const MAX_PAYLOAD_SIZE = 65536;

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'mrdw/v1';

	/**
	 * App Check instance.
	 *
	 * @var MRDW_Forms_AppCheck
	 */
	private $appcheck;

	/**
	 * Provider instance.
	 *
	 * @var MRDW_Forms_Provider
	 */
	private $provider;

	/**
	 * Entry store instance.
	 *
	 * @var MRDW_Forms_Entry_Store
	 */
	private $entry_store;

	/**
	 * Constructor.
	 *
	 * @param MRDW_Forms_AppCheck|null    $appcheck    Optional App Check instance.
	 * @param MRDW_Forms_Provider|null    $provider    Optional provider instance.
	 * @param MRDW_Forms_Entry_Store|null $entry_store Optional entry store instance.
	 */
	public function __construct( $appcheck = null, $provider = null, $entry_store = null ) {
		$this->appcheck    = $appcheck ?: new MRDW_Forms_AppCheck();
		$this->provider    = $provider ?: MRDW_Forms_Provider_Factory::create();
		$this->entry_store = $entry_store ?: new MRDW_Forms_Entry_Store();
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/submit/(?P<form_id>[0-9a-fA-F:_-]+)',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'handle_submit' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'form_id' => array(
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return (bool) preg_match( '/^\d+(?::(?:\d+|[a-f0-9-]{36}))?$/i', $param );
							},
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => 'OPTIONS',
					'callback'            => array( $this, 'handle_options' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/forms/(?P<form_id>[0-9a-fA-F:_-]+)/fields',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_get_fields' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'form_id' => array(
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return (bool) preg_match( '/^\d+(?::(?:\d+|[a-f0-9-]{36}))?$/i', $param );
							},
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => 'OPTIONS',
					'callback'            => array( $this, 'handle_options' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Handle form submission.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return \WP_REST_Response
	 */
	public function handle_submit( $request ) {
		$form_id = $request->get_param( 'form_id' );

		// Check form ID allowlist.
		if ( ! $this->is_form_allowed( $form_id ) ) {
			return $this->error_response( 'form_not_found', __( 'The specified form is not available.', 'mrdw' ), 404 );
		}

		$body = $request->get_body();
		if ( is_string( $body ) && strlen( $body ) > self::MAX_PAYLOAD_SIZE ) {
			return $this->error_response( 'invalid_fields', __( 'The submitted request is too large.', 'mrdw' ), 413 );
		}

		// Verify the form exists via provider.
		$form = $this->provider->get_form( $form_id );
		if ( ! $form ) {
			return $this->error_response( 'form_not_found', __( 'The specified form does not exist.', 'mrdw' ), 404 );
		}

		// Verify App Check token.
		$token = $this->get_app_check_token( $request );

		$appcheck_result = $this->appcheck->verify( $token, $form_id );
		if ( ! $appcheck_result['success'] ) {
			return $this->error_response(
				$appcheck_result['code'],
				$appcheck_result['message'],
				403
			);
		}

		$rate_limit = $this->check_rate_limit( $form_id, $appcheck_result['app_id'] ?? '' );
		if ( $rate_limit ) {
			return $rate_limit;
		}

		// Validate fields.
		$fields = $request->get_param( 'fields' );
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $this->error_response( 'missing_fields', __( 'Required fields are missing from the request.', 'mrdw' ), 400 );
		}

		$field_error = $this->validate_fields( $fields, $form['fields'] ?? array() );
		if ( $field_error ) {
			return $field_error;
		}

		// Create entry via provider.
		$entry_result = $this->provider->create_entry( $form_id, $fields, $request );
		if ( ! $entry_result['success'] ) {
			return $this->error_response(
				$entry_result['code'],
				$entry_result['message'],
				$entry_result['status'] ?? 500
			);
		}

		$entry_id = $entry_result['entry_id'];

		// Log to unified entry store (for non-Divi providers that have their own storage).
		if ( 'divi' !== $this->provider->get_slug() ) {
			$this->entry_store->add(
				array(
					'provider'     => $this->provider->get_slug(),
					'form_id'      => $form_id,
					'form_name'    => $form['title'] ?? '',
					'fields'       => wp_json_encode( $fields ),
					'ip_address'   => $this->provider->get_client_ip( $request ),
					'user_agent'   => sanitize_text_field( $request->get_header( 'User-Agent' ) ?? '' ),
					'date_created' => current_time( 'mysql' ),
				)
			);
		}

		// Send notifications via provider.
		$this->provider->send_notifications( $form_id, $entry_id, $fields, $form );

		/**
		 * Fires after successful entry creation.
		 *
		 * @param int              $entry_id The entry ID.
		 * @param string           $form_id  The form ID.
		 * @param array            $fields   The submitted fields.
		 * @param \WP_REST_Request $request  The REST request.
		 */
		do_action( 'mrdw_forms_entry_created', $entry_id, $form_id, $fields, $request );

		$response = array(
			'success'  => true,
			'message'  => __( 'Form submitted successfully.', 'mrdw' ),
			'entry_id' => $entry_id,
		);

		/**
		 * Filter the REST API response before returning.
		 *
		 * @param array  $response The response data.
		 * @param int    $entry_id The entry ID.
		 * @param string $form_id  The form ID.
		 */
		$response = apply_filters( 'mrdw_forms_rest_response', $response, $entry_id, $form_id );

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Handle GET form fields.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return \WP_REST_Response
	 */
	public function handle_get_fields( $request ) {
		$form_id = $request->get_param( 'form_id' );

		if ( ! $this->is_form_allowed( $form_id ) ) {
			return $this->error_response( 'form_not_found', __( 'The specified form is not available.', 'mrdw' ), 404 );
		}

		$appcheck_result = $this->appcheck->verify( $request->get_header( 'X-Firebase-AppCheck' ), $form_id );
		if ( ! $appcheck_result['success'] ) {
			return $this->error_response( $appcheck_result['code'], $appcheck_result['message'], 403 );
		}

		$form = $this->provider->get_form( $form_id );
		if ( ! $form ) {
			return $this->error_response( 'form_not_found', __( 'The specified form does not exist.', 'mrdw' ), 404 );
		}

		$response = new \WP_REST_Response(
			array(
				'success'    => true,
				'form_id'    => $form_id,
				'form_title' => $form['title'] ?? '',
				'fields'     => $this->provider->get_fields( $form_id ),
			),
			200
		);

		$response->header( 'Cache-Control', 'private, no-store' );
		$response->header( 'Vary', 'Origin' );

		return $response;
	}

	/**
	 * Handle OPTIONS preflight request.
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_options() {
		return new \WP_REST_Response( null, 200 );
	}

	/**
	 * Add CORS headers to REST API responses.
	 *
	 * @param bool              $served  Whether the request has been served.
	 * @param \WP_HTTP_Response $result  Result to send.
	 * @param \WP_REST_Request  $request Request used to generate the response.
	 * @param \WP_REST_Server   $server  Server instance.
	 * @return bool
	 */
	public function add_cors_headers( $served, $result, $request, $server ) {
		$route = $request->get_route();

		if ( 0 !== strpos( $route, '/' . self::NAMESPACE ) ) {
			return $served;
		}

		$origin          = $request->get_header( 'Origin' );
		$allowed_origins = $this->get_allowed_origins();

		if ( ! empty( $allowed_origins ) && $origin && in_array( $origin, $allowed_origins, true ) ) {
			// The origin strictly matched an admin-configured allowlist entry.
			// esc_url() is intentionally not used: it strips app schemes like
			// capacitor:// and ionic:// that mobile WebView clients send.
			$safe_origin = str_replace( array( "\r", "\n" ), '', $origin );
			if ( ! empty( $safe_origin ) ) {
				header( 'Access-Control-Allow-Origin: ' . $safe_origin );
				header( 'Vary: Origin', false );
			}
		}

		header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS' );
		header( 'Access-Control-Allow-Headers: Content-Type, X-Firebase-AppCheck' );

		return $served;
	}

	/**
	 * Read App Check from the standard header, with the legacy body parameter as fallback.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return string|null
	 */
	private function get_app_check_token( $request ) {
		$token = $request->get_header( 'X-Firebase-AppCheck' );

		return $token ?: $request->get_param( 'app_check_token' );
	}

	/**
	 * Enforce the normalized provider schema and payload bounds.
	 *
	 * @param array $fields      Submitted fields.
	 * @param array $form_fields Normalized provider fields.
	 * @return \WP_REST_Response|null
	 */
	private function validate_fields( $fields, $form_fields ) {
		if ( count( $fields ) > self::MAX_FIELDS ) {
			return $this->error_response( 'invalid_fields', __( 'Too many fields were submitted.', 'mrdw' ), 400 );
		}

		$schema     = array();
		$total_size = 0;
		foreach ( $form_fields as $index => $field ) {
			$field_id            = isset( $field['id'] ) ? (string) $field['id'] : (string) $index;
			$schema[ $field_id ] = $field;
		}

		foreach ( $fields as $field_id => $value ) {
			$field_id = (string) $field_id;
			if ( ! isset( $schema[ $field_id ] ) ) {
				return $this->error_response( 'invalid_fields', __( 'An unknown field was submitted.', 'mrdw' ), 400 );
			}
			if ( null !== $value && ! is_scalar( $value ) ) {
				return $this->error_response( 'invalid_fields', __( 'Field values must be scalar.', 'mrdw' ), 400 );
			}

			$value_length = strlen( (string) $value );
			$total_size  += strlen( $field_id ) + $value_length;
			if ( $value_length > self::MAX_FIELD_LENGTH || $total_size > self::MAX_PAYLOAD_SIZE ) {
				return $this->error_response( 'invalid_fields', __( 'The submitted field data is too large.', 'mrdw' ), 413 );
			}

			$type = $schema[ $field_id ]['type'] ?? '';
			if ( 'email' === $type && '' !== (string) $value && ! is_email( $value ) ) {
				return $this->error_response( 'invalid_email', __( 'Invalid email address provided.', 'mrdw' ), 400 );
			}

			$choices = $schema[ $field_id ]['choices'] ?? array();
			if ( in_array( $type, array( 'select', 'radio' ), true ) && ! in_array( (string) $value, $choices, true ) ) {
				return $this->error_response( 'invalid_fields', __( 'An invalid field choice was submitted.', 'mrdw' ), 400 );
			}
		}

		if ( 'gravityforms' !== $this->provider->get_slug() ) {
			foreach ( $schema as $field_id => $field ) {
				if ( ! empty( $field['required'] ) && ( ! array_key_exists( $field_id, $fields ) || '' === trim( (string) $fields[ $field_id ] ) ) ) {
					return $this->error_response( 'missing_fields', __( 'Required fields are missing from the request.', 'mrdw' ), 400 );
				}
			}
		}

		return null;
	}

	/**
	 * Limit verified submissions per app, form, and network address.
	 *
	 * @param string $form_id Form ID.
	 * @param string $app_id  Verified Firebase app ID.
	 * @return \WP_REST_Response|null
	 */
	private function check_rate_limit( $form_id, $app_id ) {
		global $wpdb;

		$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$key       = 'mrdw_forms_rate_' . md5( $ip . '|' . $app_id . '|' . $form_id );
		$lock_name = 'mrdw_forms_' . md5( $key );
		$locked    = false;

		if ( isset( $wpdb ) && method_exists( $wpdb, 'get_var' ) ) {
			$locked = '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 2)', $lock_name ) );
			if ( ! $locked ) {
				return $this->error_response( 'rate_limited', __( 'Too many submissions. Please try again later.', 'mrdw' ), 429 );
			}
		}

		try {
			$count = (int) get_transient( $key );
			if ( $count >= 30 ) {
				return $this->error_response( 'rate_limited', __( 'Too many submissions. Please try again later.', 'mrdw' ), 429 );
			}

			set_transient( $key, $count + 1, 60 );

			return null;
		} finally {
			if ( $locked ) {
				$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
			}
		}
	}

	/**
	 * Check if a form ID is in the allowlist.
	 *
	 * @param string $form_id The form ID.
	 * @return bool
	 */
	private function is_form_allowed( $form_id ) {
		$settings    = MRDW_Forms_Settings::get_settings();
		$allowed_ids = array_filter( array_map( 'trim', explode( ',', $settings['allowed_form_ids'] ?? '' ) ) );

		/**
		 * Filter allowed form IDs.
		 *
		 * @param array $allowed_ids The allowed form IDs.
		 */
		$allowed_ids = apply_filters( 'mrdw_forms_allowed_form_ids', $allowed_ids );

		if ( empty( $allowed_ids ) ) {
			return false;
		}

		return in_array( (string) $form_id, $allowed_ids, true );
	}

	/**
	 * Get allowed CORS origins.
	 *
	 * @return array
	 */
	private function get_allowed_origins() {
		$settings = MRDW_Forms_Settings::get_settings();
		$origins  = $settings['allowed_origins'] ?? '';

		if ( empty( $origins ) ) {
			return array();
		}

		return array_filter( array_map( 'trim', explode( ',', $origins ) ) );
	}

	/**
	 * Build an error response.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @return \WP_REST_Response
	 */
	private function error_response( $code, $message, $status ) {
		return new \WP_REST_Response(
			array(
				'success' => false,
				'code'    => $code,
				'message' => $message,
			),
			$status
		);
	}
}
