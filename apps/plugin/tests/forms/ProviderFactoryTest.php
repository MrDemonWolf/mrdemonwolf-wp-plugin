<?php
/**
 * Tests for MRDW_Forms_Provider_Factory.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 */

namespace MRDW_Forms\Tests;

use Brain\Monkey\Functions;

class ProviderFactoryTest extends TestCase {

	public function test_create_defaults_to_divi(): void {
		Functions\when( 'get_option' )->alias(
			function ( $key, $default = false ) {
				if ( 'mrdw_forms_settings' === $key ) {
					return array( 'form_provider' => 'divi' );
				}
				return $default;
			}
		);

		$provider = \MRDW_Forms_Provider_Factory::create();

		$this->assertInstanceOf( \MRDW_Forms_Provider_Divi::class, $provider );
	}

	public function test_create_respects_settings(): void {
		Functions\when( 'get_option' )->alias(
			function ( $key, $default = false ) {
				if ( 'mrdw_forms_settings' === $key ) {
					return array( 'form_provider' => 'wpforms' );
				}
				return $default;
			}
		);

		$provider = \MRDW_Forms_Provider_Factory::create();

		$this->assertInstanceOf( \MRDW_Forms_Provider_WPForms::class, $provider );
	}

	public function test_create_with_explicit_slug(): void {
		$provider = \MRDW_Forms_Provider_Factory::create( 'gravityforms' );

		$this->assertInstanceOf( \MRDW_Forms_Provider_GravityForms::class, $provider );
	}

	public function test_create_falls_back_to_divi_for_unknown_slug(): void {
		$provider = \MRDW_Forms_Provider_Factory::create( 'unknown' );

		$this->assertInstanceOf( \MRDW_Forms_Provider_Divi::class, $provider );
	}

	public function test_get_available_providers_returns_all(): void {
		$providers = \MRDW_Forms_Provider_Factory::get_available_providers();

		$this->assertArrayHasKey( 'divi', $providers );
		$this->assertArrayHasKey( 'wpforms', $providers );
		$this->assertArrayHasKey( 'gravityforms', $providers );

		$this->assertSame( 'Divi', $providers['divi']['label'] );
		$this->assertSame( 'WPForms', $providers['wpforms']['label'] );
		$this->assertSame( 'Gravity Forms', $providers['gravityforms']['label'] );
	}
}
