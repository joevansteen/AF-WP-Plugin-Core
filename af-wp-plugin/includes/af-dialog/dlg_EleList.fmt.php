<?php
/*
 * dlg_EleList.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-28 JVS Integration with new standalone PHP environment scripts.
 *								This is a replacement for tiki-air_EleList.tpl and
 *								was bootstrapped from airMainMenu.fmt
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirEleListPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirEleListPanel extends C_HtmlPanel {
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
	 * initialize
	 *
	 * Initialize at this level consists of 'building' the result panel as a
	 * series of object specifications in the panel shell.
	 *******/
	function initialize($panelClass = '')
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		parent::initialize($panelClass);

		/*
		 * Get out data variables for use in configuring the detail portions
		 * of the result panel
		 */
		$air_SelectMultiple		= $this->anchor->getDlgVar('air_SelectMultiple');
		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

		$dlgAction					= $this->anchor->getDlgVar('dlgAction');
		$air_ShowNumbered			= $this->anchor->getDlgVar('air_ShowNumbered');
		$air_ShowKeys				= $this->anchor->getDlgVar('air_ShowKeys');
		$air_ShowChoice			= $this->anchor->getDlgVar('air_ShowChoice');
		$air_eleArray				= $this->anchor->getDlgVar('air_eleArray');
		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
		$this->setHiddenElement('dlgClientDlgID',	'Client4randomvalue');
		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Architecture Information Repository');
		$this->panelTitle = & $myTitleData;

		$itemArray	= array();

		$pageItem	= array();

		if ($air_showDiagnostics)
			{
			$itemMessages	= array();
			foreach ($resultDiag as $diagItem)
				{
				$itemMessage	=	array();
				$itemMessage['msgItem']	= 	$diagItem['msgItem'];
				$itemMessage['msgText']	=	$diagItem['msgText'];
				if (array_key_exists('msgType', $diagItem))
					{
					$itemMessage['msgType']	= 	$diagItem['msgType'];
					}
				$itemMessages[]	= $itemMessage;
				}
			$pageItem['itemMessages']	= $itemMessages;
			}

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleSpan, null, $air_EleType[$air_selectedEleType].' Element Selection');
		$myTitleData->setAttribute(htmlAttrClass, 'ItemTitle');
		$pageItem['itemTitle']	= & $myTitleData;

		$headerText = '';
		if ($dlgAction == AIR_Action_Show)
			{
			$headerText  = '';
			}
		else
		if ($dlgAction == AIR_Action_PurgeType)
			{
			$headerText  = 'Please click on the <b>Submit</b> button to continue processing,';
			$headerText .= ' or click <b>Quit</b> to stop.';
			}
		else
		if ($dlgAction == AIR_Action_DirViewRaw)
			{
			$headerText  = 'Select one';
			if ($air_SelectMultiple)
				{
				$headerText .= ' or more';
				}
			$headerText .= ' of the following.';
			$headerText .= ' Then click an action button to perform that task on the selected item(s).';
			}
		else
			{
			$headerText  = 'Select one';
			if ($air_SelectMultiple)
				{
				$headerText .= ' or more';
				}
			$headerText .= ' of the following. Then click <b>Submit</b> to make your choice.';
			}
		$myHeaderData = new C_AirHtmlElement($this);
		$myHeaderData->initialize(htmlEleSpan, null, $headerText);
		$myHeaderData->setAttribute(htmlAttrClass, 'ItemHeader');
		$pageItem['itemHeader']	= & $myHeaderData;

		$listTable = new C_AirHtmlElement($this);
		$listTable->initialize(htmlEleTable);

		if ($air_ShowNumbered)
			{
			$listTabRow		= & $listTable->createChild(htmlEleTblRow);
			$listTabData	= & $listTabRow->createChild(htmlEleTblData);
									 $listTabData->setAttribute(htmlAttrAlign, 'left');
			$innerList		= & $listTabData->createChild(htmlEleOList);

			foreach ($air_eleArray as $key => $value)
				{
				$itemText = '';
				if ($air_ShowKeys)
					{
					$itemText .= $key.' ';
					}
				$itemText .= $value;
//		echo 'ListFormat '. __LINE__ . ' itemText = '.$itemText.'<br/>';
				$innerList->createChild(htmlEleLineItem, $itemText);
				}
			}
		else
			{
			foreach ($air_eleArray as $key => $value)
				{
				$listTabRow		= & $listTable->createChild(htmlEleTblRow);
				$listTabData	= & $listTabRow->createChild(htmlEleTblData);
										 $listTabData->setAttribute(htmlAttrAlign, 'left');
				$itemText = '';
				if ($air_ShowChoice)
					{
					if ($air_SelectMultiple)
						{
						if ($air_ShowKeys)
							{
							$itemText .= $key.' ';
							}
						$itemText .= $value;

						$work		= & $listTabData->createChild(htmlEleInput, $itemText);
						$work->setAttribute(htmlAttrType, 'checkbox');
						if (!empty($key))
							{
							$work->setAttribute(htmlAttrName, $key);
						 	if ((strpos($key, 'dlgChoice:') == 0)
							 && (strpos($key, 'dlgChoice:') !== false))
								{
								/*
								 * Checkbox values recorded in HTML with unique 'dlgChoice:xxxxx' variable
								 * names will be re-coded and passed in the original decode message as a
								 * collection of 'dlgObject' elements.
								 */
								$this->anchor->sessionDoc->putDialogVarTracker('dlgObject', 'Collection'); // Catalog the dialog form variables
								}
							else
								{
								$this->anchor->sessionDoc->putDialogVarTracker($key, 'Collection'); // Catalog the dialog form variables
								}
							}
						}
					else
						{
						if ($air_ShowKeys)
							{
							$itemText .= $key.' ';
							}
						$itemText .= $value;

						$work		= & $listTabData->createChild(htmlEleInput, $itemText);
						$work->setAttribute(htmlAttrType, 'radio');
						$work->setAttribute(htmlAttrName, 'dlgObject');
						$work->setAttribute(htmlAttrValue, $key);
						$this->anchor->sessionDoc->putDialogVarTracker('dlgObject', 'Item'); // Catalog the dialog form variables
						}
					}
				else
					{
					if ($air_ShowKeys)
						{
						$itemText .= $key.' ';
						}
					$itemText .= $value;

					$work		= & $listTabData->createChild(htmlEleSpan, $itemText);
					}
				}
			}

		$pageItem['itemBody']	= & $listTable;

		$pageItem['itemFooter']	= & $this->formatStdDialogArray($air_ItemFooter);

		$itemArray[]				= $pageItem;

		$this->panelItems = $itemArray;
		}

	} // end of class

 ?>