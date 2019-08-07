<?php
/*
 * AirLib script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.0 2005-JUL-26 JVS Original code.
 *				-AUG-05 JVS Shaping. Change to perform output encoding
 *								process only.
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is a first prototype version of a dynamically
 * loaded and invoked processing module for execution within
 * a PHP processing environment.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirProcModBase';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * Data list based field management series
	 *
	 * postTxnDataArrayToEleDoc
	 * publishTxnDataArrayToResultMsg
	 * getChangedElementDataArray
	 * postEleDocToTxnDataArray
	 * createTxnDataArrayFromProcMsg
	 *******/
class C_AirProcModBase extends C_AirObjectBase {
	var $procContext		= NULL;		// The message context (session info)
	var $baseMsg			= NULL;		// The original message (when processing a reply)
	var $procMsg			= NULL;		// The input message being processed
	var $myTxnSpec 		= array();  // array format of the dynamic transaction specification
	var $resultMsg 		= NULL;		// The process result message
	private $globalUow	= NULL;
	private $parentUow	= NULL;
	var $myMsgId			= NULL;
	var $myMsgObject		= NULL;
	var $myMsgAction		= NULL;
	var $myMsgVers			= NULL;
	var $myDialog			= NULL;
	var $myContextObject	= NULL;
	var $myContextAction	= NULL;

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
		// Propogate the construction process
		parent::__construct($air_anchor);

		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * initialize
	 *
	 *******/
	function initialize(& $myProcContext, & $myBaseMsg, & $myProcMsg)
	 	{

		$this->procContext	= &$myProcContext;
		$this->baseMsg = &$myBaseMsg;
		$this->procMsg	= &$myProcMsg;
	 	$this->myMsgId = $this->baseMsg->getDocumentId();
		$this->globalUow = $this->baseMsg->getMessageControlData('GlobalUowid');
		$this->parentUow = $this->baseMsg->getMessageControlData('ParentUowid');
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' processing msg: '.$this->myMsgId);
			}

		/*
		 * Create default flow-through routing information
		 * We are encoded as the 'destination' for the incoming message, and we
		 * copy our 'logical' naming designation to be the source of the result
		 * data we are about to create.
		 */
		$this->myMsgObject	= $this->procMsg->getMessageControlData('DestObject');
		$this->myMsgAction	= $this->procMsg->getMessageControlData('DestAction');
		$this->myMsgVers		= $this->procMsg->getMessageControlData('DestVers');

		/*
		 * Get the operating context for the transaction
		 */
		$this->myContextAction = $this->procContext->getSessionData('eleMaintAction');
		$this->myContextObject = $this->procContext->getSessionData('eleMaintObject');
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		parent::terminate();
		}

	/***************************************************************************
	 * validateContext
	 *******/
	function validateContext()
		{
		$validated		= false;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		/*
		 * Extract validation fields
		 */
		$dlgContext			= $this->procMsg->getMessageData('dlgContext');
		$dlgCorrID			= $this->procMsg->getMessageData('dlgCorrID');
		$dlgDlgID			= $this->procMsg->getMessageData('dlgDlgID');
		$dlgMode				= $this->procMsg->getMessageData('dlgMode');
		$dlgAuth				= $this->procMsg->getMessageData('dlgAuth');
		/*
		 * Verify that we are in synch with what we expected
		 */
		if (($dlgContext == $this->procContext->getContextId())
		 && ($dlgCorrID == $this->procContext->getCorrId())
		 && ($dlgDlgID == $this->procContext->getDialogId())
		 && ($dlgMode == $this->procContext->getModeId())
		 && ($dlgAuth == $this->procContext->getAuthId()))
			{
			/*
			 * if so, we are validated with respect to the dialog context
			 * and we can prepare the remaining context information
			 */
			$validated		= true;
			$dlgClientDlgID	= $this->procMsg->getMessageData('dlgClientDlgID');
			$this->procContext->putClientDlgId($dlgClientDlgID);
			}
		else
			{
			/*
			 * if not validated, document the items with issues
			 */
			$txnAction		= $this->procMsg->getMessageControlData('SourceAction');
			if ($txnAction == 'HTTP:GET')
				{
				$validated = true;
				}
			else
				{
				if ($dlgContext != $this->procContext->getContextId())
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, ' **** CONTEXT MODIFIED on Request ******** ');
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Request Context = ' . $dlgContext);
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Session Context = ' . $this->procContext->getContextId());
					$this->anchor->putTraceData(__LINE__, __FILE__, ' ************ ');
					}
				if ($dlgCorrID != $this->procContext->getCorrId())
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, ' **** CORR-ID VALIDATION FAILURE on Request ******** ');
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Request Corr-ID = ' . $dlgCorrID);
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Session Corr-ID = ' . $this->procContext->getCorrId());
					$this->anchor->putTraceData(__LINE__, __FILE__, ' ************ ');
					}
				if ($dlgDlgID != $this->procContext->getDialogId())
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, ' **** DIALOG-ID VALIDATION FAILURE on Request ******** ');
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Request Dialog-ID = ' . $dlgDlgID);
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Session Dialog-ID = ' . $this->procContext->getDialogId());
					$this->anchor->putTraceData(__LINE__, __FILE__, ' ************ ');
					}
				if ($dlgMode != $this->procContext->getModeId())
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, ' **** MODE-ID VALIDATION FAILURE on Request ******** ');
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Request Mode-ID = ' . $dlgMode);
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Session Mode-ID = ' . $this->procContext->getModeId());
					$this->anchor->putTraceData(__LINE__, __FILE__, ' ************ ');
					}
				if ($dlgAuth != $this->procContext->getAuthId())
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, ' **** AUTH-ID VALIDATION FAILURE on Request ******** ');
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Request Auth-ID = ' . $dlgAuth);
					$this->anchor->putTraceData(__LINE__, __FILE__, ' Session Auth-ID = ' . $this->procContext->getAuthId());
					$this->anchor->putTraceData(__LINE__, __FILE__, ' ************ ');
					}
				}
			}

		return($validated);
		}

	/***************************************************************************
	 * initResultMsg
	 *
	 *******/
	function initResultMsg()
	 	{
		/*
		 * Create an initial result message
		 */
		$this->resultMsg = $this->anchor->createAirMessageDoc();
		$docId = $this->resultMsg->getDocumentId();
		$this->resultMsg->putMessageControlData('GlobalUowId', $this->globalUow);
		$this->resultMsg->putMessageControlData('ParentUowId', $this->parentUow);

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' processing msg: '.$docId);
			}

		/*
		 * Create default flow-through routing information
		 * We are encoded as the 'destination' for the incoming message, and we
		 * copy our 'logical' naming designation to be the source of the result
		 * data we are about to create.
		 */
		$this->resultMsg->putMessageControlData('SourceObject', $this->myMsgObject);
		$this->resultMsg->putMessageControlData('SourceAction', $this->myMsgAction);
		$this->resultMsg->putMessageControlData('SourceVers', $this->myMsgVers);
		}

	/***************************************************************************
	 * postResultMsg
	 *
	 *******/
	function postResultMsg()
	 	{
		$docId = $this->resultMsg->getDocumentId();
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' processing msg: '.$docId);
			}

		/*
		 * Persist the result in the database and post the
		 * message ID to the processing queue
		 */
		$this->resultMsg->persist();
		$this->anchor->enqueueMessage($docId);
		}

	/***************************************************************************
	 * createBaseTxnSpecArray
	 *
	 * Extract the key transaction specification elements from the source message
	 * and post them into a PHP array for processing
	 *******/
	function createBaseTxnSpecArray()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		foreach($this->myTxnSpec as $varKey => $varValue)
			{
				unset($this->myTxnSpec[$varKey]);
			}

		$this->myTxnSpec['TxnOper']		= $this->procMsg->getMessageData('TxnOper');
		$this->myTxnSpec['TxnStepOper']	= $this->procMsg->getMessageData('TxnStepOper');

		return;
		}

	/***************************************************************************
	 * publishBaseResultMsgInfo
	 *
	 * Extract the key transaction specification elements from the PHP 'work'
	 * array and post them to the result message
	 *******/
	function publishBaseResultMsgInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}


		if (array_key_exists('TxnOper', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('TxnOper',					$this->myTxnSpec['TxnOper']);
			}

		if (array_key_exists('TxnStepOper', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('TxnStepOper',				$this->myTxnSpec['TxnStepOper']);
			}

		return;
		}

	/***************************************************************************
	 * createMenuTxnSpecArray
	 *
	 * Extract the key transaction specification elements from the source message
	 * and post them into a PHP array for processing
	 *******/
	function createMenuTxnSpecArray()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->createBaseTxnSpecArray();

		return;
		}

	/***************************************************************************
	 * publishMenuResultMsgInfo
	 *
	 * Extract the key transaction specification elements from the PHP 'work'
	 * array and post them to the result message
	 *******/
	function publishMenuResultMsgInfo()
		{
		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		$this->publishBaseResultMsgInfo();

		if (array_key_exists('EleType', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleType',					$this->myTxnSpec['EleType']);
			}
		if (array_key_exists('EleIdent', $this->myTxnSpec))
			{
			$eleContent = $this->myTxnSpec['EleIdent'];
			$eleCount   = 0;
 			foreach ($eleContent as $eleIdent)
 				{
				$node = $this->resultMsg->createTextElement('EleIdent', $eleIdent);
				$this->resultMsg->createNewDataCollectionItem($node);
				$eleCount++;
				}
			$this->resultMsg->putMessageData('EleCount',	$eleCount);
			}

		return;
		}

	/***************************************************************************
	 * createMaintTxnSpecArray
	 *
	 * Extract the key transaction specification elements from the source message
	 * and post them into a PHP array for processing
	 *******/
	function createMaintTxnSpecArray()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->createBaseTxnSpecArray();
		$this->myTxnSpec['Author']					= $this->procMsg->getMessageData('Author');
		$this->myTxnSpec['EleClass']				= $this->procMsg->getMessageData('EleClass');
		$this->myTxnSpec['EleType']				= $this->procMsg->getMessageData('EleType');
		$this->myTxnSpec['EleName']				= $this->procMsg->getMessageData('EleName');
		$this->myTxnSpec['EleChgComments']		= $this->procMsg->getMessageData('EleChgComments');

		$eleCreateEntity		= $this->procMsg->getMessageData('EleCreateEntity');
		$eleCreateDt			= $this->procMsg->getMessageData('EleCreateDt');
		$eleLastChgEntity		= $this->procMsg->getMessageData('EleLastChgEntity');
		$eleLastChgDt			= $this->procMsg->getMessageData('EleLastChgDt');
		$eleLastChgType		= $this->procMsg->getMessageData('EleLastChgType');
		$eleLastChgComments	= $this->procMsg->getMessageData('EleLastChgComments');
		if ((! empty($eleCreateEntity))
		 || (! empty($eleCreateDt))
		 || (! empty($eleLastChgEntity))
		 || (! empty($eleLastChgDt))
		 || (! empty($eleLastChgType))
		 || (! empty($eleLastChgComments)))
			{
			if ((empty($eleCreateEntity))
			 || (empty($eleLastChgEntity)))
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				$this->diagnoseTxnDataArray();
				if (empty($eleCreateEntity))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Found empty author specification on txn");
					trigger_error("Found empty author specification on txn", E_USER_NOTICE);
					$eleCreateEntity		= AIR_Null_Identifier;
					}
				if (empty($eleLastChgEntity))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Found empty change author specification on txn");
					trigger_error("Found empty change author specification on txn", E_USER_NOTICE);
					$eleLastChgEntity		= AIR_Null_Identifier;
					}
				}
			$this->myTxnSpec['EleCreateEntity']		= $eleCreateEntity;
			$this->myTxnSpec['EleCreateDt']			= $eleCreateDt;
			$this->myTxnSpec['EleLastChgEntity']	= $eleLastChgEntity;
			$this->myTxnSpec['EleLastChgDt']			= $eleLastChgDt;
			$this->myTxnSpec['EleLastChgType']		= $eleLastChgType;
			$this->myTxnSpec['EleLastChgComments']	= $eleLastChgComments;
			}

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		return;
		}

	/***************************************************************************
	 * backpostBaseTxnSpecArray
	 *
	 * Extract the key transaction specification elements from the element document
	 * and post them into a PHP array for further processing
	 *******/
	function backpostBaseTxnSpecArray(& $eleDoc)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->myTxnSpec['EleType']				= $eleDoc->getDocType();
		$this->myTxnSpec['EleName']				= $eleDoc->getDocName();

//		$this->myTxnSpec['EleClass']				= $this->procMsg->getMessageData('EleClass');

//		$this->myTxnSpec['EleChgComments']		= $this->procMsg->getMessageData('EleChgComments');

		$this->myTxnSpec['EleCreateEntity']		= $eleDoc->getDocCreateParty();
		$debugEdit = $this->myTxnSpec['EleCreateEntity'];
		if (empty($debugEdit))
			{
			trigger_error("Found empty author specification on txn", E_USER_NOTICE);
			$this->myTxnSpec['EleCreateEntity']		= AIR_Null_Identifier;
			}
		$this->myTxnSpec['EleCreateDt']			= $eleDoc->getDocCreateTime();
		$this->myTxnSpec['EleLastChgEntity']	= $eleDoc->getDocUpdateParty();
		$debugEdit = $this->myTxnSpec['EleCreateEntity'];
		if (empty($debugEdit))
			{
			trigger_error("Found empty change author specification on txn", E_USER_NOTICE);
			$this->myTxnSpec['EleCreateEntity']		= AIR_Null_Identifier;
			}
		$this->myTxnSpec['EleLastChgDt']			= $eleDoc->getDocUpdateTime();
		$this->myTxnSpec['EleLastChgType']		= $eleDoc->getDocUpdateType();
		$this->myTxnSpec['EleLastChgComments'] = $eleDoc->getDocUpdateComment();

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		return;
		}

	/***************************************************************************
	 * publishMaintResultMsgInfo
	 *
	 * Extract the key transaction specification elements from the PHP 'work'
	 * array and post them to the result message
	 *******/
	function publishMaintResultMsgInfo()
		{

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		$this->publishBaseResultMsgInfo();

		if (array_key_exists('Author', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('Author',					$this->myTxnSpec['Author']);
			}
		if (array_key_exists('EleClass', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleClass',					$this->myTxnSpec['EleClass']);
			}
		if (array_key_exists('EleType', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleType',					$this->myTxnSpec['EleType']);
			}
		if (array_key_exists('EleName', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleName',					$this->myTxnSpec['EleName']);
			}
		if (array_key_exists('EleChgComments', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleChgComments',			$this->myTxnSpec['EleChgComments']);
			}

		if (array_key_exists('EleCreateEntity', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleCreateEntity',		$this->myTxnSpec['EleCreateEntity']);
			}
		if (array_key_exists('EleCreateDt', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleCreateDt',				$this->myTxnSpec['EleCreateDt']);
			}
		if (array_key_exists('EleLastChgEntity', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleLastChgEntity',		$this->myTxnSpec['EleLastChgEntity']);
			}
		if (array_key_exists('EleLastChgDt', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleLastChgDt',			$this->myTxnSpec['EleLastChgDt']);
			}
		if (array_key_exists('EleLastChgType', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleLastChgType',			$this->myTxnSpec['EleLastChgType']);
			}
		if (array_key_exists('EleLastChgComments', $this->myTxnSpec))
			{
			$this->resultMsg->putMessageData('EleLastChgComments',	$this->myTxnSpec['EleLastChgComments']);
			}

		return;
		}

	/***************************************************************************
	 * createTxnDataArrayFromProcMsg
	 *
	 * Extract the key transaction specification elements from the source message
	 * and post them into a PHP array for processing
	 *******/
	function createTxnDataArrayFromProcMsg($internalContent=true)
		{
		$eleCount		= 0;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if ($internalContent)
			{
			$this->createMaintTxnSpecArray();
			}
		else
			{
			$this->myTxnSpec['EleType'] = $this->procMsg->getMessageData('dlgEleType');
			}

		$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			$errType = $this->myTxnSpec['EleType'];
			if (!is_array($eleSpec))
				{
				$errType = $this->myTxnSpec['EleType'];
				$errTypeName =
				trigger_error('Failure creating EleType:'.$errType.' array in '.__FUNCTION__, E_USER_NOTICE);
				$eleCount		= -1;
				}
			else
				{
				trigger_error('EleType:'.$errType.' array is empty in '.__FUNCTION__, E_USER_NOTICE);
				$eleCount		= -1;
				}
			}
		else
			{
			foreach ($eleSpec as $eleItem)
			 	{
			 	if ($eleItem['elementSpec'])
			 		{
				 	$itemName = $eleItem['FldName'];

					// Complex data movement, based on type
					switch ($eleItem['DataType'])
						{
						case AIR_ContentType_UUIDList:
							// Need to scan for multiple items and store as an array
							$collection = array();
							if ($internalContent)
								{
								$displayMgmtName = $itemName . 'ShowAll';
								$searchValue = $this->procMsg->getMessageData($displayMgmtName);
								$this->myTxnSpec[$displayMgmtName] = $this->procMsg->getMessageData($displayMgmtName);
								// Determine the collection size of the items in the input message
								$collectionSize = $this->procMsg->getDataCollectionItemCount($itemName);
								if ($collectionSize > 0)
									{
									for ($i = 0; $i < $collectionSize; $i++)
										{
										$collectionItemNode = $this->procMsg->getDataCollectionItem($itemName, $i);
										$nodeKey		= $collectionItemNode->getChildContentByName('Key');
										$nodeValue	= $collectionItemNode->getChildContentByName('Value');
										$collection[$nodeKey] = $nodeValue;
										}
									$this->myTxnSpec[$itemName] = $collection;
									}
								}
							else
								{
								$srcName = 'dlg' . $itemName;
								// Checklists on originaltion from browsers only have specification for
								// 'checked' elements. Elements that may have been identified before as
								// 'on' but which are now 'off' are not reported. The solution is to
								// generate a new list of items and mark all items as 'off.' The update
								// process of reading the HTML input will 'turn on' all elements that
								// should be 'on' ... inlcuidng both those that changed, and those that
								// haven't. This results in items that were previously 'on' now being
								// idenfied and having an 'off' value.

								// Get the list of keys for the items in the collection
								$resultMgmtName = $itemName . 'ShowAll';
								$sourceMgmtName = $srcName . 'ShowAll';
								$searchValue = $this->procMsg->getMessageData($sourceMgmtName);
								if (! empty($searchValue))
									{
									switch ($searchValue)
										{
										case true:
										case '1':
										case 'on':
											$this->myTxnSpec[$resultMgmtName] = true;
											break;
										default:
											$this->myTxnSpec[$resultMgmtName] = false;
											break;
										}
									}
								else
									{
									$this->myTxnSpec[$resultMgmtName] = false;
									}
								$selTypes 	= $this->anchor->get_allElementsByType($eleItem['SelectionType'], 0, NULL, false,
																		false,	//		$this->hasSelectOptionNone,
																		false,	//		$this->hasSelectOptionAny,
																		true);	// 	$this->hasSelectOptionAll);
								foreach ($selTypes as $key => $value)
									{
									// Initially mark each key in the collection as 'off'
									$collection[$key] = false;
									// Form a 'search' name using the key value
									$searchName = $srcName . $key;
									$searchValue = $this->procMsg->getMessageData($searchName);
									if (! empty($searchValue))
										{
										switch ($searchValue)
											{
											case true:
											case '1':
											case 'on':
												$collection[$key] = true;
												break;
											}
										}
									}
								$this->myTxnSpec[$itemName] = $collection;
								}
							break;
						default:
							// Straight movement of variables one-to-one
							if ($internalContent)
								{
								$srcName = $itemName;
								}
							else
								{
								$srcName = 'dlg' . $itemName;
								}
							$this->myTxnSpec[$itemName] = $this->procMsg->getMessageData($srcName);
							break;
						}
					$eleCount++;
					}
			 	}
			}

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		return($eleCount);
		}

	/***************************************************************************
	 * postEleDocToTxnDataArray
	 *
	 * Extract the key transaction specification elements from the element document
	 * and post them into a PHP array for further processing
	 *******/
	function postEleDocToTxnDataArray(& $eleDoc)
		{

		if (!is_object($eleDoc))
			{
			trigger_error("Critical parameter data error in ".__FUNCTION__ , E_USER_NOTICE);
			}

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->backpostBaseTxnSpecArray($eleDoc);

		$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			trigger_error("Critical data error in __FUNCTION__" , E_USER_NOTICE);
			}
		else
			{
			foreach ($eleSpec as $eleItem)
			 	{
			 	if ($eleItem['elementSpec'])
			 		{
				 	$itemName = $eleItem['FldName'];
					{
					// Potentially complex data movement, based on type
					switch ($eleItem['DataType'])
						{
						case AIR_ContentType_UUIDList:
							// Need to scan for multiple items and store as an array
							$collection = array();
							// Determine the collection size of the items in the element document
							$collectionSize = $eleDoc->getDataCollectionItemCount($itemName);
							if ($collectionSize > 0)
								{
								// Found, post the data
								for ($i = 0; $i < $collectionSize; $i++)
									{
									$collectionItemNode = $eleDoc->getDataCollectionItem($itemName, $i);
									$nodeKey		= $collectionItemNode->getChildContentByName('Key');
									$nodeValue	= $collectionItemNode->getChildContentByName('Value');
									$collection[$nodeKey] = $nodeValue;
									}
								$this->myTxnSpec[$itemName] = $collection;
								}
							else
								{
								// Not found, back fill a default value. This is potentially a new field
								// that needs to be added to an old element.
								$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " generating missing collection item [$itemName]");

								// Get the list of keys for the items in the collection
								$selTypes 	= $this->anchor->get_allElementsByType($eleItem['SelectionType'], 0, NULL, false,
																		false,	//		$this->hasSelectOptionNone,
																		false,	//		$this->hasSelectOptionAny,
																		false);	// 	$this->hasSelectOptionAll);
								foreach ($selTypes as $key => $value)
									{
									// Mark each key in the collection to the default value
									$collection[$key] = false;
									}
								$this->myTxnSpec[$itemName] = $collection;

								}
							break;

						default:
							// Straight movement of variables one-to-one
							// *****************************************************************
							// The following code is a workaround for a problem with the
							// immediately following statement. The issue is unclear and may
							// be a bug in PHP processing. The workaround is to use the detail code
							// of the problem function here, in place of the function itself.
							$dataContent		= $eleDoc->getDocBodyData('ElementData', $itemName);
							$this->myTxnSpec[$itemName] = $dataContent;
							//
							// the following is the problem statement
//							$this->myTxnSpec[$itemName] = $eleDoc->getElementData($itemName);
							// workaround is to bypass this statement with the above replacement for
							// the present time.
							// ******************************************************************
							break;
							}
						}
					}
			 	}
		 	}

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		return;
		}

	/***************************************************************************
	 * getChangedItemListArray
	 *
	 * Extract an array identifying the changed detail items in a single item
	 * that is an array list of UUID items.
	 *
	 * Given $itemName, the name of an data element, this routine will look for
	 * $itemName as a collection in both the element document, and in the txnSpec
	 * array. Each collection will be examined, and a comparison array will be
	 * built showing the relative values of key values in each array.
	 *
	 * The new array is keyed by the item key and contains subarrays assigned to
	 * each key with the following entries:
	 *
	 * 'old' - contains the assigned value in the ElementDocument
	 * 'new' - contains the assigned value in txnSpec
	 *
	 * if 'old' is empty, the item is new
	 * if 'new' is empty and 'old' has a value, the item is being removed
	 * if 'old' matches 'new' - there is no change to the detail item
	 * if 'old' and 'new' have different value, - old is being changed
	 *******/
	function getChangedItemListArray(& $eleDoc, $itemName, $changesOnly = true)
		{
		$changeArray		= array();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$chgScan = array();
		$newContent = $this->myTxnSpec[$itemName];
		/*
		 * Build a initial work array with the values from txnSpec
		 */
		foreach ($newContent as $newArrayKey => $newArrayValue)
			{
			$itemArray					= array();
			$itemArray['old']			= '';
			$itemArray['new']			= $newArrayValue;
			$chgScan[$newArrayKey]	= $itemArray;
			}

		/*
		 * Scan the Element Doc and update the work array
		 */
		$collectionSize = $eleDoc->getDataCollectionItemCount($itemName);
		if ($collectionSize > 0)
			{
			// Obtain reference to detail array in txnSpec for comparison
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$collectionItemNode = $eleDoc->getDataCollectionItem($itemName, $i);
				$nodeKey		= $collectionItemNode->getChildContentByName('Key');
				$nodeValue	= $collectionItemNode->getChildContentByName('Value');
				if (array_key_exists($nodeKey, $chgScan))
					{
					$itemArray				= $chgScan[$nodeKey];
					$itemArray['old']		= $nodeValue;
					$chgScan[$nodeKey]	= $itemArray;
					}
				else
					{
					$itemArray				= array();
					$itemArray['old']		= $nodeValue;
					$itemArray['new']		= '';
					$chgScan[$nodeKey]	= $itemArray;
					}
				}
			}

		/*
		 * Scan the work array for changed items
		 */
		foreach ($chgScan as $chgArrayKey => $chgArrayValue)
			{
			if (!empty($chgArrayValue))
				{
				if ($changesOnly)
					{
					if ($chgArrayValue['old'] != $chgArrayValue['new'])
						{
						$changeArray[$chgArrayKey]	= $chgArrayValue;
						}
					}
				else
					{
					$changeArray[$chgArrayKey]	= $chgArrayValue;
					}
				}
			}

		return($changeArray);
		}

	/***************************************************************************
	 * getElementItemListArray
	 *
	 * Extract an array identifying the detail items in a single attribute item
	 * that is an array list of UUID items.
	 *
	 * Given $itemName, the name of an data element, this routine will look for
	 * $itemName as a collection in the element document. If found, the collection
	 * will be examined, and a new array will be providing the relative values of
	 * the key values in the array.
	 *
	 * The new array is keyed by the item key and contains subarrays assigned to
	 * each key with the following entries:
	 *
	 * 'old' - contains the assigned value in the ElementDocument
	 * 'new' - always empty
	 *******/
	function getElementItemListArray(& $eleDoc, $itemName)
		{
		$changeArray		= array();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$chgScan = array();

		/*
		 * Scan the Element Doc and create the array
		 */
		$collectionSize = $eleDoc->getDataCollectionItemCount($itemName);
		if ($collectionSize > 0)
			{
			// Obtain reference to detail array in txnSpec for comparison
			for ($i = 0; $i < $collectionSize; $i++)
				{
				/*
				 * Get the element document data
				 */
				$collectionItemNode = $eleDoc->getDataCollectionItem($itemName, $i);
				$nodeKey		= $collectionItemNode->getChildContentByName('Key');
				$nodeValue	= $collectionItemNode->getChildContentByName('Value');
				/*
				 * Convert it to our work array format
				 */
				$itemArray				= array();
				$itemArray['old']		= $nodeValue;
				$itemArray['new']		= '';
				$chgScan[$nodeKey]	= $itemArray;
				}
			}

		return($chgScan);
		}

	/***************************************************************************
	 * getChangedElementDataArray
	 *
	 * Extract an array identifying the changed elements.
	 *******/
	function getChangedElementDataArray(& $eleDoc)
		{
		$changeArray		= array();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
			}
		else
			{
			foreach ($eleSpec as $eleItem)
			 	{
			 	if ($eleItem['elementSpec'])
			 		{
				 	$itemName = $eleItem['FldName'];
					// Complex data comparison, based on type
					switch ($eleItem['DataType'])
						{
						case AIR_ContentType_UUIDList:
							// Need to scan for multiple items and store as an array
							$changes = $this->getChangedItemListArray($eleDoc, $itemName);
							if (! empty($changes))
								{
								$itemArray					= array();
								$itemArray['list']		= $changes;
								$changeArray[$itemName]	= $itemArray;
								}
							break;

						default:
							// Straight comparison of variables one-to-one
							if ($this->myTxnSpec[$itemName] != $eleDoc->getElementData($itemName))
								{
								$itemArray					= array();
								$itemArray['old']			= $eleDoc->getElementData($itemName);
								$itemArray['new']			= $this->myTxnSpec[$itemName];
								$changeArray[$itemName]	= $itemArray;
								}
							break;
							}
					}
			 	}
		 	}

		return($changeArray);
		}

	/***************************************************************************
	 * publishTxnDataArrayToResultMsg
	 *
	 * publishes the entire transaction data array to the result message. This
	 * is INDEPENDENT of the specifications for the data type.
	 *******/
	function publishTxnDataArrayToResultMsg()
		{
		$eleCount		= 0;

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		foreach ($this->myTxnSpec as $eleName => $eleContent)
	 		{
	 		if (is_array($eleContent))
	 			{
	 			foreach ($eleContent as $eleArrayKey => $eleArrayValue)
	 				{
					$newNode = $this->resultMsg->createElement($eleName);

					$node = $this->resultMsg->createTextElement('Key', $eleArrayKey);
					$newNode->appendChild($node);
					$node = $this->resultMsg->createTextElement('Value', $eleArrayValue);
					$newNode->appendChild($node);

					$this->resultMsg->createNewDataCollectionItem($newNode);
	 				}
	 			}
	 		else
	 			{
				$this->resultMsg->putMessageData($eleName, $eleContent);
	 			}
			$eleCount++;
			}

//		$this->publishMaintResultMsgInfo();

//		$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
//		if ((!is_array($eleSpec))
//		 || (empty($eleSpec)))
//			{
//			trigger_error("Critical data error in ".__FUNCTION__ , E_USER_NOTICE);
//			$eleCount		= -1;
//			}

//		foreach ($eleSpec as $eleItem)
//		 	{
//		 	if ($eleItem['elementSpec'])
//		 		{
//			 	$itemName = $eleItem['FldName'];
//				if (array_key_exists($itemName, $this->myTxnSpec))
//					{
//					$this->resultMsg->putMessageData($itemName, $this->myTxnSpec[$itemName]);
//					$eleCount++;
//					}
//				}
//		 	}

		return($eleCount);
		}

	/***************************************************************************
	 * diagnoseTxnDataArray
	 *
	 * publishes the entire transaction data array as a diagnostic dump.
	 *******/
	function diagnoseTxnDataArray($loud = false)
		{
		$arrayContent		= '';

		if ($loud)
			{
			trigger_error("************** " . __CLASS__ . '::' . __FUNCTION__, E_USER_NOTICE);
			}
		else
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "************** " . __CLASS__ . '::' . __FUNCTION__);
			$this->anchor->putTraceData(__LINE__, __FILE__, $this->anchor->whereAmI());
			}

		foreach ($this->myTxnSpec as $eleName => $eleContent)
	 		{
	 		if (is_array($eleContent))
	 			{
	 			foreach ($eleContent as $eleArrayKey => $eleArrayValue)
	 				{
		 			$arrayContent = 'myTxnSpec[' . $eleName . '] = key=[' . $eleArrayKey . '] value=[' . $eleArrayValue . ']';
		 			if ($loud)
			 			{
						trigger_error($arrayContent , E_USER_NOTICE);
			 			}
		 			else
		 				{
						$this->anchor->putTraceData(__LINE__, __FILE__, $arrayContent);
		 				}
	 				}
	 			}
	 		else
	 			{
	 			$arrayContent = 'myTxnSpec[' . $eleName . '] = [' . $eleContent . ']';
	 			if ($loud)
		 			{
					trigger_error($arrayContent , E_USER_NOTICE);
		 			}
	 			else
	 				{
					$this->anchor->putTraceData(__LINE__, __FILE__, $arrayContent);
	 				}
	 			}
			}
		if (! $loud)
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, '**************');
			}

		return;
		}

	/***************************************************************************
	 * postTxnDataArrayToEleDoc
	 *
	 * Post the data from the myTxnSpec array to the element document. Posting is
	 * based on element type. For those data elements that do not exist in the
	 * myTxnSpec data array, default values are posted for the element type
	 * specification.
	 *******/
	function postTxnDataArrayToEleDoc(& $eleDoc)
		{
		$content				= NULL;
		$isAssociation		= false;

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

//		$this->backpostBaseTxnSpecArray($eleDoc);

		$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
			}
		else
			{
			$eleIdent	= $eleDoc->getDocumentId();
			foreach ($eleSpec as $eleRule)
			 	{
			 	if ($eleRule['elementSpec'])
			 		{
				 	$itemName 	= $eleRule['FldName'];
					$isAssociation		= false;
				 	if ($eleRule['RelClass'] ==  AIR_RelClass_Associations)
				 		{
						if (($this->anchor != NULL) && ($this->anchor->trace()))
							{
							$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " posting $itemName as ASSOCIATION");
							}
						if (! array_key_exists('HasInverse', $eleRule))
							{
							if (($this->anchor != NULL) && ($this->anchor->trace()))
								{
								$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item has no inverse indicator flag!");
								}
							}
						else
						if ($eleRule['HasInverse'])
							{
							if (! array_key_exists('InvPredType', $eleRule))
								{
								if (($this->anchor != NULL) && ($this->anchor->trace()))
									{
									$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item w/inverse has no inverse predicate!");
									}
								}
							else
							if ((empty($eleRule['InvPredType']))
							 || ($eleRule['InvPredType'] == AIR_Null_Identifier))
								{
								if (($this->anchor != NULL) && ($this->anchor->trace()))
									{
									$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item has invalid inverse predicate!");
									}
								}
							else
								{
								switch ($eleRule['DataType'])
									{
									case AIR_ContentType_UUID:
									case AIR_ContentType_UUIDList:
										$isAssociation		= true;
									 	$predType = $eleRule['PredType'];
										break;
									default:
										if (($this->anchor != NULL) && ($this->anchor->trace()))
											{
											$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item has invalid datatype!");
											}
										break;
									}
								}
							}
				 		}
				 	else
				 		{
						$isAssociation		= false;
						if (($this->anchor != NULL) && ($this->anchor->trace()))
							{
							$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " posting $itemName as PROPERTY");
							}
				 		}
					if (! array_key_exists($itemName, $this->myTxnSpec))
						{
						// Supply default value
						$this->myTxnSpec[$itemName] = $this->getTxnDataDefaultValue($eleRule);
						}

					// Complex data movement, based on type
					switch ($eleRule['DataType'])
						{
						case AIR_ContentType_UUIDList:
							// Build a change array before changing the element details
							$changes = $this->getChangedItemListArray($eleDoc, $itemName);
							if (! empty($changes))
								{
								// Purge the old collection to ensure we remove any old items
								// that are no longer supported.
								$eleDoc->purgeDataCollectionItems($itemName);
								// Regenerate from the txnSpec
								$eleContent = $this->myTxnSpec[$itemName];
					 			foreach ($eleContent as $eleArrayKey => $eleArrayValue)
					 				{
			 						if (!empty($eleArrayValue))
			 							{
										$newNode = $eleDoc->createElement($itemName);

										$node = $eleDoc->createTextElement('Key', $eleArrayKey);
										$newNode->appendChild($node);
										$node = $eleDoc->createTextElement('Value', $eleArrayValue);
										$newNode->appendChild($node);

										$eleDoc->createNewDataCollectionItem($newNode);
										}
					 				}

								if ($isAssociation)
									{
									// We need to update the inverse associations in partner
									// elements for those associations which have been modified
									foreach ($changes as $chgArrayKey => $chgArrayValue)
										{
										if ($chgArrayValue['old'] != $chgArrayValue['new'])
											{
											$postingOld = false;
											$postingNew = false;
											if (! empty($chgArrayValue['old']))
												{
												$postingOld = true;
												$this->postAssocChgToInverseEleDoc($eleDoc, $eleRule, $chgArrayKey, NULL);
												$this->anchor->adjustAirEleRelationship($eleIdent, $predType, $chgArrayKey, false);
												}
											if (! empty($chgArrayValue['new']))
												{
												$postingNew = true;
												$this->postAssocChgToInverseEleDoc($eleDoc, $eleRule, NULL, $chgArrayKey);
												$this->anchor->adjustAirEleRelationship($eleIdent, $predType, $chgArrayKey, true);
												}
											if ($postingOld && $postingNew)
												{
												$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " posting old and new for same item key");
												}
											}
										}
									}
				 				}
							break;

						case AIR_ContentType_UUID:
							// Straight movement of variables one-to-one, but in case of
							// associations we also need to remember the old value for the
							// inverse update.
							$oldItemValue = $eleDoc->getElementData($itemName);
							if ($this->myTxnSpec[$itemName] != $oldItemValue)
								{
								$eleDoc->putElementData($itemName,		$this->myTxnSpec[$itemName]);

								if ($isAssociation)
									{
									// We need to update the inverse associations in partner
									// elements for those associations which have been modified
									$this->postAssocChgToInverseEleDoc($eleDoc, $eleRule, $oldItemValue, $this->myTxnSpec[$itemName]);
									if ((! empty($oldItemValue))
									 && ($oldItemValue != AIR_Null_Identifier))
										{
										$this->anchor->adjustAirEleRelationship($eleIdent, $predType, $oldItemValue, false);
										}
									if ((! empty($this->myTxnSpec[$itemName]))
									 && ($this->myTxnSpec[$itemName] != AIR_Null_Identifier))
										{
										$this->anchor->adjustAirEleRelationship($eleIdent, $predType, $this->myTxnSpec[$itemName], true);
										}
									}
								}
							break;

						default:
							// Straight movement of variables one-to-one
							$eleDoc->putElementData($itemName,		$this->myTxnSpec[$itemName]);

							break;
						}
					}
			 	}
		 	}

		return;
		}

	/***************************************************************************
	 * crosspostDeletedEleDoc
	 *
	 * Cross post the deletion of an element document. At creation and during
	 * change maintenance, selected details of element documents are cross
	 * posted to other elements to form a bidirectional link between the two
	 * items. This function removes the cross posted links on peer elements
	 * at the time of deletion of a element.
	 *******/
	function crosspostDeletedEleDoc(& $eleDoc)
		{
		$content				= NULL;
		$isAssociation		= false;
		$successful			= true;

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

//		$this->backpostBaseTxnSpecArray($eleDoc);

		$eleType 	= $eleDoc->getDocType();
		$eleIdent	= $eleDoc->getDocumentId();
		$eleSpec = $this->anchor->getCompositionByEleType($eleType);
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
			$successful = false;
			}
		else
			{
			foreach ($eleSpec as $eleRule)
			 	{
			 	if ($eleRule['elementSpec'])
			 		{
				 	$itemName = $eleRule['FldName'];
					$isAssociation		= false;
				 	if ($eleRule['RelClass'] ==  AIR_RelClass_Associations)
				 		{
//						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " posting $itemName as ASSOCIATION");
						if (! array_key_exists('HasInverse', $eleRule))
							{
							$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item has no inverse indicator flag!");
							}
						else
						if ($eleRule['HasInverse'])
							{
							if (! array_key_exists('InvPredType', $eleRule))
								{
								$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item w/inverse has no inverse predicate!");
								}
							else
							if ((empty($eleRule['InvPredType']))
							 || ($eleRule['InvPredType'] == AIR_Null_Identifier))
								{
								$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item has invalid inverse predicate!");
								}
							else
								{
								switch ($eleRule['DataType'])
									{
									case AIR_ContentType_UUID:
									case AIR_ContentType_UUIDList:
										$isAssociation		= true;
									 	$predType = $eleRule['PredType'];
										break;
									default:
										$this->anchor->putTraceData(__LINE__, __FILE__, "ASSOCIATION item has invalid datatype!");
										break;
									}
								}
							}
				 		}
				 	else
				 		{
						$isAssociation		= false;
						if (($this->anchor != NULL) && ($this->anchor->trace()))
							{
							$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " posting $itemName as PROPERTY");
							}
				 		}

					// Complex data movement, based on type
					switch ($eleRule['DataType'])
						{
						case AIR_ContentType_UUIDList:
							// Build a change array before changing the element details
							$changes = $this->getElementItemListArray($eleDoc, $itemName);
							if (! empty($changes))
								{
								if ($isAssociation)
									{
									// We need to update the inverse associations in partner
									// elements for those associations which existed
									foreach ($changes as $chgArrayKey => $chgArrayValue)
										{
										if (! empty($chgArrayValue['old']))
											{
											$this->postAssocChgToInverseEleDoc($eleDoc, $eleRule, $chgArrayKey, NULL);
											$this->anchor->adjustAirEleRelationship($eleIdent, $predType, $chgArrayKey, false);
											}
										}
									}
				 				}
							break;

						case AIR_ContentType_UUID:
							// Straight movement of variables one-to-one, but in case of
							// associations we also need to remember the old value for the
							// inverse update.
							$oldItemValue = $eleDoc->getElementData($itemName);
							if (! empty($oldItemValue))
								{
								if ($isAssociation)
									{
									// We need to update the inverse associations in partner
									// elements for those associations which have been modified
									$this->postAssocChgToInverseEleDoc($eleDoc, $eleRule, $oldItemValue, NULL);
									$this->anchor->adjustAirEleRelationship($eleIdent, $predType, $oldItemValue, false);
									}
								}
							break;

						default:
							break;
						}
					}
			 	}
		 	}

		return($successful);
		}

	/***************************************************************************
	 * postAssocChgToInverseEleDoc
	 *
	 * Post an association change to the opposite element in the association.
	 * This routine will perform the following detail functions:
	 * 1) Look-up the inverse association and determine the datatype of the inverse side.
	 * 2) If an old object is defined, remove the association to the old object.
	 * 3) If a new object is defined, add an association to the new object.
	 *******/
	function postAssocChgToInverseEleDoc(& $eleDoc, $eleRule, $oldObject, $newObject)
		{
		$eleIdent			= $eleDoc->getDocumentId();
		$invPredicate		= $eleRule['InvPredType'];
		$relObject			= NULL;

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Determine posting strategy based on inverse predicate data type
		 */
		$invPredDef			= & $this->anchor->getRefElement($invPredicate);
		if (! is_object($invPredDef))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Inverse predicate not found");
			return;
			}

		if ($invPredDef->getElementData('RelClass') != AIR_RelClass_Associations)
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Inverse predicate is not an association");
			return;
			}

		$invPredDataType	= $invPredDef->getElementData('DataType');
		$invPredFldName	= $invPredDef->getElementData('FldName');

		if (! empty($oldObject))
			{
			$relObject		= & $this->anchor->getRefElement($oldObject);
			if (! is_object($relObject))
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Inverse object not found");
				return;
				}
			switch ($invPredDataType)
				{
				case AIR_ContentType_UUIDList:
					if (($this->anchor != NULL) && ($this->anchor->trace()))
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " list DELETE [$oldObject]");
						}
					$collectionSize = $relObject->getDataCollectionItemCount($invPredFldName);
					$itemRemoved = false;
					if ($collectionSize > 0)
						{
						for ($i = 0; $i < $collectionSize; $i++)
							{
							$collectionItemNode = $relObject->getDataCollectionItem($invPredFldName, $i);
							if ($collectionItemNode != null)
								{
								$nodeKey		= $collectionItemNode->getChildContentByName('Key');
								$nodeValue	= $collectionItemNode->getChildContentByName('Value');
								if (($nodeKey == $eleIdent)
								 && ($nodeValue == true))
									{
									/*
									 * Remove the item from the purge list
									 */
									$relObject->removeDataCollectionItemByIndex($invPredFldName, $i);
									$itemRemoved = true;
									$this->anchor->adjustAirEleRelationship($oldObject, $invPredicate, $eleIdent, false);
									}
								}
							}
						}
					if (! $itemRemoved)
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " $invPredFldName warning, item not found");
						return;
						}
					break;

				case AIR_ContentType_UUID:
					if (($this->anchor != NULL) && ($this->anchor->trace()))
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " item DELETE [$oldObject]");
						}
					$relInvItem	= $relObject->getElementData($invPredFldName);
					if (empty($relInvItem))
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " $invPredFldName warning, item not found");
						return;
						}
					else
					if ($relInvItem != $eleIdent)
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " $invPredFldName warning, inverse item not reciprocal");
						return;
						}
					$relObject->removeElementNode($invPredFldName);
					$this->anchor->adjustAirEleRelationship($oldObject, $invPredicate, $eleIdent, false);
					break;
				}
			}

		if (! empty($newObject))
			{
			$relObject		= & $this->anchor->getRefElement($newObject);
			if (! is_object($relObject))
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Inverse object not found");
				return;
				}
			switch ($invPredDataType)
				{
				case AIR_ContentType_UUIDList:
					if (($this->anchor != NULL) && ($this->anchor->trace()))
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " list ADD [$newObject]");
						}
					/*
					 * Scan for item already there
					 */
					$collectionSize = $relObject->getDataCollectionItemCount($invPredFldName);
					if ($collectionSize > 0)
						{
						// Obtain reference to detail array in txnSpec for comparison
						for ($i = 0; $i < $collectionSize; $i++)
							{
							$collectionItemNode = $relObject->getDataCollectionItem($invPredFldName, $i);
							$nodeKey		= $collectionItemNode->getChildContentByName('Key');
							$nodeValue	= $collectionItemNode->getChildContentByName('Value');
							if (($nodeKey == $eleIdent)
							 && ($nodeValue == true))
								{
								$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " $invPredFldName conflict, item already exists");
								return;
								}
							}
						}
					/*
					 * Add the new item
					 */
					$newNode = $relObject->createElement($invPredFldName);

					$node = $relObject->createTextElement('Key', $eleIdent);
					$newNode->appendChild($node);
					$node = $relObject->createTextElement('Value', true);
					$newNode->appendChild($node);

					$relObject->createNewDataCollectionItem($newNode);
					$this->anchor->adjustAirEleRelationship($newObject, $invPredicate, $eleIdent, true);
					break;

				case AIR_ContentType_UUID:
					if (($this->anchor != NULL) && ($this->anchor->trace()))
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " item ADD [$newObject]");
						}
					$relInvItem	= $relObject->getElementData($invPredFldName);
					if (! empty($relInvItem))
						{
						$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " $invPredFldName conflict, item already exists");
						return;
						}
					$relObject->putElementData($invPredFldName, $eleIdent);
					$this->anchor->adjustAirEleRelationship($newObject, $invPredicate, $eleIdent, true);
					break;
				}
			}

		if (! is_null($relObject))
			{
			$subjectName = $eleDoc->getPreferredName();
			$relObject->putDocUpdateParty($this->myTxnSpec['Author']);
			$relObject->putDocUpdateTime(date("YmdHisO"));
			$relObject->putDocUpdateType(AIR_EleChgType_Modify);
			$relObject->putDocUpdateComment('Cross-post of changes made to ' . $subjectName);

			/*
			 * Persist the element definition to the database
			 */
	 		$relObject->persist();
	 		}

		return;
		}

	/***************************************************************************
	 * getTxnDataDefaultValue
	 *
	 * Generate a default entry for a data item purely based on the specification
	 * for the item.
	 *******/
	function getTxnDataDefaultValue($eleItem)
		{
		$resultValue		= '';

		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

			$itemDataType		= $eleItem['DataType'];
			$itemDefault		= $eleItem['Default'];

		if (!empty($itemDefault))
			{
			/*
			 * We have a default value,
			 * we just need to put selected items into the proper data format.
			 */
			if ($itemDataType == AIR_ContentType_Boolean)
				{
				$resultValue = $this->anchor->getBoolEvaluation($itemDefault);
				}
			else
			if ($itemDataType == AIR_ContentType_UUIDList)
				{
				$collection = array();
				$collection[$itemDefault] = true;
				$resultValue = $collection;
				}
			else
				{
				$resultValue = $itemDefault;
				}
			}
		else
			{
			/*
			 * We don't have a default value,
		 	 * we need to check to see what form of value to create.
			 */
			switch ($itemDataType)
				{
				case AIR_ContentType_UUID:
					$resultValue = AIR_Null_Identifier;
					break;
				case AIR_ContentType_UUIDList:
					$resultValue = array();
					break;
				case AIR_ContentType_OrdSpec:
					trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
					break;
				case AIR_ContentType_Boolean:
					$resultValue = false;
					break;
				default:
					$resultValue = '';
					break;
				}
			}

		return;
		}

	/***************************************************************************
	 * editBaseEleSpec
	 *
	 * Perform common edits on base element definitions.
	 *******/
	function editBaseEleSpec()
		{
		if (($this->anchor->trace())
		 || ($this->anchor->traceMsgDataFlow()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$this->diagnoseTxnDataArray();
			}

		$errorDiagnosed	= false;

		if ((! array_key_exists('EleType', $this->myTxnSpec))
		 || (! array_key_exists('EleName', $this->myTxnSpec))
		 || (! array_key_exists('Author', $this->myTxnSpec))
		 || (empty($this->myTxnSpec['EleType']))
		 || (empty($this->myTxnSpec['EleName']))
		 || (empty($this->myTxnSpec['Author'])))
			{
			$this->resultMsg->attachDiagnosticTextItem('Element Definition', 'Critical data missing');
			if ($this->anchor->debug())
				{
				$this->anchor->putTraceData(__LINE__, __FILE__, __FUNCTION__ . " critical data missing" , E_USER_NOTICE);
				}
			if (empty($this->myTxnSpec['EleType']))
				{
				$this->resultMsg->attachDiagnosticTextItem('Element Type', 'Data not defined');
				}
			if (empty($this->myTxnSpec['EleName']))
				{
				$this->resultMsg->attachDiagnosticTextItem('Element Name', 'Data not defined');
				}
			if (empty($this->myTxnSpec['Author']))
				{
				$this->resultMsg->attachDiagnosticTextItem('Author', 'Data not defined');
				}
			$errorDiagnosed	= true;
			}
		else
		if (strlen($this->myTxnSpec['EleName']) > 255)
			{
			$this->resultMsg->attachDiagnosticTextItem('Element Name', 'Data exceeds size constraint');
			$this->resultMsg->attachDiagnosticTextItem('Element Name', 'Data size is '.strlen($this->myTxnSpec['EleName']));
			$errorDiagnosed	= true;
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * editAirInfoMaintSpec
	 *
	 * Perform common edits on element definitions relative to DB maintenance
	 * and annotation information.
	 *******/
	function editAirInfoMaintSpec()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= false;

		if ((array_key_exists('EleChgComments', $this->myTxnSpec))
		 || (! empty($this->myTxnSpec['EleChgComments'])))
			{
			if (strlen($this->myTxnSpec['EleChgComments']) > 255)
				{
				$this->resultMsg->attachDiagnosticTextItem('Change Comment', 'Data exceeds size constraint');
				$this->resultMsg->attachDiagnosticTextItem('Change Comment', 'Data size is '.strlen($this->myTxnSpec['EleChgComments']));
				$errorDiagnosed	= true;
				}
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * editEleSpecStructure
	 *
	 *******/
	function editEleSpecStructure()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= $this->editBaseEleSpec();
		if (! $errorDiagnosed)
			{
			$eleSpec = $this->anchor->getCompositionByEleType($this->myTxnSpec['EleType']);
			if ((!is_array($eleSpec))
			 || (empty($eleSpec)))
				{
				trigger_error("Critical data error in ".__FUNCTION__, E_USER_NOTICE);
				}
			else
				{
				foreach ($eleSpec as $eleItem)
				 	{
				 	$itemName 			= $eleItem['FldName'];
				 	$itemSpecType		= $eleItem['specType'];
					$itemLabel			= $eleItem['Label'];
					$itemManualInput	= $eleItem['Manual'];
					$itemOrdSpec		= $eleItem['PredOrdSpec'];
					$itemRequired		= ($itemOrdSpec == AIR_EleIsRequired);
					$itemDataType		= $eleItem['DataType'];
					$itemSelType		= $eleItem['SelectionType'];

					if (($itemSpecType == 'basic')
					 || ($itemSpecType == 'compound'))
						{
						switch ($itemDataType)
							{
							case AIR_ContentType_OrdSpec:
								$errorDiagnosed	|= $this->editOrdinalitySpecs($itemLabel,	$itemName, $itemRequired);
								break;

							case AIR_ContentType_UUID:
							case AIR_ContentType_ExtText:
							case AIR_ContentType_ExtTextBlock:
							case AIR_ContentType_ExtHyperlink:
								$errorDiagnosed	|= $this->editEleRefItemExists($itemLabel, $itemName, $itemRequired);
								break;

							case AIR_ContentType_Integer:
								$errorDiagnosed	|= $this->editEleNumericItem($itemLabel, $itemName, $itemRequired);
								break;

							case AIR_ContentType_Boolean:
								break;

							case AIR_ContentType_UUIDList:
								break;

							case AIR_ContentType_IntText:
							case AIR_ContentType_IntHyperlink:
							case AIR_ContentType_IntTextBlock:
								$errorDiagnosed	|= $this->editEleTextItem($itemLabel, $itemName, $itemRequired);
								break;

							case AIR_ContentType_Binary:
							case AIR_ContentType_Float:
							case AIR_ContentType_Date:
							case AIR_ContentType_Time:
							case AIR_ContentType_Datetime:
							default:
								break;
							}
						}
				 	}
				}
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * editEleRefItemExists
	 *
	 * Detail item edit for items which contain references to other elements.
	 *******/
	function editEleRefItemExists($labelName, $itemName, $required=false)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= false;

		if ((! array_key_exists($itemName, $this->myTxnSpec))
		 || (empty($this->myTxnSpec[$itemName]))
		 || (! is_string($this->myTxnSpec[$itemName])))
			{
			if ((! is_string($this->myTxnSpec[$itemName]))
			 || ($required))
				{
				$this->resultMsg->attachDiagnosticTextItem($labelName, 'Content not valid, must be a valid element reference');
				$errorDiagnosed	= true;
				}
			}
		else
			{
			if ($required)
				{
				if ($this->myTxnSpec[$itemName] == AIR_Null_Identifier)
					{
					$this->resultMsg->attachDiagnosticTextItem($labelName, 'Null content is not valid');
					$errorDiagnosed	= true;
					}
				}
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * editEleNumericItem
	 *
	 * Detail item edit for elements that contain data values other than references.
	 *******/
	function editEleNumericItem($labelName, $itemName, $required=false)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= false;

		if ((! array_key_exists($itemName, $this->myTxnSpec))
		 || (empty($this->myTxnSpec[$itemName])))
			{
			if ($required)
				{
				$this->resultMsg->attachDiagnosticTextItem($labelName, 'Required item not defined');
				$errorDiagnosed	= true;
				}
			}
		else
		if (! is_numeric($this->myTxnSpec[$itemName]))
			{
			$this->resultMsg->attachDiagnosticTextItem($labelName, 'Must be a numeric specification');
			$this->resultMsg->attachDiagnosticTextItem($labelName, 'Value ['.$this->myTxnSpec[$itemName].'] was removed');
			$this->myTxnSpec[$itemName] = '';
			$errorDiagnosed	= true;
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * editEleTextItem
	 *
	 *******/
	function editEleTextItem($labelName, $itemName, $required=false)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= false;

		if ((! array_key_exists($itemName, $this->myTxnSpec))
		 || (empty($this->myTxnSpec[$itemName])))
			{
			if ($required)
				{
				$this->resultMsg->attachDiagnosticTextItem($labelName, 'Required item has no content');
				$errorDiagnosed	= true;
				}
			}
		else
		if (! is_string($this->myTxnSpec[$itemName]))
			{
			$this->resultMsg->attachDiagnosticTextItem($labelName, 'Content not valid, must be string data');
			$this->resultMsg->attachDiagnosticTextItem($labelName, 'Value was removed');
			$this->myTxnSpec[$itemName] = '';
			$errorDiagnosed	= true;
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * editOrdinalitySpecs
	 *
	 *******/
	function editOrdinalitySpecs($seriesName, $itemPrefix, $required=false)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errorDiagnosed	= false;

		$ordSpecName		= $seriesName . ' Ordinality';
		$cardSpecName		= $seriesName . ' Cardinality';
		$cardLimitName		= $seriesName . ' Max Occurs';
		$ordSpecVar			= $itemPrefix . 'OrdSpec';
		$cardSpecVar		= $itemPrefix . 'CardSpec';
		$cardLimitVar		= $itemPrefix . 'CardLimit';

		$errorDiagnosed	|= $this->editEleRefItemExists($ordSpecName, $ordSpecVar, $required);
		$errorDiagnosed	|= $this->editEleRefItemExists($cardSpecName, $cardSpecVar);
		$errorDiagnosed	|= $this->editEleNumericItem($cardLimitName, $cardLimitVar);

		if (! $errorDiagnosed)
			{
			switch ($this->myTxnSpec[$ordSpecVar])
				{
				case AIR_Null_Identifier:
					$this->resultMsg->attachDiagnosticTextItem($ordSpecName, 'Null content is not valid');
					$errorDiagnosed	= true;
					break;
				case AIR_EleIsRequired:
				case AIR_EleIsDesired:
				case AIR_EleIsOptional:
				case AIR_EleIsNotPreferred:
					switch ($this->myTxnSpec[$cardSpecVar])
						{
						case AIR_Null_Identifier:
							$this->resultMsg->attachDiagnosticTextItem($cardSpecName, 'Null content is not valid');
							$errorDiagnosed	= true;
							break;
						case AIR_RelIsUnique:
							$maxValue = $this->myTxnSpec[$cardLimitVar];
							if ((empty($maxValue))
							 || ($maxValue == '0')
							 || ($maxValue == '1'))
								{
								$this->myTxnSpec[$cardLimitVar] = '';
								}
							else
								{
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Conflicts with Cardinality specification');
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Value ['.$this->myTxnSpec[$cardLimitVar].'] was removed');
								$this->myTxnSpec[$cardLimitVar] = '';
								$errorDiagnosed	= true;
								}
							break;
						case AIR_RelIsCollection:
							$maxValue = $this->myTxnSpec[$cardLimitVar];
							if (empty($maxValue))
								{
								$this->myTxnSpec[$cardLimitVar] = '';
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Value assumed unlimited');
								}
							else
							if (! is_numeric($maxValue))
								{
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Must be a numeric specification');
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Value ['.$this->myTxnSpec[$cardLimitVar].'] was removed');
								$this->myTxnSpec[$cardLimitVar] = '';
								$errorDiagnosed	= true;
								}
							else
							if ($maxValue < 2)
								{
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Conflicts with Cardinality specification');
								$errorDiagnosed	= true;
								}
							break;
						case AIR_RelIsNotAllowed:
							$this->resultMsg->attachDiagnosticTextItem($cardSpecName, 'Conflicts with Ordinality specification');
							$errorDiagnosed	= true;
							break;
						default:
							$this->resultMsg->attachDiagnosticTextItem($cardSpecName, 'Unrecognized value ['.$this->myTxnSpec[$cardSpecVar].'] was removed');
							$this->myTxnSpec[$cardSpecVar] = AIR_Null_Identifier;
							$errorDiagnosed	= true;
							break;
						}
					break;
				case AIR_EleIsNotAllowed:
					switch ($this->myTxnSpec[$cardSpecVar])
						{
						case AIR_RelIsUnique:
						case AIR_RelIsCollection:
						default:
							$this->resultMsg->attachDiagnosticTextItem($cardSpecName, 'Conflicts with Ordinality specification');
							$errorDiagnosed	= true;
							// Drop-thru for last check
						case AIR_Null_Identifier:
							$this->resultMsg->attachDiagnosticTextItem($cardSpecName, 'Changed to -Not Allowed-');
							$this->myTxnSpec[$cardSpecVar] = AIR_RelIsNotAllowed;
							// By itself, this is NOT considered an error
							// Drop-thru for last check
						case AIR_RelIsNotAllowed:
							if (! empty($this->myTxnSpec[$cardLimitVar]))
								{
								$this->resultMsg->attachDiagnosticTextItem($cardLimitName, 'Value ['.$this->myTxnSpec[$cardLimitVar].'] was removed');
								$errorDiagnosed	= true;
								}
							if (! strlen($this->myTxnSpec[$cardLimitVar]) == 0)
								{
								// We either failed the above test, or we are "empty" with a value of zero specified.
								// Either way, remove the content and make it "empty"
								$this->myTxnSpec[$cardLimitVar] = '';
								}
							break;
						}
					break;
				default:
					break;
				}
			}

		return($errorDiagnosed);
		}

	/***************************************************************************
	 * procDefault
	 *
	 *******/
	function procDefault()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$encodeObject	= Dialog_Home;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';
		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	} // End of class C_AirProcModBase

/*
 * This code is executed once per 'include' as the module is scanned
 * and all code not inside newly defined "function blocks" is executed.
 */
	if ($sysDiag)
		{
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br/></debug> ';
		}
 ?>