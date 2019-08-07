<?php
/*
 * af_fileedit3 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-16 JVS Begin test of new standalone PHP environment script
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_logout';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	$currentDir 	= '/temp/';
	$pathName 		= $currentDir.$target;
				
  	if ($anchor->user->isLoggedIn())
  		{
		$anchor->user->logout();
		}
		
	include_once($stdScriptDir.AIR_Script_Login.'.php');		
?>