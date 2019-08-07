<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://architectedfutures.org
 * @since      5.2019.0805
 *
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      5.2019.0805
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/includes
 * @author     JVS <joe.vansteen@architectedfutures.org>
 */
class AF_WP_Plugin_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    5.2019.0805
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'af-wp-plugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
/**
 * Close the module properly!
 */
 ?>