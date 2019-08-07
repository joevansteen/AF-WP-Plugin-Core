<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af_RestApiImageFormSubmit';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Form submission 

var imageForm = $( '#image-form' ),
    fileInput = $('#file'),
    formData = new FormData();
 
imageForm.on( 'submit',
	function( m ) {
		e.preventDefault();
     
    formData.append( 'file', fileInput[0].files[0] );
     
    $.ajax({
        url: 'http://example/wp-json/wp/v2/media',
        method: 'POST',
        data: formData,
        crossDomain: true,
        contentType: false,
        processData: false,
        beforeSend: function ( xrh ) {
        	xhr.setRequestHeader( 'Authorization', 'Basic username:password' );
	        },
        success: function( data ) {
					console.log( data );
	        },
        error: function( error ) {
					console.log( error );
	        }
  	});
	});
?>