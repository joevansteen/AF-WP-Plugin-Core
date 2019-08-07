<?php
/*
 * af_viewMgr_propertyReview script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from af_dialogencode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrPropertyReview';
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

class C_ViewMgrPropertyReview extends C_ViewMgrBase {

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
			case Dialog_PropertyReview:
				$this->createMaintTxnSpecArray();
				switch ($this->myTxnSpec['TxnOper'])
					{
					case AIR_Action_PropertyReviewAll:
						$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit All *** / Element Property Rules');
						$this->procAuditPropertyRules();
						break;
			   	case AIR_Action_PropertyReviewItem:
						$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit Item ***');
						break;
					case AIR_Action_PropertyReviewType:
						$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit Type *** / Property Specifications');
						$this->procAuditPropertySpecs();
						if (! array_key_exists('EleType', $this->myTxnSpec))
							{
							$this->anchor->putTraceData(__LINE__, __FILE__, 'Can NOT find item type spec!');
							}
						break;
					default:
						$this->anchor->abort('Unrecognized PropertyReview menu action ['.$this->myTxnSpec['TxnOper'].']');
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
	 * procAuditPropertySpecs
	 *******/
	function procAuditPropertySpecs()
		{
		$dlgChoice		= null;
//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		$showAsArray = false;
		$showAsReadOnly = true;
		$showAsModifiable = false;

		$this->showDiagnosticsInfo();

		$typeList = $this->anchor->get_allElementsByType(AIR_EleType_PropType);
			$this->pushContextStack(AIR_EleType_PropType); // eleType code is the 'stack name'

		$i = 0;
		foreach ($typeList as $eleType => $eleName)
			{
		 	$i++;
//			$this->anchor->putTraceData(__LINE__, __FILE__, 'Scanning property: '.$eleName);
			$eleSpec		= & $this->anchor->Repository->getElementRef($eleType);
			if (! is_object($eleSpec))
				{
				trigger_error('Critical data error in '.__FUNCTION__, E_USER_NOTICE);
				}
			$this->setTextDisplay('Name', 'Name'.$i, $eleName, true);

					$itemContent	= $eleSpec->getElementData('PrefLabel');
			$this->setTextDisplay('PrefLabel', 'PrefLabel'.$i, $itemContent, true);

					$itemContent			= $eleSpec->getElementData('ShortName');
			$this->setTextDisplay('ShortName', 'ShortName'.$i, $itemContent, true);

					$itemContent	= $eleSpec->getElementData('Label');
			$this->setTextDisplay('Label', 'Label'.$i, $itemContent, true);

					$itemContent			= $eleSpec->getElementData('FldName');
			$this->setTextDisplay('FldName', 'FldName'.$i, $itemContent, true);

//					$itemContent			= $eleSpec->getElementData('Abstract');
//			$this->setTextDisplay('Abstract', 'Abstract'.$i, $itemContent, true);
//					$itemContent			= $eleSpec->getElementData('Purpose');
//			$this->setTextDisplay('Purpose', 'Purpose'.$i, $itemContent, true);
//					$itemContent			= $eleSpec->getElementData('Description');
//			$this->setTextDisplay('Description', 'Description'.$i, $itemContent, true);
//					$itemContent			= $eleSpec->getElementData('Edits');
//			$this->setTextDisplay('Edits', 'Edits'.$i, $itemContent, true);
//					$itemContent			= $eleSpec->getElementData('Issues');
//			$this->setTextDisplay('Issues', 'Issues'.$i, $itemContent, true);
				$this->setContextItemBreak();
			}
			$this->popColumnContext(''); // eleName is the 'label' for the row

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);
		if (! empty($this->myTxnSpec['EleName']))
			{
			$maintItem = $this->myTxnSpec['EleName'];
			}
		else
			{
			$maintItem	 = '';
			}

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'Property Type Specifications');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
		}

	/***************************************************************************
	 * procAuditPropertyRules
	 *******/
	function procAuditPropertyRules()
		{
		$dlgChoice		= null;
//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		$showAsArray = false;
		$showAsReadOnly = true;
		$showAsModifiable = false;

		$this->showDiagnosticsInfo();

		$typeList = $this->anchor->get_allElementsByType(AIR_EleType_EleType);
		foreach ($typeList as $eleType => $eleName)
			{
			$eleModel = $this->anchor->Repository->getElementModel($eleType);
			$eleSpec = $eleModel->getRuleArray();
			if ((!is_array($eleSpec))
			 || (empty($eleSpec)))
				{
				trigger_error('Critical data error in '.__FUNCTION__, E_USER_NOTICE);
				}

			$this->pushContextStack($eleType); // eleType code is the 'stack name'
			$i = 0;
			foreach ($eleSpec as $eleItem)
			 	{
			 	$i++;

				try
				{
					$itemIsElement		= $eleItem['elementSpec'];
					$this->setTextDisplay('elementSpec', 'elementSpec'.$i, $itemIsElement, true);
				 	$itemName 			= $eleItem['FldName'];
					$this->setTextDisplay('FldName', 'FldName'.$i, $itemName, true);
					$dlgItemName		= 'dlg'.$itemName;
				 	$itemSpecType		= $eleItem['specType'];
					$this->setTextDisplay('specType', 'specType'.$i, $itemSpecType, true);
					$itemVisible		= $eleItem['Visible'];
					$this->setTextDisplay('Visible', 'Visible'.$i, $itemVisible, true);
					$itemLabel			= $eleItem['Label'];
					$this->setTextDisplay('Label', 'Label'.$i, $itemLabel, true);
					$itemManualInput	= $eleItem['Manual'];
					$this->setTextDisplay('Manual', 'Manual'.$i, $itemManualInput, true);
					$itemOrdSpec		= $eleItem['PredOrdSpec'];
					$eleItemName = $this->getElementName($itemOrdSpec);
					$this->setTextDisplay('PredOrdSpec', 'PredOrdSpec'.$i, $eleItemName, true);
					$itemCardSpec		= $eleItem['PredCardSpec'];
					$eleItemName = $this->getElementName($itemCardSpec);
					$this->setTextDisplay('PredCardSpec', 'PredCardSpec'.$i, $eleItemName, true);
					$itemCardLimit		= $eleItem['PredCardLimit'];
					$this->setTextDisplay('PredCardLimit', 'PredCardLimit'.$i, $itemCardLimit, true);
					$itemDataType		= $eleItem['DataType'];
					$eleItemName = $this->getElementName($itemDataType);
					$this->setTextDisplay('DataType', 'DataType'.$i, $eleItemName, true);
					$itemDataCaptType	= $eleItem['DataCaptType'];
					$eleItemName = $this->getElementName($itemDataCaptType);
					$this->setTextDisplay('DataCaptType', 'DataCaptType'.$i, $eleItemName, true);
					$itemDefault		= $eleItem['Default'];
					$itemSelType		= $eleItem['SelectionType'];
					$eleItemName = $this->getElementName($itemSelType);
					$this->setTextDisplay('SelectionType', 'SelectionType'.$i, $eleItemName, true);

					$itemRelClass		= $eleItem['RelClass'];
					$eleItemName = $this->getElementName($itemRelClass);
					$this->setTextDisplay('RelClass', 'RelClass'.$i, $eleItemName, true);
					$itemExternal		= $eleItem['External'];
					$this->setTextDisplay('External', 'External'.$i, $itemExternal, true);
					$itemPlugInMod		= $eleItem['PlugInMod'];
					$this->setTextDisplay('PlugInMod', 'PlugInMod'.$i, $itemPlugInMod, true);
					$itemSortKey		= $eleItem['RelSortKey'];
					$this->setTextDisplay('RelSortKey', 'RelSortKey'.$i, $itemSortKey, true);
					$itemSelTypeFld	= $eleItem['SelTypeFld'];
					$eleItemName = $this->getElementName($itemSelTypeFld);
					$this->setTextDisplay('SelTypeFld', 'SelTypeFld'.$i, $eleItemName, true);
				}
				catch (Exception $e)
				{
					$this->anchor->putTraceData(__LINE__, __FILE__, '*** Exception processing Model for: '
														.$eleName.' ['.$eleType.'] = '
														.$e->getMessage());
				}
				$this->setContextItemBreak();
			 	}
			$this->popColumnContext($eleName); // eleName is the 'label' for the row
			}

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);
		if (! empty($this->myTxnSpec['EleName']))
			{
			$maintItem = $this->myTxnSpec['EleName'];
			}
		else
			{
			$maintItem	 = '';
			}

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'Element Type Property Features');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
		}

	/***************************************************************************
	 * getElementName
	 *******/
	function & getElementName($eleIdent)
		{
		$result = '';
		if (!empty($eleIdent))
			{
			$result = $this->anchor->Repository->getElementName($eleIdent, true);
			}
		return $result;
		}

	}

?>