<?php
/*
 * AirLib script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.2 2005-SEP-08 JVS Original code
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is the primary business logic processing module for BEAMS 'rules'
 * element types.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ProcModEleMaint';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ProcModEleMaint extends C_AirProcModBase {

	// --------------------------------------------------------
	// Constructor
	//
	// Initialize the local variable store and creates a local
	// reference to the AIR_anchor object for later use in
	// detail function processing. (Be careful with code here
	// to ensure that we are really talking to the right object.)
	// --------------------------------------------------------
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
	 *
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
		parent::initialize($procContext, $baseMsg, $procMsg);
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
		$this->myDialog	= Dialog_EleMaint;

		switch ($this->myMsgAction)
			{
			case AIR_Action_Add:
				$this->createElement();
				break;
			case AIR_Action_Modify:
				$this->modifyElement();
				break;
			case AIR_Action_PurgeItems:
				$this->deleteElementSet();
				break;
			case AIR_Action_DeleteItem:
				$this->deleteElement();
				break;
	   	case AIR_Action_PurgeType:
				$this->purgeElements();
				break;
			case AIR_Action_View:
			case AIR_Action_ShowItem:
			case AIR_Action_ShowRaw:
				$this->showElement();
				break;
			default:
				$this->procDefault();
				break;
			}

		$result = $this->publishTxnDataArrayToResultMsg();
		if ($result < 0)
			{
			trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
			}

		$this->postResultMsg();
		}

	/***************************************************************************
	 * createElement
	 *
	 *******/
	function createElement()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= $this->editEleSpecStructure();
		$errorDiagnosed	|= $this->editAirInfoMaintSpec();

		if (($errorDiagnosed)
		 || ($this->myTxnSpec['TxnStepOper'] != AIR_Action_Submit))
			{
			$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			/*
			 * Create a new C_AirElement encoding	for the element
			 */
			$newEle = $this->anchor->createAirElementDoc($this->myTxnSpec['EleType'],
																		$this->myTxnSpec['EleName'],
																		$this->myTxnSpec['Author'],
																		$this->myTxnSpec['EleChgComments']);
			$newEleType 		= $newEle->getDocType();

			/*
			 * Post the new elements to the element document
			 */
			$this->postTxnDataArrayToEleDoc($newEle);

			/*
			 * Persist the element definition to the database
			 */
	 		$newEle->persist();
	 		if ($this->anchor->eleInsertDebug())
	 			{
		 		/*
		 		 * Write a serialized version to the log file
		 		 */
				$newEle->logContextInfo();
				}

			if (($newEleType == AIR_EleType_PropRule)
			 || ($newEleType == AIR_EleType_AssocRule)
			 || ($newEleType == AIR_EleType_CoordRule))
				{
				$this->addRuleExtension($newEle);
				}

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * addRuleExtension
	 *
	 *******/
	function addRuleExtension(& $eleDoc)
		{
		$eleArray      = array();
		$success			= true;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleIdent	= $eleDoc->getDocumentId();
		$eleType		= $eleDoc->getDocType();

		$eleArray['Air_Ele_Id']					= $eleIdent;
		$eleArray['Air_RelRule_Predicate']	= $eleDoc->getElementData('PredType');
		$eleArray['Air_RelRule_PredOrd']		= $eleDoc->getElementData('PredOrdSpec');
		$eleArray['Air_RelRule_PredCard']	= $eleDoc->getElementData('PredCardSpec');
		$eleArray['Air_RelRule_PredMax']		= $eleDoc->getElementData('PredCardLimit');
		$eleArray['Air_RelRule_Diag']			= $eleDoc->getElementData('RuleDiag');
		switch ($eleType)
			{
			case AIR_EleType_PropRule:
				$eleArray['Air_RelRule_Object']		= AIR_EleType_ContentBlock;
				$eleArray['Air_RelRule_IObject']		= AIR_Null_Identifier;
				break;
			case AIR_EleType_AssocRule:
				$eleArray['Air_RelRule_Object']		= $eleDoc->getElementData('ObjType');
				$eleArray['Air_RelRule_IObject']		= AIR_Null_Identifier;
				break;
			case AIR_EleType_CoordRule:
				$eleArray['Air_RelRule_Object']		= $eleDoc->getElementData('ObjType');
				$eleArray['Air_RelRule_IObject']		= $eleDoc->getElementData('IObjType');
				break;
			}

		$collectionSize = $eleDoc->getDataCollectionItemCount('SubjType');
		if ($collectionSize > 0)
			{
			// Found, post the data
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " SUBJTYPE collection has $collectionSize items");
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$collectionItemNode = $eleDoc->getDataCollectionItem('SubjType', $i);
				$nodeKey		= $collectionItemNode->getChildContentByName('Key');
				$nodeValue	= $collectionItemNode->getChildContentByName('Value');

				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " item $i key = $nodeKey value = $nodeValue");

				if ($nodeValue)
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Posted");
					$eleArray['Air_RelRule_Subject']		= $nodeKey;
					$success		= $this->anchor->myDbLayer->insert_AirRelRulesItem($eleArray);
					}
				}
			}
		else
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " SUBJTYPE collection not found");
			}

		return($success);
		}

	/***************************************************************************
	 * modifyRuleExtension
	 *
	 *******/
	function modifyRuleExtension(& $eleDoc)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$success		= $this->deleteRuleExtension($eleDoc);
		$success		= $this->addRuleExtension($eleDoc);

		return($success);
		}

	/***************************************************************************
	 * deleteRuleExtension
	 *
	 *******/
	function deleteRuleExtension(& $eleDoc)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleIdent	= $eleDoc->getDocumentId();
		$success		= $this->anchor->myDbLayer->purge_AirRelRulesItem($eleIdent);

		return($success);
		}

	/***************************************************************************
	 * showElement
	 *
	 *******/
	function showElement()
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
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . ' Using ['. $eleIdent .']');
			$this->procContext->removeDataCollectionItemByRef('EleIdent', $identNode);
			}
		else
			{
			if (($this->anchor != NULL) && ($this->anchor->trace()))
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " EleIdent not found!");
				}
			$eleIdent			= AIR_Null_Identifier;
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
			$eleDoc = $this->anchor->getSavedAirDocument($eleIdent, NULL);
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
			$this->procContext->putSessionData('eleMaintObject', $eleIdent);

			$debugAction = $this->procContext->getSessionData('eleMaintAction');
			$debugObject = $this->procContext->getSessionData('eleMaintObject');
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " eleMaintAction = $debugAction");
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " eleMaintObject = $debugObject");

			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * deleteElement
	 *
	 * Initial delete is a hard removal of the element from the database. This
	 * should become the action taken by a 'purge' rather than a 'delete'
	 * command. Deletes should be 'soft' so they can be reversed. That logic
	 * has been defered pending a general review of the history key management
	 * processing after the more routine functions are working.
	 *******/
	function deleteElement()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($this->myContextAction != AIR_Action_DeleteItem)
		 || (empty($this->myContextObject))
		 || (!is_string($this->myContextObject)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " invalid context");
			$this->anchor->putTraceData(__LINE__, __FILE__, "eleMaintAction = $this->myContextAction");
			$this->anchor->putTraceData(__LINE__, __FILE__, "eleMaintObject = $this->myContextObject");

			$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			/*
			 * Delete the C_AirElement from the database
			 */
			$eleDoc = $this->anchor->getSavedAirDocument($this->myContextObject, NULL);
			if (is_object($eleDoc))
				{
				$oldEleType 		= $eleDoc->getDocType();

				/*
				 * Check for use as a predicate in relationships
				 */
				$dbData = $this->anchor->myDbLayer->get_RelTypeRelations($this->myContextObject, 1);
				if ((!isset($dbData))
				 || (is_null($dbData))
				 || (!is_array($dbData)))
					{
					echo $this->anchor->whereAmI();
					die ('Invalid DB query return to ' . __FUNCTION__);
					}

				$dbDataCount = count($dbData);
				if ($dbDataCount > 0)
					{
					$itemName = $eleDoc->getPreferredName();
					$this->anchor->putTraceData(__LINE__, __FILE__, "Not deleted! $itemName is in active use as an association. ");
					$success = false;
					}
				else
					{
					$success = $this->anchor->purgeAirElement($this->myContextObject);
					$this->crosspostDeletedEleDoc($eleDoc);

					if (($oldEleType == AIR_EleType_PropRule)
					 || ($oldEleType == AIR_EleType_AssocRule)
					 || ($oldEleType == AIR_EleType_CoordRule))
						{
						$this->deleteRuleExtension($eleDoc);
						}
					}
				}
			else
				{
				$success = false;
				}

//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " completion = $success");
			$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
			if ($collectionSize > 0)
				{
//				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Found $collectionSize session EleIdent identifiers");
//				trigger_error("Found $collectionSize session EleIdent identifiers", E_USER_NOTICE);
				$maintEleType = $this->myTxnSpec['EleType'];

				/*
				 * Keep the information content from 'bleeding' between elements
				 */
//				$this->diagnoseTxnDataArray();
				$this->myTxnSpec['EleChgComments'] = '';

	   		$encodeObject = $this->anchor->getProcModFromEleType($maintEleType);
				$encodeAction	= AIR_Action_ShowItem;
				$encodeVers		= '1.0';
				}
			else
				{
				$encodeObject	= Dialog_MenuSelect;
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * deleteElementSet
	 *******/
	function deleteElementSet()
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
				$node = $this->procContext->createTextElement('PurgeItem', $eleIdent);
				$this->procContext->createNewDataCollectionItem($node);
				}
			}

		/*
		 * Determine if there are any elements identified on the session.
		 */
		$purgeCount = $this->procContext->getDataCollectionItemCount('PurgeItem');
		$purgeArray = array();

		if (($this->myContextAction != AIR_Action_DirViewRaw)
		 || (empty($purgeCount)))
			{
			if ($this->myContextAction != AIR_Action_DirViewRaw)
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " invalid context action");
				$this->resultMsg->attachDiagnosticTextItem('Pgm Logic', 'Invalid context.');
				}
			else
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " purge item list is empty");
				$this->resultMsg->attachDiagnosticTextItem('Selection', 'Nothing selected.');
				}
			$this->anchor->putTraceData(__LINE__, __FILE__, "eleMaintAction = $this->myContextAction");
			$this->anchor->putTraceData(__LINE__, __FILE__, "purgeCount = $purgeCount");

			$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

			$encodeObject	= Dialog_EleList;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$purgeItemNo = $purgeCount - 1;
			for ($i = 0; $i < $purgeCount; $i++)
				{
				/*
				 * Get the item ID from the session data
				 */
				$purgeItem = $this->procContext->getDataCollectionItemContent('PurgeItem', $purgeItemNo);
				$purgeDoc = $this->anchor->getSavedAirDocument($purgeItem, NULL);
				if (is_object($purgeDoc))
					{
					$purgeEleType 		= $purgeDoc->getDocType();
					$this->crosspostDeletedEleDoc($purgeDoc);
					if (($purgeEleType == AIR_EleType_PropRule)
					 || ($purgeEleType == AIR_EleType_AssocRule)
					 || ($purgeEleType == AIR_EleType_CoordRule))
						{
						$this->deleteRuleExtension($purgeDoc);
						}
					}

				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " delete item [$purgeItem] completion = $success");
					}
				/*
				 * Add the item to the purge command array, and
				 * Remove the item from the purge list
				 */
				$purgeArray[] = $purgeItem;
				$this->procContext->removeDataCollectionItemByIndex('PurgeItem', $purgeItemNo);
				$purgeItemNo -= 1;
				}

			/*
			 * Delete the C_AirElement from the database
			 */
			$success = $this->anchor->purgeAirElementSet($purgeArray);


			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * modifyElement
	 *
	 *******/
	function modifyElement()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if ((empty($this->myContextObject))
		 || (!is_string($this->myContextObject)))
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'Element specification not found');
			$errorDiagnosed	= true;
			$eleDoc				= NULL;
			$oldEleType 		= NULL;
			$oldEleName			= NULL;
			$oldContent			= NULL;
			}
		else
			{
			/*
			 * Obtain an C_AirElement encoding	for the element
			 */
			$eleDoc = $this->anchor->getSavedAirDocument($this->myContextObject, NULL);

			$oldEleType 		= $eleDoc->getDocType();
			$oldEleName 		= $eleDoc->getDocName();
			}

		$errorDiagnosed	= $this->editEleSpecStructure();
		$errorDiagnosed	|= $this->editAirInfoMaintSpec();

		if ($this->myContextAction != AIR_Action_Modify)
			{
			$this->resultMsg->attachDiagnosticTextItem('Saved Context', 'Invalid context action specified = '.$this->myContextAction);
			$errorDiagnosed	= true;
			}

		if ((empty($this->myContextObject))
		 || (!is_string($this->myContextObject)))
			{
			$this->resultMsg->attachDiagnosticTextItem('Saved Context', 'Invalid context action specified = '.$this->myContextObject);
			$errorDiagnosed	= true;
			}

		if (! $errorDiagnosed)
			{
			if ($oldEleType != $this->myTxnSpec['EleType'])
				{
				/*
				 * Type is being changed
				 */
				$this->resultMsg->attachDiagnosticTextItem('Element Type', 'Type was changed. Type changes may cause parsing errors'
																							  .' until certain features of the element have been updated'
																							  .' and reconciled with the new type.');
				$errorDiagnosed	= true;
				}
			else
				{
				$dataChanges = 0;
				if ($this->myTxnSpec['EleName'] != $oldEleName)
					{
					$dataChanges += 1;
					}

				if (! $dataChanges)
					{
					$changeArray = $this->getChangedElementDataArray($eleDoc);
					$dataChanges += count($changeArray);
				  	}

				if (! $dataChanges)
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, 'No change to contentof: '.$oldEleName);
				 	}
				}
			}

		if (($errorDiagnosed)
		 || ($this->myTxnSpec['TxnStepOper'] != AIR_Action_Submit))
			{
			if (! empty($eleDoc))
				{
				$this->myTxnSpec['TxnOper']				= $this->procContext->getSessionData('eleMaintAction');
				$this->myTxnSpec['EleCreateEntity']		= $eleDoc->getDocCreateParty();
				$this->myTxnSpec['EleCreateDt']			= $eleDoc->getDocCreateTime();
				$this->myTxnSpec['EleLastChgEntity']	= $eleDoc->getDocUpdateParty();
				$this->myTxnSpec['EleLastChgDt']			= $eleDoc->getDocUpdateTime();
				$this->myTxnSpec['EleLastChgType']		= $eleDoc->getDocUpdateType();
				$this->myTxnSpec['EleLastChgComments'] = $eleDoc->getDocUpdateComment();
				}

			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			if ($dataChanges)
			 	{
				$eleDoc->putDocType($this->myTxnSpec['EleType']);
				$eleDoc->putDocName($this->myTxnSpec['EleName']);

				$eleDoc->putDocUpdateParty($this->myTxnSpec['Author']);
				$eleDoc->putDocUpdateTime(date("YmdHisO"));
				$eleDoc->putDocUpdateType(AIR_EleChgType_Modify);
				if (empty($this->myTxnSpec['EleChgComments']))
					{
					$eleDoc->putDocUpdateComment('Modification');
					}
				else
					{
					$eleDoc->putDocUpdateComment($this->myTxnSpec['EleChgComments']);
					}

				/*
				 * Post the modified elements to the element document
				 */
				$this->postTxnDataArrayToEleDoc($eleDoc);

				/*
				 * Persist the element definition to the database
				 */
	 			$eleDoc->persist();
				$newEleType 		= $eleDoc->getDocType();

				if (($newEleType == AIR_EleType_PropRule)
				 || ($newEleType == AIR_EleType_AssocRule)
				 || ($newEleType == AIR_EleType_CoordRule))
					{
					$this->modifyRuleExtension($eleDoc);
					}
				else
					{
					if (($oldEleType == AIR_EleType_PropRule)
					 || ($oldEleType == AIR_EleType_AssocRule)
					 || ($oldEleType == AIR_EleType_CoordRule))
						{
						$this->deleteRuleExtension($eleDoc);
						}
					}

				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Update successful");
				}

			$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
			if ($collectionSize > 0)
				{
					echo '<debug>Collection Size GT 0<br /></debug>';
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Found $collectionSize session EleIdent identifiers");
//				trigger_error("Found $collectionSize session EleIdent identifiers", E_USER_NOTICE);
				$maintEleType = $this->myTxnSpec['EleType'];

				/*
				 * Keep the information content from 'bleeding' between elements
				 */
				$this->diagnoseTxnDataArray();
				$this->myTxnSpec['EleChgComments'] = '';

	   		$encodeObject = $this->anchor->getProcModFromEleType($maintEleType);
				$encodeAction	= AIR_Action_ShowItem;
				$encodeVers		= '1.0';
				}
			else
				{
				$encodeObject	= Dialog_MenuSelect;
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * purgeElements
	 *
	 *******/
	function purgeElements()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$purgeCount = $this->procContext->getDataCollectionItemCount('PurgeItem');
		$purgeArray = array();

		if (($this->myContextAction != AIR_Action_PurgeType)
		 || (empty($purgeCount)))
			{
			if ($this->myContextAction != AIR_Action_PurgeType)
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " invalid context action");
				}
			else
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " purge item list is empty");
				}
			$this->anchor->putTraceData(__LINE__, __FILE__, "eleMaintAction = $this->myContextAction");
			$this->anchor->putTraceData(__LINE__, __FILE__, "purgeCount = $purgeCount");

			$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

			$encodeObject	= $this->myDialog;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$purgeItemNo = $purgeCount - 1;
			for ($i = 0; $i < $purgeCount; $i++)
				{
				/*
				 * Get the item ID from the session data
				 */
				$purgeItem = $this->procContext->getDataCollectionItemContent('PurgeItem', $purgeItemNo);

				/*
				 * Undo the cross posting of the element we are about to delete.
				 *
				 * Note. This has been made conditional simply as a means of making it
				 * easier to periodically turn this action on and off during development testing.
				 * Normally, it would always be desired. However, on occasion there have been
				 * bad database records created with various forms of XML errors in their
				 * structure or content. When this happens, they become very difficult to
				 * delete. The easiest work-around for this problem, when it occurs in
				 * development, is to use the normal delete process on the records AFTER having
				 * changed the 'purgeCrossPost' variable to bypass reading and loading the
				 * old data.
				 *
				 * BE SURE TO RESTORE THE VALUE AFTER THE PROBLEM RECORDS HAVE BEEN DELETED.
				 */
				$purgeCrossPost = true;
				if ($purgeCrossPost)
					{
					$purgeDoc = $this->anchor->getSavedAirDocument($purgeItem, NULL);
					if (is_object($purgeDoc))
						{
						$purgeEleType 		= $purgeDoc->getDocType();
						$this->crosspostDeletedEleDoc($purgeDoc);
						if (($purgeEleType == AIR_EleType_PropRule)
						 || ($purgeEleType == AIR_EleType_AssocRule)
						 || ($purgeEleType == AIR_EleType_CoordRule))
							{
							$this->deleteRuleExtension($purgeDoc);
							}
						}
					}

				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " delete item [$purgeItem] completion = $success");
					}
				/*
				 * Add the item to the purge command array, and
				 * Remove the item from the purge list
				 */
				$purgeArray[] = $purgeItem;
				$this->procContext->removeDataCollectionItemByIndex('PurgeItem', $purgeItemNo);
				$purgeItemNo -= 1;
				}

			/*
			 * Delete the C_AirElement from the database
			 */
			$success = $this->anchor->purgeAirElementSet($purgeArray);


			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}
	}

/*
 * This code is executed once per 'include' as the module is scanned
 * and all code not inside newly defined "function blocks" is executed.
 */
	if ($this->anchor->debugCoreFcns())
		{
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br/></debug> ';
		}
 ?>