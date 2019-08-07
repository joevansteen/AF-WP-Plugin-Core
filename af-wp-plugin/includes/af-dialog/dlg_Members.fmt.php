<?php
/*
 * dlg_Members.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-19 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-01 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirMembersPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirMembersPanel extends C_HtmlPanel {
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
		$air_showContext			= $this->anchor->getDlgVar('air_showContext');
		$air_showContextArray	= $this->anchor->getDlgVar('air_showContextArray');
		$air_Context				= $this->anchor->getDlgVar('air_Context');
		$air_selectedContext		= $this->anchor->getDlgVar('air_selectedContext');
		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
//		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
//		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
//		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
//		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
//		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
//		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

		$navSecondary	= array();

		$navItem						= array();
		$navItem['name']			= 'Element Directory';
		$navItem['procIdent']	= Dialog_DirViewMenu;
		$navSecondary[] 			= $navItem;
		$navItem						= array();
		$navItem['name']			= 'Repository Maintenance';
		$navItem['procIdent']	= Dialog_AirMenu;
		$navSecondary[] 			= $navItem;
		$navItem						= array();
		$navItem['name']			= 'Repository Admin';
		$navItem['procIdent']	= Dialog_AirAdmin;
		$navSecondary[] 			= $navItem;
		$navItem						= array();
		$navItem['name']			= 'System Admin';
		$navItem['procIdent']	= Dialog_SysAdmin;
		$navSecondary[] 			= $navItem;

		$this->anchor->setStdVar('navSecondary', $navSecondary);

		$this->pageTitle = 'Members';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Members');
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

		$itemArray	= array();

		$pageItem	= array();

		if ($panelItemTitle == '')
			{
			$panelItemTitle == $air_EleType[$air_selectedEleType];
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

		if ($air_showContext)
			{
			$optTabRow		= & $pageBody->createChild(htmlEleTblRow);
			$optTabData		= & $optTabRow->createChild(htmlEleTblData, '&#160;');
									 $optTabData->setAttribute(htmlAttrAlign, 'right');
			$optTabData		= & $optTabRow->createChild(htmlEleTblData);
									 $optTabData->setAttribute(htmlAttrAlign, 'left');
			$work				= & $optTabData->createChild(htmlEleParagraph);
			$textInfo		= 'The context defines your current <em>viewpoint</em> ';
			$textInfo	  .= ' with respect to the repository content.';
			$textInfo	  .= ' It causes filtering to be applied to what you see';
			$textInfo	  .= ' as element types, properties, associations and related items.';
			$work->setContent($this->htmlLinewrap($textInfo));

			$optTabRow		= & $pageBody->createChild(htmlEleTblRow);
			$optTabData		= & $optTabRow->createChild(htmlEleTblData, 'Context');
									 $optTabData->setAttribute(htmlAttrAlign, 'right');
			$optTabData		= & $optTabRow->createChild(htmlEleTblData);
									 $optTabData->setAttribute(htmlAttrAlign, 'left');
			if ($air_showContextArray)
				{
				$work			= & $optTabData->createChild(htmlEleSelect);
				$work->setAttribute(htmlAttrName, 'dlgContext');
				$this->anchor->sessionDoc->putDialogVarTracker('dlgContext', 'Item'); // Catalog the dialog form variables
				foreach ($air_Context as $key => $value)
					{
					$item		= & $work->createChild(htmlEleOption, $value);
					$item->setAttribute(htmlAttrValue, $key);
					if ($key == $air_selectedContext)
						{
						$item->setAttribute(htmlAttrSelected);
						}
					}
				}
			else
				{
				$work		= & $optTabData->createChild(htmlEleInput);
				$work->setAttribute(htmlAttrType, 'text');
				$work->setAttribute(htmlAttrValue, $air_Context[$air_selectedContext]);
				$work->setAttribute(htmlAttrSize, '64');
				$work->setAttribute(htmlAttrReadonly);
				$this->setHiddenElement('dlgContext',			$air_selectedContext);
				}

			}
		else
			{
			$this->setHiddenElement('dlgContext',			$this->anchor->sessionDoc->getContextId());
			}

//		echo 'ProcOptFormat '. __LINE__ . ' airContext = '.$air_Context.'<br/>';

		$optTabRow		= & $pageBody->createChild(htmlEleTblRow);
		$optTabData	= & $optTabRow->createChild(htmlEleTblData, 'Options');
								 $optTabData->setAttribute(htmlAttrAlign, 'right');
		$optTabData	= & $optTabRow->createChild(htmlEleTblData);
								 $optTabData->setAttribute(htmlAttrAlign, 'left');
		$workset			= & $optTabData->createChild(htmlEleFormCtlGrp);


		$pageItem['itemBody']	= & $pageBody;

		$pageItem['itemFooter']	= & $this->formatStdDialogArray($air_ItemFooter);

		$itemArray[]				= $pageItem;
		}

	} // end of class
/*******************************************************************
 *******************************************************************/

 ?>