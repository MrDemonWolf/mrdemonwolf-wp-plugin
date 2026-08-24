<?php
/**
 * Notification History admin page template.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$table = new MRDW_Push_History_List_Table();
$table->prepare_items();
?>
<div id="mrdw-push-app" class="wrap">
	<!-- Page Header -->
	<div class="mrdw-push-page-header tw-flex tw-items-start tw-justify-between">
		<div>
			<h1>
				<span class="mrdw-push-page-header-icon"><span class="dashicons dashicons-backup"></span></span>
				<?php esc_html_e( 'Notification History', 'mrdw' ); ?>
			</h1>
			<p class="mrdw-push-page-desc"><?php esc_html_e( 'Browse all past and scheduled notification sends.', 'mrdw' ); ?></p>
		</div>
		<button type="button" id="mrdw-push-delete-all-history" class="button mrdw-push-btn-danger">
			<?php esc_html_e( 'Delete All History', 'mrdw' ); ?>
		</button>
	</div>

	<div class="mrdw-push-table-wrap">
		<form method="get">
			<input type="hidden" name="page" value="mrdw-push-history" />
			<?php $table->display(); ?>
		</form>
	</div>
</div>
