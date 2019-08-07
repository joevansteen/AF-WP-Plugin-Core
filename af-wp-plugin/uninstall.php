<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://architectedfutures.org
 * @since      5.2019.0805
 *
 * @package    AF_WP_Plugin
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
<?php


// Get access to global WordPress wpdb class
global $wpdb;

// Check if site is configured for network installation
if ( is_multisite() ) {
	if ( !empty( $_GET['networkwide'] ) ) {
		$start_blog = $wpdb->blogid;

		// Get blog list and cycle through all blogs under network
		$blog_list = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
		foreach ( $blog_list as $blog ) {
			switch_to_blog( $blog );

			// Call function to delete bug table with prefix
			af_bt_drop_table( $wpdb->get_blog_prefix() );
		}
		switch_to_blog( $start_blog );
		return;
	}	
}

af_bt_drop_table( $wpdb->prefix );

function af_bt_drop_table( $prefix ) {
	global $wpdb;
	$wpdb->query( 'DROP TABLE ' . $prefix . 'af_bug_data' );
}

delete_option(AF_CORE_ID);

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}af_core_post_attachments");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpqpa_post_attachments");

/**
 * Close the module properly!
 */
 ?>