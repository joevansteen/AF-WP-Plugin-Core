<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af_RestApiPostElement';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Post output; chapter 2

$response = wp_remote_get( $url );
function get_json( $url ) {
//GET remote site
$response = wp_remote_get( $url );
//Checking for errors
if ( is_wp_error( $response ) ) {
return sprintf( 'Your URL %1s could not be retrieved', $url ); 
//GET only body
$data = wp_remote_retrieve_body( $response );
	}
//return if no error
if ( ! is_wp_error( $response ) ) { 
//Now, decode and return		
return json_decode( $response );
	}
}
?>