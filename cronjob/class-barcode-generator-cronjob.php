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
	 * Determine if the system is running in debug mode
	 * By default it is disabled
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @var      boolean    $debug    If the system is running in debug mode.
	 */
	private static $debug = false;

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

		// Enable/disable debug mode
		if( !empty($_GET['debug']) && $_GET['debug'] == 1 ):
			self::$debug = true;
		endif;
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

		if( self::$debug ):
			echo '<b>'.__('Start debug mode', self::$plugin_name).'</b><br><br>';
		endif;

		$results = $wpdb->get_results("SELECT * FROM $table WHERE `resolved` = 0");
		if( self::$debug ):
			echo sprintf(__('The amount of orders to process are %1$s', self::$plugin_name), count($results));
			
		endif;

		if(!empty($results)):
			foreach($results as $result):
				$order = wc_get_order( $result->order_id );

				// echo '<pre>';
				// var_dump($order->is_paid());
				// echo '</pre>';
				// exit();

				if( self::$debug ):
					echo '<hr>';
					echo __('Order found with the ID: ', self::$plugin_name);
					echo $result->order_id;
					echo '<br>';
				endif;


				if( !$order->is_paid() ):
					if( self::$debug ):
						echo __('Order isn\'t paid, and will be skipped for this iteration.', self::$plugin_name);
						echo '<br>';
					endif;

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

							if( self::$debug ):
								echo sprintf(__('Cart contains %1$sx %2$s', self::$plugin_name), $quantity, $item['name']);
								echo '<br>';
							endif;

							$barcodeAmount = get_post_meta( $product_id, '_barcode_amount', true );
							$barcodePrefix = get_post_meta( $product_id, '_barcode_prefix', true );

							for($i =0;$i < $quantity; $i++):
								if(!array_key_exists($barcodePrefix, $barcodes)):
									$barcodes[$barcodePrefix] = 0;
								endif;
								$barcodes[$barcodePrefix] += $barcodeAmount;
							endfor;

							if( self::$debug ):
								echo sprintf(__('This requires %1$s barcodes to resolve, with the prefix %2$s', self::$plugin_name), $barcodes[$barcodePrefix], $barcodePrefix);
								echo '<br>';
							endif;
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
					if( self::$debug ):
						echo '<span style="color:green">'.__('There are enough barcodes in stock to resolve', self::$plugin_name).'</span>';
						echo '<br>';
					endif;

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

					if( self::$debug ):
						echo sprintf(__('These barcodes are being used for this order: %1$s', self::$plugin_name), str_replace('\n',', ', $current));
						echo '<br>';
					endif;

					file_put_contents(WP_CONTENT_DIR . '/uploads/' . $file, $current);
					$attachments = array(WP_CONTENT_DIR . '/uploads/'.$file);

					if( self::$debug ):
						echo sprintf(__('Attachment has been created on: %1$s', self::$plugin_name), current($attachments));
						echo ' - '.(file_exists(current($attachments)) ? __('File exists', self::$plugin_name) : __('File doesn\'t exist', self::$plugin_name));
						echo '<br>';
					endif;

					$headers = 'From: '.get_option('blogname').' <'.get_option('admin_email').'>' . "\r\n";

					if( self::$debug ):
						echo '<br><b>'.__('Mail creation started').'</b><br>';
						echo sprintf(__('Mail header - %1$s', self::$plugin_name), htmlspecialchars($headers)).'<br>';
					endif;

					$message = 'Beste '.$order->get_billing_first_name().' '. $order->get_billing_last_name().','. "\r\n\r\n";
					$message .= get_option(self::$plugin_name.'_desc');

					if( self::$debug ):
						echo __('Mail message -', self::$plugin_name).'<br><i>';
						echo nl2br($message);
						echo '</i><br>';
					endif;

					$mail = wp_mail($order->get_billing_email(), get_option(self::$plugin_name.'_title'), $message, $headers, $attachments);
					if($mail):
						$wpdb->update($table, ['resolved' => 1], ['order_id' => $result->order_id]);
						
						$order->update_status( 'completed' );
						unlink(WP_CONTENT_DIR . '/uploads/'. $file);

						if( self::$debug ):
							echo '<br>';
							echo __('Order status update: completed', self::$plugin_name).'<br>';
							echo __('Attachment has been removed', self::$plugin_name).'<br>';
						endif;
					else:
						// debug $mail;
						echo '<pre>';
						print_r($mail);
						echo '</pre>';

						if( self::$debug ):
							echo __('Mail couldn\'t be send. See the error below.', self::$plugin_name).'<br>';
							echo '<pre>';
							print_r($mail);
							echo '</pre>';
						endif;
					endif;
				else:
					if( self::$debug ):
						echo '<span style="color:red">'.__('There are not enough barcodes in stock to resolve, will try again later.', self::$plugin_name).'</span>';
						echo '<br>';
					endif;
				endif;

				if( self::$debug ):
					echo '<br>';
				endif;
			endforeach;
		endif;

		if( self::$debug ):
			echo '<Br><b>'.__('End debug mode', self::$plugin_name).'</b>';
			exit();
		endif;
	}
}
