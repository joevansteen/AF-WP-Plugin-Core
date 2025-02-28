<?php
/*
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-CORE';
$myProcClass = 'af-GetSiteTaxonomies';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

// Ch 3, pg 50
/*
 Check $sites, which may only exist on multisite.
 Iterates through a sety of sites, and gets taxonomies
 */
$sites = wp_get_sites();
$the_list = arr();
foreach( $sites as $site ) {
$response = wp_remote_get( get_rest_url( $site->site_id, 'wp/v2/terms/categories' ) );
if ( ! is_wp_error( $response ) ) {
$terms = json_decode( wp_remote_retrieve_body( $response ) );
$term_list = arr();
foreach( $terms as $term ) {	
$term_list[] = sprintf( '<li><a href="%1s">%2s</a></li>', esc_url( $term->link
),$term->name );
		}		
if ( ! empty( $term_list ) ) {
$site_info = get_blog_details( $site->site_id );	
$term_list = sprintf( '<ul>%1s</ul>', implode( $term_list ) );
$the_list[] = sprintf( '<li><a href="%1s">%2s</a><ul>%3s</ul>', $site_info-
>siteurl, $site_info->blogname, $term_list );
		}
		
	}
}
if ( ! empty( $the_list ) ) {
echo sprintf( '<ul>%1s</ul>', implode( $the_list ) );
}
//end of code

/**
//Same code, but not multisite. (this will not run on WPMU)
$response = wp_remote_get( rest_url( 'wp/v2/terms/categories' ) );
if ( ! is_wp_error( $response ) ) {
$terms = json_decode( wp_remote_retrieve_body( $response ) );
$term_list = array();
foreach( $terms as $term ) {
$term_list[] = sprintf( '<li><a href="%1s">%2s</a></li>', esc_url( $term->link
),$term->name );
	}
if ( ! empty( $term_list ) ) {	
echo sprintf( '<ul>%1s</ul>', implode( $term_list ) );
	}
}

*/
?>


