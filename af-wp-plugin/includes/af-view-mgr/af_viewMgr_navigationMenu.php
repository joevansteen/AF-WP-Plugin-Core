<?php
/*
 * af_viewMgr_navigationMenu script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-16 JVS Bootstrap from af_viewMgr_dispatchRules
  * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrNavigationMenu';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


class C_ViewMgrNavigationMenu extends C_ViewMgrBase {

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

		$this->hasSelectOptionNone	= true;
		$this->hasSelectOptionAny	= false;
		$this->hasSelectOptionAll	= false;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, $this->myMsgObject);
			}

		switch ($this->myMsgObject)
			{
			case AF_NAVIGATION_MENU:
				$this->createMaintTxnSpecArray();
				switch ($this->myTxnSpec['TxnOper'])
					{
					case AIR_Action_Review:
						$this->procShowNavigationMenu();
						break;
					default:
						$this->anchor->abort('Unrecognized menu action ['.$this->myTxnSpec['TxnOper'].']');
						throw new Exception('Unrecognized message action');
					}
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
	 * procShowNavigationMenu
	 *******/
	function procShowNavigationMenu()
		{
		$dlgChoice		= null;
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		$showAsArray = false;
		$showAsReadOnly = true;
		$showAsModifiable = false;

		$this->showDiagnosticsInfo();

		$menuSet = $this->anchor->getMenuNavArray();
		$rootElement  = $menuSet->getRoot();

		$stackName = 'Menu Items:';
		$this->pushContextStack($stackName);

		$this->setTextDisplay('ItemKey', 'hdrItemKey', 'Item Key', true);
		$this->setTextDisplay('Role', 'hdrRole', 'Role Restriction', true);
		$this->setTextDisplay('Parent', 'hdrParent', 'Parent Item', true);
		$this->setTextDisplay('Children', 'hdrChildren', 'Child Count', true);
		$this->setTextDisplay('Type', 'hdrType', 'Item Type', true);
		$this->setTextDisplay('Label', 'hdrLabel', 'Label', true);
		$this->setTextDisplay('Description', 'hdrDescription', 'Description', true);
		$this->setContextItemBreak();

		$this->documentItem($rootElement);

		$this->popColumnContext($stackName); // is the 'label' for the row

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'High Level Navigation Menu');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
		}

	private function documentItem($menuElement)
	{
		$this->setTextDisplay('ItemKey', 'txtItemKey', $menuElement->ItemKey, true);
		$this->setTextDisplay('Role', 'txtRole', $menuElement->Role, true);
		$this->setTextDisplay('Parent', 'txtParent', $menuElement->Parent->ItemKey, true);
		$this->setTextDisplay('Children', 'txtChildren', $menuElement->childCount(), true);
		$this->setTextDisplay('Type', 'txtType', $menuElement->ItemType, true);
		$this->setTextDisplay('Label', 'txtLabel', $menuElement->ItemLabel, true);
		$this->setTextDisplay('Description', 'txtDescription', $menuElement->ItemDescription, true);
		$this->setContextItemBreak();

		if ($menuElement->hasChildren())
			{
			$nextElement = $menuElement->firstChild();
			while ($nextElement)
				{
				$this->documentItem($nextElement);

				$nextElement = $menuElement->nextChild();
				}
			}
	}

	}

?>