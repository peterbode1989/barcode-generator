<?php

/**
 * The cronjob-specific functionality of the plugin.
 *
 * @link       https://github.com/peterbode1989/barcode-generator
 * @since      1.0.0
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/cronjob
 */

/**
 * The cronjob-specific functionality of the plugin.
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/cronjob
 * @author     Peter Bode <peterbode1989@gmail.com>
 */
class Barcode_Generator_Cronjob
{

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
	 * @param      string    $version           The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		self::$plugin_name = $plugin_name;
		$this->version = $version;
	}

	public static function scheduleSendBarcodes($schedules)
	{
		$schedules['sendBarcodes'] = array(
			'interval' => 5 * 60,
			'display'  => __('Every Fifteen Minutes'),
		);
		return $schedules;
	}

	public static function sendBarcodes()
	{
		global $wpdb;
		$table = $wpdb->prefix.'barcodes';
		$table_entries = $wpdb->prefix.'barcodes_entries';

		$results = $wpdb->get_results("SELECT * FROM $table WHERE `resolved` = 0");
		if(!empty($results)):
			foreach($results as $result):
				$order = wc_get_order( $result->order_id );

				if(!$order->is_paid()):
					continue;
				endif;

				$barcodes = [];
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
							$barcodeAmount = get_post_meta( $product_id, '_barcode_amount', true );
							$barcodePrefix = get_post_meta( $product_id, '_barcode_prefix', true );

							for($i =0;$i < $quantity; $i++):
								if(!array_key_exists($barcodePrefix, $barcodes)):
									$barcodes[$barcodePrefix] = 0;
								endif;
								$barcodes[$barcodePrefix] += $barcodeAmount;
							endfor;
						endif;
					endif;
				endforeach;

				$validated = [];
				foreach($barcodes as $index => $barcode):
					$scan_barcodes = $wpdb->get_results("SELECT * FROM $table_entries WHERE `order_id` = 0 AND SUBSTR( barcode, 1 , 2 ) = $index LIMIT $barcode");

					if(count($scan_barcodes) >= (int)$barcode):
						$validated[$index] = $scan_barcodes;
					endif;
				endforeach;
				
				if(count($barcodes) == count($validated)):
					$file = 'barcodes-'.$result->order_id.'.txt';
					$current = '';
					foreach($validated as $type => $entries):
						foreach($entries as $entry):
							$IDs[] = $entry->id;
							// var_dump($entry);
							if(!empty($entry->barcode)):
								$current .= $entry->barcode."\n";

								$wpdb->update($table_entries, ['order_id' => $result->order_id], ['id' => $entry->id]);
							endif;
						endforeach;
					endforeach;

					file_put_contents(WP_CONTENT_DIR . '/uploads/' . $file, $current);
					$attachments = array(WP_CONTENT_DIR . '/uploads/'.$file);
					$headers = 'From: '.get_option('blogname').' <'.get_option('admin_email').'>' . "\r\n";

					$message = 'Beste '.$order->get_billing_first_name().' '. $order->get_billing_last_name().','. "\r\n\r\n";
					$message .= get_option(self::$plugin_name.'_desc');


					if(wp_mail($order->get_billing_email(), get_option(self::$plugin_name.'_title'), $message, $headers, $attachments)):
						$wpdb->update($table, ['resolved' => 1], ['order_id' => $result->order_id]);
						
						$order->update_status( 'completed' );
						unlink(WP_CONTENT_DIR . '/uploads/'. $file);
					endif;
				endif;
			endforeach;
		endif;
	}
}
