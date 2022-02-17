<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/peterbode1989/barcode-generator
 * @since      1.0.0
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/includes
 * @author     Peter Bode <peterbode1989@gmail.com>
 */
class Barcode_Generator_Activator {

	/**
	 * This created the required databases.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		
		$table_name = $wpdb->prefix . 'barcodes';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`order_id` bigint(20) NOT NULL,
			`resolved` tinyint(1) NOT NULL,
			`date_created` datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			UNIQUE (`order_id`),
			PRIMARY KEY (`id`)
		) $charset_collate;";

		$table_name_entries = $wpdb->prefix . 'barcodes_entries';
		$sqlEntries = "CREATE TABLE IF NOT EXISTS $table_name_entries (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`order_id` bigint(20) NOT NULL,
			`barcode` bigint(13) NOT NULL,
			`date_created` datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			UNIQUE (`barcode`),
			PRIMARY KEY (`id`)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		dbDelta( $sqlEntries );
	}

}
