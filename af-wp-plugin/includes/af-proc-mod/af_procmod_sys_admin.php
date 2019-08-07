<?php
/*
 * af_procmod_sys_admin Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-NOV-02 JVS Bootstrap from af_procmod_ele_maint
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is the primary business logic processing module for system
 * administrative functions.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ProcModSysAdmin';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ProcModSysAdmin extends C_AirProcModBase {

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
			case AIR_Action_Login:
				$this->procLogin();
				break;
			case AIR_Action_Logout:
				$this->procLogout();
				break;
			case AIR_Action_Create:
				$this->procNewUser();
				break;
			case AIR_Action_Modify:
				$this->procChangePswd();
				break;
			case AIR_Action_Reset:
				$this->procResetPswd();
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
	 * procNewUser
	 *
	 *******/
	function procNewUser()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors		= false;
		$userName 	= $this->procMsg->getMessageData('user');
		$userPswd 	= $this->procMsg->getMessageData('pswd');
		$userPswd2 	= $this->procMsg->getMessageData('pswd2');
		$regCode 	= $this->procMsg->getMessageData('regCode');

		if (empty($userName))
			{
			$errors		= true;
			$diagnostic = 'An email address is required as the user ID.';
			$this->resultMsg->attachDiagnosticTextItem('User ID', $diagnostic, AIR_DiagMsg_Error);
			}

		if ((empty($userPswd))
		 || (empty($userPswd2)))
			{
			$errors		= true;
			$diagnostic = 'Both passphrase fields must have the passphrase entered.';
			$this->resultMsg->attachDiagnosticTextItem('Pass-phrase', $diagnostic, AIR_DiagMsg_Error);
			}
		else
		if ($userPswd != $userPswd2)
			{
			$errors		= true;
			$diagnostic = 'Both passphrase fields must match.';
			$this->resultMsg->attachDiagnosticTextItem('Pass-phrase', $diagnostic, AIR_DiagMsg_Error);
			}

		if (empty($regCode))
			{
			$errors		= true;
			$diagnostic = 'The text value shown in the image must be re-typed into the security code field.';
			$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$securityCode = $this->procContext->getSessionData('dlgSecurityCode');
//			$msgDiag	= 'Security Code     = ['.$securityCode.']  ';
//			$msgDiag	.= 'Panel Security code = ['.$regCode.']  ';
//			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			if ($securityCode != strtoupper($regCode))
				{
				$errors		= true;
				$diagnostic = 'Security code must match the value shown in the image.';
				$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		if (! $errors)
			{
			$successful = $this->anchor->user->register($userName, $userPswd);
			$diagnostic	= $this->anchor->user->getLastResultText();
			if ($successful)
				{
				$this->resultMsg->attachDiagnosticTextItem('&#160;', $diagnostic, AIR_DiagMsg_Success);
				$this->resultMsg->putMessageData('user',				$userName);
				$encodeObject	= Dialog_Login;
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			else
				{
				$errors		= true;
				$this->resultMsg->attachDiagnosticTextItem('User ID', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		if ($errors)
			{
			$this->resultMsg->putMessageData('user',				$userName);
			$this->resultMsg->putMessageData('pswd',				$userPswd);
			$this->resultMsg->putMessageData('pswd2',				$userPswd2);
			$encodeObject	= Dialog_NewUser;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}


	/***************************************************************************
	 * procChangePswd
	 *
	 *******/
	function procChangePswd()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors		= false;
		$loggedIn	= $this->anchor->user->isLoggedIn();
		$userPswd 	= $this->procMsg->getMessageData('pswd');
		$userPswd2 	= $this->procMsg->getMessageData('pswd2');
		$oldPswd 	= $this->procMsg->getMessageData('oldPswd');
		$regCode 	= $this->procMsg->getMessageData('regCode');

		if (! $loggedIn)
			{
			$userName 	= $this->procMsg->getMessageData('user');
			if (empty($userName))
				{
				$errors		= true;
				$diagnostic = 'An email address is required as the user ID.';
				$this->resultMsg->attachDiagnosticTextItem('User ID', $diagnostic, AIR_DiagMsg_Error);
				}
			}
		else
			{
			$userName 	= $this->anchor->user->userLoginName;
			}

		if (empty($oldPswd))
			{
			$errors		= true;
			$diagnostic = 'The old (current) passphrase must be specified.';
			$this->resultMsg->attachDiagnosticTextItem('Old passphrase', $diagnostic, AIR_DiagMsg_Error);
			}

		if ((empty($userPswd))
		 || (empty($userPswd2)))
			{
			$errors		= true;
			$diagnostic = 'Both passphrase fields must have the passphrase entered.';
			$this->resultMsg->attachDiagnosticTextItem('New passphrase', $diagnostic, AIR_DiagMsg_Error);
			}
		else
		if ($userPswd != $userPswd2)
			{
			$errors		= true;
			$diagnostic = 'Both passphrase fields must match.';
			$this->resultMsg->attachDiagnosticTextItem('New passphrase', $diagnostic, AIR_DiagMsg_Error);
			}
		else
		if ($userPswd == $oldPswd)
			{
			$errors		= true;
			$diagnostic = 'New passphrase must be diferent than old passphrase.';
			$this->resultMsg->attachDiagnosticTextItem('New passphrase', $diagnostic, AIR_DiagMsg_Error);
			}

		if (empty($regCode))
			{
			$errors		= true;
			$diagnostic = 'The text value shown in the image must be re-typed into the security code field.';
			$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$securityCode = $this->procContext->getSessionData('dlgSecurityCode');
//			$msgDiag	= 'Security Code     = ['.$securityCode.']  ';
//			$msgDiag	.= 'Panel Security code = ['.$regCode.']  ';
//			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			if ($securityCode != strtoupper($regCode))
				{
				$errors		= true;
				$diagnostic = 'Security code must match the value shown in the image.';
				$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		if (! $errors)
			{
			$successful = $this->anchor->user->changePassphrase($userName, $oldPswd, $userPswd);
			$diagnostic	= $this->anchor->user->getLastResultText();
			if ($successful)
				{
				$this->resultMsg->attachDiagnosticTextItem('&#160;', $diagnostic, AIR_DiagMsg_Success);
				$this->resultMsg->putMessageData('user',				$userName);
				if ($this->anchor->user->isLoggedIn())
					{
					$encodeObject	= Dialog_MenuSelect;
					}
				else
					{
					$encodeObject	= Dialog_Login;
					}
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			else
				{
				$errors		= true;
				$this->resultMsg->attachDiagnosticTextItem('New passphrase', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		if ($errors)
			{
			$this->resultMsg->putMessageData('user',				$userName);
			$encodeObject	= Dialog_ChangePswd;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procResetPswd
	 *
	 * This needs to guard against malicious attempts to reset passwords that
	 * belong to other people. The old passwords should not be automatically
	 * inactivated. The strategy is to e-mail a 'superkey' that will allow a
	 * one-time logon and password change. However, until used, the old
	 * password should also continue to be valid. If the old password is used
	 * before the 'superkey' is used, the 'superkey' should be deactivated.
	 *
	 *										I M P O R T A N T
	 *
	 * Currently, activation of the remainder of this logic is dependent on
	 * verification of the email feature. If the email doesn't work, we can't
	 * change the password because then nobody would know what it is!
	 *******/
	function procResetPswd()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$errors		= false;
		$userName 	= $this->procMsg->getMessageData('user');
		$userPswd 	= $this->procMsg->getMessageData('pswd');
		$userPswd2 	= $this->procMsg->getMessageData('pswd2');
		$regCode 	= $this->procMsg->getMessageData('regCode');

		if (empty($userName))
			{
			$errors		= true;
			$diagnostic = 'An email address is required as the user ID.';
			$this->resultMsg->attachDiagnosticTextItem('User ID', $diagnostic, AIR_DiagMsg_Error);
			}

		if (empty($regCode))
			{
			$errors		= true;
			$diagnostic = 'The text value shown in the image must be re-typed into the security code field.';
			$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$securityCode = $this->procContext->getSessionData('dlgSecurityCode');
//			$msgDiag	= 'Security Code     = ['.$securityCode.']  ';
//			$msgDiag	.= 'Panel Security code = ['.$regCode.']  ';
//			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			if ($securityCode != strtoupper($regCode))
				{
				$errors		= true;
				$diagnostic = 'Security code must match the value shown in the image.';
				$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		if (! $errors)
			{
			$mailMsg = new C_AirMailMsg($this->anchor);
			$mailMsg->initialize();
			$step 		= 0;
			$finished	= false;
			while ((! $finished) && (! $errors))
				{
				switch ($step)
					{
					case 0:
						$success = $mailMsg->setAddressee($userName);
						break;
					case 1:
						$success = $mailMsg->setSubject('Pass phrase reset notification');
						break;
					case 2:
						$text = 'We changed it. Try to guess what it is now!';
						$success = $mailMsg->setContent($text);
						break;
					default:
						$finished = true;
						break;
					}
				if (! $success)
					{
					$errors		= true;
					$diagnostic = $mailMsg->getLastResultText();
					$this->resultMsg->attachDiagnosticTextItem('&#160;', $diagnostic, AIR_DiagMsg_Error);
					}
				$step++;
				}
			}

		if (! $errors)
			{
			$successful = $mailMsg->sendMessage();
			$diagnostic	= $mailMsg->getLastResultText();
			if ($successful)
				{
				$this->resultMsg->attachDiagnosticTextItem('&#160;', $diagnostic, AIR_DiagMsg_Success);
				$this->resultMsg->putMessageData('user',				$userName);
				$encodeObject	= Dialog_Login;
				$encodeAction	= AIR_Action_Encode;
				$encodeVers		= '1.0';
				}
			else
				{
				$errors		= true;
				$this->resultMsg->attachDiagnosticTextItem('User ID', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		$errors		= true;
		$diagnostic = 'This feature is not fully activated.';
		$this->resultMsg->attachDiagnosticTextItem('&#160;', $diagnostic, AIR_DiagMsg_Info);

		if ($errors)
			{
			$this->resultMsg->putMessageData('user',				$userName);
			$encodeObject	= Dialog_ResetPswd;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procLogin
	 *
	 *******/
	function procLogin()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$user 			= $this->procMsg->getMessageData('user');
		$pswd 			= $this->procMsg->getMessageData('pswd');
		$regCode 		= $this->procMsg->getMessageData('regCode');
		$errors 			= false;
		$success			= false;

		if (empty($regCode))
			{
			$errors		= true;
			$diagnostic = 'The text value shown in the image must be re-typed into the security code field.';
			$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
			}
		else
			{
			$securityCode = $this->procContext->getSessionData('dlgSecurityCode');
//			$msgDiag	= 'Security Code     = ['.$securityCode.']  ';
//			$msgDiag	.= 'Panel Security code = ['.$regCode.']  ';
//			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			if ($securityCode != strtoupper($regCode))
				{
				$errors		= true;
				$diagnostic = 'Security code must match the value shown in the image.';
				$this->resultMsg->attachDiagnosticTextItem('Security Code', $diagnostic, AIR_DiagMsg_Error);
				}
			}

		if (! $errors)
			{
			$success	= $this->anchor->user->login($user, $pswd);
			$this->resultMsg->attachDiagnosticTextItem('', $this->anchor->user->getLastResultText());
			}

		if ($success)
			{
			$encodeObject	= Dialog_MenuSelect;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}
		else
			{
//			$this->resultMsg->putMessageData('user',				$this->myTxnSpec['user']);
//			$this->resultMsg->putMessageData('pswd',				$this->myTxnSpec['pswd']);
			$this->resultMsg->putMessageData('user',				$user);
//			$this->resultMsg->putMessageData('pswd',				$pswd);

			$encodeObject	= Dialog_Login;
			$encodeAction	= AIR_Action_Encode;
			$encodeVers		= '1.0';
			}

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}

	/***************************************************************************
	 * procLogout
	 *
	 *******/
	function procLogout()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->anchor->user->logout();
		$this->resultMsg->attachDiagnosticTextItem('', $this->anchor->user->getLastResultText());

		$encodeObject	= Dialog_Login;
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		$this->resultMsg->putMessageControlData('DestObject', $encodeObject);
		$this->resultMsg->putMessageControlData('DestAction', $encodeAction);
		$this->resultMsg->putMessageControlData('DestVers', $encodeVers);
		}
	} // end of class

/*
 * This code is executed once per 'include' as the module is scanned
 * and all code not inside newly defined "function blocks" is executed.
 */
	if ($this->anchor->debugCoreFcns())
		{
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br/></debug> ';
		}
 ?>