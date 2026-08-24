<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package MrDemonWolf
 */

// Define WordPress constants needed by plugin files.
define( 'ABSPATH', '/tmp/wordpress/' );
define( 'MRDW_VERSION', '1.0.0' );
define( 'MRDW_PLUGIN_FILE', dirname( __DIR__, 2 ) . '/mrdemonwolf.php' );
define( 'MRDW_PUSH_DIR', dirname( __DIR__, 2 ) . '/modules/push/' );
define( 'MRDW_PUSH_URL', 'http://example.com/wp-content/plugins/mrdemonwolf-wp-plugin/modules/push/' );
define( 'MRDW_PLUGIN_BASENAME', 'mrdemonwolf-wp-plugin/mrdemonwolf.php' );

// Create WordPress stub files needed by plugin.
$wp_admin_dir = ABSPATH . 'wp-admin/includes/';
if ( ! is_dir( $wp_admin_dir ) ) {
	mkdir( $wp_admin_dir, 0755, true );
}
if ( ! file_exists( $wp_admin_dir . 'upgrade.php' ) ) {
	file_put_contents( $wp_admin_dir . 'upgrade.php', '<?php // Stub for testing.' );
}

// Load Composer autoloader.
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Shared plugin glue (module gate, secrets, conflict guard, updater, admin menu).
define( 'MRDW_VERSION', '1.4.0' );
define( 'MRDW_PLUGIN_FILE', dirname( __DIR__, 2 ) . '/mrdemonwolf.php' );
define( 'MRDW_PLUGIN_DIR', dirname( __DIR__, 2 ) . '/' );
define( 'MRDW_PLUGIN_URL', 'https://example.com/wp-content/plugins/mrdemonwolf-wp-plugin/' );
define( 'MRDW_PLUGIN_BASENAME', 'mrdemonwolf-wp-plugin/mrdemonwolf.php' );
require_once dirname( __DIR__, 2 ) . '/includes/class-mrdw-modules.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-mrdw-secrets.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-mrdw-conflict.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-mrdw-updater.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-mrdw-admin.php';

// Define WordPress stub classes for testing.
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $code;
		public $message;
		public $data;

		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}

if ( ! class_exists( 'WP_REST_Server' ) ) {
	class WP_REST_Server {
		const READABLE   = 'GET';
		const CREATABLE  = 'POST';
		const EDITABLE   = 'PUT, PATCH';
		const DELETABLE  = 'DELETE';
		const ALLMETHODS  = 'GET, POST, PUT, PATCH, DELETE';
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		protected $data;
		protected $status;
		protected $headers = array();

		public function __construct( $data = null, $status = 200, $headers = array() ) {
			$this->data    = $data;
			$this->status  = $status;
			$this->headers = $headers;
		}

		public function get_data() {
			return $this->data;
		}

		public function get_status() {
			return $this->status;
		}

		public function header( $key, $value ) {
			$this->headers[ $key ] = $value;
		}

		public function get_headers() {
			return $this->headers;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		protected $params = array();

		public function get_param( $key ) {
			return $this->params[ $key ] ?? null;
		}

		public function set_param( $key, $value ) {
			$this->params[ $key ] = $value;
		}

		public function get_file_params() {
			return array();
		}
	}
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	class WP_List_Table {
		public $items = array();
		public function __construct( $args = array() ) {}
		public function set_pagination_args( $args ) {}
		public function get_pagenum() { return 1; }
		protected function row_actions( $actions, $always_visible = false ) { return ''; }
	}
}

// Define is_wp_error if not already defined.
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

// Load base test class.
if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

require_once __DIR__ . '/TestCase.php';
