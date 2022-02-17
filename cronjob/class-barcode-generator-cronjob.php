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
			'interval' => 15 * 60,
			'display'  => __('Every Fifteen Minutes'),
		);
		return $schedules;
	}

	// public function createAttachment()
	// {
	// 	global $wpdb;
	// 	$table = $wpdb->prefix.'barcodes';
	// 	$table_entries = $wpdb->prefix.'barcodes_entries';

	// 	$results = $wpdb->get_results("SELECT * FROM $table WHERE `resolved` = 0");

	// 	// echo '<pre>';
	// 	// print_r($results);
	// 	// echo '</pre>';

	// 	if(!empty($results)):
	// 		foreach($results as $result):
	// 			$order = wc_get_order( $result->order_id );

	// 			// echo '<pre>';
	// 			// print_r($order);
	// 			// echo '</pre>';


	// 			// echo 'Beste '.$order->get_billing_first_name().' '. $order->get_billing_last_name().',';
	// 			// echo '<hr>';
	// 			// echo 'Send to: '.$order->get_billing_email();
	// 			// echo '<hr>';
	// 			// echo get_option(self::$plugin_name.'_title');
	// 			// echo '<hr>';
	// 			// echo get_option(self::$plugin_name.'_desc');
				
	// 			// // Beste #,

	// 			// exit();

	// 			$barcodes = [];
	// 			foreach ( $order->get_items() as $item_id => $item ) :
	// 				if( $item['variation_id'] > 0 ):
	// 					$product_id = $item['variation_id']; // variable product
	// 				else:
	// 					$product_id = $item['product_id']; // simple product
	// 				endif;
					
	// 				$quantity = $item['quantity'];
	// 				if(!empty($quantity)):
	// 					$barcodeProduct = get_post_meta( $product_id, '_barcode_enabled', true );
					
	// 					if($barcodeProduct === 'yes'):
	// 						$barcodeAmount = get_post_meta( $product_id, '_barcode_amount', true );
	// 						$barcodePrefix = get_post_meta( $product_id, '_barcode_prefix', true );

	// 						// echo 'barcode product '.$product_id .' found '.$quantity.' times<br>';
	// 						// echo 'This product requests: '.$barcodeAmount.' with the prefix: '.$barcodePrefix;
							
	// 						// echo '<hr>';

	// 						for($i =0;$i < $quantity; $i++):
	// 							if(!array_key_exists($barcodePrefix, $barcodes)):
	// 								$barcodes[$barcodePrefix] = 0;
	// 							endif;
	// 							$barcodes[$barcodePrefix] += $barcodeAmount;
	// 						endfor;
	// 					endif;
	// 				endif;

	// 				// Get the product object
	// 				// $product = wc_get_product( $product_id );
	// 			endforeach;

	// 			// echo '<pre>';
	// 			// print_r($barcodes);
	// 			// echo '</pre>';

				

	// 			$validated = [];
	// 			foreach($barcodes as $index => $barcode):
	// 				$scan_barcodes = $wpdb->get_results("SELECT * FROM $table_entries WHERE `order_id` = 0 AND SUBSTR( barcode, 1 , 2 ) = $index LIMIT $barcode");

	// 				// if(count($scan_barcodes) >= (int)$barcode):
	// 				if(17 >= (int)$barcode):
	// 					$validated[$index] = $scan_barcodes;

	// 					// break;
	// 				endif;

	// 				// echo '<pre>';
	// 				// print_r($scan_barcodes);
	// 				// echo '</pre>';

					
	// 			endforeach;
				

	// 			if(count($barcodes) == count($validated)):
	// 				$file = 'barcodes-'.$result->order_id.'.txt';

	// 				// echo '<pre>';
	// 				// print_r($validated);
	// 				// echo '</pre>';
	// 				// $IDs = [];
	// 				$current = '';
	// 				foreach($validated as $type => $entries):
	// 					foreach($entries as $entry):
	// 						$IDs[] = $entry->id;
	// 						// var_dump($entry);
	// 						if(!empty($entry->barcode)):
	// 							$current .= $entry->barcode."\n";

	// 							$wpdb->update($table_entries, ['order_id' => $result->order_id], ['id' => $entry->id]);
	// 						endif;
	// 					endforeach;
	// 				endforeach;

	// 				// echo '<pre>';
	// 				// print_r($IDs);
	// 				// echo '</pre>';

	// 				// $fields = ['order_id' => $result->order_id];
	// 				// $where = ['id' => $IDs];

					

	// 				// var_dump($current);

	// 				file_put_contents(WP_CONTENT_DIR . '/uploads/' . $file, $current);
	// 				$attachments = array(WP_CONTENT_DIR . '/uploads/'.$file);
	// 				$headers = 'From: My Name <info@peterbode.com>' . "\r\n";
	// 				wp_mail('peterbode1989@gmail.com', 'subject', 'message', $headers, $attachments);

	// 			endif;
	// 			// SUBSTRING ( MyColumn, 1 , 2 )


	// 		endforeach;
	// 	endif;



	// 	// $file = 'barcodes.txt';

	// 	// $current = "87094601630000\n";
	// 	// $current .= "97000471039800\n";


	// 	// file_put_contents(WP_CONTENT_DIR . '/uploads/' . $file, $current);

	// 	// $attachments = array(WP_CONTENT_DIR . '/uploads/barcodes.txt');
	// 	// // $attachments = array($myfile);
	// 	// $headers = 'From: My Name <info@peterbode.com>' . "\r\n";
	// 	// wp_mail('peterbode1989@gmail.com', 'subject', 'message', $headers, $attachments);

	// 	// unlink(WP_CONTENT_DIR . '/uploads/barcodes.txt');
	// 	exit();
	// }

	public static function sendBarcodes()
	{
		global $wpdb;
		$table = $wpdb->prefix.'barcodes';
		$table_entries = $wpdb->prefix.'barcodes_entries';

		$results = $wpdb->get_results("SELECT * FROM $table WHERE `resolved` = 0");
		if(!empty($results)):
			foreach($results as $result):
				$order = wc_get_order( $result->order_id );
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

						unlink(WP_CONTENT_DIR . '/uploads/'. $file);
					endif;
				endif;
			endforeach;
		endif;
	}
}