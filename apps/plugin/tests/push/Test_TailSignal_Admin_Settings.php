<?php
/**
 * Tests for TailSignal_Admin_Settings.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-tailsignal-admin-settings.php';

class Test_TailSignal_Admin_Settings extends TailSignal_TestCase {

	/**
	 * @var TailSignal_Admin_Settings
	 */
	private $settings;

	protected function setUp(): void {
		parent::setUp();
		$this->settings = new TailSignal_Admin_Settings();
	}

	/**
	 * Test register_settings registers sections and fields.
	 */
	public function test_register_settings() {
		Functions\expect( 'add_settings_section' )->times( 3 ); // General + Templates + Portfolio Templates.
		Functions\expect( 'register_setting' )->times( 10 );  // 10 settings registered.
		Functions\expect( 'add_settings_field' )->times( 10 ); // 10 fields.

		$this->settings->register_settings();
		$this->assertTrue( true );
	}

	/**
	 * Test sanitize_checkbox returns '1' for truthy.
	 */
	public function test_sanitize_checkbox_truthy() {
		$this->assertSame( '1', $this->settings->sanitize_checkbox( '1' ) );
		$this->assertSame( '1', $this->settings->sanitize_checkbox( 'yes' ) );
		$this->assertSame( '1', $this->settings->sanitize_checkbox( true ) );
		$this->assertSame( '1', $this->settings->sanitize_checkbox( 1 ) );
	}

	/**
	 * Test sanitize_checkbox returns '0' for falsy.
	 */
	public function test_sanitize_checkbox_falsy() {
		$this->assertSame( '0', $this->settings->sanitize_checkbox( '' ) );
		$this->assertSame( '0', $this->settings->sanitize_checkbox( '0' ) );
		$this->assertSame( '0', $this->settings->sanitize_checkbox( false ) );
		$this->assertSame( '0', $this->settings->sanitize_checkbox( null ) );
		$this->assertSame( '0', $this->settings->sanitize_checkbox( 0 ) );
	}

	/**
	 * Test render_general_section outputs text.
	 */
	public function test_render_general_section() {
		ob_start();
		$this->settings->render_general_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Configure general TailSignal settings', $output );
	}

	/**
	 * Test render_templates_section outputs placeholder reference.
	 */
	public function test_render_templates_section() {
		ob_start();
		$this->settings->render_templates_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'auto-publish notifications', $output );
		$this->assertStringContainsString( '{post_title}', $output );
		$this->assertStringContainsString( '{site_name}', $output );
		$this->assertStringContainsString( '{author_name}', $output );
	}

	/**
	 * Test render_text_field outputs input element.
	 */
	public function test_render_text_field() {
		Functions\expect( 'get_option' )
			->with( 'tailsignal_default_title', '' )
			->andReturn( 'Hello {site_name}' );

		ob_start();
		$this->settings->render_text_field( array(
			'name' => 'tailsignal_default_title',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="text"', $output );
		$this->assertStringContainsString( 'name="tailsignal_default_title"', $output );
		$this->assertStringContainsString( 'Hello {site_name}', $output );
	}

	/**
	 * Test render_text_field with password type.
	 */
	public function test_render_text_field_password() {
		Functions\expect( 'get_option' )
			->with( 'tailsignal_expo_access_token', '' )
			->andReturn( 'secret-token' );

		ob_start();
		$this->settings->render_text_field( array(
			'name'        => 'tailsignal_expo_access_token',
			'type'        => 'password',
			'description' => 'Your token here.',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="password"', $output );
		$this->assertStringContainsString( 'Your token here.', $output );
	}

	/**
	 * Test render_toggle_field outputs checkbox.
	 */
	public function test_render_toggle_field_checked() {
		Functions\expect( 'get_option' )
			->with( 'tailsignal_dev_mode', '0' )
			->andReturn( '1' );

		Functions\expect( 'checked' )
			->with( true, true, false )
			->andReturn( 'checked="checked"' );

		ob_start();
		$this->settings->render_toggle_field( array(
			'name'        => 'tailsignal_dev_mode',
			'description' => 'Enable dev mode.',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="checkbox"', $output );
		$this->assertStringContainsString( 'tailsignal_dev_mode', $output );
		$this->assertStringContainsString( 'Enable dev mode.', $output );
	}

	/**
	 * Test render_toggle_field unchecked.
	 */
	public function test_render_toggle_field_unchecked() {
		Functions\expect( 'get_option' )
			->with( 'tailsignal_auto_notify', '0' )
			->andReturn( '0' );

		Functions\expect( 'checked' )
			->with( false, true, false )
			->andReturn( '' );

		ob_start();
		$this->settings->render_toggle_field( array(
			'name' => 'tailsignal_auto_notify',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="checkbox"', $output );
	}
}
