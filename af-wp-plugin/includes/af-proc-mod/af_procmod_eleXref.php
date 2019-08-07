<?php
/*
 * AirLib script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.2 2005-SEP-08 JVS Original code
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-11 JVS Begin of data structure reshape as part of extension
 *								past properties to encoding of associations as part
 *								of the element specification.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is the primary business logic processing module for BEAMS 'rules'
 * element types.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ProcModEleAudit';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ProcModEleAudit extends C_AirProcModBase {
	var $eleStructByUuidTable		= array();		// Element Structure Properties table
	var $eleStructByNameTable		= array();		// Element Structure Properties table

	// --------------------------------------------------------
	// Constructor
	//
	// Initialize the local variable store and creates a local
	// reference to the AIR_anchor object for later use in
	// detail function processing. (Be careful with code here
	// to ensure that we are really talking to the right object.)
	// --------------------------------------------------------
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
	 * ProcMod_EleAuditInit
	 *
	 *******/
	function ProcMod_EleAuditInit()
	 	{
		$reportEmptyRefs = false;

	 	/*
	 	 * Create a list of all the relationship types
	 	 */
	 	$typeList	= array();
	 	$typeList['Property']		= AIR_EleType_PropType;
	 	$typeList['Association']	= AIR_EleType_AssocType;
	 	$typeList['Coord Model']	= AIR_EleType_CoordType;

		/*
		 * Create a list of all the individual relationship elements of each type
		 */
	 	foreach ($typeList as $relLabel => $relType)
	 		{
			$dataArray		= $this->anchor->get_allElementsByType($relType, 0);
			$entries 		= count($dataArray);
			if ($entries > 0)
				{
				foreach ($dataArray as $key => $value)
					{
					$eleDoc = & $this->anchor->Repository->getTypedAirDocument($key, NULL);
					$eleName = $eleDoc->getPreferredName();
					$foundsAs = 'literal';
					$itemName = $eleDoc->getElementData('FldName');
					if (empty($itemName))
						{
						$foundsAs = 'UUID';
						$itemName = $eleDoc->getElementData(AIR_PropType_MD_FldName);
						}
					if (empty($itemName))
						{
						$foundsAs = '';
						if ($reportEmptyRefs)
							{
							$msgTgt		= $eleName;
							$msgDiag		= $relLabel.' has no field name.';
							$this->anchor->setDialogDiagnostic($msgTgt, $msgDiag, AIR_DiagMsg_Error);
							}
						}
					else
						{
						$this->initEleStuctPropertyTable($key,	$itemName, $relLabel);
						}
					}
				}
			}
		}

	/***************************************************************************
	 * initEleStuctPropertyTable
	 * Detail line item initialization for the Element Structure Properties table
	 *******/
	function initEleStuctPropertyTable($propUUID, $propFldName, $relType)
		{
		$detailEntry					= array();
		$detailEntry['UUID']			= $propUUID;
		$detailEntry['FldName']		= $propFldName;
		$detailEntry['RelType']		= $relType;
		$detailEntry['Count']		= 0;
		$detailEntry['Collision']	= 0;
		$this->eleStructByUuidTable[$propUUID] 	= $detailEntry;
		$this->eleStructByNameTable[$propFldName] = $detailEntry;
		}

	/***************************************************************************
	 * ProcMod_Main
	 *
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT PROC +++ AUDIT PROC +++ AUDIT PROC +++ AUDIT PROC +++++++++');
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		   }

		parent::initialize($procContext, $baseMsg, $procMsg);
		$this->ProcMod_EleAuditInit();

		$testValue = $this->procMsg->getMessageData('EleType');
		if (! empty($testValue))
			{
			$result = $this->createTxnDataArrayFromProcMsg();
			if ($result < 0)
				{
				trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
				}
			}
		$this->initResultMsg();
		$this->myDialog	= Dialog_EleXref;

		$msgDiag		= 'object = ['.$this->myMsgObject.'] action = ['.$this->myMsgAction.']';
		$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);

		switch ($this->myMsgAction)
			{
			case AIR_Action_AuditItem:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT == ITEM');
				$this->procAuditItem();
				break;
			case AIR_Action_AuditType:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT == TYPE');
				$this->procAuditType();
				break;
			case AIR_Action_AuditAll:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT == ALL');
				$this->procAuditRepository();
				break;
			case AIR_Action_Create:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == CREATE');
				$this->procDbConvertCreate();
				break;
			case AIR_Action_Register:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == REGISTER');
				$this->procDbConvertRegister();
				break;
			case AIR_Action_CodeConvert:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == CODE CONVERT');
				$this->procDbConvertCode();
				break;
			case AIR_Action_IdentConvert:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == IDENT CONVERT');
				$this->procDbConvertIdent();
				break;
			case AIR_Action_DbRefConvert:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == DB REF CONVERT');
				$this->procDbRefConvert();
				break;
			case AIR_Action_GetDbStats:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == DB GET STATS');
				$this->procDbGetStats();
				break;
			case AIR_Action_CleanseDb:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == CLEANSE DB');
				$this->procCleanseDb();
				break;
			case AIR_Action_Load:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == LOAD');
				$this->procDbConvertLoad();
				break;
			case AIR_Action_View:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == VIEW');
				$this->procDbConvertView();
				break;
			case AIR_Action_Execute:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == EXECUTE');
				$this->procDbConvertExecute();
				break;
			case AIR_Action_ShowItem:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ DB CONVERT == SHOWITEM');
				$this->procDbConvertShowItem();
				break;
			default:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT == DEFAULT');
				$this->procDefault();
				break;
			}

		$result = $this->publishTxnDataArrayToResultMsg();
		if ($result < 0)
			{
			trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
			}

		$this->postResultMsg();

		$this->anchor->putTraceData(__LINE__, __FILE__, 'Completing ' . __CLASS__ . '::' . __FUNCTION__);
		$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT PROC +++ AUDIT PROC +++ AUDIT PROC +++ AUDIT PROC +++++++++');
		}

	/***************************************************************************
	 * procAuditItem
	 *
	 *******/
	function procAuditItem()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Determine if there were any elements identified on the input message.
		 */
		$collectionSize = $this->procMsg->getDataCollectionItemCount('EleIdent');
		if ($collectionSize > 0)
			{
			/*
			 * Elements identified on the input message. Create a new collection of
			 * element identifiers in the session document, or add to the existing
			 * list of elements being processed.
			 */
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$eleIdent = $this->procMsg->getDataCollectionItemContent('EleIdent', $i);
				$node = $this->procContext->createTextElement('EleIdent', $eleIdent);
				$this->procContext->createNewDataCollectionItem($node);
				}
			}

		/*
		 * Determine if there are any elements identified on the session.
		 */
		$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
		if ($collectionSize > 0)
			{
			$this->procAuditCollection();
			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'No elements selected');

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procAuditType
	 *******/
	function procAuditType()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Determine if there was an element type identified on the input message.
		 */
		$testValue = $this->procMsg->getMessageData('TxnStepOper');
		if ($testValue == 'Init')
			{
			$testValue = $this->procMsg->getMessageData('EleType');
			if ((!empty($testValue))
			 && ($testValue != AIR_Null_Identifier))
				{
				/*
				 * Element type identified on the input message. Create a new collection of
				 * element identifiers in the session document, or add to the existing
				 * list of elements being processed.
				 */
				$dataArray		= $this->anchor->get_allElementsByType($testValue, 0);
				$entries 		= count($dataArray);
				if ($entries > 0)
					{
					foreach ($dataArray as $key => $value)
						{
						$node = $this->procContext->createTextElement('EleIdent', $key);
						$this->procContext->createNewDataCollectionItem($node);
						}
					}
				}
			else
				{
				/*
				 * This was the initial message, there's something wrong!
				 */
				$this->resultMsg->attachDiagnosticTextItem('Element Type', 'Element specification not found');
				}
			}

		/*
		 * Determine if there are any elements identified on the session.
		 */
		$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
		if ($collectionSize > 0)
			{
			$this->procAuditCollection();
			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'No elements selected');

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procAuditRepository
	 *******/
	function procAuditRepository()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Create a new collection of element identifiers in the session document,
		 * or add to the existing list of elements being processed.
		 */
		$dataArray		= $this->anchor->get_allElementsArray();
		$entries 		= count($dataArray);
		if ($entries > 0)
			{
			foreach ($dataArray as $key => $value)
				{
				/*
				 * Value is an array with details: 'Name' and 'Type'
				 */
				$node = $this->procContext->createTextElement('EleIdent', $key);
				$this->procContext->createNewDataCollectionItem($node);
				}
			}

		/*
		 * Determine if there are any elements identified on the session.
		 */
		$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
		if ($collectionSize > 0)
			{
			$this->procAuditCollection();
			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'No elements selected');

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procAuditCollection
	 *******/
	function procAuditCollection()
		{
		$processLimit		= 1000;
		$processedItems	= 0;
		$bypassedLimit		= 3500;
		$bypassedItems		= 0;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$auditTimePt1			= time();

		/*
		 * Determine if there are any elements identified on the session.
		 */
		$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
		if ($collectionSize < 1)
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'No element specifications found');
			return;
			}

		/*
		 * Process the identified collection, up to a maximum of 'limit' items
		 */
		while (($collectionSize > 0)
			 && ($processedItems < $processLimit)
			 && ($bypassedItems < $bypassedLimit))
			{
			$identNode = $this->procContext->getDataCollectionItem('EleIdent', 0);
			$eleIdent	= $identNode->getContent();
			$this->procContext->removeDataCollectionItemByRef('EleIdent', $identNode);

			if ((!empty($eleIdent))
			 && ($eleIdent	!= AIR_Null_Identifier))
				{
				/*
				 * Obtain an C_AirElement encoding	for the element
				 */
				$eleDoc = & $this->anchor->Repository->getTypedAirDocument($eleIdent, NULL);
//				$itemName = $eleDoc->getPreferredName();
				$itemName = $eleDoc->getDocName();
				$itemType = $eleDoc->getDocType();
				/*
				 *
				 *

				if ($this->myMsgAction == AIR_Action_ShowRaw)
					{
					$this->backpostBaseTxnSpecArray($eleDoc);
					$serialized = $eleDoc->serialize(TRUE);
					$this->myTxnSpec['EleContent'] = htmlspecialchars($serialized, ENT_QUOTES);
					}
				else
					{
					$this->postEleDocToTxnDataArray($eleDoc);
					}
				 *
				 */

				if ($itemType == AIR_EleType_ArchMessage)
					{
//					$this->resultMsg->attachDiagnosticTextItem($itemName, 'Message Item Audit Bypassed');
					$bypassedItems++;
					}
				else
				if ($itemType == AIR_EleType_WebSession)
					{
//					$this->resultMsg->attachDiagnosticTextItem($itemName, 'Session Item Audit Bypassed');
					$bypassedItems++;
					}
				else
					{
					$eleDataNode = $eleDoc->getDocBodyNode('ElementData');
					if ($eleDataNode == null)
						{
//						$this->resultMsg->attachDiagnosticTextItem($itemName, 'ElementData not found');
						}
					else
						{
						if ($eleDataNode->hasChildNodes())
							{
							$childNodes = 0;
							$child = $eleDataNode->firstChild();
							while ($child)
								{
								if ($child instanceof C_DomElement)
									{
									$childNodes++;

									$refItemName = $child->tagName;
									if (array_key_exists($refItemName, $this->eleStructByNameTable))
										{
										$detailEntry				= $this->eleStructByNameTable[$refItemName];
										$references					= $detailEntry['Count'];
										$references++;
										$detailEntry['Count']	= $references;
										$this->eleStructByNameTable[$refItemName] = $detailEntry;
										}
									}
								$child = $child->nextSibling();
								}
							$this->resultMsg->attachDiagnosticTextItem($itemName, 'ElementData has ' . $childNodes . ' direct elements');
							}
						else
							{
//							$this->resultMsg->attachDiagnosticTextItem($itemName, 'ElementData has no detail');
							}
						}
					$processedItems++;
					}
				}

			$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
			}
		$auditTimePt2			= time();

		$reportEmptyRefs = false;
		foreach ($this->eleStructByNameTable as $key => $detailEntry)
			{
			$references					= $detailEntry['Count'];
			$relType						= $detailEntry['RelType'];
			if ($references)
				{
				$this->resultMsg->attachDiagnosticTextItem($key, $relType.' has '.$references.' references');
				}
			else
				{
				if ($reportEmptyRefs)
					{
					$this->resultMsg->attachDiagnosticTextItem($key,  $relType.' has no references');
					}
				}
			}

		$smryMsg = 'Processed '.$processedItems.' ['.$processLimit.'] Btpassed '.$bypassedItems.' ['.$bypassedLimit.']';
		$this->resultMsg->attachDiagnosticTextItem('SUMMARY', $smryMsg);

		$auditTimeEnd			= time();
		$auditTime				= $auditTimeEnd - $auditTimePt1;
		$auditTime1				= $auditTimePt2 - $auditTimePt1;
		$auditTime2				= $auditTimeEnd - $auditTimePt2;
		$smryMsg = 'Audit took '.$auditTime.' seconds, '.$auditTime1.' sec scanning, '.$auditTime2.' sec composing';
		$this->resultMsg->attachDiagnosticTextItem('TIMING', $smryMsg);

		return;
		}

	/***************************************************************************
	 * procDbConvertCreate
	 *******/
	function procDbConvertCreate()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$readyToGo = false;

		/*
		 * Create a new C_AirElement encoding	for the conversion manifest
		 */
		$testValue	= $this->procMsg->getMessageData('EleType');
		$manifestName	= '';
		if (!empty($testValue))
			{
			$manifestName 	= $this->anchor->Repository->getElementName($testValue).' ';
			}
		$manifestName	= $manifestName.'Conversion Manifest '.date('j F Y H:i');
		$newEle = $this->anchor->createAirElementDoc(AIR_EleType_EleManifest,
																	$manifestName,
																	$this->procContext->getLoggedUserId(),
																	'Original Entry');
		$newEleType 		= $newEle->getDocType();
		$manifestId 		= $newEle->getDocumentId();
		$createTime			= time();

		/*
		 * Determine the scope of the operation, and create an array of
		 * identifiers for the appropriate database elements.
		 */
		$dataArray	= array();
		if ((!empty($testValue))
		 && ($testValue != AIR_Null_Identifier))
			{
			/*
			 * Element type identified on the input message. Create a new collection of
			 * element identifiers in the session document, or add to the existing
			 * list of elements being processed.
			 */
			$dataArray		= $this->anchor->get_allElementsByType($testValue, 0);
			}
		else
			{
			$dataArray		= $this->anchor->get_allElementsArray();
			}

		$entries 		= count($dataArray);
		if ($entries > 0)
			{
			foreach ($dataArray as $key => $value)
				{
				if (($value['Type'] != AIR_EleType_ArchMessage)
				 && ($value['Type'] != AIR_EleType_WebSession))
					{
					switch ($key)
						{
						case AIR_Null_Identifier:
							$newkey = NEW_Null_Identifier;
							break;
						case AIR_Any_Identifier:
							$newkey = NEW_Any_Identifier;
							break;
						case AIR_All_Identifier:
							$newkey = NEW_All_Identifier;
							break;
						default;
							$newkey = $this->anchor->create_UUID();
							break;
						}

					$manifestItem = $newEle->createElement('Item');

					$node = $newEle->createTextElement('OldKey', $key);
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('NewKey', $newkey);
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('Name', $value['Name']);
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('Type', $value['Type']);
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('Assigned', $createTime);
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('Created', '');
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('Reviewed', '');
					$manifestItem->appendChild($node);

					$node = $newEle->createTextElement('Converted', '');
					$manifestItem->appendChild($node);

					$newEle->createNewDataCollectionItem($manifestItem);
					}
				}
			/*
			 * If this is an 'all elements' conversion,
			 * include the manifest in the array.
			 */
			if ((empty($testValue))
			 || ($testValue == AIR_Null_Identifier)
			 || ($testValue == AIR_EleType_EleManifest))
				{
				$manifestItem = $newEle->createElement('Item');

				$node = $newEle->createTextElement('OldKey', $manifestId);
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('NewKey', $this->anchor->create_UUID());
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('Name', $manifestName);
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('Type', AIR_EleType_EleManifest);
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('Assigned', $createTime);
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('Created', '');
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('Reviewed', '');
				$manifestItem->appendChild($node);

				$node = $newEle->createTextElement('Converted', '');
				$manifestItem->appendChild($node);

				$newEle->createNewDataCollectionItem($manifestItem);
				}

			/*
			 * Persist the element definition to the database
			 */
	 		$newEle->persist();
			}


		/*
		 * Prep the follow-up step - display the manifest
		 */
		$this->myTxnSpec['TxnOper'] = AIR_Action_ShowItem;
		$this->resultMsg->putMessageData('EleType',	AIR_EleType_EleManifest);
		$this->resultMsg->putMessageData('EleCount',	'1');
		$this->resultMsg->putMessageData('EleIdent',	$newEle->getDocumentId());

		$encodeObject = $this->anchor->getProcModFromEleType(AIR_EleType_EleManifest);
		$encodeAction	= AIR_Action_ShowItem;
		$encodeVers		= '1.0';
/*******
		$encodeObject = Dialog_ManifestReview;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';
*********/

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procDbConvertRegister
	 *******/
	function procDbConvertRegister()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$missingManifest	= false;
		$cvtManifest		= '';

		/*
		 * See if we have a conversion manifest identified from either
		 * the drop-down on the menu, or from the selection list.
		 */
		$cvtManifest		= $this->procMsg->getMessageData('EleItem');

		if ((empty($cvtManifest))
		 || ($cvtManifest == NEW_Null_Identifier)
		 || ($cvtManifest == AIR_Null_Identifier))
		 	{
 			$collectionSize = $this->procMsg->getDataCollectionItemCount('EleIdent');
			if ($collectionSize == 1)
				{
				$cvtManifest		= $this->procMsg->getMessageData('EleIdent');
				}
			else
				{
				$missingManifest	= true;
				}
		 	}

		if (! $missingManifest)
		 	{
			/*
			 * Obtain an C_AirElement encoding	for the manifest
			 */
			$manifest = & $this->anchor->Repository->getTypedAirDocument($cvtManifest, NULL);
			$docType	 = $manifest->getDocType();
			if ($docType != AIR_EleType_EleManifest)
				{
				$missingManifest = true;
				}
		 	}

		if ($missingManifest)
			{
			$air_eleArray 	= $this->anchor->get_allElementsByType(AIR_EleType_EleManifest);
			if (count($air_eleArray) > 1)
				{
				$this->procContext->putSessionData('cvtContextManifest', AIR_Null_Identifier);

				$this->myTxnSpec['TxnOper']		= AIR_Action_Register;
				$this->myTxnSpec['TxnStepOper']	= 'Init';
				$this->myTxnSpec['EleType']		= AIR_EleType_EleManifest;

				$encodeObject = Dialog_EleList;
				$encodeAction	= AIR_Action_Show;
				$encodeVers		= '1.0';
				}
			else
				{
				if (empty($air_eleArray))
					{
					$this->resultMsg->attachDiagnosticTextItem('', 'No manifests available for registration.');
					}
				else
					{
					foreach ($air_eleArray as $key => $value)
						{
						$cvtManifest = $key;
						}
					$this->procContext->putSessionData('cvtContextManifest', $cvtManifest);
					}

				$encodeObject = Dialog_MenuSelect;
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			}
		else
			{
			$this->procContext->putSessionData('cvtContextManifest', $cvtManifest);

			$encodeObject = Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procDbConvertCode
	 *******/
	function procDbConvertCode()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors				= false;
		$cvtElementList	= array();

		/*
		 * First get the manifest
		 */
		$cvtManifest		= $this->procContext->getSessionData('cvtContextManifest');
		if ((empty($cvtManifest))
		 || ($cvtManifest == NEW_Null_Identifier)
		 || ($cvtManifest == AIR_Null_Identifier))
			{
			$errors	= true;
			$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification not found');
			}

		if (! $errors)
		 	{
			/*
			 * Obtain an C_AirElement encoding	for the manifest
			 */
			$manifest = & $this->anchor->Repository->getTypedAirDocument($cvtManifest, NULL);
			$docType	 = $manifest->getDocType();
			if ($docType != AIR_EleType_EleManifest)
				{
				$errors = true;
				$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification is not valid');
				}
			else
				{
				$collectionSize = $manifest->getDataCollectionItemCount('Item');
				if ($collectionSize > 0)
					{
					// Found, post the data
		//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Collection has $collectionSize items");
					for ($i = 0; $i < $collectionSize; $i++)
						{
						$collectionItemNode 	= $manifest->getDataCollectionItem('Item', $i);
						$manifestItem 			= array();

						$manifestItem['OldKey']		= $collectionItemNode->getChildContentByName('OldKey');
						$manifestItem['NewKey']		= $collectionItemNode->getChildContentByName('NewKey');
						$manifestItem['Name']		= $collectionItemNode->getChildContentByName('Name');
						$manifestItem['Type']		= $collectionItemNode->getChildContentByName('Type');
						$manifestItem['Assigned']	= $collectionItemNode->getChildContentByName('Assigned');
						$manifestItem['Created']	= $collectionItemNode->getChildContentByName('Created');
						$manifestItem['Reviewed']	= $collectionItemNode->getChildContentByName('Reviewed');
						$manifestItem['Converted']	= $collectionItemNode->getChildContentByName('Converted');

						$cvtElementList[] = $manifestItem;
						}

					if (count($cvtElementList) > 0)
						{
						$cvtCount = $this->procDbConvertCodeFiles($cvtElementList);
						$this->resultMsg->attachDiagnosticTextItem('', $cvtCount.' code files converted.');
						}
					else
						{
						$errors = true;
						$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest has no remaining work');
						}
					}
				else
					{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest has no detail items');
					}
				}
		 	}

//		if ($errors)
//			{
			$encodeObject = Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
//			}
//		else
//			{
//			$this->myTxnSpec['TxnOper']		= AIR_Action_Register;
//			$this->myTxnSpec['TxnStepOper']	= 'Init';
//			$this->myTxnSpec['EleType']		= AIR_EleType_EleManifest;

//			$encodeObject = Dialog_EleList;
//			$encodeAction	= AIR_Action_Encode;
//			$encodeVers		= '1.0';
//			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procDbConvertCodeFiles
	 *******/
	function procDbConvertCodeFiles($cvtElementList)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$directory	= AF_ROOT_DIR.'/scripts/conversion/';
		$errors		= false;
		$cvtCount	= 0;

		$errFlag = $this->anchor->getSuppressErrorMsgs();
		$this->anchor->setSuppressErrMsgs(true);
		$dir = @opendir($directory);
		$this->anchor->setSuppressErrMsgs($errFlag);

		if ($dir)
			{
			$fileCount	= 0;
			while ($file = readdir($dir))
				{
				$pathName = $directory.$file;
				$pathInfo = pathinfo($pathName);
				$pathInfo['dirname']		= strtolower($pathInfo['dirname']);
				$pathInfo['basename']	= strtolower($pathInfo['basename']);
				if (array_key_exists('extension', $pathInfo))
					{
					$pathInfo['extension']	= strtolower($pathInfo['extension']);
					}
				else
					{
					$pathInfo['extension']	= '';
					}
				if (is_file($pathName))	// no directories or links
					{
					$errFlag = $this->anchor->getSuppressErrorMsgs();
					$this->anchor->setSuppressErrMsgs(true);
					$fileContent	= $this->anchor->getFileContent($pathName);
					$this->anchor->setSuppressErrMsgs($errFlag);

					$replCount = 0;
					foreach($cvtElementList as $cvtElementItem)
						{
						$fileContent = $this->procSubstituteUuidCodes($replCount, $fileContent, $cvtElementItem);
						}

					if ($replCount > 0)
						{
						$pathName = $directory.$file.'.new';
						$errFlag = $this->anchor->getSuppressErrorMsgs();
						$this->anchor->setSuppressErrMsgs(true);
						$success	= $this->anchor->putFileContent($pathName, $fileContent);
						$this->anchor->setSuppressErrMsgs($errFlag);
						$cvtCount++;
						$diagnostic = $replCount.' changes made to '.$file;
						$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Info);
						if (!$success)
							{
							$errors = true;
							$diagnostic = 'Problems rewriting '.$file;
							$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Error);
							}
						}
					}
				}
			closedir($dir);
			}
		else
			{
			$errors = true;
			$diagnostic = 'Code conversion directory not found.';
			$this->resultMsg->attachDiagnosticTextItem('', $diagnostic, AIR_DiagMsg_Error);
			}

		return($cvtCount);
		}

	/***************************************************************************
	 * procSubstituteUuidCodes
	 *******/
	function procSubstituteUuidCodes(& $replCount, $fileContent, $cvtElementItem)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$oldKey	= $cvtElementItem['OldKey'];
		$newKey	= $cvtElementItem['NewKey'];

		if ($oldKey == $newKey)
			{
			return($fileContent);
			}

		$oldKeySize	= strlen($oldKey);
		$newKeySize	= strlen($newKey);
		$more			= true;
		$startPos	= 0;
		$fileSize	= strlen($fileContent);
//		echo 'Content size is '.$fileSize.'<br/>';
//		echo 'oldKeySize size is '.$oldKeySize.'<br/>';
//		echo 'newKeySize size is '.$newKeySize.'<br/>';
		while ($more)
			{
			$keyPos = strpos($fileContent, $oldKey, $startPos);
			if ($keyPos === false)
				{
//				echo 'End of search for '.$oldKey.'<br/>';
				$more = false;
				}
			else
				{
//				echo 'Substituting for '.$oldKey.' at position '.$keyPos.' of '.$fileSize.'<br/>';
	         $size1			= $keyPos;
	         $size2			= $fileSize - $size1;
	         $size2			= $size2 - $oldKeySize;
	         $fileContent	= substr($fileContent,0,$size1).$newKey.substr($fileContent,$keyPos+$oldKeySize,$size2);
				$startPos		= $keyPos + $newKeySize;
	         $fileSize 		= strlen($fileContent);
				$replCount 	  += 1;
				}
			}

		return($fileContent);
		}

	/***************************************************************************
	 * procDbConvertView
	 *******/
	function procDbConvertView()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Determine if there were any elements identified on the input message.
		 */
		$collectionSize = $this->procMsg->getDataCollectionItemCount('target');
		if ($collectionSize > 0)
			{
			/*
			 * Elements identified on the input message. Create a new collection of
			 * element identifiers in the session document, or add to the existing
			 * list of elements being processed.
			 */
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$eleIdent = $this->procMsg->getDataCollectionItemContent('target', $i);
				}
			}

		if ((empty($eleIdent))
		 || ($eleIdent	== AIR_Null_Identifier))
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'Element specification not found');

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->myTxnSpec['EleIdent'] = $eleIdent;

			$encodeObject	= ProcMod_EleMaint;
			$encodeAction	= AIR_Action_ShowItem;
			$encodeVers		= '1.0';
			}


		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procDbConvertShowItem
	 *******/
	function procDbConvertShowItem()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Determine if there were any elements identified on the input message.
		 */
		$collectionSize = $this->procMsg->getDataCollectionItemCount('EleIdent');
		if ($collectionSize > 0)
			{
			/*
			 * Elements identified on the input message. Create a new collection of
			 * element identifiers in the session document, or add to the existing
			 * list of elements being processed.
			 */
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$eleIdent = $this->procMsg->getDataCollectionItemContent('EleIdent', $i);
				$node = $this->procContext->createTextElement('EleIdent', $eleIdent);
				$this->procContext->createNewDataCollectionItem($node);
				}
			}

		/*
		 * Determine if there are any elements identified on the session.
		 */
		$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
		if ($collectionSize > 0)
			{
			if ($collectionSize > 1)
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Found $collectionSize session EleIdent identifiers, using first one!");
//				trigger_error("Found $collectionSize session EleIdent identifiers, using first one!", E_USER_NOTICE);
				}
			$identNode = $this->procContext->getDataCollectionItem('EleIdent', 0);
			$eleIdent	= $identNode->getContent();
			$this->procContext->removeDataCollectionItemByRef('EleIdent', $identNode);
			}
		else
			{
			if (($this->anchor != NULL) && ($this->anchor->trace()))
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " EleIdent not found!");
				}
			$cvtManifest		= $this->procContext->getSessionData('cvtContextManifest');
			if ((empty($cvtManifest))
			 || ($cvtManifest == NEW_Null_Identifier)
			 || ($cvtManifest == AIR_Null_Identifier))
				{
				$eleIdent			= AIR_Null_Identifier;
				}
			else
				{
				$eleIdent			= $cvtManifest;
//				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " EleIdent defaulted from conversion contrext.");
					}
				}
			}

		if ((empty($eleIdent))
		 || ($eleIdent	== AIR_Null_Identifier))
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'Element specification not found');

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			/*
			 * Obtain an C_AirElement encoding	for the element
			 */
			$eleDoc = & $this->anchor->Repository->getTypedAirDocument($eleIdent, NULL);

			$collectionSize = $eleDoc->getDataCollectionItemCount('Item');
			if ($collectionSize > 0)
				{
				// Found, post the data
	//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Collection has $collectionSize items");
				for ($i = 0; $i < $collectionSize; $i++)
					{
					$collectionItemNode 	= $eleDoc->getDataCollectionItem('Item', $i);
					$manifestItem 			= $this->resultMsg->createElement('Item');

					$node = $this->resultMsg->createTextElement('OldKey',		$collectionItemNode->getChildContentByName('OldKey'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('NewKey',		$collectionItemNode->getChildContentByName('NewKey'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('Name',		$collectionItemNode->getChildContentByName('Name'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('Type',		$collectionItemNode->getChildContentByName('Type'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('Assigned',	$collectionItemNode->getChildContentByName('Assigned'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('Created',	$collectionItemNode->getChildContentByName('Created'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('Reviewed',	$collectionItemNode->getChildContentByName('Reviewed'));
					$manifestItem->appendChild($node);

					$node = $this->resultMsg->createTextElement('Converted',	$collectionItemNode->getChildContentByName('Converted'));
					$manifestItem->appendChild($node);

					$this->resultMsg->createNewDataCollectionItem($manifestItem);
					}
				}
			$this->procContext->putSessionData('eleMaintObject', $eleIdent);

			$debugAction = $this->procContext->getSessionData('eleMaintAction');
			$debugObject = $this->procContext->getSessionData('eleMaintObject');
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " eleMaintAction = $debugAction");
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " eleMaintObject = $debugObject");

			$encodeObject	= Dialog_ManifestReview;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}


	/***************************************************************************
	 * procDbConvertIdent
	 *******/
	function procDbConvertIdent()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors				= false;
		$cvtElementList	= array();

		/*
		 * First get the manifest
		 */
		$cvtManifest		= $this->procContext->getSessionData('cvtContextManifest');
		if ((empty($cvtManifest))
		 || ($cvtManifest == NEW_Null_Identifier)
		 || ($cvtManifest == AIR_Null_Identifier))
			{
			$errors	= true;
			$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification not found');
			}

		if (! $errors)
		 	{
			/*
			 * Obtain an C_AirElement encoding	for the manifest
			 */
			$manifest = & $this->anchor->Repository->getTypedAirDocument($cvtManifest, NULL);
			$docType	 = $manifest->getDocType();
			if ($docType != AIR_EleType_EleManifest)
				{
				$errors = true;
				$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification is not valid');
				}
			else
				{
				$collectionSize = $manifest->getDataCollectionItemCount('Item');
				if ($collectionSize > 0)
					{
					// Found, post the data to the session worklist
		//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Collection has $collectionSize items");
					for ($i = 0; $i < $collectionSize; $i++)
						{
						$collectionItemNode 	= $manifest->getDataCollectionItem('Item', $i);
						$manifestItem 			= $collectionItemNode->getChildContentByName('OldKey');
						$this->procContext->appendSessionDataCollection('ConvertItem', $manifestItem);
						}
					}
				else
					{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest has no detail items');
					}
				}
		 	}

		if ($errors)
			{
			$encodeObject = Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->procContext->putSessionData('cvtContextAction', $this->myMsgAction);
			$this->myTxnSpec['TxnOper']		= $this->myMsgAction;

			$encodeObject = ProcMod_EleAudit;
			$encodeAction	= AIR_Action_ShowItem;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procDbRefConvert
	 *******/
	function procDbRefConvert()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors				= false;
		$cvtMasterList		= array();
		$cvtElementList	= array();
		$ruleCount			= 0;
		$propCount			= 0;
		$assocCount			= 0;
		$relationCount		= 0;
		$ruleEleSwaps		= 0;
		$propEleSwaps		= 0;
		$assocEleSwaps		= 0;
		$relationEleSwaps	= 0;
		$cvtTimePt1			= time();
		/*
		 * First get the manifest
		 */
		$cvtManifest		= $this->procContext->getSessionData('cvtContextManifest');
		if ((empty($cvtManifest))
		 || ($cvtManifest == NEW_Null_Identifier)
		 || ($cvtManifest == AIR_Null_Identifier))
			{
			$errors	= true;
			$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification not found');
			}

		if (! $errors)
		 	{
			/*
			 * Obtain an C_AirElement encoding	for the manifest
			 */
		$this->anchor->putTraceData(__LINE__, __FILE__, 'obtaining manifest '.$cvtManifest);
			$manifest = & $this->anchor->Repository->getTypedAirDocument($cvtManifest, NULL);
			$docType	 = $manifest->getDocType();
			if ($docType != AIR_EleType_EleManifest)
				{
				$errors = true;
				$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification is not valid');
				}
		 	}

		/*
		 * If no error identified, we have a manifest, and we have a target list.
		 */
		if (! $errors)
		 	{
	 		/*
	 		 * First, build a conversion array to drive the UUID exchange process
	 		 */
			$manifestSize = $manifest->getDataCollectionItemCount('Item');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'manifest size = '.$manifestSize);
			if ($manifestSize > 0)
				{
				for ($i = 0; $i < $manifestSize; $i++)
					{
					$collectionItemNode 	= $manifest->getDataCollectionItem('Item', $i);

					/*
					 * Build a conversion array of all UUID entries to be changed
					 */
					$manifestEntry 					= array();
					$manifestEntry['OldKey']		= $collectionItemNode->getChildContentByName('OldKey');
					$manifestEntry['NewKey']		= $collectionItemNode->getChildContentByName('NewKey');
//					$manifestEntry['Name']			= $collectionItemNode->getChildContentByName('Name');
//					$manifestEntry['Type']			= $collectionItemNode->getChildContentByName('Type');
//					$manifestEntry['Assigned']		= $collectionItemNode->getChildContentByName('Assigned');
//					$manifestEntry['Created']		= $collectionItemNode->getChildContentByName('Created');
//					$manifestEntry['Reviewed']		= $collectionItemNode->getChildContentByName('Reviewed');
//					$manifestEntry['Converted']	= $collectionItemNode->getChildContentByName('Converted');
					$cvtMasterList[] 					= $manifestEntry;
					}

				/*
				 * Create additional 'magic cookie' UUID entries
				 */
				$manifestEntry 					= array();
				$manifestEntry['OldKey']		= AIR_Null_Identifier;
				$manifestEntry['NewKey']		= NEW_Null_Identifier;
				$cvtMasterList[] 					= $manifestEntry;

				$manifestEntry 					= array();
				$manifestEntry['OldKey']		= AIR_Any_Identifier;
				$manifestEntry['NewKey']		= NEW_Any_Identifier;
				$cvtMasterList[] 					= $manifestEntry;

				$manifestEntry 					= array();
				$manifestEntry['OldKey']		= AIR_All_Identifier;
				$manifestEntry['NewKey']		= NEW_All_Identifier;
				$cvtMasterList[] 					= $manifestEntry;
				}

		$this->anchor->putTraceData(__LINE__, __FILE__, 'manifest list = '.count($cvtMasterList));
		 	/****************************************************************************/
		 	/****************************************************************************/
		 	/***                                                                      ***/
		 	/***                                                                      ***/
		 	/***                                                                      ***/
		 	/****************************************************************************/
		 	/****************************************************************************/

			$cvtTimePt2			= time();
			$rowCount	= $this->anchor->Repository->airDB->getCount_AirRelRules();
			if ($rowCount > 0)
				{
				$dbData		= $this->anchor->Repository->airDB->get_AllRelRules($rowCount);
				if ((!isset($dbData))
				 || (is_null($dbData))
				 || (!is_array($dbData)))
					{
					echo $this->anchor->whereAmI();
					die ('Invalid DB query return to ' . __FUNCTION__);
					}

				$ruleCount = count($dbData);
				if ($ruleCount > 0)
					{
					/* examine the rowset */
					foreach ($dbData as $dbRow)
						{
						$newArray      = array();
						$newArray['Air_Ele_Id']					= null;
						$newArray['Air_RelRule_Subject']		= null;
						$newArray['Air_RelRule_Predicate']	= null;
						$newArray['Air_RelRule_PredOrd']		= null;
						$newArray['Air_RelRule_PredCard']	= null;
						$newArray['Air_RelRule_PredMax']		= null;
						$newArray['Air_RelRule_Object']		= null;
						$newArray['Air_RelRule_IObject']		= null;
						$newArray['Air_RelRule_Diag']			= null;
						foreach ($dbRow as $key => $value)
							{
							$newValue = $value;
							foreach ($cvtMasterList as $manifestEntry)
								{
								if ($manifestEntry['OldKey'] == $value)
									{
									$newValue = $manifestEntry['NewKey'];
									$ruleEleSwaps++;
									break;
									}
								}
							$newArray[$key] = $newValue;
							}
						$success		= $this->anchor->Repository->airDB->insert_AirRelRulesItem($newArray);
						}
					}
				}
			$cvtTimePt3			= time();
			$rowCount	= $this->anchor->Repository->airDB->getCount_AirProperties();
			if ($rowCount > 0)
				{
				$dbData		= $this->anchor->Repository->airDB->get_AllProperties($rowCount);
				if ((!isset($dbData))
				 || (is_null($dbData))
				 || (!is_array($dbData)))
					{
					echo $this->anchor->whereAmI();
					die ('Invalid DB query return to ' . __FUNCTION__);
					}

				$propCount = count($dbData);
				if ($propCount > 0)
					{
					/* examine the rowset */
					foreach ($dbData as $dbRow)
						{
						$newArray      = array();
						$newArray['Air_Ele_Id']				= null;
						$newArray['Air_Prop_Subject']		= null;
						$newArray['Air_Prop_Predicate']	= null;
						$newArray['Air_Prop_Object']		= null;
						foreach ($dbRow as $key => $value)
							{
							$newValue = $value;
							foreach ($cvtMasterList as $manifestEntry)
								{
								if ($manifestEntry['OldKey'] == $value)
									{
									$newValue = $manifestEntry['NewKey'];
									$propEleSwaps++;
									break;
									}
								}
							$newArray[$key] = $newValue;
							}
						$success		= $this->anchor->Repository->airDB->insert_AirElePropertiesItem($newArray);
						}
					}
				}
			$cvtTimePt4			= time();
			$rowCount	= $this->anchor->Repository->airDB->getCount_AirAssociations();
			if ($rowCount > 0)
				{
				$dbData		= $this->anchor->Repository->airDB->get_AllAssociations($rowCount);
				if ((!isset($dbData))
				 || (is_null($dbData))
				 || (!is_array($dbData)))
					{
					echo $this->anchor->whereAmI();
					die ('Invalid DB query return to ' . __FUNCTION__);
					}

				$assocCount = count($dbData);
				if ($assocCount > 0)
					{
					/* examine the rowset */
					foreach ($dbData as $dbRow)
						{
						$newArray      = array();
						$newArray['Air_Ele_Id']				= null;
						$newArray['Air_Assoc_Subject']	= null;
						$newArray['Air_Assoc_Predicate']	= null;
						$newArray['Air_Assoc_Object']		= null;
						$newArray['Air_Assoc_IObject']	= null;
						foreach ($dbRow as $key => $value)
							{
							$newValue = $value;
							foreach ($cvtMasterList as $manifestEntry)
								{
								if ($manifestEntry['OldKey'] == $value)
									{
									$newValue = $manifestEntry['NewKey'];
									$assocEleSwaps++;
									break;
									}
								}
							$newArray[$key] = $newValue;
							}
						$success		= $this->anchor->Repository->airDB->insert_AirAssociationsItem($newArray);
						}
					}
				}
			$cvtTimePt5			= time();
			$rowCount	= $this->anchor->Repository->airDB->getCount_AirRelationships();
			if ($rowCount > 0)
				{
				$dbData		= $this->anchor->Repository->airDB->get_AllRelationships($rowCount);
				if ((!isset($dbData))
				 || (is_null($dbData))
				 || (!is_array($dbData)))
					{
					echo $this->anchor->whereAmI();
					die ('Invalid DB query return to ' . __FUNCTION__);
					}

				$relationCount = count($dbData);
				if ($relationCount > 0)
					{
					/* examine the rowset */
					foreach ($dbData as $dbRow)
						{
						$newArray      = array();
						$newArray['Air_Rel_Subject']		= null;
						$newArray['Air_Rel_Predicate']	= null;
						$newArray['Air_Rel_Object']		= null;
						$newArray['Air_Rel_RefCount']		= null;
						foreach ($dbRow as $key => $value)
							{
							$newValue = $value;
							foreach ($cvtMasterList as $manifestEntry)
								{
								if ($manifestEntry['OldKey'] == $value)
									{
									$newValue = $manifestEntry['NewKey'];
									$relationEleSwaps++;
									break;
									}
								}
							$newArray[$key] = $newValue;
							}
						$success		= $this->anchor->Repository->airDB->insert_AirEleRelationshipsItem($newArray);
						}
					}
				}
			}

		$cvtTimeEnd			= time();
		$cvtTime				= $cvtTimeEnd - $cvtTimePt1;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Overall main conversion process took '.$cvtTime.' seconds');
		$cvtTime				= $cvtTimePt2 - $cvtTimePt1;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Setup processing took '.$cvtTime.' seconds');
		$cvtTime				= $cvtTimePt3 - $cvtTimePt2;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Rules conversion processing took '.$cvtTime.' seconds for '.$ruleCount.' elements, '.$ruleEleSwaps.' key swaps');
		$cvtTime				= $cvtTimePt4 - $cvtTimePt3;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Properties conversion processing took '.$cvtTime.' seconds for '.$propCount.' elements, '.$propEleSwaps.' key swaps');
		$cvtTime				= $cvtTimePt5 - $cvtTimePt4;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Associations conversion processing took '.$cvtTime.' seconds for '.$assocCount.' elements, '.$assocEleSwaps.' key swaps');
		$cvtTime				= $cvtTimeEnd - $cvtTimePt5;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Relationships conversion processing took '.$cvtTime.' seconds for '.$relationCount.' elements, '.$relationEleSwaps.' key swaps');

		$encodeObject = Dialog_MenuSelect;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procCleanseDb
	 *******/
	function procCleanseDb()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleCount				= 0;
		$indexCount				= 0;
		$ruleCount				= 0;
		$propCount				= 0;
		$assocCount				= 0;
		$relationCount			= 0;
		$ruleRowsDeleted		= 0;
		$propRowsDeleted		= 0;
		$assocRowsDeleted		= 0;
		$relationRowsDeleted	= 0;
		$eleDelete				= array();
		$relDelete				= array();
		$cvtTimePt1				= time();

	 	/****************************************************************************/
	 	/****************************************************************************/
	 	/***                                                                      ***/
	 	/***                                                                      ***/
	 	/***                                                                      ***/
	 	/****************************************************************************/
	 	/****************************************************************************/
		$cvtTimePt2			= time();
		$rowCount	= $this->anchor->Repository->airDB->getCount_AirElements();
		if ($rowCount > 0)
			{
			$dbData		= $this->anchor->Repository->airDB->get_AllElements($rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			$eleCount = count($dbData);
			$eleDelete      = array();
			if ($eleCount > 0)
				{
				/* examine the rowset */
				foreach ($dbData as $dbRow)
					{
					$eleIdent	= $dbRow['Air_Ele_Id'];
					if ($this->isObsoleteIdentifier($eleIdent))
						{
						$eleDelete[] = $eleIdent;
						$relDelete[] = $eleIdent;
						}
					}
				$eleRowsDeleted = count($eleDelete);
				if ($eleRowsDeleted > 0)
					{
					$success		= $this->anchor->purgeAirElementSet($eleDelete);
					}
				}
			}
		$cvtTimePt2			= time();
		$rowCount	= $this->anchor->Repository->airDB->getCount_AirEleIndex();
		if ($rowCount > 0)
			{
			$dbData		= $this->anchor->Repository->airDB->get_AllIndexItems($rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			$indexCount = count($dbData);
			$eleDelete      = array();
			if ($indexCount > 0)
				{
				/* examine the rowset */
				foreach ($dbData as $dbRow)
					{
					$eleIdent	= $dbRow['Air_Ele_Id'];
					$eleType		= $dbRow['Air_Ele_EleType'];
					if (($this->isObsoleteIdentifier($eleIdent))
					 || ($this->isObsoleteIdentifier($eleType)))
						{
						$eleDelete[] = $eleIdent;
						$relDelete[] = $eleIdent;
						}
					}
				$indexRowsDeleted = count($eleDelete);
				if ($indexRowsDeleted > 0)
					{
					$success		= $this->anchor->purgeAirElementSet($eleDelete);
					}
				}
			}
		$cvtTimePt2			= time();
		$rowCount	= $this->anchor->Repository->airDB->getCount_AirRelRules();
		if ($rowCount > 0)
			{
			$dbData		= $this->anchor->Repository->airDB->get_AllRelRules($rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			$ruleCount = count($dbData);
			$eleDelete      = array();
			if ($ruleCount > 0)
				{
				/* examine the rowset */
				foreach ($dbData as $dbRow)
					{
					$eleIdent	= $dbRow['Air_Ele_Id'];
					if ($this->isObsoleteIdentifier($eleIdent))
						{
						$eleDelete[] = $eleIdent;
						$relDelete[] = $eleIdent;
						}
					}
				$ruleRowsDeleted = count($eleDelete);
				if ($ruleRowsDeleted > 0)
					{
					$success		= $this->anchor->Repository->airDB->purge_AirRelRulesSet($eleDelete);
					}
				}
			}
		$cvtTimePt3			= time();
		$rowCount	= $this->anchor->Repository->airDB->getCount_AirProperties();
		if ($rowCount > 0)
			{
			$dbData		= $this->anchor->Repository->airDB->get_AllProperties($rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			$propCount = count($dbData);
			$eleDelete      = array();
			if ($propCount > 0)
				{
				/* examine the rowset */
				foreach ($dbData as $dbRow)
					{
					$eleIdent	= $dbRow['Air_Ele_Id'];
					if ($this->isObsoleteIdentifier($eleIdent))
						{
						$eleDelete[] = $eleIdent;
						$relDelete[] = $eleIdent;
						}
					}
				$propRowsDeleted = count($eleDelete);
				if ($propRowsDeleted > 0)
					{
					$success		= $this->anchor->Repository->airDB->purge_AirPropertiesSet($eleDelete);
					}
				}
			}
		$cvtTimePt4			= time();
		$rowCount	= $this->anchor->Repository->airDB->getCount_AirAssociations();
		if ($rowCount > 0)
			{
			$dbData		= $this->anchor->Repository->airDB->get_AllAssociations($rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			$assocCount = count($dbData);
			$eleDelete      = array();
			if ($assocCount > 0)
				{
				/* examine the rowset */
				foreach ($dbData as $dbRow)
					{
					$eleIdent	= $dbRow['Air_Ele_Id'];
					if ($this->isObsoleteIdentifier($eleIdent))
						{
						$eleDelete[] = $eleIdent;
						$relDelete[] = $eleIdent;
						}
					}
				$assocRowsDeleted = count($eleDelete);
				if ($assocRowsDeleted > 0)
					{
					$success		= $this->anchor->Repository->airDB->purge_AirAssociationsSet($eleDelete);
					}
				}
			}
		$cvtTimePt5			= time();
		$rowCount	= $this->anchor->Repository->airDB->getCount_AirRelationships();
		if ($rowCount > 0)
			{
			if (count($relDelete) > 0)
				{
				$success		= $this->anchor->Repository->airDB->purge_AirAssociationsSet($eleDelete);
				}
			$dbData		= $this->anchor->Repository->airDB->get_AllRelationships($rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			$relDelete     = array();
			$relationCount = count($dbData);
			if ($relationCount > 0)
				{
				/* examine the rowset */
				foreach ($dbData as $dbRow)
					{
					foreach ($dbRow as $key => $value)
						{
						$eleIdent	= $value;
						if ($this->isObsoleteIdentifier($eleIdent))
							{
							$relDelete[] = $eleIdent;
							}
						}
					}
				if (count($relDelete) > 0)
					{
					$success		= $this->anchor->Repository->airDB->purge_AirAssociationsSet($eleDelete);
					}
				}
			}

		$cvtTimeEnd			= time();
		$cvtTime				= $cvtTimeEnd - $cvtTimePt1;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Overall process took '.$cvtTime.' seconds');
		$cvtTime				= $cvtTimePt2 - $cvtTimePt1;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Setup processing took '.$cvtTime.' seconds');
		$cvtTime				= $cvtTimePt3 - $cvtTimePt2;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Rules processing took '.$cvtTime.' seconds for '.$ruleCount.' elements, '.$ruleRowsDeleted.' rows deleted');
		$cvtTime				= $cvtTimePt4 - $cvtTimePt3;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Properties processing took '.$cvtTime.' seconds for '.$propCount.' elements, '.$propRowsDeleted.' rows deleted');
		$cvtTime				= $cvtTimePt5 - $cvtTimePt4;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Associations processing took '.$cvtTime.' seconds for '.$assocCount.' elements, '.$assocRowsDeleted.' rows deleted');
		$cvtTime				= $cvtTimeEnd - $cvtTimePt5;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Relationships processing took '.$cvtTime.' seconds for '.$relationCount.' elements');

		$encodeObject = Dialog_MenuSelect;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * isObsoleteIdentifier
	 *******/
	function isObsoleteIdentifier($eleIdent)
		{
		$obsolete	= false;

		if ((empty($eleIdent))
		 || (strlen($eleIdent) <= 44))
			{
			$obsolete = true;
			}
		return($obsolete);
		}

	/***************************************************************************
	 * procDbGetStats
	 *******/
	function procDbGetStats()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

	 	$attrkey				= 'Air_Ele_EleType';
		$eleCount			= $this->anchor->Repository->airDB->getCount_AirElements();
		$indexCount			= $this->anchor->Repository->airDB->getCount_AirEleIndex();
		$attrCountArray	= $this->anchor->Repository->airDB->getCountByAttr_AirEleIndex($attrkey);
		$attrGroups			= count($attrCountArray);
		$ruleCount			= $this->anchor->Repository->airDB->getCount_AirRelRules();
		$propCount			= $this->anchor->Repository->airDB->getCount_AirProperties();
		$assocCount			= $this->anchor->Repository->airDB->getCount_AirAssociations();
		$relationCount		= $this->anchor->Repository->airDB->getCount_AirRelationships();

		$this->anchor->putTraceData(__LINE__, __FILE__, $eleCount.' AIR Element table elements');
		$this->anchor->putTraceData(__LINE__, __FILE__, $indexCount.' Index table elements');
		$this->anchor->putTraceData(__LINE__, __FILE__, $attrGroups.' Index element types');
		for ($i = 0; $i < $attrGroups; $i++)
			{
			$dbCountArray 		= $attrCountArray[$i];
			$countType			= $dbCountArray[$attrkey];
			$typeCount			= $dbCountArray['TotalRows'];
			$typeName			= $this->anchor->Repository->getElementName($countType);
			$this->anchor->putTraceData(__LINE__, __FILE__, $typeCount.' '.$typeName.' elements');
			}
		$this->anchor->putTraceData(__LINE__, __FILE__, $ruleCount.' Rules table elements');
		$this->anchor->putTraceData(__LINE__, __FILE__, $propCount.' Properties table elements');
		$this->anchor->putTraceData(__LINE__, __FILE__, $assocCount.' Associations table elements');
		$this->anchor->putTraceData(__LINE__, __FILE__, $relationCount.' Relationships table elements');

		$encodeObject = Dialog_MenuSelect;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procDbConvertExecute
	 *******/
	function procDbConvertExecute()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		$errors				= false;
		$cvtMasterList		= array();
		$cvtElementList	= array();
		$cvtBatchLimit		= 25;
		$cvtTimePt1			= time();
		/*
		 * First get the manifest
		 */
		$cvtManifest		= $this->procContext->getSessionData('cvtContextManifest');
		if ((empty($cvtManifest))
		 || ($cvtManifest == NEW_Null_Identifier)
		 || ($cvtManifest == AIR_Null_Identifier))
			{
			$errors	= true;
			$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification not found');
			}

		if (! $errors)
		 	{
			/*
			 * Obtain an C_AirElement encoding	for the manifest
			 */
		$this->anchor->putTraceData(__LINE__, __FILE__, 'obtaining manifest '.$cvtManifest);
			$manifest = & $this->anchor->Repository->getTypedAirDocument($cvtManifest, NULL);
			$docType	 = $manifest->getDocType();
			if ($docType != AIR_EleType_EleManifest)
				{
				$errors = true;
				$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'Conversion manifest specification is not valid');
				}
			else
				{
				/*
				 * Determine if there were any elements identified on the input message.
				 * If so, the input message 'target' element is what we will convert.
				 */
				$collectionSize = $this->procMsg->getDataCollectionItemCount('target');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'target count = '.$collectionSize);
				if ($collectionSize > 0)
					{
					for ($i = 0; $i < $collectionSize; $i++)
						{
						$eleIdent = $this->procMsg->getDataCollectionItemContent('target', $i);
						if ((! empty($eleIdent))
						 && ($eleIdent	!= AIR_Null_Identifier))
							{
							$cvtElementList[]	= $eleIdent;
							}
						}
		$this->anchor->putTraceData(__LINE__, __FILE__, 'convert pool count = '.$collectionSize);
					}

				/*
				 * If nothing identified on the input message, determine if we have
				 * elements identified in the session data that we need to continue
				 * to work off.
				 */
				if (empty($cvtElementList))
					{
					$collectionSize = $this->procContext->getDataCollectionItemCount('ConvertItem');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Session ConvertItem count = '.$collectionSize);
					if ($collectionSize > 0)
						{
						if ($collectionSize < $cvtBatchLimit)
							{
							$cvtBatchLimit = $collectionSize;
							}
						for ($i = 0; $i < $cvtBatchLimit; $i++)
							{
							$eleIdent = $this->procContext->getDataCollectionItemContent('ConvertItem', $i);
							/*
							 * Do NOT auto-convert the manifest document. The manifest should be
							 * converted by selection as the last element.
							 */
							if ($eleIdent != $cvtManifest)
								{
								$cvtElementList[]	= $eleIdent;
								}
							}
						}
		$this->anchor->putTraceData(__LINE__, __FILE__, 'convert pool count = '.$collectionSize);
					}

				/*
				 * Check again to see if we have target material.
				 */
				if (empty($cvtElementList))
					{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'No conversion target specified.');
					}
				}
		 	}

		/*
		 * If no error identified, we have a manifest, and we have a target list.
		 */
		if (! $errors)
		 	{
	 		/*
	 		 * First, build a conversion array to drive the UUID exchange process
	 		 */
			$manifestSize = $manifest->getDataCollectionItemCount('Item');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'manifest size = '.$manifestSize);
			if ($manifestSize > 0)
				{
				for ($i = 0; $i < $manifestSize; $i++)
					{
					$collectionItemNode 	= $manifest->getDataCollectionItem('Item', $i);

					/*
					 * Build a conversion array of all UUID entries to be changed
					 */
					$manifestEntry 					= array();
					$manifestEntry['OldKey']		= $collectionItemNode->getChildContentByName('OldKey');
					$manifestEntry['NewKey']		= $collectionItemNode->getChildContentByName('NewKey');
//					$manifestEntry['Name']			= $collectionItemNode->getChildContentByName('Name');
//					$manifestEntry['Type']			= $collectionItemNode->getChildContentByName('Type');
//					$manifestEntry['Assigned']		= $collectionItemNode->getChildContentByName('Assigned');
//					$manifestEntry['Created']		= $collectionItemNode->getChildContentByName('Created');
//					$manifestEntry['Reviewed']		= $collectionItemNode->getChildContentByName('Reviewed');
//					$manifestEntry['Converted']	= $collectionItemNode->getChildContentByName('Converted');
					$cvtMasterList[] 					= $manifestEntry;
					}

				/*
				 * Create additional 'magic cookie' UUID entries
				 */
				$manifestEntry 					= array();
				$manifestEntry['OldKey']		= AIR_Null_Identifier;
				$manifestEntry['NewKey']		= NEW_Null_Identifier;
				$cvtMasterList[] 					= $manifestEntry;

				$manifestEntry 					= array();
				$manifestEntry['OldKey']		= AIR_Any_Identifier;
				$manifestEntry['NewKey']		= NEW_Any_Identifier;
				$cvtMasterList[] 					= $manifestEntry;

				$manifestEntry 					= array();
				$manifestEntry['OldKey']		= AIR_All_Identifier;
				$manifestEntry['NewKey']		= NEW_All_Identifier;
				$cvtMasterList[] 					= $manifestEntry;
				}

		$this->anchor->putTraceData(__LINE__, __FILE__, 'manifest list = '.count($cvtMasterList));
		$cvtTimePt2			= time();
		 	/****************************************************************************/
		 	/****************************************************************************/
		 	/***                                                                      ***/
		 	/***                                                                      ***/
		 	/***                                                                      ***/
		 	/****************************************************************************/
		 	/****************************************************************************/
		 	foreach ($cvtElementList as $cvtElement)
		 		{
		 		/*
		 		 * Convert the element and track the process
		 		 */

		 		/*
		 		 * First, find the element in the conversion manifest in order
		 		 * to obtain the specifications and status of where we are.
		 		 */
				$manifestItemNode	= null;
				if ($manifestSize > 0)
					{
					for ($i = 0; $i < $manifestSize; $i++)
						{
						$collectionItemNode 	= $manifest->getDataCollectionItem('Item', $i);

						/*
						 * Build a conversion array of all UUID entries to be changed
						 */
						$manifestItem 		= $collectionItemNode->getChildContentByName('OldKey');
						if ($manifestItem == $cvtElement)
							{
							$manifestItemNode	= & $collectionItemNode;
							break;
							}
						}
					}
		 		if (empty($cvtMasterList))
		 			{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('', 'Manifest list has no detail.');
		 			break;
		 			}
		 		if (empty($manifestItemNode))
		 			{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('', 'Conversion target not found on manifest.');
		 			break;
		 			}

		 		/*
		 		 * Now get the old element from the database.
		 		 */
				$oldDbDoc = & $this->anchor->Repository->getTypedAirDocument($cvtElement, NULL);
				if (is_object($oldDbDoc))
					{
					$cvtEleType 		= $oldDbDoc->getDocType();
					$cvtEleName 		= $oldDbDoc->getDocName();
					$cvtEleContent		= $oldDbDoc->serialize();
		$this->anchor->putTraceData(__LINE__, __FILE__, 'old element ['.$cvtEleName.'] located');
					}
		 		else
		 			{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('', 'Cannot find old database element for '.$cvtElement);
		 			break;
		 			}

				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, 'Convert item ['.$cvtEleName.'] accessed');
					}

				/*
				 * Convert the UUID references within the XML
				 */
		$directory	= AF_ROOT_DIR.'/scripts/conversion/audit/';
		$pathName 		= $directory.'old/'.$cvtElement.'.xml';
		$this->anchor->putFileContent($pathName, $cvtEleContent);
				$replCount = 0;
				foreach($cvtMasterList as $cvtElementItem)
					{
					$cvtEleContent = $this->procSubstituteUuidCodes($replCount, $cvtEleContent, $cvtElementItem);
					}

				if ($replCount < 1)
					{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('', 'No content replacement found for element '.$cvtElement);
		 			break;
					}
		$this->anchor->putTraceData(__LINE__, __FILE__, $replCount.' key alterations identified');
				/*
				 * Create a new element document
				 */
				$newAirDoc = new C_AF_AirElementDoc();
				if (!$newAirDoc->load($cvtEleContent))
					{
					$errors = true;
					$this->resultMsg->attachDiagnosticTextItem('', 'Cannot load converted XML into new document '.$cvtElement);
		 			break;
					}

				$newAirDoc->putDocName('AIR V0.4: '.$cvtEleName);
				$newAirDoc->persist();
				$cvtEleContent		= $newAirDoc->serialize();
		$this->anchor->putTraceData(__LINE__, __FILE__, 'New DB element inserted');
		$directory	= AF_ROOT_DIR.'/scripts/conversion/audit/';
		$pathName 		= $directory.'new/'.$cvtElement.'.xml';
		$this->anchor->putFileContent($pathName, $cvtEleContent);

		 		/*
		 		 * Locate and remove the item from the session data.
		 		 */
				$collectionSize = $this->procContext->getDataCollectionItemCount('ConvertItem');
				if ($collectionSize > 0)
					{
					for ($i = 0; $i < $cvtBatchLimit; $i++)
						{
						$eleIdent = $this->procContext->getDataCollectionItemContent('ConvertItem', $i);
						if ($eleIdent == $cvtElement)
							{
							$this->procContext->removeDataCollectionItemByIndex('ConvertItem', $i);
							break;
							}
						}
		$collectionSize -= 1;
		$this->anchor->putTraceData(__LINE__, __FILE__, $collectionSize.' items left in session pool');
					}

				/*
				 * Update the manifest to reflect the work done
				 */
				$manifestItemNode->putChildContentByName('Created', time());
		 		}
		 	/****************************************************************************/
		 	/****************************************************************************/
		 	/***                                                                      ***/
		 	/***                                                                      ***/
		 	/***                                                                      ***/
		 	/****************************************************************************/
		 	/****************************************************************************/

		 	/*
		 	 * Persist the manifest changes
		 	 */
		 	$manifest->persist();
			}

		$cvtTimeEnd			= time();
		$cvtTime				= $cvtTimeEnd - $cvtTimePt1;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Overall main conversion process took '.$cvtTime.' seconds');
		$cvtTime				= $cvtTimePt2 - $cvtTimePt1;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Setup processing took '.$cvtTime.' seconds');
		$cvtTime				= $cvtTimeEnd - $cvtTimePt2;
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Element conversion processing took '.$cvtTime.' seconds for '.$cvtBatchLimit.' elements');

		if ($errors)
			{
			$encodeObject = Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->procContext->putSessionData('cvtContextAction', $this->myMsgAction);
			$this->myTxnSpec['TxnOper']		= $this->myMsgAction;

			$encodeObject = ProcMod_EleAudit;
			$encodeAction	= AIR_Action_ShowItem;
			$encodeVers		= '1.0';
			}





		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}


	}

 ?>