<?php
/**
 * Tests for TailSignal_Admin_Dashboard.
 *
 * @package TailSignal
 */

use Brain\Monkey\Functions;

require_once dirname( __DIR__, 2 ) . '/modules/push/includes/class-tailsignal-db.php';
require_once dirname( __DIR__, 2 ) . '/modules/push/admin/class-tailsignal-admin-dashboard.php';

class Test_TailSignal_Admin_Dashboard extends TailSignal_TestCase {

	/**
	 * Helper to set up DB mocks and run render(), capturing wp_localize_script args.
	 *
	 * @param array $db_results Optional overrides for DB return values.
	 * @return array|null The args passed to wp_localize_script, or null.
	 */
	private function run_render_and_capture_localize( $db_results = array() ) {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$device_stats = isset( $db_results['stats'] ) ? $db_results['stats'] : (object) array(
			'total'   => 10,
			'ios'     => 6,
			'android' => 4,
			'dev'     => 2,
		);
		$success_stats = (object) array(
			'total_success' => 0,
			'total_devices' => 0,
		);
		$monthly_count = isset( $db_results['monthly_count'] ) ? $db_results['monthly_count'] : '25';
		$chart_results = isset( $db_results['chart_results'] ) ? $db_results['chart_results'] : array();

		// get_row is called twice: first for device stats, then for success rate.
		$get_row_call = 0;
		$wpdb->shouldReceive( 'get_row' )->andReturnUsing( function() use ( &$get_row_call, $device_stats, $success_stats ) {
			$get_row_call++;
			return ( 1 === $get_row_call ) ? $device_stats : $success_stats;
		} );

		$wpdb->shouldReceive( 'get_var' )->andReturn( $monthly_count );
		$wpdb->shouldReceive( 'get_results' )->andReturn( $chart_results );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		Functions\expect( 'get_option' )->andReturn( '0' );

		$localized = null;
		Functions\expect( 'wp_localize_script' )
			->andReturnUsing( function( $handle, $name, $data ) use ( &$localized ) {
				$localized = array( $handle, $name, $data );
			} );

		$dashboard = new TailSignal_Admin_Dashboard();

		ob_start();
		try {
			$dashboard->render();
		} catch ( \Throwable $e ) {
			// May fail on include — that's OK, we're testing the logic before it.
		}
		ob_end_clean();

		return $localized;
	}

	/**
	 * Test render calls wp_localize_script with chart data.
	 */
	public function test_render_localizes_chart_data() {
		$localized = $this->run_render_and_capture_localize();

		$this->assertNotNull( $localized, 'wp_localize_script was not called' );
		$this->assertSame( 'tailsignal-admin', $localized[0] );
		$this->assertSame( 'tailsignal_chart', $localized[1] );
	}

	/**
	 * Test chart data structure has correct keys.
	 */
	public function test_render_chart_data_structure() {
		$localized = $this->run_render_and_capture_localize();

		$this->assertNotNull( $localized, 'wp_localize_script was not called' );
		$chart_data = $localized[2];
		$this->assertArrayHasKey( 'labels', $chart_data );
		$this->assertArrayHasKey( 'success', $chart_data );
		$this->assertArrayHasKey( 'failed', $chart_data );
	}

	/**
	 * Test chart data has 12 months of labels.
	 */
	public function test_render_chart_has_12_months() {
		$localized = $this->run_render_and_capture_localize();

		$this->assertNotNull( $localized, 'wp_localize_script was not called' );
		$chart_data = $localized[2];
		$this->assertCount( 12, $chart_data['labels'] );
		$this->assertCount( 12, $chart_data['success'] );
		$this->assertCount( 12, $chart_data['failed'] );
	}

	/**
	 * Test chart data defaults to zeros.
	 */
	public function test_render_chart_defaults_to_zeros() {
		$localized = $this->run_render_and_capture_localize();

		$this->assertNotNull( $localized, 'wp_localize_script was not called' );
		$chart_data = $localized[2];
		foreach ( $chart_data['success'] as $val ) {
			$this->assertSame( 0, $val );
		}
		foreach ( $chart_data['failed'] as $val ) {
			$this->assertSame( 0, $val );
		}
	}

	/**
	 * Test render method exists.
	 */
	public function test_render_method_exists() {
		$dashboard = new TailSignal_Admin_Dashboard();
		$this->assertTrue( method_exists( $dashboard, 'render' ) );
	}

	/**
	 * Test chart labels are in YYYY-MM format.
	 */
	public function test_render_chart_label_format() {
		$localized = $this->run_render_and_capture_localize();

		$this->assertNotNull( $localized, 'wp_localize_script was not called' );
		$chart_data = $localized[2];
		foreach ( $chart_data['labels'] as $label ) {
			$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}$/', $label );
		}
	}

	/**
	 * Test render queries dev_mode option.
	 */
	public function test_render_checks_dev_mode() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'wp_';

		$get_row_call = 0;
		$wpdb->shouldReceive( 'get_row' )->andReturnUsing( function() use ( &$get_row_call ) {
			$get_row_call++;
			if ( 1 === $get_row_call ) {
				return (object) array( 'total' => 0, 'ios' => 0, 'android' => 0, 'dev' => 0 );
			}
			return (object) array( 'total_success' => 0, 'total_devices' => 0 );
		} );
		$wpdb->shouldReceive( 'get_var' )->andReturn( '0' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array() );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );

		$dev_mode_checked = false;
		Functions\expect( 'get_option' )->andReturnUsing( function( $key ) use ( &$dev_mode_checked ) {
			if ( 'tailsignal_dev_mode' === $key ) {
				$dev_mode_checked = true;
				return '1';
			}
			return '';
		} );

		Functions\expect( 'wp_localize_script' )->andReturn( true );

		$dashboard = new TailSignal_Admin_Dashboard();

		ob_start();
		try {
			$dashboard->render();
		} catch ( \Throwable $e ) {
			// Expected.
		}
		ob_end_clean();

		$this->assertTrue( $dev_mode_checked );
	}

	/**
	 * Test chart data fills in actual stats from DB.
	 */
	public function test_render_chart_fills_stats() {
		$current_month     = gmdate( 'Y-m' );
		$stat_row          = new stdClass();
		$stat_row->month   = $current_month;
		$stat_row->success = 8;
		$stat_row->failed  = 2;

		$localized = $this->run_render_and_capture_localize( array(
			'chart_results' => array( $stat_row ),
		) );

		$this->assertNotNull( $localized, 'wp_localize_script was not called' );
		$chart_data = $localized[2];

		// The current month should have the stats filled in (last label = index 11).
		$this->assertSame( 8, $chart_data['success'][11] );
		$this->assertSame( 2, $chart_data['failed'][11] );
	}
}
