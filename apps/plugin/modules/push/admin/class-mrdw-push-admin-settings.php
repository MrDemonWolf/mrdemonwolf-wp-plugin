<?php
/**
 * Settings admin page using WordPress Settings API.
 *
 * @package MrDemonWolf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRDW_Push_Admin_Settings {

	/**
	 * Register settings.
	 */
	public function register_settings() {
		// General section.
		add_settings_section(
			'mrdw_push_general',
			__( 'General', 'mrdw' ),
			array( $this, 'render_general_section' ),
			'mrdw-push-settings'
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_dev_mode',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '0',
			)
		);

		add_settings_field(
			'mrdw_push_dev_mode',
			__( 'Dev Mode', 'mrdw' ),
			array( $this, 'render_toggle_field' ),
			'mrdw-push-settings',
			'mrdw_push_general',
			array(
				'name'        => 'mrdw_push_dev_mode',
				'description' => __( 'When ON, notifications only go to devices flagged as "dev". Use this for testing.', 'mrdw' ),
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_auto_notify',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'mrdw_push_auto_notify',
			__( 'Auto-notify on new posts', 'mrdw' ),
			array( $this, 'render_toggle_field' ),
			'mrdw-push-settings',
			'mrdw_push_general',
			array(
				'name'        => 'mrdw_push_auto_notify',
				'description' => __( 'Automatically send a push notification when a post is published.', 'mrdw' ),
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_expo_access_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( 'MRDW_Secrets', 'sanitize_expo_access_token' ),
				'default'           => '',
			)
		);

		add_settings_field(
			'mrdw_push_expo_access_token',
			__( 'Expo Access Token', 'mrdw' ),
			array( $this, 'render_text_field' ),
			'mrdw-push-settings',
			'mrdw_push_general',
			array(
				'name'        => 'mrdw_push_expo_access_token',
				'description' => __( 'Optional. Get from expo.dev dashboard.', 'mrdw' ),
				'type'        => 'password',
				'locked'      => MRDW_Secrets::expo_token_is_constant(),
			)
		);

		// Notification Templates section.
		add_settings_section(
			'mrdw_push_templates',
			__( 'Notification Templates', 'mrdw' ),
			array( $this, 'render_templates_section' ),
			'mrdw-push-settings'
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_default_title',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'New from {site_name}',
			)
		);

		add_settings_field(
			'mrdw_push_default_title',
			__( 'Default Title', 'mrdw' ),
			array( $this, 'render_text_field' ),
			'mrdw-push-settings',
			'mrdw_push_templates',
			array(
				'name' => 'mrdw_push_default_title',
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_default_body',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '{post_title}',
			)
		);

		add_settings_field(
			'mrdw_push_default_body',
			__( 'Default Body', 'mrdw' ),
			array( $this, 'render_text_field' ),
			'mrdw-push-settings',
			'mrdw_push_templates',
			array(
				'name' => 'mrdw_push_default_body',
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_use_featured_image',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'mrdw_push_use_featured_image',
			__( 'Include Featured Image', 'mrdw' ),
			array( $this, 'render_toggle_field' ),
			'mrdw-push-settings',
			'mrdw_push_templates',
			array(
				'name'        => 'mrdw_push_use_featured_image',
				'description' => __( 'Sends post featured image as rich notification on iOS and Android.', 'mrdw' ),
			)
		);

		// Portfolio Templates section.
		add_settings_section(
			'mrdw_push_portfolio_templates',
			__( 'Portfolio Templates', 'mrdw' ),
			array( $this, 'render_portfolio_templates_section' ),
			'mrdw-push-settings'
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_portfolio_auto_notify',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'mrdw_push_portfolio_auto_notify',
			__( 'Auto-notify on new portfolio', 'mrdw' ),
			array( $this, 'render_toggle_field' ),
			'mrdw-push-settings',
			'mrdw_push_portfolio_templates',
			array(
				'name'        => 'mrdw_push_portfolio_auto_notify',
				'description' => __( 'Automatically send a push notification when a portfolio item is published.', 'mrdw' ),
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_portfolio_default_title',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'New Project: {post_title}',
			)
		);

		add_settings_field(
			'mrdw_push_portfolio_default_title',
			__( 'Default Title', 'mrdw' ),
			array( $this, 'render_text_field' ),
			'mrdw-push-settings',
			'mrdw_push_portfolio_templates',
			array(
				'name' => 'mrdw_push_portfolio_default_title',
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_portfolio_default_body',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '{post_title} by {author_name}',
			)
		);

		add_settings_field(
			'mrdw_push_portfolio_default_body',
			__( 'Default Body', 'mrdw' ),
			array( $this, 'render_text_field' ),
			'mrdw-push-settings',
			'mrdw_push_portfolio_templates',
			array(
				'name' => 'mrdw_push_portfolio_default_body',
			)
		);

		register_setting(
			'mrdw_push_settings',
			'mrdw_push_portfolio_use_featured_image',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'mrdw_push_portfolio_use_featured_image',
			__( 'Include Featured Image', 'mrdw' ),
			array( $this, 'render_toggle_field' ),
			'mrdw-push-settings',
			'mrdw_push_portfolio_templates',
			array(
				'name'        => 'mrdw_push_portfolio_use_featured_image',
				'description' => __( 'Sends portfolio featured image as rich notification on iOS and Android.', 'mrdw' ),
			)
		);
	}

	/**
	 * Render general section description.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure general MRDW_Push settings.', 'mrdw' ) . '</p>';
	}

	/**
	 * Render templates section description.
	 */
	public function render_templates_section() {
		echo '<p>' . esc_html__( 'These templates are used for auto-publish notifications. Each post can override them in the MRDW_Push meta box.', 'mrdw' ) . '</p>';
		echo '<p class="description">';
		echo esc_html__( 'Available placeholders:', 'mrdw' ) . ' ';
		echo '<code>{post_title}</code> <code>{post_excerpt}</code> <code>{site_name}</code> <code>{author_name}</code> <code>{category}</code>';
		echo '</p>';
	}

	/**
	 * Render portfolio templates section description.
	 */
	public function render_portfolio_templates_section() {
		echo '<p>' . esc_html__( 'These templates are used for auto-publish notifications on portfolio items.', 'mrdw' ) . '</p>';
		echo '<p class="description">';
		echo esc_html__( 'Available placeholders:', 'mrdw' ) . ' ';
		echo '<code>{post_title}</code> <code>{post_excerpt}</code> <code>{site_name}</code> <code>{author_name}</code> <code>{category}</code>';
		echo '</p>';
	}

	/**
	 * Render a text input field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$name   = $args['name'];
		$type   = $args['type'] ?? 'text';
		$locked = ! empty( $args['locked'] );
		$value  = get_option( $name );

		// A disabled input is never submitted, so a constant-backed secret
		// cannot be overwritten from this screen.
		printf(
			'<input type="%s" name="%s" value="%s" class="regular-text"%s />',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $locked ? '' : $value ),
			$locked ? ' disabled="disabled"' : ''
		);

		if ( $locked ) {
			printf(
				'<p class="description">%s</p>',
				esc_html(
					sprintf(
						/* translators: %s: name of the PHP constant supplying the value. */
						__( 'Set by the %s constant in wp-config.php. Remove the constant to manage this value here instead.', 'mrdw' ),
						MRDW_Secrets::EXPO_TOKEN_CONSTANT
					)
				)
			);
		}

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render a toggle switch field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_toggle_field( $args ) {
		$name    = $args['name'];
		$checked = '1' === get_option( $name );

		echo '<label class="mrdw-push-toggle">';
		printf(
			'<input type="checkbox" name="%s" value="1" %s />',
			esc_attr( $name ),
			checked( $checked, true, false )
		);
		echo '<span class="mrdw-push-toggle-slider"></span>';
		echo '</label>';

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Sanitize checkbox value.
	 *
	 * @param mixed $value The value.
	 * @return string '1' or '0'.
	 */
	public function sanitize_checkbox( $value ) {
		return $value ? '1' : '0';
	}
}
