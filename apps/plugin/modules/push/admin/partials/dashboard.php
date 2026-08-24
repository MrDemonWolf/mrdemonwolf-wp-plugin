<?php
/**
 * Dashboard admin page template.
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
				<span class="mrdw-push-page-header-icon"><span class="dashicons dashicons-chart-area"></span></span>
				<?php esc_html_e( 'Dashboard', 'mrdw' ); ?>
			</h1>
			<p class="mrdw-push-page-desc"><?php esc_html_e( 'Overview of your push notification activity.', 'mrdw' ); ?></p>
		</div>
		<?php if ( $dev_mode ) : ?>
			<span class="mrdw-push-dev-pill"><?php esc_html_e( 'Dev Mode: ON', 'mrdw' ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( $dev_mode ) : ?>
		<div class="mrdw-push-dev-banner tw-mb-6">
			<span class="mrdw-push-dev-banner-icon">&#x26A0;&#xFE0F;</span>
			<p>
				<?php
				printf(
					/* translators: %d: dev device count */
					esc_html__( 'Dev Mode is ON — notifications only go to dev devices (%d).', 'mrdw' ),
					(int) $dev_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Stats Cards -->
	<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4 tw-mb-6">
		<!-- Devices Card -->
		<div class="mrdw-push-stat-card mrdw-push-stat-card--brand">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--brand"><span class="dashicons dashicons-smartphone"></span></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'Devices', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $device_count ); ?></div>
			<div class="mrdw-push-stat-detail">
				<?php
				printf(
					/* translators: 1: iOS count, 2: Android count */
					esc_html__( '%1$d iOS, %2$d Android', 'mrdw' ),
					(int) $platform_counts['ios'],
					(int) $platform_counts['android']
				);
				?>
			</div>
		</div>

		<!-- Sent Card -->
		<div class="mrdw-push-stat-card mrdw-push-stat-card--green">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--green"><span class="dashicons dashicons-email-alt"></span></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'Sent This Month', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $monthly_sent ); ?></div>
			<div class="mrdw-push-stat-detail"><?php esc_html_e( 'notifications delivered', 'mrdw' ); ?></div>
		</div>

		<!-- Success Rate Card -->
		<div class="mrdw-push-stat-card mrdw-push-stat-card--purple">
			<div class="mrdw-push-stat-icon mrdw-push-stat-icon--purple"><span class="dashicons dashicons-yes-alt"></span></div>
			<div class="mrdw-push-stat-label"><?php esc_html_e( 'Success Rate', 'mrdw' ); ?></div>
			<div class="mrdw-push-stat-value"><?php echo esc_html( $success_rate ); ?>%</div>
			<div class="mrdw-push-stat-detail"><?php esc_html_e( 'delivery rate', 'mrdw' ); ?></div>
		</div>
	</div>

	<!-- Monthly Trends Chart -->
	<div class="mrdw-push-card tw-mb-6">
		<div class="mrdw-push-card-header">
			<h2><?php esc_html_e( 'Monthly Trends', 'mrdw' ); ?></h2>
		</div>
		<div class="mrdw-push-card-body" style="<?php echo empty( $chart_stats ) ? 'padding: 24px 20px;' : ''; ?>">
			<?php if ( ! empty( $chart_stats ) ) : ?>
				<canvas id="mrdw-push-chart" height="280" style="max-height: 280px;" aria-label="<?php esc_attr_e( 'Monthly notification trends chart', 'mrdw' ); ?>" role="img"></canvas>
			<?php else : ?>
				<div style="text-align: center; color: var(--ts-text-muted);">
					<span style="font-size: 24px; opacity: 0.4;">&#x1F4CA;</span>
					<p style="margin: 8px 0 0; font-size: 13px;"><?php esc_html_e( 'No notification data yet. Send your first notification to see trends here.', 'mrdw' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Recent Notifications -->
	<div class="mrdw-push-card">
		<div class="mrdw-push-card-header">
			<h2><?php esc_html_e( 'Recent Notifications', 'mrdw' ); ?></h2>
			<?php if ( ! empty( $recent ) ) : ?>
				<button type="button" id="mrdw-push-clear-recent" class="button button-small mrdw-push-btn-danger">
					<?php esc_html_e( 'Clear All', 'mrdw' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $recent ) ) : ?>
			<table class="tw-w-full">
				<thead>
					<tr>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Title', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Type', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Devices', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Status', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Date', 'mrdw' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $notification ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-900 tw-font-medium"><?php echo esc_html( wp_trim_words( $notification->title, 6, '...' ) ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<?php
								$type_badges = array(
									'post'      => 'mrdw-push-badge-green',
									'manual'    => 'mrdw-push-badge-blue',
									'scheduled' => 'mrdw-push-badge-purple',
								);
								$badge_class = $type_badges[ $notification->type ] ?? 'mrdw-push-badge-gray';
								?>
								<span class="mrdw-push-badge <?php echo esc_attr( $badge_class ); ?>">
									<?php echo esc_html( $notification->type ); ?>
								</span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500 tw-tabular-nums"><?php echo esc_html( $notification->total_devices ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<?php
								$status_badges = array(
									'sent'             => array( 'mrdw-push-badge-green', __( 'ok', 'mrdw' ) ),
									'receipts_checked' => array( 'mrdw-push-badge-green', __( 'ok', 'mrdw' ) ),
									'pending'          => array( 'mrdw-push-badge-gray', __( 'pending', 'mrdw' ) ),
									'scheduled'        => array( 'mrdw-push-badge-yellow', __( 'scheduled', 'mrdw' ) ),
									'failed'           => array( 'mrdw-push-badge-red', __( 'failed', 'mrdw' ) ),
									'cancelled'        => array( 'mrdw-push-badge-gray-muted', __( 'cancelled', 'mrdw' ) ),
								);
								$status_info   = $status_badges[ $notification->status ] ?? array( 'mrdw-push-badge-gray', $notification->status );
								?>
								<span class="mrdw-push-badge <?php echo esc_attr( $status_info[0] ); ?>">
									<?php echo esc_html( $status_info[1] ); ?>
								</span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500">
								<?php echo esc_html( human_time_diff( strtotime( $notification->created_at ), time() ) ); ?>
								<?php esc_html_e( 'ago', 'mrdw' ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="mrdw-push-empty-state">
				<div class="mrdw-push-empty-state-icon">&#x1F514;</div>
				<p><?php esc_html_e( 'No notifications sent yet. Your recent sends will appear here.', 'mrdw' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
