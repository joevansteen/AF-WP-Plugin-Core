<?php
/*
 * dlg_SiteMap.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-19 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-01 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirSiteMapPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirSiteMapPanel extends C_HtmlPanel {
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
		$actionRefPrefix	= $this->anchor->getActionUrlBase().'dialog='.$this->getPanelName();
		$textareaWidth 	= $this->stdTextareaWidth;

		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
//		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
//		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
//		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
//		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
//		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
//		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

		$this->pageTitle = 'Site Map';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Site Map');
		$this->panelTitle = & $myTitleData;

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
			$this->panelMsgs	= $itemMessages;
			}

		$myTable = new C_AirHtmlElement($this);
		$myTable->initialize(htmlEleTable);
		$myTable->setAttribute(htmlAttrAlign, 'center');

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'center');
							 $myTabData->createChild(htmlEleSpan, 'Coming soon ...');

		$menuSet = $this->anchor->getMenuNavArray();

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$rootElement  = $menuSet->getRoot();
		$rootText	  = $rootElement->ItemDescription;

		$work			  = & $myTabData->createChild(htmlEleUList);
		$rootListItem = & $work->createChild(htmlEleLineItem);
		$rootListItem->createChildSiteLink($rootText, AIR_Action_MenuSelect, $rootElement->ItemKey, 'RefLinkData');

		if ($rootElement->hasChildren())
			{
			$this->itemizeMenuLayer($rootElement, $rootListItem);
			}

		$this->panelBody			= & $myTable;
		}

	function itemizeMenuLayer($menuElement, $parentControl)
		{
		$work		= & $parentControl->createChild(htmlEleUList);
		$nextElement = $menuElement->firstChild();
		while ($nextElement)
			{
			$itemText = $nextElement->ItemDescription;

			$work2 = & $work->createChild(htmlEleLineItem);
			$work2->createChildSiteLink($itemText, AIR_Action_MenuSelect, $nextElement->ItemKey, 'RefLinkData');
			if ($nextElement->hasChildren())
				{
				$this->itemizeMenuLayer($nextElement, $work2);
				}

			$nextElement = $menuElement->nextChild();
			}
		}

	} // end of class

 ?>