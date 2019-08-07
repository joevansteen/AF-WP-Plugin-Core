<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-RestApiGetCache';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Fetching and returning the variables

add_filter (	rest_pre_dispatch',	'rest_cache_get',	10,	2	);

function rest_cache_get( $result, $server ) {
	//Checking to see if rebuild	callback exists,	if it does not then return unmodified.
	
	if	(	!	function_exists('rest_cache_rebuild')	)	{
		return $result;
	}
	
	//get the REST request and hash it to make the transient key
	$request_uri	=	$_SERVER[	'REQUEST_URI'	];
	$key	=	md5(	$request_uri	);	
	
	//return the cache or build cache
	$result	=		transient(	__FUNCTION__	.	$key		)
		->updates_with(	'rest_cache_rebuild',	array(	$server		)	)
		->expires_in(	600	)	
		->get();
		
	return $result;
	}

?>