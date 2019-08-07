<?php
/*
 * af_viewMgr_workflowRules script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-16 JVS Bootstrap from af_viewMgr_dispatchRules
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
require(AF_AUTOMATED_SCRIPTS.'/af_viewMgr_base.php');
$myProcClass = 'C_ViewMgrWorkflowRules';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


class C_ViewMgrWorkflowRules extends C_ViewMgrBase {

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *******/
	function __construct()
		{
		// Propogate the construction process
		parent::__construct();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * ProcMod_Main
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
	 	parent::initialize($procContext, $baseMsg, $procMsg);

		$this->hasSelectOptionNone	= true;
		$this->hasSelectOptionAny	= false;
		$this->hasSelectOptionAll	= false;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, $this->myMsgObject);
			}

		switch ($this->myMsgObject)
			{
			case AF_WORKFLOW_RULES:
				$this->createMaintTxnSpecArray();
				switch ($this->myTxnSpec['TxnOper'])
					{
					case AIR_Action_Review:
						$this->procShowWorkflowRules();
						break;
					default:
						$this->anchor->abort('Unrecognized menu action ['.$this->myTxnSpec['TxnOper'].']');
						throw new Exception('Unrecognized message action');
					}
				break;
			default:
				$this->anchor->abort('Unrecognized menu decode object ['.$this->myMsgObject.']');
				throw new Exception('Unrecognized message object');
			}

		$this->anchor->setDlgVarByRef('air_ItemHeader', $this->dlgHeader);
		$this->anchor->setDlgVarByRef('air_Dialog', 		$this->dlgContent);
		$this->anchor->setDlgVarByRef('air_ItemFooter', $this->dlgFooter);
		}

	/***************************************************************************
	 * procShowWorkflowRules
	 *******/
	function procShowWorkflowRules()
		{
		$dlgChoice		= null;
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		$showAsArray = false;
		$showAsReadOnly = true;
		$showAsModifiable = false;

		$this->showDiagnosticsInfo();

		$dlgWorkflowMap		= $this->getDlgWorkflowMap();

		$this->pushContextStack('Dialog Workflow Entries:');

		$this->setTextDisplay('Object', 'hdrObject', 'Dialog Object', true);
		$this->setTextDisplay('Mode', 'hdrMode', 'Mode', true);
		$this->setTextDisplay('Action', 'hdrAction', 'Dialog Action', true);
		$this->setTextDisplay('MsgObject', 'hdrMsgObject', 'Message Object', true);
		$this->setTextDisplay('MsgAction', 'hdrMsgAction', 'Message Action', true);
		$this->setTextDisplay('Version', 'hdrVers', 'Version', true);
		$this->setTextDisplay('Description', 'hdrDescription', 'Description', true);
		$this->setContextItemBreak();

		foreach ($dlgWorkflowMap as $workflowKey => $actionEntry)
			{
			foreach ($actionEntry as $actionKey => $entry)
				{
						$dlgAction		= $entry['dlgAction'];
						$msgTarget		= $entry['procModule'];
						$msgAction		= $entry['procAction'];
						$msgVers			= $entry['procVers'];
						$descr			= $entry['itemDescr'];
						$mode				= $entry['dlgMode'];

						$this->setTextDisplay('Dialog', 'txtDialog', $workflowKey, true);
						$this->setTextDisplay('Mode', 'txtMode', $mode, true);
						$this->setTextDisplay('Action', 'txtAction', $actionKey, true);
						$this->setTextDisplay('MsgObject', 'txtMsgObject', $msgTarget, true);
						$this->setTextDisplay('MsgAction', 'txtMsgAction', $msgAction, true);
						$this->setTextDisplay('Version', 'txtVers', $msgVers, true);
						$this->setTextDisplay('Descr', 'txtDescr', $descr, true);
						$this->setContextItemBreak();
				}
			}
		$this->popColumnContext(''); // is the 'label' for the row

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'Dialog Action Workflow Rules');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
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
													(String) $dispatchRule->msgMode,
													(String) $dispatchRule->object,
													(String) $dispatchRule->action,
													(String) $dispatchRule->msgObject,
													(String) $dispatchRule->msgAction,
													(String) $dispatchRule->version,
													(String) $dispatchRule->description);
				}
			}
		ksort ($dlgWorkflowMap);

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
										  $entryMode, $entryDlg, $entryAction, $procModule,
										  $procAction = null, $procVers = '1.0', $description = '')
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
	 	$entry['dlgMode']			= $entryMode;		// mode
	 	$entry['dlgAction']		= $entryAction;	// action as triggered by input panel (e.g., "ok")
	 	$entry['procModule']		= $procModule;		// module to process action (e.g., Login)
	 	$entry['procAction']		= $procAction;		// action as presented to module (e.g., "Login")
 		$entry['procVers']		= $procVers;
 		$entry['itemDescr']		= $description;

		$dlgSpec[$entryAction]	= $entry;
		ksort ($dlgSpec);
		$workflowMap[$entryDlg]	= $dlgSpec;
		}

	}

?>