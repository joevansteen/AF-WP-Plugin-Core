<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://architectedfutures.org
 * @since      5.2019.0805
 *
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/admin
 * @author     JVS <joe.vansteen@architectedfutures.org>
 */
class AF_WP_Plugin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 * @var      string    $af_wp_plugin    The ID of this plugin.
	 */
	private $af_wp_plugin;

	/**
	 * The version of this plugin.
	 *
	 * @since    5.2019.0805
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    5.2019.0805
	 * @param      string    $af_wp_plugin       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $af_wp_plugin, $version ) {

		$this->af_wp_plugin = $af_wp_plugin;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    5.2019.0805
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AF_WP_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AF_WP_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->af_wp_plugin, plugin_dir_url( __FILE__ ) . 'css/af-wp-plugin-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    5.2019.0805
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AF_WP_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AF_WP_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->af_wp_plugin, plugin_dir_url( __FILE__ ) . 'js/af-wp-plugin-admin.js', array( 'jquery' ), $this->version, false );

	}

}
/**
 * Close the module properly!
 */
 ?>