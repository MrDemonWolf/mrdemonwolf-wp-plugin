<?php
/**
 * Groups admin page.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Admin_Groups {

	/**
	 * Render the groups page.
	 */
	public function render() {
		$groups  = MRDW_Push_DB::get_groups_with_counts();
		$devices = MRDW_Push_DB::get_devices(
			array(
				'per_page'  => 999,
				'is_active' => 1,
			)
		);

		// Editing a group?
		$editing_group   = null;
		$editing_devices = array();
		if ( isset( $_GET['edit'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mrdw_push_edit_group' ) ) {
				wp_die( esc_html__( 'Invalid nonce. Please try again.', 'mrdw' ) );
			}
			$group_id      = intval( $_GET['edit'] );
			$editing_group = MRDW_Push_DB::get_group( $group_id );
			if ( $editing_group ) {
				$editing_devices = MRDW_Push_DB::get_group_device_ids( $group_id );
			}
		}

		include MRDW_PUSH_DIR . 'admin/partials/groups.php';
	}

	/**
	 * Handle AJAX save group.
	 */
	public function handle_save_group() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$group_id    = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;
		$name        = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
		$device_ids  = isset( $_POST['device_ids'] ) ? array_map( 'intval', (array) $_POST['device_ids'] ) : array();

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Group name is required.', 'mrdw' ) ) );
		}

		if ( $group_id ) {
			// Update existing.
			MRDW_Push_DB::update_group(
				$group_id,
				array(
					'name'        => $name,
					'description' => $description,
				)
			);
		} else {
			// Create new.
			$group_id = MRDW_Push_DB::create_group(
				array(
					'name'        => $name,
					'description' => $description,
				)
			);
		}

		if ( ! $group_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to save group.', 'mrdw' ) ) );
		}

		// Set devices.
		MRDW_Push_DB::set_group_devices( $group_id, $device_ids );

		wp_send_json_success(
			array(
				'message'  => __( 'Group saved.', 'mrdw' ),
				'group_id' => $group_id,
			)
		);
	}

	/**
	 * Handle AJAX delete group.
	 */
	public function handle_delete_group() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;

		if ( ! $group_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid group ID.', 'mrdw' ) ) );
		}

		$result = MRDW_Push_DB::delete_group( $group_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Group deleted.', 'mrdw' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete group.', 'mrdw' ) ) );
		}
	}
}
