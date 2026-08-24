<?php
/**
 * Send Notification admin page template.
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
				<span class="mrdw-push-page-header-icon"><span class="dashicons dashicons-megaphone"></span></span>
				<?php esc_html_e( 'Send Notification', 'mrdw' ); ?>
			</h1>
			<p class="mrdw-push-page-desc"><?php esc_html_e( 'Compose and send push notifications to your audience.', 'mrdw' ); ?></p>
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
					esc_html__( 'Dev Mode ON — "All devices" will only send to %d dev devices.', 'mrdw' ),
					(int) $dev_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Fill Test Data Banner -->
	<div class="mrdw-push-test-banner tw-mb-6">
		<div class="tw-flex tw-items-center tw-gap-3">
			<span class="tw-text-lg">&#x1F9EA;</span>
			<p><?php esc_html_e( 'Testing? Pre-fill the form with sample data.', 'mrdw' ); ?></p>
		</div>
		<button type="button" id="mrdw-push-fill-test" class="button">
			<?php esc_html_e( 'Fill Test Data', 'mrdw' ); ?>
		</button>
	</div>

	<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
		<!-- Send Form (2/3 width) -->
		<div class="lg:tw-col-span-2">
			<form id="mrdw-push-send-form">
				<!-- Content Section -->
				<div class="mrdw-push-card tw-mb-6">
					<div class="mrdw-push-card-body">
						<h3 class="tw-m-0 tw-mb-4 mrdw-push-section-header"><?php esc_html_e( 'Content', 'mrdw' ); ?></h3>
						<div class="tw-space-y-4">
							<!-- Title -->
							<div>
								<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="mrdw-push-title">
									<?php esc_html_e( 'Title', 'mrdw' ); ?>
								</label>
								<input type="text" id="mrdw-push-title" name="title" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" required />
								<div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
									<div class="mrdw-push-placeholder-btns">
										<button type="button" class="mrdw-push-placeholder-btn" data-target="mrdw-push-title" data-value="{site_name}">{site_name}</button>
										<button type="button" class="mrdw-push-placeholder-btn" data-target="mrdw-push-title" data-value="{post_title}">{post_title}</button>
									</div>
									<span class="tw-text-xs mrdw-push-char-count" data-target="mrdw-push-title" data-limit="65">0 / 65</span>
								</div>
							</div>

							<!-- Body -->
							<div>
								<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="mrdw-push-body">
									<?php esc_html_e( 'Body', 'mrdw' ); ?>
								</label>
								<textarea id="mrdw-push-body" name="body" rows="3" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" required></textarea>
								<div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
									<div class="mrdw-push-placeholder-btns">
										<button type="button" class="mrdw-push-placeholder-btn" data-target="mrdw-push-body" data-value="{post_title}">{post_title}</button>
										<button type="button" class="mrdw-push-placeholder-btn" data-target="mrdw-push-body" data-value="{post_excerpt}">{post_excerpt}</button>
										<button type="button" class="mrdw-push-placeholder-btn" data-target="mrdw-push-body" data-value="{author_name}">{author_name}</button>
										<button type="button" class="mrdw-push-placeholder-btn" data-target="mrdw-push-body" data-value="{category}">{category}</button>
									</div>
									<span class="tw-text-xs mrdw-push-char-count" data-target="mrdw-push-body" data-limit="178">0 / 178</span>
								</div>
							</div>

							<!-- Image URL -->
							<div>
								<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="mrdw-push-image-url">
									<?php esc_html_e( 'Image URL (optional)', 'mrdw' ); ?>
								</label>
								<div class="tw-flex tw-gap-2">
									<input type="url" id="mrdw-push-image-url" name="image_url" class="tw-flex-1 tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" placeholder="https://example.com/image.jpg" />
									<button type="button" id="mrdw-push-choose-image" class="button">
										<span class="dashicons dashicons-format-image" style="line-height: 1.4;"></span>
										<?php esc_html_e( 'Choose Image', 'mrdw' ); ?>
									</button>
								</div>
								<p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-m-0"><?php esc_html_e( 'Shows as rich notification on iOS/Android.', 'mrdw' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<!-- Targeting Section -->
				<div class="mrdw-push-card tw-mb-6">
					<div class="mrdw-push-card-body">
						<h3 class="tw-m-0 tw-mb-4 mrdw-push-section-header"><?php esc_html_e( 'Targeting', 'mrdw' ); ?></h3>
						<div class="tw-space-y-2">
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="all" checked class="mrdw-push-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'All devices', 'mrdw' ); ?></span>
							</label>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="dev" class="mrdw-push-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Dev devices only', 'mrdw' ); ?></span>
							</label>
							<?php if ( ! empty( $groups ) ) : ?>
								<label class="tw-flex tw-items-center tw-gap-2">
									<input type="radio" name="target_type" value="group" class="mrdw-push-target-radio" />
									<span class="tw-text-sm"><?php esc_html_e( 'Group:', 'mrdw' ); ?></span>
									<select name="group_id" id="mrdw-push-group-select" class="tw-rounded-md tw-border tw-border-gray-300 tw-px-2 tw-py-1 tw-text-sm" disabled>
										<?php foreach ( $groups as $group ) : ?>
											<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
							<?php endif; ?>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="specific" class="mrdw-push-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Specific devices...', 'mrdw' ); ?></span>
							</label>
						</div>

						<!-- Specific Devices Picker -->
						<div id="mrdw-push-specific-devices" class="tw-mt-3 tw-border tw-border-gray-200 tw-rounded-md tw-p-3 tw-max-h-48 tw-overflow-y-auto" style="display:none;">
							<?php if ( ! empty( $devices['items'] ) ) : ?>
								<?php foreach ( $devices['items'] as $device ) : ?>
									<label class="tw-flex tw-items-center tw-gap-2 tw-py-1">
										<input type="checkbox" name="target_ids[]" value="<?php echo esc_attr( $device->id ); ?>" class="mrdw-push-device-checkbox" />
										<span class="tw-text-sm">
											<?php
											echo esc_html(
												! empty( $device->user_label )
													? $device->user_label
													: substr( $device->expo_token, 0, 25 ) . '...'
											);
											?>
											<?php if ( $device->is_dev ) : ?>
												<span class="mrdw-push-badge mrdw-push-badge-yellow tw-ml-1">DEV</span>
											<?php endif; ?>
										</span>
									</label>
								<?php endforeach; ?>
							<?php else : ?>
								<p class="tw-text-sm tw-text-gray-500 tw-m-0"><?php esc_html_e( 'No devices registered.', 'mrdw' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Scheduling Section -->
				<div class="mrdw-push-card tw-mb-6">
					<div class="mrdw-push-card-body">
						<h3 class="tw-m-0 tw-mb-4 mrdw-push-section-header"><?php esc_html_e( 'Scheduling', 'mrdw' ); ?></h3>
						<div class="tw-flex tw-items-center tw-gap-4">
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="send_when" value="now" checked class="mrdw-push-when-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Now', 'mrdw' ); ?></span>
							</label>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="send_when" value="schedule" class="mrdw-push-when-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Schedule:', 'mrdw' ); ?></span>
							</label>
							<input type="datetime-local" name="scheduled_at" id="mrdw-push-schedule-datetime" class="tw-rounded-md tw-border tw-border-gray-300 tw-px-2 tw-py-1 tw-text-sm" disabled />
						</div>
					</div>
				</div>

				<!-- Advanced Section -->
				<div class="mrdw-push-card tw-mb-6">
					<div class="mrdw-push-card-body">
						<h3 class="tw-m-0 tw-mb-4 mrdw-push-section-header"><?php esc_html_e( 'Advanced', 'mrdw' ); ?></h3>
						<div>
							<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="mrdw-push-data">
								<?php esc_html_e( 'Custom Data (JSON, optional)', 'mrdw' ); ?>
							</label>
							<textarea id="mrdw-push-data" name="data" rows="3" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-font-mono" placeholder='{ "screen": "article", "articleId": "123" }'></textarea>
							<p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-m-0">
								<?php esc_html_e( 'JSON payload sent to your app. For auto-published posts, MRDW_Push automatically includes post_id and post_type. Common keys for manual sends: post_id for deep linking, url for web links, badgeCount for badge updates.', 'mrdw' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Submit -->
				<div class="tw-flex tw-items-center tw-gap-3">
					<button type="submit" id="mrdw-push-send-btn" class="button mrdw-push-btn-brand">
						<?php esc_html_e( 'Signal the Pack', 'mrdw' ); ?>
					</button>
					<span id="mrdw-push-send-status" class="tw-text-sm" aria-live="polite"></span>
				</div>
			</form>
		</div>

		<!-- Live Preview (1/3 width) -->
		<div class="lg:tw-col-span-1">
			<div class="tw-sticky tw-top-8">
				<div class="mrdw-push-card">
					<div class="mrdw-push-card-body">
						<div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
							<h3 class="tw-m-0 mrdw-push-section-header"><?php esc_html_e( 'Preview', 'mrdw' ); ?></h3>
							<div class="mrdw-push-preview-toggle">
								<button type="button" class="mrdw-push-preview-toggle-btn active" data-preview="ios">iOS</button>
								<button type="button" class="mrdw-push-preview-toggle-btn" data-preview="android">Android</button>
							</div>
						</div>

						<!-- iOS-style notification mockup -->
						<div id="mrdw-push-preview-ios" class="mrdw-push-preview-variant">
							<div class="mrdw-push-preview-card">
								<div class="mrdw-push-preview-header">
									<span class="mrdw-push-preview-icon">&#x1F43E;</span>
									<span class="mrdw-push-preview-app"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
									<span class="mrdw-push-preview-time"><?php esc_html_e( 'now', 'mrdw' ); ?></span>
								</div>
								<div class="mrdw-push-preview-body">
									<div class="mrdw-push-preview-text">
										<div id="mrdw-push-preview-title" class="mrdw-push-preview-title"><?php esc_html_e( 'Notification Title', 'mrdw' ); ?></div>
										<div id="mrdw-push-preview-body" class="mrdw-push-preview-body-text"><?php esc_html_e( 'Notification body text will appear here...', 'mrdw' ); ?></div>
									</div>
									<div id="mrdw-push-preview-image" class="mrdw-push-preview-image" style="display:none;"></div>
								</div>
							</div>
						</div>

						<!-- Android-style notification mockup -->
						<div id="mrdw-push-preview-android" class="mrdw-push-preview-variant" style="display:none;">
							<div class="mrdw-push-preview-card-android">
								<div class="mrdw-push-preview-android-accent"></div>
								<div class="mrdw-push-preview-android-content">
									<div class="mrdw-push-preview-android-header">
										<span class="mrdw-push-preview-android-icon">&#x1F43E;</span>
										<span class="mrdw-push-preview-android-app"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
										<span class="mrdw-push-preview-android-dot">&middot;</span>
										<span class="mrdw-push-preview-android-time"><?php esc_html_e( 'now', 'mrdw' ); ?></span>
									</div>
									<div id="mrdw-push-preview-title-android" class="mrdw-push-preview-android-title"><?php esc_html_e( 'Notification Title', 'mrdw' ); ?></div>
									<div id="mrdw-push-preview-body-android" class="mrdw-push-preview-android-body"><?php esc_html_e( 'Notification body text will appear here...', 'mrdw' ); ?></div>
									<div id="mrdw-push-preview-image-android" class="mrdw-push-preview-android-image" style="display:none;"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Scheduled Notifications -->
	<?php if ( ! empty( $scheduled ) ) : ?>
		<div class="mrdw-push-card tw-mt-6">
			<div class="mrdw-push-card-header">
				<h2><?php esc_html_e( 'Scheduled Notifications', 'mrdw' ); ?></h2>
			</div>
			<table class="tw-w-full">
				<thead>
					<tr>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Title', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Target', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Scheduled For', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Actions', 'mrdw' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scheduled as $item ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-font-medium"><?php echo esc_html( $item->title ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<span class="mrdw-push-badge mrdw-push-badge-gray"><?php echo esc_html( ucfirst( $item->target_type ) ); ?></span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->scheduled_at ) ) ); ?>
							</td>
							<td class="tw-px-5 tw-py-3.5">
								<button type="button" class="button button-small mrdw-push-cancel-scheduled" data-id="<?php echo esc_attr( $item->id ); ?>">
									<?php esc_html_e( 'Cancel', 'mrdw' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
