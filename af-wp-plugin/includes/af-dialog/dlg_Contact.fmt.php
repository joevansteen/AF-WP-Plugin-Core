<?php
/*
 * dlg_Contact.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-19 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-01 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirContactPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirContactPanel extends C_HtmlPanel {
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

		$this->pageTitle = 'Contact';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Contact Points');
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
							 $myTabData->createChild(htmlEleSpan, 'We would enjoy hearing any feedback you may have to offer.');
							 $myTabData->createChild(htmlEleBreak);
							 $myTabData->createChild(htmlEleSpan, 'Please direct questions regarding <em>xxx</em> to <em>yyy</em>.');
							 $myTabData->createChild(htmlEleBreak);
							 $myTabData->createChild(htmlEleSpan, 'Please direct questions regarding <em>aaa</em> to <em>bbb</em>.');
							 $myTabData->createChild(htmlEleBreak);
							 $myTabData->createChild(htmlEleSpan, 'Please direct any other matters to <em>yyyyy</em>.');

		$this->panelBody			= & $myTable;
		}

	} // end of class

 ?>