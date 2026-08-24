<?php
/**
 * Tests for TailSignal_Loader.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-tailsignal-loader.php';

class Test_TailSignal_Loader extends TailSignal_TestCase {

	/**
	 * @var TailSignal_Loader
	 */
	private $loader;

	protected function setUp(): void {
		parent::setUp();
		$this->loader = new TailSignal_Loader();
	}

	/**
	 * Test add_action stores hook data correctly.
	 */
	public function test_add_action_stores_hook() {
		$component = new stdClass();
		$this->loader->add_action( 'init', $component, 'my_callback', 20, 2 );

		// Verify by running — add_action should be called with the right args.
		Functions\expect( 'add_action' )
			->once()
			->with( 'init', array( $component, 'my_callback' ), 20, 2 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test add_filter stores filter data correctly.
	 */
	public function test_add_filter_stores_hook() {
		$component = new stdClass();
		$this->loader->add_filter( 'the_content', $component, 'filter_content', 15, 1 );

		Functions\expect( 'add_filter' )
			->once()
			->with( 'the_content', array( $component, 'filter_content' ), 15, 1 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test default priority is 10 and accepted_args is 1.
	 */
	public function test_add_action_default_priority_and_args() {
		$component = new stdClass();
		$this->loader->add_action( 'init', $component, 'callback' );

		Functions\expect( 'add_action' )
			->once()
			->with( 'init', array( $component, 'callback' ), 10, 1 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test add_filter default priority and accepted_args.
	 */
	public function test_add_filter_default_priority_and_args() {
		$component = new stdClass();
		$this->loader->add_filter( 'the_title', $component, 'filter_title' );

		Functions\expect( 'add_filter' )
			->once()
			->with( 'the_title', array( $component, 'filter_title' ), 10, 1 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test run with no hooks registered does nothing.
	 */
	public function test_run_with_no_hooks() {
		// No add_action/add_filter expectations — should not fail.
		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test multiple actions are all registered.
	 */
	public function test_multiple_actions_registered() {
		$component = new stdClass();
		$this->loader->add_action( 'init', $component, 'callback_a' );
		$this->loader->add_action( 'init', $component, 'callback_b' );
		$this->loader->add_action( 'wp_loaded', $component, 'callback_c' );

		Functions\expect( 'add_action' )->times( 3 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test multiple filters are all registered.
	 */
	public function test_multiple_filters_registered() {
		$component = new stdClass();
		$this->loader->add_filter( 'the_content', $component, 'filter_a' );
		$this->loader->add_filter( 'the_title', $component, 'filter_b' );

		Functions\expect( 'add_filter' )->times( 2 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test mixed actions and filters.
	 */
	public function test_mixed_actions_and_filters() {
		$component = new stdClass();
		$this->loader->add_action( 'init', $component, 'action_callback' );
		$this->loader->add_filter( 'the_content', $component, 'filter_callback' );

		Functions\expect( 'add_action' )->once();
		Functions\expect( 'add_filter' )->once();

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test filters are registered before actions (per source code order).
	 */
	public function test_filters_registered_before_actions() {
		$order     = array();
		$component = new stdClass();

		$this->loader->add_action( 'init', $component, 'action_cb' );
		$this->loader->add_filter( 'the_content', $component, 'filter_cb' );

		Functions\expect( 'add_filter' )->once()->andReturnUsing( function() use ( &$order ) {
			$order[] = 'filter';
		} );
		Functions\expect( 'add_action' )->once()->andReturnUsing( function() use ( &$order ) {
			$order[] = 'action';
		} );

		$this->loader->run();

		$this->assertSame( array( 'filter', 'action' ), $order );
	}

	/**
	 * Test same hook name with different components.
	 */
	public function test_same_hook_different_components() {
		$comp_a = new stdClass();
		$comp_b = new stdClass();

		$this->loader->add_action( 'init', $comp_a, 'callback' );
		$this->loader->add_action( 'init', $comp_b, 'callback' );

		Functions\expect( 'add_action' )->times( 2 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test custom priority is passed correctly.
	 */
	public function test_custom_priority() {
		$component = new stdClass();
		$this->loader->add_action( 'init', $component, 'callback', 99 );

		Functions\expect( 'add_action' )
			->once()
			->with( 'init', array( $component, 'callback' ), 99, 1 );

		$this->loader->run();
		$this->assertTrue( true );
	}

	/**
	 * Test custom accepted_args is passed correctly.
	 */
	public function test_custom_accepted_args() {
		$component = new stdClass();
		$this->loader->add_action( 'transition_post_status', $component, 'callback', 10, 3 );

		Functions\expect( 'add_action' )
			->once()
			->with( 'transition_post_status', array( $component, 'callback' ), 10, 3 );

		$this->loader->run();
		$this->assertTrue( true );
	}
}
