<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/peterbode1989/barcode-generator
 * @since      1.0.0
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/admin
 * @author     Peter Bode <peterbode1989@gmail.com>
 */
class Barcode_Generator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private static $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		self::$plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( self::$plugin_name, plugin_dir_url( __FILE__ ) . 'css/barcode-generator-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( self::$plugin_name, plugin_dir_url( __FILE__ ) . 'js/barcode-generator-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Custom settings page.
	 *
	 * @since    1.0.0
	 */
	public static function settings_init() {
		// Register a new setting for "wporg" page.
		register_setting( self::$plugin_name, self::$plugin_name.'_opt' );
	
		// Register a new section in the "wporg" page.
		add_settings_section(
			self::$plugin_name.'_field',
			__( 'Create barcodes', self::$plugin_name ),
			array(__CLASS__, 'wporg_section_developers_callback'),
			self::$plugin_name
		);
	
		// Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
		add_settings_field(
			self::$plugin_name.'_field', 
			__( 'How much barcodes do you need?', self::$plugin_name ),
			array(__CLASS__, 'settings_fields'),
			self::$plugin_name,
			self::$plugin_name.'_field'
		);
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	public static function wporg_section_developers_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'You can create a finite amount of barcodes here. To make sure there are always plenty available, you shoeld make enough.', self::$plugin_name ); ?></p>
		<?php
	}

	/**
	 * Pill field callbakc function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	public static function settings_fields( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( self::$plugin_name.'_opt' );

		// echo '<pre>';
		// print_R($options);
		// echo '</pre>';

		$inputNumberName = self::$plugin_name.'_amount';
		echo "<input type='number' name='{$inputNumberName}' value='0' min='0'>";
	}
	
	/**
	 * Add the top level menu page.
	 */
	public static function wporg_options_page() {
		add_menu_page(
			self::$plugin_name,
			__('Barcode generator', self::$plugin_name),
			'manage_options',
			self::$plugin_name,
			array(__CLASS__, 'settings_page')
		);
	}
	
	
	/**
	 * Top level menu callback function
	 */
	public static function settings_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	
		// add error/update messages
	
		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( self::$plugin_name.'_barcode', self::$plugin_name.'_barcode', __( 'Barcodes generated', self::$plugin_name ), 'updated' );
		}
	
		// show error/update messages
		settings_errors( self::$plugin_name.'_barcode' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wporg"
				settings_fields( self::$plugin_name );
				// output setting sections and their fields
				// (sections are registered for "wporg", each field is registered to a specific section)
				do_settings_sections( self::$plugin_name );
				// output save settings button
				submit_button( __('Generate barcodes', self::$plugin_name) );
				?>
			</form>
		</div>
		<?php
	}
}
