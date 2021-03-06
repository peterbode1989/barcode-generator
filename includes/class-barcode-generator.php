<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/peterbode1989/barcode-generator
 * @since      1.0.0
 *
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Barcode_Generator
 * @subpackage Barcode_Generator/includes
 * @author     Peter Bode <peterbode1989@gmail.com>
 */
class Barcode_Generator
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Barcode_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('BARCODE_GENERATOR_VERSION')) {
			$this->version = BARCODE_GENERATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'barcode-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_cronjob_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Barcode_Generator_Loader. Orchestrates the hooks of the plugin.
	 * - Barcode_Generator_i18n. Defines internationalization functionality.
	 * - Barcode_Generator_Admin. Defines all hooks for the admin area.
	 * - Barcode_Generator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-barcode-generator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-barcode-generator-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-barcode-generator-admin.php';

		/**
		 * The class responsible for defining all actions that occur relative to cronjobs.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'cronjob/class-barcode-generator-cronjob.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-barcode-generator-public.php';

		$this->loader = new Barcode_Generator_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Barcode_Generator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Barcode_Generator_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Barcode_Generator_Admin($this->get_plugin_name(), $this->get_version());

		// Create settings page
		$this->loader->add_action('admin_init', $plugin_admin, 'settings_init');
		$this->loader->add_action('admin_menu', $plugin_admin, 'wporg_options_page');

		// Create the custom tab / product properties
		$this->loader->add_filter('woocommerce_product_data_tabs', $plugin_admin, 'add_my_custom_product_data_tab', 99, 1);
		$this->loader->add_action('woocommerce_product_data_panels', $plugin_admin, 'add_my_custom_product_data_fields');
		$this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'woocommerce_process_product_meta_fields_save');
	}

	/**
	 * Register all of the hooks related to the cronjob functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cronjob_hooks()
	{
		$plugin_cronjob = new Barcode_Generator_Cronjob($this->get_plugin_name(), $this->get_version());

		// The global cronjob settings / schedule
		$this->loader->add_filter('cron_schedules', $plugin_cronjob, 'scheduleSendBarcodes');

		// The scheduled task hook
		$this->loader->add_action('task_barcodes', $plugin_cronjob, 'sendBarcodes');

		// Run the system in debug mode
		if(!empty($_GET['page']) && $_GET['page'] == 'barcode-generator'
			&& !empty($_GET['debug']) && $_GET['debug'] == 1):
			$this->loader->add_action('init', $plugin_cronjob, 'sendBarcodes');
		endif;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Barcode_Generator_Public($this->get_plugin_name(), $this->get_version());

		// Create the WC hook
		$this->loader->add_action('woocommerce_thankyou', $plugin_public, 'wc_hook', 10, 1);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Barcode_Generator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
