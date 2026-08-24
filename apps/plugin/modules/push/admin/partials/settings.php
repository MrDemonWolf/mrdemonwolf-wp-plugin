<?php
/**
 * Settings admin page template.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="mrdw-push-app" class="wrap">
	<!-- Page Header -->
	<div class="mrdw-push-page-header">
		<h1>
			<span class="mrdw-push-page-header-icon"><span class="dashicons dashicons-admin-generic"></span></span>
			<?php esc_html_e( 'Settings', 'mrdw' ); ?>
		</h1>
		<p class="mrdw-push-page-desc"><?php esc_html_e( 'Configure MRDW_Push behavior, templates, and integrations.', 'mrdw' ); ?></p>
	</div>

	<form method="post" action="options.php">
		<?php settings_fields( 'mrdw_push_settings' ); ?>

		<?php
		global $wp_settings_sections, $wp_settings_fields;
		$mrdw_push_settings_page = 'mrdw-push-settings';
		if ( isset( $wp_settings_sections[ $mrdw_push_settings_page ] ) ) {
			foreach ( $wp_settings_sections[ $mrdw_push_settings_page ] as $section ) {
				echo '<div class="mrdw-push-settings-section">';
				if ( $section['title'] ) {
					echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
				}
				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}
				if ( isset( $wp_settings_fields[ $mrdw_push_settings_page ][ $section['id'] ] ) ) {
					echo '<table class="form-table" role="presentation">';
					do_settings_fields( $mrdw_push_settings_page, $section['id'] );
					echo '</table>';
				}
				echo '</div>';
			}
		}
		?>

		<div class="mrdw-push-settings-submit">
			<?php submit_button( __( 'Save Settings', 'mrdw' ), 'mrdw-push-btn-brand', 'submit', false ); ?>
		</div>
	</form>

	<!-- Data Management -->
	<div class="tw-mt-6">
		<div class="mrdw-push-card">
			<div class="mrdw-push-card-header">
				<h2><?php esc_html_e( 'Data Management', 'mrdw' ); ?></h2>
			</div>
			<div class="mrdw-push-card-body">
				<p class="mrdw-push-settings-helper"><?php esc_html_e( 'Export all registered devices as a CSV, or import devices from a CSV file.', 'mrdw' ); ?></p>
				<div class="tw-flex tw-gap-3">
					<a href="<?php echo esc_url( wp_nonce_url( rest_url( 'mrdw/v1/devices/export' ), 'wp_rest', '_wpnonce' ) ); ?>" class="button">
						<span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;margin-right:4px;vertical-align:middle;margin-top:-2px;"></span>
						<?php esc_html_e( 'Export Devices (CSV)', 'mrdw' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mrdw-push-devices' ) ); ?>" class="button">
						<span class="dashicons dashicons-upload" style="font-size:16px;width:16px;height:16px;margin-right:4px;vertical-align:middle;margin-top:-2px;"></span>
						<?php esc_html_e( 'Import Devices (CSV)', 'mrdw' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- About -->
	<div class="tw-mt-4">
		<div class="mrdw-push-about-card">
			<div class="mrdw-push-about-card-logo">
				<span class="dashicons dashicons-bell"></span>
			</div>
			<div class="mrdw-push-about-card-info">
				<strong>MRDW_Push</strong>
				<span class="mrdw-push-about-card-version">v<?php echo esc_html( MRDW_VERSION ); ?></span>
				<span class="mrdw-push-about-card-sep">&bull;</span>
				<a href="https://github.com/mrdemonwolf/MRDW_Push/releases" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Changelog', 'mrdw' ); ?>
				</a>
				<span class="mrdw-push-about-card-sep">&bull;</span>
				<a href="https://github.com/mrdemonwolf/MRDW_Push/issues" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Report Issue', 'mrdw' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
