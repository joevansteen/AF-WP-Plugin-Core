<?php
/*
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-CORE';
$myProcClass = 'af-GetTaxonomyTerms';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//GET request 
$url = rest_url( 'wp/v2/posts/1/terms/category' );
$response = wp_remote_request( $url, array(
     'method' => 'GET'
)
);
$body = wp_remote_retrieve_body( $response );
if ( ! is_wp_error( $body ) ) {
//Decoding 
$terms = json_decode( $body );
$terms = array_combine( wp_list_pluck( $terms, 'slug' ), wp_list_pluck( $terms, id ) );
}

//Checking for the taxonomy terms
if ( isset( $terms[ 'example' ] ) ) {
	
	 //get term ID
$term_id = $terms['example'];
	
	 //Adding ID to URL
$term_url = $url . '/' . $term_id;
//DELETE request
$headers = array(
'headers' => array(
'Authorization' => 'Basic ' . base64_encode( 'admin : password' ),
		)
	);
$response = wp_remote_request( $term_url,
		array(
'method' => 'DELETE',
'headers' => $headers
		)
	);
}

?>
