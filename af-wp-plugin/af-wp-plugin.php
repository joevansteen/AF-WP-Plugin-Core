<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://architectedfutures.net
 * @since             5.2019.0805
 * @package           AF_WP_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       AF WP Plugin Instance
 * Plugin URI:        http://architectedfutures.net/af-wp-plugin-uri/
 * Description:       This is the interface, gateway, boundary function which provides an Architected Futures EATSv5 API and capability set to a standard WordPress site. The architected interface allows for features from remote linking to full federated operational management, depending on how the WordPress site is configured as part of an EATSv5 federation.
 * Version:           5.2019.0805
 * Author:            Joe Van Steen
 * Author URI:        https://jvs.guru/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       af-wp-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 5.2019.0805 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AF_WP_PLUGIN_VERSION', '5.2019.0805' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-af-wp-plugin-activator.php
 *
 * Plugin activation and de-activation involve potentially complex and confidential
 * activities. For example awareness of passwords for administrative functions. For this
 * reason Activation and Deactivation are managed within our ITIL framework as independent
 * operations. This is the cassic managed housekeeping and control cycle:
 * - prepare
 * - do
 * - clean up
 *
 * This layer primarily exists as a virtual envelope for the remainer of the system. It is the
 * place we try to account for things we can handle gracefully.
 */
function activate_af_wp_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-af-wp-plugin-activator.php';
	AF_WP_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-af-wp-plugin-deactivator.php
 */
function deactivate_af_wp_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-af-wp-plugin-deactivator.php';
	AF_WP_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_af_wp_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_af_wp_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-af-wp-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    5.2019.0805
 */
function run_af_wp_plugin() {

	$plugin = new AF_WP_Plugin();
	$plugin->run();

}
run_af_wp_plugin();
