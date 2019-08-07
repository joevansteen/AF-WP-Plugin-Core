<?php
/*
 * af_reqfilter_dlgOptionsAdmin script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-15 JVS Bootstrap from af_dialogdecode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ReqFilterDialogOptionsAdmin';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ReqFilterDialogOptionsAdmin extends C_AF_ReqFilterBase {

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
				case Dialog_ProcOptions:
					$this->procProcOptions();
					$this->publishMenuResultMsgInfo();
					break;
				}

			$this->postResultMsg();

		}

	/***************************************************************************
	 * procProcOptions
	 *******/
	function procProcOptions()
		{
//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$txnAction		= $this->procMsg->getMessageData('request');
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		$msgDiag		= 'driver action ['.$txnAction.']';
		$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);

		switch ($txnAction)
			{
			case AIR_Action_Reset:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					// trigger_error(__CLASS__ . '::' . __FUNCTION__ . " improper RESET found" , E_USER_NOTICE);
					// echo '<debug>'.__FUNCTION__.'</debug>';
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " improper RESET found");
					}
				$encodeObject	= Dialog_ProcOptions;
				break;
			case AIR_Action_Submit:
			case AIR_Action_Save:
			case AIR_Action_Refresh:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " checkpoint taken");
					}

				if (($txnAction == AIR_Action_Save)
				 || ($txnAction == AIR_Action_Refresh))
					{
					$encodeObject	= Dialog_ProcOptions;
					}
				else
					{
					}

				/*
				 * Now look at what we found relative to inter-related flags and variables
				 * and evaluate the mix. Only 'checked' values are returned, unchecked values
				 * are simply not posted.
				 */

				$optionSettings	= $this->anchor->getStdVar('optionSettings');
				foreach ($optionSettings as $key => $option)
					{
					$fldName		= $option['FldName'];
					$type			= $option['Type'];
					$value		= $option['Value'];

					$dlgName		= 'dlg'.$fldName;
					if ($type == AIR_ContentType_Boolean)
						{
						$dlgValue	= false;
						if ($this->procMsg->getMessageData($dlgName) == 'on')
							{
							$dlgValue = true;
							}
						}
					else
						{
						$dlgValue	= $this->procMsg->getMessageData($dlgName);
						}

					$oldValue	= $this->procContext->getSessionData($fldName);
					if ((! is_null($dlgValue))
					 && ($dlgValue != $oldValue))
						{
						/*
						 * We have a change in value
						 */
						$this->procContext->putSessionData($fldName, $dlgValue);
						}
					}

				$this->anchor->restore_BehaviorFlags();

				if (($txnAction == AIR_Action_Save)
				 || ($txnAction == AIR_Action_Refresh))
					{
					$encodeObject	= Dialog_ProcOptions;
					}
				else
					{
					$encodeObject	= Dialog_MenuSelect;
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

	}
 ?>