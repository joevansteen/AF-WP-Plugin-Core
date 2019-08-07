<?php

// If this file is called out of context, abort.
if ( ! defined( 'AF_WP_PLUGIN_VERSION' ) ) {
	die;
}

/**
 * This is the Activation Event Trigger Function which is fired to iniate plugin activation
 * by the host WordPress container environment.
 *
 * This class defines all code necessary to activate EATSv5 in a suitable WordPress container
 * on a suitable web hosting facility. 
 *
 * @since      5.2019.0805
 * @package    AF_WP_Plugin
 * @subpackage AF_WP_Plugin/includes
 * @author     Joe Van Steen <joe.vansteen@architectedfutures.org>
 */
class AF_WP_Plugin_Activator {

	/**
	 * AF_WP_Plugin initialization and activation facility.(use period)
	 *
	 * AF_WP_Plugin_Activator is an implementation of the WP Best Practice
	 * for plugin activation.
	 *
	 * @since    5.2019.0805
	 */
	public static function activate() {

	}

}
/**
 * Close the module properly!
 */
 ?>