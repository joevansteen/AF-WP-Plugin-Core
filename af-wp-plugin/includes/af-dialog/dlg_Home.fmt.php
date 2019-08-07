<?php
/*
 * dlg_Home.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-19 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-01 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirHomePanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirHomePanel extends C_HtmlPanel {
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
		$companyName	= $this->anchor->getStdVar('companyName');
		$imageHome	= $this->anchor->getStdVar('imageHome');
		$logoImage = 'afwebwatermark.gif';
		$logoPath  = $imageHome . $logoImage;
		$actionRefPrefix	= $this->anchor->getActionUrlBase().'dialog='.$this->getPanelName();
		$textareaWidth 	= $this->stdTextareaWidth;

//		$air_SelectMultiple		= $this->anchor->getDlgVar('air_SelectMultiple');
//		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
//		$dlgPanelType				= $this->anchor->getDlgVar('dlgPanelType');
//		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');
//		$panelItemSubtitle		= $this->anchor->getDlgVar('panelItemSubtitle');
//		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

//		$Ele_CreateEntity			= $this->anchor->getDlgVar('Ele_CreateEntity');
//		$Ele_CreateDt				= $this->anchor->getDlgVar('Ele_CreateDt');
//		$Ele_ChgType				= $this->anchor->getDlgVar('Ele_ChgType');
//		$Ele_ChgEntity				= $this->anchor->getDlgVar('Ele_ChgEntity');
//		$Ele_ChgDt					= $this->anchor->getDlgVar('Ele_ChgDt');
//		$Ele_ChgComments			= $this->anchor->getDlgVar('Ele_ChgComments');

//		$air_ItemHeader			= $this->anchor->getDlgVar('air_ItemHeader');
//		$air_Dialog					= $this->anchor->getDlgVar('air_Dialog');
//		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
//		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
//		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
//		$this->setHiddenElement('dlgClientDlgID',	'Client2randomvalue');
//		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
//		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
//		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
//		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

		$this->pageTitle = 'Home Page';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Welcome to <em>BEAMS</em>');
		$myTitleData->createChild(htmlEleH2, '<em>B</em>usiness <em>E</em>ngineering and <em>A</em>nalytic <em>M</em>odeling <em>S</em>ystem&#8482;');
		$this->panelTitle = & $myTitleData;

		$content  = 'Helping to manage the present, and architect a path to the future;<br/>';
		$content .= 'by defining, tracking and analyzing the elements and connections that matter.';

		$myHdrData = new C_AirHtmlElement($this);
		$myHdrData->initialize(htmlEleSpan, null, $content);
		$myHdrData->setAttribute(htmlAttrClass, 'CatchPhrase');
		$this->panelHeader = & $myHdrData;

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
			$this->panelMsgs	= $itemMessages;
			}

//		$pageItem['itemTitle']	= & $myTitleData;

//		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);

		$myTable = new C_AirHtmlElement($this);
		$myTable->initialize(htmlEleTable);
		$myTable->setAttribute(htmlAttrAlign, 'center');

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrClass, 'ImageCell');
		$image		= & $myTabData->createChild(htmlEleImg);
							 $image->setAttribute(htmlAttrSrc, $logoPath);
							 $image->setAttribute(htmlAttrAlt, $companyName);
//							 $image->setAttribute(htmlAttrHeight, '586');
//							 $image->setAttribute(htmlAttrWidth, '603');
							 $image->setAttribute(htmlAttrHeight, '393');	// 66%
							 $image->setAttribute(htmlAttrWidth, '404');		// 66%

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'center');
							 $myTabData->createChild(htmlEleSpan, 'Please take some time to explore our site and get to know us.');
							 $myTabData->createChild(htmlEleSpan, 'We would enjoy hearing any feedback you may have to offer.');

		$this->panelBody			= & $myTable;
		}

	} // end of class
/*******************************************************************
 *******************************************************************/

 ?>