<?php
/**
 * Post editor meta box for MRDW_Push.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Meta_Box {

	/**
	 * Add the meta box to the post editor.
	 */
	public function add_meta_box() {
		$post_types = apply_filters( 'mrdw_push_post_types', array( 'post', 'portfolio' ) );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'mrdw_push_meta_box',
				__( 'MRDW_Push Push Notification', 'mrdw' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'mrdw_push_meta_box', 'mrdw_push_meta_box_nonce' );

		$notify       = get_post_meta( $post->ID, '_mrdw_push_notify', true );
		$notified     = get_post_meta( $post->ID, '_mrdw_push_notified', true );
		$include_img  = get_post_meta( $post->ID, '_mrdw_push_include_image', true );
		$custom_title = get_post_meta( $post->ID, '_mrdw_push_custom_title', true );
		$custom_body  = get_post_meta( $post->ID, '_mrdw_push_custom_body', true );
		$dev_mode     = '1' === get_option( 'mrdw_push_dev_mode', '0' );
		$groups       = MRDW_Push_DB::get_all_groups();
		$history      = MRDW_Push_DB::get_post_notification_history( $post->ID );

		// Resolve post-type-specific default templates for the Quick Send fields.
		list( $default_title, $default_body ) = $this->get_default_templates( $post->post_type );

		// Default values.
		if ( '' === $notify ) {
			$notify = '1';
		}
		if ( '' === $include_img ) {
			$include_img = get_option( 'mrdw_push_use_featured_image', '1' );
		}

		include MRDW_PUSH_DIR . 'admin/partials/meta-box.php';
	}

	/**
	 * Resolve the default notification templates for a post type.
	 *
	 * @param string $post_type The post type.
	 * @return array Array of [ default_title, default_body ].
	 */
	private function get_default_templates( $post_type ) {
		if ( 'portfolio' === $post_type ) {
			return array(
				get_option( 'mrdw_push_portfolio_default_title', 'New Project: {post_title}' ),
				get_option( 'mrdw_push_portfolio_default_body', '{post_title} by {author_name}' ),
			);
		}

		return array(
			get_option( 'mrdw_push_default_title', 'New from {site_name}' ),
			get_option( 'mrdw_push_default_body', '{post_title}' ),
		);
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Skip revisions.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['mrdw_push_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mrdw_push_meta_box_nonce'] ) ), 'mrdw_push_meta_box' ) ) {
			return;
		}

		// Skip autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Require MRDW_Push capability for notification meta fields.
		if ( ! current_user_can( 'mrdw_manage' ) ) {
			return;
		}

		// Save notify toggle.
		$notify = isset( $_POST['mrdw_push_notify'] ) ? '1' : '0';
		update_post_meta( $post_id, '_mrdw_push_notify', $notify );

		// Save include image toggle.
		//
		// An unchecked checkbox and an absent field look identical in $_POST, so
		// a hidden companion field marks that the box was actually on screen.
		// Without it, any save from a screen that does not render this meta box
		// would write '0' and permanently opt the post out of featured images,
		// even if the site-wide setting is later switched on.
		if ( isset( $_POST['mrdw_push_image_field_present'] ) ) {
			$include_img = isset( $_POST['mrdw_push_include_image'] ) ? '1' : '0';
			update_post_meta( $post_id, '_mrdw_push_include_image', $include_img );
		}

		// Save custom title/body — only persist genuine overrides. The Quick
		// Send fields are pre-filled with the default templates, so a value
		// equal to the default (or empty) is not an override.
		list( $default_title, $default_body ) = $this->get_default_templates( $post->post_type );

		if ( isset( $_POST['mrdw_push_custom_title'] ) ) {
			$custom_title = sanitize_text_field( wp_unslash( $_POST['mrdw_push_custom_title'] ) );
			if ( '' === $custom_title || $default_title === $custom_title ) {
				delete_post_meta( $post_id, '_mrdw_push_custom_title' );
			} else {
				update_post_meta( $post_id, '_mrdw_push_custom_title', $custom_title );
			}
		}

		if ( isset( $_POST['mrdw_push_custom_body'] ) ) {
			$custom_body = sanitize_text_field( wp_unslash( $_POST['mrdw_push_custom_body'] ) );
			if ( '' === $custom_body || $default_body === $custom_body ) {
				delete_post_meta( $post_id, '_mrdw_push_custom_body' );
			} else {
				update_post_meta( $post_id, '_mrdw_push_custom_body', $custom_body );
			}
		}
	}

	/**
	 * Handle AJAX quick send from meta box.
	 */
	public function handle_quick_send() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$post_id     = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$title       = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$body        = sanitize_textarea_field( wp_unslash( $_POST['body'] ?? '' ) );
		$target_type = sanitize_text_field( wp_unslash( $_POST['target_type'] ?? 'all' ) );
		$target_ids  = isset( $_POST['target_ids'] ) ? array_map( 'intval', (array) $_POST['target_ids'] ) : null;

		if ( ! $post_id || empty( $title ) || empty( $body ) ) {
			wp_send_json_error( array( 'message' => __( 'Title and body are required.', 'mrdw' ) ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'mrdw' ) ) );
		}

		// Parse placeholders.
		$notification_service = new MRDW_Push_Notification();
		$title                = $notification_service->parse_placeholders( $title, $post );
		$body                 = $notification_service->parse_placeholders( $body, $post );

		$params = array(
			'title' => $title,
			'body'  => $body,
			'data'  => wp_json_encode(
				array(
					'post_id'   => $post->ID,
					'post_type' => $post->post_type,
					'url'       => get_permalink( $post ),
				)
			),
		);

		// Featured image.
		$include_image = isset( $_POST['include_image'] ) && '1' === $_POST['include_image'];
		if ( $include_image && has_post_thumbnail( $post->ID ) ) {
			$params['image_url'] = get_the_post_thumbnail_url( $post->ID, 'large' );
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
			$post_id,
			$target_type,
			$target_ids,
			get_current_user_id()
		);

		if ( $notification_id ) {
			$notification = MRDW_Push_DB::get_notification( $notification_id );
			wp_send_json_success(
				array(
					'message' => sprintf(
					/* translators: 1: success count, 2: total count */
						__( 'Sent to %1$d of %2$d devices.', 'mrdw' ),
						$notification ? $notification->total_success : 0,
						$notification ? $notification->total_devices : 0
					),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send notification.', 'mrdw' ) ) );
		}
	}
}
