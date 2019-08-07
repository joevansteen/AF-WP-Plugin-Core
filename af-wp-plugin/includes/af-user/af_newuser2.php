<?php
/*
 * af_newuser2 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-16 JVS Begin test of new standalone PHP environment script
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_newuser2';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	$currentDir 	= '/temp/';
	$pathName 		= $currentDir.$target;
	$successful		= false;
	
	if (array_key_exists('user', $_REQUEST))
  		{
	  	$userName = $_REQUEST['user'];
		}
	else
		{
		$userName = '';
		}			
	if (array_key_exists('pswd', $_REQUEST))
  		{
	  	$userPswd = $_REQUEST['pswd'];
		}
	else
		{
		$userPswd = '';
		}			
	if (array_key_exists('pswd2', $_REQUEST))
  		{
	  	$userPswd2 = $_REQUEST['pswd2'];
		}
	else
		{
		$userPswd2 = '';
		}			

	if (empty($userName))
		{
		$diagnostic = 'An email address is required as the user ID.';	
		}
	else
	if ((empty($userPswd))
	 || (empty($userPswd2)))
		{
		$diagnostic = 'Both passphrase fields must have the passphrase entered.';	
		}
	else
	if ($userPswd != $userPswd2)
		{
		$diagnostic = 'Both passphrase fields must match.';	
		}
	else
		{		
		$successful = $anchor->user->register($userName, $userPswd);
		$diagnostic	= $anchor->user->getLastResultText(); 
		}
		
	if ($successful)
		{
		$anchor->dlgStepResponse->pageDialog = Dialog_Login; 
		include_once($stdScriptDir.AIR_Script_Login.'.php');		
		}
	else
		{
		/*
		 * cludge ...
		 */	
		$anchor->dlgStepResponse->pageDialog = Dialog_NewUser; 
		include_once($stdScriptDir.AIR_Script_NewUser.'.php');		
		}
?>