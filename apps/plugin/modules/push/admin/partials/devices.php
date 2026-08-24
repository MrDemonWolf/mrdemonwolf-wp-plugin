<?php
/**
 * Devices admin page template.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="mrdw-push-app" class="wrap">
	<!-- Page Header -->
	<div class="mrdw-push-page-header tw-flex tw-items-start tw-justify-between">
		<div>
			<h1>
				<span class="mrdw-push-page-header-icon"><span class="dashicons dashicons-smartphone"></span></span>
				<?php esc_html_e( 'Devices', 'mrdw' ); ?>
			</h1>
			<p class="mrdw-push-page-desc"><?php esc_html_e( 'Manage registered push notification devices.', 'mrdw' ); ?></p>
		</div>
		<div class="tw-flex tw-gap-2">
			<a href="<?php echo esc_url( wp_nonce_url( rest_url( 'mrdw/v1/devices/export' ), 'wp_rest', '_wpnonce' ) ); ?>" class="button">
				<?php esc_html_e( 'Export CSV', 'mrdw' ); ?>
			</a>
			<button type="button" class="button" id="mrdw-push-import-btn">
				<?php esc_html_e( 'Import CSV', 'mrdw' ); ?>
			</button>
		</div>
	</div>

	<!-- Summary Stats -->
	<div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 tw-mb-6">
		<div class="mrdw-push-stat-card mrdw-push-stat-card--brand">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--brand"><span class="dashicons dashicons-groups"></span></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'Total Active', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $device_count ); ?></div>
		</div>
		<div class="mrdw-push-stat-card mrdw-push-stat-card--gray">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--gray"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 384 512" fill="currentColor"><path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/></svg></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'iOS', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $platform_counts['ios'] ); ?></div>
		</div>
		<div class="mrdw-push-stat-card mrdw-push-stat-card--green">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--green"><span class="dashicons dashicons-tablet"></span></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'Android', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $platform_counts['android'] ); ?></div>
		</div>
		<div class="mrdw-push-stat-card mrdw-push-stat-card--yellow">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--yellow"><span class="dashicons dashicons-admin-tools"></span></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'Dev', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $dev_count ); ?></div>
		</div>
	</div>

	<!-- Import Form (hidden) -->
	<div id="mrdw-push-import-form" class="mrdw-push-card tw-mb-6" style="display:none;">
		<div class="mrdw-push-card-body">
			<form method="post" enctype="multipart/form-data" id="mrdw-push-import-upload">
				<div class="tw-flex tw-items-center tw-gap-4">
					<input type="file" name="file" accept=".csv" required class="tw-text-sm" />
					<button type="submit" class="button mrdw-push-btn-brand"><?php esc_html_e( 'Upload & Import', 'mrdw' ); ?></button>
					<button type="button" class="button" id="mrdw-push-import-cancel"><?php esc_html_e( 'Cancel', 'mrdw' ); ?></button>
				</div>
				<span id="mrdw-push-import-status" class="tw-text-sm tw-mt-2 tw-block" aria-live="polite"></span>
			</form>
		</div>
	</div>

	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number of devices deleted */
					esc_html( _n( '%d device deleted.', '%d devices deleted.', intval( $_GET['deleted'] ), 'mrdw' ) ),
					intval( $_GET['deleted'] )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Edit Label Dialog (hidden) -->
	<div id="mrdw-push-edit-dialog" class="tw-fixed tw-inset-0 mrdw-push-modal-overlay tw-flex tw-items-center tw-justify-center tw-z-50" style="display:none;" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Edit Device Label', 'mrdw' ); ?>">
		<div class="mrdw-push-modal-panel tw-w-96">
			<div class="mrdw-push-modal-header">
				<h3><?php esc_html_e( 'Edit Device Label', 'mrdw' ); ?></h3>
			</div>
			<div class="mrdw-push-modal-body">
				<input type="text" id="mrdw-push-edit-label" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-mb-4" />
				<input type="hidden" id="mrdw-push-edit-device-id" />
				<div class="tw-flex tw-justify-end tw-gap-2">
					<button type="button" class="button" id="mrdw-push-edit-cancel"><?php esc_html_e( 'Cancel', 'mrdw' ); ?></button>
					<button type="button" class="button mrdw-push-btn-brand" id="mrdw-push-edit-save"><?php esc_html_e( 'Save', 'mrdw' ); ?></button>
				</div>
			</div>
		</div>
	</div>

<?php
$table = new MRDW_Push_Devices_List_Table();
$table->prepare_items();
?>
	<div class="mrdw-push-table-wrap">
		<form method="post">
			<input type="hidden" name="page" value="mrdw-push-devices" />
			<?php
			$table->search_box( __( 'Search Devices', 'mrdw' ), 'mrdw-push-search' );
			$table->display();
			?>
		</form>
	</div>
</div>
