<?php
/*
 * af_viewMgr_userSecurity script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from af_dialogencode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
require(AF_AUTOMATED_SCRIPTS.'/af_viewMgr_base.php');
$myProcClass = 'C_ViewMgrUserSecurity';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ViewMgrUserSecurity extends C_ViewMgrBase {
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
			case Dialog_Login:
			case Dialog_NewUser:
			case Dialog_ChangePswd:
			case Dialog_ResetPswd:
				$this->procLogin();
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
	 * procLogin
	 *******/
	function procLogin()
		{
		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		switch ($this->myMsgObject)
			{
			case Dialog_Login:
				$this->anchor->setDlgVar('user',		$this->procMsg->getMessageData('user'));
				$this->anchor->setDlgVar('pswd',		$this->procMsg->getMessageData('pswd'));
				break;
			case Dialog_NewUser:
//				echo 'user = '.$this->procMsg->getMessageData('user');
//				echo 'pswd = '.$this->procMsg->getMessageData('pswd');
//				echo 'pswd2 = '.$this->procMsg->getMessageData('pswd2');
				$this->anchor->setDlgVar('user',		$this->procMsg->getMessageData('user'));
				$this->anchor->setDlgVar('pswd',		$this->procMsg->getMessageData('pswd'));
				$this->anchor->setDlgVar('pswd2',	$this->procMsg->getMessageData('pswd2'));
				break;
			case Dialog_ChangePswd:
//				echo 'user = '.$this->procMsg->getMessageData('user');
//				echo 'pswd = '.$this->procMsg->getMessageData('pswd');
//				echo 'pswd2 = '.$this->procMsg->getMessageData('pswd2');
				$this->anchor->setDlgVar('user',		$this->procMsg->getMessageData('user'));
				$this->anchor->setDlgVar('oldPswd',	$this->procMsg->getMessageData('oldPswd'));
				$this->anchor->setDlgVar('pswd',		$this->procMsg->getMessageData('pswd'));
				$this->anchor->setDlgVar('pswd2',	$this->procMsg->getMessageData('pswd2'));
				break;
			case Dialog_ResetPswd:
				$this->anchor->setDlgVar('user',		$this->procMsg->getMessageData('user'));
				break;
			}

		$this->showDiagnosticsInfo();

		$this->procContext->putSessionData('responseDialog', $this->myMsgObject);
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