<?php
/**
 * Parent admin menu and module settings screen.
 *
 * Uses core admin markup and the Settings API only, so the screen inherits
 * WordPress styling and needs no stylesheet of its own.
 *
 * @package    MrDemonWolf
 * @copyright  2026 MrDemonWolf, Inc.
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MRDW_Admin
 */
class MRDW_Admin {

	/**
	 * Slug of the top-level menu every module hangs off.
	 */
	const MENU_SLUG = 'mrdemonwolf';

	/**
	 * Capability required to manage the plugin as a whole.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Settings API group name.
	 */
	const SETTINGS_GROUP = 'mrdemonwolf_general';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'update_option_' . MRDW_Modules::OPTION_NAME, array( 'MRDW_Modules', 'clear_cache' ) );
	}

	/**
	 * Register the top-level menu.
	 *
	 * Priority 5 so this runs before the modules add their submenus.
	 */
	public static function register_menu() {
		add_menu_page(
			__( 'MrDemonWolf', 'mrdemonwolf' ),
			__( 'MrDemonWolf', 'mrdemonwolf' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-superhero-alt',
			58
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'General', 'mrdemonwolf' ),
			__( 'General', 'mrdemonwolf' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the module toggle setting.
	 */
	public static function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			MRDW_Modules::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'MRDW_Modules', 'sanitize' ),
				'default'           => MRDW_Modules::defaults(),
			)
		);

		add_settings_section(
			'mrdemonwolf_modules_section',
			__( 'Modules', 'mrdemonwolf' ),
			array( __CLASS__, 'render_section_intro' ),
			self::MENU_SLUG
		);

		add_settings_field(
			'mrdemonwolf_module_forms',
			__( 'Forms', 'mrdemonwolf' ),
			array( __CLASS__, 'render_module_field' ),
			self::MENU_SLUG,
			'mrdemonwolf_modules_section',
			array(
				'module'      => 'forms',
				'label'       => __( 'Accept form submissions over the REST API', 'mrdemonwolf' ),
				'description' => __( 'Bridges external apps to Divi, WPForms and Gravity Forms, protected by Firebase App Check.', 'mrdemonwolf' ),
				'label_for'   => 'mrdemonwolf_module_forms',
			)
		);

		add_settings_field(
			'mrdemonwolf_module_push',
			__( 'Push', 'mrdemonwolf' ),
			array( __CLASS__, 'render_module_field' ),
			self::MENU_SLUG,
			'mrdemonwolf_modules_section',
			array(
				'module'      => 'push',
				'label'       => __( 'Send Expo push notifications', 'mrdemonwolf' ),
				'description' => __( 'Device registry, groups, scheduling and delivery receipts.', 'mrdemonwolf' ),
				'label_for'   => 'mrdemonwolf_module_push',
			)
		);
	}

	/**
	 * Section blurb.
	 */
	public static function render_section_intro() {
		echo '<p>' . esc_html__(
			'Turning a module off unregisters its hooks and REST routes. Stored data is left untouched, so it returns unchanged when switched back on.',
			'mrdemonwolf'
		) . '</p>';
	}

	/**
	 * Render one module checkbox.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_module_field( $args ) {
		$module = isset( $args['module'] ) ? (string) $args['module'] : '';
		if ( '' === $module ) {
			return;
		}

		$id      = 'mrdemonwolf_module_' . $module;
		$enabled = MRDW_Modules::is_enabled( $module );
		?>
		<label for="<?php echo esc_attr( $id ); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr( $id ); ?>"
				name="<?php echo esc_attr( MRDW_Modules::OPTION_NAME . '[' . $module . ']' ); ?>"
				value="1"
				<?php checked( $enabled ); ?>
			/>
			<?php echo esc_html( isset( $args['label'] ) ? $args['label'] : '' ); ?>
		</label>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render the settings screen.
	 */
	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to manage this plugin.', 'mrdemonwolf' ) );
		}

		$channel = MRDW_Updater::channel();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'MrDemonWolf', 'mrdemonwolf' ); ?></h1>

			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				do_settings_sections( self::MENU_SLUG );
				submit_button();
				?>
			</form>

			<h2><?php echo esc_html__( 'Updates', 'mrdemonwolf' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Installed version', 'mrdemonwolf' ); ?></th>
						<td><code><?php echo esc_html( MRDW_VERSION ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__( 'Update channel', 'mrdemonwolf' ); ?></th>
						<td>
							<code><?php echo esc_html( $channel ); ?></code>
							<p class="description">
								<?php
								printf(
									/* translators: %s: name of the PHP constant to define. */
									esc_html__( 'Set %s in wp-config.php to switch channels. Nightly builds are pre-releases cut from the main branch and are not production tested.', 'mrdemonwolf' ),
									esc_html( MRDW_Updater::CHANNEL_CONSTANT )
								);
								?>
							</p>
							<?php if ( MRDW_Updater::is_nightly() ) : ?>
								<p class="description">
									<?php echo esc_html__( 'WordPress never offers a lower version than the one installed, so returning to stable means reinstalling the stable zip by hand.', 'mrdemonwolf' ); ?>
								</p>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
