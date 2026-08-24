<?php
/**
 * Settings admin page using WordPress Settings API.
 *
 * @package TailSignal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TailSignal_Admin_Settings {

	/**
	 * Register settings.
	 */
	public function register_settings() {
		// General section.
		add_settings_section(
			'tailsignal_general',
			__( 'General', 'mrdemonwolf' ),
			array( $this, 'render_general_section' ),
			'tailsignal-settings'
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_dev_mode',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '0',
			)
		);

		add_settings_field(
			'tailsignal_dev_mode',
			__( 'Dev Mode', 'mrdemonwolf' ),
			array( $this, 'render_toggle_field' ),
			'tailsignal-settings',
			'tailsignal_general',
			array(
				'name'        => 'tailsignal_dev_mode',
				'description' => __( 'When ON, notifications only go to devices flagged as "dev". Use this for testing.', 'mrdemonwolf' ),
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_auto_notify',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'tailsignal_auto_notify',
			__( 'Auto-notify on new posts', 'mrdemonwolf' ),
			array( $this, 'render_toggle_field' ),
			'tailsignal-settings',
			'tailsignal_general',
			array(
				'name'        => 'tailsignal_auto_notify',
				'description' => __( 'Automatically send a push notification when a post is published.', 'mrdemonwolf' ),
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_expo_access_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( 'MRDW_Secrets', 'sanitize_expo_access_token' ),
				'default'           => '',
			)
		);

		add_settings_field(
			'tailsignal_expo_access_token',
			__( 'Expo Access Token', 'mrdemonwolf' ),
			array( $this, 'render_text_field' ),
			'tailsignal-settings',
			'tailsignal_general',
			array(
				'name'        => 'tailsignal_expo_access_token',
				'description' => __( 'Optional. Get from expo.dev dashboard.', 'mrdemonwolf' ),
				'type'        => 'password',
				'locked'      => MRDW_Secrets::expo_token_is_constant(),
			)
		);

		// Notification Templates section.
		add_settings_section(
			'tailsignal_templates',
			__( 'Notification Templates', 'mrdemonwolf' ),
			array( $this, 'render_templates_section' ),
			'tailsignal-settings'
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_default_title',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'New from {site_name}',
			)
		);

		add_settings_field(
			'tailsignal_default_title',
			__( 'Default Title', 'mrdemonwolf' ),
			array( $this, 'render_text_field' ),
			'tailsignal-settings',
			'tailsignal_templates',
			array(
				'name' => 'tailsignal_default_title',
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_default_body',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '{post_title}',
			)
		);

		add_settings_field(
			'tailsignal_default_body',
			__( 'Default Body', 'mrdemonwolf' ),
			array( $this, 'render_text_field' ),
			'tailsignal-settings',
			'tailsignal_templates',
			array(
				'name' => 'tailsignal_default_body',
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_use_featured_image',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'tailsignal_use_featured_image',
			__( 'Include Featured Image', 'mrdemonwolf' ),
			array( $this, 'render_toggle_field' ),
			'tailsignal-settings',
			'tailsignal_templates',
			array(
				'name'        => 'tailsignal_use_featured_image',
				'description' => __( 'Sends post featured image as rich notification on iOS and Android.', 'mrdemonwolf' ),
			)
		);

		// Portfolio Templates section.
		add_settings_section(
			'tailsignal_portfolio_templates',
			__( 'Portfolio Templates', 'mrdemonwolf' ),
			array( $this, 'render_portfolio_templates_section' ),
			'tailsignal-settings'
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_portfolio_auto_notify',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'tailsignal_portfolio_auto_notify',
			__( 'Auto-notify on new portfolio', 'mrdemonwolf' ),
			array( $this, 'render_toggle_field' ),
			'tailsignal-settings',
			'tailsignal_portfolio_templates',
			array(
				'name'        => 'tailsignal_portfolio_auto_notify',
				'description' => __( 'Automatically send a push notification when a portfolio item is published.', 'mrdemonwolf' ),
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_portfolio_default_title',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'New Project: {post_title}',
			)
		);

		add_settings_field(
			'tailsignal_portfolio_default_title',
			__( 'Default Title', 'mrdemonwolf' ),
			array( $this, 'render_text_field' ),
			'tailsignal-settings',
			'tailsignal_portfolio_templates',
			array(
				'name' => 'tailsignal_portfolio_default_title',
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_portfolio_default_body',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '{post_title} by {author_name}',
			)
		);

		add_settings_field(
			'tailsignal_portfolio_default_body',
			__( 'Default Body', 'mrdemonwolf' ),
			array( $this, 'render_text_field' ),
			'tailsignal-settings',
			'tailsignal_portfolio_templates',
			array(
				'name' => 'tailsignal_portfolio_default_body',
			)
		);

		register_setting(
			'tailsignal_settings',
			'tailsignal_portfolio_use_featured_image',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
				'default'           => '1',
			)
		);

		add_settings_field(
			'tailsignal_portfolio_use_featured_image',
			__( 'Include Featured Image', 'mrdemonwolf' ),
			array( $this, 'render_toggle_field' ),
			'tailsignal-settings',
			'tailsignal_portfolio_templates',
			array(
				'name'        => 'tailsignal_portfolio_use_featured_image',
				'description' => __( 'Sends portfolio featured image as rich notification on iOS and Android.', 'mrdemonwolf' ),
			)
		);
	}

	/**
	 * Render general section description.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure general TailSignal settings.', 'mrdemonwolf' ) . '</p>';
	}

	/**
	 * Render templates section description.
	 */
	public function render_templates_section() {
		echo '<p>' . esc_html__( 'These templates are used for auto-publish notifications. Each post can override them in the TailSignal meta box.', 'mrdemonwolf' ) . '</p>';
		echo '<p class="description">';
		echo esc_html__( 'Available placeholders:', 'mrdemonwolf' ) . ' ';
		echo '<code>{post_title}</code> <code>{post_excerpt}</code> <code>{site_name}</code> <code>{author_name}</code> <code>{category}</code>';
		echo '</p>';
	}

	/**
	 * Render portfolio templates section description.
	 */
	public function render_portfolio_templates_section() {
		echo '<p>' . esc_html__( 'These templates are used for auto-publish notifications on portfolio items.', 'mrdemonwolf' ) . '</p>';
		echo '<p class="description">';
		echo esc_html__( 'Available placeholders:', 'mrdemonwolf' ) . ' ';
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
						__( 'Set by the %s constant in wp-config.php. Remove the constant to manage this value here instead.', 'mrdemonwolf' ),
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

		echo '<label class="tailsignal-toggle">';
		printf(
			'<input type="checkbox" name="%s" value="1" %s />',
			esc_attr( $name ),
			checked( $checked, true, false )
		);
		echo '<span class="tailsignal-toggle-slider"></span>';
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
