<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://architectedfutures.org
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
	 * The standard git boilerplatee provided our operational order, which we are adopting
	 * as WordPress current best practice. However, EATSv5 adapts the pattern with a
	 * strategy adopted from past experience. 
	 *
	 * We can assume is an optimized system we will be involved in orchestrating responses
	 * to client requests which will take one of three general forms. One form is very
	 * heavy, one form very light, and the other varies between the two.
	 *
	 * Stage 1 is always to figure out where the current call for action (WP wants do do something,
	 * it has a message to handle, and maybe we might be involved. What to do? Gear up, or snooze?
	 *
	 * Then, start the load and go stuff, but adjusted for only what we need to work with the
	 * current request, unless some other process or plugin in WP calls us for help in what they're
	 * doing. So, we always need our service support routines and framework. ..
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

		$this->set_process_strategy();
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Define the processing strategy for the current invokation of the instance by WordPress
	 * for the processing of the current request.
	 *
	 * Employs / Uses:
	 *
	 *	 AF_WP_Plugin_Xxxx class in order to ... set the domain and to register the hook with WordPress.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function set_process_strategy() {

		$af_wp_plugin_i18n = new AF_WP_Plugin_i18n();

		$this->loader->add_action( 'plugins_loaded', $af_wp_plugin_i18n, 'load_plugin_textdomain' );

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

		$af_wp_plugin_i18n = new AF_WP_Plugin_i18n();

		$this->loader->add_action( 'plugins_loaded', $af_wp_plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function define_admin_hooks() {

		$af_wp_plugin_admin = new AF_WP_Plugin_Admin( $this->get_af_wp_plugin(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $af_wp_plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $af_wp_plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 */
	private function define_public_hooks() {

		$af_wp_plugin_public = new AF_WP_Plugin_Public( $this->get_af_wp_plugin(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $af_wp_plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $af_wp_plugin_public, 'enqueue_scripts' );

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
/**
 * Close the module properly!
 */
 ?>