<?php
/*
 * dlg_MenuDlg01.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-26 JVS Integration with new standalone PHP environment scripts
 *								This is a prototype for a panel formatting class
 *								that is used to format the content area of an HTML
 *								result. Was previously tiki-air_MainMenu.tpl
 * V1.4 2005-NOV-16 JVS Modified to form the basis for a generic menu display.
 *								This become the physical format routine for a general
 *								case menu selection.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirMenuPanel01';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


class C_AirMenuPanel01 extends C_HtmlPanel {
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
		$air_showContext			= $this->anchor->getDlgVar('air_showContext');
		$air_showContextArray	= $this->anchor->getDlgVar('air_showContextArray');
		$air_Context				= $this->anchor->getDlgVar('air_Context');
		$air_selectedContext		= $this->anchor->getDlgVar('air_selectedContext');
		$air_showEleTypeArray	= $this->anchor->getDlgVar('air_showEleTypeArray');
		$air_listItemArray		= $this->anchor->getDlgVar('air_listItemArray');
		$air_SelectMultiple		= $this->anchor->getDlgVar('air_SelectMultiple');
		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
		$this->setHiddenElement('dlgClientDlgID',	'Client1randomvalue');
		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

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

		switch ($this->getPanelName())
			{
			case Dialog_AirAdmin:
   			$constraintName = 'dlgEleType';
				$text = 'Repository Administration Menu';
				break;
			case Dialog_AirMenu:
   			$constraintName = 'dlgEleType';
				$text = 'Repository Element Maintenance Menu';
				break;
			case Dialog_SysAdmin:
//   			$constraintName = 'dlgEleType';
				$text = 'System Administration Menu';
				break;
			case Dialog_DbCvtMenu:
   			$constraintName = 'dlgEleItem';
				$text = 'DB Conversion Menu';
				break;
			case Dialog_EleNdxMenu:
//   			$constraintName = 'dlgEleItem';
				$text = 'Repository Element Index';
				break;
			case Dialog_DirViewMenu:
//   			$constraintName = 'dlgEleType';
				$text = 'Directory View Menu';
				break;
			default:
//   			$constraintName = 'dlgEleType';
				$text = 'Set title for '.$this->getPanelName().' at dlg_MenuDlg01.fmt['.__LINE__.']';
				break;
			}
		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleAnchor, null, $text);
		$myTitleData->setAttribute(htmlAttrClass, 'ItemTitle');
//		$myTitleData->setAttribute(htmlAttrHRef, $this->anchor->getActionUrlBase().'dialog='.$this->getPanelName());
		$pageItem['itemTitle']	= & $myTitleData;

		$menuTable = new C_AirHtmlElement($this);
		$menuTable->initialize(htmlEleTable);

		if ($air_showContext)
			{
			$menuTabRow		= & $menuTable->createChild(htmlEleTblRow);
			$menuTabData	= & $menuTabRow->createChild(htmlEleTblData, '&#160;');
									 $menuTabData->setAttribute(htmlAttrAlign, 'right');
			$menuTabData	= & $menuTabRow->createChild(htmlEleTblData);
									 $menuTabData->setAttribute(htmlAttrAlign, 'left');
			$work				= & $menuTabData->createChild(htmlEleParagraph);
			$textInfo		= 'The context defines your current <em>viewpoint</em> ';
			$textInfo	  .= ' with respect to the repository content.';
			$textInfo	  .= ' It causes filtering to be applied to what you see';
			$textInfo	  .= ' as element types, properties, associations and related items.';
			$work->setContent($this->htmlLinewrap($textInfo));

			$menuTabRow		= & $menuTable->createChild(htmlEleTblRow);
			$menuTabData	= & $menuTabRow->createChild(htmlEleTblData, 'Context');
									 $menuTabData->setAttribute(htmlAttrAlign, 'right');
			$menuTabData	= & $menuTabRow->createChild(htmlEleTblData);
									 $menuTabData->setAttribute(htmlAttrAlign, 'left');
			if ($air_showContextArray)
				{
				$work		= & $menuTabData->createChild(htmlEleSelect);
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
				$work		= & $menuTabData->createChild(htmlEleInput);
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

		if ($air_showEleTypeArray)
			{
			$menuTabRow		= & $menuTable->createChild(htmlEleTblRow);
			$menuTabData	= & $menuTabRow->createChild(htmlEleTblData, 'Constraint Selection');
									 $menuTabData->setAttribute(htmlAttrAlign, 'right');
			$menuTabData	= & $menuTabRow->createChild(htmlEleTblData);
									 $menuTabData->setAttribute(htmlAttrAlign, 'left');

			$work		= & $menuTabData->createChild(htmlEleSelect);
			$work->setAttribute(htmlAttrName, $constraintName);
			$this->anchor->sessionDoc->putDialogVarTracker($constraintName, 'Item'); // Catalog the dialog form variables
			foreach ($air_EleType as $key => $value)
				{
				$item		= & $work->createChild(htmlEleOption, $value);
				$item->setAttribute(htmlAttrValue, $key);
				if ($key == $air_selectedEleType)
					{
					$item->setAttribute(htmlAttrSelected);
					}
				}
			}

		$menuTabRow		= & $menuTable->createChild(htmlEleTblRow);
		$menuTabData	= & $menuTabRow->createChild(htmlEleTblData, 'Action Choice');
								 $menuTabData->setAttribute(htmlAttrAlign, 'right');
		$menuTabData	= & $menuTabRow->createChild(htmlEleTblData);
								 $menuTabData->setAttribute(htmlAttrAlign, 'left');
		$workset			= & $menuTabData->createChild(htmlEleFormCtlGrp);

		foreach ($air_listItemArray as $key => $value)
			{
			$work		= & $workset->createChild(htmlEleInput);
			if ($air_SelectMultiple)
				{
				$work->setAttribute(htmlAttrType, 'checkbox');
				if (!empty($key))
					{
					$work->setAttribute(htmlAttrName, $key);
				 	if ((strpos($key, 'dlgChoice:') == 0)
					 && (strpos($key, 'dlgChoice:') !== false))
						{
						/*
						 * Checkbox values recorded in HTML with unique 'dlgChoice:xxxxx' variable
						 * names will be re-coded and pased in the original decode message as a
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
				$work->setAttribute(htmlAttrType, 'radio');
				$work->setAttribute(htmlAttrName, 'dlgAction');
				$work->setAttribute(htmlAttrValue, $key);
				$this->anchor->sessionDoc->putDialogVarTracker('dlgAction', 'Item'); // Catalog the dialog form variables
				}
			if ($value['on'])
				{
				$work->setAttribute(htmlAttrChecked);
				}
			$work->setContent($value['content']);
			$work		= & $workset->createChild(htmlEleBreak);
			}

		$pageItem['itemBody']	= & $menuTable;

		$cmdTable = new C_AirHtmlElement($this);
		$cmdTable->initialize(htmlEleTable);

		$cmdTabRow		= & $cmdTable->createChild(htmlEleTblRow);
		$cmdTabData		= & $cmdTabRow->createChild(htmlEleTblData);
								 $cmdTabData->setAttribute(htmlAttrAlign, 'center');

		$cmdTabBtn		= & $cmdTabData->createChild(htmlEleInput);
								 $cmdTabBtn->setAttribute(htmlAttrType, 'submit');
								 $cmdTabBtn->setAttribute(htmlAttrName, 'request');
								 $cmdTabBtn->setAttribute(htmlAttrValue, AIR_Action_Submit);

		$cmdTabBtn		= & $cmdTabData->createChild(htmlEleInput);
								 $cmdTabBtn->setAttribute(htmlAttrType, 'submit');
								 $cmdTabBtn->setAttribute(htmlAttrName, 'request');
								 $cmdTabBtn->setAttribute(htmlAttrValue, AIR_Action_Quit);

		$pageItem['itemFooter']	= & $cmdTable;

		$itemArray[]				= $pageItem;

		$this->panelItems = $itemArray;
		}

	} // end of class

 ?>