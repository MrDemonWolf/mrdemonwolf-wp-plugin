<?php
/**
 * Groups admin page template.
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
				<span class="mrdw-push-page-header-icon"><span class="dashicons dashicons-groups"></span></span>
				<?php esc_html_e( 'Device Groups', 'mrdw' ); ?>
			</h1>
			<p class="mrdw-push-page-desc"><?php esc_html_e( 'Organize devices into groups for targeted notifications.', 'mrdw' ); ?></p>
		</div>
		<button type="button" class="button mrdw-push-btn-brand" id="mrdw-push-create-group-btn">
			<?php esc_html_e( '+ Create Group', 'mrdw' ); ?>
		</button>
	</div>

	<!-- Groups Table -->
	<?php if ( ! empty( $groups ) ) : ?>
		<div class="mrdw-push-card tw-mb-6">
			<table class="tw-w-full">
				<thead>
					<tr>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Name', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Devices', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Description', 'mrdw' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Actions', 'mrdw' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $groups as $group ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-font-medium tw-text-gray-900"><?php echo esc_html( $group->name ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<span class="mrdw-push-badge mrdw-push-badge-brand"><?php echo esc_html( $group->device_count ); ?></span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500"><?php echo esc_html( $group->description ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<div class="tw-flex tw-gap-2">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=mrdw-push-groups&edit=' . $group->id ), 'mrdw_push_edit_group' ) ); ?>" class="button button-small">
										<?php esc_html_e( 'Edit', 'mrdw' ); ?>
									</a>
									<button type="button" class="button button-small mrdw-push-btn-danger mrdw-push-delete-group" data-id="<?php echo esc_attr( $group->id ); ?>">
										<?php esc_html_e( 'Delete', 'mrdw' ); ?>
									</button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="mrdw-push-card tw-mb-6">
			<div class="mrdw-push-empty-state">
				<div class="mrdw-push-empty-state-icon">&#x1F465;</div>
				<p><?php esc_html_e( 'No groups created yet. Create a group to organize your devices.', 'mrdw' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Create/Edit Group Form -->
	<div id="mrdw-push-group-form" class="mrdw-push-card" <?php echo ( ! $editing_group && ! isset( $_GET['new'] ) ) ? 'style="display:none;"' : ''; /* No user input rendered here */ ?>>
		<div class="mrdw-push-card-body">
			<h2 class="tw-text-base tw-font-bold tw-mb-4 tw-m-0">
				<?php echo $editing_group ? esc_html__( 'Edit Group', 'mrdw' ) : esc_html__( 'Create Group', 'mrdw' ); ?>
			</h2>
			<form id="mrdw-push-group-save-form">
				<input type="hidden" name="group_id" value="<?php echo $editing_group ? esc_attr( $editing_group->id ) : ''; ?>" />

				<div class="tw-space-y-4">
					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="mrdw-push-group-name">
							<?php esc_html_e( 'Name', 'mrdw' ); ?>
						</label>
						<input type="text" id="mrdw-push-group-name" name="name" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" value="<?php echo $editing_group ? esc_attr( $editing_group->name ) : ''; ?>" required />
					</div>

					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="mrdw-push-group-description">
							<?php esc_html_e( 'Description', 'mrdw' ); ?>
						</label>
						<textarea id="mrdw-push-group-description" name="description" rows="2" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm"><?php echo $editing_group ? esc_textarea( $editing_group->description ) : ''; ?></textarea>
					</div>

					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
							<?php esc_html_e( 'Assign Devices', 'mrdw' ); ?>
						</label>
						<?php if ( ! empty( $devices['items'] ) ) : ?>
							<div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
								<input type="text" id="mrdw-push-group-device-search" class="tw-flex-1 tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" placeholder="<?php esc_attr_e( 'Search by name or token...', 'mrdw' ); ?>" />
								<span id="mrdw-push-device-selected-count" class="tw-text-xs tw-text-gray-400 tw-whitespace-nowrap tw-tabular-nums">
									<?php
									$checked_count = count( $editing_devices );
									$total_count   = count( $devices['items'] );
									printf(
										/* translators: %1$d: selected count, %2$d: total count */
										esc_html__( '%1$d / %2$d selected', 'mrdw' ),
										(int) $checked_count,
										(int) $total_count
									);
									?>
								</span>
							</div>
							<div class="tw-flex tw-gap-2 tw-mb-2">
								<button type="button" id="mrdw-push-select-all-devices" class="tw-text-xs tw-cursor-pointer tw-bg-transparent tw-border-0 tw-p-0 hover:tw-underline" style="color: var(--ts-brand);"><?php esc_html_e( 'Select all', 'mrdw' ); ?></button>
								<span class="tw-text-gray-300 tw-text-xs">|</span>
								<button type="button" id="mrdw-push-deselect-all-devices" class="tw-text-xs tw-cursor-pointer tw-bg-transparent tw-border-0 tw-p-0 hover:tw-underline" style="color: var(--ts-brand);"><?php esc_html_e( 'Deselect all', 'mrdw' ); ?></button>
							</div>
							<div class="tw-border tw-border-gray-200 tw-rounded-md tw-max-h-64 tw-overflow-y-auto">
								<?php
								foreach ( $devices['items'] as $device ) :
									$is_checked = in_array( (string) $device->id, $editing_devices, true ) || in_array( (int) $device->id, $editing_devices, true );
									$label      = ! empty( $device->user_label ) ? $device->user_label : substr( $device->expo_token, 0, 25 ) . '...';
									$platform   = '';
									if ( ! empty( $device->device_type ) ) {
										$platform = 'ios' === strtolower( $device->device_type ) ? 'iOS' : ucfirst( strtolower( $device->device_type ) );
									}
									$model = ! empty( $device->device_model ) ? $device->device_model : '';
									?>
									<label class="tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2.5 tw-border-b tw-border-gray-100 last:tw-border-b-0 tw-cursor-pointer mrdw-push-device-option hover:tw-bg-gray-50" data-label="<?php echo esc_attr( strtolower( $device->user_label . ' ' . $device->expo_token . ' ' . $device->device_type . ' ' . $device->device_model ) ); ?>">
										<input type="checkbox" name="device_ids[]" value="<?php echo esc_attr( $device->id ); ?>" class="tw-rounded"
											<?php checked( $is_checked ); ?> />
										<div class="tw-flex-1 tw-min-w-0">
											<div class="tw-text-sm tw-font-medium tw-text-gray-800 tw-truncate"><?php echo esc_html( $label ); ?></div>
											<div class="tw-text-xs tw-text-gray-400 tw-flex tw-items-center tw-gap-2">
												<?php if ( $platform ) : ?>
													<span><?php echo esc_html( $platform ); ?></span>
												<?php endif; ?>
												<?php if ( $platform && $model ) : ?>
													<span>&middot;</span>
												<?php endif; ?>
												<?php if ( $model ) : ?>
													<span><?php echo esc_html( $model ); ?></span>
												<?php endif; ?>
											</div>
										</div>
										<?php if ( $device->is_dev ) : ?>
											<span class="mrdw-push-badge mrdw-push-badge-yellow tw-text-[10px]"><?php esc_html_e( 'DEV', 'mrdw' ); ?></span>
										<?php endif; ?>
									</label>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<div class="tw-border tw-border-gray-200 tw-rounded-md tw-p-4">
								<p class="tw-text-sm tw-text-gray-500 tw-m-0 tw-text-center"><?php esc_html_e( 'No active devices registered.', 'mrdw' ); ?></p>
							</div>
						<?php endif; ?>
					</div>

					<div class="tw-flex tw-items-center tw-gap-3">
						<button type="submit" class="button mrdw-push-btn-brand"><?php esc_html_e( 'Save Group', 'mrdw' ); ?></button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mrdw-push-groups' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'mrdw' ); ?></a>
						<span id="mrdw-push-group-status" class="tw-text-sm" aria-live="polite"></span>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
