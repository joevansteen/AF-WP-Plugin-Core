<?php
/*
 * af_viewMgr_eleXref script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-14 JVS Bootstrap from af_dialogencode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrEleXref';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


	/***************************************************************************
	 * sortManifestItemsByType()
	 *******/
function sortManifestItemsByType($ele1, $ele2)
	{
	$sortResult = 0;

	$key1 = strtolower($ele1['Type']);
	$key2 = strtolower($ele2['Type']);

	if ($key1 == $key2)
		{
		$key1 = strtolower($ele1['Name']);
		$key2 = strtolower($ele2['Name']);

		if ($key1 == $key2)
			{
			$sortResult = 0;
			}
		else
		if ($key1 < $key2)
			{
			$sortResult = -1;
			}
		else
			{
			$sortResult = 1;
			}
		}
	else
	if ($key1 < $key2)
		{
		$sortResult = -1;
		}
	else
		{
		$sortResult = 1;
		}


	return ($sortResult);
	}

class C_ViewMgrEleXref extends C_ViewMgrBase {

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
			case Dialog_EleXref:
				$this->createMaintTxnSpecArray();
				$this->procEleAudit();
				break;
			}

		$this->anchor->setDlgVarByRef('air_ItemHeader', $this->dlgHeader);
		$this->anchor->setDlgVarByRef('air_Dialog', 		$this->dlgContent);
		$this->anchor->setDlgVarByRef('air_ItemFooter', $this->dlgFooter);
		}

	/***************************************************************************
	 * procEleAudit
	 *******/
	function procEleAudit()
		{
		$dlgChoice		= null;
//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, '+++ ENCODE +++ ENCODE +++ ENCODE +++ ENCODE +++ ENCODE +++');
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		$showAsArray = false;
		$showAsReadOnly = true;
		$showAsModifiable = false;

		switch ($this->myTxnSpec['TxnOper'])
			{
			case AIR_Action_AuditAll:
				$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit All ***');
				break;
	   	case AIR_Action_AuditItem:
				$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit Item ***');
				break;
			case AIR_Action_AuditType:
				$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit Type ***');
				if (! array_key_exists('EleType', $this->myTxnSpec))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, 'Can NOT find item type spec!');
					}
				break;
			}

		$this->showDiagnosticsInfo();

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);
		if (! empty($this->myTxnSpec['EleName']))
			{
			$maintItem = $this->myTxnSpec['EleName'];
			}
		else
			{
			$maintItem	 = '';
//			$maintItemId = $this->myTxnSpec['EleType'];
// 			if (! empty($maintItemId))
//	 			{
//		 		$maintItem = $this->anchor->Repository->getElementName($maintItemId);
//	 			}
			}

		$this->anchor->setDlgVar('panelSubtitle', 'Business Model Maintenance');
		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Model Constraints Maintenance');
		$this->anchor->setDlgVar('panelSubtitle', '* Model Element Maintenance *');

		$this->anchor->setDlgVar('panelItemTitle',	 'Model Element Maintenance');
//		$this->anchor->setDlgVar('panelItemTitle',	 $txnAction . ' ' . $maintItem);
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_EleMaint);
		}

	}

 ?>