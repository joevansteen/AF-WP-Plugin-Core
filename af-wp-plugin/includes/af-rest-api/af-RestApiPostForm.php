<?php
/*
 * AF-REST API script Copyright (c) 2019 Joe Van Steen
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-REST API';
$myProcClass = 'af-RestApiPostForm';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//Basic function for setup

function my_rest_post_editor_form( ) {
$form = '
       <form id="editor">
        <input type="text" name="title" id="title" value="My title">
        <textarea id="content" ></textarea>
         <input type="submit" value="Submit" id="submit">
     </form>
     <div id="results">
     </div>
';
if (
is_user_logged_in() ) {
if ( user_can( get_current_user_id(), 'edit_posts' ) ) {
return $form;
}
else {
return __( 'You do not have permissions to do this.', 'my-rest-post-editor' );
}
}
else {
     return sprintf( '<a href="%1s" title="Login">%2s</a>', wp_login_url( get_permalink( get_
queried_object_id() ) ), __( 'You must be logged in to do this, please click here to log in.',
'my-rest-post-editor') );
}
}
?>