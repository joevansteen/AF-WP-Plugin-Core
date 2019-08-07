<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-RestApiGetMeta';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//GET post meta fields; chapter 2

function get_post_meta_clbk( $object, $field_name, $request ) {
return get_post_meta( $object[ 'id' ], $field_name );
}
function update_post_meta_clbk( $value, $object, $field_name ) {
return update_post_meta( $object[ 'id' ], $field_name, $value );
}

add_action( 'rest_api_init', function() {
register_api_field( 'painter',
'water_color',
array(
'get_callback'  => 'get_post_meta_clbk',
'update_callback' => 'update_post_meta_clbk',
'schema'  => null,
)
);
});

?>