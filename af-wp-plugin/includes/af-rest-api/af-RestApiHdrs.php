<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af_RestApiHdrs';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Setting the Domain 

remove_filter (	'rest_pre_serve_request',	'rest_send_cors_headers'	);
	
add_filter (	'rest_pre_serve_request',
	function($value	)	{
		$origin = get_http_origin();
		if ( $origin && in_array( $origin, array(
						'jvs.guru', 
						'148avendia.guru', 
						'architectedfutures.org', 
						'architectedfutures.us', 
						'architectedfutures.guru' ) ) ) {
			header(	'Access-Control-Allow-Origin:	'	.	esc_url_raw(	$origin	)	);
			header(	'Access-Control-Allow-Methods:	POST,	GET,	OPTIONS,	PUT,	DELETE'	);
			header(	'Access-Control-Allow-Credentials:	true'	);
		}
		return $value;
	});
?>