<?php
/**
 * Tests for MRDW_Forms core class.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

namespace MRDW_Forms\Tests;

use Brain\Monkey\Functions;

class CoreTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		// Reset the singleton.
		$reflection = new \ReflectionClass( \MRDW_Forms::class );
		$instance   = $reflection->getProperty( 'instance' );
		$instance->setValue( null, null );

		// Stub functions used during construction.
		Functions\stubs(
			array(
				'get_option' => function ( $key, $default = false ) {
					if ( 'mrdw_forms_settings' === $key ) {
						return array(
							'form_provider'       => 'divi',
							'firebase_project_id' => 'mrdemonwolf-official-app',
							'notification_email'  => '',
							'allowed_form_ids'    => '',
							'allowed_origins'     => '',
						);
					}
					return $default;
				},
			)
		);
	}

	public function test_get_instance_returns_singleton(): void {
		$instance1 = \MRDW_Forms::get_instance();
		$instance2 = \MRDW_Forms::get_instance();

		$this->assertSame( $instance1, $instance2 );
	}

	public function test_get_instance_returns_forms_module(): void {
		$instance = \MRDW_Forms::get_instance();

		$this->assertInstanceOf( \MRDW_Forms::class, $instance );
	}

	public function test_get_loader_returns_loader(): void {
		$instance = \MRDW_Forms::get_instance();

		$this->assertInstanceOf( \MRDW_Forms_Loader::class, $instance->get_loader() );
	}

	public function test_hooks_are_registered(): void {
		$instance = \MRDW_Forms::get_instance();
		$loader   = $instance->get_loader();
		$actions  = $loader->get_actions();

		$hook_names = array_column( $actions, 'hook' );
		$this->assertContains( 'admin_menu', $hook_names );
		$this->assertContains( 'admin_init', $hook_names );
		$this->assertContains( 'rest_api_init', $hook_names );
		$this->assertContains( 'admin_notices', $hook_names );
	}

	public function test_cors_filter_registered(): void {
		$instance = \MRDW_Forms::get_instance();
		$loader   = $instance->get_loader();
		$filters  = $loader->get_filters();

		$hook_names = array_column( $filters, 'hook' );
		$this->assertContains( 'rest_pre_serve_request', $hook_names );
	}

	public function test_provider_dependency_notice_method_exists(): void {
		$instance = \MRDW_Forms::get_instance();
		$loader   = $instance->get_loader();
		$actions  = $loader->get_actions();

		$callbacks = array();
		foreach ( $actions as $action ) {
			if ( 'admin_notices' === $action['hook'] ) {
				$callbacks[] = $action['callback'];
			}
		}

		$this->assertContains( 'provider_dependency_notice', $callbacks );
	}
}
