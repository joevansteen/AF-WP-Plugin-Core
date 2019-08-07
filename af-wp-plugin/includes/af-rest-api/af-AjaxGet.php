<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-AjaxGet';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

$.ajax( {
   url: Slug_API_Settings.root + 'wp/v2/users/',
   method: 'POST',
   beforeSend: function ( xhr ) {
       xhr.setRequestHeader( 'X-WP-Nonce', Slug_API_Settings.nonce );
},
data:{
email: 'test@example.com',
username: 'usertest',
       password: Math.random().toString(46).substring(8)
}
} ).done( function ( response ) {
console.log( response );
} )

//same code, but authentication is different
add_action( 'wp_enqueue_scripts', function() {
  wp_enqueue_scripts( 'user-editor', plugin_dir_url( __FILE__ ) .'user-editor.js', array('jquery')
);
  wp_localize_scripts( 'user-editor', 'Slug_API_Settings', array(
     'root' => esc_url_raw( rest_url() ),
     'nonce' => wp_create_nonce( 'wp_rest' ),
     'current_user_id' => (int) get_current_user_id()
) );
});

?>
