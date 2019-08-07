<?php
/*
 * af_reqfilter_dlgMaint script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-15 JVS Bootstrap from af_dialogdecode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ReqFilterDialogMaint';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


class C_ReqFilterDialogMaint extends C_AF_ReqFilterBase {

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
				default:
					echo '... '.__FILE__.'['.__LINE__.'] Missing DirView function execution goes here';
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
					$this->anchor->putLogicCheck(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " improper RESET found");
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
			   	case AIR_Action_ModifyRaw:
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
			   	case AIR_Action_ModifyRaw:
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

	}
 ?>