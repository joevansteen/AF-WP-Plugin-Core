<?php
/*
 * C_AF_ViewMgrViewProcessorMap script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-17 JVS Bootstrap from af_viewMgr_dispatchRules
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

require(AF_AUTOMATED_SCRIPTS.'/af_viewMgr_base.php');

// Insure a correct execution context ...
$myProcClass = 'C_AF_ViewMgrViewProcessorMap';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AF_ViewMgrViewProcessorMap extends C_ViewMgrBase {

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
			case AF_VIEW_PROCESSOR_MAP:
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

		$processDefinitions	= $this->anchor->getStdVar('processDefinitions');

		$this->pushContextStack('Processor Map Entries:');

		$this->setTextDisplay('ViewDialog', 'hdrViewDialog', 'View Dialog', true);
		$this->setTextDisplay('Type', 'hdrType', 'View Type', true);
		$this->setTextDisplay('Processor', 'hdrProcessor', 'Processor Module', true);
		$this->setContextItemBreak();

		foreach ($processDefinitions as $viewDialog => $entry)
			{
				$dlgType		= $entry['type'];
				$dlgIdent	= $entry['procIdent'];
				$dlgProc		= $entry['ReplyProcessor'];

				$this->setTextDisplay('Dialog', 'txtDialog', $dlgIdent, true);
				$this->setTextDisplay('Type', 'txtType', $dlgType, true);
				$this->setTextDisplay('Processor', 'txtProcessor', $dlgProc, true);
				$this->setContextItemBreak();
			}
		$this->popColumnContext(''); // is the 'label' for the row

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'View Processor Module Map');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
		}

	}

?>