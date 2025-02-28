<?php
/*
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-CORE';
$myProcClass = 'af-AddTaxonomyTerm';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

function add_term_to_post( $post_id, $taxonomy, $term_slug, $term_name, $auth_header ) {
//First, finding post type and if post exists
$post_type = get_post_type( $post_id );
if ( false == $post_type ) {
		return;
	}
if ( 'post' == $post_type ) {		
$post_type = 'posts';
	}
$term_url = rest_url( 'wp/v2/terms/' . $taxonomy );
//GET request for ID
$response = wp_remote_request( $term_url,
		array(
  'method' => 'GET',
		)
	);
$body = wp_remote_retrieve_body( $response );
if ( ! is_wp_error( $body ) ) {
$terms = json_decode( $body );
if ( ! empty( $terms ) ) {
$term_id = false;			
foreach ( $terms as $term ) {
  if ( $term->slug == $term_slug ) {
  $term_id = $term->id;
					 break;
				 }
			 }
  //Auth headers for POST request
  $headers['Authorization'] = $auth_header;
//If term doesn't exist, we create it
if ( ! $term_id ) {		
//PUT term slug and name in request
$body = array(
  'slug' => sanitize_title( $term_slug ),
  'name' => $term_name
		 );
  //POST request
  $create_term_url = rest_url( 'wp/v2/terms/' . $taxonomy );	
				 //Create term
$response = wp_remote_request( $create_term_url,
					 array(
  'method'  => 'POST',
  'headers' => $headers,
  'body'    => $body,
					 )
				 );
//wp_die( print_r( $response ) );
  //Finding term ID		
$body = wp_remote_retrieve_body( $response );		
if ( ! is_wp_error( $body ) ) {
						$term = json_decode( $body );
if ( is_object( $term ) && isset( $term->id ) ) {
$term_id = $term->id;
						}
				 }
			 }			
//Adding term ID to post
if ( $term_id ) {
				 //Create URL for request
$post_term_url = rest_url( 'wp/v2/' . $post_type . '/' . $post_id . '/
terms/' . $taxonomy . '/' . $term_id );
  //POST request		
$response = wp_remote_request( $post_term_url,
					 array(
  'method'  => 'POST',	
  'headers' => $headers
					 )
				 );
do_action( 'slug_post_term_update', $response, $post_id, $post_type,
$taxonomy, $term_slug )
			 }
		}
		return $term_id;	
	}
}
?>
