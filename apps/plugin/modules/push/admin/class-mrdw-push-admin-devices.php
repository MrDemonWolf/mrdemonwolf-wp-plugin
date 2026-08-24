<?php
/**
 * Devices admin page with WP_List_Table.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MRDW_Push_Devices_List_Table extends WP_List_Table {

	/**
	 * Bulk-loaded device groups map (device_id => groups array).
	 *
	 * @var array
	 */
	private $groups_map = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'device',
				'plural'   => 'devices',
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
			'cb'             => '<input type="checkbox" />',
			'user_label'     => __( 'Label', 'mrdw' ),
			'expo_token'     => __( 'Token', 'mrdw' ),
			'device_type'    => __( 'Platform', 'mrdw' ),
			'device_model'   => __( 'Model', 'mrdw' ),
			'os_version'     => __( 'OS', 'mrdw' ),
			'app_version'    => __( 'App Ver', 'mrdw' ),
			'locale'         => __( 'Locale', 'mrdw' ),
			'last_active_at' => __( 'Last Active', 'mrdw' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'user_label'     => array( 'user_label', false ),
			'device_type'    => array( 'device_type', false ),
			'last_active_at' => array( 'last_active_at', false ),
		);
	}

	/**
	 * Column cb.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="device_ids[]" value="%d" />', $item->id );
	}

	/**
	 * Column: user_label.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_user_label( $item ) {
		$label = ! empty( $item->user_label ) ? esc_html( $item->user_label ) : '<em>' . esc_html__( '(no label)', 'mrdw' ) . '</em>';

		$badges = '';
		if ( $item->is_dev ) {
			$badges .= ' <span class="mrdw-push-badge mrdw-push-badge-yellow">DEV</span>';
		}
		if ( ! $item->is_active ) {
			$badges .= ' <span class="mrdw-push-badge mrdw-push-badge-red">' . esc_html__( 'Inactive', 'mrdw' ) . '</span>';
		}

		// Groups (pre-loaded in bulk via prepare_items).
		$groups = isset( $this->groups_map[ $item->id ] ) ? $this->groups_map[ $item->id ] : array();
		foreach ( $groups as $group ) {
			$badges .= ' <span class="mrdw-push-badge mrdw-push-badge-blue">' . esc_html( $group->name ) . '</span>';
		}

		// Row actions.
		$actions = array(
			'edit'       => sprintf(
				'<a href="#" class="mrdw-push-edit-device" data-id="%d" data-label="%s">%s</a>',
				$item->id,
				esc_attr( $item->user_label ),
				esc_html__( 'Edit Label', 'mrdw' )
			),
			'toggle_dev' => sprintf(
				'<a href="#" class="mrdw-push-toggle-dev" data-id="%d" data-dev="%d">%s</a>',
				$item->id,
				$item->is_dev ? 0 : 1,
				$item->is_dev ? esc_html__( 'Remove Dev', 'mrdw' ) : esc_html__( 'Mark Dev', 'mrdw' )
			),
			'delete'     => sprintf(
				'<a href="%s" class="mrdw-push-delete-device" onclick="return confirm(\'%s\')">%s</a>',
				wp_nonce_url(
					admin_url( 'admin.php?page=mrdw-push-devices&action=delete&device_id=' . $item->id ),
					'mrdw_push_delete_device_' . $item->id
				),
				esc_js( __( 'Are you sure you want to delete this device?', 'mrdw' ) ),
				esc_html__( 'Delete', 'mrdw' )
			),
		);

		return $label . $badges . $this->row_actions( $actions );
	}

	/**
	 * Column: expo_token.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_expo_token( $item ) {
		$token = $item->expo_token;
		if ( strlen( $token ) > 30 ) {
			$token = substr( $token, 0, 25 ) . '...';
		}
		return '<code class="mrdw-push-code">' . esc_html( $token ) . '</code>';
	}

	/**
	 * Column: device_type.
	 *
	 * @param object $item The item.
	 * @return string
	 */
	public function column_device_type( $item ) {
		if ( 'ios' === $item->device_type ) {
			return '<span class="mrdw-push-badge mrdw-push-badge-gray">iOS</span>';
		} elseif ( 'android' === $item->device_type ) {
			return '<span class="mrdw-push-badge mrdw-push-badge-green">Android</span>';
		}
		if ( empty( $item->device_type ) ) {
			return '<em>' . esc_html__( '(unknown)', 'mrdw' ) . '</em>';
		}
		return esc_html( $item->device_type );
	}

	/**
	 * Default column handler.
	 *
	 * @param object $item        The item.
	 * @param string $column_name The column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'device_model':
			case 'os_version':
			case 'app_version':
			case 'locale':
				if ( empty( $item->$column_name ) ) {
					return '<em>' . esc_html__( '(unknown)', 'mrdw' ) . '</em>';
				}
				return esc_html( $item->$column_name );
			case 'last_active_at':
				if ( empty( $item->last_active_at ) ) {
					return '<em>' . esc_html__( 'Never', 'mrdw' ) . '</em>';
				}
				return esc_html( human_time_diff( strtotime( $item->last_active_at ), time() ) ) . ' ' . esc_html__( 'ago', 'mrdw' );
			default:
				return '';
		}
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'mrdw' ),
		);
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

		$current_type  = isset( $_REQUEST['device_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['device_type'] ) ) : '';
		$current_dev   = isset( $_REQUEST['is_dev'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['is_dev'] ) ) : '';
		$current_group = isset( $_REQUEST['group_id'] ) ? intval( $_REQUEST['group_id'] ) : '';
		$groups        = MRDW_Push_DB::get_all_groups();

		echo '<div class="alignleft actions">';

		// Platform filter.
		echo '<select name="device_type">';
		echo '<option value="">' . esc_html__( 'All Platforms', 'mrdw' ) . '</option>';
		echo '<option value="ios"' . selected( $current_type, 'ios', false ) . '>iOS</option>';
		echo '<option value="android"' . selected( $current_type, 'android', false ) . '>Android</option>';
		echo '</select>';

		// Dev filter.
		echo '<select name="is_dev">';
		echo '<option value="">' . esc_html__( 'All Devices', 'mrdw' ) . '</option>';
		echo '<option value="1"' . selected( $current_dev, '1', false ) . '>' . esc_html__( 'Dev Only', 'mrdw' ) . '</option>';
		echo '<option value="0"' . selected( $current_dev, '0', false ) . '>' . esc_html__( 'Non-Dev', 'mrdw' ) . '</option>';
		echo '</select>';

		// Group filter.
		if ( ! empty( $groups ) ) {
			echo '<select name="group_id">';
			echo '<option value="">' . esc_html__( 'All Groups', 'mrdw' ) . '</option>';
			foreach ( $groups as $group ) {
				echo '<option value="' . esc_attr( $group->id ) . '"' . selected( $current_group, $group->id, false ) . '>' . esc_html( $group->name ) . '</option>';
			}
			echo '</select>';
		}

		submit_button( __( 'Filter', 'mrdw' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare items for display.
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page = 20;

		$args = array(
			'per_page'    => $per_page,
			'page'        => $this->get_pagenum(),
			'orderby'     => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
			'order'       => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
			'search'      => isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '',
			'device_type' => isset( $_REQUEST['device_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['device_type'] ) ) : '',
			'is_dev'      => isset( $_REQUEST['is_dev'] ) && '' !== $_REQUEST['is_dev'] ? sanitize_text_field( wp_unslash( $_REQUEST['is_dev'] ) ) : '',
			'group_id'    => isset( $_REQUEST['group_id'] ) && '' !== $_REQUEST['group_id'] ? intval( $_REQUEST['group_id'] ) : '',
		);

		$result = MRDW_Push_DB::get_devices( $args );

		$this->items = $result['items'];

		// Bulk-load groups for all devices on this page (1 query vs N).
		if ( ! empty( $this->items ) ) {
			$device_ids       = wp_list_pluck( $this->items, 'id' );
			$this->groups_map = MRDW_Push_DB::get_devices_groups_bulk( $device_ids );
		}

		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
				'total_pages' => ceil( $result['total'] / $per_page ),
			)
		);
	}
}

class MRDW_Push_Admin_Devices {

	/**
	 * Render the devices page.
	 */
	public function render() {
		// Handle single delete action.
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['device_id'] ) ) {
			if ( ! current_user_can( 'mrdw_manage' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'mrdw' ) );
			}
			$device_id = intval( $_GET['device_id'] );
			check_admin_referer( 'mrdw_push_delete_device_' . $device_id );
			MRDW_Push_DB::delete_device( $device_id );
			wp_safe_redirect( admin_url( 'admin.php?page=mrdw-push-devices&deleted=1' ) );
			exit;
		}

		// Handle bulk actions.
		$bulk_action = '';
		if ( ! empty( $_POST['action'] ) && '-1' !== $_POST['action'] ) {
			$bulk_action = sanitize_key( wp_unslash( $_POST['action'] ) );
		} elseif ( ! empty( $_POST['action2'] ) && '-1' !== $_POST['action2'] ) {
			$bulk_action = sanitize_key( wp_unslash( $_POST['action2'] ) );
		}
		if ( 'delete' === $bulk_action && ! empty( $_POST['device_ids'] ) ) {
			if ( ! current_user_can( 'mrdw_manage' ) ) {
				wp_die( esc_html__( 'You do not have permission to perform this action.', 'mrdw' ) );
			}
			check_admin_referer( 'bulk-devices' );
			$device_ids = array_map( 'intval', $_POST['device_ids'] );
			MRDW_Push_DB::bulk_delete_devices( $device_ids );
			wp_safe_redirect( admin_url( 'admin.php?page=mrdw-push-devices&deleted=' . count( $device_ids ) ) );
			exit;
		}

		// Summary stats — single query instead of 3 separate COUNTs.
		$device_stats    = MRDW_Push_DB::get_device_summary_stats();
		$device_count    = $device_stats['total'];
		$platform_counts = array(
			'ios'     => $device_stats['ios'],
			'android' => $device_stats['android'],
		);
		$dev_count       = $device_stats['dev'];

		include MRDW_PUSH_DIR . 'admin/partials/devices.php';
	}

	/**
	 * Handle AJAX update device.
	 */
	public function handle_update_device() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$device_id  = isset( $_POST['device_id'] ) ? intval( $_POST['device_id'] ) : 0;
		$user_label = isset( $_POST['user_label'] ) ? sanitize_text_field( wp_unslash( $_POST['user_label'] ) ) : '';

		if ( ! $device_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid device ID.', 'mrdw' ) ) );
		}

		$result = MRDW_Push_DB::update_device( $device_id, array( 'user_label' => $user_label ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Device updated.', 'mrdw' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update device.', 'mrdw' ) ) );
		}
	}

	/**
	 * Handle AJAX toggle dev flag.
	 */
	public function handle_toggle_dev() {
		check_ajax_referer( 'mrdw_push_nonce', 'nonce' );

		if ( ! current_user_can( 'mrdw_manage' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mrdw' ) ) );
		}

		$device_id = isset( $_POST['device_id'] ) ? intval( $_POST['device_id'] ) : 0;
		$is_dev    = isset( $_POST['is_dev'] ) ? intval( $_POST['is_dev'] ) : 0;

		if ( ! $device_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid device ID.', 'mrdw' ) ) );
		}

		$result = MRDW_Push_DB::update_device( $device_id, array( 'is_dev' => $is_dev ) );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => $is_dev
						? __( 'Device marked as dev.', 'mrdw' )
						: __( 'Device removed from dev.', 'mrdw' ),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update device.', 'mrdw' ) ) );
		}
	}
}
