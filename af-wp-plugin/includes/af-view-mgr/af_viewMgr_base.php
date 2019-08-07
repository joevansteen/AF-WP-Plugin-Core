<?php
/*
 * af_viewMgr_userSecurity script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from af_dialogencode
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_ViewMgrBase';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_ViewMgrBase extends C_AirProcModBase {
	private $dlgContentArea		= 'dlgContent';
	private $dlgContextStack	= array();
	private $dlgContextId		= array();
	private $currentStackItem	= 'dlgContent';
	var $dlgHeader					= NULL;
	var $dlgContent				= NULL;
	var $dlgFooter					= NULL;
	var $dlgDfltMaxFieldSize	= 255;
	var $dlgDfltFieldWidth		= 72;
	var $dlgDfltTextBoxRows 	= 5;
	var $dlgDfltMaxPswdSize		= 32;
	var $dlgDfltPswdFieldWidth	= 72;
	var $hasSelectOptionNone	= true;
   var $hasSelectOptionAny		= false;
	var $hasSelectOptionAll		= false;
	var $selectOnFooter			= true;
	var $selectOnHeader			= false;
	var $actionButtons			= array();

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

		$opttion = $this->anchor->sessionDoc->getSessionData('DfltMaxFieldSize');
		if (! empty($opttion))
			{
			$dlgDfltMaxFieldSize = $opttion;
			}
		$opttion = $this->anchor->sessionDoc->getSessionData('DfltFieldWidth');
		if (! empty($opttion))
			{
			$dlgDfltFieldWidth = $opttion;
			}
		$opttion = $this->anchor->sessionDoc->getSessionData('DfltTextBoxRows');
		if (! empty($opttion))
			{
			$dlgDfltTextBoxRows = $opttion;
			}
		$opttion = $this->anchor->sessionDoc->getSessionData('DfltMaxPswdSize');
		if (! empty($opttion))
			{
			$dlgDfltMaxPswdSize = $opttion;
			}
		$opttion = $this->anchor->sessionDoc->getSessionData('DfltPswdFieldWidth');
		if (! empty($opttion))
			{
			$dlgDfltPswdFieldWidth = $opttion;
			}
		}

	/***************************************************************************
	 * initialize
	 *
	 *******/
	function initialize(& $procContext, & $baseMsg, & $procMsg)
	 	{
	 	parent::initialize($procContext, $baseMsg, $procMsg);

		$this->dlgHeader				= array();
		$this->dlgFooter				= array();
		$this->dlgContent				= array();
		}

	/***************************************************************************
	 * pushContextStack
	 *
	 * Identifies a new context Stack item. All detail items added once this
	 * function is called become part of a new dlgContext. When the corresponding
	 * popContextStack is called, the array will be placed into the "higher level"
	 * dlgContext as the content of that single item.
	 *******/
	function pushContextStack($contextName)
	 	{
		array_push($this->dlgContextId, $contextName);
		$this->currentStackItem = $contextName;

		if (array_key_exists($contextName, $this->dlgContextStack))
			{
			unset($this->dlgContextStack[$contextName]);
			}
		}

	/***************************************************************************
	 * popNestedContext
	 *
	 * Pops a context as a fully nested dialog context.
	 *******/
	function popNestedContext($contentLabel)
	 	{
	 	$this->popContextStack('dialogarray', $contentLabel);
		}

	/***************************************************************************
	 * popColumnContext
	 *
	 * Pops a context as a set of dialog column elements.
	 *******/
	function popColumnContext($contentLabel)
	 	{
	 	$this->popContextStack('columns', $contentLabel);
		}

	/***************************************************************************
	 * popContextStack
	 *
	 * Identifies a new context Stack item. All detail items added once this
	 * function is called become part of a new dlgContext. When the corresponding
	 * popContextStack is called, the array will be placed into the "higher level"
	 * dlgContext as the content of that single item.
	 *******/
	function popContextStack($contextType, $contentLabel)
	 	{
	 	// Check processing logic
	 	if ($this->currentStackItem == 'dlgContent')
		 	{
	 		throw new Exception('Empty dialog context stack being popped');
		 	}

		$contextName = array_pop($this->dlgContextId);
	 	if ($this->currentStackItem != $contextName)
		 	{
	 		throw new Exception('Dialog context stack synchronization error');
		 	}

		// Process the request
		if (array_key_exists($contextName, $this->dlgContextStack))
			{
			// Push the sub-context onto the dialog content stack
			$eleArray						= array();
			$eleArray['itemType']		= $contextType;
			$eleArray['itemName']		= $contextName;
			$eleArray['itemLabel']		= $contentLabel;
			$itemArray						= array();
			foreach ($this->dlgContextStack[$contextName] as $stackedItem)
				{
				$itemArray[] = $stackedItem;
				}
			$eleArray['itemContent']	= $itemArray;
			$this->dlgContent[] 			= $eleArray;
			}

		// Reset the environment
		$contextName = array_pop($this->dlgContextId);
		if ($contextName == NULL)
			{
			$this->currentStackItem = 'dlgContent';
			}
		else
			{
			array_push($this->dlgContextId, $contextName);
			$this->currentStackItem = $contextName;
			}
		}

	/***************************************************************************
	 * postContentElement()
	 *
	 * Posts an HTML content element to the appropriate array based on the
	 * segment defined in dlgContentArea.
	 *******/
	function postContentElement($dlgElement)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		switch ($this->dlgContentArea)
			{
			case 'dlgHeader':
				$this->dlgHeader[] = $dlgElement;
				break;
			case 'dlgContent':
			 	if ($this->currentStackItem == 'dlgContent')
				 	{
					$this->dlgContent[] = $dlgElement;
				 	}
				else
					{
					$this->dlgContextStack[$this->currentStackItem][] = $dlgElement;
					}
				break;
			case 'dlgFooter':
				$this->dlgFooter[] = $dlgElement;
				break;
			default:
				break;
			}

		return;
		}

	/***************************************************************************
	 * createActionButtonInfo()
	 *
	 * Place the panel action button information into the footer, and if
	 * requested header, SMARTY template arrays.
	 *******/
	function createActionButtonInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$itemLabel			= '';
		$dlgItemName		= '';
		$chklistArray		= array();

		if (! empty($this->actionButtons))
			{
			foreach ($this->actionButtons as $cmdAction)
				{
				$itemArray						= array();
				if ($cmdAction == AIR_Action_Reset)
					{
					$itemArray['itemForm']		= 'reset';						// defines the button action
					}
				else
					{
					$itemArray['itemForm']		= 'submit';						// defines the button action
					}
				$itemArray['itemName']		= 'request';				// defines the button variable name when the form is posted
				$itemArray['itemContent']	= $cmdAction;		// defines the display value of the button, and the content posted
				$chklistArray[]				= $itemArray;
				}
			}

		$dlgContentAreaSave = $this->dlgContentArea;

		if (! empty($chklistArray))
			{
			if ($this->selectOnHeader)
				{
				$this->dlgContentArea = 'dlgHeader';
				$this->setButtonOptDisplay($itemLabel, $dlgItemName, $chklistArray, false);
				}
			if ($this->selectOnFooter)
				{
				$this->dlgContentArea = 'dlgFooter';
				$this->setButtonOptDisplay($itemLabel, $dlgItemName, $chklistArray, false);
				}
			}

		$this->dlgContentArea = $dlgContentAreaSave;
		}

	/***************************************************************************
	 * showDiagnosticsInfo()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showDiagnosticsInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->createActionButtonInfo();

		$diagData			= array();

		/*
		 * Process accumulated step diagnostics
		 */
		if (count($this->anchor->dialogDiagnostics) > 0)
			{
			foreach ($this->anchor->dialogDiagnostics as $diagItem)
				{
				$diag = array();
				$diag['msgItem']	= $diagItem['diagItem'];
				$diag['msgType']	= $diagItem['diagLvl'];
				if ($diagItem['diagType'] == 'Text')
					{
					$diag['msgText']	= $diagItem['diagText'];
					}
				else
					{
					$diag['msgText']	= $diagItem['diagText'];
					}
				$diagData[] = $diag;
				}
			}


		/*
		 * Process message diagnostics
		 */
		$msgDiagCount = $this->procMsg->getDiagnosticCount();
		if ($msgDiagCount > 0)
			{
			$daignosticsExist = true;

			for ($i = 0; $i < $msgDiagCount; $i++)
				{
				/*
				 * NOTE.
				 * Use double quotes rather than single quotes for this intial version to
				 * get the line break characters to be recognized. However, this should
				 * probably migrate to a more sophisticated solution where a table is
				 * used to display the messages, rather than a text box. Within the table
				 * a message number can be provided along with the text, and a hyperlink
				 * can be made available on the number to provide a reference to a diagnostic
				 * help feature or function ... or a pop-up window, etc.
				 */
				$diagItem = $this->procMsg->getDiagnosticItemData($i);
				$diagType	= $diagItem['DiagType'];
				$diagRef		= $diagItem['DiagRef'];
				$diagIdent	= $diagItem['DiagIdent'];
				if ($diagType == 'Ref')
					{
					$diagText = $diagIdent;
					}
				else
					{
					$diagText = $diagIdent;
					}
				$diag = array();
				$diag['msgItem']	= $diagRef;
				$diag['msgType']	= AIR_DiagMsg_Error;
				$diag['msgText']	= $diagText;
				$diagData[] = $diag;
				}
			}

		if (count($diagData) > 0)
			{
			$this->anchor->setDlgVarByRef('resultDiag', $diagData);
			$diagnosticsExist	= true;
			}
		else
			{
			$diagnosticsExist	= false;
			}

		$this->anchor->setDlgVar('air_showDiagnostics', $diagnosticsExist);
		}

	/***************************************************************************
	 * setSimpleDisplay()
	 *
	 * Add display management variables to place a basic text data field into
	 * the output panel construction array.
	 *******/
	function setSimpleDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'contentdump';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setKeyedTextDisplay()
	 *
	 * Add display management variables for a keyed, read-only data field to the
	 * output panel construction array.
	 *******/
	function setKeyedTextDisplay($dlgItemLabel, $dlgItemName, $dlgItemKey,
										  $dlgItemContent, $dlgFieldSize=NULL)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'lookup';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		if ((is_null($dlgFieldSize))
		 || (empty($dlgFieldSize)))
		 	{
			$dlgFieldWidth		= $this->dlgDfltFieldWidth;
		 	}
		 else
		 	{
			$dlgFieldWidth		=	$dlgFieldSize;
			}
		$eleArray['itemSize']		= $dlgFieldWidth;
		$eleArray['itemChoice']		= $dlgItemKey;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setDisplayBreak()
	 *
	 * Add display management variables for a text data field to the output panel
	 * construction array.
	 *******/
	function setDisplayBreak($dlgItemLabel='')
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for [$dlgItemLabel]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'break';
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setContextItemBreak()
	 *
	 * Add display management variables for a text data field to the output panel
	 * construction array.
	 *******/
	function setContextItemBreak($dlgItemLabel='')
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for [$dlgItemLabel]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'contextItemBreak';
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}


	/***************************************************************************
	 * setDisplayRule()
	 *
	 * Add display management variables for a text data field to the output panel
	 * construction array.
	 *******/
	function setDisplayRule($dlgItemLabel='')
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for [$dlgItemLabel]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'rule';
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setTextDisplay()
	 *
	 * Add display management variables for a text data field to the output panel
	 * construction array.
	 *******/
	function setTextDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent,
									$dlgReadOnly=false, $dlgFieldSize=NULL, $dlgMaxSize=NULL)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'text';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		if ((is_null($dlgFieldSize))
		 || (empty($dlgFieldSize)))
		 	{
			$dlgFieldWidth				= $this->dlgDfltFieldWidth;
		 	}
		 else
		 	{
			$dlgFieldWidth				= $dlgFieldSize;
			}
		$eleArray['itemSize']		= $dlgFieldWidth;
		if ((is_null($dlgMaxSize))
		 || (empty($dlgMaxSize)))
		 	{
			$dlgMaxFieldSize			= $this->dlgDfltMaxFieldSize;
		 	}
		 else
		 	{
			$dlgMaxFieldSize			= $dlgMaxSize;
			}
		$eleArray['itemMaxSize']	= $dlgMaxFieldSize;
		$eleArray['itemNoCapture']	= $dlgReadOnly;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setPswdDisplay()
	 *
	 * Add display management variables for a password data field to the output panel
	 * construction array.
	 *******/
	function setPswdDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent,
									$dlgReadOnly=false, $dlgFieldSize=NULL, $dlgMaxSize=NULL)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'password';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		if ((is_null($dlgFieldSize))
		 || (empty($dlgFieldSize)))
		 	{
			$dlgFieldWidth				= $this->dlgDfltPswdFieldWidth;
		 	}
		 else
		 	{
			$dlgFieldWidth				= $dlgFieldSize;
			}
		$eleArray['itemSize']		= $dlgFieldWidth;
		if ((is_null($dlgMaxSize))
		 || (empty($dlgMaxSize)))
		 	{
			$dlgMaxFieldSize			= $this->dlgDfltMaxPswdSize;
		 	}
		 else
		 	{
			$dlgMaxFieldSize			= $dlgMaxSize;
			}
		$eleArray['itemMaxSize']	= $dlgMaxFieldSize;
		$eleArray['itemNoCapture']	= $dlgReadOnly;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setTextboxDisplay()
	 *
	 * Add display management variables for a textbox data field to the output panel
	 * construction array.
	 *******/
	function setTextboxDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent,
										$dlgReadOnly=false, $dlgTextRows=NULL, $dlgTextCols=NULL)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		$eleArray						= array();
		$eleArray['itemType']		= 'textbox';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		if ((is_null($dlgTextCols))
		 || (empty($dlgTextCols)))
		 	{
			$dlgFieldWidth				= $this->dlgDfltFieldWidth;
		 	}
		 else
		 	{
			$dlgFieldWidth				= $dlgTextCols;
			}
		$eleArray['itemCols']		= $dlgFieldWidth;
		if ((is_null($dlgTextRows))
		 || (empty($dlgTextRows)))
		 	{
			$dlgMaxRows					= $this->dlgDfltTextBoxRows;
		 	}
		 else
		 	{
			$dlgMaxRows					= $dlgTextRows;
			}
		$eleArray['itemRows']		= $dlgMaxRows;
		$eleArray['itemNoCapture']	= $dlgReadOnly;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setDropdownDisplay()
	 *
	 * Add display management variables for a drop-down selection data field to
	 * the output panel construction array.
	 *******/
	function setDropdownDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $dlgItemChoice)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		$eleArray['itemType']		= 'dropdown';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemChoice']		= $dlgItemChoice;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setRadioOptDisplay()
	 *
	 * Add display management variables for a radio option selection data field to
	 * the output panel construction array.
	 *******/
	function setRadioOptDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $dlgItemChoice, $vertical=true)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		if ($vertical)
			{
			$eleArray['itemType']	= 'radiolist';
			}
		else
			{
			$eleArray['itemType']	= 'radiolisth';
			}
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemChoice']		= $dlgItemChoice;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setCheckOptDisplay()
	 *
	 * Add display management variables for a checklist option selection data field to
	 * the output panel construction array.
	 *******/
	function setCheckOptDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $vertical=true)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		if ($vertical)
			{
			$eleArray['itemType']	= 'checkboxlist';
			}
		else
			{
			$eleArray['itemType']	= 'checkboxlisth';
			}
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setButtonOptDisplay()
	 *
	 * Add display management variables for a button set option selection data field to
	 * the output panel construction array.
	 *******/
	function setButtonOptDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $vertical=false)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		if ($vertical)
			{
			$eleArray['itemType']	= 'buttonlist';
			}
		else
			{
			$eleArray['itemType']	= 'buttonlisth';
			}
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setMultiSelectDisplay()
	 *
	 * Add display management variables for a multiple selection drop down
	 * selection data field to the output panel construction array.
	 *******/
	function setMultiSelectDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		$eleArray['itemType']		= 'multidropdown';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setSimpleListDisplay()
	 *
	 * Add display management variables for a simple list data presentation to
	 * the output panel construction array.
	 *******/
	function setSimpleListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $ordered=true)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		if ($ordered)
			{
			$eleArray['itemType']	= 'orderedlist';
			}
		else
			{
			$eleArray['itemType']	= 'unorderedlist';
			}
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setPropertyListDisplay()
	 *
	 * Add display management variables for a simple list data presentation to
	 * the output panel construction array.
	 *******/
	function setPropertyListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		$eleArray['itemType']	= 'propertylist';
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setKeyedListDisplay()
	 *
	 * Add display management variables for a simple list data presentation to
	 * the output panel construction array.
	 *******/
	function setKeyedListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $columnar=true)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			}

		if ((!isset($dlgItemContent))
		 || (is_null($dlgItemContent))
		 || (!is_array($dlgItemContent)))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, "Error executing " . __CLASS__ . '::' . __FUNCTION__ . " for $dlgItemLabel [$dlgItemName]");
			trigger_error("Invalid content passed to ".__FUNCTION__, E_USER_NOTICE);
			}
		$eleArray						= array();
		if ($columnar)
			{
			$eleArray['itemType']	= 'columnlist';
			}
		else
			{
			$eleArray['itemType']	= 'keyedlist';
			}
		$eleArray['itemName']		= $dlgItemName;
		$eleArray['itemContent']	= $dlgItemContent;
		$eleArray['itemLabel']		= $dlgItemLabel;
		$this->postContentElement($eleArray);

		return;
		}

	/***************************************************************************
	 * setSelectionListInfo()
	 *
	 * Create a series of standard display management SMARTY variables for use in
	 * directling a standard data selection display.
	 *******/
	function setSelectionListInfo($dlgItemLabel, $varNameBase, $dlgSelectionType, $dlgSelectedValue,
											$showAsArray = true, $showAsReadOnly = false)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " using $varNameBase");
			}

		$dlgItemName	= 'dlg'.$varNameBase;

		if ((empty($dlgSelectedValue))
		 || (!is_string($dlgSelectedValue)))
		 	{
			$dlgItemChoice = AIR_Null_Identifier;
		 	}
	 	else
		 	{
			$dlgItemChoice = $dlgSelectedValue;
		 	}

		if ((! $showAsArray)
		 && ($showAsReadOnly))
		 	{
	 		$textValue = '';
	 		if (! empty($dlgItemChoice))
	 			{
		 		$textValue = $this->anchor->getRefName($dlgItemChoice);
		 		}
			$this->setKeyedTextDisplay($dlgItemLabel, $dlgItemName, $dlgItemChoice, $textValue);
		 	}
		else
			{
			$dataArray		= $this->anchor->get_allElementsByType($dlgSelectionType, 0, NULL, false,
																					$this->hasSelectOptionNone,
																					$this->hasSelectOptionAny,
																					$this->hasSelectOptionAll);
			$entries 		= count($dataArray);
			if ($entries > 0)
				{
				if ($showAsArray)
					{
					$this->setDropdownDisplay($dlgItemLabel, $dlgItemName, $dataArray, $dlgItemChoice);
					}
				else
					{
					$this->setTextDisplay($dlgItemLabel, $dlgItemName, $dataArray[$dlgItemChoice], $showAsReadOnly);
					}
				}
			else
				{
				$this->setSimpleDisplay($dlgItemLabel, $dlgItemName, 'No content defined.');
				}
			}

		return;
		}

	/***************************************************************************
	 * setShowSelectionInfo()
	 *
	 * Create a series of standard display management SMARTY variables for use in
	 * directling a standard data selection display.
	 *
	 * The first parameter defines the 'base data variable name' which forms the
	 * core of the series of data names created.
	 *
	 * 	The following series of variables are created, where Xxxx is the character
	 *		string defined as $varNameBase:
	 *
	 *			air_Xxxx					An array of candidate selection values
	 *			air_showXxxx			T/F, defines whether to show the variable or not
	 * 		air_showXxxxArray		T/F, defines whether to show the variable as a
	 *											selection array, or as a single selected value
	 *			air_XxxxModifiable	T/F, defines whether the data value shown should be
	 *											indicated to the user as a modifiable value
	 *			air_selectedXxxx		A string value providing the key to the item in the
	 *											air_showXxxxArray that has been selected or has
	 *											been identified as the default value
	 *
	 * The second parameter defines whether the content is desired to be shown
	 * on the dialog panel. If FALSE, it is up to the template code to bypass
	 * production of the field.
	 *
	 * dlgSelectionType defines collection of valid types
	 * dlgSelectedValue, if not null, defines actual selection from this collection
	 * showAsArray - defines whether the result should be shown as an array
	 * showAsModifiable - defines whether the dialog should be prepared to
	 *			allow modification of the choice as an option
	 *******/
	function setShowSelectionInfo($varNameBase, $showVar,
											$dlgSelectionType, $dlgSelectedValue,
											$showAsArray = true, $showAsModifiable = false,
											$keyPrefix = null)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__ . " using $varNameBase");
			}

		/*
		 * Establish variable variables to hold the names of the PHP variables we will be
		 * creating that contain the actual variable values. These will be used to set the
		 * SMARTY variables, or the SMARTY variables will be referenced to point to these
		 * variables.
		 */
		$php_VarBase				= 'air_'.$varNameBase;

		if ($showVar)
			{
			/*
			 * If variable display has been requested, build the selection array
			 * and post the various SMARTY variables
			 */
			$$php_VarBase 	= $this->anchor->get_allElementsByType($dlgSelectionType, 0, NULL, false,
																					$this->hasSelectOptionNone,
																					$this->hasSelectOptionAny,
																					$this->hasSelectOptionAll);
/*
 * Following code demonstrates correct syntax to do array references on variable variable
 */
//			if ($this->hasSelectOptionNone)
//				{
//				${$php_VarBase}[AIR_Null_Identifier] = '- None -';
//				}

			$entries 		= count($$php_VarBase);

			$this->anchor->setDlgVarByRef('air_'.$varNameBase, $$php_VarBase);
			if ($entries > 0)
				{
				$this->anchor->setDlgVar('air_show'.$varNameBase, true);
				if (($entries > 1)
				 && ($showAsArray))
					{
					$this->anchor->setDlgVar('air_show'.$varNameBase.'Array', true);
					}
				else
					{
					$this->anchor->setDlgVar('air_show'.$varNameBase.'Array', false);
					if (($entries > 1)
					 && ($showAsModifiable))
						{
						$this->anchor->setDlgVar('air_'.$varNameBase.'Modifable', true);
						}
					else
						{
						$this->anchor->setDlgVar('air_'.$varNameBase.'Modifable', false);
						}
					}

				if ((empty($dlgSelectedValue))
				 || (!is_string($dlgSelectedValue)))
				 	{
					$selected = AIR_Null_Identifier;
				 	}
			 	else
				 	{
					$selected = $dlgSelectedValue;
				 	}
				$this->anchor->setDlgVar('air_selected'.$varNameBase, $selected);
				}
			else
				{
				$this->anchor->setDlgVar('air_show'.$varNameBase, false);
				}
			}
		else
			{
			/*
			 * If variable display has NOT been requested, set the single SMARTY variable
			 * to inform the template to skip the dialog field.
			 */
			$this->anchor->setDlgVar('air_show'.$varNameBase, false);
			}

		return;
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