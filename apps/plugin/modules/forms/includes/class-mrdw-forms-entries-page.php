<?php
/**
 * Admin entries page controller.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Forms_Entries_Page
 *
 * Handles the admin menu page for viewing and managing MRDW_Forms entries.
 */
class MRDW_Forms_Entries_Page {

	/**
	 * Register admin menu pages.
	 */
	public function add_menu_pages() {
		add_submenu_page(
			MRDW_Admin::MENU_SLUG,
			__( 'Form Entries', 'mrdw' ),
			__( 'Form Entries', 'mrdw' ),
			'manage_options',
			'mrdw-forms-entries',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Get a nonced URL for viewing an entry.
	 *
	 * @param int $entry_id The entry ID.
	 * @return string
	 */
	public static function get_view_url( $entry_id ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'page'     => 'mrdw-forms-entries',
					'action'   => 'view',
					'entry_id' => absint( $entry_id ),
				),
				admin_url( 'admin.php' )
			),
			'mrdw_forms_view_entry_' . absint( $entry_id )
		);
	}

	/**
	 * Enqueue admin styles and scripts for MRDW_Forms pages.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'mrdw-forms' ) ) {
			return;
		}

		wp_enqueue_style(
			'mrdw-forms-admin',
			MRDW_FORMS_URL . 'assets/css/mrdw-forms-admin.css',
			array(),
			MRDW_VERSION
		);

		wp_enqueue_script(
			'mrdw-forms-admin',
			MRDW_FORMS_URL . 'assets/js/mrdw-forms-admin.js',
			array( 'jquery' ),
			MRDW_VERSION,
			true
		);

		wp_localize_script(
			'mrdw-forms-admin',
			'mrdwFormsAdmin',
			array(
				'adminEmail' => get_option( 'admin_email' ),
			)
		);
	}

	/**
	 * Render the entries page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! MRDW_Forms_Activator::is_provider_available() ) {
			$provider = MRDW_Forms_Provider_Factory::create();
			$label    = $provider->get_label();

			echo '<div class="wrap mrdw-forms-wrap">';
			echo '<h1>' . esc_html__( 'MRDW_Forms Entries', 'mrdw' ) . '</h1>';
			echo '<div class="notice notice-warning"><p>';
			printf(
				/* translators: %s: form builder name */
				esc_html__( 'MRDW_Forms requires %1$s to be installed and active. Please install %2$s to use MRDW_Forms.', 'mrdw' ),
				esc_html( $label ),
				esc_html( $label )
			);
			echo '</p></div>';
			echo '</div>';
			return;
		}

		$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? '' ) );

		if ( 'view' === $action && ! empty( $_GET['entry_id'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'mrdw_forms_view_entry_' . absint( $_GET['entry_id'] ) ) ) {
				wp_die( esc_html__( 'Security check failed.', 'mrdw' ) );
			}
			$this->render_detail_page( absint( $_GET['entry_id'] ) );
			return;
		}

		if ( 'delete' === $action && ! empty( $_GET['entry_id'] ) ) {
			$this->handle_delete( absint( $_GET['entry_id'] ) );
		}

		$this->handle_bulk_actions();
		$this->render_list_page();
	}

	/**
	 * Handle CSV export.
	 */
	public function handle_export() {
		if ( ! isset( $_GET['mrdw_forms_export'] ) || '1' !== $_GET['mrdw_forms_export'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'mrdw_forms_export_csv' );

		$provider_filter = sanitize_text_field( wp_unslash( $_GET['provider_filter'] ?? '' ) );
		$source_filter   = sanitize_text_field( wp_unslash( $_GET['source_filter'] ?? '' ) );

		$base_args = array();

		if ( $provider_filter ) {
			$base_args['provider'] = $provider_filter;
		}

		if ( 'divi_frontend' === $source_filter ) {
			$base_args['provider'] = 'divi_frontend';
		} elseif ( 'mobile_app' === $source_filter ) {
			$base_args['exclude_provider'] = 'divi_frontend';
		}

		$store      = new MRDW_Forms_Entry_Store();
		$chunk_size = 500;

		// First pass: collect all unique field labels (keyset pagination —
		// LIMIT/OFFSET degrades to O(n²) row reads on large tables).
		$all_labels = array();
		$since_id   = 0;
		do {
			$chunk = $store->get_entries(
				array_merge(
					$base_args,
					array(
						'per_page' => $chunk_size,
						'since_id' => $since_id,
						'orderby'  => 'id',
						'order'    => 'ASC',
					)
				)
			);

			foreach ( $chunk as $entry ) {
				$since_id = (int) $entry['id'];
				$fields   = json_decode( $entry['fields'], true );
				if ( is_array( $fields ) ) {
					foreach ( array_keys( $fields ) as $label ) {
						if ( ! in_array( $label, $all_labels, true ) ) {
							$all_labels[] = $label;
						}
					}
				}
			}
		} while ( count( $chunk ) === $chunk_size );

		$filename = 'mrdw-forms-entries-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// UTF-8 BOM for Excel compatibility.
		fwrite( $output, "\xEF\xBB\xBF" );

		// Header row.
		$headers = array_merge(
			array( 'ID', 'Source', 'Provider', 'Form ID', 'Form Name', 'Page', 'Date', 'IP Address' ),
			array_map( array( $this, 'sanitize_csv_cell' ), $all_labels )
		);
		fputcsv( $output, $headers );

		// Second pass: stream data rows in chunks.
		$since_id = 0;
		do {
			$chunk = $store->get_entries(
				array_merge(
					$base_args,
					array(
						'per_page' => $chunk_size,
						'since_id' => $since_id,
						'orderby'  => 'id',
						'order'    => 'ASC',
					)
				)
			);

			foreach ( $chunk as $entry ) {
				$since_id = (int) $entry['id'];
				$fields   = json_decode( $entry['fields'], true );
				$source   = ( 'divi_frontend' === $entry['provider'] ) ? 'Divi Frontend' : 'Mobile App';
				$row      = array(
					$entry['id'],
					$source,
					$entry['provider'],
					$entry['form_id'],
					$entry['form_name'],
					$entry['page_title'],
					$entry['date_created'],
					$entry['ip_address'],
				);

				foreach ( $all_labels as $label ) {
					$value = isset( $fields[ $label ] ) ? $fields[ $label ] : '';
					$row[] = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
				}

				fputcsv( $output, array_map( array( $this, 'sanitize_csv_cell' ), $row ) );
			}

			flush();
		} while ( count( $chunk ) === $chunk_size );

		fclose( $output );
		exit;
	}

	/**
	 * Neutralize spreadsheet formula injection in a CSV cell.
	 *
	 * Submitted field values are untrusted; a leading =, +, -, @, tab, or CR
	 * would be interpreted as a formula by Excel/LibreOffice/Sheets (CWE-1236).
	 *
	 * @param mixed $value The cell value.
	 * @return string
	 */
	public function sanitize_csv_cell( $value ) {
		$value = (string) $value;

		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}

		return $value;
	}

	/**
	 * Handle single entry deletion.
	 *
	 * @param int $entry_id The entry ID.
	 */
	private function handle_delete( $entry_id ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'mrdw_forms_delete_entry_' . $entry_id ) ) {
			return;
		}

		$store = new MRDW_Forms_Entry_Store();
		$store->delete_entry( $entry_id );

		echo '<div class="notice notice-success"><p>' . esc_html__( 'Entry deleted.', 'mrdw' ) . '</p></div>';
	}

	/**
	 * Handle bulk actions.
	 */
	private function handle_bulk_actions() {
		// The list table renders inside a GET form, so bulk-action fields
		// arrive in $_GET; read $_REQUEST to support either method.
		if ( empty( $_REQUEST['entry_ids'] ) || ! is_array( $_REQUEST['entry_ids'] ) ) {
			return;
		}

		if ( 'delete' !== sanitize_key( wp_unslash( $_REQUEST['action'] ?? '' ) )
			&& 'delete' !== sanitize_key( wp_unslash( $_REQUEST['action2'] ?? '' ) ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ?? '' ) ), 'bulk-entries' ) ) {
			return;
		}

		$store = new MRDW_Forms_Entry_Store();
		$ids   = array_map( 'absint', $_REQUEST['entry_ids'] );

		foreach ( $ids as $id ) {
			$store->delete_entry( $id );
		}

		echo '<div class="notice notice-success"><p>';
		printf(
			/* translators: %d: number of entries deleted */
			esc_html__( '%d entries deleted.', 'mrdw' ),
			count( $ids )
		);
		echo '</p></div>';
	}

	/**
	 * Render the list page.
	 */
	private function render_list_page() {
		$list_table = new MRDW_Forms_Entries_List_Table();
		$list_table->prepare_items();

		$export_url = wp_nonce_url(
			add_query_arg(
				array(
					'mrdw_forms_export' => '1',
					'provider_filter'  => sanitize_text_field( wp_unslash( $_GET['provider_filter'] ?? '' ) ),
					'source_filter'    => sanitize_text_field( wp_unslash( $_GET['source_filter'] ?? '' ) ),
				),
				admin_url( 'admin.php' )
			),
			'mrdw_forms_export_csv'
		);

		echo '<div class="wrap mrdw-forms-wrap">';
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'MRDW_Forms Entries', 'mrdw' ) . '</h1>';
		echo '<a href="' . esc_url( $export_url ) . '" class="page-title-action">' . esc_html__( 'Export CSV', 'mrdw' ) . '</a>';
		echo '<hr class="wp-header-end">';
		echo '<form method="get">';
		echo '<input type="hidden" name="page" value="mrdw-forms-entries" />';
		$list_table->display();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render the detail page for a single entry.
	 *
	 * @param int $entry_id The entry ID.
	 */
	private function render_detail_page( $entry_id ) {
		$store = new MRDW_Forms_Entry_Store();
		$entry = $store->get_entry( $entry_id );

		$back_url = admin_url( 'admin.php?page=mrdw-forms-entries' );

		echo '<div class="wrap mrdw-forms-wrap">';
		printf(
			'<h1>%s <a href="%s" class="page-title-action">%s</a></h1>',
			/* translators: %d: entry ID */
			esc_html( sprintf( __( 'Entry #%d', 'mrdw' ), $entry_id ) ),
			esc_url( $back_url ),
			esc_html__( 'Back to Entries', 'mrdw' )
		);

		if ( ! $entry ) {
			echo '<p>' . esc_html__( 'Entry not found.', 'mrdw' ) . '</p>';
			echo '</div>';
			return;
		}

		$provider_labels = array(
			'divi'          => 'Divi',
			'divi_frontend' => 'Divi',
			'wpforms'       => 'WPForms',
			'gravityforms'  => 'Gravity Forms',
		);

		if ( 'divi_frontend' === $entry['provider'] ) {
			$source_label = __( 'Divi Frontend', 'mrdw' );
			$source_type  = 'frontend';
		} else {
			$source_label = __( 'Mobile App', 'mrdw' );
			$source_type  = 'mobile';
		}

		echo '<div class="mrdw-forms-detail">';
		echo '<table class="widefat striped">';
		echo '<tbody>';

		echo '<tr><th>' . esc_html__( 'ID', 'mrdw' ) . '</th><td>' . absint( $entry['id'] ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Source', 'mrdw' ) . '</th><td><span class="mrdw-forms-source-badge ' . esc_attr( $source_type ) . '">' . esc_html( $source_label ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Provider', 'mrdw' ) . '</th><td><span class="mrdw-forms-provider-badge">' . esc_html( $provider_labels[ $entry['provider'] ] ?? $entry['provider'] ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Form ID', 'mrdw' ) . '</th><td>' . esc_html( $entry['form_id'] ) . '</td></tr>';

		if ( ! empty( $entry['form_name'] ) ) {
			echo '<tr><th>' . esc_html__( 'Form Name', 'mrdw' ) . '</th><td>' . esc_html( $entry['form_name'] ) . '</td></tr>';
		}

		if ( ! empty( $entry['page_title'] ) ) {
			echo '<tr><th>' . esc_html__( 'Page', 'mrdw' ) . '</th><td>' . esc_html( $entry['page_title'] ) . '</td></tr>';
		}

		echo '<tr><th>' . esc_html__( 'IP Address', 'mrdw' ) . '</th><td>' . esc_html( $entry['ip_address'] ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'User Agent', 'mrdw' ) . '</th><td>' . esc_html( $entry['user_agent'] ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Date', 'mrdw' ) . '</th><td>' . esc_html( $entry['date_created'] ) . '</td></tr>';

		if ( ! empty( $entry['referer_url'] ) ) {
			echo '<tr><th>' . esc_html__( 'Referer', 'mrdw' ) . '</th><td>' . esc_html( $entry['referer_url'] ) . '</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';

		// Fields table.
		$fields = json_decode( $entry['fields'], true );
		if ( is_array( $fields ) && ! empty( $fields ) ) {
			echo '<h2>' . esc_html__( 'Submitted Fields', 'mrdw' ) . '</h2>';
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>' . esc_html__( 'Field', 'mrdw' ) . '</th><th>' . esc_html__( 'Value', 'mrdw' ) . '</th></tr></thead>';
			echo '<tbody>';

			foreach ( $fields as $field_id => $value ) {
				echo '<tr>';
				echo '<td>' . esc_html( $field_id ) . '</td>';
				echo '<td>' . esc_html( is_scalar( $value ) ? (string) $value : wp_json_encode( $value ) ) . '</td>';
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';
		}

		echo '</div>';
		echo '</div>';
	}
}
