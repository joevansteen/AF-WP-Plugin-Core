<?php
/*
 * af_viewMgr_menuDlg01 script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from af_dialogencode
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrMenuDlg01';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ViewMgrMenuDlg01 extends C_ViewMgrBase {
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

		if ($this->myMsgObject == Dialog_MenuSelect)
			{
			$this->createMenuTxnSpecArray();
			$this->procMenuSelect();
			}

		switch ($this->myMsgObject)
			{
			case Dialog_Home:
			case Dialog_Services:
			case Dialog_Content:
			case Dialog_Members:
			case Dialog_Contact:
			case Dialog_SiteMap:
				$this->procPassThrough();
				break;
			case Dialog_AirMenu:
			case Dialog_AirAdmin:
			case Dialog_SysAdmin:
			case Dialog_DirViewMenu:
				$this->createMenuTxnSpecArray();
				$this->procMenu01();
				break;
			case Dialog_EleNdxMenu:
			case Dialog_DbCvtMenu:
				$this->createMenuTxnSpecArray();
				$this->procMenu01();
				break;
			default:
				$this->anchor->abort('Unrecognized menu decode object ['.$this->myMsgObject.']');
				throw new Exception('Unrecognized message object');
			}

		$this->procContext->putSessionData('currentMenuDialog', $this->myMsgObject);

//		$this->anchor->putTraceData(__LINE__, __FILE__, 'dlgContent is '.$this->dlgContent);
		$this->anchor->setDlgVarByRef('air_ItemHeader', $this->dlgHeader);
		$this->anchor->setDlgVarByRef('air_Dialog', 		$this->dlgContent);
		$this->anchor->setDlgVarByRef('air_ItemFooter', $this->dlgFooter);
		}

	/***************************************************************************
	 * procMenuSelect
	 *
	 * This function redirects itself to procMenu01 after resetting the object
	 * from the generic "menu" to the most recently displayed Menu01 menu set.
	 *******/
	function procMenuSelect()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$currentMenuDialog = $this->procContext->getSessionData('currentMenuDialog');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'currentMenuDialog is '.$currentMenuDialog);
		if (empty($currentMenuDialog))
			{
			$this->procContext->putSessionData('currentMenuSelect', 'mnuItemHome');
			$currentMenuDialog = Dialog_Home;
			}
		$this->myMsgObject = $currentMenuDialog;

		return;
		}

	/***************************************************************************
	 * procMenu01
	 *******/
	function procMenu01()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		/*
		 * The main menus are currently considered to be the starting points of
		 * interaction dialogs. Each return to a main menu is the start of
		 * a new dialog. Intermediate screens are considered part of a single
		 * dialog session.
		 *
		 * Generate a new dialog session state
		 */
		$this->procContext->putAuthId($this->anchor->create_UUID());
		$this->procContext->putDialogId($this->anchor->create_UUID());

		/*
		 * Kludge for clearing values from session data. We should have a
		 * segment of named space that we can simply clear as a block.
		 */
		$purgeArray = array();
		$purgeArray[] = 'ConvertItem';
		$purgeArray[] = 'PurgeItem';
		$purgeArray[] = 'EleIdent';

		foreach ($purgeArray as $purgeElement)
			{
			$purgeCount = $this->procContext->getDataCollectionItemCount($purgeElement);
			if ($purgeCount)
				{
				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$this->anchor->putTraceData(__LINE__, __FILE__, "Purging [$purgeCount] $purgeElement items from session data");
					}
				$this->procContext->purgeDataCollectionItems($purgeElement);
				if (($this->anchor != NULL) && ($this->anchor->trace()))
					{
					$purgeCount = $this->procContext->getDataCollectionItemCount($purgeElement);
					$this->anchor->putTraceData(__LINE__, __FILE__, "New $purgeElement count = [$purgeCount]");
					}
				}
			}

		$this->procContext->removeElementNode('eleMaintObject');
		/*
		 * Don't remove the 'type' - it's what helps us save typing when selecting
		 * the next item of the same type, or when running the list, it helps
		 * remind us of where we are.
		 */
//		$this->procContext->removeElementNode('txnContextEleType');
		/*
		 * Don't remove the 'action' - it's what helps us save typing when
		 * performing the same action a number of times in a row on different
		 * items, potentially of different types. It also helps remind us of
		 * where we are.
		 */
//		$this->procContext->removeElementNode('eleMaintAction');

		/*
		 * Generate the menu material
		 */
		if (($this->myMsgObject == Dialog_AirMenu)
		 && (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		  || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		  || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify)))
			{
			$showAsArray = true;
			$showAsReadOnly = false;
			$showAsModifiable = true;
			}
		else
			{
			$showAsArray = false;
			$showAsReadOnly = true;
			$showAsModifiable = false;
			}

		/*
		 * Create context array and post context display information to SMARTY
		 */
		switch ($this->myMsgObject)
			{
			case Dialog_AirMenu:
				$dlgContext	= $this->procContext->getContextId();
				$this->setShowSelectionInfo('Context', true, AIR_EleType_Context, $dlgContext, $showAsArray, $showAsModifiable);
//				$dlgObject	= $this->procContext->getSessionData('dlgObject');
			case Dialog_AirAdmin:
				$this->myTxnSpec['EleType']	= $this->procContext->getSessionData('txnContextEleType');
		 		$this->setShowSelectionInfo('EleType', true, AIR_EleType_EleType, $this->myTxnSpec['EleType'], true, false);
		 		break;
			case Dialog_EleNdxMenu:
				break;
			case Dialog_DbCvtMenu:
				$dlgContext	= $this->procContext->getSessionData('cvtContextManifest');
		 		$this->setShowSelectionInfo('EleType', true, AIR_EleType_EleManifest, $dlgContext, true, false);
		 		break;
			}

		$this->anchor->setDlgVar('air_SelectMultiple',		false);
		$optionList = array();
		$optionItem = array();

		$oldMenuKey = $this->procContext->getSessionData('eleMaintAction');

		$menuDialog = $this->myMsgObject;
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
				$this->anchor->abort('Unrecognized menu encode object ['.$this->myMsgObject.']');
				$menuArray = null;
				break;
			}

		foreach ($menuArray as $menuItem)
			{
			$menuKey				= $menuItem['ActionCode'];
			$menuDescription	= $menuItem['Description'];
			if ($menuKey == $oldMenuKey)
				{
				$optionItem['on'] 		= true;
				}
			else
				{
				$optionItem['on'] 		= false;
				}
			$optionItem['content']		= $menuDescription;
			$optionList[$menuKey]		= $optionItem;
			}

		$this->anchor->setDlgVarByRef('air_listItemArray',	$optionList);
		$this->showDiagnosticsInfo();
		$this->procContext->putSessionData('responseDialog', $menuDialog);
		}

	/***************************************************************************
	 * procPassThrough
	 *******/
	function procPassThrough()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

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