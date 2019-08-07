<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-AjaxPost';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

jQuery( document ).ready(function( $ ) {
   $.ajax( {
       url: Slug_API_Settings.root + 'wp/v2/users/' + Slug_API_Settings.current_user_id + '?context=edit',
method: 'GET',
       beforeSend: function ( xhr ) {
           xhr.setRequestHeader( 'X-WP-Nonce', Slug_API_Settings.nonce );
}
} ).done( function ( user ) {
       $( '#username' ).html( '<p>' + user.name + '</p>' );
$( '#email' ).val( user.email );
} );
});

jQuery( document ).ready(function( $ ) {
var get_user_data;
(get_user_data = function () {
       $.ajax( {
           url: Slug_API_Settings.root + 'wp/v2/users/' + Slug_API_Settings.current_user_id +
'?context=edit',
method: 'GET',
           beforeSend: function ( xhr ) {
               xhr.setRequestHeader( 'X-WP-Nonce', Slug_API_Settings.nonce );
}
} ).done( function ( user ) {
           $( '#username' ).html( '<p>' + user.name + '</p>' );
$( '#email' ).val( user.email );
} );
})();
   $( '#profile-form' ).on( 'submit', function(e) {
       e.preventDefault();
       $.ajax( {
           url: Slug_API_Settings.root + 'wp/v2/users/' + Slug_API_Settings.current_user_id,
           method: 'POST',
           beforeSend: function ( xhr ) {
               xhr.setRequestHeader( 'X-WP-Nonce', Slug_API_Settings.nonce );
},
data:{
email: $( '#email' ).val()
}
} ).done( function ( response ) {
console.log( response )
} )
});
});

?>
