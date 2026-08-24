<?php
/**
 * Groups admin page template.
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
				<span class="tailsignal-page-header-icon"><span class="dashicons dashicons-groups"></span></span>
				<?php esc_html_e( 'Device Groups', 'mrdemonwolf' ); ?>
			</h1>
			<p class="tailsignal-page-desc"><?php esc_html_e( 'Organize devices into groups for targeted notifications.', 'mrdemonwolf' ); ?></p>
		</div>
		<button type="button" class="button tailsignal-btn-brand" id="tailsignal-create-group-btn">
			<?php esc_html_e( '+ Create Group', 'mrdemonwolf' ); ?>
		</button>
	</div>

	<!-- Groups Table -->
	<?php if ( ! empty( $groups ) ) : ?>
		<div class="tailsignal-card tw-mb-6">
			<table class="tw-w-full">
				<thead>
					<tr>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Name', 'mrdemonwolf' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Devices', 'mrdemonwolf' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Description', 'mrdemonwolf' ); ?></th>
						<th scope="col" class="tw-px-5 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider"><?php esc_html_e( 'Actions', 'mrdemonwolf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $groups as $group ) : ?>
						<tr class="tw-border-b tw-border-gray-100">
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-font-medium tw-text-gray-900"><?php echo esc_html( $group->name ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<span class="tailsignal-badge tailsignal-badge-brand"><?php echo esc_html( $group->device_count ); ?></span>
							</td>
							<td class="tw-px-5 tw-py-3.5 tw-text-sm tw-text-gray-500"><?php echo esc_html( $group->description ); ?></td>
							<td class="tw-px-5 tw-py-3.5">
								<div class="tw-flex tw-gap-2">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=tailsignal-groups&edit=' . $group->id ), 'tailsignal_edit_group' ) ); ?>" class="button button-small">
										<?php esc_html_e( 'Edit', 'mrdemonwolf' ); ?>
									</a>
									<button type="button" class="button button-small tailsignal-btn-danger tailsignal-delete-group" data-id="<?php echo esc_attr( $group->id ); ?>">
										<?php esc_html_e( 'Delete', 'mrdemonwolf' ); ?>
									</button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="tailsignal-card tw-mb-6">
			<div class="tailsignal-empty-state">
				<div class="tailsignal-empty-state-icon">&#x1F465;</div>
				<p><?php esc_html_e( 'No groups created yet. Create a group to organize your devices.', 'mrdemonwolf' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Create/Edit Group Form -->
	<div id="tailsignal-group-form" class="tailsignal-card" <?php echo ( ! $editing_group && ! isset( $_GET['new'] ) ) ? 'style="display:none;"' : ''; /* No user input rendered here */ ?>>
		<div class="tailsignal-card-body">
			<h2 class="tw-text-base tw-font-bold tw-mb-4 tw-m-0">
				<?php echo $editing_group ? esc_html__( 'Edit Group', 'mrdemonwolf' ) : esc_html__( 'Create Group', 'mrdemonwolf' ); ?>
			</h2>
			<form id="tailsignal-group-save-form">
				<input type="hidden" name="group_id" value="<?php echo $editing_group ? esc_attr( $editing_group->id ) : ''; ?>" />

				<div class="tw-space-y-4">
					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-group-name">
							<?php esc_html_e( 'Name', 'mrdemonwolf' ); ?>
						</label>
						<input type="text" id="tailsignal-group-name" name="name" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" value="<?php echo $editing_group ? esc_attr( $editing_group->name ) : ''; ?>" required />
					</div>

					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1" for="tailsignal-group-description">
							<?php esc_html_e( 'Description', 'mrdemonwolf' ); ?>
						</label>
						<textarea id="tailsignal-group-description" name="description" rows="2" class="tw-w-full tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm"><?php echo $editing_group ? esc_textarea( $editing_group->description ) : ''; ?></textarea>
					</div>

					<div>
						<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
							<?php esc_html_e( 'Assign Devices', 'mrdemonwolf' ); ?>
						</label>
						<?php if ( ! empty( $devices['items'] ) ) : ?>
							<div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
								<input type="text" id="tailsignal-group-device-search" class="tw-flex-1 tw-rounded-md tw-border tw-border-gray-300 tw-px-3 tw-py-2 tw-text-sm" placeholder="<?php esc_attr_e( 'Search by name or token...', 'mrdemonwolf' ); ?>" />
								<span id="tailsignal-device-selected-count" class="tw-text-xs tw-text-gray-400 tw-whitespace-nowrap tw-tabular-nums">
									<?php
									$checked_count = count( $editing_devices );
									$total_count   = count( $devices['items'] );
									printf(
										/* translators: %1$d: selected count, %2$d: total count */
										esc_html__( '%1$d / %2$d selected', 'mrdemonwolf' ),
										(int) $checked_count,
										(int) $total_count
									);
									?>
								</span>
							</div>
							<div class="tw-flex tw-gap-2 tw-mb-2">
								<button type="button" id="tailsignal-select-all-devices" class="tw-text-xs tw-cursor-pointer tw-bg-transparent tw-border-0 tw-p-0 hover:tw-underline" style="color: var(--ts-brand);"><?php esc_html_e( 'Select all', 'mrdemonwolf' ); ?></button>
								<span class="tw-text-gray-300 tw-text-xs">|</span>
								<button type="button" id="tailsignal-deselect-all-devices" class="tw-text-xs tw-cursor-pointer tw-bg-transparent tw-border-0 tw-p-0 hover:tw-underline" style="color: var(--ts-brand);"><?php esc_html_e( 'Deselect all', 'mrdemonwolf' ); ?></button>
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
									<label class="tw-flex tw-items-center tw-gap-3 tw-px-3 tw-py-2.5 tw-border-b tw-border-gray-100 last:tw-border-b-0 tw-cursor-pointer tailsignal-device-option hover:tw-bg-gray-50" data-label="<?php echo esc_attr( strtolower( $device->user_label . ' ' . $device->expo_token . ' ' . $device->device_type . ' ' . $device->device_model ) ); ?>">
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
											<span class="tailsignal-badge tailsignal-badge-yellow tw-text-[10px]"><?php esc_html_e( 'DEV', 'mrdemonwolf' ); ?></span>
										<?php endif; ?>
									</label>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<div class="tw-border tw-border-gray-200 tw-rounded-md tw-p-4">
								<p class="tw-text-sm tw-text-gray-500 tw-m-0 tw-text-center"><?php esc_html_e( 'No active devices registered.', 'mrdemonwolf' ); ?></p>
							</div>
						<?php endif; ?>
					</div>

					<div class="tw-flex tw-items-center tw-gap-3">
						<button type="submit" class="button tailsignal-btn-brand"><?php esc_html_e( 'Save Group', 'mrdemonwolf' ); ?></button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=tailsignal-groups' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'mrdemonwolf' ); ?></a>
						<span id="tailsignal-group-status" class="tw-text-sm" aria-live="polite"></span>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
