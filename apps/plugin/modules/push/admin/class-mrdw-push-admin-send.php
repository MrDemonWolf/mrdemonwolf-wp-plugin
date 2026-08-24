<?php
/**
 * Send Notification admin page.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Admin_Send {

	/**
	 * Render the send notification page.
	 */
	public function render() {
		$groups    = MRDW_Push_DB::get_all_groups();
		$devices   = MRDW_Push_DB::get_devices(
			array(
				'per_page'  => 999,
				'is_active' => 1,
			)
		);
		$scheduled = MRDW_Push_DB::get_scheduled_notifications();
		$dev_mode  = '1' === get_option( 'mrdw_push_dev_mode', '0' );
		$dev_count = MRDW_Push_DB::get_dev_device_count();

		include MRDW_PUSH_DIR . 'admin/partials/send-notification.php';
	}

	/**
	 * Handle AJAX send notification.
	 */
	public function handle_send() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$title     = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$body      = sanitize_textarea_field( wp_unslash( $_POST['body'] ?? '' ) );
		// Rejects plain HTTP and unreachable hosts, which would otherwise be
		// accepted here and then silently dropped on the handset.
		$image_url = MRDW_Push_Expo::validate_image_url( esc_url_raw( wp_unslash( $_POST['image_url'] ?? '' ) ) );
		$data      = null;
		if ( isset( $_POST['data'] ) && '' !== $_POST['data'] ) {
			// Kept raw on purpose: this is a JSON document, validated by the
			// json_decode() check below and re-encoded before it is stored.
			$data = wp_unslash( $_POST['data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated as JSON immediately below.
			if ( null === json_decode( $data ) && JSON_ERROR_NONE !== json_last_error() ) {
				wp_send_json_error( array( 'message' => __( 'Invalid JSON in data field.', 'mrdw' ) ) );
			}
		}
		$target_type  = sanitize_text_field( wp_unslash( $_POST['target_type'] ?? 'all' ) );
		$target_ids   = isset( $_POST['target_ids'] ) ? array_map( 'intval', (array) $_POST['target_ids'] ) : null;
		$scheduled_at = sanitize_text_field( wp_unslash( $_POST['scheduled_at'] ?? '' ) );

		if ( empty( $title ) || empty( $body ) ) {
			wp_send_json_error( array( 'message' => __( 'Title and body are required.', 'mrdw' ) ) );
		}

		$params = array(
			'title'     => $title,
			'body'      => $body,
			'data'      => $data,
			'image_url' => $image_url ?: null,
		);

		// Handle scheduled notifications.
		if ( ! empty( $scheduled_at ) ) {
			$notification_id = MRDW_Push_Notification::schedule_notification(
				$params,
				$scheduled_at,
				$target_type,
				$target_ids,
				null,
				get_current_user_id()
			);

			if ( $notification_id ) {
				wp_send_json_success(
					array(
						'message'         => __( 'Notification scheduled successfully.', 'mrdw' ),
						'notification_id' => $notification_id,
					)
				);
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to schedule notification.', 'mrdw' ) ) );
			}
		}

		// Get tokens.
		$tokens = MRDW_Push_DB::get_tokens_by_target( $target_type, $target_ids );

		if ( empty( $tokens ) ) {
			wp_send_json_error( array( 'message' => __( 'No devices found for the selected target.', 'mrdw' ) ) );
		}

		$notification_id = MRDW_Push_Notification::send_notification(
			$params,
			$tokens,
			'manual',
			null,
			$target_type,
			$target_ids,
			get_current_user_id()
		);

		if ( $notification_id ) {
			$notification = MRDW_Push_DB::get_notification( $notification_id );
			wp_send_json_success(
				array(
					'message'         => sprintf(
					/* translators: 1: success count, 2: total count */
						__( 'Notification sent to %1$d of %2$d devices.', 'mrdw' ),
						$notification ? $notification->total_success : 0,
						$notification ? $notification->total_devices : 0
					),
					'notification_id' => $notification_id,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send notification.', 'mrdw' ) ) );
		}
	}

	/**
	 * Handle AJAX cancel scheduled notification.
	 */
	public function handle_cancel_scheduled() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$notification_id = isset( $_POST['notification_id'] ) ? intval( $_POST['notification_id'] ) : 0;

		if ( ! $notification_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid notification ID.', 'mrdw' ) ) );
		}

		$result = MRDW_Push_Notification::cancel_scheduled( $notification_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Scheduled notification cancelled.', 'mrdw' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to cancel notification.', 'mrdw' ) ) );
		}
	}
}
