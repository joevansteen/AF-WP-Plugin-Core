<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-RestApiGetMessage';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Copy post from remote site; chapter 2

function insert_post_from_json( $post ) {
//checking to see if array or object; because we can work only with array
if ( is_array( $post ) || ( is_object( $post ) && ! is_wp_error( $post ) ) ) {
//ensure $post is an array; or force typecast
$post = (array) $post;
}
else {
return sprintf( '%1s must be an object or array', __FUNCTION__ );
}
//Create new array for conversion
//Set ID as post_id to try and use the same ID; note that leaving ID as ID would //UPDATE an existing post of the same ID
$convert_keys = array(
     'title' => 'post_title',
     'content' => 'post_content',
     'slug' => 'post_name',
     'status' => 'post_status',
     'parent' => 'post_parent',
     'excerpt' => 'post_excerpt',
     'date' => 'post_date',
     'type' => 'post_type',
     'ID' => 'post_id',
);
//copy API response and unset old key
foreach ( $convert_keys as $from => $to ) {
if ( isset( $post[ $from ] ) ) {
        		$post[ $to ] = $post[ $from ];
        		unset( $post[ $from ] );
}
}
//remove all keys of $post that are disallowed and convert objects (if any) to strings
$allowed = array_values( $convert_keys );
foreach( $post as $key => $value ) {
if( ! in_array( $key, $allowed ) ) {
unset( $post[ $key ] );
} else{
if ( is_object( $value ) ) {
$post[ $key ] = $value->rendered;
}
}
}
//All done. Create post and return its ID
return wp_insert_post( $post );
}


?>