<?php
/**
 * Tests for TailSignal_i18n.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-tailsignal-i18n.php';

class Test_TailSignal_i18n extends TailSignal_TestCase {

	/**
	 * Test load_plugin_textdomain is called with correct domain.
	 */
	public function test_load_textdomain_correct_domain() {
		Functions\expect( 'load_plugin_textdomain' )
			->once()
			->with( 'mrdemonwolf', false, Mockery::type( 'string' ) );

		$i18n = new TailSignal_i18n();
		$i18n->load_plugin_textdomain();
		$this->assertTrue( true );
	}

	/**
	 * Test second argument is false.
	 */
	public function test_load_textdomain_second_arg_false() {
		$called_args = null;

		Functions\expect( 'load_plugin_textdomain' )
			->once()
			->andReturnUsing( function() use ( &$called_args ) {
				$called_args = func_get_args();
			} );

		$i18n = new TailSignal_i18n();
		$i18n->load_plugin_textdomain();

		$this->assertSame( 'mrdemonwolf', $called_args[0] );
		$this->assertFalse( $called_args[1] );
	}

	/**
	 * Test languages path contains plugin directory.
	 */
	public function test_load_textdomain_languages_path() {
		$called_args = null;

		Functions\expect( 'load_plugin_textdomain' )
			->once()
			->andReturnUsing( function() use ( &$called_args ) {
				$called_args = func_get_args();
			} );

		$i18n = new TailSignal_i18n();
		$i18n->load_plugin_textdomain();

		$this->assertStringContainsString( 'languages/', $called_args[2] );
	}
}
