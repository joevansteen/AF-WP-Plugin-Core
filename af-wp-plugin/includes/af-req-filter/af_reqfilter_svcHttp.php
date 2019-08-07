<?php
/*
 * af_reqfilter_svcHttp script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-27 JVS Bootstrap from af_dialogdecode
 *
 * This module is a first prototype version of a dynamically
 * loaded and invoked processing module for execution within
 * a PHP processing environment.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ReqFilterSvcHttp';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ReqFilterSvcHttp extends C_AirProcModBase {

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *******/
	function __construct(&$air_anchor)
		{
		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		// Propogate the construction process
		parent::__construct($air_anchor);
		}

	/***************************************************************************
	 * ProcMod_Main
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
		parent::initialize($procContext, $baseMsg, $procMsg);
		$this->initResultMsg();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$msgDiag		= 'driver object ['.$this->myMsgObject.']';
			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			}

		$validated = $this->validateContext();

			switch ($this->myMsgObject)
				{
				case Service_HttpRequest:
					$this->procHttpDecode();
					$this->publishMenuResultMsgInfo();
					break;
				default:
					$this->anchor->abort('Unrecognized menu decode object ['.$this->myMsgObject.']');
					throw new Exception('Unrecognized message object');
				}

			$this->postResultMsg();

		}

	/***************************************************************************
	 * procHttpDecode
	 *******/
	function procHttpDecode()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 *
		 */
		$strategyRule = $this->evaluateRequest();
		if (empty($strategyRule))
			{
			$msgDiag		= 'Unable to formulate WF strategy';
			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			}
		else
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$msgDiag		= 'WF strategy dlgAction  = ['.$strategyRule['dlgAction'].']'
											. ' procModule = ['.$strategyRule['procModule'].']'
											. ' procAction = ['.$strategyRule['procAction'].']';
			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			}

		if (empty($strategyRule))
			{
			$encodeObject	= '';
			$encodeAction	= '';
			$encodeVers		= '';
			}
		else
			{
			$encodeObject	= $strategyRule['procModule'];
			$encodeAction	= $strategyRule['procAction'];
			$encodeVers		= $strategyRule['procVers'];
			}

		/*
		 * Determine if there were any elements identified in the dialog context
		 * that need to be picked up from the message and passed to the processing
		 * module.
		 */
		$collectionSize = $this->procContext->getDataCollectionItemCount('dlgVar');
		if ($collectionSize > 0)
			{
			/*
			 * Elements identified on the dialog session to be picked up.
			 */
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$collectionItemNode = $this->procContext->getDataCollectionItem('dlgVar', $i);
				$varName		= $collectionItemNode->getChildContentByName('Name');
				$varType		= $collectionItemNode->getChildContentByName('Type');

				switch ($varType)
					{
					case 'Item':
						$this->resultMsg->putMessageData($varName, $this->procMsg->getMessageData($varName));

						if (($this->anchor->trace())
						 || ($this->anchor->traceMsgDataFlow()))
							{
							$msgDiag		= 'dlgVar type = ['.$varType.'] name = ['.$varName.'] value = ['.$this->procMsg->getMessageData($varName).']';
							$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
							}
						break;
					case 'Collection':
						$varCollectionSize = $this->procMsg->getDataCollectionItemCount($varName);

						if (($this->anchor->trace())
						 || ($this->anchor->traceMsgDataFlow()))
							{
							$msgDiag		= 'dlgVar type = ['.$varType.'] name = ['.$varName.'] count = ['.$varCollectionSize.']';
							$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
							}
						if ($varCollectionSize > 0)
							{
							for ($j = 0; $j < $varCollectionSize; $j++)
								{
								$varCollectionNode = $this->procMsg->getDataCollectionItem($varName, $j);
								$varNodeValue		 = $this->procMsg->getDataCollectionItemContent($varName, $j);

//								$msgDiag		= 'dlgVar '.$varName.' content = ['.$varNodeValue.']';
//								$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);

								$newNode = $this->resultMsg->createTextElement($varName, $varNodeValue);
								$this->resultMsg->createNewDataCollectionItem($newNode);
								}
							}
						break;
					}
				}
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * evaluateRequest
	 *
	 * This function evaluates the input request and determines the general
	 * processing strategy for handling the request.
	 *******/
	function & evaluateRequest()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

  		$redirectTarget	= array();
		$dispatchFunction = & $redirectTarget;

		/*
		 * Create array of key strategy variables
		 */
		$fcnVarList = array();
		$fcnVarList['dialog']	= ''; // dialog identifier for where we are
		$fcnVarList['request']	= ''; // action request for this dialog step
		$fcnVarList['target']	= ''; // target of the action, assuming some selection is required
		$fcnVarList['auth']		= ''; // authorization for the action

		$dlgWorkflowMap		= $this->getDlgWorkflowMap();
		$processDefinitions	= $this->anchor->getStdVar('processDefinitions');

		/*
		 * Get the primary strategy variables. For each variable:
		 *		- post the key/value pair to the TxnSpec array
		 *		- instantiate the name/value pair as a local variable
		 *		- store the assignment value in the array
		 */
		foreach ($fcnVarList as $varKey => $varData)
			{
			if (array_key_exists($varKey, $_REQUEST))
				{
				$varValue	= $_REQUEST[$varKey];
				}
			else
				{
				$varValue	= '';
				}

			$this->resultMsg->putMessageData($varKey, $varValue);
			$this->myTxnSpec[$varKey]	= $varValue;
			$$varKey							= $varValue;
		  	$fcnVarList[$varKey]			= $varValue;

			if ($this->anchor->debugCoreFcns())
				{
				$msgDiag		= 'WF strategy var '.$varKey.' is: '.$$varKey;
				$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
				}
			}

		/*
		 * Diagnostics
		 */
		if (($this->anchor->debugCoreFcns())
		 || ($this->anchor->trace()))
			{
			$msgDiag		= 'WF strategy vars '	. ' dialog  = ['.$dialog.']'
															. ' request = ['.$request.']'
															. ' target  = ['.$target.']'
															. ' auth    = ['.$auth.']';
			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			}

		/*
		 * Develop the dispatch and service strategy based on the request.
		 */

		/*
		 * 1st evaluation is for a redirection request.
		 *
		 */
		if ($request == AIR_Action_Redirect)
			{
 			$redirectTarget['dlgAction']	= AIR_Action_Redirect;

		  	if (array_key_exists($target, $processDefinitions))
		  		{
				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, ' redirect dispatch!');
					}
				$processDef		= $processDefinitions[$target];
				$processType	= $processDef['type'];
				if (($processType == AIR_ProcType_MenuManager)
				 || ($processType == AIR_ProcType_DialogHandler)
				 || ($processType == AIR_ProcType_FunctionManager))
					{
		 			$redirectTarget['procModule']	= $target;
		 			$redirectTarget['procAction']	= AIR_Action_Encode;
		 			$redirectTarget['procVers']		= '1.0';

					if (($this->anchor != NULL) && ($this->anchor->trace()))
						{
						$msgDiag		= 'strategy based on REDIRECT action';
						$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
						}

					return($dispatchFunction);
		 			}
		  		}

 			$redirectTarget['procModule']	= Dialog_Home;
 			$redirectTarget['procAction']	= AIR_Action_Encode;
 			$redirectTarget['procVers']		= '1.0';

			if (($this->anchor != NULL) && ($this->anchor->trace()))
				{
				$msgDiag		= 'strategy based on REDIRECT action<br/>';
				$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
				}

			return($dispatchFunction);
			}

		/*
		 * 2nd evaluation is for a redirection request.
		 *
		 */
		if ($request == AIR_Action_MenuSelect)
			{
		  	if ((empty($redirectTarget))
			 && (array_key_exists($target, $dlgWorkflowMap)))
  				{
		  		$workflowSpec = & $dlgWorkflowMap[$target];
			  	if (array_key_exists($request, $workflowSpec))
			  		{
		  			$dispatchFunction = & $workflowSpec[$request];
					$this->procContext->PutSessionData('currentMenuSelect', $target);
					if (($this->anchor != NULL) && ($this->anchor->trace()))
						{
						$msgDiag		= 'strategy based on ['.$target.'] WF action rule';
						$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
						}

					return($dispatchFunction);
			  		}
	  			}
 			}

		/*
		 * 3rd evaluation reviews global dispatch workflow, if any
		 */
	  	if ((empty($redirectTarget))
  		 && (array_key_exists(Dialog_Global, $dlgWorkflowMap)))
  			{
  			$workflowSpec = & $dlgWorkflowMap[Dialog_Global];
			/*
			 * Resolve the dispatch array for the request type
			 */
		  	if (array_key_exists($request, $workflowSpec))
		  		{
		  		$dispatchFunction = & $workflowSpec[$request];

				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$msgDiag		= 'strategy based on GLOBAL action rule';
					$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
					}

				return($dispatchFunction);
		  		}
  			}

		/*
		 * 4th evaluation looks for a module specific dispatch workflow, if any
		 */
	  	if ((empty($redirectTarget))
		 && (array_key_exists($dialog, $dlgWorkflowMap)))
  			{
	  		$workflowSpec = & $dlgWorkflowMap[$dialog];
		  	if (array_key_exists($request, $workflowSpec))
		  		{
		  		$dispatchFunction = & $workflowSpec[$request];

				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$msgDiag		= 'strategy based on ['.$dialog.'] WF action rule';
					$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
					}

				return($dispatchFunction);
		  		}
	  		}

		/*
		 * 5th evaluation reviews default dispatch workflow, if any
		 */
	  	if ((empty($redirectTarget))
  		 && (array_key_exists(Dialog_Default, $dlgWorkflowMap)))
  			{
  			$workflowSpec = & $dlgWorkflowMap[Dialog_Default];
			/*
			 * Resolve the dispatch array for the request type
			 */
		  	if (array_key_exists($request, $workflowSpec))
		  		{
		  		$dispatchFunction = & $workflowSpec[$request];
				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$msgDiag		= 'strategy based on [Dialog_Default] action rule';
					$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
					}
				return($dispatchFunction);
		  		}
  			}

		return($dispatchFunction);
		}

	/***************************************************************************
	 * getDlgWorkflowMap
	 *******/
	function & getDlgWorkflowMap()
	 	{
		$dlgWorkflowMap = array();

		$xmlFile = AF_ROOT_DIR.'/data/AF_WorkflowRules.xml';
		if (!file_exists($xmlFile))
			{
			throw new Exception('Failed to find XML config file!');
			}

		$simpleXML = simplexml_load_file($xmlFile);
      if (!($simpleXML instanceof SimpleXMLElement))
			{
			throw new Exception('Failed to load config file as SimpleXML!');
			}

		foreach ($simpleXML->workFlow as $workFlow)
			{
			foreach ($workFlow->dispatchRule as $dispatchRule)
				{
				// Note: String cast is necessary, otherwise these elements are
				// passed as SimpleXMLElement objects!
				$this->initDlgWorkflowSpec($dlgWorkflowMap,
													(String) $dispatchRule->object,
													(String) $dispatchRule->action,
													(String) $dispatchRule->msgObject,
													(String) $dispatchRule->msgAction);
				}
			}

		return $dlgWorkflowMap;
		}

	/***************************************************************************
	 * initDlgWorkflowSpec
	 *
	 * Support routine to build dlgWorkflowMap entries based on dialog identifiers
	 * and action requests that are enabled in association with the dialog panel.
	 * Workflow specifications define what internal object/action request should
	 * be fired based on the dialog request.
	 *******/
	function initDlgWorkflowSpec(& $workflowMap,
										  $entryDlg, $entryAction, $procModule,
										  $procAction = null, $procVers = '1.0')
	 	{
	 	if (array_key_exists($entryDlg, $workflowMap))

	 		{
	 		$dlgSpec	= $workflowMap[$entryDlg];
	 		}
 		else
	 		{
	 		$dlgSpec	= array();
	 		}

	 	if (empty($procAction))
	 		{
	 		$procAction = $entryAction;
	 		}

	 	$entry			= array();
	 	$entry['dlgAction']		= $entryAction;	// action as triggered by input panel (e.g., "ok")
	 	$entry['procModule']		= $procModule;		// module to process action (e.g., Login)
	 	$entry['procAction']		= $procAction;		// action as presented to module (e.g., "Login")
 		$entry['procVers']		= $procVers;

		$dlgSpec[$entryAction]	= $entry;
		$workflowMap[$entryDlg]	= $dlgSpec;
		}

	} // end of class

/*
 * Additional code to test PHP process flow under various arrangements.
 * This code is executed once per 'include' as the module is scanned
 * and all code not inside newly defined "function blocks" is executed.
 */
	if ($this->anchor->debugCoreFcns())
		{
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br/></debug> ';
		}
 ?>