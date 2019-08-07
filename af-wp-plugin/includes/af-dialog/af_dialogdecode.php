<?php
/*
 * af_dialogdecode script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.1 2005-JUL-26 JVS Original code.
 *      2005-AUG-05 JVS Shaping to be stage one of a multi-stage
 *								process stream.
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-31 JVS Name change from air-html_decode to af_dialogdecode
 *                      as part of extraction from TikiWiki framework.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is a first prototype version of a dynamically
 * loaded and invoked processing module for execution within
 * a PHP processing environment.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ProcModDialogDecode';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ProcModDialogDecode extends C_AirProcModBase {

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
		$this->initResultMsg();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			$msgDiag		= 'driver object ['.$this->myMsgObject.']';
			$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);
			}

		$validated = $this->validateContext();

			switch ($this->myMsgObject)
				{
				case Dialog_EleMaint:
				case Dialog_ModelTaxMaint:
				case Dialog_ModelRuleMaint:
				case Dialog_ModelMaint:
					$this->procEleMaint();
					$result = $this->publishTxnDataArrayToResultMsg();
					if ($result < 0)
						{
						trigger_error("Critical data error in __FUNCTION__ [$this->myMsgObject]" , E_USER_NOTICE);
						}
					break;
				case Dialog_EleList:
					$this->procEleList();
					$this->publishMenuResultMsgInfo();
					break;
				case Dialog_EleListContext:
					$this->procEleListContext();
					$this->publishMenuResultMsgInfo();
					break;
				case Dialog_ProcOptions:
					$this->procProcOptions();
					$this->publishMenuResultMsgInfo();
					break;
				case Dialog_DirView:
					echo '... '.__FILE__.'['.__LINE__.'] Missing DirView function execution goes here';
					break;
				case ProcMod_Login:
					$this->procLogin();
					break;
				case ProcMod_Logout:
				case ProcMod_NewUser:
				case ProcMod_ChangePswd:
				case ProcMod_ResetPswd:
					$this->procDefault();
					break;
				default:
//					echo '... '.__FILE__.'['.__LINE__.'] Default processing for '.$this->myMsgObject.' decoding.';
					$this->procDefault();
					break;
				}

			$this->postResultMsg();

		}

	/***************************************************************************
	 * procEleMaint
	 *******/
	function procEleMaint()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$txnObject		= $this->procMsg->getMessageData('dlgPanelType');
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
				$this->myTxnSpec['TxnStepOper']	=	$txnAction;
				$encodeObject	= Dialog_EleMaint;
				break;
			case AIR_Action_Save:
			case AIR_Action_Refresh:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " checkpoint taken");
					}
				/*
				 *
				 */
			   switch ($this->myContextAction)
			   	{
			   	case AIR_Action_Add:
						$this->myTxnSpec['TxnStepOper']	=	$txnAction;
						$encodeObject	= $txnObject;
						$this->setEleMaintContent();
			   		break;

			   	case AIR_Action_Modify:
						$this->myTxnSpec['TxnStepOper']	=	$txnAction;
						$maintElement = $this->procContext->getSessionData('eleMaintObject');
			   		$encodeObject = $this->anchor->getProcModFromEleType($maintElement);
			   		if (empty($encodeObject))
			   			{
							trigger_error(__FUNCTION__ . " cannot dispatch $txnObject" , E_USER_NOTICE);
			   			}
						$encodeAction	= $this->myContextAction;
						$this->setEleMaintContent();
			   		break;
			   	case AIR_Action_DeleteItem:
			   	case AIR_Action_Load:
			   	default:
						$encodeObject	= Dialog_MenuSelect;
			   		break;
			   	}
				break;
			case AIR_Action_Submit:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " panel processing initiated");
					}

			   switch ($this->myContextAction)
			   	{
			   	case AIR_Action_Add:
			   	case AIR_Action_Modify:
			   	case AIR_Action_DeleteItem:
			   	case AIR_Action_PurgeType:
						$this->myTxnSpec['TxnStepOper']	=	$txnAction;
						$maintElement = $this->procContext->getSessionData('eleMaintObject');
			   		$encodeObject = $this->anchor->getProcModFromEleType($maintElement);
			   		if (empty($encodeObject))
			   			{
							trigger_error(__FUNCTION__ . " cannot dispatch $txnObject" , E_USER_NOTICE);
			   			}
						$encodeAction	= $this->myContextAction;

						$this->setEleMaintContent();
			   		break;
			   	case AIR_Action_Load:
			   		$encodeObject	= Dialog_MenuSelect;
			   		break;
			   	case AIR_Action_AuditItem:
			   	case AIR_Action_AuditType:
			   	case AIR_Action_AuditAll:
						$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
						if ($collectionSize > 0)
							{
							$this->myTxnSpec['TxnStepOper']	=	AIR_Action_Submit;
				   		$encodeObject  = ProcMod_EleAudit;
							$encodeAction	= $this->myContextAction;
							$this->setEleMaintContent();
							}
						else
							{
							$encodeObject	= Dialog_MenuSelect;
							}
			   		break;
			   	case AIR_Action_ShowItem:
			   	case AIR_Action_ShowRaw:
			   	default:
						$collectionSize = $this->procContext->getDataCollectionItemCount('EleIdent');
						if ($collectionSize > 0)
							{
							$this->myTxnSpec['TxnStepOper']	=	AIR_Action_Submit;
							$maintElement = $this->procContext->getSessionData('eleMaintObject');
				   		$encodeObject = $this->anchor->getProcModFromEleType($maintElement);
							$encodeAction	= $this->myContextAction;

							$this->setEleMaintContent();
							}
						else
							{
							$encodeObject	= Dialog_MenuSelect;
							}
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
	 * translateBaseMsgSpec
	 *
	 * Passes standard content elements from trigger message to result message.
	 *******/
	function translateBaseMsgSpec()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->myTxnSpec['TxnOper'] =				$this->myContextAction;
		$this->myTxnSpec['Author'] =				$this->procContext->getLoggedUserId();
		$this->myTxnSpec['EleClass'] =			$this->procMsg->getMessageData('dlgEleClass');
		$this->myTxnSpec['EleType'] =				$this->procMsg->getMessageData('dlgEleType');
		$this->myTxnSpec['EleName'] =				$this->procMsg->getMessageData('dlgEleName');
		$this->myTxnSpec['EleContent'] =			$this->procMsg->getMessageData('dlgEleContent');
		$this->myTxnSpec['EleChgComments'] =	$this->procMsg->getMessageData('dlgEleChgComments');
		}

	/***************************************************************************
	 * setEleMaintContent
	 *
	 * Passes standard content elements from trigger message to result message.
	 *
	 * Patterns and data for actual models match the patterns and data in the rules. The
	 * primary difference is that the model 'elements' in the rules are of
	 * type 'type' and define relationships between classes. The elements in the
	 * actual models define instances of those classes.
	 *******/
	function setEleMaintContent()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->translateBaseMsgSpec();
		/*
		 * New items for content of element type or element class definition
		 *
		 *		NOTE: Rules set the pattern for actual model relationships.
		 *
		 * There are 3 ModelRule subtypes:
		 *		Attribute Rules
		 *			Object attribute/property rules - defines what relationship types
		 *			may be used to associate elemental (atomic) elements with
		 * 		composite element. (Does this still make sense? Atomic elements
		 *			have more flexibility if they can have multiple types. If that is
		 *			the design scheme, then this 'rule' set become an inter-object rule
		 *			and not a simple attribute/property, since it requires the definition
		 *			of a constraint between two element types. Or, as in the original
		 *			DModel and SysModel, the second 'type' might always be 'text.')
		 *		Binary Rules
		 *			Inter-obect association rules - defines what elements types may be
		 *			related to one another, and what relationship types may be used
		 *			to define the associations
		 *		Ternary Rules
		 *			Object flow association rules - defines 'triple' relationships between
		 *			three objects (composite elements). The single 'triple' relation
		 *			defined here is a specific packaging of three binary relations
		 *			joining these same objects as defined in the inter-object
		 *			association rules defined above.
		 *
		 * Attribute Rules has:
		 *		subject element type (ele type)
		 *		relationship element type (rel type)

		 *		diagnostic level (ok, warning, error, ...)
		 *		diagnostic element (pointer to diagnostic text)
		 *		NOTE: Diagnostic level should be a feature of the diagnostic. The
		 *				same diagnostic should NOT be used as both an error and warning.
		 *
		 * Binary Rules has: above plus
		 *		relationship object element type (ele type)
		 *
		 * Ternary Rules has: above plus
		 *		communication object element type (ele type)
		 *			- might be a list!
		 */

		/*
		 * Note: The following are the assignment of diagnostics to this rule. Only one diagnostic
		 * is assigned per rule. (If the diagnostic is actually a diagnostic collection, then
		 * it will translate to a set of diagnostics.) These are NOT diagnistics associated with
		 * any issues on this maintenance transaction.
		 */
//		$this->myTxnSpec['ModelRuleDiagLevel'] =	$this->procMsg->getMessageData('dlgRuleDiagLevel');
//		$this->myTxnSpec['ModelRuleDiagItem'] =	$this->procMsg->getMessageData('dlgRuleDiagItem');

		$result = $this->createTxnDataArrayFromProcMsg(false);
		if ($result < 0)
			{
			trigger_error("Critical data error in __FUNCTION__" , E_USER_NOTICE);
			}
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
			case AIR_Action_Submit:
				$dlgEleType	= $this->procContext->getSessionData('txnContextEleType');
			   switch ($this->myContextAction)
			   	{
			   	case AIR_Action_Modify:
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