<?php
/*
 * dlg_ManifestReview.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.4 2005-NOV-17 JVS Bootstrap from dlg_DirView.fmt
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirManifestReviewPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirManifestReviewPanel extends C_HtmlPanel {
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
		$air_listItemArray		= $this->anchor->getDlgVar('air_listItemArray');
		$air_listTypeArray		= $this->anchor->getDlgVar('air_listTypeArray');
		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');
//		$panelItemSubtitle		= $this->anchor->getDlgVar('panelItemSubtitle');
//		$air_ItemHeader			= $this->anchor->getDlgVar('air_ItemHeader');
		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
//		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
//		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
//		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
//		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
//		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
//		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

		$this->pageTitle = 'Element Manifest Review';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Element Manifest Review');
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
		$myTitleData->initialize(htmlEleSpan, null, $panelItemTitle);
		$myTitleData->setAttribute(htmlAttrClass, 'ItemTitle');
		$myTitleData->setAttribute(htmlAttrAlign, 'left');
		if (! empty($panelItemSubtitle))
			{
			$work		= & $myTitleData->createChild(htmlEleSpan);
			$work->setAttribute(htmlAttrClass, 'ItemSubtitle');
			$work		= & $work->createChild(htmlEleMini, '<br/>'.$panelItemSubtitle);
			}
		$pageItem['itemTitle']	= & $myTitleData;

		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);

		$pageBody = new C_AirHtmlElement($this);
		$pageBody->initialize(htmlEleTable);

	$defaultRequest	= AIR_Action_View;

	$itemCount	= count($air_listItemArray);

	$myTable		= & $pageBody->createChild(htmlEleTable);
	$myTabRow		= & $myTable->createChild(htmlEleTblRow);
	$myTabData		= & $myTabRow->createChild(htmlEleTblHeadCell, 'Type');
							 $myTabData->setAttribute(htmlAttrAlign, 'center');
	$myTabData		= & $myTabRow->createChild(htmlEleTblHeadCell, 'Element Name');
							 $myTabData->setAttribute(htmlAttrAlign, 'center');
/*	$myTabData		= & $myTabRow->createChild(htmlEleTblHeadCell, 'Old Key');
							 $myTabData->setAttribute(htmlAttrAlign, 'center');      */
/*	$myTabData		= & $myTabRow->createChild(htmlEleTblHeadCell, 'New Key');
							 $myTabData->setAttribute(htmlAttrAlign, 'center');		*/

	$lastType = '';
	$lastItem = '';
	$lastName = '';

		foreach ($air_listItemArray as $dirElement)
			{
			$myTabRow		= & $myTable->createChild(htmlEleTblRow);

			$itemType	   = $dirElement['Type'];
			if ($itemType != $lastType)
				{
//				$cellContent   = $air_listTypeArray[$itemType];
				$cellContent   = $itemType;
				$myTabData		= & $myTabRow->createChild(htmlEleTblData, $cellContent);
										 $myTabData->setAttribute(htmlAttrColSpan, '2');
										 $myTabData->setAttribute(htmlAttrAlign, 'left');
										 $myTabData->setAttribute(htmlAttrClass, 'TabGroupHdr');
				$lastType = $itemType;
				$myTabRow		= & $myTable->createChild(htmlEleTblRow);
				}

			$cellContent   = '&#160;';
			$myTabData		= & $myTabRow->createChild(htmlEleTblData, $cellContent);
									 $myTabData->setAttribute(htmlAttrAlign, 'left');

			$itemIdent		= $dirElement['ItemGuid'];
			$itemName		= $dirElement['Name'];
			$myTabData		= & $myTabRow->createChild(htmlEleTblData);
									 $myTabData->setAttribute(htmlAttrAlign, 'left');
			$itemClass		= null;
			if ($lastItem	== $itemIdent)
				{
				$itemClass	= 'ErrorMsg';
				}
			else
			if ($lastName	== $itemName)
				{
				$itemClass	= 'InfoMsg';
				}
			$myTabData->createChildSiteLink($dirElement['Name'], $defaultRequest, $dirElement['ItemGuid'], $itemClass);
			$lastItem		= $itemIdent;
			$lastName		= $itemName;
			$first = true;

//			$cellContent = '';
//			$cellContent	= '<a href="'.$actionRefPrefix.'&request='.AIR_Action_Delete.'&target='.$dirElement['ItemGuid'].'">';
//			$myTabData		= & $myTabRow->createChild(htmlEleTblData, $cellContent);
			}

		$pageItem['itemBody']	= & $pageBody;

		$pageItem['itemFooter']	= & $this->formatStdDialogArray($air_ItemFooter);

		$itemArray[]				= $pageItem;
		$this->panelItems 		= $itemArray;
		}

	} // end of class

?>