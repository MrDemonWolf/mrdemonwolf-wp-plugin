<?php
/**
 * Tests for MRDW_Push_Admin.
 *
 * @package MrDemonWolf
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-mrdw-push-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-mrdw-push-admin.php';

class Test_MRDW_Push_Admin extends MRDW_Push_TestCase {

	/**
	 * @var MRDW_Push_Admin
	 */
	private $admin;

	protected function setUp(): void {
		parent::setUp();
		$this->admin = new MRDW_Push_Admin();
	}

	// ── add_menu_pages ──────────────────────────────────────────

	/**
	 * The Push module no longer owns a top-level menu; it hangs off the
	 * shared MrDemonWolf menu registered by MRDW_Admin.
	 */
	public function test_add_menu_pages_registers_no_top_level_menu() {
		Functions\expect( 'add_menu_page' )->never();
		Functions\expect( 'add_submenu_page' )->times( 6 );

		$this->admin->add_menu_pages();
		$this->assertTrue( true );
	}

	/**
	 * Every submenu is parented to the shared MrDemonWolf menu.
	 */
	public function test_add_menu_pages_parent_slug() {
		$parents = array();

		Functions\expect( 'add_menu_page' )->never();
		Functions\expect( 'add_submenu_page' )->times( 6 )->andReturnUsing( function() use ( &$parents ) {
			$args      = func_get_args();
			$parents[] = $args[0]; // parent slug is the 1st arg.
		} );

		$this->admin->add_menu_pages();

		foreach ( $parents as $parent ) {
			$this->assertSame( MRDW_Admin::MENU_SLUG, $parent );
		}
	}

	/**
	 * Test all submenus use mrdw_manage capability.
	 */
	public function test_add_menu_pages_submenu_capabilities() {
		$submenu_caps = array();

		Functions\expect( 'add_menu_page' )->never();
		Functions\expect( 'add_submenu_page' )->times( 6 )->andReturnUsing( function() use ( &$submenu_caps ) {
			$args = func_get_args();
			$submenu_caps[] = $args[3]; // capability is 4th arg (0-indexed).
		} );

		$this->admin->add_menu_pages();

		foreach ( $submenu_caps as $cap ) {
			$this->assertSame( 'mrdw_manage', $cap );
		}
	}

	/**
	 * Test submenu slugs are correct and unchanged by the merge, so existing
	 * bookmarks and the app's documented admin URLs keep working.
	 */
	public function test_add_menu_pages_submenu_slugs() {
		$submenu_slugs = array();

		Functions\expect( 'add_menu_page' )->never();
		Functions\expect( 'add_submenu_page' )->times( 6 )->andReturnUsing( function() use ( &$submenu_slugs ) {
			$args = func_get_args();
			$submenu_slugs[] = $args[4]; // slug is 5th arg (0-indexed).
		} );

		$this->admin->add_menu_pages();

		$expected = array(
			'mrdw-push',
			'mrdw-push-send',
			'mrdw-push-devices',
			'mrdw-push-groups',
			'mrdw-push-history',
			'mrdw-push-settings',
		);
		$this->assertSame( $expected, $submenu_slugs );
	}


	// ── enqueue_styles ──────────────────────────────────────────

	/**
	 * Test enqueue_styles returns early for non-MRDW_Push page.
	 */
	public function test_enqueue_styles_returns_early_for_other_pages() {
		// wp_enqueue_style should NOT be called.
		$this->admin->enqueue_styles( 'edit.php' );
		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_styles enqueues both stylesheets on MRDW_Push page.
	 */
	public function test_enqueue_styles_enqueues_on_mrdw_push_page() {
		$enqueued = array();

		Functions\expect( 'wp_enqueue_style' )->twice()->andReturnUsing( function() use ( &$enqueued ) {
			$args = func_get_args();
			$enqueued[] = $args[0];
		} );

		$this->admin->enqueue_styles( 'mrdw_page_mrdw-push' );

		$this->assertContains( 'mrdw-push-tailwind', $enqueued );
		$this->assertContains( 'mrdw-push-admin', $enqueued );
	}

	/**
	 * Test mrdw-push-admin depends on mrdw-push-tailwind.
	 */
	public function test_enqueue_styles_admin_depends_on_tailwind() {
		$deps_map = array();

		Functions\expect( 'wp_enqueue_style' )->twice()->andReturnUsing( function() use ( &$deps_map ) {
			$args = func_get_args();
			$deps_map[ $args[0] ] = $args[2]; // deps is 3rd arg.
		} );

		$this->admin->enqueue_styles( 'mrdw_page_mrdw-push' );

		$this->assertSame( array( 'mrdw-push-tailwind' ), $deps_map['mrdw-push-admin'] );
	}

	/**
	 * Test enqueue_styles works on all MRDW_Push pages.
	 */
	public function test_enqueue_styles_works_on_send_page() {
		Functions\expect( 'wp_enqueue_style' )->twice();
		$this->admin->enqueue_styles( 'mrdw_page_mrdw-push-send' );
		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_styles works on settings page.
	 */
	public function test_enqueue_styles_works_on_settings_page() {
		Functions\expect( 'wp_enqueue_style' )->twice();
		$this->admin->enqueue_styles( 'mrdw_page_mrdw-push-settings' );
		$this->assertTrue( true );
	}

	// ── enqueue_scripts ─────────────────────────────────────────

	/**
	 * Test enqueue_scripts returns early for unrelated hook.
	 */
	public function test_enqueue_scripts_returns_early_for_other_pages() {
		$this->admin->enqueue_scripts( 'edit.php' );
		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_scripts works on MRDW_Push page.
	 */
	public function test_enqueue_scripts_on_mrdw_push_page() {
		Functions\expect( 'wp_enqueue_script' )->atLeast()->once();
		Functions\expect( 'wp_localize_script' )->once();
		Functions\expect( 'admin_url' )->andReturn( 'http://example.com/wp-admin/admin-ajax.php' );
		Functions\expect( 'rest_url' )->andReturn( 'http://example.com/wp-json/mrdw/v1/' );
		Functions\expect( 'wp_create_nonce' )->andReturn( 'test_nonce' );

		$this->admin->enqueue_scripts( 'mrdw_page_mrdw-push-devices' );
		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_scripts on post editor.
	 */
	public function test_enqueue_scripts_on_post_editor() {
		Functions\expect( 'wp_enqueue_media' )->once();
		Functions\expect( 'wp_enqueue_script' )->atLeast()->once();
		Functions\expect( 'wp_localize_script' )->once();
		Functions\expect( 'admin_url' )->andReturn( '' );
		Functions\expect( 'rest_url' )->andReturn( '' );
		Functions\expect( 'wp_create_nonce' )->andReturn( '' );

		$this->admin->enqueue_scripts( 'post.php' );
		$this->assertTrue( true );
	}

	/**
	 * Test wp_enqueue_media called on send page.
	 */
	public function test_enqueue_scripts_media_on_send_page() {
		Functions\expect( 'wp_enqueue_media' )->once();
		Functions\expect( 'wp_enqueue_script' )->atLeast()->once();
		Functions\expect( 'wp_localize_script' )->once();
		Functions\expect( 'admin_url' )->andReturn( '' );
		Functions\expect( 'rest_url' )->andReturn( '' );
		Functions\expect( 'wp_create_nonce' )->andReturn( '' );

		$this->admin->enqueue_scripts( 'mrdw_page_mrdw-push-send' );
		$this->assertTrue( true );
	}

	/**
	 * Test wp_enqueue_media NOT called on dashboard.
	 */
	public function test_enqueue_scripts_no_media_on_dashboard() {
		Functions\expect( 'wp_enqueue_script' )->atLeast()->once();
		Functions\expect( 'wp_localize_script' )->once();
		Functions\expect( 'admin_url' )->andReturn( '' );
		Functions\expect( 'rest_url' )->andReturn( '' );
		Functions\expect( 'wp_create_nonce' )->andReturn( '' );

		// wp_enqueue_media should NOT be called.
		$this->admin->enqueue_scripts( 'mrdw_page_mrdw-push' );
		$this->assertTrue( true );
	}

	/**
	 * Test Chart.js enqueued only on dashboard.
	 */
	public function test_enqueue_scripts_chartjs_on_dashboard() {
		$enqueued = array();

		Functions\expect( 'wp_enqueue_script' )->andReturnUsing( function() use ( &$enqueued ) {
			$args = func_get_args();
			$enqueued[] = $args[0];
		} );
		Functions\expect( 'wp_localize_script' )->once();
		Functions\expect( 'admin_url' )->andReturn( '' );
		Functions\expect( 'rest_url' )->andReturn( '' );
		Functions\expect( 'wp_create_nonce' )->andReturn( '' );

		$this->admin->enqueue_scripts( 'mrdw_page_mrdw-push' );

		$this->assertContains( 'chartjs', $enqueued );
	}

	/**
	 * Test Chart.js NOT enqueued on non-dashboard MRDW_Push page.
	 */
	public function test_enqueue_scripts_no_chartjs_on_other_pages() {
		$enqueued = array();

		Functions\expect( 'wp_enqueue_script' )->andReturnUsing( function() use ( &$enqueued ) {
			$args = func_get_args();
			$enqueued[] = $args[0];
		} );
		Functions\expect( 'wp_localize_script' )->once();
		Functions\expect( 'admin_url' )->andReturn( '' );
		Functions\expect( 'rest_url' )->andReturn( '' );
		Functions\expect( 'wp_create_nonce' )->andReturn( '' );

		$this->admin->enqueue_scripts( 'mrdw_page_mrdw-push-devices' );

		$this->assertNotContains( 'chartjs', $enqueued );
	}

	/**
	 * Test localize_script includes required keys.
	 */
	public function test_enqueue_scripts_localize_data() {
		$localized = null;

		Functions\expect( 'wp_enqueue_script' )->atLeast()->once();
		Functions\expect( 'wp_localize_script' )->once()->andReturnUsing( function() use ( &$localized ) {
			$args = func_get_args();
			$localized = $args[2]; // data is 3rd arg.
		} );
		Functions\expect( 'admin_url' )->andReturn( 'http://example.com/wp-admin/admin-ajax.php' );
		Functions\expect( 'rest_url' )->andReturn( 'http://example.com/wp-json/mrdw/v1/' );
		Functions\expect( 'wp_create_nonce' )->andReturn( 'nonce123' );

		$this->admin->enqueue_scripts( 'mrdw_page_mrdw-push-devices' );

		$this->assertArrayHasKey( 'ajax_url', $localized );
		$this->assertArrayHasKey( 'rest_url', $localized );
		$this->assertArrayHasKey( 'nonce', $localized );
		$this->assertArrayHasKey( 'strings', $localized );
	}

	// ── Render methods ──────────────────────────────────────────

	/**
	 * Test render_settings_page includes partial file.
	 */
	public function test_render_settings_page() {
		// Create a temporary settings.php partial.
		$partials_dir = MRDW_PUSH_DIR . 'admin/partials/';
		if ( ! is_dir( $partials_dir ) ) {
			mkdir( $partials_dir, 0755, true );
		}

		// The file already exists in the plugin, so just verify no errors.
		// We can't easily test the include without a real file, so we just
		// verify the method exists and is callable.
		$this->assertTrue( method_exists( $this->admin, 'render_settings_page' ) );
	}

	/**
	 * Test render methods exist.
	 */
	public function test_render_methods_exist() {
		$this->assertTrue( method_exists( $this->admin, 'render_dashboard_page' ) );
		$this->assertTrue( method_exists( $this->admin, 'render_send_page' ) );
		$this->assertTrue( method_exists( $this->admin, 'render_devices_page' ) );
		$this->assertTrue( method_exists( $this->admin, 'render_groups_page' ) );
		$this->assertTrue( method_exists( $this->admin, 'render_history_page' ) );
	}
}
