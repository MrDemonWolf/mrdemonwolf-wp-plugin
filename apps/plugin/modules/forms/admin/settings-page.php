<?php
/**
 * Settings page template.
 *
 * @package MrDemonWolf
 * @copyright 2026 MrDemonWolf, Inc.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mrdw_forms_provider_available = MRDW_Forms_Activator::is_provider_available();
$mrdw_forms_provider           = MRDW_Forms_Provider_Factory::create();
?>
<div class="wrap mrdw-forms-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="mrdw-forms-header">
		<div class="mrdw-forms-header-icon">
			<span class="dashicons dashicons-rest-api"></span>
		</div>
		<div class="mrdw-forms-header-info">
			<h2><?php esc_html_e( 'MRDW_Forms', 'mrdw' ); ?></h2>
			<div class="mrdw-forms-header-meta">
				<span class="mrdw-forms-version-badge">v<?php echo esc_html( MRDW_VERSION ); ?></span>
				<?php if ( $mrdw_forms_provider_available ) : ?>
					<span class="mrdw-forms-status-pill active"><?php esc_html_e( 'Active', 'mrdw' ); ?></span>
					<span class="mrdw-forms-provider-badge"><?php echo esc_html( $mrdw_forms_provider->get_label() ); ?></span>
				<?php else : ?>
					<span class="mrdw-forms-status-pill inactive"><?php esc_html_e( 'Provider Missing', 'mrdw' ); ?></span>
				<?php endif; ?>
				<a href="https://github.com/MrDemonWolf/mrdemonwolf-wp-plugin" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View on GitHub', 'mrdw' ); ?> &#8599;
				</a>
			</div>
		</div>
	</div>

	<form action="options.php" method="post">
		<?php
		settings_fields( MRDW_Forms_Settings::PAGE_SLUG );
		do_settings_sections( MRDW_Forms_Settings::PAGE_SLUG );
		submit_button();
		?>
	</form>
</div>
