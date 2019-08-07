<?php
/*
 * af_viewMgr_procOptions script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from af_dialogencode
  * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrProcOptions';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


class C_ViewMgrProcOptions extends C_ViewMgrBase {
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

		$this->hasSelectOptionNone	= true;
		$this->hasSelectOptionAny	= false;
		$this->hasSelectOptionAll	= false;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, $this->myMsgObject);
			}

		switch ($this->myMsgObject)
			{
			case Dialog_ProcOptions:
				$this->createMenuTxnSpecArray();
				$this->procProcOptions();
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
	 * procProcOptions
	 *******/
	function procProcOptions()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Save;
		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;
		$this->actionButtons[] 		= AIR_Action_Quit;

		/*
		 * Create context array and post context display information to SMARTY
		 */
		$dlgContext	= $this->procContext->getContextId();
		$this->setShowSelectionInfo('Context', true, AIR_EleType_Context, $dlgContext, true, true);

		$this->anchor->setDlgVar('air_showSaveCommand',		true);
		$this->anchor->setDlgVar('dlgAction', 					'Set Options');

		$this->anchor->setDlgVar('air_SelectMultiple',		true);
		$optionList = array();
		$optionItem = array();

		$optionSettings	= $this->anchor->getStdVar('optionSettings');
		foreach ($optionSettings as $key => $option)
			{
			$fldName		= $option['FldName'];
			$type			= $option['Type'];
			$value		= $option['Value'];
			$txtName		= $option['TxtName'];

			$dlgName		= 'dlg'.$fldName;

			$optionItem['type'] 		= $type;
			$optionItem['on'] 		= $this->procContext->getSessionData($fldName);
			$optionItem['content']	= $txtName;
			$optionList[$dlgName]	= $optionItem;
			}

		$this->anchor->setDlgVarByRef('air_listItemArray',	$optionList);

		$this->showDiagnosticsInfo();
		$this->anchor->setDlgVar('panelTitle',	 'Processing Options');

		$this->procContext->putSessionData('responseDialog', Dialog_ProcOptions);
		}

	}

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