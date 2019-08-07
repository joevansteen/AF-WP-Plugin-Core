<?php
/*
 * AF_jpeg script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-13 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'AF_SuperZap';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

//<jpeg debug code>
	$source	= AF_IMAGES.'/cooltext46666849.jpg';
	$source	= AF_IMAGES.'/regCodeMask.jpg';
	header ('Content-Type: image/jpeg');
	$image = imagecreatefromjpeg($source);
	imagejpeg($image);
//<jpeg debug code>

?>