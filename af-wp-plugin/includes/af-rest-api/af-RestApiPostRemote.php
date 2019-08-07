<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af_RestApiPostRemote';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Create post on remote site; chapter 2

//Authentication
$url = 'http://example.com/wp-json/wp/v2/posts/1';
$body = get_json( $url );
if ( is_object( $post ) ) {
	$body = $post;
	$headers = array (
		'Authorization' => 'Basic ' . base64_encode( 'admin' . ':' . 'password' ),
	);
$remote_url = 'http://example-remote.com/wp-json/wp/v2/posts';
}
	
$headers = array ('Authorization' => 'Basic ' . base64_encode( 'admin' . ':' . 'password' ),
	);

//Copying response 
$response = wp_remote_post( $remote_url, array (
		'method'      => 'POST',
		'timeout'     => 45,	
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
		'body'        => json_encode( $post ),
		'cookies'     => array ()
		)
	);
	
//Checking if error occurred. 
if ( is_wp_error( $response ) ) {	
	$error_message = $response->get_error_message();
	echo sprintf(  '<p>Error: %1s</p>', $error_message );
	}
else {
	echo 'Response:<pre>';	
	print_r( $response );
	echo '</pre>';
	}
}
else {	
	$error_message = 'Data invalid!';
	echo sprintf(  '<p>Error: %1s</p>', $error_message );
}
?>
