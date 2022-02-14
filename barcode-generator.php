<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/peterbode1989/barcode-generator
 * @since             1.0.0
 * @package           Barcode_Generator
 *
 * @wordpress-plugin
 * Plugin Name:       Barcode Generator
 * Plugin URI:        https://github.com/peterbode1989/barcode-generator
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Peter Bode
 * Author URI:        https://github.com/peterbode1989/barcode-generator
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       barcode-generator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BARCODE_GENERATOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-barcode-generator-activator.php
 */
function activate_barcode_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-barcode-generator-activator.php';
	Barcode_Generator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-barcode-generator-deactivator.php
 */
function deactivate_barcode_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-barcode-generator-deactivator.php';
	Barcode_Generator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_barcode_generator' );
register_deactivation_hook( __FILE__, 'deactivate_barcode_generator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-barcode-generator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_barcode_generator() {

	$plugin = new Barcode_Generator();
	$plugin->run();

}
run_barcode_generator();
