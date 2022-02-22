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
	 * Custom settings page.
	 *
	 * @since    1.0.0
	 */
	public static function settings_init() {
		// Register a new setting for "wporg" page.
		register_setting( self::$plugin_name, self::$plugin_name.'_title' );
		register_setting( self::$plugin_name, self::$plugin_name.'_desc' );
	
		// Register a new section in the "wporg" page.
		add_settings_section(
			self::$plugin_name.'_field',
			__( 'Email settings', self::$plugin_name ),
			array(__CLASS__, 'desc'),
			self::$plugin_name
		);
	
		// Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
		add_settings_field(
			self::$plugin_name.'_field', 
			__( 'Email title', self::$plugin_name ),
			array(__CLASS__, 'settings_fields'),
			self::$plugin_name,
			self::$plugin_name.'_field'
		);

		add_settings_field(
			self::$plugin_name.'_field_alt', 
			__( 'Email description', self::$plugin_name ),
			array(__CLASS__, 'settings_fields_alt'),
			self::$plugin_name,
			self::$plugin_name.'_field'
		);
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	public static function desc( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Manage all email content from here.', self::$plugin_name ); ?></p>
		<?php
	}

	/**
	 * Pill field callback function.
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
		$title = get_option( self::$plugin_name.'_title' );

		$inputName = self::$plugin_name.'_title';
		echo "<input type='text' name='{$inputName}' value='{$title}'>";
	}

	public static function settings_fields_alt( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$desc = get_option( self::$plugin_name.'_desc' );

		$inputName = self::$plugin_name.'_desc';
		echo "<textarea type='text' name='{$inputName}'>{$desc}</textarea>";
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

		global $wpdb;
		$table = $wpdb->prefix.'barcodes_entries';
	
		// add error/update messages
	
		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( self::$plugin_name.'_settings', self::$plugin_name.'_settings', __( 'Settings saved', self::$plugin_name ), 'updated' );
		}

		if(!empty($_POST['uploadCSV'])):
			$csv = array();
			// check there are no errors
			if($_FILES['csv']['error'] == 0){
				$format = array('%d');

				$name = $_FILES['csv']['name'];
				$ext = explode('.', $_FILES['csv']['name']);
				$ext = strtolower(end($ext));
				$type = $_FILES['csv']['type'];
				$tmpName = $_FILES['csv']['tmp_name'];

				// check the file is a csv
				if($ext === 'csv'){
					if(($handle = fopen($tmpName, 'r')) !== FALSE) {
						// necessary if a large csv file
						set_time_limit(0);

						while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
							$value = current($data);
							if (mb_detect_encoding($value) === 'UTF-8') {
								$value = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $value);
							}

							$fields = array('barcode' => $value);
							$wpdb->insert($table,$fields,$format);
						}
						fclose($handle);
					}
				}
				
				add_settings_error( self::$plugin_name.'_csv', self::$plugin_name.'_csv', __( 'Barcodes generated', self::$plugin_name ), 'updated' );
			}
		endif;
	
		// show error/update messages
		settings_errors( self::$plugin_name.'_settings' );
		settings_errors( self::$plugin_name.'_csv' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wporg"
				settings_fields( self::$plugin_name );
				// settings_fields( self::$plugin_name.'_alt' );
				// output setting sections and their fields
				// (sections are registered for "wporg", each field is registered to a specific section)
				do_settings_sections( self::$plugin_name );
				// do_settings_sections( self::$plugin_name.'_alt' );
				// output save settings button
				submit_button( __('Save settings', self::$plugin_name) );
				?>
			</form>

			<h1><?php _e('Upload CSV', self::$plugin_name); ?></h1>
			<form action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="uploadCSV" value="1" />
				<input type="file" name="csv" value="" />
				<?php
					submit_button( __('Upload .csv', self::$plugin_name) );
				?>
			</form>

			<h1><?php _e('Barcode details', self::$plugin_name); ?></h1>

			<?php
				$barcodes = $wpdb->get_results("SELECT *
											   FROM $table
											   WHERE order_id = 0");
				$output = [];
				foreach($barcodes as $barcode):
					$output[substr($barcode->barcode, 0, 2)][] = $barcode;
				endforeach;
		
				if(!empty($output)):
					foreach($output as $type => $entries):
						echo sprintf(__('For barcode type %1$s there are still %2$s remaining barcodes.', self::$plugin_name), $type, count($entries));
						echo '<br>';
					endforeach;
				else:
					echo __('No barcodes have been found.', self::$plugin_name);
				endif;
			?>

			<br>
			<h1><?php _e('Debug', self::$plugin_name); ?></h1>
			<p><?php _e('Is the system behaving strange, you can try the debug function to see what the problem could be.', self::$plugin_name); ?></p>
			<a href="?page=barcode-generator&debug=1" class="button button-primary" target="_self"><?php _e('Start debug', self::$plugin_name); ?></a>
		</div>
		<?php

	}


	/**
	 * Add a custom tab to the product create/edit page
	 */
	public static function add_my_custom_product_data_tab( $product_data_tabs ) {
		$product_data_tabs['barcode-tab'] = array(
			'label' => __( 'Barcode', self::$plugin_name ),
			'target' => 'barcode_data',
		);
		return $product_data_tabs;
	}

	/**
	 * Populate the custom tab with content
	 */
	public static function add_my_custom_product_data_fields() {
		global $woocommerce, $post;

		?>
		<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
		<div id="barcode_data" class="panel woocommerce_options_panel">
			<?php
			woocommerce_wp_checkbox( array( 
				'id'            => '_barcode_enabled', 
				'wrapper_class' => 'show_if_simple', 
				'label'         => __( 'Barcode product', self::$plugin_name ),
				'description'   => __( 'Mark this product as a barcode product.', self::$plugin_name ),
				'default'       => '0',
				'desc_tip'      => false,
			) );

			$barcodeAmount = get_post_meta( $post->ID, '_barcode_amount', true );
			if(!$barcodeAmount) :
				$barcodeAmount = 1;
			endif;
			woocommerce_wp_text_input( array( 
				'id'            => '_barcode_amount', 
				'wrapper_class' => 'show_if_simple', 
				'label'         => __( 'Barcode amount', self::$plugin_name ),
				'description'   => __( 'The amount of barcodes that will be given when purchasing this product.', self::$plugin_name ),
				'value'       => $barcodeAmount,
				'desc_tip'      => true,
				'type' => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min' => '1'
				)
			) );

			$barcodePrefix = get_post_meta( $post->ID, '_barcode_prefix', true );
			if(!$barcodePrefix) :
				$barcodePrefix = 87;
			endif;
			woocommerce_wp_text_input( array( 
				'id'            => '_barcode_prefix', 
				'wrapper_class' => 'show_if_simple', 
				'label'         => __( 'Barcode prefix', self::$plugin_name ),
				'description'   => __( 'The first two numbers of the barcode.', self::$plugin_name ),
				'value'       => $barcodePrefix,
				'desc_tip'      => true,
				'type' => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min' => '0'
				)
			) );
			?>
		</div>
		<?php
	}

	/**
	 * Save all changes from custom fields used inside the custom tab
	 */
	public static function woocommerce_process_product_meta_fields_save( $post_id ){
		$woo_checkbox = isset( $_POST['_barcode_enabled'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_barcode_enabled', $woo_checkbox );

		$woo_number = !empty( $_POST['_barcode_amount'] ) ? (int)$_POST['_barcode_amount'] : 1;
		update_post_meta( $post_id, '_barcode_amount', $woo_number );

		$woo_prefix = !empty( $_POST['_barcode_prefix'] ) ? (int)$_POST['_barcode_prefix'] : 1;
		update_post_meta( $post_id, '_barcode_prefix', $woo_prefix );
	}
}
