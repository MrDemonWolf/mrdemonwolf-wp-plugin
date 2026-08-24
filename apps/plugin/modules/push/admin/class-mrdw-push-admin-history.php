<?php
/**
 * Notification History admin page with WP_List_Table.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MRDW_Push_History_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'notification',
				'plural'   => 'notifications',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'title'         => __( 'Title', 'mrdw' ),
			'type'          => __( 'Type', 'mrdw' ),
			'target_type'   => __( 'Target', 'mrdw' ),
			'total_devices' => __( 'Devices', 'mrdw' ),
			'status'        => __( 'Status', 'mrdw' ),
			'created_at'    => __( 'Date', 'mrdw' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'title'      => array( 'title', false ),
			'type'       => array( 'type', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Column: title.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_title( $item ) {
		$title = esc_html( $item->title );

		// Show body preview.
		$body    = esc_html( wp_trim_words( $item->body, 10, '...' ) );
		$output  = '<strong>' . $title . '</strong>';
		$output .= '<br><span class="mrdw-push-text-muted">' . $body . '</span>';

		return $output;
	}

	/**
	 * Column: type.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_type( $item ) {
		$types = array(
			'post'      => '<span class="mrdw-push-badge mrdw-push-badge-green">post</span>',
			'manual'    => '<span class="mrdw-push-badge mrdw-push-badge-blue">manual</span>',
			'scheduled' => '<span class="mrdw-push-badge mrdw-push-badge-purple">scheduled</span>',
		);

		return $types[ $item->type ] ?? esc_html( $item->type );
	}

	/**
	 * Column: target_type.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_target_type( $item ) {
		$targets = array(
			'all'      => __( 'All', 'mrdw' ),
			'dev'      => __( 'Dev', 'mrdw' ),
			'group'    => __( 'Group', 'mrdw' ),
			'specific' => __( 'Specific', 'mrdw' ),
		);

		return $targets[ $item->target_type ] ?? esc_html( $item->target_type );
	}

	/**
	 * Column: total_devices.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_total_devices( $item ) {
		return sprintf( '%d/%d', $item->total_success, $item->total_devices );
	}

	/**
	 * Column: status.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_status( $item ) {
		$statuses = array(
			'pending'          => '<span class="mrdw-push-badge mrdw-push-badge-gray">pending</span>',
			'scheduled'        => '<span class="mrdw-push-badge mrdw-push-badge-yellow">scheduled</span>',
			'sent'             => '<span class="mrdw-push-badge mrdw-push-badge-green">sent</span>',
			'receipts_checked' => '<span class="mrdw-push-badge mrdw-push-badge-green">ok</span>',
			'failed'           => '<span class="mrdw-push-badge mrdw-push-badge-red">failed</span>',
			'cancelled'        => '<span class="mrdw-push-badge mrdw-push-badge-gray-muted">cancelled</span>',
		);

		return $statuses[ $item->status ] ?? esc_html( $item->status );
	}

	/**
	 * Column: created_at.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_created_at( $item ) {
		if ( 'scheduled' === $item->status && ! empty( $item->scheduled_at ) ) {
			return esc_html__( 'Scheduled:', 'mrdw' ) . '<br>' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->scheduled_at ) ) );
		}

		return esc_html( human_time_diff( strtotime( $item->created_at ), time() ) ) . ' ' . esc_html__( 'ago', 'mrdw' );
	}

	/**
	 * Extra table nav for filters.
	 *
	 * @param string $which Top or bottom.
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$current_type   = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
		$current_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		echo '<div class="alignleft actions">';

		echo '<select name="type">';
		echo '<option value="">' . esc_html__( 'All Types', 'mrdw' ) . '</option>';
		echo '<option value="post"' . selected( $current_type, 'post', false ) . '>' . esc_html__( 'Post', 'mrdw' ) . '</option>';
		echo '<option value="manual"' . selected( $current_type, 'manual', false ) . '>' . esc_html__( 'Manual', 'mrdw' ) . '</option>';
		echo '<option value="scheduled"' . selected( $current_type, 'scheduled', false ) . '>' . esc_html__( 'Scheduled', 'mrdw' ) . '</option>';
		echo '</select>';

		echo '<select name="status">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'mrdw' ) . '</option>';
		echo '<option value="sent"' . selected( $current_status, 'sent', false ) . '>' . esc_html__( 'Sent', 'mrdw' ) . '</option>';
		echo '<option value="receipts_checked"' . selected( $current_status, 'receipts_checked', false ) . '>' . esc_html__( 'OK', 'mrdw' ) . '</option>';
		echo '<option value="scheduled"' . selected( $current_status, 'scheduled', false ) . '>' . esc_html__( 'Scheduled', 'mrdw' ) . '</option>';
		echo '<option value="failed"' . selected( $current_status, 'failed', false ) . '>' . esc_html__( 'Failed', 'mrdw' ) . '</option>';
		echo '</select>';

		submit_button( __( 'Filter', 'mrdw' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page = 20;

		$args = array(
			'per_page' => $per_page,
			'page'     => $this->get_pagenum(),
			'orderby'  => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
			'order'    => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
			'type'     => isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '',
			'status'   => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
		);

		$result = MRDW_Push_DB::get_notifications( $args );

		$this->items = $result['items'];

		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
				'total_pages' => ceil( $result['total'] / $per_page ),
			)
		);
	}
}

class MRDW_Push_Admin_History {

	/**
	 * Render the history page.
	 */
	public function render() {
		include MRDW_PUSH_DIR . 'admin/partials/history.php';
	}

	/**
	 * Handle AJAX delete all notifications.
	 */
	public function handle_delete_all() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$result = MRDW_Push_DB::delete_all_notifications();

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete notification history.', 'mrdw' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'All notification history deleted.', 'mrdw' ) ) );
	}
}
