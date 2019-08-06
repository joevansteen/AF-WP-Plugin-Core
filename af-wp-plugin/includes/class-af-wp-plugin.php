<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://architectedfutures.net
 * @since      5.2019.0805
 *
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/includes
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
 * @since      5.2019.0805
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/includes
 * @author     JVS <joe.vansteen@architectedfutures.org>
 */
class AF_WP_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    5.2019.0805
	 * @access   protected
	 * @var      AF_WP_Plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    5.2019.0805
	 * @access   protected
	 * @var      string    $af_wp_plugin    The string used to uniquely identify this plugin.
	 */
	protected $af_wp_plugin;

	/**
	 * The current version of the plugin.
	 *
	 * @since    5.2019.0805
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
	 * @since    5.2019.0805
	 */
	public function __construct() {
		if ( defined( 'AF_WP_PLUGIN_VERSION' ) ) {
			$this->version = AF_WP_PLUGIN_VERSION;
		} else {
			$this->version = '5.2019.0805';
		}
		$this->af_wp_plugin = 'af-wp-plugin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - AF_WP_Plugin_Loader. Orchestrates the hooks of the plugin.
	 * - AF_WP_Plugin_i18n. Defines internationalization functionality.
	 * - AF_WP_Plugin_Admin. Defines all hooks for the admin area.
	 * - AF_WP_Plugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-af-wp-plugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-af-wp-plugin-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-af-wp-plugin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-af-wp-plugin-public.php';

		$this->loader = new AF_WP_Plugin_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the AF_WP_Plugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new AF_WP_Plugin_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new AF_WP_Plugin_Admin( $this->get_af_wp_plugin(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new AF_WP_Plugin_Public( $this->get_af_wp_plugin(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    5.2019.0805
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     5.2019.0805
	 * @return    string    The name of the plugin.
	 */
	public function get_af_wp_plugin() {
		return $this->af_wp_plugin;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     5.2019.0805
	 * @return    AF_WP_Plugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     5.2019.0805
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
