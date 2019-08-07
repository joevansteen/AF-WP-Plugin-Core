<?php
/*
 * af_reqfilter_dlgEleList script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-15 JVS Bootstrap from af_dialogdecode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ReqFilterDialogEleList';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ReqFilterDialogEleList extends C_AF_ReqFilterBase {

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
		$this->initResultMsg();

//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$msgDiag		= 'driver object ['.$this->myMsgObject.']';
			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			}

		$validated = $this->validateContext();

			switch ($this->myMsgObject)
				{
				case Dialog_EleListContext:
					$this->procEleListContext();
					$this->publishMenuResultMsgInfo();
					break;
				case Dialog_EleIndex:
				case Dialog_EleList:
					$this->procEleList();
					$this->publishMenuResultMsgInfo();
					break;
				}

			$this->postResultMsg();

		}

	/***************************************************************************
	 * procEleListContext
	 *******/
	function procEleListContext()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$txnAction		= $this->procMsg->getMessageData('request');
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		switch ($txnAction)
			{
			case AIR_Action_Reset:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					// trigger_error(__CLASS__ . '::' . __FUNCTION__ . " improper RESET found" , E_USER_NOTICE);
					// echo '<debug>'.__FUNCTION__.'</debug>';
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " improper RESET found");
					}
				$encodeObject	= Dialog_EleList;
				break;
			case AIR_Action_Submit:
			case AIR_Action_Save:
			case AIR_Action_Refresh:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " checkpoint taken");
					}
				$this->setEleListContent();
				$encodeObject	= Dialog_EleList;
				break;
			default:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " panel unrecognized action: $txnAction");
					}
				$encodeObject	= Dialog_MenuSelect;
				break;
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procEleList
	 *******/
	function procEleList()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$txnAction		= $this->procMsg->getMessageData('request');
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		switch ($txnAction)
			{
			case AIR_Action_Reset:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					// trigger_error(__CLASS__ . '::' . __FUNCTION__ . " improper RESET found" , E_USER_NOTICE);
					// echo '<debug>'.__FUNCTION__.'</debug>';
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " improper RESET found");
					}
				$encodeObject	= Dialog_EleList;
				break;
			case AIR_Action_Save:
			case AIR_Action_Refresh:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " checkpoint taken");
					}
				$this->setEleListContent();
				$encodeObject	= Dialog_EleList;
				break;
			case AIR_Action_View:
//				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " admin VIEW");
					}
				$encodeObject	= $this->anchor->getProcModFromEleType(null);
				$encodeAction	= AIR_Action_ShowRaw;
				$this->setEleListContent();
				break;
			case AIR_Action_Delete:
//				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " admin DELETE");
					}
				$this->setEleListContent();
				$encodeObject	= $this->anchor->getProcModFromEleType(null);
				$encodeAction	= AIR_Action_PurgeItems;
				break;
			case AIR_Action_Okay:
			case AIR_Action_ByPass:
				$dlgEleType	= $this->procContext->getSessionData('txnContextEleType');
			   switch ($this->myContextAction)
			   	{
			   	case AIR_Action_Modify:
			   	case AIR_Action_ModifyRaw:
			   	case AIR_Action_DeleteItem:

						$encodeObject = $this->anchor->getProcModFromEleType($dlgEleType);
						$encodeAction	= AIR_Action_ShowItem;
						$this->setEleListContent();
			   		break;

			   	case AIR_Action_ShowItem:

						$encodeObject = $this->anchor->getProcModFromEleType($dlgEleType);
						$encodeAction	= $this->myContextAction;
						$this->setEleListContent();
			   		break;

			   	case AIR_Action_PurgeType:
						if (($this->anchor != NULL) && ($this->anchor->trace()))
						 	{
							$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " PURGE processing initiated");
							}
			   	case AIR_Action_ShowRaw:
			   		$encodeObject	= ProcMod_EleMaint;
						$encodeAction	= $this->myContextAction;
						$this->setEleListContent();
			   		break;
			   	case AIR_Action_Register:
					case AIR_Action_CodeConvert:
					case AIR_Action_IdentConvert:
			   	case AIR_Action_AuditItem:
			   		$encodeObject	= ProcMod_EleAudit;
						$encodeAction	= $this->myContextAction;
						$this->setEleListContent();
			   		break;
			   	case AIR_Action_Add:
			   	case AIR_Action_Load:
			   	default:
						$encodeObject	= Dialog_MenuSelect;
			   		break;
			   	}

				break;
			default:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " panel unrecognized action: $txnAction");
					}
				$encodeObject	= Dialog_MenuSelect;
				break;
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * setEleListContent
	 *
	 * Passes standard content elements from trigger message to result message.
	 *******/
	function setEleListContent()
		{
		$collectionSize	= 0;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->translateBaseMsgSpec();

		$collectionSize = $this->procMsg->getDataCollectionItemCount('dlgObject');
		$this->myTxnSpec['EleCount'] = $collectionSize;
		if ($collectionSize > 0)
			{
			$collection = array();
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$dlgObject = $this->procMsg->getDataCollectionItemContent('dlgObject', $i);
				$collection[] = $dlgObject;
				}
			$this->myTxnSpec['EleIdent'] = $collection;
			}
		}

	}
 ?>