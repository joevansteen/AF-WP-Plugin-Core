<?php
/*
 * af_viewMgr_dispatchRules script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-APR-16 JVS Bootstrap from af_viewMgr_propertyReview
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
require(AF_AUTOMATED_SCRIPTS.'/af_viewMgr_base.php');
$myProcClass = 'C_ViewMgrDispatchRules';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


	/***************************************************************************
	 * sortMyDispatchItems()
	 *******/
function sortMyDispatchItems($item1, $item2)
	{
	$sortResult = 0;

	$sortResult = compareMyDispatchFields($item1['mode'], $item2['mode']);
	if ($sortResult == 0)
	{
		$sortResult = compareMyDispatchFields($item1['object'], $item2['object']);
	}
	if ($sortResult == 0)
	{
		$sortResult = compareMyDispatchFields($item1['action'], $item2['action']);
	}
	if ($sortResult == 0)
	{
		$sortResult = compareMyDispatchFields($item1['vers'], $item2['vers']);
	}

	return ($sortResult);
	}

	/***************************************************************************
	 * compareMyDispatchFields()
	 *******/
function compareMyDispatchFields($item1, $item2)
{
	$sortResult = 0;

	$value1 = strtolower($item1);
	$value2 = strtolower($item2);
	if ($value1 == $value2)
	{
		$sortResult = 0;
	}
	else
	if ($value1 == '*')
	{
		$sortResult = 1;
	}
	else
	if ($value2 == '*')
	{
		$sortResult = -1;
	}
	else
	if ($value1 < $value2)
	{
		$sortResult = -1;
	}
	else
	{
		$sortResult = 1;
	}

	return ($sortResult);
}

class C_ViewMgrDispatchRules extends C_ViewMgrBase {

	private $dispatchTable	= array();					// temporary dispatch table

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
			case AF_DISPATCH_RULES:
				$this->createMaintTxnSpecArray();
				switch ($this->myTxnSpec['TxnOper'])
					{
					case AIR_Action_Review:
						$this->procShowDispatchRules();
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
	 * procShowDispatchRules
	 *******/
	function procShowDispatchRules()
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

		$this->initializeTable();

		$this->pushContextStack('Dispatch Entries:');

		$this->setTextDisplay('Mode', 'hdrMode', 'Mode', true);
		$this->setTextDisplay('Object', 'hdrObject', 'Message Object', true);
		$this->setTextDisplay('Action', 'hdrAction', 'Message Action', true);
		$this->setTextDisplay('Version', 'hdrVers', 'Version', true);
		$this->setTextDisplay('Processor', 'hdrModule', 'Processor Module', true);
		$this->setContextItemBreak();

		foreach ($this->dispatchTable as $modeKey => $modeValue)
			{
			foreach ($modeValue as $objectKey => $objectValue)
				{
				foreach ($objectValue as $actionKey => $actionValue)
					{
					foreach ($actionValue as $versKey => $versValue)
						{
						$txnProcessor	= $versValue;
						$version			= $versKey;
						$action			= $actionKey;
						$object			= $objectKey;
						$mode				= $modeKey;

						$this->setTextDisplay('Mode', 'txtMode', $mode, true);
						$this->setTextDisplay('Object', 'txtObject', $object, true);
						$this->setTextDisplay('Action', 'txtAction', $action, true);
						$this->setTextDisplay('Version', 'txtVers', $version, true);
						$this->setTextDisplay('Processor', 'txtModule', $txnProcessor, true);
						$this->setContextItemBreak();
						}
					}
				}
			}
		$this->popColumnContext('Dispatch Entries:'); // is the 'label' for the row

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Repository Metadata Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 'Dispatch Rules');
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_PropertyReview);
		}

	/***************************************************************************
	 * initializeTable()
	 *
	 * Initialization code to initialize the dispatch rules array.
	 *******/
	function initializeTable()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' executed');
			}

		/*
		 * Initialize the dispatch table. This should be changed to a direct
		 * use of a database entry to replace the need for the XML file and allow
		 * dynamic maintenance. Another alternative would be to initialize the
		 * PHP array from a databse entry at the start of processing.
		 *
		 * *******************************************
		 * Caution ... read before changing table data
		 * *******************************************
		 *
		 * Actual table data is searched in the following array priority order
		 *  -> mode -> object -> action -> version ==> target
		 *
		 * When placing new entries in the XML file, the search order must find unique names
		 * before encountering '*' entries. '*' entries are wildcard values and must always
		 * be the last to be defined within the branch of the table.
		 */

		$xmlFile = AF_ROOT_DIR.'/data/AF_DispatchRules.xml';
		if (!file_exists($xmlFile))
			{
			throw new Exception('Failed to find XML config file!');
			}

		$simpleXML = simplexml_load_file($xmlFile);
      if (!($simpleXML instanceof SimpleXMLElement))
			{
			throw new Exception('Failed to load config file as SimpleXML!');
			}

		$ruleset = array();

		foreach ($simpleXML->RuleSet as $RuleSet)
			{
			foreach ($RuleSet->DispatchRule as $DispatchRule)
				{
				// Note: String cast is necessary, otherwise these elements are
				// passed as SimpleXMLElement objects!
				$target		= (String) $DispatchRule->ModuleName;
				$object		= (String) $DispatchRule->Object;
				$action 		= (String) $DispatchRule->Action;
				$mode			= (String) $DispatchRule->MsgMode;
				$vers			= (String) $DispatchRule->Version;

				$item = array();
				$item['mode'] 		= $mode;
				$item['object']	= $object;
				$item['action']	= $action;
				$item['vers']		= $vers;
				$item['target']	= $target;
				$ruleset[] = $item;
				}
			}

		usort($ruleset, 'sortMyDispatchItems');

		foreach ($ruleset as $item)
			{
				$mode		= $item['mode'];
				$object	= $item['object'];
				$action	= $item['action'];
				$vers		= $item['vers'];
				$target	= $item['target'];
				$this->dispatchTable[$mode][$object][$action][$vers] = $target;
			}
		}

	}

?>