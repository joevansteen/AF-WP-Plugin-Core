<?php
/*
 * af_reqfilter_dlgAirAudit script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-02 JVS Bootstrap from af_dialogdecode
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ReqFilterAirAudit';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ReqFilterAirAudit extends C_AirProcModBase {

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
				case Dialog_AirAudit:
					$this->procAirAudit();
					$this->publishMenuResultMsgInfo();
					break;
				default:
					$this->anchor->abort('Unrecognized menu decode object ['.$this->myMsgObject.']');
					throw new Exception('Unrecognized message object');
				}

			$this->postResultMsg();
		}

	/***************************************************************************
	 * procAirAudit
	 *
	 * This code was copied from the Menu01 filter. At the time this module was
	 created there was no input filter for the audit module.
	 *******/
	function procAirAudit()
		{
		$this->anchor->abort('No input filter coded for '.$this->myMsgObject.']');
		throw new Exception('Unrecognized message object');

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$txnAction		= $this->procMsg->getMessageData('request');
		$encodeAction	= AIR_Action_Encode;
		$encodeVers		= '1.0';

		switch ($txnAction)
			{
			case AIR_Action_Submit:
				/*
				 * Establish the controlling menu array for selection decoding
				 */
				switch ($this->myMsgObject)
					{
					case Dialog_AirAdmin:
						$menuArray = & $this->anchor->menuSet['AirAdmin'];
						break;
					case Dialog_SysAdmin:
						$menuArray = & $this->anchor->menuSet['SysAdmin'];
						break;
					case Dialog_DbCvtMenu:
						$menuArray = & $this->anchor->menuSet['DbConvert'];
						break;
					case Dialog_EleNdxMenu:
						$menuArray = & $this->anchor->menuSet['EleIndex'];
						break;
					case Dialog_AirMenu:
						$menuArray = & $this->anchor->menuSet['AirMaint'];
						break;
					case Dialog_DirViewMenu:
						$menuArray = & $this->anchor->menuSet['DirView'];
						break;
					default:
						throw new Exception('Unrecognized message object');
						$menuArray = null;
						break;
					}
				$selection = $this->anchor->getDialogAction($this->procMsg->getMessageData('dlgAction'));

//				$msgDiag		= 'selection object ['.$selection.']';
//				$this->anchor->putTraceData(__LINE__, __FILE__, $msgDiag);

				if (empty($selection))
					{
					$this->resultMsg->attachDiagnosticTextItem('Action Choice', 'A specific selection must be made.');
					$encodeObject	= $this->myMsgObject;
					break;
					}
				$dlgEleType = $this->procMsg->getMessageData('dlgEleType');

				if (! is_null($dlgEleType))
					{
	   			if (($this->procContext->getSessionData('txnContextEleType') != $dlgEleType)
	   			 && (! empty($dlgEleType))
	   			 && ($dlgEleType != AIR_Null_Identifier))
	   				{
  						$this->anchor->putTraceData(__LINE__, __FILE__, 'Resetting context ...');
		   			$this->procContext->removeElementNode('txnContextEleTypeClass');
		   			$this->procContext->putSessionData('txnContextEleType', $dlgEleType);
		   			$eleTypeDoc = & $this->anchor->getRefElement($dlgEleType);
		   			$eleTypeClass = $eleTypeDoc->getElementData('Class');
		   			$this->procContext->putSessionData('txnContextEleTypeClass', $eleTypeClass);
		   			}
					}
		   	$this->procContext->putSessionData('eleMaintAction', $selection);

				$this->myTxnSpec['TxnOper']		=	$selection;
				$this->myTxnSpec['TxnStepOper']	=	'Init';
				$this->myTxnSpec['Author']			= $this->procContext->getLoggedUserId();

				/*
				 * Set the processing target based on the menu array specification
				 */
				if (array_key_exists($selection, $menuArray))
					{
					$menuItem		= $menuArray[$selection];
					if (! empty($menuItem['ItemTarget']))
						{
						$encodeObject	= $menuItem['ItemTarget'];
						}
					if (! empty($menuItem['ItemAction']))
						{
						$encodeAction	= $menuItem['ItemAction'];
						$encodeVers		= '1.0';
						}
					}
				else
					{
					$this->anchor->abort('Unrecognized menu action: ['.selection.']');
					}

				/*
				 * Get additional specialized capture variables and refine the
				 * target strategy, based on the menu selection.
				 *
				 * NOTE. The encode object and action should already have default values
				 * at this point from the menu table. The only need to re-specify them is to
				 * refine the targeting based on an object type selection that was made on
				 * the menu, as is done with the 'ByType' menu selections.
				 */
			   switch ($selection)
			   	{
			   	case AIR_Action_Show:
			   	case AIR_Action_ShowItem:
			   	case AIR_Action_ShowRaw:
			   	case AIR_Action_Modify:
			   	case AIR_Action_DeleteItem:
			   	case AIR_Action_AuditItem:
			   	case AIR_Action_AuditAll:
			   	case AIR_Action_AuditType:
			   	case AIR_Action_PurgeType:
			   		/*
			   		 * The following test is a cludge. The above action types are an
			   		 * orthogonal list to the menu types. Not all menus carry an "EleType"
			   		 * selection critieria. Only those that do need the following code.
			   		 */
						if (! is_null($dlgEleType))
							{
							$this->myTxnSpec['EleType'] =	$dlgEleType;
							$itemCount = $this->anchor->myDbLayer->getTypeCount_AirEleIndex($dlgEleType);
							if (! $itemCount)
								{
									$name = $this->anchor->getRefName($dlgEleType);
								$this->resultMsg->attachDiagnosticTextItem('Action Choice', 'No '.$name.' items found in the repository.');
								$encodeObject	= Dialog_MenuSelect;
								}
							else
							if ($selection == AIR_Action_PurgeType)
								{
						   	if ($this->anchor->isCoreElementType($dlgEleType))
						   		{
									$this->resultMsg->attachDiagnosticTextItem('Menu Selection', 'Purge is not allowed on core element types');
									$this->resultMsg->attachDiagnosticTextItem('Menu Selection', 'Constraint is temporarily overridden');
//									$encodeObject	= Dialog_MenuSelect;
						   		}
								}
							}
			   		break;
			   	case AIR_Action_Register:
						$this->myTxnSpec['EleItem'] =	$this->procMsg->getMessageData('EleItem');
			   		break;
			   	case AIR_Action_Add:
			   	case AIR_Action_Load:
						if ((empty($dlgEleType))
						 || ($dlgEleType == AIR_Null_Identifier)
						 || ($dlgEleType == AIR_Any_Identifier)
						 || ($dlgEleType == AIR_All_Identifier))
							{
							$this->resultMsg->attachDiagnosticTextItem('Constraint Selection', 'A specific selection must be made for the action requested.');
							$encodeObject	= $this->myMsgObject;
							break;
							}
						$this->myTxnSpec['EleType'] =	$dlgEleType;
		   			$this->procContext->removeElementNode('eleMaintSubject');
		   			switch ($dlgEleType)
			   			{
		   				case AIR_EleType_EleType:
					   		$encodeObject	= Dialog_EleTypeMaint;
		   					break;
		   				case AIR_EleType_PropType:
					   		$encodeObject	= Dialog_PropTypeMaint;
		   					break;
		   				case AIR_EleType_AssocType:
					   		$encodeObject	= Dialog_AssocTypeMaint;
		   					break;
		   				case AIR_EleType_CoordType:
					   		$encodeObject	= Dialog_CoordTypeMaint;
		   					break;
		   				case AIR_EleType_EleClass:
					   		$encodeObject	= Dialog_EleClassMaint;
		   					break;
		   				case AIR_EleType_RelClass:
					   		$encodeObject	= Dialog_RelClassMaint;
		   					break;
		   				case AIR_EleType_PropRule:
					   		$encodeObject	= Dialog_PropRuleMaint;
		   					break;
		   				case AIR_EleType_AssocRule:
					   		$encodeObject	= Dialog_AssocRuleMaint;
		   					break;
		   				case AIR_EleType_CoordRule:
					   		$encodeObject	= Dialog_CoordRuleMaint;
		   					break;
		   				case AIR_EleType_Property:
					   		$encodeObject	= Dialog_PropMaint;
		   					break;
		   				case AIR_EleType_Association:
					   		$encodeObject	= Dialog_AssocMaint;
		   					break;
	   					case AIR_EleType_CoordModel:
					   		$encodeObject	= Dialog_CoordMaint;
		   					break;
		   				default:
					   		$encodeObject	= Dialog_EleMaint;
		   					break;
			   			}
			   		break;
					case AIR_Action_DataConvert:
			   	case AIR_Action_SetOptions:
			   	default:
			   		/*
			   		 * Options are identified primarily for documentation purposes
			   		 * to note that NOTHING special is being done for them.
			   		 */
			   		break;
			   	}
				break;
			case AIR_Action_Redirect:
				$encodeObject	= $this->myMsgObject;
				break;
			default:
				if (($this->anchor != NULL) && ($this->anchor->trace()))
				 	{
					$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " panel unrecognized action: $txnAction");
					}
				$encodeObject	= $this->myMsgObject;
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