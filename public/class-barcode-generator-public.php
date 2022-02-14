<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/peterbode1989/barcode-generator
 * @since      1.0.0
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/public
 * @author     Peter Bode <peterbode1989@gmail.com>
 */
class Barcode_Generator_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Barcode_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Barcode_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/barcode-generator-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/barcode-generator-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Hook into the Woocommerce order complete action
	 *
	 * @since    1.0.0
	 */
	public static function wc_hook($order_id) {
		if ( ! $order_id )
			return;

		// Getting an instance of the order object
		$order = wc_get_order( $order_id );

		if($order->is_paid())
			$paid = 'yes';
		else
			$paid = 'no';

		// iterating through each order items (getting product ID and the product object) 
		// (work for simple and variable products)
		foreach ( $order->get_items() as $item_id => $item ) {

			if( $item['variation_id'] > 0 ){
				$product_id = $item['variation_id']; // variable product
			} else {
				$product_id = $item['product_id']; // simple product
			}

			// Get the product object
			$product = wc_get_product( $product_id );

		}

		// Ouptput some data
		echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';

		exit();
	}
}
