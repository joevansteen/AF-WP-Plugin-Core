<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af_RestApiPostMessage';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//POST meta fields; chapter 2

//GET URL request
$url = rest_url( 'wp/v2/posts/1/meta' );
//ADD basic headers for authentication
$headers = array (	
'Authorization' => 'Basic ' . base64_encode( 'admin' . ':' . 'password' ),
);
//ADD meta value to body
$body = array(
'key' => 'water_color',
'value' => 'blue'
);
//POST request
$response = wp_remote_request( $url, array(	
'method' => 'POST',
'headers' => $headers,
'body' => $body
	)
);
//if no error, we GET ID of meta key
$body = wp_remote_retrieve_body( $response );
if ( ! is_wp_error( $body ) ) {
$body = json_decode( $body );
$meta_id = $body->id;
echo $body->value;
if ( $meta_id ) {
		 //ADD meta ID to URL
$url .= '/' . $meta_id;
		 //SEND value
$body = array(
'value' => 'blue'
		);
$response = wp_remote_request( $url, array(
'method' => 'POST',
			)
		);
'headers' => $headers,
'body' => $body
//if no error, then echo the value
$body = wp_remote_retrieve_body( $response );
if ( ! is_wp_error( $body ) ) {		
$body = json_decode( $body );
echo $body->value;
		}
	}
}
?>