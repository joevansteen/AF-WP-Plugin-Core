<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-RestApiFormSubmit';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Sending data using forms 

var postForm = $( '#post-form' );
 
postForm.on( 'submit',
	function( f ) {
		e.preventDefault();
     
    $.ajax({
				url: 'http://example/wp-json/wp/v2/posts',
        method: 'POST',
        data: postForm.serialize(),
        crossDomain: true,
        beforeSend: function ( xrh ) {
					xhr.setRequestHeader( 'Authorization', 'Basic username:password' );
	        },
				success: function( data ) {
					console.log( data );
	        }
		});
		
	});

?>