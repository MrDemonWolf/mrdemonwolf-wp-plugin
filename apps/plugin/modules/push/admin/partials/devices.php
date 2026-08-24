<?php
/**
 * Devices admin page template.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tailsignal-app" class="wrap">
	<!-- Page Header -->
	<div class="tailsignal-page-header tw-flex tw-items-start tw-justify-between">
		<div>
			<h1>
				<span class="tailsignal-page-header-icon"><span class="dashicons dashicons-smartphone"></span></span>
				<?php esc_html_e( 'Devices', 'mrdemonwolf' ); ?>
			</h1>
			<p class="tailsignal-page-desc"><?php esc_html_e( 'Manage registered push notification devices.', 'mrdemonwolf' ); ?></p>
		</div>
		<div class="tw-flex tw-gap-2">
			<a href="<?php echo esc_url( wp_nonce_url( rest_url( 'tailsignal/v1/devices/export' ), 'wp_rest', '_wpnonce' ) ); ?>" class="button">
				<?php esc_html_e( 'Export CSV', 'mrdemonwolf' ); ?>
			</a>
			<button type="button" class="button" id="tailsignal-import-btn">
				<?php esc_html_e( 'Import CSV', 'mrdemonwolf' ); ?>
			</button>
		</div>
	</div>

	<!-- Summary Stats -->
	<div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 tw-mb-6">
		<div class="tailsignal-stat-card tailsignal-stat-card--brand">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--brand"><span class="dashicons dashicons-groups"></span></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'Total Active', 'mrdemonwolf' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $device_count ); ?></div>
		</div>
		<div class="tailsignal-stat-card tailsignal-stat-card--gray">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--gray"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 384 512" fill="currentColor"><path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/></svg></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'iOS', 'mrdemonwolf' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $platform_counts['ios'] ); ?></div>
		</div>
		<div class="tailsignal-stat-card tailsignal-stat-card--green">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--green"><span class="dashicons dashicons-tablet"></span></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'Android', 'mrdemonwolf' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $platform_counts['android'] ); ?></div>
		</div>
		<div class="tailsignal-stat-card tailsignal-stat-card--yellow">
			<div class="tailsignal-stat-icon tailsignal-stat-icon--yellow"><span class="dashicons dashicons-admin-tools"></span></div>
			<div class="tailsignal-stat-label"><?php esc_html_e( 'Dev', 'mrdemonwolf' ); ?></div>
			<div class="tailsignal-stat-value"><?php echo esc_html( $dev_count ); ?></div>
		</div>
	</div>

	<!-- Import Form (hidden) -->
	<div id="tailsignal-import-form" class="tailsignal-card tw-mb-6" style="display:none;">
		<div class="tailsignal-card-body">
			<form method="post" enctype="multipart/form-data" id="tailsignal-import-upload">
				<div class="tw-flex tw-items-center tw-gap-4">
					<input type="file" name="file" accept=".csv" required class="tw-text-sm" />
					<button type="submit" class="button tailsignal-btn-brand"><?php esc_html_e( 'Upload & Import', 'mrdemonwolf' ); ?></button>
					<button type="button" class="button" id="tailsignal-import-cancel"><?php esc_html_e( 'Cancel', 'mrdemonwolf' ); ?></button>
				</div>
				<span id="tailsignal-import-status" class="tw-text-sm tw-mt-2 tw-block" aria-live="polite"></span>
			</form>
		</div>
	</div>

	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number of devices deleted */
					esc_html( _n( '%d device deleted.', '%d devices deleted.', intval( $_GET['deleted'] ), 'mrdemonwolf' ) ),
					intval( $_GET['deleted'] )
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Edit Label Dialog (hidden) -->
	<div id="tailsignal-edit-dialog" class="tw-fixed tw-inset-0 tailsignal-modal-overlay tw-flex tw-items-center tw-justify-center tw-z-50" style="display:none;" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Edit Device Label', 'mrdemonwolf' ); ?>">
		<div class="tailsignal-modal-panel tw-w-96">
			<div class="tailsignal-modal-header">
				<h3><?php esc_html_e( 'Edit Device Label', 'mrdemonwolf' ); ?></h3>
			</div>
			<div class="tailsignal-modal-body">
				<input type="text" id="tailsignal-edit-label" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-mb-4" />
				<input type="hidden" id="tailsignal-edit-device-id" />
				<div class="tw-flex tw-justify-end tw-gap-2">
					<button type="button" class="button" id="tailsignal-edit-cancel"><?php esc_html_e( 'Cancel', 'mrdemonwolf' ); ?></button>
					<button type="button" class="button tailsignal-btn-brand" id="tailsignal-edit-save"><?php esc_html_e( 'Save', 'mrdemonwolf' ); ?></button>
				</div>
			</div>
		</div>
	</div>

<?php
$table = new TailSignal_Devices_List_Table();
$table->prepare_items();
?>
	<div class="tailsignal-table-wrap">
		<form method="post">
			<input type="hidden" name="page" value="tailsignal-devices" />
			<?php
			$table->search_box( __( 'Search Devices', 'mrdemonwolf' ), 'tailsignal-search' );
			$table->display();
			?>
		</form>
	</div>
</div>
