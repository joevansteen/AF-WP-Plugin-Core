<?php
/*
 * af_reqfilter_dlgUserAdmin script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-15 JVS Bootstrap from af_dialogdecode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ReqFilterDialogUserAdmin';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ReqFilterDialogUserAdmin extends C_AF_ReqFilterBase {

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
				case ProcMod_Login:
					$this->procLogin();
					break;
				case ProcMod_Logout:
				case ProcMod_NewUser:
				case ProcMod_ChangePswd:
				case ProcMod_ResetPswd:
					$this->procDefault();
					break;
				}

			$this->postResultMsg();

		}

	/***************************************************************************
	 * procDefault
	 *******/
	function procDefault()
		{
//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$txnAction		= $this->procMsg->getMessageData('request');
		$encodeObject	= $this->myMsgObject;
		$encodeAction	= $txnAction;
		$encodeVers		= '1.0';

		switch ($this->myMsgObject)
			{
			case ProcMod_Logout:
				$encodeObject	= $this->myMsgObject;
				$encodeAction	= $txnAction;
				$encodeVers		= '1.0';
				break;
			case ProcMod_NewUser:
				$this->resultMsg->putMessageData('user',		$this->procMsg->getMessageData('user'));
				$this->resultMsg->putMessageData('pswd',		$this->procMsg->getMessageData('pswd'));
				$this->resultMsg->putMessageData('pswd2',		$this->procMsg->getMessageData('pswd2'));
				$this->resultMsg->putMessageData('regCode',	$this->procMsg->getMessageData('regCode'));

				$encodeObject	= $this->myMsgObject;
				$encodeAction	= $txnAction;
				$encodeVers		= '1.0';
				break;
			case ProcMod_ChangePswd:
				$this->resultMsg->putMessageData('user',		$this->procMsg->getMessageData('user'));
				$this->resultMsg->putMessageData('oldPswd',	$this->procMsg->getMessageData('oldPswd'));
				$this->resultMsg->putMessageData('pswd',		$this->procMsg->getMessageData('pswd'));
				$this->resultMsg->putMessageData('pswd2',		$this->procMsg->getMessageData('pswd2'));
				$this->resultMsg->putMessageData('regCode',	$this->procMsg->getMessageData('regCode'));

				$encodeObject	= $this->myMsgObject;
				$encodeAction	= $txnAction;
				$encodeVers		= '1.0';
				break;
			case ProcMod_ResetPswd:
				$this->resultMsg->putMessageData('user',		$this->procMsg->getMessageData('user'));
				$this->resultMsg->putMessageData('regCode',	$this->procMsg->getMessageData('regCode'));

				$encodeObject	= $this->myMsgObject;
				$encodeAction	= $txnAction;
				$encodeVers		= '1.0';
				break;
			}

		$this->publishBaseResultMsgInfo();
		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procLogin
	 *******/
	function procLogin()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors			= false;

		$txnAction		= $this->procMsg->getMessageData('request');
		$user 			= $this->procMsg->getMessageData('user');
		$pswd 			= $this->procMsg->getMessageData('pswd');

		$this->myTxnSpec['user']				= $this->procMsg->getMessageData('user');
		$this->myTxnSpec['pswd']				= $this->procMsg->getMessageData('pswd');
		$this->myTxnSpec['regCode']			= $this->procMsg->getMessageData('regCode');

		$this->resultMsg->putMessageData('user',				$this->myTxnSpec['user']);
		$this->resultMsg->putMessageData('pswd',				$this->myTxnSpec['pswd']);
		$this->resultMsg->putMessageData('regCode',			$this->myTxnSpec['regCode']);

		$encodeObject	= Dialog_Login;
		$encodeAction	= AIR_Action_Encode;

		$encodeObject	= $this->myMsgObject;
		$encodeAction	= AIR_Action_Login;
		$encodeVers		= '1.0';
		if (empty($user))
			{
			$errors = true;
			}
		if (empty($pswd))
			{
			$errors = true;
			}

		$this->publishBaseResultMsgInfo();
		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	}
 ?>