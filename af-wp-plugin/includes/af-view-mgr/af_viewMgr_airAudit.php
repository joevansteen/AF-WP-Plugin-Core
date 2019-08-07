<?php
/*
 * af_viewMgr_airAudit script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from af_dialogencode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrAirAudit';
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

class C_ViewMgrAirAudit extends C_ViewMgrBase {

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
			case Dialog_AirAudit:
				$this->createMaintTxnSpecArray();
				$this->procAirAudit();
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
	 * procAirAudit
	 *******/
	function procAirAudit()
		{
		$dlgChoice		= null;
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, '+++ ENCODE +++ ENCODE +++ ENCODE +++ ENCODE +++ ENCODE +++');
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

//		$this->showAirElementBaseInfo2();

		$showAsArray = false;
		$showAsReadOnly = true;
		$showAsModifiable = false;

		switch ($this->myTxnSpec['TxnOper'])
			{
			case AIR_Action_AirAuditAll:
				$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit All ***');
				break;
	   	case AIR_Action_AirAuditItem:
				$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit Item ***');
				break;
			case AIR_Action_AirAuditType:
				$this->anchor->putTraceData(__LINE__, __FILE__, '*** Audit Type ***');
				if (! array_key_exists('EleType', $this->myTxnSpec))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, 'Can NOT find item type spec!');
					}
//				$this->createHtmlResponsePanelInfo($showAsArray, $showAsReadOnly);
				break;
			}

		$this->showDiagnosticsInfo();

		$typeList = $this->anchor->get_allElementsByType(AIR_EleType_EleType);
		foreach ($typeList as $eleType => $eleName)
			{
			$eleSpec = $this->anchor->getCompositionByEleType($eleType);
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
				$eleItemName = $this->getRefName($itemOrdSpec);
				$this->setTextDisplay('PredOrdSpec', 'PredOrdSpec'.$i, $eleItemName, true);
				$itemCardSpec		= $eleItem['PredCardSpec'];
				$eleItemName = $this->getRefName($itemCardSpec);
				$this->setTextDisplay('PredCardSpec', 'PredCardSpec'.$i, $eleItemName, true);
				$itemCardLimit		= $eleItem['PredCardLimit'];
				$this->setTextDisplay('PredCardLimit', 'PredCardLimit'.$i, $itemCardLimit, true);
				$itemDataType		= $eleItem['DataType'];
				$eleItemName = $this->getRefName($itemDataType);
				$this->setTextDisplay('DataType', 'DataType'.$i, $eleItemName, true);
				$itemDataCaptType	= $eleItem['DataCaptType'];
				$eleItemName = $this->getRefName($itemDataCaptType);
				$this->setTextDisplay('DataCaptType', 'DataCaptType'.$i, $eleItemName, true);
				$itemDefault		= $eleItem['Default'];
				$itemSelType		= $eleItem['SelectionType'];
				$eleItemName = $this->getRefName($itemSelType);
				$this->setTextDisplay('SelectionType', 'SelectionType'.$i, $eleItemName, true);

				$itemRelClass		= $eleItem['RelClass'];
				$eleItemName = $this->getRefName($itemRelClass);
				$this->setTextDisplay('RelClass', 'RelClass'.$i, $eleItemName, true);
				$itemExternal		= $eleItem['External'];
				$this->setTextDisplay('External', 'External'.$i, $itemExternal, true);
				$itemPlugInMod		= $eleItem['PlugInMod'];
				$this->setTextDisplay('PlugInMod', 'PlugInMod'.$i, $itemPlugInMod, true);
				$itemSortKey		= $eleItem['RelSortKey'];
				$this->setTextDisplay('RelSortKey', 'RelSortKey'.$i, $itemSortKey, true);
				$itemSelTypeFld	= $eleItem['SelTypeFld'];
				$eleItemName = $this->getRefName($itemSelTypeFld);
				$this->setTextDisplay('SelTypeFld', 'SelTypeFld'.$i, $eleItemName, true);
				$this->setContextItemBreak();
			 	}
			$this->popColumnContext($eleName); // eleName is the 'label' for the row
			}

//		for ($i = 0; $i < 3; $i++)
//			{
//			$this->setTextDisplay('Label', 'ItemName', 'Text Content'.$i, true);
//			}
//		$this->popColumnContext('Test Items:');

		$txnAction = $this->anchor->setDialogAction($this->myTxnSpec['TxnOper']);
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
//		 		$maintItem = $this->anchor->getRefName($maintItemId);
//	 			}
			}

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');

		$this->anchor->setDlgVar('panelItemTitle',	 'Element Type Property Audit');
//		$this->anchor->setDlgVar('panelItemTitle',	 $txnAction . ' ' . $maintItem);
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->procContext->putSessionData('responseDialog', Dialog_AirAudit);
		}

	/***************************************************************************
	 * getRefName
	 *******/
	function & getRefName($eleIdent)
		{
		$result = '';
		if (!empty($eleIdent))
			{
			$result = $this->anchor->getRefName($eleIdent, true);
			}
		return $result;
		}

	/***************************************************************************
	 * createHtmlResponsePanelInfo
	 *******/
	function createHtmlResponsePanelInfo($showAsArray, $showAsReadOnly)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			trigger_error('Critical data error in '.__FUNCTION__, E_USER_NOTICE);
			}

		foreach ($eleSpec as $eleItem)
		 	{
		 	$itemExists			= true;
			$itemIsElement		= $eleItem['elementSpec'];
		 	$itemName 			= $eleItem['FldName'];
			$dlgItemName		= 'dlg'.$itemName;
		 	$itemSpecType		= $eleItem['specType'];
			$itemVisible		= $eleItem['Visible'];
			$itemLabel			= $eleItem['Label'];
			$itemManualInput	= $eleItem['Manual'];
			$itemOrdSpec		= $eleItem['PredOrdSpec'];
			$itemCardSpec		= $eleItem['PredCardSpec'];
			$itemCardLimit		= $eleItem['PredCardLimit'];
			$itemDataType		= $eleItem['DataType'];
			$itemDataCaptType	= $eleItem['DataCaptType'];
			$itemDefault		= $eleItem['Default'];
			$itemSelType		= $eleItem['SelectionType'];

			$itemRelClass		= $eleItem['RelClass'];
			$itemExternal		= $eleItem['External'];
			$itemPlugInMod		= $eleItem['PlugInMod'];
			$itemSortKey		= $eleItem['RelSortKey'];
			$itemSelTypeFld	= $eleItem['SelTypeFld'];

		 	if (($itemIsElement)
		 	 && ($itemVisible))
		 		{
		 		/*
		 		 * If the item does not exist, create a default value
		 		 * and flag the fact that it was artificially generated.
		 		 */
				if ((! array_key_exists($itemName, $this->myTxnSpec))
				 || (empty($this->myTxnSpec[$itemName])))
					{
					$itemExists	= false;
					$this->myTxnSpec[$itemName] = $this->getTxnDataDefaultValue($eleItem);
					}

				/*
				 * Now, based on the data type and capture type, and taking into account
				 * the purpose of the output display (read only viewing versus data capture),
				 * formulate the display scheme for the element.
				 */
				switch ($itemDataCaptType)
					{
					case AIR_CaptureType_EleSelect:
						// $itemSelType defines an element type group that the selection
						// needs to be made from
//						$this->setSelectionListInfo($dlgItemLabel, $varNameBase,
//															$dlgSelectionType, $dlgSelectedValue,
//															$showAsArray = true, $showAsReadOnly = false)
						$itemValue = $this->myTxnSpec[$itemName];
						if ((! $showAsReadOnly)
						 || ((! empty($itemValue))
						  && ($itemValue != AIR_Null_Identifier)))
							{
							$this->hasSelectOptionNone	= ($itemOrdSpec != AIR_EleIsRequired);
							$this->hasSelectOptionAny	= false;
							$this->hasSelectOptionAll	= false;
							$this->setSelectionListInfo($itemLabel, $itemName,
																 $itemSelType, $itemValue,
																 ($showAsArray and $itemManualInput),
																 ($showAsReadOnly or (! $itemManualInput)));
							}
						break;
					case AIR_CaptureType_EleSpec:
						// $itemSelType defines an the element UUID. The element is a constant.
						// There is no selection to be made.
						if ((! $showAsReadOnly)
						 || ((! empty($itemSelType))
						  && ($itemSelType != AIR_Null_Identifier)))
							{
					 		$textValue = '';
					 		if (! empty($itemSelType))
					 			{
						 		$textValue = $this->anchor->getRefName($itemSelType);
						 		}
							$this->setKeyedTextDisplay($itemLabel, $dlgItemName, $itemSelType, $textValue);
							}
						break;
					case AIR_CaptureType_EleRef:
						// The element defines a UUID. The element is maintained internally. It is
						// display only. There is no selection to be made.
						$itemValue = $this->myTxnSpec[$itemName];
						if ((! $showAsReadOnly)
						 || ((! empty($itemValue))
						  && ($itemValue != AIR_Null_Identifier)))
							{
					 		$textValue = '';
					 		if (! empty($itemValue))
					 			{
						 		$textValue = $this->anchor->getRefName($itemValue);
						 		}
							$this->setKeyedTextDisplay($itemLabel, $dlgItemName, $itemValue, $textValue);
							}
						break;
					case AIR_CaptureType_CheckList:
							// Get the list of keys for the items in the collection
							$selTypes 	= $this->anchor->get_allElementsByType($eleItem['SelectionType'], 0, NULL, false,
																	false,	//		$this->hasSelectOptionNone,
																	false,	//		$this->hasSelectOptionAny,
																	true);	// 	$this->hasSelectOptionAll);
							// Get addressability on the txnSpec array sub-array
							$eleContent = $this->myTxnSpec[$itemName];
							if (! empty($eleContent))
								{
								if (! is_array($eleContent))
									{
									trigger_error("myTxnSpec[$itemName] content was not an array!", E_USER_NOTICE);
									$this->diagnoseTxnDataArray();
									}
								}
//		$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " found txnSpec content as:");
//		foreach ($eleContent as $key => $value)
//			{
//			$this->anchor->putTraceData(__LINE__, __FILE__, "  key=[$key] value =[$value]");
//			}
							if (! $showAsReadOnly)
								{
								$itemArray = array();
								$itemArray['itemName']		= $dlgItemName . 'ShowAll';
								$itemArray['itemContent']	= 'Show unselected items'; // was $itemLabel
								$checklistShowAll				= $this->myTxnSpec[$itemName . 'ShowAll'];
								$itemArray['itemSelect']	= $checklistShowAll;
								$chklistArray = array();
								$chklistArray[] = $itemArray;
								$this->setCheckOptDisplay($itemLabel, $dlgItemName, $chklistArray, false);
								$itemLabel = '';
								}
							else
								{
								$checklistShowAll				= false;
								}
							$chklistArray = array();
							foreach ($selTypes as $key => $value)
								{
								$itemArray = array();
								$dlgName = 'dlg' . $itemName . $key;
								$itemArray['itemName']		 = $dlgName;
								$itemArray['itemContent']	 = $value;
								if ((! empty($eleContent))
								 && (array_key_exists($key, $eleContent)))
									{
									$itemCheckboxValue	= $eleContent[$key];
									}
								else
									{
									$itemCheckboxValue	= false;
									}
								$itemArray['itemSelect']	= $itemCheckboxValue;
								if (($checklistShowAll)
								 || ($itemCheckboxValue))
									{
									$chklistArray[] = $itemArray;
									}
								}
							if ((! $showAsReadOnly)
							 || (! empty($chklistArray)))
								{
								$this->setCheckOptDisplay($itemLabel, $dlgItemName, $chklistArray);
//								$this->setMultiSelectDisplay($itemLabel, $dlgItemName, $chklistArray);
								}

						break;

					case AIR_CaptureType_RelEleSel:
						// $itemSelTypeFld defines the item name of the variable that contains
						// the UUID of the element type group that the selection needs to be made from.
						// If the variable does not exist, or if it is empty or NULL, then no selection
						// can be made. Instead, the field value and display/capture method will be
						// determined based on the data type.
						$this->hasSelectOptionNone	= ($itemOrdSpec != AIR_EleIsRequired);
						$this->hasSelectOptionAny	= false;
						$this->hasSelectOptionAll	= false;
						if (array_key_exists($itemSelTypeFld, $this->myTxnSpec))
							{
							$selItemType = $this->myTxnSpec[$itemSelTypeFld];
							if ((!empty($selItemType))
							 && ($selItemType != AIR_Null_Identifier))
								{
								$itemValue = $this->myTxnSpec[$itemName];
								$this->setSelectionListInfo($itemLabel, $itemName,
																	 $selItemType, $itemValue,
																	 ($showAsArray and $itemManualInput),
																	 ($showAsReadOnly or (! $itemManualInput)));
								break;
								}
							}
					default:
						/*
						 * Remaining items should be "key entry" and are parsed by data type
						 */
						switch ($itemDataType)
							{
							case AIR_ContentType_Boolean:
								if (($showAsReadOnly )
								 || (! $itemManualInput))
									{
									if ($this->myTxnSpec[$itemName])
										{
										$this->setTextDisplay($itemLabel, $dlgItemName, 'TRUE',
																	($showAsReadOnly or (! $itemManualInput)));
										}
									else
										{
										$this->setTextDisplay($itemLabel, $dlgItemName, 'FALSE',
																	($showAsReadOnly or (! $itemManualInput)));
										}
									}
								else
									{
									// Boolean is displayed as single element checklist
									$itemArray = array();
									$itemArray['itemName']		 = $dlgItemName;
									$itemArray['itemContent']	 = 'True / Yes'; // was $itemLabel
									$itemArray['itemSelect']	 = $this->myTxnSpec[$itemName];
									$chklistArray = array();
									$chklistArray[] = $itemArray;
									$this->setCheckOptDisplay($itemLabel, $dlgItemName, $chklistArray, false);
									}
								break;

							case AIR_ContentType_IntText:
							case AIR_ContentType_IntHyperlink:
							case AIR_ContentType_Integer:
							case AIR_ContentType_Float:
							case AIR_ContentType_Date:
							case AIR_ContentType_Time:
							case AIR_ContentType_Datetime:
							case AIR_ContentType_UUID:
								$fldContent = $this->myTxnSpec[$itemName];
								/*
								 * The following test should include an additional item to be able to
								 * force display of blank fields under "show" (read only) conditions.
								 * A form of "show all" to forcefully display empty contents on those
								 * properties that are allowed, but have no information content.
								 */
								if ((! $showAsReadOnly)
								 || (! empty($fldContent)))
								 	{
									$this->setTextDisplay($itemLabel, $dlgItemName, $fldContent,
																($showAsReadOnly or (! $itemManualInput)));
									}
								break;
							case AIR_ContentType_IntTextBlock:
								$fldContent = $this->myTxnSpec[$itemName];
								/*
								 * Same comment as 'text' above.
								 */
								if ((! $showAsReadOnly)
								 || (! empty($fldContent)))
								 	{
									$this->setTextboxDisplay($itemLabel, $dlgItemName, $this->myTxnSpec[$itemName],
																($showAsReadOnly or (! $itemManualInput)));
									}
								break;
							case AIR_ContentType_ExtText:
							case AIR_ContentType_ExtTextBlock:
							case AIR_ContentType_ExtHyperlink:
							case AIR_ContentType_Binary:
							case AIR_ContentType_OrdSpec:
							default:
								$this->myTxnSpec[$itemName] = '';
								break;
							}
						break;
					}
				}
		 	}

		return;
		}





	/***************************************************************************
	 * createRulesEleTypeXrefInfo
	 *******/
	function createRulesEleTypeXrefInfo($eleIdent, $showAsReadOnly)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$ruleCount	= $this->anchor->myDbLayer->getCount_AirRelRules();

		$ruleArray = $this->anchor->myDbLayer->get_RelRulesUsingElement($eleIdent, $ruleCount);
		if ((isset($ruleArray))
		 && (is_array($ruleArray))
		 && (!empty($ruleArray)))
			{
			$predArray	= array();
			$objArray	= array();
			$iobjArray	= array();
			$diagArray	= array();

			$rowCount 	= count($ruleArray);
			if ($rowCount > 0)
				{
				/* examine the rowset */
				$rowNumber = 0;
				foreach ($ruleArray as $dbRow)
					{
					$rowNumber++;
					$ruleIdent						= $dbRow['Air_Ele_Id'];
					$ruleDoc 						= & $this->anchor->getRefElement($ruleIdent);
					$predicateId					= '';
					$objectId						= '';
					$iObjectId						= '';
					$diagId							= '';
					if (array_key_exists('Air_RelRule_Predicate', $dbRow))
						{
						$assocData					= array();
						$assocSeq					= $rowNumber * 5;
						$predicateId				= $dbRow['Air_RelRule_Predicate'];
						if ((!empty($predicateId))
						 && ($predicateId != AIR_Null_Identifier))
							{
							$assocData['seq']				= $assocSeq;
							$assocData['item']			= $ruleDoc->getElementData('PredType');
							$assocData['itemName']		= $this->anchor->getRefName($predicateId);
							$assocData['external']		= $ruleDoc->getElementData('External');
							$assocData['ordinality']	= $ruleDoc->getElementData('PredOrdSpec');
							$assocData['cardinality']	= $ruleDoc->getElementData('PredCardSpec');
							$assocData['maxOccurs']		= $ruleDoc->getElementData('PredCardLimit');
							$assocData['diag']			= $ruleDoc->getElementData('RuleDiag');
							$predArray[$assocSeq]		= $assocData;
							}
						}
					if (array_key_exists('Air_RelRule_Object', $dbRow))
						{
						$objectId						= $dbRow['Air_RelRule_Object'];
						if ((!empty($objectId))
						 && ($objectId != AIR_Null_Identifier))
							{
							$objArray[$objectId]			= $this->anchor->getRefName($objectId);
							}
						}
					if (array_key_exists('Air_RelRule_IObject', $dbRow))
						{
						$iObjectId						= $dbRow['Air_RelRule_IObject'];
						if ((!empty($iObjectId))
						 && ($iObjectId != AIR_Null_Identifier))
							{
							$iobjArray[$iObjectId]		= $this->anchor->getRefName($iObjectId);
							}
						}
					if (array_key_exists('Air_RelRule_Diag', $dbRow))
						{
						$diagId						= $dbRow['Air_RelRule_Diag'];
						if ((!empty($diagId))
						 && ($diagId != AIR_Null_Identifier))
							{
							$diagArray[$diagId]		= $this->anchor->getRefName($diagId);
							}
						}
//					$result							= array();
//					$result['specType'] 			= 'basic';
//					$result['elementSpec']		= true;
//
//					$result['RuleIdent'] 		= $dbRow['Air_Ele_Id'];
//					$result['PredType'] 			= $dbRow['Air_RelRule_Predicate'];
//					$result['SubjType'] 			= $dbRow['Air_RelRule_Subject'];
//					$result['PredOrdSpec'] 		= $dbRow['Air_RelRule_PredOrd'];
//					$result['PredCardSpec'] 	= $dbRow['Air_RelRule_PredCard'];
//					$result['PredCardLimit']	= $dbRow['Air_RelRule_PredMax'];
					}
				}

			if (! empty($predArray))
				{
//				ksort($predArray);
				$this->setPropertyListDisplay('Attributes', '', $predArray);
				}
			if (! empty($objArray))
				{
				asort($objArray);
				$this->setSimpleListDisplay('Relates to', '', $objArray);
				}
			if (! empty($iobjArray))
				{
				asort($iobjArray);
				$this->setSimpleListDisplay('Relates via', '', $iobjArray);
				}
			if (! empty($diagArray))
				{
				asort($diagArray);
				$this->setSimpleListDisplay('Diagnostics', '', $diagArray);
				}
//			function setSimpleListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $ordered=true)
			}

		return;
		}

	/***************************************************************************
	 * createRulesRelTypeXrefInfo
	 *******/
	function createRulesRelTypeXrefInfo($eleIdent, $showAsReadOnly)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$ruleCount	= $this->anchor->myDbLayer->getCount_AirRelRules();
		$ruleArray = $this->anchor->myDbLayer->get_RelRulesUsingPredicate($eleIdent, $ruleCount);
		if ((isset($ruleArray))
		 && (is_array($ruleArray))
		 && (!empty($ruleArray)))
			{
			$subjArray	= array();
			$objArray	= array();
			$iobjArray	= array();
			$diagArray	= array();

			$rowCount 	= count($ruleArray);
			if ($rowCount > 0)
				{
				/* examine the rowset */
				foreach ($ruleArray as $dbRow)
					{
					$subjectId						= '';
					$objectId						= '';
					$iObjectId						= '';
					$diagId							= '';
					if (array_key_exists('Air_RelRule_Subject', $dbRow))
						{
						$subjectId						= $dbRow['Air_RelRule_Subject'];
						if ((!empty($subjectId))
						 && ($subjectId != AIR_Null_Identifier))
							{
							$subjArray[$subjectId]		= $this->anchor->getRefName($subjectId);
							}
						}
					if (array_key_exists('Air_RelRule_Object', $dbRow))
						{
						$objectId						= $dbRow['Air_RelRule_Object'];
						if ((!empty($objectId))
						 && ($objectId != AIR_Null_Identifier))
							{
							$objArray[$objectId]			= $this->anchor->getRefName($objectId);
							}
						}
					if (array_key_exists('Air_RelRule_IObject', $dbRow))
						{
						$iObjectId						= $dbRow['Air_RelRule_IObject'];
						if ((!empty($iObjectId))
						 && ($iObjectId != AIR_Null_Identifier))
							{
							$iobjArray[$iObjectId]		= $this->anchor->getRefName($iObjectId);
							}
						}
					if (array_key_exists('Air_RelRule_Diag', $dbRow))
						{
						$diagId						= $dbRow['Air_RelRule_Diag'];
						if ((!empty($diagId))
						 && ($diagId != AIR_Null_Identifier))
							{
							$diagArray[$diagId]		= $this->anchor->getRefName($diagId);
							}
						}
//					$result							= array();
//					$result['specType'] 			= 'basic';
//					$result['elementSpec']		= true;
//
//					$result['RuleIdent'] 		= $dbRow['Air_Ele_Id'];
//					$result['PredType'] 			= $dbRow['Air_RelRule_Predicate'];
//					$result['SubjType'] 			= $dbRow['Air_RelRule_Subject'];
//					$result['PredOrdSpec'] 		= $dbRow['Air_RelRule_PredOrd'];
//					$result['PredCardSpec'] 	= $dbRow['Air_RelRule_PredCard'];
//					$result['PredCardLimit']	= $dbRow['Air_RelRule_PredMax'];
					}
				}

			if (! empty($subjArray))
				{
				asort($subjArray);
				$this->setSimpleListDisplay('Attribute of', '', $subjArray);
				}
			if (! empty($objArray))
				{
				asort($objArray);
				$this->setSimpleListDisplay('Link to', '', $objArray);
				}
			if (! empty($iobjArray))
				{
				asort($iobjArray);
				$this->setSimpleListDisplay('Link via', '', $iobjArray);
				}
			if (! empty($diagArray))
				{
				asort($diagArray);
				$this->setSimpleListDisplay('Diagnostics', '', $diagArray);
				}
//			function setSimpleListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $ordered=true)
			}

		return;
		}

	/***************************************************************************
	 * showAirElementBaseInfo()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showAirElementBaseInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify))
			{
			$showAsArray = true;
			$showAsReadOnly = false;
			$showAsModifiable = true;
			}
		else
			{
			$showAsArray = false;
			$showAsReadOnly = true;
			$showAsModifiable = false;
			}

		/*
		 * Create context array and post context display information to SMARTY
		 */
		$dlgContext	= $this->procContext->getContextId();
		$this->setShowSelectionInfo('Context', true, AIR_EleType_Context, $dlgContext, $showAsArray, $showAsModifiable);
		switch ($this->myTxnSpec['EleType'])
			{
			case AIR_EleType_EleType:
				$this->anchor->setDlgVar('air_showParent', false);
		 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], true, false);
				break;
			case AIR_EleType_EleClass:
				$this->anchor->setDlgVar('air_showParent', true);
		 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], true, false);
				break;
			default:
				$this->anchor->setDlgVar('air_showParent', false);
		 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], false, false);
				break;
			}

 		$this->setShowSelectionInfo('EleType', true, AIR_EleType_EleType, $this->myTxnSpec['EleType'], $showAsArray, $showAsModifiable);

  		if (($this->myContextAction == AIR_Action_ShowItem)
  		 || ($this->myContextAction == AIR_Action_ShowRaw))
			{
			$dlgDataCaptureOper	= false;
			}
		else
		 	{
			$dlgDataCaptureOper	= true;
			}
		$this->anchor->setDlgVar('air_dataCaptureOper', $dlgDataCaptureOper);

		switch ($this->myContextAction)
		  	{
		  	case AIR_Action_Modify:
		  	case AIR_Action_ShowItem:
		  	case AIR_Action_ShowRaw:
		  	case AIR_Action_DeleteItem:
	   	case AIR_Action_PurgeType:
			case AIR_Action_AuditItem:
		  		$this->showAirElementMaintInfo();
		  		break;
			}
		$this->anchor->setDialogAction($this->myTxnSpec['TxnOper']);

		/*
		 * The following variables are typically missing and result in empty
		 * values being passed. However, when diagnostics are reported rather
		 * than accepting the input data, this causes the original data to be
		 * pre-filled on the replay of the form rather than being lost.
		 */
		$this->anchor->setDlgVar('Ele_EleName',		$this->myTxnSpec['EleName']);
		$this->anchor->setDlgVar('Ele_ChgComments',	$this->myTxnSpec['EleChgComments']);
		}

	/***************************************************************************
	 * showAirElementBaseInfo2()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showAirElementBaseInfo2()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Set display options for key control values that should not be modified as
		 * part of detail item maintenance. These should be changed using transaction
		 * mechanisms that are specifically geared toward those changes. Changing both
		 * the contextual model and the detail model within the same transaction mechanism
		 * is not allowed.
		 */
		$showAsArray		= false;
		$showAsModifiable = false;
		$showAsReadOnly	= true;

		/*
		 * Post context and element property information to the display management array
		 */
		$dlgContext	= $this->procContext->getContextId();

		$this->setSelectionListInfo('Model Context', 'Context',  AIR_EleType_Context,  $dlgContext,                  $showAsArray, $showAsReadOnly);
		$this->setSelectionListInfo('Element Type',  'EleType',  AIR_EleType_EleType,  $this->myTxnSpec['EleType'],  $showAsArray, $showAsReadOnly);
		if ($this->procContext->getSessionData('txnContextEleType') == $this->myTxnSpec['EleType'])
			{
  			$eleTypeClass = $this->procContext->getSessionData('txnContextEleTypeClass');
			}
		else
			{
			if (! empty($this->myTxnSpec['EleType']))
				{
	  			$eleTypeDoc = & $this->anchor->getRefElement($this->myTxnSpec['EleType']);
  				if (is_object($eleTypeDoc))
  					{
		  			$eleTypeClass = $eleTypeDoc->getElementData('Class');
					$this->setSelectionListInfo('Type Class',		'EleClass', AIR_EleType_EleClass, $eleTypeClass, $showAsArray, $showAsReadOnly);
		  			}
		  		}
  			}
		$this->setDisplayRule();

		if (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify))
			{
			$showAsArray		= true;
			$showAsModifiable	= true;
			$showAsReadOnly	= false;
			}
		else
			{
			$showAsArray		= false;
			$showAsModifiable = false;
			$showAsReadOnly	= true;
			}

  		if (($this->myContextAction == AIR_Action_ShowItem)
  		 || ($this->myContextAction == AIR_Action_ShowRaw))
			{
			$dlgDataCaptureOper	= false;
			}
		else
		 	{
			$dlgDataCaptureOper	= true;
			}
		$this->anchor->setDlgVar('air_dataCaptureOper', $dlgDataCaptureOper);

		switch ($this->myContextAction)
		  	{
		  	case AIR_Action_Modify:
		  	case AIR_Action_ShowItem:
		  	case AIR_Action_ShowRaw:
		  	case AIR_Action_DeleteItem:
	   	case AIR_Action_PurgeType:
	   	case AIR_Action_AuditItem:
		  		$this->showAirElementMaintInfo();
		  		break;
			}
		$this->anchor->setDialogAction($this->myTxnSpec['TxnOper']);

		/*
		 * The following variables are typically missing and result in empty
		 * values being passed. However, when diagnostics are reported rather
		 * than accepting the input data, this causes the original data to be
		 * pre-filled on the replay of the form rather than being lost.
		 */
		$this->setTextDisplay('Element Name',		'dlgEleName',			$this->myTxnSpec['EleName'],			$showAsReadOnly);
		if ($dlgDataCaptureOper)
			{
			$this->setTextboxDisplay('Update Comments',	'dlgEleChgComments', $this->myTxnSpec['EleChgComments'], false);
			}
		}

	/***************************************************************************
	 * showAirElementMaintInfo()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showAirElementMaintInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (array_key_exists('EleCreateEntity', $this->myTxnSpec))
			{
			$authorName = '';
			$authorId	= $this->myTxnSpec['EleCreateEntity'];
 			if (! empty($authorId))
 				{
		 		$authorName = $this->anchor->getRefName($authorId);
		 		}
 			$modifierName 	= '';
			$modifierId		= $this->myTxnSpec['EleLastChgEntity'];
 			if (! empty($modifierId))
 				{
		 		$modifierName = $this->anchor->getRefName($modifierId);
		 		}

			$this->anchor->setDlgVar('Ele_CreateEntity', $authorName);
			$this->anchor->setDlgVar('Ele_CreateDt',		$this->myTxnSpec['EleCreateDt']);
			$this->anchor->setDlgVar('Ele_ChgEntity',		$modifierName);
			$this->anchor->setDlgVar('Ele_ChgDt',			$this->myTxnSpec['EleLastChgDt']);
			switch ($this->myTxnSpec['EleLastChgType'])
				{
				case AIR_EleChgType_Insert: $chgType = 'Add';		break;
				case AIR_EleChgType_Modify: $chgType = 'Update';	break;
				case AIR_EleChgType_Delete: $chgType = 'Delete';	break;
				case AIR_EleChgType_Null:
				default:							 $chgType = 'huh?';		break;
				}
			$this->anchor->setDlgVar('Ele_ChgType', $chgType);
			$this->anchor->setDlgVar('Ele_ChgComments',	$this->myTxnSpec['EleLastChgComments']);
			}
		$this->anchor->setDlgVar('air_showSaveCommand', false);
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