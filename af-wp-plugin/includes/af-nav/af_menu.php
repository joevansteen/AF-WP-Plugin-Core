<?php
/*
 * af_menu script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-19 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_menu';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	if ($sysDiag)
		{
	 	echo '<span>af_menu found dialog['.$dialog.']<br/></span> ';
	 	echo '<span>af_menu found request['.$request.']<br/></span> ';
	 	echo '<span>af_menu found target['.$target.']<br/></span> ';
		}

	$dispatchFunction = null;
	if ($request == AIR_Action_Redirect)
		{
	  	if (array_key_exists($target, $processDefinitions))
	  		{
			if ($sysDiag)
				{
			 	echo '<span>redirect dispatch!<br/></span> ';
				}
	  		$dispatchFunction = $processDefinitions[$target];
	  		}
		}

  	if (empty($dispatchFunction))
  		{
		/*
		 * Attempt to resolve dispatch request at the global level
		 */
	  	if (array_key_exists(Dialog_Global, $dlgWorkflowMap))
	  		{
	  		$workflowSpec = $dlgWorkflowMap[Dialog_Global];
			/*
			 * Resolve the dispatch array for the request type
			 */
		  	if (($request != AIR_Action_Redirect)
		  	 && (array_key_exists($request, $workflowSpec)))
		  		{
		  		$dispatchFunction = $workflowSpec[$request];
		  		}
	  		}
  		}

  	if (empty($dispatchFunction))
  		{
		/*
		 * Attempt to resolve dispatch request at the dialog level
		 */
	  	if (array_key_exists($target, $dlgWorkflowMap))
	  		{
	  		$workflowSpec = $dlgWorkflowMap[$target];
	  		}
	  	else
	  		{
			/*
			 * Resolve dispatch request at the default level
			 */
		  	if (! array_key_exists(Dialog_Default, $dlgWorkflowMap))
		  		{
			 	echo '<span>No workflow to resolve action ['.$request.'] to target ['.$target.'] in dialog ['.$dialog.']<br/></span> ';
				trigger_error('No workflow to resolve action ['.$request.'] to target ['.$target.'] in dialog ['.$dialog.']', E_USER_NOTICE);
	  			exit();
	  			}
	  		$workflowSpec = $dlgWorkflowMap[Dialog_Default];
	  		}
  				/*
		 * Resolve the dispatch array for the request type
		 */
	  	if (($request != AIR_Action_Redirect)
	  	 && (array_key_exists($request, $workflowSpec)))
	  		{
	  		$dispatchFunction = $workflowSpec[$request];
	  		}
	  	else
	  		{
		  	if ($request != AIR_Action_Redirect)
		  		{
				trigger_error('No workflow rule for action ['.$request.'] in dialog ['.$dialog.']', E_USER_NOTICE);
				}
	  		$dispatchFunction = $workflowSpec[$defaultKey];
	  		}
	  	}

//echo 'controller(0)<br/>';
	$anchor->dlgStepResponse->pageDialog = $dispatchFunction['procIdent'];
	if ($sysDiag)
		{
	 	echo '<span>loading '.$stdScriptDir.$dispatchFunction['ReplyProcessor'].'.php'.'<br/></span> ';
		}
	include_once($stdScriptDir.$dispatchFunction['ReplyProcessor'].'.php');

?>