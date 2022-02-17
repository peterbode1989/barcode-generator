<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/peterbode1989/barcode-generator
 * @since      1.0.0
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/includes
 * @author     Peter Bode <peterbode1989@gmail.com>
 */
class Barcode_Generator_Deactivator
{
	/**
	 * Remove stuff what no longer is needed.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		// Remove the scheduled event
		wp_clear_scheduled_hook('task_barcodes');
	}
}
