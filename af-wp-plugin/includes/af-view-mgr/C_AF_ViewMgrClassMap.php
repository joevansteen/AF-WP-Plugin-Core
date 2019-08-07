<?php
/*
 * C_AF_ViewMgrClassMap script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-17 JVS Bootstrap from C_AF_ViewMgrViewProcessorMap
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

require(AF_AUTOMATED_SCRIPTS.'/af_viewMgr_base.php');

// Insure a correct execution context ...
$myProcClass = 'C_AF_ViewMgrClassMap';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AF_ViewMgrClassMap extends C_ViewMgrBase {

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
			case Dialog_ClassMap:
				$this->createMaintTxnSpecArray();
				switch ($this->myTxnSpec['TxnOper'])
					{
					case AIR_Action_Review:
						$this->procShowClassMap();
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
	 * procShowClassMap
	 *******/
	function procShowClassMap()
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

		$classHierarchy = $this->anchor->Repository->getClassHierarchy(AIR_AssocType_SupertypeOf);
		$roots = $classHierarchy->getRoots();

		$action = AIR_Action_ShowItem;
		$class = 'Taxonomy';
		$selection = 'ViewOnly';

		$dlgItemContent = $this->procBuildHierarchyMap($classHierarchy, $roots, $action, $class, $selection);

		$this->setHierarchicalDataDisplay('', 'dlg', $dlgItemContent, 'ViewOnly', 'Taxonomy');


		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'Repository Element Class Hierarchy');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
		}

	/***************************************************************************
	 * procBuildHierarchyMap
	 *
	 * $hierarchy is a C_AF_AirElementHierarchy
	 * $items is a C_AF_AirElementCollection of the root nodes in the hierarchy
	 *******/
	function procBuildHierarchyMap($hierarchy, $items, $action, $class, $selection)
		{
			$result = array();

			$item = $items->getFirst();
			while ($item != NULL)
			{
				$element = $hierarchy->getItem($item->getGuid());
				$result[] = $this->procBuildHierarchyItemMap($element, $action, $class, $selection);
				$item = $items->getNext();
			}

			return $result;
		}

	/***************************************************************************
	 * procBuildHierarchyItemMap
	 *
	 * $item is a C_AF_AirElementHierarchyItem
	 * $selection controls the selctability of individual items
	 *******/
	function procBuildHierarchyItemMap(& $item, $action, $class, $selection)
		{
			$result = array();

			$itemKey = $item->ItemKey;

			$result['key'] = $itemKey;
			$result['text'] = $item->getName();
			$result['action'] = $action;
			$result['class'] = $class;
			$result['selection'] = $selection;
			$children = $item->hasChildren();
			if ($children)
			{
				$childHierarchy = array();
				$child = $item->firstChild();
				while ($child != NULL)
				{
					$childHierarchy[] = $this->procBuildHierarchyItemMap($child, $action, $class, $selection);
					$child = $item->nextChild();
				}
				$result['children'] = $childHierarchy;
			}

			return $result;
		}

	}

?>