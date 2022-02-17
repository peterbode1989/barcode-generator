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
 * Defines the plugin name, version
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
	 * Hook into the Woocommerce order complete action
	 *
	 * @since    1.0.0
	 */
	public static function wc_hook($order_id) {
		if ( ! $order_id )
			return;

		// Getting an instance of the order object
		$order = wc_get_order( $order_id );

		$status = false;
		foreach ( $order->get_items() as $item_id => $item ) :
			if( $item['variation_id'] > 0 ):
				$product_id = $item['variation_id']; // variable product
			else:
				$product_id = $item['product_id']; // simple product
			endif;
			
			$quantity = $item['quantity'];
			if(!empty($quantity)):
				$barcodeProduct = get_post_meta( $product_id, '_barcode_enabled', true );
			
				if($barcodeProduct === 'yes'):
					if(!$status):
						$status = true;
					endif;
				endif;
			endif;

		endforeach;

		if($status):
			global $wpdb;
			$table = $wpdb->prefix.'barcodes';

			$check = $wpdb->get_results("SELECT * FROM $table WHERE order_id = ".(int)$order_id);
			if(count($check) == 0):
				$fields = array('order_id' => $order_id);
				$wpdb->insert($table,$fields);
			endif;

		endif;
	}
}
