<?php
/**
 * Tests for the merge glue: module gate, secret resolution, conflict guard.
 *
 * These cover the behaviour that is new in the merged plugin and therefore not
 * exercised by either module's inherited suite.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

class Test_MRDW_Glue extends MRDW_Push_TestCase {

	protected function setUp(): void {
		parent::setUp();
		MRDW_Modules::clear_cache();
	}

	protected function tearDown(): void {
		MRDW_Modules::clear_cache();
		parent::tearDown();
	}

	// ── Module gate ─────────────────────────────────────────────

	/**
	 * A site that has never saved settings gets both modules.
	 */
	public function test_modules_default_to_enabled() {
		Functions\when( 'get_option' )->justReturn( array() );

		$this->assertTrue( MRDW_Modules::is_enabled( 'forms' ) );
		$this->assertTrue( MRDW_Modules::is_enabled( 'push' ) );
	}

	/**
	 * An explicitly disabled module reports as off, without affecting the other.
	 */
	public function test_disabled_module_reports_off() {
		Functions\when( 'get_option' )->justReturn(
			array(
				'forms' => true,
				'push'  => false,
			)
		);

		$this->assertTrue( MRDW_Modules::is_enabled( 'forms' ) );
		$this->assertFalse( MRDW_Modules::is_enabled( 'push' ) );
	}

	/**
	 * An unknown module slug is never enabled.
	 */
	public function test_unknown_module_is_not_enabled() {
		Functions\when( 'get_option' )->justReturn( array() );

		$this->assertFalse( MRDW_Modules::is_enabled( 'nope' ) );
	}

	/**
	 * A corrupt option value falls back to the defaults instead of fatalling.
	 */
	public function test_non_array_option_falls_back_to_defaults() {
		Functions\when( 'get_option' )->justReturn( 'corrupted' );

		$this->assertTrue( MRDW_Modules::is_enabled( 'forms' ) );
		$this->assertTrue( MRDW_Modules::is_enabled( 'push' ) );
	}

	/**
	 * Unchecked checkboxes are absent from the payload and must mean "off".
	 */
	public function test_sanitize_treats_missing_keys_as_disabled() {
		$clean = MRDW_Modules::sanitize( array( 'forms' => '1' ) );

		$this->assertSame(
			array(
				'forms' => true,
				'push'  => false,
			),
			$clean
		);
	}

	/**
	 * Sanitize always returns a full, boolean-typed map.
	 */
	public function test_sanitize_normalises_garbage_input() {
		$clean = MRDW_Modules::sanitize( 'not an array' );

		$this->assertSame(
			array(
				'forms' => false,
				'push'  => false,
			),
			$clean
		);
	}

	// ── Secret resolution ───────────────────────────────────────

	/**
	 * With no constant defined, the legacy option is used.
	 */
	public function test_expo_token_falls_back_to_option() {
		Functions\when( 'get_option' )->justReturn( '  option-token  ' );

		$this->assertFalse( MRDW_Secrets::expo_token_is_constant() );
		$this->assertSame( 'option-token', MRDW_Secrets::expo_access_token() );
	}

	/**
	 * Sanitizing the token stores a cleaned value when no constant is set.
	 */
	public function test_expo_token_sanitize_stores_value_without_constant() {
		Functions\when( 'get_option' )->justReturn( '' );

		// The base test case already stubs sanitize_text_field as a passthrough.
		$this->assertSame( 'fresh-token', MRDW_Secrets::sanitize_expo_access_token( 'fresh-token' ) );
	}

	// ── Conflict guard ──────────────────────────────────────────

	/**
	 * A clean site reports no conflict.
	 */
	public function test_no_conflict_when_predecessors_inactive() {
		Functions\when( 'get_option' )->justReturn( array( 'akismet/akismet.php' ) );
		Functions\when( 'is_multisite' )->justReturn( false );

		$this->assertSame( array(), MRDW_Conflict::active_legacy_plugins() );
		$this->assertFalse( MRDW_Conflict::has_conflict() );
	}

	/**
	 * An active predecessor is detected by name so the notice can name it.
	 */
	public function test_active_predecessor_is_detected() {
		Functions\when( 'get_option' )->justReturn(
			array( 'akismet/akismet.php', 'tailsignal/tailsignal.php' )
		);
		Functions\when( 'is_multisite' )->justReturn( false );

		$this->assertSame( array( 'TailSignal' ), MRDW_Conflict::active_legacy_plugins() );
		$this->assertTrue( MRDW_Conflict::has_conflict() );
	}

	/**
	 * Both predecessors active are both reported.
	 */
	public function test_both_predecessors_detected() {
		Functions\when( 'get_option' )->justReturn(
			array( 'packrelay/packrelay.php', 'tailsignal/tailsignal.php' )
		);
		Functions\when( 'is_multisite' )->justReturn( false );

		$this->assertSame(
			array( 'PackRelay', 'TailSignal' ),
			MRDW_Conflict::active_legacy_plugins()
		);
	}

	// ── Update channel ──────────────────────────────────────────

	/**
	 * Without the opt-in constant, a site tracks stable releases.
	 */
	public function test_update_channel_defaults_to_stable() {
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$this->assertSame( MRDW_Updater::CHANNEL_STABLE, MRDW_Updater::channel() );
		$this->assertFalse( MRDW_Updater::is_nightly() );
	}

	/**
	 * A filter can move a site onto nightly, and anything unrecognised is
	 * treated as stable rather than silently enabling pre-releases.
	 */
	public function test_update_channel_filter_only_accepts_known_values() {
		Functions\when( 'apply_filters' )->justReturn( 'nightly' );
		$this->assertSame( MRDW_Updater::CHANNEL_NIGHTLY, MRDW_Updater::channel() );

		Functions\when( 'apply_filters' )->justReturn( 'banana' );
		$this->assertSame( MRDW_Updater::CHANNEL_STABLE, MRDW_Updater::channel() );
	}
}
