<?php
/*
 * AirLib script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.4 2005-DEC-30 JVS Original code
 *
 * This module is the primary business logic processing module for BEAMS 'rules'
 * element types.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ProcModEleIndex';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ProcModEleIndex extends C_AirProcModBase {
	var $rowCount						= 0; // Number of index entries in the database

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
	 * ProcMod_EleIndexInit
	 *
	 *******/
	function ProcMod_EleIndexInit()
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$this->rowCount	= $this->anchor->myDbLayer->getCount_AirEleIndex();
		}

	/***************************************************************************
	 * ProcMod_Main
	 *
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		parent::initialize($procContext, $baseMsg, $procMsg);
		$this->ProcMod_EleIndexInit();

		$this->initResultMsg();
		$this->myDialog	= Dialog_EleIndex;

		$msgDiag		= 'object = ['.$this->myMsgObject.'] action = ['.$this->myMsgAction.']';
		$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);

		switch ($this->myMsgAction)
			{
			case AIR_Action_EleCreateIndex:
			case AIR_Action_EleUpdateIndex:
			case AIR_Action_EleTypeIndex:
				$this->procDbConvertShowItem();
				break;
			default:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ FALL-THROUGH');
				$this->procDefault();
				break;
			}

		$result = $this->publishTxnDataArrayToResultMsg();
		if ($result < 0)
			{
			trigger_error("Critical data error in __FUNCTION__" , E_USER_NOTICE);
			}

		$this->postResultMsg();

		$this->anchor->putTraceData(__LINE__, __FILE__, 'Completing ' . __CLASS__ . '::' . __FUNCTION__);
		$this->anchor->putTraceData(__LINE__, __FILE__, '+++ AUDIT PROC +++ AUDIT PROC +++ AUDIT PROC +++ AUDIT PROC +++++++++');
		}

	/***************************************************************************
	 * procDbConvertShowItem
	 *******/
	function procDbConvertShowItem()
		{
		$includeMessages			= false;
		$includeSessions			= false;
		$includeInfrastructure	= false;
		$includeMetadata			= false;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		switch ($this->myMsgAction)
			{
			/********************
			Air_Ele_CreateDt
			Air_Ele_CreateEntity
			Air_Ele_ChgDt
			Air_Ele_ChgType
			Air_Ele_ChgEntity
			Air_Ele_ChgComments
			Air_Ele_ChgPubWorkflow
			Air_Ele_ChgPendingStatus
			Air_Ele_EffDtStart
			Air_Ele_EffDtEnd
			Air_Ele_CntElements
			Air_Ele_CntAssociations
			Air_Ele_CntProperties
			Air_Ele_CntRelationships
			Air_Ele_RefbyElements
			Air_Ele_RefByAssociations
			Air_Ele_RefByProperties
			Air_Ele_RefByRelationships
			Air_Ele_EleType
			Air_Ele_EleName
			Air_Ele_EleContentSize
			******************/
			case AIR_Action_EleCreateIndex:
			 	$attrkey				= 'Air_Ele_CreateDt';
				break;
			case AIR_Action_EleUpdateIndex:
			 	$attrkey				= 'Air_Ele_ChgDt';
				break;
			case AIR_Action_EleTypeIndex:
			 	$attrkey				= 'Air_Ele_EleType';
				break;
			default:
				$this->anchor->putTraceData(__LINE__, __FILE__, '+++ FALL-THROUGH');
				$this->procDefault();
				break;
			}

		if ($attrkey)
			{
			$attrCountArray	= $this->anchor->myDbLayer->getCountByAttr_AirEleIndex($attrkey);
			$attrGroups			= count($attrCountArray);

			$this->anchor->putTraceData(__LINE__, __FILE__, $this->rowCount.' Index table elements');
			$this->anchor->putTraceData(__LINE__, __FILE__, $attrGroups.' Index element types');
			$summation = 0;
			for ($i = 0; $i < $attrGroups; $i++)
				{
				$dbCountArray 		= $attrCountArray[$i];
				$countType			= $dbCountArray[$attrkey];
				$typeCount			= $dbCountArray['TotalRows'];
				if ($this->myMsgAction == AIR_Action_EleTypeIndex)
					{
					$typeName			= $this->anchor->getRefName($countType);
					}
				else
					{
					$typeName			= $countType;
					}
				$this->anchor->putTraceData(__LINE__, __FILE__, $typeCount.' '.$typeName.' elements');
				$summation += $typeCount;
				}
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Sum = '.$summation.' elements');

			$dbData = $this->anchor->myDbLayer->get_AllIndexItems($this->rowCount);
			if ((!isset($dbData))
			 || (is_null($dbData))
			 || (!is_array($dbData)))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}

			if ($this->rowCount !=  count($dbData))
				{
				echo $this->anchor->whereAmI();
				die ('Invalid DB query return to ' . __FUNCTION__);
				}
			else if ($this->rowCount > 0)
				{
				/* append the rowset */
				foreach ($dbData as $dbRow)
					{
					$select = true;
					$eleType = $dbRow['Air_Ele_EleType'];
					switch ($eleType)
						{
						case AIR_EleType_ArchMessage:
							$select = $includeMessages;
							break;
						case AIR_EleType_WebSession:
							$select = $includeSessions;
							break;
						default:
							/*
							 * Be careful about logic overlap here ...
							 * all infrastructure elements are also considered to be
							 * core metadata. This logic will exclude the infrastructure
							 * elements from the metadata unless specifically included
							 * by the option flags.
							 */
							if ($this->anchor->isInfrastructureElement($eleType))
								{
								$select = $includeInfrastructure;
								}
							else
							if ($this->anchor->isCoreElementType($eleType))
								{
								$select = $includeMetadata;
								}
							break;
						}

					if ($select)
						{
						$manifestItem 			= $this->resultMsg->createElement('Item');

						$node = $this->resultMsg->createTextElement('OldKey',		$dbRow['Air_Ele_Id']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('NewKey',		$dbRow['Air_Ele_Id']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('Name',		$dbRow['Air_Ele_EleName']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('Type',		$dbRow['Air_Ele_EleType']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('Assigned',	$dbRow['Air_Ele_CreateDt']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('Created',	$dbRow['Air_Ele_CreateDt']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('Reviewed',	$dbRow['Air_Ele_ChgDt']);
						$manifestItem->appendChild($node);

						$node = $this->resultMsg->createTextElement('Converted',	$dbRow['Air_Ele_ChgDt']);
						$manifestItem->appendChild($node);

						$this->resultMsg->createNewDataCollectionItem($manifestItem);
						}
					}
				}

			$encodeObject	= Dialog_EleIndex;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
			$this->resultMsg->attachDiagnosticTextItem('Element ID', 'Element specification not found');

			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->myTxnSpec['TxnOper'] =				$this->procContext->getSessionData('eleMaintAction');

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
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br /></debug> ';
		}
 ?>