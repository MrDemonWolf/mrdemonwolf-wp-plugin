<?php
/**
 * Send Notification admin page template.
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
				<span class="tailsignal-page-header-icon"><span class="dashicons dashicons-megaphone"></span></span>
				<?php esc_html_e( 'Send Notification', 'mrdemonwolf' ); ?>
			</h1>
			<p class="tailsignal-page-desc"><?php esc_html_e( 'Compose and send push notifications to your audience.', 'mrdemonwolf' ); ?></p>
		</div>
		<?php if ( $dev_mode ) : ?>
			<span class="tailsignal-dev-pill"><?php esc_html_e( 'Dev Mode: ON', 'mrdemonwolf' ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( $dev_mode ) : ?>
		<div class="tailsignal-dev-banner tw-mb-6">
			<span class="tailsignal-dev-banner-icon">&#x26A0;&#xFE0F;</span>
			<p>
				<?php
				printf(
					/* translators: %d: dev device count */
					esc_html__( 'Dev Mode ON — "All devices" will only send to %d dev devices.', 'mrdemonwolf' ),
					(int) $dev_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Fill Test Data Banner -->
	<div class="tailsignal-test-banner tw-mb-6">
		<div class="tw-flex tw-items-center tw-gap-3">
			<span class="tw-text-lg">&#x1F9EA;</span>
			<p><?php esc_html_e( 'Testing? Pre-fill the form with sample data.', 'mrdemonwolf' ); ?></p>
		</div>
		<button type="button" id="tailsignal-fill-test" class="button">
			<?php esc_html_e( 'Fill Test Data', 'mrdemonwolf' ); ?>
		</button>
	</div>

	<div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
		<!-- Send Form (2/3 width) -->
		<div class="lg:tw-col-span-2">
			<form id="tailsignal-send-form">
				<!-- Content Section -->
				<div class="tailsignal-card tw-mb-6">
					<div class="tailsignal-card-body">
						<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Content', 'mrdemonwolf' ); ?></h3>
						<div class="tw-space-y-4">
							<!-- Title -->
							<div>
								<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-title">
									<?php esc_html_e( 'Title', 'mrdemonwolf' ); ?>
								</label>
								<input type="text" id="tailsignal-title" name="title" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" required />
								<div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
									<div class="tailsignal-placeholder-btns">
										<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-title" data-value="{site_name}">{site_name}</button>
										<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-title" data-value="{post_title}">{post_title}</button>
									</div>
									<span class="tw-text-xs tailsignal-char-count" data-target="tailsignal-title" data-limit="65">0 / 65</span>
								</div>
							</div>

							<!-- Body -->
							<div>
								<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-body">
									<?php esc_html_e( 'Body', 'mrdemonwolf' ); ?>
								</label>
								<textarea id="tailsignal-body" name="body" rows="3" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" required></textarea>
								<div class="tw-flex tw-items-center tw-justify-between tw-mt-1">
									<div class="tailsignal-placeholder-btns">
										<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{post_title}">{post_title}</button>
										<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{post_excerpt}">{post_excerpt}</button>
										<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{author_name}">{author_name}</button>
										<button type="button" class="tailsignal-placeholder-btn" data-target="tailsignal-body" data-value="{category}">{category}</button>
									</div>
									<span class="tw-text-xs tailsignal-char-count" data-target="tailsignal-body" data-limit="178">0 / 178</span>
								</div>
							</div>

							<!-- Image URL -->
							<div>
								<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-image-url">
									<?php esc_html_e( 'Image URL (optional)', 'mrdemonwolf' ); ?>
								</label>
								<div class="tw-flex tw-gap-2">
									<input type="url" id="tailsignal-image-url" name="image_url" class="tw-flex-1 tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" placeholder="https://example.com/image.jpg" />
									<button type="button" id="tailsignal-choose-image" class="button">
										<span class="dashicons dashicons-format-image" style="line-height: 1.4;"></span>
										<?php esc_html_e( 'Choose Image', 'mrdemonwolf' ); ?>
									</button>
								</div>
								<p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-m-0"><?php esc_html_e( 'Shows as rich notification on iOS/Android.', 'mrdemonwolf' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<!-- Targeting Section -->
				<div class="tailsignal-card tw-mb-6">
					<div class="tailsignal-card-body">
						<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Targeting', 'mrdemonwolf' ); ?></h3>
						<div class="tw-space-y-2">
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="all" checked class="tailsignal-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'All devices', 'mrdemonwolf' ); ?></span>
							</label>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="dev" class="tailsignal-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Dev devices only', 'mrdemonwolf' ); ?></span>
							</label>
							<?php if ( ! empty( $groups ) ) : ?>
								<label class="tw-flex tw-items-center tw-gap-2">
									<input type="radio" name="target_type" value="group" class="tailsignal-target-radio" />
									<span class="tw-text-sm"><?php esc_html_e( 'Group:', 'mrdemonwolf' ); ?></span>
									<select name="group_id" id="tailsignal-group-select" class="tw-rounded-md tw-border tw-border-gray-300 tw-px-2 tw-py-1 tw-text-sm" disabled>
										<?php foreach ( $groups as $group ) : ?>
											<option value="<?php echo esc_attr( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
							<?php endif; ?>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="target_type" value="specific" class="tailsignal-target-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Specific devices...', 'mrdemonwolf' ); ?></span>
							</label>
						</div>

						<!-- Specific Devices Picker -->
						<div id="tailsignal-specific-devices" class="tw-mt-3 tw-border tw-border-gray-200 tw-rounded-md tw-p-3 tw-max-h-48 tw-overflow-y-auto" style="display:none;">
							<?php if ( ! empty( $devices['items'] ) ) : ?>
								<?php foreach ( $devices['items'] as $device ) : ?>
									<label class="tw-flex tw-items-center tw-gap-2 tw-py-1">
										<input type="checkbox" name="target_ids[]" value="<?php echo esc_attr( $device->id ); ?>" class="tailsignal-device-checkbox" />
										<span class="tw-text-sm">
											<?php
											echo esc_html(
												! empty( $device->user_label )
													? $device->user_label
													: substr( $device->expo_token, 0, 25 ) . '...'
											);
											?>
											<?php if ( $device->is_dev ) : ?>
												<span class="tailsignal-badge tailsignal-badge-yellow tw-ml-1">DEV</span>
											<?php endif; ?>
										</span>
									</label>
								<?php endforeach; ?>
							<?php else : ?>
								<p class="tw-text-sm tw-text-gray-500 tw-m-0"><?php esc_html_e( 'No devices registered.', 'mrdemonwolf' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Scheduling Section -->
				<div class="tailsignal-card tw-mb-6">
					<div class="tailsignal-card-body">
						<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Scheduling', 'mrdemonwolf' ); ?></h3>
						<div class="tw-flex tw-items-center tw-gap-4">
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="send_when" value="now" checked class="tailsignal-when-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Now', 'mrdemonwolf' ); ?></span>
							</label>
							<label class="tw-flex tw-items-center tw-gap-2">
								<input type="radio" name="send_when" value="schedule" class="tailsignal-when-radio" />
								<span class="tw-text-sm"><?php esc_html_e( 'Schedule:', 'mrdemonwolf' ); ?></span>
							</label>
							<input type="datetime-local" name="scheduled_at" id="tailsignal-schedule-datetime" class="tw-rounded-md tw-border tw-border-gray-300 tw-px-2 tw-py-1 tw-text-sm" disabled />
						</div>
					</div>
				</div>

				<!-- Advanced Section -->
				<div class="tailsignal-card tw-mb-6">
					<div class="tailsignal-card-body">
						<h3 class="tw-m-0 tw-mb-4 tailsignal-section-header"><?php esc_html_e( 'Advanced', 'mrdemonwolf' ); ?></h3>
						<div>
							<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-data">
								<?php esc_html_e( 'Custom Data (JSON, optional)', 'mrdemonwolf' ); ?>
							</label>
							<textarea id="tailsignal-data" name="data" rows="3" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm tw-font-mono" placeholder='{ "screen": "article", "articleId": "123" }'></textarea>
							<p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-m-0">
								<?php esc_html_e( 'JSON payload sent to your app. For auto-published posts, TailSignal automatically includes post_id and post_type. Common keys for manual sends: post_id for deep linking, url for web links, badgeCount for badge updates.', 'mrdemonwolf' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Submit -->
				<div class="tw-flex tw-items-center tw-gap-3">
					<button type="submit" id="tailsignal-send-btn" class="button tailsignal-btn-brand">
						<?php esc_html_e( 'Signal the Pack', 'mrdemonwolf' ); ?>
					</button>
					<span id="tailsignal-send-status" class="tw-text-sm" aria-live="polite"></span>
				</div>
			</form>
		</div>

		<!-- Live Preview (1/3 width) -->
		<div class="lg:tw-col-span-1">
			<div class="tw-sticky tw-top-8">
				<div class="tailsignal-card">
					<div class="tailsignal-card-body">
						<div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
							<h3 class="tw-m-0 tailsignal-section-header"><?php esc_html_e( 'Preview', 'mrdemonwolf' ); ?></h3>
							<div class="tailsignal-preview-toggle">
								<button type="button" class="tailsignal-preview-toggle-btn active" data-preview="ios">iOS</button>
								<button type="button" class="tailsignal-preview-toggle-btn" data-preview="android">Android</button>
							</div>
						</div>

						<!-- iOS-style notification mockup -->
						<div id="tailsignal-preview-ios" class="tailsignal-preview-variant">
							<div class="tailsignal-preview-card">
								<div class="tailsignal-preview-header">
									<span class="tailsignal-preview-icon">&#x1F43E;</span>
									<span class="tailsignal-preview-app"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
									<span class="tailsignal-preview-time"><?php esc_html_e( 'now', 'mrdemonwolf' ); ?></span>
								</div>
								<div class="tailsignal-preview-body">
									<div class="tailsignal-preview-text">
										<div id="tailsignal-preview-title" class="tailsignal-preview-title"><?php esc_html_e( 'Notification Title', 'mrdemonwolf' ); ?></div>
										<div id="tailsignal-preview-body" class="tailsignal-preview-body-text"><?php esc_html_e( 'Notification body text will appear here...', 'mrdemonwolf' ); ?></div>
									</div>
									<div id="tailsignal-preview-image" class="tailsignal-preview-image" style="display:none;"></div>
								</div>
							</div>
						</div>

						<!-- Android-style notification mockup -->
						<div id="tailsignal-preview-android" class="tailsignal-preview-variant" style="display:none;">
							<div class="tailsignal-preview-card-android">
								<div class="tailsignal-preview-android-accent"></div>
								<div class="tailsignal-preview-android-content">
									<div class="tailsignal-preview-android-header">
										<span class="tailsignal-preview-android-icon">&#x1F43E;</span>
										<span class="tailsignal-preview-android-app"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
										<span class="tailsignal-preview-android-dot">&middot;</span>
										<span class="tailsignal-preview-android-time"><?php esc_html_e( 'now', 'mrdemonwolf' ); ?></span>
									</div>
									<div id="tailsignal-preview-title-android" class="tailsignal-preview-android-title"><?php esc_html_e( 'Notification Title', 'mrdemonwolf' ); ?></div>
									<div id="tailsignal-preview-body-android" class="tailsignal-preview-android-body"><?php esc_html_e( 'Notification body text will appear here...', 'mrdemonwolf' ); ?></div>
									<div id="tailsignal-preview-image-android" class="tailsignal-preview-android-image" style="display:none;"></div>
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
		<div class="tailsignal-card tw-mt-6">
			<div class="tailsignal-card-header">
				<h2><?php esc_html_e( 'Scheduled Notifications', 'mrdemonwolf' ); ?></h2>
			</div>
			<table class="tw-w-full">
				<thead>
					<tr>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Title', 'mrdemonwolf' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Target', 'mrdemonwolf' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Scheduled For', 'mrdemonwolf' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Actions', 'mrdemonwolf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scheduled as $item ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-font-medium"><?php echo esc_html( $item->title ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<span class="tailsignal-badge tailsignal-badge-gray"><?php echo esc_html( ucfirst( $item->target_type ) ); ?></span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->scheduled_at ) ) ); ?>
							</td>
							<td class="tw-px-5 tw-py-3.5">
								<button type="button" class="button button-small tailsignal-cancel-scheduled" data-id="<?php echo esc_attr( $item->id ); ?>">
									<?php esc_html_e( 'Cancel', 'mrdemonwolf' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
